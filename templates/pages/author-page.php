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

// Academic specialty
$academic_specialty = (string) get_user_meta($author_id, 'rc_academic_specialty', true);

// Title / Job
$author_title = (string) get_user_meta($author_id, 'rc_author_title', true);
if ($author_title === '') {
    $author_title = (string) get_user_meta($author_id, 'occupation', true);
    if ($author_title === '') {
        $author_title = (string) get_user_meta($author_id, 'job_title', true);
    }
}
if ($author_title === '') {
    $author_title = 'Profesor Red Cultural';
}

// Social links
$social_links = [
    'facebook' => [
        'url' => (string) get_user_meta($author_id, 'rc_social_facebook', true),
        'icon' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>',
        'label' => 'Facebook',
        'key' => 'rc_social_facebook'
    ],
    'instagram' => [
        'url' => (string) get_user_meta($author_id, 'rc_social_instagram', true),
        'icon' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>',
        'label' => 'Instagram',
        'key' => 'rc_social_instagram'
    ],
    'youtube' => [
        'url' => (string) get_user_meta($author_id, 'rc_social_youtube', true),
        'icon' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>',
        'label' => 'YouTube',
        'key' => 'rc_social_youtube'
    ],
    'x' => [
        'url' => (string) get_user_meta($author_id, 'rc_social_x', true),
        'icon' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
        'label' => 'X (Twitter)',
        'key' => 'rc_social_x'
    ],
    'linkedin' => [
        'url' => (string) get_user_meta($author_id, 'linkedin', true),
        'icon' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
        'label' => 'LinkedIn',
        'key' => 'linkedin'
    ],
];

// Re-check legacy twitter if rc_social_x is empty
if (empty($social_links['x']['url'])) {
    $social_links['x']['url'] = (string) get_user_meta($author_id, 'twitter', true);
}

$can_edit = (current_user_can('manage_options') || get_current_user_id() === $author_id);

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

// Toggle tabs visibility based on content
$has_writings = $writings_q->have_posts();
$has_courses = $courses_q->have_posts();
$default_tab = 'writings';

