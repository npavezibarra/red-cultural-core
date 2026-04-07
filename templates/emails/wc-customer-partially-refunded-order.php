<?php
/**
 * Custom WooCommerce Customer Partially Refunded Email Template
 *
 * @var WC_Order $order
 * @var WC_Order_Refund|null $refund
 */
if (!defined('ABSPATH')) {
    exit;
}

$order_number = $order->get_order_number();
$billing_first_name = $order->get_billing_first_name();
$refunded_total = (float) $order->get_total_refunded();
$order_total = (float) $order->get_total();
$remaining_total = max(0, $order_total - $refunded_total);
$date = wc_format_datetime($order->get_date_created());

$refunded_amount_label = wc_price($refunded_total);
$remaining_amount_label = wc_price($remaining_total);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Red Cultural - Reembolso Parcial #<?php echo esc_html($order_number); ?></title>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #efefef; margin: 0; padding: 24px 16px; color: #111827; }
        .container { max-width: 500px; margin: 0 auto; background-color: #ffffff; border-radius: 6px; overflow: hidden; border: 1px solid #f3f4f6; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .header { padding: 28px 32px 16px; text-align: center; }
        .subheader { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.2em; color: #9ca3af; margin-bottom: 6px; }
        .title { font-size: 22px; font-weight: 500; letter-spacing: -0.025em; margin: 0; }
        .content { padding: 8px 32px 28px; }
        .pill { display:inline-block; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.12em; background:#e0f2fe; color:#075985; padding: 6px 10px; border-radius: 999px; }
        .receipt { width: 100%; border-collapse: collapse; margin-top: 16px; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
        .receipt td { padding: 12px 16px; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
        .receipt tr:last-child td { border-bottom: none; }
        .label { color:#6b7280; font-weight: 600; }
        .value { text-align:right; font-weight: 800; }
        .muted { color:#6b7280; font-size: 13px; margin: 10px 0 0; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="margin-bottom: 18px;">
                <img src="https://red-cultural.cl/wp-content/uploads/2021/01/logoRedCulturalNegro.svg" alt="Red Cultural" style="width: 140px; height: auto; display: inline-block;">
            </div>
            <span class="pill">Reembolso Parcial</span>
            <p class="subheader" style="margin-top: 14px;">Actualización de tu pedido</p>
            <h1 class="title">Pedido #<?php echo esc_html($order_number); ?></h1>
        </div>
        <div class="content">
            <p class="muted">Hola <?php echo esc_html($billing_first_name ?: ''); ?>, registramos un <strong>reembolso parcial</strong> para tu pedido.</p>
            <table class="receipt">
                <tr><td class="label">Fecha</td><td class="value"><?php echo esc_html($date); ?></td></tr>
                <tr><td class="label">Reembolsado</td><td class="value"><?php echo $refunded_amount_label; ?></td></tr>
                <tr><td class="label">Monto restante</td><td class="value"><?php echo $remaining_amount_label; ?></td></tr>
            </table>
            <p class="muted" style="margin-top: 14px;">Si tienes dudas, responde a este correo o contáctanos por el sitio.</p>
        </div>
    </div>
</body>
</html>

