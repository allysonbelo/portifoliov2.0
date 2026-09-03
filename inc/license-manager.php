<?php
/**
 * Gerenciador de Licenças ABC Tech
 *
 * Módulo de servidor de licenças centralizado para o site allysonbelo.com.
 * Registra o CPT de licenças, painel administrativo, gerador de chaves seguras
 * e a REST API para validação e ativação remota do plugin ABC Engagement Toast.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 1. Verifica se o site atual está autorizado a atuar como Servidor de Licenças.
 * Funciona em allysonbelo.com e em ambientes de desenvolvimento local.
 */
function abc_license_is_server_authorized() {
    $host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : '';
    
    // Permitido em allysonbelo.com e em ambientes locais/desenvolvimento
    if (
        strpos($host, 'allysonbelo.com') !== false ||
        strpos($host, 'localhost') !== false ||
        strpos($host, 'local') !== false ||
        strpos($host, '127.0.0.1') !== false ||
        (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'local')
    ) {
        return true;
    }

    return false;
}

// Se não for o servidor autorizado, encerra para proteger outros ambientes
if (!abc_license_is_server_authorized()) {
    return;
}

/**
 * 2. Registrar Custom Post Type: `abc_license`
 */
function abc_license_register_cpt() {
    $labels = array(
        'name'                  => _x('Licenças ABC', 'Post Type General Name', 'abc-tech'),
        'singular_name'         => _x('Licença', 'Post Type Singular Name', 'abc-tech'),
        'menu_name'             => __('Licenças ABC', 'abc-tech'),
        'name_admin_bar'        => __('Licença', 'abc-tech'),
        'archives'              => __('Arquivo de Licenças', 'abc-tech'),
        'attributes'            => __('Atributos da Licença', 'abc-tech'),
        'all_items'             => __('Todas as Licenças', 'abc-tech'),
        'add_new_item'          => __('Adicionar Nova Licença', 'abc-tech'),
        'add_new'               => __('Nova Licença', 'abc-tech'),
        'new_item'              => __('Nova Licença', 'abc-tech'),
        'edit_item'             => __('Editar Licença', 'abc-tech'),
        'update_item'           => __('Atualizar Licença', 'abc-tech'),
        'view_item'             => __('Ver Licença', 'abc-tech'),
        'view_items'            => __('Ver Licenças', 'abc-tech'),
        'search_items'          => __('Buscar Licenças', 'abc-tech'),
        'not_found'             => __('Nenhuma licença encontrada', 'abc-tech'),
        'not_found_in_trash'    => __('Nenhuma licença na lixeira', 'abc-tech'),
    );

    $args = array(
        'label'                 => __('Licença', 'abc-tech'),
        'description'           => __('Gerenciamento central de licenças para plugins ABC', 'abc-tech'),
        'labels'                => $labels,
        'supports'              => array('title'),
        'hierarchical'          => false,
        'public'                => false,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 80,
        'menu_icon'             => 'dashicons-shield',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => false,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => true,
        'publicly_queryable'    => false,
        'capability_type'       => 'post',
        'show_in_rest'          => false,
    );

    register_post_type('abc_license', $args);
}
add_action('init', 'abc_license_register_cpt');

/**
 * 3. Função auxiliar para gerar Chave de Licença formatada
 * Formato: ABCT-XXXX-XXXX-XXXX-XXXX (16 chars alfanuméricos aleatórios)
 */
function abc_license_generate_key($prefix = 'ABCT') {
    $bytes = random_bytes(8);
    $hex = strtoupper(bin2hex($bytes));
    return $prefix . '-' . substr($hex, 0, 4) . '-' . substr($hex, 4, 4) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4);
}

/**
 * 4. Normalizar domínio (remover https://, http://, www., barras e query strings)
 */
