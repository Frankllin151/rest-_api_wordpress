<?php
/*
Plugin Name: Atualizar Preços
Description: Plugin para atualizar preços dos produtos com base em um arquivo JSON.
Version: 1.0
Author: Seu Nome
*/



// Adicionar menu no painel de administração


function atualizar_precos_menu() {
    add_menu_page(
        'Atualizar Preços', 
        'Atualizar Preços', 
        'manage_options', 
        'atualizar_precos', 
        'atualizar_precos_page'
    );
}
add_action('admin_menu', 'atualizar_precos_menu');
function atualizar_precos_page() {
    ?>
<div class="wrap">
  <h1>Atualizar Preços</h1>
  <form method="post" enctype="multipart/form-data">
    <input type="file" name="precos_json" />
    <input type="submit" name="atualizar_precos" value="Atualizar Preços" class="button button-primary" />
  </form>
  <?php
        if (isset($_POST['atualizar_precos'])) {
            atualizar_precos();
        }
        ?>
</div>
<?php
}

function atualizar_precos() {
    if (isset($_FILES['precos_json']) && $_FILES['precos_json']['error'] == 0) {
        $file = $_FILES['precos_json']['tmp_name'];
        $json_data = file_get_contents($file);
        $data = json_decode($json_data, true);
       
        if (json_last_error() === JSON_ERROR_NONE) {
            
          update_precos($data);
            echo '<div class="updated"><p>Preços atualizados com sucesso!</p></div>';
        } else {
            echo '<div class="error"><p>Erro ao processar o JSON!</p></div>';
        }
    } else {
        echo '<div class="error"><p>Erro ao carregar o arquivo!</p></div>';
    }
}

function   update_precos($data){
  foreach ($data as $item) {
    $codigo_de_barras = $item['codigo_de_barras'];
    $preco_novo = $item['preco_do_excel'];

    // Buscar produtos com base no título que contém o código de barras
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
    );
    $products = get_posts($args);

    foreach ($products as $product) {
        $product_data = wc_get_product($product->ID);
        $nome = $product_data->get_name();
     
        // Extrair número de barras do título
        preg_match('/(\d+)$/', preg_replace('/[^0-9]/', '', $nome), $matches);
        $numero_de_barras = isset($matches[1]) ? $matches[1] : '';

        if ($numero_de_barras === $codigo_de_barras) {
          
          
         
          echo "preço do excel:". floatval($preco_novo);
          $product_data->set_regular_price(floatval($preco_novo));
            $product_data->save();
            $updated_product = wc_get_product($product_data->get_id());

if ($updated_product && $updated_product->get_price() == $preco_novo) {
    echo "<pre>Preço atualizado com sucesso para o produto ID: " . $product_data->get_id() . "-" .  $preco_novo . "</pre>";
} else {
    echo "<pre>Erro ao atualizar o preço para o produto ID: " . $product_data->get_id() . "-" . "preço atual:". $updated_product->get_price() ."</pre>";
}
 
            //echo "<pre>Produto atualizado: " . $nome . " | Novo preço: " . floatval($preco_novo) . "</pre>";
        }
    }
}
}
?>