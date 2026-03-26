<?php
/**
 * Custom Reset Password Page Template for Red Cultural
 * Intercepted by Red_Cultural_Templates::maybe_render_reset_password_template
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

// Get parameters from URL
$rp_key   = isset($_GET['key']) ? sanitize_text_field((string) $_GET['key']) : '';
$rp_login = isset($_GET['login']) ? sanitize_text_field((string) $_GET['login']) : '';

// Basic verification (WordPress core logic)
$user = null;
$error_message = '';

if ($rp_key && $rp_login) {
    $user = check_password_reset_key($rp_key, $rp_login);
    if (is_wp_error($user)) {
        $error_message = 'El enlace ha expirado o es inválido. Por favor, solicita uno nuevo.';
        $user = null;
    }
} else {
    $error_message = 'Falta información necesaria para restablecer la contraseña.';
}

$display_name = $user ? ($user->display_name ?: $user->user_login) : 'Usuario';

?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crea nueva contraseña | Red Cultural</title>
    <?php wp_head(); ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
        
        #rp-custom-page-wrapper {
            font-family: 'Inter', sans-serif;
            background-color: #fcfcfc;
            color: #000;
        }

        .font-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        }

        /* Ensure header/footer look correct */
		#red-cultural-rp-site-header, 
		#red-cultural-rp-site-footer {
			background-color: #fff;
			position: relative;
			z-index: 100;
		}
    </style>
