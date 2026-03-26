<?php
/**
 * Shortcodes for Red Cultural Templates.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

final class RC_Templates_Shortcodes {
	/**
	 * Initialize the shortcodes.
	 */
	public static function init(): void {
		self::load_dependencies();

		// Core shortcodes
		add_shortcode('rc_user_first_name', array(__CLASS__, 'render_user_first_name_shortcode'));
		add_shortcode('rc_auth_modal_open', array(__CLASS__, 'render_auth_modal_open_shortcode'));
		add_shortcode('red_cultural_sales_admin', array(__CLASS__, 'render_sales_admin_shortcode'));

		// Rendering shortcodes (functions defined in included files)
		add_shortcode('red-cultural-cursos', 'rcp_red_cultural_cursos_shortcode');
		add_shortcode('red-cultural-cursos-carousel', 'rcp_red_cultural_cursos_carousel_shortcode');
		add_shortcode('red-cultural-viajes', 'rcp_red_cultural_viajes_shortcode');
		add_shortcode('red-cultural-us', 'rcp_red_cultural_us_shortcode');
	}

	/**
	 * Load individual shortcode rendering files.
	 */
	private static function load_dependencies(): void {
		$base_path = plugin_dir_path(__FILE__);
		$files = array(
			'red-cultural-cursos.php',
			'red-cultural-cursos-carousel.php',
			'red-cultural-viajes.php',
			'red-cultural-us.php',
		);

		foreach ($files as $file) {
			$path = $base_path . $file;
			if (file_exists($path)) {
				require_once $path;
			}
		}
	}

	public static function render_user_first_name_shortcode(): string {
		if (!is_user_logged_in()) return '';
		$user = wp_get_current_user();
		return $user->first_name ?: $user->display_name;
	}

	public static function render_auth_modal_open_shortcode(array $atts = array(), ?string $content = null): string {
		$atts = shortcode_atts(array('class' => ''), $atts, 'rc_auth_modal_open');
		$label = $content ?: 'Iniciar Sesión';
		if (is_user_logged_in()) return '';
		return sprintf(
			'<button type="button" class="rcp-auth-trigger %s" data-rcp-auth-open="1">%s</button>',
			esc_attr($atts['class']),
			esc_html($label)
		);
	}

	public static function render_sales_admin_shortcode(): string {
		if (!current_user_can('manage_options')) return '';
		ob_start();
		?>
		<div id="rc-sales-admin-wrap" class="bg-zinc-950 text-white min-h-screen p-8 font-sans">
			<!-- Admin sales UI extracted from original file -->
			<div class="max-w-7xl mx-auto">
				<div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
					<div>
						<h1 class="text-4xl font-light tracking-tight mb-2">Panel de Ventas</h1>
						<p class="text-zinc-500 text-sm uppercase tracking-widest">Red Cultural — Gestión de Pedidos</p>
					</div>
					<div class="relative group">
						<span class="absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500 group-focus-within:text-[#c5a367] transition-colors"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg></span>
						<input type="text" id="rc-sales-search" placeholder="Cursos, clientes o emails..." class="bg-zinc-900 border border-zinc-800 text-white pl-12 pr-6 py-3 rounded-md w-full md:w-80 focus:outline-none focus:border-[#c5a367] transition-all duration-300 placeholder:text-zinc-700" />
					</div>
				</div>
				<div class="bg-zinc-900/50 border border-zinc-800 rounded-xl overflow-hidden backdrop-blur-sm shadow-2xl">
					<div class="overflow-x-auto">
						<table class="w-full text-left border-collapse" id="rc-sales-table">
							<thead>
								<tr class="border-b border-zinc-800 bg-zinc-900/80">
									<th class="px-6 py-5 text-[10px] font-bold text-zinc-500 uppercase tracking-widest">ID</th>
									<th class="px-6 py-5 text-[10px] font-bold text-zinc-500 uppercase tracking-widest">Cliente</th>
									<th class="px-6 py-5 text-[10px] font-bold text-zinc-500 uppercase tracking-widest">Producto</th>
									<th class="px-6 py-5 text-[10px] font-bold text-zinc-500 uppercase tracking-widest text-center">Cant.</th>
									<th class="px-6 py-5 text-[10px] font-bold text-zinc-500 uppercase tracking-widest text-right">Total</th>
									<th class="px-6 py-5 text-[10px] font-bold text-zinc-500 uppercase tracking-widest text-center">Estado</th>
									<th class="px-6 py-5 text-[10px] font-bold text-zinc-500 uppercase tracking-widest">Fecha</th>
								</tr>
							</thead>
							<tbody id="rc-sales-tbody" class="divide-y divide-zinc-800/50">
								<?php RC_Templates_Admin::render_sales_table_rows(); ?>
							</tbody>
						</table>
					</div>
				</div>
				<div id="rc-sales-pagination-container" class="mt-8 flex justify-center"></div>
			</div>
		</div>
		<style>
			#rc-sales-admin-wrap .status-badge{padding:4px 10px;border-radius:99px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1px}
			#rc-sales-admin-wrap .status-completed{background:rgba(34,197,94,.1);color:#4ade80}
			#rc-sales-admin-wrap .status-processing{background:rgba(59,130,246,.1);color:#60a5fa}
			#rc-sales-admin-wrap .status-on-hold{background:rgba(234,179,8,.1);color:#facc15}
			#rc-sales-admin-wrap .rcp-pagination{display:flex;gap:8px}
			#rc-sales-admin-wrap .rcp-pagination a, #rc-sales-admin-wrap .rcp-pagination span{padding:8px 16px;background:#18181b;border:1px solid #27272a;color:#a1a1aa;border-radius:6px;text-decoration:none;transition:all .2s}
			#rc-sales-admin-wrap .rcp-pagination .current{background:#c5a367;color:#000;border-color:#c5a367}
			#rc-sales-admin-wrap .rcp-pagination a:hover{border-color:#c5a367;color:#fff}
		</style>
		<script>
			jQuery(function($) {
				var $input = $('#rc-sales-search'), $tbody = $('#rc-sales-tbody'), $pag = $('#rc-sales-pagination-container');
				function updatePag() { var $newPag = $('#rc-sales-pagination-new'); if ($newPag.length) { $pag.html($newPag.html()); $newPag.remove(); } }
				updatePag();
				var timer;
				$input.on('input', function() {
					clearTimeout(timer);
					timer = setTimeout(function() {
						var s = $input.val();
						$.post(ajaxurl, { action: 'rcp_search_sales', search: s, paged: 1 }, function(res) {
							if (res.success) { $tbody.html(res.data.html); updatePag(); }
						});
					}, 300);
				});
				$(document).on('click', '#rc-sales-pagination-container a', function(e) {
					e.preventDefault();
					var href = $(this).attr('href'), p = 1;
					if (href) { var match = href.match(/#(\d+)/); if (match) p = match[1]; }
					$.post(ajaxurl, { action: 'rcp_search_sales', search: $input.val(), paged: p }, function(res) {
						if (res.success) { $tbody.html(res.data.html); updatePag(); window.scrollTo({top:0, behavior:'smooth'}); }
					});
				});
			});
		</script>
		<?php
		return ob_get_clean();
	}
}
