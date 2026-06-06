<?php

/**
 * The header for our theme
 * 
 * Foco em Acessibilidade (a11y), Mobile-First e Segurança.
 */
if (! defined('ABSPATH')) {
    exit; // Previne acesso direto
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <a class="skip-link screen-reader-text" href="#primary">
        <?php esc_html_e('Pular para o conteúdo principal', 'technical-precision'); ?>
    </a>

    <header id="masthead" class="site-header">
        <div class="container header-inner">

            <!-- Logo / Branding -->
            <div class="site-branding">
                <a href="<?php echo esc_url(home_url('/')); ?>" rel="home" class="site-title">
                    <?php bloginfo('name'); // WP+SEO ARCHITECT 
                    ?>
                </a>
            </div>

            <!-- Mobile Menu Toggle -->
            <button id="menu-toggle" class="menu-toggle" aria-controls="primary-menu" aria-expanded="false" aria-label="<?php esc_attr_e('Abrir menu', 'technical-precision'); ?>">
                <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                </span>
            </button>

            <!-- Navegação Principal -->
            <nav id="site-navigation" class="main-navigation">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'menu-1',
                    'menu_id'        => 'primary-menu',
                    'container'      => false,
                    'fallback_cb'    => false,
                ));
                ?>

                <!-- CTA Button -->
                <div class="header-cta">
                    <a href="#contato" class="btn btn-primary">
                        <?php esc_html_e('Vamos Conversar', 'abc-tech'); ?>
                    </a>
                </div>
            </nav>

        </div>
    </header>

    <!-- Início do conteúdo principal -->
    <main id="primary" class="site-main"></main>