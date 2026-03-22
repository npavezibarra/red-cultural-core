<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

// Pre-render block theme template parts BEFORE wp_head so their assets are enqueued in the correct place.
$rcp_theme_header_html = '';
$rcp_theme_footer_html = '';
if (function_exists('do_blocks')) {
	$rcp_theme_header_html = (string) do_blocks('<!-- wp:template-part {"slug":"header","area":"header"} /-->');
	$rcp_theme_footer_html = (string) do_blocks('<!-- wp:template-part {"slug":"footer","area":"footer"} /-->');
}

$uploads = wp_get_upload_dir();
$uploads_base = isset($uploads['baseurl']) ? (string) $uploads['baseurl'] : (string) content_url('/uploads');
$uploads_base = rtrim($uploads_base, '/');

// Images: prefer local uploads base URL, fall back to live CDN.
$cocha_logo_rel = '/2024/08/CoChaRedLogo.png';
$cocha_logo_local = $uploads_base . $cocha_logo_rel;
$cocha_logo_live = 'https://red-cultural.cl/wp-content/uploads/2024/08/CoChaRedLogo.png';

$hosts_rel = '/2025/12/maidebarbara.jpg';
$hosts_local = $uploads_base . $hosts_rel;
$hosts_live = 'https://red-cultural.cl/wp-content/uploads/2025/12/maidebarbara.jpg';

$banner_rel = '/2025/12/Stockholm3.jpg';
$banner_local = $uploads_base . $banner_rel;
$banner_live = 'https://red-cultural.cl/wp-content/uploads' . $banner_rel;

// Gallery images (relative to uploads base).
$gallery_items = array(
	array(
		'slug' => 'copenhagen',
		'rel'  => '/2025/12/Copenhagen.jpg',
		'alt'  => 'Copenhagen',
	),
	array(
		'slug' => 'flam-berger',
		'rel'  => '/2025/12/FlamBerger.jpg',
		'alt'  => 'FlamBerger',
	),
	array(
		'slug' => 'oslo',
		'rel'  => '/2025/12/Oslo.jpg',
		'alt'  => 'Oslo',
	),
	array(
		'slug' => 'stockholm',
		'rel'  => '/2025/12/Stockholm.jpg',
		'alt'  => 'Stockholm',
	),
	array(
		'slug' => 'castillo-egen',
		'rel'  => '/2025/12/CastilloEgen.jpg',
		'alt'  => 'CastilloEgen',
	),
);

