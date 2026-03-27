<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles author and status editing for admins on course pages.
 */
class RC_Author_Edit {

    public static function init() {
        add_action('wp_ajax_rc_search_authors', [__CLASS__, 'ajax_search_authors']);
        add_action('wp_ajax_rc_update_course_author', [__CLASS__, 'ajax_update_author']);
        add_action('wp_ajax_rc_update_course_status', [__CLASS__, 'ajax_update_status']);
        add_action('wp_footer', [__CLASS__, 'render_js']);
    }

    public static function ajax_search_authors() {
        check_ajax_referer('rc_author_edit_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        if (strlen($search) < 2) {
            wp_send_json_success([]);
        }
        $users = get_users([
            'search' => '*' . $search . '*',
            'role__in' => ['administrator', 'author'],
            'number' => 10,
            'fields' => ['ID', 'display_name', 'user_email']
        ]);
        wp_send_json_success($users);
    }

    public static function ajax_update_author() {
        check_ajax_referer('rc_author_edit_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        $author_id = isset($_POST['author_id']) ? absint($_POST['author_id']) : 0;

        if (!$post_id || !$author_id) {
            wp_send_json_error('Invalid data');
        }

        // Update the primary post
        $updated = wp_update_post(['ID' => $post_id, 'post_author' => $author_id]);
        if (is_wp_error($updated)) {
            wp_send_json_error($updated->get_error_message());
        }

        // Handle Bidirectional Sync
        $post_type = get_post_type($post_id);
        $linked_post_id = 0;

        if ($post_type === 'sfwd-courses') {
            // Find linked product
            $linked_post_id = get_post_meta($post_id, 'learndash_woocommerce_product_ids', true);
            if (is_array($linked_post_id)) {
                $linked_post_id = !empty($linked_post_id) ? (int) $linked_post_id[0] : 0;
            } else {
                $linked_post_id = absint($linked_post_id);
            }
        } elseif ($post_type === 'product') {
            // Find linked course
            $linked_post_id = get_post_meta($post_id, '_related_course_id', true);
            if (!$linked_post_id) {
                $linked_post_id = get_post_meta($post_id, '_related_course', true);
            }
            $linked_post_id = absint($linked_post_id);
        }

        if ($linked_post_id > 0) {
            wp_update_post(['ID' => $linked_post_id, 'post_author' => $author_id]);
        }

        $user = get_userdata($author_id);
        wp_send_json_success(['display_name' => $user->display_name]);
    }

    public static function ajax_update_status() {
        check_ajax_referer('rc_author_edit_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

        if (!$post_id || !in_array($status, ['publish', 'draft'])) {
            wp_send_json_error('Invalid data');
        }

        $updated = wp_update_post(['ID' => $post_id, 'post_status' => $status]);
        if (is_wp_error($updated)) {
            wp_send_json_error($updated->get_error_message());
        }

        wp_send_json_success(['status' => $status]);
    }

    public static function render_js() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $nonce = wp_create_nonce('rc_author_edit_nonce');
        ?>
        <style>
            #rc-author-admin-box.hidden { display: none !important; }
            #rc-author-search-results.hidden { display: none !important; }
            .author-result-item:hover { background-color: #f3f4f6; }
            @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
            .animate-in { animation: slideDown 0.2s ease-out; }
        </style>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const trigger = document.getElementById('rc-author-edit-trigger');
            const adminBox = document.getElementById('rc-author-admin-box');
            const cancelBtn = document.getElementById('rc-author-edit-cancel');
            const searchInput = document.getElementById('rc-author-search-input');
            const resultsDiv = document.getElementById('rc-author-search-results');
            const statusDiv = document.getElementById('rc-author-edit-status');
            const headerNameSpan = document.getElementById('rc-author-display-name-header');
            const statusToggle = document.getElementById('rc-course-status-toggle');
            const statusLabel = document.getElementById('rc-status-label');

            if (!trigger || !adminBox) return;

            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                adminBox.classList.toggle('hidden');
                if (!adminBox.classList.contains('hidden')) {
                    searchInput.focus();
                    trigger.textContent = 'Cancelar';
                } else {
                    trigger.textContent = 'Cambiar Autor';
                }
            });

            cancelBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                adminBox.classList.add('hidden');
                resultsDiv.classList.add('hidden');
                searchInput.value = '';
                trigger.textContent = 'Cambiar Autor';
            });

            if (statusToggle) {
                statusToggle.addEventListener('change', (e) => {
                    const status = e.target.checked ? 'publish' : 'draft';
                    updateStatus(status);
                });
            }

            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                const query = e.target.value.trim();
                if (query.length < 2) {
                    resultsDiv.innerHTML = '';
                    resultsDiv.classList.add('hidden');
                    return;
                }
                searchTimeout = setTimeout(() => {
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=rc_search_authors&nonce=<?php echo $nonce; ?>&search=' + encodeURIComponent(query))
                        .then(r => r.json())
                        .then(res => {
                            if (res.success) renderResults(res.data);
                        });
                }, 300);
            });

            function renderResults(users) {
                if (users.length === 0) {
                    resultsDiv.innerHTML = '<div class="px-4 py-3 text-xs text-gray-500 italic">No se encontraron profesores</div>';
                } else {
                    resultsDiv.innerHTML = users.map(u => `
                        <div class="px-4 py-3 cursor-pointer text-sm text-gray-700 author-result-item border-b border-gray-50 last:border-0 rounded-lg" data-id="${u.ID}" data-name="${u.display_name}">
                            <div class="font-bold text-gray-900">${u.display_name}</div>
                            <div class="text-[10px] text-gray-400 mt-0.5">${u.user_email}</div>
                        </div>
                    `).join('');
                }
                resultsDiv.classList.remove('hidden');

                document.querySelectorAll('.author-result-item').forEach(item => {
                    item.onclick = (e) => {
                        e.stopPropagation();
                        updateAuthor(item.dataset.id, item.dataset.name);
                    };
                });
            }

            function updateAuthor(authorId, authorName) {
                statusDiv.textContent = 'Sincronizando...';
                statusDiv.style.color = '#3b82f6';
                
                const formData = new FormData();
                formData.append('action', 'rc_update_course_author');
                formData.append('nonce', '<?php echo $nonce; ?>');
                formData.append('post_id', '<?php echo is_singular() ? get_the_ID() : 0; ?>');
                formData.append('author_id', authorId);

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        if (headerNameSpan) headerNameSpan.textContent = authorName;
                        statusDiv.textContent = '¡Profesor actualizado!';
                        statusDiv.style.color = '#10b981';
                        setTimeout(() => {
                            statusDiv.textContent = '';
                            resultsDiv.classList.add('hidden');
                            searchInput.value = '';
                        }, 1500);
                    } else {
                        statusDiv.textContent = 'Error al actualizar';
                        statusDiv.style.color = '#ef4444';
                    }
                });
            }

            function updateStatus(status) {
                statusDiv.textContent = 'Actualizando estado...';
                statusDiv.style.color = '#3b82f6';
                
                const formData = new FormData();
                formData.append('action', 'rc_update_course_status');
                formData.append('nonce', '<?php echo $nonce; ?>');
                formData.append('post_id', '<?php echo is_singular() ? get_the_ID() : 0; ?>');
                formData.append('status', status);

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        statusLabel.textContent = (status === 'publish') ? 'Publicado' : 'Borrador';
                        statusDiv.textContent = 'Estado actualizado';
                        statusDiv.style.color = '#10b981';
                        setTimeout(() => { statusDiv.textContent = ''; }, 1500);
                    } else {
                        statusDiv.textContent = 'Error al cambiar estado';
                        statusDiv.style.color = '#ef4444';
                        statusToggle.checked = !statusToggle.checked; // revert
                    }
                });
            }
        });
        </script>
        <?php
    }
}
