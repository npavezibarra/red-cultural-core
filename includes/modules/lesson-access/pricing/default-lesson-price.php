<?php
/**
 * Dynamic calculation for Individual Lesson Price.
 * 
 * This logic provides a fallback price when no explicit price is set for a course.
 * It calculates 125% of the proportional lesson price and rounds down to the nearest hundred.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_filter('rcil_course_lesson_price', 'rcil_calculate_default_lesson_price', 10, 2);

/**
 * Dynamically calculate the lesson price based on course total price and lesson count.
 * Formula: floor(((course_price / lesson_count) * 1.25) / 100) * 100
 */
function rcil_calculate_default_lesson_price($price, $course_id)
{
    // If a specific price is defined, it always takes precedence.
    if ($price > 0) {
        return $price;
    }

    $course_price = rcil_get_full_course_price($course_id);
    
    // If the course price is 0 or not found, we can't calculate a default.
    if ($course_price <= 0) {
        return 0;
    }

    $lessons = rcil_get_course_lessons($course_id);
    $lesson_count = count($lessons);

    // Avoid division by zero.
    if ($lesson_count <= 0) {
        return 0;
    }

    // proportional_price = course_price / lesson_count
    // lesson_price = proportional_price * 1.25
    // then round down to nearest 100.
    
    $calculated_price = ($course_price / $lesson_count) * 1.25;
    $rounded_price = floor($calculated_price / 100) * 100;

    return (int)$rounded_price;
}
