<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('rcp_toscana_replace_section')) {
	function rcp_toscana_replace_section(string $html, string $section_id, string $replacement): string {
		$pattern = '~<section[^>]*id="' . preg_quote($section_id, '~') . '"[^>]*>.*?</section>~s';
		$result = preg_replace($pattern, $replacement, $html, 1);
		return is_string($result) ? $result : $html;
	}
}

if (!function_exists('rcp_toscana_build_itinerary_section')) {
	function rcp_toscana_build_itinerary_section(): string {
		$rows = array(
			array('day' => 'Día 1', 'subdate' => 'Lunes 08 de Marzo', 'localities' => 'Santiago - Madrid', 'itinerary' => 'Salida 12:50 hs en vuelo Iberia 118.', 'hotel' => ''),
			array('day' => 'Día 2', 'subdate' => 'Martes 09 de Marzo', 'localities' => 'Madrid - Roma - Florencia', 'itinerary' => '08:45 hs tomaremos vuelo IB 647 a Roma. 11:10 hs Llegada a Roma y traslado a Florencia. En el camino almorzaremos y luego visitaremos Tarquinia.', 'hotel' => 'Brunelleschi Hotel'),
			array('day' => 'Día 3', 'subdate' => 'Miércoles 10 de Marzo', 'localities' => 'Florencia', 'itinerary' => 'En la mañana visita al centro histórico de la ciudad y la Galería de la Academia. En la tarde visitaremos la Galleria Uffizi y luego tomaremos una merienda en el Caffe Gilli.', 'hotel' => 'Brunelleschi Hotel'),
			array('day' => 'Día 4', 'subdate' => 'Jueves 11 de Marzo', 'localities' => 'Florencia-Chianti-Asís', 'itinerary' => 'En la mañana salida hacia Asís visitando la región de Chianti. Visita, degustación y almuerzo en bodega Castello di Verrazzano. Continuación hacia Asís para alojar.', 'hotel' => 'Palace Hotel Fontebella Assisi'),
			array('day' => 'Día 5', 'subdate' => 'Viernes 12 de Marzo', 'localities' => 'Asís-Perugia-Asís', 'itinerary' => 'En la mañana visitaremos el centro de Asís y su Basílica. Continuaremos visitando Perugia y tendremos una degustación y experiencia de chocolate con un maestro chocolatero.', 'hotel' => 'Palace Hotel Fontebella Assisi'),
			array('day' => 'Día 6', 'subdate' => 'Sábado 13 de Marzo', 'localities' => 'Asís-Todi-Spoleto-Asís', 'itinerary' => 'Por la mañana visitaremos la ciudad medieval de Todi. En la tarde visitaremos Spoleto, su Catedral y el Puente de las Torres. Regreso a Asís para alojar', 'hotel' => 'Palace Hotel Fontebella Assisi'),
			array('day' => 'Día 7', 'subdate' => 'Domingo 14 de Marzo', 'localities' => 'Asís-Orvieto-Caprarola-Roma', 'itinerary' => 'En la mañana salida hacia Orvieto para visitar el centro histórico y su catedral. En la tarde visitaremos el Palazzo Farnese de Caprarola. Continuaremos hacia Roma para alojar.', 'hotel' => 'Hotel Nazionale Roma'),
			array('day' => 'Día 8', 'subdate' => 'Lunes 15 de Marzo', 'localities' => 'Roma', 'itinerary' => 'City tour privado a pie por Roma visitando la Plaza España, la Fontana di Trevi, El Panteón y la Piazza Navona. Tarde Libre.', 'hotel' => 'Hotel Nazionale Roma'),
			array('day' => 'Día 9', 'subdate' => 'Martes 16 de Marzo', 'localities' => 'Roma', 'itinerary' => 'Visita a la Basílica de San Pedro. En la tarde Visita a Castel Sant Angelo. 19:30 hs Visita Privada a los Museos Vaticanos y Capilla Sixtina.', 'hotel' => 'Hotel Nazionale Roma'),
			array('day' => 'Día 10', 'subdate' => 'Miércoles 17 de Marzo', 'localities' => 'Roma', 'itinerary' => 'Visitaremos el Coliseo y subterraneos. Visita a la Cloaca Máxima por el exterior y Domus Aurea. Puesta del sol en la terraza panorámica del Vittoriale.', 'hotel' => 'Hotel Nazionale Roma'),
			array('day' => 'Día 11', 'subdate' => 'Jueves 18 de Marzo', 'localities' => 'Roma-Tivoli-Roma', 'itinerary' => 'Salida a Tivoli para visitar Villa d Este y Villa Adriana. Regreso a Roma para alojar.', 'hotel' => 'Hotel Nazionale Roma'),
			array('day' => 'Día 12', 'subdate' => 'Viernes 19 de Marzo', 'localities' => 'Roma', 'itinerary' => 'Visitaremos Ostia Antica. Tarde Libre.', 'hotel' => 'Hotel Nazionale Roma'),
			array('day' => 'Día 13', 'subdate' => 'Sábado 20 de Marzo', 'localities' => 'Roma', 'itinerary' => 'Visita a la Galleria Borghese. Tarde libre.', 'hotel' => 'Hotel Nazionale Roma'),
			array('day' => 'Día 14', 'subdate' => 'Domingo 21 de Marzo', 'localities' => 'Roma - Madrid - Santiago', 'itinerary' => 'Mañana libre. En la tarde traslado al Aeropuerto de Roma para tomar vuelo Iberia 654 que sale 19:00 hs hacia Madrid', 'hotel' => 'Hotel Nazionale Roma'),
			array('day' => 'Día 15', 'subdate' => 'Lunes 22 de Marzo', 'localities' => 'Santiago', 'itinerary' => 'Llegada a Santiago 9:20 hs', 'hotel' => ''),
		);

		ob_start();
		?>
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
						<?php foreach ($rows as $index => $row) : ?>
							<tr id="<?php echo esc_attr('red-cultural-viaje-italia-itinerary-day-' . ($index + 1)); ?>">
								<td id="<?php echo esc_attr('red-cultural-viaje-italia-itinerary-day-' . ($index + 1) . '-date'); ?>" data-label="Fecha">
									<span class="rcp-itin-day" id="<?php echo esc_attr('red-cultural-viaje-italia-itinerary-day-' . ($index + 1) . '-day'); ?>">
										<?php echo esc_html((string) $row['day']); ?>
										<span class="rcp-itin-subdate" id="<?php echo esc_attr('red-cultural-viaje-italia-itinerary-day-' . ($index + 1) . '-subdate'); ?>"><?php echo esc_html((string) $row['subdate']); ?></span>
									</span>
								</td>
								<td id="<?php echo esc_attr('red-cultural-viaje-italia-itinerary-day-' . ($index + 1) . '-localities'); ?>" data-label="Localidades"><?php echo esc_html((string) $row['localities']); ?></td>
								<td id="<?php echo esc_attr('red-cultural-viaje-italia-itinerary-day-' . ($index + 1) . '-itinerary'); ?>" data-label="Itinerario"><?php echo esc_html((string) $row['itinerary']); ?></td>
								<td id="<?php echo esc_attr('red-cultural-viaje-italia-itinerary-day-' . ($index + 1) . '-hotels'); ?>" data-label="Hoteles"><?php echo esc_html((string) $row['hotel']); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</section>
		<?php
		return (string) ob_get_clean();
	}
}

