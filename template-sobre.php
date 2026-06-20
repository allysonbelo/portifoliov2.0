<?php

/**
 * Template Name: Página Sobre
 * Description: Layout "Technical Precision" para a página Sobre/Bio.
 * Padrões aplicados: Late Escaping (Security), i18n (Tradução) e ACF Free Modular.
 */
get_header();

// =========================================================================
// 1. RESGATE DOS DADOS (Com Fallbacks Traduzíveis)
// =========================================================================

// Hero
// Hero
$hero_prompt  = get_field('about_prompt') ?: __('// SYSTEM.OUT.PRINTLN("HELLO, WORLD");', 'abc-tech'); // <-- Adicionado aqui
$hero_title_1 = get_field('about_title_1') ?: __('Arquitetando a Web', 'abc-tech');
$hero_title_1 = get_field('about_title_1') ?: __('Arquitetando a Web', 'abc-tech');
$hero_title_2 = get_field('about_title_2') ?: __('Linha por Linha.', 'abc-tech');
$hero_bio     = get_field('about_bio') ?: __('<p>Sou Allyson Belo, um desenvolvedor movido pela busca incessante pela performance e precisão. Minha trajetória na tecnologia começou não apenas com o desejo de criar interfaces bonitas, mas com a obsessão de entender como a web funciona em sua essência.</p><p>Acredito que o verdadeiro design reside na fundação de um código limpo, semântico e altamente otimizado. Como arquiteto focado em WordPress e SEO Técnico, transformo problemas complexos em soluções elegantes, garantindo que cada projeto não apenas brilhe visualmente, mas domine os motores de busca.</p><p>Quando não estou auditando Core Web Vitals ou estruturando ecossistemas complexos de dados, estou explorando novas fronteiras no desenvolvimento Front-End, sempre buscando a interseção perfeita entre forma e função.</p>', 'abc-tech');
$hero_cv_url  = get_field('about_cv_file') ?: '#';
$hero_image   = get_field('about_image');
$status_label = get_field('about_status_label') ?: __('STATUS', 'abc-tech');
$status_value = get_field('about_status_value') ?: __('Otimização Extrema', 'abc-tech');

// Seção: Formação
$edu_title    = get_field('about_edu_title') ?: __('Formação & Especialização', 'abc-tech');

// Seção: Métricas
$metrics_title = get_field('about_metrics_title') ?: __('Métricas Operacionais', 'abc-tech');

?>

