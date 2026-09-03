<?php
/**
 * Cliente de Licenciamento - ABC Engagement Toast
 * 
 * Gerencia a ativação, verificação remota com allysonbelo.com,
 * suporte a sites Headless (Next.js/React), cache de status e bloqueio.
 */

if (!defined('ABSPATH')) {
    exit;
}

class ABC_Toast_License_Client {

    const OPTION_KEY              = 'abc_toast_license_key';
    const OPTION_FRONTEND_DOMAIN  = 'abc_toast_frontend_domain';
    const OPTION_DATA             = 'abc_toast_license_data';
    const TRANSIENT_STATUS        = 'abc_toast_license_transient';
    const CACHE_HOURS             = 24;
    const GRACE_PERIOD_HOURS      = 72;

    public function __construct() {
        add_action('admin_menu', array($this, 'register_license_menu'));
        add_action('admin_init', array($this, 'handle_license_actions'));
        add_action('admin_notices', array($this, 'render_admin_notices'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Retorna a URL do servidor de licenças.
     * Em produção aponta para allysonbelo.com.
     * Em ambiente local/localhost, aponta automaticamente para o próprio host local para testes.
     */
    public static function get_api_endpoint($endpoint = 'validate') {
        $custom_server = apply_filters('abc_toast_license_server_url', '');
        if (!empty($custom_server)) {
            return trailingslashit($custom_server) . $endpoint;
        }

        $host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : '';
        
        // Se estiver testando em ambiente de desenvolvimento local
        if (
            strpos($host, 'localhost') !== false ||
            strpos($host, 'local') !== false ||
            strpos($host, '127.0.0.1') !== false ||
            (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'local')
        ) {
            return trailingslashit(get_site_url()) . 'wp-json/abc-licenses/v1/' . $endpoint;
        }

        // Servidor Oficial de Produção
        return 'https://allysonbelo.com/wp-json/abc-licenses/v1/' . $endpoint;
    }

    /**
     * Retorna o domínio limpo do site.
     * Se houver um Domínio de Frontend cadastrado (ex: roteirodeviagem.org), utiliza ele.
     */
    public static function get_current_domain() {
        $frontend_domain = trim(get_option(self::OPTION_FRONTEND_DOMAIN, ''));
        if (!empty($frontend_domain)) {
            $domain = preg_replace('#^https?://#i', '', $frontend_domain);
            $domain = preg_replace('#^www\.#i', '', $domain);
            $domain = strtok($domain, '/');
            $domain = strtok($domain, ':');
            return strtolower(trim($domain));
        }

        $domain = parse_url(get_site_url(), PHP_URL_HOST);
        if (empty($domain) && isset($_SERVER['HTTP_HOST'])) {
            $domain = $_SERVER['HTTP_HOST'];
        }
        $domain = preg_replace('#^www\.#i', '', $domain);
        $domain = strtok($domain, ':'); // remove porta se houver
        return strtolower(trim($domain));
    }

    /**
     * Verifica se a licença está atualmente ativa e válida.
     * Retorna booleano imediato (com suporte a cache).
     */
    public static function is_license_active() {
        $key = trim(get_option(self::OPTION_KEY, ''));
        if (empty($key)) {
            return false;
        }

        // Verifica cache de curta duração
        $cached_valid = get_transient(self::TRANSIENT_STATUS);
        if ($cached_valid !== false) {
            return (bool) $cached_valid;
        }

        // Se o cache expirou, executa a checagem remota
        $instance = new self();
        $result = $instance->remote_validate($key);

        return (bool) $result['success'];
    }

    /**
     * Validação Remota com a API de Licenças
     */
    public function remote_validate($license_key) {
        $license_key = trim($license_key);
        if (empty($license_key)) {
            $this->store_status(false, 'empty_key', __('Nenhuma chave de licença foi informada.', 'abc-engagement-toast'));
            return array('success' => false, 'message' => 'Chave vazia');
        }

        $api_url = self::get_api_endpoint('validate');
        $domain  = self::get_current_domain();

        $payload = array(
            'license_key'    => $license_key,
            'domain'         => $domain,
            'site_url'       => get_site_url(),
            'plugin_version' => defined('ABC_ENGAGEMENT_TOAST_VERSION') ? ABC_ENGAGEMENT_TOAST_VERSION : '1.0.0',
        );

        $response = wp_remote_post($api_url, array(
            'timeout'   => 15,
            'sslverify' => false, // Evita falha com certificados autoassinados em localhost
            'body'      => $payload,
            'headers'   => array(
                'Accept' => 'application/json',
            )
        ));

        // Em caso de falha de conexão com a internet ou servidor temporariamente fora
        if (is_wp_error($response)) {
            $data = get_option(self::OPTION_DATA, array());
            $last_check = isset($data['last_checked']) ? $data['last_checked'] : 0;
            $was_active = isset($data['status']) && $data['status'] === 'active';

            // Grace period: se já estava ativa e a falha foi de conexão, tolera até 72 horas
            if ($was_active && (time() - $last_check) < (self::GRACE_PERIOD_HOURS * 3600)) {
                set_transient(self::TRANSIENT_STATUS, 1, 6 * 3600); // 6 horas
                return array('success' => true, 'message' => 'Servidor indisponível, período de carência ativo.');
            }

            $error_message = $response->get_error_message();
            $this->store_status(false, 'connection_error', 'Falha ao conectar com o servidor de licença: ' . $error_message);
            return array('success' => false, 'message' => $error_message);
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code === 200 && is_array($body) && !empty($body['success'])) {
            // Licença VÁLIDA
            set_transient(self::TRANSIENT_STATUS, 1, self::CACHE_HOURS * 3600);

            $info = isset($body['data']) ? $body['data'] : array();
            $save_data = array(
                'status'       => 'active',
                'code'         => 'license_active',
                'client_name'  => isset($info['client_name']) ? $info['client_name'] : '',
                'domain'       => isset($info['domain']) ? $info['domain'] : $domain,
                'expires_at'   => isset($info['expires_at']) ? $info['expires_at'] : 'lifetime',
                'is_lifetime'  => !empty($info['is_lifetime']),
                'last_checked' => time(),
                'message'      => isset($body['message']) ? $body['message'] : 'Licença ativa com sucesso.',
            );
            update_option(self::OPTION_DATA, $save_data);

            return array('success' => true, 'message' => $save_data['message']);
        } else {
            // Licença INVÁLIDA, SUSPENSA ou EXPIRADA
            delete_transient(self::TRANSIENT_STATUS);

            $msg = (is_array($body) && isset($body['message'])) ? $body['message'] : __('Chave de licença inválida.', 'abc-engagement-toast');
            $err_code = (is_array($body) && isset($body['code'])) ? $body['code'] : 'invalid_license';

            $this->store_status(false, $err_code, $msg);
            return array('success' => false, 'message' => $msg);
        }
    }

    /**
     * Salva o status nos dados locais
     */
    private function store_status($is_active, $code, $message) {
        $data = get_option(self::OPTION_DATA, array());
        $data['status']       = $is_active ? 'active' : 'inactive';
        $data['code']         = $code;
        $data['message']      = $message;
        $data['last_checked'] = time();
        update_option(self::OPTION_DATA, $data);
        if (!$is_active) {
            delete_transient(self::TRANSIENT_STATUS);
        }
    }

    /**
     * Registra o menu de licença dentro de "Toasts (Popups)"
     */
    public function register_license_menu() {
        add_submenu_page(
            'edit.php?post_type=abc_popup',
            __('Licença do Plugin', 'abc-engagement-toast'),
            __('🔑 Licença', 'abc-engagement-toast'),
            'manage_options',
            'abc-toast-license',
            array($this, 'render_license_page')
        );
    }

    /**
     * Enfileira estilos na página da licença
     */
    public function enqueue_admin_assets($hook) {
        if ($hook === 'abc_popup_page_abc-toast-license') {
            wp_enqueue_style(
                'abc-toast-admin-license',
                ABC_ENGAGEMENT_TOAST_URL . 'assets/css/admin-license.css',
                array(),
                defined('ABC_ENGAGEMENT_TOAST_VERSION') ? ABC_ENGAGEMENT_TOAST_VERSION : '1.0.0'
            );
        }
    }

    /**
     * Processamento de formulários (Ativar, Revalidar, Desativar)
     */
    public function handle_license_actions() {
        if (!isset($_POST['abc_toast_license_action'])) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        check_admin_referer('abc_toast_license_verify', 'abc_toast_license_nonce');

        $action = sanitize_text_field($_POST['abc_toast_license_action']);

        if ($action === 'activate' || $action === 'revalidate') {
            $key = sanitize_text_field(isset($_POST['abc_license_key']) ? $_POST['abc_license_key'] : '');
            if (!empty($key)) {
                update_option(self::OPTION_KEY, $key);
            }

            // Salva domínio do frontend (Headless) se informado
            if (isset($_POST['abc_frontend_domain'])) {
                $raw_fe = sanitize_text_field($_POST['abc_frontend_domain']);
                $clean_fe = preg_replace('#^https?://#i', '', $raw_fe);
                $clean_fe = preg_replace('#^www\.#i', '', $clean_fe);
                $clean_fe = strtok($clean_fe, '/');
                $clean_fe = strtok($clean_fe, ':');
                update_option(self::OPTION_FRONTEND_DOMAIN, strtolower(trim($clean_fe)));
            }

            delete_transient(self::TRANSIENT_STATUS);

            $current_key = get_option(self::OPTION_KEY, '');
            $result = $this->remote_validate($current_key);

            if ($result['success']) {
                set_transient('abc_toast_license_feedback', array('type' => 'success', 'message' => __('Licença ativada com sucesso! O Engagement Toast está liberado para exibição.', 'abc-engagement-toast')), 30);
            } else {
                set_transient('abc_toast_license_feedback', array('type' => 'error', 'message' => $result['message']), 30);
            }

            wp_safe_redirect(admin_url('edit.php?post_type=abc_popup&page=abc-toast-license'));
            exit;
        }

        if ($action === 'deactivate') {
            $key = get_option(self::OPTION_KEY, '');
            $api_url = self::get_api_endpoint('deactivate');

            if (!empty($key)) {
                wp_remote_post($api_url, array(
                    'timeout'   => 15,
                    'sslverify' => false,
                    'body'      => array(
                        'license_key' => $key,
                        'domain'      => self::get_current_domain()
                    )
                ));
            }

            delete_option(self::OPTION_KEY);
            delete_option(self::OPTION_FRONTEND_DOMAIN);
            delete_option(self::OPTION_DATA);
            delete_transient(self::TRANSIENT_STATUS);

            set_transient('abc_toast_license_feedback', array('type' => 'info', 'message' => __('Licença desativada deste site com sucesso.', 'abc-engagement-toast')), 30);
            wp_safe_redirect(admin_url('edit.php?post_type=abc_popup&page=abc-toast-license'));
            exit;
        }
    }

    /**
     * Renderização da Página de Licença
     */
    public function render_license_page() {
        $license_key     = get_option(self::OPTION_KEY, '');
        $frontend_domain = get_option(self::OPTION_FRONTEND_DOMAIN, '');
        $data            = get_option(self::OPTION_DATA, array());
        $feedback        = get_transient('abc_toast_license_feedback');
        if ($feedback) {
            delete_transient('abc_toast_license_feedback');
        }

        $is_active     = isset($data['status']) && $data['status'] === 'active';
        $client_name   = isset($data['client_name']) ? $data['client_name'] : '';
        $domain        = isset($data['domain']) ? $data['domain'] : self::get_current_domain();
        $expires_at    = isset($data['expires_at']) ? $data['expires_at'] : '';
        $is_lifetime   = !empty($data['is_lifetime']) || $expires_at === 'lifetime';
        $last_checked  = isset($data['last_checked']) ? $data['last_checked'] : 0;
        $error_message = isset($data['message']) ? $data['message'] : '';

        // Snippet para site Headless (inclui versão para furar cache de Cloudflare/CDN)
        $headless_script_url = ABC_ENGAGEMENT_TOAST_URL . 'assets/js/headless-loader.js?v=' . ABC_ENGAGEMENT_TOAST_VERSION;
        $headless_embed_code = '<script src="' . esc_url($headless_script_url) . '" async></script>';
        $headless_api_url    = trailingslashit(get_site_url()) . 'wp-json/abc-toast/v1/toasts';
        ?>
        <div class="wrap abc-license-wrap">
            <div class="abc-license-header">
                <h1>
                    <span class="dashicons dashicons-shield" style="font-size: 28px; width: 28px; height: 28px;"></span>
                    <?php _e('Ativação do ABC Engagement Toast', 'abc-engagement-toast'); ?>
                </h1>
                <p><?php _e('Para que os Toasts e Popups sejam renderizados no frontend (tradicional ou Headless), é necessário ativar uma chave de licença emitida em allysonbelo.com.', 'abc-engagement-toast'); ?></p>
            </div>

            <?php if ($feedback): ?>
                <div class="abc-license-notice-inline <?php echo esc_attr($feedback['type']); ?>">
                    <strong><?php echo esc_html($feedback['message']); ?></strong>
                </div>
            <?php endif; ?>

            <div class="abc-license-card">
                <!-- Banner de Status -->
                <?php if ($is_active): ?>
                    <div class="abc-license-status-banner is-active">
                        <div class="abc-status-indicator">
                            <span class="abc-status-dot"></span>
                            <span><?php _e('Licença Ativa e Funcionando Normalmente', 'abc-engagement-toast'); ?></span>
                        </div>
                        <span style="font-size: 13px; font-weight: 600;"><?php _e('Toasts Habilitados no Site / Headless', 'abc-engagement-toast'); ?></span>
                    </div>
                <?php elseif (!empty($license_key)): ?>
                    <div class="abc-license-status-banner is-inactive">
                        <div class="abc-status-indicator">
                            <span class="abc-status-dot"></span>
                            <span><?php _e('Licença Inválida ou Suspensa', 'abc-engagement-toast'); ?></span>
                        </div>
                        <span style="font-size: 13px; font-weight: 600;"><?php _e('Toasts Bloqueados no Frontend e na REST API', 'abc-engagement-toast'); ?></span>
                    </div>
                <?php else: ?>
                    <div class="abc-license-status-banner is-unregistered">
                        <div class="abc-status-indicator">
                            <span class="abc-status-dot"></span>
                            <span><?php _e('Nenhuma Licença Ativada', 'abc-engagement-toast'); ?></span>
                        </div>
                        <span style="font-size: 13px;"><?php _e('Aguardando Chave', 'abc-engagement-toast'); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Formulário de Ativação -->
                <form method="post" action="">
                    <?php wp_nonce_field('abc_toast_license_verify', 'abc_toast_license_nonce'); ?>

                    <div class="abc-license-form-group">
                        <label for="abc_license_key"><?php _e('Chave de Licença (License Key)', 'abc-engagement-toast'); ?></label>
                        <div class="abc-license-input-wrapper">
                            <input 
                                type="text" 
                                id="abc_license_key" 
                                name="abc_license_key" 
                                class="abc-license-input" 
                                value="<?php echo esc_attr($license_key); ?>" 
                                placeholder="ABCT-XXXX-XXXX-XXXX-XXXX" 
                                required
                            />
                            <button type="submit" name="abc_toast_license_action" value="activate" class="button abc-btn-primary">
                                <?php echo $is_active ? __('Atualizar Configurações', 'abc-engagement-toast') : __('Ativar Licença', 'abc-engagement-toast'); ?>
                            </button>
                        </div>
                        <p class="description" style="margin-top: 6px; color: #64748b;">
                            <?php _e('Insira a chave fornecida pelo desenvolvedor (Allyson Belo).', 'abc-engagement-toast'); ?>
                        </p>
                    </div>

                    <!-- Configuração de Domínio Headless -->
                    <div class="abc-license-form-group" style="margin-top: 20px;">
                        <label for="abc_frontend_domain">
                            <?php _e('🌐 Domínio do Frontend (Apenas se o site for Headless)', 'abc-engagement-toast'); ?>
                        </label>
                        <input 
                            type="text" 
                            id="abc_frontend_domain" 
                            name="abc_frontend_domain" 
                            class="regular-text" 
                            style="width: 100%; max-width: 450px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;"
                            value="<?php echo esc_attr($frontend_domain); ?>" 
                            placeholder="ex: roteirodeviagem.org" 
                        />
                        <p class="description" style="margin-top: 6px; color: #64748b;">
                            <?php _e('Deixe em branco se o site for um WordPress normal. Se for Headless (Next.js/React/Astro), informe o domínio onde o visitante acessa o site para que a licença seja validada nele.', 'abc-engagement-toast'); ?>
                        </p>
                    </div>

                    <?php if (!$is_active && !empty($error_message)): ?>
                        <div class="abc-license-notice-inline error">
                            <strong><?php _e('Aviso do Servidor:', 'abc-engagement-toast'); ?></strong> <?php echo esc_html($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($license_key)): ?>
                        <!-- Detalhes da Licença -->
                        <div class="abc-license-details-grid">
                            <div class="abc-detail-box">
                                <div class="abc-detail-label"><?php _e('Cliente / Projeto', 'abc-engagement-toast'); ?></div>
                                <div class="abc-detail-value"><?php echo !empty($client_name) ? esc_html($client_name) : '—'; ?></div>
                            </div>
                            <div class="abc-detail-box">
                                <div class="abc-detail-label"><?php _e('Domínio Vinculado', 'abc-engagement-toast'); ?></div>
                                <div class="abc-detail-value"><code><?php echo esc_html($domain); ?></code></div>
                            </div>
                            <div class="abc-detail-box">
                                <div class="abc-detail-label"><?php _e('Validade', 'abc-engagement-toast'); ?></div>
                                <div class="abc-detail-value">
                                    <?php 
                                    if ($is_lifetime) {
                                        echo '<span style="color:#16a34a;">' . __('Vitalícia', 'abc-engagement-toast') . '</span>';
                                    } elseif (!empty($expires_at)) {
                                        echo esc_html(date_i18n(get_option('date_format'), strtotime($expires_at)));
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="abc-detail-box">
                                <div class="abc-detail-label"><?php _e('Última Verificação', 'abc-engagement-toast'); ?></div>
                                <div class="abc-detail-value">
                                    <?php echo $last_checked ? human_time_diff($last_checked, time()) . ' ' . __('atrás', 'abc-engagement-toast') : '—'; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Ações Secundárias -->
                        <div class="abc-license-actions">
                            <button type="submit" name="abc_toast_license_action" value="revalidate" class="button abc-btn-secondary">
                                <span class="dashicons dashicons-update" style="vertical-align: middle; font-size: 16px;"></span>
                                <?php _e('Verificar Status Novamente', 'abc-engagement-toast'); ?>
                            </button>
                            <button type="submit" name="abc_toast_license_action" value="deactivate" class="button abc-btn-danger" onclick="return confirm('<?php _e('Deseja realmente desativar esta licença? Os Toasts pararão de ser exibidos.', 'abc-engagement-toast'); ?>')">
                                <?php _e('Desativar Licença', 'abc-engagement-toast'); ?>
                            </button>
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Card de Integração Headless -->
            <div class="abc-license-card" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                <h3 style="margin-top: 0; display: flex; align-items: center; gap: 8px; color: #0f172a;">
                    <span class="dashicons dashicons-rest-api" style="color: #f9a045;"></span>
                    <?php _e('Como Usar em Sites Headless (Next.js, React, Astro, HTML)', 'abc-engagement-toast'); ?>
                </h3>
                <p style="color: #475569; font-size: 14px; margin-bottom: 16px;">
                    <?php _e('Se o seu site utiliza um frontend desacoplado (como <code>roteirodeviagem.org</code>), você pode integrar todos os toasts e popups de forma 100% automática colando o script abaixo no <code>&lt;head&gt;</code> do seu frontend:', 'abc-engagement-toast'); ?>
                </p>

                <div class="abc-headless-code-box" style="background: #0f172a; border: 1px solid #1e293b; border-radius: 8px; margin-bottom: 16px; overflow: hidden;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 14px; background: #1e293b; border-bottom: 1px solid #334155; flex-wrap: wrap; gap: 8px;">
                        <span style="color: #94a3b8; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
                            <span class="dashicons dashicons-editor-code" style="font-size: 16px; width: 16px; height: 16px; line-height: 16px; color: #38bdf8;"></span>
                            <?php _e('Código HTML para o Frontend', 'abc-engagement-toast'); ?>
                        </span>
                        <button type="button" class="button button-small abc-copy-btn" onclick="abcCopyHeadlessSnippet(this)" style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; height: 28px; line-height: 26px; padding: 0 12px; background: #334155; border: 1px solid #475569; color: #f8fafc; cursor: pointer; border-radius: 6px; transition: all 0.2s ease;">
                            <span class="dashicons dashicons-admin-page" style="font-size: 14px; width: 14px; height: 14px; line-height: 14px;"></span>
                            <span class="abc-copy-text"><?php _e('Copiar Código', 'abc-engagement-toast'); ?></span>
                        </button>
                    </div>
                    <div style="padding: 14px 16px; overflow-x: auto; background: #0b1120;">
                        <code id="abc-headless-snippet" style="color: #38bdf8; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 13px; line-height: 1.6; word-break: break-all; display: block; white-space: pre-wrap;"><?php echo esc_html($headless_embed_code); ?></code>
                    </div>
                </div>

                <script>
                function abcCopyHeadlessSnippet(btn) {
                    const text = document.getElementById('abc-headless-snippet').innerText.trim();
                    navigator.clipboard.writeText(text).then(function() {
                        const textSpan = btn.querySelector('.abc-copy-text');
                        const iconSpan = btn.querySelector('.dashicons');
                        const originalText = textSpan.innerText;
                        btn.style.background = '#059669';
                        btn.style.borderColor = '#10b981';
                        textSpan.innerText = '<?php echo esc_js(__('Copiado!', 'abc-engagement-toast')); ?>';
                        iconSpan.className = 'dashicons dashicons-yes';
                        setTimeout(function() {
                            btn.style.background = '#334155';
                            btn.style.borderColor = '#475569';
                            textSpan.innerText = originalText;
                            iconSpan.className = 'dashicons dashicons-admin-page';
                        }, 2000);
                    });
                }
                </script>

                <p style="font-size: 13px; color: #64748b; margin: 0;">
                    <strong><?php _e('Endpoint REST API Protegido:', 'abc-engagement-toast'); ?></strong>
                    <code style="background: #e2e8f0; padding: 2px 6px; border-radius: 4px;"><?php echo esc_html($headless_api_url); ?></code>
                    <br>
                    <em><?php _e('Nota: Se a licença estiver inativa ou suspensa, a API bloqueia a entrega dos toasts (HTTP 403) automaticamente.', 'abc-engagement-toast'); ?></em>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Alerta administrativo global se a licença estiver inativa
     */
    public function render_admin_notices() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $screen = get_current_screen();
        if ($screen && $screen->id === 'abc_popup_page_abc-toast-license') {
            return;
        }

        if (!self::is_license_active()) {
            $url = admin_url('edit.php?post_type=abc_popup&page=abc-toast-license');
            ?>
            <div class="notice notice-warning is-dismissible" style="border-left-color: #f9a045;">
                <p>
                    <strong><?php _e('⚠️ ABC Engagement Toast:', 'abc-engagement-toast'); ?></strong> 
                    <?php _e('O plugin requer uma chave de licença ativa para exibir os toasts aos visitantes (e na API Headless).', 'abc-engagement-toast'); ?>
                    <a href="<?php echo esc_url($url); ?>" class="button button-small" style="margin-left: 8px;">
                        <?php _e('Ativar Licença Agora', 'abc-engagement-toast'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }
}

/**
 * Função helper global para checagem rápida de licença
 */
function abc_toast_is_license_active() {
    return ABC_Toast_License_Client::is_license_active();
}
