<?php 

function puxar_produto_unico($product_id) {
    $product = get_post($product_id);

    if (!$product || $product->post_type !== 'product') {
        return null;
    }

    $product_data = [
        'id' => $product_id,
        'name' => $product->post_title,
        'description' => $product->post_content,
        'price' => get_post_meta($product_id, '_price', true),
        'regular_price' => get_post_meta($product_id, '_regular_price', true),
        'sale_price' => get_post_meta($product_id, '_sale_price', true),
        'stock_status' => get_post_meta($product_id, '_stock_status', true),
        'sku' => get_post_meta($product_id, '_sku', true),
        'weight' => get_post_meta($product_id, '_weight', true),
        'dimensions' => [
            'length' => get_post_meta($product_id, '_length', true),
            'width' => get_post_meta($product_id, '_width', true),
            'height' => get_post_meta($product_id, '_height', true),
        ],
        'categories' => wp_get_post_terms($product_id, 'product_cat', ['fields' => 'names']),
        'tags' => wp_get_post_terms($product_id, 'product_tag', ['fields' => 'names']),
        'image' => wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'large'),
    ];

    return $product_data;
}




function api_get_produto_unicos($request) {
    $product_id = $request['id'];

    // Obter os detalhes do produto
    $product_data = puxar_produto_unico($product_id);

    if (!$product_data) {
        return new WP_Error('no_product', 'Produto não encontrado', ['status' => 404]);
    }

    return rest_ensure_response($product_data);
}

function register_api_get_produto_unicos() {
    register_rest_route('api', '/products/(?P<id>[0-9]+)', [
        'methods' => WP_REST_Server::READABLE,  // post: CREATABLE para get: READABLE . delete: DELETABLE
        'callback' => 'api_get_produto_unicos',
    ]);
}

add_action('rest_api_init', 'register_api_get_produto_unicos');