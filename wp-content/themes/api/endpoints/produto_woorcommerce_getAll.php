<?php 

function get_all_woocommerce_products() {
    // Definir argumentos para a consulta de produtos
    $args = [
        'post_type' => 'product',
        'posts_per_page' => -1,
    ];

    // Obter todos os produtos
    $products = get_posts($args);

    $all_products = [];

    // Iterar sobre cada produto e obter dados necessários
    foreach ($products as $product) {
        $product_id = $product->ID;
        $product_meta = get_post_meta($product_id);
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

        $all_products[] = $product_data;
    }

    return $all_products;
}

function api_get_all_products($request) {
    $products = get_all_woocommerce_products();

    if (empty($products)) {
        return new WP_Error('no_products', 'Nenhum produto encontrado', ['status' => 404]);
    }

    return rest_ensure_response($products);
}

function register_api_get_all_products() {
    register_rest_route('api', '/products/', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_get_all_products',
    ]);
}

add_action('rest_api_init', 'register_api_get_all_products');
