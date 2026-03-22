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

/**
 * Images: prefer local uploads base URL, fall back to live.
 * We treat everything as relative to /wp-content/uploads/... so it works on local and live.
 */
$hero_bg_rel = '/2025/12/Edimburg1.jpg';
$hero_bg_local = $uploads_base . $hero_bg_rel;
$hero_bg_live = 'https://red-cultural.cl/wp-content/uploads' . $hero_bg_rel;

$hero_rel = '/elementor/thumbs/Screenshot-2025-12-12-at-9.40.02-PM-rg2md8119f7uk518kdsk6k228ummuggp30jlkj5ybs.png';
$hero_local = $uploads_base . $hero_rel;
$hero_live = 'https://red-cultural.cl/wp-content/uploads' . $hero_rel;

$itinerary_img_rel = '/elementor/thumbs/Screenshot-2025-12-12-at-8.39.25-PM-rg2jpql7jo1lq5kz8prrx8deopu2f17tx2z13xerk0.png';
$itinerary_img_local = $uploads_base . $itinerary_img_rel;
$itinerary_img_live = 'https://red-cultural.cl/wp-content/uploads' . $itinerary_img_rel;

$gallery_items = array(
	array('slug' => 'glasgow', 'rel' => '/2025/12/Glasgow.jpg', 'alt' => 'Glasgow'),
	array('slug' => 'isla-mull', 'rel' => '/2025/12/IslaMull.jpg', 'alt' => 'Isla Mull'),
	array('slug' => 'adam-smith', 'rel' => '/2025/12/AdmaSmithScotland.jpg', 'alt' => 'Adam Smith (Escocia)'),
	array('slug' => 'castle', 'rel' => '/2025/12/ScotlandCastle.jpg', 'alt' => 'Castillo en Escocia'),
	array('slug' => 'maida', 'rel' => '/2025/12/MaidaSCotland.jpg', 'alt' => 'Magdalena Merbilháa'),
	array('slug' => 'orcadas', 'rel' => '/2025/12/IslaOrcadas.jpg', 'alt' => 'Islas Orcadas'),
	array('slug' => 'pitlochry', 'rel' => '/2025/12/Pitlochry.jpg', 'alt' => 'Pitlochry'),
);