if (!function_exists('rcp_toscana_build_gallery_section')) {
	function rcp_toscana_build_gallery_section(): string {
		$gallery_items = array(
			array(
				'slug' => 'roma-plaza',
				'url'  => 'https://red-cultural.cl/wp-content/uploads/2026/08/roma_300kb.jpg',
				'alt'  => 'Roma',
			),
			array(
				'slug' => 'uffizi',
				'url'  => 'https://red-cultural.cl/wp-content/uploads/2026/08/ChatGPT-Image-25-ago-2026-08_29_33.png',
				'alt'  => 'Toscana y Roma',
			),
			array(
				'slug' => 'roma-antigua',
				'url'  => 'https://red-cultural.cl/wp-content/uploads/2026/08/ChatGPT-Image-25-ago-2026-08_27_39.png',
				'alt'  => 'Toscana y Roma',
			),
			array(
				'slug' => 'roma-view',
				'url'  => 'https://red-cultural.cl/wp-content/uploads/2026/08/toscana_2_menos_300kb.jpg',
				'alt'  => 'Toscana',
			),
		);

		ob_start();
		?>
		<section id="red-cultural-viaje-italia-gallery" aria-label="Galería">
			<h2 id="red-cultural-viaje-italia-gallery-title">Galería</h2>
			<ul id="red-cultural-viaje-italia-gallery-grid">
				<?php foreach ($gallery_items as $index => $item) : ?>
					<?php
					$url = isset($item['url']) ? (string) $item['url'] : '';
					$slug = isset($item['slug']) ? (string) $item['slug'] : (string) $index;
					$alt = isset($item['alt']) ? (string) $item['alt'] : '';
					?>
					<li id="<?php echo esc_attr('red-cultural-viaje-italia-gallery-item-' . $slug); ?>">
						<a
							id="<?php echo esc_attr('red-cultural-viaje-italia-gallery-link-' . $slug); ?>"
							href="<?php echo esc_url($url); ?>"
							target="_blank"
							rel="noopener noreferrer"
						>
							<img
								id="<?php echo esc_attr('red-cultural-viaje-italia-gallery-img-' . $slug); ?>"
								src="<?php echo esc_url($url); ?>"
								data-fallback="<?php echo esc_url($url); ?>"
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
		<?php
		return (string) ob_get_clean();
	}
}

