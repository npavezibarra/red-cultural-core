<?php
/**
 * Custom WooCommerce Customer Payment Retry Email Template
 *
 * @var WC_Order $order
 */
if (!defined('ABSPATH')) {
    exit;
}

$order_number = $order->get_order_number();
$billing_first_name = $order->get_billing_first_name();
$total = $order->get_formatted_order_total();
$payment_url = $order->get_checkout_payment_url();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Red Cultural - Reintentar Pago #<?php echo esc_html($order_number); ?></title>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #efefef; margin: 0; padding: 24px 16px; color: #111827; }
        .container { max-width: 500px; margin: 0 auto; background-color: #ffffff; border-radius: 6px; overflow: hidden; border: 1px solid #f3f4f6; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .header { padding: 28px 32px 16px; text-align: center; }
        .subheader { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.2em; color: #9ca3af; margin-bottom: 6px; }
        .title { font-size: 22px; font-weight: 500; letter-spacing: -0.025em; margin: 0; }
        .content { padding: 8px 32px 28px; }
        .pill { display:inline-block; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.12em; background:#fef3c7; color:#92400e; padding: 6px 10px; border-radius: 999px; }
        .btn { display:block; width:100%; background:#000; color:#fff !important; text-decoration:none; text-align:center; padding: 12px 0; border-radius: 6px; font-size: 12px; font-weight: 600; letter-spacing: 0.05em; margin-top: 16px; }
        .muted { color:#6b7280; font-size: 13px; margin: 10px 0 0; line-height: 1.6; }
        .box { background:#f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px 16px; margin-top: 14px; }
        .k { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.12em; color:#9ca3af; margin: 0 0 6px; }
        .v { font-size: 14px; font-weight: 800; margin: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="margin-bottom: 18px;">
                <img src="https://red-cultural.cl/wp-content/uploads/2021/01/logoRedCulturalNegro.svg" alt="Red Cultural" style="width: 140px; height: auto; display: inline-block;">
            </div>
            <span class="pill">Pago Pendiente</span>
            <p class="subheader" style="margin-top: 14px;">Reintenta tu pago</p>
            <h1 class="title">Pedido #<?php echo esc_html($order_number); ?></h1>
        </div>
        <div class="content">
            <p class="muted">Hola <?php echo esc_html($billing_first_name ?: ''); ?>, tu pago no se completó. Puedes reintentar el pago usando el botón de abajo.</p>
            <div class="box">
                <p class="k">Total</p>
                <p class="v"><?php echo $total; ?></p>
            </div>
            <?php if (!empty($payment_url)) : ?>
                <a class="btn" href="<?php echo esc_url($payment_url); ?>">Reintentar Pago</a>
            <?php endif; ?>
            <p class="muted" style="margin-top: 14px;">Si necesitas ayuda, contáctanos desde el sitio.</p>
        </div>
    </div>
</body>
</html>

