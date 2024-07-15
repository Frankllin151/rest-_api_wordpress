<?php

function api_user_post($request)
{
       // faça sua logica aqui para inserir o usuario 
    $email = sanitize_email($request["email"]);
    $username = sanitize_text_field($request["username"]);
    $password = $request["password"];
    
    // verificado ser os inputs está vazios // criado error com WP_Error
     if(empty($email) || empty($username) || empty($password)){
           $response = new WP_Error("error", "Dados Incompleto" ,["status" => 406]);
           return rest_ensure_response($response);
     }
     // verificar se o usuario existe 
     if(username_exists($username) || email_exists($email)){
        $response = new WP_Error("error", "email já registrado" ,["status" => 403]);
        return rest_ensure_response($response);
  }

    // criado usuário dentro da function nativa do wp 
    $response = wp_insert_user([
        // passa nomes especificos  // user_login , user_email , user_password
        "user_login" => $username,
        "user_email" => $email, 
        "user_pass" => $password,
        // o subscriber e para o usuário seja um visitante no wp 
        "role" => "subscriber"
    ]);

    
 

 return rest_ensure_response($response);
}

// resgitrar routa 
function register_api_user_post()
{
    // function do wp para registrar routa
    register_rest_route("api" , "/user" ,[
        'methods' => WP_REST_Server::CREATABLE, // para ler os resultado colocar WP_REST_Server::READABLE
        'callback' => "api_user_post"
    ]);
}
add_action("rest_api_init" , "register_api_user_post");



