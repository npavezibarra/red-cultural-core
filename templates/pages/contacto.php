<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

// Pre-render block theme template parts BEFORE wp_head so their assets are enqueued in the correct place.
$rcp_theme_header_html = '';
$rcp_theme_footer_html = '';
if (function_exists('do_blocks')) {
	$rcp_theme_header_html = (string) do_blocks('<!-- wp:template-part {"slug":"header","area":"header"} /-->');
	$rcp_theme_footer_html = (string) do_blocks('<!-- wp:template-part {"slug":"footer","area":"footer"} /-->');
}

$uploads = wp_get_upload_dir();
$uploads_base = isset($uploads['baseurl']) ? (string) $uploads['baseurl'] : (string) content_url('/uploads');
$uploads_base = rtrim($uploads_base, '/');

$bg_rel = '/2026/03/la-anunciacion_red-cultural_1280x558.webp';
$bg_local = $uploads_base . $bg_rel;
$bg_live = 'https://red-cultural.cl/wp-content/uploads' . $bg_rel;

?><!DOCTYPE html>
<html lang="es">
<head>
	<meta id="red-cultural-contacto-meta-charset" charset="UTF-8">
	<meta id="red-cultural-contacto-meta-viewport" name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html((string) wp_get_document_title()); ?></title>
	<script id="red-cultural-contacto-tailwind" src="https://cdn.tailwindcss.com"></script>
	<style id="red-cultural-contacto-style">
		@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

		body{
			font-family:'Inter',sans-serif;
			background-color:#000;
			margin:0;
			padding:0;
			overflow-x:hidden;
		}

		#red-cultural-contacto-bg{
			background-image:
				url('<?php echo esc_url($bg_local); ?>'),
				url('<?php echo esc_url($bg_live); ?>');
			background-size:cover,cover;
			background-position:center,center;
			background-attachment:fixed,fixed;
			min-height:50%;
			display:flex;
			align-items:flex-start;
			justify-content:center;
			position:relative;
			padding:50px 16px 48px;
		}

		@media (min-width: 768px){
			#red-cultural-contacto-bg{padding:50px 40px 64px}
		}

		#red-cultural-contacto-overlay{
			position:absolute;
			inset:0;
			background:rgba(0,0,0,0.25);
			backdrop-filter:blur(4px);
		}

		#red-cultural-contacto-main{
			position:relative;
			z-index:10;
			background-color:#ffffff;
			border:1px solid #000000;
			max-width:1000px;
			width:95%;
			display:flex;
			flex-direction:column;
		}

		@media (min-width: 768px){
			#red-cultural-contacto-main{flex-direction:row}
		}

		#red-cultural-contacto-form .rcp-contact-input{
			border:1px solid #e5e7eb;
			background:#fff;
			transition:all 0.2s ease;
		}
		#red-cultural-contacto-form .rcp-contact-input:focus{
			outline:none;
			border-color:#000;
			background:#fafafa;
		}

		/* Contact form typography: +15% and black text */
		#red-cultural-contacto-form,
		#red-cultural-contacto-form *{
			color:#000;
		}
		#red-cultural-contacto-form label{
			font-size:11.5px !important;
			color:#000 !important;
		}
		#red-cultural-contacto-form input,
		#red-cultural-contacto-form select,
		#red-cultural-contacto-form textarea{
			font-size:16px !important;
			color:#000 !important;
		}
		#red-cultural-contacto-form ::placeholder{
			color:rgba(0,0,0,0.45) !important;
		}
		#red-cultural-contacto-submit{
			font-size:13.8px;
		}

		#red-cultural-contacto-submit{
			background-color:#000;
			color:#fff;
			transition:all 0.3s ease;
			text-transform:uppercase;
			letter-spacing:0.05em;
			font-weight:600;
		}
		#red-cultural-contacto-submit:hover{
			background-color:#222;
			transform:translateY(-1px);
		}

		#red-cultural-contacto-success{display:none}
		h1,h2,label{font-style:normal !important}
	</style>
	<?php wp_head(); ?>
