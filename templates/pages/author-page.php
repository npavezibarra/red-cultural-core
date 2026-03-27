<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

$author = get_queried_object();
if (!($author instanceof \WP_User)) {
	$author_id = (int) get_the_author_meta('ID');
	$author = get_userdata($author_id);
}

if (!($author instanceof \WP_User)) {
    return;
}

$author_id = $author->ID;
$author_name = (string) $author->display_name;
$author_bio = (string) $author->description;
$custom_avatar = get_user_meta($author_id, 'rc_profile_photo', true);
$author_avatar = $custom_avatar ? $custom_avatar : (string) get_avatar_url($author_id, ['size' => 400]);

// Try to get occupation/job title from meta, fallback to placeholder
$author_job = (string) get_user_meta($author_id, 'occupation', true);
if ($author_job === '') {
    $author_job = (string) get_user_meta($author_id, 'job_title', true);
}
if ($author_job === '') {
    $author_job = 'Profesor Red Cultural';
}

// Social links
$twitter_url = (string) get_user_meta($author_id, 'twitter', true);
$linkedin_url = (string) get_user_meta($author_id, 'linkedin', true);

// Writings Query
$writings_q = new \WP_Query([
    'post_type' => 'post',
    'author' => $author_id,
    'posts_per_page' => 12,
    'post_status' => 'publish',
]);

// Courses Query
$courses_q = new \WP_Query([
    'post_type' => 'sfwd-courses',
    'author' => $author_id,
    'posts_per_page' => -1,
    'post_status' => 'publish',
]);

// Block theme header/footer parts
$rcp_theme_header_html = '';
$rcp_theme_footer_html = '';
if (function_exists('do_blocks')) {
	$rcp_theme_header_html = (string) do_blocks('<!-- wp:template-part {"slug":"header","area":"header"} /-->');
	$rcp_theme_footer_html = (string) do_blocks('<!-- wp:template-part {"slug":"footer","area":"footer"} /-->');
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($author_name); ?> | <?php bloginfo('name'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            color: #000000;
            -webkit-font-smoothing: antialiased;
        }
        .profile-image {
            filter: grayscale(100%);
        }
        .card-hover {
            transition: all 0.2s ease;
        }
        .card-hover:hover {
            background-color: #fafafa;
            border-color: #000;
        }
        .tab-active {
            color: #000;
            border-bottom: 2px solid #000;
        }
        .tab-inactive {
            color: #a1a1aa; /* zinc-400 */
        }
        #author-page-wrapper { 
            min-height: 60vh; 
            max-width: var(--wp--style--global--wide-size);
            padding: 30px 0px !important;
        }
        @media (max-width: 1240px) {
            #author-page-wrapper {
                padding: 30px !important;
            }
        }
    </style>
    <?php 
    if (current_user_can('manage_options')) {
        wp_enqueue_media();
    }
    wp_head(); 
    ?>
