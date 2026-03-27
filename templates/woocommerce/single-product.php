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

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title(); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                <div class="flex items-center gap-4 mb-6">
                    <span class="text-xl font-light text-gray-900"><?php echo $price_html; ?></span>
                </div>

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

                        <button class="border border-gray-200 p-3 rounded-custom hover:bg-gray-50 transition-colors bg-transparent cursor-pointer h-12 w-12 flex items-center justify-center">
                            <i class="fa-regular fa-heart text-sm"></i>
                        </button>
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
