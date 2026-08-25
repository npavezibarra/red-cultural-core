<?php
/**
 * Interest form email template for Viaje Toscana y Roma.
 *
 * @var string $trip_name
 * @var string $trip_dates
 * @var string $name
 * @var string $email
 * @var string $phone
 * @var string $message
 */

if (!defined('ABSPATH')) {
	exit;
}

$trip_name = isset($trip_name) ? (string) $trip_name : 'Toscana y Roma';
$trip_dates = isset($trip_dates) ? (string) $trip_dates : '08 al 22 de marzo de 2027';
$name = isset($name) ? (string) $name : '';
$email = isset($email) ? (string) $email : '';
$phone = isset($phone) ? (string) $phone : '';
$message = isset($message) ? (string) $message : '';
$hero_image = 'https://red-cultural.cl/wp-content/uploads/2026/08/toscanaroma.png';
$year = date('Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Viaje Toscana y Roma - Nuevo interés</title>
	<style>
		body {
			margin: 0;
			padding: 0;
			background: #f3f4f6;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
			color: #111827;
		}
		.wrapper {
			max-width: 640px;
			margin: 0 auto;
			padding: 28px 16px;
		}
		.card {
			background: #ffffff;
			border-radius: 18px;
			overflow: hidden;
			border: 1px solid #e5e7eb;
			box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
		}
		.hero {
			background:
				linear-gradient(180deg, rgba(10, 10, 10, 0.78), rgba(10, 10, 10, 0.78)),
				url('<?php echo esc_url($hero_image); ?>');
			background-size: cover;
			background-position: center center;
			background-repeat: no-repeat;
			padding: 34px 32px 28px;
			text-align: center;
			color: #ffffff;
		}
		.logo {
			width: 140px;
			height: auto;
			display: inline-block;
			margin-bottom: 22px;
		}
		.kicker {
			margin: 0 0 10px;
			font-size: 10px;
			font-weight: 800;
			letter-spacing: 0.3em;
			text-transform: uppercase;
			color: rgba(255, 255, 255, 0.68);
		}
		.title {
			margin: 0;
			font-size: 30px;
			line-height: 1.1;
			font-weight: 800;
			letter-spacing: -0.03em;
		}
		.subtitle {
			margin: 10px 0 0;
			font-size: 16px;
			line-height: 1.5;
			color: rgba(255, 255, 255, 0.8);
		}
		.content {
			padding: 28px 32px 10px;
		}
		.section-title {
			margin: 0 0 12px;
			font-size: 12px;
			font-weight: 800;
			letter-spacing: 0.24em;
			text-transform: uppercase;
			color: #6b7280;
		}
		.field-grid {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 14px;
		}
		.field {
			background: #f9fafb;
			border: 1px solid #e5e7eb;
			border-radius: 14px;
			padding: 14px 16px;
			margin-bottom: 10px;
		}
		.field-label {
			display: block;
			margin: 0 0 6px;
			font-size: 10px;
			font-weight: 800;
			letter-spacing: 0.18em;
			text-transform: uppercase;
			color: #9ca3af;
		}
		.field-value {
			margin: 0;
			font-size: 15px;
			line-height: 1.5;
			color: #111827;
			word-break: break-word;
		}
		.message-box {
			margin-top: 16px;
			background: #f9fafb;
			border: 1px solid #e5e7eb;
			border-radius: 14px;
			padding: 16px;
		}
		.message-copy {
			margin: 0;
			font-size: 15px;
			line-height: 1.6;
			color: #111827;
			white-space: pre-wrap;
		}
		.footer {
			padding: 20px 32px 32px;
			text-align: center;
			font-size: 12px;
			color: #9ca3af;
		}
		@media (max-width: 560px) {
			.wrapper {
				padding: 0;
			}
			.card {
				border-radius: 0;
				border-left: 0;
				border-right: 0;
			}
			.hero,
			.content,
			.footer {
				padding-left: 20px;
				padding-right: 20px;
			}
			.field-grid {
				grid-template-columns: 1fr;
			}
			.title {
				font-size: 26px;
			}
		}
	</style>
</head>
<body>
	<div class="wrapper">
		<div class="card">
			<div class="hero">
				<img class="logo" src="https://red-cultural.cl/wp-content/uploads/2021/01/logoRedCulturalBlanco.svg" alt="Red Cultural">
				<p class="kicker">Nuevo interés</p>
				<h1 class="title"><?php echo esc_html($trip_name); ?></h1>
				<p class="subtitle"><?php echo esc_html($trip_dates); ?></p>
			</div>

			<div class="content">
				<p class="section-title">Datos del formulario</p>

				<div class="field-grid">
					<div class="field">
						<span class="field-label">Nombre</span>
						<p class="field-value"><?php echo esc_html($name !== '' ? $name : 'Sin nombre'); ?></p>
					</div>
					<div class="field">
						<span class="field-label">Email</span>
						<p class="field-value"><?php echo esc_html($email !== '' ? $email : 'Sin email'); ?></p>
					</div>
					<div class="field">
						<span class="field-label">Teléfono</span>
						<p class="field-value"><?php echo esc_html($phone !== '' ? $phone : 'Sin teléfono'); ?></p>
					</div>
					<div class="field">
						<span class="field-label">Viaje</span>
						<p class="field-value"><?php echo esc_html($trip_name); ?></p>
					</div>
				</div>

				<div class="message-box">
					<span class="field-label">Mensaje</span>
					<p class="message-copy"><?php echo esc_html($message !== '' ? $message : 'Sin mensaje'); ?></p>
				</div>
			</div>

			<div class="footer">
				Red Cultural · <?php echo esc_html($year); ?>
			</div>
		</div>
	</div>
</body>
</html>
