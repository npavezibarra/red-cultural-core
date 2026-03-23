<?php
if (!defined('ABSPATH')) {
	exit;
}

get_header();
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
				<a class="rc404__btn" href="<?php echo esc_url(home_url('/')); ?>">Principal</a>
			</p>
		</div>
	</section>
</main>

<?php
get_footer();
