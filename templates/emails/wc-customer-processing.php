<?php
/**
 * Custom WooCommerce Customer Processing Order Email Template
 * 
 * @var WC_Order $order
 */

if (!defined('ABSPATH')) {
    exit;
}

$order_id = $order->get_id();
$order_number = $order->get_order_number();
$billing_first_name = $order->get_billing_first_name();

// Support for Email Tester user override
if (isset($test_user_id) && $test_user_id > 0) {
    $user = get_userdata($test_user_id);
    if ($user) {
        $billing_first_name = $user->first_name ?: $user->display_name;
    }
}

$subtotal = $order->get_subtotal_to_display();
$total = $order->get_formatted_order_total();
$date = wc_format_datetime($order->get_date_created());

// Get Access Links
$access_links = [];
if (class_exists('Red_Cultural_WC_Emails')) {
    $access_links = Red_Cultural_WC_Emails::get_access_links($order);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Red Cultural - Pedido Confirmado #<?php echo esc_html($order_number); ?></title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #efefef;
            margin: 0;
            padding: 24px 16px;
            color: #111827;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #f3f4f6;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .header {
            padding: 32px 32px 16px 32px;
            text-align: center;
        }
        .subheader {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: #9ca3af;
            margin-bottom: 4px;
        }
        .order-title {
            font-size: 24px;
            font-weight: 300;
            letter-spacing: -0.025em;
            margin: 0;
            line-height: 1.2;
        }
        .order-title strong {
            font-weight: 500;
        }
        .content {
            padding: 16px 32px;
        }
        .greeting {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 12px;
        }
        .message {
            font-size: 14px;
            line-height: 1.6;
            color: #4b5563;
            margin-bottom: 24px;
        }
        .btn-container {
            text-align: center;
            margin: 32px 0;
        }
        .btn {
            background-color: #000000;
            color: #ffffff !important;
            padding: 12px 32px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            display: inline-block;
        }
        .item-row {
            padding: 4px 0;
            display: flex;
            justify-content: space-between;
            font-size: 13px;
        }
        .receipt-card {
            max-width: 500px;
            margin: 24px auto;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
        }
        .receipt-table {
            width: 100%;
            border-collapse: collapse;
        }
        .receipt-table td {
            padding: 16px 24px;
            border-bottom: 1px solid #f3f4f6;
        }
        .receipt-label {
            color: #6b7280;
            font-size: 13px;
            font-weight: 500;
            width: 40%;
        }
        .receipt-value {
            color: #111827;
            font-size: 13px;
            font-weight: 600;
            text-align: right;
        }
        .receipt-total-row {
            background-color: #f9fafb;
        }
        .receipt-total-value {
            color: #111827;
            font-size: 18px;
            font-weight: 800;
        }
        .footer {
            padding: 24px 32px;
            text-align: center;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #d1d5db;
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #efefef;">
    <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#efefef">
        <tr>
            <td align="center" style="padding: 24px 16px;">
                <div class="container">
                    <!-- Header -->
                    <div class="header">
                        <div style="margin-bottom: 24px;">
                            <img src="https://red-cultural.cl/wp-content/uploads/2021/01/logoRedCulturalNegro.svg" alt="Red Cultural" style="width: 140px; height: auto; display: inline-block;">
                        </div>
                        <div class="subheader">Pedido Confirmado</div>
                        <h1 class="order-title">Tu pedido <strong>#<?php echo esc_html($order_number); ?></strong> está listo</h1>
                    </div>

                    <div class="content">
                        <div class="greeting">Hola <?php echo esc_html($billing_first_name); ?>,</div>
                        <div class="message">
                            ¡Excelentes noticias! Hemos confirmado tu pago y ya tienes acceso a tus contenidos en Red Cultural.
                        </div>

                        <?php if (!empty($access_links)) : ?>
                            <div class="btn-container">
                                <?php foreach ($access_links as $link) : ?>
                                    <a href="<?php echo esc_url($link['url']); ?>" class="btn">
                                        <?php echo esc_html($link['label']); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div style="margin-bottom: 24px;">
                            <h4 style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: #9ca3af; margin-bottom: 12px;">Resumen de compra</h4>
                            <?php foreach ($order->get_items() as $item) : ?>
                                <div class="item-row">
                                    <span style="max-width: 70%;"><?php echo esc_html($item->get_name()); ?> x<?php echo esc_html($item->get_quantity()); ?></span>
                                    <span style="font-weight: 600;"><?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Order Summary Footer -->
                    <div style="padding: 0 32px 32px 32px;">
                        <div class="receipt-card">
                            <table class="receipt-table" width="100%">
                                <tbody>
                                    <tr>
                                        <td class="receipt-label">ID Pedido</td>
                                        <td class="receipt-value">#<?php echo esc_html($order_number); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="receipt-label">Fecha</td>
                                        <td class="receipt-value"><?php echo esc_html($date); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="receipt-label">Método</td>
                                        <td class="receipt-value"><?php echo esc_html($order->get_payment_method_title()); ?></td>
                                    </tr>
                                    <tr class="receipt-total-row">
                                        <td class="receipt-label" style="font-weight: 700; color: #111827;">Total Pagado</td>
                                        <td class="receipt-value receipt-total-value"><?php echo wp_kses_post($total); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="footer">
                        &copy; <?php echo date('Y'); ?> RED CULTURAL. DISFRUTA TU CONTENIDO.
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
