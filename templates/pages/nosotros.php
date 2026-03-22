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

?><!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html((string) wp_get_document_title()); ?></title>
	<?php wp_head(); ?>
</head>
<body id="red-cultural-nosotros-page" <?php body_class(); ?>>
	<?php if (function_exists('wp_body_open')) { wp_body_open(); } ?>

	<?php
	// Render the active block theme header so navbar matches the rest of the site.
	if ($rcp_theme_header_html !== '') {
		echo $rcp_theme_header_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<style id="red-cultural-nosotros-hero-style">
		.rc-nosotros-hero{display:none !important}
		#red-cultural-nosotros-hero{
			width:100vw;
			margin-left:calc(50% - 50vw);
			margin-right:calc(50% - 50vw);
			height:500px;
			margin-bottom:24px;
			background-image:
				linear-gradient(to top, rgba(0,0,0,.85) 0%, rgba(0,0,0,0) 95%),
				url('https://red-cultural.cl/wp-content/uploads/2026/03/red-cultural-main-cuadro.jpeg');
			background-size:cover;
			background-position:center;
			background-repeat:no-repeat;
			display:flex;
			align-items:flex-end;
			color:#fff;
		}
		#red-cultural-nosotros-hero-inner{width:100%;max-width:1180px;margin:0 auto;padding:0 16px 34px}
		#red-cultural-nosotros-hero-copy{width:65%;max-width:760px}
		#red-cultural-nosotros-hero-title{margin:0;font-size:48px;line-height:1.1;font-weight:700;color:#fff}
		#red-cultural-nosotros-hero-lead,
		#red-cultural-nosotros-hero-body{margin:14px 0 0;font-size:22px;line-height:1.45;font-weight:400;color:#fff}
		@media (max-width: 1250px){
			#red-cultural-nosotros-hero-inner{padding-left:30px;padding-right:30px}
		}
		@media (max-width: 900px){
			#red-cultural-nosotros-hero-copy{width:100%;max-width:none}
			#red-cultural-nosotros-hero-title{font-size:38px;line-height:1.15}
			#red-cultural-nosotros-hero-lead,
			#red-cultural-nosotros-hero-body{font-size:18px;line-height:1.5}
		}
	</style>

	<section id="red-cultural-nosotros-hero" aria-label="Sobre Red Cultural">
		<div id="red-cultural-nosotros-hero-inner">
			<div id="red-cultural-nosotros-hero-copy">
				<h2 id="red-cultural-nosotros-hero-title">Sobre Red Cultural.</h2>
				<p id="red-cultural-nosotros-hero-lead">Somos una fundación dedicada a la innovación educativa a través del rescate histórico y cultural. Creemos que el análisis de nuestras raíces y nuestra identidad es la herramienta clave para comprender el entorno actual, fortaleciendo el sentido de pertenencia y el pleno desarrollo del potencial humano.</p>
				<p id="red-cultural-nosotros-hero-body">Nuestra misión es acercar las Humanidades de forma accesible a toda la comunidad, eliminando barreras para llegar con más cultura a más personas.</p>
			</div>
		</div>
	</section>

	<main id="red-cultural-nosotros-main">
		<?php
		// Shortcode provides the full Nosotros content section.
		echo do_shortcode('[red-cultural-us id="red-cultural-nosotros"]'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
	</main>

	<?php
	if ($rcp_theme_footer_html !== '') {
		echo $rcp_theme_footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<?php wp_footer(); ?>
</body>
</html>
