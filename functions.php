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
        'menu-1'      => esc_html__('Primary Menu', 'abc-tech'),
        'footer-menu' => esc_html__('Footer Menu', 'abc-tech'),
    ));

    // Suporte a tags de título nativas do WP (Melhor prática de SEO)
    add_theme_support('title-tag');

    // Suporte a miniaturas de post
    add_theme_support('post-thumbnails');

    // Suporte a Logo Personalizada via Customizer
    add_theme_support('custom-logo', array(
        'height'      => 80,
        'width'       => 240,
        'flex-height' => true,
        'flex-width'  => true,
    ));

    // Suporte a embeds responsivos e alinhamentos largos do Gutenberg
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');

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

/**
 * Registro Nativo do Custom Post Type "project"
 */
function abc_tech_register_cpts()
{
    if (post_type_exists('project')) {
        return;
    }

    $labels = array(
        'name'               => _x('Projetos', 'Post Type General Name', 'abc-tech'),
        'singular_name'      => _x('Projeto', 'Post Type Singular Name', 'abc-tech'),
        'menu_name'          => __('Projetos', 'abc-tech'),
        'all_items'          => __('Todos os Projetos', 'abc-tech'),
        'add_new_item'       => __('Adicionar Novo Projeto', 'abc-tech'),
        'add_new'            => __('Adicionar Novo', 'abc-tech'),
        'edit_item'          => __('Editar Projeto', 'abc-tech'),
        'update_item'        => __('Atualizar Projeto', 'abc-tech'),
        'view_item'          => __('Ver Projeto', 'abc-tech'),
        'search_items'       => __('Buscar Projeto', 'abc-tech'),
        'not_found'          => __('Nenhum projeto encontrado', 'abc-tech'),
    );
    $args = array(
        'label'              => __('Projeto', 'abc-tech'),
        'labels'             => $labels,
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'page-attributes'),
        'public'             => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-portfolio',
        'has_archive'        => true,
        'show_in_rest'       => true,
        'capability_type'    => 'post',
    );
    register_post_type('project', $args);

    // Registro da Taxonomia "tech_stack" (Categorias de Tecnologia)
    if (!taxonomy_exists('tech_stack')) {
        $tax_labels = array(
            'name'              => _x('Tecnologias', 'taxonomy general name', 'abc-tech'),
            'singular_name'     => _x('Tecnologia', 'taxonomy singular name', 'abc-tech'),
            'search_items'      => __('Buscar Tecnologias', 'abc-tech'),
            'all_items'         => __('Todas as Tecnologias', 'abc-tech'),
            'edit_item'         => __('Editar Tecnologia', 'abc-tech'),
            'update_item'       => __('Atualizar Tecnologia', 'abc-tech'),
            'add_new_item'      => __('Adicionar Nova Tecnologia', 'abc-tech'),
            'new_item_name'     => __('Nome da Nova Tecnologia', 'abc-tech'),
            'menu_name'         => __('Tecnologias', 'abc-tech'),
        );
        register_taxonomy('tech_stack', array('project'), array(
            'hierarchical'      => false,
            'labels'            => $tax_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'tecnologia'),
            'show_in_rest'      => true,
        ));
    }
}
add_action('init', 'abc_tech_register_cpts', 0);

/**
 * =========================================================================
 * 1. ACF LOCAL JSON (Sincronização Automática via Git)
 * =========================================================================
 */
add_filter('acf/settings/save_json', function ($path) {
    return get_template_directory() . '/acf-json';
});

add_filter('acf/settings/load_json', function ($paths) {
    unset($paths[0]);
    $paths[] = get_template_directory() . '/acf-json';
    return $paths;
});

/**
 * =========================================================================
 * 2. PERFORMANCE & DEFER SCRIPT LOADING
 * =========================================================================
 */

