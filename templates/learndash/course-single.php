<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

global $post;
if (!$post instanceof \WP_Post) {
	exit;
}

$course_id = (int) $post->ID;
$user_id = (int) get_current_user_id();

$title = (string) get_the_title($course_id);
$course_url = (string) get_permalink($course_id);

$author_id = (int) get_post_field('post_author', $course_id);
$author_name = $author_id ? (string) get_the_author_meta('display_name', $author_id) : '';

$header_image = get_the_post_thumbnail_url($course_id, 'full');
$card_image = get_the_post_thumbnail_url($course_id, 'large');
$fallback_header = 'https://images.unsplash.com/photo-1548013146-72479768bbaa?auto=format&fit=crop&q=80&w=2000';
$fallback_card = 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?auto=format&fit=crop&q=80&w=1000';

$header_image_url = $header_image ? (string) $header_image : $fallback_header;
$card_image_url = $card_image ? (string) $card_image : $fallback_card;

$raw_desc = (string) get_the_excerpt($course_id);
if ($raw_desc === '') {
	$raw_desc = (string) wp_strip_all_tags((string) $post->post_content);
}
$desc = (string) wp_trim_words($raw_desc, 70, '…');
$intro = (string) apply_filters('the_content', $post->post_content);

$enrolled = false;
if (function_exists('sfwd_lms_has_access')) {
	$enrolled = (bool) sfwd_lms_has_access($course_id, $user_id);
}

$rcil_is_active = false;
$rcil_has_full_access = false;
if (function_exists('rcil_get_course_lesson_price')) {
	$rcil_is_active = ((int) rcil_get_course_lesson_price($course_id) > 0);
}
if ($rcil_is_active && $user_id > 0 && function_exists('rcil_user_has_full_course_access')) {
	$rcil_has_full_access = (bool) rcil_user_has_full_course_access($user_id, $course_id);
}

$price = function_exists('rcp_ld_course_price_display') ? (string) rcp_ld_course_price_display($course_id) : '';
$lessons = function_exists('rcp_ld_course_lessons') ? (array) rcp_ld_course_lessons($course_id, $user_id) : array();
$lesson_count = is_array($lessons) ? count($lessons) : 0;

// CTA access rules:
// - If RCIL is active, we only show "Ir al curso" after the user has purchased the full course OR at least 1 lesson.
// - Otherwise, we fall back to LearnDash access.
$rcp_has_any_purchase_access = false;
$rcp_first_accessible_lesson_url = $course_url;

if ($user_id > 0) {
    foreach ($lessons as $lesson_item) {
        $lesson_post = $lesson_item['post'] ?? null;
        if (!$lesson_post instanceof \WP_Post) {
            continue;
        }
        $l_id = (int) $lesson_post->ID;
        
        // If RCIL is active, check specific individual access.
        // If not, check general LD enrollment.
        $has_access = $rcil_is_active ? rcil_user_has_lesson_access($user_id, $l_id) : $enrolled;
        
        if ($has_access) {
            $rcp_has_any_purchase_access = true;
            if ($rcp_first_accessible_lesson_url === $course_url) {
                $rcp_first_accessible_lesson_url = (string) get_permalink($l_id);
            }
            if ($rcil_is_active) {
                // For RCIL, we found the first specifically purchased lesson, so stop.
                break;
            }
        }
    }
}

// Special case: full access but maybe no lessons? 
if ($rcil_has_full_access || (!$rcil_is_active && $enrolled)) {
    $rcp_has_any_purchase_access = true;
}

// Never show "Ir al curso" to guests (even if LearnDash is "open").
$rcp_show_go_to_course = ($user_id > 0) && $rcp_has_any_purchase_access;

