<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

// Ensure WooCommerce front-end scripts are available for add-to-cart behavior when possible.
if (function_exists('wc_enqueue_scripts')) {
	wc_enqueue_scripts();
}

// Pre-render block theme template parts BEFORE wp_head so their assets are enqueued in the correct place.
$rcp_theme_header_html = '';
$rcp_theme_footer_html = '';
if (function_exists('do_blocks')) {
	$rcp_theme_header_html = (string) do_blocks('<!-- wp:template-part {"slug":"header","area":"header"} /-->');
	$rcp_theme_footer_html = (string) do_blocks('<!-- wp:template-part {"slug":"footer","area":"footer"} /-->');
}

$paged = (int) max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
$per_page = 12;

$results = null;
$products = array();
$total_pages = 1;

if (function_exists('wc_get_products')) {
	$selected_cat_ids = get_option('rcp_shop_category_ids', array());
	$selected_cat_ids = is_array($selected_cat_ids) ? array_values(array_filter(array_map('intval', $selected_cat_ids))) : array();

	$args = array(
		'status' => 'publish',
		'limit' => $per_page,
		'page' => $paged,
		'paginate' => true,
		'orderby' => 'date',
		'order' => 'DESC',
	);
	if ($selected_cat_ids !== array()) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'product_cat',
				'field' => 'term_id',
				'terms' => $selected_cat_ids,
			),
		);
	}

	$results = wc_get_products($args);

	if (is_object($results) && isset($results->products) && is_array($results->products)) {
		$products = $results->products;
		$total = isset($results->total) ? (int) $results->total : 0;
		$total_pages = (int) max(1, (int) ceil($total / $per_page));
	}
}

?><!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html((string) wp_get_document_title()); ?></title>
	<script>
		// Keep the global theme styles intact (avoid Tailwind preflight).
		window.tailwind = window.tailwind || {};
		window.tailwind.config = { corePlugins: { preflight: false } };
	</script>
	<script src="https://cdn.tailwindcss.com"></script>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
	<?php wp_head(); ?>
	<style>
		#red-cultural-shop-root{
			font-family:'Inter',sans-serif;
			background-color:#ffffff;
			color:#000000;
		}
		#red-cultural-shop-root .product-card:hover .add-to-cart{
			opacity:1;
			transform:translateY(0);
		}
		#red-cultural-shop-root .add-to-cart{transition:all 0.3s ease;}
		#red-cultural-shop-root ::-webkit-scrollbar{width:5px;}
		#red-cultural-shop-root ::-webkit-scrollbar-track{background:#f1f1f1;}
		#red-cultural-shop-root ::-webkit-scrollbar-thumb{background:#000;}

		#red-cultural-shp-pagination .page-numbers{
			list-style:none;
			margin:0;
			padding:0;
			display:flex;
			flex-wrap:wrap;
			gap:10px;
			align-items:center;
		}
		#red-cultural-shp-pagination .page-numbers li{margin:0;padding:0}
		#red-cultural-shp-pagination .page-numbers a,
		#red-cultural-shp-pagination .page-numbers span{
			display:inline-flex;
			align-items:center;
			justify-content:center;
			min-width:34px;
			height:34px;
			padding:0 10px;
			border:1px solid #e5e7eb;
			color:#000;
			text-decoration:none;
			font-size:12px;
			font-weight:600;
			letter-spacing:1px;
			text-transform:uppercase;
		}
		#red-cultural-shp-pagination .page-numbers .current{
			background:#000;
			color:#fff;
			border-color:#000;
		}
		#red-cultural-shp-pagination .page-numbers a:hover{border-color:#000}
		a#red-cultural-shp-btn-cursos{
			font-size:12px;
			font-weight:700;
			padding:10px 28px;
			border-radius:6px;
		}
		@media (max-width: 640px){
			#red-cultural-shp-actions{flex-direction:column;align-items:stretch;gap:14px}
			#red-cultural-shp-actions-right{flex-direction:column;align-items:stretch;gap:14px}
			#red-cultural-shp-actions-right #red-cultural-shp-pagination{justify-content:center}
			#red-cultural-shp-actions-right #red-cultural-shp-btn-cursos{width:100%}
		}
	</style>
