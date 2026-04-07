<?php
/**
 * Custom WooCommerce Admin Cancelled Order Email Template
 *
 * @var WC_Order $order
 */
if (!defined('ABSPATH')) {
    exit;
}

$order_number = $order->get_order_number();
$date = wc_format_datetime($order->get_date_created());
$total = $order->get_formatted_order_total();
$admin_order_url = admin_url('post.php?post=' . $order->get_id() . '&action=edit');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Red Cultural - Pedido Cancelado #<?php echo esc_html($order_number); ?></title>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #efefef; margin: 0; padding: 24px 16px; color: #111827; }
        .container { max-width: 500px; margin: 0 auto; background-color: #ffffff; border-radius: 6px; overflow: hidden; border: 1px solid #f3f4f6; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .header { padding: 28px 32px 16px; text-align: center; }
        .subheader { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.2em; color: #9ca3af; margin-bottom: 6px; }
        .title { font-size: 22px; font-weight: 500; letter-spacing: -0.025em; margin: 0; }
        .content { padding: 8px 32px 28px; }
        .pill { display:inline-block; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.12em; background:#fee2e2; color:#991b1b; padding: 6px 10px; border-radius: 999px; }
        .receipt { width: 100%; border-collapse: collapse; margin-top: 16px; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
        .receipt td { padding: 12px 16px; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
        .receipt tr:last-child td { border-bottom: none; }
        .label { color:#6b7280; font-weight: 600; }
        .value { text-align:right; font-weight: 700; }
        .btn { display:block; width:100%; background:#000; color:#fff !important; text-decoration:none; text-align:center; padding: 12px 0; border-radius: 6px; font-size: 12px; font-weight: 600; letter-spacing: 0.05em; margin-top: 16px; }
        .muted { color:#6b7280; font-size: 13px; margin: 10px 0 0; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="margin-bottom: 18px;">
                <img src="https://red-cultural.cl/wp-content/uploads/2021/01/logoRedCulturalNegro.svg" alt="Red Cultural" style="width: 140px; height: auto; display: inline-block;">
            </div>
            <span class="pill">Pedido Cancelado</span>
            <p class="subheader" style="margin-top: 14px;">Notificación Administrador</p>
            <h1 class="title">Pedido #<?php echo esc_html($order_number); ?></h1>
        </div>
        <div class="content">
            <p class="muted">Este pedido fue marcado como <strong>cancelado</strong>.</p>
            <table class="receipt">
                <tr><td class="label">Fecha</td><td class="value"><?php echo esc_html($date); ?></td></tr>
                <tr><td class="label">Total</td><td class="value"><?php echo $total; ?></td></tr>
            </table>
            <a class="btn" href="<?php echo esc_url($admin_order_url); ?>">Ver Pedido en Admin</a>
        </div>
    </div>
</body>
</html>