if (!function_exists('rcp_toscana_resolve_lectura_post_id')) {
	function rcp_toscana_resolve_lectura_post_id(string $url): int {
		$post_id = (int) url_to_postid($url);
		if ($post_id > 0) {
			return $post_id;
		}

		$path = (string) parse_url($url, PHP_URL_PATH);
		$slug = trim($path, '/');
		$slug = $slug !== '' ? basename($slug) : '';
		if ($slug === '') {
			return 0;
		}

		$candidates = get_posts(array(
			'name' => $slug,
			'post_type' => array('post', 'page'),
			'post_status' => 'publish',
			'posts_per_page' => 1,
			'fields' => 'ids',
		));
		if (!empty($candidates)) {
			return (int) $candidates[0];
		}

		$candidate = get_page_by_path($slug, OBJECT, array('post', 'page'));
		if ($candidate instanceof WP_Post) {
			return (int) $candidate->ID;
		}

		return 0;
	}
}

if (!function_exists('rcp_toscana_extract_first_image_url')) {
	function rcp_toscana_extract_first_image_url(string $content): string {
		if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches)) {
			return esc_url_raw($matches[1]);
		}

		if (preg_match('/<img[^>]+data-src=["\']([^"\']+)["\']/i', $content, $matches)) {
			return esc_url_raw($matches[1]);
		}

		return '';
	}
}

if (!function_exists('rcp_toscana_build_lectura_card_data')) {
	function rcp_toscana_build_lectura_card_data(array $item): array {
		$url = isset($item['url']) ? (string) $item['url'] : '';
		$title = isset($item['title']) ? (string) $item['title'] : '';
		$slug = isset($item['slug']) ? (string) $item['slug'] : sanitize_title($title);
		$fallback_thumb = isset($item['thumb']) ? (string) $item['thumb'] : '';
		$manual_excerpt = isset($item['excerpt']) ? (string) $item['excerpt'] : '';
		$category = isset($item['category']) ? (string) $item['category'] : 'Cultura';
		$reading_time = isset($item['reading_time']) ? (string) $item['reading_time'] : '5 min de lectura';

		$post_id = $url !== '' ? rcp_toscana_resolve_lectura_post_id($url) : 0;
		$thumbnail = '';
		$excerpt = '';

		if ($post_id > 0) {
			$thumbnail = (string) get_the_post_thumbnail_url($post_id, 'large');
			if ($thumbnail === '') {
				$thumbnail = (string) get_the_post_thumbnail_url($post_id, 'medium_large');
			}

			$content = (string) get_post_field('post_content', $post_id);
			if ($thumbnail === '') {
				$thumbnail = rcp_toscana_extract_first_image_url($content);
			}

			$excerpt = trim(wp_strip_all_tags((string) get_the_excerpt($post_id)));
			if ($excerpt === '') {
				$excerpt = trim(wp_strip_all_tags(wp_trim_words(strip_shortcodes($content), 18, '…')));
			}
		}

		if ($thumbnail === '') {
			$thumbnail = $fallback_thumb;
		}

		if ($excerpt === '') {
			$excerpt = $manual_excerpt;
		}

		$excerpt = trim((string) wp_trim_words($excerpt, 16, '…'));

		return array(
			'slug' => $slug,
			'url' => $url,
			'title' => $title,
			'thumbnail' => $thumbnail,
			'excerpt' => $excerpt,
			'category' => $category,
			'reading_time' => $reading_time,
		);
	}
}

