<?php
/**
 * AJAX and Form Handlers for Red Cultural Templates.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

final class RC_Templates_Handlers {
	public static function init(): void {
		add_action('admin_post_nopriv_rcp_checkout_auth', array(__CLASS__, 'handle_checkout_auth'));
		add_action('admin_post_rcp_checkout_auth', array(__CLASS__, 'handle_checkout_auth'));
		
		add_action('admin_post_nopriv_rcp_viaje_italia_interest', array(__CLASS__, 'handle_viaje_italia_interest_form'));
		add_action('admin_post_rcp_viaje_italia_interest', array(__CLASS__, 'handle_viaje_italia_interest_form'));
		
		add_action('admin_post_nopriv_rcp_viaje_japon_interest', array(__CLASS__, 'handle_viaje_japon_interest_form'));
		add_action('admin_post_rcp_viaje_japon_interest', array(__CLASS__, 'handle_viaje_japon_interest_form'));
		
		add_action('admin_post_nopriv_rcp_viaje_escandinavia_interest', array(__CLASS__, 'handle_viaje_escandinavia_interest_form'));
		add_action('admin_post_rcp_viaje_escandinavia_interest', array(__CLASS__, 'handle_viaje_escandinavia_interest_form'));
		
		add_action('admin_post_nopriv_rcp_contact_form', array(__CLASS__, 'handle_contacto_form'));
		add_action('admin_post_rcp_contact_form', array(__CLASS__, 'handle_contacto_form'));

		add_action('wp_ajax_nopriv_rcp_user_exists', array(__CLASS__, 'handle_user_exists_ajax'));
		add_action('wp_ajax_rcp_user_exists', array(__CLASS__, 'handle_user_exists_ajax'));
		add_action('wp_ajax_nopriv_rcp_forgot_password', array(__CLASS__, 'handle_forgot_password_ajax'));
		add_action('wp_ajax_rcp_forgot_password', array(__CLASS__, 'handle_forgot_password_ajax'));
		add_action('wp_ajax_nopriv_rcp_reset_password', array(__CLASS__, 'handle_reset_password_ajax'));
		add_action('wp_ajax_rcp_reset_password', array(__CLASS__, 'handle_reset_password_ajax'));

		// Global Checkout Fields Filter
		add_filter('woocommerce_checkout_fields', array(__CLASS__, 'filter_checkout_fields_for_digital_products'), 20);

		// Force course products to be treated as virtual (no shipping cost)
		add_filter('woocommerce_product_is_virtual', array(__CLASS__, 'force_course_products_virtual'), 10, 2);

		// Global course filtering for non-admins
		add_action('pre_get_posts', array(__CLASS__, 'filter_unassigned_courses_globally'));
	}

	public static function force_course_products_virtual($is_virtual, $product) {
		if ($is_virtual) {
			return $is_virtual;
		}

		if (!is_object($product) || !is_callable([$product, 'get_id'])) {
			return $is_virtual;
		}

		$product_id = $product->get_id();

		// Check if it's an RCIL dynamic product
		if (get_post_meta($product_id, '_rcil_is_dynamic_product', true)) {
			return true;
		}

		// Check if it is explicitly linked to a LearnDash course
		if (get_post_meta($product_id, '_related_course_id', true) || get_post_meta($product_id, '_related_course', true)) {
			return true;
		}

		// Query if this product ID is used as the linked WooCommerce product for ANY LearnDash course
		// Need to account for serialized arrays since learndash_woocommerce_product_ids is an array.
		global $wpdb;
		$like_int_str = '%i:' . $product_id . ';%';
		$like_str_str = '%"' . $product_id . '"%';
		$like_url_str = '%add-to-cart=' . $product_id . '%';

		$course_id = $wpdb->get_var($wpdb->prepare("
			SELECT post_id FROM $wpdb->postmeta 
			WHERE meta_key IN ('_pcg_woo_product_id', 'learndash_woocommerce_product_ids', '_learndash_woocommerce_product_ids', '_sfwd-courses') 
			AND (
				meta_value = %s 
				OR meta_value LIKE %s 
				OR meta_value LIKE %s
				OR meta_value LIKE %s
			) LIMIT 1", 
			$product_id,
			$like_int_str,
			$like_str_str,
			$like_url_str
		));

		if ($course_id) {
			return true;
		}

		// Fallback: Check if product is in 'cursos' category
		if (has_term(array('curso', 'cursos', 'course', 'courses'), 'product_cat', $product_id)) {
			return true;
		}

		return $is_virtual;
	}

	public static function filter_checkout_fields_for_digital_products(array $fields): array {
		if (!function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) {
			return $fields;
		}

		$has_physical = false;
		foreach (WC()->cart->get_cart() as $cart_item) {
			$product = $cart_item['data'] ?? null;
			if ($product && is_object($product) && method_exists($product, 'needs_shipping') && $product->needs_shipping()) {
				$has_physical = true;
				break;
			}
		}

		if ($has_physical) {
			return $fields;
		}

		$allowed = array(
			'billing_first_name',
			'billing_last_name',
			'billing_email',
			'billing_phone',
		);

		foreach (array('billing', 'shipping', 'account', 'order') as $section) {
			if (empty($fields[$section]) || !is_array($fields[$section])) {
				continue;
			}
			foreach (array_keys($fields[$section]) as $key) {
				if ($section === 'billing' && in_array($key, $allowed, true)) {
					continue;
				}
				unset($fields[$section][$key]);
			}
		}

		return $fields;
	}

	private static function get_form_recipients(string $form_id, string $default_email = ''): array {
		if ($default_email === '') $default_email = (string) get_option('admin_email');
		$recipients = array($default_email);
		$settings = get_option('rcp_form_recipients', array());
		if (isset($settings[$form_id]) && is_string($settings[$form_id]) && $settings[$form_id] !== '') {
			$additional = explode(',', $settings[$form_id]);
			foreach ($additional as $email) {
				$email = sanitize_email(trim($email));
				if ($email !== '' && is_email($email)) $recipients[] = $email;
			}
		}
		return array_unique($recipients);
	}

	public static function handle_contacto_form(): void {
		if (!isset($_POST['rcp_contact_nonce']) || !wp_verify_nonce((string) $_POST['rcp_contact_nonce'], 'rcp_contact_form')) {
			wp_safe_redirect((string) home_url('/contacto/'));
			exit;
		}
		$token = isset($_POST['captcha_token']) ? (string) wp_unslash($_POST['captcha_token']) : '';
		if (!RC_Anti_Spam::verify($token) || !RC_Anti_Spam::verify_honeypot() || !RC_Anti_Spam::verify_timing()) {
			wp_safe_redirect(add_query_arg('rcp_contact', 'error', (string) home_url('/contacto/')));
			exit;
		}
		$name = isset($_POST['name']) ? sanitize_text_field((string) wp_unslash($_POST['name'])) : '';
		$email = isset($_POST['email']) ? sanitize_email((string) wp_unslash($_POST['email'])) : '';
		$phone = isset($_POST['phone']) ? sanitize_text_field((string) wp_unslash($_POST['phone'])) : '';
		$subject = isset($_POST['subject']) ? sanitize_text_field((string) wp_unslash($_POST['subject'])) : '';
		$message = isset($_POST['message']) ? sanitize_textarea_field((string) wp_unslash($_POST['message'])) : '';
		$to = self::get_form_recipients('contacto');
		$mail_subject = 'Contacto — ' . ($subject !== '' ? $subject : 'Nuevo mensaje');
		$body = "Nombre: {$name}\nEmail: {$email}\nCelular: {$phone}\nAsunto: {$subject}\n\nMensaje:\n{$message}\n";
		$headers = $email !== '' ? array('Reply-To: ' . $email) : array();
		wp_mail($to, $mail_subject, $body, $headers);
		$redirect = wp_get_referer() ?: home_url('/contacto/');
		wp_safe_redirect(add_query_arg('rcp_contact', 'success', (string) $redirect));
		exit;
	}

	public static function handle_viaje_italia_interest_form(): void {
		if (!isset($_POST['rcp_vi_nonce']) || !wp_verify_nonce((string) $_POST['rcp_vi_nonce'], 'rcp_viaje_italia_interest')) {
			wp_safe_redirect((string) home_url('/viaje-italia/'));
			exit;
		}
		$token = isset($_POST['captcha_token']) ? (string) wp_unslash($_POST['captcha_token']) : '';
		if (!RC_Anti_Spam::verify($token) || !RC_Anti_Spam::verify_honeypot() || !RC_Anti_Spam::verify_timing()) {
			wp_safe_redirect(add_query_arg('rcp_vi_interest', 'error', (string) home_url('/viaje-italia/')));
			exit;
		}
		$name = isset($_POST['rcp_vi_name']) ? sanitize_text_field((string) wp_unslash($_POST['rcp_vi_name'])) : '';
		$email = isset($_POST['rcp_vi_email']) ? sanitize_email((string) wp_unslash($_POST['rcp_vi_email'])) : '';
		$phone = isset($_POST['rcp_vi_phone']) ? sanitize_text_field((string) wp_unslash($_POST['rcp_vi_phone'])) : '';
		$message = isset($_POST['rcp_vi_message']) ? sanitize_textarea_field((string) wp_unslash($_POST['rcp_vi_message'])) : '';
		$to = self::get_form_recipients('viaje_italia');
		$subject = 'Viaje Italia — Nuevo interés';
		$body = "Viaje: Italia\n\nNombre: {$name}\nEmail: {$email}\nTeléfono: {$phone}\n\nMensaje:\n{$message}\n";
		$headers = $email !== '' ? array('Reply-To: ' . $email) : array();
		wp_mail($to, $subject, $body, $headers);
		$redirect = wp_get_referer() ?: home_url('/viaje-italia/');
		wp_safe_redirect(add_query_arg('rcp_vi_interest', 'success', (string) $redirect));
		exit;
	}

	public static function handle_viaje_escandinavia_interest_form(): void {
		if (!isset($_POST['rcp_ve_nonce']) || !wp_verify_nonce((string) $_POST['rcp_ve_nonce'], 'rcp_viaje_escandinavia_interest')) {
			wp_safe_redirect((string) home_url('/viaje-escandinavia/'));
			exit;
		}
		$token = isset($_POST['captcha_token']) ? (string) wp_unslash($_POST['captcha_token']) : '';
		if (!RC_Anti_Spam::verify($token) || !RC_Anti_Spam::verify_honeypot() || !RC_Anti_Spam::verify_timing()) {
			wp_safe_redirect(add_query_arg('rcp_ve_interest', 'error', (string) home_url('/viaje-escandinavia/')));
			exit;
		}
		$name = isset($_POST['rcp_ve_name']) ? sanitize_text_field((string) wp_unslash($_POST['rcp_ve_name'])) : '';
		$email = isset($_POST['rcp_ve_email']) ? sanitize_email((string) wp_unslash($_POST['rcp_ve_email'])) : '';
		$phone = isset($_POST['rcp_ve_phone']) ? sanitize_text_field((string) wp_unslash($_POST['rcp_ve_phone'])) : '';
		$message = isset($_POST['rcp_ve_message']) ? sanitize_textarea_field((string) wp_unslash($_POST['rcp_ve_message'])) : '';
		$to = self::get_form_recipients('viaje_escandinavia', 'magdalena@redcultural.cl');
		$subject = 'Viaje Escandinavia — Nuevo interés';
		$body = "Viaje: Escandinavia 2026\nFechas: 25 de agosto al 09 de septiembre de 2026\n\nNombre: {$name}\nEmail: {$email}\nTeléfono: {$phone}\n\nMensaje:\n{$message}\n";
		$headers = $email !== '' ? array('Reply-To: ' . $email) : array();
		wp_mail($to, $subject, $body, $headers);
		$redirect = wp_get_referer() ?: home_url('/viaje-escandinavia/');
		wp_safe_redirect(add_query_arg('rcp_ve_interest', 'success', (string) $redirect));
		exit;
	}

	public static function handle_viaje_japon_interest_form(): void {
		if (!isset($_POST['rcp_vj_nonce']) || !wp_verify_nonce((string) $_POST['rcp_vj_nonce'], 'rcp_viaje_japon_interest')) {
			wp_safe_redirect((string) home_url('/viaje-japon/'));
			exit;
		}
		$token = isset($_POST['captcha_token']) ? (string) wp_unslash($_POST['captcha_token']) : '';
		if (!RC_Anti_Spam::verify($token) || !RC_Anti_Spam::verify_honeypot() || !RC_Anti_Spam::verify_timing()) {
			wp_safe_redirect(add_query_arg('rcp_vj_interest', 'error', (string) home_url('/viaje-japon/')));
			exit;
		}
		$name = isset($_POST['rcp_vj_name']) ? sanitize_text_field((string) wp_unslash($_POST['rcp_vj_name'])) : '';
		$email = isset($_POST['rcp_vj_email']) ? sanitize_email((string) wp_unslash($_POST['rcp_vj_email'])) : '';
		$phone = isset($_POST['rcp_vj_phone']) ? sanitize_text_field((string) wp_unslash($_POST['rcp_vj_phone'])) : '';
		$message = isset($_POST['rcp_vj_message']) ? sanitize_textarea_field((string) wp_unslash($_POST['rcp_vj_message'])) : '';
		$to = self::get_form_recipients('viaje_japon', 'magdalena@redcultural.cl');
		$subject = 'Viaje Japón — Nuevo interés';
		$body = "Viaje: Japón\nFechas: 24-octubre al 09 de noviembre de 2026\n\nNombre: {$name}\nEmail: {$email}\nTeléfono: {$phone}\n\nMensaje:\n{$message}\n";
		$headers = $email !== '' ? array('Reply-To: ' . $email) : array();
		wp_mail($to, $subject, $body, $headers);
		$redirect = wp_get_referer() ?: home_url('/viaje-japon/');
		wp_safe_redirect(add_query_arg('rcp_vj_interest', 'success', (string) $redirect));
		exit;
	}

	public static function handle_user_exists_ajax(): void {
		check_ajax_referer('rcp_user_exists', 'nonce');
		$login_or_email = isset($_POST['user_login']) ? sanitize_text_field((string) wp_unslash($_POST['user_login'])) : '';
		$login_or_email = trim((string) $login_or_email);
		if ($login_or_email === '') wp_send_json_success(array('exists' => false));
		$exists = is_email($login_or_email) ? (bool) email_exists($login_or_email) : (bool) username_exists($login_or_email);
		wp_send_json_success(array('exists' => $exists));
	}

	public static function handle_forgot_password_ajax(): void {
		$nonce = isset($_POST['rcp_nonce']) ? (string) wp_unslash($_POST['rcp_nonce']) : '';
		if ($nonce === '' || !wp_verify_nonce($nonce, 'rcp_checkout_auth')) wp_send_json_error(array('message' => 'No autorizado.'), 403);
		$email = isset($_POST['email']) ? sanitize_email((string) wp_unslash($_POST['email'])) : '';
		$email = trim((string) $email);
		if ($email === '' || !is_email($email)) wp_send_json_error(array('message' => 'Correo inválido.'), 400);
		if (!email_exists($email)) wp_send_json_error(array('code' => 'not_found', 'message' => 'Esta cuenta no existe en nuestro sistema'), 404);
		$_POST['user_login'] = $email;
		add_filter('wp_mail_content_type', static fn() => 'text/html');
		$result = retrieve_password();
		remove_filter('wp_mail_content_type', static fn() => 'text/html');
		if (is_wp_error($result)) wp_send_json_error(array('message' => 'No pudimos enviar el correo. Inténtalo de nuevo.'), 500);
		wp_send_json_success(array('message' => 'Te enviamos un correo para restablecer tu contraseña. Revisa tu bandeja de entrada.'));
	}

	public static function handle_reset_password_ajax(): void {
		$key = isset($_POST['key']) ? sanitize_text_field((string) $_POST['key']) : '';
		$login = isset($_POST['login']) ? sanitize_text_field((string) $_POST['login']) : '';
		$password = isset($_POST['password']) ? (string) $_POST['password'] : '';
		if (!$key || !$login || !$password) wp_send_json_error(array('message' => 'Información incompleta.'), 400);
		$user = check_password_reset_key($key, $login);
		if (is_wp_error($user)) wp_send_json_error(array('message' => 'El enlace ha expirado o es inválido.'), 400);
		if (strlen($password) < 8) wp_send_json_error(array('message' => 'La contraseña debe tener al menos 8 caracteres.'), 400);
		reset_password($user, $password);
		wp_send_json_success(array('message' => 'Contraseña actualizada con éxito.'));
	}

	public static function handle_checkout_auth(): void {
		if (!isset($_POST['rcp_nonce']) || !wp_verify_nonce((string) $_POST['rcp_nonce'], 'rcp_checkout_auth')) {
			wp_safe_redirect(home_url('/'));
			exit;
		}
		$redirect_to = isset($_POST['redirect_to']) ? esc_url_raw((string) wp_unslash($_POST['redirect_to'])) : (string) home_url('/');
		if ($redirect_to === '') $redirect_to = (string) home_url('/');
		$token = isset($_POST['captcha_token']) ? (string) wp_unslash($_POST['captcha_token']) : '';
		if (!RC_Anti_Spam::verify($token)) {
			error_log('RC Anti-Spam: Captcha verification failed.');
			wp_safe_redirect(add_query_arg('rcp_auth_error', 'captcha_failed', $redirect_to));
			exit;
		}

		// Honeypot & Timing checks
		if (!RC_Anti_Spam::verify_honeypot() || !RC_Anti_Spam::verify_timing()) {
			error_log('RC Anti-Spam: Honeypot or Timing verification failed.');
			// We don't want to give bots too much info, but for debugging we use a generic error.
			wp_safe_redirect(add_query_arg('rcp_auth_error', 'spam_detected', $redirect_to));
			exit;
		}
		$mode = isset($_POST['mode']) ? (string) wp_unslash($_POST['mode']) : 'login';
		$user_login = isset($_POST['user_login']) ? sanitize_text_field((string) wp_unslash($_POST['user_login'])) : '';
		$email = isset($_POST['email']) ? sanitize_email((string) wp_unslash($_POST['email'])) : '';
		$password = isset($_POST['password']) ? (string) wp_unslash($_POST['password']) : '';
		$remember = !empty($_POST['remember']);
		if ($mode === 'forgot') {
			$login_or_email = $user_login !== '' ? $user_login : $email;
			$login_or_email = trim((string) $login_or_email);
			if ($login_or_email === '') {
				wp_safe_redirect(add_query_arg('rcp_auth_error', '1', $redirect_to));
				exit;
			}
			$_POST['user_login'] = $login_or_email;
			$result = retrieve_password();
			if (is_wp_error($result)) {
				wp_safe_redirect(add_query_arg('rcp_auth_error', '1', $redirect_to));
				exit;
			}
			wp_safe_redirect(add_query_arg('rcp_auth_notice', 'reset_sent', $redirect_to));
			exit;
		}
		if (($user_login === '' && $email === '') || $password === '') {
			wp_safe_redirect(add_query_arg('rcp_auth_error', '1', $redirect_to));
			exit;
		}
		if ($user_login === '') $user_login = $email;
		if ($mode === 'register') {
			if ($email === '' || !is_email($email)) {
				wp_safe_redirect(add_query_arg('rcp_auth_error', '1', $redirect_to));
				exit;
			}
			if (email_exists($email)) {
				$mode = 'login';
			} else {
				$first_name = isset($_POST['first_name']) ? sanitize_text_field((string) wp_unslash($_POST['first_name'])) : '';
				$last_name = isset($_POST['last_name']) ? sanitize_text_field((string) wp_unslash($_POST['last_name'])) : '';
				$username = sanitize_user(strstr($email, '@', true) ?: $email, true);
				if ($username === '') $username = 'user';
				$base = $username;
				$i = 1;
				while (username_exists($username)) {
					$username = $base . $i;
					$i++;
				}
				$user_id = wp_create_user($username, $password, $email);
				if (is_wp_error($user_id)) {
					wp_safe_redirect(add_query_arg('rcp_auth_error', '1', $redirect_to));
					exit;
				}
				RC_Templates_Emails::send_welcome_email((int) $user_id, $first_name, $email);
				wp_new_user_notification($user_id, null, 'admin');
				if ($first_name !== '') update_user_meta((int) $user_id, 'first_name', $first_name);
				if ($last_name !== '') update_user_meta((int) $user_id, 'last_name', $last_name);
				$user_login = $username;
			}
		}
		if (strpos($user_login, '@') !== false) {
			$by_email = get_user_by('email', $user_login);
			if ($by_email && isset($by_email->user_login)) $user_login = (string) $by_email->user_login;
		}
		$user = wp_signon(array('user_login' => $user_login, 'user_password' => $password, 'remember' => $remember), is_ssl());
		if (is_wp_error($user)) {
			wp_safe_redirect(add_query_arg('rcp_auth_error', '1', $redirect_to));
			exit;
		}

		// ----- Purchase Intent: prepare cart and redirect to confirmation -----
		if (class_exists('RC_Purchase_Intent')) {
			$intent = RC_Purchase_Intent::get();
			if ($intent && !empty($intent['type'])) {
				$result = RC_Purchase_Intent::prepare_cart();
				if (!is_wp_error($result)) {
					wp_safe_redirect(wc_get_checkout_url());
					exit;
				}
				// If prepare fails, fall through to normal redirect.
			}
		}

		wp_safe_redirect($redirect_to);
		exit;
	}

	/**
	 * Filter unassigned courses globally for non-admins.
	 *
	 * @param WP_Query $query The query object.
	 */
	public static function filter_unassigned_courses_globally($query): void {
		if (is_admin() || !$query->is_main_query()) {
			return;
		}

		if (current_user_can('manage_options')) {
			return;
		}

		$post_type = $query->get('post_type');

		// Handle both string and array post types
		$is_course_query = false;
		if ($post_type === 'sfwd-courses') {
			$is_course_query = true;
		} elseif (is_array($post_type) && in_array('sfwd-courses', $post_type, true)) {
			$is_course_query = true;
		} elseif ($query->is_search() || $query->is_archive()) {
			// In search or generic archives, we might be querying multiple types.
			// We can't easily exclude author 1 for ONLY sfwd-courses without a complex tax query or meta query,
			// but usually author 1 is the admin who owns all unassigned content.
			// If we exclude author 1 here, we might hide articles too if they are unassigned.
			// Let's be specific to courses if possible.
		}

		if ($is_course_query) {
			$teacher_ids = \Red_Cultural_Templates::get_active_teacher_ids();
			$query->set('author__in', !empty($teacher_ids) ? $teacher_ids : array(-1));
		}
	}
}