</head>
<body <?php body_class('selection:bg-black selection:text-white'); ?>>
    <?php if (function_exists('wp_body_open')) { wp_body_open(); } ?>

    <?php
	if ($rcp_theme_header_html !== '') {
		echo str_replace('<header ', '<header id="red-cultural-header" ', $rcp_theme_header_html);
	}
	?>

    <div id="author-page-wrapper" class="mx-auto px-6">
        
        <div class="flex flex-col md:flex-row gap-12 md:gap-16">
            <!-- Sidebar / Header Column (25%) -->
            <aside class="w-full md:w-1/4">
                <header class="flex flex-col items-start gap-6">
                    <div id="rc-profile-photo-container" class="w-24 h-24 md:w-32 md:h-32 flex-shrink-0 relative group/avatar <?php echo current_user_can('manage_options') ? 'cursor-pointer' : ''; ?>">
                        <img 
                            id="rc-profile-photo-img"
                            src="<?php echo esc_url($author_avatar); ?>" 
                            alt="<?php echo esc_attr($author_name); ?>" 
                            class="profile-image w-full h-full object-cover rounded-full border border-zinc-100 transition-all group-hover/avatar:opacity-80"
                        >
                        <?php if (current_user_can('manage_options')) : ?>
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover/avatar:opacity-100 transition-opacity bg-black/20 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-camera w-8 h-8 text-white"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="w-full">
                        <h1 class="text-2xl md:text-3xl font-semibold tracking-tight"><?php echo esc_html($author_name); ?></h1>
                        <p class="text-zinc-500 mb-4 text-sm md:text-base"><?php echo esc_html($author_job); ?></p>
                        
                        <p class="text-sm md:text-base leading-snug text-zinc-800">
                            <?php echo wp_kses_post($author_bio); ?>
                        </p>

                        <!-- Socials -->
                        <div class="flex gap-3 mt-8">
                            <?php if ($twitter_url) : ?>
                            <a href="<?php echo esc_url($twitter_url); ?>" target="_blank" class="text-zinc-400 hover:text-black transition-colors" title="X (Twitter)">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            </a>
                            <?php endif; ?>
                            <?php if ($linkedin_url) : ?>
                            <a href="<?php echo esc_url($linkedin_url); ?>" target="_blank" class="text-zinc-400 hover:text-black transition-colors" title="LinkedIn">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </header>
            </aside>

            <!-- Main Content Column (75%) -->
            <main class="w-full md:w-3/4">
                <!-- Tabs Navigation -->
                <div class="flex gap-8 border-b border-zinc-100 mb-8">
                    <button onclick="switchTab('writings')" id="tab-writings" class="pb-2 text-[10px] font-bold uppercase tracking-[0.2em] transition-all tab-active">
                        Artículos
                    </button>
                    <button onclick="switchTab('courses')" id="tab-courses" class="pb-2 text-[10px] font-bold uppercase tracking-[0.2em] transition-all tab-inactive">
                        Cursos
                    </button>
                </div>

                <!-- Content Sections -->
                <div id="content-container">
                    
                    <!-- Blog Section -->
                    <section id="section-writings" class="block">
                        <?php if ($writings_q->have_posts()) : ?>
                            <div class="divide-y divide-zinc-100">
                                <?php while ($writings_q->have_posts()) : $writings_q->the_post(); ?>
                                    <a href="<?php the_permalink(); ?>" class="group py-5 first:pt-0 flex justify-between items-baseline gap-4 no-underline">
                                        <div>
                                            <h3 class="text-base font-medium text-black group-hover:text-zinc-600 transition-colors"><?php the_title(); ?></h3>
                                            <p class="text-zinc-400 text-xs mt-1"><?php echo esc_html(get_the_excerpt()); ?></p>
                                        </div>
                                        <time class="text-zinc-300 text-[10px] tabular-nums font-medium whitespace-nowrap uppercase"><?php echo get_the_date('M / y'); ?></time>
                                    </a>
                                <?php endwhile; wp_reset_postdata(); ?>
                            </div>
                        <?php else : ?>
                            <p class="text-zinc-400 text-sm italic">No se han encontrado artículos escritos por <?php echo esc_html($author_name); ?>.</p>
                        <?php endif; ?>
                    </section>

                    <!-- Courses Section -->
                    <section id="section-courses" class="hidden">
                        <?php if ($courses_q->have_posts()) : ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <?php while ($courses_q->have_posts()) : $courses_q->the_post(); ?>
                                    <?php 
                                    $c_id = get_the_ID(); 
                                    $thumb_url = get_the_post_thumbnail_url($c_id, 'medium');
                                    $course_price = '';
                                    if (class_exists('WooCommerce')) {
                                        $product_ids = get_post_meta($c_id, 'learndash_woocommerce_product_ids', true);
                                        if (is_array($product_ids) && !empty($product_ids)) {
                                            $wc_product = wc_get_product($product_ids[0]);
                                            if ($wc_product) {
                                                $course_price = $wc_product->get_price_html();
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="card-hover border border-zinc-100 rounded overflow-hidden flex flex-col h-full">
                                        <?php if ($thumb_url) : ?>
                                            <a href="<?php the_permalink(); ?>" class="block aspect-video overflow-hidden border-b border-zinc-50">
                                                <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php the_title_attribute(); ?>" class="w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-500">
                                            </a>
                                        <?php endif; ?>
                                        
                                        <div class="p-6 flex flex-col flex-grow">
                                            <div class="flex justify-between items-start gap-4 mb-3">
                                                <h3 class="text-base font-semibold leading-tight text-black">
                                                    <a href="<?php the_permalink(); ?>" class="no-underline hover:text-zinc-600 transition-colors"><?php the_title(); ?></a>
                                                </h3>
                                                <?php if ($course_price) : ?>
                                                    <span class="text-sm font-light text-zinc-900 whitespace-nowrap"><?php echo wp_kses_post($course_price); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <p class="text-zinc-500 text-xs leading-relaxed mb-6">
                                                <?php echo esc_html(wp_trim_words(get_the_excerpt(), 18)); ?>
                                            </p>
                                            
                                            <div class="mt-auto">
                                                <a href="<?php the_permalink(); ?>" class="text-[10px] font-bold uppercase tracking-[0.2em] text-black hover:text-zinc-500 transition-colors no-underline border-b border-black hover:border-zinc-500 pb-1">Ver curso</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; wp_reset_postdata(); ?>
                            </div>
                        <?php else : ?>
                            <p class="text-zinc-400 text-sm italic">No se han encontrado cursos impartidos por <?php echo esc_html($author_name); ?>.</p>
                        <?php endif; ?>
                    </section>

                </div>
            </main>
        </div>

    </div>

    <?php
	if ($rcp_theme_footer_html !== '') {
		echo str_replace('<footer ', '<footer id="red-cultural-footer" ', $rcp_theme_footer_html);
	}
	?>

    <script>
        function switchTab(tab) {
            const writingsSection = document.getElementById('section-writings');
            const coursesSection = document.getElementById('section-courses');
            const writingsTab = document.getElementById('tab-writings');
            const coursesTab = document.getElementById('tab-courses');

            if (tab === 'writings') {
                writingsSection.classList.remove('hidden');
                writingsSection.classList.add('block');
                coursesSection.classList.remove('block');
                coursesSection.classList.add('hidden');
                
                writingsTab.classList.add('tab-active');
                writingsTab.classList.remove('tab-inactive');
                coursesTab.classList.add('tab-inactive');
                coursesTab.classList.remove('tab-active');
            } else {
                coursesSection.classList.remove('hidden');
                coursesSection.classList.add('block');
                writingsSection.classList.remove('block');
                writingsSection.classList.add('hidden');
                
                coursesTab.classList.add('tab-active');
                coursesTab.classList.remove('tab-inactive');
                writingsTab.classList.add('tab-inactive');
                writingsTab.classList.remove('tab-active');
            }
        }

        // Profile Photo Edit for Admins
        document.addEventListener('DOMContentLoaded', function() {
            const avatarContainer = document.getElementById('rc-profile-photo-container');
            const avatarImg = document.getElementById('rc-profile-photo-img');
            
            if (!avatarContainer || !avatarImg) return;
            if (!window.wp || !window.wp.media) return;

            avatarContainer.addEventListener('click', function(e) {
                e.preventDefault();
                
                const frame = wp.media({
                    title: 'Seleccionar Foto de Perfil',
                    button: { text: 'Usar esta foto' },
                    multiple: false
                });

                frame.on('select', function() {
                    const attachment = frame.state().get('selection').first().toJSON();
                    const imageUrl = attachment.url;
                    
                    // Show loading state
                    avatarContainer.style.opacity = '0.5';
                    
                    const formData = new FormData();
                    formData.append('action', 'rc_update_author_profile_photo');
                    formData.append('nonce', '<?php echo wp_create_nonce("rc_author_edit_nonce"); ?>');
                    formData.append('user_id', '<?php echo $author_id; ?>');
                    formData.append('image_url', imageUrl);

                    fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => r.json())
                    .then(res => {
                        avatarContainer.style.opacity = '1';
                        if (res.success) {
                            avatarImg.src = imageUrl;
                        } else {
                            alert('Error al actualizar la foto: ' + (res.data || 'Error desconocido'));
                        }
                    })
                    .catch(err => {
                        avatarContainer.style.opacity = '1';
                        console.error('Error:', err);
                    });
                });

                frame.open();
            });
        });
    </script>

    <?php wp_footer(); ?>
</body>
</html>
