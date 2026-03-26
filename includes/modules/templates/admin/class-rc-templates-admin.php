<?php
/**
 * Admin logic for Red Cultural Templates.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

final class RC_Templates_Admin {
	public static function init(): void {
		add_action('admin_menu', array(__CLASS__, 'register_admin_pages'));
		add_action('admin_post_rcp_save_shop_settings', array(__CLASS__, 'handle_save_shop_settings'));
		add_action('admin_post_rcp_save_contact_forms_settings', array(__CLASS__, 'handle_save_contact_forms_settings'));
		add_action('admin_post_rcp_save_antispam_settings', array(__CLASS__, 'handle_save_antispam_settings'));
		add_action('wp_ajax_rcp_search_sales', array(__CLASS__, 'ajax_search_sales'));
	}

	public static function register_admin_pages(): void {
		add_menu_page(
			'Red Cultural',
			'Red Cultural',
			'manage_options',
			'red-cultural-pages',
			array(__CLASS__, 'render_admin_root_page'),
			'dashicons-admin-page',
			58
		);

		add_submenu_page(
			'red-cultural-pages',
			'Tienda',
			'Tienda',
			'manage_options',
			'red-cultural-pages-shop',
			array(__CLASS__, 'render_admin_shop_page')
		);

		add_submenu_page(
			'red-cultural-pages',
			'Contact Forms',
			'Contact Forms',
			'manage_options',
			'red-cultural-pages-contact-forms',
			array(__CLASS__, 'render_admin_contact_forms_page')
		);
	}

	public static function render_admin_root_page(): void {
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('No tienes permisos para ver esta página.', 'red-cultural-pages'));
		}

		?>
		<div class="wrap">
			<h1><?php echo esc_html__('Páginas Red Cultural', 'red-cultural-pages'); ?></h1>
			<p><?php echo esc_html__('Configuraciones internas para plantillas y páginas personalizadas.', 'red-cultural-pages'); ?></p>
			<p>
				<a class="button button-primary" href="<?php echo esc_url((string) admin_url('admin.php?page=red-cultural-pages-shop')); ?>">
					<?php echo esc_html__('Abrir configuración de Tienda', 'red-cultural-pages'); ?>
				</a>
			</p>
		</div>
		<?php
	}

	public static function render_admin_shop_page(): void {
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('No tienes permisos para ver esta página.', 'red-cultural-pages'));
		}

		$terms = get_terms(
			array(
				'taxonomy' => 'product_cat',
				'hide_empty' => false,
				'orderby' => 'name',
				'order' => 'ASC',
			)
		);

		$selected = get_option('rcp_shop_category_ids', array());
		$selected = is_array($selected) ? array_values(array_filter(array_map('intval', $selected))) : array();

		$updated = isset($_GET['rcp_updated']) && (string) $_GET['rcp_updated'] === '1';
		?>
		<div class="wrap">
			<h1><?php echo esc_html__('Tienda', 'red-cultural-pages'); ?></h1>

			<?php if ($updated) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo esc_html__('Guardado.', 'red-cultural-pages'); ?></p></div>
			<?php endif; ?>

			<p><?php echo esc_html__('Selecciona las categorías de productos que quieres mostrar en la página de Tienda.', 'red-cultural-pages'); ?></p>

			<form method="post" action="<?php echo esc_url((string) admin_url('admin-post.php')); ?>">
				<input type="hidden" name="action" value="rcp_save_shop_settings" />
				<?php wp_nonce_field('rcp_save_shop_settings', 'rcp_shop_nonce'); ?>

				<table class="widefat striped" style="max-width:900px">
					<thead>
						<tr>
							<th style="width:50px"><?php echo esc_html__('#', 'red-cultural-pages'); ?></th>
							<th><?php echo esc_html__('Categoría', 'red-cultural-pages'); ?></th>
							<th style="width:120px"><?php echo esc_html__('Seleccionar', 'red-cultural-pages'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if (is_wp_error($terms) || !is_array($terms) || $terms === array()) : ?>
							<tr><td colspan="3"><?php echo esc_html__('No se encontraron categorías de productos.', 'red-cultural-pages'); ?></td></tr>
						<?php else : ?>
							<?php foreach ($terms as $i => $term) : ?>
								<?php if (!($term instanceof WP_Term)) {
									continue;
								} ?>
								<tr>
									<td><?php echo esc_html((string) ($i + 1)); ?></td>
									<td><?php echo esc_html((string) $term->name); ?></td>
									<td>
										<label>
											<input
												type="checkbox"
												name="rcp_shop_categories[]"
												value="<?php echo esc_attr((string) $term->term_id); ?>"
												<?php checked(in_array((int) $term->term_id, $selected, true)); ?>
											/>
										</label>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>

				<p style="margin-top:16px">
					<button type="submit" class="button button-primary"><?php echo esc_html__('Guardar', 'red-cultural-pages'); ?></button>
				</p>
			</form>
		</div>
		<?php
	}

	public static function handle_save_shop_settings(): void {
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('No tienes permisos para realizar esta acción.', 'red-cultural-pages'));
		}

		if (!isset($_POST['rcp_shop_nonce']) || !wp_verify_nonce((string) $_POST['rcp_shop_nonce'], 'rcp_save_shop_settings')) {
			wp_die(esc_html__('Nonce inválido.', 'red-cultural-pages'));
		}

		$raw = isset($_POST['rcp_shop_categories']) ? (array) $_POST['rcp_shop_categories'] : array();
		$ids = array();
		foreach ($raw as $value) {
			$ids[] = (int) sanitize_text_field((string) wp_unslash($value));
		}
		$ids = array_values(array_filter(array_unique($ids), static fn($v): bool => is_int($v) && $v > 0));

		update_option('rcp_shop_category_ids', $ids, false);

		wp_safe_redirect((string) admin_url('admin.php?page=red-cultural-pages-shop&rcp_updated=1'));
		exit;
	}

	public static function render_admin_contact_forms_page(): void {
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('No tienes permisos para ver esta página.', 'red-cultural-pages'));
		}

		$settings = get_option('rcp_form_recipients', array());
		$forms = array(
			'contacto'           => array('label' => 'Contacto Principal', 'default' => get_option('admin_email')),
			'viaje_italia'       => array('label' => 'Viaje Italia', 'default' => get_option('admin_email')),
			'viaje_escandinavia' => array('label' => 'Viaje Escandinavia', 'default' => 'magdalena@redcultural.cl'),
			'viaje_japon'        => array('label' => 'Viaje Japón', 'default' => 'magdalena@redcultural.cl'),
			'viaje_escocia'      => array('label' => 'Viaje Escocia', 'default' => get_option('admin_email')),
		);

		$updated = isset($_GET['rcp_updated']) && (string) $_GET['rcp_updated'] === '1';
		?>
		<div class="wrap">
			<h1><?php echo esc_html__('Configuración de Formularios de Contacto', 'red-cultural-pages'); ?></h1>

			<?php if ($updated) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo esc_html__('Configuración guardada.', 'red-cultural-pages'); ?></p></div>
			<?php endif; ?>

			<p><?php echo esc_html__('Define los correos adicionales que recibirán notificaciones de cada formulario. El correo del administrador siempre se incluye.', 'red-cultural-pages'); ?></p>

			<form method="post" action="<?php echo esc_url((string) admin_url('admin-post.php')); ?>">
				<input type="hidden" name="action" value="rcp_save_contact_forms_settings" />
				<?php wp_nonce_field('rcp_save_contact_forms_settings', 'rcp_cf_nonce'); ?>

				<table class="widefat striped" style="max-width:1000px; margin-top:20px;">
					<thead>
						<tr>
							<th style="padding:12px;"><?php echo esc_html__('Formulario', 'red-cultural-pages'); ?></th>
							<th style="padding:12px;"><?php echo esc_html__('Correos Adicionales (separados por coma)', 'red-cultural-pages'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($forms as $id => $data) : ?>
							<tr>
								<td style="padding:12px; vertical-align:middle;">
									<strong><?php echo esc_html($data['label']); ?></strong>
									<br><small style="color:#666">ID: <?php echo esc_html($id); ?></small>
								</td>
								<td style="padding:12px;">
									<input 
										type="text" 
										name="rcp_form_recipients[<?php echo esc_attr($id); ?>]" 
										value="<?php echo esc_attr($settings[$id] ?? ''); ?>" 
										class="large-text"
										placeholder="ejemplo1@redcultural.cl, ejemplo2@redcultural.cl"
										style="padding:8px;"
									/>
									<p class="description">
										<?php echo esc_html__('Se enviará a: ', 'red-cultural-pages'); ?> 
										<code><?php echo esc_html((string) $data['default']); ?></code>
										<?php if (!empty($settings[$id])) {
											echo ' + ' . esc_html($settings[$id]);
										} ?>
									</p>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<p style="margin-top:24px">
					<button type="submit" class="button button-primary button-large"><?php echo esc_html__('Guardar Cambios de Destinatarios', 'red-cultural-pages'); ?></button>
				</p>
			</form>

			<hr style="margin:40px 0;">

			<h2><?php echo esc_html__('Configuración Anti-Spam (reCAPTCHA / Turnstile)', 'red-cultural-pages'); ?></h2>
			<p><?php echo esc_html__('Protege tus formularios usando Google reCAPTCHA v3 o Cloudflare Turnstile.', 'red-cultural-pages'); ?></p>
			
			<div class="notice notice-warning inline" style="margin-bottom:20px;">
				<p><strong><?php echo esc_html__('Prevención de Bloqueos:', 'red-cultural-pages'); ?></strong> <?php echo esc_html__('Si te quedas fuera de tu sitio por un error del Captcha, puedes desactivarlo desde SSH ejecutando:', 'red-cultural-pages'); ?><br>
				<code>wp option update rc_antispam_settings '{"provider":"none"}' --format=json</code></p>
			</div>

			<form method="post" action="<?php echo esc_url((string) admin_url('admin-post.php')); ?>">
				<input type="hidden" name="action" value="rcp_save_antispam_settings" />
				<?php wp_nonce_field('rcp_save_antispam_settings', 'rcp_as_nonce'); ?>

				<?php 
				$as_settings = RC_Anti_Spam::get_settings();
				?>

				<table class="form-table" style="max-width:1000px;">
					<tr>
						<th scope="row"><label><?php echo esc_html__('Proveedor', 'red-cultural-pages'); ?></label></th>
						<td>
							<select name="rc_as[provider]" id="rc_as_provider" style="min-width:200px;">
								<option value="none" <?php selected($as_settings['provider'], 'none'); ?>><?php echo esc_html__('Ninguno (Desactivado)', 'red-cultural-pages'); ?></option>
								<option value="recaptcha" <?php selected($as_settings['provider'], 'recaptcha'); ?>><?php echo esc_html__('Google reCAPTCHA v3', 'red-cultural-pages'); ?></option>
								<option value="turnstile" <?php selected($as_settings['provider'], 'turnstile'); ?>><?php echo esc_html__('Cloudflare Turnstile', 'red-cultural-pages'); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label><?php echo esc_html__('Site Key', 'red-cultural-pages'); ?></label></th>
						<td>
							<input type="text" name="rc_as[site_key]" value="<?php echo esc_attr($as_settings['site_key']); ?>" class="large-text" placeholder="Pega aquí tu Site Key">
						</td>
					</tr>
					<tr>
						<th scope="row"><label><?php echo esc_html__('Secret Key', 'red-cultural-pages'); ?></label></th>
						<td>
							<input type="password" name="rc_as[secret_key]" value="<?php echo esc_attr($as_settings['secret_key']); ?>" class="large-text" placeholder="Pega aquí tu Secret Key">
							<p class="description"><?php echo esc_html__('Tu llave secreta se guarda de forma segura.', 'red-cultural-pages'); ?></p>
						</td>
					</tr>
				</table>

				<p style="margin-top:24px">
					<button type="submit" class="button button-primary button-large"><?php echo esc_html__('Guardar Configuración Anti-Spam', 'red-cultural-pages'); ?></button>
				</p>
			</form>
		</div>
		<?php
	}

	public static function handle_save_contact_forms_settings(): void {
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('No tienes permisos para realizar esta acción.', 'red-cultural-pages'));
		}

		if (!isset($_POST['rcp_cf_nonce']) || !wp_verify_nonce((string) $_POST['rcp_cf_nonce'], 'rcp_save_contact_forms_settings')) {
			wp_die(esc_html__('Nonce inválido.', 'red-cultural-pages'));
		}

		$raw = isset($_POST['rcp_form_recipients']) ? (array) $_POST['rcp_form_recipients'] : array();
		$settings = array();
		foreach ($raw as $id => $emails) {
			$id = sanitize_key((string) $id);
			$emails = sanitize_text_field((string) $emails);
			
			// Basic cleanup of the comma separated string
			$parts = explode(',', $emails);
			$clean = array();
			foreach ($parts as $p) {
				$p = sanitize_email(trim($p));
				if ($p !== '' && is_email($p)) {
					$clean[] = $p;
				}
			}
			$settings[$id] = implode(', ', $clean);
		}

		update_option('rcp_form_recipients', $settings, false);

		wp_safe_redirect((string) admin_url('admin.php?page=red-cultural-pages-contact-forms&rcp_updated=1'));
		exit;
	}

	public static function handle_save_antispam_settings(): void {
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('No tienes permisos para realizar esta acción.', 'red-cultural-pages'));
		}

		if (!isset($_POST['rcp_as_nonce']) || !wp_verify_nonce((string) $_POST['rcp_as_nonce'], 'rcp_save_antispam_settings')) {
			wp_die(esc_html__('Nonce inválido.', 'red-cultural-pages'));
		}

		$raw = isset($_POST['rc_as']) ? (array) $_POST['rc_as'] : array();
		$settings = array(
			'provider'   => sanitize_key($raw['provider'] ?? 'none'),
			'site_key'   => sanitize_text_field($raw['site_key'] ?? ''),
			'secret_key' => sanitize_text_field($raw['secret_key'] ?? ''),
		);

		update_option('rc_antispam_settings', $settings, false);

		wp_safe_redirect((string) admin_url('admin.php?page=red-cultural-pages-contact-forms&rcp_updated=1'));
		exit;
	}

	public static function ajax_search_sales(): void {
		if (!current_user_can('manage_options')) {
			wp_send_json_error('Unauthorized');
		}

		$s = isset($_POST['search']) ? sanitize_text_field((string) $_POST['search']) : '';
		$paged = isset($_POST['paged']) ? max(1, (int) $_POST['paged']) : 1;

		ob_start();
		self::render_sales_table_rows($s, $paged);
		$html = (string) ob_get_clean();

		wp_send_json_success(array('html' => $html));
	}

	public static function render_sales_table_rows(string $s = '', int $paged = 1): void {
		if (!class_exists('WooCommerce')) {
			echo '<tr><td colspan="7" class="text-center py-10 text-red-400">WooCommerce no está activo.</td></tr>';
			return;
		}

		$args = array(
			'limit'    => 25,
			'status'   => array('processing', 'completed', 'failed', 'on-hold', 'cancelled', 'refunded'),
			'orderby'  => 'date',
			'order'    => 'DESC',
			'page'     => $paged,
			'paginate' => true,
		);

		if ($s !== '') {
			global $wpdb;

			$order_ids_by_product = (array) $wpdb->get_col($wpdb->prepare(
				"SELECT DISTINCT order_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_item_name LIKE %s",
				'%' . $wpdb->esc_like($s) . '%'
			));

			$order_ids_by_meta = (array) $wpdb->get_col($wpdb->prepare(
				"SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ('_billing_first_name', '_billing_last_name', '_billing_email') AND meta_value LIKE %s",
				'%' . $wpdb->esc_like($s) . '%'
			));

			$all_found_ids = array_unique(array_merge($order_ids_by_product, $order_ids_by_meta));

			if (!empty($all_found_ids)) {
				$args['include'] = $all_found_ids;
			} else {
				$args['search'] = $s;
			}
		}

		$results = wc_get_orders($args);
		$orders = (array) $results->orders;
		$total_pages = (int) $results->max_num_pages;

		if (!empty($orders)) {
			foreach ($orders as $order) {
				if (!$order instanceof WC_Order) continue;
				$status = $order->get_status();
				$status_label = wc_get_order_status_name($status);
				$items = $order->get_items();
				$order_date = $order->get_date_created();
				$billing_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
				$billing_email = $order->get_billing_email();

				foreach ($items as $item) {
					if (!$item instanceof WC_Order_Item_Product) continue;
					$quantity = $item->get_quantity();
					$subtotal = $item->get_total();
					?>
					<tr>
						<td><span class="opacity-50">#</span><?php echo esc_html((string) $order->get_id()); ?></td>
						<td>
							<div class="font-bold"><?php echo esc_html($billing_name); ?></div>
							<div class="text-[10px] opacity-40 uppercase tracking-wider"><?php echo esc_html($billing_email); ?></div>
						</td>
						<td class="max-w-[300px]">
							<div class="truncate font-medium text-white" title="<?php echo esc_attr((string) $item->get_name()); ?>">
								<?php echo esc_html((string) $item->get_name()); ?>
							</div>
						</td>
						<td class="text-center"><?php echo esc_html((string) $quantity); ?></td>
						<td class="text-right font-mono font-bold text-[#c5a367]">
							<?php echo esc_html(number_format((float) $subtotal, 0, ',', '.') . ' ' . $order->get_currency()); ?>
						</td>
						<td class="text-center">
							<span class="status-badge status-<?php echo esc_attr($status); ?>"><?php echo esc_html($status_label); ?></span>
						</td>
						<td class="whitespace-nowrap">
							<div class="text-[13px]"><?php echo esc_html($order_date->date('d/m/Y')); ?></div>
							<div class="text-[11px] opacity-40"><?php echo esc_html($order_date->date('H:i')); ?></div>
						</td>
					</tr>
					<?php
				}
			}
		} else {
			echo '<tr><td colspan="7" class="text-center py-10 opacity-50 italic">No se encontraron ventas.</td></tr>';
		}

		echo '<div id="rc-sales-pagination-new" class="hidden">';
		if ($total_pages > 1) {
			echo '<div class="rcp-pagination" data-paged="' . esc_attr((string) $paged) . '" data-total="' . esc_attr((string) $total_pages) . '">';
			echo paginate_links(array(
				'base'      => '#%#%',
				'format'    => '',
				'current'   => $paged,
				'total'     => $total_pages,
				'prev_text' => '&larr;',
				'next_text' => '&rarr;',
				'type'      => 'plain',
			));
			echo '</div>';
		}
		echo '</div>';
	}
}