if (!$has_writings && $has_courses) {
    $default_tab = 'courses';
} elseif ($has_writings && !$has_courses) {
    $default_tab = 'writings';
}

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
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9f9f9 !important;
            color: #000000;
            -webkit-font-smoothing: antialiased;
        }
        .profile-image {
            /* filter: grayscale(100%); */
        }
        .card-hover {
            transition: all 0.3s cubic-bezier(.4,0,.2,1);
            border-radius: 9px;
        }
        .card-hover:hover {
            background-color: #fafafa;
            border-color: #000;
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(0,0,0,.1), 0 8px 10px -6px rgba(0,0,0,.1);
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
        .editable-field {
            position: relative;
            cursor: pointer;
            transition: all 0.2s ease;
            border-radius: 4px;
            padding: 2px 4px;
            margin: -2px -4px;
        }
        .editable-field:hover {
            background-color: rgba(0,0,0,0.03);
            outline: 1px dashed #ccc;
        }
        .editable-field .edit-indicator {
            position: absolute;
            right: -20px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0;
            transition: opacity 0.2s;
        }
        .editable-field:hover .edit-indicator {
            opacity: 0.5;
        }
        .inline-edit-input {
            width: 100%;
            border: 1px solid #000;
            padding: 4px 8px;
            font-family: inherit;
            font-size: inherit;
            font-weight: inherit;
            line-height: inherit;
            color: inherit;
            background: #fff;
            border-radius: 4px;
            outline: none;
        }
        .social-link-item {
            position: relative;
        }
        .social-edit-btn {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #000;
            color: #fff;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            opacity: 0;
            transition: opacity 0.2s;
            cursor: pointer;
        }
        .social-link-item:hover .social-edit-btn {
            opacity: 1;
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
                    <div id="rc-profile-photo-container" class="w-24 h-24 md:w-32 md:h-32 flex-shrink-0 relative group/avatar <?php echo $can_edit ? 'cursor-pointer' : ''; ?>">
                        <img 
                            id="rc-profile-photo-img"
                            src="<?php echo esc_url($author_avatar); ?>" 
                            alt="<?php echo esc_attr($author_name); ?>" 
                            class="profile-image w-full h-full object-cover rounded-full border border-zinc-100 transition-all group-hover/avatar:opacity-80"
                        >
                        <?php if ($can_edit) : ?>
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover/avatar:opacity-100 transition-opacity bg-black/20 rounded-full text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-camera"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="w-full">
                        <h1 class="text-2xl md:text-3xl font-semibold tracking-tight"><?php echo esc_html($author_name); ?></h1>
                        
                        <div class="<?php echo $can_edit ? 'editable-field' : ''; ?> mb-4" data-key="rc_author_title" data-type="input">
                            <p class="text-zinc-500 text-sm md:text-base">
                                <?php echo esc_html($author_title); ?>
                            </p>
                            <?php if ($can_edit) : ?>
                                <svg class="edit-indicator w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            <?php endif; ?>
                        </div>
                        
                        <div class="<?php echo $can_edit ? 'editable-field' : ''; ?> mb-6" data-key="description" data-type="textarea">
                            <p class="text-sm md:text-base leading-snug text-zinc-800">
                                <?php echo $author_bio ? wp_kses_post($author_bio) : ($can_edit ? '<span class="text-zinc-500 font-medium">Clic para añadir biografía...</span>' : ''); ?>
                            </p>
                            <?php if ($can_edit) : ?>
                                <svg class="edit-indicator w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            <?php endif; ?>
                        </div>

                        <?php if ($academic_specialty || $can_edit) : ?>
                            <div class="mt-6">
                                <h4 class="text-[10px] font-bold uppercase tracking-[0.2em] text-zinc-400 mb-2">Especialidad Académica</h4>
                                <div class="<?php echo $can_edit ? 'editable-field' : ''; ?>" data-key="rc_academic_specialty" data-type="textarea">
                                    <p class="text-sm leading-relaxed text-zinc-600">
                                        <?php echo $academic_specialty ? wp_kses_post($academic_specialty) : ($can_edit ? '<span class="text-zinc-500 font-medium">Clic para añadir especialidad...</span>' : ''); ?>
                                    </p>
                                    <?php if ($can_edit) : ?>
                                        <svg class="edit-indicator w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Socials -->
                        <div class="flex gap-4 mt-8 flex-wrap">
                            <?php foreach ($social_links as $type => $data) : ?>
                                <?php if ($data['url'] || $can_edit) : ?>
                                    <div class="social-link-item">
                                        <a href="<?php echo $data['url'] ? esc_url($data['url']) : '#'; ?>" 
                                           target="_blank" 
                                           class="<?php echo $data['url'] ? 'text-black hover:text-zinc-600' : 'text-zinc-400'; ?> transition-colors" 
                                           title="<?php echo esc_attr($data['label']); ?>"
                                           id="social-link-<?php echo $type; ?>"
                                        >
                                            <?php echo $data['icon']; ?>
                                        </a>
                                        <?php if ($can_edit) : ?>
                                            <div class="social-edit-btn" onclick="editSocial('<?php echo $type; ?>', '<?php echo esc_js($data['key']); ?>', '<?php echo esc_js($data['url']); ?>', event)">
                                                <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </header>
            </aside>

            <!-- Main Content Column (75%) -->
            <main class="w-full md:w-3/4">
                <!-- Tabs Navigation -->
                <?php if ($has_writings && $has_courses) : ?>
                    <div class="flex gap-8 border-b border-zinc-100 mb-8">
                        <button onclick="switchTab('writings')" id="tab-writings" class="pb-2 text-[10px] font-bold uppercase tracking-[0.2em] transition-all <?php echo $default_tab === 'writings' ? 'tab-active' : 'tab-inactive'; ?>">
                            Artículos
                        </button>
                        <button onclick="switchTab('courses')" id="tab-courses" class="pb-2 text-[10px] font-bold uppercase tracking-[0.2em] transition-all <?php echo $default_tab === 'courses' ? 'tab-active' : 'tab-inactive'; ?>">
                            Cursos
                        </button>
                    </div>
                <?php elseif ($has_writings || $has_courses) : ?>
                    <!-- Optional: Title for single section if preferred -->
                    <div class="border-b border-zinc-100 mb-8 pb-2">
                        <h4 class="text-[10px] font-bold uppercase tracking-[0.2em] text-black">
                            <?php echo $has_writings ? 'Artículos' : 'Cursos'; ?>
                        </h4>
                    </div>
                <?php endif; ?>

                <!-- Content Sections -->
                <div id="content-container">
                    
                    <!-- Blog Section -->
                    <?php if ($has_writings) : ?>
                    <section id="section-writings" class="<?php echo $default_tab === 'writings' ? 'block' : 'hidden'; ?>">
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
                    <?php endif; ?>

                    <!-- Courses Section -->
                    <?php if ($has_courses) : ?>
                    <section id="section-courses" class="<?php echo $default_tab === 'courses' ? 'block' : 'hidden'; ?>">
                        <?php if ($courses_q->have_posts()) : ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <?php while ($courses_q->have_posts()) : $courses_q->the_post(); ?>
                                    <?php 
                                    $c_id = get_the_ID(); 
                                    $thumb_url = get_the_post_thumbnail_url($c_id, 'medium');
                                    
                                    // 1. Try LearnDash Native settings via existing shortcode helper
                                    $course_price = function_exists('rcp_format_ld_course_price') ? rcp_format_ld_course_price($c_id) : '';

                                    // 2. Try WooCommerce Product Relation if LearnDash price is empty
                                    if (!$course_price && class_exists('WooCommerce')) {
                                        $product_id = function_exists('rcil_get_course_woo_product_id') ? rcil_get_course_woo_product_id($c_id) : false;
                                        if (!$product_id) {
                                            $p_ids = get_post_meta($c_id, 'learndash_woocommerce_product_ids', true);
                                            $product_id = is_array($p_ids) ? reset($p_ids) : $p_ids;
                                        }

                                        if ($product_id) {
                                            $wc_product = wc_get_product($product_id);
                                            if ($wc_product) {
                                                $course_price = $wc_product->get_price_html();
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="card-hover border border-zinc-100 overflow-hidden flex flex-col h-full">
                                        <?php if ($thumb_url) : ?>
                                            <a href="<?php the_permalink(); ?>" class="block aspect-video overflow-hidden border-b border-zinc-50">
                                                <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php the_title_attribute(); ?>" class="w-full h-full object-cover transition-all duration-500">
                                            </a>
                                        <?php endif; ?>
                                        
                                        <div class="p-6 flex flex-col flex-grow">
                                            <div class="mb-3">
                                                <h3 class="text-base font-semibold leading-tight text-black">
                                                    <a href="<?php the_permalink(); ?>" class="no-underline hover:text-zinc-600 transition-colors"><?php the_title(); ?></a>
                                                </h3>
                                            </div>
                                            
                                            <p class="text-zinc-500 text-xs leading-relaxed mb-6">
                                                <?php echo esc_html(wp_trim_words(get_the_excerpt(), 18)); ?>
                                            </p>
                                            
                                            <div class="mt-auto flex items-end justify-between">
                                                <a href="<?php the_permalink(); ?>" class="text-[10px] font-bold uppercase tracking-[0.2em] text-black hover:text-zinc-500 transition-colors no-underline border-b border-black hover:border-zinc-500 pb-1 shrink-0">Ver curso</a>
                                                <?php if ($course_price) : ?>
                                                    <span class="text-sm font-bold text-gray-900 leading-none"><?php echo wp_kses_post($course_price); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; wp_reset_postdata(); ?>
                            </div>
                        <?php else : ?>
                            <p class="text-zinc-400 text-sm italic">No se han encontrado cursos impartidos por <?php echo esc_html($author_name); ?>.</p>
                        <?php endif; ?>
                    </section>
                    <?php endif; ?>

                    <?php if (!$has_writings && !$has_courses) : ?>
                        <div class="py-12 text-center border-t border-zinc-100">
                            <p class="text-zinc-400 text-sm italic">Este autor aún no tiene artículos ni cursos publicados.</p>
                        </div>
                    <?php endif; ?>

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

        // Profile Photo Edit
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
                            alert('Error: ' + (res.data || 'Acceso denegado'));
                        }
                    })
                    .catch(err => {
                        avatarContainer.style.opacity = '1';
                    });
                });

                frame.open();
            });

            // Inline Editing for Meta Fields
            const editableFields = document.querySelectorAll('.editable-field');
            editableFields.forEach(field => {
                field.addEventListener('click', function() {
                    if (this.querySelector('.inline-edit-input')) return;

                    const key = this.dataset.key;
                    const type = this.dataset.type;
                    const originalP = this.querySelector('p');
                    const currentValue = originalP.innerText.replace('Clic para añadir', '').trim() === '...' ? '' : originalP.innerText;
                    
                    const input = type === 'textarea' 
                        ? document.createElement('textarea') 
                        : document.createElement('input');
                    
                    input.className = 'inline-edit-input';
                    if (type === 'textarea') input.rows = 4;
                    input.value = currentValue.includes('Clic para añadir') ? '' : currentValue;
                    
                    const finishEdit = () => {
                        const newValue = input.value.trim();
                        if (newValue === currentValue) {
                            this.innerHTML = originalP.outerHTML + (this.querySelector('.edit-indicator')?.outerHTML || '');
                            return;
                        }

                        this.style.opacity = '0.5';
                        const formData = new FormData();
                        formData.append('action', 'rc_update_author_meta');
                        formData.append('nonce', '<?php echo wp_create_nonce("rc_author_edit_nonce"); ?>');
                        formData.append('user_id', '<?php echo $author_id; ?>');
                        formData.append('meta_key', key);
                        formData.append('meta_value', newValue);

                        fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                            method: 'POST',
                            body: formData
                        })
                        .then(r => r.json())
                        .then(res => {
                            this.style.opacity = '1';
                            if (res.success) {
                                originalP.innerHTML = newValue || '<span class="text-zinc-500 font-medium">Clic para añadir...</span>';
                                this.innerHTML = originalP.outerHTML + (this.querySelector('.edit-indicator')?.outerHTML || '');
                            } else {
                                alert('Error: ' + (res.data || 'No se pudo guardar'));
                                this.innerHTML = originalP.outerHTML + (this.querySelector('.edit-indicator')?.outerHTML || '');
                            }
                        });
                    };

                    this.innerHTML = '';
                    this.appendChild(input);
                    input.focus();

                    input.addEventListener('blur', finishEdit);
                    if (type === 'input') {
                        input.addEventListener('keypress', (e) => {
                            if (e.key === 'Enter') finishEdit();
                        });
                    }
                });
            });
        });

        // Social Editing
        function editSocial(type, key, currentUrl, event) {
            event.preventDefault();
            event.stopPropagation();
            
            const newUrl = prompt(`Ingresa la URL de ${type}:`, currentUrl);
            if (newUrl === null) return;

            const iconLink = document.getElementById(`social-link-${type}`);
            const item = iconLink.closest('.social-link-item');
            
            item.style.opacity = '0.5';
            
            const formData = new FormData();
            formData.append('action', 'rc_update_author_meta');
            formData.append('nonce', '<?php echo wp_create_nonce("rc_author_edit_nonce"); ?>');
            formData.append('user_id', '<?php echo $author_id; ?>');
            formData.append('meta_key', key);
            formData.append('meta_value', newUrl);

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                item.style.opacity = '1';
                if (res.success) {
                    if (newUrl) {
                        iconLink.href = newUrl;
                        iconLink.classList.remove('text-zinc-400');
                        iconLink.classList.add('text-black', 'hover:text-zinc-600');
                    } else {
                        iconLink.href = '#';
                        iconLink.classList.add('text-zinc-400');
                        iconLink.classList.remove('text-black', 'hover:text-zinc-600');
                    }
                } else {
                    alert('Error: ' + (res.data || 'No se pudo guardar'));
                }
            });
        }
    </script>

    <?php wp_footer(); ?>
</body>
</html>
