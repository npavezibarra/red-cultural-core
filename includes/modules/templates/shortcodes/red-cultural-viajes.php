<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

function rcp_red_cultural_viajes_maybe_enqueue_assets(): void {
	if (is_admin()) {
		return;
	}

	if (!is_singular()) {
		return;
	}

	$post = get_post();
	if (!$post || !isset($post->post_content)) {
		return;
	}

	if (!has_shortcode((string) $post->post_content, 'red-cultural-viajes')) {
		return;
	}

	wp_enqueue_script('rcp-tailwind-cdn');
	wp_enqueue_style('rcp-inter-font');
}

add_action('wp_enqueue_scripts', 'rcp_red_cultural_viajes_maybe_enqueue_assets', 20);

function rcp_red_cultural_viajes_register_assets(): void {
	if (is_admin()) {
		return;
	}

	if (!wp_script_is('rcp-tailwind-cdn', 'registered')) {
		wp_register_script('rcp-tailwind-cdn', 'https://cdn.tailwindcss.com', array(), null, false);
	}

	if (!wp_style_is('rcp-inter-font', 'registered')) {
		wp_register_style('rcp-inter-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700&display=swap', array(), null);
	}
}

add_action('wp_enqueue_scripts', 'rcp_red_cultural_viajes_register_assets', 1);

