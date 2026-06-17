<?php
/**
 * Template Name: Página de Contato
 */
get_header(); ?>

<main id="primary" class="site-main page-contact">
    <section class="contact-section">
        <div class="container contact-grid">
            
            <!-- COLUNA ESQUERDA: Formulário -->
            <div class="contact-form-col reveal-element">
                <div class="contact-header">
                    <span class="label-technical secure-protocol">
                        INICIAR PROTOCOLO SEGURO
                    </span>
                    <h1 class="contact-title">Vamos construir algo extraordinário?</h1>
                    <p class="contact-description">
                        Pronto para arquitetar soluções digitais de alta performance? Envie suas especificações abaixo e aguarde uma resposta precisa em até 24 horas.
                    </p>
                </div>

                <div class="cf7-terminal-wrapper">
                    <?php 
                    // Mantenha o seu ID do shortcode aqui
                    echo do_shortcode('[contact-form-7 id="0f6f24c" title="Formulário de contato PT"]'); 
                    ?>
                </div>
            </div>

            <!-- COLUNA DIREITA: Card Data Links -->
            <div class="contact-info-col reveal-element" style="transition-delay: 0.2s;">
                <div class="data-links-card">
                    <div class="card-dots">
                        <span class="dot dot-red"></span>
                        <span class="dot dot-yellow"></span>
                        <span class="dot dot-green"></span>
                    </div>

                    <h2 class="card-title">Data Links</h2>

                    <div class="data-list">
                        <!-- Email -->
                        <div class="data-item">
                            <div class="data-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            </div>
                            <div class="data-text">
                                <span class="data-label">CORREIO ELETRÔNICO</span>
                                <a href="mailto:contato@allysonbelo.com" class="data-value">contato@allysonbelo.com</a>
                            </div>
                        </div>

                        <!-- WhatsApp/Telefone -->
                        <div class="data-item">
                            <div class="data-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                            </div>
                            <div class="data-text">
                                <span class="data-label">COMUNICAÇÃO CRIPTOGRAFADA</span>
                                <a href="tel:+5500000000000" class="data-value">+55 (XX) XXXXX-XXXX</a>
                            </div>
                        </div>

                        <!-- LinkedIn -->
                        <div class="data-item">
                            <div class="data-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                            </div>
                            <div class="data-text">
                                <span class="data-label">REDE PROFISSIONAL</span>
                                <a href="https://www.linkedin.com/in/allysoncavalcante/" target="_blank" class="data-value">linkedin.com/in/allysonbelo</a>
                            </div>
                        </div>

                        <!-- GitHub -->
                        <div class="data-item">
                            <div class="data-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                            </div>
                            <div class="data-text">
                                <span class="data-label">REPOSITÓRIOS DE CÓDIGO</span>
                                <a href="https://github.com/allysonbelo" target="_blank" class="data-value">github.com/allysonbelo</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</main>

<?php get_footer(); ?>