if (!function_exists('rcp_toscana_build_lecturas_section')) {
	function rcp_toscana_build_lecturas_section(): string {
		$lecturas_items = array(
			array(
				'slug'  => 'florencia',
				'title' => 'Florencia: la ciudad que inventó una nueva manera de mirar',
				'url'   => 'https://red-cultural.cl/florencia-la-ciudad-que-invento-una-nueva-manera-de-mirar/',
				'thumb' => 'https://red-cultural.cl/wp-content/uploads/2026/08/toscana_2_menos_300kb.jpg',
				'excerpt' => 'Una lectura sobre cómo Florencia cambió la forma de ver el arte, la ciudad y la experiencia del viaje.',
				'category' => 'Arte & Arquitectura',
				'reading_time' => '4 min de lectura',
			),
			array(
				'slug'  => 'tarquinia',
				'title' => 'Tarquinia: antes de que Roma fuera Roma',
				'url'   => 'https://red-cultural.cl/tarquinia-antes-de-que-roma-fuera-roma/',
				'thumb' => 'https://red-cultural.cl/wp-content/uploads/2026/08/roma_300kb.jpg',
				'excerpt' => 'Una antesala etrusca para entender el paisaje cultural que existía antes de la Roma imperial.',
				'category' => 'Historia Antigua',
				'reading_time' => '6 min de lectura',
			),
			array(
				'slug'  => 'roma',
				'title' => 'Roma: la ciudad que nunca terminó de caer',
				'url'   => 'http://redcultural.local/roma-la-ciudad-que-nunca-termino-de-caer/',
				'thumb' => 'https://red-cultural.cl/wp-content/uploads/2026/08/roma_300kb.jpg',
				'excerpt' => 'Roma como archivo vivo: ruina, memoria y continuidad en una ciudad que siempre vuelve a empezar.',
				'category' => 'Historia & Ciudad',
				'reading_time' => '5 min de lectura',
			),
			array(
				'slug'  => 'umbria',
				'title' => 'Umbria: las ciudades que aprendieron a vivir en las alturas',
				'url'   => 'https://red-cultural.cl/umbria-las-ciudades-que-aprendieron-a-vivir-en-las-alturas/',
				'thumb' => 'https://red-cultural.cl/wp-content/uploads/2026/08/toscana_2_menos_300kb.jpg',
				'excerpt' => 'Una lectura sobre poblados en altura, paisaje y la manera en que Umbria enseña a mirar desde arriba.',
				'category' => 'Paisaje & Cultura',
				'reading_time' => '5 min de lectura',
			),
		);

		$lecturas_cards = array_map('rcp_toscana_build_lectura_card_data', $lecturas_items);

		ob_start();
		?>
		<section id="red-cultural-viaje-italia-lecturas" aria-label="Lecturas">
			<style id="red-cultural-viaje-italia-lecturas-style">
				#red-cultural-viaje-italia-lecturas{max-width:var(--wp--style--global--wide-size);margin:0 auto;padding:24px 16px 96px}
				#red-cultural-viaje-italia-lecturas-header{max-width:760px;margin:0 auto 34px;text-align:center}
				#red-cultural-viaje-italia-lecturas-kicker{display:block;margin:0 0 12px;font-size:11px;line-height:1.2;font-weight:800;letter-spacing:.34em;text-transform:uppercase;color:#9ca3af}
				#red-cultural-viaje-italia-lecturas-title{font-size:44px;line-height:1.05;font-weight:900;letter-spacing:-.03em;margin:0;color:#111827}
				#red-cultural-viaje-italia-lecturas-intro{margin:14px auto 0;max-width:620px;font-size:16px;line-height:1.7;color:#6b7280}
				#red-cultural-viaje-italia-lecturas-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:32px;list-style:none;margin:0;padding:0}
				#red-cultural-viaje-italia-lecturas-grid>li{list-style:none;margin:0;padding:0;min-width:0}
				#red-cultural-viaje-italia-lecturas-grid>li>a{display:block;height:100%;text-decoration:none;color:inherit;border:1px solid #eef0f3;border-radius:26px;overflow:hidden;background:#fff;box-shadow:0 12px 32px rgba(15,23,42,.09);transform:translateZ(0);transition:transform .25s ease,box-shadow .25s ease}
				#red-cultural-viaje-italia-lecturas-grid>li>a:hover{transform:translateY(-4px);box-shadow:0 20px 42px rgba(15,23,42,.14)}
				#red-cultural-viaje-italia-lecturas .red-cultural-viaje-italia-lecturas-card{display:flex;flex-direction:column;height:100%;margin:0;background:#fff}
				#red-cultural-viaje-italia-lecturas .red-cultural-viaje-italia-lecturas-thumb-wrap{position:relative;aspect-ratio:16/10;overflow:hidden;background:#e5e7eb}
				#red-cultural-viaje-italia-lecturas .red-cultural-viaje-italia-lecturas-thumb{display:block;width:100%;height:100%;object-fit:cover;background:#f3f4f6;transition:transform .7s ease}
				#red-cultural-viaje-italia-lecturas-grid>li>a:hover .red-cultural-viaje-italia-lecturas-thumb{transform:scale(1.035)}
				#red-cultural-viaje-italia-lecturas .red-cultural-viaje-italia-lecturas-category{position:absolute;top:18px;left:18px;margin:0;padding:8px 14px;border-radius:999px;background:rgba(255,255,255,.92);box-shadow:0 3px 12px rgba(15,23,42,.10);color:#111827;font-size:12px;line-height:1;font-weight:800}
				#red-cultural-viaje-italia-lecturas .red-cultural-viaje-italia-lecturas-body{display:flex;flex:1;flex-direction:column;padding:28px 30px 24px}
				#red-cultural-viaje-italia-lecturas .red-cultural-viaje-italia-lecturas-card-kicker{margin:0 0 12px;color:#94a3b8;font-size:12px;line-height:1.25;font-weight:700;letter-spacing:0}
				#red-cultural-viaje-italia-lecturas .red-cultural-viaje-italia-lecturas-card-kicker span+span:before{content:'•';margin:0 9px}
				#red-cultural-viaje-italia-lecturas .red-cultural-viaje-italia-lecturas-card-title{margin:0 0 14px;color:#111827;font-size:25px;line-height:1.22;font-weight:900;letter-spacing:-.025em;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
				#red-cultural-viaje-italia-lecturas .red-cultural-viaje-italia-lecturas-card-excerpt{margin:0;color:#596273;font-size:14px;line-height:1.65;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
				#red-cultural-viaje-italia-lecturas .red-cultural-viaje-italia-lecturas-footer{display:flex;align-items:center;margin-top:auto;padding-top:20px}
				#red-cultural-viaje-italia-lecturas .red-cultural-viaje-italia-lecturas-read-more{display:flex;align-items:center;gap:9px;width:100%;padding-top:17px;border-top:1px solid #edf0f4;color:#111827;font-size:13px;line-height:1.2;font-weight:800}
				#red-cultural-viaje-italia-lecturas .red-cultural-viaje-italia-lecturas-arrow{font-size:19px;line-height:1;transition:transform .2s ease}
				#red-cultural-viaje-italia-lecturas-grid>li>a:hover .red-cultural-viaje-italia-lecturas-arrow{transform:translateX(4px)}
				@media (max-width: 1100px){
					#red-cultural-viaje-italia-lecturas-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
				}
				@media (max-width: 640px){
					#red-cultural-viaje-italia-lecturas{padding:8px 16px 68px}
					#red-cultural-viaje-italia-lecturas-header{margin-bottom:24px}
					#red-cultural-viaje-italia-lecturas-title{font-size:32px}
					#red-cultural-viaje-italia-lecturas-intro{font-size:15px}
					#red-cultural-viaje-italia-lecturas-grid{grid-template-columns:1fr}
					#red-cultural-viaje-italia-lecturas .red-cultural-viaje-italia-lecturas-body{padding:22px 22px 20px}
					#red-cultural-viaje-italia-lecturas .red-cultural-viaje-italia-lecturas-card-title{font-size:21px}
				}
			</style>
			<div id="red-cultural-viaje-italia-lecturas-header">
				<span id="red-cultural-viaje-italia-lecturas-kicker">Lecturas</span>
				<h2 id="red-cultural-viaje-italia-lecturas-title">Lecturas para el viaje</h2>
				<p id="red-cultural-viaje-italia-lecturas-intro">Una selección breve para entrar en contexto antes de Toscana y Roma, con piezas que conectan paisaje, historia y mirada cultural.</p>
			</div>
			<ul id="red-cultural-viaje-italia-lecturas-grid">
				<?php foreach ($lecturas_cards as $card) : ?>
					<?php
					$slug = isset($card['slug']) ? (string) $card['slug'] : '';
					$url = isset($card['url']) ? (string) $card['url'] : '';
					$title = isset($card['title']) ? (string) $card['title'] : '';
					$thumbnail = isset($card['thumbnail']) ? (string) $card['thumbnail'] : '';
					$excerpt = isset($card['excerpt']) ? (string) $card['excerpt'] : '';
					$category = isset($card['category']) ? (string) $card['category'] : '';
					$reading_time = isset($card['reading_time']) ? (string) $card['reading_time'] : '';
					?>
					<li id="<?php echo esc_attr('red-cultural-viaje-italia-lecturas-item-' . $slug); ?>">
						<a
							id="<?php echo esc_attr('red-cultural-viaje-italia-lecturas-link-' . $slug); ?>"
							href="<?php echo esc_url($url); ?>"
							target="_blank"
							rel="noopener noreferrer"
							aria-label="<?php echo esc_attr($title); ?>"
						>
							<article id="<?php echo esc_attr('red-cultural-viaje-italia-lecturas-card-' . $slug); ?>" class="red-cultural-viaje-italia-lecturas-card">
								<div id="<?php echo esc_attr('red-cultural-viaje-italia-lecturas-thumb-wrap-' . $slug); ?>" class="red-cultural-viaje-italia-lecturas-thumb-wrap">
									<img
										id="<?php echo esc_attr('red-cultural-viaje-italia-lecturas-thumb-' . $slug); ?>"
										class="red-cultural-viaje-italia-lecturas-thumb"
										src="<?php echo esc_url($thumbnail); ?>"
										alt="<?php echo esc_attr($title); ?>"
										loading="lazy"
										referrerpolicy="no-referrer"
									>
									<span class="red-cultural-viaje-italia-lecturas-category"><?php echo esc_html($category); ?></span>
								</div>
								<div id="<?php echo esc_attr('red-cultural-viaje-italia-lecturas-body-' . $slug); ?>" class="red-cultural-viaje-italia-lecturas-body">
									<p id="<?php echo esc_attr('red-cultural-viaje-italia-lecturas-card-kicker-' . $slug); ?>" class="red-cultural-viaje-italia-lecturas-card-kicker"><span>Artículo</span><span><?php echo esc_html($reading_time); ?></span></p>
									<h3 id="<?php echo esc_attr('red-cultural-viaje-italia-lecturas-card-title-' . $slug); ?>" class="red-cultural-viaje-italia-lecturas-card-title"><?php echo esc_html($title); ?></h3>
									<p id="<?php echo esc_attr('red-cultural-viaje-italia-lecturas-card-excerpt-' . $slug); ?>" class="red-cultural-viaje-italia-lecturas-card-excerpt"><?php echo esc_html($excerpt); ?></p>
									<div class="red-cultural-viaje-italia-lecturas-footer"><span class="red-cultural-viaje-italia-lecturas-read-more">Leer artículo completo <span class="red-cultural-viaje-italia-lecturas-arrow" aria-hidden="true">→</span></span></div>
								</div>
							</article>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
		<?php
		return (string) ob_get_clean();
	}
}

