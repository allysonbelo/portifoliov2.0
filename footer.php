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
            <a href="<?php echo esc_url(home_url('/')); ?>" class="footer-title">
                <?php bloginfo('name'); // Puxa dinamicamente "ABC tech" 
                ?>
            </a>
            <p class="footer-copyright">
                &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php esc_html_e('Todos os direitos reservados.', 'abc-tech'); ?>
            </p>
        </div>

        <nav class="footer-social" aria-label="<?php esc_attr_e('Links Sociais', 'abc-tech'); ?>">
            <ul role="list">
                <li><a href="#" target="_blank" rel="noopener noreferrer"><?php esc_html_e('LinkedIn', 'abc-tech'); ?></a></li>
                <li><a href="#" target="_blank" rel="noopener noreferrer"><?php esc_html_e('GitHub', 'abc-tech'); ?></a></li>
                <li><a href="#" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Twitter', 'abc-tech'); ?></a></li>
                <li><a href="#" target="_blank" rel="noopener noreferrer"><?php esc_html_e('WhatsApp', 'abc-tech'); ?></a></li>
            </ul>
        </nav>

    </div>
</footer>

<?php wp_footer(); // Hook obrigatório para carregar scripts no rodapé 
?>
</body>

</html>