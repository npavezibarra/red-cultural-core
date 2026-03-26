<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Artículos (Blog) — custom landing template.
 *
 * Uses the active block theme header/footer template-parts for consistent navbar/footer,
 * while rendering a modern newspaper-style layout populated with WordPress posts.
 */

function rcp_articulos_primary_category_name(int $post_id): string {
	$categories = get_the_category($post_id);
	if (!is_array($categories) || count($categories) === 0) {
		return '';
	}

	$first = $categories[0];
	if (!($first instanceof WP_Term)) {
		return '';
	}

	return (string) $first->name;
}

function rcp_articulos_estimated_read_minutes(int $post_id): int {
	$content = (string) get_post_field('post_content', $post_id);
	$content = wp_strip_all_tags((string) strip_shortcodes($content));
	$word_count = str_word_count($content);
	$minutes = (int) ceil(max(1, $word_count) / 200);

	return max(1, $minutes);
}

function rcp_articulos_post_excerpt(WP_Post $post, int $words = 20): string {
	$excerpt = (string) $post->post_excerpt;
	if ($excerpt !== '') {
		return wp_trim_words($excerpt, $words, '…');
	}

	$content = (string) $post->post_content;
	$content = wp_strip_all_tags((string) strip_shortcodes($content));

	return wp_trim_words($content, $words, '…');
}

// Pre-render block theme template parts BEFORE wp_head so their assets are enqueued in the correct place.
$rcp_theme_header_html = '';
$rcp_theme_footer_html = '';
if (function_exists('do_blocks')) {
	$rcp_theme_header_html = (string) do_blocks('<!-- wp:template-part {"slug":"header","area":"header"} /-->');
	$rcp_theme_footer_html = (string) do_blocks('<!-- wp:template-part {"slug":"footer","area":"footer"} /-->');
}

$posts = get_posts(
	array(
		'post_type' => 'post',
		'post_status' => 'publish',
		'posts_per_page' => 10,
		'orderby' => 'rand',
		'ignore_sticky_posts' => true,
		'no_found_rows' => true,
	)
);

$feature_post = $posts[0] ?? null;
$left_posts = array_slice($posts, 1, 3);
$right_posts = array_slice($posts, 4, 2);
$dispatch_posts = array_slice($posts, 6, 4);

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
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
	<?php wp_head(); ?>
	<style>
		#red-cultural-articulos-root{
			font-family:'Inter',sans-serif;
			background-color:#f8f8f8;
			color:#1a1a1a;
		}
		#red-cultural-articulos-root h1,
		#red-cultural-articulos-root h2,
		#red-cultural-articulos-root h3,
		#red-cultural-articulos-root h4,
		#red-cultural-articulos-root .serif{
			font-family:'Inter',sans-serif;
			letter-spacing:-0.02em;
			font-weight:600;
		}
		#red-cultural-articulos-root .newspaper-border-double{border-bottom:4px double #1a1a1a;}
		#red-cultural-articulos-root .newspaper-border-single{border-bottom:1px solid #1a1a1a;}
		#red-cultural-articulos-root .vertical-rule{border-left:1px solid #e5e7eb;}
		#red-cultural-articulos-root .drop-cap::first-letter{
			font-family:'Inter',sans-serif;
			float:left;
			font-size:4.5rem;
			line-height:3.5rem;
			padding-right:0.75rem;
			font-weight:600;
			color:#000;
		}
		main#front-page-main{
			max-width:1180px;
			padding:30px 0;
			margin-left:auto;
			margin-right:auto;
		}
		#red-cultural-articulos-topbar{
			max-width:var(--wp--style--global--wide-size);
			padding:20px 0px 0px;
			margin-left:auto;
			margin-right:auto;
		}
		#red-cultural-articulos-search-wrap{z-index:20}
		#red-cultural-articulos-search-results{box-shadow:0 10px 30px rgba(0,0,0,.10)}
		@media (max-width: 1240px) {
			#red-cultural-articulos-root {
				padding: 30px;
			}
		}
		@media (max-width: 767px){
			#red-cultural-articulos-root .vertical-rule{border-left:none !important; padding-left:0 !important;}
			#red-cultural-articulos-search-wrap{margin-bottom:12px}
		}
	</style>