?><!DOCTYPE html>
<html lang="es">
<head>
	<meta id="red-cultural-viaje-italia-meta-charset" charset="UTF-8">
	<meta id="red-cultural-viaje-italia-meta-viewport" name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html((string) wp_get_document_title()); ?></title>
	<script id="red-cultural-viaje-italia-tailwind" src="https://cdn.tailwindcss.com"></script>
	<link id="red-cultural-viaje-italia-font-montserrat" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
	<link id="red-cultural-viaje-italia-material-symbols" rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
	<style id="red-cultural-viaje-italia-style">
		body{font-family:'Montserrat',sans-serif;margin:0;padding:0}
		.material-symbols-outlined{font-family:'Material Symbols Outlined'!important;font-weight:normal;font-style:normal;font-size:28px;line-height:1;letter-spacing:normal;text-transform:none;display:inline-block;white-space:nowrap;word-wrap:normal;direction:ltr;-webkit-font-feature-settings:'liga';-webkit-font-smoothing:antialiased;font-variation-settings:'FILL' 0,'wght' 500,'GRAD' 0,'opsz' 24}
		.banner-bg{
			background-image:
				linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.2)),
				url('<?php echo esc_url($banner_local); ?>'),
				url('<?php echo esc_url($banner_live); ?>');
			background-size:cover,cover,cover;
			background-position:center,center,center;
			height:550px;
		}
		.overlap-container{margin-top:-180px;position:relative;z-index:20}
		#red-cultural-viaje-italia-hero-content{max-width:1180px;margin:0 auto}
		#red-cultural-viaje-italia-hero-logo-wrap{top:-80px}
		#red-cultural-viaje-italia-hero{height:420px}
		#red-cultural-viaje-italia-gallery{max-width:var(--wp--style--global--wide-size);margin:0 auto;padding:10px 16px 84px}
		#red-cultural-viaje-italia-gallery-title{font-size:40px;line-height:1.05;font-weight:900;letter-spacing:-.02em;margin:0 0 18px;color:#111827;text-align:center}
		#red-cultural-viaje-italia-gallery-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:20px;list-style:none;margin:0;padding:0}
		#red-cultural-viaje-italia-gallery-grid a{display:block;border-radius:18px;overflow:hidden;background:#f3f4f6;box-shadow:0 10px 24px rgba(0,0,0,.08);transform:translateZ(0);transition:transform .22s ease, box-shadow .22s ease;text-decoration:none}
		#red-cultural-viaje-italia-gallery-grid a:hover{transform:translateY(-2px);box-shadow:0 14px 34px rgba(0,0,0,.12)}
		#red-cultural-viaje-italia-gallery-grid img{display:block;width:100%;height:240px;object-fit:cover}
		@media (max-width: 1100px){
			#red-cultural-viaje-italia-gallery-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
		}
		@media (max-width: 640px){
			#red-cultural-viaje-italia-gallery{padding-top:0}
			#red-cultural-viaje-italia-gallery-title{font-size:32px}
			#red-cultural-viaje-italia-gallery-grid{grid-template-columns:1fr}
			#red-cultural-viaje-italia-gallery-grid img{height:220px}
		}

		#red-cultural-viaje-italia-itinerary{background:black;color:#fff}
		#red-cultural-viaje-italia-itinerary-inner{max-width:900px;margin:0 auto;padding:56px 16px 64px}
		#red-cultural-viaje-italia-itinerary-title{font-size:40px;line-height:1.05;font-weight:900;letter-spacing:-.02em;margin:0 0 18px;text-align:center}
		#red-cultural-viaje-italia-itinerary-table{width:100%;border-collapse:collapse;background:transparent;border-top:1px solid #fff;border-bottom:1px solid #fff}
		#red-cultural-viaje-italia-itinerary-table thead th{background:transparent;color:#fff;font-weight:700;padding:15px 10px;text-align:left;border-bottom:2px solid #fff;text-transform:uppercase}
		#red-cultural-viaje-italia-itinerary-table tbody td{padding:12px 10px;vertical-align:top;font-size:14px;line-height:1.4;color:#fff;border-bottom:1px solid #fff;border-right:1px solid rgba(255,255,255,0.25)}
		#red-cultural-viaje-italia-itinerary-table tbody td:last-child{border-right:none}
		#red-cultural-viaje-italia-itinerary-table tbody tr:last-child td{border-bottom:none}
		#red-cultural-viaje-italia-itinerary-table tbody td:nth-child(1){min-width:120px}
		#red-cultural-viaje-italia-itinerary-table tbody td:nth-child(4){min-width:160px;font-weight:700}
		#red-cultural-viaje-italia-itinerary-table .rcp-itin-day{font-weight:900;font-size:18px;line-height:1.1;color:#FF0000}
		#red-cultural-viaje-italia-itinerary-table .rcp-itin-subdate{display:block;margin-top:4px;font-size:12px;font-weight:400;color:#fff;opacity:0.85}
		@media (max-width: 768px){
			#red-cultural-viaje-italia-itinerary-table thead{display:none}
			#red-cultural-viaje-italia-itinerary-table,
			#red-cultural-viaje-italia-itinerary-table tbody,
			#red-cultural-viaje-italia-itinerary-table tr,
			#red-cultural-viaje-italia-itinerary-table td{display:block;width:100%}
			#red-cultural-viaje-italia-itinerary-table tr{margin-bottom:12px;border:1px solid #fff;border-radius:4px;padding:10px}
			#red-cultural-viaje-italia-itinerary-table tbody td{text-align:right;padding:8px 10px;position:relative;border-bottom:1px dotted #fff;border-right:none}
			#red-cultural-viaje-italia-itinerary-table tbody td:before{content:attr(data-label);position:absolute;left:10px;width:45%;font-weight:700;text-align:left;color:#fff;opacity:0.9}
			#red-cultural-viaje-italia-itinerary-table tbody td:last-child{border-bottom:none}
		}

		#red-cultural-viaje-italia-rates{max-width:900px;margin:0 auto;padding:62px 16px 88px}
		#red-cultural-viaje-italia-rates-title{margin:0 0 38px;font-size:44px;line-height:1.05;font-weight:900;letter-spacing:-.02em;text-align:center;color:#111827}
		#red-cultural-viaje-italia-rates-grid{display:grid;grid-template-columns:1fr 1fr;gap:72px;align-items:start}
		#red-cultural-viaje-italia-rates-price-left,#red-cultural-viaje-italia-rates-price-right{margin:0;text-align:center;font-size:56px;line-height:1;font-weight:900;color:#6b7280}
		#red-cultural-viaje-italia-rates-sub-left,#red-cultural-viaje-italia-rates-sub-right{margin:14px 0 0;text-align:center;font-size:18px;color:#6b7280}
		#red-cultural-viaje-italia-rates .rcp-rate-note{display:block;margin-top:10px;font-size:16px;line-height:1.35;font-weight:800;color:#111827}
		#red-cultural-viaje-italia-rates-include-title,#red-cultural-viaje-italia-rates-exclude-title{margin:26px 0 18px;text-align:center;font-size:40px;line-height:1;font-weight:900;color:#ff0000}
		#red-cultural-viaje-italia-rates-include-list,#red-cultural-viaje-italia-rates-exclude-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:22px}
		#red-cultural-viaje-italia-rates-include-list li,#red-cultural-viaje-italia-rates-exclude-list li{display:grid;grid-template-columns:44px 1fr;align-items:start;gap:18px}
		#red-cultural-viaje-italia-rates-include-list .material-symbols-outlined,#red-cultural-viaje-italia-rates-exclude-list .material-symbols-outlined{color:#ff0000;margin-top:2px}
		#red-cultural-viaje-italia-rates-include-list p,#red-cultural-viaje-italia-rates-exclude-list p{margin:0;font-size:20px;line-height:1.55;color:#6b7280}
		@media (max-width: 900px){
			#red-cultural-viaje-italia-rates-grid{display:flex;flex-direction:column;gap:44px}
			#red-cultural-viaje-italia-rates-col-left,#red-cultural-viaje-italia-rates-col-right{display:contents}
			#red-cultural-viaje-italia-rates-price-block-left{order:1}
			#red-cultural-viaje-italia-rates-price-block-right{order:2}
			#red-cultural-viaje-italia-rates-include-block{order:3}
			#red-cultural-viaje-italia-rates-exclude-block{order:4}
			#red-cultural-viaje-italia-rates-price-left,#red-cultural-viaje-italia-rates-price-right{font-size:48px}
			#red-cultural-viaje-italia-rates-include-title,#red-cultural-viaje-italia-rates-exclude-title{font-size:36px}
		}
		@media (max-width: 640px){
			#red-cultural-viaje-italia-rates{padding-left:30px;padding-right:30px}
			#red-cultural-viaje-italia-rates-include-list li,
			#red-cultural-viaje-italia-rates-exclude-list li{
				grid-template-columns:1fr;
				justify-items:center;
				text-align:center;
			}
			#red-cultural-viaje-italia-rates-include-list .material-symbols-outlined,
			#red-cultural-viaje-italia-rates-exclude-list .material-symbols-outlined{margin-top:0}
		}

		#red-cultural-viaje-italia-interest{
			background-image: 
				linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)),
				url('<?php echo esc_url($banner_local); ?>'), 
				url('<?php echo esc_url($banner_live); ?>');
			background-size:cover;
			background-position:center;
			color:#fff
		}
		#red-cultural-viaje-italia-interest-inner{max-width:900px;margin:0 auto;padding:84px 16px;display:grid;grid-template-columns:1fr 1fr;gap:56px;align-items:start}
		#red-cultural-viaje-italia-interest-copy{text-align:center}
		#red-cultural-viaje-italia-interest-question{margin:0;font-size:18px;line-height:1.2;font-weight:600;letter-spacing:.42em;text-transform:uppercase;color:rgba(255,255,255,.55)}
		#red-cultural-viaje-italia-interest-desc{margin:24px 0 0;font-size:28px;line-height:1.25;color:rgba(255,255,255,.78);font-weight:500}
		#red-cultural-viaje-italia-interest-trip-title{margin:30px 0 0;font-size:48px;line-height:1.05;color:#fff;font-weight:900;letter-spacing:-.02em}
		#red-cultural-viaje-italia-interest-trip-dates{margin:14px 0 0;font-size:28px;line-height:1.2;color:rgba(255,255,255,.78);font-weight:500}
		#red-cultural-viaje-italia-interest-form{display:flex;flex-direction:column;gap:14px}
		#red-cultural-viaje-italia-interest-success{margin:0 0 10px;padding:12px 14px;border-radius:10px;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.18);font-size:14px;color:#fff}
		#red-cultural-viaje-italia-interest-form label{display:block;font-size:11px;letter-spacing:.22em;text-transform:uppercase;color:rgba(255,255,255,.72);font-weight:800;margin:0 0 6px}
		#red-cultural-viaje-italia-interest-form input,
		#red-cultural-viaje-italia-interest-form textarea{
			width:100%;
			padding:12px 12px;
			border-radius:8px;
			border:1px solid rgba(255,255,255,.35);
			background:transparent;
			color:#fff;
			font-size:14px;
			outline:none;
			transition:border-color .18s ease, box-shadow .18s ease, background-color .18s ease;
		}
		#red-cultural-viaje-italia-interest-form textarea{min-height:120px;resize:vertical}
		#red-cultural-viaje-italia-interest-form input::placeholder,
		#red-cultural-viaje-italia-interest-form textarea::placeholder{color:rgba(255,255,255,.45)}
		#red-cultural-viaje-italia-interest-form input:focus,
		#red-cultural-viaje-italia-interest-form textarea:focus{border-color:rgba(255,255,255,.8);box-shadow:0 0 0 3px rgba(255,255,255,.14);background-color:rgba(255,255,255,.04)}
			#red-cultural-viaje-italia-interest-submit{margin-top:6px;display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:12px 14px;border-radius:6px;border:0;background:#fff;color:#000;font-weight:900;letter-spacing:.22em;text-transform:uppercase;font-size:11px;cursor:pointer;transition:transform .12s ease, opacity .12s ease}
			#red-cultural-viaje-italia-interest-submit:hover{opacity:.92}
			#red-cultural-viaje-italia-interest-submit:active{transform:translateY(1px)}
			@media (max-width: 900px) and (min-width: 501px){
				#red-cultural-viaje-italia-interest-form{
					width:min(460px,100%);
					max-width:none;
					margin-left:auto;
					margin-right:auto;
					justify-self:center;
				}
				#red-cultural-viaje-italia-interest-form > div{width:100%}
			}
			@media (max-width: 900px){
				#red-cultural-viaje-italia-interest-inner{grid-template-columns:1fr;gap:34px}
				#red-cultural-viaje-italia-interest-trip-title{font-size:42px}
				#red-cultural-viaje-italia-interest-trip-dates{font-size:22px}
				#red-cultural-viaje-italia-interest-desc{font-size:22px}
			}

			#red-cultural-viaje-italia-conditions{background:#fff;color:#111827}
			#red-cultural-viaje-italia-conditions-inner{max-width:900px;margin:0 auto;padding:78px 16px 92px}
			#red-cultural-viaje-italia-conditions-title{margin:0 0 18px;font-size:44px;line-height:1.05;font-weight:900;letter-spacing:-.02em;text-align:center}
			#red-cultural-viaje-italia-conditions-subtitle{margin:0 0 18px;font-size:20px;line-height:1.4;font-weight:700;color:#111827}
			#red-cultural-viaje-italia-conditions h3{margin:34px 0 12px;font-size:24px;line-height:1.2;font-weight:900;color:#111827}
			#red-cultural-viaje-italia-conditions p{margin:0 0 10px;color:#374151;line-height:1.6}

			#red-cultural-viaje-italia-conditions .rcp-conditions-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
			#red-cultural-viaje-italia-conditions .rcp-conditions-table{width:100%;min-width:820px;border-collapse:collapse;background:#fff;border:1px solid #e5e7eb;font-size:.75em}
			#red-cultural-viaje-italia-conditions .rcp-conditions-table th{background:#f9fafb;color:#111827;font-weight:800;padding:14px 16px;border-bottom:1px solid #e5e7eb;text-align:left;vertical-align:bottom}
			#red-cultural-viaje-italia-conditions .rcp-conditions-table td{padding:14px 16px;border-top:1px solid #e5e7eb;color:#6b7280;vertical-align:top}
			#red-cultural-viaje-italia-conditions .rcp-conditions-table th + th,
			#red-cultural-viaje-italia-conditions .rcp-conditions-table td + td{border-left:1px solid #e5e7eb}

			#red-cultural-viaje-italia-conditions .rcp-conditions-bullets{margin:18px 0 0;padding:0;list-style:none;display:block}
			#red-cultural-viaje-italia-conditions .rcp-conditions-bullets li{margin:6px 0;font-size:16px;line-height:1.6;color:#6b7280}

			#red-cultural-viaje-italia-conditions .rcp-conditions-lines{margin:8px 0 18px;padding:0;list-style:none;display:block}
			#red-cultural-viaje-italia-conditions .rcp-conditions-lines li{margin:6px 0;font-size:16px;line-height:1.6;color:#6b7280}

			@media (max-width: 640px){
				#red-cultural-viaje-italia-conditions-title{font-size:36px}
				#red-cultural-viaje-italia-conditions-subtitle{font-size:18px}
				#red-cultural-viaje-italia-conditions h3{font-size:22px}
			}
	</style>
	<?php wp_head(); ?>
