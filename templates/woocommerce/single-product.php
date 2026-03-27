<?php
/**
 * Single Product Page template
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! is_a( $product, 'WC_Product' ) ) {
	$product = wc_get_product( get_the_ID() );
}

if ( ! $product ) {
	return;
}

$product_id = $product->get_id();
$title = $product->get_name();
$price_html = $product->get_price_html();
$description = $product->get_short_description();
if ( empty( $description ) ) {
	$description = $product->get_description();
}
// Strip tags but keep some basic formatting if needed, or just plain text as per snippet
$description = wp_strip_all_tags( $description );

$image_id = $product->get_image_id();
$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';
if ( ! $image_url ) {
	$image_url = function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src( 'full' ) : '';
}

// Breadcrumbs / Categories
$categories = wc_get_product_category_list( $product_id, ', ', '', '' );
$primary_cat = '';
$terms = get_the_terms( $product_id, 'product_cat' );
if ( $terms && ! is_wp_error( $terms ) ) {
	$primary_cat = $terms[0]->name;
}

$rcp_theme_header_html = '';
$rcp_theme_footer_html = '';
if ( function_exists( 'do_blocks' ) ) {
	$rcp_theme_header_html = (string) do_blocks( '<!-- wp:template-part {"slug":"header","area":"header"} /-->' );
	$rcp_theme_footer_html = (string) do_blocks( '<!-- wp:template-part {"slug":"footer","area":"footer"} /-->' );
}

// Author Logic
$author_id = (int) get_post_field( 'post_author', $product_id );
$author_name = $author_id ? (string) get_the_author_meta( 'display_name', $author_id ) : '';

// Fallback to linked course author if product author is not set or belongs to admin (ID 1)
// and a course author is available.
$linked_course_id = (int) get_post_meta( $product_id, '_related_course_id', true );
if ( ! $linked_course_id ) {
	$linked_course_id = (int) get_post_meta( $product_id, '_related_course', true );
}

if ( $linked_course_id > 0 ) {
	$course_author_id = (int) get_post_field( 'post_author', $linked_course_id );
	if ( $course_author_id > 0 && ( $author_id === 0 || $author_id === 1 ) ) {
		$author_id = $course_author_id;
		$author_name = (string) get_the_author_meta( 'display_name', $author_id );
	}
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title(); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            color: #1a1a1a;
        }

        .product-image-container {
            aspect-ratio: 1/1;
            background-color: #f9f9f9;
            overflow: hidden;
            border-radius: 6px;
        }

        .quantity-input::-webkit-outer-spin-button,
        .quantity-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .btn-primary {
            transition: all 0.3s ease;
            border-radius: 6px;
        }

        .btn-primary:hover {
            background-color: #333;
        }

        .rounded-custom {
            border-radius: 6px;
        }

        /* Prevent Tailwind preflight conflicts with theme if necessary */
        #red-cultural-product-page input[type="number"] {
            -moz-appearance: textfield;
        }
    </style>
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'antialiased bg-white' ); ?>>
    <?php if ( function_exists( 'wp_body_open' ) ) { wp_body_open(); } ?>

    <?php if ( $rcp_theme_header_html !== '' ) : ?>
        <div id="red-cultural-header-wrapper">
            <?php echo $rcp_theme_header_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
    <?php endif; ?>

    <main id="red-cultural-product-page" class="mx-auto" style="max-width: var(--wp--style--global--wide-size); width: 100%; padding: 20px 0px;">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 lg:gap-16">
            
            <!-- Imagen única del producto (Cuadrada) -->
            <div>
                <div class="product-image-container relative group">
                    <img id="mainImage" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="w-full h-full object-cover">
                </div>
            </div>

            <!-- Información del producto -->
            <div class="flex flex-col text-left">
                <nav class="flex text-[10px] text-gray-400 mb-4 uppercase tracking-widest font-medium" aria-label="<?php esc_attr_e( 'Migas de pan', 'red-cultural-pages' ); ?>">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-black no-underline transition-colors"><?php esc_html_e( 'Inicio', 'red-cultural-pages' ); ?></a>
                    <?php if ( $primary_cat ) : ?>
                        <span class="mx-2">/</span>
                        <span class="text-gray-900"><?php echo esc_html( $primary_cat ); ?></span>
                    <?php endif; ?>
                </nav>

                <h1 class="text-2xl md:text-3xl font-medium mb-2 leading-tight"><?php echo esc_html( $title ); ?></h1>
                
                <?php if ( $author_name ) : ?>
                    <div class="flex items-center gap-2 mb-6 text-sm text-gray-500">
                        <span>Por</span>
                        <a id="rc-author-display-name-header" href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>" class="font-medium text-black hover:underline decoration-gray-300 underline-offset-4 transition-all">
                            <?php echo esc_html( $author_name ); ?>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="flex items-center gap-4 mb-6">
                    <span class="text-xl font-light text-gray-900"><?php echo $price_html; ?></span>
                </div>

                <?php if ( current_user_can( 'manage_options' ) ) : ?>
                    <!-- Panel Admin de Autor y Estado -->
                    <div id="rc-author-admin-ui" class="mb-8 p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center space-x-2 text-gray-700">
                                    <i data-lucide="settings" class="w-4 h-4"></i>
                                    <span class="text-[10px] font-bold uppercase tracking-wider"><?php esc_html_e( 'Panel Admin', 'red-cultural-core' ); ?></span>
                                </div>
                                
                                <div class="flex items-center space-x-3 bg-white border border-gray-100 px-3 py-1.5 rounded-lg shrink-0 scale-90 md:scale-100 origin-left">
                                    <span class="text-[9px] font-bold uppercase text-gray-400">Estado:</span>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="rc-course-status-toggle" class="sr-only peer" <?php echo ( get_post_status() === 'publish' ) ? 'checked' : ''; ?>>
                                        <div class="w-8 h-4 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-green-500"></div>
                                        <span id="rc-status-label" class="ml-2 text-[9px] font-bold uppercase text-gray-600"><?php echo ( get_post_status() === 'publish' ) ? 'Publicado' : 'Borrador'; ?></span>
                                    </label>
                                </div>
                            </div>

                            <button id="rc-author-edit-trigger" class="text-[10px] bg-black text-white px-3 py-1 rounded-full font-bold uppercase hover:bg-gray-800 transition-colors shadow-sm" type="button">Cambiar Autor</button>
                        </div>

                        <div id="rc-author-admin-box" class="hidden mt-4 pt-4 border-t border-gray-200">
                            <div class="relative w-full">
                                <input type="text" id="rc-author-search-input" class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:border-black outline-none shadow-sm" placeholder="<?php esc_attr_e( 'Cambiar autor...', 'red-cultural-core' ); ?>" autocomplete="off">
                                <div id="rc-author-search-results" class="hidden absolute left-0 top-full mt-1 w-full bg-white text-gray-800 rounded-lg shadow-2xl max-h-48 overflow-y-auto z-[9999] border border-gray-100 p-1"></div>
                            </div>
                            
                            <div class="flex items-center justify-between mt-3">
                                <div id="rc-author-edit-status" class="text-[10px] font-bold"></div>
                                <button id="rc-author-edit-cancel" class="text-[10px] text-gray-400 font-bold hover:text-gray-600 uppercase" type="button">Cerrar</button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Texto a 22px -->
                <p class="text-gray-500 leading-snug mb-8 text-[22px]">
                    <?php echo esc_html( $description ); ?>
                </p>

                <!-- Sección Añadir al carrito -->
                <div class="flex flex-col sm:row gap-3 mb-8">
                    <div class="flex items-center gap-3">
                        <div class="flex border border-gray-200 rounded-custom px-1 items-center bg-gray-50 h-12">
                            <button onclick="stepQty(-1)" class="p-2 text-gray-400 hover:text-black transition-colors bg-transparent border-0 cursor-pointer">
                                <i class="fa-solid fa-minus text-[10px]"></i>
                            </button>
                            <input type="number" id="quantity" value="1" min="1" class="quantity-input bg-transparent w-10 text-center border-none focus:ring-0 text-xs font-medium [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                            <button onclick="stepQty(1)" class="p-2 text-gray-400 hover:text-black transition-colors bg-transparent border-0 cursor-pointer">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                            </button>
                        </div>
                        
                        <form action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' class="flex-grow flex gap-3 m-0">
                            <input type="hidden" name="add-to-cart" value="<?php echo esc_attr( (string) $product_id ); ?>" />
                            <input type="hidden" id="real-quantity" name="quantity" value="1" />
                            
                            <button type="submit" class="btn-primary flex-grow bg-black text-white py-3 px-6 text-xs font-semibold tracking-wider flex items-center justify-center gap-2 border-0 cursor-pointer h-12 uppercase">
                                <?php esc_html_e( 'AÑADIR AL CARRITO', 'red-cultural-pages' ); ?>
                            </button>
                        </form>


                    </div>
                </div>

                <!-- Insignia de Envío -->
                <div class="py-6 border-t border-gray-100">
                    <div class="flex items-center gap-3 text-xs text-gray-600 font-medium">
                        <i class="fa-solid fa-truck-fast text-black"></i>
                        <span><?php esc_html_e( 'Despachos a todo Chile', 'red-cultural-pages' ); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notificación Toast -->
        <div id="toast" class="fixed bottom-8 right-8 bg-black text-white px-5 py-3 rounded-custom shadow-2xl transform translate-y-20 opacity-0 transition-all duration-300 flex items-center gap-3 text-xs z-50">
            <i class="fa-solid fa-circle-check text-green-400"></i>
            <span><?php esc_html_e( 'Producto añadido con éxito', 'red-cultural-pages' ); ?></span>
        </div>
    </main>

    <?php if ( $rcp_theme_footer_html !== '' ) : ?>
        <div id="red-cultural-footer-wrapper">
            <?php echo $rcp_theme_footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
    <?php endif; ?>

    <script>
        function stepQty(val) {
            const input = document.getElementById('quantity');
            const realInput = document.getElementById('real-quantity');
            let current = parseInt(input.value);
            if (current + val >= 1) {
                input.value = current + val;
                if (realInput) realInput.value = input.value;
            }
        }

        // Sync real quantity on manual input change
        document.getElementById('quantity').addEventListener('change', function() {
            const realInput = document.getElementById('real-quantity');
            if (realInput) realInput.value = this.value;
        });

        // Form interception for toast notification
        const addToCartForm = document.querySelector('form[method="post"]');
        if (addToCartForm) {
            addToCartForm.addEventListener('submit', function(e) {
                // We let the form submit normally (standard WooCommerce behavior)
                // but we trigger the toast just before redirecting if it were AJAX.
                // Since it's a standard POST, the toast might only show briefly.
                // If the user wants AJAX add to cart, we'd need to handle it via fetch.
                // For now, let's keep it standard but show toast if it was AJAX-enabled.
                
                // Show toast (it will disappear on page reload anyway if not AJAX)
                showToast();
            });
        }

        function showToast() {
            const toast = document.getElementById('toast');
            if (toast) {
                toast.classList.remove('translate-y-20', 'opacity-0');
                setTimeout(() => {
                    toast.classList.add('translate-y-20', 'opacity-0');
                }, 3000);
            }
        }
    </script>

    <?php wp_footer(); ?>
</body>
</html>
