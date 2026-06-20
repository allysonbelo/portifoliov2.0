<?php

/**
 * Configurações e Inicialização do Tema ABC tech.
 * Foco em segurança, escaping, suporte a internacionalização (Polylang) e performance.
 */

if (! defined('ABSPATH')) {
    exit; // Previne acesso direto por segurança
}

function abc_tech_setup()
{
    // Carrega o domínio de texto para tradução (Polylang)
    load_theme_textdomain('abc-tech', get_template_directory() . '/languages');

    register_nav_menus(array(
        'menu-1' => esc_html__('Primary Menu', 'abc-tech'),
    ));

    // Suporte a tags de título nativas do WP (Melhor prática de SEO)
    add_theme_support('title-tag');

    // Suporte a miniaturas de post
    add_theme_support('post-thumbnails');

    // Suporte a HTML5 para elementos mais semânticos
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));
}
add_action('after_setup_theme', 'abc_tech_setup');

function abc_tech_scripts()
{
    // Versão do tema para cache busting controlado
    $version = '1.0.0';

    // Enfileiramento das folhas de estilo
    wp_enqueue_style('abc-tech-reset', get_template_directory_uri() . '/css/reset.css', array(), $version);
    wp_enqueue_style('abc-tech-fonts', get_template_directory_uri() . '/css/fonts.css', array(), $version);
    wp_enqueue_style('abc-tech-base', get_template_directory_uri() . '/css/base.css', array('abc-tech-reset', 'abc-tech-fonts'), $version);

    // Arquivos modulares criados anteriormente
    wp_enqueue_style('abc-tech-header', get_template_directory_uri() . '/css/header.css', array('abc-tech-base'), $version);
    wp_enqueue_style('abc-tech-footer', get_template_directory_uri() . '/css/footer.css', array('abc-tech-base'), $version);

    // Enfileiramento das seções
    wp_enqueue_style('abc-tech-hero', get_template_directory_uri() . '/css/hero.css', array('abc-tech-base'), $version);
    wp_enqueue_style('abc-tech-stack', get_template_directory_uri() . '/css/stack.css', array('abc-tech-base'), $version);
    wp_enqueue_style('abc-tech-experience', get_template_directory_uri() . '/css/experience.css', array('abc-tech-base'), $version);
    wp_enqueue_style('abc-tech-cta', get_template_directory_uri() . '/css/cta.css', array('abc-tech-base'), $version);
    wp_enqueue_style('abc-tech-projetos', get_template_directory_uri() . '/css/projetos.css', array('abc-tech-base'), $version);

    if (is_singular('project')) {
        // CORRIGIDO: Nome do arquivo agora bate com o que está na pasta css/ (single-projeto.css)
        $css_path = get_template_directory() . '/css/single-projeto.css';

        // Blindagem: Só lê a data se o arquivo realmente existir no disco
        if (file_exists($css_path)) {
            $versao_css = filemtime($css_path);
        } else {
            $versao_css = '1.0.fallback-' . time();
        }

        // CORRIGIDO: URL do arquivo corrigida e adicionada a dependência do 'abc-tech-base'
        wp_enqueue_style('abc-tech-single-project', get_template_directory_uri() . '/css/single-projeto.css', array('abc-tech-base'), $versao_css, 'all');
    }

    // Carrega o CSS apenas se a página estiver usando o template de contato
    if (is_page_template('template-contato.php')) {
        $css_path = get_template_directory() . '/css/contato.css';

        if (file_exists($css_path)) {
            $versao_css = filemtime($css_path);
        } else {
            $versao_css = '1.0.fallback-' . time();
        }

        wp_enqueue_style('abc-tech-contato', get_template_directory_uri() . '/css/contato.css', array('abc-tech-base'), $versao_css, 'all');
    }

    // Carrega o CSS específico para a página 404
    if (is_404()) {
        $css_path = get_template_directory() . '/css/404.css';

        if (file_exists($css_path)) {
            $versao_css = filemtime($css_path);
        } else {
            $versao_css = '1.0.fallback-' . time();
        }

        wp_enqueue_style('abc-tech-404', get_template_directory_uri() . '/css/404.css', array('abc-tech-base'), $versao_css, 'all');
    }

    // Carrega o CSS apenas se a página estiver usando o template "Sobre"
    if (is_page_template('template-sobre.php')) {
        $css_path = get_template_directory() . '/css/sobre.css';

        if (file_exists($css_path)) {
            $versao_css = filemtime($css_path);
        } else {
            $versao_css = '1.0.fallback-' . time();
        }

        wp_enqueue_style('abc-tech-sobre', get_template_directory_uri() . '/css/sobre.css', array('abc-tech-base'), $versao_css, 'all');
    }

    // Arquivo style.css principal
    wp_enqueue_style('abc-tech-style', get_stylesheet_uri(), array('abc-tech-base'), $version);

    // Enfileiramento do JavaScript Modular (carregado no footer)
    wp_enqueue_script('abc-tech-navigation', get_template_directory_uri() . '/js/navigation.js', array(), $version, true);
    wp_enqueue_script('abc-tech-animations', get_template_directory_uri() . '/js/animations.js', array(), $version, true);
    wp_enqueue_script('abc-tech-typing', get_template_directory_uri() . '/js/typing-hero.js', array(), $version, true);
    wp_enqueue_script('abc-tech-projetos-view', get_template_directory_uri() . '/js/projetos-view.js', array(), $version, true);
    wp_enqueue_script('abc-tech-typewriter-cards', get_template_directory_uri() . '/js/typewriter-cards.js', array(), $version, true);
}
add_action('wp_enqueue_scripts', 'abc_tech_scripts');

