<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Pre-render block theme template parts BEFORE wp_head so their assets are enqueued in the correct place.
$rcp_theme_header_html = '';
$rcp_theme_footer_html = '';
if ( function_exists( 'do_blocks' ) ) {
	$rcp_theme_header_html = (string) do_blocks( '<!-- wp:template-part {"slug":"header","area":"header"} /-->' );
	$rcp_theme_footer_html = (string) do_blocks( '<!-- wp:template-part {"slug":"footer","area":"footer"} /-->' );
}

?><!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html( (string) wp_get_document_title() ); ?></title>
	<?php wp_head(); ?>
</head>
<body id="red-cultural-404-page" <?php body_class(); ?>>
	<?php if ( function_exists( 'wp_body_open' ) ) { wp_body_open(); } ?>

	<?php
	// Render the active block theme header so navbar matches the rest of the site.
	if ( $rcp_theme_header_html !== '' ) {
		echo $rcp_theme_header_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<main class="rc404" id="red-cultural-404-main">
		<section class="rc404__hero" role="region" aria-label="404">
			<div class="rc404__inner">
				<h1 class="rc404__copy">
					¿te perdiste?
					<span class="rc404__sub">Esta página no existe</span>
					<span class="rc404__sub">Vuelve a la página principal</span>
				</h1>

				<p class="rc404__actions">
					<a class="rc404__btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">Principal</a>
				</p>
			</div>
		</section>
	</main>

	<?php
	if ( $rcp_theme_footer_html !== '' ) {
		echo $rcp_theme_footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<?php wp_footer(); ?>
</body>
</html>
