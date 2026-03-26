<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

global $post;
if (!($post instanceof WP_Post)) {
	exit;
}

function rcp_blog_post_primary_category_name(int $post_id): string {
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

function rcp_blog_post_lead_text(WP_Post $post, int $words = 38): string {
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

$featured_img = get_the_post_thumbnail_url($post, 'large');
$author_name = (string) get_the_author_meta('display_name', (int) $post->post_author);
$date_display = (string) get_the_date('j \\d\\e F, Y', $post);
$lead_text = rcp_blog_post_lead_text($post, 40);

$post_id = (int) $post->ID;
$related_category_ids = wp_get_post_terms($post_id, 'category', array('fields' => 'ids'));
$related_tag_ids = wp_get_post_terms($post_id, 'post_tag', array('fields' => 'ids'));
$related_category_ids = is_array($related_category_ids) ? array_values(array_filter(array_map('intval', $related_category_ids))) : array();
$related_tag_ids = is_array($related_tag_ids) ? array_values(array_filter(array_map('intval', $related_tag_ids))) : array();

$related_tax_query = array();
if ($related_category_ids !== array() || $related_tag_ids !== array()) {
	$related_tax_query = array('relation' => 'OR');
	if ($related_category_ids !== array()) {
		$related_tax_query[] = array(
			'taxonomy' => 'category',
			'field' => 'term_id',
			'terms' => $related_category_ids,
		);
	}
	if ($related_tag_ids !== array()) {
		$related_tax_query[] = array(
			'taxonomy' => 'post_tag',
			'field' => 'term_id',
			'terms' => $related_tag_ids,
		);
	}
}

$other_posts = get_posts(
	array_filter(
		array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'posts_per_page' => 4,
			'post__not_in' => array($post_id),
			'ignore_sticky_posts' => true,
			'no_found_rows' => true,
			'orderby' => 'rand',
			'tax_query' => $related_tax_query !== array() ? $related_tax_query : null,
		),
		static fn($value): bool => $value !== null
	)
);

// If there aren't enough related posts, fill the remaining slots with random posts.
if (count($other_posts) < 4) {
	$exclude_ids = array($post_id);
	foreach ($other_posts as $p) {
		if ($p instanceof WP_Post) {
			$exclude_ids[] = (int) $p->ID;
		}
	}

	$fallback_posts = get_posts(
		array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'posts_per_page' => 4 - count($other_posts),
			'post__not_in' => $exclude_ids,
			'ignore_sticky_posts' => true,
			'no_found_rows' => true,
			'orderby' => 'rand',
		)
	);

	if (is_array($fallback_posts) && $fallback_posts !== array()) {
		$other_posts = array_merge($other_posts, $fallback_posts);
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
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
	<?php wp_head(); ?>
	<style>
		#red-cultural-blog-post-root{
			font-family:'Garamond','EB Garamond','Baskerville','Georgia',serif;
			background-color:#f9f9f9;
			color:#111827;
			padding-top:48px;
		}
		#red-cultural-blog-post-root .header-font{
			font-family:'Inter',system-ui,-apple-system,sans-serif;
		}
		#red-cultural-blog-post-root .max-container{max-width:1180px;margin:0 auto;}
		#red-cultural-blog-post-root .content-text{font-size:24px;line-height:1.6;}
		#red-cultural-blog-post-root .article-card:hover img{transform:scale(1.05);}
		#red-cultural-blog-post-root .rcp-article-body p{margin-bottom:32px;}
		#red-cultural-blog-post-root .rcp-article-body h2,
		#red-cultural-blog-post-root .rcp-article-body h3{font-family:'Inter',system-ui,-apple-system,sans-serif;letter-spacing:-0.01em;}
		#red-cultural-blog-post-root .rcp-article-body a{color:#111827;text-decoration:underline;text-underline-offset:2px;}
	</style>