function abc_license_clean_domain($domain) {
    if (empty($domain)) return '';
    $domain = trim($domain);
    $domain = preg_replace('#^https?://#i', '', $domain);
    $domain = preg_replace('#^www\.#i', '', $domain);
    $domain = strtok($domain, '/');
    $domain = strtok($domain, ':');
    return strtolower(trim($domain));
}

/**
 * 5. Meta Box: Detalhes e Configuração da Licença
 */
function abc_license_add_meta_box() {
    add_meta_box(
        'abc_license_details_box',
        __('Configuração e Status da Licença', 'abc-tech'),
        'abc_license_render_meta_box',
        'abc_license',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'abc_license_add_meta_box');

function abc_license_render_meta_box($post) {
    wp_nonce_field('abc_license_save_meta', 'abc_license_meta_nonce');

    $license_key      = get_post_meta($post->ID, '_abc_license_key', true);
    $client_name      = get_post_meta($post->ID, '_abc_client_name', true);
    $client_email     = get_post_meta($post->ID, '_abc_client_email', true);
    $status           = get_post_meta($post->ID, '_abc_status', true) ?: 'active';
    $allowed_domain   = get_post_meta($post->ID, '_abc_allowed_domain', true);
    $activated_domain = get_post_meta($post->ID, '_abc_activated_domain', true);
    $activated_at     = get_post_meta($post->ID, '_abc_activated_at', true);
    $expires_at       = get_post_meta($post->ID, '_abc_expires_at', true);
    $last_ping        = get_post_meta($post->ID, '_abc_last_ping', true);
    $last_ip          = get_post_meta($post->ID, '_abc_last_ip', true);
    $ping_count       = (int) get_post_meta($post->ID, '_abc_ping_count', true);

    if (empty($license_key)) {
        $license_key = abc_license_generate_key();
    }
    ?>
    <style>
        .abc-lic-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 10px; }
        .abc-lic-field { margin-bottom: 18px; }
        .abc-lic-field label { font-weight: 600; display: block; margin-bottom: 6px; color: #1e1e1e; }
        .abc-lic-field input[type="text"], .abc-lic-field input[type="email"], .abc-lic-field input[type="date"], .abc-lic-field select {
            width: 100%; max-width: 400px; padding: 8px 12px; border: 1px solid #8c8f94; border-radius: 6px; font-size: 14px;
        }
        .abc-lic-key-wrap { display: flex; align-items: center; gap: 10px; max-width: 450px; }
        .abc-lic-key-input { font-family: monospace; font-size: 16px !important; font-weight: bold; letter-spacing: 1px; color: #0073aa; background: #f0f6fc !important; border: 1px solid #72aee6 !important; }
        .abc-lic-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-weight: 600; font-size: 12px; text-transform: uppercase; }
        .abc-lic-badge.active { background: #d1fae5; color: #065f46; }
        .abc-lic-badge.suspended { background: #fee2e2; color: #991b1b; }
        .abc-lic-badge.expired { background: #ffedd5; color: #9a3412; }
        .abc-lic-info-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-top: 20px; }
        .abc-lic-info-card h4 { margin: 0 0 12px 0; color: #334155; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
        .abc-lic-info-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .abc-lic-info-table td { padding: 6px 0; border-bottom: 1px solid #edf2f7; }
        .abc-lic-info-table td:first-child { font-weight: 600; color: #64748b; width: 40%; }
        .abc-btn-secondary { background: #f3f4f6; border: 1px solid #d1d5db; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500; transition: all 0.2s; }
        .abc-btn-secondary:hover { background: #e5e7eb; }
    </style>

    <div class="abc-lic-container">
        <!-- Chave de Licença -->
        <div class="abc-lic-field">
            <label for="abc_license_key"><?php _e('Chave de Licença (License Key)', 'abc-tech'); ?></label>
            <div class="abc-lic-key-wrap">
                <input type="text" id="abc_license_key" name="abc_license_key" value="<?php echo esc_attr($license_key); ?>" class="abc-lic-key-input" readonly />
                <button type="button" class="button abc-btn-secondary" onclick="abcGenerateNewKey()"><?php _e('Gerar Nova', 'abc-tech'); ?></button>
                <button type="button" class="button abc-btn-secondary" onclick="abcCopyKey()"><?php _e('Copiar', 'abc-tech'); ?></button>
            </div>
            <p class="description"><?php _e('Esta é a chave que o cliente deve colar nas configurações do plugin.', 'abc-tech'); ?></p>
        </div>

        <div class="abc-lic-grid">
            <div>
                <!-- Status -->
                <div class="abc-lic-field">
                    <label for="abc_status"><?php _e('Status da Licença', 'abc-tech'); ?></label>
                    <select id="abc_status" name="abc_status">
                        <option value="active" <?php selected($status, 'active'); ?>><?php _e('🟢 Ativa (Permite exibição dos Toasts)', 'abc-tech'); ?></option>
                        <option value="suspended" <?php selected($status, 'suspended'); ?>><?php _e('🔴 Suspensa (Bloqueia Toasts imediatamente)', 'abc-tech'); ?></option>
                        <option value="expired" <?php selected($status, 'expired'); ?>><?php _e('🟠 Expirada', 'abc-tech'); ?></option>
                    </select>
                </div>

                <!-- Nome do Cliente / Projeto -->
                <div class="abc-lic-field">
                    <label for="abc_client_name"><?php _e('Nome do Cliente / Projeto', 'abc-tech'); ?></label>
                    <input type="text" id="abc_client_name" name="abc_client_name" value="<?php echo esc_attr($client_name); ?>" placeholder="<?php _e('Ex: Loja Virtual do João', 'abc-tech'); ?>" />
                </div>

                <!-- E-mail de Contato -->
                <div class="abc-lic-field">
                    <label for="abc_client_email"><?php _e('E-mail do Cliente (Opcional)', 'abc-tech'); ?></label>
                    <input type="email" id="abc_client_email" name="abc_client_email" value="<?php echo esc_attr($client_email); ?>" placeholder="<?php _e('cliente@dominio.com.br', 'abc-tech'); ?>" />
                </div>
            </div>

            <div>
                <!-- Domínio Permitido -->
                <div class="abc-lic-field">
                    <label for="abc_allowed_domain"><?php _e('Domínio Autorizado', 'abc-tech'); ?></label>
                    <input type="text" id="abc_allowed_domain" name="abc_allowed_domain" value="<?php echo esc_attr($allowed_domain); ?>" placeholder="<?php _e('ex: meudominio.com.br (ou deixe em branco para amarrar na 1ª ativação)', 'abc-tech'); ?>" />
                    <p class="description"><?php _e('Deixe em branco para registrar automaticamente o primeiro domínio que ativar. Use * para permitir qualquer domínio.', 'abc-tech'); ?></p>
                </div>

                <!-- Data de Expiração -->
                <div class="abc-lic-field">
                    <label for="abc_expires_at"><?php _e('Data de Expiração (Vazio = Vitalícia)', 'abc-tech'); ?></label>
                    <input type="date" id="abc_expires_at" name="abc_expires_at" value="<?php echo esc_attr($expires_at); ?>" />
                    <p class="description"><?php _e('Se definida, o plugin bloqueará os toasts após essa data.', 'abc-tech'); ?></p>
                </div>
            </div>
        </div>

        <!-- Telemetria e Diagnóstico da Ativação -->
        <div class="abc-lic-info-card">
            <h4><?php _e('Status de Uso e Telemetria', 'abc-tech'); ?></h4>
            <table class="abc-lic-info-table">
                <tr>
                    <td><?php _e('Domínio Efetivamente Vinculado:', 'abc-tech'); ?></td>
                    <td>
                        <?php if ($activated_domain): ?>
                            <strong><code><?php echo esc_html($activated_domain); ?></code></strong>
                            <button type="button" class="button-link" style="margin-left: 10px; color:#b91c1c;" onclick="abcResetDomain()"><?php _e('[Desvincular Domínio]', 'abc-tech'); ?></button>
                            <input type="hidden" id="abc_reset_domain_input" name="abc_reset_domain" value="0" />
                        <?php else: ?>
                            <em><?php _e('Ainda não ativado em nenhum domínio', 'abc-tech'); ?></em>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><?php _e('Data da Primeira Ativação:', 'abc-tech'); ?></td>
                    <td><?php echo $activated_at ? esc_html($activated_at) : '—'; ?></td>
                </tr>
                <tr>
                    <td><?php _e('Última Verificação (Ping):', 'abc-tech'); ?></td>
                    <td><?php echo $last_ping ? esc_html($last_ping) : '—'; ?></td>
                </tr>
                <tr>
                    <td><?php _e('Último IP Solicitante:', 'abc-tech'); ?></td>
                    <td><?php echo $last_ip ? esc_html($last_ip) : '—'; ?></td>
                </tr>
                <tr>
                    <td><?php _e('Total de Validações Realizadas:', 'abc-tech'); ?></td>
                    <td><strong><?php echo esc_html($ping_count); ?></strong></td>
                </tr>
            </table>
        </div>
    </div>

    <script>
        function abcGenerateNewKey() {
            if (confirm('<?php _e('Deseja realmente gerar uma nova chave? Se o cliente já estiver utilizando a chave atual, ele precisará atualizar para a nova.', 'abc-tech'); ?>')) {
                const hexChars = '0123456789ABCDEF';
                let newKey = 'ABCT';
                for (let part = 0; part < 4; part++) {
                    newKey += '-';
                    for (let i = 0; i < 4; i++) {
                        newKey += hexChars.charAt(Math.floor(Math.random() * hexChars.length));
                    }
                }
                document.getElementById('abc_license_key').value = newKey;
            }
        }

        function abcCopyKey() {
            const input = document.getElementById('abc_license_key');
            input.select();
            document.execCommand('copy');
            alert('Chave copiada: ' + input.value);
        }

        function abcResetDomain() {
            if (confirm('<?php _e('Deseja desvincular o domínio atual? Isso permitirá que esta chave seja reativada em um novo domínio.', 'abc-tech'); ?>')) {
                document.getElementById('abc_reset_domain_input').value = '1';
                document.getElementById('abc_allowed_domain').value = '';
                alert('<?php _e('Domínio desvinculado. Clique em "Atualizar" para salvar as alterações.', 'abc-tech'); ?>');
            }
        }
    </script>
    <?php
}

/**
 * 6. Salvar Metadados da Licença
 */
function abc_license_save_meta($post_id) {
    if (!isset($_POST['abc_license_meta_nonce']) || !wp_verify_nonce($_POST['abc_license_meta_nonce'], 'abc_license_save_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['abc_license_key'])) {
        update_post_meta($post_id, '_abc_license_key', sanitize_text_field($_POST['abc_license_key']));
    }

    if (isset($_POST['abc_client_name'])) {
        update_post_meta($post_id, '_abc_client_name', sanitize_text_field($_POST['abc_client_name']));
        // Se o título estiver vazio ou padrão, preenche com o nome do cliente
        $post_title = get_the_title($post_id);
        if (empty($post_title) || strpos($post_title, 'Auto Draft') !== false) {
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => sanitize_text_field($_POST['abc_client_name']) ?: 'Licença - ' . sanitize_text_field($_POST['abc_license_key'])
            ));
        }
    }

    if (isset($_POST['abc_client_email'])) {
        update_post_meta($post_id, '_abc_client_email', sanitize_email($_POST['abc_client_email']));
    }

    if (isset($_POST['abc_status'])) {
        update_post_meta($post_id, '_abc_status', sanitize_text_field($_POST['abc_status']));
    }

    if (isset($_POST['abc_allowed_domain'])) {
        $clean = abc_license_clean_domain($_POST['abc_allowed_domain']);
        update_post_meta($post_id, '_abc_allowed_domain', $clean);
    }

    if (isset($_POST['abc_expires_at'])) {
        update_post_meta($post_id, '_abc_expires_at', sanitize_text_field($_POST['abc_expires_at']));
    }

    // Ação de reset de domínio
    if (!empty($_POST['abc_reset_domain']) && $_POST['abc_reset_domain'] === '1') {
        delete_post_meta($post_id, '_abc_activated_domain');
        delete_post_meta($post_id, '_abc_allowed_domain');
        delete_post_meta($post_id, '_abc_activated_at');
    }
}
add_action('save_post_abc_license', 'abc_license_save_meta');

/**
 * 7. Colunas Personalizadas na Listagem de Licenças
 */
function abc_license_columns($columns) {
    $new_columns = array(
        'cb'             => $columns['cb'],
        'title'          => __('Cliente / Identificação', 'abc-tech'),
        'license_key'    => __('Chave de Licença', 'abc-tech'),
        'license_status' => __('Status', 'abc-tech'),
        'domain'         => __('Domínio Vinculado', 'abc-tech'),
        'expires'        => __('Validade', 'abc-tech'),
        'last_ping'      => __('Último Acesso', 'abc-tech'),
        'date'           => __('Criado em', 'abc-tech')
    );
    return $new_columns;
}
add_filter('manage_abc_license_posts_columns', 'abc_license_columns');

function abc_license_column_content($column, $post_id) {
    switch ($column) {
        case 'license_key':
            $key = get_post_meta($post_id, '_abc_license_key', true);
            if ($key) {
                echo '<code style="font-size:13px; font-weight:600; color:#0369a1; background:#f0f9ff; padding:2px 6px; border-radius:4px;">' . esc_html($key) . '</code> ';
                echo '<button type="button" class="button button-small" onclick="navigator.clipboard.writeText(\'' . esc_attr($key) . '\'); this.innerText=\'Copiado!\'; setTimeout(() => this.innerText=\'Copiar\', 1500);" style="vertical-align:middle;">Copiar</button>';
            } else {
                echo '—';
            }
            break;

        case 'license_status':
            $status = get_post_meta($post_id, '_abc_status', true) ?: 'active';
            $labels = array(
                'active'    => array('label' => 'Ativa', 'bg' => '#d1fae5', 'color' => '#065f46'),
                'suspended' => array('label' => 'Suspensa', 'bg' => '#fee2e2', 'color' => '#991b1b'),
                'expired'   => array('label' => 'Expirada', 'bg' => '#ffedd5', 'color' => '#9a3412'),
            );
            $info = isset($labels[$status]) ? $labels[$status] : $labels['active'];
            echo '<span style="display:inline-block; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:700; background:' . esc_attr($info['bg']) . '; color:' . esc_attr($info['color']) . ';">' . esc_html($info['label']) . '</span>';
            break;

        case 'domain':
            $allowed = get_post_meta($post_id, '_abc_allowed_domain', true);
            $activated = get_post_meta($post_id, '_abc_activated_domain', true);
            $display_domain = $activated ?: $allowed;

            if ($display_domain === '*') {
                echo '<span style="color:#0284c7; font-weight:600;">* (Ilimitado)</span>';
            } elseif ($display_domain) {
                echo '<strong><code>' . esc_html($display_domain) . '</code></strong>';
                if ($activated && $allowed && $activated !== $allowed && $allowed !== '*') {
                    echo ' <small style="color:#dc2626;">(divergente)</small>';
                }
            } else {
                echo '<span style="color:#94a3b8; font-style:italic;">Livre (Aguardando ativação)</span>';
            }
            break;

        case 'expires':
            $expires = get_post_meta($post_id, '_abc_expires_at', true);
            if (!empty($expires)) {
                $is_expired = strtotime($expires) < time();
                $date_fmt = date_i18n(get_option('date_format'), strtotime($expires));
                if ($is_expired) {
                    echo '<span style="color:#dc2626; font-weight:600;">Expirou em ' . esc_html($date_fmt) . '</span>';
                } else {
                    echo esc_html($date_fmt);
                }
            } else {
                echo '<span style="color:#16a34a; font-weight:600;">Vitalícia</span>';
            }
            break;

        case 'last_ping':
            $last = get_post_meta($post_id, '_abc_last_ping', true);
            $count = (int) get_post_meta($post_id, '_abc_ping_count', true);
            if (!empty($last)) {
                $time_diff = human_time_diff(strtotime($last), current_time('timestamp'));
                echo esc_html($time_diff) . ' atrás';
                echo '<br><small style="color:#64748b;">' . esc_html($count) . ' verificações</small>';
            } else {
                echo '<span style="color:#94a3b8;">Nunca</span>';
            }
            break;
    }
}
add_action('manage_abc_license_posts_custom_column', 'abc_license_column_content', 10, 2);

/**
 * 8. REST API: Endpoint de Validação de Licença
 * Rota: POST /wp-json/abc-licenses/v1/validate
 */
function abc_license_register_rest_routes() {
    register_rest_route('abc-licenses/v1', '/validate', array(
        'methods'             => WP_REST_Server::CREATABLE, // POST
        'callback'            => 'abc_license_rest_validate',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('abc-licenses/v1', '/deactivate', array(
        'methods'             => WP_REST_Server::CREATABLE, // POST
        'callback'            => 'abc_license_rest_deactivate',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'abc_license_register_rest_routes');

/**
 * Validação da Licença via REST API
 */
function abc_license_rest_validate(WP_REST_Request $request) {
    $license_key = sanitize_text_field($request->get_param('license_key'));
    $raw_domain  = sanitize_text_field($request->get_param('domain'));
    $site_url    = esc_url_raw($request->get_param('site_url'));
    $plugin_ver  = sanitize_text_field($request->get_param('plugin_version'));

    if (empty($license_key)) {
        return new WP_REST_Response(array(
            'success' => false,
            'code'    => 'missing_key',
            'message' => 'A chave de licença não foi informada.'
        ), 400);
    }

    $clean_domain = abc_license_clean_domain($raw_domain);
    if (empty($clean_domain) && !empty($site_url)) {
        $clean_domain = abc_license_clean_domain(parse_url($site_url, PHP_URL_HOST));
    }

    // Busca o post da licença pelo post_meta _abc_license_key
    $query = new WP_Query(array(
        'post_type'      => 'abc_license',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'meta_query'     => array(
            array(
                'key'     => '_abc_license_key',
                'value'   => $license_key,
                'compare' => '='
            )
        )
    ));

    if (!$query->have_posts()) {
        return new WP_REST_Response(array(
            'success' => false,
            'code'    => 'invalid_key',
            'message' => 'Chave de licença não encontrada ou inválida.'
        ), 200); // 200 para facilitar leitura no client
    }

    $license_post = $query->posts[0];
    $post_id = $license_post->ID;

    $status           = get_post_meta($post_id, '_abc_status', true) ?: 'active';
    $client_name      = get_post_meta($post_id, '_abc_client_name', true) ?: get_the_title($post_id);
    $allowed_domain   = get_post_meta($post_id, '_abc_allowed_domain', true);
    $activated_domain = get_post_meta($post_id, '_abc_activated_domain', true);
    $expires_at       = get_post_meta($post_id, '_abc_expires_at', true);
    $ping_count       = (int) get_post_meta($post_id, '_abc_ping_count', true);

    // 1. Verificar se a licença está suspensa
    if ($status === 'suspended') {
        return new WP_REST_Response(array(
            'success' => false,
            'code'    => 'license_suspended',
            'message' => 'Esta licença foi suspensa ou desativada pelo administrador.'
        ), 200);
    }

    // 2. Verificar expiração
    if (!empty($expires_at)) {
        if (strtotime($expires_at) < time()) {
            update_post_meta($post_id, '_abc_status', 'expired');
            return new WP_REST_Response(array(
                'success' => false,
                'code'    => 'license_expired',
                'message' => 'Esta licença expirou em ' . date_i18n(get_option('date_format'), strtotime($expires_at)) . '.'
            ), 200);
        }
    }

    // 3. Validação e Amarração de Domínio
    if (empty($allowed_domain) || $allowed_domain === '') {
        // Primeira ativação: amarra automaticamente ao domínio solicitante
        update_post_meta($post_id, '_abc_allowed_domain', $clean_domain);
        update_post_meta($post_id, '_abc_activated_domain', $clean_domain);
        update_post_meta($post_id, '_abc_activated_at', current_time('mysql'));
        $allowed_domain = $clean_domain;
    } elseif ($allowed_domain !== '*' && $clean_domain !== $allowed_domain) {
        // Domínio não bate com o registrado
        return new WP_REST_Response(array(
            'success' => false,
            'code'    => 'domain_mismatch',
            'message' => sprintf('Esta licença está vinculada exclusivamente ao domínio "%s" e não pode ser utilizada em "%s".', $allowed_domain, $clean_domain)
        ), 200);
    } else {
        // Se ainda não tinha salvo o activated_domain, salva agora
        if (empty($activated_domain)) {
            update_post_meta($post_id, '_abc_activated_domain', $clean_domain);
            update_post_meta($post_id, '_abc_activated_at', current_time('mysql'));
        }
    }

    // 4. Registrar Telemetria (Ping)
    update_post_meta($post_id, '_abc_last_ping', current_time('mysql'));
    update_post_meta($post_id, '_abc_last_ip', sanitize_text_field(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''));
    update_post_meta($post_id, '_abc_ping_count', $ping_count + 1);
    if (!empty($plugin_ver)) {
        update_post_meta($post_id, '_abc_plugin_version', $plugin_ver);
    }

    // 5. Retornar resposta de sucesso
    return new WP_REST_Response(array(
        'success'      => true,
        'code'         => 'license_active',
        'message'      => 'Licença válida e ativa com sucesso.',
        'data'         => array(
            'license_key'  => $license_key,
            'client_name'  => $client_name,
            'domain'       => $clean_domain,
            'status'       => 'active',
            'expires_at'   => $expires_at ?: 'lifetime',
            'is_lifetime'  => empty($expires_at),
            'server_time'  => time(),
        )
    ), 200);
}

/**
 * Desativação da Licença via REST API (para troca de domínio)
 */
function abc_license_rest_deactivate(WP_REST_Request $request) {
    $license_key = sanitize_text_field($request->get_param('license_key'));
    $raw_domain  = sanitize_text_field($request->get_param('domain'));
    $clean_domain = abc_license_clean_domain($raw_domain);

    if (empty($license_key)) {
        return new WP_REST_Response(array(
            'success' => false,
            'code'    => 'missing_key',
            'message' => 'Chave de licença não fornecida.'
        ), 400);
    }

    $query = new WP_Query(array(
        'post_type'      => 'abc_license',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'meta_query'     => array(
            array(
                'key'     => '_abc_license_key',
                'value'   => $license_key,
                'compare' => '='
            )
        )
    ));

    if (!$query->have_posts()) {
        return new WP_REST_Response(array(
            'success' => false,
            'code'    => 'not_found',
            'message' => 'Licença não encontrada.'
        ), 200);
    }

    $post_id = $query->posts[0]->ID;
    $allowed_domain = get_post_meta($post_id, '_abc_allowed_domain', true);

    if ($allowed_domain === $clean_domain || empty($allowed_domain)) {
        delete_post_meta($post_id, '_abc_activated_domain');
        delete_post_meta($post_id, '_abc_allowed_domain');
        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Licença desvinculada com sucesso deste domínio.'
        ), 200);
    }

    return new WP_REST_Response(array(
        'success' => false,
        'message' => 'O domínio solicitante não confere com o domínio vinculado.'
    ), 200);
}
