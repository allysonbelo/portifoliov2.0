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
