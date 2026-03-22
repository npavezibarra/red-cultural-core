<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('rcp_red_cultural_cursos_carousel_shortcode')) {
	/**
	 * Shortcode: [red-cultural-cursos-carousel per_slide="4" interval="3" total="12"]
	 *
	 * - per_slide: number of courses shown per slide (default 4)
	 * - interval: seconds between slides (default 3)
	 * - total: total number of courses to show (default per_slide * 3)
	 */
	function rcp_red_cultural_cursos_carousel_shortcode($atts = array()): string {
		$atts = shortcode_atts(
			array(
				'per_slide' => 4,
				'interval' => 3,
				'total' => 0,
			),
			(array) $atts,
			'red-cultural-cursos-carousel'
		);

		$post_type = 'sfwd-courses';
		if (!post_type_exists($post_type)) {
			return '';
		}

		$per_slide = (int) $atts['per_slide'];
		if ($per_slide <= 0) {
			$per_slide = 4;
		}

		$interval = (float) $atts['interval'];
		if ($interval <= 0) {
			$interval = 3;
		}
		$interval_ms = (int) round($interval * 1000);
		if ($interval_ms < 800) {
			$interval_ms = 800;
		}

		$total = (int) $atts['total'];
		if ($total <= 0) {
			$total = $per_slide * 3;
		}
		if ($total < $per_slide) {
			$total = $per_slide;
		}

		$script_rel_path = 'assets/js/red-cultural-cursos-carousel.js';
		$script_file = RC_CORE_PATH . $script_rel_path;
		$script_url = RC_CORE_URL . $script_rel_path;

		if (!wp_script_is('rcp-red-cultural-cursos-carousel', 'registered')) {
			wp_register_script(
				'rcp-red-cultural-cursos-carousel',
				$script_url,
				array(),
				file_exists($script_file) ? (string) filemtime($script_file) : null,
				true
			);
		}
		wp_enqueue_script('rcp-red-cultural-cursos-carousel');

		// Tailwind CDN (in <head>) to match the existing Cursos shortcode style.
		if (!wp_script_is('rcp-tailwind', 'enqueued') && !wp_script_is('rcp-tailwind', 'done')) {
			wp_enqueue_script('rcp-tailwind', 'https://cdn.tailwindcss.com', array(), null, false);
		}

		$q = new \WP_Query(
			array(
				'post_type' => $post_type,
				'post_status' => 'publish',
				'orderby' => 'date',
				'order' => 'DESC',
				'posts_per_page' => $total,
				'no_found_rows' => true,
				'ignore_sticky_posts' => true,
			)
		);

		if (!$q->have_posts()) {
			return '';
		}

		if (!function_exists('rcp_render_red_cultural_course_card')) {
			$helpers = dirname(__FILE__, 2) . '/templates/learndash-course.php';
			if (file_exists($helpers)) {
				require_once $helpers;
			}
		}
		if (!function_exists('rcp_render_red_cultural_course_card')) {
			return '';
		}

		$slides = array();
		$slide = array();
		while ($q->have_posts()) {
			$q->the_post();
			$slide[] = (int) get_the_ID();
			if (count($slide) >= $per_slide) {
				$slides[] = $slide;
				$slide = array();
			}
		}
		if (!empty($slide)) {
			$slides[] = $slide;
		}
		wp_reset_postdata();

		if (empty($slides)) {
			return '';
		}

		$catalog_url = (string) home_url('/cursos/');
		$root_id = function_exists('wp_unique_id') ? wp_unique_id('rcp-cursos-carousel-') : ('rcp-cursos-carousel-' . uniqid());

		ob_start();
		?>
		<style>
			.course-card{border-radius:9px;overflow:hidden;transition:all .3s cubic-bezier(.4,0,.2,1)}
			.course-card:hover{transform:translateY(-4px);box-shadow:0 10px 25px -5px rgba(0,0,0,.1),0 8px 10px -6px rgba(0,0,0,.1)}
			.aspect-video-custom{aspect-ratio:16/9}
			.rcp-course-thumb{background-size:cover;background-position:center}
			.rcp-carousel-viewport{position:relative}
			/* IMPORTANT: don't use inset:0 (it collapses height to the viewport, causing overlap with following sections). */
			.rcp-carousel-slide{position:absolute;top:0;left:0;right:0;opacity:0;transform:translate3d(-14px,0,0);transition:opacity .55s cubic-bezier(.22,1,.36,1),transform .55s cubic-bezier(.22,1,.36,1);pointer-events:none}
			.rcp-carousel-slide.is-active{opacity:1;transform:translate3d(0,0,0);pointer-events:auto}
		</style>
		<div id="<?php echo esc_attr($root_id); ?>" class="rcp-red-cultural-cursos-carousel py-8" data-rcp-carousel data-interval="<?php echo esc_attr((string) $interval_ms); ?>">
			<div class="max-w-7xl mx-auto">
				<div class="flex items-center justify-between mb-8">
					<h2 class="text-2xl font-bold text-gray-900"><?php echo esc_html__('Cursos', 'red-cultural-pages'); ?></h2>
						<a href="<?php echo esc_url($catalog_url); ?>" class="inline-flex items-center justify-center rounded-md border-2 border-black bg-black px-5 py-2 text-xs font-bold tracking-[.2em] uppercase text-white hover:bg-zinc-800 transition">
							<?php echo esc_html__('VER CATÁLOGO', 'red-cultural-pages'); ?>
						</a>
					</div>

				<div class="rcp-carousel-viewport" data-rcp-viewport>
					<?php foreach ($slides as $i => $course_ids) : ?>
						<div class="rcp-carousel-slide<?php echo $i === 0 ? ' is-active' : ''; ?>" data-rcp-slide>
							<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
								<?php
								foreach ($course_ids as $course_id) {
									echo rcp_render_red_cultural_course_card((int) $course_id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
								?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php

		return (string) ob_get_clean();
	}
}