// Adiciona atributo 'defer' aos scripts JS enfileirados pelo tema
function abc_tech_defer_scripts($tag, $handle, $src)
{
    if (is_admin()) return $tag;

    $defer_scripts = array(
        'abc-tech-navigation',
        'abc-tech-animations',
        'abc-tech-typing',
        'abc-tech-projetos-view',
        'abc-tech-typewriter-cards',
    );

    if (in_array($handle, $defer_scripts, true)) {
        return '<script src="' . esc_url($src) . '" id="' . esc_attr($handle) . '-js" defer></script>' . "\n";
    }

    return $tag;
}
add_filter('script_loader_tag', 'abc_tech_defer_scripts', 10, 3);

// Desativa Emojis Nativos do WordPress (Economiza requisições HTTP e bytes no head)
function abc_tech_disable_emojis()
{
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
}
add_action('init', 'abc_tech_disable_emojis');

/**
 * =========================================================================
 * 3. HARDENING DE SEGURANÇA NO WORDPRESS
 * =========================================================================
 */
function abc_tech_security_cleanup()
{
    // Oculta a versão do WordPress
    remove_action('wp_head', 'wp_generator');

    // Desativa XML-RPC (Previne força bruta e ataques amplificados)
    add_filter('xmlrpc_enabled', '__return_false');

    // Limpa links desnecessários no <head>
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    // Desativa a barra de administração do WordPress no front-end
    add_filter('show_admin_bar', '__return_false');
}
add_action('init', 'abc_tech_security_cleanup');

/**
 * Injeta o Favicon SVG Animado no <head> (Fallback se não houver Ícone de Site definido)
 */
function abc_tech_render_favicon()
{
    if (!has_site_icon()) {
        $favicon_url = get_template_directory_uri() . '/images/favicon.svg';
        echo '<link rel="icon" type="image/svg+xml" href="' . esc_url($favicon_url) . '">' . "\n";
        echo '<link rel="alternate icon" href="' . esc_url($favicon_url) . '">' . "\n";
    }
}
add_action('wp_head', 'abc_tech_render_favicon', 5);

/**
 * =========================================================================
 * 4. SEO TÉCNICO: MARCAÇÃO SCHEMA.ORG (JSON-LD)
 * =========================================================================
 */
function abc_tech_render_schema_org()
{
    $schema = array();

    if (is_front_page() || is_home() || is_page_template('template-sobre.php')) {
        $schema = array(
            '@context'  => 'https://schema.org',
            '@type'     => 'Person',
            'name'      => 'Allyson Belo Cavalcante',
            'jobTitle'  => 'WordPress Architect & Front-End Developer',
            'url'       => home_url('/'),
            'sameAs'    => array(
                'https://github.com/allysonbelo',
                'https://www.linkedin.com/in/allysoncavalcante/',
            ),
            'knowsAbout' => array(
                'WordPress Development',
                'SEO Técnico',
                'Core Web Vitals',
                'PHP',
                'JavaScript',
                'Front-End Architecture'
            )
        );
    } elseif (is_page_template('template-projetos.php')) {
        $schema = array(
            '@context'    => 'https://schema.org',
            '@type'       => 'CollectionPage',
            'name'        => get_the_title() ?: 'Portfólio de Projetos',
            'description' => get_field('portfolio_description') ?: 'Uma seleção de projetos de alta performance, focados em arquitetura WordPress, otimização técnica de SEO e interfaces conversivas.',
            'url'         => get_permalink(),
        );
    } elseif (is_page_template('template-contato.php')) {
        $schema = array(
            '@context'    => 'https://schema.org',
            '@type'       => 'ContactPage',
            'name'        => get_the_title() ?: 'Contato',
            'description' => get_field('contact_description') ?: 'Entre em contato para orçamentos de projetos WordPress, auditorias de SEO técnico e desenvolvimento sob medida.',
            'url'         => get_permalink(),
        );
    } elseif (is_singular('project')) {
        global $post;
        $live_url = get_field('project_live_url', $post->ID);
        $code_url = get_field('project_code_url', $post->ID);
        $tech_stack = get_field('project_tech_stack', $post->ID);

        $schema = array(
            '@context'          => 'https://schema.org',
            '@type'             => 'SoftwareSourceCode',
            'name'              => get_the_title($post),
            'description'       => get_the_excerpt($post) ?: wp_trim_words(wp_strip_all_tags(get_the_content(null, false, $post)), 25),
            'programmingLanguage' => $tech_stack ?: 'PHP, JavaScript, WordPress',
            'codeRepository'    => $code_url ?: home_url('/'),
            'url'               => get_permalink($post),
        );
    }

    if (!empty($schema)) {
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
    }
}
add_action('wp_head', 'abc_tech_render_schema_org', 20);

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

