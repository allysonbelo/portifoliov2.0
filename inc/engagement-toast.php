<?php
/**
 * Engagement Toast (PoopUp Style)
 * 
 * Registra o CPT, Campos ACF e injeta o HTML/JS no footer.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 1. Registrar Custom Post Type para os Popups
 */
function abc_tech_register_popup_cpt() {
    $labels = array(
        'name'               => _x('Toasts (Popups)', 'post type general name', 'abc-tech'),
        'singular_name'      => _x('Toast', 'post type singular name', 'abc-tech'),
        'menu_name'          => _x('Toasts (Popups)', 'admin menu', 'abc-tech'),
        'name_admin_bar'     => _x('Toast', 'add new on admin bar', 'abc-tech'),
        'add_new'            => _x('Adicionar Novo', 'toast', 'abc-tech'),
        'add_new_item'       => __('Adicionar Novo Toast', 'abc-tech'),
        'new_item'           => __('Novo Toast', 'abc-tech'),
        'edit_item'          => __('Editar Toast', 'abc-tech'),
        'view_item'          => __('Ver Toast', 'abc-tech'),
        'all_items'          => __('Todos os Toasts', 'abc-tech'),
        'search_items'       => __('Procurar Toasts', 'abc-tech'),
        'not_found'          => __('Nenhum toast encontrado.', 'abc-tech'),
        'not_found_in_trash' => __('Nenhum toast encontrado na lixeira.', 'abc-tech')
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
        'menu_position'      => 6,
        'menu_icon'          => 'dashicons-testimonial',
        // Usamos apenas o título nativo para organização interna no painel
        'supports'           => array('title'),
        'show_in_rest'       => false, 
    );

    register_post_type('abc_popup', $args);
}
add_action('init', 'abc_tech_register_popup_cpt');

/**
 * 2. Registrar Campos ACF (Interface mais intuitiva)
 */
