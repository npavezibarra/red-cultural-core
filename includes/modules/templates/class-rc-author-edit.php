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
        
        // Back-end Metabox
        add_action('add_meta_boxes', [__CLASS__, 'add_author_metabox']);
        add_action('save_post_product', [__CLASS__, 'save_author_metabox'], 10, 2);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
    }

    public static function enqueue_admin_assets($hook) {
        if (!in_array($hook, ['post.php', 'post-new.php'])) return;
        if (get_post_type() !== 'product') return;
        
        wp_enqueue_script('lucide', 'https://unpkg.com/lucide@latest', [], null, true);
    }

    public static function add_author_metabox() {
        add_meta_box(
            'rc_author_metabox',
            'Autor del Producto (Autosuggest)',
            [__CLASS__, 'render_metabox'],
            'product',
            'side',
            'default'
        );
    }

    public static function render_metabox($post) {
        $author_id = (int) $post->post_author;
        $author_name = $author_id ? get_the_author_meta('display_name', $author_id) : 'Seleccionar autor...';
        $nonce = wp_create_nonce('rc_author_edit_nonce');
        ?>
        <div id="rc-backend-author-ui" class="rc-author-admin-ui">
            <div class="current-author-display mb-2" style="font-weight: 600; font-size: 13px;">
                Actual: <span id="rc-author-display-name-header" style="color: #2271b1;"><?php echo esc_html($author_name); ?></span>
            </div>
            
            <div class="relative w-full">
                <input type="text" id="rc-author-search-input" class="components-text-control__input" style="width: 100%;" placeholder="Buscar profesor..." autocomplete="off">
                <input type="hidden" name="rc_post_author_id" id="rc-post-author-id-hidden" value="<?php echo $author_id; ?>">
                <div id="rc-author-search-results" class="hidden absolute left-0 top-full mt-1 w-full bg-white text-gray-800 rounded shadow-xl max-h-48 overflow-y-auto z-[9999] border border-gray-100 p-1"></div>
            </div>

            <div id="rc-author-edit-status" class="text-[10px] mt-2 font-bold" style="font-size: 10px; min-height: 15px;"></div>
        </div>
        
        <style>
            .rc-author-admin-ui .hidden { display: none !important; }
            .author-result-item { padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f1; border-radius: 4px; }
            .author-result-item:hover { background-color: #f0f0f1; }
            .author-result-item:last-child { border-bottom: none; }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('rc-author-search-input');
            const resultsDiv = document.getElementById('rc-author-search-results');
            const hiddenInput = document.getElementById('rc-post-author-id-hidden');
            const nameDisplay = document.getElementById('rc-author-display-name-header');
            const statusDiv = document.getElementById('rc-author-edit-status');

            if (!searchInput) return;

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
                    resultsDiv.innerHTML = '<div class="px-3 py-2 text-xs text-gray-400 italic">No se encontraron profesores</div>';
                } else {
                    resultsDiv.innerHTML = users.map(u => `
                        <div class="author-result-item" data-id="${u.ID}" data-name="${u.display_name}">
                            <div style="font-weight: 600;">${u.display_name}</div>
                            <div style="font-size: 10px; color: #646970;">${u.user_email}</div>
                        </div>
                    `).join('');
                }
                resultsDiv.classList.remove('hidden');

                resultsDiv.querySelectorAll('.author-result-item').forEach(item => {
                    item.onclick = (e) => {
                        e.stopPropagation();
                        hiddenInput.value = item.dataset.id;
                        nameDisplay.textContent = item.dataset.name;
                        searchInput.value = '';
                        resultsDiv.classList.add('hidden');
                        statusDiv.textContent = 'Seleccionado. Recuerda Guardar el producto.';
                        statusDiv.style.color = '#d63638';
                    };
                });
            }

            // Close results on click outside
            document.addEventListener('click', (e) => {
                if (!resultsDiv.contains(e.target) && e.target !== searchInput) {
                    resultsDiv.classList.add('hidden');
                }
            });
        });
        </script>
        <?php
    }

    public static function save_author_metabox($post_id, $post) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if (!isset($_POST['rc_post_author_id'])) return;

        $new_author_id = absint($_POST['rc_post_author_id']);
        if ($new_author_id > 0 && $new_author_id !== (int) $post->post_author) {
            // Remove this action to avoid infinite loop when wp_update_post is called
            remove_action('save_post_product', [__CLASS__, 'save_author_metabox']);
            
            wp_update_post([
                'ID' => $post_id,
                'post_author' => $new_author_id
            ]);

            // Sync with LearnDash course if linked
            $linked_course_id = get_post_meta($post_id, '_related_course_id', true);
            if (!$linked_course_id) {
                $linked_course_id = get_post_meta($post_id, '_related_course', true);
            }
            if ($linked_course_id) {
                wp_update_post([
                    'ID' => absint($linked_course_id),
                    'post_author' => $new_author_id
                ]);
            }

            add_action('save_post_product', [__CLASS__, 'save_author_metabox'], 10, 2);
        }
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
