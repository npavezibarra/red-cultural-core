<?php
/**
 * Email customization for Red Cultural Templates.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

final class RC_Templates_Emails {
	public static function init(): void {
		add_filter('retrieve_password_message', array(__CLASS__, 'custom_retrieve_password_message'), 10, 4);
		add_filter('retrieve_password_title', array(__CLASS__, 'custom_retrieve_password_title'), 10, 1);
	}

	public static function custom_retrieve_password_message(string $message, string $key, string $user_login, WP_User $user_data): string {
		$reset_url = add_query_arg(
			array(
				'key'   => $key,
				'login' => $user_login,
			),
			home_url('/restablecer-contrasena/')
		);

		$display_name = $user_data->display_name ?: $user_login;

		return self::get_branded_reset_password_email_html($display_name, $reset_url);
	}

	public static function custom_retrieve_password_title(string $title): string {
		return 'Restablece tu contraseña — Red Cultural';
	}

	private static function get_branded_reset_password_email_html(string $display_name, string $reset_url): string {
		$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Desconocida';
		$year = date('Y');

		return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Red Cultural</title>
    <style>
        body { margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f9fafb; color: #111827; -webkit-font-smoothing: antialiased; }
        table { border-collapse: collapse; width: 100%; }
        .container { max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .content { padding: 48px 40px; text-align: center; }
        .logo-container { margin-bottom: 40px; text-align: center; }
        h1 { font-size: 20px; font-weight: 600; margin-bottom: 16px; color: #111827; }
        p { font-size: 16px; line-height: 1.6; color: #4b5563; margin-bottom: 24px; }
        .button-container { margin: 32px 0; }
        .btn { background-color: #000000; color: #ffffff !important; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px; display: inline-block; }
        .footer { padding: 32px 40px; background-color: #f9fafb; text-align: center; border-top: 1px solid #f3f4f6; }
        .footer-text { font-size: 13px; color: #9ca3af; line-height: 1.5; }
        .divider { height: 1px; background-color: #e5e7eb; margin: 20px auto; width: 40px; }
        @media (max-width: 600px) { .container { margin: 0; border-radius: 0; } .content { padding: 32px 24px; } }
    </style>
</head>
<body>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center">
                <div class="container">
                    <div class="content">
                        <div class="logo-container">
                            <img src="https://red-cultural.cl/wp-content/uploads/2021/01/logoRedCulturalNegro.svg" alt="Red Cultural" style="width: 140px; height: auto; display: inline-block;">
                        </div>
                        <h1>Restablecer contraseña</h1>
                        <p>Hola <strong>{$display_name}</strong>,<br>Recibimos una solicitud para restablecer la contraseña de tu cuenta en Red Cultural.</p>
                        <div class="button-container"><a href="{$reset_url}" class="btn">Restablecer contraseña</a></div>
                        <p style="font-size: 14px;">Si no realizaste esta solicitud, puedes ignorar este correo de forma segura. Tu contraseña no cambiará hasta que accedas al enlace anterior.</p>
                    </div>
                    <div class="footer">
                        <div class="footer-text">Esta solicitud se originó desde la dirección IP: <strong>{$ip_address}</strong></div>
                        <div class="divider"></div>
                        <div class="footer-text">© {$year} Red Cultural. Todos los derechos reservados.</div>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
	}

	public static function send_welcome_email(int $user_id, string $first_name, string $email): void {
		$to = $email;
		$subject = 'Bienvenido a Red Cultural';
		$user_name = ($first_name !== '') ? $first_name : 'Amigo(a)';
		$home_url = home_url('/');

		$body = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a Red Cultural</title>
    <style>
        body { font-family: 'Inter', Helvetica, Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 0; color: #1f2937; }
        .email-container { max-width: 600px; margin: 40px auto; background-color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { padding: 48px 48px 24px 48px; text-align: center; }
        .content { padding: 0 32px 48px 32px; text-align: center; }
        .title { font-size: 30px; font-weight: 300; color: #111827; margin-bottom: 24px; line-height: 1.25; }
        .description { color: #6b7280; font-size: 18px; line-height: 1.625; margin-bottom: 40px; }
        .cta-button { display: inline-block; background-color: #000000; color: #ffffff !important; font-size: 13px !important; padding: 10px 32px; border-radius: 6px; font-weight: 500; letter-spacing: 2px; text-decoration: none; text-transform: uppercase; }
        .footer { background-color: #f9fafb; padding: 40px; text-align: center; font-size: 12px; color: #9ca3af; line-height: 2; }
    </style>
</head>
<body style="background-color: #f9fafb; margin: 0; padding: 0; font-family: 'Inter', sans-serif;">
    <div class="email-container">
        <div class="header">
            <img src="https://red-cultural.cl/wp-content/uploads/2021/01/logoRedCulturalNegro.svg" alt="Red Cultural" style="width: 130px; height: auto; display: inline-block;">
        </div>
        <div style="border-top: 1px solid #f3f4f6; margin: 0 48px;"></div>
        <div class="content">
            <h1 class="title">Confirma tu cuenta <br> <span style="font-weight: 600;">para comenzar</span></h1>
            <p class="description">Hola {$user_name},<br>Solo falta un paso para activar tu cuenta.<br><br>Haz clic en el botón para empezar a explorar contenido cultural seleccionado para ti.</p>
            <a href="{$home_url}" class="cta-button">Confirmar mi cuenta</a>
            <p style="margin-top: 32px; font-size: 13px; color: #9ca3af; line-height: 1.5;">Si no has sido tú quien se ha registrado en nuestra plataforma, puedes ignorar este correo con total tranquilidad. No se realizará ninguna acción adicional en tu cuenta.</p>
        </div>
        <div class="footer">
            <p style="margin-bottom: 16px;">Recibiste este correo porque te registraste en red-cultural.cl</p>
            <p style="font-weight: 500; letter-spacing: 0.1em; color: #6b7280; text-transform: uppercase;">RED CULTURAL &copy; 2026</p>
        </div>
    </div>
</body>
</html>
HTML;

		$headers = array('Content-Type: text/html; charset=UTF-8');
		
		if (class_exists('RC_Email_Log_Manager')) {
			RC_Email_Log_Manager::set_last_template_file(__FILE__);
		}

		wp_mail($to, $subject, $body, $headers);
	}
}
