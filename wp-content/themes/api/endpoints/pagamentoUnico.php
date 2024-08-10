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
            'city' => '',
            'state' => '',
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

function processar_pagamento($order_id) {
    try {
        $order = wc_get_order($order_id);

        if ($order->is_paid()) {
            return $order;
        }
       return rest_ensure_response($order);
    } catch (Exception $e) {
        error_log('Exceção ao processar pagamento: ' . $e->getMessage());
        return new WP_Error('payment_exception', 'Exceção ao processar o pagamento');
    }

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
            'price_produto' => $request['price_produto'],
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

        // Processar pagamento
        $payment_response = processar_pagamento($order_id);
        if (is_wp_error($payment_response)) {
            return $payment_response;
        }

        return rest_ensure_response([
            'status' => 'success',
            'order_id' => $order_id,
            'payment_id' => $payment_response->get_transaction_id()
        ]);
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