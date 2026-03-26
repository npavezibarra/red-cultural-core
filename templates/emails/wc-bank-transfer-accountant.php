<?php
/**
 * Accountant Notification for Bank Transfer Order
 * 
 * @var WC_Order $order
 * @var string   $confirm_url
 */

if (!defined('ABSPATH')) {
    exit;
}

$order_number = $order->get_order_number();
$billing_first_name = $order->get_billing_first_name();
$billing_last_name = $order->get_billing_last_name();
$total = $order->get_formatted_order_total();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación de Transferencia - #<?php echo esc_html($order_number); ?></title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 40px 20px;
            color: #111827;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            padding: 32px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 32px;
        }
        .message {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 32px;
            color: #374151;
        }
        .btn-container {
            text-align: center;
        }
        .btn {
            display: inline-block;
            background-color: #000000;
            color: #ffffff !important;
            padding: 16px 32px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .footer {
            margin-top: 32px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="https://red-cultural.cl/wp-content/uploads/2021/01/logoRedCulturalNegro.svg" alt="Red Cultural" style="width: 140px; height: auto;">
        </div>
        
        <div class="message">
            <strong><?php echo esc_html($billing_first_name . ' ' . $billing_last_name); ?></strong> hizo un pedido por <strong><?php echo wp_kses_post($total); ?></strong>. 
            Verifica en tu banco si tienes tal transferencia para confirmar con el botón de abajo.
        </div>

        <div class="btn-container">
            <a href="<?php echo esc_url($confirm_url); ?>" class="btn">CONFIRMAR TRANSFERENCIA</a>
        </div>

        <div class="footer">
            Orden #<?php echo esc_html($order_number); ?> | <?php echo date('d/m/Y H:i'); ?>
        </div>
    </div>
</body>
</html>
