<?php
/**
 * Script to bulk update author profile photos.
 * Run this script from the CLI: php update-author-photos.php
 */

require_once __DIR__ . '/../../../../wp-load.php';

if (!defined('ABSPATH')) {
    die('WordPress not loaded.');
}

$authors_data = [
    // Equipo Administrativo
    ['name' => 'Magdalena', 'last_name' => 'Merbihláa', 'url' => 'http://red-cultural.cl/wp-content/uploads/2021/01/magdalena.jpg'],
    ['name' => 'Bárbara', 'last_name' => 'Bustamante', 'url' => 'http://red-cultural.cl/wp-content/uploads/2021/01/barbara.jpg'],
    ['name' => 'Guillermo', 'last_name' => 'González', 'url' => 'http://red-cultural.cl/wp-content/uploads/2022/05/23b99eb8-4770-486c-b0cf-2751da339dc2-1-e1652661760158.jpg'],
    ['name' => 'Viviana', 'last_name' => 'Ávila', 'url' => 'http://red-cultural.cl/wp-content/uploads/2021/01/viviana-avila.jpg'],
    
    // Equipo Docente
    ['name' => 'Isabel', 'last_name' => 'Eluchans', 'url' => 'http://red-cultural.cl/wp-content/uploads/2021/01/isabeleluchans.jpg'],
    ['name' => 'Rosita', 'last_name' => 'Larraín', 'url' => 'http://red-cultural.cl/wp-content/uploads/2021/01/rositalarrain.jpg'],
    ['name' => 'Pilar', 'last_name' => 'Ducci', 'url' => 'http://red-cultural.cl/wp-content/uploads/2021/01/pilarducci.jpg'],
    ['name' => 'Gonzalo', 'last_name' => 'Larios', 'url' => 'http://red-cultural.cl/wp-content/uploads/2021/01/gonzalolarios-1.jpg'],
    ['name' => 'Cristián', 'last_name' => 'Leon', 'url' => 'http://red-cultural.cl/wp-content/uploads/2021/01/cristianleon.jpg'],
    ['name' => 'Klaus', 'last_name' => 'Droste', 'url' => 'http://red-cultural.cl/wp-content/uploads/2021/01/klausdroste-200x200-1.jpg'],
    ['name' => 'Ángel', 'last_name' => 'Soto', 'url' => 'http://red-cultural.cl/wp-content/uploads/2021/01/angelsoto-1.jpg'],
    ['name' => 'Patricio', 'last_name' => 'Carvajal', 'url' => 'http://red-cultural.cl/wp-content/uploads/2021/03/patriciocarvajal.jpg'],
    ['name' => 'Sebastián', 'last_name' => 'Salinas', 'url' => 'http://red-cultural.cl/wp-content/uploads/2021/01/sebastiansalinas.jpg'],
    ['name' => 'Armando', 'last_name' => 'Roa', 'url' => 'http://red-cultural.cl/wp-content/uploads/2021/01/armandoroa-1.jpg'],
    ['name' => 'Magdalena', 'last_name' => 'Dittborn', 'url' => 'http://red-cultural.cl/wp-content/uploads/2021/03/magdalenadittborn.jpg'],
    ['name' => 'Rafael', 'last_name' => 'Mellafe', 'url' => 'http://red-cultural.cl/wp-content/uploads/2022/04/mellafe.jpg'],
    ['name' => 'Sergio', 'last_name' => 'Vergara', 'url' => 'http://red-cultural.cl/wp-content/uploads/2021/11/sergiovergara.jpg'],
    ['name' => 'María Paz', 'last_name' => 'Díaz', 'url' => 'http://red-cultural.cl/wp-content/uploads/2022/04/mariapazdiaz.jpeg'],
    ['name' => 'José', 'last__name' => 'Blanco', 'url' => 'http://red-cultural.cl/wp-content/uploads/2022/04/joseblancofoto.jpeg'],
    ['name' => 'Felipe', 'last_name' => 'Munizaga', 'url' => 'http://red-cultural.cl/wp-content/uploads/2022/04/felipemunizaga.jpg'],
    ['name' => 'Joseph', 'last_name' => 'Pearce', 'url' => 'http://red-cultural.cl/wp-content/uploads/2022/04/josephpearce.jpg'],
    ['name' => 'Francisca', 'last_name' => 'Willson', 'url' => 'http://red-cultural.cl/wp-content/uploads/2021/01/franciscawillson.jpg'],
];

function sanitize_to_email_part($str) {
    $str = strtolower($str);
    $str = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', ' '], ['a', 'e', 'i', 'o', 'u', 'n', ''], $str);
    return preg_replace('/[^a-z0-9]/', '', $str);
}

foreach ($authors_data as $data) {
    $first = $data['name'];
    $last = $data['last_name'] ?? '';
    $url = $data['url'];

    echo "Processing: $first $last... ";

    // Try finding by email variations
    $email_variations = [
        sanitize_to_email_part($first) . '@redcultural.cl',
        sanitize_to_email_part($first . $last) . '@redcultural.cl',
        sanitize_to_email_part($first) . '.' . sanitize_to_email_part($last) . '@redcultural.cl',
    ];

    $user = null;
    foreach ($email_variations as $email) {
        $user = get_user_by('email', $email);
        if ($user) break;
    }

    // Fallback: search by name
    if (!$user) {
        $search = get_users([
            'search' => "*$first*",
            'search_columns' => ['display_name', 'user_nicename'],
        ]);
        if (!empty($search)) {
            foreach ($search as $s_user) {
                if (stripos($s_user->display_name, $last) !== false) {
                    $user = $s_user;
                    break;
                }
            }
        }
    }

    if ($user) {
        update_user_meta($user->ID, 'rc_profile_photo', $url);
        echo "SUCCESS (User ID: {$user->ID}, Email: {$user->user_email})\n";
    } else {
        echo "FAILED (No user found for variations of $first $last)\n";
    }
}

echo "Done.\n";
