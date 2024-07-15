<?php 

function photo_data_All($post)
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


function api_photos_get($request) 
{
    // query parameters na routa photo:http://localhost:8943/json/api/photo/?_total=10&_page=1&_user=6 
    // para pegar as query parameters usamos o $request com chave
   $_total =  sanitize_text_field( $request["_total"]) ? : 6 ;
   // "page" será a quantidade de pagina no caso será 6 items para uma pagina
   // se o usuário querem mais item terá que acessa a próxima pagina 
   $_page = sanitize_text_field($request["_page"]) ? : 1 ;
   $_user =  sanitize_text_field($request["_user"]) ? : 0;

   // também podemos pegar pelo nome no caso usamos condicional para isso 
   // isso e para garantir que sempre passe o id no request
   if(is_numeric($_user)){
     $user = get_user_by("login", $_user);
    if(!$user){
        $response = new WP_Error("error" ,
    "Usuário  não encontrado.", ["status" => 404]);
   return rest_ensure_response($response);
    }
     $_user = $user->ID;
   }

   // criado argumento para a buscar
   $args = [
      "post_type" => "post", 
      "author" => $_user,
      "post_per_page" => $_total, 
      "paged" => $_page
   ];
   // buscado com Wp_Query
   $query = new WP_Query($args);
   $posts = $query->posts;

   $photos = [];
   if($posts) {
     foreach($posts as $post){
       $photos[] = photo_data_All($post);
     };
   }


 /*$response = [
    'total' => $_total,
    'page' => $_page,
    'user' => $_user
 ];*/

   return rest_ensure_response($photos);
}

function register_api_photos_get() {
  register_rest_route('api', '/photo/', [
    'methods' => WP_REST_Server::READABLE,  // post: CREATABLE para get: READABLE . delete: DELETABLE
    'callback' => 'api_photos_get',
  ]);
}
add_action('rest_api_init', 'register_api_photos_get');
