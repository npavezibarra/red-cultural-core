<?php
/**
 * Confirm Purchase — intermediate page before checkout.
 *
 * Shows the user a summary of what they are about to buy and offers a
 * "Continuar al pago" button that redirects to WooCommerce checkout,
 * or a "Volver al curso" link.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

if (!is_user_logged_in()) {
	wp_safe_redirect(home_url('/'));
	exit;
}

// ---- Resolve purchase intent or fall back to current cart ----
$rcp_intent    = class_exists('RC_Purchase_Intent') ? RC_Purchase_Intent::get() : null;
$rcp_course_id = 0;
$rcp_type      = '';
$rcp_back_url  = home_url('/');

if ($rcp_intent) {
	$rcp_course_id = absint($rcp_intent['course_id'] ?? 0);
	$rcp_type      = $rcp_intent['type'] ?? '';

	// Make sure the cart is prepared.
	if (empty($rcp_intent['prepared'])) {
		$result = RC_Purchase_Intent::prepare_cart();
		if (is_wp_error($result)) {
			// Can't prepare? Redirect back.
			wp_safe_redirect($rcp_course_id ? get_permalink($rcp_course_id) : home_url('/'));
			exit;
		}
	}
}

if ($rcp_course_id) {
	$rcp_back_url = (string) get_permalink($rcp_course_id);
}

// ---- Gather cart items for display ----
$rcp_cart_items  = [];
$rcp_cart_total  = 0;
$rcp_currency    = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '$';

if (function_exists('WC') && WC()->cart && !WC()->cart->is_empty()) {
	foreach (WC()->cart->get_cart() as $cart_item) {
		$product = $cart_item['data'] ?? null;
		if (!$product || !is_object($product)) {
			continue;
		}
		$rcp_cart_items[] = [
			'name'     => $product->get_name(),
			'price'    => (float) $product->get_price(),
			'quantity' => (int) ($cart_item['quantity'] ?? 1),
			'image'    => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail') ?: '',
		];
	}
	$rcp_cart_total = (float) WC()->cart->get_total('edit');
}

$rcp_course_title = $rcp_course_id ? (string) get_the_title($rcp_course_id) : '';
$rcp_course_thumb = $rcp_course_id ? (string) get_the_post_thumbnail_url($rcp_course_id, 'medium') : '';
$rcp_checkout_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout/');

// Pre-render block theme template parts.
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
	<title>Confirmar compra — <?php echo esc_html($rcp_course_title ?: 'Red Cultural'); ?></title>
	<script src="https://cdn.tailwindcss.com"></script>
	<script src="https://unpkg.com/lucide@latest"></script>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<style>
		body { font-family: 'Inter', sans-serif; }
		.rcp-wide { max-width: var(--wp--style--global--wide-size, 1200px); }
		.rcp-confirm-card {
			background: #fff;
			border-radius: 16px;
			box-shadow: 0 25px 50px -12px rgba(0,0,0,.08), 0 0 0 1px rgba(0,0,0,.03);
			overflow: hidden;
		}
		.rcp-confirm-btn {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 10px;
			width: 100%;
			padding: 16px 32px;
			background: #000;
			color: #fff;
			font-weight: 700;
			font-size: 14px;
			letter-spacing: 2px;
			text-transform: uppercase;
			border: none;
			border-radius: 10px;
			cursor: pointer;
			transition: all 0.2s ease;
			text-decoration: none;
		}
		.rcp-confirm-btn:hover {
			background: #1f2937;
			transform: translateY(-1px);
			box-shadow: 0 4px 12px rgba(0,0,0,.15);
		}
		.rcp-confirm-btn:active {
			transform: translateY(0);
		}
		.rcp-back-link {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			color: #6b7280;
			font-size: 13px;
			font-weight: 600;
			text-decoration: none;
			transition: color 0.2s;
		}
		.rcp-back-link:hover { color: #111; }
		.rcp-item-row {
			display: flex;
			align-items: center;
			gap: 16px;
			padding: 16px 0;
			border-bottom: 1px solid #f3f4f6;
		}
		.rcp-item-row:last-child { border-bottom: none; }
		.rcp-item-thumb {
			width: 56px;
			height: 56px;
			border-radius: 10px;
			object-fit: cover;
			flex-shrink: 0;
			background: #f3f4f6;
		}
		.rcp-total-row {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 20px 0 0;
			border-top: 2px solid #000;
			margin-top: 8px;
		}
		@keyframes fadeInUp {
			from { opacity: 0; transform: translateY(16px); }
			to   { opacity: 1; transform: translateY(0); }
		}
		.rcp-animate { animation: fadeInUp 0.5s ease both; }
		.rcp-animate-delay { animation-delay: 0.15s; }
	</style>
	<?php wp_head(); ?>
</head>
<body <?php body_class('bg-gray-50 text-gray-900'); ?>>
	<?php if (function_exists('wp_body_open')) { wp_body_open(); } ?>

	<?php
	if ($rcp_theme_header_html !== '') {
		echo $rcp_theme_header_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<main class="rcp-wide mx-auto px-6 py-20 min-h-[60vh] flex items-start justify-center">
		<div class="w-full max-w-lg">

			<?php if (empty($rcp_cart_items)) : ?>
				<!-- Empty State -->
				<div class="text-center rcp-animate">
					<div class="w-16 h-16 mx-auto mb-6 rounded-full bg-gray-100 flex items-center justify-center">
						<i data-lucide="shopping-cart" class="w-7 h-7 text-gray-400"></i>
					</div>
					<h1 class="text-2xl font-bold text-gray-900 mb-3">No hay productos en tu carrito</h1>
					<p class="text-gray-500 text-sm mb-8">Parece que aún no has seleccionado un curso o lecciones.</p>
					<a href="<?php echo esc_url(home_url('/')); ?>" class="rcp-confirm-btn inline-flex" style="width:auto; display:inline-flex;">
						Explorar Cursos
					</a>
				</div>
			<?php else : ?>
				<!-- Confirmation Card -->
				<div class="rcp-confirm-card rcp-animate">
					<!-- Header -->
					<div class="px-8 pt-8 pb-6 border-b border-gray-100">
						<div class="flex items-center gap-3 mb-2">
							<div class="w-8 h-8 rounded-full bg-black flex items-center justify-center">
								<i data-lucide="check" class="w-4 h-4 text-white"></i>
							</div>
							<h1 class="text-xl font-bold text-gray-900">Confirmar compra</h1>
						</div>
						<?php if ($rcp_course_title) : ?>
							<p class="text-sm text-gray-500 ml-11"><?php echo esc_html($rcp_course_title); ?></p>
						<?php endif; ?>
					</div>

					<!-- Items -->
					<div class="px-8 py-4">
						<?php foreach ($rcp_cart_items as $item) : ?>
							<div class="rcp-item-row">
								<?php if ($rcp_course_thumb) : ?>
									<img src="<?php echo esc_url($rcp_course_thumb); ?>" alt="" class="rcp-item-thumb" />
								<?php elseif ($item['image']) : ?>
									<img src="<?php echo esc_url($item['image']); ?>" alt="" class="rcp-item-thumb" />
								<?php else : ?>
									<div class="rcp-item-thumb flex items-center justify-center">
										<i data-lucide="book-open" class="w-5 h-5 text-gray-400"></i>
									</div>
								<?php endif; ?>
								<div class="flex-1 min-w-0">
									<p class="text-sm font-semibold text-gray-900 truncate"><?php echo esc_html($item['name']); ?></p>
									<?php if ($item['quantity'] > 1) : ?>
										<p class="text-xs text-gray-400 mt-0.5">x<?php echo esc_html((string)$item['quantity']); ?></p>
									<?php endif; ?>
								</div>
								<div class="text-sm font-bold text-gray-900 whitespace-nowrap">
									<?php echo esc_html($rcp_currency . number_format($item['price'] * $item['quantity'], 0, ',', '.')); ?>
								</div>
							</div>
						<?php endforeach; ?>

						<!-- Total -->
						<div class="rcp-total-row">
							<span class="text-sm font-bold text-gray-900 uppercase tracking-wider">Total</span>
							<span class="text-lg font-extrabold text-gray-900">
								<?php echo esc_html($rcp_currency . number_format($rcp_cart_total, 0, ',', '.')); ?>
							</span>
						</div>
					</div>

					<!-- Actions -->
					<div class="px-8 pb-8 pt-4 space-y-4">
						<a id="rcp-confirm-continue" href="<?php echo esc_url($rcp_checkout_url); ?>" class="rcp-confirm-btn">
							<i data-lucide="credit-card" class="w-5 h-5"></i>
							Continuar al pago
						</a>
						<div class="text-center">
							<a href="<?php echo esc_url($rcp_back_url); ?>" class="rcp-back-link">
								<i data-lucide="arrow-left" class="w-4 h-4"></i>
								Volver al curso
							</a>
						</div>
					</div>
				</div>

				<!-- Security Note -->
				<div class="rcp-animate rcp-animate-delay mt-6 text-center">
					<p class="text-[11px] text-gray-400 flex items-center justify-center gap-1.5">
						<i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
						Pago seguro procesado por WooCommerce
					</p>
				</div>
			<?php endif; ?>

		</div>
	</main>

	<?php if ($rcp_theme_footer_html !== '') : ?>
		<?php echo $rcp_theme_footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php else : ?>
		<footer class="bg-white border-t border-gray-100 py-12">
			<div class="rcp-wide mx-auto px-6 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0 text-[10px] text-gray-400 uppercase tracking-widest font-bold">
				<div><?php echo esc_html('© ' . gmdate('Y') . ' Red Cultural'); ?></div>
			</div>
		</footer>
	<?php endif; ?>

	<script>
		(function () {
			// Clear intent when clicking continue
			var continueBtn = document.getElementById('rcp-confirm-continue');
			if (continueBtn) {
				continueBtn.addEventListener('click', function () {
					// Intent will be cleared server-side on entering checkout,
					// but we also do a lightweight cookie clear here.
					document.cookie = 'rc_purchase_intent=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
				});
			}

			// Lucide icons
			if (window.lucide && typeof window.lucide.createIcons === 'function') {
				window.lucide.createIcons();
			}
		})();
	</script>
	<?php wp_footer(); ?>
</body>
</html>