/**
 * =========================================================================
 * REGISTRO DE STRINGS DO TEMA NO POLYLANG (Painel Idiomas -> Traduções)
 * =========================================================================
 */
function abc_tech_register_polylang_strings()
{
    if (!function_exists('pll_register_string')) {
        return;
    }

    // Grupo: Portfólio & Filtros
    pll_register_string('Portfolio Filters', 'Portfólio de', 'abc-tech');
    pll_register_string('Portfolio Filters', 'Projetos', 'abc-tech');
    pll_register_string('Portfolio Filters', 'Uma seleção de projetos de alta performance, focados em arquitetura WordPress, otimização técnica de SEO e interfaces conversivas.', 'abc-tech');
    pll_register_string('Portfolio Filters', 'Todos', 'abc-tech');
    pll_register_string('Portfolio Filters', 'WordPress', 'abc-tech');
    pll_register_string('Portfolio Filters', 'PHP', 'abc-tech');
    pll_register_string('Portfolio Filters', 'JavaScript / React', 'abc-tech');
    pll_register_string('Portfolio Filters', 'SEO Técnico', 'abc-tech');
    pll_register_string('Portfolio Filters', 'Visualização:', 'abc-tech');
    pll_register_string('Portfolio Filters', 'Ver em Lista', 'abc-tech');
    pll_register_string('Portfolio Filters', 'Ver em Grid', 'abc-tech');
    pll_register_string('Portfolio Filters', 'Live', 'abc-tech');
    pll_register_string('Portfolio Filters', 'Role', 'abc-tech');
    pll_register_string('Portfolio Filters', 'Highlights', 'abc-tech');
    pll_register_string('Portfolio Filters', 'Ver Case', 'abc-tech');
    pll_register_string('Portfolio Filters', 'Nenhum projeto encontrado.', 'abc-tech');
    pll_register_string('Portfolio Filters', 'Voltar ao topo', 'abc-tech');

    // Grupo: General UI
    pll_register_string('General UI', 'Pular para o conteúdo principal', 'abc-tech');
    pll_register_string('General UI', 'Abrir menu', 'abc-tech');
    pll_register_string('General UI', 'Vamos Conversar', 'abc-tech');
    pll_register_string('General UI', 'Fale Conosco', 'abc-tech');
    pll_register_string('General UI', 'Entre em Contato', 'abc-tech');
    pll_register_string('General UI', 'Todos os direitos reservados.', 'abc-tech');

    // Grupo: Página 404
    pll_register_string('404 Page', 'Oops! Rota não encontrada.', 'abc-tech');
    pll_register_string('404 Page', 'Parece que você tentou acessar um endpoint que não existe. A página pode ter sido movida, excluída ou talvez você tenha digitado a URL incorretamente.', 'abc-tech');
    pll_register_string('404 Page', 'Voltar para a Home', 'abc-tech');
}
add_action('init', 'abc_tech_register_polylang_strings');

/**
 * Função Auxiliar de Tradução compatível com Polylang e gettext
 */
function abc_tech_tr($string)
{
    if (function_exists('pll__')) {
        return pll__($string);
    }
    return __($string, 'abc-tech');
}

/**
 * =========================================================================
 * GOOGLE ANALYTICS (GA4) INTEGRATION
 * =========================================================================
 */
