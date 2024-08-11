<?php 
function puxar_id_api_produto($product_id) {
    $product = get_post($product_id);

    if (!$product || $product->post_type !== 'product') {
        return null;
    }

    $price = get_post_meta($product_id, '_price', true);
    $regular_price = get_post_meta($product_id, '_regular_price', true);
    $sale_price = get_post_meta($product_id, '_sale_price', true);

    $product_data = [
        'id' => $product_id,
        'name' => $product->post_title,
        'description' => $product->post_content,
        'price' => $price,
        'regular_price' => $regular_price,
        'sale_price' => $sale_price,
    ];

    return $product_data;
}

function criar_pedido($user_data) {
    $order_data = [
        'payment_method' => 'mercadopago',
        'payment_method_title' => 'Mercado Pago',
        'set_paid' => false,
        'billing' => [
            'first_name' => $user_data['nome'],
            'last_name' => '',
            'address_1' => $user_data['endereco'],
            'city' => 'Natal',
            'state' => 'Rio grande do norte',
            'postcode' => $user_data['cep'],
            'country' => 'BR',
            'email' => $user_data['email'],
            'phone' => '',
        ],
        'line_items' => [
            [
                'product_id' => $user_data['id_produto'],
                'quantity' => 1,
                'total' => $user_data['price_produto'],
            ],
        ],
    ];

    $order = wc_create_order();
    $order->set_address($order_data['billing'], 'billing');

    foreach ($order_data['line_items'] as $item) {
        $product = wc_get_product($item['product_id']);
        $order->add_product($product, $item['quantity']);
    }

    $order->set_payment_method($order_data['payment_method']);
    $order->calculate_totals();

    return $order->save();
}

function mp_process_payment($order_id) {
    $order = wc_get_order($order_id);
    $access_token = MP_ACCESS_TOKEN;
    $idempotency_key = wp_generate_uuid4(); 
    $body = array(
        'transaction_amount' => (float) $order->get_total(),
        'description' => 'Descrição do produto',
        'payment_method_id' => 'pix',
        'payer' => array(
            'email' => $order->get_billing_email(),
            'first_name' => $order->get_billing_first_name(),
            'last_name' => $order->get_billing_last_name(),
            'identification' => array(
                'type' => 'CPF',
                'number' => $order->get_meta('_billing_cpf')
            )
        ),
        'notification_url' => 'https://1c69-2804-3380-5e01-b300-d0b5-af3c-8f70-9af3.ngrok-free.app/json/api/payment',
    );

    $args = array(
        'body'    => json_encode($body),
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type'  => 'application/json',
            'X-Idempotency-Key'  => $idempotency_key,
        ),
    );

    $response = wp_remote_post('https://api.mercadopago.com/v1/payments', $args);

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        return new WP_REST_Response(array('error' => $error_message), 500);
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_REST_Response(array('error' => 'Erro ao decodificar JSON: ' . json_last_error_msg()), 500);
    }
    
    // Depuração - Verifique a resposta completa
    error_log('Resposta da API do Mercado Pago: ' . print_r($data, true));
    
    // Verifique a estrutura antes de acessar a URL do ticket
    return rest_ensure_response($data["point_of_interaction"]["transaction_data"]["ticket_url"]);
}
 /// criado processo de pedido e pagamento
 function api_criar_pedido_e_processar_pagamento($request) {
    try {
        $user_data = [
            'nome' => $request['nome'],
            'email' => $request['email'],
            'endereco' => $request['endereco'],
            'cep' => $request['cep'],
            'id_produto' => $request['id_produto'],
        ];

        $product_data = puxar_id_api_produto($user_data['id_produto']);

        if (!$product_data) {
            return new WP_Error('no_product', 'Produto não encontrado', ['status' => 404]);
        }

        $price_produto = $product_data['price'] ?: ($product_data['regular_price'] ?: $product_data['sale_price']);

        if ($price_produto === null) {
            return new WP_Error('no_price', 'Preço não encontrado', ['status' => 404]);
        }

        $user_data['price_produto'] = $price_produto;
        $order_id = criar_pedido($user_data);

        if (is_wp_error($order_id)) {
            return $order_id;
        }

        // Processar pagamento com Mercado Pago
        $ticket_url = mp_process_payment($order_id);

        if (is_wp_error($ticket_url)) {
            return $ticket_url;
        }
     
       return rest_ensure_response($ticket_url);
    } catch (Exception $e) {
        error_log('Exceção ao criar pedido e processar pagamento: ' . $e->getMessage());
        return new WP_Error('order_creation_exception', 'Exceção ao criar pedido e processar pagamento');
    }
}

function register_api_criar_pedido_e_processar_pagamento() {
    register_rest_route('api', '/create-order-and-process-payment', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'api_criar_pedido_e_processar_pagamento',
        'permission_callback' => function () {
            return true; // Permissão para qualquer usuário, ajustar conforme necessário
        },
    ]);
}

add_action('rest_api_init', 'register_api_criar_pedido_e_processar_pagamento');

function add_cors_headers() {
    header('Access-Control-Allow-Origin: *'); // Permitir todas as origens, ajuste conforme necessário
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: Authorization, Content-Type');
}

// Adiciona cabeçalhos CORS apenas para o endpoint específico
add_action('rest_api_init', function () {
    add_cors_headers();
}, 15);