<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('rcp_find_state_code')) {
	function rcp_find_state_code(array $states, string $value): string {
		$clean = strtolower(trim($value));
		foreach ($states as $code => $name) {
			if ($clean === strtolower($code) || $clean === strtolower($name)) {
				return $code;
			}
		}
		return $value;
	}
}

if (!function_exists('rcp_get_state_label')) {
	function rcp_get_state_label(array $states, string $code, string $fallback): string {
		if (isset($states[$code])) {
			return $states[$code];
		}
		$clean = strtolower(trim($code));
		foreach ($states as $name) {
			if ($clean === strtolower($name)) {
				return $name;
			}
		}
		return $fallback;
	}
}

if (!function_exists('WC')) {
	wp_die(esc_html__('WooCommerce es necesario para ver esta página.', 'red-cultural-pages'));
}

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

$user = wp_get_current_user();
$first_name = isset($user->first_name) ? trim((string) $user->first_name) : '';
$display_name = isset($user->display_name) ? trim((string) $user->display_name) : '';
$hello_name = $first_name !== '' ? $first_name : ($display_name !== '' ? $display_name : 'Hola');

$initial_tab = 'escritorio';
$requested_tab = filter_input(INPUT_GET, 'tab', FILTER_SANITIZE_STRING);
if ($requested_tab === null) {
	$requested_tab = '';
}
if ($requested_tab !== '') {
	$requested_tab = strtolower(trim((string) $requested_tab));
	if (in_array($requested_tab, array('cursos', 'pedidos', 'direcciones', 'detalles'), true)) {
		$initial_tab = $requested_tab;
	}
}
if (function_exists('is_wc_endpoint_url')) {
	if (is_wc_endpoint_url('orders') || is_wc_endpoint_url('view-order')) {
		$initial_tab = 'pedidos';
	} elseif (is_wc_endpoint_url('edit-address')) {
		$initial_tab = 'direcciones';
	} elseif (is_wc_endpoint_url('edit-account')) {
		$initial_tab = 'detalles';
	}
}

$orders = array();
if (function_exists('wc_get_orders') && is_user_logged_in()) {
	$orders = wc_get_orders(
		array(
			'customer' => get_current_user_id(),
			'limit' => 50,
			'orderby' => 'date',
			'order' => 'DESC',
			'paginate' => false,
		)
	);
	if (!is_array($orders)) {
		$orders = array();
	}
}

$courses = array();
if (is_user_logged_in() && function_exists('learndash_user_get_enrolled_courses')) {
	$user_id = (int) get_current_user_id();
	$course_ids = learndash_user_get_enrolled_courses($user_id, array(), true);
	$course_ids = is_array($course_ids) ? array_values(array_filter(array_map('intval', $course_ids))) : array();

	foreach (array_slice($course_ids, 0, 50) as $course_id) {
		$course = get_post($course_id);
		if (!($course instanceof WP_Post) || $course->post_status !== 'publish') {
			continue;
		}

		$instructor = (string) get_the_author_meta('display_name', (int) $course->post_author);
		$progress_pct = 0;
		if (function_exists('learndash_course_progress')) {
			$progress = learndash_course_progress(
				array(
					'array' => true,
					'course_id' => (int) $course_id,
					'user_id' => $user_id,
				)
			);
			if (is_array($progress) && isset($progress['percentage'])) {
				$progress_pct = (int) $progress['percentage'];
			}
		}
		$progress_pct = max(0, min(100, $progress_pct));

		$image_url = get_the_post_thumbnail_url($course, 'medium_large');
		$courses[] = array(
			'id' => (int) $course_id,
			'title' => (string) get_the_title($course),
			'url' => (string) get_permalink($course),
			'instructor' => $instructor,
			'progress' => $progress_pct,
			'image' => is_string($image_url) ? $image_url : '',
		);
	}
}

function rcp_wc_format_address_lines(string $type): array {
	if (!function_exists('wc_get_account_formatted_address')) {
		return array();
	}

	$html = (string) wc_get_account_formatted_address($type);
	if ($html === '') {
		return array();
	}

	$text = preg_replace('/<br\\s*\\/?>/i', "\n", $html);
	$text = wp_strip_all_tags((string) $text);
	$text = trim((string) $text);
	if ($text === '') {
		return array();
	}

	$lines = preg_split("/\\r\\n|\\r|\\n/", $text);
	$lines = is_array($lines) ? array_values(array_filter(array_map('trim', $lines), static fn($v): bool => $v !== '')) : array();
	return $lines;
}

$billing_lines = rcp_wc_format_address_lines('billing');
$shipping_lines = rcp_wc_format_address_lines('shipping');

$myaccount_url = function_exists('wc_get_page_permalink') ? (string) wc_get_page_permalink('myaccount') : (string) home_url('/my-account/');
$edit_billing_url = function_exists('wc_get_endpoint_url') ? (string) wc_get_endpoint_url('edit-address', 'billing', $myaccount_url) : '#';
$edit_shipping_url = function_exists('wc_get_endpoint_url') ? (string) wc_get_endpoint_url('edit-address', 'shipping', $myaccount_url) : '#';
$edit_account_url = function_exists('wc_get_endpoint_url') ? (string) wc_get_endpoint_url('edit-account', '', $myaccount_url) : $myaccount_url;
$logout_url = function_exists('wc_logout_url') ? (string) wc_logout_url() : (string) wp_logout_url($myaccount_url);

$uploads = wp_get_upload_dir();
$uploads_base = isset($uploads['baseurl']) ? (string) $uploads['baseurl'] : (string) content_url('/uploads');
$uploads_base = rtrim($uploads_base, '/');
$guest_bg_rel = '/2026/03/hortusconclusus.jpeg';
$guest_bg_local = $uploads_base . $guest_bg_rel;
$guest_bg_live = 'https://red-cultural.cl/wp-content/uploads' . $guest_bg_rel;

