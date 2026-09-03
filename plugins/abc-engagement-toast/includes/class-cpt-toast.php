<?php
/**
 * Registro do Custom Post Type `abc_popup` e Campos ACF
 * 
 * ABC Engagement Toast
 */

if (!defined('ABSPATH')) {
    exit;
}

class ABC_Toast_CPT {

    public function __construct() {
        add_action('init', array($this, 'register_popup_cpt'));
        add_action('acf/init', array($this, 'register_acf_fields'));
        add_filter('pll_get_post_types', array($this, 'polylang_support'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_preview_scripts'));
        add_action('admin_notices', array($this, 'banner_license_in_cpt'));
        add_filter('manage_abc_popup_posts_columns', array($this, 'custom_popup_columns'));
        add_action('manage_abc_popup_posts_custom_column', array($this, 'render_popup_columns'), 10, 2);
        add_action('admin_menu', array($this, 'register_analytics_page'));
        add_action('admin_post_abc_toast_reset_analytics', array($this, 'handle_reset_analytics'));
        add_action('admin_post_abc_toast_export_pdf', array($this, 'render_pdf_report'));
    }

    /**
     * 1. Registrar Custom Post Type: `abc_popup`
     */
    public function register_popup_cpt() {
        $labels = array(
            'name'               => _x('Toasts (Popups)', 'post type general name', 'abc-engagement-toast'),
            'singular_name'      => _x('Toast', 'post type singular name', 'abc-engagement-toast'),
            'menu_name'          => _x('Toasts (Popups)', 'admin menu', 'abc-engagement-toast'),
            'name_admin_bar'     => _x('Toast', 'add new on admin bar', 'abc-engagement-toast'),
            'add_new'            => _x('Adicionar Novo', 'toast', 'abc-engagement-toast'),
            'add_new_item'       => __('Adicionar Novo Toast', 'abc-engagement-toast'),
            'new_item'           => __('Novo Toast', 'abc-engagement-toast'),
            'edit_item'          => __('Editar Toast', 'abc-engagement-toast'),
            'view_item'          => __('Ver Toast', 'abc-engagement-toast'),
            'all_items'          => __('Todos os Toasts', 'abc-engagement-toast'),
            'search_items'       => __('Procurar Toasts', 'abc-engagement-toast'),
            'not_found'          => __('Nenhum toast encontrado.', 'abc-engagement-toast'),
            'not_found_in_trash' => __('Nenhum toast encontrado na lixeira.', 'abc-engagement-toast')
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 25,
            'menu_icon'          => 'dashicons-testimonial',
            'supports'           => array('title'),
            'show_in_rest'       => false, 
        );

        register_post_type('abc_popup', $args);
    }

    /**
     * 2. Registrar Campos ACF Nativamente no Plugin
     */
    public function register_acf_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group(array(
            'key' => 'group_abc_popup_settings',
            'title' => __('Construtor do Toast (Como vai aparecer)', 'abc-engagement-toast'),
            'fields' => array(
                // === PREVIEW (Ficará na direita via CSS) ===
                array(
                    'key' => 'field_popup_preview',
                    'label' => __('Preview em Tempo Real', 'abc-engagement-toast'),
                    'name' => 'popup_preview_html',
                    'type' => 'message',
                    'message' => '<div id="abc-toast-admin-preview-container" style="background:#f0f0f1; padding:40px; border-radius:8px; display:flex; justify-content:center; align-items:center; min-height: 200px;"></div>',
                    'new_lines' => 'wpautop',
                    'esc_html' => 0,
                ),
                // === TAB: CONTEÚDO ===
                array(
                    'key' => 'field_popup_tab_content',
                    'label' => __('Conteúdo Visual', 'abc-engagement-toast'),
                    'name' => '',
                    'type' => 'tab',
                    'placement' => 'top',
                ),
                array(
                    'key' => 'field_popup_image',
                    'label' => __('Imagem', 'abc-engagement-toast'),
                    'name' => 'popup_image',
                    'type' => 'image',
                    'return_format' => 'url',
                    'preview_size' => 'thumbnail',
                    'instructions' => __('Substitui o ícone/emoji se escolhida.', 'abc-engagement-toast'),
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_popup_icon_emoji',
                    'label' => __('Ícone/Emoji', 'abc-engagement-toast'),
                    'name' => 'popup_icon_emoji',
                    'type' => 'text',
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_popup_display_title',
                    'label' => __('Título Principal', 'abc-engagement-toast'),
                    'name' => 'popup_display_title',
                    'type' => 'text',
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_popup_description',
                    'label' => __('Descrição (Gatilho)', 'abc-engagement-toast'),
                    'name' => 'popup_description',
                    'type' => 'textarea',
                    'rows' => 3,
                    'new_lines' => 'br',
                ),
                array(
                    'key' => 'field_popup_button_link',
                    'label' => __('Link e Ação do Botão', 'abc-engagement-toast'),
                    'name' => 'popup_button_link',
                    'type' => 'link',
                    'wrapper' => array('width' => '100'),
                ),

                // === TAB: ESTILO E POSIÇÃO ===
                array(
                    'key' => 'field_popup_tab_style',
                    'label' => __('Aparência e Posição', 'abc-engagement-toast'),
                    'name' => '',
                    'type' => 'tab',
                    'placement' => 'top',
                ),
                array(
                    'key' => 'field_popup_position',
                    'label' => __('Posição na Tela', 'abc-engagement-toast'),
                    'name' => 'popup_position',
                    'type' => 'select',
                    'choices' => array(
                        'bottom-right' => __('Canto Inferior Direito', 'abc-engagement-toast'),
                        'bottom-left'  => __('Canto Inferior Esquerdo', 'abc-engagement-toast'),
                        'top-right'    => __('Canto Superior Direito', 'abc-engagement-toast'),
                        'top-left'     => __('Canto Superior Esquerdo', 'abc-engagement-toast'),
                        'center'       => __('Centro da Tela', 'abc-engagement-toast'),
                    ),
                    'default_value' => 'bottom-right',
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_popup_close_color',
                    'label' => __('Cor do Botão Fechar (X)', 'abc-engagement-toast'),
                    'name' => 'popup_close_color',
                    'type' => 'color_picker',
                    'default_value' => '#212121',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_popup_close_bg_color',
                    'label' => __('Fundo do Botão Fechar', 'abc-engagement-toast'),
                    'name' => 'popup_close_bg_color',
                    'type' => 'color_picker',
                    'default_value' => 'transparent',
                    'enable_opacity' => 1,
                    'return_format' => 'string',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_popup_bg_color',
                    'label' => __('Fundo do Toast', 'abc-engagement-toast'),
                    'name' => 'popup_bg_color',
                    'type' => 'color_picker',
                    'default_value' => '#ffffff',
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_popup_text_color',
                    'label' => __('Texto do Toast', 'abc-engagement-toast'),
                    'name' => 'popup_text_color',
                    'type' => 'color_picker',
                    'default_value' => '#212121',
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_popup_icon_bg_color',
                    'label' => __('Fundo do Ícone/Imagem', 'abc-engagement-toast'),
                    'name' => 'popup_icon_bg_color',
                    'type' => 'color_picker',
                    'default_value' => 'rgba(249, 160, 69, 0.1)',
                    'enable_opacity' => 1,
                    'return_format' => 'string',
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_popup_btn_alignment',
                    'label' => __('Alinhamento do Botão', 'abc-engagement-toast'),
                    'name' => 'popup_btn_alignment',
                    'type' => 'select',
                    'choices' => array(
                        'left'   => __('À Esquerda', 'abc-engagement-toast'),
                        'center' => __('Centralizado', 'abc-engagement-toast'),
                        'right'  => __('À Direita', 'abc-engagement-toast'),
                        'full'   => __('Ocupar Toda a Largura', 'abc-engagement-toast'),
                    ),
                    'default_value' => 'left',
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_popup_btn_bg_color',
                    'label' => __('Fundo do Botão', 'abc-engagement-toast'),
                    'name' => 'popup_btn_bg_color',
                    'type' => 'color_picker',
                    'default_value' => '#f9a045',
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_popup_btn_text_color',
                    'label' => __('Texto do Botão', 'abc-engagement-toast'),
                    'name' => 'popup_btn_text_color',
                    'type' => 'color_picker',
                    'default_value' => '#ffffff',
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_popup_border_width',
                    'label' => __('Espessura da Borda (px)', 'abc-engagement-toast'),
                    'name' => 'popup_border_width',
                    'type' => 'number',
                    'default_value' => 1,
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_popup_border_color',
                    'label' => __('Cor da Borda', 'abc-engagement-toast'),
                    'name' => 'popup_border_color',
                    'type' => 'color_picker',
                    'default_value' => 'rgba(0,0,0,0.1)',
                    'enable_opacity' => 1,
                    'return_format' => 'string',
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_popup_border_radius',
                    'label' => __('Arredondamento (px)', 'abc-engagement-toast'),
                    'name' => 'popup_border_radius',
                    'type' => 'number',
                    'default_value' => 12,
                    'wrapper' => array('width' => '100'),
                ),

                // === TAB: GATILHOS ===
                array(
                    'key' => 'field_popup_tab_triggers',
                    'label' => __('Gatilhos (Triggers)', 'abc-engagement-toast'),
                    'name' => '',
                    'type' => 'tab',
                    'placement' => 'top',
                ),
                array(
                    'key' => 'field_popup_trigger',
                    'label' => __('Quando o Toast deve aparecer?', 'abc-engagement-toast'),
                    'name' => 'popup_trigger',
                    'type' => 'radio',
                    'choices' => array(
                        'load'   => __('Ao carregar a página', 'abc-engagement-toast'),
                        'delay'  => __('Após X segundos', 'abc-engagement-toast'),
                        'scroll' => __('Ao rolar a página', 'abc-engagement-toast'),
                        'exit'   => __('Intenção de Saída (Mouse sai da tela)', 'abc-engagement-toast'),
                    ),
                    'default_value' => 'load',
                    'layout' => 'vertical',
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_popup_trigger_delay_sec',
                    'label' => __('Atraso (Segundos)', 'abc-engagement-toast'),
                    'name' => 'popup_trigger_delay_sec',
                    'type' => 'number',
                    'default_value' => 5,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_popup_trigger',
                                'operator' => '==',
                                'value' => 'delay',
                            ),
                        ),
                    ),
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_popup_trigger_scroll_pct',
                    'label' => __('Porcentagem de Rolagem (%)', 'abc-engagement-toast'),
                    'name' => 'popup_trigger_scroll_pct',
                    'type' => 'number',
                    'default_value' => 50,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_popup_trigger',
                                'operator' => '==',
                                'value' => 'scroll',
                            ),
                        ),
                    ),
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_popup_trigger_exit_overlay',
                    'label' => __('Ativar Overlay de Fundo (Escurecer Tela)?', 'abc-engagement-toast'),
                    'name' => 'popup_trigger_exit_overlay',
                    'type' => 'true_false',
                    'message' => __('Escurece e dá destaque total ao Popup ao tentar fechar a página', 'abc-engagement-toast'),
                    'default_value' => 0,
                    'ui' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_popup_trigger',
                                'operator' => '==',
                                'value' => 'exit',
                            ),
                        ),
                    ),
                    'wrapper' => array('width' => '100'),
                ),
                array(
                    'key' => 'field_popup_trigger_exit_overlay_color',
                    'label' => __('Cor do Overlay de Fundo', 'abc-engagement-toast'),
                    'name' => 'popup_trigger_exit_overlay_color',
                    'type' => 'color_picker',
                    'default_value' => 'rgba(0, 0, 0, 0.65)',
                    'enable_opacity' => 1,
                    'return_format' => 'string',
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_popup_trigger',
                                'operator' => '==',
                                'value' => 'exit',
                            ),
                            array(
                                'field' => 'field_popup_trigger_exit_overlay',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array('width' => '50'),
                ),
                array(
                    'key' => 'field_popup_trigger_exit_overlay_blur',
                    'label' => __('Desfocar o Fundo da Página (Blur)?', 'abc-engagement-toast'),
                    'name' => 'popup_trigger_exit_overlay_blur',
                    'type' => 'true_false',
                    'message' => __('Aplica um efeito fosco/desfoque suave na página de trás', 'abc-engagement-toast'),
                    'default_value' => 1,
                    'ui' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_popup_trigger',
                                'operator' => '==',
                                'value' => 'exit',
                            ),
                            array(
                                'field' => 'field_popup_trigger_exit_overlay',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array('width' => '50'),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'abc_popup',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'seamless',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => array(
                0 => 'the_content',
                1 => 'excerpt',
                2 => 'featured_image',
            ),
            'active' => true,
            'description' => '',
        ));
    }

    /**
     * 3. Forçar Suporte do Polylang
     */
    public function polylang_support($post_types, $is_settings) {
        $post_types['abc_popup'] = 'abc_popup';
        return $post_types;
    }

    /**
     * 4. Scripts e Estilos para o Preview no Admin
     */
    public function enqueue_admin_preview_scripts($hook) {
        global $post;
        if (($hook === 'post-new.php' || $hook === 'post.php') && $post && $post->post_type === 'abc_popup') {
            wp_enqueue_style(
                'abc-engagement-toast-frontend-css',
                ABC_ENGAGEMENT_TOAST_URL . 'assets/css/engagement-toast.css',
                array(),
                ABC_ENGAGEMENT_TOAST_VERSION
            );
            wp_enqueue_script(
                'abc-engagement-toast-admin-preview',
                ABC_ENGAGEMENT_TOAST_URL . 'assets/js/admin-engagement-toast.js',
                array('jquery'),
                ABC_ENGAGEMENT_TOAST_VERSION,
                true
            );
        }
    }

    /**
     * 5. Aviso nos posts de Toast se a licença não estiver ativa
     */
    public function banner_license_in_cpt() {
        $screen = get_current_screen();
        if ($screen && $screen->post_type === 'abc_popup' && $screen->id !== 'abc_popup_page_abc-toast-license') {
            if (!abc_toast_is_license_active()) {
                $license_url = admin_url('edit.php?post_type=abc_popup&page=abc-toast-license');
                ?>
                <div class="notice notice-error" style="border-left-color: #dc2626; padding: 12px 16px;">
                    <p style="font-size: 14px; margin: 0;">
                        <strong><?php _e('Bloqueio de Licença Ativo:', 'abc-engagement-toast'); ?></strong>
                        <?php _e('Os toasts configurados aqui NÃO serão renderizados no seu site até que uma licença válida seja ativada.', 'abc-engagement-toast'); ?>
                        <a href="<?php echo esc_url($license_url); ?>" class="button button-primary" style="margin-left: 12px;">
                            <?php _e('Ativar Licença', 'abc-engagement-toast'); ?>
                        </a>
                    </p>
                </div>
                <?php
            }
        }
    }

    /**
     * 6. Colunas personalizadas na listagem de Toasts
     */
    public function custom_popup_columns($columns) {
        $new_columns = array();
        foreach ($columns as $key => $title) {
            $new_columns[$key] = $title;
            if ($key === 'title') {
                $new_columns['abc_views']  = '👁️ ' . __('Visualizações', 'abc-engagement-toast');
                $new_columns['abc_clicks'] = '🖱️ ' . __('Cliques (CTA)', 'abc-engagement-toast');
                $new_columns['abc_ctr']    = '🎯 ' . __('Conversão (CTR)', 'abc-engagement-toast');
            }
        }
        return $new_columns;
    }

    /**
     * 7. Renderização dos dados nas colunas
     */
    public function render_popup_columns($column, $post_id) {
        $views  = (int) get_post_meta($post_id, '_abc_toast_views', true);
        $clicks = (int) get_post_meta($post_id, '_abc_toast_clicks', true);

        if ($column === 'abc_views') {
            echo '<span style="font-weight: 600; color: #1e293b; font-size: 13px;">' . number_format_i18n($views) . '</span>';
        } elseif ($column === 'abc_clicks') {
            echo '<span style="font-weight: 600; color: #0284c7; font-size: 13px;">' . number_format_i18n($clicks) . '</span>';
        } elseif ($column === 'abc_ctr') {
            if ($views > 0) {
                $ctr = round(($clicks / $views) * 100, 1);
                $style = $ctr >= 10 ? 'background: #dcfce7; color: #166534;' : ($ctr >= 5 ? 'background: #e0f2fe; color: #0369a1;' : 'background: #f1f5f9; color: #475569;');
                echo '<span style="display: inline-block; padding: 3px 10px; border-radius: 9999px; font-weight: 700; font-size: 12px; ' . $style . '">' . $ctr . '%</span>';
            } else {
                echo '<span style="color: #94a3b8; font-size: 12px;">0.0%</span>';
            }
        }
    }

    /**
     * 8. Menu de Guia do Google Analytics
     */
    public function register_analytics_page() {
        add_submenu_page(
            'edit.php?post_type=abc_popup',
            __('Métricas & Google Analytics', 'abc-engagement-toast'),
            '📊 ' . __('Google Analytics', 'abc-engagement-toast'),
            'manage_options',
            'abc-toast-analytics',
            array($this, 'render_analytics_guide_page')
        );
    }

    /**
     * 9. Processa o reset de estatísticas
     */
    public function handle_reset_analytics() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissão para realizar esta ação.', 'abc-engagement-toast'));
        }
        check_admin_referer('abc_toast_reset_analytics_nonce');

        delete_option('abc_toast_analytics_summary');

        $popups = get_posts(array(
            'post_type'      => 'abc_popup',
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'fields'         => 'ids'
        ));

        foreach ($popups as $id) {
            delete_post_meta($id, '_abc_toast_views');
            delete_post_meta($id, '_abc_toast_clicks');
            delete_post_meta($id, '_abc_toast_dismisses');
        }

        wp_safe_redirect(admin_url('edit.php?post_type=abc_popup&page=abc-toast-analytics&reset=1'));
        exit;
    }

    /**
     * 10. Consulta estatísticas filtradas por período (Mês, 30 dias ou Todo o período)
     */
    public function get_filtered_stats($period = 'all') {
        $stats = get_option('abc_toast_analytics_summary', array(
            'total_views'     => 0,
            'total_clicks'    => 0,
            'total_dismisses' => 0,
            'devices'         => array('desktop' => 0, 'mobile' => 0),
            'pages'           => array(),
            'daily'           => array(),
            'recent_events'   => array()
        ));

        $daily = isset($stats['daily']) && is_array($stats['daily']) ? $stats['daily'] : array();

        $meses = array(
            '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', '04' => 'Abril',
            '05' => 'Maio',    '06' => 'Junho',     '07' => 'Julho', '08' => 'Agosto',
            '09' => 'Setembro','10' => 'Outubro',   '11' => 'Novembro','12' => 'Dezembro'
        );

        $current_month = gmdate('Y-m');
        $last_month    = gmdate('Y-m', strtotime('-1 month'));
        $thirty_days   = gmdate('Y-m-d', strtotime('-30 days'));

        $period_label = __('Todo o Período', 'abc-engagement-toast');

        if ($period === 'this_month') {
            $m = substr($current_month, 5, 2);
            $y = substr($current_month, 0, 4);
            $period_label = (isset($meses[$m]) ? $meses[$m] : $m) . ' de ' . $y;
        } elseif ($period === 'last_month') {
            $m = substr($last_month, 5, 2);
            $y = substr($last_month, 0, 4);
            $period_label = (isset($meses[$m]) ? $meses[$m] : $m) . ' de ' . $y;
        } elseif ($period === 'last_30') {
            $period_label = __('Últimos 30 Dias', 'abc-engagement-toast');
        } elseif (preg_match('/^\d{4}-\d{2}$/', $period)) {
            $m = substr($period, 5, 2);
            $y = substr($period, 0, 4);
            $period_label = (isset($meses[$m]) ? $meses[$m] : $m) . ' de ' . $y;
        }

        $available_months = array();
        foreach (array_keys($daily) as $day_key) {
            $m_key = substr($day_key, 0, 7);
            if (!isset($available_months[$m_key])) {
                $m_part = substr($m_key, 5, 2);
                $y_part = substr($m_key, 0, 4);
                $available_months[$m_key] = (isset($meses[$m_part]) ? $meses[$m_part] : $m_part) . ' de ' . $y_part;
            }
        }
        krsort($available_months);

        if ($period === 'all' || empty($daily)) {
            $views     = isset($stats['total_views']) ? (int)$stats['total_views'] : 0;
            $clicks    = isset($stats['total_clicks']) ? (int)$stats['total_clicks'] : 0;
            $dismisses = isset($stats['total_dismisses']) ? (int)$stats['total_dismisses'] : 0;
            $devices   = isset($stats['devices']) ? $stats['devices'] : array('desktop' => 0, 'mobile' => 0);
            $pages     = isset($stats['pages']) && is_array($stats['pages']) ? $stats['pages'] : array();
        } else {
            $views     = 0;
            $clicks    = 0;
            $dismisses = 0;
            $devices   = array('desktop' => 0, 'mobile' => 0);
            $pages     = array();

            foreach ($daily as $day => $d_data) {
                $match = false;
                if ($period === 'this_month' && strpos($day, $current_month) === 0) {
                    $match = true;
                } elseif ($period === 'last_month' && strpos($day, $last_month) === 0) {
                    $match = true;
                } elseif ($period === 'last_30' && $day >= $thirty_days) {
                    $match = true;
                } elseif ($period === substr($day, 0, 7)) {
                    $match = true;
                }

                if ($match) {
                    $views     += isset($d_data['views']) ? (int)$d_data['views'] : 0;
                    $clicks    += isset($d_data['clicks']) ? (int)$d_data['clicks'] : 0;
                    $dismisses += isset($d_data['dismisses']) ? (int)$d_data['dismisses'] : 0;

                    if (isset($d_data['devices'])) {
                        $devices['desktop'] += isset($d_data['devices']['desktop']) ? (int)$d_data['devices']['desktop'] : 0;
                        $devices['mobile']  += isset($d_data['devices']['mobile']) ? (int)$d_data['devices']['mobile'] : 0;
                    }

                    if (isset($d_data['pages']) && is_array($d_data['pages'])) {
                        foreach ($d_data['pages'] as $p_path => $p_info) {
                            if (!isset($pages[$p_path])) {
                                $pages[$p_path] = array('views' => 0, 'clicks' => 0, 'dismisses' => 0);
                            }
                            $pages[$p_path]['views']     += isset($p_info['views']) ? (int)$p_info['views'] : 0;
                            $pages[$p_path]['clicks']    += isset($p_info['clicks']) ? (int)$p_info['clicks'] : 0;
                            $pages[$p_path]['dismisses'] += isset($p_info['dismisses']) ? (int)$p_info['dismisses'] : 0;
                        }
                    }
                }
            }
        }

        uasort($pages, function($a, $b) {
            $ca = isset($a['clicks']) ? (int)$a['clicks'] : 0;
            $cb = isset($b['clicks']) ? (int)$b['clicks'] : 0;
            if ($ca === $cb) {
                $va = isset($a['views']) ? (int)$a['views'] : 0;
                $vb = isset($b['views']) ? (int)$b['views'] : 0;
                return $vb <=> $va;
            }
            return $cb <=> $ca;
        });

        $total_dev   = $devices['desktop'] + $devices['mobile'];
        $pct_desktop = $total_dev > 0 ? round(($devices['desktop'] / $total_dev) * 100) : 50;
        $pct_mobile  = $total_dev > 0 ? (100 - $pct_desktop) : 50;
        $ctr         = $views > 0 ? round(($clicks / $views) * 100, 1) : 0.0;

        return array(
            'period'           => $period,
            'period_label'     => $period_label,
            'available_months' => $available_months,
            'total_views'      => $views,
            'total_clicks'     => $clicks,
            'total_dismisses'  => $dismisses,
            'ctr_global'       => $ctr,
            'devices'          => $devices,
            'pct_desktop'      => $pct_desktop,
            'pct_mobile'       => $pct_mobile,
            'pages'            => $pages,
            'recent_events'    => isset($stats['recent_events']) ? $stats['recent_events'] : array()
        );
    }

    /**
     * 11. Dashboard de Métricas Organizado em 4 Abas com Filtro de Período e Exportação PDF
     */
    public function render_analytics_guide_page() {
        $selected_period = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : 'all';
        $data            = $this->get_filtered_stats($selected_period);

        $total_views      = $data['total_views'];
        $total_clicks     = $data['total_clicks'];
        $total_dismisses  = $data['total_dismisses'];
        $ctr_global       = $data['ctr_global'];
        $devices          = $data['devices'];
        $pct_desktop      = $data['pct_desktop'];
        $pct_mobile       = $data['pct_mobile'];
        $pages            = $data['pages'];
        $recent_events    = $data['recent_events'];
        $available_months = $data['available_months'];
        ?>
        <div class="wrap" style="max-width: 1100px; margin-top: 20px;">
            <?php if (isset($_GET['reset']) && $_GET['reset'] == '1'): ?>
                <div class="notice notice-success is-dismissible" style="margin-bottom: 20px;">
                    <p><strong><?php _e('Sucesso:', 'abc-engagement-toast'); ?></strong> <?php _e('Todas as métricas e contadores foram zerados.', 'abc-engagement-toast'); ?></p>
                </div>
            <?php endif; ?>

            <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 12px; padding: 24px 32px; color: #ffffff; margin-bottom: 24px; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.15); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div>
                    <h1 style="color: #ffffff; margin: 0 0 6px 0; display: flex; align-items: center; gap: 12px; font-size: 22px;">
                        <span style="background: #f9a045; width: 34px; height: 34px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 18px;">📊</span>
                        <?php _e('Métricas, Desempenho & Google Analytics', 'abc-engagement-toast'); ?>
                    </h1>
                    <p style="color: #94a3b8; font-size: 14px; margin: 0;">
                        <?php echo sprintf(__('Exibindo dados de: %s', 'abc-engagement-toast'), '<strong style="color: #38bdf8;">' . esc_html($data['period_label']) . '</strong>'); ?>
                    </p>
                </div>

                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <form method="get" action="<?php echo esc_url(admin_url('edit.php')); ?>" style="display: flex; align-items: center; gap: 8px; margin: 0;">
                        <input type="hidden" name="post_type" value="abc_popup">
                        <input type="hidden" name="page" value="abc-toast-analytics">
                        <select name="period" onchange="this.form.submit()" style="background: #1e293b; color: #ffffff; border: 1px solid #334155; border-radius: 8px; padding: 8px 14px; font-size: 13px; font-weight: 600; cursor: pointer; outline: none;">
                            <option value="all" <?php selected($selected_period, 'all'); ?>>📅 <?php _e('Todo o Período', 'abc-engagement-toast'); ?></option>
                            <option value="this_month" <?php selected($selected_period, 'this_month'); ?>>📅 <?php _e('Este Mês (Atual)', 'abc-engagement-toast'); ?></option>
                            <option value="last_month" <?php selected($selected_period, 'last_month'); ?>>📅 <?php _e('Mês Anterior', 'abc-engagement-toast'); ?></option>
                            <option value="last_30" <?php selected($selected_period, 'last_30'); ?>>📅 <?php _e('Últimos 30 Dias', 'abc-engagement-toast'); ?></option>
                            <?php if (!empty($available_months)): ?>
                                <optgroup label="<?php esc_attr_e('Meses Anteriores', 'abc-engagement-toast'); ?>">
                                    <?php foreach ($available_months as $m_val => $m_name): ?>
                                        <option value="<?php echo esc_attr($m_val); ?>" <?php selected($selected_period, $m_val); ?>><?php echo esc_html($m_name); ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>
                        </select>
                    </form>

                    <a href="<?php echo esc_url(admin_url('admin-post.php?action=abc_toast_export_pdf&period=' . urlencode($selected_period))); ?>" target="_blank" class="button" style="display: inline-flex; align-items: center; gap: 6px; background: #0284c7; border-color: #0284c7; padding: 6px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; color: #ffffff; text-decoration: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <span>📥</span> <?php _e('Exportar Relatório PDF', 'abc-engagement-toast'); ?>
                    </a>
                </div>
            </div>

            <div class="abc-tabs-nav" style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; overflow-x: auto;">
                <button type="button" class="abc-tab-btn active" onclick="abcSwitchAnalyticsTab('tab-overview', this)" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; background: #0f172a; color: #ffffff; transition: all 0.2s ease;">
                    <span>📊</span> <?php _e('Visão Geral & KPIs', 'abc-engagement-toast'); ?>
                </button>
                <button type="button" class="abc-tab-btn" onclick="abcSwitchAnalyticsTab('tab-pages', this)" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; background: #f1f5f9; color: #475569; transition: all 0.2s ease;">
                    <span>🗺️</span> <?php _e('Desempenho por Página', 'abc-engagement-toast'); ?>
                    <span style="background: #e2e8f0; font-size: 11px; padding: 2px 7px; border-radius: 9999px;"><?php echo count($pages); ?></span>
                </button>
                <button type="button" class="abc-tab-btn" onclick="abcSwitchAnalyticsTab('tab-live', this)" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; background: #f1f5f9; color: #475569; transition: all 0.2s ease;">
                    <span>⚡</span> <?php _e('Atividades Recentes', 'abc-engagement-toast'); ?>
                    <span style="background: #e2e8f0; font-size: 11px; padding: 2px 7px; border-radius: 9999px;"><?php echo count($recent_events); ?></span>
                </button>
                <button type="button" class="abc-tab-btn" onclick="abcSwitchAnalyticsTab('tab-ga4', this)" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; background: #f1f5f9; color: #475569; transition: all 0.2s ease;">
                    <span>📘</span> <?php _e('Guia Google Analytics (GA4)', 'abc-engagement-toast'); ?>
                </button>
            </div>

            <div id="tab-overview" class="abc-tab-content">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                            <span>👁️</span> <?php _e('Total de Exibições', 'abc-engagement-toast'); ?>
                        </div>
                        <div style="font-size: 30px; font-weight: 800; color: #0f172a; line-height: 1;">
                            <?php echo number_format_i18n($total_views); ?>
                        </div>
                        <div style="font-size: 12px; color: #94a3b8; margin-top: 8px;">
                            <?php _e('Popups apresentados na tela', 'abc-engagement-toast'); ?>
                        </div>
                    </div>

                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                        <div style="font-size: 12px; font-weight: 600; color: #0284c7; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                            <span>🖱️</span> <?php _e('Total de Conversões (Cliques)', 'abc-engagement-toast'); ?>
                        </div>
                        <div style="font-size: 30px; font-weight: 800; color: #0284c7; line-height: 1;">
                            <?php echo number_format_i18n($total_clicks); ?>
                        </div>
                        <div style="font-size: 12px; color: #94a3b8; margin-top: 8px;">
                            <?php _e('Cliques no botão principal (CTA)', 'abc-engagement-toast'); ?>
                        </div>
                    </div>

                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                        <div style="font-size: 12px; font-weight: 600; color: #166534; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                            <span>🎯</span> <?php _e('Taxa de Conversão (CTR)', 'abc-engagement-toast'); ?>
                        </div>
                        <div style="font-size: 30px; font-weight: 800; color: #16a34a; line-height: 1;">
                            <?php echo $ctr_global; ?>%
                        </div>
                        <div style="font-size: 12px; color: #94a3b8; margin-top: 8px;">
                            <?php echo $ctr_global >= 10 ? '🔥 ' . __('Desempenho excelente', 'abc-engagement-toast') : ($ctr_global >= 5 ? '⚡ ' . __('Bom desempenho', 'abc-engagement-toast') : '📈 ' . __('Média de cliques no período', 'abc-engagement-toast')); ?>
                        </div>
                    </div>

                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                        <div style="font-size: 12px; font-weight: 600; color: #be123c; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                            <span>❌</span> <?php _e('Fechamentos (X)', 'abc-engagement-toast'); ?>
                        </div>
                        <div style="font-size: 30px; font-weight: 800; color: #e11d48; line-height: 1;">
                            <?php echo number_format_i18n($total_dismisses); ?>
                        </div>
                        <div style="font-size: 12px; color: #94a3b8; margin-top: 8px;">
                            <?php _e('Fechados pelo X ou clicando fora', 'abc-engagement-toast'); ?>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 24px;">
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px;">
                        <h3 style="margin: 0 0 16px 0; font-size: 16px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                            <span>📱</span> <?php _e('Visualizações por Dispositivo', 'abc-engagement-toast'); ?>
                        </h3>
                        
                        <div style="height: 16px; border-radius: 9999px; background: #e2e8f0; overflow: hidden; display: flex; margin-bottom: 16px;">
                            <div style="width: <?php echo $pct_mobile; ?>%; background: #0284c7; transition: width 0.5s;" title="Celular (<?php echo $pct_mobile; ?>%)"></div>
                            <div style="width: <?php echo $pct_desktop; ?>%; background: #64748b; transition: width 0.5s;" title="Computador (<?php echo $pct_desktop; ?>%)"></div>
                        </div>

                        <div style="display: flex; justify-content: space-between; font-size: 14px; color: #334155;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 3px; background: #0284c7;"></span>
                                <strong><?php _e('Celular (Mobile):', 'abc-engagement-toast'); ?></strong>
                                <span><?php echo $devices['mobile']; ?> (<?php echo $pct_mobile; ?>%)</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 3px; background: #64748b;"></span>
                                <strong><?php _e('Computador (Desktop):', 'abc-engagement-toast'); ?></strong>
                                <span><?php echo $devices['desktop']; ?> (<?php echo $pct_desktop; ?>%)</span>
                            </div>
                        </div>
                    </div>

                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <h3 style="margin: 0 0 8px 0; font-size: 15px; color: #0f172a;">
                                <?php _e('Reiniciar Estatísticas', 'abc-engagement-toast'); ?>
                            </h3>
                            <p style="color: #64748b; font-size: 13px; margin: 0 0 16px 0; line-height: 1.4;">
                                <?php _e('Deseja iniciar uma nova campanha? Você pode zerar os contadores locais a qualquer momento.', 'abc-engagement-toast'); ?>
                            </p>
                        </div>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('<?php echo esc_js(__('Tem certeza que deseja zerar todas as estatísticas de visualizações e cliques?', 'abc-engagement-toast')); ?>');">
                            <input type="hidden" name="action" value="abc_toast_reset_analytics">
                            <?php wp_nonce_field('abc_toast_reset_analytics_nonce'); ?>
                            <button type="submit" class="button" style="color: #b91c1c; border-color: #fecdd3; background: #fff1f2; font-weight: 600; width: 100%; text-align: center;">
                                🗑️ <?php _e('Zerar Todas as Métricas', 'abc-engagement-toast'); ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div id="tab-pages" class="abc-tab-content" style="display: none;">
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
                        <h2 style="margin: 0; font-size: 17px; color: #0f172a;">
                            <?php _e('Onde seus popups estão convertendo mais:', 'abc-engagement-toast'); ?>
                        </h2>
                        <span style="font-size: 13px; color: #64748b;">
                            <?php echo sprintf(__('Total de páginas registradas: %d', 'abc-engagement-toast'), count($pages)); ?>
                        </span>
                    </div>

                    <?php if (empty($pages)): ?>
                        <div style="text-align: center; padding: 48px 20px; color: #94a3b8;">
                            <span style="font-size: 40px; display: block; margin-bottom: 12px;">🗺️</span>
                            <p style="font-size: 15px; margin: 0; font-weight: 500; color: #64748b;">
                                <?php _e('Nenhuma interação por página registrada neste período.', 'abc-engagement-toast'); ?>
                            </p>
                            <p style="font-size: 13px; margin-top: 4px;">
                                <?php _e('Assim que os visitantes visualizarem ou clicarem nos popups pelo site, as URLs aparecerão listadas aqui.', 'abc-engagement-toast'); ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <table class="wp-list-table widefat fixed striped" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                            <thead>
                                <tr style="background: #f8fafc;">
                                    <th style="font-weight: 700; color: #0f172a; padding: 12px;"><?php _e('Página / URL', 'abc-engagement-toast'); ?></th>
                                    <th style="font-weight: 700; color: #0f172a; width: 140px;"><?php _e('Visualizações', 'abc-engagement-toast'); ?></th>
                                    <th style="font-weight: 700; color: #0f172a; width: 140px;"><?php _e('Cliques (CTA)', 'abc-engagement-toast'); ?></th>
                                    <th style="font-weight: 700; color: #0f172a; width: 140px;"><?php _e('Fechamentos', 'abc-engagement-toast'); ?></th>
                                    <th style="font-weight: 700; color: #0f172a; width: 140px;"><?php _e('Conversão (CTR)', 'abc-engagement-toast'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pages as $path => $p_data): 
                                    $p_views  = isset($p_data['views']) ? (int)$p_data['views'] : 0;
                                    $p_clicks = isset($p_data['clicks']) ? (int)$p_data['clicks'] : 0;
                                    $p_diss   = isset($p_data['dismisses']) ? (int)$p_data['dismisses'] : 0;
                                    $p_ctr    = $p_views > 0 ? round(($p_clicks / $p_views) * 100, 1) : 0.0;
                                    $ctr_bg   = $p_ctr >= 10 ? 'background: #dcfce7; color: #166534;' : ($p_ctr >= 5 ? 'background: #e0f2fe; color: #0369a1;' : 'background: #f1f5f9; color: #475569;');
                                ?>
                                    <tr>
                                        <td style="padding: 12px;">
                                            <code style="color: #0284c7; font-size: 13px; font-weight: 600;"><?php echo esc_html($path); ?></code>
                                        </td>
                                        <td style="vertical-align: middle; font-weight: 600; color: #1e293b;">
                                            <?php echo number_format_i18n($p_views); ?>
                                        </td>
                                        <td style="vertical-align: middle; font-weight: 600; color: #0284c7;">
                                            <?php echo number_format_i18n($p_clicks); ?>
                                        </td>
                                        <td style="vertical-align: middle; font-weight: 600; color: #64748b;">
                                            <?php echo number_format_i18n($p_diss); ?>
                                        </td>
                                        <td style="vertical-align: middle;">
                                            <span style="display: inline-block; padding: 3px 10px; border-radius: 9999px; font-weight: 700; font-size: 12px; <?php echo $ctr_bg; ?>">
                                                <?php echo $p_ctr; ?>%
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <div id="tab-live" class="abc-tab-content" style="display: none;">
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px;">
                    <h2 style="margin: 0 0 16px 0; font-size: 17px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <span>⚡</span> <?php _e('Últimas Interações no Site (Tempo Real)', 'abc-engagement-toast'); ?>
                    </h2>

                    <?php if (empty($recent_events)): ?>
                        <div style="text-align: center; padding: 48px 20px; color: #94a3b8;">
                            <span style="font-size: 40px; display: block; margin-bottom: 12px;">🕒</span>
                            <p style="font-size: 15px; margin: 0; font-weight: 500; color: #64748b;">
                                <?php _e('Nenhuma atividade recente registrada.', 'abc-engagement-toast'); ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <?php foreach ($recent_events as $ev): 
                                $ev_action = isset($ev['action']) ? $ev['action'] : 'view';
                                $ev_time   = isset($ev['time']) ? $ev['time'] : time();
                                $ev_title  = isset($ev['title']) ? $ev['title'] : 'Toast';
                                $ev_page   = isset($ev['page']) ? $ev['page'] : '/';
                                $ev_dev    = (isset($ev['device']) && $ev['device'] === 'mobile') ? '📱 Mobile' : '💻 Desktop';

                                if ($ev_action === 'click') {
                                    $badge_label = __('Conversão (Clique)', 'abc-engagement-toast');
                                    $badge_style = 'background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;';
                                    $icon        = '🟢';
                                } elseif ($ev_action === 'dismiss') {
                                    $badge_label = __('Fechamento', 'abc-engagement-toast');
                                    $badge_style = 'background: #ffe4e6; color: #be123c; border: 1px solid #fecdd3;';
                                    $icon        = '❌';
                                } else {
                                    $badge_label = __('Exibição', 'abc-engagement-toast');
                                    $badge_style = 'background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;';
                                    $icon        = '👁️';
                                }
                            ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 8px; font-size: 13px; flex-wrap: wrap; gap: 8px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span><?php echo $icon; ?></span>
                                        <span style="font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 9999px; <?php echo $badge_style; ?>">
                                            <?php echo esc_html($badge_label); ?>
                                        </span>
                                        <strong style="color: #0f172a;"><?php echo esc_html($ev_title); ?></strong>
                                        <span style="color: #64748b;">na página</span>
                                        <code style="color: #0284c7; font-size: 12px;"><?php echo esc_html($ev_page); ?></code>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 12px; color: #64748b; font-size: 12px;">
                                        <span><?php echo esc_html($ev_dev); ?></span>
                                        <span>•</span>
                                        <span><?php echo human_time_diff($ev_time, time()) . ' ' . __('atrás', 'abc-engagement-toast'); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div id="tab-ga4" class="abc-tab-content" style="display: none;">
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin-bottom: 24px;">
                    <h2 style="margin-top: 0; font-size: 18px; color: #0f172a;">
                        <?php _e('Eventos Enviados Automaticamente para o GA4 & GTM:', 'abc-engagement-toast'); ?>
                    </h2>
                    
                    <div style="display: grid; gap: 16px; margin-top: 20px;">
                        <div style="border-left: 4px solid #3b82f6; background: #f8fafc; border-radius: 0 8px 8px 0; padding: 16px 20px;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                                <code style="background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 14px; padding: 2px 8px; border-radius: 4px;">abc_toast_view</code>
                                <span style="font-weight: 700; color: #0f172a;"><?php _e('Exibição / Impressão do Popup', 'abc-engagement-toast'); ?></span>
                            </div>
                            <p style="margin: 0; color: #475569; font-size: 14px; line-height: 1.5;">
                                <?php _e('Dispara quando o popup surge na tela para o visitante. Use para medir o alcance.', 'abc-engagement-toast'); ?>
                            </p>
                        </div>

                        <div style="border-left: 4px solid #10b981; background: #f8fafc; border-radius: 0 8px 8px 0; padding: 16px 20px;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                                <code style="background: #dcfce7; color: #15803d; font-weight: 700; font-size: 14px; padding: 2px 8px; border-radius: 4px;">abc_toast_click</code>
                                <span style="font-weight: 700; color: #0f172a;"><?php _e('Conversão / Clique no Botão de Ação (CTA)', 'abc-engagement-toast'); ?></span>
                            </div>
                            <p style="margin: 0; color: #475569; font-size: 14px; line-height: 1.5;">
                                <?php _e('Dispara quando o visitante clica no botão principal de destino do popup.', 'abc-engagement-toast'); ?>
                            </p>
                        </div>

                        <div style="border-left: 4px solid #f43f5e; background: #f8fafc; border-radius: 0 8px 8px 0; padding: 16px 20px;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                                <code style="background: #ffe4e6; color: #be123c; font-weight: 700; font-size: 14px; padding: 2px 8px; border-radius: 4px;">abc_toast_dismiss</code>
                                <span style="font-weight: 700; color: #0f172a;"><?php _e('Fechamento / Rejeição do Popup', 'abc-engagement-toast'); ?></span>
                            </div>
                            <p style="margin: 0; color: #475569; font-size: 14px; line-height: 1.5;">
                                <?php _e('Dispara quando o visitante fecha o popup clicando no botão "X" ou tocando fora da área.', 'abc-engagement-toast'); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px;">
                    <h3 style="margin-top: 0; font-size: 16px; color: #0f172a;">
                        <?php _e('Parâmetros que acompanham cada evento no GA4:', 'abc-engagement-toast'); ?>
                    </h3>
                    <ul style="color: #475569; font-size: 14px; line-height: 1.8; margin-bottom: 0;">
                        <li><strong><code>toast_id</code>:</strong> <?php _e('ID numérico do Toast.', 'abc-engagement-toast'); ?></li>
                        <li><strong><code>toast_title</code>:</strong> <?php _e('Título cadastrado.', 'abc-engagement-toast'); ?></li>
                        <li><strong><code>toast_trigger</code>:</strong> <?php _e('Gatilho que disparou.', 'abc-engagement-toast'); ?></li>
                        <li><strong><code>toast_target_url</code>:</strong> <?php _e('URL da página visitada.', 'abc-engagement-toast'); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <script>
        function abcSwitchAnalyticsTab(tabId, btn) {
            document.querySelectorAll('.abc-tab-content').forEach(function(el) {
                el.style.display = 'none';
            });
            const target = document.getElementById(tabId);
            if (target) {
                target.style.display = 'block';
            }

            document.querySelectorAll('.abc-tab-btn').forEach(function(b) {
                b.style.background = '#f1f5f9';
                b.style.color = '#475569';
                b.classList.remove('active');
            });
            btn.style.background = '#0f172a';
            btn.style.color = '#ffffff';
            btn.classList.add('active');

            if (history.pushState) {
                history.pushState(null, null, '#' + tabId.replace('tab-', ''));
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.hash) {
                const h = window.location.hash.replace('#', '');
                const targetBtn = document.querySelector('button[onclick*="tab-' + h + '"]');
                if (targetBtn) {
                    targetBtn.click();
                }
            }
        });
        </script>
        <?php
    }

    /**
     * 12. Gera a Visualização Executiva para Impressão / Salvamento em PDF
     */
    public function render_pdf_report() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissão para gerar este relatório.', 'abc-engagement-toast'));
        }

        $period = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : 'all';
        $data   = $this->get_filtered_stats($period);

        $site_name = get_bloginfo('name');
        $site_url  = home_url();
        $date_now  = date_i18n('d/m/Y \à\s H:i');
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <title><?php echo sprintf(__('Relatório de Performance - %s', 'abc-engagement-toast'), esc_html($data['period_label'])); ?> - <?php echo esc_html($site_name); ?></title>
            <style>
                @page { size: A4 portrait; margin: 14mm 12mm; }
                * { box-sizing: border-box; }
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 24px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                .pdf-container { max-width: 860px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 36px 40px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
                .report-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0f172a; padding-bottom: 20px; margin-bottom: 24px; }
                .report-title h1 { font-size: 24px; margin: 0 0 6px 0; color: #0f172a; font-weight: 800; }
                .report-title p { margin: 0; color: #64748b; font-size: 14px; }
                .report-meta { text-align: right; font-size: 13px; color: #475569; line-height: 1.5; }
                .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 28px; }
                .kpi-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; }
                .kpi-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 6px; letter-spacing: 0.5px; }
                .kpi-val { font-size: 26px; font-weight: 800; color: #0f172a; line-height: 1; }
                .section-heading { font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 14px 0; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; }
                table { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 28px; }
                th { background: #f1f5f9; color: #0f172a; font-weight: 700; text-align: left; padding: 10px 12px; border: 1px solid #e2e8f0; }
                td { padding: 10px 12px; border: 1px solid #e2e8f0; color: #334155; }
                tr:nth-child(even) { background: #f8fafc; }
                .badge-ctr { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-weight: 700; font-size: 12px; }
                .report-footer { margin-top: 32px; padding-top: 16px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; color: #94a3b8; font-size: 12px; }
                .toolbar { position: sticky; top: 12px; display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 16px; max-width: 860px; margin: 0 auto 16px auto; }
                .btn { padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; border: none; text-decoration: none; }
                .btn-print { background: #0284c7; color: #ffffff; }
                .btn-close { background: #e2e8f0; color: #334155; }
                @media print { body { background: #ffffff; padding: 0; } .pdf-container { border: none; box-shadow: none; padding: 0; } .toolbar { display: none !important; } }
            </style>
        </head>
        <body>
            <div class="toolbar">
                <button type="button" class="btn btn-close" onclick="window.close()">✕ Fechar</button>
                <button type="button" class="btn btn-print" onclick="window.print()">🖨️ Salvar em PDF / Imprimir</button>
            </div>
            <div class="pdf-container">
                <div class="report-header">
                    <div class="report-title">
                        <h1>📊 Relatório de Conversão & Popups</h1>
                        <p><?php echo esc_html($site_name); ?> — <code><?php echo esc_html($site_url); ?></code></p>
                    </div>
                    <div class="report-meta">
                        <div><strong>Período:</strong> <?php echo esc_html($data['period_label']); ?></div>
                        <div><strong>Emitido em:</strong> <?php echo esc_html($date_now); ?></div>
                    </div>
                </div>
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-label">👁️ Total de Exibições</div>
                        <div class="kpi-val"><?php echo number_format_i18n($data['total_views']); ?></div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label" style="color: #0284c7;">🖱️ Cliques no Botão (CTA)</div>
                        <div class="kpi-val" style="color: #0284c7;"><?php echo number_format_i18n($data['total_clicks']); ?></div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label" style="color: #166534;">🎯 Taxa de Conversão</div>
                        <div class="kpi-val" style="color: #16a34a;"><?php echo $data['ctr_global']; ?>%</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label" style="color: #be123c;">❌ Fechamentos (X)</div>
                        <div class="kpi-val" style="color: #e11d48;"><?php echo number_format_i18n($data['total_dismisses']); ?></div>
                    </div>
                </div>
                <div style="margin-bottom: 24px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 18px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 13px; font-weight: 700; color: #0f172a;">📱 Distribuição por Dispositivo:</span>
                        <span style="font-size: 13px; color: #334155;"><strong>Celular:</strong> <?php echo $data['devices']['mobile']; ?> (<?php echo $data['pct_mobile']; ?>%) &nbsp;|&nbsp; <strong>Computador:</strong> <?php echo $data['devices']['desktop']; ?> (<?php echo $data['pct_desktop']; ?>%)</span>
                    </div>
                </div>
                <div class="section-heading"><span>🗺️</span> Desempenho por Página de Exibição</div>
                <?php if (empty($data['pages'])): ?>
                    <p style="color: #94a3b8; font-size: 13px; text-align: center; padding: 20px 0;">Nenhuma página com interações registrada neste período.</p>
                <?php else: ?>
                    <table>
                        <thead><tr><th>Página / URL</th><th style="width: 120px;">Visualizações</th><th style="width: 120px;">Cliques (CTA)</th><th style="width: 120px;">Fechamentos</th><th style="width: 130px;">Conversão (CTR)</th></tr></thead>
                        <tbody>
                            <?php foreach ($data['pages'] as $p_path => $p_data): 
                                $pv  = isset($p_data['views']) ? (int)$p_data['views'] : 0;
                                $pc  = isset($p_data['clicks']) ? (int)$p_data['clicks'] : 0;
                                $pd  = isset($p_data['dismisses']) ? (int)$p_data['dismisses'] : 0;
                                $pct = $pv > 0 ? round(($pc / $pv) * 100, 1) : 0.0;
                                $bg  = $pct >= 10 ? 'background: #dcfce7; color: #166534;' : ($pct >= 5 ? 'background: #e0f2fe; color: #0369a1;' : 'background: #f1f5f9; color: #475569;');
                            ?>
                                <tr><td><code><?php echo esc_html($p_path); ?></code></td><td><strong><?php echo number_format_i18n($pv); ?></strong></td><td style="color: #0284c7;"><strong><?php echo number_format_i18n($pc); ?></strong></td><td><?php echo number_format_i18n($pd); ?></td><td><span class="badge-ctr" style="<?php echo $bg; ?>"><?php echo $pct; ?>%</span></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                <div class="report-footer"><span>ABC Engagement Toast • Relatório Oficial de Métricas</span><span>Página 1 de 1</span></div>
            </div>
            <script>window.addEventListener('load', function() { setTimeout(function() { window.print(); }, 600); });</script>
        </body>
        </html>
        <?php
        exit;
    }
}
