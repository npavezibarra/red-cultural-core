<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

global $post;
if (!$post instanceof \WP_Post) {
	exit;
}

$lesson_id = (int) $post->ID;
$user_id = (int) get_current_user_id();

$course_id = 0;
if (function_exists('learndash_get_course_id')) {
	$course_id = (int) learndash_get_course_id($lesson_id);
}

$course_title = ($course_id > 0) ? (string) get_the_title($course_id) : '';
$course_url = ($course_id > 0) ? (string) get_permalink($course_id) : (string) home_url('/');

$lesson_title = (string) get_the_title($lesson_id);
$lesson_url = (string) get_permalink($lesson_id);

$lesson_access_from = 0;
if ($course_id > 0 && function_exists('ld_lesson_access_from')) {
	$lesson_access_from = (int) ld_lesson_access_from($lesson_id, $user_id, $course_id);
}
$lesson_is_scheduled = ($lesson_access_from > 0);
$lesson_available_after_display = '';
if ($lesson_is_scheduled) {
	if (function_exists('learndash_adjust_date_time_display')) {
		$lesson_available_after_display = (string) learndash_adjust_date_time_display($lesson_access_from);
	} else {
		$lesson_available_after_display = (string) date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $lesson_access_from);
	}
}

$rcil_is_active = false;
$rcil_has_full_access = false;
$rcp_is_locked = false;
if (
	$course_id > 0
	&& function_exists('rcil_get_course_lesson_price')
	&& function_exists('rcil_user_has_full_course_access')
	&& function_exists('rcil_user_has_lesson_access')
) {
	$rcil_is_active = ((int) rcil_get_course_lesson_price($course_id) > 0);
	$rcil_has_full_access = (bool) rcil_user_has_full_course_access($user_id, $course_id);
	if ($rcil_is_active && !$rcil_has_full_access && !rcil_user_has_lesson_access($user_id, $lesson_id)) {
		$rcp_is_locked = true;
	}
}

$video_url = '';
$video_enabled = '';
if (function_exists('learndash_get_setting')) {
	$video_enabled = (string) learndash_get_setting($lesson_id, 'lesson_video_enabled');
	$video_url = (string) learndash_get_setting($lesson_id, 'lesson_video_url');
}

if (trim($video_url) === '') {
	$meta = get_post_meta($lesson_id, '_sfwd-lessons', true);
	if (is_array($meta)) {
		if (isset($meta['sfwd-lessons_lesson_video_url']) && is_scalar($meta['sfwd-lessons_lesson_video_url'])) {
			$video_url = (string) $meta['sfwd-lessons_lesson_video_url'];
		} elseif (isset($meta['lesson_video_url']) && is_scalar($meta['lesson_video_url'])) {
			$video_url = (string) $meta['lesson_video_url'];
		}
		if ($video_enabled === '' && isset($meta['sfwd-lessons_lesson_video_enabled']) && is_scalar($meta['sfwd-lessons_lesson_video_enabled'])) {
			$video_enabled = (string) $meta['sfwd-lessons_lesson_video_enabled'];
		}
	}
}

$video_url = trim($video_url);
$video_is_enabled = ($video_enabled !== '' && $video_enabled !== 'off' && $video_enabled !== '0');

$video_embed_html = '';
if (!$lesson_is_scheduled && !$rcp_is_locked && $video_is_enabled && $video_url !== '') {
	$video_settings = function_exists('learndash_get_setting') ? (array) learndash_get_setting($lesson_id) : array();
	$video_settings['lesson_video_enabled'] = 'on';
	$video_settings['lesson_video_url'] = $video_url;

	if (class_exists('Learndash_Course_Video')) {
		// This outputs the LearnDash video wrapper + enqueues video progression scripts in wp_footer.
		$video_embed_html = (string) \Learndash_Course_Video::get_instance()->add_video_to_content('', $post, $video_settings);
		$video_embed_html = trim($video_embed_html);
	}
}

$mark_complete_html = '';
if (shortcode_exists('learndash_mark_complete')) {
	$mark_complete_html = (string) do_shortcode('[learndash_mark_complete]');
	$mark_complete_html = trim($mark_complete_html);
}