?><!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html__('Mi cuenta', 'red-cultural-pages'); ?></title>
	<script>
		// Keep the global theme styles intact (avoid Tailwind preflight).
		window.tailwind = window.tailwind || {};
		window.tailwind.config = { corePlugins: { preflight: false } };
	</script>
	<script src="https://cdn.tailwindcss.com"></script>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
	<?php wp_head(); ?>
	<style>
		div#red-cultural-ma-container{
			padding:20px 0px;
			max-width:var(--wp--style--global--wide-size);
		}
		h1#red-cultural-ma-page-title {
			font-weight: 600;
		}
		body {
			font-family: 'Inter', sans-serif;
			background-color: #fff;
			color: #000;
		}

		/* Typography limits - Max weight 700 */
		.text-huge { font-size: 2.5rem; line-height: 1.1; font-weight: 700; }
		.text-body-lg { font-size: 1.125rem; }
		.label-caps {
			font-size: 0.85rem;
			letter-spacing: 0.15em;
			font-weight: 700;
			text-transform: uppercase;
			color: #737373;
		}

		/* Action Buttons - 6pc round corners */
		.rounded-6pc { border-radius: 6pc; }
		.rounded-6px { border-radius: 6px; }

		/* Sidebar Buttons - 0px round corners */
		.nav-item { border-radius: 0px !important; }

		/* Custom Transitions */
		.tab-content {
			display: none;
			animation: fadeInUp 0.4s ease-out forwards;
		}
		.tab-content.active { display: block; }

		@keyframes fadeInUp {
			from { opacity: 0; transform: translateY(8px); }
			to { opacity: 1; transform: translateY(0); }
		}

		.nav-item.active {
			background-color: #000;
			color: #fff;
		}

		input:focus {
			outline: none;
			border-color: #000;
		}

		::-webkit-scrollbar { width: 4px; }
		::-webkit-scrollbar-track { background: #fff; }
		::-webkit-scrollbar-thumb { background: #000; }

		/* "Ver" buttons (orders list) */
		#red-cultural-ma-pedidos-list a[id^="red-cultural-ma-order-"][id$="-btn-view"]{
			padding:3px 20px;
			border-radius:3px;
		}

		#red-cultural-ma-detalles-submit{
			border-radius:6px;
			padding:15px 20px;
		}

		@media (max-width: 1250px) {
			#red-cultural-ma-container,
			#red-cultural-ma-guest-inner {
				padding-left: 30px !important;
				padding-right: 30px !important;
			}
		}

		h2#red-cultural-ma-cursos-title,
		h2#red-cultural-ma-pedidos-title,
		h2#red-cultural-ma-direcciones-title{
			font-size:24px;
			font-weight:600;
		}

		#red-cultural-ma-guest{
			background-image:
				url('<?php echo esc_url($guest_bg_local); ?>'),
				url('<?php echo esc_url($guest_bg_live); ?>');
			background-size:cover,cover;
			background-position:center,center;
			min-height:70vh;
			position:relative;
			display:flex;
			align-items:center;
		}
		#red-cultural-ma-guest-overlay{
			position:absolute;
			inset:0;
			background:linear-gradient(to top, rgba(0,0,0,.55) 0%, rgba(0,0,0,0) 65%);
		}
		#red-cultural-ma-guest-inner{
			display:flex;
			align-items:center;
			justify-content:center;
			text-align:center;
			width:100%;
		}
		#red-cultural-ma-guest-content{
			max-width:760px;
		}
		#red-cultural-ma-guest-title{
			color:#fff;
			font-size:42px;
			font-weight:500;
			line-height:1.05;
			margin:0 0 10px 0;
		}
		#red-cultural-ma-guest-copy{
			color:rgb(255 255 255 / 100%);
			font-size:24px;
			margin:0;
		}
		#red-cultural-ma-guest-btn-login{
			margin-top:26px;
			background:#fff;
			color:#000;
			border:2px solid #fff;
			border-radius:6px;
			padding:12px 26px;
			font-size:12px;
			font-weight:800;
			letter-spacing:0.25em;
			text-transform:uppercase;
			transition:all .2s ease;
		}
		#red-cultural-ma-guest-btn-login:hover{opacity:.92}
		#red-cultural-ma-guest-btn-login:active{transform:scale(.98)}
		}
		.ui-helper-hidden-accessible {
			display: none !important;
		}
		.ui-autocomplete {
			border-color: #000 !important;
			box-shadow: 0 24px 48px rgba(0, 0, 0, 0.2) !important;
			border-radius: 8px;
			padding: 0 !important;
			margin: 4px 0 0 !important;
		}
		.ui-autocomplete li {
			padding: 0 !important;
		}
		.ui-autocomplete li .rcp-comuna-item {
			padding: 14px 20px;
			font-size: 16px;
			justify-content: space-between;
		}
		.ui-autocomplete li .rcp-comuna-item:not(:last-child) {
			border-bottom: 1px solid #e5e7eb;
		}
		.rcp-comuna-region {
			color: #4b5563;
			font-size: 14px;
			font-weight: 500;
		}
	</style>
