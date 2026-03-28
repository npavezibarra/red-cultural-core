<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles custom author profile fields in WordPress admin and provides logic for metadata.
 */
class RC_Author_Profile {

    public static function init() {
        add_action('show_user_profile', [__CLASS__, 'add_custom_profile_fields']);
        add_action('edit_user_profile', [__CLASS__, 'add_custom_profile_fields']);

        add_action('personal_options_update', [__CLASS__, 'save_custom_profile_fields']);
        add_action('edit_user_profile_update', [__CLASS__, 'save_custom_profile_fields']);
    }

    public static function add_custom_profile_fields($user) {
        $academic_specialty = get_user_meta($user->ID, 'rc_academic_specialty', true);
        $author_title = get_user_meta($user->ID, 'rc_author_title', true);
        $facebook = get_user_meta($user->ID, 'rc_social_facebook', true);
        $instagram = get_user_meta($user->ID, 'rc_social_instagram', true);
        $youtube = get_user_meta($user->ID, 'rc_social_youtube', true);
        $twitter = get_user_meta($user->ID, 'rc_social_x', true);
        
        // Legacy fallback for twitter if rc_social_x is empty
        if (empty($twitter)) {
            $twitter = get_user_meta($user->ID, 'twitter', true);
        }

        ?>
        <h3>Red Cultural: Información de Autor</h3>
        <table class="form-table">
            <tr>
                <th><label for="rc_author_title">Título Académico / Cargo</label></th>
                <td>
                    <input type="text" name="rc_author_title" id="rc_author_title" value="<?php echo esc_attr($author_title); ?>" class="regular-text" placeholder="Ej: Profesor Especialista en Historia del Arte" /><br />
                    <span class="description">Este título aparecerá debajo de tu nombre en la página de autor.</span>
                </td>
            </tr>
            <tr>
                <th><label for="rc_academic_specialty">Especialidad Académica (Breve párrafo)</label></th>
                <td>
                    <textarea name="rc_academic_specialty" id="rc_academic_specialty" rows="5" cols="30" style="width: 100%; max-width: 500px;"><?php echo esc_textarea($academic_specialty); ?></textarea><br />
                    <span class="description">Un breve párrafo describiendo tu formación y especialidad.</span>
                </td>
            </tr>
            <tr>
                <th><label>Redes Sociales</label></th>
                <td>
                    <div style="margin-bottom: 10px;">
                        <span style="display:inline-block; width: 80px;">Facebook:</span>
                        <input type="url" name="rc_social_facebook" value="<?php echo esc_url($facebook); ?>" class="regular-text" placeholder="https://facebook.com/..." />
                    </div>
                    <div style="margin-bottom: 10px;">
                        <span style="display:inline-block; width: 80px;">Instagram:</span>
                        <input type="url" name="rc_social_instagram" value="<?php echo esc_url($instagram); ?>" class="regular-text" placeholder="https://instagram.com/..." />
                    </div>
                    <div style="margin-bottom: 10px;">
                        <span style="display:inline-block; width: 80px;">YouTube:</span>
                        <input type="url" name="rc_social_youtube" value="<?php echo esc_url($youtube); ?>" class="regular-text" placeholder="https://youtube.com/..." />
                    </div>
                    <div style="margin-bottom: 10px;">
                        <span style="display:inline-block; width: 80px;">X (Twitter):</span>
                        <input type="url" name="rc_social_x" value="<?php echo esc_url($twitter); ?>" class="regular-text" placeholder="https://x.com/..." />
                    </div>
                </td>
            </tr>
        </table>
        <?php
    }

    public static function save_custom_profile_fields($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }

        if (isset($_POST['rc_author_title'])) {
            update_user_meta($user_id, 'rc_author_title', sanitize_text_field($_POST['rc_author_title']));
        }
        
        if (isset($_POST['rc_academic_specialty'])) {
            update_user_meta($user_id, 'rc_academic_specialty', wp_kses_post($_POST['rc_academic_specialty']));
        }

        if (isset($_POST['rc_social_facebook'])) {
            update_user_meta($user_id, 'rc_social_facebook', esc_url_raw($_POST['rc_social_facebook']));
        }

        if (isset($_POST['rc_social_instagram'])) {
            update_user_meta($user_id, 'rc_social_instagram', esc_url_raw($_POST['rc_social_instagram']));
        }

        if (isset($_POST['rc_social_youtube'])) {
            update_user_meta($user_id, 'rc_social_youtube', esc_url_raw($_POST['rc_social_youtube']));
        }

        if (isset($_POST['rc_social_x'])) {
            update_user_meta($user_id, 'rc_social_x', esc_url_raw($_POST['rc_social_x']));
            // Sync with legacy key
            update_user_meta($user_id, 'twitter', esc_url_raw($_POST['rc_social_x']));
        }
    }
}
