<?php

function api_photo_delete($request)
{
 // Pegado usuário do wp 
 $user = wp_get_current_user();
 $user_id = (int) $user->ID;
 // id do post
 $post_id = $request["id"];
 $post = get_post($post_id);
 // id do author do post
 // (int) vai sempre return um numero 
 $author_id = (int) $post->post_author;

 if($user_id !== $author_id || !isset($post)){
    $response = new WP_Error("error" ,
    "Sem permissão", ["status" => 401]);
   return rest_ensure_response($response);
 }
 
  // puxado o id do attachment (img)
 $attachment_id = get_post_meta($post_id, "img" , true);
 wp_delete_attachment($attachment_id , true);
 wp_delete_post($post_id , true);

 


 return rest_ensure_response("Post Deletado");
}

// resgitrar routa 
function register_api_photo_delete()
{
    // function do wp para registrar routa
    register_rest_route("api" , "/photo/(?P<id>[0-9]+)" ,[
        'methods' => WP_REST_Server::DELETABLE,  // post: CREATABLE para get: READABLE . delete: DELETABLE
        'callback' => "api_photo_delete"
    ]);
}
add_action("rest_api_init" , "register_api_photo_delete");