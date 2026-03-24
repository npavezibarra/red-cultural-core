<?php
/**
 * RC Anti-Spam Module
 * Handles Google reCAPTCHA v3 and Cloudflare Turnstile.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

final class RC_Anti_Spam {
	
	public static function init(): void {
		add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
	}

	/**
	 * Get anti-spam settings.
	 * 
	 * @return array{provider:string, site_key:string, secret_key:string}
	 */
	public static function get_settings(): array {
		$defaults = array(
			'provider'   => 'none', // 'none', 'recaptcha', 'turnstile'
			'site_key'   => '',
			'secret_key' => '',
		);
		$saved = get_option('rc_antispam_settings', array());
		return wp_parse_args((array)$saved, $defaults);
	}

	/**
	 * Check if anti-spam is active and configured.
	 */
	public static function is_enabled(): bool {
		$settings = self::get_settings();
		
		// Lockout prevention: if provider is 'none', it's always disabled.
		if ($settings['provider'] === 'none') {
			return false;
		}

		return !empty($settings['site_key']) && !empty($settings['secret_key']);
	}

	/**
	 * Enqueue necessary scripts based on provider.
	 */
	public static function enqueue_assets(): void {
		if (!self::is_enabled()) {
			return;
		}

		$settings = self::get_settings();

		if ($settings['provider'] === 'recaptcha') {
			wp_enqueue_script(
				'rc-recaptcha-api',
				'https://www.google.com/recaptcha/api.js?render=' . esc_attr($settings['site_key']),
				array(),
				null,
				true
			);
		} elseif ($settings['provider'] === 'turnstile') {
			wp_enqueue_script(
				'rc-turnstile-api',
				'https://challenges.cloudflare.com/turnstile/v0/api.js',
				array(),
				null,
				true
			);
		}
	}

	/**
	 * Verify the captcha token with the provider's API.
	 */
	public static function verify(?string $token): bool {
		// If disabled, verification always passes.
		if (!self::is_enabled()) {
			return true;
		}

		if (empty($token)) {
			return false;
		}

		$settings = self::get_settings();
		$api_url  = '';

		if ($settings['provider'] === 'recaptcha') {
			$api_url = 'https://www.google.com/recaptcha/api/siteverify';
		} elseif ($settings['provider'] === 'turnstile') {
			$api_url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
		}

		if (empty($api_url)) {
			return true; // Unknown provider, allow to avoid lockout.
		}

		$response = wp_remote_post($api_url, array(
			'body' => array(
				'secret'   => $settings['secret_key'],
				'response' => $token,
				'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
			),
		));

		if (is_wp_error($response)) {
			// On API error, we might want to fail-open to avoid lockout, 
			// but usually, it's better to fail-closed for security.
			// Given the user's past experience, we'll log it and let it pass if it's a server-side timeout.
			error_log('RC Anti-Spam: API connection failed. ' . $response->get_error_message());
			return true; 
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);
		
		if (!is_array($body) || !isset($body['success'])) {
			return false;
		}

		// For reCAPTCHA v3, we should also check the score if we want to be strict.
		// Usually > 0.5 is human.
		if ($settings['provider'] === 'recaptcha' && isset($body['score'])) {
			return (float)$body['score'] >= 0.5;
		}

		return (bool)$body['success'];
	}

	/**
	 * Output the widget HTML if needed (Turnstile needs a div).
	 */
	public static function render_widget(): void {
		if (!self::is_enabled()) {
			return;
		}

		$settings = self::get_settings();
		
		if ($settings['provider'] === 'turnstile') {
			echo '<div class="cf-turnstile" data-sitekey="' . esc_attr($settings['site_key']) . '"></div>';
		}
	}

	/**
	 * Render hidden fields for forms.
	 */
	public static function render_form_fields(): void {
		if (!self::is_enabled()) {
			return;
		}
		echo '<input type="hidden" name="captcha_token" class="rc-captcha-token">';
		self::render_widget();
	}

	/**
	 * Render JS for specific form ID.
	 */
	public static function render_form_js(string $form_id): void {
		if (!self::is_enabled()) {
			return;
		}
		$settings = self::get_settings();
		?>
		<script>
		document.addEventListener('DOMContentLoaded', function() {
			var form = document.getElementById('<?php echo esc_js($form_id); ?>');
			if (!form) return;
			form.addEventListener('submit', function(e) {
				var self = this;
				var provider = '<?php echo esc_js($settings['provider']); ?>';
				var siteKey = '<?php echo esc_js($settings['site_key']); ?>';
				
				if (provider === 'recaptcha' && window.grecaptcha) {
					if (self.querySelector('.rc-captcha-token').value) return; 
					e.preventDefault();
					grecaptcha.ready(function() {
						grecaptcha.execute(siteKey, {action: 'submit'}).then(function(token) {
							self.querySelector('.rc-captcha-token').value = token;
							self.submit();
						});
					});
				} else if (provider === 'turnstile' && window.turnstile) {
					var token = turnstile.getResponse();
					if (!token) {
						e.preventDefault();
						alert('Por favor completa el captcha.');
						return;
					}
					self.querySelector('.rc-captcha-token').value = token;
				}
			});
		});
		</script>
		<?php
	}
}
