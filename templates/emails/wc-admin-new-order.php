<?php
/**
 * Custom WooCommerce Admin New Order Email Template
 * 
 * @var WC_Order $order
 */

if (!defined('ABSPATH')) {
    exit;
}

$order = isset($order) && $order instanceof WC_Order ? $order : null;
if (!$order && isset($order_id)) {
    $maybe_order = wc_get_order((int) $order_id);
    if ($maybe_order instanceof WC_Order) {
        $order = $maybe_order;
    }
}

if (!$order) {
    echo esc_html__('No se pudo cargar el pedido para este correo.', 'red-cultural-core');
    return;
}

$order_id = $order->get_id();
$order_number = $order->get_order_number();
$billing_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
$billing_email = (string) $order->get_billing_email();
$billing_phone = (string) $order->get_billing_phone();
$billing_address = (string) $order->get_formatted_billing_address();
$shipping_address = (string) $order->get_formatted_shipping_address();
$has_shipping_address = $shipping_address !== '';
$subtotal = $order->get_subtotal_to_display();
$total = $order->get_formatted_order_total();
$date = wc_format_datetime($order->get_date_created());
$line_items = $order->get_items('line_item');

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Red Cultural - Nueva Orden #<?php echo esc_html($order_number); ?></title>
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
        .notice-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 24px;
        }
        .section-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #9ca3af;
            margin: 0 0 12px 0;
        }
        .info-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 24px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 18px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #6b7280; font-weight: 500; }
        .info-value { color: #111827; font-weight: 600; text-align: right; }
        .info-value.multiline { text-align: left; white-space: pre-line; font-weight: 600; }
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
            color: #000000;
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
                    <div class="header">
                        <div style="margin-bottom: 24px;">
                            <img src="https://red-cultural.cl/wp-content/uploads/2021/01/logoRedCulturalNegro.svg" alt="Red Cultural" style="width: 140px; height: auto;">
                        </div>
                        <div class="subheader">Notificación Administrador</div>
                        <h1 class="order-title">Nueva venta <strong>#<?php echo esc_html($order_number); ?></strong></h1>
                    </div>

                    <div class="content">
                        <div class="notice-box">
                            <p style="margin: 0; font-size: 13px; color: #334155; line-height: 1.5;">
                                Se ha realizado un nuevo pedido de <strong><?php echo esc_html($billing_name); ?></strong>.
                            </p>
                        </div>

                        <h4 class="section-title"><?php echo esc_html__('Cliente', 'red-cultural-core'); ?></h4>
                        <div class="info-card">
                            <div class="info-row">
                                <div class="info-label"><?php echo esc_html__('Nombre', 'red-cultural-core'); ?></div>
                                <div class="info-value"><?php echo esc_html(trim($billing_name)); ?></div>
                            </div>
                            <?php if ($billing_email !== '') : ?>
                                <div class="info-row">
                                    <div class="info-label"><?php echo esc_html__('Email', 'red-cultural-core'); ?></div>
                                    <div class="info-value"><?php echo esc_html($billing_email); ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if ($billing_phone !== '') : ?>
                                <div class="info-row">
                                    <div class="info-label"><?php echo esc_html__('Teléfono', 'red-cultural-core'); ?></div>
                                    <div class="info-value"><?php echo esc_html($billing_phone); ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if ($billing_address !== '') : ?>
                                <div class="info-row">
                                    <div class="info-label"><?php echo esc_html__('Dirección (facturación)', 'red-cultural-core'); ?></div>
                                    <div class="info-value multiline"><?php echo esc_html($billing_address); ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if ($has_shipping_address) : ?>
                                <div class="info-row">
                                    <div class="info-label"><?php echo esc_html__('Dirección (envío)', 'red-cultural-core'); ?></div>
                                    <div class="info-value multiline"><?php echo esc_html($shipping_address); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <h4 class="section-title"><?php echo esc_html__('Productos', 'red-cultural-core'); ?></h4>
                        <?php if (!empty($line_items)) : ?>
                            <?php foreach ($line_items as $item) : ?>
                                <div class="item-row">
                                    <span style="max-width: 70%;"><?php echo esc_html($item->get_name()); ?> x<?php echo esc_html($item->get_quantity()); ?></span>
                                    <span style="font-weight: 600;"><?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="item-row">
                                <span style="color:#6b7280;"><?php echo esc_html__('No se encontraron productos en el pedido.', 'red-cultural-core'); ?></span>
                                <span></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="padding: 0 32px 32px 32px;">
                        <div class="receipt-card">
                            <table class="receipt-table">
                                <tr>
                                    <td class="receipt-label">ID Venta</td>
                                    <td class="receipt-value">#<?php echo esc_html($order_number); ?></td>
                                </tr>
                                <tr>
                                    <td class="receipt-label">Fecha</td>
                                    <td class="receipt-value"><?php echo esc_html($date); ?></td>
                                </tr>
                                <tr>
                                    <td class="receipt-label">Pago</td>
                                    <td class="receipt-value"><?php echo esc_html($order->get_payment_method_title()); ?></td>
                                </tr>
                                <tr class="receipt-total-row">
                                    <td class="receipt-label" style="font-weight: 700; color: #111827;">Total Recibido</td>
                                    <td class="receipt-value receipt-total-value"><?php echo wp_kses_post($total); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="footer">
                        &copy; <?php echo date('Y'); ?> RED CULTURAL. TODA LA GESTIÓN DE VENTAS SE REALIZA DESDE PANEL DE CONTROL.
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