</head>
<body id="red-cultural-viaje-italia-page" <?php body_class('bg-white text-gray-800'); ?>>
	<?php if (function_exists('wp_body_open')) { wp_body_open(); } ?>

	<?php
	if ($rcp_theme_header_html !== '') {
		echo '<div id="red-cultural-viaje-italia-site-header">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $rcp_theme_header_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<main id="red-cultural-viaje-italia-main">
		<header id="red-cultural-viaje-italia-hero" class="relative banner-bg flex flex-col items-center justify-start pt-20 px-4 text-center z-10">
			<div id="red-cultural-viaje-italia-hero-content" class="relative w-full flex flex-col items-center">
				<div id="red-cultural-viaje-italia-hero-inner" class="relative w-full flex flex-col items-center">
					<div id="red-cultural-viaje-italia-hero-logo-wrap" class="absolute top-6 right-0 md:right-6">
						<img
							id="red-cultural-viaje-italia-hero-logo"
							src="<?php echo esc_url($cocha_logo_local); ?>"
							data-fallback="<?php echo esc_url($cocha_logo_live); ?>"
							alt="Cocha Logo"
							class="h-12 md:h-16 w-auto"
							loading="lazy"
							referrerpolicy="no-referrer"
							onerror="if(this.dataset.fallback&&this.src!==this.dataset.fallback){this.src=this.dataset.fallback;}"
						>
					</div>

					<div id="red-cultural-viaje-italia-hero-text">
						<h1 id="red-cultural-viaje-italia-title" class="text-4xl md:text-6xl font-bold text-white mb-2 drop-shadow-2xl">
							Escandinavia
						</h1>
						<p id="red-cultural-viaje-italia-dates" class="text-xl md:text-2xl text-white font-medium drop-shadow-lg">
							25-agosto al 09-septiembre de 2026
						</p>
					</div>
				</div>
			</div>
		</header>

		<div id="red-cultural-viaje-italia-content" class="max-w-5xl mx-auto px-4 pb-20">
			<section id="red-cultural-viaje-italia-overlap-card" class="overlap-container flex flex-col md:flex-row overflow-hidden rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.3)] bg-[#444444] text-white">
				<div id="red-cultural-viaje-italia-overlap-photo-wrap" class="w-full md:w-1/2 h-72 md:h-auto">
					<img
						id="red-cultural-viaje-italia-overlap-photo"
						src="<?php echo esc_url($hosts_local); ?>"
						data-fallback="<?php echo esc_url($hosts_live); ?>"
						alt="Magdalena y Bárbara"
						class="w-full h-full object-cover"
						loading="lazy"
						referrerpolicy="no-referrer"
						onerror="if(this.dataset.fallback&&this.src!==this.dataset.fallback){this.src=this.dataset.fallback;}"
					>
				</div>

				<div id="red-cultural-viaje-italia-overlap-info" class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center space-y-6">
					<div id="red-cultural-viaje-italia-overlap-heading">
						<h2 id="red-cultural-viaje-italia-overlap-title" class="text-lg md:text-2xl font-semibold leading-tight">
							<span id="red-cultural-viaje-italia-overlap-title-line-1">Viaja junto a Magdalena Merbilháa</span>
							<span id="red-cultural-viaje-italia-overlap-title-line-2" class="block">y Bárbara Bustamante</span>
						</h2>
					</div>

					<hr id="red-cultural-viaje-italia-overlap-divider" class="border-t border-gray-500 w-full">

					<div id="red-cultural-viaje-italia-overlap-contact" class="space-y-4">
						<div id="red-cultural-viaje-italia-overlap-email">
							<p id="red-cultural-viaje-italia-overlap-email-label" class="text-xs uppercase tracking-widest text-gray-400 mb-1">Inscripciones en:</p>
							<p id="red-cultural-viaje-italia-overlap-email-value" class="text-lg md:text-xl font-bold">magdalena@redcultural.cl</p>
						</div>
						<div id="red-cultural-viaje-italia-overlap-phone">
							<p id="red-cultural-viaje-italia-overlap-phone-value" class="text-2xl font-bold">+56 9 9322 3163</p>
						</div>
					</div>
				</div>
			</section>

			<section id="red-cultural-viaje-italia-features" class="mt-24 text-center">
				<h2 id="red-cultural-viaje-italia-features-title" class="text-3xl md:text-4xl font-bold text-gray-800 mb-14">
					Disfruta una experiencia única
				</h2>

				<div id="red-cultural-viaje-italia-features-list" class="max-w-3xl mx-auto text-left space-y-8 px-4">
					<div id="red-cultural-viaje-italia-feature-1" class="flex items-center space-x-5">
						<div id="red-cultural-viaje-italia-feature-1-icon" class="flex-shrink-0">
							<svg id="red-cultural-viaje-italia-feature-1-svg" class="w-7 h-7 text-red-600" fill="currentColor" viewBox="0 0 20 20">
								<path id="red-cultural-viaje-italia-feature-1-path" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
							</svg>
						</div>
						<p id="red-cultural-viaje-italia-feature-1-text" class="text-lg md:text-xl text-gray-600">
							Sé parte de lugares llenos de historia y belleza única.
						</p>
					</div>

					<div id="red-cultural-viaje-italia-feature-2" class="flex items-center space-x-5">
						<div id="red-cultural-viaje-italia-feature-2-icon" class="flex-shrink-0">
							<svg id="red-cultural-viaje-italia-feature-2-svg" class="w-7 h-7 text-red-600" fill="currentColor" viewBox="0 0 20 20">
								<path id="red-cultural-viaje-italia-feature-2-path" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
							</svg>
						</div>
						<p id="red-cultural-viaje-italia-feature-2-text" class="text-lg md:text-xl text-gray-600">
							Aprende de la mano de una experta en historia y viajes culturales.
						</p>
					</div>

					<div id="red-cultural-viaje-italia-feature-3" class="flex items-center space-x-5">
							<div id="red-cultural-viaje-italia-feature-3-icon" class="flex-shrink-0">
								<svg id="red-cultural-viaje-italia-feature-3-svg" class="w-7 h-7 text-red-600" fill="currentColor" viewBox="0 0 20 20">
									<path id="red-cultural-viaje-italia-feature-3-path" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
								</svg>
							</div>
						<p id="red-cultural-viaje-italia-feature-3-text" class="text-lg md:text-xl text-gray-600">
							Conoce y comparte con gente nueva igual de apasionada que tú por la historia y viajes.
						</p>
					</div>
				</div>
			</section>
		</div>

		<section id="red-cultural-viaje-italia-gallery" aria-label="Galería">
			<h2 id="red-cultural-viaje-italia-gallery-title">Galería</h2>
			<ul id="red-cultural-viaje-italia-gallery-grid">
				<?php foreach ($gallery_items as $index => $item) : ?>
					<?php
					$rel = (string) $item['rel'];
					$local = $uploads_base . $rel;
					$live = 'https://red-cultural.cl/wp-content/uploads' . $rel;
					$slug = isset($item['slug']) ? (string) $item['slug'] : (string) $index;
					$alt = isset($item['alt']) ? (string) $item['alt'] : '';
					?>
					<li id="<?php echo esc_attr('red-cultural-viaje-italia-gallery-item-' . $slug); ?>">
						<a
							id="<?php echo esc_attr('red-cultural-viaje-italia-gallery-link-' . $slug); ?>"
							href="<?php echo esc_url($local); ?>"
							target="_blank"
							rel="noopener noreferrer"
						>
							<img
								id="<?php echo esc_attr('red-cultural-viaje-italia-gallery-img-' . $slug); ?>"
								src="<?php echo esc_url($local); ?>"
								data-fallback="<?php echo esc_url($live); ?>"
								alt="<?php echo esc_attr($alt); ?>"
								loading="lazy"
								referrerpolicy="no-referrer"
								onerror="if(this.dataset.fallback&&this.src!==this.dataset.fallback){this.closest('a').href=this.dataset.fallback;this.src=this.dataset.fallback;}"
							>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>

		<section id="red-cultural-viaje-italia-itinerary" aria-label="Itinerario">
			<div id="red-cultural-viaje-italia-itinerary-inner">
				<h2 id="red-cultural-viaje-italia-itinerary-title">Itinerario</h2>

				<table id="red-cultural-viaje-italia-itinerary-table">
					<thead id="red-cultural-viaje-italia-itinerary-thead">
						<tr id="red-cultural-viaje-italia-itinerary-head-row">
							<th id="red-cultural-viaje-italia-itinerary-th-date">Fecha</th>
							<th id="red-cultural-viaje-italia-itinerary-th-localities">Localidades</th>
							<th id="red-cultural-viaje-italia-itinerary-th-itinerary">Itinerario</th>
							<th id="red-cultural-viaje-italia-itinerary-th-hotels">Hoteles</th>
						</tr>
					</thead>

					<tbody id="red-cultural-viaje-italia-itinerary-tbody">
						<tr id="red-cultural-viaje-italia-itinerary-day-1">
							<td id="red-cultural-viaje-italia-itinerary-day-1-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-italia-itinerary-day-1-day">Día 1<span class="rcp-itin-subdate" id="red-cultural-viaje-italia-itinerary-day-1-subdate">Martes 25 de Agosto</span></span></td>
							<td id="red-cultural-viaje-italia-itinerary-day-1-localities" data-label="Localidades">Santiago - Madrid</td>
							<td id="red-cultural-viaje-italia-itinerary-day-1-itinerary" data-label="Itinerario">Salida en vuelo IB 118 a las 11:55 hrs. desde Santiago a Madrid.</td>
							<td id="red-cultural-viaje-italia-itinerary-day-1-hotels" data-label="Hoteles">-</td>
						</tr>

						<tr id="red-cultural-viaje-italia-itinerary-day-2">
							<td id="red-cultural-viaje-italia-itinerary-day-2-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-italia-itinerary-day-2-day">Día 2<span class="rcp-itin-subdate" id="red-cultural-viaje-italia-itinerary-day-2-subdate">Miércoles 26 de Agosto</span></span></td>
							<td id="red-cultural-viaje-italia-itinerary-day-2-localities" data-label="Localidades">Madrid - Estocolmo</td>
							<td id="red-cultural-viaje-italia-itinerary-day-2-itinerary" data-label="Itinerario">10:15 hrs. salida en vuelo IB 823 desde Madrid a Estocolmo. 14:10 hrs. llegada al Aeropuerto de Estocolmo y traslado al hotel.</td>
							<td id="red-cultural-viaje-italia-itinerary-day-2-hotels" data-label="Hoteles">Reisen Hotel</td>
						</tr>

						<tr id="red-cultural-viaje-italia-itinerary-day-3">
							<td id="red-cultural-viaje-italia-itinerary-day-3-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-italia-itinerary-day-3-day">Día 3<span class="rcp-itin-subdate" id="red-cultural-viaje-italia-itinerary-day-3-subdate">Jueves 27 de Agosto</span></span></td>
							<td id="red-cultural-viaje-italia-itinerary-day-3-localities" data-label="Localidades">Estocolmo</td>
							<td id="red-cultural-viaje-italia-itinerary-day-3-itinerary" data-label="Itinerario">Visita guiada por Estocolmo.</td>
							<td id="red-cultural-viaje-italia-itinerary-day-3-hotels" data-label="Hoteles">Reisen Hotel</td>
						</tr>

						<tr id="red-cultural-viaje-italia-itinerary-day-4">
							<td id="red-cultural-viaje-italia-itinerary-day-4-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-italia-itinerary-day-4-day">Día 4<span class="rcp-itin-subdate" id="red-cultural-viaje-italia-itinerary-day-4-subdate">Viernes 28 de Agosto</span></span></td>
							<td id="red-cultural-viaje-italia-itinerary-day-4-localities" data-label="Localidades">Estocolmo</td>
							<td id="red-cultural-viaje-italia-itinerary-day-4-itinerary" data-label="Itinerario">Visita al Vasa Museum y el Stockholm City Hall.</td>
							<td id="red-cultural-viaje-italia-itinerary-day-4-hotels" data-label="Hoteles">Reisen Hotel</td>
						</tr>

						<tr id="red-cultural-viaje-italia-itinerary-day-5">
							<td id="red-cultural-viaje-italia-itinerary-day-5-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-italia-itinerary-day-5-day">Día 5<span class="rcp-itin-subdate" id="red-cultural-viaje-italia-itinerary-day-5-subdate">Sábado 29 de Agosto</span></span></td>
							<td id="red-cultural-viaje-italia-itinerary-day-5-localities" data-label="Localidades">Estocolmo - Copenhague</td>
							<td id="red-cultural-viaje-italia-itinerary-day-5-itinerary" data-label="Itinerario">Tren de Estocolmo a Copenhague donde alojaremos.</td>
							<td id="red-cultural-viaje-italia-itinerary-day-5-hotels" data-label="Hoteles">NH Collection Copenhagen</td>
						</tr>

						<tr id="red-cultural-viaje-italia-itinerary-day-6">
							<td id="red-cultural-viaje-italia-itinerary-day-6-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-italia-itinerary-day-6-day">Día 6<span class="rcp-itin-subdate" id="red-cultural-viaje-italia-itinerary-day-6-subdate">Domingo 30 de Agosto</span></span></td>
							<td id="red-cultural-viaje-italia-itinerary-day-6-localities" data-label="Localidades">Copenhague</td>
							<td id="red-cultural-viaje-italia-itinerary-day-6-itinerary" data-label="Itinerario">Visita por las cercanías de Copenhague: el Palacio de Frederiksborg en Hillerød y Castillo de Kronborg.</td>
							<td id="red-cultural-viaje-italia-itinerary-day-6-hotels" data-label="Hoteles">NH Collection Copenhagen</td>
						</tr>

						<tr id="red-cultural-viaje-italia-itinerary-day-7">
							<td id="red-cultural-viaje-italia-itinerary-day-7-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-italia-itinerary-day-7-day">Día 7<span class="rcp-itin-subdate" id="red-cultural-viaje-italia-itinerary-day-7-subdate">Lunes 31 de Agosto</span></span></td>
							<td id="red-cultural-viaje-italia-itinerary-day-7-localities" data-label="Localidades">Copenhague - Fyn - Odense</td>
							<td id="red-cultural-viaje-italia-itinerary-day-7-itinerary" data-label="Itinerario">Visitaremos la casa de Hans Christian Andersen y el Castillo de Egeskov.</td>
							<td id="red-cultural-viaje-italia-itinerary-day-7-hotels" data-label="Hoteles">NH Collection Copenhagen</td>
						</tr>

						<tr id="red-cultural-viaje-italia-itinerary-day-8">
							<td id="red-cultural-viaje-italia-itinerary-day-8-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-italia-itinerary-day-8-day">Día 8<span class="rcp-itin-subdate" id="red-cultural-viaje-italia-itinerary-day-8-subdate">Martes 01 de Septiembre</span></span></td>
							<td id="red-cultural-viaje-italia-itinerary-day-8-localities" data-label="Localidades">Copenhague - Oslo</td>
							<td id="red-cultural-viaje-italia-itinerary-day-8-itinerary" data-label="Itinerario">Recorrido panorámico por Copenhague. Crucero nocturno de Copenhague a Oslo. Cena y alojamiento a bordo.</td>
							<td id="red-cultural-viaje-italia-itinerary-day-8-hotels" data-label="Hoteles">Alojamiento a bordo del Crucero</td>
						</tr>

						<tr id="red-cultural-viaje-italia-itinerary-day-9">
							<td id="red-cultural-viaje-italia-itinerary-day-9-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-italia-itinerary-day-9-day">Día 9<span class="rcp-itin-subdate" id="red-cultural-viaje-italia-itinerary-day-9-subdate">Miércoles 02 de Septiembre</span></span></td>
							<td id="red-cultural-viaje-italia-itinerary-day-9-localities" data-label="Localidades">Oslo</td>
							<td id="red-cultural-viaje-italia-itinerary-day-9-itinerary" data-label="Itinerario">Recorrido por Oslo finalizando en el Parque de Esculturas de Vigeland.</td>
							<td id="red-cultural-viaje-italia-itinerary-day-9-hotels" data-label="Hoteles">Radisson Blu Plaza Oslo</td>
						</tr>

						<tr id="red-cultural-viaje-italia-itinerary-day-10">
							<td id="red-cultural-viaje-italia-itinerary-day-10-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-italia-itinerary-day-10-day">Día 10<span class="rcp-itin-subdate" id="red-cultural-viaje-italia-itinerary-day-10-subdate">Jueves 03 de Septiembre</span></span></td>
							<td id="red-cultural-viaje-italia-itinerary-day-10-localities" data-label="Localidades">Oslo</td>
							<td id="red-cultural-viaje-italia-itinerary-day-10-itinerary" data-label="Itinerario">Visita al National Museum (Nasjonalmuseet) y al Munch Museum.</td>
							<td id="red-cultural-viaje-italia-itinerary-day-10-hotels" data-label="Hoteles">Radisson Blu Plaza Oslo</td>
						</tr>

						<tr id="red-cultural-viaje-italia-itinerary-day-11">
							<td id="red-cultural-viaje-italia-itinerary-day-11-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-italia-itinerary-day-11-day">Día 11<span class="rcp-itin-subdate" id="red-cultural-viaje-italia-itinerary-day-11-subdate">Viernes 04 de Septiembre</span></span></td>
							<td id="red-cultural-viaje-italia-itinerary-day-11-localities" data-label="Localidades">Oslo - Flam</td>
							<td id="red-cultural-viaje-italia-itinerary-day-11-itinerary" data-label="Itinerario">Tren de Oslo a Myrdal y luego tren a Flam para alojar. Cena en Ægir Pub Viking Plank.</td>
							<td id="red-cultural-viaje-italia-itinerary-day-11-hotels" data-label="Hoteles">Flåmsbrygga Hotel</td>
						</tr>

						<tr id="red-cultural-viaje-italia-itinerary-day-12">
							<td id="red-cultural-viaje-italia-itinerary-day-12-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-italia-itinerary-day-12-day">Día 12<span class="rcp-itin-subdate" id="red-cultural-viaje-italia-itinerary-day-12-subdate">Sábado 05 de Septiembre</span></span></td>
							<td id="red-cultural-viaje-italia-itinerary-day-12-localities" data-label="Localidades">Flam</td>
							<td id="red-cultural-viaje-italia-itinerary-day-12-itinerary" data-label="Itinerario">FjordSafari en RIB-boat. Cena.</td>
							<td id="red-cultural-viaje-italia-itinerary-day-12-hotels" data-label="Hoteles">Flåmsbrygga Hotel</td>
						</tr>

						<tr id="red-cultural-viaje-italia-itinerary-day-13">
							<td id="red-cultural-viaje-italia-itinerary-day-13-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-italia-itinerary-day-13-day">Día 13<span class="rcp-itin-subdate" id="red-cultural-viaje-italia-itinerary-day-13-subdate">Domingo 06 de Septiembre</span></span></td>
							<td id="red-cultural-viaje-italia-itinerary-day-13-localities" data-label="Localidades">Flam - Bergen</td>
							<td id="red-cultural-viaje-italia-itinerary-day-13-itinerary" data-label="Itinerario">Traslado a Bergen con visita a Viking Valley, Gudvangen. Cena.</td>
							<td id="red-cultural-viaje-italia-itinerary-day-13-hotels" data-label="Hoteles">Bergen Børs</td>
						</tr>

						<tr id="red-cultural-viaje-italia-itinerary-day-14">
							<td id="red-cultural-viaje-italia-itinerary-day-14-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-italia-itinerary-day-14-day">Día 14<span class="rcp-itin-subdate" id="red-cultural-viaje-italia-itinerary-day-14-subdate">Lunes 07 de Septiembre</span></span></td>
							<td id="red-cultural-viaje-italia-itinerary-day-14-localities" data-label="Localidades">Bergen</td>
							<td id="red-cultural-viaje-italia-itinerary-day-14-itinerary" data-label="Itinerario">Visita en Bergen. Cena.</td>
							<td id="red-cultural-viaje-italia-itinerary-day-14-hotels" data-label="Hoteles">Bergen Børs</td>
						</tr>

						<tr id="red-cultural-viaje-italia-itinerary-day-15">
							<td id="red-cultural-viaje-italia-itinerary-day-15-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-italia-itinerary-day-15-day">Día 15<span class="rcp-itin-subdate" id="red-cultural-viaje-italia-itinerary-day-15-subdate">Martes 08 de Septiembre</span></span></td>
							<td id="red-cultural-viaje-italia-itinerary-day-15-localities" data-label="Localidades">Bergen - Oslo - Madrid</td>
							<td id="red-cultural-viaje-italia-itinerary-day-15-itinerary" data-label="Itinerario">10:10 hrs. salida en vuelo SK 260 de Bergen a Oslo. 15:00 hrs. salida en vuelo IB 954 de Oslo a Madrid. 23:55 hrs. salida en vuelo IB 117 de Madrid a Santiago.</td>
							<td id="red-cultural-viaje-italia-itinerary-day-15-hotels" data-label="Hoteles">-</td>
						</tr>

						<tr id="red-cultural-viaje-italia-itinerary-day-16">
							<td id="red-cultural-viaje-italia-itinerary-day-16-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-italia-itinerary-day-16-day">Día 16<span class="rcp-itin-subdate" id="red-cultural-viaje-italia-itinerary-day-16-subdate">Miércoles 09 de Septiembre</span></span></td>
							<td id="red-cultural-viaje-italia-itinerary-day-16-localities" data-label="Localidades">Santiago</td>
							<td id="red-cultural-viaje-italia-itinerary-day-16-itinerary" data-label="Itinerario">Llegada a Santiago a las 07:20 hrs.</td>
							<td id="red-cultural-viaje-italia-itinerary-day-16-hotels" data-label="Hoteles">-</td>
						</tr>
					</tbody>
				</table>
			</div>
		</section>

		<section id="red-cultural-viaje-italia-rates" aria-label="Tarifas">
			<h2 id="red-cultural-viaje-italia-rates-title">Tarifas Preventa</h2>
			<p id="red-cultural-viaje-italia-rates-preventa-sub" class="text-center text-gray-600 text-lg font-semibold -mt-6 mb-10">Hasta 30 Abril de 2026</p>

			<div id="red-cultural-viaje-italia-rates-preventa-grid" class="grid grid-cols-1 md:grid-cols-2 gap-12 max-w-4xl mx-auto mb-16">
				<div id="red-cultural-viaje-italia-rates-preventa-double" class="text-center">
					<p id="red-cultural-viaje-italia-rates-preventa-double-price" class="text-5xl md:text-6xl font-black text-gray-500">USD 11.139</p>
					<p id="red-cultural-viaje-italia-rates-preventa-double-sub" class="mt-3 text-lg text-gray-500">Por persona en base doble</p>
				</div>
				<div id="red-cultural-viaje-italia-rates-preventa-single" class="text-center">
					<p id="red-cultural-viaje-italia-rates-preventa-single-price" class="text-5xl md:text-6xl font-black text-gray-500">USD 14.269</p>
					<p id="red-cultural-viaje-italia-rates-preventa-single-sub" class="mt-3 text-lg text-gray-500">Por persona en base single</p>
				</div>
			</div>

			<h2 id="red-cultural-viaje-italia-rates-normal-title" class="text-center text-[44px] leading-[1.05] font-black tracking-[-.02em] text-gray-800 mb-10">Tarifas Normales</h2>

			<div id="red-cultural-viaje-italia-rates-normal-grid" class="grid grid-cols-1 md:grid-cols-2 gap-12 max-w-4xl mx-auto mb-16">
				<div id="red-cultural-viaje-italia-rates-normal-double" class="text-center">
					<p id="red-cultural-viaje-italia-rates-normal-double-price" class="text-5xl md:text-6xl font-black text-gray-500">USD 11.869</p>
					<p id="red-cultural-viaje-italia-rates-normal-double-sub" class="mt-3 text-lg text-gray-500">Por persona en base doble</p>
				</div>
				<div id="red-cultural-viaje-italia-rates-normal-single" class="text-center">
					<p id="red-cultural-viaje-italia-rates-normal-single-price" class="text-5xl md:text-6xl font-black text-gray-500">USD 15.249</p>
					<p id="red-cultural-viaje-italia-rates-normal-single-sub" class="mt-3 text-lg text-gray-500">Por persona en base single</p>
				</div>
			</div>

			<div id="red-cultural-viaje-italia-rates-grid" class="grid grid-cols-1 md:grid-cols-2 gap-14 max-w-5xl mx-auto">
				<div id="red-cultural-viaje-italia-rates-include-block">
					<p id="red-cultural-viaje-italia-rates-include-title">Incluye</p>
					<ul id="red-cultural-viaje-italia-rates-include-list">
						<li id="red-cultural-viaje-italia-rates-include-1">
							<span id="red-cultural-viaje-italia-rates-include-1-icon" class="material-symbols-outlined" aria-hidden="true">flight</span>
							<p id="red-cultural-viaje-italia-rates-include-1-text">Pasajes aéreos en clase turista.</p>
						</li>
						<li id="red-cultural-viaje-italia-rates-include-2">
							<span id="red-cultural-viaje-italia-rates-include-2-icon" class="material-symbols-outlined" aria-hidden="true">hotel</span>
							<p id="red-cultural-viaje-italia-rates-include-2-text">Alojamiento en hoteles mencionados o similares con desayuno.</p>
						</li>
						<li id="red-cultural-viaje-italia-rates-include-3">
							<span id="red-cultural-viaje-italia-rates-include-3-icon" class="material-symbols-outlined" aria-hidden="true">task_alt</span>
							<p id="red-cultural-viaje-italia-rates-include-3-text">Entradas a sitios a visitar según itinerario.</p>
						</li>
						<li id="red-cultural-viaje-italia-rates-include-4">
							<span id="red-cultural-viaje-italia-rates-include-4-icon" class="material-symbols-outlined" aria-hidden="true">directions_bus</span>
							<p id="red-cultural-viaje-italia-rates-include-4-text">Traslados terrestres en bus exclusivo para el grupo.</p>
						</li>
						<li id="red-cultural-viaje-italia-rates-include-5">
							<span id="red-cultural-viaje-italia-rates-include-5-icon" class="material-symbols-outlined" aria-hidden="true">favorite</span>
							<p id="red-cultural-viaje-italia-rates-include-5-text">Asistencia en viaje Universal Assistance plan Value.</p>
						</li>
					</ul>
				</div>

				<div id="red-cultural-viaje-italia-rates-exclude-block">
					<p id="red-cultural-viaje-italia-rates-exclude-title">No incluye</p>
					<ul id="red-cultural-viaje-italia-rates-exclude-list">
						<li id="red-cultural-viaje-italia-rates-exclude-1">
							<span id="red-cultural-viaje-italia-rates-exclude-1-icon" class="material-symbols-outlined" aria-hidden="true">payments</span>
							<p id="red-cultural-viaje-italia-rates-exclude-1-text">Propinas, gastos personales o ningún otro servicio no especificado.</p>
						</li>
					</ul>
				</div>
			</div>
		</section>

		<section id="red-cultural-viaje-italia-interest" aria-label="Interés">
			<div id="red-cultural-viaje-italia-interest-inner">
				<div id="red-cultural-viaje-italia-interest-copy">
					<p id="red-cultural-viaje-italia-interest-question">¿Estás interesado?</p>
					<p id="red-cultural-viaje-italia-interest-desc">Llena el formulario para más información sobre el</p>
					<p id="red-cultural-viaje-italia-interest-trip-title">Escandinavia 2026</p>
					<p id="red-cultural-viaje-italia-interest-trip-dates">25 de agosto al 09 de septiembre de 2026</p>
				</div>

				<form
					id="red-cultural-viaje-italia-interest-form"
					method="post"
					action="<?php echo esc_url((string) admin_url('admin-post.php')); ?>"
				>
					<?php if (isset($_GET['rcp_ve_interest']) && (string) $_GET['rcp_ve_interest'] === 'success') : ?>
						<p id="red-cultural-viaje-italia-interest-success">¡Gracias! Te contactaremos pronto.</p>
					<?php endif; ?>

					<input type="hidden" id="red-cultural-viaje-italia-interest-action" name="action" value="rcp_viaje_escandinavia_interest">
					<?php wp_nonce_field('rcp_viaje_escandinavia_interest', 'rcp_ve_nonce'); ?>

					<div id="red-cultural-viaje-italia-interest-field-name">
						<label id="red-cultural-viaje-italia-interest-label-name" for="red-cultural-viaje-italia-interest-input-name">Nombre</label>
						<input id="red-cultural-viaje-italia-interest-input-name" name="rcp_ve_name" type="text" autocomplete="name" placeholder="Tu nombre" required>
					</div>

					<div id="red-cultural-viaje-italia-interest-field-email">
						<label id="red-cultural-viaje-italia-interest-label-email" for="red-cultural-viaje-italia-interest-input-email">Email</label>
						<input id="red-cultural-viaje-italia-interest-input-email" name="rcp_ve_email" type="email" autocomplete="email" placeholder="correo@ejemplo.com" required>
					</div>

					<div id="red-cultural-viaje-italia-interest-field-phone">
						<label id="red-cultural-viaje-italia-interest-label-phone" for="red-cultural-viaje-italia-interest-input-phone">Teléfono</label>
						<input id="red-cultural-viaje-italia-interest-input-phone" name="rcp_ve_phone" type="tel" autocomplete="tel" placeholder="+56 9 1234 5678">
					</div>

					<div id="red-cultural-viaje-italia-interest-field-message">
						<label id="red-cultural-viaje-italia-interest-label-message" for="red-cultural-viaje-italia-interest-input-message">Mensaje</label>
						<textarea id="red-cultural-viaje-italia-interest-input-message" name="rcp_ve_message" placeholder="Cuéntanos qué necesitas..."></textarea>
					</div>

					<button id="red-cultural-viaje-italia-interest-submit" type="submit">Enviar</button>
				</form>
			</div>
		</section>

		<section id="red-cultural-viaje-italia-conditions" aria-label="Condiciones">
			<div id="red-cultural-viaje-italia-conditions-inner">
				<h2 id="red-cultural-viaje-italia-conditions-title">Condiciones</h2>

				<p id="red-cultural-viaje-italia-conditions-subtitle">Políticas de reserva y cancelación:</p>

				<div id="red-cultural-viaje-italia-conditions-table-wrap" class="rcp-conditions-table-wrap" aria-label="Políticas de reserva y cancelación">
					<table id="red-cultural-viaje-italia-conditions-table" class="rcp-conditions-table">
						<thead id="red-cultural-viaje-italia-conditions-table-head">
							<tr id="red-cultural-viaje-italia-conditions-table-head-row">
								<th id="red-cultural-viaje-italia-conditions-table-th-stage">Etapa de pago</th>
								<th id="red-cultural-viaje-italia-conditions-table-th-amount">Monto</th>
								<th id="red-cultural-viaje-italia-conditions-table-th-deadline">Fecha límite</th>
								<th id="red-cultural-viaje-italia-conditions-table-th-notes">Observación</th>
							</tr>
						</thead>
						<tbody id="red-cultural-viaje-italia-conditions-table-body">
							<tr id="red-cultural-viaje-italia-conditions-table-row-1">
								<td id="red-cultural-viaje-italia-conditions-table-td-1-stage">Primer abono</td>
								<td id="red-cultural-viaje-italia-conditions-table-td-1-amount">USD 3.500 por persona</td>
								<td id="red-cultural-viaje-italia-conditions-table-td-1-deadline">Al momento de solicitar la reserva</td>
								<td id="red-cultural-viaje-italia-conditions-table-td-1-notes">No reembolsable</td>
							</tr>
							<tr id="red-cultural-viaje-italia-conditions-table-row-2">
								<td id="red-cultural-viaje-italia-conditions-table-td-2-stage">Segundo abono</td>
								<td id="red-cultural-viaje-italia-conditions-table-td-2-amount">USD 3.155 por persona</td>
								<td id="red-cultural-viaje-italia-conditions-table-td-2-deadline">Hasta 31-marzo-2026</td>
								<td id="red-cultural-viaje-italia-conditions-table-td-2-notes">No reembolsable</td>
							</tr>
							<tr id="red-cultural-viaje-italia-conditions-table-row-3">
								<td id="red-cultural-viaje-italia-conditions-table-td-3-stage">Saldo final</td>
								<td id="red-cultural-viaje-italia-conditions-table-td-3-amount">Según tipo de tarifa preventa o normal y tipo de habitación</td>
								<td id="red-cultural-viaje-italia-conditions-table-td-3-deadline">Hasta 25-mayo-2026</td>
								<td id="red-cultural-viaje-italia-conditions-table-td-3-notes">No reembolsable</td>
							</tr>
						</tbody>
					</table>
				</div>

				<ul id="red-cultural-viaje-italia-conditions-bullets" class="rcp-conditions-bullets">
					<li id="red-cultural-viaje-italia-conditions-bullets-1">· Se requiere un mínimo de 25 pasajeros confirmados de lo contrario el viaje se cancela.</li>
					<li id="red-cultural-viaje-italia-conditions-bullets-2">· Pasajes aéreos: se puede cotizar upgrade de cabina sujeto a disponibilidad.</li>
					<li id="red-cultural-viaje-italia-conditions-bullets-3">· Asistencia en viaje aplica tarifa extra para mayores de 70 años.</li>
				</ul>

				<h3 id="red-cultural-viaje-italia-conditions-baggage-title">Políticas de Equipaje</h3>
				<div id="red-cultural-viaje-italia-conditions-baggage">
					<p id="red-cultural-viaje-italia-conditions-baggage-ib-title"><strong>Vuelos operados por IBERIA (IB)</strong> el equipaje permitido por persona es:</p>
					<ul id="red-cultural-viaje-italia-conditions-baggage-ib-list" class="rcp-conditions-lines">
						<li id="red-cultural-viaje-italia-conditions-baggage-ib-1">01 maleta de bodega de hasta 23 kilos, de hasta 158 cms. (alto + ancho + largo)</li>
						<li id="red-cultural-viaje-italia-conditions-baggage-ib-2">01 maleta de cabina de hasta 10 kilos, medidas 56 cms. x 40 cms. x 20cms.</li>
						<li id="red-cultural-viaje-italia-conditions-baggage-ib-3">01 articulo personal, medidas 40 cms. x 30 cms. x 15cms.</li>
					</ul>

					<p id="red-cultural-viaje-italia-conditions-baggage-sk-title"><strong>Vuelo operado por SAS (SK)</strong> el equipaje permitido por persona es:</p>
					<ul id="red-cultural-viaje-italia-conditions-baggage-sk-list" class="rcp-conditions-lines">
						<li id="red-cultural-viaje-italia-conditions-baggage-sk-1">01 maleta de bodega de hasta 23 kilos de hasta 158 cms. (alto + ancho + largo)</li>
						<li id="red-cultural-viaje-italia-conditions-baggage-sk-2">01 maleta de cabina de hasta 8 kilos, medidas 55 cms. x 40 cms. x 23cms.</li>
						<li id="red-cultural-viaje-italia-conditions-baggage-sk-3">01 articulo personal de hasta 18 litros, medidas 40 cms. x 30 cms. x 15cms.</li>
					</ul>
				</div>

				<h3 id="red-cultural-viaje-italia-conditions-docs-title">Documentación:</h3>
				<ul id="red-cultural-viaje-italia-conditions-docs-list" class="rcp-conditions-bullets">
					<li id="red-cultural-viaje-italia-conditions-docs-1">· Es responsabilidad de cada pasajero ir provisto de un pasaporte vigente y dotado de todos los visados y requisitos necesarios.</li>
				</ul>

				<h3 id="red-cultural-viaje-italia-conditions-variations-title">Variaciones:</h3>
				<ul id="red-cultural-viaje-italia-conditions-variations-list" class="rcp-conditions-bullets">
					<li id="red-cultural-viaje-italia-conditions-variations-1">· La información de hoteles mencionados, tarifas, itinerario, horarios de llegada y salida, fechas de operación, etc., está sujeta a posibles modificaciones.</li>
				</ul>
			</div>
		</section>

		<footer id="red-cultural-viaje-italia-footer" class="bg-gray-50 py-12 text-center text-gray-400 text-sm border-t border-gray-100">
			<span id="red-cultural-viaje-italia-footer-text">&copy; 2026 Red Cultural &amp; COCHA. Todos los derechos reservados.</span>
		</footer>
	</main>

	<?php
	if ($rcp_theme_footer_html !== '') {
		echo '<div id="red-cultural-viaje-italia-site-footer">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $rcp_theme_footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<?php wp_footer(); ?>
</body>
</html>