function abc_tech_render_google_analytics()
{
?>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-SMCL9RD6WX"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-SMCL9RD6WX');
    </script>
<?php
}
add_action('wp_head', 'abc_tech_render_google_analytics', 1);

/**
 * =========================================================================
 * AGENT DISCOVERY & LINK HEADERS (RFC 8288 & RFC 9727)
 * =========================================================================
 */
function abc_tech_send_agent_headers()
{
    if (is_admin()) return;

    $link_headers = array(
        '</.well-known/api-catalog>; rel="api-catalog"',
        '</.well-known/agent-skills/index.json>; rel="agent-skills"',
        '</.well-known/mcp/server-card.json>; rel="mcp-server"',
        '</.well-known/oauth-protected-resource>; rel="oauth-protected-resource"',
        '</.well-known/openid-configuration>; rel="authorizing-issuer"'
    );
    header('Link: ' . implode(', ', $link_headers), false);
}
add_action('send_headers', 'abc_tech_send_agent_headers');

/**
 * =========================================================================
 * MARKDOWN NEGOTIATION FOR AGENTS (Accept: text/markdown)
 * Runs at init priority 0 to bypass LiteSpeed cache for markdown requests.
 * =========================================================================
 */
function abc_tech_handle_markdown_negotiation()
{
    if (is_admin()) return;

    $accept_header = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
    $is_markdown_req = (strpos($accept_header, 'text/markdown') !== false) || isset($_GET['markdown']);

    if (!$is_markdown_req) return;

    // Bypass LiteSpeed, WP Super Cache, and other full-page caches
    define('LSCACHE_NO_CACHE', true);
    header('X-LiteSpeed-Cache-Control: no-cache');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Vary: Accept');
    header('Content-Type: text/markdown; charset=utf-8');
    header('Access-Control-Allow-Origin: *');

    // Build page context from WP query or static metadata
    global $post;
    $site_name = get_bloginfo('name');
    $site_desc = get_bloginfo('description');
    $req_path  = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    $url       = esc_url(home_url($req_path));

    // Try to get actual post content
    $content = '';
    if ($post && !empty($post->post_content)) {
        $content = wp_strip_all_tags(apply_filters('the_content', $post->post_content));
    }

    // Fallback context based on URL path
    if (empty($content)) {
        if (strpos($req_path, '/projetos') !== false) {
            $content = "Portfolio of WordPress development and technical SEO projects by Allyson Belo. Each project demonstrates expertise in custom theme architecture, Core Web Vitals optimization, and performance-first WordPress development.";
        } elseif (strpos($req_path, '/sobre') !== false) {
            $content = "Allyson Belo is a WordPress Architect and Technical SEO Specialist with expertise in high-performance custom themes, PHP architecture, and Core Web Vitals optimization.";
        } elseif (strpos($req_path, '/contato') !== false) {
            $content = "Contact page for project inquiries and collaboration requests. Allyson Belo is available for WordPress development, Technical SEO, and performance optimization projects.";
        } else {
            $content = "Allyson Belo - WordPress Architect & Technical SEO Specialist. Expert in high-performance custom WordPress themes, Core Web Vitals, and clean PHP architecture.";
        }
    }

    $title = $site_name;
    if ($post) {
        $the_title = get_the_title($post);
        if ($the_title) $title = $the_title;
    }

    $md  = "# " . $title . "\n\n";
    $md .= "> " . $site_desc . "\n\n";
    $md .= "**URL:** " . $url . "\n\n";
    $md .= "---\n\n";
    $md .= "## Overview\n\n";
    $md .= $content . "\n\n";
    $md .= "---\n\n";
    $md .= "## Discovery Endpoints\n\n";
    $md .= "| Resource | URL |\n";
    $md .= "|---|---|\n";
    $md .= "| Agent Skills Index | " . home_url('/.well-known/agent-skills/index.json') . " |\n";
    $md .= "| API Catalog | " . home_url('/.well-known/api-catalog') . " |\n";
    $md .= "| MCP Server Card | " . home_url('/.well-known/mcp/server-card.json') . " |\n";
    $md .= "| WordPress REST API | " . home_url('/wp-json/wp/v2/') . " |\n\n";
    $md .= "## Contact & Info\n\n";
    $md .= "- Email: contato@allysonbelo.com\n";
    $md .= "- GitHub: https://github.com/allysonbelo\n";
    $md .= "- LinkedIn: https://www.linkedin.com/in/allysoncavalcante/\n";

    header('X-Markdown-Tokens: ' . str_word_count($md));
    echo $md;
    exit;
}
add_action('init', 'abc_tech_handle_markdown_negotiation', 0);

