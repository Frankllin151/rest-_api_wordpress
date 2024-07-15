<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php the_title(); ?></title>
    <link rel="stylesheet" href="<?php echo esc_url(get_stylesheet_uri()); ?>" type="text/css">
    <?php wp_head(); ?>

</head>
<body class="<?php body_class(); ?>">

<?php get_header(); ?>

<?php
$produtos = wc_get_products(array(
    'status' => 'publish', // Produtos publicados
    'limit' => -1, // Limitar a quantidade de produtos (-1 para todos)
));

// Verificar se existem produtos disponíveis
if ($produtos) {
    foreach ($produtos as $produto) {
        // Título do produto
        echo $produto->get_title() . '<br>';

        // Imagem do produto
        $imagem = $produto->get_image(); // Obtém a tag HTML da imagem
        echo $imagem . '<br>';

        // Preço do produto
        $preco = $produto->get_price_html(); // Obtém o preço formatado
        echo $preco . '<br>';

        // Descrição do produto
        $descricao = $produto->get_description(); // Obtém a descrição
        echo $descricao . '<br>';

        // Mais informações ou manipulações do produto aqui...
    }
} else {
    // Caso não haja produtos disponíveis
    echo 'Nenhum produto disponível.';
}
?>


<?php get_footer(); ?>
</body>
</html>