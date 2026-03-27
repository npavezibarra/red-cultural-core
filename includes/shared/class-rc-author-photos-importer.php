<?php
/**
 * Author Photos Importer Logic.
 */

if (!defined('ABSPATH')) {
    exit;
}

class RC_Author_Photos_Importer {
    public static function run() {
        $authors_data = require RC_CORE_PATH . 'includes/shared/author-photos-data.php';
        $results = [];

        // Get all users with @redcultural.cl domain to try matching
        $rc_users = get_users([
            'search'         => '*@redcultural.cl',
            'search_columns' => ['user_email'],
            'fields'         => 'all',
        ]);

        foreach ($authors_data as $data) {
            $first = (string) $data['name'];
            $last = (string) ($data['last_name'] ?? '');
            $url = (string) $data['url'];
            $full_name = trim($first . ' ' . $last);

            $user = null;

            // 1. Try exact email patterns first (prioritize current known patterns)
            $first_clean = self::sanitize_to_email_part($first);
            $last_clean = self::sanitize_to_email_part($last);
            $email_variations = [
                $first_clean . '.' . $last_clean . '@redcultural.cl',
                $first_clean . $last_clean . '@redcultural.cl',
                $first_clean . '@redcultural.cl',
            ];

            foreach ($email_variations as $email) {
                $user = get_user_by('email', $email);
                if ($user) break;
            }

            // 2. If not found, search in the pool of @redcultural.cl users
            if (!$user && !empty($rc_users)) {
                foreach ($rc_users as $rc_user) {
                    $u_display = (string) $rc_user->display_name;
                    $u_email = (string) $rc_user->user_email;
                    $u_login = (string) $rc_user->user_login;

                    // Match by first and last name in display name
                    if (stripos($u_display, $first) !== false && ($last === '' || stripos($u_display, $last) !== false)) {
                        $user = $rc_user;
                        break;
                    }
                    
                    // Match by name parts in email
                    if (stripos($u_email, $first_clean) !== false && ($last_clean === '' || stripos($u_email, $last_clean) !== false)) {
                        $user = $rc_user;
                        break;
                    }

                    // Match by name parts in login
                    if (stripos($u_login, $first_clean) !== false) {
                        $user = $rc_user;
                        break;
                    }
                }
            }

            // 3. Last resort: search globally (in case email is different but display name matches)
            if (!$user) {
                $search = get_users([
                    'search' => '*' . $first . '*',
                    'search_columns' => ['display_name', 'user_nicename', 'user_login'],
                    'number' => 5
                ]);
                foreach ($search as $s_user) {
                    if ($last === '' || stripos($s_user->display_name, $last) !== false) {
                        $user = $s_user;
                        break;
                    }
                }
            }

            if ($user) {
                update_user_meta($user->ID, 'rc_profile_photo', $url);
                $results[] = sprintf("SUCCESS: Updated %s (ID: %d, Email: %s)", $full_name, $user->ID, $user->user_email);
            } else {
                $results[] = sprintf("FAILED: No user found for %s", $full_name);
            }
        }

        return $results;
    }

    private static function sanitize_to_email_part($str) {
        $str = strtolower($str);
        $str = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', ' '], ['a', 'e', 'i', 'o', 'u', 'n', ''], $str);
        return preg_replace('/[^a-z0-9]/', '', $str);
    }
}