</head>
<body <?php body_class(); ?>>
	<?php if (function_exists('wp_body_open')) { wp_body_open(); } ?>

	<?php
	// Render the active block theme header so navbar matches the rest of the site.
	if ($rcp_theme_header_html !== '') {
		echo $rcp_theme_header_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<div id="red-cultural-articulos-root">
		<!-- Page title + Search (top-right) -->
		<div id="red-cultural-articulos-topbar" class="flex items-end justify-between gap-6">
			<h1 id="red-cultural-articulos-title" class="text-3xl font-bold text-gray-900 mb-2">Artículos</h1>
			<div id="red-cultural-articulos-search-wrap" class="w-[340px] max-w-[80vw]">
				<div class="relative">
					<input
						id="red-cultural-articulos-search-input"
						type="search"
						placeholder="<?php echo esc_attr__('Buscar artículos…', 'red-cultural-pages'); ?>"
						class="w-full text-sm bg-white border border-stone-200 rounded-sm px-4 py-3 focus:outline-none focus:ring-1 focus:ring-black"
						autocomplete="off"
						aria-label="<?php echo esc_attr__('Buscar artículos', 'red-cultural-pages'); ?>"
					/>
					<div
						id="red-cultural-articulos-search-results"
						class="hidden absolute left-0 right-0 mt-2 bg-white border border-stone-200 rounded-sm overflow-hidden"
						role="listbox"
						aria-label="<?php echo esc_attr__('Resultados de búsqueda', 'red-cultural-pages'); ?>"
					></div>
				</div>
			</div>
		</div>

		<!-- SECTION 1: Front Page Hero (Three Column Newspaper Layout) -->
		<main id="front-page-main" class="grid grid-cols-1 md:grid-cols-12 gap-8 mb-20">
			<!-- Left Column: Leading Snippets -->
			<aside id="news-sidebar-left" class="md:col-span-3 space-y-8">
				<?php foreach ($left_posts as $index => $post) : ?>
					<?php
					if (!($post instanceof WP_Post)) {
						continue;
					}

					$category_name = rcp_articulos_primary_category_name((int) $post->ID);
					$tag_class = $index === 0 ? 'text-red-700' : 'text-stone-400';
					?>
					<article id="<?php echo esc_attr('sidebar-article-' . (string) ($index + 1)); ?>" class="<?php echo esc_attr($index < 2 ? 'border-b border-stone-200 pb-6' : ''); ?> group cursor-pointer">
						<a href="<?php echo esc_url((string) get_permalink($post)); ?>" class="block no-underline">
							<span id="<?php echo esc_attr('sidebar-tag-' . (string) ($index + 1)); ?>" class="<?php echo esc_attr('text-[9px] uppercase font-semibold mb-2 block tracking-widest ' . $tag_class); ?>">
								<?php echo esc_html($category_name !== '' ? $category_name : 'Artículo'); ?>
							</span>
							<h3 id="<?php echo esc_attr('sidebar-title-' . (string) ($index + 1)); ?>" class="text-xl font-semibold leading-tight group-hover:text-stone-600 transition">
								<?php echo esc_html((string) get_the_title($post)); ?>
							</h3>
							<p id="<?php echo esc_attr('sidebar-excerpt-' . (string) ($index + 1)); ?>" class="text-sm text-stone-500 mt-2 leading-relaxed">
								<?php echo esc_html(rcp_articulos_post_excerpt($post, 18)); ?>
							</p>
						</a>
					</article>
				<?php endforeach; ?>
			</aside>

			<!-- Center Column: Main Headline -->
			<section id="main-feature-center" class="md:col-span-6 vertical-rule px-0 md:px-8">
				<?php if ($feature_post instanceof WP_Post) : ?>
					<?php
					$feature_img = get_the_post_thumbnail_url($feature_post, 'large');
					$feature_author = get_the_author_meta('display_name', (int) $feature_post->post_author);
					$feature_read_minutes = rcp_articulos_estimated_read_minutes((int) $feature_post->ID);
					?>
					<article id="feature-article">
						<a href="<?php echo esc_url((string) get_permalink($feature_post)); ?>" class="block no-underline group">
							<div id="feature-img-wrapper" class="bg-stone-200 aspect-[16/9] mb-8 overflow-hidden relative rounded-sm group-hover:opacity-90 transition">
								<?php if (is_string($feature_img) && $feature_img !== '') : ?>
									<img
										src="<?php echo esc_url($feature_img); ?>"
										alt="<?php echo esc_attr((string) get_the_title($feature_post)); ?>"
										class="absolute inset-0 w-full h-full object-cover"
										decoding="async"
										loading="eager"
									/>
								<?php else : ?>
									<div id="feature-img-placeholder" class="absolute inset-0 flex items-center justify-center text-[10px] uppercase tracking-widest text-stone-400 font-semibold">
										<?php echo esc_html__('Sin imagen destacada', 'red-cultural-pages'); ?>
									</div>
								<?php endif; ?>
							</div>

							<h2 id="feature-title" class="text-6xl font-semibold leading-[0.95] mb-8 tracking-tighter group-hover:text-stone-600 transition">
								<?php echo esc_html((string) get_the_title($feature_post)); ?>
							</h2>
						</a>

						<div id="feature-meta-author" class="text-[10px] uppercase tracking-[0.2em] font-semibold mb-6 flex items-center justify-between border-y border-stone-200 py-3">
							<span><?php echo esc_html(sprintf('Por %s', $feature_author !== '' ? $feature_author : 'Red Cultural')); ?></span>
							<span class="text-stone-400"><?php echo esc_html(sprintf('Lectura: %02d Min', $feature_read_minutes)); ?></span>
						</div>

						<p id="feature-body-1" class="drop-cap text-lg leading-relaxed text-stone-800 mb-6 font-normal">
							<?php echo esc_html(rcp_articulos_post_excerpt($feature_post, 44)); ?>
						</p>

						<a id="feature-read-more" href="<?php echo esc_url((string) get_permalink($feature_post)); ?>" class="inline-block bg-black text-white px-8 py-4 text-[10px] uppercase tracking-[0.3em] font-semibold hover:bg-stone-800 transition-all rounded-sm">
							<?php echo esc_html__('Continuar leyendo', 'red-cultural-pages'); ?>
						</a>
					</article>
				<?php endif; ?>
			</section>

			<!-- Right Column: Vertical Feature -->
			<aside id="news-sidebar-right" class="md:col-span-3 vertical-rule pl-8 space-y-10">
				<?php foreach ($right_posts as $index => $post) : ?>
					<?php
					if (!($post instanceof WP_Post)) {
						continue;
					}
					$img_url = get_the_post_thumbnail_url($post, 'medium_large');
					?>
					<article id="<?php echo esc_attr('side-feature-' . (string) ($index + 1)); ?>" class="group cursor-pointer">
						<a href="<?php echo esc_url((string) get_permalink($post)); ?>" class="block no-underline">
							<?php if ($index === 0) : ?>
								<div id="<?php echo esc_attr('side-feature-img-' . (string) ($index + 1)); ?>" class="aspect-square bg-stone-200 mb-4 rounded-sm overflow-hidden relative">
									<?php if (is_string($img_url) && $img_url !== '') : ?>
										<img
											src="<?php echo esc_url($img_url); ?>"
											alt="<?php echo esc_attr((string) get_the_title($post)); ?>"
											class="absolute inset-0 w-full h-full object-cover"
											decoding="async"
											loading="lazy"
										/>
									<?php endif; ?>
								</div>
								<h3 id="<?php echo esc_attr('side-feature-title-' . (string) ($index + 1)); ?>" class="text-2xl font-semibold leading-tight mb-2 group-hover:text-stone-600 transition">
									<?php echo esc_html((string) get_the_title($post)); ?>
								</h3>
								<p id="<?php echo esc_attr('side-feature-text-' . (string) ($index + 1)); ?>" class="text-sm text-stone-500 leading-snug">
									<?php echo esc_html(rcp_articulos_post_excerpt($post, 18)); ?>
								</p>
							<?php else : ?>
								<h3 id="<?php echo esc_attr('side-feature-title-' . (string) ($index + 1)); ?>" class="text-xl font-semibold leading-tight mb-2 group-hover:text-stone-600 transition">
									<?php echo esc_html((string) get_the_title($post)); ?>
								</h3>
								<p id="<?php echo esc_attr('side-feature-text-' . (string) ($index + 1)); ?>" class="text-sm text-stone-500 leading-snug">
									<?php echo esc_html(rcp_articulos_post_excerpt($post, 20)); ?>
								</p>
								<span id="<?php echo esc_attr('side-feature-link-' . (string) ($index + 1)); ?>" class="text-[9px] font-semibold uppercase tracking-widest mt-4 block border-b-2 border-black w-fit pb-1">
									<?php echo esc_html__('Ver artículo', 'red-cultural-pages'); ?>
								</span>
							<?php endif; ?>
						</a>
					</article>

					<?php if ($index === 0) : ?>
						<div id="separator-right" class="border-t border-stone-200"></div>
					<?php endif; ?>
				<?php endforeach; ?>
			</aside>
		</main>

		<!-- SECTION 2: Secondary Stories (Grid Layout) -->
		<section id="section-global-dispatches" class="bg-white border-y border-stone-200 py-20 mb-20">
			<div id="dispatches-container" class="max-w-7xl mx-auto px-8">
				<h2 id="dispatches-header" class="text-[30px] font-semibold uppercase tracking-[0.25em] mb-10 text-center text-black">
					<?php echo esc_html__('Artículos recientes', 'red-cultural-pages'); ?>
				</h2>

				<div id="dispatches-grid" class="grid grid-cols-1 md:grid-cols-4 gap-8">
					<?php foreach ($dispatch_posts as $index => $post) : ?>
						<?php
						if (!($post instanceof WP_Post)) {
							continue;
						}
						$img_url = get_the_post_thumbnail_url($post, 'medium_large');
						?>
						<article id="<?php echo esc_attr('dispatch-card-' . (string) ($index + 1)); ?>" class="<?php echo esc_attr($index === 0 ? '' : 'vertical-rule pl-12'); ?>">
							<a href="<?php echo esc_url((string) get_permalink($post)); ?>" class="block no-underline">
								<div id="<?php echo esc_attr('dispatch-img-wrapper-' . (string) ($index + 1)); ?>" class="aspect-[3/2] bg-stone-100 mb-6 overflow-hidden relative rounded-sm">
									<?php if (is_string($img_url) && $img_url !== '') : ?>
										<img
											src="<?php echo esc_url($img_url); ?>"
											alt="<?php echo esc_attr((string) get_the_title($post)); ?>"
											class="absolute inset-0 w-full h-full object-cover"
											decoding="async"
											loading="lazy"
										/>
									<?php else : ?>
										<div id="<?php echo esc_attr('dispatch-img-placeholder-' . (string) ($index + 1)); ?>" class="absolute inset-0 flex items-center justify-center text-[8px] uppercase tracking-widest text-stone-400 font-semibold">
											<?php echo esc_html__('Sin imagen', 'red-cultural-pages'); ?>
										</div>
									<?php endif; ?>
								</div>

								<h4 id="<?php echo esc_attr('dispatch-title-' . (string) ($index + 1)); ?>" class="text-xl font-semibold mb-3">
									<?php echo esc_html((string) get_the_title($post)); ?>
								</h4>
								<p id="<?php echo esc_attr('dispatch-text-' . (string) ($index + 1)); ?>" class="text-sm text-stone-500 leading-relaxed font-normal">
									<?php echo esc_html(rcp_articulos_post_excerpt($post, 18)); ?>
								</p>
							</a>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

		<script id="script-main">
			(function(){
				var root = document.getElementById('red-cultural-articulos-root');
				if (!root) return;
				root.querySelectorAll('article').forEach(function(art){
					art.addEventListener('mouseenter', function(){ art.style.opacity = '0.7'; });
					art.addEventListener('mouseleave', function(){ art.style.opacity = '1'; });
				});

				var input = document.getElementById('red-cultural-articulos-search-input');
				var panel = document.getElementById('red-cultural-articulos-search-results');
				var wrap = document.getElementById('red-cultural-articulos-search-wrap');
				if (!input || !panel || !wrap) return;

				var debounceTimer = null;
				var abortController = null;

				function hidePanel(){
					panel.classList.add('hidden');
					panel.innerHTML = '';
				}

				function showPanel(){
					panel.classList.remove('hidden');
				}

				function escapeHtml(str){
					return String(str)
						.replace(/&/g,'&amp;')
						.replace(/</g,'&lt;')
						.replace(/>/g,'&gt;')
						.replace(/\"/g,'&quot;')
						.replace(/'/g,'&#039;');
				}

				function renderResults(items){
					if (!Array.isArray(items) || items.length === 0){
						panel.innerHTML = '<div class="px-4 py-3 text-sm text-stone-500"><?php echo esc_js(__('Sin resultados', 'red-cultural-pages')); ?></div>';
						showPanel();
						return;
					}

					var html = items.map(function(item){
						var url = item && item.link ? item.link : '#';
						var title = item && item.title && item.title.rendered ? item.title.rendered : '';
						var authorName = '';
						var thumb = '';
						try{
							if (item && item._embedded && item._embedded.author && item._embedded.author[0] && item._embedded.author[0].name){
								authorName = item._embedded.author[0].name;
							}
							var media = item && item._embedded && item._embedded['wp:featuredmedia'] && item._embedded['wp:featuredmedia'][0];
							if (media && media.media_details && media.media_details.sizes){
								if (media.media_details.sizes.thumbnail && media.media_details.sizes.thumbnail.source_url){
									thumb = media.media_details.sizes.thumbnail.source_url;
								} else if (media.media_details.sizes.medium && media.media_details.sizes.medium.source_url){
									thumb = media.media_details.sizes.medium.source_url;
								}
							}
						} catch(e){}

						var imgHtml = thumb
							? '<img src="' + escapeHtml(thumb) + '" alt="" class="w-10 h-10 object-cover rounded-sm flex-none" loading="lazy" decoding="async" />'
							: '<div class="w-10 h-10 bg-stone-100 rounded-sm flex-none"></div>';

						return (
							'<a href="' + escapeHtml(url) + '" class="flex items-center gap-3 px-4 py-3 hover:bg-stone-50 transition no-underline">' +
								imgHtml +
								'<div class="min-w-0">' +
									'<div class="text-sm font-semibold text-stone-900 truncate">' + title + '</div>' +
									'<div class="text-[11px] text-stone-500 truncate"><?php echo esc_js(__('Por', 'red-cultural-pages')); ?> ' + escapeHtml(authorName || 'Red Cultural') + '</div>' +
								'</div>' +
							'</a>'
						);
					}).join('');

					panel.innerHTML = html;
					showPanel();
				}

				function search(term){
					if (abortController) abortController.abort();
					abortController = new AbortController();

					var url = '<?php echo esc_js((string) rest_url('wp/v2/posts')); ?>' +
						'?search=' + encodeURIComponent(term) +
						'&per_page=6&_embed=1';

					fetch(url, { signal: abortController.signal, credentials: 'same-origin' })
						.then(function(res){ return res.ok ? res.json() : []; })
						.then(function(data){ renderResults(data); })
						.catch(function(){ /* ignore */ });
				}

				input.addEventListener('input', function(){
					var term = (input.value || '').trim();
					if (debounceTimer) clearTimeout(debounceTimer);
					if (term.length < 2){
						hidePanel();
						return;
					}
					debounceTimer = setTimeout(function(){ search(term); }, 220);
				});

				input.addEventListener('focus', function(){
					var term = (input.value || '').trim();
					if (term.length >= 2 && panel.innerHTML.trim() !== ''){
						showPanel();
					}
				});

				document.addEventListener('click', function(e){
					var t = e.target;
					if (!(t instanceof Element)) return;
					if (!wrap.contains(t)) hidePanel();
				});

				document.addEventListener('keydown', function(e){
					if (e.key === 'Escape') hidePanel();
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
