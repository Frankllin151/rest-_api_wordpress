<?php

function photo_data($post)
{
 // pegar os meta field que seria peso, idade , nome. 
 //usa function nativa do wp get_post_meta();
 // o $post_meta já chamar o id da img
 // mas para acessa o id da imagem precisa usar: $post_meta["img"]
 $post_meta = get_post_meta($post->ID);
 // source da imagem
 // O $post_meta["img"] vai vim um array
 // mais como e para acessa array unico colocamos  $post_meta["img"][0]
 $src = wp_get_attachment_image_src($post_meta["img"][0],"large")[0];
 // puxado dados do usuário que fez a postagem
 $user = get_userdata($post->post_author);
 // total de comentario na postagens 
 $total_comments = get_comments_number($post->ID);

 return [
    "id" => $post->ID,
    "author" => $user->user_login,
    "title" => $post->post_title,
    "date" => $post->post_date,
    "src" => $src,
    "peso" => $post_meta["peso"][0],
    "idade" => $post_meta["idade"][0],
    "acessos" => $post_meta["acessos"][0],
    "total_comments" => $total_comments
 ];
}

function api_photo_get($request) 
{
  $post_id = $request['id'];
  $post = get_post($post_id);

 if(!isset($post) || empty($post_id)){
    $response = new WP_Error("error" ,
    "Post não encontrado.", ["status" => 404]);
   return rest_ensure_response($response);
 }

 $photo = photo_data($post);

 // todas vezes que fazer um get vai ser mais um acesso na imagem
 $photo["acessos"] = (int) $photo["acessos"] + 1;
 update_post_meta($post_id, "acessos" , $photo["acessos"]);

 // pegado os comentario 
 $comments = get_comments([
    "post_id" => $post_id,
    "order" => "ASC", 


 ]);

 $response = [
   "photo" => $photo,
   "comments" => $comments
 ];
   return rest_ensure_response($photo);
}

function register_api_photo_get() {
  register_rest_route('api', '/photo/(?P<id>[0-9]+)', [
    'methods' => WP_REST_Server::READABLE,  // post: CREATABLE para get: READABLE . delete: DELETABLE
    'callback' => 'api_photo_get',
  ]);
}
add_action('rest_api_init', 'register_api_photo_get');