?><!DOCTYPE html>
<html lang="es">
<head>
	<meta id="red-cultural-viaje-escocia-meta-charset" charset="UTF-8">
	<meta id="red-cultural-viaje-escocia-meta-viewport" name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html((string) wp_get_document_title()); ?></title>
	<script id="red-cultural-viaje-escocia-tailwind" src="https://cdn.tailwindcss.com"></script>
	<link id="red-cultural-viaje-escocia-font-inter" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
	<style id="red-cultural-viaje-escocia-style">
		body{font-family:'Inter',sans-serif;margin:0;padding:0}
		#red-cultural-viaje-escocia-page{--rcp-escocia-red:#AA0B0B}
		#red-cultural-viaje-escocia-hero{
			position:relative;
			min-height:460px;
			display:flex;
			align-items:flex-end;
			color:#fff;
			overflow:hidden;
		}
		#red-cultural-viaje-escocia-hero::before{
			content:'';
			position:absolute;
			inset:0;
			background-image:url('<?php echo esc_url($hero_bg_local); ?>'),url('<?php echo esc_url($hero_bg_live); ?>');
			background-size:cover,cover;
			background-position:center,center;
			filter:blur(3px);
			transform:scale(1.04);
			z-index:0;
		}
		#red-cultural-viaje-escocia-hero::after{
			content:'';
			position:absolute;
			inset:0;
			background:linear-gradient(to top, rgba(0,0,0,.75), rgba(0,0,0,.25));
			z-index:1;
		}
		#red-cultural-viaje-escocia-hero-inner{position:relative;z-index:2;max-width:var(--wp--style--global--wide-size);margin:0 auto;padding:84px 16px 46px;width:100%;text-align:center}
		#red-cultural-viaje-escocia-hero-libero-logo{display:inline-flex;align-items:center;justify-content:center;background:var(--rcp-escocia-red);color:#fff;font-weight:900;letter-spacing:.12em;text-transform:uppercase;font-size:14px;padding:12px 18px;line-height:1;border-radius:0;margin:0 auto 10px}
		#red-cultural-viaje-escocia-kicker{font-size:12px;letter-spacing:.32em;text-transform:uppercase;font-weight:700;opacity:.9}
		#red-cultural-viaje-escocia-title{margin:14px 0 0;font-size:56px;line-height:1.05;font-weight:900;letter-spacing:-.02em}
		#red-cultural-viaje-escocia-dates{margin:12px 0 0;font-size:24px;line-height:1.2;font-weight:600;opacity:.92}
		#red-cultural-viaje-escocia-subtitle{margin:18px 0 0;max-width:860px;font-size:18px;line-height:1.55;font-weight:400;opacity:.92}
		#red-cultural-viaje-escocia-subtitle{margin-left:auto;margin-right:auto}

		#red-cultural-viaje-escocia-content{max-width:var(--wp--style--global--wide-size);margin:0 auto;padding:56px 16px 40px;color:#111827}

		#red-cultural-viaje-escocia-libero{
			background:var(--rcp-escocia-red);
			color:#fff;
			padding:0;
			text-align:center;
			width:100vw;
			margin-left:calc(50% - 50vw);
			margin-right:calc(50% - 50vw);
		}
		#red-cultural-viaje-escocia-libero-inner{max-width:var(--wp--style--global--wide-size);margin:0 auto;padding:58px 26px}
		#red-cultural-viaje-escocia-libero-title{
			color:#fff;
			font-size:44px;
			letter-spacing:.12em;
			margin:0 0 18px;
		}
		#red-cultural-viaje-escocia-libero p{
			color:rgba(255,255,255,.92);
			max-width:860px;
			margin-left:auto;
			margin-right:auto;
		}

		#red-cultural-viaje-escocia-threecol{
			position:relative;
			width:100vw;
			margin-left:calc(50% - 50vw);
			margin-right:calc(50% - 50vw);
			overflow:hidden;
			padding:74px 0;
		}
		#red-cultural-viaje-escocia-threecol::before{
			content:'';
			position:absolute;
			inset:0;
			background-image:url('<?php echo esc_url($hero_bg_local); ?>'),url('<?php echo esc_url($hero_bg_live); ?>');
			background-size:cover,cover;
			background-position:center,center;
			filter:blur(0px);
			transform:scale(1.02);
			opacity:.16;
			z-index:0;
		}
		#red-cultural-viaje-escocia-threecol::after{
			content:'';
			position:absolute;
			inset:0;
			background:rgba(255,255,255,.86);
			z-index:1;
		}
		#red-cultural-viaje-escocia-threecol-inner{position:relative;z-index:2;max-width:var(--wp--style--global--wide-size);margin:0 auto;padding:0 16px}
		#red-cultural-viaje-escocia-threecol-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:54px;align-items:start;text-align:center}
		#red-cultural-viaje-escocia-threecol-grid h2{margin:0 0 16px;font-size:26px;line-height:1.05;font-weight:900;letter-spacing:-.01em;text-transform:uppercase;color:#111827}
		#red-cultural-viaje-escocia-threecol-grid p{margin:0;color:#111827;font-size:18px;line-height:1.85}
		#red-cultural-viaje-escocia-threecol-grid strong{font-weight:900}
		#red-cultural-viaje-escocia-threecol-col-2{display:flex;flex-direction:column;gap:34px}

		#red-cultural-viaje-escocia-general{
			margin-top:56px;
			background:var(--rcp-escocia-red);
			color:#fff;
			padding:0;
			width:100vw;
			margin-left:calc(50% - 50vw);
			margin-right:calc(50% - 50vw);
		}
		#red-cultural-viaje-escocia-general-inner{max-width:var(--wp--style--global--wide-size);margin:0 auto;padding:58px 26px}
		#red-cultural-viaje-escocia-general-grid{display:grid;grid-template-columns:1fr 1fr;gap:56px;align-items:center}
		#red-cultural-viaje-escocia-general-title{margin:0;font-size:44px;line-height:1.06;font-weight:900;letter-spacing:-.02em;color:#fff;text-transform:uppercase}
		#red-cultural-viaje-escocia-general-list{margin:18px 0 0;padding:0;list-style:none;display:flex;flex-direction:column;gap:10px}
		#red-cultural-viaje-escocia-general-list li{color:rgba(255,255,255,.92);line-height:1.7;font-size:18px}

		#red-cultural-viaje-escocia-itinerary-image{margin-top:0;border-radius:0;overflow:hidden;background:transparent;box-shadow:none;width:100%}
		#red-cultural-viaje-escocia-itinerary-image img{display:block;width:100%;height:auto}

		#red-cultural-viaje-escocia-gallery{margin-top:70px}
		#red-cultural-viaje-escocia-gallery-title{margin:0 0 18px;font-size:32px;line-height:1.08;font-weight:900;letter-spacing:-.02em;text-align:center}
		#red-cultural-viaje-escocia-gallery-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px;list-style:none;margin:0;padding:0}
		#red-cultural-viaje-escocia-gallery-grid a{display:block;border-radius:18px;overflow:hidden;background:#f3f4f6;box-shadow:0 10px 24px rgba(0,0,0,.08);transform:translateZ(0);transition:transform .22s ease, box-shadow .22s ease;text-decoration:none}
		#red-cultural-viaje-escocia-gallery-grid a:hover{transform:translateY(-2px);box-shadow:0 14px 34px rgba(0,0,0,.12)}
		#red-cultural-viaje-escocia-gallery-grid img{display:block;width:100%;height:230px;object-fit:cover}

		#red-cultural-viaje-escocia-detailed{margin-top:74px}
		#red-cultural-viaje-escocia-detailed-title{margin:0 0 18px;font-size:32px;line-height:1.08;font-weight:900;letter-spacing:-.02em}
		#red-cultural-viaje-escocia-detailed .itinerario-table{width:100%;border-collapse:collapse;background:#fff;border-top:1px solid rgba(17,24,39,.15);border-bottom:1px solid rgba(17,24,39,.15)}
		#red-cultural-viaje-escocia-detailed .itinerario-table thead th{background:#fff;color:#111827;font-weight:900;padding:14px 10px;text-align:left;border-bottom:2px solid rgba(17,24,39,.18);text-transform:uppercase;font-size:12px;letter-spacing:.22em}
		#red-cultural-viaje-escocia-detailed .itinerario-table tbody td{padding:12px 10px;vertical-align:top;font-size:14px;line-height:1.55;color:#111827;border-bottom:1px solid rgba(17,24,39,.12);border-right:1px solid rgba(17,24,39,.08)}
		#red-cultural-viaje-escocia-detailed .itinerario-table tbody td:last-child{border-right:none}
		#red-cultural-viaje-escocia-detailed .itinerario-table tbody tr:last-child td{border-bottom:none}

		#red-cultural-viaje-escocia-terms{margin-top:78px;padding-top:30px;border-top:1px solid rgba(17,24,39,.10)}
		#red-cultural-viaje-escocia-terms-title{margin:0 0 18px;font-size:32px;line-height:1.08;font-weight:900;letter-spacing:-.02em}
		#red-cultural-viaje-escocia-terms h3{margin:24px 0 12px;font-size:14px;letter-spacing:.32em;text-transform:uppercase;font-weight:900}
		#red-cultural-viaje-escocia-terms p{margin:0;color:#374151;line-height:1.75}
		#red-cultural-viaje-escocia-terms ul{margin:10px 0 0;padding:0;list-style:none;display:flex;flex-direction:column;gap:8px}
		#red-cultural-viaje-escocia-terms li{color:#374151;line-height:1.7}

		@media (max-width: 1100px){
			#red-cultural-viaje-escocia-gallery-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
			#red-cultural-viaje-escocia-threecol-grid{grid-template-columns:1fr;gap:36px}
		}
		@media (max-width: 900px){
			#red-cultural-viaje-escocia-general-grid{grid-template-columns:1fr;gap:34px}
			#red-cultural-viaje-escocia-itinerary-image{max-width:560px;margin:0 auto}
		}
		@media (max-width: 640px){
			#red-cultural-viaje-escocia-hero-inner{padding-top:64px}
			#red-cultural-viaje-escocia-title{font-size:40px}
			#red-cultural-viaje-escocia-dates{font-size:20px}
			#red-cultural-viaje-escocia-gallery-grid{grid-template-columns:1fr}
			#red-cultural-viaje-escocia-gallery-grid img{height:220px}
		}
	</style>
	<?php wp_head(); ?>
