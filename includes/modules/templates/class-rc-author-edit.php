<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles front-end author editing for admins on course pages.
 * Simplified inline version.
 */
class RC_Author_Edit {

    public static function init() {
        add_action('wp_ajax_rc_search_authors', [__CLASS__, 'ajax_search_authors']);
        add_action('wp_ajax_rc_update_course_author', [__CLASS__, 'ajax_update_author']);
        add_action('wp_footer', [__CLASS__, 'render_js']);
    }

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
        $updated = wp_update_post(['ID' => $course_id, 'post_author' => $author_id]);
        if (is_wp_error($updated)) {
            wp_send_json_error($updated->get_error_message());
        }
        $user = get_userdata($author_id);
        wp_send_json_success(['display_name' => $user->display_name]);
    }

    public static function render_js() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $nonce = wp_create_nonce('rc_author_edit_nonce');
        ?>
        <style>
            #rc-author-inline-edit.hidden, #rc-author-info-wrap.hidden { display: none !important; }
            #rc-author-search-results.hidden { display: none !important; }
            .author-result-item:hover { background-color: #f3f4f6; }
        </style>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editBtn = document.getElementById('rc-edit-author-btn');
            const cancelBtn = document.getElementById('rc-cancel-author-edit');
            const infoWrap = document.getElementById('rc-author-info-wrap');
            const editWrap = document.getElementById('rc-author-inline-edit');
            const searchInput = document.getElementById('rc-author-search-input');
            const resultsDiv = document.getElementById('rc-author-search-results');
            const statusDiv = document.getElementById('rc-author-edit-status');
            const displayNameSpan = document.getElementById('rc-author-display-name');

            if (!editBtn || !editWrap) return;

            editBtn.addEventListener('click', () => {
                infoWrap.classList.add('hidden');
                editWrap.classList.remove('hidden');
                searchInput.focus();
            });

            cancelBtn.addEventListener('click', () => {
                editWrap.classList.add('hidden');
                infoWrap.classList.remove('hidden');
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
                            if (res.success) renderResults(res.data);
                        });
                }, 300);
            });

            function renderResults(users) {
                if (users.length === 0) {
                    resultsDiv.innerHTML = '<div class="px-3 py-2 text-xs text-gray-500">No hay resultados</div>';
                } else {
                    resultsDiv.innerHTML = users.map(u => `
                        <div class="px-3 py-2 cursor-pointer text-sm text-gray-700 author-result-item border-b border-gray-100 last:border-0" data-id="${u.ID}" data-name="${u.display_name}">
                            <div class="font-bold">${u.display_name}</div>
                            <div class="text-[10px] text-gray-400">${u.user_email}</div>
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
                statusDiv.textContent = '...';
                const formData = new FormData();
                formData.append('action', 'rc_update_course_author');
                formData.append('nonce', '<?php echo $nonce; ?>');
                formData.append('course_id', '<?php echo is_singular() ? get_the_ID() : 0; ?>');
                formData.append('author_id', authorId);
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        displayNameSpan.textContent = authorName;
                        editWrap.classList.add('hidden');
                        infoWrap.classList.remove('hidden');
                        resultsDiv.classList.add('hidden');
                        searchInput.value = '';
                        statusDiv.textContent = '';
                    } else {
                        statusDiv.textContent = 'Error';
                    }
                });
            }
        });
        </script>
        <?php
    }
}