</head>
<body id="red-cultural-ma-body" class="selection:bg-black selection:text-white">
	<?php if (function_exists('wp_body_open')) { wp_body_open(); } ?>

	<?php
	if ($rcp_theme_header_html !== '') {
		echo $rcp_theme_header_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<?php if (!is_user_logged_in()) : ?>
		<div id="red-cultural-ma-guest">
			<div id="red-cultural-ma-guest-overlay" aria-hidden="true"></div>
			<main id="red-cultural-ma-guest-inner" class="relative mx-auto px-8 py-20" style="max-width: var(--wp--style--global--wide-size); width: 100%;">
				<div id="red-cultural-ma-guest-content">
					<h1 id="red-cultural-ma-guest-title">Mi cuenta</h1>
					<p id="red-cultural-ma-guest-copy">
						Debes iniciar sesión para acceder a tu cuenta.
					</p>
					<button
						id="red-cultural-ma-guest-btn-login"
						type="button"
						data-rcp-auth-open="1"
					>
						Login
					</button>
				</div>
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
	<?php return; ?>
	<?php endif; ?>


	<div id="red-cultural-ma-container" class="max-w-7xl mx-auto px-8 py-16 md:py-32">
		<div id="red-cultural-ma-page-title-wrap" class="mb-10">
			<h1 id="red-cultural-ma-page-title" class="text-3xl font-semibold text-gray-900 mb-2">Mi cuenta</h1>
		</div>
		<div id="red-cultural-ma-grid" class="grid grid-cols-1 md:grid-cols-12 gap-16">

			<!-- Sidebar Navigation -->
			<nav id="red-cultural-ma-nav" class="md:col-span-3 space-y-2">
				<div id="red-cultural-ma-nav-container" class="space-y-1">
					<button id="red-cultural-ma-nav-escritorio" onclick="showTab('escritorio')" class="nav-item <?php echo $initial_tab === 'escritorio' ? 'active' : 'text-neutral-400 hover:text-black hover:bg-neutral-50'; ?> w-full text-left py-4 px-6 transition-all duration-300 flex items-center justify-between group">
						<span id="red-cultural-ma-nav-escritorio-label" class="text-xs uppercase tracking-[0.2em] font-bold">Escritorio</span>
					</button>
					<button id="red-cultural-ma-nav-cursos" onclick="showTab('cursos')" class="nav-item text-neutral-400 hover:text-black hover:bg-neutral-50 w-full text-left py-4 px-6 transition-all duration-300 flex items-center justify-between group">
						<span id="red-cultural-ma-nav-cursos-label" class="text-xs uppercase tracking-[0.2em] font-bold">Mis Cursos</span>
					</button>
					<button id="red-cultural-ma-nav-pedidos" onclick="showTab('pedidos')" class="nav-item <?php echo $initial_tab === 'pedidos' ? 'active' : 'text-neutral-400 hover:text-black hover:bg-neutral-50'; ?> w-full text-left py-4 px-6 transition-all duration-300 flex items-center justify-between group">
						<span id="red-cultural-ma-nav-pedidos-label" class="text-xs uppercase tracking-[0.2em] font-bold">Pedidos</span>
					</button>
					<button id="red-cultural-ma-nav-direcciones" onclick="showTab('direcciones')" class="nav-item <?php echo $initial_tab === 'direcciones' ? 'active' : 'text-neutral-400 hover:text-black hover:bg-neutral-50'; ?> w-full text-left py-4 px-6 transition-all duration-300 flex items-center justify-between group">
						<span id="red-cultural-ma-nav-direcciones-label" class="text-xs uppercase tracking-[0.2em] font-bold">Direcciones</span>
					</button>
					<button id="red-cultural-ma-nav-detalles" onclick="showTab('detalles')" class="nav-item <?php echo $initial_tab === 'detalles' ? 'active' : 'text-neutral-400 hover:text-black hover:bg-neutral-50'; ?> w-full text-left py-4 px-6 transition-all duration-300 flex items-center justify-between group">
						<span id="red-cultural-ma-nav-detalles-label" class="text-xs uppercase tracking-[0.2em] font-bold">Detalles</span>
					</button>
					<button id="red-cultural-ma-nav-logout" onclick="window.location.href='<?php echo esc_js(esc_url($logout_url)); ?>'" class="w-full text-left py-4 px-6 text-neutral-400 hover:text-black hover:bg-neutral-50 transition-all duration-300 flex items-center justify-between group mt-8">
						<span id="red-cultural-ma-nav-logout-label" class="text-xs uppercase tracking-[0.2em] font-bold">Cerrar Sesión</span>
					</button>
				</div>
			</nav>

			<!-- Main Content -->
			<main id="red-cultural-ma-main" class="md:col-span-9">

				<!-- Tab: Escritorio -->
				<section id="escritorio" class="tab-content <?php echo $initial_tab === 'escritorio' ? 'active' : ''; ?>">
					<div id="red-cultural-ma-escritorio-inner" class="py-10 text-center md:text-left">
						<h2 id="red-cultural-ma-escritorio-title" class="text-huge mb-6 tracking-tight"><?php echo esc_html(sprintf('Hola, %s.', $hello_name)); ?></h2>
						<p id="red-cultural-ma-escritorio-copy" class="text-body-lg text-neutral-500 max-w-xl leading-relaxed font-normal">
							Desde el escritorio de tu cuenta puedes ver tus pedidos recientes, gestionar tus direcciones de envío y facturación y editar tu contraseña y los detalles de tu cuenta.
						</p>
					</div>
				</section>

				<!-- Tab: Mis Cursos -->
				<section id="cursos" class="tab-content">
					<div id="red-cultural-ma-cursos-header" class="border-b-2 border-black pb-6 mb-10">
						<div id="red-cultural-ma-cursos-header-inner" class="flex items-end justify-between gap-6">
							<h2 id="red-cultural-ma-cursos-title" class="text-4xl font-bold tracking-tight">Mis Cursos</h2>
							<div id="red-cultural-ma-cursos-pagination" class="flex items-center gap-3">
								<button id="red-cultural-ma-cursos-pagination-prev" type="button" class="border border-black w-8 h-7 flex items-center justify-center text-sm font-semibold rounded-[3px] hover:bg-black hover:text-white transition-all" aria-label="<?php echo esc_attr__('Anterior', 'red-cultural-pages'); ?>" title="<?php echo esc_attr__('Anterior', 'red-cultural-pages'); ?>">
									&lsaquo;
								</button>
								<span id="red-cultural-ma-cursos-pagination-status" class="text-xs font-semibold text-neutral-400"></span>
								<button id="red-cultural-ma-cursos-pagination-next" type="button" class="border border-black w-8 h-7 flex items-center justify-center text-sm font-semibold rounded-[3px] hover:bg-black hover:text-white transition-all" aria-label="<?php echo esc_attr__('Siguiente', 'red-cultural-pages'); ?>" title="<?php echo esc_attr__('Siguiente', 'red-cultural-pages'); ?>">
									&rsaquo;
								</button>
							</div>
						</div>
					</div>

					<div id="red-cultural-ma-cursos-list" class="space-y-12">
						<?php if (!is_user_logged_in()) : ?>
							<div id="red-cultural-ma-cursos-empty-login">
								<p id="red-cultural-ma-cursos-empty-login-text" class="text-body-lg text-neutral-500">Debes iniciar sesión para ver tus cursos.</p>
							</div>
						<?php elseif ($courses === array()) : ?>
							<div id="red-cultural-ma-cursos-empty">
								<p id="red-cultural-ma-cursos-empty-text" class="text-body-lg text-neutral-500">Aún no tienes cursos asignados.</p>
							</div>
						<?php else : ?>
							<?php foreach ($courses as $course) : ?>
								<?php
								$course_id = isset($course['id']) ? (int) $course['id'] : 0;
								$title = isset($course['title']) ? (string) $course['title'] : '';
								$url = isset($course['url']) ? (string) $course['url'] : '#';
								$instructor = isset($course['instructor']) ? (string) $course['instructor'] : '';
								$progress = isset($course['progress']) ? (int) $course['progress'] : 0;
								$image = isset($course['image']) ? (string) $course['image'] : '';
								?>
								<div id="<?php echo esc_attr('red-cultural-ma-cursos-course-' . (string) $course_id); ?>" class="flex flex-col md:flex-row items-start md:items-center gap-8 group">
									<div id="<?php echo esc_attr('red-cultural-ma-cursos-course-' . (string) $course_id . '-media'); ?>" class="w-full md:w-48 aspect-video bg-neutral-100 flex-shrink-0 relative overflow-hidden">
										<?php if ($image !== '') : ?>
											<img
												id="<?php echo esc_attr('red-cultural-ma-cursos-course-' . (string) $course_id . '-img'); ?>"
												src="<?php echo esc_url($image); ?>"
												alt="<?php echo esc_attr($title); ?>"
												class="absolute inset-0 w-full h-full object-cover"
												loading="lazy"
												decoding="async"
											/>
										<?php else : ?>
											<div id="<?php echo esc_attr('red-cultural-ma-cursos-course-' . (string) $course_id . '-placeholder'); ?>" class="absolute inset-0 flex items-center justify-center">
												<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-neutral-300"><path d="m9 18 6-6-6-6"/></svg>
											</div>
										<?php endif; ?>
									</div>
									<div id="<?php echo esc_attr('red-cultural-ma-cursos-course-' . (string) $course_id . '-body'); ?>" class="flex-1 space-y-3 w-full">
										<a
											id="<?php echo esc_attr('red-cultural-ma-cursos-course-' . (string) $course_id . '-title'); ?>"
											href="<?php echo esc_url($url); ?>"
											class="text-2xl font-bold tracking-tight group-hover:underline decoration-1 underline-offset-4 no-underline"
										><?php echo esc_html($title); ?></a>
										<p id="<?php echo esc_attr('red-cultural-ma-cursos-course-' . (string) $course_id . '-instructor'); ?>" class="label-caps !text-neutral-400"><?php echo esc_html($instructor !== '' ? ('Prof. ' . $instructor) : ''); ?></p>

										<div id="<?php echo esc_attr('red-cultural-ma-cursos-course-' . (string) $course_id . '-progress'); ?>" class="pt-4 max-w-md">
											<div class="flex justify-between text-[10px] uppercase tracking-[0.2em] font-bold mb-2">
												<span id="<?php echo esc_attr('red-cultural-ma-cursos-course-' . (string) $course_id . '-progress-label'); ?>">Progreso</span>
												<span id="<?php echo esc_attr('red-cultural-ma-cursos-course-' . (string) $course_id . '-progress-value'); ?>"><?php echo esc_html((string) $progress . '%'); ?></span>
											</div>
											<div class="w-full h-1 bg-neutral-100 rounded-full overflow-hidden">
												<div id="<?php echo esc_attr('red-cultural-ma-cursos-course-' . (string) $course_id . '-progress-bar'); ?>" class="bg-black h-full transition-all duration-700 ease-out" style="width: <?php echo esc_attr((string) $progress); ?>%"></div>
											</div>
										</div>
									</div>
									<a
										id="<?php echo esc_attr('red-cultural-ma-cursos-course-' . (string) $course_id . '-continue'); ?>"
										href="<?php echo esc_url($url); ?>"
										class="border-2 border-black px-7 py-2 text-xs font-bold uppercase tracking-widest rounded-6px hover:bg-black hover:text-white transition-all duration-300 mt-4 md:mt-0 no-underline"
									>
										Continuar
									</a>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</section>

				<!-- Tab: Pedidos -->
				<section id="pedidos" class="tab-content <?php echo $initial_tab === 'pedidos' ? 'active' : ''; ?>">
					<div id="red-cultural-ma-pedidos-header" class="flex justify-between items-end border-b-2 border-black pb-6 mb-10">
						<h2 id="red-cultural-ma-pedidos-title" class="text-4xl font-bold tracking-tight">Pedidos</h2>
						<div id="red-cultural-ma-pedidos-header-right" class="flex items-center gap-6">
							<span id="red-cultural-ma-pedidos-subtitle" class="text-sm text-neutral-400 font-bold uppercase tracking-widest">Historial</span>
								<div id="red-cultural-ma-pedidos-pagination" class="flex items-center gap-3">
									<button id="red-cultural-ma-pedidos-pagination-prev" type="button" class="border border-black w-8 h-7 flex items-center justify-center text-sm font-semibold rounded-[3px] hover:bg-black hover:text-white transition-all" aria-label="<?php echo esc_attr__('Anterior', 'red-cultural-pages'); ?>" title="<?php echo esc_attr__('Anterior', 'red-cultural-pages'); ?>">
										&lsaquo;
									</button>
									<span id="red-cultural-ma-pedidos-pagination-status" class="text-xs font-semibold text-neutral-400"></span>
									<button id="red-cultural-ma-pedidos-pagination-next" type="button" class="border border-black w-8 h-7 flex items-center justify-center text-sm font-semibold rounded-[3px] hover:bg-black hover:text-white transition-all" aria-label="<?php echo esc_attr__('Siguiente', 'red-cultural-pages'); ?>" title="<?php echo esc_attr__('Siguiente', 'red-cultural-pages'); ?>">
										&rsaquo;
									</button>
								</div>
							</div>
						</div>

					<div id="red-cultural-ma-pedidos-list" class="divide-y divide-neutral-100">
						<?php if (!is_user_logged_in()) : ?>
							<div id="red-cultural-ma-pedidos-empty-login" class="py-8">
								<p id="red-cultural-ma-pedidos-empty-login-text" class="text-body-lg text-neutral-500">Debes iniciar sesión para ver tus pedidos.</p>
							</div>
						<?php elseif ($orders === array()) : ?>
							<div id="red-cultural-ma-pedidos-empty" class="py-8">
								<p id="red-cultural-ma-pedidos-empty-text" class="text-body-lg text-neutral-500">Aún no tienes pedidos.</p>
							</div>
						<?php else : ?>
							<?php foreach ($orders as $order) : ?>
								<?php
								if (!($order instanceof WC_Order)) {
									continue;
								}
								$oid = (int) $order->get_id();
								$order_number = (string) $order->get_order_number();
								$date_created = $order->get_date_created();
								$date_label = $date_created ? (string) wc_format_datetime($date_created, 'F j, Y') : '';
								$status_label = function_exists('wc_get_order_status_name') ? (string) wc_get_order_status_name($order->get_status()) : (string) $order->get_status();
								$total_label = (string) $order->get_formatted_order_total();
								$view_url = (string) $order->get_checkout_order_received_url();
								?>
								<div id="<?php echo esc_attr('red-cultural-ma-order-' . (string) $oid); ?>" class="py-8 flex flex-wrap md:flex-nowrap items-center justify-between group transition-all duration-300">
									<div id="<?php echo esc_attr('red-cultural-ma-order-' . (string) $oid . '-col-order'); ?>" class="flex-1 min-w-[140px] mb-4 md:mb-0">
										<span id="<?php echo esc_attr('red-cultural-ma-order-' . (string) $oid . '-label-order'); ?>" class="label-caps block mb-1">Orden</span>
										<span id="<?php echo esc_attr('red-cultural-ma-order-' . (string) $oid . '-value-order'); ?>" class="font-bold text-xl">#<?php echo esc_html($order_number); ?></span>
									</div>
									<div id="<?php echo esc_attr('red-cultural-ma-order-' . (string) $oid . '-col-date'); ?>" class="flex-1 min-w-[160px] mb-4 md:mb-0">
										<span id="<?php echo esc_attr('red-cultural-ma-order-' . (string) $oid . '-label-date'); ?>" class="label-caps block mb-1">Fecha</span>
										<span id="<?php echo esc_attr('red-cultural-ma-order-' . (string) $oid . '-value-date'); ?>" class="text-body-lg font-medium"><?php echo esc_html($date_label); ?></span>
									</div>
									<div id="<?php echo esc_attr('red-cultural-ma-order-' . (string) $oid . '-col-status'); ?>" class="flex-1 min-w-[140px] mb-4 md:mb-0">
										<span id="<?php echo esc_attr('red-cultural-ma-order-' . (string) $oid . '-label-status'); ?>" class="label-caps block mb-1">Estado</span>
										<span id="<?php echo esc_attr('red-cultural-ma-order-' . (string) $oid . '-value-status'); ?>" class="text-body-lg text-neutral-400"><?php echo esc_html($status_label); ?></span>
									</div>
									<div id="<?php echo esc_attr('red-cultural-ma-order-' . (string) $oid . '-col-total'); ?>" class="flex-1 min-w-[140px] mb-4 md:mb-0">
										<span id="<?php echo esc_attr('red-cultural-ma-order-' . (string) $oid . '-label-total'); ?>" class="label-caps block mb-1">Total</span>
										<span id="<?php echo esc_attr('red-cultural-ma-order-' . (string) $oid . '-value-total'); ?>" class="text-body-lg font-bold"><?php echo wp_kses_post($total_label); ?></span>
									</div>
									<a id="<?php echo esc_attr('red-cultural-ma-order-' . (string) $oid . '-btn-view'); ?>" href="<?php echo esc_url($view_url); ?>" class="border-2 border-black px-10 py-3 text-xs font-bold uppercase tracking-widest rounded-6pc hover:bg-black hover:text-white transition-all duration-300 no-underline">
										Ver
									</a>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</section>

				<!-- Tab: Direcciones -->
				<?php
				$countries = function_exists('WC') && function_exists('WC_Countries') ? new WC_Countries() : null;
				$cl_states = $countries ? $countries->get_states('CL') : array();
				$billing_address_1 = sanitize_text_field((string) get_user_meta($user->ID, 'billing_address_1', true));
				$billing_address_2 = sanitize_text_field((string) get_user_meta($user->ID, 'billing_address_2', true));
				$billing_city = sanitize_text_field((string) get_user_meta($user->ID, 'billing_city', true));
				$billing_state = sanitize_text_field((string) get_user_meta($user->ID, 'billing_state', true));
				$billing_first_name = (string) get_user_meta($user->ID, 'billing_first_name', true);
				$billing_last_name = (string) get_user_meta($user->ID, 'billing_last_name', true);
				$billing_email = (string) get_user_meta($user->ID, 'billing_email', true);
				$billing_phone = (string) get_user_meta($user->ID, 'billing_phone', true);
				if ($billing_first_name === '') $billing_first_name = $user->first_name;
				if ($billing_last_name === '') $billing_last_name = $user->last_name;
				if ($billing_email === '') $billing_email = $user->user_email;

				$shipping_address_1 = sanitize_text_field((string) get_user_meta($user->ID, 'shipping_address_1', true));
				$shipping_address_2 = sanitize_text_field((string) get_user_meta($user->ID, 'shipping_address_2', true));
				$shipping_city = sanitize_text_field((string) get_user_meta($user->ID, 'shipping_city', true));
				$shipping_state = sanitize_text_field((string) get_user_meta($user->ID, 'shipping_state', true));
				$shipping_first_name = (string) get_user_meta($user->ID, 'shipping_first_name', true);
				$shipping_last_name = (string) get_user_meta($user->ID, 'shipping_last_name', true);
				if ($shipping_first_name === '') $shipping_first_name = $user->first_name;
				if ($shipping_last_name === '') $shipping_last_name = $user->last_name;
				function find_state_code(array $states, string $value): string {
					$clean = strtolower(trim($value));
					foreach ($states as $code => $name) {
						if ($clean === strtolower($code) || $clean === strtolower($name)) {
							return $code;
						}
					}
					return $value;
				}
				$billing_state_code = find_state_code($cl_states, $billing_state);
				$shipping_state_code = find_state_code($cl_states, $shipping_state);
				function get_state_label(array $states, string $code, string $fallback): string {
					if (isset($states[$code])) {
						return $states[$code];
					}
					$clean = strtolower(trim($code));
					foreach ($states as $name) {
						if ($clean === strtolower($name)) {
							return $name;
						}
					}
					return $fallback;
				}
				$billing_state_label = get_state_label($cl_states, $billing_state_code, $billing_state);
				$shipping_state_label = get_state_label($cl_states, $shipping_state_code, $shipping_state);
				$billing_lines = array_filter(
					array(
						$billing_address_1,
						$billing_address_2,
						$billing_city,
						$billing_state_label,
					),
					static fn($line): bool => $line !== ''
				);
				$shipping_lines = array_filter(
					array(
						$shipping_address_1,
						$shipping_address_2,
						$shipping_city,
						$shipping_state_label,
					),
					static fn($line): bool => $line !== ''
				);
				$notices = array();
				if (function_exists('wc_get_notices')) {
					$notices = wc_get_notices();
					wc_clear_notices();
				}
				?>

				<section id="direcciones" class="tab-content <?php echo $initial_tab === 'direcciones' ? 'active' : ''; ?>">
					<div id="red-cultural-ma-direcciones-header" class="border-b-2 border-black pb-6 mb-12 flex flex-col md:flex-row md:items-baseline md:justify-between gap-4">
						<h2 id="red-cultural-ma-direcciones-title" class="text-4xl font-bold tracking-tight shrink-0">Direcciones</h2>
						<div id="red-cultural-ma-direcciones-notices" class="text-right flex flex-col items-end gap-1">
							<?php if ($notices !== array()) : ?>
								<?php foreach ($notices as $type => $msg_list) : ?>
									<?php foreach ($msg_list as $msg) : ?>
										<span class="<?php echo $type === 'success' ? 'text-green-600' : 'text-red-500'; ?> text-sm font-semibold">
											<?php echo wp_kses_post((string) ($msg['notice'] ?? '')); ?>
										</span>
									<?php endforeach; ?>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</div>

					<div id="red-cultural-ma-direcciones-grid" class="grid grid-cols-1 md:grid-cols-2 gap-16">
						<div id="red-cultural-ma-direcciones-billing" class="space-y-8">
							<div class="flex items-center justify-between border-b border-neutral-100 pb-3">
								<h3 class="label-caps !text-black">Facturación</h3>
								<button id="red-cultural-ma-direcciones-billing-edit" type="button" class="text-xs uppercase font-bold border-b border-black hover:pb-1 transition-all">Editar</button>
							</div>
							<div id="red-cultural-ma-direcciones-billing-body" class="text-body-lg leading-loose font-normal space-y-1">
								<?php if ($billing_lines !== array()) : ?>
									<?php foreach ($billing_lines as $line) : ?>
										<p class="text-neutral-500"><?php echo esc_html($line); ?></p>
									<?php endforeach; ?>
								<?php else : ?>
									<p class="text-neutral-500">Aún no has configurado este tipo de dirección.</p>
								<?php endif; ?>
							</div>
							<form id="red-cultural-ma-direcciones-billing-form" class="hidden space-y-3" method="post" action="<?php echo esc_url(wc_get_endpoint_url('edit-address', 'billing', $myaccount_url)); ?>">
								<?php wp_nonce_field('woocommerce-edit_address', 'woocommerce-edit-address-nonce'); ?>
								<input type="hidden" name="action" value="edit_address" />
								<input type="hidden" id="billing_country" name="billing_country" value="CL" />
								<input type="hidden" name="billing_first_name" value="<?php echo esc_attr($billing_first_name); ?>" />
								<input type="hidden" name="billing_last_name" value="<?php echo esc_attr($billing_last_name); ?>" />
								<input type="hidden" name="billing_email" value="<?php echo esc_attr($billing_email); ?>" />
								<input type="hidden" name="billing_phone" value="<?php echo esc_attr($billing_phone); ?>" />
								<input type="hidden" name="address_type" value="billing" />
								<label class="block text-xs font-bold uppercase tracking-[0.3em] text-neutral-400">Calle y Número</label>
								<input id="billing_address_1" name="billing_address_1" value="<?php echo esc_attr($billing_address_1); ?>" class="w-full border border-gray-200 rounded-3px px-3 py-2" />
								<label class="block text-xs font-bold uppercase tracking-[0.3em] text-neutral-400">Info Adicional</label>
								<input id="billing_address_2" name="billing_address_2" value="<?php echo esc_attr($billing_address_2); ?>" class="w-full border border-gray-200 rounded-3px px-3 py-2" />
								<label class="block text-xs font-bold uppercase tracking-[0.3em] text-neutral-400">Comuna</label>
								<input id="billing_city" name="billing_city" value="<?php echo esc_attr($billing_city); ?>" class="w-full border border-gray-200 rounded-3px px-3 py-2" />
								<select id="billing_state_temp" name="billing_state_temp" class="hidden" style="display:none;">
									<?php foreach ($cl_states as $state_code => $state_name) : ?>
										<option value="<?php echo esc_attr($state_code); ?>" <?php selected($state_code, $billing_state_code); ?>>
											<?php echo esc_html($state_name); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<label class="block text-xs font-bold uppercase tracking-[0.3em] text-neutral-400">Región</label>
								<input type="text" id="billing_state" name="billing_state" value="<?php echo esc_attr($billing_state_code); ?>" class="w-full border border-gray-200 rounded-3px px-3 py-2 mb-3" readonly />
								<div class="flex gap-3">
									<button type="submit" name="save_address" value="save_address" class="bg-black text-white uppercase tracking-[0.2em] text-xs font-bold px-6 py-3 rounded-6px">Guardar</button>
									<button type="button" data-address-cancel="billing" class="text-xs uppercase font-bold border-b border-neutral-100 hover:pb-1 transition-all">Cancelar</button>
								</div>
							</form>
						</div>

						<div id="red-cultural-ma-direcciones-shipping" class="space-y-8">
							<div class="flex items-center justify-between border-b border-neutral-100 pb-3">
								<h3 class="label-caps">Envío</h3>
								<button id="red-cultural-ma-direcciones-shipping-edit" type="button" class="text-xs uppercase font-bold border-b border-neutral-100 hover:border-black transition-all"><?php echo $shipping_lines !== array() ? 'Editar' : 'Agregar'; ?></button>
							</div>
							<div id="red-cultural-ma-direcciones-shipping-body" class="text-body-lg leading-loose font-normal space-y-1">
								<?php if ($shipping_lines !== array()) : ?>
									<?php foreach ($shipping_lines as $line) : ?>
										<p class="text-neutral-500"><?php echo esc_html($line); ?></p>
									<?php endforeach; ?>
								<?php else : ?>
									<div class="text-sm text-neutral-400 uppercase tracking-[0.3em] border-2 border-dashed border-neutral-200 rounded-3px px-4 py-8 text-center">
										Aún no has configurado este tipo de dirección.
									</div>
								<?php endif; ?>
							</div>
							<form id="red-cultural-ma-direcciones-shipping-form" class="hidden space-y-3" method="post" action="<?php echo esc_url(wc_get_endpoint_url('edit-address', 'shipping', $myaccount_url)); ?>">
								<?php wp_nonce_field('woocommerce-edit_address', 'woocommerce-edit-address-nonce'); ?>
								<input type="hidden" name="action" value="edit_address" />
								<input type="hidden" id="shipping_country" name="shipping_country" value="CL" />
								<input type="hidden" name="shipping_first_name" value="<?php echo esc_attr($shipping_first_name); ?>" />
								<input type="hidden" name="shipping_last_name" value="<?php echo esc_attr($shipping_last_name); ?>" />
								<input type="hidden" name="address_type" value="shipping" />
								<label class="block text-xs font-bold uppercase tracking-[0.3em] text-neutral-400">Calle y Número</label>
								<input id="shipping_address_1" name="shipping_address_1" value="<?php echo esc_attr($shipping_address_1); ?>" class="w-full border border-gray-200 rounded-3px px-3 py-2" />
								<label class="block text-xs font-bold uppercase tracking-[0.3em] text-neutral-400">Info Adicional</label>
								<input id="shipping_address_2" name="shipping_address_2" value="<?php echo esc_attr($shipping_address_2); ?>" class="w-full border border-gray-200 rounded-3px px-3 py-2" />
								<label class="block text-xs font-bold uppercase tracking-[0.3em] text-neutral-400">Comuna</label>
								<input id="shipping_city" name="shipping_city" value="<?php echo esc_attr($shipping_city); ?>" class="w-full border border-gray-200 rounded-3px px-3 py-2" />
								<select id="shipping_state_temp" name="shipping_state_temp" class="hidden" style="display:none;">
									<?php foreach ($cl_states as $state_code => $state_name) : ?>
										<option value="<?php echo esc_attr($state_code); ?>" <?php selected($state_code, $shipping_state_code); ?>>
											<?php echo esc_html($state_name); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<label class="block text-xs font-bold uppercase tracking-[0.3em] text-neutral-400">Región</label>
								<input type="text" id="shipping_state" name="shipping_state" value="<?php echo esc_attr($shipping_state_code); ?>" class="w-full border border-gray-200 rounded-3px px-3 py-2 mb-3" readonly />
								<div class="flex gap-3">
									<button type="submit" name="save_address" value="save_address" class="bg-black text-white uppercase tracking-[0.2em] text-xs font-bold px-6 py-3 rounded-6px">Guardar</button>
									<button type="button" data-address-cancel="shipping" class="text-xs uppercase font-bold border-b border-neutral-100 hover:pb-1 transition-all">Cancelar</button>
								</div>
							</form>
						</div>
					</div>
				</section>

				<!-- Tab: Detalles -->
				<section id="detalles" class="tab-content <?php echo $initial_tab === 'detalles' ? 'active' : ''; ?>">
					<div id="red-cultural-ma-detalles-header" class="border-b-2 border-black pb-6 mb-12">
						<h2 id="red-cultural-ma-detalles-title" class="text-4xl font-bold tracking-tight">Detalles de la cuenta</h2>
					</div>

					<form id="red-cultural-ma-detalles-form" class="space-y-12 max-w-2xl" method="post" action="<?php echo esc_url($edit_account_url); ?>">
						<div id="red-cultural-ma-detalles-grid" class="grid grid-cols-1 md:grid-cols-2 gap-10">
							<div id="red-cultural-ma-detalles-first-name" class="space-y-3">
								<label id="red-cultural-ma-detalles-first-name-label" class="label-caps block">Nombre *</label>
								<input id="red-cultural-ma-detalles-first-name-input" type="text" name="account_first_name" value="<?php echo esc_attr((string) $user->first_name); ?>" class="w-full border-b-2 border-neutral-100 py-3 text-body-lg font-medium transition-all">
							</div>
							<div id="red-cultural-ma-detalles-last-name" class="space-y-3">
								<label id="red-cultural-ma-detalles-last-name-label" class="label-caps block">Apellidos *</label>
								<input id="red-cultural-ma-detalles-last-name-input" type="text" name="account_last_name" value="<?php echo esc_attr((string) $user->last_name); ?>" class="w-full border-b-2 border-neutral-100 py-3 text-body-lg font-medium transition-all">
							</div>
						</div>

						<div id="red-cultural-ma-detalles-display-name" class="space-y-3">
							<label id="red-cultural-ma-detalles-display-name-label" class="label-caps block">Nombre visible *</label>
							<input id="red-cultural-ma-detalles-display-name-input" type="text" name="account_display_name" value="<?php echo esc_attr((string) $user->display_name); ?>" class="w-full border-b-2 border-neutral-100 py-3 text-body-lg font-medium transition-all">
							<p id="red-cultural-ma-detalles-display-name-help" class="text-body-lg text-neutral-500">Así será como se mostrará tu nombre en la sección de tu cuenta y en las valoraciones</p>
						</div>

						<div id="red-cultural-ma-detalles-email" class="space-y-3">
							<label id="red-cultural-ma-detalles-email-label" class="label-caps block">Correo electrónico *</label>
							<input id="red-cultural-ma-detalles-email-input" type="email" name="account_email" value="<?php echo esc_attr((string) $user->user_email); ?>" class="w-full border-b-2 border-neutral-100 py-3 text-body-lg font-medium transition-all">
						</div>

						<div id="red-cultural-ma-detalles-password" class="pt-12 space-y-10 border-t border-neutral-100">
							<h3 id="red-cultural-ma-detalles-password-title" class="label-caps !text-black">Cambio de Contraseña</h3>
							<div id="red-cultural-ma-detalles-password-fields" class="space-y-8">
								<div id="red-cultural-ma-detalles-password-current" class="space-y-3">
									<label id="red-cultural-ma-detalles-password-current-label" class="label-caps block text-xs">Contraseña actual</label>
									<input id="red-cultural-ma-detalles-password-current-input" type="password" name="password_current" placeholder="••••••••" class="w-full border-b-2 border-neutral-100 py-3 text-body-lg transition-all">
								</div>
								<div id="red-cultural-ma-detalles-password-new" class="space-y-3">
									<label id="red-cultural-ma-detalles-password-new-label" class="label-caps block text-xs">Nueva contraseña</label>
									<input id="red-cultural-ma-detalles-password-new-input" type="password" name="password_1" class="w-full border-b-2 border-neutral-100 py-3 text-body-lg transition-all">
								</div>
								<div id="red-cultural-ma-detalles-password-confirm" class="space-y-3">
									<label id="red-cultural-ma-detalles-password-confirm-label" class="label-caps block text-xs">Confirmar nueva contraseña</label>
									<input id="red-cultural-ma-detalles-password-confirm-input" type="password" name="password_2" class="w-full border-b-2 border-neutral-100 py-3 text-body-lg transition-all">
								</div>
							</div>
						</div>

						<?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>
						<input type="hidden" name="action" value="save_account_details" />

						<button id="red-cultural-ma-detalles-submit" type="submit" name="save_account_details" value="1" class="bg-black text-white px-16 py-5 text-xs font-bold uppercase tracking-[0.25em] rounded-6pc hover:bg-neutral-800 transition-all shadow-xl active:scale-[0.97]">
							Guardar cambios
						</button>
					</form>
				</section>

			</main>
		</div>
	</div>

	<script>
		(function () {
			function paginate(sectionId, itemSelector, perPage, controls) {
				var section = document.getElementById(sectionId);
				if (!section) return null;

				var list = section.querySelector(itemSelector);
				if (!list) return null;

				var items = Array.prototype.slice.call(list.children || []).filter(function (el) {
					return el instanceof HTMLElement && el.id && el.id.indexOf(controls.itemIdPrefix) === 0;
				});

				var state = { page: 1, totalPages: 1, items: items };
				state.totalPages = Math.max(1, Math.ceil(items.length / perPage));

				function update() {
					var start = (state.page - 1) * perPage;
					var end = start + perPage;
					state.items.forEach(function (el, idx) {
						el.style.display = (idx >= start && idx < end) ? '' : 'none';
					});

					if (controls.status) {
						controls.status.textContent = state.totalPages > 1 ? (state.page + ' / ' + state.totalPages) : '';
					}
					if (controls.wrap) {
						controls.wrap.style.display = state.totalPages > 1 ? '' : 'none';
					}
					if (controls.prev) controls.prev.disabled = state.page <= 1;
					if (controls.next) controls.next.disabled = state.page >= state.totalPages;
					if (controls.prev) controls.prev.style.opacity = controls.prev.disabled ? '0.35' : '';
					if (controls.next) controls.next.style.opacity = controls.next.disabled ? '0.35' : '';
				}

				function setPage(p) {
					state.page = Math.max(1, Math.min(state.totalPages, p));
					update();
				}

				if (controls.prev) controls.prev.addEventListener('click', function () { setPage(state.page - 1); });
				if (controls.next) controls.next.addEventListener('click', function () { setPage(state.page + 1); });

				update();
				return { update: update, setPage: setPage, state: state };
			}

			window.__rcpMyAccountPagers = window.__rcpMyAccountPagers || {};

			var cursosControls = {
				wrap: document.getElementById('red-cultural-ma-cursos-pagination'),
				prev: document.getElementById('red-cultural-ma-cursos-pagination-prev'),
				next: document.getElementById('red-cultural-ma-cursos-pagination-next'),
				status: document.getElementById('red-cultural-ma-cursos-pagination-status'),
				itemIdPrefix: 'red-cultural-ma-cursos-course-'
			};
			var pedidosControls = {
				wrap: document.getElementById('red-cultural-ma-pedidos-pagination'),
				prev: document.getElementById('red-cultural-ma-pedidos-pagination-prev'),
				next: document.getElementById('red-cultural-ma-pedidos-pagination-next'),
				status: document.getElementById('red-cultural-ma-pedidos-pagination-status'),
				itemIdPrefix: 'red-cultural-ma-order-'
			};

			window.__rcpMyAccountPagers.cursos = paginate('cursos', '#red-cultural-ma-cursos-list', 4, cursosControls);
			window.__rcpMyAccountPagers.pedidos = paginate('pedidos', '#red-cultural-ma-pedidos-list', 5, pedidosControls);
		})();

		function showTab(tabId) {
			const contents = document.querySelectorAll('.tab-content');
			contents.forEach(content => content.classList.remove('active'));

			const navItems = document.querySelectorAll('.nav-item');
			navItems.forEach(item => {
				item.classList.remove('active');
				item.classList.add('text-neutral-400');
			});

			document.getElementById(tabId).classList.add('active');

			const activeNav = Array.from(navItems).find(item => item.getAttribute('onclick').includes(tabId));
			if (activeNav) {
				activeNav.classList.add('active');
				activeNav.classList.remove('text-neutral-400');
			}

			if (window.innerWidth < 768) {
				document.querySelector('main').scrollIntoView({ behavior: 'smooth' });
			}
			updateTabQuery(tabId);
		}

		(function () {
			const url = new URL(window.location.href);
			const tabParam = url.searchParams.get('tab');
			const hash = window.location.hash ? window.location.hash.replace('#', '') : '';

			if (tabParam && document.getElementById(tabParam)) {
				showTab(tabParam);
				return;
			}

			if (hash && document.getElementById(hash)) {
				showTab(hash);
				return;
			}
		})();

		function updateTabQuery(tabId) {
			const url = new URL(window.location.href);
			url.searchParams.set('tab', tabId);
			window.history.replaceState({}, '', url.toString());
		}

		function toggleAddressForm(type, show) {
			const display = document.getElementById(`red-cultural-ma-direcciones-${type}-body`);
			const form = document.getElementById(`red-cultural-ma-direcciones-${type}-form`);
			if (!form || !display) {
				return;
			}
			display.classList.toggle('hidden', show);
			form.classList.toggle('hidden', !show);
		}

		document.addEventListener('DOMContentLoaded', function () {
			const billingEdit = document.getElementById('red-cultural-ma-direcciones-billing-edit');
			const shippingEdit = document.getElementById('red-cultural-ma-direcciones-shipping-edit');
			const cancelButtons = document.querySelectorAll('[data-address-cancel]');
			if (billingEdit) {
				billingEdit.addEventListener('click', function () {
					toggleAddressForm('billing', true);
				});
			}
			if (shippingEdit) {
				shippingEdit.addEventListener('click', function () {
					toggleAddressForm('shipping', true);
				});
			}
			cancelButtons.forEach(function (btn) {
				const type = btn.getAttribute('data-address-cancel');
				btn.addEventListener('click', function () {
					toggleAddressForm(type, false);
				});
			});
		});

	</script>

	<?php
	if ($rcp_theme_footer_html !== '') {
		echo $rcp_theme_footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<?php wp_footer(); ?>
</body>
</html>