/**
 * =========================================================================
 * ROBOTS.TXT CONTENT SIGNALS & SITEMAP FILTER
 * =========================================================================
 */
function abc_tech_custom_robots_txt($output, $public)
{
    $site_url = home_url();
    $robots = "User-agent: *\n";
    $robots .= "Allow: /\n\n";
    $robots .= "Content-Signal: ai-train=no, search=yes, ai-input=no\n\n";
    $robots .= "Sitemap: {$site_url}/sitemap_index.xml\n";
    return $robots;
}
add_filter('robots_txt', 'abc_tech_custom_robots_txt', 100, 2);

/**
 * =========================================================================
 * ROTEAMENTO PROGRAMÁTICO GLOBAL DOS ENDPOINTS DE IA & AGENT DISCOVERY
 * =========================================================================
 */
function abc_tech_handle_well_known_requests()
{
    $req_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $path_info = parse_url($req_uri, PHP_URL_PATH);

    // 1. Rota /auth.md
    if ($path_info === '/auth.md') {
        header('Content-Type: text/markdown; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        echo "# Agent Auth Registration Guidelines\n\n";
        echo "Welcome! This site supports AI agent discovery and interaction.\n\n";
        echo "## Discovery Endpoints\n";
        echo "- OAuth Protected Resource Metadata: https://allysonbelo.com/.well-known/oauth-protected-resource\n";
        echo "- OAuth Authorization Server: https://allysonbelo.com/.well-known/oauth-authorization-server\n";
        echo "- API Catalog: https://allysonbelo.com/.well-known/api-catalog\n";
        echo "- Agent Skills Index: https://allysonbelo.com/.well-known/agent-skills/index.json\n";
        echo "- MCP Server Card: https://allysonbelo.com/.well-known/mcp/server-card.json\n\n";
        echo "## Access & Capabilities\n";
        echo "AI agents may query public portfolio cases, technical specifications, and metadata.\n";
        echo "For direct contact, send inquiries to https://allysonbelo.com/contato/ or via WordPress REST API at /wp-json/wp/v2/\n";
        exit;
    }

    // 2. Rotas /.well-known/*
    if (preg_match('#^\/\.well-known\/(.+)$#i', $path_info, $matches)) {
        $path = strtolower(trim($matches[1], '/'));
        header('Access-Control-Allow-Origin: *');

        // Agent Skills Index
        if ($path === 'agent-skills/index.json' || $path === 'agent-skills') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                '$schema' => 'https://agentskills.io/schema/v0.2.0/index.json',
                'skills'  => array(
                    array(
                        'name'        => 'portfolio-search',
                        'type'        => 'search',
                        'description' => 'Discover WordPress engineering portfolio projects and technical SEO cases',
                        'url'         => home_url('/projetos/'),
                        'sha256'      => 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855'
                    ),
                    array(
                        'name'        => 'contact-developer',
                        'type'        => 'action',
                        'description' => 'Send a direct message or project inquiry to WordPress Architect Allyson Belo',
                        'url'         => home_url('/contato/'),
                        'sha256'      => 'f4c9c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b899'
                    )
                )
            ), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            exit;
        }

        // API Catalog (RFC 9727)
        if ($path === 'api-catalog') {
            header('Content-Type: application/linkset+json; charset=utf-8');
            echo json_encode(array(
                'linkset' => array(
                    array(
                        'anchor'       => home_url('/wp-json/wp/v2/'),
                        'service-desc' => array(array('href' => home_url('/wp-json/'), 'type' => 'application/json')),
                        'service-doc'  => array(array('href' => 'https://developer.wordpress.org/rest-api/', 'type' => 'text/html')),
                        'status'       => array(array('href' => home_url('/wp-json/wp/v2/posts?per_page=1'), 'type' => 'application/json'))
                    )
                )
            ), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            exit;
        }

        // MCP Server Card (SEP-1649)
        if ($path === 'mcp/server-card.json' || $path === 'mcp/server-card') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'serverInfo'   => array(
                    'name'        => 'allysonbelo-portfolio-mcp',
                    'version'     => '1.0.0',
                    'description' => 'MCP Server for Allyson Belo Portfolio, Project Discovery and Technical SEO Specs'
                ),
                'transport'    => array(
                    'type'     => 'sse',
                    'endpoint' => home_url('/wp-json/wp/v2/project')
                ),
                'capabilities' => array('resources' => true, 'tools' => true, 'prompts' => true)
            ), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            exit;
        }

        // OpenID Configuration
        if ($path === 'openid-configuration') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'issuer'                 => home_url('/'),
                'authorization_endpoint' => home_url('/wp-login.php'),
                'token_endpoint'         => home_url('/wp-json/jwt-auth/v1/token'),
                'jwks_uri'               => home_url('/.well-known/http-message-signatures-directory'),
                'response_types_supported'=> array('code', 'token'),
                'grant_types_supported'  => array('authorization_code', 'client_credentials', 'password'),
                'subject_types_supported'=> array('public'),
                'id_token_signing_alg_values_supported' => array('RS256')
            ), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            exit;
        }

        // OAuth Authorization Server
        if ($path === 'oauth-authorization-server') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'issuer'                 => home_url('/'),
                'authorization_endpoint' => home_url('/wp-login.php'),
                'token_endpoint'         => home_url('/wp-json/jwt-auth/v1/token'),
                'jwks_uri'               => home_url('/.well-known/http-message-signatures-directory'),
                'grant_types_supported'  => array('authorization_code', 'client_credentials'),
                'response_types_supported'=> array('code'),
                'agent_auth'             => array(
                    'register_uri'              => home_url('/auth.md'),
                    'supported_identity_types'  => array('agent', 'user'),
                    'supported_credential_types'=> array('bearer_token')
                )
            ), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            exit;
        }

        // OAuth Protected Resource (RFC 9728)
        if ($path === 'oauth-protected-resource') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'resource'              => home_url('/wp-json/'),
                'authorization_servers' => array(home_url('/')),
                'scopes_supported'      => array('read', 'write', 'portfolio:read', 'contact:write')
            ), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            exit;
        }

        // Web Bot Auth Directory
        if ($path === 'http-message-signatures-directory') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'keys' => array(
                    array('kty' => 'RSA', 'use' => 'sig', 'alg' => 'RS256', 'kid' => 'bot-key-1', 'n' => 'u1W5b8z2...', 'e' => 'AQAB')
                )
            ), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            exit;
        }

        // ACP Discovery
        if ($path === 'acp.json' || $path === 'acp') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'protocol'             => array('name' => 'acp', 'version' => '1.0.0'),
                'api_base_url'         => home_url('/wp-json/'),
                'supported_transports' => array('https'),
                'capabilities'         => array('services' => array('inquiry', 'portfolio-access'))
            ), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            exit;
        }

        // UCP Profile
        if ($path === 'ucp') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'version'      => '1.0.0',
                'services'     => array('content-licensing', 'portfolio-discovery'),
                'capabilities' => array('free-read', 'agent-query'),
                'endpoints'    => array(
                    'catalog' => home_url('/.well-known/api-catalog'),
                    'skills'  => home_url('/.well-known/agent-skills/index.json')
                )
            ), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            exit;
        }
    }
}
add_action('init', 'abc_tech_handle_well_known_requests', 0);