function rcp_red_cultural_viajes_shortcode(array $atts = array()): string {
	$uploads = wp_get_upload_dir();
	$uploads_base = isset($uploads['baseurl']) ? (string) $uploads['baseurl'] : (string) content_url('/uploads');
	$uploads_base = rtrim($uploads_base, '/');

	$hero_bg_rel = '/2024/08/BudaPestCity.jpg';
	$hero_bg_local = $uploads_base . $hero_bg_rel;
	$hero_bg_live = 'https://red-cultural.cl/wp-content/uploads/2024/08/BudaPestCity.jpg';

	$cocha_logo_rel = '/2024/08/CoChaRedLogo.png';
	$cocha_logo_local = $uploads_base . $cocha_logo_rel;
	$cocha_logo_live = 'https://red-cultural.cl/wp-content/uploads/2024/08/CoChaRedLogo.png';

	$viaje_italia_url = (string) home_url('/viaje-italia/');
	$viaje_escocia_url = (string) home_url('/viaje-escocia/');
	$viaje_escandinavia_url = (string) home_url('/viaje-escandinavia/');
	$viaje_japon_url = (string) home_url('/viaje-japon/');

	ob_start();
	?>
	<div id="red-cultural-viajes">
		<style id="red-cultural-viajes-style">
			#red-cultural-viajes{font-family:'Inter',sans-serif}
			#red-cultural-viajes-hero-inner{padding:0 !important}
			@media screen and (max-width: 1240px) {
				#red-cultural-viajes-hero-inner {
					padding: 0px 30px !important;
				}
			}
			#red-cultural-viajes .rcp-viajes-banner{position:relative;overflow:hidden}
			#red-cultural-viajes .rcp-viajes-banner::before{
				content:'';
				position:absolute;
				inset:0;
				background-image:
					url('<?php echo esc_url($hero_bg_local); ?>'),
					url('<?php echo esc_url($hero_bg_live); ?>');
				background-size:cover,cover;
				background-position:center,center;
				filter:blur(5px);
				transform:scale(1.06);
				z-index:0;
			}
			#red-cultural-viajes .rcp-viajes-banner::after{
				content:'';
				position:absolute;
				inset:0;
				background:linear-gradient(to right, rgba(0,0,0,0.65), rgba(0,0,0,0.2));
				z-index:1;
			}
			#red-cultural-viajes-hero-inner{position:relative;z-index:2}
			#red-cultural-viajes .rcp-viajes-card-bg{background-size:cover;background-position:center;transition:transform .5s ease}
			#red-cultural-viajes .rcp-viajes-card:hover .rcp-viajes-card-bg{transform:scale(1.05)}
		</style>

		<section id="red-cultural-viajes-hero" class="rcp-viajes-banner w-full relative min-h-[450px] flex items-start text-white pt-0 pb-12 overflow-hidden">
			<div id="red-cultural-viajes-hero-inner" class="max-w-[1180px] mx-auto w-full px-6 flex flex-col md:flex-row justify-between items-start">
				<div id="red-cultural-viajes-hero-copy" class="max-w-2xl mt-12 md:mt-20">
					<span id="red-cultural-viajes-hero-kicker" class="uppercase tracking-[0.3em] text-xs md:text-sm font-light mb-2 block opacity-90">Lugares con historia</span>
					<h1 id="red-cultural-viajes-hero-title" class="text-[58px] font-bold leading-[1.1] mb-6">Viajes<br>Culturales</h1>
					<div id="red-cultural-viajes-hero-accent" class="w-20 h-[3px] bg-yellow-400 mb-8"></div>
					<p id="red-cultural-viajes-hero-text" class="text-lg md:text-xl lg:text-2xl leading-relaxed font-light max-w-xl opacity-95">
						Explora el legado del mundo con una mirada experta. Red Cultural y COCHA te llevan a recorrer ciudades icónicas a través de vivencias culturales inigualables, guiadas por las historiadoras
						<span id="red-cultural-viajes-hero-name-1" class="font-normal">Magdalena Merbilháa</span> y
						<span id="red-cultural-viajes-hero-name-2" class="font-normal">Bárbara Bustamante</span>.
						Un viaje diseñado para quienes buscan profundidad y sentido.
						<span id="red-cultural-viajes-hero-tag" class="font-bold uppercase tracking-wider text-sm">Así me gusta viajar</span>.
					</p>
				</div>

				<div id="red-cultural-viajes-hero-logo" class="self-start">
					<img
						id="red-cultural-viajes-cocha-logo"
						src="<?php echo esc_url($cocha_logo_local); ?>"
						data-fallback="<?php echo esc_url($cocha_logo_live); ?>"
						alt="COCHA Logo"
						class="w-24 md:w-32 lg:w-36 h-auto drop-shadow-xl block"
						loading="lazy"
						referrerpolicy="no-referrer"
						onerror="if(this.dataset.fallback&&this.src!==this.dataset.fallback){this.src=this.dataset.fallback;}"
					>
				</div>
			</div>
		</section>

		<section id="red-cultural-viajes-upcoming" class="py-20 px-6">
			<div id="red-cultural-viajes-upcoming-inner" class="max-w-[1180px] mx-auto">
				<div id="red-cultural-viajes-grid" class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-20">

					<div id="red-cultural-viajes-trip-1" class="flex flex-col items-center text-center">
						<span id="red-cultural-viajes-trip-1-badge" class="bg-red-600 text-white text-[10px] font-bold tracking-[0.2em] px-4 py-1 mb-4 uppercase">Próximo Viaje</span>
						<h2 id="red-cultural-viajes-trip-1-title" class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Nápoles, Sicilia y Malta</h2>
						<p id="red-cultural-viajes-trip-1-dates" class="text-lg text-gray-600 mb-4">16-marzo al 01 abril de 2026</p>

						<div id="red-cultural-viajes-trip-1-card" class="rcp-viajes-card relative w-full aspect-[16/9] overflow-hidden rounded-[18px] shadow-lg group cursor-pointer">
							<div id="red-cultural-viajes-trip-1-bg" class="rcp-viajes-card-bg absolute inset-0" style="background-image:url('<?php echo esc_url($uploads_base . '/2025/12/Sicilia2.jpg'); ?>'),url('https://red-cultural.cl/wp-content/uploads/2025/12/Sicilia2.jpg');"></div>
							<div id="red-cultural-viajes-trip-1-overlay" class="absolute inset-0 bg-black/10 group-hover:bg-black/20 transition-colors"></div>
							<div id="red-cultural-viajes-trip-1-cta-wrap" class="absolute inset-0 flex items-center justify-center">
								<a id="red-cultural-viajes-trip-1-cta" href="<?php echo esc_url($viaje_italia_url); ?>" class="bg-white text-[#00b1ba] font-bold text-xs tracking-widest px-8 py-3 rounded-full shadow-lg transform translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
									VER ITINERARIO
								</a>
							</div>
						</div>
					</div>

					<div id="red-cultural-viajes-trip-2" class="flex flex-col items-center text-center">
						<span id="red-cultural-viajes-trip-2-badge" class="bg-red-600 text-white text-[10px] font-bold tracking-[0.2em] px-4 py-1 mb-4 uppercase">Próximo Viaje</span>
						<h2 id="red-cultural-viajes-trip-2-title" class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Expedición Escocia 2026</h2>
						<p id="red-cultural-viajes-trip-2-dates" class="text-lg text-gray-600 mb-4">10 al 20 de Junio</p>

						<div id="red-cultural-viajes-trip-2-card" class="rcp-viajes-card relative w-full aspect-[16/9] overflow-hidden rounded-[18px] shadow-lg group cursor-pointer">
							<div id="red-cultural-viajes-trip-2-bg" class="rcp-viajes-card-bg absolute inset-0" style="background-image:url('<?php echo esc_url($uploads_base . '/2025/12/Edimburg1.jpg'); ?>'),url('https://red-cultural.cl/wp-content/uploads/2025/12/Edimburg1.jpg');"></div>
							<div id="red-cultural-viajes-trip-2-overlay" class="absolute inset-0 bg-black/10 group-hover:bg-black/20 transition-colors"></div>
							<div id="red-cultural-viajes-trip-2-cta-wrap" class="absolute inset-0 flex items-center justify-center">
								<a id="red-cultural-viajes-trip-2-cta" href="<?php echo esc_url($viaje_escocia_url); ?>" class="bg-white text-[#8b7355] font-bold text-xs tracking-widest px-8 py-3 rounded-full shadow-lg transform translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
									VER ITINERARIO
								</a>
							</div>
						</div>
					</div>

					<div id="red-cultural-viajes-trip-3" class="flex flex-col items-center text-center">
						<span id="red-cultural-viajes-trip-3-badge" class="bg-red-600 text-white text-[10px] font-bold tracking-[0.2em] px-4 py-1 mb-4 uppercase">Próximo Viaje</span>
						<h2 id="red-cultural-viajes-trip-3-title" class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Escandinavia</h2>
						<p id="red-cultural-viajes-trip-3-dates" class="text-lg text-gray-600 mb-4">16-marzo al 01 al abril de 2026</p>

						<div id="red-cultural-viajes-trip-3-card" class="rcp-viajes-card relative w-full aspect-[16/9] overflow-hidden rounded-[18px] shadow-lg group cursor-pointer">
							<div id="red-cultural-viajes-trip-3-bg" class="rcp-viajes-card-bg absolute inset-0" style="background-image:url('<?php echo esc_url($uploads_base . '/2025/12/Sweden1.jpg'); ?>'),url('https://red-cultural.cl/wp-content/uploads/2025/12/Sweden1.jpg');"></div>
							<div id="red-cultural-viajes-trip-3-overlay" class="absolute inset-0 bg-black/10 group-hover:bg-black/20 transition-colors"></div>
							<div id="red-cultural-viajes-trip-3-cta-wrap" class="absolute inset-0 flex items-center justify-center">
								<a id="red-cultural-viajes-trip-3-cta" href="<?php echo esc_url($viaje_escandinavia_url); ?>" class="bg-white text-[#6ca0dc] font-bold text-xs tracking-widest px-8 py-3 rounded-full shadow-lg transform translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
									VER ITINERARIO
								</a>
							</div>
						</div>
					</div>

					<div id="red-cultural-viajes-trip-4" class="flex flex-col items-center text-center">
						<span id="red-cultural-viajes-trip-4-badge" class="bg-red-600 text-white text-[10px] font-bold tracking-[0.2em] px-4 py-1 mb-4 uppercase">Próximo Viaje</span>
						<h2 id="red-cultural-viajes-trip-4-title" class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Japón</h2>
						<p id="red-cultural-viajes-trip-4-dates" class="text-lg text-gray-600 mb-4">26-octubre al 08 noviembre de 2026</p>

						<div id="red-cultural-viajes-trip-4-card" class="rcp-viajes-card relative w-full aspect-[16/9] overflow-hidden rounded-[18px] shadow-lg group cursor-pointer">
							<div id="red-cultural-viajes-trip-4-bg" class="rcp-viajes-card-bg absolute inset-0" style="background-image:url('<?php echo esc_url($uploads_base . '/2025/12/JapanMain.jpg'); ?>'),url('https://red-cultural.cl/wp-content/uploads/2025/12/JapanMain.jpg');"></div>
							<div id="red-cultural-viajes-trip-4-overlay" class="absolute inset-0 bg-black/10 group-hover:bg-black/20 transition-colors"></div>
							<div id="red-cultural-viajes-trip-4-cta-wrap" class="absolute inset-0 flex items-center justify-center">
								<a id="red-cultural-viajes-trip-4-cta" href="<?php echo esc_url($viaje_japon_url); ?>" class="bg-white text-[#b088f9] font-bold text-xs tracking-widest px-8 py-3 rounded-full shadow-lg transform translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
									VER ITINERARIO
								</a>
							</div>
						</div>
					</div>

				</div>
			</div>
		</section>
	</div>
	<?php
	return (string) ob_get_clean();
}