</head>
<body id="red-cultural-shp-body" <?php body_class('antialiased'); ?>>
	<?php if (function_exists('wp_body_open')) { wp_body_open(); } ?>

	<?php
	if ($rcp_theme_header_html !== '') {
		echo $rcp_theme_header_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<div id="red-cultural-shop-root">
		<main
			id="red-cultural-shp-main"
			class="mx-auto"
			style="max-width: var(--wp--style--global--wide-size); width: 100%; padding: 20px 0px;"
		>
			<div id="red-cultural-shp-actions" class="flex items-end justify-between gap-6 mb-10">
				<h1 id="red-cultural-shp-title" class="text-3xl font-bold text-gray-900 mb-2">Tienda</h1>

				<div id="red-cultural-shp-actions-right" class="flex items-center gap-6">
					<?php if ($total_pages > 1) : ?>
						<div id="red-cultural-shp-pagination" class="flex justify-end">
							<?php
							echo wp_kses_post(
								paginate_links(
									array(
										'current' => $paged,
										'total' => $total_pages,
										'type' => 'list',
									)
								)
							);
							?>
						</div>
					<?php endif; ?>

					<a
						id="red-cultural-shp-btn-cursos"
						href="<?php echo esc_url((string) home_url('/cursos/')); ?>"
						class="inline-flex items-center justify-center bg-black text-white px-8 py-4 text-[10px] uppercase tracking-[0.3em] font-bold hover:bg-gray-800 transition-all no-underline"
					>
						<?php echo esc_html__('Ver Cursos', 'red-cultural-pages'); ?>
					</a>
				</div>
			</div>

			<div id="red-cultural-shp-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-16">
				<?php foreach ($products as $product) : ?>
					<?php
					if (!is_object($product) || !method_exists($product, 'get_id')) {
						continue;
					}

					/** @var WC_Product $product */
					$product_id = (int) $product->get_id();
					$name = (string) $product->get_name();
					$link = (string) $product->get_permalink();
					$price_html = (string) $product->get_price_html();
					$image_id = (int) $product->get_image_id();
					$image_url = $image_id ? (string) wp_get_attachment_image_url($image_id, 'large') : '';
					$category_names = function_exists('wc_get_product_category_list') ? wp_strip_all_tags((string) wc_get_product_category_list($product_id)) : '';
					$category_names = trim((string) preg_replace('/\\s+/', ' ', (string) $category_names));
					$subtitle = $category_names !== '' ? $category_names : '';

					$can_add = $product->is_purchasable() && $product->is_in_stock() && $product->is_type('simple');
					$button_label = $can_add ? __('Agregar al carrito', 'red-cultural-pages') : __('Ver producto', 'red-cultural-pages');
					$button_href = $can_add ? (string) $product->add_to_cart_url() : $link;
					$button_classes = $can_add ? 'add-to-cart add_to_cart_button ajax_add_to_cart' : 'add-to-cart';
					?>
					<div id="<?php echo esc_attr('red-cultural-shp-product-' . (string) $product_id); ?>" class="product-card group cursor-pointer">
						<a id="<?php echo esc_attr('red-cultural-shp-product-link-' . (string) $product_id); ?>" href="<?php echo esc_url($link); ?>" class="block no-underline">
							<div id="<?php echo esc_attr('red-cultural-shp-product-media-' . (string) $product_id); ?>" class="relative overflow-hidden bg-gray-100 aspect-square mb-4 border border-transparent group-hover:border-black transition-all">
								<?php if ($image_url !== '') : ?>
									<img
										id="<?php echo esc_attr('red-cultural-shp-product-img-' . (string) $product_id); ?>"
										src="<?php echo esc_url($image_url); ?>"
										alt="<?php echo esc_attr($name); ?>"
										class="w-full h-full object-cover transition-all duration-500"
										loading="lazy"
										decoding="async"
									/>
								<?php else : ?>
									<div id="<?php echo esc_attr('red-cultural-shp-product-img-empty-' . (string) $product_id); ?>" class="w-full h-full flex items-center justify-center text-xs uppercase tracking-widest text-gray-400">
										<?php echo esc_html__('Sin imagen', 'red-cultural-pages'); ?>
									</div>
								<?php endif; ?>

								<a
									id="<?php echo esc_attr('red-cultural-shp-product-cta-' . (string) $product_id); ?>"
									href="<?php echo esc_url($button_href); ?>"
									class="<?php echo esc_attr($button_classes); ?> absolute bottom-0 left-0 w-full bg-black text-white py-4 uppercase text-xs tracking-widest font-bold opacity-0 transform translate-y-4 transition-all text-center"
									<?php if ($can_add) : ?>
										data-product_id="<?php echo esc_attr((string) $product_id); ?>"
										data-product_sku="<?php echo esc_attr((string) $product->get_sku()); ?>"
										rel="nofollow"
									<?php endif; ?>
								>
									<?php echo esc_html($button_label); ?>
								</a>
							</div>

							<div id="<?php echo esc_attr('red-cultural-shp-product-meta-' . (string) $product_id); ?>" class="flex justify-between items-start">
								<div id="<?php echo esc_attr('red-cultural-shp-product-meta-left-' . (string) $product_id); ?>" class="min-w-0 pr-2">
									<h3 id="<?php echo esc_attr('red-cultural-shp-product-title-' . (string) $product_id); ?>" class="uppercase text-sm font-semibold tracking-wider truncate"><?php echo esc_html($name); ?></h3>
									<?php if ($subtitle !== '') : ?>
										<p id="<?php echo esc_attr('red-cultural-shp-product-subtitle-' . (string) $product_id); ?>" class="text-xs text-gray-500 uppercase mt-1 truncate"><?php echo esc_html($subtitle); ?></p>
									<?php endif; ?>
								</div>
								<span id="<?php echo esc_attr('red-cultural-shp-product-price-' . (string) $product_id); ?>" class="text-sm font-medium whitespace-nowrap"><?php echo wp_kses_post($price_html); ?></span>
							</div>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		</main>

		<script id="red-cultural-shp-script">
			(function(){
				var buttons = document.querySelectorAll('#red-cultural-shop-root .add-to-cart');
				buttons.forEach(function(btn){
					btn.addEventListener('click', function(e){
						// Let WooCommerce handle the click (AJAX or navigation); we only show the toast.
						var card = e.target && e.target.closest ? e.target.closest('.product-card') : null;
						var titleEl = card ? card.querySelector('h3') : null;
						var productName = titleEl ? titleEl.innerText : '';
						if (!productName) return;

						var notification = document.createElement('div');
						notification.id = 'red-cultural-shp-toast';
						notification.className = 'fixed bottom-8 right-8 bg-black text-white px-6 py-4 text-xs font-bold tracking-widest uppercase shadow-2xl z-[100] transform translate-y-10 opacity-0 transition-all duration-300';
						notification.innerText = 'Agregado: ' + productName;
						document.body.appendChild(notification);
						setTimeout(function(){ notification.classList.remove('translate-y-10', 'opacity-0'); }, 10);
						setTimeout(function(){
							notification.classList.add('translate-y-10', 'opacity-0');
							setTimeout(function(){ notification.remove(); }, 300);
						}, 3000);
					});
				});
			})();
		</script>
	</div>

	<?php
	if ($rcp_theme_footer_html !== '') {
		echo $rcp_theme_footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<?php wp_footer(); ?>
</body>
</html>
