<?php
require_once dirname(dirname(dirname(__DIR__))) . '/wp-load.php';

$course = get_page_by_path('arte-de-la-posguerra-1945-1990', OBJECT, 'sfwd-courses');
if ($course) {
    echo "Course ID: " . $course->ID . "\n";
    $meta = get_post_meta($course->ID);
    print_r($meta);
} else {
    echo "Course not found.\n";
}
