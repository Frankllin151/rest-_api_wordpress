<?php
add_action( 'wp_enqueue_scripts', 'theme_lood' );


function theme_lood() {
    

 // Obtém o caminho completo para o arquivo CSS
 $css_file_path = get_parent_theme_file_path("/assets/css/root.css");

 // Obtém o número de versão usando filemtime
 $version = filemtime($css_file_path);

 // Adiciona o estilo CSS com o número de versão dinâmico
 wp_enqueue_style(
     "theme_root_style",
     get_parent_theme_file_uri("/assets/css/root.css"),
     array(),
     $version, // Número de versão dinâmico
     'all'
 );


 $Js_file_path = get_parent_theme_file_path("/assets/js/scritps.js");
    $versionJs = filemtime($Js_file_path);

    wp_enqueue_script('dropdown' , get_template_directory_uri().
    '/assets/js/scritps.js',
              array(), $versionJs  );

}


// Registrando menu 
register_nav_menus(array(
    'wp_devs_main_menu' => "Main Menu", 
    'wp_devs_footer0_menu' => "Footer Menu",
));