// =========================================================================
// Ícone de Edição Contextual ACF no Frontend (Apenas para Admins)
// =========================================================================

// 1. Adicionar Página de Opções do ACF (Para o Header, Footer, Contactos Globais)
if (function_exists('acf_add_options_page')) {
    acf_add_options_page(array(
        'page_title'    => 'Opções do Tema',
        'menu_title'    => 'Opções do Tema',
        'menu_slug'     => 'opcoes-tema',
        'capability'    => 'edit_posts',
        'redirect'      => false,
        'icon_url'      => 'dashicons-admin-generic', // Ícone de engrenagem
    ));
}

// 2. Função para exibir o ícone no front-end
function abc_tech_edit_section_icon($acf_target, $page_id = null)
{
    // Só exibe se o utilizador tiver permissão para editar
    if (!current_user_can('edit_posts')) return;

    $edit_url = '';

    // Verifica se estamos a apontar para a Página de Opções
    if ($page_id === 'option' || $page_id === 'options') {
        $edit_url = admin_url('admin.php?page=opcoes-tema') . '&acf_focus=' . $acf_target;
    } else {
        // Se não for passado um ID, tenta usar o post atual
        if (!$page_id) {
            global $post;
            $page_id = isset($post->ID) ? $post->ID : false;
        }

        if (!$page_id) return;

        // Cria o link de edição do post com o parâmetro de foco
        $edit_url = html_entity_decode(get_edit_post_link($page_id)) . '&acf_focus=' . $acf_target;
    }

    echo '<a href="' . esc_url($edit_url) . '" class="abc_tech-acf-edit-icon" target="_blank" title="Editar esta secção">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
          </a>';
}

// 3. Script para fechar blocos e fazer scroll/destaque no painel do WordPress
add_action('admin_footer', 'abc_tech_acf_highlight_script');
function abc_tech_acf_highlight_script()
{
    // Impede a execução do script dentro do painel de construção do próprio ACF
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if ( $screen && $screen->post_type === 'acf-field-group' ) {
        return;
    }

    $focus = isset($_GET['acf_focus']) ? sanitize_text_field($_GET['acf_focus']) : '';
?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            if (document.body.classList.contains('post-type-acf-field-group')) return;

            // =========================================================================
            // 1. FUNÇÕES AUXILIARES DE ESTILO
            // =========================================================================
            
            // Limpa o destaque de TODOS os blocos e campos
            function clearAllActiveStyles() {
                const allBlocks = document.querySelectorAll('.postbox.acf-postbox, .acf-field');
                allBlocks.forEach(block => {
                    block.classList.remove('abc-tech-active-block');
                    block.style.backgroundColor = '';
                    block.style.borderLeft = '';
                });
            }

            // Aplica o estilo Laranja
            function applyActiveStyles(element) {
                element.style.backgroundColor = 'rgba(249, 160, 69, 0.05)';
                element.style.borderLeft = '4px solid rgb(249 160 69)';
            }


            // =========================================================================
            // 2. LÓGICA DE REDIRECIONAMENTO E PISCAR (Vindo do Frontend)
            // =========================================================================
            const focusName = "<?php echo esc_js($focus); ?>";
            
            if (focusName) {
                const acfMetaboxes = document.querySelectorAll('.postbox.acf-postbox');
                acfMetaboxes.forEach(box => box.classList.add('closed'));

                const targetElement = document.querySelector('[data-name="' + focusName + '"]') || document.getElementById('acf-' + focusName);

                if (targetElement) {
                    const metabox = targetElement.closest('.postbox');
                    if (metabox) {
                        metabox.classList.remove('closed');
                        const toggleBtn = metabox.querySelector('.handlediv');
                        if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
                    }

                    setTimeout(() => {
                        targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });

                        let isHighlight = false;
                        let blinks = 0;
                        const originalBg = targetElement.style.backgroundColor;
                        targetElement.style.transition = 'background-color 0.3s ease, border-left 0.3s ease';

                        const blinkInterval = setInterval(() => {
                            targetElement.style.backgroundColor = isHighlight ? originalBg : 'rgb(249 160 69)';
                            isHighlight = !isHighlight;
                            blinks++;

                            if (blinks >= 6) {
                                clearInterval(blinkInterval);
                                clearAllActiveStyles(); 
                                targetElement.classList.add('abc-tech-active-block');
                                applyActiveStyles(targetElement);
                            }
                        }, 300);
                    }, 150);
                }
            }

            // =========================================================================
            // 3. LÓGICA DE FOCO ATIVO GERAL (Cliques e Teclado no Painel)
            // =========================================================================
            const adminContent = document.getElementById('wpcontent');
            if (adminContent) {
                
                function handleSectionActivation(e) {
                    const targetSection = e.target.closest('.postbox.acf-postbox');
                    
                    if (targetSection) {
                        if (targetSection.classList.contains('abc-tech-active-block')) return;

                        clearAllActiveStyles();

                        targetSection.classList.add('abc-tech-active-block');
                        targetSection.style.transition = 'background-color 0.3s ease, border-left 0.3s ease';
                        applyActiveStyles(targetSection);
                    }
                }

                adminContent.addEventListener('click', handleSectionActivation);
                adminContent.addEventListener('focusin', handleSectionActivation);
            }

        });
    </script>
<?php
}