if (!function_exists('rcp_toscana_build_interest_section')) {
	function rcp_toscana_build_interest_section(): string {
		$show_success = isset($_GET['rcp_toscana_roma_interest']) && (string) $_GET['rcp_toscana_roma_interest'] === 'success';
		ob_start();
		?>
		<section id="red-cultural-viaje-italia-interest" aria-label="Interés">
			<style>
				#red-cultural-viaje-italia-interest-form.rcp-interest-success .rcp-interest-field,
				#red-cultural-viaje-italia-interest-form.rcp-interest-success .rcp-interest-submit,
				#red-cultural-viaje-italia-interest-form.rcp-interest-success .rcp-interest-anti-spam,
				#red-cultural-viaje-italia-interest-form.rcp-interest-success .rcp-interest-action {
					display: none !important;
				}
			</style>
			<div id="red-cultural-viaje-italia-interest-inner">
				<div id="red-cultural-viaje-italia-interest-copy">
					<p id="red-cultural-viaje-italia-interest-question">¿Estás interesado?</p>
					<p id="red-cultural-viaje-italia-interest-desc">Llena el formulario para más información sobre el</p>
					<p id="red-cultural-viaje-italia-interest-trip-title">Toscana y Roma</p>
					<p id="red-cultural-viaje-italia-interest-trip-dates">08 al 22 de marzo de 2027</p>
				</div>

				<form
					id="red-cultural-viaje-italia-interest-form"
					class="<?php echo $show_success ? 'rcp-interest-success' : ''; ?>"
					method="post"
					action="<?php echo esc_url((string) admin_url('admin-post.php')); ?>"
				>
					<?php if ($show_success) : ?>
						<p id="red-cultural-viaje-italia-interest-success" role="status" aria-live="polite">¡Gracias! Te contactaremos pronto.</p>
					<?php endif; ?>

					<input class="rcp-interest-action" type="hidden" id="red-cultural-viaje-italia-interest-action" name="action" value="rcp_viaje_toscana_roma_interest">
					<?php wp_nonce_field('rcp_viaje_toscana_roma_interest', 'rcp_toscana_roma_nonce'); ?>
					<div class="rcp-interest-anti-spam">
						<?php RC_Anti_Spam::render_form_fields(); ?>
					</div>

					<div id="red-cultural-viaje-italia-interest-field-name" class="rcp-interest-field">
						<label id="red-cultural-viaje-italia-interest-label-name" for="red-cultural-viaje-italia-interest-input-name">Nombre</label>
						<input id="red-cultural-viaje-italia-interest-input-name" name="rcp_toscana_roma_name" type="text" autocomplete="name" placeholder="Tu nombre" required>
					</div>

					<div id="red-cultural-viaje-italia-interest-field-email" class="rcp-interest-field">
						<label id="red-cultural-viaje-italia-interest-label-email" for="red-cultural-viaje-italia-interest-input-email">Email</label>
						<input id="red-cultural-viaje-italia-interest-input-email" name="rcp_toscana_roma_email" type="email" autocomplete="email" placeholder="correo@ejemplo.com" required>
					</div>

					<div id="red-cultural-viaje-italia-interest-field-phone" class="rcp-interest-field">
						<label id="red-cultural-viaje-italia-interest-label-phone" for="red-cultural-viaje-italia-interest-input-phone">Teléfono</label>
						<input id="red-cultural-viaje-italia-interest-input-phone" name="rcp_toscana_roma_phone" type="tel" autocomplete="tel" placeholder="+56 9 1234 5678">
					</div>

					<div id="red-cultural-viaje-italia-interest-field-message" class="rcp-interest-field">
						<label id="red-cultural-viaje-italia-interest-label-message" for="red-cultural-viaje-italia-interest-input-message">Mensaje</label>
						<textarea id="red-cultural-viaje-italia-interest-input-message" name="rcp_toscana_roma_message" placeholder="Cuéntanos qué necesitas..."></textarea>
					</div>

					<button id="red-cultural-viaje-italia-interest-submit" class="rcp-interest-submit" type="submit">Enviar</button>
				</form>
				<?php RC_Anti_Spam::render_form_js('red-cultural-viaje-italia-interest-form'); ?>
			</div>
			<script>
				(function () {
					var params = new URLSearchParams(window.location.search);
					if (params.get('rcp_toscana_roma_interest') === 'success') {
						params.delete('rcp_toscana_roma_interest');
						var nextUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '') + window.location.hash;
						window.history.replaceState({}, document.title, nextUrl);
					}
				})();
			</script>
		</section>
		<?php
		return (string) ob_get_clean();
	}
}

