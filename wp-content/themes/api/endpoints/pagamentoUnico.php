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


function processar_pagamento($order_id){
    $order = wc_get_order($order_id);
   
    if ($order->is_paid()) {
        return $order;
    }

    $access_token = "seutokenaqui";

     // Definir os dados do pagamento
     $payment_data = [
        'transaction_amount' => (float) $order->get_total(),
        'token' => $access_token, // Token de pagamento do Mercado Pago
        'description' => 'Pedido #' . $order_id,
        'installments' => 1, // Número de parcelas
        'payment_method_id' => $_POST['mercado_pago_payment_method_id'],
        'payer' => [
            'email' => $order->get_billing_email(),
            'identification' => [
                'type' => $_POST['mercado_pago_identification_type'],
                'number' => $_POST['mercado_pago_identification_number']
            ]
        ]
    ];

     // Enviar a solicitação de pagamento para o Mercado Pago
     $response = wp_remote_post('https://api.mercadopago.com/v1/payments', [
        'method' => 'POST',
        'headers' => [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json'
        ],
        'body' => json_encode($payment_data)
    ]);

     // Processar a resposta
     if (is_wp_error($response)) {
        return new WP_Error('payment_error', 'Erro ao processar o pagamento');
    }
    $response_body = json_decode(wp_remote_retrieve_body($response), true);

    if ($response_body['status'] == 'approved') {
        $order->payment_complete($response_body['id']);
        $order->add_order_note('Pagamento aprovado pelo Mercado Pago. ID de transação: ' . $response_body['id']);
        return $order;
    } else {
        return new WP_Error('payment_declined', 'Pagamento recusado pelo Mercado Pago. Motivo: ' . $response_body['status_detail']);
    }

    
}



function api_criar_pedido_e_processar_pagamento($request)
{
    $user_data = [
        'nome' => $request['nome'],
        'email' => $request['email'],
        'endereco' => $request['endereco'],
        'cep' => $request['cep'],
        'id_produto' => $request['id_produto'],
        'price_produto' => $request["price_produto"],
        
    ];
    $product_data = puxar_id_api_produto($user_data["id_produto"]);
    
    if (!$product_data) {
        return new WP_Error('no_product', 'Produto não encontrado', ['status' => 404]);
    }

    $price_produto = null;

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


    $user_data = [
        'nome' => $request['nome'],
        'email' => $request['email'],
        'endereco' => $request['endereco'],
        'cep' => $request['cep'],
        'id_produto' => $request['id_produto'],
        'price_produto' => $price_produto,
        
    ];

    return rest_ensure_response([
        'status' => 'success',
        'order_id' => $order_id,
        'payment_id' => $payment_response->get_transaction_id()
    ]);
}
// http://localhost:8943/json/wc/v3/orders == https://seusite.com/wp-json/wc/v3/orders

function register_api_criar_pedido_e_processar_pagamento() {
    register_rest_route('api', '/create-order-and-process-payment', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'api_criar_pedido_e_processar_pagamento',
    ]);
}

add_action('rest_api_init', 'register_api_criar_pedido_e_processar_pagamento');