</head>
<body <?php body_class(); ?>>
	<?php if (function_exists('wp_body_open')) { wp_body_open(); } ?>

	<?php
	if ($rcp_theme_header_html !== '') {
		echo $rcp_theme_header_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<div id="red-cultural-blog-post-root" class="text-gray-900">
		<main class="max-container px-[30px]">
			<!-- Hero Section -->
			<div class="flex flex-col md:flex-row gap-8 mb-12">
				<!-- Featured Image -->
				<div class="w-full md:w-1/2">
					<div class="bg-gray-200 aspect-[4/3] rounded-sm overflow-hidden flex items-center justify-center border border-gray-300 relative">
						<?php if (is_string($featured_img) && $featured_img !== '') : ?>
							<img
								src="<?php echo esc_url($featured_img); ?>"
								alt="<?php echo esc_attr((string) get_the_title($post)); ?>"
								class="absolute inset-0 w-full h-full object-cover"
								decoding="async"
								loading="eager"
							/>
						<?php else : ?>
							<svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
							</svg>
						<?php endif; ?>
						<span class="absolute bottom-4 left-4 bg-black/50 text-white text-xs px-2 py-1 uppercase tracking-widest">
							<?php echo esc_html__('Imagen Destacada', 'red-cultural-pages'); ?>
						</span>
					</div>
				</div>

				<!-- Title & Snippet -->
				<div class="w-full md:w-1/2 flex flex-col justify-center">
					<h1 class="header-font text-4xl md:text-5xl font-black uppercase leading-tight mb-4 text-black">
						<?php echo esc_html((string) get_the_title($post)); ?>
					</h1>
					<p class="header-font text-sm text-gray-500 mb-6 uppercase tracking-wider">
						<?php echo esc_html__('por', 'red-cultural-pages'); ?>
						<span class="text-black font-semibold"><?php echo esc_html($author_name !== '' ? $author_name : 'Red Cultural'); ?></span>
						<?php echo esc_html(' | ' . $date_display); ?>
					</p>
					<div class="content-text italic text-gray-700 border-l-4 border-gray-900 pl-6 py-2">
						<?php echo esc_html($lead_text); ?>
					</div>
				</div>
			</div>

			<!-- Full Content -->
			<article class="max-w-[700px] mx-auto mb-20 content-text">
				<div class="rcp-article-body text-gray-800">
					<?php
					echo apply_filters('the_content', (string) $post->post_content); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</div>
				<div class="h-px bg-gray-200 w-full mb-12"></div>
			</article>

			<!-- Otros Artículos Grid -->
			<section class="mb-24">
				<h2 class="header-font text-2xl font-bold uppercase tracking-widest mb-10 text-center text-gray-900">
					<?php echo esc_html__('Otros Artículos', 'red-cultural-pages'); ?>
				</h2>
				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
					<?php foreach ($other_posts as $other_post) : ?>
						<?php
						if (!($other_post instanceof WP_Post)) {
							continue;
						}
						$other_img = get_the_post_thumbnail_url($other_post, 'medium_large');
						$other_cat = rcp_blog_post_primary_category_name((int) $other_post->ID);
						?>
						<a href="<?php echo esc_url((string) get_permalink($other_post)); ?>" class="article-card group block no-underline">
							<div class="bg-gray-200 aspect-video mb-4 overflow-hidden rounded-[6px] border border-gray-200 relative">
								<?php if (is_string($other_img) && $other_img !== '') : ?>
									<img
										src="<?php echo esc_url($other_img); ?>"
										alt="<?php echo esc_attr((string) get_the_title($other_post)); ?>"
										class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110 rounded-[6px]"
										decoding="async"
										loading="lazy"
									/>
								<?php else : ?>
									<div class="w-full h-full bg-slate-300 flex items-center justify-center transition-transform duration-500 group-hover:scale-110 rounded-[6px]"></div>
								<?php endif; ?>
								<?php if ($other_cat !== '') : ?>
									<span class="header-font text-[10px] text-gray-700 font-bold uppercase tracking-widest bg-white/80 px-2 py-1 absolute left-3 bottom-3">
										<?php echo esc_html($other_cat); ?>
									</span>
								<?php endif; ?>
							</div>
							<h3 class="header-font font-bold text-sm uppercase leading-tight group-hover:text-gray-600 transition-colors">
								<?php echo esc_html((string) get_the_title($other_post)); ?>
							</h3>
						</a>
					<?php endforeach; ?>
				</div>
			</section>

			<!-- Comments Section -->
			<section class="max-w-2xl mx-auto pb-24" id="comments">
				<h2 class="header-font text-xl font-bold uppercase tracking-widest mb-8 text-black border-b-2 border-black inline-block pb-1">
					<?php echo esc_html__('Comentarios', 'red-cultural-pages'); ?>
				</h2>

				<?php if (post_password_required($post)) : ?>
					<p class="header-font text-sm text-gray-500"><?php echo esc_html__('Este contenido está protegido por contraseña.', 'red-cultural-pages'); ?></p>
				<?php else : ?>
					<?php if (comments_open($post) || get_comments_number($post) > 0) : ?>
						<div class="mb-12">
							<?php
							comment_form(
								array(
									'title_reply' => '',
									'label_submit' => __('Enviar Comentario', 'red-cultural-pages'),
									'class_form' => 'mb-12',
									'class_submit' => 'bg-black text-white px-8 py-3 header-font text-[10px] uppercase tracking-[0.2em] font-bold hover:bg-gray-800 transition-all rounded-[6px]',
									'comment_field' => '<textarea id="comment" name="comment" class="w-full p-4 border border-gray-300 rounded-sm focus:outline-none focus:ring-1 focus:ring-black mb-4 h-32 text-sm header-font bg-white" placeholder="' . esc_attr__('Escribe tu comentario...', 'red-cultural-pages') . '" required></textarea>',
									'must_log_in' => '<p class="header-font text-sm text-gray-500 mb-4">' . wp_kses_post(sprintf(__('Debes <a href="%s">iniciar sesión</a> para comentar.', 'red-cultural-pages'), esc_url(wp_login_url((string) get_permalink($post))))) . '</p>',
									'logged_in_as' => '<p class="header-font text-sm text-gray-500 mb-4">' . wp_kses_post(sprintf(__('Conectado como %1$s. <a href="%2$s">Cerrar sesión</a>', 'red-cultural-pages'), esc_html(wp_get_current_user()->display_name), esc_url(wp_logout_url((string) get_permalink($post))))) . '</p>',
									'comment_notes_before' => '',
									'comment_notes_after' => '',
								)
							);
							?>
						</div>

						<?php if (have_comments()) : ?>
							<div class="space-y-8">
								<?php
								wp_list_comments(
									array(
										'style' => 'div',
										'avatar_size' => 0,
										'short_ping' => true,
										'callback' => static function ($comment, $args, $depth): void {
											if (!($comment instanceof WP_Comment)) {
												return;
											}
											$author = get_comment_author($comment);
											$date = get_comment_date('', $comment);
											$text = get_comment_text($comment);
											?>
											<div class="border-b border-gray-100 pb-8">
												<div class="flex justify-between items-center mb-3">
													<span class="header-font font-bold text-xs uppercase tracking-widest"><?php echo esc_html((string) $author); ?></span>
													<span class="header-font text-[10px] text-gray-400 uppercase"><?php echo esc_html((string) $date); ?></span>
												</div>
												<p class="text-lg text-gray-700 leading-relaxed italic"><?php echo wp_kses_post($text); ?></p>
											</div>
											<?php
										},
									)
								);
								?>
							</div>
						<?php endif; ?>
					<?php endif; ?>
				<?php endif; ?>
			</section>
		</main>
	</div>

	<?php
	if ($rcp_theme_footer_html !== '') {
		echo $rcp_theme_footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<?php wp_footer(); ?>
</body>
</html>