if (!function_exists('rcp_toscana_build_conditions_section')) {
	function rcp_toscana_build_conditions_section(): string {
		ob_start();
		?>
		<section id="red-cultural-viaje-italia-conditions" aria-label="Condiciones">
			<div id="red-cultural-viaje-italia-conditions-inner">
				<h2 id="red-cultural-viaje-italia-conditions-title">Políticas de reserva y cancelación:</h2>

				<div id="red-cultural-viaje-italia-conditions-grid">
					<div id="red-cultural-viaje-italia-conditions-col-left" class="rcp-cond-block">
						<div id="red-cultural-viaje-italia-conditions-baggage">
							<table id="red-cultural-viaje-italia-conditions-payment-table" style="width:100%;border-collapse:collapse;border:1px solid #bbb;background:#fff;font-size:14px;line-height:1.45">
								<thead>
									<tr>
										<th style="border:1px solid #bbb;padding:12px 10px;text-align:center;font-weight:800">Etapa de pago</th>
										<th style="border:1px solid #bbb;padding:12px 10px;text-align:center;font-weight:800">Monto</th>
										<th style="border:1px solid #bbb;padding:12px 10px;text-align:center;font-weight:800">Fecha límite</th>
										<th style="border:1px solid #bbb;padding:12px 10px;text-align:center;font-weight:800">Observación</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td style="border:1px solid #bbb;padding:12px 10px;text-align:center">Primer abono</td>
										<td style="border:1px solid #bbb;padding:12px 10px;text-align:center">USD 3.500<br>por persona</td>
										<td style="border:1px solid #bbb;padding:12px 10px;text-align:center">Al momento de solicitar la reserva</td>
										<td style="border:1px solid #bbb;padding:12px 10px;text-align:center">No reembolsable</td>
									</tr>
									<tr>
										<td style="border:1px solid #bbb;padding:12px 10px;text-align:center">Segundo abono</td>
										<td style="border:1px solid #bbb;padding:12px 10px;text-align:center">USD 3.155<br>por persona</td>
										<td style="border:1px solid #bbb;padding:12px 10px;text-align:center">Hasta 04-Septiembre-2026</td>
										<td style="border:1px solid #bbb;padding:12px 10px;text-align:center">No reembolsable</td>
									</tr>
									<tr>
										<td style="border:1px solid #bbb;padding:12px 10px;text-align:center">Saldo final</td>
										<td style="border:1px solid #bbb;padding:12px 10px;text-align:center">Según tipo de tarifa preventa o normal y tipo de habitación</td>
										<td style="border:1px solid #bbb;padding:12px 10px;text-align:center">Hasta 30-Octubre-2026</td>
										<td style="border:1px solid #bbb;padding:12px 10px;text-align:center">No reembolsable</td>
									</tr>
								</tbody>
							</table>
						</div>

						<div id="red-cultural-viaje-italia-conditions-cancel" style="margin-top:22px">
							<ul id="red-cultural-viaje-italia-conditions-cancel-list" style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:10px">
								<li id="red-cultural-viaje-italia-conditions-cancel-1">· Se requiere un mínimo de 20 pasajeros confirmados de lo contrario el viaje se cancela.</li>
								<li id="red-cultural-viaje-italia-conditions-cancel-2">· Pasajes aéreos: se puede cotizar upgrade de cabina sujeto a disponibilidad.</li>
								<li id="red-cultural-viaje-italia-conditions-cancel-3">· Asistencia en viaje aplica tarifa extra para mayores de 70 años.</li>
							</ul>
						</div>
					</div>

					<div id="red-cultural-viaje-italia-conditions-col-right" class="rcp-cond-block">
						<div id="red-cultural-viaje-italia-conditions-docs">
							<h3 id="red-cultural-viaje-italia-conditions-docs-title">Documentación:</h3>
							<ul id="red-cultural-viaje-italia-conditions-docs-list">
								<li id="red-cultural-viaje-italia-conditions-docs-1">· Es responsabilidad de cada pasajero ir provisto de un pasaporte vigente y dotado de todos los visados y requisitos necesarios.</li>
							</ul>
						</div>

						<div id="red-cultural-viaje-italia-conditions-variations">
							<h3 id="red-cultural-viaje-italia-conditions-variations-title">Variaciones:</h3>
							<ul id="red-cultural-viaje-italia-conditions-variations-list">
								<li id="red-cultural-viaje-italia-conditions-variations-1">· La información de hoteles mencionados, tarifas, itinerario, horarios de llegada y salida, fechas de operación, etc., está sujeta a posibles modificaciones.</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</section>
		<?php
		return (string) ob_get_clean();
	}
}

