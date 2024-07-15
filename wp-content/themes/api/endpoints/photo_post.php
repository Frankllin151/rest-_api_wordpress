<?php

function api_photo_post($request)
{
 // Pegado usuário do wp 
 $user = wp_get_current_user();
 $user_id = $user->ID;
 
 if($user_id === 0){
    $response = new WP_Error("error" ,
     "Usuário não possui permissão", ["status" => 401]);
    return rest_ensure_response($response);
  }

  /// pegado valor dos dados para enviar photo,nome,peso etc
   $nome = sanitize_text_field( $request["nome"]);
   $peso = sanitize_text_field( $request["peso"]);
   $idade = sanitize_text_field( $request["idade"]);
   $files = $request->get_file_params();
   if(empty($nome) || empty($peso) || empty($idade) || empty($files)){
    $response = new WP_Error("error" ,
     "Dados Incompletos", ["status" => 422]);
    return rest_ensure_response($response);
   }
   
   // para postar dentro do wp usamos uma function nativa 
   $response = [
    "post_author" => $user_id,
    "post_type" => "post", 
    "post_status" => "publish",
    "post_title" => $nome, 
    "post_content" => $nome, 
    "files" => $files,
    "meta_input" =>[
        "peso" => $peso, 
        "idade" => $idade,
        "acessos" => 0
     ]
    ];
    // pegado o id do post  $post_id
    // wp_insert_post return o id do post
  $post_id =  wp_insert_post($response);
   
  require_once ABSPATH . 'wp-admin/includes/image.php';
  require_once ABSPATH . 'wp-admin/includes/file.php';
  require_once ABSPATH . 'wp-admin/includes/media.php';

  // pegado id da imagem pelo $photo_id
  // media_handle_upload() return o id da imagem
  $photo_id =  media_handle_upload("img", $post_id);
   update_post_meta($post_id , "img", $photo_id); 


 return rest_ensure_response($response);
}

// resgitrar routa 
function register_api_photo_post()
{
    // function do wp para registrar routa
    register_rest_route("api" , "/photo" ,[
        'methods' => WP_REST_Server::CREATABLE,  // post: CREATABLE para get: READABLE
        'callback' => "api_photo_post"
    ]);
}
add_action("rest_api_init" , "register_api_photo_post");