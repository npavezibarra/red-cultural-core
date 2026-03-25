<?php
/**
 * Custom WooCommerce New Order Email Template
 * 
 * @var WC_Order $order
 */

if (!defined('ABSPATH')) {
    exit;
}

$order_id = $order->get_id();
$order_number = $order->get_order_number();
$view = Red_Cultural_WC_Emails::identify_order_type($order);
$access_links = Red_Cultural_WC_Emails::get_access_links($order);

// Shared data
$subtotal = $order->get_subtotal_to_display();
$shipping = $order->get_shipping_to_display();
$total = $order->get_formatted_order_total();
$date = wc_format_datetime($order->get_date_created());

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Red Cultural - Tu Pedido #<?php echo esc_html($order_number); ?></title>
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
        .section-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #9ca3af;
            margin-bottom: 8px;
        }
        .item-row {
            padding: 4px 0;
        }
        .item-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .access-card {
            border: 1px solid #e5e7eb;
            padding: 16px;
            border-radius: 6px;
            margin-top: 8px;
        }
        .badge {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            background-color: #eff6ff;
            color: #2563eb;
            padding: 2px 6px;
            border-radius: 6px;
            margin-bottom: 4px;
            display: inline-block;
        }
        .btn-main {
            display: block;
            width: 100%;
            background-color: #000000;
            color: #ffffff !important;
            padding: 12px 0;
            text-align: center;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            border-radius: 6px;
            margin-top: 16px;
            letter-spacing: 0.05em;
        }
        .btn-lesson {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            color: #000000 !important;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .lesson-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            background-color: #f9fafb;
            border-radius: 6px;
            margin-bottom: 8px;
        }
        .lesson-number {
            width: 24px;
            height: 24px;
            background-color: #ffffff;
            border: 1px solid #f3f4f6;
            border-radius: 6px;
            color: #6b7280;
            font-size: 10px;
            font-weight: 700;
        }
        .address-box {
            background-color: #f9fafb;
            padding: 16px;
            border-radius: 6px;
            margin-top: 16px;
        }
        .summary-footer {
            padding: 24px 32px;
            background-color: #f9fafb;
            border-top: 1px solid #f3f4f6;
        }
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 16px;
        }
        .summary-col {
            display: table-cell;
            width: 50%;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 8px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            font-size: 14px;
            color: #111827;
            padding-top: 4px;
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
        svg {
            vertical-align: middle;
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
            <div class="subheader">Gracias por tu compra</div>
            <h1 class="order-title">Estamos procesando tu pedido <strong>#<?php echo esc_html($order_number); ?></strong></h1>
        </div>

        <div class="content">

            <?php if ($view === 'physical') : ?>
                <!-- PHYSICAL VIEW -->
                <div class="section-title">Tu Pedido</div>
                <?php foreach ($order->get_items() as $item) : ?>
                <div class="item-row">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td width="40" valign="middle">
                                <div style="width: 40px; height: 40px; background-color: #f9fafb; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                    <img width="18" src="https://api.iconify.design/lucide:package.svg?color=%239ca3af" alt="box">
                                </div>
                            </td>
                            <td style="padding-left: 12px;" valign="middle">
                                <p style="margin: 0; font-size: 14px; font-weight: 500;"><?php echo esc_html($item->get_name()); ?></p>
                                <p style="margin: 0; font-size: 11px; color: #6b7280;">Cantidad: <?php echo esc_html($item->get_quantity()); ?></p>
                            </td>
                            <td align="right" valign="middle">
                                <p style="margin: 0; font-size: 14px; font-weight: 500;"><?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                <?php endforeach; ?>

                <div class="address-box">
                    <div class="section-title" style="display: flex; align-items: center;">
                        <img width="12" src="https://api.iconify.design/lucide:map-pin.svg?color=%239ca3af" alt="pin" style="margin-right: 6px;">
                        Dirección de Envío
                    </div>
                    <p style="margin: 0; font-size: 12px; color: #4b5563; line-height: 1.4;">
                        <?php echo wp_kses_post($order->get_formatted_shipping_address()); ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if ($view === 'course') : ?>
                <!-- COURSE VIEW -->
                <div style="text-align: center; margin-bottom: 24px; padding-top: 8px;">
                    <h2 style="font-size: 20px; font-weight: 300; margin: 0 0 4px 0; color: #111827;">Acceso Confirmado</h2>
                    <p style="font-size: 12px; color: #6b7280; margin: 0; max-width: 280px; margin-left: auto; margin-right: auto;">Ya tienes acceso total al curso. Tu aprendizaje comienza ahora mismo.</p>
                </div>

                <div style="background-color: #ffffff; border: 1px solid #e5e7eb; padding: 24px; border-radius: 6px; margin-bottom: 16px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                    <?php 
                    $course_title_found = 'Curso de Red Cultural';
                    $course_image_url = '';
                    
                    // If a resource_id is passed (from Email Tester), use it to override everything
                    if (!empty($resource_id)) {
                        $course_title_found = get_the_title($resource_id);
                        $course_image_url = get_the_post_thumbnail_url($resource_id, 'medium');
                    } else {
                        foreach ($order->get_items() as $item) {
                            if ($item->get_meta('_rcil_is_full_course') === '1' || $item->get_meta('_rcil_is_full_course') === 1) {
                                $course_title_found = $item->get_meta('_rcil_target_course');
                                $course_id = $item->get_meta('_rcil_course_id');
                                if ($course_id) {
                                    $course_image_url = get_the_post_thumbnail_url($course_id, 'medium');
                                }
                                break;
                            }
                        }
                    }
                    
                    if ($course_image_url) : ?>
                        <div style="margin-bottom: 16px; border-radius: 6px; overflow: hidden; height: 160px; background-color: #f3f4f6;">
                            <img src="<?php echo esc_url($course_image_url); ?>" alt="Course Image" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    <?php endif; ?>

                    <div style="margin-bottom: 16px;">
                        <span style="font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; background-color: #eff6ff; color: #2563eb; padding: 2px 8px; border-radius: 6px; display: inline-block; margin-bottom: 8px;">Curso Online</span>
                        <h3 style="font-size: 18px; font-weight: 500; margin: 0; color: #111827; letter-spacing: -0.01em;"><?php echo esc_html($course_title_found); ?></h3>
                    </div>
                    <?php 
                    $links = Red_Cultural_WC_Emails::get_access_links($order);
                    if (!empty($links)) :
                        foreach ($links as $link) : ?>
                            <a href="<?php echo esc_url(wp_login_url($link['url'])); ?>" class="btn-main" style="display: block; width: 100%; background-color: #000000; color: #ffffff !important; padding: 12px 0; text-align: center; text-decoration: none; font-size: 12px; font-weight: 500; border-radius: 6px; letter-spacing: 0.05em;">IR AL CURSO</a>
                        <?php endforeach; 
                    else: ?>
                        <a href="<?php echo esc_url(wp_login_url(home_url('/mis-cursos'))); ?>" class="btn-main" style="display: block; width: 100%; background-color: #000000; color: #ffffff !important; padding: 12px 0; text-align: center; text-decoration: none; font-size: 12px; font-weight: 500; border-radius: 6px; letter-spacing: 0.05em;">IR AL CURSO</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($view === 'lessons') : ?>
                <!-- LESSONS VIEW -->
                <div style="margin-bottom: 16px;">
                    <div class="section-title">Curso</div>
                    <p style="font-size: 16px; font-weight: 500; margin: 2px 0 0 0;">
                    <?php 
                        if (!empty($resource_id)) {
                            $course_id = function_exists('learndash_get_course_id') ? learndash_get_course_id($resource_id) : 0;
                            echo esc_html($course_id ? get_the_title($course_id) : 'Curso de Prueba');
                        } else {
                            foreach ($order->get_items() as $item) {
                                if ($item->get_meta('_is_rcil_purchase')) {
                                    echo esc_html($item->get_meta('_rcil_target_course'));
                                    break;
                                }
                            }
                        }
                    ?>
                    </p>
                </div>

                <div>
                    <div class="section-title" style="margin-bottom: 12px;">Lecciones Adquiridas</div>
                    
                    <?php 
                    if (!empty($resource_id)) {
                        // Mock a single lesson row
                        ?>
                        <div class="lesson-row" style="padding: 12px; background-color: #f9fafb; border-radius: 6px; margin-bottom: 8px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td width="30" valign="middle">
                                        <table class="lesson-number" cellpadding="0" cellspacing="0" border="0" width="24" height="24">
                                            <tr>
                                                <td align="center" valign="middle" style="font-size: 10px; font-weight: 700; color: #6b7280;">1</td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td style="padding: 0 12px;" valign="middle">
                                        <p style="margin: 0; font-size: 12px; font-weight: 500; color: #1f2937; line-height: 1.4;"><?php echo esc_html(get_the_title($resource_id)); ?></p>
                                    </td>
                                    <td width="100" align="right" valign="middle">
                                        <a href="<?php echo esc_url(wp_login_url(get_permalink($resource_id))); ?>" class="btn-lesson" style="display: inline-block; background-color: #ffffff; border: 1px solid #e5e7eb; padding: 8px 10px; border-radius: 6px; text-decoration: none; color: #000000 !important; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; white-space: nowrap;">
                                            IR A LA LECCIÓN <img width="8" src="https://api.iconify.design/lucide:chevron-right.svg?color=black" style="vertical-align: middle; margin-left: 2px;">
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <?php
                    } else {
                        $lesson_count = 1;
                        foreach ($order->get_items() as $item) : 
                            if ($item->get_meta('_is_rcil_purchase')) :
                                $titles = maybe_unserialize($item->get_meta('_rcil_lesson_titles'));
                                $ids = maybe_unserialize($item->get_meta('_rcil_lesson_ids'));
                                
                                if (is_array($titles)) :
                                    foreach ($titles as $index => $title) :
                        ?>
                        <div class="lesson-row" style="padding: 12px; background-color: #f9fafb; border-radius: 6px; margin-bottom: 8px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td width="30" valign="middle">
                                        <table class="lesson-number" cellpadding="0" cellspacing="0" border="0" width="24" height="24">
                                            <tr>
                                                <td align="center" valign="middle" style="font-size: 10px; font-weight: 700; color: #6b7280;">
                                                    <?php echo $lesson_count++; ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td style="padding: 0 12px;" valign="middle">
                                        <p style="margin: 0; font-size: 12px; font-weight: 500; color: #1f2937; line-height: 1.4;"><?php echo esc_html($title); ?></p>
                                    </td>
                                    <td width="100" align="right" valign="middle">
                                        <a href="<?php echo esc_url(wp_login_url(get_permalink($ids[$index]))); ?>" class="btn-lesson" style="display: inline-block; background-color: #ffffff; border: 1px solid #e5e7eb; padding: 8px 10px; border-radius: 6px; text-decoration: none; color: #000000 !important; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; white-space: nowrap;">
                                            IR A LA LECCIÓN <img width="8" src="https://api.iconify.design/lucide:chevron-right.svg?color=black" style="vertical-align: middle; margin-left: 2px;">
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <?php 
                                    endforeach;
                                endif;
                            endif;
                        endforeach; 
                    }
                    ?>
                </div>
            <?php endif; ?>

        </div>

        <!-- Order Summary Footer -->
        <div class="summary-footer">
            <div class="summary-grid">
                <div class="summary-col">
                    <h4 class="section-title" style="margin-bottom: 4px;">Pedido</h4>
                    <p style="margin: 0; font-size: 12px; font-weight: 500;">#<?php echo esc_html($order_number); ?></p>
                </div>
                <div class="summary-col">
                    <h4 class="section-title" style="margin-bottom: 4px;">Fecha</h4>
                    <p style="margin: 0; font-size: 12px; font-weight: 500;"><?php echo esc_html($date); ?></p>
                </div>
            </div>

            <div style="border-top: 1px solid #e5e7eb; padding-top: 16px; margin-top: 8px;">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span><?php echo wp_kses_post($subtotal); ?></span>
                </div>
                <?php if ($view === 'physical') : ?>
                <div class="summary-row">
                    <span>Envío</span>
                    <span><?php echo wp_kses_post($shipping); ?></span>
                </div>
                <?php endif; ?>
                <?php foreach ($order->get_tax_totals() as $code => $tax) : ?>
                <div class="summary-row">
                    <span><?php echo esc_html($tax->label); ?></span>
                    <span><?php echo wp_kses_post($tax->formatted_amount); ?></span>
                </div>
                <?php endforeach; ?>
                <div class="total-row">
                    <span>Total</span>
                    <span><?php echo wp_kses_post($total); ?></span>
                </div>
            </div>
        </div>

        <!-- Support Footer -->
        <div class="support-footer">
            <p class="support-text">
                ¿Tienes alguna duda? Responde a este email o visita nuestro centro de ayuda.
            </p>
            <div style="margin-bottom: 16px;">
                <img width="16" src="https://api.iconify.design/lucide:credit-card.svg?color=%23d1d5db" style="margin: 0 8px;">
                <img width="16" src="https://api.iconify.design/lucide:book-open.svg?color=%23d1d5db" style="margin: 0 8px;">
                <img width="16" src="https://api.iconify.design/lucide:external-link.svg?color=%23d1d5db" style="margin: 0 8px;">
            </div>
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
