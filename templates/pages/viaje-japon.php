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

$banner_rel = '/2025/12/JapanMain.jpg';
$banner_local = $uploads_base . $banner_rel;
$banner_live = 'https://red-cultural.cl/wp-content/uploads' . $banner_rel;

// Gallery images (relative to uploads base).
$gallery_items = array(
	array(
		'slug' => 'tokio22',
		'rel'  => '/2025/12/Tokio22.jpg',
		'alt'  => 'Tokio22',
	),
	array(
		'slug' => 'snow-japan',
		'rel'  => '/2025/12/SnowJapan.jpg',
		'alt'  => 'SnowJapan',
	),
	array(
		'slug' => 'street-japan',
		'rel'  => '/2025/12/StreetJapan.jpg',
		'alt'  => 'StreetJapan',
	),
	array(
		'slug' => 'tokio1',
		'rel'  => '/2025/12/tokio1.jpg',
		'alt'  => 'tokio1',
	),
	array(
		'slug' => 'fuji',
		'rel'  => '/2025/12/fuji.jpg',
		'alt'  => 'fuji',
	),
);

?><!DOCTYPE html>
<html lang="es">
<head>
	<meta id="red-cultural-viaje-japon-meta-charset" charset="UTF-8">
	<meta id="red-cultural-viaje-japon-meta-viewport" name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html((string) wp_get_document_title()); ?></title>
	<script id="red-cultural-viaje-japon-tailwind" src="https://cdn.tailwindcss.com"></script>
	<link id="red-cultural-viaje-japon-font-montserrat" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
	<link id="red-cultural-viaje-japon-material-symbols" rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
	<style id="red-cultural-viaje-japon-style">
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
		#red-cultural-viaje-japon-hero-content{max-width:1180px;margin:0 auto}
		#red-cultural-viaje-japon-hero-logo-wrap{top:-80px}
		#red-cultural-viaje-japon-hero{height:420px}
		#red-cultural-viaje-japon-gallery{max-width:var(--wp--style--global--wide-size);margin:0 auto;padding:10px 16px 84px}
		#red-cultural-viaje-japon-gallery-title{font-size:40px;line-height:1.05;font-weight:900;letter-spacing:-.02em;margin:0 0 18px;color:#111827;text-align:center}
		#red-cultural-viaje-japon-gallery-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:20px;list-style:none;margin:0;padding:0}
		#red-cultural-viaje-japon-gallery-grid a{display:block;border-radius:18px;overflow:hidden;background:#f3f4f6;box-shadow:0 10px 24px rgba(0,0,0,.08);transform:translateZ(0);transition:transform .22s ease, box-shadow .22s ease;text-decoration:none}
		#red-cultural-viaje-japon-gallery-grid a:hover{transform:translateY(-2px);box-shadow:0 14px 34px rgba(0,0,0,.12)}
		#red-cultural-viaje-japon-gallery-grid img{display:block;width:100%;height:240px;object-fit:cover}
		@media (max-width: 1100px){
			#red-cultural-viaje-japon-gallery-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
		}

		@media (max-width: 640px){
			#red-cultural-viaje-japon-gallery{padding-top:0}
			#red-cultural-viaje-japon-gallery-title{font-size:32px}
			#red-cultural-viaje-japon-gallery-grid{grid-template-columns:1fr}
			#red-cultural-viaje-japon-gallery-grid img{height:220px}
		}

		#red-cultural-viaje-japon-itinerary{background:black;color:#fff}
		#red-cultural-viaje-japon-itinerary-inner{max-width:900px;margin:0 auto;padding:56px 16px 64px}
		#red-cultural-viaje-japon-itinerary-title{font-size:40px;line-height:1.05;font-weight:900;letter-spacing:-.02em;margin:0 0 18px;text-align:center}
		#red-cultural-viaje-japon-itinerary-table{width:100%;border-collapse:collapse;background:transparent;border-top:1px solid #fff;border-bottom:1px solid #fff}
		#red-cultural-viaje-japon-itinerary-table thead th{background:transparent;color:#fff;font-weight:700;padding:15px 10px;text-align:left;border-bottom:2px solid #fff;text-transform:uppercase}
		#red-cultural-viaje-japon-itinerary-table tbody td{padding:12px 10px;vertical-align:top;font-size:14px;line-height:1.4;color:#fff;border-bottom:1px solid #fff;border-right:1px solid rgba(255,255,255,0.25)}
		#red-cultural-viaje-japon-itinerary-table tbody td:last-child{border-right:none}
		#red-cultural-viaje-japon-itinerary-table tbody tr:last-child td{border-bottom:none}
		#red-cultural-viaje-japon-itinerary-table tbody td:nth-child(1){min-width:120px}
		#red-cultural-viaje-japon-itinerary-table tbody td:nth-child(4){min-width:160px;font-weight:700}
		#red-cultural-viaje-japon-itinerary-table .rcp-itin-day{font-weight:900;font-size:18px;line-height:1.1;color:#000000}
		#red-cultural-viaje-japon-itinerary-table .rcp-itin-subdate{display:block;margin-top:4px;font-size:12px;font-weight:400;color:#fff;opacity:0.85}
		@media (max-width: 768px){
			#red-cultural-viaje-japon-itinerary-table thead{display:none}
			#red-cultural-viaje-japon-itinerary-table,
			#red-cultural-viaje-japon-itinerary-table tbody,
			#red-cultural-viaje-japon-itinerary-table tr,
			#red-cultural-viaje-japon-itinerary-table td{display:block;width:100%}
			#red-cultural-viaje-japon-itinerary-table tr{margin-bottom:12px;border:1px solid #fff;border-radius:4px;padding:10px}
			#red-cultural-viaje-japon-itinerary-table tbody td{text-align:right;padding:8px 10px;position:relative;border-bottom:1px dotted #fff;border-right:none}
			#red-cultural-viaje-japon-itinerary-table tbody td:before{content:attr(data-label);position:absolute;left:10px;width:45%;font-weight:700;text-align:left;color:#fff;opacity:0.9}
			#red-cultural-viaje-japon-itinerary-table tbody td:last-child{border-bottom:none}
		}

		#red-cultural-viaje-japon-rates{max-width:900px;margin:0 auto;padding:62px 16px 88px}
		#red-cultural-viaje-japon-rates-title{margin:0 0 38px;font-size:44px;line-height:1.05;font-weight:900;letter-spacing:-.02em;text-align:center;color:#111827}
		#red-cultural-viaje-japon-rates-grid{display:grid;grid-template-columns:1fr 1fr;gap:72px;align-items:start}
		#red-cultural-viaje-japon-rates-price-left,#red-cultural-viaje-japon-rates-price-right{margin:0;text-align:center;font-size:56px;line-height:1;font-weight:900;color:#6b7280}
		#red-cultural-viaje-japon-rates-sub-left,#red-cultural-viaje-japon-rates-sub-right{margin:14px 0 0;text-align:center;font-size:18px;color:#6b7280}
		#red-cultural-viaje-japon-rates-include-title,#red-cultural-viaje-japon-rates-exclude-title{margin:26px 0 18px;text-align:center;font-size:40px;line-height:1;font-weight:900;color:#000000}
		#red-cultural-viaje-japon-rates-include-list,#red-cultural-viaje-japon-rates-exclude-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:22px}
		#red-cultural-viaje-japon-rates-include-list li,#red-cultural-viaje-japon-rates-exclude-list li{display:grid;grid-template-columns:44px 1fr;align-items:start;gap:18px}
		#red-cultural-viaje-japon-rates-include-list .material-symbols-outlined,#red-cultural-viaje-japon-rates-exclude-list .material-symbols-outlined{color:#000000;margin-top:2px}
		#red-cultural-viaje-japon-rates-include-list p,#red-cultural-viaje-japon-rates-exclude-list p{margin:0;font-size:20px;line-height:1.55;color:#6b7280}
		@media (max-width: 900px){
			#red-cultural-viaje-japon-rates-grid{display:flex;flex-direction:column;gap:44px}
			#red-cultural-viaje-japon-rates-col-left,#red-cultural-viaje-japon-rates-col-right{display:contents}
			#red-cultural-viaje-japon-rates-price-block-left{order:1}
			#red-cultural-viaje-japon-rates-price-block-right{order:2}
			#red-cultural-viaje-japon-rates-include-block{order:3}
			#red-cultural-viaje-japon-rates-exclude-block{order:4}
			#red-cultural-viaje-japon-rates-price-left,#red-cultural-viaje-japon-rates-price-right{font-size:48px}
			#red-cultural-viaje-japon-rates-include-title,#red-cultural-viaje-japon-rates-exclude-title{font-size:36px}
		}
		@media (max-width: 640px){
			#red-cultural-viaje-japon-rates{padding-left:30px;padding-right:30px}
			#red-cultural-viaje-japon-rates-include-list li,
			#red-cultural-viaje-japon-rates-exclude-list li{
				grid-template-columns:1fr;
				justify-items:center;
				text-align:center;
			}
			#red-cultural-viaje-japon-rates-include-list .material-symbols-outlined,
			#red-cultural-viaje-japon-rates-exclude-list .material-symbols-outlined{margin-top:0}
		}

		#red-cultural-viaje-japon-interest{
			background-image: 
				linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)),
				url('<?php echo esc_url($banner_local); ?>'), 
				url('<?php echo esc_url($banner_live); ?>');
			background-size:cover;
			background-position:center;
			color:#fff
		}
		#red-cultural-viaje-japon-interest-inner{max-width:900px;margin:0 auto;padding:84px 16px;display:grid;grid-template-columns:1fr 1fr;gap:56px;align-items:start}
		#red-cultural-viaje-japon-interest-copy{text-align:center}
		#red-cultural-viaje-japon-interest-question{margin:0;font-size:18px;line-height:1.2;font-weight:600;letter-spacing:.42em;text-transform:uppercase;color:rgba(255,255,255,.55)}
		#red-cultural-viaje-japon-interest-desc{margin:24px 0 0;font-size:28px;line-height:1.25;color:rgba(255,255,255,.78);font-weight:500}
		#red-cultural-viaje-japon-interest-trip-title{margin:30px 0 0;font-size:48px;line-height:1.05;color:#fff;font-weight:900;letter-spacing:-.02em}
		#red-cultural-viaje-japon-interest-trip-dates{margin:14px 0 0;font-size:28px;line-height:1.2;color:rgba(255,255,255,.78);font-weight:500}
		#red-cultural-viaje-japon-interest-form{display:flex;flex-direction:column;gap:14px}
		#red-cultural-viaje-japon-interest-success{margin:0 0 10px;padding:12px 14px;border-radius:10px;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.18);font-size:14px;color:#fff}
		#red-cultural-viaje-japon-interest-form label{display:block;font-size:11px;letter-spacing:.22em;text-transform:uppercase;color:rgba(255,255,255,.72);font-weight:800;margin:0 0 6px}
		#red-cultural-viaje-japon-interest-form input,
		#red-cultural-viaje-japon-interest-form textarea{
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
		#red-cultural-viaje-japon-interest-form textarea{min-height:120px;resize:vertical}
		#red-cultural-viaje-japon-interest-form input::placeholder,
		#red-cultural-viaje-japon-interest-form textarea::placeholder{color:rgba(255,255,255,.45)}
		#red-cultural-viaje-japon-interest-form input:focus,
		#red-cultural-viaje-japon-interest-form textarea:focus{border-color:rgba(255,255,255,.8);box-shadow:0 0 0 3px rgba(255,255,255,.14);background-color:rgba(255,255,255,.04)}
		#red-cultural-viaje-japon-interest-submit{margin-top:6px;display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:12px 14px;border-radius:6px;border:0;background:#fff;color:#000;font-weight:900;letter-spacing:.22em;text-transform:uppercase;font-size:11px;cursor:pointer;transition:transform .12s ease, opacity .12s ease}
		#red-cultural-viaje-japon-interest-submit:hover{opacity:.92}
		#red-cultural-viaje-japon-interest-submit:active{transform:translateY(1px)}
		@media (max-width: 900px) and (min-width: 501px){
			#red-cultural-viaje-japon-interest-form{
				width:min(460px,100%);
				max-width:none;
				margin-left:auto;
				margin-right:auto;
				justify-self:center;
			}
			#red-cultural-viaje-japon-interest-form > div{width:100%}
		}
		@media (max-width: 900px){
			#red-cultural-viaje-japon-interest-inner{grid-template-columns:1fr;gap:34px}
			#red-cultural-viaje-japon-interest-trip-title{font-size:42px}
			#red-cultural-viaje-japon-interest-trip-dates{font-size:22px}
			#red-cultural-viaje-japon-interest-desc{font-size:22px}
		}

		#red-cultural-viaje-japon-conditions{background:#fff;color:#111827}
		#red-cultural-viaje-japon-conditions-inner{max-width:900px;margin:0 auto;padding:78px 16px 92px}
		#red-cultural-viaje-japon-conditions-title{margin:0 0 26px;font-size:44px;line-height:1.05;font-weight:900;letter-spacing:-.02em;text-align:center}
		#red-cultural-viaje-japon-conditions-grid{display:grid;grid-template-columns:1fr 1fr;gap:56px;align-items:start}
		#red-cultural-viaje-japon-conditions h3{margin:0 0 12px;font-size:16px;letter-spacing:.32em;text-transform:uppercase;font-weight:900;color:#111827}
		#red-cultural-viaje-japon-conditions p{margin:0}
		#red-cultural-viaje-japon-conditions ul{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:10px}
		#red-cultural-viaje-japon-conditions li{font-size:14px;line-height:1.55;color:#374151}
		#red-cultural-viaje-japon-conditions .rcp-cond-note{margin-top:12px;color:#6b7280;font-size:13px;line-height:1.55}
		#red-cultural-viaje-japon-conditions .rcp-cond-block{display:flex;flex-direction:column;gap:18px}
		@media (max-width: 900px){
			#red-cultural-viaje-japon-conditions-grid{grid-template-columns:1fr;gap:34px}
		}
	</style>
	<?php wp_head(); ?>
