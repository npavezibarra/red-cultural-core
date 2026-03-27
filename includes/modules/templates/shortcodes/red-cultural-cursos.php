<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('rcp_red_cultural_cursos_shortcode')) {
	function rcp_get_course_emojis(): array {
		return array('⚛️', '🎨', '🐍', '🤖', '📊', '🔐', '☁️', '📱');
	}

	function rcp_get_course_emoji(int $course_id): string {
		$emojis = rcp_get_course_emojis();
		if ($course_id <= 0) {
			return $emojis[0];
		}

		return $emojis[$course_id % count($emojis)];
	}

	function rcp_format_price_string(string $raw_price): string {
		$raw = trim($raw_price);
		if ($raw === '') {
			return '';
		}

		// If it already includes separators (e.g. "$220,000"), keep it exactly as entered.
		if (preg_match('/[\\,\\.]/', $raw) === 1) {
			return esc_html($raw);
		}

		// Try to detect a currency symbol prefix.
		$symbol = '';
		if (preg_match('/^\\s*([^0-9\\s]+)/u', $raw, $m) === 1) {
			$symbol = trim((string) $m[1]);
		}

		$digits = preg_replace('/[^0-9\\-]/', '', $raw);
		if ($digits === '' || !is_numeric($digits)) {
			return esc_html($raw);
		}

		$value = (int) $digits;
		$formatted = number_format($value, 0, '.', ',');

		if ($symbol === '') {
			$symbol = '$';
		}

		return esc_html($symbol . $formatted);
	}

	function rcp_format_ld_course_price(int $course_id): string {
		if (!function_exists('learndash_get_setting')) {
			return '';
		}

		$price_type = (string) learndash_get_setting($course_id, 'course_price_type');
		$raw_price = (string) learndash_get_setting($course_id, 'course_price');

		// Fallback: some LearnDash installs store settings in the serialized meta array.
		if (trim($raw_price) === '') {
			$meta = get_post_meta($course_id, '_sfwd-courses', true);
			if (is_array($meta)) {
				foreach (array('sfwd-courses_course_price', 'course_price') as $key) {
					if (isset($meta[$key]) && is_scalar($meta[$key]) && trim((string) $meta[$key]) !== '') {
						$raw_price = (string) $meta[$key];
						break;
					}
				}
			}
		}

			if ($price_type === 'open' || $price_type === 'free') {
				$raw_price_trim = trim($raw_price);
				if ($raw_price_trim !== '') {
					return rcp_format_price_string($raw_price_trim);
				}
				return esc_html((string) __('Gratis', 'red-cultural-pages'));
			}

		$raw_price_trim = trim($raw_price);
		if ($raw_price_trim === '') {
			return '';
		}

		return rcp_format_price_string($raw_price_trim);
	}

	function rcp_render_red_cultural_course_card(int $course_id): string {
		$title = get_the_title($course_id);
		$author_id = (int) get_post_field('post_author', $course_id);
		$author_name = $author_id ? get_the_author_meta('display_name', $author_id) : '';

		$thumb = get_the_post_thumbnail_url($course_id, 'medium_large');
		$price = rcp_format_ld_course_price($course_id);

		$course_url = get_permalink($course_id);
		$emoji = rcp_get_course_emoji($course_id);

		ob_start();
		?>
		<a class="course-card bg-white border border-gray-200 flex flex-col no-underline" href="<?php echo esc_url((string) $course_url); ?>">
			<?php if ($thumb) : ?>
				<div class="aspect-video-custom rcp-course-thumb bg-gray-100 relative" style="<?php echo esc_attr('background-image:url(' . esc_url($thumb) . ')'); ?>">
					<span class="sr-only"><?php echo esc_html((string) $title); ?></span>
				</div>
			<?php else : ?>
				<div class="aspect-video-custom bg-blue-100 flex items-center justify-center relative">
					<span class="text-blue-500 text-4xl"><?php echo esc_html($emoji); ?></span>
				</div>
			<?php endif; ?>
			<div class="p-5 flex-grow flex flex-col">
				<h3 class="font-bold text-lg text-gray-900 mb-1 line-clamp-2"><?php echo esc_html((string) $title); ?></h3>
				<?php if ($author_name) : ?>
					<p class="text-xs text-gray-400 mb-2"><?php echo esc_html(sprintf(__('por %s', 'red-cultural-pages'), $author_name)); ?></p>
				<?php else : ?>
					<p class="text-sm text-gray-500 mb-4">&nbsp;</p>
				<?php endif; ?>
				<div class="mt-auto flex items-center justify-between">
					<span class="text-xl font-bold text-gray-900"><?php echo wp_kses_post($price !== '' ? $price : '&nbsp;'); ?></span>
					<span class="text-[10px] font-bold uppercase tracking-widest text-gray-900 transition-opacity hover:opacity-75"><?php echo esc_html__('Ver curso', 'red-cultural-pages'); ?></span>
				</div>
			</div>
		</a>
		<?php

		return (string) ob_get_clean();
	}

	if (!function_exists('rcp_red_cultural_cursos_search_ajax')) {
		function rcp_red_cultural_cursos_search_ajax(): void {
			$nonce = isset($_POST['nonce']) ? sanitize_text_field((string) wp_unslash($_POST['nonce'])) : '';
			if ($nonce === '' || wp_verify_nonce($nonce, 'rcp_red_cultural_cursos_search') === false) {
				wp_send_json_error(
					array(
						'message' => 'Invalid nonce.',
					),
					403
				);
			}

			$post_type = 'sfwd-courses';
			if (!post_type_exists($post_type)) {
				wp_send_json_success(
					array(
						'html' => '',
						'count' => 0,
					)
				);
			}

			$raw_search = isset($_POST['search']) ? sanitize_text_field((string) wp_unslash($_POST['search'])) : '';
			$search = trim($raw_search);

			$limit = isset($_POST['limit']) ? (int) $_POST['limit'] : 0;
			if ($limit < 0) {
				$limit = 0;
			}
			if ($limit > 50) {
				$limit = 50;
			}

			$query_args = array(
				'post_type' => $post_type,
				'post_status' => 'publish',
				'posts_per_page' => ($limit > 0 ? $limit : -1),
				'no_found_rows' => true,
				'ignore_sticky_posts' => true,
			);

			if ($search !== '') {
				$query_args['s'] = $search;
			} else {
				$query_args['orderby'] = 'date';
				$query_args['order'] = 'DESC';
			}

			$q = new \WP_Query($query_args);

			if (!$q->have_posts()) {
				wp_send_json_success(
					array(
						'html' => '<div class="col-span-full text-gray-600">' . esc_html__('No se encontraron cursos.', 'red-cultural-pages') . '</div>',
						'count' => 0,
					)
				);
			}

			$html = '';
			foreach ($q->posts as $post) {
				$course_id = isset($post->ID) ? (int) $post->ID : 0;
				if ($course_id <= 0) {
					continue;
				}
				$html .= rcp_render_red_cultural_course_card($course_id);
			}

			wp_send_json_success(
				array(
					'html' => $html,
					'count' => (int) $q->post_count,
				)
			);
		}

		add_action('wp_ajax_rcp_red_cultural_cursos_search', 'rcp_red_cultural_cursos_search_ajax');
		add_action('wp_ajax_nopriv_rcp_red_cultural_cursos_search', 'rcp_red_cultural_cursos_search_ajax');
	}

	/**
	 * Shortcode: [red-cultural-cursos]
	 */
	function rcp_red_cultural_cursos_shortcode($atts = array()): string {
		$atts = shortcode_atts(
			array(
				'limit' => 0,
				// If > 0, shows that many random courses (overrides limit).
				'random' => 0,
			),
			(array) $atts,
			'red-cultural-cursos'
		);

		$post_type = 'sfwd-courses';
		if (!post_type_exists($post_type)) {
			return '';
		}

		$limit = (int) $atts['limit'];
		if ($limit < 0) {
			$limit = 0;
		}

		$random = (int) $atts['random'];
		if ($random < 0) {
			$random = 0;
		}

		$script_rel_path = 'assets/js/red-cultural-cursos.js';
		$script_file = RC_CORE_PATH . $script_rel_path;
		$script_url = RC_CORE_URL . $script_rel_path;

		if (!wp_script_is('rcp-red-cultural-cursos', 'registered')) {
			wp_register_script(
				'rcp-red-cultural-cursos',
				$script_url,
				array(),
				file_exists($script_file) ? (string) filemtime($script_file) : null,
				true
			);
		}
		wp_enqueue_script('rcp-red-cultural-cursos');

		// Tailwind CDN (in <head>) to match the requested format.
		if (!wp_script_is('rcp-tailwind', 'enqueued') && !wp_script_is('rcp-tailwind', 'done')) {
			wp_enqueue_script('rcp-tailwind', 'https://cdn.tailwindcss.com', array(), null, false);
		}

		$posts_per_page = ($limit > 0 ? $limit : -1);
		$orderby = 'date';
		$order = 'DESC';

		if ($random > 0) {
			$posts_per_page = $random;
			$orderby = 'rand';
			$order = '';
		}

		$query_args = array(
			'post_type' => $post_type,
			'post_status' => 'publish',
			'orderby' => $orderby,
			'order' => $order,
			'posts_per_page' => $posts_per_page,
			'no_found_rows' => true,
			'ignore_sticky_posts' => true,
		);

		$q = new \WP_Query($query_args);
		if (!$q->have_posts()) {
			return '';
		}

		$ajax_url = admin_url('admin-ajax.php');
		$nonce = wp_create_nonce('rcp_red_cultural_cursos_search');
		$search_input_id = function_exists('wp_unique_id') ? wp_unique_id('rcp-course-search-') : ('rcp-course-search-' . uniqid());

		ob_start();
		?>
		<style>
			.course-card{border-radius:9px;overflow:hidden;transition:all .3s cubic-bezier(.4,0,.2,1)}
			.course-card:hover{transform:translateY(-4px);box-shadow:0 10px 25px -5px rgba(0,0,0,.1),0 8px 10px -6px rgba(0,0,0,.1)}
			.aspect-video-custom{aspect-ratio:16/9}
			.rcp-course-thumb{background-size:cover;background-position:center}
			input#<?php echo esc_attr($search_input_id); ?>{font-weight:600;font-size:16px}
			.rcp-red-cultural-cursos.min-h-screen.py-8 { padding-top: 0px; }
		</style>
		<div class="rcp-red-cultural-cursos min-h-screen py-8" data-rcp-cursos data-ajax-url="<?php echo esc_attr((string) $ajax_url); ?>" data-nonce="<?php echo esc_attr((string) $nonce); ?>" data-limit="<?php echo esc_attr((string) $limit); ?>">
			<div class="max-w-7xl mx-auto">
				<header class="mb-10">
					<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
						<div>
							<h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo esc_html__('Cursos de Red Cultural', 'red-cultural-pages'); ?></h1>
						</div>
						<div class="w-full sm:w-80">
							<label class="sr-only" for="<?php echo esc_attr($search_input_id); ?>"><?php echo esc_html__('Buscar cursos', 'red-cultural-pages'); ?></label>
							<input id="<?php echo esc_attr($search_input_id); ?>" data-rcp-search type="search" inputmode="search" autocomplete="off" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10" placeholder="<?php echo esc_attr__('Buscar cursos…', 'red-cultural-pages'); ?>" />
						</div>
					</div>
				</header>

				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6" data-rcp-grid>
					<?php
					while ($q->have_posts()) :
						$q->the_post();
						$course_id = (int) get_the_ID();
						echo rcp_render_red_cultural_course_card($course_id);
					endwhile;
					?>
				</div>
			</div>
		</div>
		<?php
		wp_reset_postdata();

		return (string) ob_get_clean();
	}
}