<main id="primary" class="site-main page-about">

    <!-- ========================================================== -->
    <!-- 1. HERO SECTION -->
    <!-- ========================================================== -->
    <section class="about-hero-section editable-section">

        <?php
        // Ícone de Edição - Foco no Hero
        if (function_exists('abc_tech_edit_section_icon')) {
            abc_tech_edit_section_icon('group_about_hero');
        }
        ?>

        <div class="container about-hero-grid">

            <div class="about-content reveal-element">
                <div class="system-prompt">
                    <span class="prompt-icon">_</span>
                    <span class="prompt-text"><?php echo esc_html($hero_prompt); ?></span>
                </div>

                <h1 class="about-title">
                    <?php echo esc_html($hero_title_1); ?> <br>
                    <span class="highlight-primary"><?php echo esc_html($hero_title_2); ?></span>
                </h1>

                <div class="about-bio">
                    <?php echo wp_kses_post(wpautop($hero_bio)); ?>
                </div>

                <a href="<?php echo esc_url($hero_cv_url); ?>" class="btn-download-cv" target="_blank" rel="noopener noreferrer">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <?php esc_html_e('Download CV', 'abc-tech'); ?>
                </a>
            </div>

            <div class="about-image-wrapper reveal-element" style="transition-delay: 0.2s;">
                <div class="image-glow"></div>

                <?php
                $img_url = $hero_image ? $hero_image['url'] : get_template_directory_uri() . '/images/front-end-wordpress-developer-and-technical-seo.webp';
                $img_alt = $hero_image ? $hero_image['alt'] : __('Allyson Belo - Desenvolvedor', 'abc-tech');
                ?>
                <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($img_alt); ?>" class="profile-image">

                <div class="status-badge">
                    <div class="status-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                        </svg>
                    </div>
                    <div class="status-info">
                        <span class="status-label"><?php echo esc_html($status_label); ?></span>
                        <span class="status-value"><?php echo esc_html($status_value); ?></span>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ========================================================== -->
    <!-- 2. FORMAÇÃO & ESPECIALIZAÇÃO -->
    <!-- ========================================================== -->
    <section class="education-section editable-section">

        <?php
        // Ícone de Edição - Foco na Educação
        if (function_exists('abc_tech_edit_section_icon')) {
            abc_tech_edit_section_icon('group_about_edu');
        }
        ?>

        <div class="container">
            <h2 class="section-title">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 12px; color: var(--color-secondary);">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                </svg>
                <?php echo esc_html($edu_title); ?>
            </h2>

            <div class="education-grid">

                <?php
                $edu_icons = [
                    1 => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>',
                    2 => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>'
                ];

                $has_edu = false;
                $delay_edu = 0;

                for ($i = 1; $i <= 2; $i++) {
                    $e_title = get_field("about_edu_{$i}_title");

                    if ($e_title) {
                        $has_edu = true;
                        $e_year = get_field("about_edu_{$i}_year");
                        $e_inst = get_field("about_edu_{$i}_inst");
                        $e_desc = get_field("about_edu_{$i}_desc");
                        $delay_attr = ($delay_edu > 0) ? 'style="transition-delay: ' . $delay_edu . 's;"' : '';
                ?>
                        <div class="edu-card reveal-element" <?php echo $delay_attr; ?>>
                            <div class="edu-header">
                                <div class="edu-icon">
                                    <?php echo $edu_icons[$i]; ?>
                                </div>
                                <span class="edu-year"><?php echo esc_html($e_year); ?></span>
                            </div>
                            <h3 class="edu-title"><?php echo esc_html($e_title); ?></h3>
                            <div class="edu-institution"><?php echo esc_html($e_inst); ?></div>
                            <p class="edu-desc"><?php echo esc_html($e_desc); ?></p>
                        </div>
                    <?php
                        $delay_edu += 0.2;
                    }
                }

                if (!$has_edu) : ?>
                    <div class="edu-card reveal-element">
                        <div class="edu-header">
                            <div class="edu-icon"><?php echo $edu_icons[1]; ?></div>
                            <span class="edu-year">2018 - 2021</span>
                        </div>
                        <h3 class="edu-title"><?php esc_html_e('Análise e Desenvolvimento de Sistemas', 'abc-tech'); ?></h3>
                        <div class="edu-institution"><?php esc_html_e('Universidade Tecnológica', 'abc-tech'); ?></div>
                        <p class="edu-desc"><?php esc_html_e('Fundação rigorosa em lógica de programação, arquitetura de software e banco de dados. Foco em metodologias ágeis e engenharia de software voltada para performance e escalabilidade estrutural.', 'abc-tech'); ?></p>
                    </div>

                    <div class="edu-card reveal-element" style="transition-delay: 0.2s;">
                        <div class="edu-header">
                            <div class="edu-icon"><?php echo $edu_icons[2]; ?></div>
                            <span class="edu-year">2022 - 2023</span>
                        </div>
                        <h3 class="edu-title"><?php esc_html_e('Pós-Graduação: Front-End & Mobile-First', 'abc-tech'); ?></h3>
                        <div class="edu-institution"><?php esc_html_e('Instituto de Pós-Graduação Tech', 'abc-tech'); ?></div>
                        <p class="edu-desc"><?php esc_html_e('Especialização avançada na construção de interfaces responsivas e progressivas. Domínio de frameworks modernos, acessibilidade (WCAG) e estratégias avançadas de renderização para a web moderna.', 'abc-tech'); ?></p>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </section>

    <!-- ========================================================== -->
    <!-- 3. MÉTRICAS OPERACIONAIS -->
    <!-- ========================================================== -->
    <section class="metrics-section editable-section">

        <?php
        // Ícone de Edição - Foco nas Métricas
        if (function_exists('abc_tech_edit_section_icon')) {
            abc_tech_edit_section_icon('group_about_metrics');
        }
        ?>

        <div class="container">
            <h2 class="section-title centered"><?php echo esc_html($metrics_title); ?></h2>

            <div class="metrics-grid">

                <?php
                $metric_icons = [
                    1 => '<svg class="metric-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>',
                    2 => '<svg class="metric-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>',
                    3 => '<svg class="metric-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>',
                    4 => '<svg class="metric-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>'
                ];

                $has_metrics = false;
                $delay_metric = 0;

                for ($i = 1; $i <= 4; $i++) {
                    $m_val = get_field("about_m_{$i}_val");

                    if ($m_val) {
                        $has_metrics = true;
                        $m_label = get_field("about_m_{$i}_label");
                        $m_color = get_field("about_m_{$i}_color") ?: 'color-green';
                        $delay_attr = ($delay_metric > 0) ? 'style="transition-delay: ' . $delay_metric . 's;"' : '';
                ?>
                        <div class="metric-card reveal-element" <?php echo $delay_attr; ?>>
                            <?php echo $metric_icons[$i]; ?>
                            <div class="metric-value <?php echo esc_attr($m_color); ?>"><?php echo esc_html($m_val); ?></div>
                            <div class="metric-label"><?php echo esc_html($m_label); ?></div>
                        </div>
                    <?php
                        $delay_metric += 0.1;
                    }
                }

                if (!$has_metrics) : ?>
                    <div class="metric-card reveal-element">
                        <?php echo $metric_icons[1]; ?>
                        <div class="metric-value color-green">&gt; 10k</div>
                        <div class="metric-label"><?php esc_html_e('COMMITS NO GITHUB', 'abc-tech'); ?></div>
                    </div>
                    <div class="metric-card reveal-element" style="transition-delay: 0.1s;">
                        <?php echo $metric_icons[2]; ?>
                        <div class="metric-value color-cyan">5k+</div>
                        <div class="metric-label"><?php esc_html_e('HORAS DE IMERSÃO', 'abc-tech'); ?></div>
                    </div>
                    <div class="metric-card reveal-element" style="transition-delay: 0.2s;">
                        <?php echo $metric_icons[3]; ?>
                        <div class="metric-value color-orange">∞</div>
                        <div class="metric-label"><?php esc_html_e('XÍCARAS DE CAFÉ', 'abc-tech'); ?></div>
                    </div>
                    <div class="metric-card reveal-element" style="transition-delay: 0.3s;">
                        <?php echo $metric_icons[4]; ?>
                        <div class="metric-value color-green">100</div>
                        <div class="metric-label"><?php esc_html_e('LIGHTHOUSE SCORE', 'abc-tech'); ?></div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>