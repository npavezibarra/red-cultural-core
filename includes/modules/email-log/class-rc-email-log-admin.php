<?php
/**
 * Admin UI for Email Log.
 */

if (!defined('ABSPATH')) {
    exit;
}

final class RC_Email_Log_Admin
{
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('admin_menu', [$this, 'register_admin_pages'], 110);
        add_action('wp_ajax_rc_get_email_content', [$this, 'ajax_get_email_content']);
    }

    public function register_admin_pages()
    {
        $parent_slug = 'red-cultural-pages';

        add_submenu_page(
            $parent_slug,
            'Registro de Emails',
            'Email Log',
            'manage_options',
            'red-cultural-email-log',
            [$this, 'render_admin_page']
        );
    }

    /**
     * AJAX handler to get email content by ID.
     */
    public function ajax_get_email_content()
    {
        check_ajax_referer('rc_email_log_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $id = isset($_GET['id']) ? absint($_GET['id']) : 0;
        if (!$id) {
            wp_send_json_error('Invalid ID');
        }

        $log = RC_Email_Log_DB::get_instance()->get_log($id);
        if (!$log) {
            wp_send_json_error('Log not found');
        }

        // Return the HTML content directly
        echo (string) $log->content;
        exit;
    }

    public function render_admin_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos para ver esta página.', 'red-cultural-core'));
        }

        $paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $per_page = 20;
        $offset = ($paged - 1) * $per_page;

        $db = RC_Email_Log_DB::get_instance();
        $logs = $db->get_logs($per_page, $offset, $search);
        $total_count = $db->get_total_count($search);
        $total_pages = ceil($total_count / $per_page);

        $nonce = wp_create_nonce('rc_email_log_nonce');

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Registro de Emails</h1>
            <hr class="wp-header-end">

            <div class="rc-email-log-header" style="margin-top: 20px; display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px;">
                <p>Aquí se registran todos los correos enviados desde el sitio.</p>
                <form method="get">
                    <input type="hidden" name="page" value="red-cultural-email-log">
                    <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Buscar destinatario o asunto...">
                    <button type="submit" class="button">Buscar</button>
                </form>
            </div>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 15%;">A quién</th>
                        <th style="width: 25%;">Asunto</th>
                        <th style="width: 10%;">Tipo</th>
                        <th style="width: 35%;">Archivo</th>
                        <th style="width: 10%;">Fecha y Hora</th>
                        <th style="width: 10%;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($log->recipient); ?></strong>
                                </td>
                                <td><?php echo esc_html($log->subject); ?></td>
                                <td>
                                    <span class="rc-badge type-<?php echo esc_attr(strtolower($log->email_type)); ?>">
                                        <?php echo esc_html($log->email_type); ?>
                                    </span>
                                </td>
                                <td>
                                    <code style="font-size: 10px; background: #f1f5f9; padding: 2px 4px; border-radius: 3px; color: #64748b;">
                                        <?php echo esc_html($log->file_path ?: 'Desconocido'); ?>
                                    </code>
                                </td>
                                <td><?php echo esc_html(date_i18n('d/m/Y H:i', strtotime($log->sent_at))); ?></td>
                                <td>
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <button type="button" class="button rc-view-email" data-id="<?php echo esc_attr($log->id); ?>">Ver Email</button>
                                        <?php
                                        $thankyou_url = '';
                                        $order_id = isset($log->order_id) ? absint($log->order_id) : 0;
                                        if ($order_id && function_exists('wc_get_order')) {
                                            $order = wc_get_order($order_id);
                                            if ($order instanceof \WC_Order) {
                                                $thankyou_url = wc_get_endpoint_url('order-received', $order->get_id(), wc_get_checkout_url());
                                                $thankyou_url = add_query_arg('key', $order->get_order_key(), $thankyou_url);
                                            }
                                        }
                                        ?>
                                        <?php if ($thankyou_url): ?>
                                            <a class="button" href="<?php echo esc_url($thankyou_url); ?>" target="_blank" rel="noopener noreferrer">Ver Thank You Page</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No se encontraron correos registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
                <div class="tablenav bottom">
                    <div class="tablenav-pages">
                        <span class="displaying-num"><?php echo esc_html("$total_count elementos"); ?></span>
                        <span class="pagination-links">
                            <?php if ($paged > 1): ?>
                                <a class="prev-page button" href="<?php echo esc_url(add_query_arg('paged', $paged - 1)); ?>">&lsaquo;</a>
                            <?php endif; ?>
                            <span class="paging-input">
                                <span class="current-page"><?php echo esc_html($paged); ?></span> de <span class="total-pages"><?php echo esc_html($total_pages); ?></span>
                            </span>
                            <?php if ($paged < $total_pages): ?>
                                <a class="next-page button" href="<?php echo esc_url(add_query_arg('paged', $paged + 1)); ?>">&rsaquo;</a>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Overlay and Modal -->
            <div id="rc-email-overlay" class="rc-overlay" style="display:none;">
                <div class="rc-modal">
                    <div class="rc-modal-header">
                        <h3>Visualizar Correo</h3>
                        <button type="button" class="rc-close-modal">&times;</button>
                    </div>
                    <div class="rc-modal-body">
                        <iframe id="rc-email-frame" style="width: 100%; height: 100%; border: none;"></iframe>
                    </div>
                </div>
            </div>

            <style>
                .rc-badge {
                    padding: 4px 8px;
                    border-radius: 4px;
                    font-size: 11px;
                    font-weight: 600;
                    text-transform: uppercase;
                    background: #f0f0f1;
                    color: #50575e;
                }
                .type-woocommerce { background: #EBDCF2; color: #763F98; }
                .type-contacto { background: #DFF1E4; color: #1E6C3B; }
                .type-viajes { background: #DFF1FB; color: #155E8D; }
                .type-registro { background: #FEF3C7; color: #92400E; }
                .type-cuenta { background: #FCE7F3; color: #9D174D; }

                .rc-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.8);
                    z-index: 99999;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    backdrop-filter: blur(5px);
                }
                .rc-modal {
                    background: #fff;
                    width: 90%;
                    max-width: 900px;
                    height: 85%;
                    border-radius: 12px;
                    display: flex;
                    flex-direction: column;
                    overflow: hidden;
                    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
                }
                .rc-modal-header {
                    padding: 15px 25px;
                    background: #f8fafc;
                    border-bottom: 1px solid #e2e8f0;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .rc-modal-header h3 { margin: 0; font-size: 1.1rem; color: #1e293b; }
                .rc-close-modal {
                    background: none;
                    border: none;
                    font-size: 28px;
                    cursor: pointer;
                    color: #94a3b8;
                    transition: color 0.2s;
                }
                .rc-close-modal:hover { color: #ef4444; }
                .rc-modal-body {
                    flex-grow: 1;
                    background: #f1f5f9;
                }
            </style>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const overlay = document.getElementById('rc-email-overlay');
                    const frame = document.getElementById('rc-email-frame');
                    const closeBtn = document.querySelector('.rc-close-modal');

                    document.querySelectorAll('.rc-view-email').forEach(btn => {
                        btn.onclick = function() {
                            const id = this.getAttribute('data-id');
                            frame.src = 'about:blank';
                            overlay.style.display = 'flex';
                            
                            fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=rc_get_email_content&id=' + id + '&nonce=<?php echo $nonce; ?>')
                                .then(response => response.text())
                                .then(html => {
                                    const doc = frame.contentWindow.document;
                                    doc.open();
                                    doc.write(html);
                                    doc.close();
                                });
                        };
                    });

                    closeBtn.onclick = function() {
                        overlay.style.display = 'none';
                        frame.src = 'about:blank';
                    };

                    overlay.onclick = function(e) {
                        if (e.target === overlay) {
                            closeBtn.onclick();
                        }
                    };
                });
            </script>
        </div>
        <?php
    }
}