if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
    'key' => 'group_abc_popup_settings',
    'title' => 'Construtor do Toast (Como vai aparecer)',
    'fields' => array(
        // === PREVIEW (Ficará na direita via CSS) ===
        array(
            'key' => 'field_popup_preview',
            'label' => 'Preview',
            'name' => 'popup_preview_html',
            'type' => 'message',
            'message' => '<div id="abc-toast-admin-preview-container" style="background:#f0f0f1; padding:40px; border-radius:8px; display:flex; justify-content:center; align-items:center; min-height: 200px;"></div>',
            'new_lines' => 'wpautop',
            'esc_html' => 0,
        ),
        // === TAB: CONTEÚDO ===
        array(
            'key' => 'field_popup_tab_content',
            'label' => 'Conteúdo Visual',
            'name' => '',
            'type' => 'tab',
            'placement' => 'top',
        ),
        array(
            'key' => 'field_popup_image',
            'label' => 'Imagem',
            'name' => 'popup_image',
            'type' => 'image',
            'return_format' => 'url',
            'preview_size' => 'thumbnail',
            'instructions' => 'Substitui o ícone/emoji se escolhida.',
            'wrapper' => array('width' => '100'),
        ),
        array(
            'key' => 'field_popup_icon_emoji',
            'label' => 'Ícone/Emoji',
            'name' => 'popup_icon_emoji',
            'type' => 'text',
            'wrapper' => array('width' => '100'),
        ),
        array(
            'key' => 'field_popup_display_title',
            'label' => 'Título Principal',
            'name' => 'popup_display_title',
            'type' => 'text',
            'wrapper' => array('width' => '100'),
        ),
        array(
            'key' => 'field_popup_description',
            'label' => 'Descrição (Gatilho)',
            'name' => 'popup_description',
            'type' => 'textarea',
            'rows' => 3,
            'new_lines' => 'br',
        ),
        array(
            'key' => 'field_popup_button_link',
            'label' => 'Link e Ação do Botão',
            'name' => 'popup_button_link',
            'type' => 'link',
            'wrapper' => array('width' => '100'),
        ),

        // === TAB: ESTILO E POSIÇÃO ===
        array(
            'key' => 'field_popup_tab_style',
            'label' => 'Aparência e Posição',
            'name' => '',
            'type' => 'tab',
            'placement' => 'top',
        ),
        array(
            'key' => 'field_popup_position',
            'label' => 'Posição na Tela',
            'name' => 'popup_position',
            'type' => 'select',
            'choices' => array(
                'bottom-right' => 'Canto Inferior Direito',
                'bottom-left'  => 'Canto Inferior Esquerdo',
                'top-right'    => 'Canto Superior Direito',
                'top-left'     => 'Canto Superior Esquerdo',
                'center'       => 'Centro da Tela',
            ),
            'default_value' => 'bottom-right',
            'wrapper' => array('width' => '100'),
        ),
        array(
            'key' => 'field_popup_close_color',
            'label' => 'Cor do Botão Fechar (X)',
            'name' => 'popup_close_color',
            'type' => 'color_picker',
            'default_value' => '#212121',
            'wrapper' => array('width' => '50'),
        ),
        array(
            'key' => 'field_popup_close_bg_color',
            'label' => 'Fundo do Botão Fechar',
            'name' => 'popup_close_bg_color',
            'type' => 'color_picker',
            'default_value' => 'transparent',
            'enable_opacity' => 1,
            'return_format' => 'string',
            'wrapper' => array('width' => '50'),
        ),
        array(
            'key' => 'field_popup_bg_color',
            'label' => 'Fundo do Toast',
            'name' => 'popup_bg_color',
            'type' => 'color_picker',
            'default_value' => '#ffffff',
            'wrapper' => array('width' => '100'),
        ),
        array(
            'key' => 'field_popup_text_color',
            'label' => 'Texto do Toast',
            'name' => 'popup_text_color',
            'type' => 'color_picker',
            'default_value' => '#212121',
            'wrapper' => array('width' => '100'),
        ),
        array(
            'key' => 'field_popup_icon_bg_color',
            'label' => 'Fundo do Ícone/Imagem',
            'name' => 'popup_icon_bg_color',
            'type' => 'color_picker',
            'default_value' => 'rgba(249, 160, 69, 0.1)',
            'enable_opacity' => 1,
            'return_format' => 'string',
            'wrapper' => array('width' => '100'),
        ),
        array(
            'key' => 'field_popup_btn_alignment',
            'label' => 'Alinhamento do Botão',
            'name' => 'popup_btn_alignment',
            'type' => 'select',
            'choices' => array(
                'left'   => 'À Esquerda',
                'center' => 'Centralizado',
                'right'  => 'À Direita',
                'full'   => 'Ocupar Toda a Largura',
            ),
            'default_value' => 'left',
            'wrapper' => array('width' => '100'),
        ),
        array(
            'key' => 'field_popup_btn_bg_color',
            'label' => 'Fundo do Botão',
            'name' => 'popup_btn_bg_color',
            'type' => 'color_picker',
            'default_value' => '#f9a045',
            'wrapper' => array('width' => '50'),
        ),
        array(
            'key' => 'field_popup_btn_text_color',
            'label' => 'Texto do Botão',
            'name' => 'popup_btn_text_color',
            'type' => 'color_picker',
            'default_value' => '#ffffff',
            'wrapper' => array('width' => '100'),
        ),
        array(
            'key' => 'field_popup_border_width',
            'label' => 'Espessura da Borda (px)',
            'name' => 'popup_border_width',
            'type' => 'number',
            'default_value' => 1,
            'wrapper' => array('width' => '100'),
        ),
        array(
            'key' => 'field_popup_border_color',
            'label' => 'Cor da Borda',
            'name' => 'popup_border_color',
            'type' => 'color_picker',
            'default_value' => 'rgba(0,0,0,0.1)',
            'enable_opacity' => 1,
            'return_format' => 'string',
            'wrapper' => array('width' => '100'),
        ),
        array(
            'key' => 'field_popup_border_radius',
            'label' => 'Arredondamento (px)',
            'name' => 'popup_border_radius',
            'type' => 'number',
            'default_value' => 12,
            'wrapper' => array('width' => '100'),
        ),

        // === TAB: GATILHOS ===
        array(
            'key' => 'field_popup_tab_triggers',
            'label' => 'Gatilhos (Triggers)',
            'name' => '',
            'type' => 'tab',
            'placement' => 'top',
        ),
        array(
            'key' => 'field_popup_trigger',
            'label' => 'Quando o Toast deve aparecer?',
            'name' => 'popup_trigger',
            'type' => 'radio',
            'choices' => array(
                'load'   => 'Ao carregar a página',
                'delay'  => 'Após X segundos',
                'scroll' => 'Ao rolar a página',
                'exit'   => 'Intenção de Saída (Mouse sai da tela)',
            ),
            'default_value' => 'load',
            'layout' => 'vertical',
            'wrapper' => array('width' => '100'),
        ),
        array(
            'key' => 'field_popup_trigger_delay_sec',
            'label' => 'Atraso (Segundos)',
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
            'label' => 'Porcentagem de Rolagem (%)',
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
            'label' => 'Ativar Overlay de Fundo (Escurecer Tela)?',
            'name' => 'popup_trigger_exit_overlay',
            'type' => 'true_false',
            'message' => 'Escurece e dá destaque total ao Popup ao tentar fechar a página',
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
            'label' => 'Cor do Overlay de Fundo',
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
            'label' => 'Desfocar o Fundo da Página (Blur)?',
            'name' => 'popup_trigger_exit_overlay_blur',
            'type' => 'true_false',
            'message' => 'Aplica um efeito fosco/desfoque suave na página de trás',
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

endif;

/**
 * 3. Renderizar o HTML e Passar Dados pro JS
 */
function abc_tech_render_engagement_toast() {
    if (is_admin() || is_customize_preview()) return;

    $args = array(
        'post_type' => 'abc_popup',
        'posts_per_page' => 10,
        'post_status' => 'publish',
        'orderby' => 'rand'
    );
    $query = new WP_Query($args);

    if (!$query->have_posts()) return;

    $toasts = array();

    while ($query->have_posts()) {
        $query->the_post();
        
        $title = get_field('popup_display_title') ?: get_the_title();
        $content = get_field('popup_description');
        
        $button_link = get_field('popup_button_link');
        $link_url = $button_link ? $button_link['url'] : '';
        $link_text = $button_link ? $button_link['title'] : '';
        $link_target = $button_link && isset($button_link['target']) ? $button_link['target'] : '_self';

        $bg_color = get_field('popup_bg_color') ?: '#ffffff';
        $text_color = get_field('popup_text_color') ?: '#212121';
        $icon_bg_color = get_field('popup_icon_bg_color') ?: 'rgba(249, 160, 69, 0.1)';
        
        $close_color = get_field('popup_close_color') ?: '#212121';
        $close_bg_color = get_field('popup_close_bg_color') ?: 'transparent';
        
        $btn_alignment = get_field('popup_btn_alignment') ?: 'left';
        $btn_bg_color = get_field('popup_btn_bg_color') ?: '#f9a045';
        $btn_text_color = get_field('popup_btn_text_color') ?: '#ffffff';
        
        $border_width = get_field('popup_border_width') !== '' ? get_field('popup_border_width') : 1;
        $border_color = get_field('popup_border_color') ?: 'rgba(0,0,0,0.1)';
        $border_radius = get_field('popup_border_radius') !== '' ? get_field('popup_border_radius') : 12;
        
        $emoji = get_field('popup_icon_emoji');
        $image_url = get_field('popup_image');
        $position = get_field('popup_position') ?: 'bottom-right';

        $trigger = get_field('popup_trigger') ?: 'load';
        $trigger_delay = get_field('popup_trigger_delay_sec') ?: 5;
        $trigger_scroll = get_field('popup_trigger_scroll_pct') ?: 50;

        $exit_overlay = get_field('popup_trigger_exit_overlay');
        $exit_overlay_color = get_field('popup_trigger_exit_overlay_color') ?: 'rgba(0, 0, 0, 0.65)';
        $exit_overlay_blur = get_field('popup_trigger_exit_overlay_blur');
        if ($exit_overlay_blur === null) {
            $exit_overlay_blur = true;
        }

        $toasts[] = array(
            'id' => get_the_ID(),
            'title' => $title,
            'content' => $content,
            'linkUrl' => $link_url,
            'linkText' => $link_text,
            'linkTarget' => $link_target,
            'bgColor' => $bg_color,
            'textColor' => $text_color,
            'iconBgColor' => $icon_bg_color,
            'closeColor' => $close_color,
            'closeBgColor' => $close_bg_color,
            'btnAlignment' => $btn_alignment,
            'btnBgColor' => $btn_bg_color,
            'btnTextColor' => $btn_text_color,
            'borderWidth' => $border_width,
            'borderColor' => $border_color,
            'borderRadius' => $border_radius,
            'emoji' => $emoji,
            'imageUrl' => $image_url,
            'position' => $position,
            'trigger' => $trigger,
            'triggerDelay' => $trigger_delay,
            'triggerScroll' => $trigger_scroll,
            'exitOverlay' => (bool)$exit_overlay,
            'exitOverlayColor' => $exit_overlay_color,
            'exitOverlayBlur' => (bool)$exit_overlay_blur
        );
    }
    wp_reset_postdata();

    echo '<script>';
    echo 'window.abcEngagementToasts = ' . wp_json_encode($toasts) . ';';
    echo '</script>';
}
add_action('wp_footer', 'abc_tech_render_engagement_toast', 5);

/**
 * 4. Forçar Suporte do Polylang
 */
add_filter('pll_get_post_types', function($post_types, $is_settings) {
    $post_types['abc_popup'] = 'abc_popup';
    return $post_types;
}, 10, 2);

/**
 * 5. Scripts para o Preview no Admin
 */
function abc_tech_admin_toast_preview($hook) {
    global $post;
    if (($hook === 'post-new.php' || $hook === 'post.php') && $post && $post->post_type === 'abc_popup') {
        // Carrega o CSS do front-end para o preview
        wp_enqueue_style('abc-tech-engagement-toast', get_template_directory_uri() . '/css/engagement-toast.css');
        // Carrega o JS do admin
        wp_enqueue_script('abc-tech-admin-toast', get_template_directory_uri() . '/js/admin-engagement-toast.js', array('jquery'), time(), true);
    }
}
add_action('admin_enqueue_scripts', 'abc_tech_admin_toast_preview');

