<?php
/**
 * Auth Modal UI for Red Cultural Templates.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

final class RC_Templates_Auth_Modal {
	public static function init(): void {
		add_action('wp_footer', array(__CLASS__, 'render_site_auth_modal'), 20);
	}

	public static function render_site_auth_modal(): void {
		if (is_admin() || is_user_logged_in()) {
			return;
		}

		$admin_post = admin_url('admin-post.php');
		$auth_nonce = wp_create_nonce('rcp_checkout_auth');
		$exists_nonce = wp_create_nonce('rcp_user_exists');
		$redirect_to = (string) home_url(add_query_arg(array(), (string) wp_unslash($_SERVER['REQUEST_URI'] ?? '/')));
		$as_settings = RC_Anti_Spam::get_settings();
		$as_provider = $as_settings['provider'];
		$as_site_key = $as_settings['site_key'];

		?>
		<style>
			#red-cultural-login-overlay{font-family:'Inter',sans-serif}
			#red-cultural-login-overlay .auth-card{transition:all .3s cubic-bezier(.4,0,.2,1);border-radius:9px !important}
			#red-cultural-login-overlay .focus-gold:focus{border-color:#c5a367 !important;box-shadow:0 0 0 2px rgba(197,163,103,.15) !important}
			#red-cultural-login-overlay .rounded-3px{border-radius:3px !important}
			#red-cultural-login-overlay label.block.text-\[10px\].font-bold.text-gray-400.uppercase.tracking-widest.mb-1{font-size:14px;color:#000}
			#red-cultural-login-overlay button#red-cultural-login-submit{font-size:14px;border-radius:6px;letter-spacing:3px;font-size:13px;font-weight:600;border-radius:6px !important}
			#red-cultural-login-overlay p#red-cultural-login-subtitle{font-size:14px}
			#red-cultural-login-overlay p#red-cultural-login-toggle-text{font-size:14px}
			.rc-hp-wrap{position:absolute;left:-9999px;top:-9999px;opacity:0;pointer-events:none;height:0;width:0;overflow:hidden}
		</style>
		<div id="red-cultural-login-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center opacity-0 invisible transition-all duration-300 p-4" data-admin-post="<?php echo esc_attr((string) $admin_post); ?>" data-ajax-url="<?php echo esc_attr((string) admin_url('admin-ajax.php')); ?>" data-nonce="<?php echo esc_attr((string) $auth_nonce); ?>" data-exists-nonce="<?php echo esc_attr((string) $exists_nonce); ?>" data-redirect="<?php echo esc_attr((string) $redirect_to); ?>" data-as-provider="<?php echo esc_attr($as_provider); ?>" data-as-sitekey="<?php echo esc_attr($as_site_key); ?>" role="dialog" aria-modal="true" aria-label="Red Cultural - Acceso">
			<div id="red-cultural-login-card" class="bg-white w-full max-w-sm shadow-2xl overflow-hidden relative auth-card scale-95 transform">
				<button id="red-cultural-login-close" class="absolute top-3 right-3 text-gray-400 hover:text-black transition p-1" type="button" aria-label="Cerrar">
					<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
				</button>
				<div id="red-cultural-login-inner" class="p-8">
					<div id="red-cultural-login-header" class="text-center mb-8">
						<h2 id="red-cultural-login-brand" class="text-2xl font-bold text-black tracking-tight mb-1">Red Cultural</h2>
						<p id="red-cultural-login-subtitle" class="text-gray-500 text-xs">Bienvenido de nuevo. Por favor, inicia sesión.</p>
					</div>
					<form id="red-cultural-login-form" class="space-y-4">
						<div class="rc-hp-wrap" aria-hidden="true">
							<label for="red-cultural-login-hp">Ignore this field</label>
							<input id="red-cultural-login-hp" type="text" name="_rc_hp_check" tabindex="-1" autocomplete="new-password">
							<input type="hidden" name="_rc_form_ts" id="red-cultural-login-ts" value="<?php echo time(); ?>">
						</div>
						<div id="red-cultural-login-register-fields" class="hidden space-y-4">
							<div id="red-cultural-login-register-grid" class="grid grid-cols-2 gap-3">
								<div>
									<label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1" for="red-cultural-login-first-name">Nombre</label>
									<input id="red-cultural-login-first-name" type="text" placeholder="Ej. Ana" autocomplete="given-name" class="w-full px-3 py-2 text-sm rounded-3px border border-gray-200 bg-gray-50 focus:bg-white focus-gold outline-none transition duration-200" />
								</div>
								<div>
									<label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1" for="red-cultural-login-last-name">Apellido</label>
									<input id="red-cultural-login-last-name" type="text" placeholder="Ej. García" autocomplete="family-name" class="w-full px-3 py-2 text-sm rounded-3px border border-gray-200 bg-gray-50 focus:bg-white focus-gold outline-none transition duration-200" />
								</div>
							</div>
						</div>
						<div>
							<label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1" for="red-cultural-login-email">Correo Electrónico</label>
							<input id="red-cultural-login-email" type="email" placeholder="correo@ejemplo.com" autocomplete="email" class="w-full px-3 py-2 text-sm rounded-3px border border-gray-200 bg-gray-50 focus:bg-white focus-gold outline-none transition duration-200" />
							<p id="red-cultural-login-forgot-status" class="mt-2 text-[12px] font-semibold hidden"></p>
						</div>
						<div id="red-cultural-login-email-confirm-wrap" class="hidden space-y-1">
							<label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1" for="red-cultural-login-email-confirm">Confirmar Correo Electrónico</label>
							<input id="red-cultural-login-email-confirm" type="email" placeholder="correo@ejemplo.com" autocomplete="email" class="w-full px-3 py-2 text-sm rounded-3px border border-gray-200 bg-gray-50 focus:bg-white focus-gold outline-none transition duration-200" />
						</div>
						<div id="red-cultural-login-password-wrap">
							<label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1" for="red-cultural-login-password">Contraseña</label>
							<input id="red-cultural-login-password" type="password" placeholder="••••••••" autocomplete="current-password" class="w-full px-3 py-2 text-sm rounded-3px border border-gray-200 bg-gray-50 focus:bg-white focus-gold outline-none transition duration-200" />
						</div>
						<div id="red-cultural-login-extras" class="flex items-center justify-between text-[11px]">
							<label class="flex items-center text-gray-500 cursor-pointer"><input id="red-cultural-login-remember" type="checkbox" class="mr-1.5 rounded-sm border-gray-300 text-[#c5a367] focus:ring-[#c5a367]" />Recordarme</label>
							<button id="red-cultural-login-forgot" type="button" class="text-[#c5a367] hover:brightness-90 font-medium transition">¿Olvidaste tu contraseña?</button>
						</div>
						<?php if ($as_provider === 'turnstile') : ?><div class="rcp-captcha-container py-2 flex justify-center"><?php RC_Anti_Spam::render_widget(); ?></div><?php endif; ?>
						<button id="red-cultural-login-submit" type="submit" class="w-full py-3 bg-black text-white rounded-3px font-bold hover:bg-zinc-800 transform active:scale-[0.99] transition-all duration-200 shadow-md tracking-widest uppercase text-[10px]">Iniciar Sesión</button>
						<div id="red-cultural-login-forgot-back-wrap" class="hidden mt-5 pt-4 border-t border-gray-100 text-center"><p class="text-gray-500 text-xs"><button id="red-cultural-login-forgot-back" type="button" class="text-[#c5a367] font-bold hover:brightness-90 transition">Volver a iniciar sesión</button></p></div>
					</form>
					<div id="red-cultural-login-footer" class="mt-8 pt-5 border-t border-gray-100 text-center"><p id="red-cultural-login-toggle-text" class="text-gray-500 text-xs">¿Eres nuevo en Red Cultural? <button id="red-cultural-login-toggle" type="button" class="text-[#c5a367] font-bold hover:brightness-90 transition ml-1">Crea una cuenta</button></p></div>
				</div>
			</div>
		</div>
		<script>
			(function () {
				var overlay = document.getElementById('red-cultural-login-overlay');
				if (!overlay) return;
				var card = overlay.querySelector('.auth-card'), closeBtn = document.getElementById('red-cultural-login-close'), form = document.getElementById('red-cultural-login-form'), subtitle = document.getElementById('red-cultural-login-subtitle'), toggle = document.getElementById('red-cultural-login-toggle'), toggleText = document.getElementById('red-cultural-login-toggle-text'), registerFields = document.getElementById('red-cultural-login-register-fields'), emailInput = document.getElementById('red-cultural-login-email'), passwordWrap = document.getElementById('red-cultural-login-password-wrap'), loginExtras = document.getElementById('red-cultural-login-extras'), submitBtn = document.getElementById('red-cultural-login-submit'), forgotBtn = document.getElementById('red-cultural-login-forgot'), forgotStatus = document.getElementById('red-cultural-login-forgot-status'), emailConfirmWrap = document.getElementById('red-cultural-login-email-confirm-wrap'), emailConfirmInput = document.getElementById('red-cultural-login-email-confirm'), forgotBackWrap = document.getElementById('red-cultural-login-forgot-back-wrap'), forgotBackBtn = document.getElementById('red-cultural-login-forgot-back'), footer = document.getElementById('red-cultural-login-footer');
				var currentView = 'login', forgotExists = null, forgotCheckTimer = null;
				function emailsMatch() { var main = String(emailInput?.value || '').trim(), confirm = String(emailConfirmInput?.value || '').trim(); if (main === '' || confirm === '') return true; return main.toLowerCase() === confirm.toLowerCase(); }
				function handleConfirmInput() { if (emailsMatch()) resetStatus(); }
				if (emailInput) emailInput.addEventListener('input', handleConfirmInput);
				if (emailConfirmInput) emailConfirmInput.addEventListener('input', handleConfirmInput);
				function openOverlay(v) { overlay.classList.remove('opacity-0', 'invisible'); overlay.classList.add('opacity-100', 'visible'); if (card) card.classList.replace('scale-95', 'scale-100'); setView(v || 'login'); }
				function closeOverlay() { overlay.classList.replace('opacity-100', 'opacity-0'); if (card) card.classList.replace('scale-100', 'scale-95'); setTimeout(function () { overlay.classList.replace('visible', 'invisible'); }, 300); }
				function resetStatus() { if (!forgotStatus) return; forgotStatus.classList.add('hidden'); forgotStatus.classList.remove('text-red-600', 'text-emerald-600'); forgotStatus.textContent = ''; forgotExists = null; }
				function setStatusError(m) { if (!forgotStatus) return; forgotStatus.textContent = m; forgotStatus.classList.remove('hidden'); forgotStatus.classList.add('text-red-600', 'text-emerald-600'); }
				function setStatusSuccess(m) { if (!forgotStatus) return; forgotStatus.textContent = m; forgotStatus.classList.remove('hidden', 'text-red-600'); forgotStatus.classList.add('text-emerald-600'); }
				function looksLikeEmail(v) { var s = String(v || '').trim(); return s.length >= 5 && s.indexOf('@') > 0 && s.indexOf('.') > 0; }
				function checkForgotEmailExistsDebounced() {
					if (currentView !== 'forgot') return;
					var val = String(emailInput?.value || '').trim();
					if (!looksLikeEmail(val)) { resetStatus(); return; }
					if (forgotCheckTimer) window.clearTimeout(forgotCheckTimer);
					forgotCheckTimer = window.setTimeout(function () {
						var ajaxUrl = overlay.getAttribute('data-ajax-url'), nonce = overlay.getAttribute('data-exists-nonce');
						fetch(ajaxUrl, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams({ action: 'rcp_user_exists', nonce: nonce, user_login: val }).toString() })
						.then(r => r.json()).then(json => { var ex = !!(json?.success && json?.data?.exists); forgotExists = ex; if (!ex) setStatusError('Esta cuenta no existe en nuestro sistema'); else resetStatus(); });
					}, 250);
				}
				function setView(v) {
					currentView = v; resetStatus();
					var isL = v === 'login', isR = v === 'register', isF = v === 'forgot';
					if (registerFields) registerFields.classList.toggle('hidden', !isR);
					if (passwordWrap) passwordWrap.classList.toggle('hidden', isF);
					if (loginExtras) loginExtras.classList.toggle('hidden', !isL);
					if (footer) footer.classList.toggle('hidden', isF);
					if (forgotBackWrap) forgotBackWrap.classList.toggle('hidden', !isF);
					if (emailConfirmWrap) emailConfirmWrap.classList.toggle('hidden', !isR);
					if (subtitle) {
						if (isL) subtitle.innerText = 'Bienvenido de nuevo. Por favor, inicia sesión.';
						if (isR) subtitle.innerText = 'Únete a nuestra comunidad cultural hoy.';
						if (isF) subtitle.innerText = 'Ingresa el correo asociado a tu cuenta.';
					}
					if (submitBtn) {
						submitBtn.disabled = false;
						if (isL) submitBtn.innerText = 'Iniciar Sesión';
						if (isR) submitBtn.innerText = 'Crear Cuenta';
						if (isF) submitBtn.innerText = 'Enviar correo';
					}
					if (isF && emailInput) { emailInput.addEventListener('input', checkForgotEmailExistsDebounced); checkForgotEmailExistsDebounced(); }
					if (toggleText) {
						if (isL) toggleText.innerHTML = '¿Eres nuevo en Red Cultural? <button id="red-cultural-login-toggle" type="button" class="text-[#c5a367] font-bold hover:brightness-90 transition ml-1">Crea una cuenta</button>';
						else if (isR) toggleText.innerHTML = '¿Ya tienes una cuenta? <button id="red-cultural-login-toggle" type="button" class="text-[#c5a367] font-bold hover:brightness-90 transition ml-1">Inicia sesión</button>';
						toggle = document.getElementById('red-cultural-login-toggle');
						if (toggle) toggle.addEventListener('click', function () { setView(isL ? 'register' : 'login'); });
					}
				}
				function inferAuthViewFromHref(h) { var l = String(h || '').toLowerCase(); if (l.indexOf('register') !== -1 || l.indexOf('signup') !== -1) return 'register'; return 'login'; }
				document.addEventListener('click', function (e) {
					var t = e.target; if (!(t instanceof Element)) return;
					var op = t.closest('[data-rcp-auth-open]'); if (op) { e.preventDefault(); openOverlay('login'); return; }
					var link = t.closest('a');
					if (link) {
						var hr = link.getAttribute('href') || '';
						if ((hr.indexOf('wp-login.php') !== -1 || hr.indexOf('wp-register.php') !== -1) && link.getAttribute('data-no-modal') !== '1') {
							e.preventDefault(); openOverlay(inferAuthViewFromHref(hr));
						}
					}
				});
				if (closeBtn) closeBtn.addEventListener('click', closeOverlay);
				overlay.addEventListener('click', function (e) { if (e.target === overlay) closeOverlay(); });
				function submitAuth(m, p) {
					var au = overlay.getAttribute('data-admin-post'), n = overlay.getAttribute('data-nonce'), re = overlay.getAttribute('data-redirect');
					var f = document.createElement('form'); f.method = 'POST'; f.action = au;
					var add = (k, v) => { var i = document.createElement('input'); i.type = 'hidden'; i.name = k; i.value = v; f.appendChild(i); };
					add('action', 'rcp_checkout_auth'); add('rcp_nonce', n); add('redirect_to', re); add('mode', m);
					Object.keys(p || {}).forEach(k => add(k, p[k]));
					document.body.appendChild(f); f.submit();
				}
				function forgotPassword(em) {
					var aj = overlay.getAttribute('data-ajax-url'), n = overlay.getAttribute('data-nonce');
					resetStatus(); if (!forgotStatus) return;
					var cl = String(em || '').trim(); if (!cl) { setStatusError('Ingresa tu correo.'); return; }
					if (forgotExists === false) { setStatusError('Esta cuenta no existe en nuestro sistema'); return; }
					if (submitBtn) submitBtn.disabled = true;
					fetch(aj, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams({ action: 'rcp_forgot_password', rcp_nonce: n, email: cl }).toString() })
					.then(r => r.json()).then(json => {
						if (!json?.success) { setStatusError(json?.data?.message || 'Error'); if (submitBtn) submitBtn.disabled = false; return; }
						setStatusSuccess(json.data.message); if (submitBtn) submitBtn.innerText = 'Enviado';
					}).catch(() => { setStatusError('Error'); if (submitBtn) submitBtn.disabled = false; });
				}
				if (form) {
					form.addEventListener('submit', function (e) {
						e.preventDefault();
						var em = document.getElementById('red-cultural-login-email'), ps = document.getElementById('red-cultural-login-password'), rm = document.getElementById('red-cultural-login-remember'), fn = document.getElementById('red-cultural-login-first-name'), ln = document.getElementById('red-cultural-login-last-name'), hp = document.getElementById('red-cultural-login-hp'), ts = document.getElementById('red-cultural-login-ts');
						if (currentView === 'forgot') { checkForgotEmailExistsDebounced(); forgotPassword(em ? em.value : ''); return; }
						var pay = { user_login: em?.value || '', password: ps?.value || '', remember: rm?.checked ? '1' : '', _rc_hp_check: hp?.value || '', _rc_form_ts: ts?.value || '' };
						if (currentView === 'register') { if (!emailsMatch()) { setStatusError('Correos no coinciden'); return; } pay.first_name = fn?.value || ''; pay.last_name = ln?.value || ''; pay.email = em?.value || ''; }
						var pr = overlay.getAttribute('data-as-provider'), sk = overlay.getAttribute('data-as-sitekey');
						if (submitBtn) submitBtn.disabled = true;
						if (pr === 'recaptcha' && window.grecaptcha) { grecaptcha.ready(() => grecaptcha.execute(sk, { action: 'submit' }).then(tok => { pay.captcha_token = tok; submitAuth(currentView, pay); })); }
						else if (pr === 'turnstile' && window.turnstile) { var tok = turnstile.getResponse(); if (!tok) { setStatusError('Completa el captcha'); if (submitBtn) submitBtn.disabled = false; return; } pay.captcha_token = tok; submitAuth(currentView, pay); }
						else submitAuth(currentView, pay);
					});
				}
				if (forgotBtn) forgotBtn.addEventListener('click', () => setView('forgot'));
				if (forgotBackBtn) forgotBackBtn.addEventListener('click', () => setView('login'));
			})();
		</script>
		<?php
	}
}
