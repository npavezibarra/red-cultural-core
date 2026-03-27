<?php
/**
 * Thankyou Page template
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$order_id = get_query_var('order-received');
$order    = wc_get_order($order_id);

if (!$order) {
    return;
}

$status = $order->get_status();
$is_pending = in_array($status, ['on-hold', 'pending'], true);
$method_id = $order->get_payment_method();
$method_title = $order->get_payment_method_title();

// Determine if we show the success or pending state
// Access is granted on processing or completed
$is_success = in_array($status, ['processing', 'completed'], true);

$order_number = $order->get_order_number();
$order_date = wc_format_datetime($order->get_date_created(), 'd/m/Y');
$order_total = $order->get_formatted_order_total();

// Get Bank Details if pending and method is BACS
$bank_details = [];
if ($is_pending && $method_id === 'bacs') {
    $gateways = WC()->payment_gateways->get_available_payment_gateways();
    if (isset($gateways['bacs'])) {
        $bacs = $gateways['bacs'];
        // Use the defined accounts in BACS settings
        $accounts = $bacs->account_details;
        if (!empty($accounts) && is_array($accounts)) {
            $bank_details = $accounts[0]; // Take the first account
        }
    }
}

$items = $order->get_items();
$courses_data = [];
foreach ($items as $item) {
    if (!$item instanceof WC_Order_Item_Product) continue;
    
    $product = $item->get_product();
    if (!$product) continue;

    $product_id = (int)$product->get_id();
    $course_id = 0;
    $is_course = false;

    // Detect if its a course (LearnDash relation)
    // 1. Check RCIL (Individual Lessons)
    $rcil_id = $item->get_meta('_rcil_course_id');
    if ($rcil_id) {
        $course_id = (int) $rcil_id;
        $is_course = true;
    } else {
        // 2. Standard LearnDash Metadata
        $meta_id = get_post_meta($product_id, '_course_id', true);
        if (!$meta_id) $meta_id = get_post_meta($product_id, '_related_course', true);
        if ($meta_id) {
            $course_id = (int) $meta_id;
            $is_course = true;
        }
    }

    // 3. Fallback: Check category "Cursos"
    if (!$is_course && function_exists('has_term')) {
        if (has_term(['Cursos', 'Curso', 'cursos', 'curso'], 'product_cat', $product_id)) {
            $is_course = true;
        }
    }

    // 4. If identified as course but no ID linked, try slug/title match
    if ($is_course && $course_id === 0) {
        $slug = $product->get_slug();
        // Try exact slug first
        $course_post = get_page_by_path($slug, OBJECT, 'sfwd-courses');
        if ($course_post) {
            $course_id = $course_post->ID;
        } else {
            // Try searching by exact name/title
            $course_name = $item->get_name();
            $find_course = get_posts([
                'post_type' => 'sfwd-courses',
                'title'     => $course_name,
                'numberposts' => 1
            ]);
            if (!empty($find_course)) {
                $course_id = $find_course[0]->ID;
            }
        }
    }

    $lesson_ids = $item->get_meta('_rcil_lesson_ids');
    if (!is_array($lesson_ids)) {
        $lesson_ids = maybe_unserialize($lesson_ids);
    }
    $is_rcil = ($item->get_meta('_is_rcil_purchase') === '1');

    $course_url = '#';
    if ($is_course && $course_id > 0) {
        $course_url = get_permalink($course_id);
        
        // If it's an individual lesson purchase, link to the lesson directly.
        if ($is_rcil && !empty($lesson_ids) && is_array($lesson_ids)) {
            $first_lesson_id = reset($lesson_ids);
            if ($first_lesson_id) {
                $course_url = get_permalink($first_lesson_id);
            }
        }
    }


    $mother_product_id = 0;
    if (function_exists('rcil_get_course_woo_product_id')) {
        $mother_product_id = rcil_get_course_woo_product_id($course_id);
    }

    $image_id = $mother_product_id ? get_post_thumbnail_id($mother_product_id) : 0;
    if (!$image_id) {
        $image_id = get_post_thumbnail_id($course_id);
    }
    if (!$image_id) {
        $image_id = get_post_thumbnail_id($product_id);
    }

    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';
    if (!$image_url) {
        $image_url = function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src('thumbnail') : '';
    }

    $lesson_count = (int) $item->get_meta('_rcil_lesson_count');

    $lesson_date = '';
    if ($is_rcil && !empty($lesson_ids) && is_array($lesson_ids)) {
        $first_lesson_id = reset($lesson_ids);
        $access_from = 0;
        if (function_exists('ld_lesson_access_from')) {
            $access_from = ld_lesson_access_from($first_lesson_id, $order->get_user_id(), $course_id);
        }
        if ($access_from > 0) {
            $lesson_date = date_i18n(get_option('date_format') . ' H:i \h\r\s', $access_from);
        }
    }

    $courses_data[] = [
        'title' => $item->get_name(),
        'url' => $course_url,
        'price' => wc_price((float)$item->get_total()),
        'id' => $course_id,
        'is_course' => $is_course,
        'is_rcil' => $is_rcil,
        'image' => $image_url,
        'lesson_count' => $lesson_count,
        'lesson_date' => $lesson_date
    ];
}

// Ensure unique courses
$seen = [];
$courses_data = array_filter($courses_data, function($c) use (&$seen) {
    if (!$c['id']) return true;
    if (in_array($c['id'], $seen)) return false;
    $seen[] = $c['id'];
    return true;
});

$rcp_theme_header_html = '';
$rcp_theme_footer_html = '';
if (function_exists('do_blocks')) {
	$rcp_theme_header_html = (string) do_blocks('<!-- wp:template-part {"slug":"header","area":"header"} /-->');
	$rcp_theme_footer_html = (string) do_blocks('<!-- wp:template-part {"slug":"footer","area":"footer"} /-->');
}

?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_success ? '¡Gracias por tu compra!' : 'Pedido Recibido'; ?> - Red Cultural</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <script>
		// Keep the global theme styles intact (avoid Tailwind preflight).
		window.tailwind = window.tailwind || {};
		window.tailwind.config = { corePlugins: { preflight: false } };
	</script>
    <?php wp_head(); ?>
    <style>
        :root {
            --font-display: 'Instrument Sans', sans-serif;
            --font-ui: 'Inter', sans-serif;
        }
        body {
            font-family: var(--font-ui);
            background-color: #ffffff;
            color: #000000;
        }
        h1, h2, h3, .text-display {
            font-family: var(--font-ui);
        }
        .fade-in {
            animation: fadeIn 0.4s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .btn-black {
            background-color: #000000;
            color: #ffffff;
            border-radius: 6px;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-black:hover {
            background-color: #262626;
            transform: translateY(-1px);
            color: #ffffff;
        }
        .hidden-content {
            display: none;
        }
        .order-card {
            box-shadow: 0 24px 48px rgba(0, 0, 0, 0.04);
        }

        /* Clean list styles */
        ul.bank-info { list-style: none; padding: 0; margin: 0; }
        
        #red-cultural-ty-main {
            padding-top: 30px !important;
            padding-bottom: 30px !important;

            background-image: 
                linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)),
                url('https://red-cultural.cl/wp-content/uploads/2026/03/red-cultural-main-cuadro.jpeg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        #red-cultural-ty-header {
            padding-top: 28px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e1e1e1;
        }


        #red-cultural-ty-summary {
            padding-top: 10px;
            padding-bottom: 10px;
            text-align: center;
            border-bottom: #e1e1e1 solid 1px;
        }

        #red-cultural-ty-summary-title {
            margin-bottom: 20px !important;
        }

        #red-cultural-ty-courses {
            padding-top: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e1e1e1;
        }

        #red-cultural-ty-courses-title {
            text-align: center;
            margin-bottom: 0px;
        }

        #red-cultural-ty-title {
            font-size: 24px !important;
            letter-spacing: 2px !important;
        }
        
        @media print {
            .no-print { display: none !important; }
            .min-h-screen { min-height: auto !important; }
        }
    </style>
