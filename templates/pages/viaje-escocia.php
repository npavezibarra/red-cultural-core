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

		#red-cultural-viaje-escocia-content{max-width:var(--wp--style--global--wide-size);margin:0 auto;padding:0 16px 40px;color:#111827}

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
		#red-cultural-viaje-escocia-general-inner{max-width:var(--wp--style--global--wide-size);margin:0 auto;padding:0px 26px}

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

		<section id="red-cultural-viaje-escocia-terms" class="mt-20 border-t pt-20">
            <!-- Header / Hero -->
            <div class="max-w-4xl mx-auto text-center mb-12">
                <div class="inline-block px-3 py-1 mb-4 text-[10px] font-bold tracking-widest text-white bg-[#AA0B0B] uppercase">
                    Información Oficial
                </div>
                <h1 class="text-2xl md:text-3xl font-bold tracking-tight mb-2 uppercase text-black">Términos y Condiciones Generales</h1>
                <p class="text-gray-400 text-sm">Programa de Viaje: Edimburgo 2026</p>
            </div>

            <div class="max-w-4xl mx-auto space-y-20">

                <!-- Precios -->
                <section id="precios">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="h-px flex-1 bg-gray-200"></div>
                        <h2 class="text-xl font-bold tracking-tight text-black">PRECIOS</h2>
                        <div class="h-px flex-1 bg-gray-200"></div>
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-8">
                        <!-- Publico General -->
                        <div class="p-8 border border-gray-100 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6">Público General</h3>
                            <div class="space-y-4">
                                <div class="flex justify-between items-end border-b border-gray-50 pb-2">
                                    <span class="text-gray-600">Habitación Single</span>
                                    <span class="text-xl font-bold text-black">USD 14.080</span>
                                </div>
                                <div class="flex justify-between items-end border-b border-gray-50 pb-2">
                                    <span class="text-gray-600">Habitación Doble</span>
                                    <span class="text-xl font-bold text-black">USD 11.546</span>
                                </div>
                            </div>
                        </div>

                        <!-- Red Libero -->
                        <div class="p-8 border-2 border-black rounded-lg relative overflow-hidden">
                            <div class="absolute top-0 right-0 bg-[#AA0B0B] text-white px-3 py-1 text-[10px] font-bold uppercase tracking-tighter">
                                Preferencial
                            </div>
                            <h3 class="text-sm font-bold text-[#AA0B0B] uppercase tracking-widest mb-6">Red Líbero y Cultural</h3>
                            <div class="space-y-4">
                                <div class="flex justify-between items-end border-b border-gray-50 pb-2">
                                    <span class="text-gray-600">Habitación Single</span>
                                    <span class="text-xl font-bold text-black">USD 12.800</span>
                                </div>
                                <div class="flex justify-between items-end border-b border-gray-50 pb-2">
                                    <span class="text-gray-600">Habitación Doble</span>
                                    <span class="text-xl font-bold text-black">USD 10.500</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="mt-6 text-sm italic text-gray-500 text-center">
                        * 10% de descuento adicional para hijos de participantes.
                    </p>
                </section>

                <!-- Inclusiones -->
                <section id="detalles" class="grid md:grid-cols-2 gap-12 text-left">
                    <div>
                        <h2 class="text-lg font-bold mb-6 flex items-center gap-2 text-black">
                            <span class="w-2 h-2 bg-[#AA0B0B] rounded-full"></span>
                            INCLUYE
                        </h2>
                        <ul class="space-y-4 text-sm text-gray-700 p-0 list-none">
                            <li class="flex gap-3">
                                <svg class="w-5 h-5 text-[#AA0B0B] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Vuelo Santiago - Edimburgo - Santiago (Salida 9 de junio, vuelta flexible).</span>
                            </li>
                            <li class="flex gap-3">
                                <svg class="w-5 h-5 text-[#AA0B0B] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Alojamiento en hoteles 4 estrellas o superior.</span>
                            </li>
                            <li class="flex gap-3">
                                <svg class="w-5 h-5 text-[#AA0B0B] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Transportes locales.</span>
                            </li>
                            <li class="flex gap-3">
                                <svg class="w-5 h-5 text-[#AA0B0B] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Desayuno y almuerzos livianos + Una cena especial.</span>
                            </li>
                            <li class="flex gap-3">
                                <svg class="w-5 h-5 text-[#AA0B0B] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Entradas a eventos, actividades y encuentros previos con expertos.</span>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h2 class="text-lg font-bold mb-6 flex items-center gap-2 text-black">
                            <span class="w-2 h-2 bg-black rounded-full"></span>
                            NO INCLUYE
                        </h2>
                        <ul class="space-y-4 text-sm text-gray-500 p-0 list-none">
                            <li class="flex gap-3">
                                <svg class="w-5 h-5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                <span>Traslados aeropuertos (excepto lo mencionado).</span>
                            </li>
                            <li class="flex gap-3">
                                <svg class="w-5 h-5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                <span>Noches extras o gastos personales.</span>
                            </li>
                            <li class="flex gap-3 font-bold text-black">
                                <svg class="w-5 h-5 text-black shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                <span>Seguros de viaje (Obligatorio contratar uno).</span>
                            </li>
                            <li class="flex gap-3">
                                <svg class="w-5 h-5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                <span>Upgrades de habitación/pasajes y costos de visado.</span>
                            </li>
                        </ul>
                    </div>
                </section>

                <!-- Reservas -->
                <section id="reservas" class="bg-gray-50 p-10 rounded-2xl text-center">
                    <h2 class="text-xl font-bold mb-8 text-center uppercase tracking-tight text-black">¿CÓMO RESERVO?</h2>
                    <div class="grid md:grid-cols-3 gap-8 text-center">
                        <div class="space-y-2">
                            <div class="text-[#AA0B0B] font-bold text-2xl">01</div>
                            <p class="text-sm font-semibold text-black">Contactar</p>
                            <p class="text-xs text-gray-500">veronica.tagle@ellibero.cl<br>+569 7882 0992</p>
                        </div>
                        <div class="space-y-2">
                            <div class="text-[#AA0B0B] font-bold text-2xl">02</div>
                            <p class="text-sm font-semibold text-black">Reserva de Cupo</p>
                            <p class="text-xs text-gray-500">Pago de USD 3.000<br>(No reembolsable)</p>
                        </div>
                        <div class="space-y-2">
                            <div class="text-[#AA0B0B] font-bold text-2xl">03</div>
                            <p class="text-sm font-semibold text-black">Formalizar</p>
                            <p class="text-xs text-gray-500">Firma de convenio de plazos de pago tras confirmar.</p>
                        </div>
                    </div>
                </section>

                <!-- Políticas de Cancelación -->
                <section id="cancelacion" class="text-left">
                    <h2 class="text-xl font-bold mb-8 border-l-4 border-[#AA0B0B] pl-4 uppercase tracking-tight text-black">Políticas de Devolución</h2>
                    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-sm m-0">
                            <thead class="bg-black text-white uppercase text-[10px] tracking-widest">
                                <tr>
                                    <th class="px-6 py-4">Plazo de Cancelación</th>
                                    <th class="px-6 py-4 text-right">Retención (Costo)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr>
                                    <td class="px-6 py-4 text-gray-600">Más de 150 días antes</td>
                                    <td class="px-6 py-4 text-right font-bold text-black">10% del total</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 text-gray-600">Entre 90 y 150 días</td>
                                    <td class="px-6 py-4 text-right font-bold text-black">20% del total</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 text-gray-600">Entre 30 y 90 días</td>
                                    <td class="px-6 py-4 text-right font-bold text-black">50% del total</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 text-gray-600">Entre 10 y 30 días</td>
                                    <td class="px-6 py-4 text-right font-bold text-black">70% del total</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 text-gray-600 font-bold text-[#AA0B0B]">10 días o menos</td>
                                    <td class="px-6 py-4 text-right font-bold text-[#AA0B0B]">80% del total</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 p-4 bg-[#AA0B0B]/5 rounded-lg border-l-4 border-[#AA0B0B]">
                        <p class="text-sm text-gray-700 m-0">
                            <strong>Excepción:</strong> No habrá cobro si el participante encuentra un reemplazante aprobado por la organización.
                        </p>
                    </div>
                </section>

                <!-- Datos de Pago -->
                <section id="pago" class="border-t pt-12 text-left">
                    <h2 class="text-lg font-bold mb-8 uppercase tracking-widest text-center text-black">Datos de Pago</h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Tarjeta ahora en Rojo Accent -->
                        <div class="bg-[#AA0B0B] text-white p-8 rounded-xl shadow-lg">
                            <p class="text-[10px] uppercase tracking-widest opacity-80 mb-1">Empresa</p>
                            <p class="font-bold text-base mb-4">Sociedad Periodística El Líbero S.A.</p>
                            <p class="text-[10px] uppercase tracking-widest opacity-80 mb-1">RUT</p>
                            <p class="font-mono mb-4 text-sm">76.389.727-3</p>
                            <p class="text-[10px] uppercase tracking-widest opacity-80 mb-1">Banco</p>
                            <p class="font-semibold italic text-sm">BCI</p>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="p-4 border border-gray-100 rounded-lg bg-gray-50">
                                <p class="text-xs uppercase font-bold text-gray-400 m-0">Cuenta Pesos (CLP)</p>
                                <p class="text-base font-mono font-bold tracking-wider text-black m-0">28975219</p>
                            </div>
                            <div class="p-4 border border-gray-100 rounded-lg bg-gray-50">
                                <p class="text-xs uppercase font-bold text-gray-400 m-0">Cuenta Dólares (USD)</p>
                                <p class="text-base font-mono font-bold tracking-wider text-[#AA0B0B] m-0">19643403</p>
                            </div>
                            <div class="pt-2 text-center md:text-left">
                                <p class="text-[10px] text-gray-500 mb-1 uppercase tracking-tighter">Enviar comprobante a:</p>
                                <a href="mailto:administracion@ellibero.cl" class="text-xs font-bold underline decoration-[#AA0B0B] underline-offset-4 text-black">administracion@ellibero.cl</a>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
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
