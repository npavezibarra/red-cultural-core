<?php
/**
 * 404 Template for Red Cultural
 *
 * This template is standalone to avoid theme wrapper issues.
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
			min-height: 60vh;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			position: relative;
			padding: 40px 20px;
			color: #ffffff;
			text-align: center;
		}

		#red-cultural-404-overlay {
			position: absolute;
			inset: 0;
			background: rgba(0, 0, 0, 0.4);
			/* Subtle backdrop blur to match the premium feel */
			backdrop-filter: blur(1px);
		}

		#red-cultural-404-content {
			position: relative;
			z-index: 10;
			max-width: 800px;
			width: 100%;
		}

		.rcp-404-title {
			font-size: clamp(48px, 8vw, 84px);
			font-weight: 500;
			line-height: 1.1;
			margin-bottom: 24px;
			letter-spacing: -0.02em;
			color: #ffffff;
		}

		.rcp-404-subtext {
			font-size: clamp(18px, 3vw, 24px);
			font-weight: 300;
			color: rgba(255, 255, 255, 0.9);
			margin-bottom: 8px;
			line-height: 1.4;
		}

		.rcp-404-btn {
			display: inline-block;
			background-color: #ffffff;
			color: #000000;
			padding: 12px 36px;
			font-size: 16px;
			font-weight: 600;
			border-radius: 6px;
			transition: all 0.2s ease-in-out;
			margin-top: 32px;
			text-decoration: none;
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
		}

		.rcp-404-btn:hover {
			transform: scale(1.05);
			background-color: #f8f8f8;
			box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
		}

		/* Optional: hide header/footer on 404 to match the screenshot if desired, 
		   but keeping them enqueued for SEO and branding consistency.
		   The user's example image doesn't show them, so I'll make the hero full screen. */
		
		#red-cultural-404-site-header, 
		#red-cultural-404-site-footer {
			background-color: #fff;
			position: relative;
			z-index: 100;
		}
	</style>
	<?php wp_head(); ?>
</head>
<body id="red-cultural-404-page" <?php body_class('error404'); ?>>
	<?php if (function_exists('wp_body_open')) { wp_body_open(); } ?>

	<?php
	// Render the active block theme header (optional, usually kept for navigation)
	if ($rcp_theme_header_html !== '') {
		echo '<div id="red-cultural-404-site-header">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $rcp_theme_header_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<div id="red-cultural-404-hero">
		<div id="red-cultural-404-overlay" aria-hidden="true"></div>

		<main id="red-cultural-404-content">
			<h1 class="rcp-404-title">¿te perdiste?</h1>
			<p class="rcp-404-subtext">Esta página no existe</p>
			<p class="rcp-404-subtext">Vuelve a la página principal</p>
			
			<a href="<?php echo esc_url(home_url('/')); ?>" class="rcp-404-btn">
				Principal
			</a>
		</main>
	</div>

	<?php
	// Render the active block theme footer
	if ($rcp_theme_footer_html !== '') {
		echo '<div id="red-cultural-404-site-footer">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $rcp_theme_footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<?php wp_footer(); ?>
</body>
</html>
