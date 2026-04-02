<?php
/**
 * Accounts Template for Red Cultural
 * Only accessible by admin users.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

// Pre-render block theme template parts BEFORE wp_head
$rcp_theme_header_html = '';
$rcp_theme_footer_html = '';
if (function_exists('do_blocks')) {
	$rcp_theme_header_html = (string) do_blocks('<!-- wp:template-part {"slug":"header","area":"header"} /-->');
	$rcp_theme_footer_html = (string) do_blocks('<!-- wp:template-part {"slug":"footer","area":"footer"} /-->');
}

// New background requested by user
$bg_url = 'https://red-cultural.cl/wp-content/uploads/2026/03/elcambiadors.jpeg';
$is_admin = current_user_can('manage_options');

// Initial states
$s = isset($_GET['rc_search']) ? sanitize_text_field($_GET['rc_search']) : '';
$paged = max(1, isset($_GET['paged']) ? intval($_GET['paged']) : 1);
$nonce = wp_create_nonce('rcp_search_sales');

?><!DOCTYPE html>
<html lang="es">
<head>
	<meta id="red-cultural-cuentas-meta-charset" charset="UTF-8">
	<meta id="red-cultural-cuentas-meta-viewport" name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Cuentas | Red Cultural</title>
	<script id="red-cultural-cuentas-tailwind" src="https://cdn.tailwindcss.com"></script>
	<script id="red-cultural-cuentas-chartjs" src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<style id="red-cultural-cuentas-style">
		@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

		body {
			font-family: 'Inter', sans-serif;
			background-color: #ffffff;
			margin: 0;
			padding: 0;
			overflow-x: hidden;
			color: #000000;
		}

		#red-cultural-cuentas-hero {
			background-color: #ffffff;
			min-height: 60vh;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			position: relative;
			padding: 0;
			color: #000000;
			text-align: center;
		}

		#red-cultural-cuentas-hero.restricted {
			background: url('<?php echo esc_url($bg_url); ?>') no-repeat center center;
			background-size: cover;
		}

		#red-cultural-cuentas-hero.is-admin {
			justify-content: flex-start;
			padding: 30px 20px;
		}

		#red-cultural-cuentas-overlay {
			display: none;
		}

		#red-cultural-cuentas-content {
			position: relative;
			z-index: 10;
			max-width: 1180px;
			width: 100%;
		}

		.rcp-cuentas-title {
			font-size: 30px;
			font-weight: 500;
			line-height: 1;
			margin-bottom: 0;
			letter-spacing: -0.02em;
			color: #000000;
		}

		.rcp-cuentas-subtext {
			font-size: clamp(18px, 3vw, 24px);
			font-weight: 300;
			color: #4b5563;
			margin-bottom: 8px;
			line-height: 1.4;
		}

		.rcp-cuentas-btn {
			display: inline-block;
			background-color: #000000;
			color: #ffffff;
			padding: 12px 36px;
			font-size: 16px;
			font-weight: 600;
			border-radius: 6px;
			transition: all 0.2s ease-in-out;
			margin-top: 32px;
			text-decoration: none;
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
			cursor: pointer;
			border: none;
		}

		.rcp-cuentas-btn.btn-white {
			background-color: #ffffff;
			color: #000000;
			text-transform: uppercase;
			font-size: 13px;
			letter-spacing: 0.1em;
			padding: 10px 30px;
		}

		.rcp-cuentas-btn:hover {
			transform: scale(1.05);
			box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
		}

		.sales-table-container {
			background: #ffffff;
			border-radius: 12px;
			padding: 32px;
			margin-top: 0px;
			overflow-x: auto;
			min-height: 400px;
		}

		table {
			width: 100%;
			border-collapse: separate;
			border-spacing: 0;
			text-align: left;
			transition: opacity 0.3s ease;
		}

		th {
			padding: 16px;
			border-bottom: 2px solid #f3f4f6;
			font-weight: 700;
			text-transform: uppercase;
			font-size: 11px;
			letter-spacing: 0.1em;
			color: #1a1a1a;
		}

		td {
			padding: 16px;
			border-bottom: 1px solid #f3f4f6;
			font-size: 14px;
			color: #4b5563;
			font-weight: 400;
		}

		tr:last-child td {
			border-bottom: none;
		}

		tr:hover td {
			background: #f9fafb;
		}

		.status-badge {
			padding: 4px 10px;
			border-radius: 100px;
			font-size: 10px;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: 0.05em;
		}

		.status-completed { background: #10b981; color: #fff; }
		.status-processing { background: #3b82f6; color: #fff; }
		.status-failed { background: #ef4444; color: #fff; }
		.status-on-hold { background: #f59e0b; color: #fff; }
		.status-cancelled { background: #6b7280; color: #fff; }
		.status-refunded { background: #d1d5db; color: #111; }

		.rcp-pagination {
			display: flex;
			justify-content: center;
			align-items: center;
			gap: 8px;
			margin-top: 32px;
		}

		.rcp-pagination a, .rcp-pagination span {
			padding: 8px 14px;
			background: #ffffff;
			border: 1px solid #e5e7eb;
			color: #4b5563;
			text-decoration: none;
			border-radius: 6px;
			font-size: 13px;
			font-weight: 500;
			transition: all 0.2s ease;
		}

		.rcp-pagination .current {
			background: #000000;
			color: #ffffff;
			border-color: #000000;
		}

		.rcp-pagination a:hover {
			background: #f3f4f6;
			color: #000000;
			transform: translateY(-1px);
		}

		.search-container {
			margin-bottom: 0px;
			width: 100%;
			max-width: 500px;
			margin-left: auto;
		}

		.search-wrapper {
			position: relative;
			display: flex;
			align-items: center;
		}

		.search-input {
			width: 100%;
			background: #ffffff;
			border: 1px solid #e5e7eb;
			border-radius: 12px;
			padding: 14px 20px;
			padding-right: 100px;
			color: #111827;
			font-size: 14px;
			outline: none;
			transition: all 0.3s ease;
			box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
		}

		.search-input::placeholder {
			color: #9ca3af;
		}

		.search-input:focus {
			border-color: #c5a367;
			background: #ffffff;
			box-shadow: 0 0 0 4px rgba(197, 163, 103, 0.1);
		}

		.search-btn {
			position: absolute;
			right: 6px;
			top: 6px;
			bottom: 6px;
			background: #c5a367;
			color: #000;
			border: none;
			border-radius: 8px;
			padding: 0 16px;
			font-weight: 700;
			font-size: 13px;
			cursor: pointer;
			transition: all 0.2s;
		}

		.search-btn:hover {
			background: #e0bb7a;
			transform: scale(1.02);
		}

		.rc-loading {
			opacity: 0.4;
			pointer-events: none;
		}
 
		#red-cultural-cuentas-site-header, 
		#red-cultural-cuentas-site-footer {
			background-color: #fff;
			position: relative;
			z-index: 100;
		}
		.rcp-header-row {
			display: flex;
			justify-content: space-between;
			align-items: center;
			width: 100%;
			margin-bottom: 40px;
			padding-bottom: 0;
			border-bottom: 1px solid #e5e7eb;
		}
 
		.rcp-submenu {
			display: flex;
			gap: 32px;
		}
 
		.rcp-tab {
			font-size: 16px;
			font-weight: 500;
			color: #9ca3af;
			padding: 12px 4px;
			cursor: pointer;
			position: relative;
			transition: all 0.3s ease;
		}
 
		.rcp-tab:hover {
			color: #000000;
		}
 
		.rcp-tab.active {
			color: #c5a367;
		}
 
		.rcp-tab.active::after {
			content: '';
			position: absolute;
			bottom: -1px;
			left: 0;
			right: 0;
			height: 2px;
			background: #c5a367;
			box-shadow: 0 -2px 10px rgba(197, 163, 103, 0.5);
		}

		.restriction-box {
			padding: 60px 40px;
			color: #ffffff;
			max-width: 600px;
			width: 90%;
		}

		.restriction-title {
			font-size: 48px;
			font-weight: 600;
			margin-bottom: 20px;
			letter-spacing: -0.02em;
		}

		.restriction-text {
			font-size: 20px;
			font-weight: 400;
			opacity: 0.9;
			margin-bottom: 30px;
		}
 
		.chart-view-container {
			background: #ffffff;
			border-radius: 12px;
			padding: 40px 32px;
			margin-top: 0px;
			max-height: 450px;
			display: none;
			flex-direction: column;
			align-items: center;
			justify-content: center;
		}
 
		.view-content {
			display: none;
		}
 
		.view-content.active {
			display: block;
		}
 
		.chart-view-container.active {
			display: flex;
		}
 
		#rc-sales-chart-wrapper {
			width: 100%;
			max-height: 250px;
			position: relative;
		}
 
		/* Profesores Styles */
		.month-selector-container {
			display: flex;
			justify-content: flex-end;
			margin-bottom: 20px;
		}
		.month-select {
			background: #ffffff;
			border: 1px solid #e5e7eb;
			border-radius: 8px;
			padding: 8px 16px;
			font-size: 14px;
			font-weight: 600;
			color: #111827;
			outline: none;
			cursor: pointer;
			transition: all 0.2s;
		}
		.month-select:focus {
			border-color: #c5a367;
			box-shadow: 0 0 0 3px rgba(197, 163, 103, 0.1);
		}

		.hidden { display: none !important; }
	</style>
	<?php wp_head(); ?>
