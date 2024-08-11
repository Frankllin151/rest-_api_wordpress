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