$payment_button_html = '';
if (!$enrolled && class_exists('Learndash_Payment_Button')) {
	try {
		$btn = new \Learndash_Payment_Button($course_id);
		$payment_button_html = (string) $btn->map();
	} catch (\Throwable $e) {
		$payment_button_html = '';
	}
}

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
	<title><?php echo esc_html($title); ?></title>
	<script src="https://cdn.tailwindcss.com"></script>
	<script src="https://unpkg.com/lucide@latest"></script>
	<style>
		.sticky-card { position: sticky; top: 2rem; }
		.btn-join{width:100%;background:#000;color:#fff;padding:1rem;border-radius:.375rem;font-weight:700;font-size:.875rem;letter-spacing:.05em}
		.btn-join:hover{background:#1f2937}
		.btn-join:focus{outline:2px solid transparent;outline-offset:2px}
		/* Match block theme "wide" width used by the header template-part. */
		.rcp-wide{max-width:var(--wp--style--global--wide-size, 1200px)}
		#red-cultural-course-hero-content{padding:30px 0}
		@media (max-width: 1400px){#red-cultural-course-hero-content{padding:30px}}
		/* Keep sidebar top aligned relative to the main navbar (desktop). */
		@media (min-width: 1024px){#red-cultural-course-sidebar{margin-top:var(--rcp-sidebar-offset, 0px);padding-top:30px}}
		#red-cultural-course-content{padding:90px 0px}
		#red-cultural-course-summary{font-size:16px}
		#red-cultural-course-intro-text{font-size:18px}
		#red-cultural-course-hero-content .max-w-2xl{max-width:60%}
		@media (max-width: 1023px){#red-cultural-course-hero-content .max-w-2xl{max-width:100%}}
		@media (max-width: 1023px){#red-cultural-course-main{padding:30px}}
		@media (min-width: 1400px){#red-cultural-course-main{padding:0}}
		#red-cultural-course-hero-content .max-w-xl{max-width:100%}
		.bg-black\/65{background-color:rgb(0 0 0 / 0.80)}
		#red-cultural-course-sidebar{max-width:360px}
		#btn-join{padding:10px 28px;font-weight:700}
		#red-cultural-course-lessons-list h3.font-medium.text-gray-800{font-size:16px;font-weight:700}
		#red-cultural-course-lessons h2.text-xs.font-bold.text-gray-400.tracking-widest.uppercase.mb-6{color:#000;font-weight:900;font-size:16px}
		#rcil-buy-course { margin-bottom: 10px !important; }
		#red-cultural-course-cta { margin-bottom: 10px !important; }
	</style>
	<?php wp_head(); ?>
</head>
<body <?php body_class('bg-gray-50 text-gray-900 leading-normal'); ?>>
	<?php if (function_exists('wp_body_open')) { wp_body_open(); } ?>

	<?php
	// Render the active block theme header (Twenty Twenty-Five) so navbar matches the rest of the site.
	if ($rcp_theme_header_html !== '') {
		echo str_replace('<header ', '<header id="red-cultural-header" ', $rcp_theme_header_html); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<header id="red-cultural-course-hero" class="relative w-full overflow-hidden">
		<div class="absolute inset-0 bg-cover bg-center" style="<?php echo esc_attr("background-image: url('{$header_image_url}');"); ?>">
			<div class="absolute inset-0 bg-black/65"></div>
		</div>

		<div id="red-cultural-course-hero-content" class="relative z-30 rcp-wide mx-auto flex flex-col justify-center min-h-[500px] text-white py-20 px-6">
			<div class="max-w-2xl text-white">
				<span class="uppercase tracking-widest text-xs font-semibold mb-3 block opacity-80">
					<?php echo esc_html__('Curso', 'red-cultural-pages'); ?>
				</span>
				<h1 id="red-cultural-course-title" class="text-4xl md:text-5xl font-bold mb-6 leading-tight">
					<?php echo esc_html($title); ?>
				</h1>
				<p id="red-cultural-course-summary" class="text-sm leading-relaxed mb-8 opacity-90 max-w-xl">
					<?php echo esc_html($desc); ?>
				</p>

				<button id="red-cultural-course-scroll-details" class="flex items-center text-xs font-medium hover:opacity-70 transition-opacity mb-8" type="button">
					<?php echo esc_html__('Ver detalles de Curso', 'red-cultural-pages'); ?>
					<i data-lucide="chevron-down" class="ml-1 w-4 h-4"></i>
				</button>

				<?php if ($author_name !== '') : ?>
					<a id="red-cultural-course-author" href="<?php echo esc_url(get_author_posts_url($author_id)); ?>" class="flex items-center space-x-3 no-underline hover:opacity-80 transition-opacity">
						<div class="w-10 h-10 rounded-full bg-gray-400 flex items-center justify-center overflow-hidden border-2 border-white/20">
							<i data-lucide="user" class="text-white w-6 h-6"></i>
						</div>
						<span id="rc-author-display-name-header" class="text-sm font-medium text-white"><?php echo esc_html($author_name); ?></span>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</header>

	<main id="red-cultural-course-main" class="rcp-wide mx-auto px-6 pb-20 -mt-16 relative z-20">
		<div id="red-cultural-course-grid" class="grid grid-cols-1 lg:grid-cols-3 gap-12">

			<div id="red-cultural-course-content" class="lg:col-span-2 pt-16">
				<?php if (current_user_can('manage_options')) : ?>
					<div id="rc-author-admin-ui" class="mb-8 p-4 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
						<div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
							<div class="flex items-center space-x-4">
								<div class="flex items-center space-x-2 text-gray-700">
									<i data-lucide="settings" class="w-4 h-4"></i>
									<span class="text-xs font-bold uppercase tracking-wider"><?php esc_html_e('Panel Admin', 'red-cultural-core'); ?></span>
								</div>
								
								<div class="flex items-center space-x-3 bg-white border border-gray-200 px-3 py-1.5 rounded-lg shrink-0 scale-90 md:scale-100 origin-left">
									<span class="text-[9px] font-bold uppercase text-gray-400">Estado:</span>
									<label class="relative inline-flex items-center cursor-pointer">
										<input type="checkbox" id="rc-course-status-toggle" class="sr-only peer" <?php echo (get_post_status() === 'publish') ? 'checked' : ''; ?>>
										<div class="w-8 h-4 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-green-500"></div>
										<span id="rc-status-label" class="ml-2 text-[9px] font-bold uppercase text-gray-600"><?php echo (get_post_status() === 'publish') ? 'Publicado' : 'Borrador'; ?></span>
									</label>
								</div>
							</div>

							<button id="rc-author-edit-trigger" class="text-[10px] bg-blue-600 text-white px-3 py-1 rounded-full font-bold uppercase hover:bg-blue-700 transition-colors shadow-sm" type="button">Cambiar Autor</button>
						</div>

						<div id="rc-author-admin-box" class="hidden mt-4 pt-4 border-t border-gray-200 animate-in fade-in slide-in-from-top-2 duration-200">
							<div class="relative w-full">
								<input type="text" id="rc-author-search-input" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none shadow-sm" placeholder="<?php esc_attr_e('Cambiar profesor...', 'red-cultural-core'); ?>" autocomplete="off">
								<div id="rc-author-search-results" class="hidden absolute left-0 top-full mt-1 w-full bg-white text-gray-800 rounded-lg shadow-2xl max-h-48 overflow-y-auto z-[9999] border border-gray-100 p-1"></div>
							</div>
							
							<div class="flex items-center justify-between mt-3">
								<div id="rc-author-edit-status" class="text-[10px] font-bold"></div>
								<button id="rc-author-edit-cancel" class="text-[10px] text-gray-400 font-bold hover:text-gray-600 uppercase" type="button">Cerrar</button>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<?php if ($intro !== '') : ?>
					<div id="red-cultural-course-intro" class="bg-white/50 p-1 rounded-xl mb-12">
						<p class="text-gray-600 leading-relaxed text-[15px]" id="red-cultural-course-intro-text">
							<?php echo $intro; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</p>
					</div>
				<?php endif; ?>

				<section id="red-cultural-course-lessons">
					<h2 class="text-xs font-bold text-gray-400 tracking-widest uppercase mb-6">
						<?php echo esc_html__('Contenido del curso', 'red-cultural-pages'); ?>
					</h2>

					<div id="red-cultural-course-lessons-list" class="space-y-4">
						<?php if ($lesson_count === 0) : ?>
							<div class="bg-white border border-gray-100 rounded-lg p-5 shadow-sm">
								<?php echo esc_html__('No hay lecciones publicadas todavía.', 'red-cultural-pages'); ?>
							</div>
						<?php else : ?>
							<?php foreach ($lessons as $lesson_item) : ?>
								<?php
								$lesson_post = $lesson_item['post'] ?? null;
								if (!$lesson_post instanceof \WP_Post) {
									continue;
								}

								$lesson_id = (int) $lesson_post->ID;
								$lesson_title = (string) get_the_title($lesson_id);
								$lesson_status = (string) ($lesson_item['status'] ?? '');
								$lesson_access_from = (int) ($lesson_item['lesson_access_from'] ?? 0);

								$right_text = '';
								$icon = 'clock';
								$dot_class = 'border-gray-200 group-hover:border-blue-400';

								if ($lesson_status === 'completed') {
									$right_text = (string) __('Completado', 'red-cultural-pages');
									$icon = 'check-circle';
									$dot_class = 'border-emerald-400';
								} elseif ($lesson_status === 'notavailable' && $lesson_access_from > 0 && function_exists('learndash_adjust_date_time_display')) {
									$right_text = sprintf(
										/* translators: %s is a date. */
										(string) __('Disponible en %s', 'red-cultural-pages'),
										(string) learndash_adjust_date_time_display($lesson_access_from)
									);
									$icon = 'clock';
									$dot_class = 'border-amber-300';
								} else {
									$right_text = (string) __('Disponible', 'red-cultural-pages');
									$icon = 'clock';
								}

								$edit_url = (current_user_can('edit_post', $lesson_id) ? (string) get_edit_post_link($lesson_id, 'raw') : '');

								$rcp_is_locked = false;
								if (
									!empty($rcil_is_active)
									&& empty($rcil_has_full_access)
									&& function_exists('rcil_user_has_lesson_access')
								) {
									$rcp_is_locked = !rcil_user_has_lesson_access($user_id, $lesson_id);
								}
								?>
								<?php
								$lesson_url = '';
								if (!empty($lesson_item['permalink'])) {
									$lesson_url = (string) $lesson_item['permalink'];
								} else {
									$lesson_url = (string) get_permalink($lesson_id);
								}
								?>
								<div
									class="rcp-lesson-card bg-white border border-gray-100 rounded-lg p-5 flex flex-col sm:flex-row sm:items-center justify-between shadow-sm hover:shadow-md transition-shadow group cursor-pointer"
									data-rcp-href="<?php echo esc_url($lesson_url); ?>"
									role="link"
									tabindex="0"
								>
									<div class="flex-1 mb-3 sm:mb-0">
										<div class="flex items-center space-x-2">
											<?php if ($rcp_is_locked) : ?>
												<i data-lucide="lock" class="w-4 h-4 text-gray-400"></i>
											<?php endif; ?>
											<h3 class="font-medium text-gray-800">
												<span class="rcil-rcp-lesson-title"><?php echo esc_html($lesson_title); ?></span>
											</h3>
											<?php if (current_user_can('manage_options')) : ?>
												<button
													type="button"
													class="rcil-lesson-edit-trigger text-[10px] text-blue-500 font-semibold uppercase hover:underline"
													data-lesson-url="<?php echo esc_url($lesson_url); ?>"
												>
													<?php echo esc_html__('EDITAR', 'red-cultural-individual-lesson'); ?>
												</button>
											<?php endif; ?>
										</div>
									</div>

									<div class="flex items-center justify-between sm:justify-end sm:space-x-6">
										<div class="flex items-center text-[11px] text-amber-600/70 font-medium">
											<i data-lucide="<?php echo esc_attr($icon); ?>" class="w-3 h-3 mr-1.5"></i>
											<?php echo esc_html($right_text); ?>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</section>
			</div>

			<div id="red-cultural-course-sidebar" class="lg:col-span-1">
				<div id="red-cultural-course-sidebar-sticky" class="sticky-card">
					<div id="red-cultural-course-card" class="bg-white rounded-xl shadow-2xl shadow-black/10 overflow-hidden">
						<div class="h-48 overflow-hidden">
							<img
								src="<?php echo esc_url($card_image_url); ?>"
								alt="<?php echo esc_attr($title); ?>"
								class="w-full h-full object-cover"
							/>
						</div>
						
						<div id="red-cultural-course-card-body" class="p-8 flex flex-col items-center text-center">

							<?php if ($price !== '') : ?>
								<div id="red-cultural-course-price" class="w-full text-left text-[22px] font-bold text-gray-900 mb-5">
									<?php echo esc_html($price); ?>
								</div>
							<?php endif; ?>

							<?php if ($rcp_show_go_to_course) : ?>
								<a id="red-cultural-course-cta" class="w-full bg-black text-white py-4 rounded-md font-bold text-sm tracking-wide hover:bg-gray-800 transition-colors mb-6" href="<?php echo esc_url($rcp_first_accessible_lesson_url); ?>">
									<?php echo esc_html__('Ir al curso', 'red-cultural-pages'); ?>
								</a>
							<?php elseif ($rcil_is_active) : ?>
									<button id="rcil-buy-course" type="button" class="w-full bg-black text-white py-4 rounded-md font-bold text-sm tracking-wide hover:bg-gray-800 transition-colors mb-6">
										<?php echo esc_html__('COMPRAR LECCIÓN', 'red-cultural-individual-lesson'); ?>
									</button>
							<?php elseif ($payment_button_html !== '') : ?>
								<div id="red-cultural-course-cta" class="w-full mb-6">
									<?php echo $payment_button_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>
							<?php else : ?>
								<a id="red-cultural-course-cta" class="w-full bg-black text-white py-4 rounded-md font-bold text-sm tracking-wide hover:bg-gray-800 transition-colors mb-6" href="<?php echo esc_url(wp_login_url($course_url)); ?>">
									<?php echo esc_html__('Iniciar sesión para inscribirse', 'red-cultural-pages'); ?>
								</a>
							<?php endif; ?>

							<div id="red-cultural-course-includes" class="w-full space-y-4 border-t border-gray-100 pt-6 text-left">
								<h4 class="text-[10px] font-bold text-gray-800 uppercase tracking-widest mb-4">
									<?php echo esc_html__('Incluye curso', 'red-cultural-pages'); ?>
								</h4>
								<div class="flex items-center text-sm text-gray-600">
									<i data-lucide="book-open" class="w-4 h-4 mr-3 text-gray-400"></i>
									<span><?php echo esc_html(sprintf(_n('%d Lección', '%d Lecciones', $lesson_count, 'red-cultural-pages'), $lesson_count)); ?></span>
								</div>
							</div>
						</div>
					</div>

					<button id="red-cultural-course-students" class="w-full mt-6 bg-gray-100 text-gray-800 py-4 rounded-full font-bold text-[11px] tracking-widest uppercase hover:bg-gray-200 transition-colors" type="button">
						<?php echo esc_html__('Lista de alumnos', 'red-cultural-pages'); ?>
					</button>
				</div>
			</div>

		</div>
	</main>

	<?php if ($rcp_theme_footer_html !== '') : ?>
		<?php echo $rcp_theme_footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php else : ?>
		<footer class="bg-white border-t border-gray-100 py-12">
			<div class="rcp-wide mx-auto px-6 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0 text-[10px] text-gray-400 uppercase tracking-widest font-bold">
				<div><?php echo esc_html('© ' . gmdate('Y') . ' Red Cultural'); ?></div>
				<div class="flex space-x-6">
					<a href="<?php echo esc_url(home_url('/contacto')); ?>" class="hover:text-gray-600"><?php echo esc_html__('Contacto', 'red-cultural-pages'); ?></a>
					<a href="<?php echo esc_url(home_url('/terminos-y-condiciones')); ?>" class="hover:text-gray-600"><?php echo esc_html__('Términos y Condiciones', 'red-cultural-pages'); ?></a>
				</div>
			</div>
		</footer>
	<?php endif; ?>

	<div id="red-cultural-course-alert" class="fixed bottom-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white px-6 py-3 rounded-full text-sm font-medium shadow-xl opacity-0 translate-y-4 transition-all pointer-events-none z-50">
		<?php echo esc_html__('Iniciando proceso de inscripción...', 'red-cultural-pages'); ?>
	</div>

	<script>
		(function () {
			function showAlert() {
				var alertBox = document.getElementById('red-cultural-course-alert');
				if (!alertBox) return;
				alertBox.classList.remove('opacity-0', 'translate-y-4');
				alertBox.classList.add('opacity-100', 'translate-y-0');
				setTimeout(function () {
					alertBox.classList.add('opacity-0', 'translate-y-4');
					alertBox.classList.remove('opacity-100', 'translate-y-0');
				}, 3000);
			}

			var detailsBtn = document.getElementById('red-cultural-course-scroll-details');
			var details = document.getElementById('red-cultural-course-main');
			if (detailsBtn && details) {
				detailsBtn.addEventListener('click', function () {
					details.scrollIntoView({ behavior: 'smooth', block: 'start' });
				});
			}

			var joinBtn = document.querySelector('.btn-join');
			if (joinBtn) {
				joinBtn.addEventListener('click', showAlert);
			}

			function navigateLessonCard(card) {
				if (!card) return;
				// When admin is editing lesson details inline, do not navigate.
				if (card.classList && (card.classList.contains('rcil-editing') || card.querySelector('.rcil-video-editor.is-open'))) {
					return;
				}
				var href = card.getAttribute('data-rcp-href');
				if (!href) return;
				window.location.href = href;
			}

			document.addEventListener('click', function (e) {
				var target = e.target;
				if (!target) return;
				// Never navigate when interacting with admin inline editor UI.
				if (target.closest && (target.closest('.rcil-lesson-edit-trigger') || target.closest('.rcil-video-editor') || target.closest('#rc-author-admin-box') || target.closest('#rc-author-edit-trigger'))) return;
				if (target.closest && target.closest('a')) return;

				var card = target.closest ? target.closest('.rcp-lesson-card') : null;
				if (card && card.classList && (card.classList.contains('rcil-editing') || card.querySelector('.rcil-video-editor.is-open'))) {
					return;
				}
				if (card) navigateLessonCard(card);
			});

			document.addEventListener('keydown', function (e) {
				if (e.key !== 'Enter' && e.key !== ' ') return;
				var target = e.target;
				if (!target) return;
				if (target.closest && (target.closest('.rcil-lesson-edit-trigger') || target.closest('.rcil-video-editor'))) return;
				var card = target.closest ? target.closest('.rcp-lesson-card') : null;
				if (!card) return;
				if (card.classList && (card.classList.contains('rcil-editing') || card.querySelector('.rcil-video-editor.is-open'))) {
					return;
				}
				e.preventDefault();
				navigateLessonCard(card);
			});

			function syncSidebarOffset() {
				var sidebar = document.getElementById('red-cultural-course-sidebar');
				var header = document.getElementById('red-cultural-header');
				if (!sidebar || !header) return;

				if (window.innerWidth < 1024) {
					sidebar.style.setProperty('--rcp-sidebar-offset', '0px');
					return;
				}

				// Reset to measure natural flow position, then compute required offset.
				sidebar.style.setProperty('--rcp-sidebar-offset', '0px');
				var headerBottom = header.getBoundingClientRect().bottom;
				var sidebarTop = sidebar.getBoundingClientRect().top;
				var delta = headerBottom - sidebarTop;
				sidebar.style.setProperty('--rcp-sidebar-offset', Math.round(delta) + 'px');
			}

			// Run after layout settles (images/fonts), and on resize.
			syncSidebarOffset();
			window.addEventListener('load', syncSidebarOffset);
			window.addEventListener('resize', syncSidebarOffset);
			setTimeout(syncSidebarOffset, 50);

			if (window.lucide && typeof window.lucide.createIcons === 'function') {
				window.lucide.createIcons();
			}
		})();
	</script>
	<?php wp_footer(); ?>
</body>
</html>
