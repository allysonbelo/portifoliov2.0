<?php
/**
 * Renderizador de Frontend, Rota REST API Headless e Bloqueio por Licença
 * 
 * ABC Engagement Toast
 */

if (!defined('ABSPATH')) {
    exit;
}

class ABC_Toast_Frontend {

    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_footer', array($this, 'render_toasts'), 5);
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        add_action('rest_pre_serve_request', array($this, 'send_cors_headers'), 10, 4);
    }

    /**
     * Envia cabeçalhos CORS para permitir que sites headless (ex: roteirodeviagem.org)
     * consumam os dados da REST API sem bloqueio de origem cruzada no navegador.
     */
    public function send_cors_headers($served, $result, $request, $server) {
        $route = $request->get_route();
        if (strpos($route, '/abc-toast/v1/') !== false) {
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        }
        return $served;
    }

    /**
     * Registra os endpoints REST API para consumo Headless e Rastreamento
     */
    public function register_rest_routes() {
        // Rota pública de consulta de toasts
        register_rest_route('abc-toast/v1', '/toasts', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'rest_get_active_toasts'),
            'permission_callback' => '__return_true',
        ));

        // Rota de rastreamento de interações (Views / Clicks)
        register_rest_route('abc-toast/v1', '/track', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'rest_track_interaction'),
            'permission_callback' => '__return_true',
        ));
    }

    /**
     * Callback da REST API para salvar contadores internos de Views e Clicks
     */
    public function rest_track_interaction(WP_REST_Request $request) {
        $raw_input = file_get_contents('php://input');
        $params    = json_decode($raw_input, true);
        if (empty($params)) {
            $params = $request->get_json_params() ?: $request->get_params();
        }

        $toast_id = isset($params['toast_id']) ? intval($params['toast_id']) : 0;
        $action   = isset($params['action']) ? sanitize_text_field($params['action']) : '';
        $page     = isset($params['page']) ? sanitize_text_field($params['page']) : '/';
        $device   = (isset($params['device']) && $params['device'] === 'mobile') ? 'mobile' : 'desktop';

        if ($toast_id <= 0 || get_post_type($toast_id) !== 'abc_popup') {
            return new WP_REST_Response(array('success' => false, 'message' => 'Invalid toast ID'), 400);
        }

        // =========================================================================
        // DADOS MAIS CONFIÁVEIS: Ignorar rastreamento se o usuário for administrador
        // =========================================================================
        if (current_user_can('manage_options')) {
            return new WP_REST_Response(array(
                'success' => true, 
                'ignored' => true, 
                'message' => 'Admin interactions are not tracked'
            ), 200);
        }

        // 1. Atualiza contador individual no Post Meta
        if ($action === 'view') {
            $views = (int) get_post_meta($toast_id, '_abc_toast_views', true);
            update_post_meta($toast_id, '_abc_toast_views', $views + 1);
        } elseif ($action === 'click') {
            $clicks = (int) get_post_meta($toast_id, '_abc_toast_clicks', true);
            update_post_meta($toast_id, '_abc_toast_clicks', $clicks + 1);
        } elseif ($action === 'dismiss') {
            $dismisses = (int) get_post_meta($toast_id, '_abc_toast_dismisses', true);
            update_post_meta($toast_id, '_abc_toast_dismisses', $dismisses + 1);
        }

        // 2. Atualiza estatísticas globais para o Dashboard em Abas
        $stats = get_option('abc_toast_analytics_summary', array());
        if (!is_array($stats) || empty($stats)) {
            $stats = array(
                'total_views'     => 0,
                'total_clicks'    => 0,
                'total_dismisses' => 0,
                'devices'         => array('desktop' => 0, 'mobile' => 0),
                'pages'           => array(),
                'recent_events'   => array()
            );
        }

        if (!isset($stats['devices'])) $stats['devices'] = array('desktop' => 0, 'mobile' => 0);
        if (!isset($stats['pages'])) $stats['pages'] = array();
        if (!isset($stats['recent_events'])) $stats['recent_events'] = array();

        if ($action === 'view') {
            $stats['total_views'] = (isset($stats['total_views']) ? $stats['total_views'] : 0) + 1;
            // Contabiliza dispositivo por exibição para bater exatamente com o total de visualizações
            $stats['devices'][$device] = (isset($stats['devices'][$device]) ? $stats['devices'][$device] : 0) + 1;
        } elseif ($action === 'click') {
            $stats['total_clicks'] = (isset($stats['total_clicks']) ? $stats['total_clicks'] : 0) + 1;
        } elseif ($action === 'dismiss') {
            $stats['total_dismisses'] = (isset($stats['total_dismisses']) ? $stats['total_dismisses'] : 0) + 1;
        }

        $clean_page = substr($page, 0, 120);
        if (!isset($stats['pages'][$clean_page])) {
            $stats['pages'][$clean_page] = array('views' => 0, 'clicks' => 0, 'dismisses' => 0);
        }
        $key = ($action === 'view') ? 'views' : (($action === 'click') ? 'clicks' : 'dismisses');
        $stats['pages'][$clean_page][$key] = (isset($stats['pages'][$clean_page][$key]) ? $stats['pages'][$clean_page][$key] : 0) + 1;

        // 3. Agrupamento Diário para Relatórios Mensais / Período
        $today = gmdate('Y-m-d');
        if (!isset($stats['daily'])) {
            $stats['daily'] = array();
        }
        if (!isset($stats['daily'][$today])) {
            $stats['daily'][$today] = array(
                'views'     => 0,
                'clicks'    => 0,
                'dismisses' => 0,
                'devices'   => array('desktop' => 0, 'mobile' => 0),
                'pages'     => array()
            );
        }

        if ($action === 'view') {
            $stats['daily'][$today]['views']++;
            $stats['daily'][$today]['devices'][$device] = (isset($stats['daily'][$today]['devices'][$device]) ? $stats['daily'][$today]['devices'][$device] : 0) + 1;
        } elseif ($action === 'click') {
            $stats['daily'][$today]['clicks']++;
        } elseif ($action === 'dismiss') {
            $stats['daily'][$today]['dismisses']++;
        }

        if (!isset($stats['daily'][$today]['pages'][$clean_page])) {
            $stats['daily'][$today]['pages'][$clean_page] = array('views' => 0, 'clicks' => 0, 'dismisses' => 0);
        }
        $stats['daily'][$today]['pages'][$clean_page][$key] = (isset($stats['daily'][$today]['pages'][$clean_page][$key]) ? $stats['daily'][$today]['pages'][$clean_page][$key] : 0) + 1;

        // Feed de atividades recentes (mantém os últimos 25)
        $toast_title = get_the_title($toast_id);
        array_unshift($stats['recent_events'], array(
            'time'     => time(),
            'toast_id' => $toast_id,
            'title'    => $toast_title ? $toast_title : ('Toast #' . $toast_id),
            'action'   => $action,
            'page'     => $clean_page,
            'device'   => $device
        ));
        $stats['recent_events'] = array_slice($stats['recent_events'], 0, 25);

        update_option('abc_toast_analytics_summary', $stats, false);

        return new WP_REST_Response(array('success' => true), 200);
    }

    /**
     * Callback da REST API para sites Headless
     */
    public function rest_get_active_toasts(WP_REST_Request $request) {
        // =========================================================================
        // TRAVA DE SEGURANÇA: Bloqueio estrito na API se a licença estiver inativa
        // =========================================================================
        if (!abc_toast_is_license_active()) {
            return new WP_REST_Response(array(
                'success' => false,
                'code'    => 'license_inactive',
                'message' => __('O ABC Engagement Toast requer uma chave de licença ativa no WordPress para exibir toasts.', 'abc-engagement-toast'),
                'toasts'  => array()
            ), 403);
        }

        $lang   = $request->get_param('lang') ? sanitize_text_field($request->get_param('lang')) : '';
        $toasts = $this->query_toasts($lang);

        return new WP_REST_Response(array(
            'success'     => true,
            'code'        => 'license_active',
            'count'       => count($toasts),
            'toasts'      => $toasts,
            'server_time' => time()
        ), 200);
    }

    /**
     * Enfileirar estilos e scripts no frontend tradicional (monolítico).
     * BLOQUEIO TOTAL: Não carrega assets se a licença não estiver ativa!
     */
    public function enqueue_assets() {
        if (is_admin() || is_customize_preview()) {
            return;
        }

        // =========================================================================
        // TRAVA DE SEGURANÇA: Se a licença não estiver ativa, não carrega CSS/JS
        // =========================================================================
        if (!abc_toast_is_license_active()) {
            return;
        }

        wp_enqueue_style(
            'abc-engagement-toast-frontend',
            ABC_ENGAGEMENT_TOAST_URL . 'assets/css/engagement-toast.css',
            array(),
            ABC_ENGAGEMENT_TOAST_VERSION
        );

        wp_enqueue_script(
            'abc-engagement-toast-frontend',
            ABC_ENGAGEMENT_TOAST_URL . 'assets/js/engagement-toast.js',
            array(),
            ABC_ENGAGEMENT_TOAST_VERSION,
            true
        );
    }

    /**
     * Injetar a estrutura HTML e dados dos Toasts no rodapé do site tradicional.
     * BLOQUEIO TOTAL: Não gera dados nem tags se a licença não estiver ativa!
     */
    public function render_toasts() {
        if (is_admin() || is_customize_preview()) {
            return;
        }

        // =========================================================================
        // TRAVA DE SEGURANÇA: Bloqueio estrito por chave de licença
        // =========================================================================
        if (!abc_toast_is_license_active()) {
            return;
        }

        $toasts = $this->query_toasts();
        if (empty($toasts)) {
            return;
        }

        echo '<script id="abc-engagement-toasts-data">';
        echo 'window.abcEngagementToasts = ' . wp_json_encode($toasts) . ';';
        
        // Bloqueio de tracking para administradores visualizando o site
        if (current_user_can('manage_options')) {
            echo 'window.abcIsAdmin = true;';
        }

        echo '</script>';
    }

    /**
     * Consulta e formata a lista de toasts ativos do banco de dados
     */
    public function query_toasts($lang = '') {
        $args = array(
            'post_type'      => 'abc_popup',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'rand'
        );

        // Suporte a Polylang e WPML (filtra apenas os toasts do idioma atual da página)
        if (!empty($lang)) {
            $args['lang'] = $lang;
        } elseif (function_exists('pll_current_language')) {
            $current_lang = pll_current_language('slug');
            if (!empty($current_lang)) {
                $args['lang'] = $current_lang;
            }
        } elseif (defined('ICL_LANGUAGE_CODE')) {
            $args['lang'] = ICL_LANGUAGE_CODE;
        }

        $query = new WP_Query($args);

        if (!$query->have_posts()) {
            return array();
        }

        $toasts = array();

        while ($query->have_posts()) {
            $query->the_post();

            $title = function_exists('get_field') ? get_field('popup_display_title') : '';
            if (empty($title)) {
                $title = get_the_title();
            }

            $content = function_exists('get_field') ? get_field('popup_description') : '';

            $button_link = function_exists('get_field') ? get_field('popup_button_link') : null;
            $link_url    = $button_link ? $button_link['url'] : '';
            $link_text   = $button_link ? $button_link['title'] : '';
            $link_target = $button_link && isset($button_link['target']) ? $button_link['target'] : '_self';

            $bg_color      = (function_exists('get_field') ? get_field('popup_bg_color') : '') ?: '#ffffff';
            $text_color    = (function_exists('get_field') ? get_field('popup_text_color') : '') ?: '#212121';
            $icon_bg_color = (function_exists('get_field') ? get_field('popup_icon_bg_color') : '') ?: 'rgba(249, 160, 69, 0.1)';

            $close_color    = (function_exists('get_field') ? get_field('popup_close_color') : '') ?: '#212121';
            $close_bg_color = (function_exists('get_field') ? get_field('popup_close_bg_color') : '') ?: 'transparent';

            $btn_alignment  = (function_exists('get_field') ? get_field('popup_btn_alignment') : '') ?: 'left';
            $btn_bg_color   = (function_exists('get_field') ? get_field('popup_btn_bg_color') : '') ?: '#f9a045';
            $btn_text_color = (function_exists('get_field') ? get_field('popup_btn_text_color') : '') ?: '#ffffff';

            $border_width  = (function_exists('get_field') && get_field('popup_border_width') !== '') ? get_field('popup_border_width') : 1;
            $border_color  = (function_exists('get_field') ? get_field('popup_border_color') : '') ?: 'rgba(0,0,0,0.1)';
            $border_radius = (function_exists('get_field') && get_field('popup_border_radius') !== '') ? get_field('popup_border_radius') : 12;

            $emoji     = function_exists('get_field') ? get_field('popup_icon_emoji') : '';
            $image_url = function_exists('get_field') ? get_field('popup_image') : '';
            $position  = (function_exists('get_field') ? get_field('popup_position') : '') ?: 'bottom-right';

            $trigger        = (function_exists('get_field') ? get_field('popup_trigger') : '') ?: 'load';
            $trigger_delay  = (function_exists('get_field') ? get_field('popup_trigger_delay_sec') : '') ?: 5;
            $trigger_scroll = (function_exists('get_field') ? get_field('popup_trigger_scroll_pct') : '') ?: 50;

            $exit_overlay       = function_exists('get_field') ? get_field('popup_trigger_exit_overlay') : false;
            $exit_overlay_color = (function_exists('get_field') ? get_field('popup_trigger_exit_overlay_color') : '') ?: 'rgba(0, 0, 0, 0.65)';
            $exit_overlay_blur  = function_exists('get_field') ? get_field('popup_trigger_exit_overlay_blur') : true;
            if ($exit_overlay_blur === null) {
                $exit_overlay_blur = true;
            }

            $toasts[] = array(
                'id'               => get_the_ID(),
                'title'            => $title,
                'content'          => $content,
                'linkUrl'          => $link_url,
                'linkText'         => $link_text,
                'linkTarget'       => $link_target,
                'bgColor'          => $bg_color,
                'textColor'        => $text_color,
                'iconBgColor'      => $icon_bg_color,
                'closeColor'       => $close_color,
                'closeBgColor'     => $close_bg_color,
                'btnAlignment'     => $btn_alignment,
                'btnBgColor'       => $btn_bg_color,
                'btnTextColor'     => $btn_text_color,
                'borderWidth'      => $border_width,
                'borderColor'      => $border_color,
                'borderRadius'     => $border_radius,
                'emoji'            => $emoji,
                'imageUrl'         => $image_url,
                'position'         => $position,
                'trigger'          => $trigger,
                'triggerDelay'     => $trigger_delay,
                'triggerScroll'    => $trigger_scroll,
                'exitOverlay'      => (bool) $exit_overlay,
                'exitOverlayColor' => $exit_overlay_color,
                'exitOverlayBlur'  => (bool) $exit_overlay_blur
            );
        }
        wp_reset_postdata();

        return $toasts;
    }
}