</head>
<body id="red-cultural-viaje-escocia-page" <?php body_class('bg-white'); ?>>
	<?php if (function_exists('wp_body_open')) { wp_body_open(); } ?>

	<?php
	if ($rcp_theme_header_html !== '') {
		echo '<div id="red-cultural-viaje-escocia-site-header">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $rcp_theme_header_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<header id="red-cultural-viaje-escocia-hero" aria-label="Expedición Escocia 2026">
		<div id="red-cultural-viaje-escocia-hero-inner">
			<div id="red-cultural-viaje-escocia-hero-libero-logo" aria-label="El Líbero">EL LIBERO</div>
			<div id="red-cultural-viaje-escocia-kicker">Expedición</div>
			<h1 id="red-cultural-viaje-escocia-title">Expedición Escocia 2026</h1>
			<p id="red-cultural-viaje-escocia-dates">10 al 20 de Junio</p>
			<p id="red-cultural-viaje-escocia-subtitle">Edimburgo, Orkney Islands, Dunnottar, Aberdeen y más junto a Magdalena Merbilháa.</p>
		</div>
	</header>

	<main id="red-cultural-viaje-escocia-content">
		<section id="red-cultural-viaje-escocia-libero" aria-label="Expediciones Líbero">
			<div id="red-cultural-viaje-escocia-libero-inner">
				<h2 id="red-cultural-viaje-escocia-libero-title">EXPEDICIONES LIBERO</h2>
				<p id="red-cultural-viaje-escocia-libero-text-1">Las Expediciones Líbero son otra dimensión del modelo de “periodismo en vivo” que desarrolla El Líbero. Buscan entregar contenido informativo y cultural in situ a los miembros de la Red Líbero que participen en estas actividades. Consisten en visitas en Chile o el extranjero donde los participantes logran ser testigos directos de acontecimientos noticiosos relevantes y participan de entrevistas a autoridades y líderes de opinión relevantes.</p>
				<p id="red-cultural-viaje-escocia-libero-text-2">Desde 2017, El Líbero ha realizado expediciones a Washington DC, Berlín, Corea y Taiwán, Inglaterra, Japón y Hawaii, Normandía, Copiapó, Frutillar, La Araucanía, ALMA en San Pedro de Atacama, entre otras.</p>
			</div>
		</section>

		<section id="red-cultural-viaje-escocia-threecol" aria-label="Qué exploraremos">
			<div id="red-cultural-viaje-escocia-threecol-inner">
				<div id="red-cultural-viaje-escocia-threecol-grid">
					<div id="red-cultural-viaje-escocia-threecol-col-1">
						<h2 id="red-cultural-viaje-escocia-threecol-q1">¿QUÉ ES LA EXPEDICIÓN?</h2>
						<p id="red-cultural-viaje-escocia-threecol-a1">
							Un viaje a la Escocia profunda, destacada por la ilustración escocesa, que recorrerá pueblos universitarios e islas recónditas.
							<br><br>
							<strong>Esto junto a la experta en Inglaterra Magdalena Merbilháa, periodista e historiadora.</strong>
						</p>
					</div>

					<div id="red-cultural-viaje-escocia-threecol-col-2">
						<div id="red-cultural-viaje-escocia-threecol-mid-1">
							<h2 id="red-cultural-viaje-escocia-threecol-q2">¿POR QUÉ SE CENTRA EN LA ILUSTRACIÓN ESCOCESA?</h2>
							<p id="red-cultural-viaje-escocia-threecol-a2">La Ilustración Escocesa (Smith, Hume) en Edimburgo forjó la economía moderna y la planificación urbana, mientras que las islas preservan la herencia celta.</p>
						</div>
						<div id="red-cultural-viaje-escocia-threecol-mid-2">
							<h2 id="red-cultural-viaje-escocia-threecol-q3">¿QUÉ OBJETIVO TIENE?</h2>
							<p id="red-cultural-viaje-escocia-threecol-a3">Comprender el impacto de la ilustración escocesa en la sociedad moderna y conocer.</p>
						</div>
					</div>

					<div id="red-cultural-viaje-escocia-threecol-col-3">
						<h2 id="red-cultural-viaje-escocia-threecol-q4">¿QUÉ LUGARES EXPLORAREMOS?</h2>
						<p id="red-cultural-viaje-escocia-threecol-a4">Edimburgo, el centro de la Ilustración y el imponente Castillo Dunnottar. Ferry a las Islas Orkney, donde está el poblado neolítico de Skara Brae y el círculo de piedras de Ring of Brodgar. Oban en la costa oeste, islas Mull e Iona (un sitio sagrado y antiguo), para cerrar este gran circuito en la ciudad de Glasgow, otro foco de la Ilustración escocesa.</p>
					</div>
				</div>
			</div>
		</section>

		<section id="red-cultural-viaje-escocia-general" aria-label="Itinerario general">
			<div id="red-cultural-viaje-escocia-general-inner">
				<div id="red-cultural-viaje-escocia-general-grid">
					<div id="red-cultural-viaje-escocia-general-col-left">
						<div id="red-cultural-viaje-escocia-itinerary-image">
							<img
								id="red-cultural-viaje-escocia-itinerary-img"
								src="<?php echo esc_url($itinerary_img_local); ?>"
								data-fallback="<?php echo esc_url($itinerary_img_live); ?>"
								alt="Itinerario general"
								loading="lazy"
								referrerpolicy="no-referrer"
								onerror="if(this.dataset.fallback&&this.src!==this.dataset.fallback){this.src=this.dataset.fallback;}"
							>
						</div>
					</div>

					<div id="red-cultural-viaje-escocia-general-col-right">
						<h2 id="red-cultural-viaje-escocia-general-title">ITINERARIO GENERAL</h2>
						<ul id="red-cultural-viaje-escocia-general-list">
							<li id="red-cultural-viaje-escocia-general-1"><strong>Primera parada:</strong> Miércoles 10 de junio, Edimburgo, Escocia.</li>
							<li id="red-cultural-viaje-escocia-general-2"><strong>Segunda parada:</strong> Sábado 13 de junio, Kirkwall, Islas Orkney.</li>
							<li id="red-cultural-viaje-escocia-general-3"><strong>Tercera parada:</strong> Lunes 15 de junio, Pitlochry.</li>
							<li id="red-cultural-viaje-escocia-general-4"><strong>Cuarta parada:</strong> Martes 16 de junio, Oban.</li>
							<li id="red-cultural-viaje-escocia-general-5"><strong>Quinta parada:</strong> Jueves 18 de junio, Cerca de Edimburgo, visita Glasgow.</li>
						</ul>
					</div>
				</div>
			</div>
		</section>

		<section id="red-cultural-viaje-escocia-gallery" aria-label="Galería de imágenes">
			<h2 id="red-cultural-viaje-escocia-gallery-title">Galería de Imágenes</h2>
			<ul id="red-cultural-viaje-escocia-gallery-grid">
				<?php foreach ($gallery_items as $index => $item) : ?>
					<?php
					$rel = (string) $item['rel'];
					$local = $uploads_base . $rel;
					$live = 'https://red-cultural.cl/wp-content/uploads' . $rel;
					$slug = isset($item['slug']) ? (string) $item['slug'] : (string) $index;
					$alt = isset($item['alt']) ? (string) $item['alt'] : '';
					?>
					<li id="<?php echo esc_attr('red-cultural-viaje-escocia-gallery-item-' . $slug); ?>">
						<a
							id="<?php echo esc_attr('red-cultural-viaje-escocia-gallery-link-' . $slug); ?>"
							href="<?php echo esc_url($local); ?>"
							target="_blank"
							rel="noopener noreferrer"
						>
							<img
								id="<?php echo esc_attr('red-cultural-viaje-escocia-gallery-img-' . $slug); ?>"
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

		<section id="red-cultural-viaje-escocia-detailed" aria-label="Itinerario detallado">
			<h2 id="red-cultural-viaje-escocia-detailed-title">Itinerario Detallado</h2>

			<div id="red-cultural-viaje-escocia-detailed-table-wrap">
				<table id="red-cultural-viaje-escocia-detailed-table" class="itinerario-table">
					<thead id="red-cultural-viaje-escocia-detailed-thead">
						<tr id="red-cultural-viaje-escocia-detailed-head-row">
							<th id="red-cultural-viaje-escocia-detailed-th-day">Día</th>
							<th id="red-cultural-viaje-escocia-detailed-th-date">Fecha</th>
							<th id="red-cultural-viaje-escocia-detailed-th-city">Ciudad</th>
							<th id="red-cultural-viaje-escocia-detailed-th-activities">Principales Actividades del Día</th>
						</tr>
					</thead>
					<tbody id="red-cultural-viaje-escocia-detailed-tbody">
						<tr id="red-cultural-viaje-escocia-detailed-row-travel">
							<td>Viaje</td>
							<td>Martes, 9 de junio</td>
							<td>Santiago - Edimburgo</td>
							<td>Salida desde Santiago con destino a Edimburgo.</td>
						</tr>
						<tr id="red-cultural-viaje-escocia-detailed-row-1">
							<td>Día 1</td>
							<td>Miércoles, 10 de junio</td>
							<td>Edimburgo</td>
							<td>Llegada al Aeropuerto de Edimburgo (EDI). Traslado al <strong>The George Hotel</strong>. Tarde libre para descanso.</td>
						</tr>
						<tr id="red-cultural-viaje-escocia-detailed-row-2">
							<td>Día 2</td>
							<td>Jueves, 11 de junio</td>
							<td>Edimburgo</td>
							<td>Mañana dedicada a la historia: Visita al <strong>Castillo de Edimburgo</strong> y exploración del <strong>Royal Mile</strong>. Almuerzo en <strong>The Witchery</strong>. Tarde cultural: Visita a la <strong>Catedral de Saint Giles</strong> y la <strong>tumba de Adam Smith</strong>. Visita al <strong>Palacio de Holyroodhouse</strong> y a <strong>Real Mary King's Close</strong>.</td>
						</tr>
						<tr id="red-cultural-viaje-escocia-detailed-row-3">
							<td>Día 3</td>
							<td>Viernes, 12 de junio</td>
							<td>Edimburgo</td>
							<td>Mañana cultural: Visita a la <strong>Galería Nacional de Arte</strong>. Noche de música clásica: Asistencia a la actuación en el <strong>Usher Hall</strong>.</td>
						</tr>
						<tr id="red-cultural-viaje-escocia-detailed-row-4">
							<td>Día 4</td>
							<td>Sábado, 13 de junio</td>
							<td>Islas Orcadas (Orkney)</td>
							<td>Traslado al aeropuerto y <strong>vuelo de Edimburgo a Kirkwall</strong>. Traslado al <strong>The Kirkwall Hotel</strong>. Por la tarde, excursión a los sitios neolíticos: <strong>Skara Brae</strong>, el <strong>Anillo de Brogar</strong> y las <strong>Piedras de Stennes</strong>.</td>
						</tr>
						<tr id="red-cultural-viaje-escocia-detailed-row-5">
							<td>Día 5</td>
							<td>Domingo, 14 de junio</td>
							<td>Islas Orcadas (Orkney)</td>
							<td>Mañana dedicada a Kirkwall: Visita a la <strong>Catedral de San Magnus</strong>. Almuerzo en un pub local. Por la tarde, excursión a la <strong>Capilla Italiana</strong> y visita a los <strong>vestigios de la Segunda Guerra Mundial</strong>.</td>
						</tr>
						<tr id="red-cultural-viaje-escocia-detailed-row-6">
							<td>Día 6</td>
							<td>Lunes, 15 de junio</td>
							<td>Orcadas - Aberdeen - Pitlochry</td>
							<td>Traslado al aeropuerto y <strong>vuelo de Kirkwall a Aberdeen</strong>. Visita al <strong>Castillo de Dunnottar</strong> (cerca de Stonehaven). Traslado a Pitlochry.</td>
						</tr>
						<tr id="red-cultural-viaje-escocia-detailed-row-7">
							<td>Día 7</td>
							<td>Martes, 16 de junio</td>
							<td>Pitlochry - Oban</td>
							<td>Mañana de viaje: Visita al <strong>Castillo de Inveraray</strong>. Almuerzo de picnic en el pueblo o sus alrededores. Por la tarde, traslado a Oban y alojamiento en <strong>Dungallan Country House</strong>. Visita y degustación en la <strong>Destilería Oban</strong>.</td>
						</tr>
						<tr id="red-cultural-viaje-escocia-detailed-row-8">
							<td>Día 8</td>
							<td>Miércoles, 17 de junio</td>
							<td>Islas Mull e Iona</td>
							<td>Día completo dedicado a las islas. Tomar el <strong>ferry a Mull</strong>. Visita a la <strong>Destilería Tobermory</strong>. Tomar el ferry a la sagrada <strong>Isla de Iona</strong>. Regreso a Oban.</td>
						</tr>
						<tr id="red-cultural-viaje-escocia-detailed-row-9">
							<td>Día 9</td>
							<td>Jueves, 18 de junio</td>
							<td>Glasgow - Edimburgo</td>
							<td>Traslado a Glasgow. Mañana cultural: Visita a la <strong>Galería de Arte Kelvingrove</strong> y <strong>Universidad de Glasgow</strong>. Visita al <strong>Hunterian Museum</strong>. Picnic en el parque. Traslado a Edimburgo y alojamiento en el <strong>Norton House Hotel</strong>.</td>
						</tr>
						<tr id="red-cultural-viaje-escocia-detailed-row-10">
							<td>Día 10</td>
							<td>Viernes, 19 de junio</td>
							<td>Stirling &amp; Fronteras Escocesas</td>
							<td>Excursión de un día: Visita a <strong>Stirling</strong> (Castillo de Stirling o Monumento a Wallace). Pasos por <strong>Falkirk</strong>. Visita a la <strong>Casa de Sir Walter Scott en Abbotsford</strong>. Traslado y cena en el <strong>Norton House Hotel</strong>.</td>
						</tr>
						<tr id="red-cultural-viaje-escocia-detailed-row-return">
							<td>Regreso</td>
							<td>Sábado, 20 de junio</td>
							<td>Edimburgo - Santiago</td>
							<td>Desayuno. Traslado al aeropuerto de Edimburgo (EDI) con Michael para tomar el vuelo de regreso a Santiago.</td>
						</tr>
					</tbody>
				</table>
			</div>
		</section>

		<section id="red-cultural-viaje-escocia-terms" aria-label="Términos y condiciones">
			<h2 id="red-cultural-viaje-escocia-terms-title">TÉRMINOS Y CONDICIONES GENERALES</h2>

			<h3 id="red-cultural-viaje-escocia-prices-title">Precios Público General:</h3>
			<ul id="red-cultural-viaje-escocia-prices-public">
				<li id="red-cultural-viaje-escocia-prices-public-1">Habitación single: USD 14.080</li>
				<li id="red-cultural-viaje-escocia-prices-public-2">Habitación doble: USD 11.546</li>
			</ul>

			<h3 id="red-cultural-viaje-escocia-prices-member-title">Precios Red Líbero y Red Cultural:</h3>
			<ul id="red-cultural-viaje-escocia-prices-member">
				<li id="red-cultural-viaje-escocia-prices-member-1">Habitación single: USD 12.800</li>
				<li id="red-cultural-viaje-escocia-prices-member-2">Habitación doble: USD 10.500</li>
				<li id="red-cultural-viaje-escocia-prices-member-3"><em>*10% de descuento para hijos de participantes.</em></li>
			</ul>

			<h3 id="red-cultural-viaje-escocia-includes-title">INCLUYE:</h3>
			<ul id="red-cultural-viaje-escocia-includes">
				<li id="red-cultural-viaje-escocia-includes-1">Vuelo Santiago - Edimburgo - Santiago (salida 9 de junio, vuelta flexible).</li>
				<li id="red-cultural-viaje-escocia-includes-2">Alojamiento en hoteles 4 estrellas o superior.</li>
				<li id="red-cultural-viaje-escocia-includes-3">Transportes locales.</li>
				<li id="red-cultural-viaje-escocia-includes-4">Desayuno y almuerzos livianos.</li>
				<li id="red-cultural-viaje-escocia-includes-5">Una cena.</li>
				<li id="red-cultural-viaje-escocia-includes-6">Entradas a eventos y actividades.</li>
				<li id="red-cultural-viaje-escocia-includes-7">Encuentros previos con historiadores y líderes de opinión.</li>
			</ul>

			<h3 id="red-cultural-viaje-escocia-excludes-title">NO INCLUYE:</h3>
			<ul id="red-cultural-viaje-escocia-excludes">
				<li id="red-cultural-viaje-escocia-excludes-1">Transporte desde o hacia aeropuertos (excepto lo mencionado).</li>
				<li id="red-cultural-viaje-escocia-excludes-2">Noches extras en hoteles.</li>
				<li id="red-cultural-viaje-escocia-excludes-3">Gastos personales.</li>
				<li id="red-cultural-viaje-escocia-excludes-4">Seguros de viaje (Obligatorio contratar uno).</li>
				<li id="red-cultural-viaje-escocia-excludes-5">Upgrades de habitaciones y pasajes.</li>
				<li id="red-cultural-viaje-escocia-excludes-6">Costos de visado.</li>
			</ul>

			<h3 id="red-cultural-viaje-escocia-reserve-title">¿CÓMO RESERVO?</h3>
			<p id="red-cultural-viaje-escocia-reserve-text">Escribe a <strong>veronica.tagle@ellibero.cl</strong> o al <strong>+56978820992</strong> para revisar disponibilidad.</p>
			<ul id="red-cultural-viaje-escocia-reserve-list">
				<li id="red-cultural-viaje-escocia-reserve-1">Reserva de cupo: Pago de USD 3000 (no reembolsable, se descuenta del total).</li>
				<li id="red-cultural-viaje-escocia-reserve-2">Firma de convenio de plazos de pago tras confirmar participación.</li>
			</ul>

			<h3 id="red-cultural-viaje-escocia-refunds-title">POLÍTICAS DE DEVOLUCIÓN (CANCELACIÓN)</h3>
			<ul id="red-cultural-viaje-escocia-refunds">
				<li id="red-cultural-viaje-escocia-refunds-1">Más de 150 días: 10% del valor total.</li>
				<li id="red-cultural-viaje-escocia-refunds-2">Entre 90 y 150 días: 20% del valor total.</li>
				<li id="red-cultural-viaje-escocia-refunds-3">Entre 30 y 90 días: 50% del valor total.</li>
				<li id="red-cultural-viaje-escocia-refunds-4">Entre 10 y 30 días: 70% del valor total.</li>
				<li id="red-cultural-viaje-escocia-refunds-5">10 días o menos: 80% del valor total.</li>
				<li id="red-cultural-viaje-escocia-refunds-6">Excepción: No hay cobro si el participante encuentra un reemplazante aprobado por la organización.</li>
			</ul>

			<h3 id="red-cultural-viaje-escocia-payment-title">DATOS DE PAGO (Sociedad Periodística El Líbero S.A. / RUT 76.389.727-3)</h3>
			<ul id="red-cultural-viaje-escocia-payment">
				<li id="red-cultural-viaje-escocia-payment-1">Cuenta Pesos (BCI): 28975219</li>
				<li id="red-cultural-viaje-escocia-payment-2">Cuenta Dólares (BCI): 19643403</li>
				<li id="red-cultural-viaje-escocia-payment-3">Correo: administracion@ellibero.cl</li>
			</ul>
		</section>
	</main>

	<?php
	if ($rcp_theme_footer_html !== '') {
		echo '<div id="red-cultural-viaje-escocia-site-footer">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $rcp_theme_footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<?php wp_footer(); ?>
</body>
</html>
