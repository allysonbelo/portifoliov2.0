<?php

/**
 * O template para exibir o rodapé (footer) do tema ABC tech.
 *
 * Contém o fechamento da div #primary (aberta no header) e tudo o que vem depois.
 * Foco em semântica, segurança e suporte a tradução.
 */

if (! defined('ABSPATH')) {
    exit; // Previne acesso direto
}
?>
</main>
<footer id="colophon" class="site-footer">
    <div class="container footer-inner">

        <div class="footer-branding">
            <?php if (has_custom_logo()) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="footer-logo-link">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/images/logo.svg'); ?>" alt="<?php bloginfo('name'); ?>" class="footer-logo" width="130" height="36">
                </a>
            <?php endif; ?>
            <p class="footer-copyright">
                &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php echo esc_html(abc_tech_tr('Todos os direitos reservados.')); ?>
            </p>
        </div>

        <nav class="footer-social" aria-label="<?php esc_attr_e('Links do Rodapé', 'abc-tech'); ?>">
            <?php
            if (has_nav_menu('footer-menu')) :
                wp_nav_menu(array(
                    'theme_location' => 'footer-menu',
                    'menu_id'        => 'footer-menu',
                    'container'      => false,
                    'depth'          => 1,
                    'fallback_cb'    => false,
                ));
            else :
            ?>
                <ul role="list">
                    <li><a href="<?php echo esc_url(get_field('social_linkedin', 'option') ?: '#'); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('LinkedIn', 'abc-tech'); ?></a></li>
                    <li><a href="<?php echo esc_url(get_field('social_github', 'option') ?: '#'); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('GitHub', 'abc-tech'); ?></a></li>
                    <li><a href="<?php echo esc_url(get_field('social_twitter', 'option') ?: '#'); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Twitter', 'abc-tech'); ?></a></li>
                    <li><a href="<?php echo esc_url(get_field('social_whatsapp', 'option') ?: '#'); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('WhatsApp', 'abc-tech'); ?></a></li>
                </ul>
            <?php endif; ?>
        </nav>

    </div>
</footer>

<!-- Botão Voltar ao Topo (Back to Top) -->
<button id="back-to-top" class="back-to-top-btn" aria-label="<?php echo esc_attr(abc_tech_tr('Voltar ao topo')); ?>" title="<?php echo esc_attr(abc_tech_tr('Voltar ao topo')); ?>">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="18 15 12 9 6 15"></polyline>
    </svg>
</button>

<?php wp_footer(); // Hook obrigatório para carregar scripts no rodapé 
?>
</body>

</html>