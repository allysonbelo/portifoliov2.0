<?php
/**
 * Template Name: Página Sobre
 * Description: Layout "Technical Precision" para a página Sobre/Bio.
 */
get_header(); ?>

<main id="primary" class="site-main page-about">
    
    <!-- 1. HERO SECTION -->
    <section class="about-hero-section">
        <div class="container about-hero-grid">
            
            <!-- Esquerda: Texto e Bio -->
            <div class="about-content reveal-element">
                <div class="system-prompt">
                    <span class="prompt-icon">_</span> 
                    <span class="prompt-text">// SYSTEM.OUT.PRINTLN("HELLO, WORLD");</span>
                </div>
                
                <h1 class="about-title">
                    Arquitetando a Web <br>
                    <span class="highlight-primary">Linha por Linha.</span>
                </h1>
                
                <div class="about-bio">
                    <p>Sou Allyson Belo, um desenvolvedor movido pela busca incessante pela performance e precisão. Minha trajetória na tecnologia começou não apenas com o desejo de criar interfaces bonitas, mas com a obsessão de entender como a web funciona em sua essência.</p>
                    
                    <p>Acredito que o verdadeiro design reside na fundação de um código limpo, semântico e altamente otimizado. Como arquiteto focado em WordPress e SEO Técnico, transformo problemas complexos em soluções elegantes, garantindo que cada projeto não apenas brilhe visualmente, mas domine os motores de busca.</p>
                    
                    <p>Quando não estou auditando Core Web Vitals ou estruturando ecossistemas complexos de dados, estou explorando novas fronteiras no desenvolvimento Front-End, sempre buscando a interseção perfeita entre forma e função.</p>
                </div>

                <a href="#" class="btn-download-cv">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Download CV
                </a>
            </div>

            <!-- Direita: Imagem e Badge -->
            <div class="about-image-wrapper reveal-element" style="transition-delay: 0.2s;">
                <div class="image-glow"></div>
                <!-- DICA: Substitua o src abaixo pela URL real da sua foto no painel do WordPress -->
                <img src="<?php echo get_template_directory_uri(); ?>/images/front-end-wordpress-developer-and-technical-seo.webp" alt="Allyson Belo - Desenvolvedor" class="profile-image">
                
                <div class="status-badge">
                    <div class="status-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                    </div>
                    <div class="status-info">
                        <span class="status-label">STATUS</span>
                        <span class="status-value">Otimização Extrema</span>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- 2. FORMAÇÃO & ESPECIALIZAÇÃO -->
    <section class="education-section">
        <div class="container">
            <h2 class="section-title">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 12px; color: var(--color-secondary);"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>
                Formação & Especialização
            </h2>

            <div class="education-grid">
                <!-- Card 1 -->
                <div class="edu-card reveal-element">
                    <div class="edu-header">
                        <div class="edu-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </div>
                        <span class="edu-year">2018 - 2021</span>
                    </div>
                    <h3 class="edu-title">Análise e Desenvolvimento de Sistemas</h3>
                    <div class="edu-institution">Universidade Tecnológica</div>
                    <p class="edu-desc">Fundação rigorosa em lógica de programação, arquitetura de software e banco de dados. Foco em metodologias ágeis e engenharia de software voltada para performance e escalabilidade estrutural.</p>
                </div>

                <!-- Card 2 -->
                <div class="edu-card reveal-element" style="transition-delay: 0.2s;">
                    <div class="edu-header">
                        <div class="edu-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                        </div>
                        <span class="edu-year">2022 - 2023</span>
                    </div>
                    <h3 class="edu-title">Pós-Graduação: Front-End & Mobile-First</h3>
                    <div class="edu-institution">Instituto de Pós-Graduação Tech</div>
                    <p class="edu-desc">Especialização avançada na construção de interfaces responsivas e progressivas. Domínio de frameworks modernos, acessibilidade (WCAG) e estratégias avançadas de renderização para a web moderna.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. MÉTRICAS OPERACIONAIS -->
    <section class="metrics-section">
        <div class="container">
            <h2 class="section-title centered">Métricas Operacionais</h2>
            
            <div class="metrics-grid">
                <!-- Métrica 1 -->
                <div class="metric-card reveal-element">
                    <svg class="metric-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                    <div class="metric-value color-green">&gt; 10k</div>
                    <div class="metric-label">COMMITS NO GITHUB</div>
                </div>

                <!-- Métrica 2 -->
                <div class="metric-card reveal-element" style="transition-delay: 0.1s;">
                    <svg class="metric-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <div class="metric-value color-cyan">5k+</div>
                    <div class="metric-label">HORAS DE IMERSÃO</div>
                </div>

                <!-- Métrica 3 -->
                <div class="metric-card reveal-element" style="transition-delay: 0.2s;">
                    <svg class="metric-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
                    <div class="metric-value color-orange">∞</div>
                    <div class="metric-label">XÍCARAS DE CAFÉ</div>
                </div>

                <!-- Métrica 4 -->
                <div class="metric-card reveal-element" style="transition-delay: 0.3s;">
                    <svg class="metric-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    <div class="metric-value color-green">100</div>
                    <div class="metric-label">LIGHTHOUSE SCORE</div>
                </div>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>