<?php
/**
 * Configurações e Inicialização do Tema Technical Precision.
 * Foco em segurança, escaping e suporte a internacionalização (Polylang).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Previne acesso direto por segurança
}

function technical_precision_setup() {
    // Carrega o domínio de texto para tradução (Polylang)
    load_theme_textdomain( 'technical-precision', get_template_directory() . '/languages' );

    // Suporte a tags de título nativas do WP (Melhor prática de SEO)
    add_theme_support( 'title-tag' );

    // Suporte a miniaturas de post
    add_theme_support( 'post-thumbnails' );

    // Suporte a HTML5 para elementos mais semânticos
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );
}
add_action( 'after_setup_theme', 'technical_precision_setup' );

function technical_precision_scripts() {
    // Removemos a versão do WP dos assets para segurança
    $version = '1.0.0';

    // Enfileiramento das folhas de estilo
    wp_enqueue_style( 'tp-reset', get_template_directory_uri() . '/css/reset.css', array(), $version );
    wp_enqueue_style( 'tp-base', get_template_directory_uri() . '/css/base.css', array('tp-reset'), $version );
    wp_enqueue_style( 'tp-style', get_stylesheet_uri(), array('tp-base'), $version );
    
    // Fontes Google (Carregamento performático)
    wp_enqueue_style( 'tp-fonts', 'https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500&family=JetBrains+Mono:wght@500&family=Montserrat:wght@600;700&display=swap', array(), null );
}
add_action( 'wp_enqueue_scripts', 'technical_precision_scripts' );