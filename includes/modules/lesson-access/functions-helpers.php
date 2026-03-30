<?php
/**
 * Global helper functions for RCIL.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get the individual lesson price for a LearnDash course.
 */
function rcil_get_course_lesson_price($course_id)
{
    $price = get_post_meta($course_id, '_individual_lesson_price', true);
    $price = $price ? (int) $price : 0;
    
    return apply_filters('rcil_course_lesson_price', $price, $course_id);
}

/**
 * Get the WooCommerce Product ID linked to a course.
 */
function rcil_get_course_woo_product_id($course_id)
{
    if (!class_exists('WooCommerce')) {
        return false;
    }

    $p_ids = get_post_meta($course_id, '_pcg_woo_product_id', true);
    if (!$p_ids) {
        $p_ids = get_post_meta($course_id, 'learndash_woocommerce_product_ids', true);
    }
    if (!$p_ids) {
        $p_ids = get_post_meta($course_id, '_learndash_woocommerce_product_ids', true);
    }

    if ($p_ids) {
        return is_array($p_ids) ? reset($p_ids) : $p_ids;
    }

    // Robust Fallback: Check LearnDash custom button URL if no meta link exists.
    $ld_settings = get_post_meta($course_id, '_sfwd-courses', true);
    if (is_array($ld_settings) && !empty($ld_settings['sfwd-courses_custom_button_url'])) {
        $url = (string) $ld_settings['sfwd-courses_custom_button_url'];

        // 1. Extract ID from ?add-to-cart=ID
        if (preg_match('/add-to-cart=([0-9]+)/', $url, $matches)) {
            return (int) $matches[1];
        }

        // 2. Extract slug from /product/some-slug/
        if (preg_match('/\/product\/([^\/?#]+)/', $url, $matches)) {
            $slug = trim($matches[1], '/');
            $product = get_page_by_path($slug, OBJECT, 'product');
            if ($product instanceof \WP_Post) {
                return $product->ID;
            }
        }
    }

    return false;
}

/**
 * Robustly get the full course price (supporting LearnDash, WooCommerce, BuddyBoss).
 */
function rcil_get_full_course_price($course_id)
{
    // 1. Try LearnDash Native settings
    $full_price_raw = learndash_get_course_meta_setting($course_id, 'course_price');
    $full_price = is_scalar($full_price_raw) ? (string) $full_price_raw : '';

    // 2. Try _sfwd-courses meta (the raw array LD uses)
    if (!$full_price) {
        $ld_settings = get_post_meta($course_id, '_sfwd-courses', true);
        if (is_array($ld_settings) && isset($ld_settings['sfwd-courses_course_price'])) {
            $full_price = (string) $ld_settings['sfwd-courses_course_price'];
        }
    }

    // 3. Try WooCommerce Product Relation (Common for BuddyBoss/LearnDash and PCG)
    if (!$full_price && class_exists('WooCommerce')) {
        $product_id = rcil_get_course_woo_product_id($course_id);
        if ($product_id) {
            $product = wc_get_product($product_id);
            if ($product) {
                $full_price = (string) $product->get_price();
            }
        }
    }

    // 4. Final fallback
    if (!$full_price) {
        $full_price = (string) get_post_meta($course_id, 'course_price', true);
    }

    $raw = trim((string) $full_price);
    if ($raw === '') {
        return 0;
    }

    // If it's already a simple numeric string, return as int.
    if (is_numeric($raw)) {
        return (int) round((float) $raw);
    }

    // Remove currency symbols/spaces and keep digits only (supports "80,000" or "80.000").
    $digits = preg_replace('/[^0-9\\-]/', '', $raw);
    if ($digits === '' || !is_numeric($digits)) {
        return 0;
    }

    return (int) $digits;
}

/**
 * Get a list of lessons belonging to a particular LearnDash course.
 */
function rcil_get_course_lessons($course_id)
{
    if (!function_exists('learndash_get_course_lessons_list')) {
        return [];
    }
    $lessons = learndash_get_course_lessons_list($course_id, null, ['posts_per_page' => -1]);
    return $lessons;
}

/**
 * Check if a user has access to a specific lesson via individual purchase.
 */
function rcil_user_has_lesson_access($user_id, $lesson_id)
{
    if (!$user_id) {
        return false;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'rcil_lesson_access';

    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_name WHERE user_id = %d AND lesson_id = %d AND status = 'active'",
        $user_id,
        $lesson_id
    ));

    return !empty($exists);
}

/**
 * Determine if a user has full course enrollment.
 */
function rcil_user_has_full_course_access($user_id, $course_id)
{
    // Admins: respect the LearnDash "Course Auto-enrollment" setting.
    // When ON (default 'yes'), admins have full access to every course.
    // When OFF, admins are treated like regular users and must be enrolled/purchase.
    if (user_can($user_id, 'manage_options')) {
        $admin_auto_enroll = 'yes'; // safe default
        if (class_exists('LearnDash_Settings_Section')) {
            $admin_auto_enroll = LearnDash_Settings_Section::get_section_setting(
                'LearnDash_Settings_Section_General_Admin_User',
                'courses_autoenroll_admin_users'
            );
        }
        if ($admin_auto_enroll === 'yes') {
            return true;
        }
        // Auto-enrollment OFF → fall through and check actual enrollment below.
    }

    // Check direct meta access (standard enrollment) to avoid filter recursion
    $access_from = get_user_meta($user_id, 'course_' . $course_id . '_access_from', true);
    $has_native_access = !empty($access_from);

    if (!$has_native_access && function_exists('learndash_user_group_enrolled_to_course')) {
        $has_native_access = learndash_user_group_enrolled_to_course($user_id, $course_id);
    }

    if (!$has_native_access) {
        return false;
    }

    // Now check if it's "real access" vs "partial access" via our table
    global $wpdb;
    $table_name = $wpdb->prefix . 'rcil_lesson_access';
    $partial_access = $wpdb->get_results($wpdb->prepare(
        "SELECT id, is_full_access FROM $table_name WHERE user_id = %d AND course_id = %d LIMIT 1",
        $user_id,
        $course_id
    ));

    if (empty($partial_access)) {
        return true;
    }

    // If they have entries, check if any of them is marked as full access
    if (isset($partial_access[0]->is_full_access) && $partial_access[0]->is_full_access) {
        return true;
    }

    // They have partial items and none are 'full access', so they are restricted.
    return false;
}
