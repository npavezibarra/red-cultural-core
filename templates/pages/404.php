<?php
/**
 * 404 Template for Red Cultural
 *
 * This template is standalone to avoid theme wrapper issues (specifically BuddyBoss artifacts).
 * It uses block theme template parts (header/footer) to maintain site-wide branding.
 */

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

$bg_url = RC_CORE_URL . 'assets/images/lost-nietzsche.png';

?><!DOCTYPE html>
<html lang="es">
<head>
	<meta id="red-cultural-404-meta-charset" charset="UTF-8">
	<meta id="red-cultural-404-meta-viewport" name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html((string) wp_get_document_title()); ?></title>
	<script id="red-cultural-404-tailwind" src="https://cdn.tailwindcss.com"></script>
	<style id="red-cultural-404-style">
		@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

		body {
			font-family: 'Inter', sans-serif;
			background-color: #000;
			margin: 0;
			padding: 0;
			overflow-x: hidden;
		}

		#red-cultural-404-hero {
			background-image: url('<?php echo esc_url($bg_url); ?>');
			background-size: cover;
			background-position: center;
			background-attachment: fixed;
			min-height: calc(100vh - 200px); /* Adjust based on header/footer size */
			display: flex;
			align-items: center;
			justify-content: center;
			position: relative;
			padding: 40px 20px;
		}

		#red-cultural-404-overlay {
			position: absolute;
			inset: 0;
			background: linear-gradient(to bottom, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.7) 100%);
			backdrop-filter: blur(2px);
		}

		#red-cultural-404-content {
			position: relative;
			z-index: 10;
			background-color: rgba(255, 255, 255, 0.95);
			border: 1px solid #000;
			max-width: 600px;
			width: 100%;
			padding: 60px 40px;
			text-align: center;
			box-shadow: 0 20px 50px rgba(0,0,0,0.3);
		}

		.rcp-404-title {
			font-size: 80px;
			font-weight: 700;
			line-height: 1;
			margin-bottom: 20px;
			letter-spacing: -0.05em;
			color: #000;
		}

		.rcp-404-message {
			font-size: 18px;
			font-weight: 300;
			color: #444;
			margin-bottom: 30px;
			line-height: 1.6;
		}

		.rcp-404-btn {
			display: inline-block;
			background-color: #000;
			color: #fff;
			padding: 15px 40px;
			text-transform: uppercase;
			letter-spacing: 0.2em;
			font-size: 12px;
			font-weight: 600;
			transition: all 0.3s ease;
			border: 1px solid #000;
		}

		.rcp-404-btn:hover {
			background-color: #fff;
			color: #000;
			transform: translateY(-2px);
		}

		/* Ensure block theme header/footer look okay here */
		#red-cultural-404-site-header, 
		#red-cultural-404-site-footer {
			background-color: #fff;
		}
	</style>
	<?php wp_head(); ?>
</head>
<body id="red-cultural-404-page" <?php body_class('error404'); ?>>
	<?php if (function_exists('wp_body_open')) { wp_body_open(); } ?>

	<?php
	if ($rcp_theme_header_html !== '') {
		echo '<div id="red-cultural-404-site-header" class="relative z-[100]">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $rcp_theme_header_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<div id="red-cultural-404-hero">
		<div id="red-cultural-404-overlay" aria-hidden="true"></div>

		<main id="red-cultural-404-content">
			<h1 class="rcp-404-title">404</h1>
			<p class="rcp-404-message">
				¿Te perdiste?<br>
				<span class="font-medium">Esta página no existe o ha sido movida.</span><br>
				Nietzsche tampoco encontró el camino aquí.
			</p>
			
			<div class="flex flex-col sm:flex-row gap-4 justify-center">
				<a href="<?php echo esc_url(home_url('/')); ?>" class="rcp-404-btn">
					Volver al Inicio
				</a>
				<a href="<?php echo esc_url(home_url('/cursos')); ?>" class="rcp-404-btn bg-white !text-black hover:!bg-black hover:!text-white">
					Ver Cursos
				</a>
			</div>
		</main>
	</div>

	<?php
	if ($rcp_theme_footer_html !== '') {
		echo '<div id="red-cultural-404-site-footer" class="relative z-[100]">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $rcp_theme_footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<?php wp_footer(); ?>
</body>
</html>
