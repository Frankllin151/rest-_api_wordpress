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



/// sistema de pagamento que funcionar

function mp_process_payment($request) {
    $access_token = MP_ACCESS_TOKEN;
     // Gerar um UUID para o cabeçalho X-Idempotency-Key
     $idempotency_key = wp_generate_uuid4(); 
    $body = array(
        'transaction_amount' => 100,
        'description' => 'Descrição do produto',
        'payment_method_id' => 'pix',
        'payer' => array(
            'email' => $request['email'],
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'identification' => array(
                'type' => 'CPF',
                'number' => $request['cpf']
            )
        ),
        'notification_url' => 'https://0e91-2804-3380-5e01-b300-65a5-cc00-1982-e87f.ngrok-free.app/json/api/payment',
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
    $data = json_decode($body, true); // Use true para retornar um array associativo
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_REST_Response(array('error' => 'Erro ao decodificar JSON: ' . json_last_error_msg()), 500);
    }
    
    // Imprime a resposta para depuração
    error_log(print_r($data, true)); // Verifique o log para ver o conteúdo real
   
   
    
    return rest_ensure_response($data["point_of_interaction"]["transaction_data"]["ticket_url"]);
}

add_action('rest_api_init', function () {
    add_cors_headers();
    register_rest_route('api', '/payment', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'mp_process_payment',
    ));
});