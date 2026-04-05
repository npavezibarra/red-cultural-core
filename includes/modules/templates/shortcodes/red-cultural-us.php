<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('rcp_red_cultural_us_shortcode')) {
	/**
	 * Shortcode: [red-cultural-us]
	 */
	function rcp_red_cultural_us_shortcode($atts = array()): string {
		$atts = shortcode_atts(
			array(
				'id' => 'red-cultural-nosotros',
			),
			(array) $atts,
			'red-cultural-us'
		);

		$raw_root_id = isset($atts['id']) ? (string) $atts['id'] : 'red-cultural-nosotros';
		$raw_root_id = trim($raw_root_id);
		if ($raw_root_id === '') {
			$raw_root_id = 'red-cultural-nosotros';
		}
		$root_id = sanitize_title($raw_root_id);
		if ($root_id === '') {
			$root_id = 'red-cultural-nosotros';
		}

		$uploads = wp_get_upload_dir();
		$baseurl = isset($uploads['baseurl']) ? (string) $uploads['baseurl'] : (string) content_url('/uploads');

		$members = array(
			array(
				'name' => 'Magdalena Merbihláa',
				'role' => 'Directora Ejecutiva',
				'login' => 'magdalena.merbilhaa',
				'rel' => '2021/01/magdalena.jpg',
				'fallback' => 'https://red-cultural.cl/wp-content/uploads/2021/01/magdalena.jpg',
			),
			array(
				'name' => 'Bárbara Bustamante',
				'role' => 'Directora Académica',
				'login' => 'BarbaraBustamante',
				'rel' => '2021/01/barbara.jpg',
				'fallback' => 'https://red-cultural.cl/wp-content/uploads/2021/01/barbara.jpg',
			),
			array(
				'name' => 'Guillermo González',
				'role' => 'Director Contenidos',
				'login' => 'guillermo.gonzalez',
				'rel' => '2022/05/23b99eb8-4770-486c-b0cf-2751da339dc2-1-e1652661760158.jpg',
				'fallback' => 'https://red-cultural.cl/wp-content/uploads/2022/05/23b99eb8-4770-486c-b0cf-2751da339dc2-1-e1652661760158.jpg',
			),
			array(
				'name' => 'Viviana Ávila',
				'role' => 'Secretaria Académica',
				'rel' => '2021/01/viviana-avila.jpg',
				'fallback' => 'https://red-cultural.cl/wp-content/uploads/2021/01/viviana-avila.jpg',
			),
		);

		foreach ($members as &$m) {
			$rel = isset($m['rel']) ? ltrim((string) $m['rel'], '/') : '';
			$m['img'] = $rel !== '' ? trailingslashit($baseurl) . $rel : '';
		}
		unset($m);

		$teachers = array(
			array(
				'name' => 'Isabel Eluchans',
				'role' => 'Historiadora - Historia de Chile',
				'login' => 'isabeleluchans',
				'rel' => '2021/01/isabeleluchans.jpg',
				'fallback' => 'https://red-cultural.cl/wp-content/uploads/2021/01/isabeleluchans.jpg',
			),
			array(
				'name' => 'Rosita Larraín',
				'role' => 'Historiadora - Historia Internacional',
				'login' => 'rosita.larrain',
				'rel' => '2021/01/rositalarrain.jpg',
				'fallback' => 'https://red-cultural.cl/wp-content/uploads/2021/01/rositalarrain.jpg',
			),
			array(
				'name' => 'Pilar Ducci',
				'role' => 'Historiadora - Historia de la Ciencia',
				'login' => 'pilar.ducci',
				'rel' => '2021/01/pilarducci.jpg',
				'fallback' => 'https://red-cultural.cl/wp-content/uploads/2021/01/pilarducci.jpg',
			),
			array(
				'name' => 'Gonzalo Larios',
				'role' => 'Historiador - Historia de la Cultura',
				'login' => 'gonzalo.larios',
				'rel' => '2021/01/gonzalolarios-1.jpg',
				'fallback' => 'https://red-cultural.cl/wp-content/uploads/2021/01/gonzalolarios-1.jpg',
			),
			array(
				'name' => 'Cristián Leon',
				'role' => 'Arquitecto y Doctor en Historia del Arte',
				'login' => 'cristian.leon',
				'rel' => '2021/01/cristianleon.jpg',
				'fallback' => 'https://red-cultural.cl/wp-content/uploads/2021/01/cristianleon.jpg',
			),
			array(
				'name' => 'Klaus Droste',
				'role' => 'Psicólogo y Doctor en Filosofía',
				'login' => 'klausdroste',
				'rel' => '2021/01/klausdroste-200x200-1.jpg',
				'fallback' => 'https://red-cultural.cl/wp-content/uploads/2021/01/klausdroste-200x200-1.jpg',
			),
			array(
				'name' => 'Ángel Soto',
				'role' => 'Historiador - Historia Económica',
				'login' => 'angelsoto',
				'rel' => '2021/01/angelsoto-1.jpg',
				'fallback' => 'https://red-cultural.cl/wp-content/uploads/2021/01/angelsoto-1.jpg',
			),
			array(
				'name' => 'Patricio Carvajal',
				'role' => 'Doctor en Derecho - Derecho Romano',
				'login' => 'patricio.carvajal',
				'rel' => '2021/03/patriciocarvajal.jpg',
				'fallback' => 'https://red-cultural.cl/wp-content/uploads/2021/03/patriciocarvajal.jpg',
			),
			array(
				'name' => 'Sebastián Salinas',
				'role' => 'Historiador - Estudios Árabes',
				'login' => 'sebastiansalinas',
				'rel' => '2021/01/sebastiansalinas.jpg',
				'fallback' => 'https://red-cultural.cl/wp-content/uploads/2021/01/sebastiansalinas.jpg',
			),
			array(
				'name' => 'Armando Roa',
				'role' => 'Abogado, Poeta y Experto en Literatura',
				'login' => 'armando.roa',
				'rel' => '2021/01/armandoroa-1.jpg',
				'fallback' => 'https://red-cultural.cl/wp-content/uploads/2021/01/armandoroa-1.jpg',
			),
			array(
				'name' => 'Magdalena Dittborn',
				'role' => 'Historiadora - Historia de las Mujeres',
				'login' => 'magdalena.dittborn',
				'rel' => '2021/03/magdalenadittborn.jpg',
				'fallback' => 'https://red-cultural.cl/wp-content/uploads/2021/03/magdalenadittborn.jpg',
			),
			array(
				'name' => 'Rafael Mellafe',
				'role' => 'Historiador - Historia de la Guerra',
				'login' => 'rafael.mellafe',
				'rel' => '2022/04/mellafe.jpg',
				'fallback' => 'https://red-cultural.cl/wp-content/uploads/2022/04/mellafe.jpg',
			),
			array(
				'name' => 'Sergio Vergara',
				'role' => 'Historiador - Historia Antigua',
				'login' => 'sergio.vergara',
				'rel' => '2021/11/sergiovergara.jpg',
				'fallback' => 'https://red-cultural.cl/wp-content/uploads/2021/11/sergiovergara.jpg',
			),
			array(
				'name' => 'María Paz Díaz',
				'role' => 'Doctora en Teología',
				'login' => 'mariapaz.diaz',
				'rel' => '2022/04/mariapazdiaz.jpeg',
				'fallback' => 'https://red-cultural.cl/wp-content/uploads/2022/04/mariapazdiaz.jpeg',
			),
			array(
				'name' => 'José Blanco',
				'role' => 'Historiador - Lengua y Cultura Italiana',
				'login' => 'jose.blanco',
				'rel' => '2022/04/joseblancofoto.jpeg',
				'fallback' => 'https://red-cultural.cl/wp-content/uploads/2022/04/joseblancofoto.jpeg',
			),
			array(
				'name' => 'Felipe Munizaga',
				'role' => 'Politólogo - Pensamiento Político',
				'login' => 'felipe.munizaga',
				'rel' => '2022/04/felipemunizaga.jpg',
				'fallback' => 'https://red-cultural.cl/wp-content/uploads/2022/04/felipemunizaga.jpg',
			),
			array(
				'name' => 'Joseph Pearce',
				'role' => 'Experto en Literatura Inglesa',
				'login' => 'joseph.pearce',
				'rel' => '2022/04/josephpearce.jpg',
				'fallback' => 'https://red-cultural.cl/wp-content/uploads/2022/04/josephpearce.jpg',
			),
			array(
				'name' => 'Francisca Willson',
				'role' => 'Historiadora - Medio Oriente',
				'login' => 'FranciscaWillson',
				'rel' => '2021/01/franciscawillson.jpg',
				'fallback' => 'https://red-cultural.cl/wp-content/uploads/2021/01/franciscawillson.jpg',
			),
		);

		foreach ($teachers as &$t) {
			$rel = isset($t['rel']) ? ltrim((string) $t['rel'], '/') : '';
			$t['img'] = $rel !== '' ? trailingslashit($baseurl) . $rel : '';
		}
		unset($t);

		ob_start();
		?>
		<style>
			#<?php echo esc_attr($root_id); ?>{max-width:1180px !important;margin:0 auto;padding:60px 16px 72px;color:#111827}
			.rcp-us-full-width{background-color:#f9f9f9 !important;width:100%}
			#<?php echo esc_attr($root_id); ?> h2{font-size:40px;line-height:1.05;font-weight:900;letter-spacing:-.02em;margin:0;text-align:center}
			#<?php echo esc_attr($root_id); ?> p{margin:0}
			#<?php echo esc_attr($root_id); ?> .rcp-us-sub{margin-top:14px;max-width:680px;color:#6b7280;font-size:18px;line-height:1.45}
			#<?php echo esc_attr($root_id); ?> .rcp-us-grid{list-style:none;margin:34px 0 0;padding:0;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:20px}
			#<?php echo esc_attr($root_id); ?> .rcp-us-grid--docente{margin-top:28px}
			#<?php echo esc_attr($root_id); ?> a.rcp-us-card{display:flex;flex-direction:column;align-items:center;gap:0;background:#fff;border:none;border-radius:16px;padding:26px 22px;text-align:center;transition:box-shadow .2s ease,transform .2s ease;text-decoration:none;color:inherit}
			#<?php echo esc_attr($root_id); ?> a.rcp-us-card:hover{box-shadow:0 12px 30px rgba(0,0,0,.08);transform:translateY(-2px)}
			#<?php echo esc_attr($root_id); ?> .rcp-us-card-static{display:flex;flex-direction:column;align-items:center;gap:0;background:#fff;border:1px solid rgba(17,24,39,.08);border-radius:16px;padding:26px 22px;text-align:center}
			#<?php echo esc_attr($root_id); ?> .rcp-us-photo{width:92px;height:92px;border-radius:999px;overflow:hidden;display:block;margin:0 0 16px;background:#f3f4f6}
			#<?php echo esc_attr($root_id); ?> .rcp-us-photo img{width:100%;height:100%;object-fit:cover;display:block}
			#<?php echo esc_attr($root_id); ?> .rcp-us-name{font-size:18px;line-height:1.2;font-weight:900;color:#111827;margin:0}
			#<?php echo esc_attr($root_id); ?> .rcp-us-role{margin-top:6px;font-size:12px;letter-spacing:.18em;text-transform:uppercase;color:#c5a367;font-weight:800}
			#<?php echo esc_attr($root_id); ?> .rcp-us-divider{border:0;height:1px;background:rgba(17,24,39,.10);margin:56px auto 44px;max-width:760px}
			#<?php echo esc_attr($root_id); ?> .rcp-us-h3{font-size:40px;line-height:1.05;font-weight:900;letter-spacing:-.02em;margin:0;text-align:center}
			#<?php echo esc_attr($root_id); ?> .rcp-us-accent{width:92px;height:4px;background:#c5a367;border-radius:999px;margin:14px auto 0}
			#<?php echo esc_attr($root_id); ?> .rcp-us-muted{margin-top:18px;text-align:center;color:#9ca3af;font-style:italic}

			@media (max-width: 1100px){
				#<?php echo esc_attr($root_id); ?> .rcp-us-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
			}
			@media (max-width: 640px){
				#<?php echo esc_attr($root_id); ?>{padding-top:18px}
				#<?php echo esc_attr($root_id); ?> h2{font-size:34px}
				#<?php echo esc_attr($root_id); ?> .rcp-us-grid{grid-template-columns:1fr}
			}
		</style>
		<div class="rcp-us-full-width">
			<section id="<?php echo esc_attr($root_id); ?>">
				<h2 id="<?php echo esc_attr($root_id . '-title'); ?>">Equipo Administrativo</h2>
				<div id="<?php echo esc_attr($root_id . '-admin-accent'); ?>" class="rcp-us-accent"></div>

				<ul id="<?php echo esc_attr($root_id . '-grid'); ?>" class="rcp-us-grid">
					<?php foreach ($members as $member) : ?>
						<?php
						$name = isset($member['name']) ? (string) $member['name'] : '';
						$role = isset($member['role']) ? (string) $member['role'] : '';
						$img = isset($member['img']) ? (string) $member['img'] : '';
						$fallback = isset($member['fallback']) ? (string) $member['fallback'] : '';
						$login = isset($member['login']) ? (string) $member['login'] : '';
						$href = $login !== '' ? (string) home_url('/author/' . $login . '/') : '';
						$member_id = $root_id . '-member-' . sanitize_title($name !== '' ? $name : uniqid('member-'));
						?>
						<li>
							<?php if ($href !== '') : ?>
								<a id="<?php echo esc_attr($member_id); ?>" class="rcp-us-card" href="<?php echo esc_url($href); ?>" target="_blank" rel="noopener noreferrer">
									<span id="<?php echo esc_attr($member_id . '-photo'); ?>" class="rcp-us-photo">
										<img
											id="<?php echo esc_attr($member_id . '-img'); ?>"
											src="<?php echo esc_url($img !== '' ? $img : $fallback); ?>"
											alt="<?php echo esc_attr($name); ?>"
											loading="lazy"
											referrerpolicy="no-referrer"
											data-fallback="<?php echo esc_url($fallback); ?>"
											onerror="if(this.dataset.fallback&&this.src!==this.dataset.fallback){this.src=this.dataset.fallback;}"
										/>
									</span>
									<span id="<?php echo esc_attr($member_id . '-name'); ?>" class="rcp-us-name"><?php echo esc_html($name); ?></span>
									<span id="<?php echo esc_attr($member_id . '-role'); ?>" class="rcp-us-role"><?php echo esc_html($role); ?></span>
								</a>
							<?php else : ?>
								<div id="<?php echo esc_attr($member_id); ?>" class="rcp-us-card-static">
									<span id="<?php echo esc_attr($member_id . '-photo'); ?>" class="rcp-us-photo" aria-hidden="true"></span>
									<span id="<?php echo esc_attr($member_id . '-name'); ?>" class="rcp-us-name"><?php echo esc_html($name); ?></span>
									<span id="<?php echo esc_attr($member_id . '-role'); ?>" class="rcp-us-role"><?php echo esc_html($role); ?></span>
								</div>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>

				<hr id="<?php echo esc_attr($root_id . '-divider'); ?>" class="rcp-us-divider" />

				<h3 id="<?php echo esc_attr($root_id . '-docente-title'); ?>" class="rcp-us-h3">Equipo Docente</h3>
				<div id="<?php echo esc_attr($root_id . '-docente-accent'); ?>" class="rcp-us-accent"></div>
				<ul id="<?php echo esc_attr($root_id . '-docente-grid'); ?>" class="rcp-us-grid rcp-us-grid--docente">
					<?php foreach ($teachers as $teacher) : ?>
						<?php
						$name = isset($teacher['name']) ? (string) $teacher['name'] : '';
						$role = isset($teacher['role']) ? (string) $teacher['role'] : '';
						$img = isset($teacher['img']) ? (string) $teacher['img'] : '';
						$fallback = isset($teacher['fallback']) ? (string) $teacher['fallback'] : '';
						$login = isset($teacher['login']) ? (string) $teacher['login'] : '';
						$href = $login !== '' ? (string) home_url('/author/' . $login . '/') : '';
						$teacher_id = $root_id . '-docente-' . sanitize_title($name !== '' ? $name : uniqid('docente-'));
						?>
						<li>
							<?php if ($href !== '') : ?>
								<a id="<?php echo esc_attr($teacher_id); ?>" class="rcp-us-card" href="<?php echo esc_url($href); ?>" target="_blank" rel="noopener noreferrer">
									<span id="<?php echo esc_attr($teacher_id . '-photo'); ?>" class="rcp-us-photo">
										<img
											id="<?php echo esc_attr($teacher_id . '-img'); ?>"
											src="<?php echo esc_url($img !== '' ? $img : $fallback); ?>"
											alt="<?php echo esc_attr($name); ?>"
											loading="lazy"
											referrerpolicy="no-referrer"
											data-fallback="<?php echo esc_url($fallback); ?>"
											onerror="if(this.dataset.fallback&&this.src!==this.dataset.fallback){this.src=this.dataset.fallback;}"
										/>
									</span>
									<span id="<?php echo esc_attr($teacher_id . '-name'); ?>" class="rcp-us-name"><?php echo esc_html($name); ?></span>
									<span id="<?php echo esc_attr($teacher_id . '-role'); ?>" class="rcp-us-role"><?php echo esc_html($role); ?></span>
								</a>
							<?php else : ?>
								<div id="<?php echo esc_attr($teacher_id); ?>" class="rcp-us-card-static">
									<span id="<?php echo esc_attr($teacher_id . '-photo'); ?>" class="rcp-us-photo" aria-hidden="true"></span>
									<span id="<?php echo esc_attr($teacher_id . '-name'); ?>" class="rcp-us-name"><?php echo esc_html($name); ?></span>
									<span id="<?php echo esc_attr($teacher_id . '-role'); ?>" class="rcp-us-role"><?php echo esc_html($role); ?></span>
								</div>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		</div>
		<?php

		return (string) ob_get_clean();
	}
}
