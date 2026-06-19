<?php
/**
 * The template for displaying 404 pages (not found)
 */
get_header(); ?>

<main id="primary" class="site-main page-404">
    <section class="error-404-section">
        <div class="container error-container">

            <div class="error-content reveal-element" style="transition-delay: 0.2s;">
                <div class="glitch-wrapper">
                    <h1 class="glitch-404" data-text="404">404</h1>
                </div>
                
                <h2 class="error-title">Oops! Rota não encontrada.</h2>
                <p class="error-description">
                    Parece que você tentou acessar um endpoint que não existe. A página pode ter sido movida, excluída ou talvez você tenha digitado a URL incorretamente.
                </p>
                
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary btn-home">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px; margin-top: -2px;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    Voltar para a Home
                </a>
            </div>

            <div class="terminal-window reveal-element">
                <div class="terminal-header">
                    <div class="terminal-dots">
                        <span class="dot dot-red"></span>
                        <span class="dot dot-yellow"></span>
                        <span class="dot dot-green"></span>
                    </div>
                    <div class="terminal-title">terminal</div>
                </div>
                <div class="terminal-body">
                    <p><span class="prompt">~</span> curl -I https://<?php echo $_SERVER['SERVER_NAME']; ?>/endpoint</p>
                    <p class="http-error">HTTP/2 404</p>
                    <p>server: nginx/1.24.0</p>
                    <p>content-type: text/html; charset=UTF-8</p>
                    <p class="x-status">x-status-reason: Error 404: Path Not Found</p>
                    <p><span class="prompt">~</span> <span class="cursor">█</span></p>
                </div>
            </div>

        </div>
    </section>
</main>

<?php get_footer(); ?>