</head>
<body id="red-cultural-contacto-page" <?php body_class(); ?>>
	<?php if (function_exists('wp_body_open')) { wp_body_open(); } ?>

	<?php
	if ($rcp_theme_header_html !== '') {
		echo '<div id="red-cultural-contacto-site-header">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $rcp_theme_header_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<div id="red-cultural-contacto-bg" class="p-4 md:p-10">
		<div id="red-cultural-contacto-overlay" aria-hidden="true"></div>

		<main id="red-cultural-contacto-main">
			<section id="red-cultural-contacto-left" class="w-full md:w-5/12 p-8 md:p-12 flex flex-col justify-center bg-black text-white">
				<p id="red-cultural-contacto-copy" class="text-base md:text-lg leading-relaxed font-light opacity-80">
					Escríbenos si tienes preguntas sobre nuestros
					<span id="red-cultural-contacto-copy-underline" class="font-medium text-white underline underline-offset-4">cursos, charlas o productos</span>
					de nuestra tienda o si tienes una
					<span id="red-cultural-contacto-copy-strong" class="font-medium text-white">propuesta de curso</span>
					que podamos publicar en
					<span id="red-cultural-contacto-copy-brand" class="tracking-widest font-semibold uppercase text-xs block mt-2">Red Cultural</span>.
				</p>
				<div id="red-cultural-contacto-left-divider" class="mt-10 h-[1px] w-12 bg-white opacity-30"></div>
			</section>

			<section id="red-cultural-contacto-right" class="w-full md:w-7/12 p-8 md:p-12 bg-white">
				<h1 id="red-cultural-contacto-title" class="text-3xl font-bold text-gray-900 mb-2">Contacto</h1>
				<div id="red-cultural-contacto-form-container">
					<form
						id="red-cultural-contacto-form"
						class="grid grid-cols-1 md:grid-cols-2 gap-5"
						method="post"
						action="<?php echo esc_url((string) admin_url('admin-post.php')); ?>"
					>
						<input type="hidden" id="red-cultural-contacto-action" name="action" value="rcp_contact_form">
						<?php wp_nonce_field('rcp_contact_form', 'rcp_contact_nonce'); ?>

						<div id="red-cultural-contacto-field-name" class="md:col-span-2">
							<label id="red-cultural-contacto-label-name" class="block text-[10px] font-semibold uppercase tracking-wider text-gray-500 mb-2" for="red-cultural-contacto-input-name">Nombre Completo</label>
							<input id="red-cultural-contacto-input-name" type="text" name="name" required class="rcp-contact-input w-full p-3 text-sm rounded-sm" placeholder="Tu nombre...">
						</div>

						<div id="red-cultural-contacto-field-email">
							<label id="red-cultural-contacto-label-email" class="block text-[10px] font-semibold uppercase tracking-wider text-gray-500 mb-2" for="red-cultural-contacto-input-email">Email</label>
							<input id="red-cultural-contacto-input-email" type="email" name="email" required class="rcp-contact-input w-full p-3 text-sm rounded-sm" placeholder="correo@ejemplo.com">
						</div>

						<div id="red-cultural-contacto-field-phone">
							<label id="red-cultural-contacto-label-phone" class="block text-[10px] font-semibold uppercase tracking-wider text-gray-500 mb-2" for="red-cultural-contacto-input-phone">Celular</label>
							<input id="red-cultural-contacto-input-phone" type="tel" name="phone" required class="rcp-contact-input w-full p-3 text-sm rounded-sm" placeholder="+56 9...">
						</div>

						<div id="red-cultural-contacto-field-subject" class="md:col-span-2">
							<label id="red-cultural-contacto-label-subject" class="block text-[10px] font-semibold uppercase tracking-wider text-gray-500 mb-2" for="red-cultural-contacto-input-subject">Asunto</label>
							<select id="red-cultural-contacto-input-subject" name="subject" required class="rcp-contact-input w-full p-3 text-sm bg-white rounded-sm cursor-pointer">
								<option value="" disabled selected>Selecciona una opción</option>
								<option value="Consulta sobre Curso">Consulta sobre Curso</option>
								<option value="Reclamo">Reclamo</option>
								<option value="Propuesta Curso">Propuesta Curso</option>
							</select>
						</div>

						<div id="red-cultural-contacto-field-message" class="md:col-span-2">
							<label id="red-cultural-contacto-label-message" class="block text-[10px] font-semibold uppercase tracking-wider text-gray-500 mb-2" for="red-cultural-contacto-input-message">Mensaje</label>
							<textarea id="red-cultural-contacto-input-message" name="message" rows="4" required class="rcp-contact-input w-full p-3 text-sm rounded-sm" placeholder="¿Cómo podemos ayudarte?"></textarea>
						</div>

						<div id="red-cultural-contacto-field-submit" class="md:col-span-2 mt-4">
							<button id="red-cultural-contacto-submit" type="submit" class="w-full py-4 text-xs tracking-widest">Enviar Mensaje</button>
						</div>
					</form>
				</div>

				<div id="red-cultural-contacto-success" class="h-full flex flex-col items-center justify-center text-center py-10">
					<div id="red-cultural-contacto-success-icon" class="w-12 h-12 border border-black flex items-center justify-center mb-6">
						<span id="red-cultural-contacto-success-check" class="text-xl font-light">✓</span>
					</div>
					<h2 id="red-cultural-contacto-success-title" class="text-xl font-light uppercase tracking-[0.2em]">Enviado</h2>
					<p id="red-cultural-contacto-success-text" class="mt-4 text-xs text-gray-500 max-w-xs leading-relaxed">Hemos recibido tu mensaje. Nos pondremos en contacto contigo a la brevedad.</p>
					<button id="red-cultural-contacto-success-reload" type="button" class="mt-10 text-[9px] font-semibold uppercase tracking-widest border-b border-black pb-1 hover:opacity-50 transition-opacity">Volver al formulario</button>
				</div>

				<script id="red-cultural-contacto-script">
					(function(){
						var url = new URL(window.location.href);
						var ok = url.searchParams.get('rcp_contact') === 'success';
						var formContainer = document.getElementById('red-cultural-contacto-form-container');
						var success = document.getElementById('red-cultural-contacto-success');
						if (ok && formContainer && success) {
							formContainer.style.display = 'none';
							success.style.display = 'flex';
						}

						var reloadBtn = document.getElementById('red-cultural-contacto-success-reload');
						if (reloadBtn) {
							reloadBtn.addEventListener('click', function(){
								url.searchParams.delete('rcp_contact');
								window.location.href = url.toString();
							});
						}
					})();
				</script>
			</section>
		</main>
	</div>

	<?php
	if ($rcp_theme_footer_html !== '') {
		echo '<div id="red-cultural-contacto-site-footer">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $rcp_theme_footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<?php wp_footer(); ?>
</body>
</html>