$lessons = ($course_id > 0 && function_exists('rcp_ld_course_lessons')) ? (array) rcp_ld_course_lessons($course_id, $user_id) : array();
$lesson_items = array_values($lessons);
$lesson_count = count($lesson_items);

$active_index = 0;
$is_current_completed = false;
for ($i = 0; $i < $lesson_count; $i++) {
	$item_post = $lesson_items[$i]['post'] ?? null;
	if ($item_post instanceof \WP_Post && (int) $item_post->ID === $lesson_id) {
		$active_index = $i;
		$is_current_completed = (($lesson_items[$i]['status'] ?? '') === 'completed');
		break;
	}
}

$completed = 0;
foreach ($lesson_items as $it) {
	if (($it['status'] ?? '') === 'completed') {
		$completed++;
	}
}
$progress_percent = ($lesson_count > 0) ? (int) round(($completed / $lesson_count) * 100) : 0;

// Pre-render block theme template parts BEFORE wp_head so their assets are enqueued in the correct place.
$rcp_theme_header_html = '';
if (function_exists('do_blocks')) {
	$rcp_theme_header_html = (string) do_blocks('<!-- wp:template-part {"slug":"header","area":"header"} /-->');
}

?><!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html($lesson_title); ?></title>
	<script src="https://cdn.tailwindcss.com"></script>
	<script src="https://unpkg.com/lucide@latest"></script>
	<style>
		.custom-scrollbar::-webkit-scrollbar{width:4px}
		.custom-scrollbar::-webkit-scrollbar-track{background:transparent}
		.custom-scrollbar::-webkit-scrollbar-thumb{background:#e5e7eb;border-radius:10px}

		.lesson-item{border-left:4px solid transparent;transition:all .2s ease}
		.lesson-active{background-color:#f3f4f6 !important;border-left:4px solid #111827 !important}

		.bg-light-brown{background-color:#c5a367}
		.text-light-brown{color:#c5a367}
		.border-light-brown{border-color:#c5a367}

		/* Make theme header behave like the design header. */
		#red-cultural-header{position:sticky;top:0;z-index:50;background:#fff}
		#red-cultural-header{border-bottom:1px solid #e5e7eb}
		#red-cultural-lesson-layout{max-width:var(--wp--style--global--wide-size);margin:auto}
		#red-cultural-lesson-video iframe{width:100%;height:100%}
		#red-cultural-lesson-finalizado form{margin:0}
		#red-cultural-lesson-finalizado .learndash_mark_complete_button{
			display:flex;align-items:center;
			padding:6px 16px;border-radius:6px;
			font-size:11px;font-weight:700;
			text-transform:uppercase;letter-spacing:.12em;
			border:1px solid #c5a367;background:transparent;color:#c5a367;
		}
		#red-cultural-lesson-finalizado .learndash_mark_complete_button[disabled]{
			opacity:.35;cursor:not-allowed;
		}
		#red-cultural-lesson-main .md\:text-4xl{font-size:28px;line-height:2.5rem}
		#red-cultural-lesson-available-message{font-size:22px;line-height:1.25}
		#red-cultural-lesson-countdown{font-size:40px;line-height:1.1;font-weight:900}
		#red-cultural-lesson-available-meta{font-size:14px;line-height:1.2}
		#red-cultural-lesson-main-inner{padding-top:20px !important}
		#red-cultural-lesson-header{margin-bottom:10px !important}
		#red-cultural-lesson-title{margin-bottom:10px !important}
		#red-cultural-lesson-top-nav{margin-bottom:10px !important}

		/* Mobile sliding panel styles */
		@media (max-width: 767px) {
			#red-cultural-lesson-sidebar {
				position: fixed;
				bottom: 0;
				left: 0;
				right: 0;
				width: 100%;
				height: auto;
				max-height: 85vh;
				z-index: 10000;
				transform: translateY(100%);
				visibility: hidden;
				transition: transform 0.4s cubic-bezier(0.32, 0.72, 0, 1), visibility 0s 0.4s;
				background: #fff;
				border-radius: 20px 20px 0 0;
				box-shadow: 0 -10px 40px rgba(0,0,0,0.15);
				display: flex;
				flex-direction: column;
			}
			#red-cultural-lesson-sidebar.panel-open {
				transform: translateY(0);
				visibility: visible;
				transition: transform 0.4s cubic-bezier(0.32, 0.72, 0, 1), visibility 0s 0s;
			}
		}
	</style>
	<?php wp_head(); ?>
</head>
<body <?php body_class('bg-white text-gray-800 antialiased overflow-hidden'); ?>>
	<?php if (function_exists('wp_body_open')) { wp_body_open(); } ?>

	<?php
	if ($rcp_theme_header_html !== '') {
		echo str_replace('<header ', '<header id="red-cultural-header" ', $rcp_theme_header_html); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<!-- Mobile Backdrop for sliding menu -->
	<div id="rcp-lesson-backdrop" class="fixed inset-0 bg-black/60 z-[9999] opacity-0 pointer-events-none transition-opacity duration-300 md:hidden"></div>

	<div id="red-cultural-lesson-layout" class="flex flex-col md:flex-row md:h-[calc(100vh-64px)] md:overflow-hidden relative">

		<aside id="red-cultural-lesson-sidebar" class="w-full md:w-80 border-r border-gray-200 flex-shrink-0 bg-gray-50/50 overflow-y-auto custom-scrollbar">
			<div class="p-6 hidden md:block">
				<a id="red-cultural-lesson-back" class="flex items-center text-gray-400 text-xs font-bold uppercase tracking-wider mb-8 hover:text-gray-600 transition-colors" href="<?php echo esc_url($course_url); ?>">
					<i data-lucide="chevron-left" class="w-4 h-4"></i>
					<span class="ml-1"><?php echo esc_html__('Volver a Curso', 'red-cultural-pages'); ?></span>
				</a>

				<h2 id="red-cultural-lesson-course-title" class="text-xl font-black text-gray-800 leading-tight mb-6">
					<?php echo esc_html($course_title !== '' ? $course_title : __('Curso', 'red-cultural-pages')); ?>
				</h2>

				<div class="mb-8">
					<div class="flex justify-between text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">
						<span id="red-cultural-lesson-progress-text"><?php echo esc_html($progress_percent . '% completo'); ?></span>
					</div>
					<div class="w-full bg-gray-200 h-1.5 rounded-full overflow-hidden">
						<div id="red-cultural-lesson-progress-bar" class="bg-gray-500 h-full transition-all duration-500" style="<?php echo esc_attr('width:' . $progress_percent . '%'); ?>"></div>
					</div>
				</div>
			</div>

			<!-- Close button for mobile inside the panel -->
			<div class="p-6 md:hidden flex justify-between items-center border-b border-gray-100">
				<h3 class="font-bold text-gray-900"><?php echo esc_html__('Lecciones', 'red-cultural-pages'); ?></h3>
				<button id="rcp-lesson-sidebar-close" class="p-2 bg-gray-100 rounded-full hover:bg-gray-200 active:scale-95 transition-all">
					<i data-lucide="x" class="w-4 h-4 text-gray-600"></i>
				</button>
			</div>

			<nav id="red-cultural-lesson-list" class="border-t border-gray-100">
				<?php if ($lesson_count === 0) : ?>
					<div class="p-6 text-sm text-gray-500"><?php echo esc_html__('No hay lecciones.', 'red-cultural-pages'); ?></div>
				<?php else : ?>
					<?php foreach ($lesson_items as $idx => $item) : ?>
						<?php
						$item_post = $item['post'] ?? null;
						if (!$item_post instanceof \WP_Post) {
							continue;
						}
						$item_id = (int) $item_post->ID;
						$item_title = (string) get_the_title($item_id);
						$item_url = !empty($item['permalink']) ? (string) $item['permalink'] : (string) get_permalink($item_id);

						$is_active = ($item_id === $lesson_id);
						$is_completed = (($item['status'] ?? '') === 'completed');
						?>
						<a
							id="<?php echo esc_attr('red-cultural-lesson-item-' . $item_id); ?>"
							href="<?php echo esc_url($item_url); ?>"
							class="<?php echo esc_attr('lesson-item w-full flex items-start p-5 text-left border-b border-gray-100 transition-colors hover:bg-gray-100/50 ' . ($is_active ? 'lesson-active' : '')); ?>"
						>
							<?php 
							$is_item_locked = ($rcil_is_active && !$rcil_has_full_access && !rcil_user_has_lesson_access($user_id, $item_id));
							?>
							<span class="<?php echo esc_attr('text-sm flex-grow pr-4 leading-snug ' . ($is_active ? 'font-bold text-gray-900' : 'text-gray-600 font-medium')); ?>">
								<?php if ($is_item_locked) : ?>
									<i data-lucide="lock" class="w-3.5 h-3.5 inline-block mr-1.5 text-gray-400 mb-0.5"></i>
								<?php endif; ?>
								<?php echo esc_html($item_title); ?>
							</span>
							<div class="mt-0.5 flex-shrink-0">
								<i data-lucide="<?php echo esc_attr($is_completed ? 'check-circle-2' : 'circle'); ?>" class="<?php echo esc_attr('w-[18px] h-[18px] ' . ($is_completed ? 'text-green-500' : 'text-gray-300')); ?>"></i>
							</div>
						</a>
					<?php endforeach; ?>
				<?php endif; ?>
			</nav>
		</aside>

		<main id="red-cultural-lesson-main" class="flex-grow overflow-y-auto bg-white custom-scrollbar pb-24 md:pb-0">
			<!-- MOBILE ONLY: Lesson Header replacing sidebar info -->
			<div class="block md:hidden px-6 pt-6 pb-2">
				<a class="flex items-center text-gray-500 text-[10px] font-bold uppercase tracking-[0.15em] mb-4 hover:text-gray-900 transition-colors" href="<?php echo esc_url($course_url); ?>">
					<i data-lucide="chevron-left" class="w-3.5 h-3.5 mr-1"></i>
					<span><?php echo esc_html__('Volver a Curso', 'red-cultural-pages'); ?></span>
				</a>
				<h1 class="text-2xl font-black text-gray-900 leading-tight mb-4 tracking-tight">
					<?php echo esc_html($lesson_title); ?>
				</h1>
				<div class="w-full mb-6 relative">
					<div class="flex justify-between text-[10px] font-bold uppercase tracking-[0.15em] text-gray-400 mb-2">
						<span><?php echo esc_html($progress_percent . '% completo'); ?></span>
					</div>
					<div class="w-full bg-gray-200 h-1 rounded-full overflow-hidden">
						<div class="bg-gray-500 h-full" style="<?php echo esc_attr('width:' . $progress_percent . '%'); ?>"></div>
					</div>
				</div>
				<?php 
				$zoom_url = get_post_meta($lesson_id, '_rc_zoom_url', true);
				if ($zoom_url && !$rcp_is_locked) : ?>
					<div class="mb-6">
						<a href="<?php echo esc_url($zoom_url); ?>" target="_blank" class="w-full flex justify-center items-center space-x-2 bg-[#2D8CFF] text-white px-6 py-3 rounded-md font-bold text-[11px] uppercase tracking-[0.15em] hover:bg-[#1f71cf] transition-colors shadow-sm">
							<i data-lucide="video" class="w-4 h-4"></i>
							<span><?php _e('Ir a Sesión Zoom', 'red-cultural-pages'); ?></span>
						</a>
					</div>
				<?php endif; ?>
			</div>

			<div id="red-cultural-lesson-main-inner" class="max-w-4xl mx-auto px-6 py-4 md:py-8">

				<!-- Hide top nav on mobile to match wireframe -->
				<div id="red-cultural-lesson-top-nav" class="hidden md:flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-10">
					<div id="red-cultural-lesson-nav-left" class="flex items-center">
						<span id="red-cultural-lesson-counter" class="text-[10px] uppercase tracking-[0.15em] text-gray-400 font-extrabold">
							<?php
							echo esc_html(
								sprintf(
									/* translators: 1: current lesson number, 2: total lessons. */
									__('LECCIÓN %1$d DE %2$d', 'red-cultural-pages'),
									$lesson_count > 0 ? ($active_index + 1) : 1,
									max(1, $lesson_count)
								)
							);
							?>
						</span>
					</div>

					<div id="red-cultural-lesson-nav-right" class="flex items-center space-x-3">
						<div class="flex shadow-sm rounded-md overflow-hidden border border-gray-200">
							<?php
							$prev_url = ($active_index > 0) ? (!empty($lesson_items[$active_index - 1]['permalink']) ? (string) $lesson_items[$active_index - 1]['permalink'] : (string) get_permalink((int) ($lesson_items[$active_index - 1]['id'] ?? 0))) : '';
							$next_url = ($active_index < $lesson_count - 1) ? (!empty($lesson_items[$active_index + 1]['permalink']) ? (string) $lesson_items[$active_index + 1]['permalink'] : (string) get_permalink((int) ($lesson_items[$active_index + 1]['id'] ?? 0))) : '';
							?>
								<a
									id="red-cultural-lesson-prev"
									class="<?php echo esc_attr('p-1.5 bg-white hover:bg-gray-50 transition-colors ' . ($prev_url === '' ? 'pointer-events-none opacity-30' : '')); ?>"
									href="<?php echo esc_url($prev_url !== '' ? $prev_url : $lesson_url); ?>"
									aria-label="<?php echo esc_attr__('Lección anterior', 'red-cultural-pages'); ?>"
								>
									<i data-lucide="chevron-left" class="w-4 h-4 text-gray-500"></i>
								</a>
							<div class="w-[1px] bg-gray-200"></div>
								<a
									id="red-cultural-lesson-next"
									class="<?php echo esc_attr('p-1.5 bg-white hover:bg-gray-50 transition-colors ' . ($next_url === '' ? 'pointer-events-none opacity-30' : '')); ?>"
									href="<?php echo esc_url($next_url !== '' ? $next_url : $lesson_url); ?>"
									aria-label="<?php echo esc_attr__('Siguiente lección', 'red-cultural-pages'); ?>"
								>
									<i data-lucide="chevron-right" class="w-4 h-4 text-gray-500"></i>
								</a>
						</div>

						<div id="red-cultural-lesson-finalizado">
							<?php if ($is_current_completed) : ?>
								<button type="button" class="flex items-center px-4 py-1.5 rounded-[6px] text-[11px] font-bold uppercase tracking-wider border border-transparent bg-light-brown text-white opacity-90 cursor-default" disabled>
									<?php echo esc_html__('FINALIZADO', 'red-cultural-pages'); ?>
									<span class="ml-2">
										<i data-lucide="thumbs-up" class="w-3.5 h-3.5 text-white"></i>
									</span>
								</button>
							<?php else : ?>
								<?php
								// Real LearnDash complete form (video progression will enable/disable this button).
								if (function_exists('learndash_mark_complete')) {
									echo learndash_mark_complete($post, array('button' => array('id' => 'complete-btn'))); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
								?>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<!-- Desktop Lesson Header (hidden on mobile) -->
				<header id="red-cultural-lesson-header" class="hidden md:block mb-10">
					<h1 id="red-cultural-lesson-title" class="text-3xl md:text-4xl font-black text-gray-900 leading-tight mb-8 tracking-tight">
						<?php echo esc_html($lesson_title); ?>
					</h1>
					<?php 
					// Desktop Zoom Button
					if ($zoom_url && !$rcp_is_locked) : ?>
						<div class="mt-4">
							<a href="<?php echo esc_url($zoom_url); ?>" target="_blank" class="inline-flex items-center space-x-2 bg-[#2D8CFF] text-white px-6 py-2.5 rounded-md font-bold text-[11px] uppercase tracking-[0.15em] hover:bg-[#1f71cf] transition-colors shadow-sm">
								<i data-lucide="video" class="w-4 h-4"></i>
								<span><?php _e('Ir a Sesión Zoom', 'red-cultural-pages'); ?></span>
							</a>
						</div>
					<?php endif; ?>
				<?php 
				$video_wrapper_classes = 'relative bg-black md:rounded-lg overflow-hidden md:shadow-xl group mb-6 md:mb-12 -mx-6 md:mx-0';
				if ($video_embed_html !== '') {
					$video_wrapper_classes .= ' aspect-video';
				} else {
					$video_wrapper_classes .= ' min-h-[400px] flex items-center justify-center py-10';
				}
				?>
				<div id="red-cultural-lesson-video" class="<?php echo esc_attr($video_wrapper_classes); ?>">
					<?php if ($rcp_is_locked) : ?>
						<div
							class="w-full flex flex-col items-center justify-center px-6 text-center space-y-4"
							<?php if ($lesson_is_scheduled) : ?>
								data-rcp-available-after="<?php echo esc_attr((string) $lesson_access_from); ?>"
							<?php endif; ?>
						>
								<i data-lucide="lock" class="w-12 h-12 text-white/40 mb-2"></i>
								<div id="red-cultural-lesson-available-message" class="text-white/90 font-semibold">
									<?php
									echo wp_kses_post(
										__('No tienes acceso a esta lección.<br>Compra esta lección o el curso completo para verla.', 'red-cultural-pages')
									);
									?>
								</div>
							<div id="red-cultural-lesson-available-meta" class="text-white/70 font-medium space-y-1">
								<?php if ($lesson_is_scheduled && $lesson_available_after_display !== '') : ?>
									<div>
										<?php
										echo esc_html(
											sprintf(
												/* translators: %s is a date/time. */
												__('Disponible en %s', 'red-cultural-pages'),
												$lesson_available_after_display
											)
										);
										?>
									</div>
								<?php endif; ?>
								<div>
									<?php
									echo esc_html(
										sprintf(
											/* translators: 1: current lesson number, 2: total lessons. */
											__('LECCIÓN %1$d DE %2$d', 'red-cultural-pages'),
											$lesson_count > 0 ? ($active_index + 1) : 1,
											max(1, $lesson_count)
										)
									);
									?>
								</div>
							</div>
							<?php if ($lesson_is_scheduled) : ?>
								<div id="red-cultural-lesson-countdown" class="text-white">
									00d 00h 00m 00s
								</div>
							<?php endif; ?>
								<div>
									<button type="button" class="rcil-buy-lessons-btn bg-light-brown text-white font-bold uppercase tracking-widest text-[11px] px-6 py-2 rounded-[6px]">
										<?php echo esc_html__('Comprar lección', 'red-cultural-pages'); ?>
									</button>
								</div>
						</div>
					<?php elseif ($lesson_is_scheduled) : ?>
						<div class="w-full flex flex-col items-center justify-center px-6 text-center space-y-4" data-rcp-available-after="<?php echo esc_attr((string) $lesson_access_from); ?>">
								<div id="red-cultural-lesson-available-message" class="text-white/90 font-semibold">
									<?php
									echo wp_kses_post(
										sprintf(
											/* translators: %s is the date/time when the lesson becomes available. */
											__('El contenido de esta lección estará disponible<br>después de %s', 'red-cultural-pages'),
											$lesson_available_after_display
										)
									);
									?>
								</div>
							<div id="red-cultural-lesson-available-meta" class="text-white/70 font-medium space-y-1">
								<div>
									<?php
									echo esc_html(
										sprintf(
											/* translators: 1: current lesson number, 2: total lessons. */
											__('LECCIÓN %1$d DE %2$d', 'red-cultural-pages'),
											$lesson_count > 0 ? ($active_index + 1) : 1,
											max(1, $lesson_count)
										)
									);
									?>
								</div>
							</div>
							<div id="red-cultural-lesson-countdown" class="text-white">
					<?php elseif ($video_embed_html !== '') : ?>
						<div class="absolute inset-0 w-full h-full">
							<?php echo $video_embed_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					<?php else : ?>
						<div class="w-full flex flex-col items-center justify-center">
							<span class="text-white/80 text-xs font-bold uppercase tracking-widest">
								<?php echo esc_html__('NO HAY VIDEO', 'red-cultural-pages'); ?>
							</span>
						</div>
					<?php endif; ?>
				</div>

				<!-- Mobile Only: Lesson Menu Trigger Button -->
				<button id="rcp-lesson-menu-trigger" class="flex md:hidden mb-12 items-center justify-center px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors active:scale-95 shadow-sm">
					<i data-lucide="menu" class="w-5 h-5 text-gray-700 mr-2"></i>
					<span class="text-xs font-bold text-gray-700 uppercase tracking-widest"><?php echo esc_html__('Lecciones', 'red-cultural-pages'); ?></span>
				</button>

				<?php if (!$lesson_is_scheduled && !$rcp_is_locked) : ?>
					<div id="red-cultural-lesson-content" class="prose max-w-none">
						<?php
						/** Render actual lesson content below the video area. */
						echo apply_filters('the_content', (string) $post->post_content); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</div>
				<?php endif; ?>
			</div>
		</main>
	</div>

	<script>
		(function () {
			// Mobile Panel Logic
			var menuTrigger = document.getElementById('rcp-lesson-menu-trigger');
			var menuClose = document.getElementById('rcp-lesson-sidebar-close');
			var sidebar = document.getElementById('red-cultural-lesson-sidebar');
			var backdrop = document.getElementById('rcp-lesson-backdrop');

			function toggleLessonMenu(show) {
				if (show) {
					sidebar.classList.add('panel-open');
					backdrop.classList.add('active');
					backdrop.style.opacity = '1';
					backdrop.style.pointerEvents = 'auto';
					document.body.style.overflow = 'hidden';
				} else {
					sidebar.classList.remove('panel-open');
					backdrop.classList.remove('active');
					backdrop.style.opacity = '0';
					backdrop.style.pointerEvents = 'none';
					document.body.style.overflow = '';
				}
			}

			if (menuTrigger) {
				menuTrigger.addEventListener('click', function() { toggleLessonMenu(true); });
			}
			if (menuClose) {
				menuClose.addEventListener('click', function() { toggleLessonMenu(false); });
			}
			if (backdrop) {
				backdrop.addEventListener('click', function() { toggleLessonMenu(false); });
			}

			// Keep the layout height aligned with the real header height.
			var header = document.getElementById('red-cultural-header');
			var layout = document.getElementById('red-cultural-lesson-layout');
			if (header && layout) {
				var h = header.getBoundingClientRect().height || 64;
				layout.style.height = 'calc(100vh - ' + h + 'px)';
			}

			if (window.lucide && typeof window.lucide.createIcons === 'function') {
				window.lucide.createIcons();
			}

			// Ensure the LearnDash complete button label matches the design.
			var ldBtn = document.querySelector('#red-cultural-lesson-finalizado .learndash_mark_complete_button');
			if (ldBtn) {
				if (ldBtn.tagName === 'INPUT') {
					ldBtn.value = 'FINALIZADO';
				} else {
					ldBtn.textContent = 'FINALIZADO';
				}
			}

			// Scheduled lesson countdown.
			var scheduledBox = document.querySelector('#red-cultural-lesson-video [data-rcp-available-after]');
			if (scheduledBox) {
				var target = parseInt(scheduledBox.getAttribute('data-rcp-available-after') || '0', 10);
				var countdown = document.getElementById('red-cultural-lesson-countdown');
				if (target > 0 && countdown) {
					function pad(n) { return (n < 10 ? '0' : '') + n; }
					function tick() {
						var now = Math.floor(Date.now() / 1000);
						var diff = target - now;
						if (diff <= 0) {
							countdown.textContent = '00d 00h 00m 00s';
							window.location.reload();
							return;
						}
						var days = Math.floor(diff / 86400);
						diff = diff % 86400;
						var hours = Math.floor(diff / 3600);
						diff = diff % 3600;
						var mins = Math.floor(diff / 60);
						var secs = diff % 60;

						countdown.textContent =
							days + 'd ' +
							pad(hours) + 'h ' +
							pad(mins) + 'm ' +
							pad(secs) + 's';
					}
					tick();
					window.setInterval(tick, 1000);
				}
			}
		})();
	</script>
	<?php wp_footer(); ?>
</body>
</html>
