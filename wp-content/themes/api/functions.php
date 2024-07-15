<?php 
// remova todas as routa padrão do wp 
// quando for colocar na produção tirar o remove_action

//remove_action("rest_api_init", "create_initial_rest_route" , 99);

add_filter("rest_endpoints" , function($endpoints){
    // remover a routa http://localhost:8943/json/wp/v2/users 
    // por questão de segurança
    unset($endpoints["/wp/v2/users"]);
    unset($endpoints["/wp/v2/users/(?P<id>[\d]+)"]);
    return $endpoints;
});

// diretorio base 
 $dirbase = get_template_directory();
// chamando arquivos da pasta endpoints
require_once  $dirbase ."/endpoints/user_post.php";
require_once  $dirbase ."/endpoints/user_get.php";

require_once  $dirbase ."/endpoints/photo_post.php";
require_once $dirbase ."/endpoints/photo_delete.php";
require_once $dirbase ."/endpoints/photo_get.php";
require_once $dirbase ."/endpoints/photo_getAll.php";

require_once $dirbase ."/endpoints/comments_post.php";
require_once $dirbase ."/endpoints/comments_get.php";

require_once $dirbase ."/endpoints/password.php";

// stas get
require_once $dirbase ."/endpoints/stats_get.php";

/// puxados produtos WooorCommerce 
require_once $dirbase ."/endpoints/produto_woorcommerce_getAll.php";
require_once $dirbase ."/endpoints/puxadoprodutosunicos.php";
require_once $dirbase ."/endpoints/pagamentoUnico.php";



function change_api()
{
return "json";
}
add_filter("rest_url_prefix" , "change_api");

// determina tempo para o token ficar valido
function expire_token()
{
    return time() + (60 * 60 * 24);
}
add_action("jwt_auth_expire" , 'expire_token');

// definir tamanho da imagem 
update_option("large_size_w" , 1000);
update_option("large_size_h" , 1000);
update_option("large_crop" , 1);


// Adicionar rota para listar produtos (número de barras, preço)
add_action('rest_api_init', 'adicionar_rota_listar_produtos');

function adicionar_rota_listar_produtos() {
    register_rest_route('meu-plugin/v1', '/codigobarra', array(
        'methods' => WP_REST_Server::CREATABLE, // aqui e create
        'callback' => 'listar_produtos',
    ));
}

function listar_produtos($data) {
    // Array associativo de correspondências: número de barras => novo preço
    $correspondencias_precos = array(
        "542818" => "1.00",  // Exemplo de correspondência número de barras => novo preço
        // Adicionar outras correspondências aqui conforme necessário
    );

    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
    );

    $products = get_posts($args);

    $produtos = array();
    foreach ($products as $product) {
        $product_data = wc_get_product($product->ID);
        $sku = $product_data->get_sku();
        $nome = $product_data->get_name();
        $preco = $product_data->get_price();

        // Extrair número de barras do título (padrão: grupo de dígitos no final da string)
        preg_match('/(\d+)$/', preg_replace('/[^0-9]/', '', $nome), $matches);
        $numero_de_barras = isset($matches[1]) ? $matches[1] : '';

        // Verificar se há correspondência no array de correspondências
        if (isset($correspondencias_precos[$numero_de_barras])) {
            $novo_preco = $correspondencias_precos[$numero_de_barras];
            $product_data->set_price($novo_preco);
            $product_data->save();
            $preco = $novo_preco; // Atualiza o preço no array de produtos
        }

        // Adicionar informações do produto ao array
        $produtos[] = array(
            'sku' => $sku,
            'numero_de_barras' => $numero_de_barras,
            'titulo' => $nome,
            'preco' => $preco,
        );
    }

    return rest_ensure_response($produtos);
}