</head>
<body class="antialiased selection:bg-black selection:text-white">

    <?php if ($rcp_theme_header_html !== '') echo $rcp_theme_header_html; ?>

    <div id="red-cultural-ty-main" class="flex flex-col items-center justify-start p-4 md:p-8 pt-12 md:pt-24">

        
        <!-- Main Card -->
        <div id="red-cultural-ty-card" class="max-w-2xl w-full bg-white rounded-none border border-black overflow-hidden fade-in order-card">
            
            <!-- Header -->
            <div id="red-cultural-ty-header" class="text-center border-b border-zinc-100 pb-8">

                <?php if ($is_success) : ?>
                    <h1 id="red-cultural-ty-title" class="font-bold tracking-tight mb-2 uppercase text-balance"><?php echo esc_html__('Pedido Confirmado', 'red-cultural-pages'); ?></h1>
                    <p class="text-zinc-500 text-base max-w-md mx-auto leading-relaxed">

                        <?php echo esc_html__('¡Muchas gracias por tu compra! Tu acceso ya está activo y puedes comenzar ahora mismo.', 'red-cultural-pages'); ?>
                    </p>
                <?php else : ?>
                    <div class="inline-flex items-center justify-center w-16 h-16 border border-zinc-300 rounded-full mb-6">
                        <svg class="w-8 h-8 text-zinc-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h1 id="red-cultural-ty-title" class="font-bold tracking-tight mb-2 uppercase text-balance text-zinc-400"><?php echo esc_html__('Pedido Pendiente', 'red-cultural-pages'); ?></h1>
                    <p class="text-zinc-500 text-base max-w-md mx-auto leading-relaxed">

                        <?php echo esc_html__('¡Gracias por tu compra! Recibimos la orden, pero falta confirmar la transferencia bancaria. Se hará durante el día.', 'red-cultural-pages'); ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Instrucciones de Pago (BACS) -->
            <?php if ($is_pending && $method_id === 'bacs') : ?>
            <div id="red-cultural-ty-instructions" class="p-8 border-b border-zinc-100 bg-zinc-50/50">
                <div class="max-w-md mx-auto">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] mb-4 text-center text-zinc-400"><?php echo esc_html__('Próximo Paso: Pago por Transferencia', 'red-cultural-pages'); ?></p>
                    <div class="text-sm text-zinc-700 leading-relaxed text-center space-y-4">
                        <p><?php echo sprintf(__('Por favor realiza la transferencia por el total de %s. Tendrás acceso en cuanto confirmemos el depósito.', 'red-cultural-pages'), '<span class="font-bold text-black text-base">' . $order_total . '</span>'); ?></p>
                        
                        <div class="py-5 border border-zinc-200 bg-white inline-block w-full rounded-md shadow-sm">
                            <p class="text-[11px] text-zinc-400 font-mono mb-1"><?php echo esc_html__('Nombre/Razón Social', 'red-cultural-pages'); ?></p>
                            <p class="font-bold font-mono text-black text-sm mb-3">Ediciones Alicia Limitada</p>

                            <p class="text-[11px] text-zinc-400 font-mono mb-1"><?php echo esc_html__('RUT', 'red-cultural-pages'); ?></p>
                            <p class="font-bold font-mono text-black text-sm mb-3">76.360.721-6</p>

                            <p class="text-[11px] text-zinc-400 font-mono mb-1"><?php echo esc_html__('Banco', 'red-cultural-pages'); ?></p>
                            <p class="font-bold font-mono text-black text-sm mb-3">Banco BICE</p>

                            <p class="text-[11px] text-zinc-400 font-mono mb-1"><?php echo esc_html__('Tipo de Cuenta', 'red-cultural-pages'); ?></p>
                            <p class="font-bold font-mono text-black text-sm mb-3">Cuenta Corriente</p>
                            
                            <p class="text-[11px] text-zinc-400 font-mono mb-1"><?php echo esc_html__('Número de Cuenta', 'red-cultural-pages'); ?></p>
                            <p class="font-bold font-mono text-black text-lg"><?php echo esc_html('02746948'); ?></p>

                            <p class="text-[11px] text-zinc-400 font-mono mt-4 mb-1"><?php echo esc_html__('REFERENCIA / MOTIVO', 'red-cultural-pages'); ?></p>
                            <p class="font-bold font-mono text-black"><?php echo esc_html__('PEDIDO #', 'red-cultural-pages') . $order_number; ?></p>
                        </div>
                        
                        <p class="text-[10px] text-zinc-400 italic">
                            <?php echo esc_html__('Envía el comprobante a magdalena@redcultural.cl una vez realizada la operación.', 'red-cultural-pages'); ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Course List (Tu Compra) -->
            <div id="red-cultural-ty-courses" class="p-10 border-b border-zinc-100">
                <h2 id="red-cultural-ty-courses-title" class="text-xs font-bold text-black uppercase tracking-[0.2em]"><?php echo esc_html__('Tu Compra', 'red-cultural-pages'); ?></h2>
                <div class="space-y-4">
                    <?php if (empty($courses_data)) : ?>
                         <p class="text-sm text-zinc-400"><?php echo esc_html__('No se encontraron productos en este pedido.', 'red-cultural-pages'); ?></p>
                    <?php else : ?>
                        <?php foreach ($courses_data as $course) : ?>
                                <div class="flex flex-col md:flex-row md:items-center justify-between p-6 border border-zinc-100 rounded-none gap-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="hidden sm:flex w-12 h-12 border border-zinc-200 items-center justify-center flex-shrink-0 overflow-hidden bg-zinc-50">
                                            <?php if (!empty($course['image'])) : ?>
                                                <img src="<?php echo esc_url($course['image']); ?>" class="w-full h-full object-cover">
                                            <?php else : ?>
                                                <svg class="w-6 h-6 text-zinc-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-width="1.5"></path>
                                                </svg>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold uppercase tracking-tight"><?php echo esc_html($course['title']); ?></p>
                                            <p class="text-[11px] text-zinc-400 uppercase">
                                                <?php 
                                                if ($course['is_course']) {
                                                    if (!empty($course['lesson_date'])) {
                                                        echo esc_html($course['lesson_date']);
                                                    } else {
                                                        echo $is_success ? __('Acceso Activo', 'red-cultural-pages') : __('Pendiente de activación', 'red-cultural-pages');
                                                    }
                                                    
                                                    if ($course['lesson_count'] > 0) {
                                                        echo ' • ' . sprintf(_n('1 Lección', '%d Lecciones', $course['lesson_count'], 'red-cultural-pages'), $course['lesson_count']);
                                                    }
                                                } else {
                                                    echo __('Producto Físico', 'red-cultural-pages');
                                                }
                                                ?> 
                                                • <?php echo $course['price']; ?>
                                            </p>
                                        </div>
                                    </div>
                            
                            <?php if ($course['is_course'] && $is_success && $course['url'] !== '#') : ?>
                                <div>
                                    <a href="<?php echo esc_url($course['url']); ?>" class="btn-black inline-flex items-center justify-center px-6 py-3 font-bold uppercase tracking-widest text-[10px] whitespace-nowrap">
                                        <span><?php echo $course['is_rcil'] ? __('Ir a Lección', 'red-cultural-pages') : __('Ir al Curso', 'red-cultural-pages'); ?></span>
                                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                        </svg>
                                    </a>
                                </div>
                            <?php else : ?>
                                <div class="inline-flex items-center px-4 py-2 bg-zinc-50 border border-zinc-200 text-zinc-400 font-bold uppercase tracking-widest text-[9px]">
                                    <span class="mr-2 h-2 w-2 rounded-full <?php echo $is_success ? 'bg-green-500' : 'bg-zinc-300'; ?>"></span>
                                    <?php echo $is_success ? 'Activo' : 'Pago Pendiente'; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Info Grid -->
            <div id="red-cultural-ty-summary" class="p-10 border-b border-zinc-100">
                <h2 id="red-cultural-ty-summary-title" class="text-xs font-bold text-black uppercase tracking-[0.2em] mb-8 border-b border-black pb-2 inline-block"><?php echo esc_html__('Datos de la Orden', 'red-cultural-pages'); ?></h2>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div>
                        <p class="text-[10px] text-zinc-400 uppercase font-bold mb-1">ID Pedido</p>
                        <p class="text-sm font-bold">#<?php echo esc_html($order_number); ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] text-zinc-400 uppercase font-bold mb-1">Fecha</p>
                        <p class="text-sm font-bold"><?php echo esc_html($order_date); ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] text-zinc-400 uppercase font-bold mb-1">Total</p>
                        <p class="text-sm font-bold"><?php echo $order_total; ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] text-zinc-400 uppercase font-bold mb-1">Método</p>
                        <p class="text-sm font-bold"><?php echo esc_html($method_title); ?></p>
                    </div>
                </div>
            </div>


            <!-- Footer Help -->
            <div class="p-8 bg-zinc-50 text-center border-t border-zinc-100">
                <p class="text-[11px] text-zinc-500 uppercase tracking-widest">
                    ¿Dudas sobre el proceso? <a href="/contacto" class="text-black font-bold hover:underline ml-1">Soporte Técnico</a>
                </p>
            </div>
        </div>

        <!-- Utility Links -->
        <div class="mt-8 flex space-x-10 no-print">
            <a href="/" class="text-[11px] font-bold uppercase tracking-widest text-white hover:text-zinc-200 transition-colors flex items-center no-underline">

                <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Inicio
            </a>
            <button onclick="window.print()" class="text-[11px] font-bold uppercase tracking-widest text-white hover:text-zinc-200 transition-colors flex items-center bg-transparent border-0 cursor-pointer">

                <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Imprimir Recibo
            </button>
        </div>
    </div>

    <?php if ($rcp_theme_footer_html !== '') echo $rcp_theme_footer_html; ?>

    <?php wp_footer(); ?>
</body>
</html>
