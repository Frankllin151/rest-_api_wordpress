<?php wp_head(); ?>
<h1>Api</h1>
<?php wp_footer(); 

// Verificar se o POST contém o campo 'mercado_pago_payment_method_id'
if (isset($_POST['mercado_pago_payment_method_id']) && !empty($_POST['mercado_pago_payment_method_id'])) {
  $payment_method_id = $_POST['mercado_pago_payment_method_id'];

  // Aqui você pode realizar ações adicionais com o payment_method_id
  echo 'Método de pagamento: ' . htmlspecialchars($payment_method_id);
} else {
  echo 'Método de pagamento não encontrado.';
}
?>