</head>
<body id="red-cultural-reset-password-page" <?php body_class(); ?>>
    <?php if (function_exists('wp_body_open')) { wp_body_open(); } ?>

    <?php
	// Render the active block theme header
	if ($rcp_theme_header_html !== '') {
		echo '<div id="red-cultural-rp-site-header">';
		echo $rcp_theme_header_html;
		echo '</div>';
	}
	?>

    <div id="rp-custom-page-wrapper" class="min-h-[70vh] flex items-center justify-center p-4">
        
        <!-- Contenedor Principal de Formulario -->
        <div id="setup-container" class="max-w-sm w-full bg-white p-10 rounded-[6px] <?php echo $error_message ? 'hidden' : ''; ?>">
            <div class="mb-8 text-center" style="text-align: center;">
                <h1 class="text-2xl font-semibold mb-1 uppercase tracking-tight leading-none" style="text-align: center;">
                    Crea nueva contraseña <br>
                    <span class="bg-black text-white px-2 py-1 inline-block mt-3 rounded-[2px] text-sm tracking-normal">
                        <?php echo esc_html($display_name); ?>
                    </span>
                </h1>
                <p class="font-medium mt-4 text-xs">Ingresa y confirma tus nuevas credenciales.</p>
            </div>

            <form id="password-form" class="space-y-6">
                <!-- Hidden inputs for validation -->
                <input type="hidden" id="rp_key" name="rp_key" value="<?php echo esc_attr($rp_key); ?>">
                <input type="hidden" id="rp_login" name="rp_login" value="<?php echo esc_attr($rp_login); ?>">

                <!-- Campo de Contraseña -->
                <div>
                    <label class="block text-[10px] font-semibold mb-2 uppercase tracking-widest">Nueva Contraseña</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-black"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <input
                            id="password"
                            type="password"
                            required
                            class="block w-full pl-9 pr-9 py-2.5 border border-black bg-white text-black focus:bg-zinc-50 transition-all outline-none font-mono text-sm rounded-[6px]"
                            placeholder="••••••••"
                        >
                        <button
                            type="button"
                            id="toggle-password"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center"
                        >
                            <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-black"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Campo de Confirmación -->
                <div>
                    <label class="block text-[10px] font-semibold mb-2 uppercase tracking-widest">Confirmar Contraseña</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-black"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <input
                            id="confirm-password"
                            type="password"
                            required
                            class="block w-full pl-9 pr-3 py-2.5 border border-black bg-white text-black focus:bg-zinc-50 transition-all outline-none font-mono text-sm rounded-[6px]"
                            placeholder="••••••••"
                        >
                    </div>
                </div>

                <!-- Mensaje de Error (Frontend) -->
                <div id="error-message" class="hidden items-center gap-2 text-white bg-black p-3 border border-black rounded-[6px]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span id="error-text" class="text-[10px] font-semibold uppercase tracking-tight"></span>
                </div>

                <button
                    id="submit-btn"
                    type="submit"
                    class="w-full py-3.5 bg-black text-white font-semibold rounded-[6px] hover:bg-white hover:text-black border border-black transition-all uppercase tracking-[0.15em] text-xs mt-2 disabled:bg-zinc-400"
                >
                    Aceptar y Actualizar
                </button>
            </form>

            <div class="mt-10 pt-6 border-t border-black border-dotted">
                <ul class="text-[9px] font-semibold uppercase tracking-widest space-y-1.5 opacity-70 text-center" style="text-align: center;">
                    <li>[mínimo 8 caracteres]</li>
                    <li>[combinación alfa-numérica]</li>
                </ul>
            </div>
        </div>

        <!-- Contenedor de Error Crítico (Link Inválido) -->
        <?php if ($error_message) : ?>
        <div id="critical-error" class="max-w-sm w-full bg-white p-10 text-center rounded-[6px]" style="text-align: center;">
            <div class="w-16 h-16 bg-red-100 flex items-center justify-center mx-auto mb-6 rounded-[6px]" style="margin: 0 auto 24px auto;">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <h2 class="text-xl font-semibold text-black mb-2 uppercase tracking-tight">Error</h2>
            <p class="text-sm text-black leading-relaxed">
                <?php echo esc_html($error_message); ?>
            </p>
            <a 
                href="<?php echo esc_url(home_url()); ?>"
                class="mt-8 block w-full py-3 bg-black text-white font-semibold rounded-[6px] hover:bg-zinc-800 transition-colors uppercase tracking-widest text-xs border border-black"
            >
                Volver al inicio
            </a>
        </div>
        <?php endif; ?>

        <!-- Contenedor de Éxito (Oculto inicialmente) -->
        <div id="success-container" class="hidden max-w-sm w-full bg-white p-10 text-center rounded-[6px]" style="text-align: center;">
            <div class="w-16 h-16 bg-black flex items-center justify-center mx-auto mb-6 rounded-[6px]" style="margin: 0 auto 24px auto;">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h2 class="text-xl font-semibold text-black mb-2 uppercase tracking-tight">Éxito</h2>
            <p class="text-sm text-black leading-relaxed">
                Tu nueva contraseña ha sido establecida para <span class="font-semibold underline"><?php echo esc_html($display_name); ?></span>.
            </p>
            <a 
                href="<?php echo esc_url(home_url()); ?>"
                class="mt-8 block w-full py-3 bg-black text-white font-semibold rounded-[6px] hover:bg-zinc-800 transition-colors uppercase tracking-widest text-xs border border-black"
            >
                Ir al inicio
            </a>
        </div>

    </div>

    <?php
	// Render the active block theme footer
	if ($rcp_theme_footer_html !== '') {
		echo '<div id="red-cultural-rp-site-footer">';
		echo $rcp_theme_footer_html;
		echo '</div>';
	}
	?>

    <script>
        const form = document.getElementById('password-form');
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm-password');
        const errorContainer = document.getElementById('error-message');
        const errorText = document.getElementById('error-text');
        const toggleBtn = document.getElementById('toggle-password');
        const eyeIcon = document.getElementById('eye-icon');
        const submitBtn = document.getElementById('submit-btn');
        
        const setupContainer = document.getElementById('setup-container');
        const successContainer = document.getElementById('success-container');

        // Alternar visibilidad de contraseña
        let showPassword = false;
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                showPassword = !showPassword;
                const type = showPassword ? 'text' : 'password';
                passwordInput.type = type;
                confirmInput.type = type;
                
                if (showPassword) {
                    eyeIcon.innerHTML = '<path d="M9.88 9.88L14.12 14.12"/><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/><line x1="2" y1="2" x2="22" y2="22"/>';
                } else {
                    eyeIcon.innerHTML = '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>';
                }
            });
        }

        // Manejar envío del formulario
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                
                const passValue = passwordInput.value;
                const confirmValue = confirmInput.value;
                const rp_key = document.getElementById('rp_key').value;
                const rp_login = document.getElementById('rp_login').value;

                errorContainer.classList.add('hidden');
                errorContainer.classList.remove('flex');

                if (passValue.length < 8) {
                    showError('La contraseña debe tener al menos 8 caracteres.');
                    return;
                }

                if (passValue !== confirmValue) {
                    showError('Las contraseñas no coinciden.');
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.textContent = 'Procesando...';

                // Submit via AJAX to WordPress
                const formData = new URLSearchParams();
                formData.append('action', 'rcp_reset_password');
                formData.append('key', rp_key);
                formData.append('login', rp_login);
                formData.append('password', passValue);

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        setupContainer.classList.add('hidden');
                        successContainer.classList.remove('hidden');
                    } else {
                        showError(res.data.message || 'Error al restablecer la contraseña.');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Aceptar y Actualizar';
                    }
                })
                .catch(err => {
                    console.error('AJAX Error:', err);
                    showError('Error de conexión. Inténtalo de nuevo.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Aceptar y Actualizar';
                });
            });
        }

        function showError(msg) {
            errorText.textContent = msg;
            errorContainer.classList.remove('hidden');
            errorContainer.classList.add('flex');
        }
    </script>
    <?php wp_footer(); ?>
</body>
</html>
