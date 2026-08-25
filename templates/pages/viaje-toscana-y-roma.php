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

$html = rcp_toscana_replace_section($html, 'red-cultural-viaje-italia-gallery', rcp_toscana_build_gallery_section());
$html = rcp_toscana_replace_section($html, 'red-cultural-viaje-italia-interest', rcp_toscana_build_interest_section());
$html = rcp_toscana_replace_section($html, 'red-cultural-viaje-italia-itinerary', rcp_toscana_build_itinerary_section());
$html = rcp_toscana_replace_section($html, 'red-cultural-viaje-italia-conditions', rcp_toscana_build_conditions_section());

echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
