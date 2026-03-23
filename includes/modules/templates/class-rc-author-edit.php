<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles front-end author editing for admins on course pages.
 */
class RC_Author_Edit {

    public static function init() {
        add_action('wp_ajax_rc_search_authors', [__CLASS__, 'ajax_search_authors']);
        add_action('wp_ajax_rc_update_course_author', [__CLASS__, 'ajax_update_author']);
        add_action('wp_footer', [__CLASS__, 'render_js']);
    }

    /**
     * AJAX search for authors/admins.
     */
    public static function ajax_search_authors() {
        check_ajax_referer('rc_author_edit_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos suficientes.');
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

    /**
     * AJAX update course author.
     */
    public static function ajax_update_author() {
        check_ajax_referer('rc_author_edit_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos suficientes.');
        }

        $course_id = isset($_POST['course_id']) ? absint($_POST['course_id']) : 0;
        $author_id = isset($_POST['author_id']) ? absint($_POST['author_id']) : 0;

        if (!$course_id || !$author_id) {
            wp_send_json_error('Datos inválidos.');
        }

        $updated = wp_update_post([
            'ID' => $course_id,
            'post_author' => $author_id
        ]);

        if (is_wp_error($updated)) {
            wp_send_json_error($updated->get_error_message());
        }

        $user = get_userdata($author_id);
        wp_send_json_success([
            'display_name' => $user->display_name
        ]);
    }

    /**
     * Render JS and local styles in footer.
     */
    public static function render_js() {
        if (!is_singular('sfwd-courses') || !current_user_can('manage_options')) {
            return;
        }

        $course_id = get_the_ID();
        $nonce = wp_create_nonce('rc_author_edit_nonce');
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editBtn = document.getElementById('rc-edit-author-btn');
            const floating = document.getElementById('rc-author-edit-floating');
            const closeBtn = document.getElementById('rc-close-author-edit');
            const searchInput = document.getElementById('rc-author-search-input');
            const resultsDiv = document.getElementById('rc-author-search-results');
            const statusDiv = document.getElementById('rc-author-edit-status');
            const displayNameSpan = document.getElementById('rc-author-display-name');

            if (!editBtn) return;

            editBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                floating.classList.toggle('hidden');
                if (!floating.classList.contains('hidden')) {
                    searchInput.focus();
                }
            });

            closeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                floating.classList.add('hidden');
                resultsDiv.classList.add('hidden');
            });

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
                            if (res.success) {
                                renderResults(res.data);
                            }
                        });
                }, 300);
            });

            function renderResults(users) {
                if (users.length === 0) {
                    resultsDiv.innerHTML = '<div class="px-3 py-2 text-xs text-gray-500">No se encontraron autores</div>';
                } else {
                    resultsDiv.innerHTML = users.map(u => `
                        <div class="px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm text-gray-700 author-result-item" data-id="${u.ID}" data-name="${u.display_name}">
                            <div class="font-medium">${u.display_name}</div>
                            <div class="text-[10px] text-gray-400">${u.user_email}</div>
                        </div>
                    `).join('');
                }
                resultsDiv.classList.remove('hidden');

                document.querySelectorAll('.author-result-item').forEach(item => {
                    item.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const authorId = item.dataset.id;
                        const authorName = item.dataset.name;
                        updateAuthor(authorId, authorName);
                    });
                });
            }

            function updateAuthor(authorId, authorName) {
                statusDiv.textContent = 'Actualizando...';
                statusDiv.classList.remove('hidden', 'text-red-500', 'text-green-500');
                statusDiv.classList.add('text-blue-500');
                statusDiv.style.display = 'block';

                const formData = new FormData();
                formData.append('action', 'rc_update_course_author');
                formData.append('nonce', '<?php echo $nonce; ?>');
                formData.append('course_id', '<?php echo $course_id; ?>');
                formData.append('author_id', authorId);

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        statusDiv.textContent = '¡Actualizado!';
                        statusDiv.classList.replace('text-blue-500', 'text-green-500');
                        displayNameSpan.textContent = authorName;
                        setTimeout(() => {
                            floating.classList.add('hidden');
                            statusDiv.classList.add('hidden');
                            resultsDiv.classList.add('hidden');
                            searchInput.value = '';
                        }, 1000);
                    } else {
                        statusDiv.textContent = 'Error: ' + res.data;
                        statusDiv.classList.replace('text-blue-500', 'text-red-500');
                    }
                });
            }

            // Close when clicking outside
            document.addEventListener('click', (e) => {
                if (floating && !floating.contains(e.target) && !editBtn.contains(e.target)) {
                    floating.classList.add('hidden');
                    resultsDiv.classList.add('hidden');
                }
            });
            
            // Prevent closure when clicking inside the floating div
            if (floating) {
                floating.addEventListener('click', (e) => {
                    e.stopPropagation();
                });
            }
        });
        </script>
        <?php
    }
}
