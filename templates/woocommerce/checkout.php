<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('WC')) {
	wp_die(esc_html__('WooCommerce es necesario para ver esta página.', 'red-cultural-pages'));
}

$cart = WC()->cart;
if (!$cart) {
	wp_die(esc_html__('El carrito no está disponible.', 'red-cultural-pages'));
}

$checkout = WC()->checkout();
if (!$checkout) {
	wp_die(esc_html__('La página de pago no está disponible.', 'red-cultural-pages'));
}

$checkout_url = wc_get_checkout_url();

// Remove item from cart while staying on checkout.
if (
	isset($_GET['rcp_remove_item'], $_GET['rcp_remove_nonce'])
	&& is_string($_GET['rcp_remove_item'])
	&& is_string($_GET['rcp_remove_nonce'])
) {
	$remove_key = (string) wp_unslash($_GET['rcp_remove_item']);
	$remove_nonce = (string) wp_unslash($_GET['rcp_remove_nonce']);

	if (wp_verify_nonce($remove_nonce, 'rcp_remove_item') && $remove_key !== '') {
		$cart->remove_cart_item($remove_key);
		$cart->calculate_totals();
	}

	wp_safe_redirect(remove_query_arg(array('rcp_remove_item', 'rcp_remove_nonce'), $checkout_url));
	exit;
}

$items = $cart->get_cart();
$has_physical = false;
foreach ($items as $cart_item) {
	$product = $cart_item['data'] ?? null;
	if ($product && is_object($product) && method_exists($product, 'needs_shipping') && $product->needs_shipping()) {
		$has_physical = true;
		break;
	}
}
$is_digital_only = !$has_physical;
$remove_nonce = wp_create_nonce('rcp_remove_item');
$shop_url = function_exists('wc_get_page_permalink') ? (string) wc_get_page_permalink('shop') : (string) home_url('/shop/');
if ($shop_url === '') {
	$shop_url = (string) home_url('/shop/');
}

// Only apply field rules for this request/template (digital-only hides shipping/address requirements).
add_filter('woocommerce_checkout_fields', static function (array $fields) use ($is_digital_only): array {
	if (!$is_digital_only) {
		return $fields;
	}

	$allowed = array(
		'billing_first_name',
		'billing_last_name',
		'billing_email',
		'billing_phone',
	);

	foreach (array('billing', 'shipping', 'account', 'order') as $section) {
		if (empty($fields[$section]) || !is_array($fields[$section])) {
			continue;
		}
		foreach (array_keys($fields[$section]) as $key) {
			if ($section === 'billing' && in_array($key, $allowed, true)) {
				continue;
			}
			unset($fields[$section][$key]);
		}
	}

	return $fields;
}, 20);

