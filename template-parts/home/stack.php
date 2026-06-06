<?php
/**
 * Template Part: Tech Stack & Expertise Section (Bento Grid)
 */
?>
<section id="stack" class="stack-section">
    <div class="container">
        
        <!-- Adicionada classe reveal-element -->
        <div class="section-header reveal-element">
            <h2><?php esc_html_e( 'Tech Stack & Expertise', 'abc-tech' ); ?></h2>
            <p><?php esc_html_e( 'Ferramentas e tecnologias utilizadas para construir experiências digitais rápidas, seguras e otimizadas para motores de busca.', 'abc-tech' ); ?></p>
        </div>

        <div class="bento-grid">
            
            <!-- Adicionada classe reveal-element a todos os cards -->
            <div class="bento-card card-arquitetura reveal-element">
                <div class="card-header">
                    <h3><?php esc_html_e( 'Arquitetura WordPress', 'abc-tech' ); ?></h3>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--color-secondary)" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
                </div>
                <p><?php esc_html_e( 'Desenvolvimento de temas sob medida (Custom Themes), foco em performance extrema sem excesso de plugins. Arquitetura raiz e código limpo.', 'abc-tech' ); ?></p>
                <div class="tech-chips">
                    <span class="chip"><span class="chip-icon text-orange">&lt;&gt;</span> HTML5 & CSS3</span>
                    <span class="chip"><span class="chip-icon text-yellow">JS</span> Vanilla JS</span>
                    <span class="chip"><span class="chip-icon text-blue">PHP</span> PHP 8+</span>
                    <span class="chip"><span class="chip-icon text-white">W</span> WordPress Core</span>
                </div>
            </div>

            <div class="bento-card card-seo text-center reveal-element">
                <h3><?php esc_html_e( 'Technical SEO', 'abc-tech' ); ?></h3>
                <div class="seo-badge-container">
                    <div class="seo-badge">
                        <span class="seo-score">99+</span>
                    </div>
                </div>
                <p class="label-technical text-white"><?php esc_html_e( 'Core Web Vitals', 'abc-tech' ); ?></p>
                <p class="small-text"><?php esc_html_e( 'Otimização extrema de LCP, FID e CLS. Semântica impecável e marcação Schema.org avançada.', 'abc-tech' ); ?></p>
            </div>

            <div class="bento-card card-performance reveal-element">
                <div class="card-header">
                    <h3><?php esc_html_e( 'Performance', 'abc-tech' ); ?></h3>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path></svg>
                </div>
                <ul class="feature-list" role="list">
                    <li>
                        <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <?php esc_html_e( 'Otimização de Banco de Dados e Queries', 'abc-tech' ); ?>
                    </li>
                    <li>
                        <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <?php esc_html_e( 'Otimização de Assets (WebP, SVG, Critical CSS)', 'abc-tech' ); ?>
                    </li>
                    <li>
                        <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <?php esc_html_e( 'Minimização de Bloqueio de Renderização', 'abc-tech' ); ?>
                    </li>
                </ul>
            </div>

            <div class="bento-card card-infra reveal-element">
                <div class="card-header">
                    <h3><?php esc_html_e( 'Fluxo de Trabalho', 'abc-tech' ); ?></h3>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                </div>
                <div class="infra-grid">
                    <div class="infra-item"><?php esc_html_e( 'Git / GitHub', 'abc-tech' ); ?></div>
                    <div class="infra-item"><?php esc_html_e( 'Ambiente Local (XAMPP/Local)', 'abc-tech' ); ?></div>
                    <div class="infra-item"><?php esc_html_e( 'Advanced Custom Fields (ACF)', 'abc-tech' ); ?></div>
                    <div class="infra-item"><?php esc_html_e( 'Hospedagem / cPanel', 'abc-tech' ); ?></div>
                </div>
            </div>

        </div>
    </div>
</section>