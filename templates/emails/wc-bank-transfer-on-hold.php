<?php
/**
 * Custom WooCommerce Bank Transfer On Hold Email Template
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

// Shared data
$subtotal = $order->get_subtotal_to_display();
$total = $order->get_formatted_order_total();
$date = wc_format_datetime($order->get_date_created());

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Red Cultural - Pedido en Espera #<?php echo esc_html($order_number); ?></title>
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
        .logo-text {
            display: inline-block;
            border-bottom: 2px solid #000000;
            padding-bottom: 2px;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-weight: 900;
            font-size: 20px;
            letter-spacing: -0.05em;
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
        .notice-box {
            background-color: #fffbeb;
            border: 1px solid #fef3c7;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 24px;
        }
        .section-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #92400e;
            margin-bottom: 8px;
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
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .receipt-table {
            width: 100%;
            border-collapse: collapse;
        }
        .receipt-table td {
            padding: 16px 24px;
            border-bottom: 1px solid #f3f4f6;
        }
        .receipt-table tr:last-child td {
            border-bottom: none;
        }
        .receipt-label {
            color: #6b7280;
            font-size: 13px;
            font-weight: 500;
            width: 40%;
            text-align: left;
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
        .summary-footer {
            padding: 0 32px 32px 32px;
        }
        .support-footer {
            padding: 24px 32px;
            text-align: center;
        }
        .support-text {
            font-size: 11px;
            color: #9ca3af;
            line-height: 1.5;
            max-width: 280px;
            margin: 0 auto 16px auto;
        }
        .copyright {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #d1d5db;
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #efefef;">
    <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#efefef" style="background-color: #efefef;">
        <tr>
            <td align="center" style="padding: 24px 16px;">
                <div class="container">
        
        <!-- Header -->
        <div class="header">
            <div style="margin-bottom: 24px;">
                <img src="https://red-cultural.cl/wp-content/uploads/2021/01/logoRedCulturalNegro.svg" alt="Red Cultural" style="width: 140px; height: auto; display: inline-block;">
            </div>
            <div class="subheader">Pedido Recibido</div>
            <h1 class="order-title">Tu pedido <strong>#<?php echo esc_html($order_number); ?></strong> está en espera</h1>
        </div>

        <div class="content">
            
            <div class="greeting" style="font-size: 14px; font-weight: 600; margin-bottom: 12px;">Hola <?php echo esc_html($billing_first_name); ?>,</div>

            <div class="notice-box">
                <div class="section-title">Transferencia Bancaria</div>
                <p style="margin: 0; font-size: 12px; color: #92400e; line-height: 1.5;">
                    Hemos recibido tu orden, pero aún no podemos otorgarte acceso a tus cursos o lecciones. 
                    Una vez que confirmemos la recepción de tu transferencia, activaremos tu acceso manualmente y recibirás un correo de confirmación.
                </p>
            </div>

            <div style="margin-bottom: 24px;">
                <h4 style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: #9ca3af; margin-bottom: 12px;">Tu Pago</h4>
                <div style="font-size: 12px; color: #4b5563; line-height: 1.6; background-color: #f9fafb; padding: 16px; border-radius: 6px;">
                    <p style="margin: 0 0 8px 0;"><strong>Datos para la transferencia:</strong></p>
                    <p style="margin: 0;">
                        Nombre: Ediciones Alicia Limitada<br>
                        RUT: 76.360.721-6<br>
                        Banco: Banco BICE<br>
                        Tipo de Cuenta: Cuenta Corriente<br>
                        Número de Cuenta: 02746948<br>
                        Email: magdalena@redcultural.cl
                    </p>
                </div>
            </div>

            <div>
                <h4 style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: #9ca3af; margin-bottom: 12px;">Resumen</h4>
                <?php foreach ($order->get_items() as $item) : ?>
                <div class="item-row">
                    <span><?php echo esc_html($item->get_name()); ?> x<?php echo esc_html($item->get_quantity()); ?></span>
                    <span style="font-weight: 600;"><?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?></span>
                </div>
                <?php endforeach; ?>
            </div>

        </div>

        <!-- Order Summary Footer -->
        <div class="summary-footer">
            <div class="receipt-card">
                <table class="receipt-table" width="100%" cellpadding="0" cellspacing="0">
                    <tbody>
                        <!-- Orden -->
                        <tr>
                            <td class="receipt-label">ID Pedido</td>
                            <td class="receipt-value">#<?php echo esc_html($order_number); ?></td>
                        </tr>
                        <!-- Fecha -->
                        <tr>
                            <td class="receipt-label">Fecha</td>
                            <td class="receipt-value"><?php echo esc_html($date); ?></td>
                        </tr>
                        <!-- Método -->
                        <tr>
                            <td class="receipt-label">Método de Pago</td>
                            <td class="receipt-value"><?php echo esc_html($order->get_payment_method_title()); ?></td>
                        </tr>
                        <!-- Subtotal -->
                        <tr>
                            <td class="receipt-label">Subtotal</td>
                            <td class="receipt-value"><?php echo wp_kses_post($subtotal); ?></td>
                        </tr>
                        <!-- Total -->
                        <tr class="receipt-total-row">
                            <td class="receipt-label" style="font-weight: 700; color: #111827;">Total a transferir</td>
                            <td class="receipt-value receipt-total-value"><?php echo wp_kses_post($total); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Support Footer -->
        <div class="support-footer">
            <p class="support-text">
                Por favor envía el comprobante de transferencia a <strong>magdalena@redcultural.cl</strong> para agilizar la activación de tu pedido.
            </p>
            <div class="copyright">&copy; <?php echo date('Y'); ?> Red Cultural.</div>
            <div style="display:none; font-size:1px; line-height:1px; max-height:0px; max-width:0px; opacity:0; overflow:hidden;">
                Test ID: <?php echo time(); ?>
            </div>
        </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