// Pre-render block theme template parts BEFORE wp_head so their assets are enqueued in the correct place.
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
	<title><?php echo esc_html__('Finalizar compra', 'red-cultural-pages'); ?></title>
	<script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=private_connectivity" />
	<style>
		@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
		body{font-family:'Inter',sans-serif}
		#red-cultural-header{border-bottom:1px solid #e5e7eb}
		#red-cultural-checkout-container{max-width:var(--wp--style--global--wide-size, 1200px)}
		#red-cultural-checkout-title{font-size:28px;letter-spacing:1px}
		#red-cultural-checkout-place-order{padding:10px;border-radius:6px}
		@media (min-width: 992px){
			.checkout-container{display:flex;gap:60px;align-items:flex-start}
			.billing-col{flex:1 1 50%}
			.order-col{flex:1 1 50%;position:sticky;top:40px}
		}
		/* Match the login overlay input style */
		.form-input{
			width:100%;
			padding:8px 12px;
			border:1px solid #e5e7eb;
			border-radius:3px;
			margin-top:6px;
			outline:none;
			transition:all .2s;
			background-color:#f9fafb !important;
		}
		/* Safety net: also style any Woo-generated fields inside wrappers. */
		#red-cultural-checkout-form .woocommerce-input-wrapper input[type="text"],
		#red-cultural-checkout-form .woocommerce-input-wrapper input[type="email"],
		#red-cultural-checkout-form .woocommerce-input-wrapper input[type="tel"],
		#red-cultural-checkout-form .woocommerce-input-wrapper input[type="password"],
		#red-cultural-checkout-form .woocommerce-input-wrapper textarea,
		#red-cultural-checkout-form .woocommerce-input-wrapper select{
			width:100%;
			padding:8px 12px;
			border:1px solid #e5e7eb;
			border-radius:3px;
			outline:none;
			transition:all .2s;
			background-color:#f9fafb !important;
		}
		.form-input:focus{
			background-color:#fff !important;
			border-color:#c5a367 !important;
			box-shadow:0 0 0 2px rgba(197,163,103,.15) !important;
		}
		#red-cultural-checkout-form .woocommerce-input-wrapper input:focus,
		#red-cultural-checkout-form .woocommerce-input-wrapper textarea:focus,
		#red-cultural-checkout-form .woocommerce-input-wrapper select:focus{
			background-color:#fff !important;
			border-color:#c5a367 !important;
			box-shadow:0 0 0 2px rgba(197,163,103,.15) !important;
		}
		.label-text{font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;color:#000}
		.order-table th{text-align:left;text-transform:uppercase;font-size:.7rem;letter-spacing:.1em}
		.order-table td{text-align:right}
		/* Make item/subtotal prices smaller; keep total large. */
		#red-cultural-checkout-order-tbody tr:not(#red-cultural-checkout-total-row) .woocommerce-Price-amount{font-size:20px}
		#red-cultural-checkout-total-row .woocommerce-Price-amount{font-size:32px}
		td#red-cultural-checkout-shipping-value{font-size:13px;color:#d13333}
		#red-cultural-checkout-order-table .rcp-remove-item{font-size:14px;line-height:1;color:#9ca3af;text-decoration:none}
		#red-cultural-checkout-order-table .rcp-remove-item:hover{color:#111827}
		#red-cultural-checkout-add-products{color:#c5a367;font-weight:700;font-size:11px;letter-spacing:.1em;text-transform:uppercase;text-decoration:none}
		#red-cultural-checkout-add-products:hover{filter:brightness(.9)}
		input[type="radio"], input[type="checkbox"]{accent-color:#000}
		/* Hide default Woo terms box (we keep only the checkbox if enabled). */
		.woocommerce-terms-and-conditions-wrapper{margin:0 0 16px 0}
		.woocommerce-terms-and-conditions{display:none}
		.woocommerce-privacy-policy-text{display:none}
		.rounded-3px{border-radius:3px !important}
		.focus-gold:focus{border-color:#c5a367 !important;box-shadow:0 0 0 2px rgba(197,163,103,.15) !important}

		/* Increase form typography ~15% (checkout + auth overlay). */
		#red-cultural-checkout-form .form-input{font-size:1.15rem}
		#red-cultural-checkout-form .label-text{font-size:.86rem}
		#red-cultural-checkout-identity-title,
		#red-cultural-checkout-shipping-title,
		#red-cultural-checkout-order-title{font-size:1.29rem}

		/* If user is not logged in, force auth first: hide checkout fields and disable place order. */
		#red-cultural-checkout-form[data-auth-required="1"] #red-cultural-checkout-name-row,
		#red-cultural-checkout-form[data-auth-required="1"] #red-cultural-checkout-contact-row,
		#red-cultural-checkout-form[data-auth-required="1"] #red-cultural-checkout-address-fields,
		#red-cultural-checkout-form[data-auth-required="1"] #red-cultural-checkout-after-customer-details{display:none}
		#red-cultural-checkout-form[data-auth-required="1"] #red-cultural-checkout-place-order{opacity:.35;pointer-events:none}

		/* Make the inline auth area match the "Resumen de Pedido" card (black/white, same hierarchy). */
		#red-cultural-checkout-auth-inline{border:1px solid #000;background:#fff;border-radius:0;padding:32px}
		#red-cultural-checkout-auth-inline .rcp-auth-label{font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;color:#000;display:block}
		#red-cultural-checkout-auth-inline .rcp-auth-input{
			width:100%;
			padding:12px;
			border:1px solid #000;
			border-radius:0;
			margin-top:6px;
			outline:none;
			transition:all .2s;
			background:#fff !important;
			font-size:1rem;
		}
		#red-cultural-checkout-auth-inline .rcp-auth-input:focus{background:#fcfcfc !important;box-shadow:0 0 0 2px rgba(0,0,0,.05)}
		#red-cultural-checkout-auth-inline .rcp-auth-primary{
			width:100%;
			background:#000;
			color:#fff;
			font-weight:700;
			padding:10px 17px;
			transition:all .2s;
			text-transform:uppercase;
			letter-spacing:.1em;
			font-size:.875rem;
			border-radius:6px;
		}
		#red-cultural-checkout-auth-inline .rcp-auth-primary:hover{background:#1f2937}
		#red-cultural-checkout-auth-inline .rcp-auth-link{color:#c5a367;font-weight:700}
		#red-cultural-checkout-auth-inline .rcp-auth-link:hover{filter:brightness(.9)}

		#red-cultural-checkout-terms-text{letter-spacing:1px}
		#red-cultural-checkout-auth-toggle-to-register{letter-spacing:1px}
		#red-cultural-checkout-auth-forgot-link{font-size:14px}

		/* "OTRA" shipping switch */
		#red-cultural-checkout-ship-toggle{display:flex;align-items:center;gap:10px}
		#red-cultural-checkout-ship-toggle-label{font-size:11px;letter-spacing:.1em;text-transform:uppercase;font-weight:700;color:#6b7280}
		#red-cultural-checkout-ship-toggle-input{position:absolute;opacity:0;pointer-events:none}
		#red-cultural-checkout-ship-toggle-slider{width:36px;height:18px;border:1px solid #000;border-radius:999px;position:relative;background:#fff;transition:all .2s}
		#red-cultural-checkout-ship-toggle-slider:before{content:'';position:absolute;left:2px;top:2px;width:14px;height:14px;background:#000;border-radius:999px;transition:transform .2s}
		#red-cultural-checkout-ship-toggle-input:checked + #red-cultural-checkout-ship-toggle-slider:before{transform:translateX(18px)}

		/* Sliding switch between billing/shipping address forms */
		#red-cultural-checkout-address-switcher{overflow:hidden}
		#red-cultural-checkout-address-track{display:flex;width:200%;transition:transform .35s ease}
		#red-cultural-checkout-address-panel-billing,
		#red-cultural-checkout-address-panel-shipping{width:50%}
		#red-cultural-checkout-address-switcher.is-alt #red-cultural-checkout-address-track{transform:translateX(-50%)}
	</style>
	<?php wp_head(); ?>
</head>
<body <?php body_class('bg-white px-4 pb-16'); ?> style="padding-top:0;">
	<?php if (function_exists('wp_body_open')) { wp_body_open(); } ?>

	<?php
	if ($rcp_theme_header_html !== '') {
		echo str_replace('<header ', '<header id="red-cultural-header" ', $rcp_theme_header_html); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<div id="red-cultural-checkout-container" class="max-w-[1400px] mx-auto pt-16" style="padding-top:20px;">
			<header id="red-cultural-checkout-header" class="border-b border-black flex justify-between items-end" style="margin-bottom:30px;padding-bottom:10px;">
				<h1 id="red-cultural-checkout-title" class="text-4xl font-bold tracking-tighter"><?php echo esc_html__('FINALIZAR COMPRA', 'red-cultural-pages'); ?></h1>
				<div id="red-cultural-checkout-secure" class="text-[10px] uppercase tracking-widest text-gray-500">
					<span class="material-symbols-outlined align-middle mr-1 text-[22px] leading-none">private_connectivity</span>
					<?php echo esc_html__('Transacción segura', 'red-cultural-pages'); ?>
				</div>
			</header>

		<?php
		$auth_error = (isset($_GET['rcp_auth_error']) && (string) $_GET['rcp_auth_error'] === '1');
		$auth_notice = isset($_GET['rcp_auth_notice']) ? (string) wp_unslash($_GET['rcp_auth_notice']) : '';
		$auth_nonce = wp_create_nonce('rcp_checkout_auth');
		$user_exists_nonce = wp_create_nonce('rcp_user_exists');
		$admin_post_url = admin_url('admin-post.php');
		$ajax_url = admin_url('admin-ajax.php');
		?>

			<?php if ($cart->is_empty()) : ?>
				<div id="red-cultural-checkout-empty" class="border border-black p-8">
					<?php echo esc_html__('Tu carrito está vacío.', 'red-cultural-pages'); ?>
				</div>
			<?php else : ?>
			<?php
			// We render our own auth UI, so remove Woo's default login/coupon banners.
			remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10);
			remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);

			/** @see woocommerce_checkout_process */
			do_action('woocommerce_before_checkout_form', $checkout);
			?>

			<form
				id="red-cultural-checkout-form"
				name="checkout"
				method="post"
				class="checkout woocommerce-checkout checkout-container"
				action="<?php echo esc_url(wc_get_checkout_url()); ?>"
				enctype="multipart/form-data"
				data-auth-required="<?php echo is_user_logged_in() ? '0' : '1'; ?>"
			>
				<div id="red-cultural-checkout-billing-col" class="billing-col">
					<div id="red-cultural-checkout-identity" class="mb-12">
						<?php if ($has_physical) : ?>
							<?php $ship_to_diff = (bool) $checkout->get_value('ship_to_different_address'); ?>
							<div id="red-cultural-checkout-address-title-row" class="flex items-center justify-between mb-8 border-b border-gray-100 pb-4">
								<h2 id="red-cultural-checkout-identity-title" class="text-lg font-bold uppercase tracking-widest">
									1.
									<span id="red-cultural-checkout-address-title-text">
										<?php echo $ship_to_diff ? esc_html__('Dirección de Entrega', 'red-cultural-pages') : esc_html__('Dirección de Facturación', 'red-cultural-pages'); ?>
									</span>
								</h2>
								<label id="red-cultural-checkout-ship-toggle" for="red-cultural-checkout-ship-toggle-input" class="cursor-pointer select-none">
									<span id="red-cultural-checkout-ship-toggle-label">OTRA</span>
									<input
										id="red-cultural-checkout-ship-toggle-input"
										type="checkbox"
										name="ship_to_different_address"
										value="1"
										<?php checked($ship_to_diff, true); ?>
									/>
									<span id="red-cultural-checkout-ship-toggle-slider" aria-hidden="true"></span>
								</label>
							</div>
						<?php else : ?>
							<h2 id="red-cultural-checkout-identity-title" class="text-lg font-bold mb-8 uppercase tracking-widest border-b border-gray-100 pb-4">1. <?php echo esc_html__('Identificación', 'red-cultural-pages'); ?></h2>
						<?php endif; ?>

						<?php if (!is_user_logged_in()) : ?>
							<div
								id="red-cultural-checkout-auth-inline"
								class="mb-10"
								data-admin-post="<?php echo esc_attr($admin_post_url); ?>"
								data-nonce="<?php echo esc_attr($auth_nonce); ?>"
								data-ajax-url="<?php echo esc_attr($ajax_url); ?>"
								data-exists-nonce="<?php echo esc_attr($user_exists_nonce); ?>"
								data-redirect="<?php echo esc_attr($checkout_url); ?>"
							>
								<?php
								$error_code = isset($_GET['rcp_auth_error']) ? (string) $_GET['rcp_auth_error'] : '';
								if ($error_code !== '') :
									$error_msg = esc_html__('No pudimos iniciar sesión. Revisa tus datos e inténtalo de nuevo.', 'red-cultural-pages');
									if ($error_code === 'captcha') {
										$error_msg = esc_html__('Captcha inválido. Por favor, inténtalo de nuevo.', 'red-cultural-pages');
									}
								?>
									<div id="red-cultural-checkout-auth-error" class="mb-4 text-sm font-semibold text-red-600">
										<?php echo $error_msg; ?>
									</div>
								<?php elseif ($auth_notice === 'reset_sent') : ?>
									<div id="red-cultural-checkout-auth-notice" class="mb-4 text-sm font-semibold text-emerald-600">
										<?php echo esc_html__('Te enviamos un correo para restablecer tu contraseña.', 'red-cultural-pages'); ?>
									</div>
								<?php endif; ?>

								<!-- Login view (default) -->
								<div id="red-cultural-checkout-auth-view-login">
									<div class="mb-4">
										<label class="rcp-auth-label rcp-auth-label" for="red-cultural-checkout-auth-login"><?php echo esc_html__('Email o Usuario', 'red-cultural-pages'); ?></label>
										<input id="red-cultural-checkout-auth-login" type="text" placeholder="correo@ejemplo.com" class="rcp-auth-input">
										<p id="red-cultural-checkout-auth-login-status" class="mt-2 text-sm font-semibold text-red-600 hidden"></p>
									</div>
									<div class="mb-4">
										<label class="rcp-auth-label" for="red-cultural-checkout-auth-password"><?php echo esc_html__('Contraseña', 'red-cultural-pages'); ?></label>
										<input id="red-cultural-checkout-auth-password" type="password" placeholder="••••••••" class="rcp-auth-input">
									</div>
									<div class="flex items-center justify-between text-[11px] mb-5">
										<button id="red-cultural-checkout-auth-forgot-link" type="button" class="rcp-auth-link">
											<?php echo esc_html__('Olvidé mi clave', 'red-cultural-pages'); ?>
										</button>
									</div>
									<?php /* Turnstile removed from checkout auth login */ ?>
									<button id="red-cultural-checkout-auth-login-submit" type="button" class="rcp-auth-primary">
										<?php echo esc_html__('Iniciar sesión', 'red-cultural-pages'); ?>
									</button>
									<div class="mt-5 pt-4 border-t border-gray-100 text-center">
										<p id="red-cultural-checkout-auth-toggle-to-register" class="text-gray-500 text-xs">
											<?php echo esc_html__('¿Eres nuevo en Red Cultural?', 'red-cultural-pages'); ?>
											<button id="red-cultural-checkout-auth-show-register" type="button" class="rcp-auth-link ml-1">
												<?php echo esc_html__('Crea una cuenta', 'red-cultural-pages'); ?>
											</button>
										</p>
									</div>
								</div>

								<!-- Register view -->
								<div id="red-cultural-checkout-auth-view-register" class="hidden">
									<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
										<div>
											<label class="rcp-auth-label" for="red-cultural-checkout-auth-first-name"><?php echo esc_html__('Nombre', 'red-cultural-pages'); ?></label>
											<input id="red-cultural-checkout-auth-first-name" type="text" class="rcp-auth-input">
										</div>
										<div>
											<label class="rcp-auth-label" for="red-cultural-checkout-auth-last-name"><?php echo esc_html__('Apellido', 'red-cultural-pages'); ?></label>
											<input id="red-cultural-checkout-auth-last-name" type="text" class="rcp-auth-input">
										</div>
									</div>
									<div class="mb-4">
										<label class="rcp-auth-label" for="red-cultural-checkout-auth-email"><?php echo esc_html__('Correo Electrónico', 'red-cultural-pages'); ?></label>
										<input id="red-cultural-checkout-auth-email" type="email" class="rcp-auth-input">
										<p id="red-cultural-checkout-auth-register-status" class="mt-2 text-sm font-semibold text-red-600 hidden"></p>
									</div>
									<div class="mb-5">
										<label class="rcp-auth-label" for="red-cultural-checkout-auth-register-password"><?php echo esc_html__('Contraseña', 'red-cultural-pages'); ?></label>
										<input id="red-cultural-checkout-auth-register-password" type="password" placeholder="••••••••" class="rcp-auth-input">
									</div>
									<?php /* Turnstile removed from checkout auth register */ ?>
									<button id="red-cultural-checkout-auth-register-submit" type="button" class="rcp-auth-primary">
										<?php echo esc_html__('Crear cuenta', 'red-cultural-pages'); ?>
									</button>
									<div class="mt-5 pt-4 border-t border-gray-100 text-center">
										<p class="text-gray-500 text-xs">
											<?php echo esc_html__('¿Ya tienes una cuenta?', 'red-cultural-pages'); ?>
											<button id="red-cultural-checkout-auth-show-login" type="button" class="rcp-auth-link ml-1">
												<?php echo esc_html__('Inicia sesión', 'red-cultural-pages'); ?>
											</button>
										</p>
									</div>
								</div>

								<!-- Forgot password view -->
								<div id="red-cultural-checkout-auth-view-forgot" class="hidden">
									<div class="mb-5">
										<label class="rcp-auth-label" for="red-cultural-checkout-auth-forgot-email"><?php echo esc_html__('Correo Electrónico', 'red-cultural-pages'); ?></label>
										<input id="red-cultural-checkout-auth-forgot-email" type="email" placeholder="correo@ejemplo.com" class="rcp-auth-input">
									</div>
									<button id="red-cultural-checkout-auth-forgot-submit" type="button" class="rcp-auth-primary">
										<?php echo esc_html__('Enviar correo', 'red-cultural-pages'); ?>
									</button>
									<div class="mt-5 pt-4 border-t border-gray-100 text-center">
										<p class="text-gray-500 text-xs">
											<button id="red-cultural-checkout-auth-show-login-2" type="button" class="rcp-auth-link">
												<?php echo esc_html__('Volver a iniciar sesión', 'red-cultural-pages'); ?>
											</button>
										</p>
									</div>
								</div>
							</div>
						<?php endif; ?>

						<?php if ($has_physical) : ?>
							<input type="hidden" name="ship_to_different_address" value="0">

							<div id="red-cultural-checkout-address-fields">
								<div
									id="red-cultural-checkout-address-switcher"
									class="<?php echo $ship_to_diff ? 'is-alt' : ''; ?>"
								>
									<div id="red-cultural-checkout-address-track">
										<!-- Billing address (default shipping) -->
										<div id="red-cultural-checkout-address-panel-billing">
											<div id="red-cultural-checkout-billing-name-row" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
												<div id="red-cultural-checkout-billing-first-name">
													<label class="label-text" for="billing_first_name"><?php echo esc_html__('Nombre *', 'red-cultural-pages'); ?></label>
													<input id="billing_first_name" name="billing_first_name" type="text" class="form-input" autocomplete="given-name" required value="<?php echo esc_attr((string) $checkout->get_value('billing_first_name')); ?>" placeholder="JUAN">
												</div>
												<div id="red-cultural-checkout-billing-last-name">
													<label class="label-text" for="billing_last_name"><?php echo esc_html__('Apellidos *', 'red-cultural-pages'); ?></label>
													<input id="billing_last_name" name="billing_last_name" type="text" class="form-input" autocomplete="family-name" required value="<?php echo esc_attr((string) $checkout->get_value('billing_last_name')); ?>" placeholder="PÉREZ">
												</div>
											</div>

											<div id="red-cultural-checkout-billing-contact-row" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
												<div id="red-cultural-checkout-billing-email">
													<label class="label-text" for="billing_email"><?php echo esc_html__('Email *', 'red-cultural-pages'); ?></label>
													<input id="billing_email" name="billing_email" type="email" class="form-input" autocomplete="email" required value="<?php echo esc_attr((string) $checkout->get_value('billing_email')); ?>" placeholder="EMAIL@EJEMPLO.COM">
												</div>
												<div id="red-cultural-checkout-billing-phone">
													<label class="label-text" for="billing_phone"><?php echo esc_html__('Teléfono *', 'red-cultural-pages'); ?></label>
													<input id="billing_phone" name="billing_phone" type="tel" class="form-input" autocomplete="tel" required value="<?php echo esc_attr((string) $checkout->get_value('billing_phone')); ?>" placeholder="+56 9 ...">
												</div>
											</div>

											<div id="red-cultural-checkout-billing-address-1" class="mb-6">
												<label class="label-text" for="billing_address_1"><?php echo esc_html__('Calle y Número *', 'red-cultural-pages'); ?></label>
												<input id="billing_address_1" name="billing_address_1" type="text" class="form-input" autocomplete="billing street-address" required value="<?php echo esc_attr((string) $checkout->get_value('billing_address_1')); ?>" placeholder="AV. SIEMPRE VIVA 742">
											</div>

											<div id="red-cultural-checkout-billing-address-2" class="mb-6">
												<label class="label-text" for="billing_address_2"><?php echo esc_html__('Dpto / Oficina / Block (opcional)', 'red-cultural-pages'); ?></label>
												<input id="billing_address_2" name="billing_address_2" type="text" class="form-input" autocomplete="billing address-line2" value="<?php echo esc_attr((string) $checkout->get_value('billing_address_2')); ?>" placeholder="EJ: DEPTO 402">
											</div>

											<div id="red-cultural-checkout-billing-city" class="mb-8">
												<label class="label-text" for="billing_city"><?php echo esc_html__('Comuna *', 'red-cultural-pages'); ?></label>
												<?php $billing_city = (string) $checkout->get_value('billing_city'); ?>
												<select id="billing_city" name="billing_city" class="form-input appearance-none bg-white" required>
													<option value=""><?php echo esc_html__('SELECCIONA UNA COMUNA...', 'red-cultural-pages'); ?></option>
													<?php foreach (array('SANTIAGO', 'PROVIDENCIA', 'LAS CONDES', 'VITACURA', 'ÑUÑOA') as $opt) : ?>
														<option value="<?php echo esc_attr($opt); ?>" <?php selected($billing_city, $opt); ?>><?php echo esc_html($opt); ?></option>
													<?php endforeach; ?>
												</select>
											</div>
										</div>

										<!-- Alternate shipping address -->
										<div id="red-cultural-checkout-address-panel-shipping">
											<div id="red-cultural-checkout-shipping-name-row" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
												<div id="red-cultural-checkout-shipping-first-name">
													<label class="label-text" for="shipping_first_name"><?php echo esc_html__('Nombre *', 'red-cultural-pages'); ?></label>
													<input id="shipping_first_name" name="shipping_first_name" type="text" class="form-input" autocomplete="shipping given-name" value="<?php echo esc_attr((string) $checkout->get_value('shipping_first_name')); ?>" placeholder="JUAN">
												</div>
												<div id="red-cultural-checkout-shipping-last-name">
													<label class="label-text" for="shipping_last_name"><?php echo esc_html__('Apellidos *', 'red-cultural-pages'); ?></label>
													<input id="shipping_last_name" name="shipping_last_name" type="text" class="form-input" autocomplete="shipping family-name" value="<?php echo esc_attr((string) $checkout->get_value('shipping_last_name')); ?>" placeholder="PÉREZ">
												</div>
											</div>

											<div id="red-cultural-checkout-shipping-phone-row" class="mb-6">
												<label class="label-text" for="shipping_phone"><?php echo esc_html__('Teléfono *', 'red-cultural-pages'); ?></label>
												<input id="shipping_phone" name="shipping_phone" type="tel" class="form-input" autocomplete="shipping tel" value="<?php echo esc_attr((string) $checkout->get_value('shipping_phone')); ?>" placeholder="+56 9 ...">
											</div>

											<div id="red-cultural-checkout-shipping-address-1" class="mb-6">
												<label class="label-text" for="shipping_address_1"><?php echo esc_html__('Calle y Número *', 'red-cultural-pages'); ?></label>
												<input id="shipping_address_1" name="shipping_address_1" type="text" class="form-input" autocomplete="shipping street-address" value="<?php echo esc_attr((string) $checkout->get_value('shipping_address_1')); ?>" placeholder="AV. SIEMPRE VIVA 742">
											</div>

											<div id="red-cultural-checkout-shipping-address-2" class="mb-6">
												<label class="label-text" for="shipping_address_2"><?php echo esc_html__('Dpto / Oficina / Block (opcional)', 'red-cultural-pages'); ?></label>
												<input id="shipping_address_2" name="shipping_address_2" type="text" class="form-input" autocomplete="shipping address-line2" value="<?php echo esc_attr((string) $checkout->get_value('shipping_address_2')); ?>" placeholder="EJ: DEPTO 402">
											</div>

											<div id="red-cultural-checkout-shipping-city" class="mb-8">
												<label class="label-text" for="shipping_city"><?php echo esc_html__('Comuna *', 'red-cultural-pages'); ?></label>
												<?php $ship_city = (string) $checkout->get_value('shipping_city'); ?>
												<select id="shipping_city" name="shipping_city" class="form-input appearance-none bg-white">
													<option value=""><?php echo esc_html__('SELECCIONA UNA COMUNA...', 'red-cultural-pages'); ?></option>
													<?php foreach (array('SANTIAGO', 'PROVIDENCIA', 'LAS CONDES', 'VITACURA', 'ÑUÑOA') as $opt) : ?>
														<option value="<?php echo esc_attr($opt); ?>" <?php selected($ship_city, $opt); ?>><?php echo esc_html($opt); ?></option>
													<?php endforeach; ?>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
						<?php else : ?>
							<div id="red-cultural-checkout-name-row" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
								<div id="red-cultural-checkout-billing-first-name">
									<label class="label-text" for="billing_first_name"><?php echo esc_html__('Nombre *', 'red-cultural-pages'); ?></label>
									<input id="billing_first_name" name="billing_first_name" type="text" class="form-input" autocomplete="given-name" required value="<?php echo esc_attr((string) $checkout->get_value('billing_first_name')); ?>" placeholder="JUAN">
								</div>
								<div id="red-cultural-checkout-billing-last-name">
									<label class="label-text" for="billing_last_name"><?php echo esc_html__('Apellidos *', 'red-cultural-pages'); ?></label>
									<input id="billing_last_name" name="billing_last_name" type="text" class="form-input" autocomplete="family-name" required value="<?php echo esc_attr((string) $checkout->get_value('billing_last_name')); ?>" placeholder="PÉREZ">
								</div>
							</div>

							<div id="red-cultural-checkout-contact-row" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
								<div id="red-cultural-checkout-billing-email">
									<label class="label-text" for="billing_email"><?php echo esc_html__('Email *', 'red-cultural-pages'); ?></label>
									<input id="billing_email" name="billing_email" type="email" class="form-input" autocomplete="email" required value="<?php echo esc_attr((string) $checkout->get_value('billing_email')); ?>" placeholder="EMAIL@EJEMPLO.COM">
								</div>
								<div id="red-cultural-checkout-billing-phone">
									<label class="label-text" for="billing_phone"><?php echo esc_html__('Teléfono *', 'red-cultural-pages'); ?></label>
									<input id="billing_phone" name="billing_phone" type="tel" class="form-input" autocomplete="tel" required value="<?php echo esc_attr((string) $checkout->get_value('billing_phone')); ?>" placeholder="+56 9 ...">
								</div>
							</div>
						<?php endif; ?>
					</div>

					<div id="red-cultural-checkout-after-customer-details">
						<?php do_action('woocommerce_checkout_after_customer_details'); ?>
					</div>
				</div>

				<div id="red-cultural-checkout-order-col" class="order-col border border-black p-8">
					<div id="red-cultural-checkout-order-title-row" class="flex items-center justify-between mb-8">
						<h2 id="red-cultural-checkout-order-title" class="text-lg font-bold uppercase tracking-widest"><?php echo esc_html__('Resumen de Pedido', 'red-cultural-pages'); ?></h2>
						<a id="red-cultural-checkout-add-products" href="<?php echo esc_url($shop_url); ?>">+ <?php echo esc_html__('PRODUCTOS', 'red-cultural-pages'); ?></a>
					</div>

					<table id="red-cultural-checkout-order-table" class="w-full order-table border-collapse mb-8">
						<thead id="red-cultural-checkout-order-thead">
							<tr id="red-cultural-checkout-order-head-row" class="border-b border-gray-200">
								<th id="red-cultural-checkout-order-head-item" class="pb-4"><?php echo esc_html__('Producto', 'red-cultural-pages'); ?></th>
								<th id="red-cultural-checkout-order-head-price" class="pb-4 text-right"><?php echo esc_html__('Precio', 'red-cultural-pages'); ?></th>
							</tr>
						</thead>
						<tbody id="red-cultural-checkout-order-tbody">
							<?php foreach ($items as $cart_item_key => $cart_item) : ?>
								<?php
								$product = $cart_item['data'] ?? null;
								if (!$product || !is_object($product)) {
									continue;
								}
								$name = $product->get_name();
								$qty = (int) ($cart_item['quantity'] ?? 1);
								$line = $cart->get_product_subtotal($product, $qty);
								$row_id = 'red-cultural-checkout-cart-item-' . preg_replace('/[^a-zA-Z0-9_\\-]/', '', (string) $cart_item_key);
								$remove_url = add_query_arg(
									array(
										'rcp_remove_item' => (string) $cart_item_key,
										'rcp_remove_nonce' => $remove_nonce,
									),
									$checkout_url
								);
								?>
								<tr id="<?php echo esc_attr($row_id); ?>" class="border-b border-gray-100">
									<td class="py-4 text-sm font-medium uppercase"><?php echo esc_html($name . ' × ' . $qty); ?></td>
									<td class="py-4 font-bold">
										<div class="flex items-center justify-end gap-3">
											<span class="rcp-line-price"><?php echo wp_kses_post($line); ?></span>
											<a class="rcp-remove-item" href="<?php echo esc_url($remove_url); ?>" aria-label="<?php echo esc_attr__('Eliminar producto', 'red-cultural-pages'); ?>">×</a>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>

							<tr id="red-cultural-checkout-subtotal-row" class="border-b border-gray-100">
								<th id="red-cultural-checkout-subtotal-label" class="py-4"><?php echo esc_html__('Subtotal', 'red-cultural-pages'); ?></th>
								<td id="red-cultural-checkout-subtotal-value" class="py-4 font-bold"><?php echo wp_kses_post($cart->get_cart_subtotal()); ?></td>
							</tr>

							<?php if ($has_physical && $cart->needs_shipping()) : ?>
								<tr id="red-cultural-checkout-shipping-row" class="border-b border-gray-100">
									<th id="red-cultural-checkout-shipping-label" class="py-4"><?php echo esc_html__('Envío', 'red-cultural-pages'); ?></th>
									<td id="red-cultural-checkout-shipping-value" class="py-4 text-xs"><?php echo wp_kses_post(WC()->cart->get_cart_shipping_total()); ?></td>
								</tr>
							<?php endif; ?>

							<tr id="red-cultural-checkout-total-row">
								<th id="red-cultural-checkout-total-label" class="py-6 text-xl font-black"><?php echo esc_html__('TOTAL', 'red-cultural-pages'); ?></th>
								<td id="red-cultural-checkout-total-value" class="py-6 text-2xl font-black"><?php echo wp_kses_post($cart->get_total()); ?></td>
							</tr>
						</tbody>
					</table>

					<div id="red-cultural-checkout-payment" class="payment-methods">
						<h3 id="red-cultural-checkout-payment-title" class="label-text mb-4"><?php echo esc_html__('Método de Pago', 'red-cultural-pages'); ?></h3>
						<?php
						$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
						$chosen = '';
						if (WC()->session) {
							$chosen = (string) WC()->session->get('chosen_payment_method');
						}
						if ($chosen === '' && !empty($available_gateways)) {
							$chosen = (string) array_key_first($available_gateways);
						}
						?>

						<div id="red-cultural-checkout-payment-methods" class="space-y-4 mb-8">
							<?php foreach ($available_gateways as $gateway_id => $gateway) : ?>
								<?php
								if (!$gateway) {
									continue;
								}
								$title = method_exists($gateway, 'get_title') ? (string) $gateway->get_title() : (string) $gateway_id;
								$input_id = 'payment_method_' . sanitize_title((string) $gateway_id);
								?>
								<div id="<?php echo esc_attr('red-cultural-checkout-gateway-' . sanitize_title((string) $gateway_id)); ?>" class="flex items-center gap-3">
									<input
										type="radio"
										id="<?php echo esc_attr($input_id); ?>"
										name="payment_method"
										value="<?php echo esc_attr((string) $gateway_id); ?>"
										<?php checked($chosen, (string) $gateway_id); ?>
									/>
									<label for="<?php echo esc_attr($input_id); ?>" class="text-sm font-bold uppercase cursor-pointer">
										<?php echo esc_html($title); ?>
									</label>
								</div>
							<?php endforeach; ?>
						</div>

						<?php
						// Terms + nonce required for checkout processing.
						do_action('woocommerce_checkout_terms_and_conditions');
						?>

						<button
							id="red-cultural-checkout-place-order"
							type="submit"
							class="w-full bg-black hover:bg-gray-800 text-white font-bold py-5 px-4 transition-all uppercase tracking-widest text-sm"
							name="woocommerce_checkout_place_order"
							value="<?php echo esc_attr__('Confirmar Compra', 'red-cultural-pages'); ?>"
							<?php echo is_user_logged_in() ? '' : 'disabled'; ?>
						>
							<?php echo esc_html__('Confirmar Compra', 'red-cultural-pages'); ?>
						</button>

						<?php
						wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce');
						?>
					</div>

					<div id="red-cultural-checkout-terms" class="mt-8 pt-6 border-t border-gray-100">
						<p id="red-cultural-checkout-terms-text" class="text-[9px] leading-relaxed text-gray-500 uppercase text-center tracking-tighter">
							<?php echo esc_html__('Al confirmar, aceptas nuestros términos y condiciones y política de privacidad.', 'red-cultural-pages'); ?>
						</p>
					</div>
				</div>
			</form>

			<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
		<?php endif; ?>
	</div>

	<?php if ($rcp_theme_footer_html !== '') : ?>
		<?php echo $rcp_theme_footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php endif; ?>

	<script>
		(function () {
			var inline = document.getElementById('red-cultural-checkout-auth-inline');
			if (!inline) return;

			var viewLogin = document.getElementById('red-cultural-checkout-auth-view-login');
			var viewRegister = document.getElementById('red-cultural-checkout-auth-view-register');
			var viewForgot = document.getElementById('red-cultural-checkout-auth-view-forgot');

			var showRegister = document.getElementById('red-cultural-checkout-auth-show-register');
			var showLogin = document.getElementById('red-cultural-checkout-auth-show-login');
				var showLogin2 = document.getElementById('red-cultural-checkout-auth-show-login-2');
				var forgotLink = document.getElementById('red-cultural-checkout-auth-forgot-link');

			var loginSubmit = document.getElementById('red-cultural-checkout-auth-login-submit');
			var registerSubmit = document.getElementById('red-cultural-checkout-auth-register-submit');
			var forgotSubmit = document.getElementById('red-cultural-checkout-auth-forgot-submit');

			function setView(view) {
				if (viewLogin) viewLogin.classList.toggle('hidden', view !== 'login');
				if (viewRegister) viewRegister.classList.toggle('hidden', view !== 'register');
				if (viewForgot) viewForgot.classList.toggle('hidden', view !== 'forgot');
			}

			function submitAuth(mode, payload) {
				var actionUrl = inline.getAttribute('data-admin-post');
				var nonce = inline.getAttribute('data-nonce');
				var redirectTo = inline.getAttribute('data-redirect');
				if (!actionUrl || !nonce || !redirectTo) return;

				var form = document.createElement('form');
				form.method = 'POST';
				form.action = actionUrl;

				function add(name, value) {
					var input = document.createElement('input');
					input.type = 'hidden';
					input.name = name;
					input.value = value;
					form.appendChild(input);
				}

				add('action', 'rcp_checkout_auth');
				add('rcp_nonce', nonce);
				add('redirect_to', redirectTo);
				add('mode', mode);

				Object.keys(payload || {}).forEach(function (k) {
					add(k, payload[k]);
				});

				document.body.appendChild(form);
				form.submit();
			}

			function setStatus(el, message) {
				if (!el) return;
				if (!message) {
					el.textContent = '';
					el.classList.add('hidden');
					return;
				}
				el.textContent = message;
				el.classList.remove('hidden');
			}

			var ajaxUrl = inline.getAttribute('data-ajax-url');
			var existsNonce = inline.getAttribute('data-exists-nonce');
			var loginInput = document.getElementById('red-cultural-checkout-auth-login');
			var loginStatus = document.getElementById('red-cultural-checkout-auth-login-status');
			var registerEmail = document.getElementById('red-cultural-checkout-auth-email');
			var registerStatus = document.getElementById('red-cultural-checkout-auth-register-status');

			var checkTimer = null;
			function scheduleCheck(kind) {
				if (!ajaxUrl || !existsNonce) return;
				if (checkTimer) window.clearTimeout(checkTimer);
				checkTimer = window.setTimeout(function () {
					var value = '';
					if (kind === 'login' && loginInput) value = String(loginInput.value || '').trim();
					if (kind === 'register' && registerEmail) value = String(registerEmail.value || '').trim();
					if (!value || value.length < 3) {
						if (kind === 'login') setStatus(loginStatus, '');
						if (kind === 'register') setStatus(registerStatus, '');
						return;
					}

					var data = new FormData();
					data.append('action', 'rcp_user_exists');
					data.append('nonce', existsNonce);
					data.append('user_login', value);

					fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data })
						.then(function (r) { return r.json(); })
						.then(function (json) {
							var exists = !!(json && json.success && json.data && json.data.exists);
							if (kind === 'login') {
								// Login mode: if account does NOT exist, show error.
								setStatus(loginStatus, exists ? '' : 'Esta cuenta NO existe');
							} else if (kind === 'register') {
								// Register mode: if account exists, show error.
								setStatus(registerStatus, exists ? 'Esta cuenta ya existe' : '');
							}
						})
						.catch(function () {
							// Fail silently.
						});
				}, 450);
			}

			if (loginInput) {
				loginInput.addEventListener('input', function () { scheduleCheck('login'); });
				loginInput.addEventListener('blur', function () { scheduleCheck('login'); });
			}
			if (registerEmail) {
				registerEmail.addEventListener('input', function () { scheduleCheck('register'); });
				registerEmail.addEventListener('blur', function () { scheduleCheck('register'); });
			}

			if (showRegister) showRegister.addEventListener('click', function () { setView('register'); });
			if (showLogin) showLogin.addEventListener('click', function () { setView('login'); });
			if (showLogin2) showLogin2.addEventListener('click', function () { setView('login'); });
			if (forgotLink) forgotLink.addEventListener('click', function () { setView('forgot'); });

			if (loginSubmit) loginSubmit.addEventListener('click', function () {
				var login = document.getElementById('red-cultural-checkout-auth-login');
				var pass = document.getElementById('red-cultural-checkout-auth-password');
				submitAuth('login', {
					user_login: login ? login.value : '',
					password: pass ? pass.value : ''
				});
			});

			if (registerSubmit) registerSubmit.addEventListener('click', function () {
				var firstName = document.getElementById('red-cultural-checkout-auth-first-name');
				var lastName = document.getElementById('red-cultural-checkout-auth-last-name');
				var email = document.getElementById('red-cultural-checkout-auth-email');
				var pass = document.getElementById('red-cultural-checkout-auth-register-password');
				submitAuth('register', {
					first_name: firstName ? firstName.value : '',
					last_name: lastName ? lastName.value : '',
					email: email ? email.value : '',
					password: pass ? pass.value : ''
				});
			});

			if (forgotSubmit) forgotSubmit.addEventListener('click', function () {
				var email = document.getElementById('red-cultural-checkout-auth-forgot-email');
				submitAuth('forgot', {
					user_login: email ? email.value : ''
				});
			});

			// Default view.
			setView('login');
		})();
	</script>

	<script>
		(function () {
			var toggle = document.getElementById('red-cultural-checkout-ship-toggle-input');
			var switcher = document.getElementById('red-cultural-checkout-address-switcher');
			var titleText = document.getElementById('red-cultural-checkout-address-title-text');
			if (!toggle || !switcher) return;

			function sync() {
				var on = !!toggle.checked;
				switcher.classList.toggle('is-alt', on);
				if (titleText) {
					titleText.textContent = on ? 'DIRECCIÓN DE ENTREGA' : 'DIRECCIÓN DE FACTURACIÓN';
				}

				var reqIds = ['shipping_first_name', 'shipping_last_name', 'shipping_phone', 'shipping_address_1', 'shipping_city'];
				reqIds.forEach(function (id) {
					var el = document.getElementById(id);
					if (!el) return;
					if (on) el.setAttribute('required', 'required');
					else el.removeAttribute('required');
				});
			}

			toggle.addEventListener('change', sync);
			sync();
		})();
	</script>

	<?php wp_footer(); ?>
</body>
</html>
