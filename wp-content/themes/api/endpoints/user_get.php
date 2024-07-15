<?php

function api_user_get($request)
{
 // Pegado usuário do wp 
 $user = wp_get_current_user();
 $user_id = $user->ID;

  // verifica se tá passado token 
  if($user_id === 0){
    $response = new WP_Error("error" ,
     "Usuário não possui permissão", ["status" => 401]);
    return rest_ensure_response($response);
  }

 $response = [
  "id" => $user_id, 
  "username" => $user->user_login,
  "nome" => $user->display_name, 
  "email" => $user->user_email,

 ];
 return rest_ensure_response($response);
}

// resgitrar routa 
function register_api_user_get()
{
    // function do wp para registrar routa
    register_rest_route("api" , "/user" ,[
        'methods' => WP_REST_Server::READABLE,  // post: CREATABLE para get: READABLE
        'callback' => "api_user_get"
    ]);
}
add_action("rest_api_init" , "register_api_user_get");