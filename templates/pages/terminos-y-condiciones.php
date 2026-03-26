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

?><!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html((string) wp_get_document_title()); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            color: #1a1a1a;
        }
        .prose h2 {
            margin-top: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
            font-size: 1.5rem;
            color: #111;
            border-bottom: 1px solid #eee;
            padding-bottom: 0.5rem;
        }
        .prose p {
            margin-bottom: 1.25rem;
            line-height: 1.75;
            color: #4a4a4a;
        }
        .prose ul {
            list-style-type: disc;
            padding-left: 1.5rem;
            margin-bottom: 1.25rem;
        }
        .prose li {
            margin-bottom: 0.5rem;
            color: #4a4a4a;
        }
        .nav-link:hover {
            color: #000;
            padding-left: 4px;
        }
        .nav-link {
            transition: all 0.2s ease;
        }
        .warning-box {
            background-color: #f9fafb;
            border-left: 4px solid #111;
            padding: 1.5rem;
            margin: 2rem 0;
        }
    </style>
    <?php wp_head(); ?>
</head>
<body class="bg-gray-50/50" <?php body_class(); ?>>
    <?php if (function_exists('wp_body_open')) { wp_body_open(); } ?>

    <?php
    // Render the active block theme header so navbar matches the rest of the site.
    if ($rcp_theme_header_html !== '') {
        echo $rcp_theme_header_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
    ?>

    <main class="max-w-5xl mx-auto px-6 py-12 md:py-24">
        <div class="flex flex-col md:flex-row gap-12">
            
            <!-- Sidebar Navigation -->
            <aside class="md:w-1/4 hidden md:block">
                <div class="sticky top-12">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6">Contenido</h4>
                    <ul class="space-y-4 text-sm text-gray-500">
                        <li><a href="#intro" class="nav-link block">1. Introducción</a></li>
                        <li><a href="#objeto" class="nav-link block">2. Objeto</a></li>
                        <li><a href="#servicios" class="nav-link block">3. Servicios</a></li>
                        <li><a href="#acceso" class="nav-link block">4. Acceso y Uso</a></li>
                        <li><a href="#prohibido" class="nav-link block">4.1 Conductas Prohibidas</a></li>
                        <li><a href="#privacidad" class="nav-link block">5. Privacidad</a></li>
                        <li><a href="#propiedad" class="nav-link block">6. Propiedad Intelectual</a></li>
                        <li><a href="#pagos" class="nav-link block">9. Pagos</a></li>
                        <li><a href="#contacto" class="nav-link block">Contacto</a></li>
                    </ul>
                </div>
            </aside>

            <!-- Document Content -->
            <article class="md:w-3/4 prose max-w-none">
                <h1 class="text-4xl md:text-5xl font-extrabold mb-4 tracking-tight">Términos y Condiciones</h1>
                <p class="text-gray-400 text-sm mb-12">Última actualización: Agosto 2020</p>

                <div class="warning-box">
                    <p class="font-semibold text-black uppercase text-sm mb-2">Aviso Importante</p>
                    <p class="text-sm m-0">LEE ATENTAMENTE ESTOS TÉRMINOS Y CONDICIONES DE USO. EN CASO DE NO COMPRENDERLOS O NO ACEPTARLOS, TE ABSTENGAS DE USAR EL SITIO WEB. RED CULTURAL NO ESTÁ DISPONIBLE PARA MENORES DE 13 AÑOS.</p>
                </div>

                <section id="intro">
                    <h2>1. Introducción y Definiciones</h2>
                    <p>Red Cultural es una plataforma online de conexión entre usuarios que cuenta con herramientas de interacción, a fin de distribuir contenido de carácter educativo.</p>
                    <p><strong>Tipos de usuario:</strong></p>
                    <ul>
                        <li><strong>Usuario Estudiante:</strong> Busca ayuda académica y utiliza las herramientas del sitio.</li>
                        <li><strong>Usuario Profesor:</strong> Persona conocedora de contenidos específicos que imparte conocimiento a través de videos, cursos y actividades interactivas.</li>
                    </ul>
                </section>

                <section id="objeto">
                    <h2>2. Objeto</h2>
                    <p>Red Cultural otorga a los Usuarios acceso a diversos contenidos y herramientas interactivas, con el fin de facilitar la distribución de contenidos y ejercicio de tutorías educativos con el apoyo de Profesores.</p>
                </section>

                <section id="servicios">
                    <h2>3. Servicios</h2>
                    <p>Los Servicios ofrecidos a través del Sitio Web sólo podrán ser contratados por personas mayores de 18 años. Para el caso de menores de edad, serán sus Representantes quienes deberán contratar los Servicios a su nombre.</p>
                </section>

                <section id="acceso">
                    <h2>4. Condiciones de Acceso y Uso</h2>
                    <p>Los Usuarios deberán registrarse completando un formulario con datos veraces y mantener la confidencialidad de su clave de acceso. El titular de la cuenta será responsable de toda conducta derivada de su uso por un tercero.</p>
                </section>

                <section id="prohibido">
                    <h2>4.1 Conductas Prohibidas</h2>
                    <p>Se detallan algunas conductas prohibidas en el uso del Sitio Web:</p>
                    <ul>
                        <li>Utilizar el sitio con propósitos ilegales.</li>
                        <li>Alquilar, vender o distribuir las licencias otorgadas por Red Cultural.</li>
                        <li>Subir material difamatorio, indecente u ofensivo.</li>
                        <li>Hacerse pasar por otra persona o entidad.</li>
                        <li>Realizar ofertas no solicitadas o Spam.</li>
                        <li>Interferir con funciones relacionadas con la seguridad del sitio.</li>
                    </ul>
                </section>

                <section id="privacidad">
                    <h2>5. Privacidad</h2>
                    <p>El Usuario autoriza expresamente a Red Cultural a utilizar la información entregada al momento de registrarse, según los términos descritos en la "Política de Privacidad".</p>
                </section>

                <section id="propiedad">
                    <h2>6. Propiedad Intelectual</h2>
                    <p>El Sitio Web, sus gráficos, logos, textos, videos y software son propiedad de Red Cultural y están protegidos por derechos de autor y propiedad intelectual. Se prohíbe la reproducción o uso no autorizado de estos contenidos.</p>
                </section>

                <section id="pagos">
                    <h2>9. Formas y Condiciones de Pago</h2>
                    <p>El pago se realiza directamente en la plataforma a través del servicio "Payku", que sirve de enlace con el sistema Transbank para tarjetas de crédito o débito emitidas en Chile.</p>
                </section>

                <section id="contacto" class="bg-gray-100 p-8 rounded-2xl mt-16">
                    <h2 class="border-none mt-0">Contacto y Soporte</h2>
                    <p>Para reclamos o dudas sobre los servicios adquiridos, por favor contáctanos:</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div class="bg-white p-4 rounded-lg shadow-sm">
                            <p class="text-xs text-gray-400 uppercase font-bold mb-1">General</p>
                            <a href="mailto:magdalena@redcultural.cl" class="text-black font-medium hover:underline">magdalena@redcultural.cl</a>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm">
                            <p class="text-xs text-gray-400 uppercase font-bold mb-1">Pagos</p>
                            <a href="mailto:magdalena@redcultural.cl" class="text-black font-medium hover:underline">magdalena@redcultural.cl</a>
                        </div>
                    </div>
                </section>
            </article>
        </div>
    </main>

    <?php
    if ($rcp_theme_footer_html !== '') {
        echo $rcp_theme_footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
    ?>

    <!-- Back to top button -->
    <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="fixed bottom-8 right-8 bg-black text-white p-3 rounded-full shadow-lg hover:scale-110 transition-transform">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    </button>

    <?php wp_footer(); ?>
</body>
</html>
