<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

final class RC_Course_Importer {

	public static function init(): void {
		add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
		add_action('admin_post_rc_import_course_verify', array(__CLASS__, 'handle_verify'));
		add_action('admin_post_rc_import_course_process', array(__CLASS__, 'handle_process'));
	}

	public static function add_admin_menu(): void {
		add_submenu_page(
			'red-cultural-pages',
			'Importar Curso JSON',
			'Importar Curso',
			'manage_options',
			'rc-course-importer',
			array(__CLASS__, 'render_page')
		);
	}

	public static function render_page(): void {
		if (!current_user_can('manage_options')) {
			wp_die('No tienes permisos.');
		}

		$message = get_transient('rc_import_course_msg');
		if ($message) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
			delete_transient('rc_import_course_msg');
		}

		$error = get_transient('rc_import_course_err');
		if ($error) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($error) . '</p></div>';
			delete_transient('rc_import_course_err');
		}

		$step = $_GET['step'] ?? '1';
		if ($step === '2') {
			self::render_step_2();
		} else {
			self::render_step_1();
		}
	}

	public static function render_step_1(): void {
		?>
		<div class="wrap">
			<h1>Importar Curso Rápido (JSON)</h1>
			<p>Pega el JSON generado por IA para crear el curso, sus lecciones y su producto de WooCommerce de forma automática.</p>
			
			<div style="background: #fff; border: 1px solid #ccd0d4; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
				<h2 style="margin-top: 0;">1. Generar JSON con IA</h2>
				<p>Copia las siguientes instrucciones y envíaselas a un chat de IA (como ChatGPT o Claude) adjuntando la imagen del afiche del curso.</p>
				<button type="button" class="button button-secondary" id="rc-copy-prompt-btn">Copiar Instrucciones para IA</button>
				<span id="rc-copy-msg" style="color: green; display: none; margin-left: 10px;">¡Copiado al portapapeles!</span>
			</div>

			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
				<?php wp_nonce_field('rc_import_course_nonce', 'rc_import_course_nonce'); ?>
				<input type="hidden" name="action" value="rc_import_course_verify">
				
				<h2 style="margin-top: 0;">2. Importar al Sistema</h2>
				<table class="form-table">
					<tr>
						<th scope="row"><label for="course_json">Estructura JSON</label></th>
						<td>
							<textarea name="course_json" id="course_json" rows="20" class="large-text code" placeholder='{"curso": {...}, "lecciones": [...]}'></textarea>
						</td>
					</tr>
				</table>
				
				<?php submit_button('Verificar Datos'); ?>
			</form>
		</div>

		<script>
			document.getElementById('rc-copy-prompt-btn').addEventListener('click', function() {
				var promptText = `Actúa como un experto en extracción de datos y asistente de creación de cursos. 

PASO 1: Analiza la imagen del afiche de curso que te proporciono y genera única y exclusivamente un bloque de código JSON con la información extraída, respetando el siguiente esquema estricto:

{
  "curso": {
    "titulo": "Extrae el título principal",
    "descripcion": "Escribe máximo 1 o 2 párrafos redactados que hablen sobre los contenidos del curso, infiriéndolos del título, las lecciones y otra información de la imagen.",
    "extracto": "Escribe la información comercial con esta estructura exacta, llenando los datos: 'Curso de X sesiones por un valor total de $Y, o $Z por sesión individual. Para informaciones e inscripciones, contactar a EMAIL. Los pagos deben realizarse a nombre de NOMBRE, RUT X, en la cuenta corriente número X del BANCO X.'",
    "imagen_url": "", 
    "modalidad": "closed", 
    "precio_curso_completo": 0, 
    "precio_por_sesion": 0, 
    "profesor_nombre": "Extrae el nombre del profesor",
    "youtube_url": ""
  },
  "lecciones": [
    {
      "titulo": "Extrae el título de la lección",
      "descripcion": "Extrae la bajada o descripción si la tiene",
      "fecha": "YYYY-MM-DD HH:MM:SS" 
    }
  ]
}

Reglas importantes para el JSON:
1. Los precios deben ser números enteros (ejemplo: si dice $80.000, debes poner 80000).
2. Las fechas deben estar estrictamente en formato YYYY-MM-DD HH:MM:SS. Para el año asume el año que indica el afiche (por ejemplo 2026). La hora de cada lección debe coincidir con el horario del curso.
3. Deja imagen_url y youtube_url vacíos.
4. Tu respuesta debe contener sólo el bloque JSON en formato de código.

PASO 2: Generación de imágenes.
Después de entregar el JSON, debes preguntarme obligatoriamente si quiero que generes una imagen "Featured" o "Background". NO generes ninguna imagen hasta que yo responda con uno de esos dos comandos.

- Si respondo "Featured": Genera la imagen principal representativa del curso (composición estilo foto grupal o de personajes). Debe tener las siguientes características: Tipo de archivo: image/jpeg, Tamaño de archivo cercano a: 81 KB, Dimensiones: 500 por 380 píxeles.
- Si respondo "Background": Genera una imagen sobre un paisaje representativo del tema que se habla en el curso.`;

				function showSuccess() {
					var msg = document.getElementById('rc-copy-msg');
					msg.style.display = 'inline';
					setTimeout(function() {
						msg.style.display = 'none';
					}, 3000);
				}

				if (navigator.clipboard && window.isSecureContext) {
					navigator.clipboard.writeText(promptText).then(showSuccess).catch(function(err) {
						alert('Error al copiar: ' + err);
					});
				} else {
					var textArea = document.createElement("textarea");
					textArea.value = promptText;
					textArea.style.position = "fixed";
					document.body.appendChild(textArea);
					textArea.focus();
					textArea.select();
					try {
						var successful = document.execCommand('copy');
						if (successful) showSuccess();
					} catch (err) {}
					document.body.removeChild(textArea);
				}
			});
		</script>
		<?php
	}

	public static function handle_verify(): void {
		if (!current_user_can('manage_options')) wp_die('No tienes permisos.');
		check_admin_referer('rc_import_course_nonce', 'rc_import_course_nonce');

		$json_str = stripslashes($_POST['course_json'] ?? '');
		$data = json_decode($json_str, true);

		if (json_last_error() !== JSON_ERROR_NONE || empty($data['curso'])) {
			set_transient('rc_import_course_err', 'JSON inválido o mal estructurado.', 30);
			wp_redirect(admin_url('admin.php?page=rc-course-importer'));
			exit;
		}

		set_transient('rc_course_import_json_' . get_current_user_id(), $json_str, 3600);
		wp_redirect(admin_url('admin.php?page=rc-course-importer&step=2'));
		exit;
	}

	public static function render_step_2(): void {
		$json_str = get_transient('rc_course_import_json_' . get_current_user_id());
		if (!$json_str) {
			echo '<div class="wrap"><h2>Error</h2><p>No hay datos pendientes o la sesión expiró. <a href="?page=rc-course-importer">Volver al paso 1</a></p></div>';
			return;
		}

		$data = json_decode($json_str, true);
		$prof_name = trim($data['curso']['profesor_nombre'] ?? '');

		if (empty($prof_name)) {
			$prof_name = 'Sin Profesor';
		}

		$users = get_users();
		$matches = array();
		$prof_name_low = strtolower(remove_accents($prof_name));

		foreach ($users as $u) {
			$n1 = strtolower(remove_accents($u->display_name));
			$n2 = strtolower(remove_accents(trim($u->first_name . ' ' . $u->last_name)));
			$d1 = levenshtein($prof_name_low, $n1);
			$d2 = levenshtein($prof_name_low, $n2);
			$best = min($d1, $d2);
			$matches[] = array('user' => $u, 'dist' => $best);
		}

		usort($matches, function($a, $b) {
			return $a['dist'] <=> $b['dist'];
		});

		$top_matches = array_slice($matches, 0, 3);
		$exact_match = ($top_matches[0]['dist'] === 0);

		$parts = explode(' ', $prof_name);
		$first = array_shift($parts);
		$last = implode(' ', $parts);
		if (empty($last)) $last = 'Profesor';
		$clean_first = strtolower(remove_accents($first));
		$clean_last = strtolower(remove_accents(str_replace(' ', '', $last)));
		$preview_email = $clean_first . '.' . $clean_last . '@redcultural.cl';

		?>
		<div class="wrap">
			<h1>Paso 2: Confirmar Profesor</h1>
			<p>El sistema detectó que el profesor asignado para este curso es: <strong><?php echo esc_html($prof_name); ?></strong></p>
			
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
				<?php wp_nonce_field('rc_import_course_nonce', 'rc_import_course_nonce'); ?>
				<input type="hidden" name="action" value="rc_import_course_process">

				<div style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 4px; max-width: 650px;">
					<h3>Por favor, confirma quién es el autor/profesor del curso:</h3>
					
					<?php foreach ($top_matches as $index => $match) : 
						$u = $match['user'];
						$u_name = trim($u->first_name . ' ' . $u->last_name);
						if (empty($u_name)) $u_name = $u->display_name;
						$checked = ($exact_match && $index === 0) ? 'checked' : '';
					?>
						<label style="display: block; margin-bottom: 15px; font-size: 14px;">
							<input type="radio" name="author_choice" value="<?php echo esc_attr($u->ID); ?>" <?php echo $checked; ?>>
							Vincular a existente: <strong><?php echo esc_html($u_name); ?></strong> 
							<span style="color: #666; font-size: 12px;">(Usuario: <?php echo esc_html($u->user_login); ?>, Email: <?php echo esc_html($u->user_email); ?>)</span>
							<?php if ($match['dist'] === 0) echo '<span style="color: green; font-weight: bold; margin-left: 5px;">(Coincidencia Exacta)</span>'; ?>
						</label>
					<?php endforeach; ?>

					<hr style="margin: 20px 0;">

					<label style="display: block; margin-bottom: 10px; font-size: 14px;">
						<input type="radio" name="author_choice" value="create_new" <?php echo (!$exact_match) ? 'checked' : ''; ?>>
						<strong>Crear nuevo profesor:</strong> <?php echo esc_html($prof_name); ?><br>
						<p style="color: #666; margin-top: 5px; margin-left: 25px; font-size: 13px;">Se creará automáticamente un usuario (Rol: Autor). El correo se generará como <code><?php echo esc_html($preview_email); ?></code>.</p>
					</label>
				</div>
				
				<br>
				<a href="<?php echo esc_url(admin_url('admin.php?page=rc-course-importer')); ?>" class="button button-secondary" style="margin-right: 10px;">Volver</a>
				<?php submit_button('Confirmar y Crear Curso', 'primary', 'submit', false); ?>
			</form>
		</div>
		<?php
	}

	public static function handle_process(): void {
		if (!current_user_can('manage_options')) wp_die('No tienes permisos.');
		check_admin_referer('rc_import_course_nonce', 'rc_import_course_nonce');

		$json_str = get_transient('rc_course_import_json_' . get_current_user_id());
		if (!$json_str) {
			set_transient('rc_import_course_err', 'La sesión ha expirado. Intenta nuevamente.', 30);
			wp_redirect(admin_url('admin.php?page=rc-course-importer'));
			exit;
		}

		$data = json_decode($json_str, true);
		$curso_data = $data['curso'];
		$lecciones_data = $data['lecciones'] ?? array();
		$author_choice = sanitize_text_field($_POST['author_choice'] ?? '');
		$author_id = get_current_user_id();

		// Procesar selección de profesor
		if ($author_choice === 'create_new') {
			$prof_name = trim($curso_data['profesor_nombre'] ?? 'Nuevo Profesor');
			$parts = explode(' ', $prof_name);
			$first = array_shift($parts);
			$last = implode(' ', $parts);
			if (empty($last)) $last = 'Profesor';

			$domain = 'redcultural.cl';

			$clean_first = strtolower(remove_accents($first));
			$clean_last = strtolower(remove_accents(str_replace(' ', '', $last)));

			$base_email = $clean_first . '.' . $clean_last . '@' . $domain;
			$email = $base_email;
			$counter = 1;
			while (email_exists($email)) {
				$email = $clean_first . '.' . $clean_last . $counter . '@' . $domain;
				$counter++;
			}

			$user_login = $clean_first . '.' . $clean_last;
			$counter_login = 1;
			$base_login = $user_login;
			while (username_exists($user_login)) {
				$user_login = $base_login . $counter_login;
				$counter_login++;
			}

			$new_user_id = wp_insert_user(array(
				'user_login'   => $user_login,
				'user_pass'    => wp_generate_password(12, false),
				'user_email'   => $email,
				'first_name'   => $first,
				'last_name'    => $last,
				'display_name' => $prof_name,
				'role'         => 'author'
			));

			if (!is_wp_error($new_user_id)) {
				$author_id = $new_user_id;
			}
		} elseif (is_numeric($author_choice)) {
			$author_id = (int) $author_choice;
		}

		// 1. Crear el producto en WooCommerce
		$product_id = 0;
		$precio_completo = (float) ($curso_data['precio_curso_completo'] ?? 0);
		
		if ($precio_completo > 0 && class_exists('WC_Product_Simple')) {
			$product = new \WC_Product_Simple();
			$product->set_name(sanitize_text_field($curso_data['titulo']));
			$product->set_regular_price($precio_completo);
			$product->set_virtual(true);
			$product->set_sold_individually(true);
			$product_id = $product->save();
		}

		// 2. Crear el Curso en LearnDash
		$course_post_data = array(
			'post_title'   => sanitize_text_field($curso_data['titulo']),
			'post_content' => wp_kses_post($curso_data['descripcion'] ?? ''),
			'post_excerpt' => wp_kses_post($curso_data['extracto'] ?? ''),
			'post_type'    => 'sfwd-courses',
			'post_status'  => 'publish',
			'post_author'  => $author_id,
		);
		$course_id = wp_insert_post($course_post_data);

		if (is_wp_error($course_id)) {
			set_transient('rc_import_course_err', 'Error al crear el curso.', 30);
			wp_redirect(admin_url('admin.php?page=rc-course-importer'));
			exit;
		}

		// Asignar Categoría y Etiqueta de LearnDash
		wp_set_object_terms($course_id, 'Curso', 'ld_course_category');
		wp_set_object_terms($course_id, 'curriculum_publico', 'ld_course_tag');

		// Metadatos del curso
		$ld_settings = array(
			'sfwd-courses_course_price_type' => sanitize_text_field($curso_data['modalidad'] ?? 'closed'),
			'sfwd-courses_course_price'      => $precio_completo,
		);
		
		if ($product_id > 0) {
			$ld_settings['sfwd-courses_custom_button_url'] = get_permalink($product_id);
			update_post_meta($course_id, '_pcg_woo_product_id', $product_id);
			update_post_meta($course_id, 'learndash_woocommerce_product_ids', $product_id);
		}
		
		update_post_meta($course_id, '_sfwd-courses', $ld_settings);
		update_post_meta($course_id, 'course_price', $precio_completo);
		
		$precio_sesion = (float) ($curso_data['precio_por_sesion'] ?? 0);
		if ($precio_sesion > 0) {
			update_post_meta($course_id, '_individual_lesson_price', $precio_sesion);
		}

		if (!empty($curso_data['youtube_url'])) {
			update_post_meta($course_id, 'rc_youtube_url', esc_url_raw($curso_data['youtube_url']));
		}

		// 3. Crear Lecciones
		$steps = array();
		foreach ($lecciones_data as $index => $leccion) {
			$lesson_post_data = array(
				'post_title'   => sanitize_text_field($leccion['titulo']),
				'post_content' => wp_kses_post($leccion['descripcion'] ?? ''),
				'post_type'    => 'sfwd-lessons',
				'post_status'  => 'publish',
				'post_author'  => $author_id,
				'menu_order'   => $index + 1,
			);
			$lesson_id = wp_insert_post($lesson_post_data);

			if (!is_wp_error($lesson_id)) {
				update_post_meta($lesson_id, 'course_id', $course_id);
				
				$timestamp = !empty($leccion['fecha']) ? strtotime($leccion['fecha']) : 0;
				$ld_lesson_settings = array(
					'sfwd-lessons_course' => $course_id,
				);
				if ($timestamp > 0) {
					$ld_lesson_settings['sfwd-lessons_lesson_access_from'] = $timestamp;
					update_post_meta($lesson_id, 'lesson_access_from', $timestamp);
				}
				update_post_meta($lesson_id, '_sfwd-lessons', $ld_lesson_settings);

				$steps[] = 'sfwd-lessons:' . $lesson_id;
			}
		}

		// Vincular pasos en LearnDash
		if (function_exists('learndash_course_set_steps')) {
			learndash_course_set_steps($course_id, $steps);
		} else {
			update_post_meta($course_id, 'course_steps', $steps);
		}

		delete_transient('rc_course_import_json_' . get_current_user_id());
		
		$author_name = get_the_author_meta('display_name', $author_id);
		set_transient('rc_import_course_msg', "Curso '{$curso_data['titulo']}' creado con éxito. Profesor asignado: {$author_name}.", 30);
		wp_redirect(admin_url('admin.php?page=rc-course-importer'));
		exit;
	}
}