</head>
<body id="red-cultural-cuentas-page" <?php body_class(); ?>>
	<?php if (function_exists('wp_body_open')) { wp_body_open(); } ?>

	<?php
	// Render the active block theme header
	if ($rcp_theme_header_html !== '') {
		echo '<div id="red-cultural-cuentas-site-header">';
		echo $rcp_theme_header_html;
		echo '</div>';
	}
	?>

	<div id="red-cultural-cuentas-hero" class="<?php echo $is_admin ? 'is-admin' : 'restricted'; ?>">
		<div id="red-cultural-cuentas-overlay" aria-hidden="true"></div>

		<main id="red-cultural-cuentas-content" class="<?php echo $is_admin ? '' : 'flex flex-col items-center'; ?>">
			<?php if ($is_admin): ?>
				<div class="rcp-header-row">
					<h1 class="rcp-cuentas-title">Ventas</h1>

					<div class="rcp-submenu">
						<div class="rcp-tab active" data-view="ventas">Ventas</div>
						<div class="rcp-tab" data-view="grafico">Gráfico</div>
						<div class="rcp-tab" data-view="profesores">Profesores</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if (!$is_admin): ?>
				<div class="restriction-box">
					<h1 class="restriction-title">Ventas</h1>
					<p class="restriction-text">Debes iniciar sesión como administrador para acceder a esta página.</p>
					<button type="button" class="rcp-cuentas-btn btn-white" data-rcp-auth-open="1">
						LOGIN
					</button>
				</div>
			<?php else: ?>


				<div id="view-ventas" class="view-content active">
					<div class="search-container">
						<div class="search-wrapper">
							<input type="text" id="rc-sales-search-input" class="search-input" placeholder="Buscar usuarios, productos o correos..." value="<?php echo esc_attr($s); ?>" autocomplete="off">
							<button type="button" class="search-btn">Buscar</button>
						</div>
					</div>

					<div class="sales-table-container">
						<table id="rc-sales-table">
							<thead>
								<tr>
									<th>ID</th>
									<th>Cliente</th>
									<th>Producto</th>
									<th class="text-center">Cant.</th>
									<th class="text-right">Precio</th>
									<th class="text-center">Estado</th>
									<th>Fecha</th>
								</tr>
							</thead>
							<tbody id="rc-sales-tbody">
								<?php RC_Templates_Admin::render_sales_table_rows($s, $paged); ?>
							</tbody>
						</table>

						<div id="rc-pagination-wrapper">
							<!-- Swapped by AJAX -->
						</div>
					</div>
				</div>

				<div id="view-grafico" class="view-content chart-view-container">
					<div class="month-selector-container w-full !mb-8">
						<select id="rc-chart-month-select" class="month-select">
							<?php
							$current_month = (int) date('m');
							$months = array(
								1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
								5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
								9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
							);
							foreach ($months as $m => $name) {
								echo '<option value="' . esc_attr((string)$m) . '" ' . selected($m, $current_month, false) . '>' . esc_html($name) . '</option>';
							}
							?>
						</select>
					</div>
					<div id="rc-sales-chart-wrapper">
						<canvas id="rc-sales-chart"></canvas>
					</div>
					<p id="rc-chart-title" class="mt-4 text-[12px] opacity-40 uppercase tracking-widest font-bold">Ventas por Día - <?php echo date_i18n('F Y'); ?></p>
				</div>

				<div id="view-profesores" class="view-content">
					<div class="month-selector-container">
						<select id="rc-profesores-month-select" class="month-select">
							<?php
							$current_month = (int) date('m');
							$months = array(
								1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
								5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
								9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
							);
							foreach ($months as $m => $name) {
								echo '<option value="' . esc_attr((string)$m) . '" ' . selected($m, $current_month, false) . '>' . esc_html($name) . '</option>';
							}
							?>
						</select>
					</div>

					<div class="sales-table-container">
						<table id="rc-profesores-table">
							<thead>
								<tr>
									<th>Profesor</th>
									<th class="text-center">Cursos Vendidos</th>
									<th class="text-right">Total ($)</th>
								</tr>
							</thead>
							<tbody id="rc-profesores-tbody">
								<!-- Loaded via AJAX -->
							</tbody>
						</table>
					</div>
				</div>


				<script>
				document.addEventListener('DOMContentLoaded', function() {
					const input = document.getElementById('rc-sales-search-input');
					const table = document.getElementById('rc-sales-table');
					const paginationWrap = document.getElementById('rc-pagination-wrapper');
					const tabs = document.querySelectorAll('.rcp-tab');
					const views = document.querySelectorAll('.view-content');
					let searchTimer;
					let salesChart = null;

					// --- View Switching ---
					tabs.forEach(tab => {
						tab.onclick = () => {
							const target = tab.dataset.view;
							tabs.forEach(t => t.classList.remove('active'));
							views.forEach(v => v.classList.remove('active'));
							
							tab.classList.add('active');
							document.getElementById('view-' + target).classList.add('active');

							if (target === 'grafico' && !salesChart) {
								fetchChartData();
							}

							if (target === 'profesores') {
								fetchProfesoresData();
							}
						};
					});

					function fetchProfesoresData() {
						const monthSelect = document.getElementById('rc-profesores-month-select');
						const tbody = document.getElementById('rc-profesores-tbody');
						const container = tbody.closest('.sales-table-container');
						
						container.classList.add('rc-loading');

						const formData = new URLSearchParams();
						formData.append('action', 'rcp_get_profesores_sales');
						formData.append('nonce', '<?php echo esc_js($nonce); ?>');
						formData.append('month', monthSelect.value);

						fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
							method: 'POST',
							headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
							body: formData
						})
						.then(r => r.json())
						.then(res => {
							container.classList.remove('rc-loading');
							if (res.success) {
								tbody.innerHTML = res.data.html;
							}
						})
						.catch(err => {
							container.classList.remove('rc-loading');
							console.error('Profesores AJAX Error:', err);
						});
					}

					document.getElementById('rc-profesores-month-select').onchange = fetchProfesoresData;

					function fetchChartData() {
						const monthSelect = document.getElementById('rc-chart-month-select');
						const formData = new URLSearchParams();
						formData.append('action', 'rcp_get_sales_chart_data');
						formData.append('nonce', '<?php echo esc_js($nonce); ?>');
						formData.append('month', monthSelect.value);

						fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
							method: 'POST',
							headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
							body: formData
						})
						.then(r => r.json())
						.then(res => {
							if (res.success) {
								renderChart(res.data);
								const title = document.getElementById('rc-chart-title');
								if (title) {
									title.innerText = 'Ventas por Día - ' + res.data.month + ' <?php echo date('Y'); ?>';
								}
							}
						});
					}

					document.getElementById('rc-chart-month-select').onchange = () => {
						if (salesChart) {
							salesChart.destroy();
							salesChart = null;
						}
						fetchChartData();
					};

					function renderChart(data) {
						const ctx = document.getElementById('rc-sales-chart').getContext('2d');
						salesChart = new Chart(ctx, {
							type: 'bar',
							data: {
								labels: data.labels,
								datasets: [
									{
										label: 'Libros',
										data: data.libros,
										backgroundColor: 'rgba(197, 163, 103, 0.8)',
										borderColor: '#c5a367',
										borderWidth: 1,
										borderRadius: 4
									},
									{
										label: 'Cursos',
										data: data.cursos,
										backgroundColor: 'rgba(59, 130, 246, 0.8)',
										borderColor: '#3b82f6',
										borderWidth: 1,
										borderRadius: 4
									}
								]
							},
							options: {
								responsive: true,
								maintainAspectRatio: false,
								plugins: {
									legend: {
										position: 'top',
										labels: { color: '#111827', font: { size: 11, weight: '600' }, boxWidth: 12, usePointStyle: true, padding: 20 }
									},
									tooltip: {
										backgroundColor: '#111827',
										titleColor: '#fff',
										bodyColor: '#fff',
										cornerRadius: 8,
										padding: 12
									}
								},
								scales: {
									y: {
										beginAtZero: true,
										grid: { color: '#f3f4f6' },
										ticks: { color: '#6b7280', font: { size: 10 } }
									},
									x: {
										grid: { display: false },
										ticks: { color: '#6b7280', font: { size: 10 } }
									}
								}
							}
						});
					}

					// --- Table & Search Logic ---
					function updateTable(searchValue, pageNum = 1) {
						if (!table) return;
						table.classList.add('rc-loading');
                        paginationWrap.classList.add('rc-loading');
						
						const formData = new URLSearchParams();
						formData.append('action', 'rcp_search_sales');
						formData.append('nonce', '<?php echo esc_js($nonce); ?>');
						formData.append('rc_search_term', searchValue);
						formData.append('paged', pageNum);

						fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
							method: 'POST',
							headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
							body: formData
						})
						.then(r => r.json())
						.then(res => {
							table.classList.remove('rc-loading');
                            paginationWrap.classList.remove('rc-loading');
							if (res.success) {
								const tbody = document.getElementById('rc-sales-tbody');
								if (tbody) {
									tbody.innerHTML = res.data.html;
								}

								const parser = new DOMParser();
								const doc = parser.parseFromString(res.data.html, 'text/html');
								const newPagination = doc.querySelector('#rc-sales-pagination-new');
								
								if (newPagination) {
									paginationWrap.innerHTML = newPagination.innerHTML;
									initPaginationLinks();
								} else {
                                    paginationWrap.innerHTML = '';
                                }

								const url = new URL(window.location);
								if (searchValue) url.searchParams.set('rc_search', searchValue);
                                else url.searchParams.delete('rc_search');
								url.searchParams.set('paged', pageNum);
								window.history.pushState({}, '', url);
							}
						})
                        .catch(err => {
                            console.error('AJAX Error:', err);
                            table.classList.remove('rc-loading');
                            paginationWrap.classList.remove('rc-loading');
                        });
					}

					function initPaginationLinks() {
						paginationWrap.querySelectorAll('a').forEach(link => {
							link.onclick = (e) => {
								e.preventDefault();
								const href = link.getAttribute('href');
                                const match = href.match(/#(\d+)#/);
								if (match) {
									updateTable(input.value, match[1]);
									window.scrollTo({ top: table.offsetTop - 150, behavior: 'smooth' });
								}
							};
						});
					}

					if (input) {
						input.oninput = () => {
							clearTimeout(searchTimer);
							searchTimer = setTimeout(() => updateTable(input.value, 1), 500);
						};
					}

                    const searchBtn = document.querySelector('.search-btn');
					if (searchBtn) {
						searchBtn.onclick = () => updateTable(input.value, 1);
					}

					// --- Status Dropdown Logic (Event Delegation) ---
					document.addEventListener('click', function(e) {
						// 1. Toggle Dropdown
						const badge = e.target.closest('.status-badge');
						if (badge && badge.closest('.rcp-status-dropdown')) {
							const menu = badge.nextElementSibling;
							const isOpen = !menu.classList.contains('hidden');
							
							// Close all first
							document.querySelectorAll('.rcp-status-menu').forEach(m => m.classList.add('hidden'));
							
							if (!isOpen) menu.classList.remove('hidden');
							return;
						}

						// 2. Handle Status Change Click
						const statusBtn = e.target.closest('.rcp-status-menu button');
						if (statusBtn) {
							const dropdown = statusBtn.closest('.rcp-status-dropdown');
							const orderId = dropdown.dataset.orderId;
							const newStatus = statusBtn.dataset.status;
							const badge = dropdown.querySelector('.status-badge');
							const menu = dropdown.querySelector('.rcp-status-menu');

							dropdown.classList.add('rc-loading');
							
							const formData = new URLSearchParams();
							formData.append('action', 'rcp_update_order_status');
							formData.append('nonce', '<?php echo esc_js($nonce); ?>');
							formData.append('order_id', orderId);
							formData.append('status', newStatus);

							fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
								method: 'POST',
								headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
								body: formData
							})
							.then(r => r.json())
							.then(res => {
								dropdown.classList.remove('rc-loading');
								if (res.success) {
									// Update badge class
									badge.className = `status-badge status-${res.data.status} cursor-pointer flex items-center justify-center gap-1 mx-auto w-fit`;
									badge.innerHTML = `${res.data.label} <svg class="w-2.5 h-2.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>`;
									menu.classList.add('hidden');
								} else {
									alert('Error: ' + res.data);
								}
							})
							.catch(err => {
								dropdown.classList.remove('rc-loading');
								console.error('Status Update Error:', err);
							});
							return;
						}

						// 3. Click outside -> close
						if (!e.target.closest('.rcp-status-dropdown')) {
							document.querySelectorAll('.rcp-status-menu').forEach(m => m.classList.add('hidden'));
						}
					});

					// Move initial pagination into wrapper
					const existingPagination = document.getElementById('rc-sales-pagination-new');
					if (existingPagination) {
						paginationWrap.innerHTML = existingPagination.innerHTML;
						existingPagination.remove();
						initPaginationLinks();
					}
				});
				</script>
			<?php endif; ?>
		</main>
	</div>

	<?php
	// Render the active block theme footer
	if ($rcp_theme_footer_html !== '') {
		echo '<div id="red-cultural-cuentas-site-footer">';
		echo $rcp_theme_footer_html;
		echo '</div>';
	}
	?>

	<?php wp_footer(); ?>
</body>
</html>
