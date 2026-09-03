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
    <!-- Microsoft Clarity -->
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "ya6pgg0s55");
    </script>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <a class="skip-link screen-reader-text" href="#primary">
        <?php echo esc_html(abc_tech_tr('Pular para o conteúdo principal')); ?>
    </a>

    <header id="masthead" class="site-header">
        <div class="container header-inner">

            <!-- Logo / Branding -->
            <div class="site-branding">
                <?php if (has_custom_logo()) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" rel="home" class="site-logo-link">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/images/logo.svg'); ?>" alt="<?php bloginfo('name'); ?>" class="site-logo" width="145" height="40">
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Toggle -->
            <button type="button" id="menu-toggle" class="menu-toggle" aria-controls="primary-menu" aria-expanded="false" aria-label="<?php echo esc_attr(abc_tech_tr('Abrir menu')); ?>">
                <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                </span>
            </button>

            <!-- Navegação Principal -->
            <nav id="site-navigation" class="main-navigation">
                <!-- Header exclusivo do Drawer Mobile -->
                <div class="drawer-header">
                    <span class="drawer-title"><?php echo esc_html(abc_tech_tr('Navegação')); ?></span>
                    <button type="button" id="drawer-close" class="drawer-close-btn" aria-label="<?php echo esc_attr(abc_tech_tr('Fechar menu')); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <?php
                wp_nav_menu(array(
                    'theme_location' => 'menu-1',
                    'menu_id'        => 'primary-menu',
                    'container'      => false,
                    'fallback_cb'    => false,
                ));
                ?>

                <!-- CTA Button -->
                <?php
                $front_page_id   = function_exists('pll_get_post') ? pll_get_post(get_option('page_on_front')) : get_option('page_on_front');
                $page_id_to_use  = $front_page_id ?: get_the_ID();
                $cta_link_field  = get_field('header_cta_link', $page_id_to_use) ?: get_field('header_cta_link', 'option');

                if (is_array($cta_link_field) && !empty($cta_link_field['url'])) {
                    $header_cta_url    = $cta_link_field['url'];
                    $raw_cta_text      = !empty($cta_link_field['title']) ? $cta_link_field['title'] : 'Vamos Conversar';
                    $header_cta_text   = abc_tech_tr($raw_cta_text);
                    $header_cta_target = !empty($cta_link_field['target']) ? $cta_link_field['target'] : '_self';
                } else {
                    $raw_cta_text      = get_field('header_cta_text', $page_id_to_use) ?: (get_field('header_cta_text', 'option') ?: 'Vamos Conversar');
                    $header_cta_text   = abc_tech_tr($raw_cta_text);
                    $header_cta_url    = get_field('header_cta_url', $page_id_to_use) ?: (get_field('header_cta_url', 'option') ?: '#contato');
                    $header_cta_target = '_self';
                }
                ?>
                <div class="header-cta">
                    <a href="<?php echo esc_url($header_cta_url); ?>" target="<?php echo esc_attr($header_cta_target); ?>" class="btn btn-primary">
                        <?php echo esc_html($header_cta_text); ?>
                    </a>
                </div>
            </nav>

        </div>
    </header>

    <!-- Overlay / Backdrop para o Menu Mobile Drawer -->
    <div id="menu-backdrop" class="menu-backdrop" aria-hidden="true"></div>

    <!-- Início do conteúdo principal -->
    <main id="primary" class="site-main">