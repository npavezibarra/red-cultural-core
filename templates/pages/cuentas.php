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

?><!DOCTYPE html>
<html lang="es">
<head>
	<meta id="red-cultural-cuentas-meta-charset" charset="UTF-8">
	<meta id="red-cultural-cuentas-meta-viewport" name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Cuentas | Red Cultural</title>
	<script id="red-cultural-cuentas-tailwind" src="https://cdn.tailwindcss.com"></script>
	<style id="red-cultural-cuentas-style">
		@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

		body {
			font-family: 'Inter', sans-serif;
			background-color: #000;
			margin: 0;
			padding: 0;
			overflow-x: hidden;
		}

		#red-cultural-cuentas-hero {
			background-image: url('<?php echo esc_url($bg_url); ?>');
			background-size: cover;
			background-position: center;
			background-attachment: fixed;
			min-height: 100vh;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			position: relative;
			padding: 80px 20px;
			color: #ffffff;
			text-align: center;
		}

		#red-cultural-cuentas-overlay {
			position: absolute;
			inset: 0;
			background: linear-gradient(to top, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0.4) 40%, rgba(0, 0, 0, 0.1) 100%);
			backdrop-filter: blur(1px);
		}

		#red-cultural-cuentas-content {
			position: relative;
			z-index: 10;
			max-width: 1200px;
			width: 100%;
		}

		.rcp-cuentas-title {
			font-size: 30px;
			font-weight: 500;
			line-height: 1.1;
			margin-bottom: 24px;
			letter-spacing: -0.02em;
			color: #ffffff;
		}

		.rcp-cuentas-subtext {
			font-size: clamp(18px, 3vw, 24px);
			font-weight: 300;
			color: rgba(255, 255, 255, 0.9);
			margin-bottom: 8px;
			line-height: 1.4;
		}

		.rcp-cuentas-btn {
			display: inline-block;
			background-color: #ffffff;
			color: #000000;
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

		.rcp-cuentas-btn:hover {
			transform: scale(1.05);
			background-color: #f8f8f8;
			box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
		}

		.sales-table-container {
			background: rgba(0, 0, 0, 0.65);
			backdrop-filter: blur(20px);
			border: 1px solid rgba(255, 255, 255, 0.15);
			border-radius: 16px;
			padding: 32px;
			margin-top: 48px;
			overflow-x: auto;
			box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
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
			border-bottom: 2px solid rgba(255, 255, 255, 0.1);
			font-weight: 700;
			text-transform: uppercase;
			font-size: 11px;
			letter-spacing: 0.1em;
			color: #c5a367;
		}

		td {
			padding: 16px;
			border-bottom: 1px solid rgba(255, 255, 255, 0.05);
			font-size: 14px;
			color: rgba(255, 255, 255, 0.9);
			font-weight: 400;
		}

		tr:last-child td {
			border-bottom: none;
		}

		tr:hover td {
			background: rgba(255, 255, 255, 0.03);
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
			background: rgba(255, 255, 255, 0.05);
			border: 1px solid rgba(255, 255, 255, 0.1);
			color: rgba(255, 255, 255, 0.8);
			text-decoration: none;
			border-radius: 6px;
			font-size: 13px;
			font-weight: 500;
			transition: all 0.2s ease;
		}

		.rcp-pagination .current {
			background: #ffffff;
			color: #000000;
			border-color: #ffffff;
		}

		.rcp-pagination a:hover {
			background: rgba(255, 255, 255, 0.1);
			color: #fff;
			transform: translateY(-1px);
		}

		.search-container {
			margin-bottom: 32px;
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
			background: rgba(0, 0, 0, 0.6);
			border: 1px solid rgba(255, 255, 255, 0.2);
			border-radius: 12px;
			padding: 14px 20px;
			padding-right: 100px;
			color: #fff;
			font-size: 14px;
			outline: none;
			transition: all 0.3s ease;
			backdrop-filter: blur(20px);
		}

		.search-input::placeholder {
			color: rgba(255, 255, 255, 0.4);
		}

		.search-input:focus {
			border-color: #c5a367;
			background: rgba(0, 0, 0, 0.8);
			box-shadow: 0 0 0 4px rgba(197, 163, 103, 0.15);
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

	<div id="red-cultural-cuentas-hero">
		<div id="red-cultural-cuentas-overlay" aria-hidden="true"></div>

		<main id="red-cultural-cuentas-content">
			<h1 class="rcp-cuentas-title">Ventas</h1>

			<?php if (!is_user_logged_in()): ?>
				<p class="rcp-cuentas-subtext">Acceso restringido a administradores.</p>
				<p class="rcp-cuentas-subtext">Por favor, inicia sesión para continuar.</p>
				<button type="button" class="rcp-cuentas-btn" data-rcp-auth-open="1">
					Iniciar Sesión
				</button>
			<?php elseif ($is_admin): ?>
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
						<?php RC_Templates_Admin::render_sales_table_rows($s, $paged); ?>
					</table>

                    <div id="rc-pagination-wrapper">
                        <!-- Swapped by AJAX -->
                    </div>
				</div>

				<script>
				document.addEventListener('DOMContentLoaded', function() {
					const input = document.getElementById('rc-sales-search-input');
					const table = document.getElementById('rc-sales-table');
					const paginationWrap = document.getElementById('rc-pagination-wrapper');
					let searchTimer;

					function updateTable(searchValue, pageNum = 1) {
						table.classList.add('rc-loading');
                        paginationWrap.classList.add('rc-loading');
						
						const formData = new URLSearchParams();
						formData.append('action', 'rcp_search_sales');
						formData.append('search', searchValue);
						formData.append('paged', pageNum);

						fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
							method: 'POST',
							headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
							body: formData
						})
						.then(r => r.json())
						.then(res => {
							console.log('AJAX Response:', res);
							table.classList.remove('rc-loading');
                            paginationWrap.classList.remove('rc-loading');
							if (res.success) {
								const parser = new DOMParser();
								const doc = parser.parseFromString(res.data.html, 'text/html');
								
								// Replace tbody
								const currentTbody = table.querySelector('tbody');
								const newTbody = doc.querySelector('#rc-sales-tbody');
								if (currentTbody && newTbody) {
									currentTbody.replaceWith(newTbody);
								}

								// Replace pagination
								const newPagination = doc.querySelector('#rc-sales-pagination-new');
								if (newPagination) {
									paginationWrap.innerHTML = newPagination.innerHTML;
									initPaginationLinks();
								} else {
                                    paginationWrap.innerHTML = '';
                                }

								// Update URL without reload
								const url = new URL(window.location);
								if (searchValue) url.searchParams.set('rc_search', searchValue);
                                else url.searchParams.delete('rc_search');
								url.searchParams.set('paged', pageNum);
								window.history.pushState({}, '', url);
							} else {
                                console.error('AJAX Success false:', res);
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

					input.oninput = () => {
						clearTimeout(searchTimer);
						searchTimer = setTimeout(() => updateTable(input.value, 1), 500);
					};

                    document.querySelector('.search-btn').onclick = () => updateTable(input.value, 1);

					// Move initial pagination into wrapper
					const existingPagination = document.getElementById('rc-sales-pagination-new');
					if (existingPagination) {
						paginationWrap.innerHTML = existingPagination.innerHTML;
						existingPagination.remove();
						initPaginationLinks();
					}
				});
				</script>
			<?php else: ?>
				<p class="rcp-cuentas-subtext">Acceso Denegado</p>
				<p class="rcp-cuentas-subtext">Esta página es exclusiva para administradores.</p>
				<a href="<?php echo esc_url(home_url('/')); ?>" class="rcp-cuentas-btn">Volver al Inicio</a>
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