$template_file = RC_CORE_PATH . 'templates/pages/viaje-italia.php';
if (!file_exists($template_file)) {
	return;
}

ob_start();
require $template_file;
$html = (string) ob_get_clean();

$html = str_replace(
	array(
		'Nápoles, Sicilia y Malta',
		'16-marzo al 01-abril de 2026',
		'Disfruta una experiencia única',
		'Inscripciones en:',
		'USD 10.249',
		'USD 12.269',
		'Asistencia en viaje Universal Assistance plan Value.',
		'Nápoles, Sicilia y Malta',
		'16-marzo al 01-abril de 2026',
		'16-marzo-2025 vuelo Iberia 118 Santiago / Madrid',
		'17-marzo-2025 vuelo Iberia 979 Madrid / Nápoles',
		'31-marzo-2025 vuelo Iberia 117 Madrid / Santiago',
		'20-marzo-2026 vuelo EasyJet 4103 Nápoles / Palermo',
		'27-marzo-2026 vuelo Ryanair 368 Catania / Malta',
		'30-marzo-2026 vuelo Malta Airlines 368 Malta / Madrid',
		'· Se requiere un mínimo de 20 pasajeros confirmados al 28-NOVIEMBRE-2025, de lo contrario el viaje se cancela.',
		'· Para reservar se solicita un abono de USD 3.000 por persona no reembolsable.',
		'· La totalidad del viaje debe estar pagada hasta el 31- DICIEMBRE-2025.',
		'· Cancelaciones luego del 31-DICIEMBRE-2025 se retendrá el 100 % de lo pagado.',
		'· Pasajes aéreos: se puede cotizar upgrade de cabina sujeto a disponibilidad.',
		'· Asistencia en viaje aplica tarifa extra para mayores de 70 años.',
		'· La información de hoteles mencionados, tarifas, itinerario, horarios',
		'© 2026 Red Cultural &amp; COCHA. Todos los derechos reservados.',
	),
	array(
		'Toscana y Roma',
		'08 al 22 de marzo de 2027',
		'¡Disfruta de una experiencia única!',
		'Inscripciones:',
		'USD 11.769',
		'USD 15.089',
		'Asistencia en viaje Assist Card plan Esencial.',
		'Toscana y Roma',
		'08 al 22 de marzo de 2027',
		'08-marzo-2027 vuelo Iberia 118 Santiago / Madrid',
		'09-marzo-2027 vuelo IB 647 Roma / Florencia',
		'22-marzo-2027 vuelo Iberia 654 Madrid / Santiago',
		'',
		'',
		'',
		'· Se requiere un mínimo de 20 pasajeros confirmados de lo contrario el viaje se cancela.',
		'· Pasajes aéreos: se puede cotizar upgrade de cabina sujeto a disponibilidad.',
		'· Asistencia en viaje aplica tarifa extra para mayores de 70 años.',
		'',
		'',
		'',
		'',
		'· La información de hoteles mencionados, tarifas, itinerario, horarios de llegada y salida, fechas de operación, etc., está sujeta a posibles modificaciones.',
		'© 2027 Red Cultural &amp; COCHA. Todos los derechos reservados.',
	),
	$html
);

$html = str_replace(
	'</style>',
	"#red-cultural-viaje-italia-hero.banner-bg{background-image:linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.2)),url('" . esc_url('https://red-cultural.cl/wp-content/uploads/2026/08/toscanaroma.png') . "') !important;background-size:cover !important;background-position:center !important;}\n#red-cultural-viaje-italia-interest{background-image:linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)),url('" . esc_url('https://red-cultural.cl/wp-content/uploads/2026/08/toscanaroma.png') . "') !important;background-size:cover !important;background-position:center !important;}\n</style>",
	$html
);

$html = rcp_toscana_replace_section($html, 'red-cultural-viaje-italia-gallery', rcp_toscana_build_gallery_section() . rcp_toscana_build_lecturas_section());
$html = rcp_toscana_replace_section($html, 'red-cultural-viaje-italia-interest', rcp_toscana_build_interest_section());
$html = rcp_toscana_replace_section($html, 'red-cultural-viaje-italia-itinerary', rcp_toscana_build_itinerary_section());
$html = rcp_toscana_replace_section($html, 'red-cultural-viaje-italia-conditions', rcp_toscana_build_conditions_section());

echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