</head>
<body id="red-cultural-viaje-japon-page" <?php body_class('bg-white text-gray-800'); ?>>
	<?php if (function_exists('wp_body_open')) { wp_body_open(); } ?>

	<?php
	if ($rcp_theme_header_html !== '') {
		echo '<div id="red-cultural-viaje-japon-site-header">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $rcp_theme_header_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<main id="red-cultural-viaje-japon-main">
		<header id="red-cultural-viaje-japon-hero" class="relative banner-bg flex flex-col items-center justify-start pt-20 px-4 text-center z-10">
			<div id="red-cultural-viaje-japon-hero-content" class="relative w-full flex flex-col items-center">
				<div id="red-cultural-viaje-japon-hero-inner" class="relative w-full flex flex-col items-center">
					<div id="red-cultural-viaje-japon-hero-logo-wrap" class="absolute top-6 right-0 md:right-6">
						<img
							id="red-cultural-viaje-japon-hero-logo"
							src="<?php echo esc_url($cocha_logo_local); ?>"
							data-fallback="<?php echo esc_url($cocha_logo_live); ?>"
							alt="Cocha Logo"
							class="h-12 md:h-16 w-auto"
							loading="lazy"
							referrerpolicy="no-referrer"
							onerror="if(this.dataset.fallback&&this.src!==this.dataset.fallback){this.src=this.dataset.fallback;}"
						>
					</div>

					<div id="red-cultural-viaje-japon-hero-text">
						<h1 id="red-cultural-viaje-japon-title" class="text-4xl md:text-6xl font-bold text-white mb-2 drop-shadow-2xl">
							Japón
						</h1>
						<p id="red-cultural-viaje-japon-dates" class="text-xl md:text-2xl text-white font-medium drop-shadow-lg">
							24-octubre al 09 de noviembre de 2026
						</p>
					</div>
				</div>
			</div>
		</header>

		<div id="red-cultural-viaje-japon-content" class="max-w-5xl mx-auto px-4 pb-20">
			<section id="red-cultural-viaje-japon-overlap-card" class="overlap-container flex flex-col md:flex-row overflow-hidden rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.3)] bg-[#444444] text-white">
				<div id="red-cultural-viaje-japon-overlap-photo-wrap" class="w-full md:w-1/2 h-72 md:h-auto">
					<img
						id="red-cultural-viaje-japon-overlap-photo"
						src="<?php echo esc_url($hosts_local); ?>"
						data-fallback="<?php echo esc_url($hosts_live); ?>"
						alt="Magdalena y Bárbara"
						class="w-full h-full object-cover"
						loading="lazy"
						referrerpolicy="no-referrer"
						onerror="if(this.dataset.fallback&&this.src!==this.dataset.fallback){this.src=this.dataset.fallback;}"
					>
				</div>

				<div id="red-cultural-viaje-japon-overlap-info" class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center space-y-6">
					<div id="red-cultural-viaje-japon-overlap-heading">
						<h2 id="red-cultural-viaje-japon-overlap-title" class="text-lg md:text-2xl font-semibold leading-tight">
							<span id="red-cultural-viaje-japon-overlap-title-line-1">Viaja junto a Magdalena Merbilháa</span>
							<span id="red-cultural-viaje-japon-overlap-title-line-2" class="block">y Bárbara Bustamante</span>
						</h2>
					</div>

					<hr id="red-cultural-viaje-japon-overlap-divider" class="border-t border-gray-500 w-full">

					<div id="red-cultural-viaje-japon-overlap-contact" class="space-y-4">
						<div id="red-cultural-viaje-japon-overlap-email">
							<p id="red-cultural-viaje-japon-overlap-email-label" class="text-xs uppercase tracking-widest text-gray-400 mb-1">Inscripciones en:</p>
							<p id="red-cultural-viaje-japon-overlap-email-value" class="text-lg md:text-xl font-bold">magdalena@redcultural.cl</p>
						</div>
						<div id="red-cultural-viaje-japon-overlap-phone">
							<p id="red-cultural-viaje-japon-overlap-phone-value" class="text-2xl font-bold">+56 9 9322 3163</p>
						</div>
					</div>
				</div>
			</section>

			<section id="red-cultural-viaje-japon-features" class="mt-24 text-center">
				<h2 id="red-cultural-viaje-japon-features-title" class="text-3xl md:text-4xl font-bold text-gray-800 mb-14">
					Disfruta una experiencia única
				</h2>

				<div id="red-cultural-viaje-japon-features-list" class="max-w-3xl mx-auto text-left space-y-8 px-4">
					<div id="red-cultural-viaje-japon-feature-1" class="flex items-center space-x-5">
						<div id="red-cultural-viaje-japon-feature-1-icon" class="flex-shrink-0">
							<svg id="red-cultural-viaje-japon-feature-1-svg" class="w-7 h-7 text-red-600" fill="currentColor" viewBox="0 0 20 20">
								<path id="red-cultural-viaje-japon-feature-1-path" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
							</svg>
						</div>
						<p id="red-cultural-viaje-japon-feature-1-text" class="text-lg md:text-xl text-gray-600">
							Sé parte de lugares llenos de historia y belleza única.
						</p>
					</div>

					<div id="red-cultural-viaje-japon-feature-2" class="flex items-center space-x-5">
						<div id="red-cultural-viaje-japon-feature-2-icon" class="flex-shrink-0">
							<svg id="red-cultural-viaje-japon-feature-2-svg" class="w-7 h-7 text-red-600" fill="currentColor" viewBox="0 0 20 20">
								<path id="red-cultural-viaje-japon-feature-2-path" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
							</svg>
						</div>
						<p id="red-cultural-viaje-japon-feature-2-text" class="text-lg md:text-xl text-gray-600">
							Aprende de la mano de una experta en historia y viajes culturales.
						</p>
					</div>

					<div id="red-cultural-viaje-japon-feature-3" class="flex items-center space-x-5">
						<div id="red-cultural-viaje-japon-feature-3-icon" class="flex-shrink-0">
							<svg id="red-cultural-viaje-japon-feature-3-svg" class="w-7 h-7 text-red-600" fill="currentColor" viewBox="0 0 20 20">
								<path id="red-cultural-viaje-japon-feature-3-path" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
							</svg>
						</div>
						<p id="red-cultural-viaje-japon-feature-3-text" class="text-lg md:text-xl text-gray-600">
							Conoce y comparte con gente nueva igual de apasionada que tú por la historia y viajes.
						</p>
					</div>
				</div>
			</section>
		</div>

		<section id="red-cultural-viaje-japon-gallery" aria-label="Galería">
			<h2 id="red-cultural-viaje-japon-gallery-title">Galería</h2>
			<ul id="red-cultural-viaje-japon-gallery-grid">
				<?php foreach ($gallery_items as $index => $item) : ?>
					<?php
					$rel = (string) $item['rel'];
					$local = $uploads_base . $rel;
					$live = 'https://red-cultural.cl/wp-content/uploads' . $rel;
					$slug = isset($item['slug']) ? (string) $item['slug'] : (string) $index;
					$alt = isset($item['alt']) ? (string) $item['alt'] : '';
					?>
					<li id="<?php echo esc_attr('red-cultural-viaje-japon-gallery-item-' . $slug); ?>">
						<a
							id="<?php echo esc_attr('red-cultural-viaje-japon-gallery-link-' . $slug); ?>"
							href="<?php echo esc_url($local); ?>"
							target="_blank"
							rel="noopener noreferrer"
						>
							<img
								id="<?php echo esc_attr('red-cultural-viaje-japon-gallery-img-' . $slug); ?>"
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

		<section id="red-cultural-viaje-japon-itinerary" aria-label="Itinerario">
			<div id="red-cultural-viaje-japon-itinerary-inner">
				<h2 id="red-cultural-viaje-japon-itinerary-title">Itinerario</h2>

				<table id="red-cultural-viaje-japon-itinerary-table">
					<thead id="red-cultural-viaje-japon-itinerary-thead">
						<tr id="red-cultural-viaje-japon-itinerary-head-row">
							<th id="red-cultural-viaje-japon-itinerary-th-date">Fecha</th>
							<th id="red-cultural-viaje-japon-itinerary-th-localities">Localidades</th>
							<th id="red-cultural-viaje-japon-itinerary-th-itinerary">Itinerario</th>
							<th id="red-cultural-viaje-japon-itinerary-th-hotels">Hoteles</th>
						</tr>
					</thead>

					<tbody id="red-cultural-viaje-japon-itinerary-tbody">
						<tr id="red-cultural-viaje-japon-itinerary-day-1">
							<td id="red-cultural-viaje-japon-itinerary-day-1-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-japon-itinerary-day-1-day">Día 1<span class="rcp-itin-subdate" id="red-cultural-viaje-japon-itinerary-day-1-subdate">Sábado 24 de Octubre</span></span></td>
							<td id="red-cultural-viaje-japon-itinerary-day-1-localities" data-label="Localidades">Santiago - Madrid</td>
							<td id="red-cultural-viaje-japon-itinerary-day-1-itinerary" data-label="Itinerario">12:40 hs. Salida en vuelo IB 118 desde Santiago a Madrid</td>
							<td id="red-cultural-viaje-japon-itinerary-day-1-hotels" data-label="Hoteles">-</td>
						</tr>

						<tr id="red-cultural-viaje-japon-itinerary-day-2">
							<td id="red-cultural-viaje-japon-itinerary-day-2-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-japon-itinerary-day-2-day">Día 2<span class="rcp-itin-subdate" id="red-cultural-viaje-japon-itinerary-day-2-subdate">Domingo 25 de Octubre</span></span></td>
							<td id="red-cultural-viaje-japon-itinerary-day-2-localities" data-label="Localidades">Madrid - Tokio</td>
							<td id="red-cultural-viaje-japon-itinerary-day-2-itinerary" data-label="Itinerario">05:20 hs Llegada a Madrid. 11:55 hs. Salida en vuelo IB 281 hacia Tokio</td>
							<td id="red-cultural-viaje-japon-itinerary-day-2-hotels" data-label="Hoteles">-</td>
						</tr>

						<tr id="red-cultural-viaje-japon-itinerary-day-3">
							<td id="red-cultural-viaje-japon-itinerary-day-3-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-japon-itinerary-day-3-day">Día 3<span class="rcp-itin-subdate" id="red-cultural-viaje-japon-itinerary-day-3-subdate">Lunes 26 de Octubre</span></span></td>
							<td id="red-cultural-viaje-japon-itinerary-day-3-localities" data-label="Localidades">Tokio</td>
							<td id="red-cultural-viaje-japon-itinerary-day-3-itinerary" data-label="Itinerario">Llegada al aeropuerto de Narita a las 10:05 hs. Traslado grupal al Hotel</td>
							<td id="red-cultural-viaje-japon-itinerary-day-3-hotels" data-label="Hoteles">Hotel New Otani Tokyo Garden Tower</td>
						</tr>

						<tr id="red-cultural-viaje-japon-itinerary-day-4">
							<td id="red-cultural-viaje-japon-itinerary-day-4-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-japon-itinerary-day-4-day">Día 4<span class="rcp-itin-subdate" id="red-cultural-viaje-japon-itinerary-day-4-subdate">Martes 27 de Octubre</span></span></td>
							<td id="red-cultural-viaje-japon-itinerary-day-4-localities" data-label="Localidades">Tokio</td>
							<td id="red-cultural-viaje-japon-itinerary-day-4-itinerary" data-label="Itinerario">Visitaremos la Torre de Tokio, el Templo Meiji, Omotesando y Shinjuku</td>
							<td id="red-cultural-viaje-japon-itinerary-day-4-hotels" data-label="Hoteles">Hotel New Otani Tokyo Garden Tower</td>
						</tr>

						<tr id="red-cultural-viaje-japon-itinerary-day-5">
							<td id="red-cultural-viaje-japon-itinerary-day-5-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-japon-itinerary-day-5-day">Día 5<span class="rcp-itin-subdate" id="red-cultural-viaje-japon-itinerary-day-5-subdate">Miércoles 28 de Octubre</span></span></td>
							<td id="red-cultural-viaje-japon-itinerary-day-5-localities" data-label="Localidades">Tokio</td>
							<td id="red-cultural-viaje-japon-itinerary-day-5-itinerary" data-label="Itinerario">Continuaremos visitando Tokio: Palacio Imperial, Museo Nacional de Tokio, Templo Asakusa Sensō-ji y la calle Nakamise</td>
							<td id="red-cultural-viaje-japon-itinerary-day-5-hotels" data-label="Hoteles">Hotel New Otani Tokyo Garden Tower</td>
						</tr>

						<tr id="red-cultural-viaje-japon-itinerary-day-6">
							<td id="red-cultural-viaje-japon-itinerary-day-6-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-japon-itinerary-day-6-day">Día 6<span class="rcp-itin-subdate" id="red-cultural-viaje-japon-itinerary-day-6-subdate">Jueves 29 de Octubre</span></span></td>
							<td id="red-cultural-viaje-japon-itinerary-day-6-localities" data-label="Localidades">Tokio - Hakone</td>
							<td id="red-cultural-viaje-japon-itinerary-day-6-itinerary" data-label="Itinerario">Por la mañana tomaremos un crucero por el lago Ashi. Visitaremos el Teleférico de Owakudani y luego nos trasladaremos a nuestro hotel en Hakone</td>
							<td id="red-cultural-viaje-japon-itinerary-day-6-hotels" data-label="Hoteles">Fujiya Hotel</td>
						</tr>

						<tr id="red-cultural-viaje-japon-itinerary-day-7">
							<td id="red-cultural-viaje-japon-itinerary-day-7-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-japon-itinerary-day-7-day">Día 7<span class="rcp-itin-subdate" id="red-cultural-viaje-japon-itinerary-day-7-subdate">Viernes 30 de Octubre</span></span></td>
							<td id="red-cultural-viaje-japon-itinerary-day-7-localities" data-label="Localidades">Hakone - Shirakawa - Kanazawa</td>
							<td id="red-cultural-viaje-japon-itinerary-day-7-itinerary" data-label="Itinerario">Viajamos en tren de alta velocidad con destino a Nagoya. Continuaremos hacia Shirakawa donde visitaremos la Casa Wada y el Museo Gassho-Zukuri Minka. Traslado a Kanazawa donde alojaremos.</td>
							<td id="red-cultural-viaje-japon-itinerary-day-7-hotels" data-label="Hoteles">Hyatt Centric Kanazawa</td>
						</tr>

						<tr id="red-cultural-viaje-japon-itinerary-day-8">
							<td id="red-cultural-viaje-japon-itinerary-day-8-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-japon-itinerary-day-8-day">Día 8<span class="rcp-itin-subdate" id="red-cultural-viaje-japon-itinerary-day-8-subdate">Sábado 31 de Octubre</span></span></td>
							<td id="red-cultural-viaje-japon-itinerary-day-8-localities" data-label="Localidades">Kanazawa - Takayama - Kanazawa</td>
							<td id="red-cultural-viaje-japon-itinerary-day-8-itinerary" data-label="Itinerario">Por la mañana visitaremos el Mercado Matutino de Miyagawa y el Parque Shiroyama. Continuaremos a Takayama en bus donde visitaremos el casco antiguo</td>
							<td id="red-cultural-viaje-japon-itinerary-day-8-hotels" data-label="Hoteles">Hyatt Centric Kanazawa</td>
						</tr>

						<tr id="red-cultural-viaje-japon-itinerary-day-9">
							<td id="red-cultural-viaje-japon-itinerary-day-9-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-japon-itinerary-day-9-day">Día 9<span class="rcp-itin-subdate" id="red-cultural-viaje-japon-itinerary-day-9-subdate">Domingo 01 de Noviembre</span></span></td>
							<td id="red-cultural-viaje-japon-itinerary-day-9-localities" data-label="Localidades">Kanazawa - Kioto</td>
							<td id="red-cultural-viaje-japon-itinerary-day-9-itinerary" data-label="Itinerario">Visitaremos Kenrokuen, la Calle Antigua del Té Higashi y el Museo Kanazawa XXI. Tomaremos tren con destino a Kioto</td>
							<td id="red-cultural-viaje-japon-itinerary-day-9-hotels" data-label="Hoteles">DoubleTree by Hilton Kyoto Station</td>
						</tr>

						<tr id="red-cultural-viaje-japon-itinerary-day-10">
							<td id="red-cultural-viaje-japon-itinerary-day-10-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-japon-itinerary-day-10-day">Día 10<span class="rcp-itin-subdate" id="red-cultural-viaje-japon-itinerary-day-10-subdate">Lunes 02 de Noviembre</span></span></td>
							<td id="red-cultural-viaje-japon-itinerary-day-10-localities" data-label="Localidades">Kioto</td>
							<td id="red-cultural-viaje-japon-itinerary-day-10-itinerary" data-label="Itinerario">Recorreremos Kioto visitando el Bosque de Bambú de Arashiyama, el Templo Kinkaku y el distrito de Gion</td>
							<td id="red-cultural-viaje-japon-itinerary-day-10-hotels" data-label="Hoteles">DoubleTree by Hilton Kyoto Station</td>
						</tr>

						<tr id="red-cultural-viaje-japon-itinerary-day-11">
							<td id="red-cultural-viaje-japon-itinerary-day-11-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-japon-itinerary-day-11-day">Día 11<span class="rcp-itin-subdate" id="red-cultural-viaje-japon-itinerary-day-11-subdate">Martes 03 de Noviembre</span></span></td>
							<td id="red-cultural-viaje-japon-itinerary-day-11-localities" data-label="Localidades">Kioto - Uji - Nara</td>
							<td id="red-cultural-viaje-japon-itinerary-day-11-itinerary" data-label="Itinerario">Por la mañana visitaremos el Templo de Byodoin. Luego seguiremos a Uji para visitar el Santuario Ujigami. En la tarde tendremos una experiencia de preparación de matcha. Visitaremos el Templo Todaiji y el parque Nara. Regreso a Kioto para alojar</td>
							<td id="red-cultural-viaje-japon-itinerary-day-11-hotels" data-label="Hoteles">DoubleTree by Hilton Kyoto Station</td>
						</tr>

						<tr id="red-cultural-viaje-japon-itinerary-day-12">
							<td id="red-cultural-viaje-japon-itinerary-day-12-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-japon-itinerary-day-12-day">Día 12<span class="rcp-itin-subdate" id="red-cultural-viaje-japon-itinerary-day-12-subdate">Miércoles 04 de Noviembre</span></span></td>
							<td id="red-cultural-viaje-japon-itinerary-day-12-localities" data-label="Localidades">Kioto - Osaka</td>
							<td id="red-cultural-viaje-japon-itinerary-day-12-itinerary" data-label="Itinerario">En la mañana iremos a Osaka para visitar: el Castillo, el Edificio Umeda Sky, el distrito de Dotonbori y Shinsekai</td>
							<td id="red-cultural-viaje-japon-itinerary-day-12-hotels" data-label="Hoteles">RIHGA Royal Hotel Osaka, Vignette Collection by IHG</td>
						</tr>

						<tr id="red-cultural-viaje-japon-itinerary-day-13">
							<td id="red-cultural-viaje-japon-itinerary-day-13-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-japon-itinerary-day-13-day">Día 13<span class="rcp-itin-subdate" id="red-cultural-viaje-japon-itinerary-day-13-subdate">Jueves 05 de Noviembre</span></span></td>
							<td id="red-cultural-viaje-japon-itinerary-day-13-localities" data-label="Localidades">Osaka - Hiroshima</td>
							<td id="red-cultural-viaje-japon-itinerary-day-13-itinerary" data-label="Itinerario">Tomaremos tren con destino a Hiroshima donde visitaremos: el Castillo y el Museo Conmemorativo de la Paz de Hiroshima. Traslado al hotel</td>
							<td id="red-cultural-viaje-japon-itinerary-day-13-hotels" data-label="Hoteles">Hilton Hiroshima</td>
						</tr>

						<tr id="red-cultural-viaje-japon-itinerary-day-14">
							<td id="red-cultural-viaje-japon-itinerary-day-14-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-japon-itinerary-day-14-day">Día 14<span class="rcp-itin-subdate" id="red-cultural-viaje-japon-itinerary-day-14-subdate">Viernes 06 de Noviembre</span></span></td>
							<td id="red-cultural-viaje-japon-itinerary-day-14-localities" data-label="Localidades">Hiroshima - Miyajima - Tokio</td>
							<td id="red-cultural-viaje-japon-itinerary-day-14-itinerary" data-label="Itinerario">Visitaremos la Isla de Miyajima, el Santuario Itsukushima, el Pabellón Senjokaku y Pagoda de Cinco Pisos. Tomaremos tren con destino a Tokio donde alojaremos</td>
							<td id="red-cultural-viaje-japon-itinerary-day-14-hotels" data-label="Hoteles">Hotel New Otani Tokyo Garden Tower</td>
						</tr>

						<tr id="red-cultural-viaje-japon-itinerary-day-15">
							<td id="red-cultural-viaje-japon-itinerary-day-15-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-japon-itinerary-day-15-day">Día 15<span class="rcp-itin-subdate" id="red-cultural-viaje-japon-itinerary-day-15-subdate">Sábado 07 de Noviembre</span></span></td>
							<td id="red-cultural-viaje-japon-itinerary-day-15-localities" data-label="Localidades">Tokio</td>
							<td id="red-cultural-viaje-japon-itinerary-day-15-itinerary" data-label="Itinerario">Día Libre</td>
							<td id="red-cultural-viaje-japon-itinerary-day-15-hotels" data-label="Hoteles">Hotel New Otani Tokyo Garden Tower</td>
						</tr>

						<tr id="red-cultural-viaje-japon-itinerary-day-16">
							<td id="red-cultural-viaje-japon-itinerary-day-16-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-japon-itinerary-day-16-day">Día 16<span class="rcp-itin-subdate" id="red-cultural-viaje-japon-itinerary-day-16-subdate">Domingo 08 de Noviembre</span></span></td>
							<td id="red-cultural-viaje-japon-itinerary-day-16-localities" data-label="Localidades">Tokio - Madrid</td>
							<td id="red-cultural-viaje-japon-itinerary-day-16-itinerary" data-label="Itinerario">Traslado Grupal al aeropuerto de Narita para tomar vuelo IB 282 que sale a las 11:45 hs. con destino a Madrid. Llegada a las 19:20 hs. 23:59 hs Salida en vuelo IB 117 con destino a Santiago</td>
							<td id="red-cultural-viaje-japon-itinerary-day-16-hotels" data-label="Hoteles">-</td>
						</tr>

						<tr id="red-cultural-viaje-japon-itinerary-day-17">
							<td id="red-cultural-viaje-japon-itinerary-day-17-date" data-label="Fecha"><span class="rcp-itin-day" id="red-cultural-viaje-japon-itinerary-day-17-day">Día 17<span class="rcp-itin-subdate" id="red-cultural-viaje-japon-itinerary-day-17-subdate">Lunes 09 de Noviembre</span></span></td>
							<td id="red-cultural-viaje-japon-itinerary-day-17-localities" data-label="Localidades">Santiago</td>
							<td id="red-cultural-viaje-japon-itinerary-day-17-itinerary" data-label="Itinerario">Llegada a Santiago a las 09:20 hrs.</td>
							<td id="red-cultural-viaje-japon-itinerary-day-17-hotels" data-label="Hoteles">-</td>
						</tr>
					</tbody>
				</table>
			</div>
		</section>

		<section id="red-cultural-viaje-japon-rates" aria-label="Tarifas">
			<h2 id="red-cultural-viaje-japon-rates-main-title" class="text-center text-[44px] leading-[1.05] font-black tracking-[-.02em] text-gray-800 mb-2">Tarifas</h2>
			
			<div id="red-cultural-viaje-japon-rates-table-wrap" class="overflow-x-auto mb-10">
				<table id="red-cultural-viaje-japon-rates-table" class="w-full max-w-5xl mx-auto border-collapse bg-white shadow-sm rounded-xl overflow-hidden">
					<thead>
						<tr class="bg-gray-100 text-gray-700">
							<th class="p-6 border border-gray-200"></th>
							<th colspan="2" class="p-6 border border-gray-200 text-xl font-bold uppercase tracking-wider">Con pasajes aéreos</th>
							<th colspan="2" class="p-6 border border-gray-200 text-xl font-bold uppercase tracking-wider">Sin pasajes aéreos</th>
						</tr>
						<tr class="bg-gray-50 text-gray-600 text-sm">
							<th class="p-4 border border-gray-200 uppercase font-black">Habitación</th>
							<th class="p-4 border border-gray-200 font-bold uppercase tracking-widest">Preventa*</th>
							<th class="p-4 border border-gray-200 font-bold uppercase tracking-widest">Normal</th>
							<th class="p-4 border border-gray-200 font-bold uppercase tracking-widest">Preventa*</th>
							<th class="p-4 border border-gray-200 font-bold uppercase tracking-widest">Normal</th>
						</tr>
					</thead>
					<tbody class="text-center">
						<tr class="hover:bg-gray-50 transition-colors">
							<td class="p-6 border border-gray-200 font-black text-gray-800 text-xl">Single</td>
							<td class="p-6 border border-gray-200 text-2xl font-black text-gray-500 italic">USD 14.655</td>
							<td class="p-6 border border-gray-200 text-2xl font-black text-gray-500 italic">USD 15.405</td>
							<td class="p-6 border border-gray-200 text-2xl font-black text-gray-500 italic">USD 11.955</td>
							<td class="p-6 border border-gray-200 text-2xl font-black text-gray-500 italic">USD 12.705</td>
						</tr>
						<tr class="hover:bg-gray-50 transition-colors">
							<td class="p-6 border border-gray-200 font-black text-gray-800 text-xl">Doble</td>
							<td class="p-6 border border-gray-200 text-2xl font-black text-gray-500 italic">USD 11.825</td>
							<td class="p-6 border border-gray-200 text-2xl font-black text-gray-500 italic">USD 12.395</td>
							<td class="p-6 border border-gray-200 text-2xl font-black text-gray-500 italic">USD 9.125</td>
							<td class="p-6 border border-gray-200 text-2xl font-black text-gray-500 italic">USD 9.695</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div id="red-cultural-viaje-japon-rates-notes" class="text-center mb-16 text-gray-500 font-medium">
				<p class="text-lg">* Tarifas Preventa válidas hasta 15-Mayo-2026</p>
				<p class="text-sm opacity-75">Tarifas por persona expresadas en dólares americanos</p>
			</div>

			<div id="red-cultural-viaje-japon-rates-grid" class="grid grid-cols-1 md:grid-cols-2 gap-14 max-w-5xl mx-auto">
				<div id="red-cultural-viaje-japon-rates-include-block">
					<p id="red-cultural-viaje-japon-rates-include-title">Incluye</p>
					<ul id="red-cultural-viaje-japon-rates-include-list">
						<li id="red-cultural-viaje-japon-rates-include-1">
							<span id="red-cultural-viaje-japon-rates-include-1-icon" class="material-symbols-outlined" aria-hidden="true">flight</span>
							<p id="red-cultural-viaje-japon-rates-include-1-text">Pasajes aéreos en clase turista (excepto si elige tarifa sin pasajes aéreos).</p>
						</li>
						<li id="red-cultural-viaje-japon-rates-include-2">
							<span id="red-cultural-viaje-japon-rates-include-2-icon" class="material-symbols-outlined" aria-hidden="true">hotel</span>
							<p id="red-cultural-viaje-japon-rates-include-2-text">Alojamiento en hoteles mencionados o similares con desayuno.</p>
						</li>
						<li id="red-cultural-viaje-japon-rates-include-3">
							<span id="red-cultural-viaje-japon-rates-include-3-icon" class="material-symbols-outlined" aria-hidden="true">task_alt</span>
							<p id="red-cultural-viaje-japon-rates-include-3-text">Entradas a sitios a visitar según itinerario.</p>
						</li>
						<li id="red-cultural-viaje-japon-rates-include-4">
							<span id="red-cultural-viaje-japon-rates-include-4-icon" class="material-symbols-outlined" aria-hidden="true">location_city</span>
							<p id="red-cultural-viaje-japon-rates-include-4-text">Traslados entre ciudades vía terrestre según itinerario.</p>
						</li>
						<li id="red-cultural-viaje-japon-rates-include-5">
							<span id="red-cultural-viaje-japon-rates-include-5-icon" class="material-symbols-outlined" aria-hidden="true">favorite</span>
							<p id="red-cultural-viaje-japon-rates-include-5-text">Asistencia en viaje Universal Assistance plan Value.</p>
						</li>
						<li id="red-cultural-viaje-japon-rates-include-6">
							<span id="red-cultural-viaje-japon-rates-include-6-icon" class="material-symbols-outlined" aria-hidden="true">airport_shuttle</span>
							<p id="red-cultural-viaje-japon-rates-include-6-text">Traslado grupal aeropuerto/hotel/aeropuerto en Tokio (ver nota en sección Condiciones*).</p>
						</li>
					</ul>
				</div>

				<div id="red-cultural-viaje-japon-rates-exclude-block">
					<p id="red-cultural-viaje-japon-rates-exclude-title">No incluye</p>
					<ul id="red-cultural-viaje-japon-rates-exclude-list">
						<li id="red-cultural-viaje-japon-rates-exclude-1">
							<span id="red-cultural-viaje-japon-rates-exclude-1-icon" class="material-symbols-outlined" aria-hidden="true">warning</span>
							<p id="red-cultural-viaje-japon-rates-exclude-1-text">Servicios adicionales, tales como alimentación no descrita, propinas, gastos de carácter personal y en general todo ítem no detallado en el listado de servicios incluidos.</p>
						</li>
					</ul>
				</div>
			</div>
		</section>

		<section id="red-cultural-viaje-japon-interest" aria-label="Interés">
			<div id="red-cultural-viaje-japon-interest-inner">
				<div id="red-cultural-viaje-japon-interest-copy">
					<p id="red-cultural-viaje-japon-interest-question">¿Estás interesado?</p>
					<p id="red-cultural-viaje-japon-interest-desc">Llena el formulario para más información sobre el</p>
					<p id="red-cultural-viaje-japon-interest-trip-title">Viaje Japón</p>
					<p id="red-cultural-viaje-japon-interest-trip-dates">24-octubre al 09 de noviembre de 2026</p>
				</div>

				<form
					id="red-cultural-viaje-japon-interest-form"
					method="post"
					action="<?php echo esc_url((string) admin_url('admin-post.php')); ?>"
				>
					<?php if (isset($_GET['rcp_vj_interest']) && (string) $_GET['rcp_vj_interest'] === 'success') : ?>
						<p id="red-cultural-viaje-japon-interest-success">¡Gracias! Te contactaremos pronto.</p>
					<?php endif; ?>

					<input type="hidden" id="red-cultural-viaje-japon-interest-action" name="action" value="rcp_viaje_japon_interest">
					<?php wp_nonce_field('rcp_viaje_japon_interest', 'rcp_vj_nonce'); ?>

					<div id="red-cultural-viaje-japon-interest-field-name">
						<label id="red-cultural-viaje-japon-interest-label-name" for="red-cultural-viaje-japon-interest-input-name">Nombre</label>
						<input id="red-cultural-viaje-japon-interest-input-name" name="rcp_vj_name" type="text" autocomplete="name" placeholder="Tu nombre" required>
					</div>

					<div id="red-cultural-viaje-japon-interest-field-email">
						<label id="red-cultural-viaje-japon-interest-label-email" for="red-cultural-viaje-japon-interest-input-email">Email</label>
						<input id="red-cultural-viaje-japon-interest-input-email" name="rcp_vj_email" type="email" autocomplete="email" placeholder="correo@ejemplo.com" required>
					</div>

					<div id="red-cultural-viaje-japon-interest-field-phone">
						<label id="red-cultural-viaje-japon-interest-label-phone" for="red-cultural-viaje-japon-interest-input-phone">Teléfono</label>
						<input id="red-cultural-viaje-japon-interest-input-phone" name="rcp_vj_phone" type="tel" autocomplete="tel" placeholder="+56 9 1234 5678">
					</div>

					<div id="red-cultural-viaje-japon-interest-field-message">
						<label id="red-cultural-viaje-japon-interest-label-message" for="red-cultural-viaje-japon-interest-input-message">Mensaje</label>
						<textarea id="red-cultural-viaje-japon-interest-input-message" name="rcp_vj_message" placeholder="Cuéntanos qué necesitas..."></textarea>
					</div>

					<button id="red-cultural-viaje-japon-interest-submit" type="submit">Enviar</button>
				</form>
			</div>
		</section>

		<section id="red-cultural-viaje-japon-conditions" aria-label="Condiciones">
			<div id="red-cultural-viaje-japon-conditions-inner" class="max-w-5xl mx-auto px-4 py-16">
				<h2 id="red-cultural-viaje-japon-conditions-title" class="text-4xl font-black text-black text-center mb-12 tracking-tighter">Condiciones</h2>

				<div id="red-cultural-viaje-japon-conditions-content" class="space-y-12 text-black">
					
					<div id="red-cultural-viaje-japon-conditions-transfers">
						<h3 id="red-cultural-viaje-japon-conditions-transfers-title" class="text-lg font-black underline mb-4 uppercase">*Nota Traslados llegada y salida:</h3>
						<p id="red-cultural-viaje-japon-conditions-transfers-text" class="text-sm leading-relaxed">
							· Los traslados incluidos en Tokio operan únicamente desde/hacia el Aeropuerto de Narita en los horarios indicados. Pasajeros con vuelos en horarios o aeropuertos diferentes deberán coordinar su propio transporte o solicitar una cotización adicional a través de COCHA.
						</p>
					</div>

					<div id="red-cultural-viaje-japon-conditions-cancel">
						<h3 id="red-cultural-viaje-japon-conditions-cancel-title" class="text-lg font-black underline mb-6 uppercase">Políticas de reserva y cancelación:</h3>
						
						<div class="overflow-x-auto mb-6">
							<table id="red-cultural-viaje-japon-conditions-payment-table" class="w-full border-collapse border border-black text-sm text-center">
								<thead>
									<tr>
										<th class="border border-black p-4 font-black uppercase">Etapa de pago</th>
										<th class="border border-black p-4 font-black uppercase">Monto</th>
										<th class="border border-black p-4 font-black uppercase">Fecha límite</th>
										<th class="border border-black p-4 font-black uppercase">Observación</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td class="border border-black p-4">Primer abono</td>
										<td class="border border-black p-4">USD 3.500<br>por persona</td>
										<td class="border border-black p-4">Al momento de solicitar la reserva</td>
										<td class="border border-black p-4">No reembolsable</td>
									</tr>
									<tr>
										<td class="border border-black p-4">Segundo abono</td>
										<td class="border border-black p-4">USD 3.100<br>por persona</td>
										<td class="border border-black p-4">Hasta 10-mayo-2026</td>
										<td class="border border-black p-4">No reembolsable</td>
									</tr>
									<tr>
										<td class="border border-black p-4">Saldo final</td>
										<td class="border border-black p-4">Según tipo de tarifa preventa o normal y tipo de habitación</td>
										<td class="border border-black p-4">Hasta 10-julio-2026</td>
										<td class="border border-black p-4">No reembolsable</td>
									</tr>
								</tbody>
							</table>
						</div>

						<ul id="red-cultural-viaje-japon-conditions-cancel-bullets" class="space-y-2 text-sm">
							<li>· Se requiere un mínimo de 25 pasajeros confirmados de lo contrario el viaje se cancela.</li>
							<li>· Pasajes aéreos: se puede cotizar upgrade de cabina sujeto a disponibilidad.</li>
							<li>· Asistencia en viaje aplica tarifa extra para mayores de 70 años.</li>
						</ul>
					</div>

					<div id="red-cultural-viaje-japon-conditions-docs">
						<h3 id="red-cultural-viaje-japon-conditions-docs-title" class="text-lg font-black underline mb-4 uppercase">Documentación:</h3>
						<p id="red-cultural-viaje-japon-conditions-docs-text" class="text-sm">
							· Es responsabilidad de cada pasajero ir provisto de un pasaporte vigente y dotado de todos los visados y requisitos necesarios.
						</p>
					</div>

					<div id="red-cultural-viaje-japon-conditions-variations">
						<h3 id="red-cultural-viaje-japon-conditions-variations-title" class="text-lg font-black underline mb-4 uppercase">Variaciones:</h3>
						<p id="red-cultural-viaje-japon-conditions-variations-text" class="text-sm">
							· La información de tarifas, itinerario, fechas y horarios de operación, vuelos, hoteles, transportes, visitas, asistencia en viaje, etc., está sujeta a posibles modificaciones.
						</p>
					</div>
				</div>
			</div>
		</section>

		<footer id="red-cultural-viaje-japon-footer" class="bg-gray-50 py-12 text-center text-gray-400 text-sm border-t border-gray-100">
			<span id="red-cultural-viaje-japon-footer-text">&copy; 2026 Red Cultural &amp; COCHA. Todos los derechos reservados.</span>
		</footer>
	</main>

	<?php
	if ($rcp_theme_footer_html !== '') {
		echo '<div id="red-cultural-viaje-japon-site-footer">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $rcp_theme_footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<?php wp_footer(); ?>
</body>
</html>
