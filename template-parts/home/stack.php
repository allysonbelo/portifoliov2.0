<?php

/**
 * Template Part: Tech Stack & Expertise Section (Bento Grid)
 * Padrões aplicados: Late Escaping (Security), i18n (Tradução) e ACF Free.
 */

// Resgate dos Campos ACF com fallbacks traduzíveis para o Polylang/WPML
$stack_title    = get_field('stack_title') ?: __('Tech Stack & Expertise', 'abc-tech');
$stack_subtitle = get_field('stack_subtitle') ?: __('Ferramentas e tecnologias utilizadas para construir experiências digitais rápidas, seguras e otimizadas para motores de busca.', 'abc-tech');

// Card 1
$c1_title = get_field('stack_card1_title') ?: __('Arquitetura WordPress', 'abc-tech');
$c1_desc  = get_field('stack_card1_desc') ?: __('Desenvolvimento de temas sob medida (Custom Themes), foco em performance extrema sem excesso de plugins. Arquitetura raiz e código limpo.', 'abc-tech');

// Card 2
$c2_title = get_field('stack_card2_title') ?: __('Technical SEO', 'abc-tech');
$c2_score = get_field('stack_card2_score') ?: '99+'; // Números puros geralmente não precisam de tradução
$c2_label = get_field('stack_card2_label') ?: __('Core Web Vitals', 'abc-tech');
$c2_desc  = get_field('stack_card2_desc') ?: __('Otimização extrema de LCP, FID e CLS. Semântica impecável e marcação Schema.org avançada.', 'abc-tech');

// Card 3
$c3_title    = get_field('stack_card3_title') ?: __('Performance', 'abc-tech');
$c3_features = get_field('stack_card3_features') ?: __("Otimização de Banco de Dados e Queries\nOtimização de Assets (WebP, SVG, Critical CSS)\nMinimização de Bloqueio de Renderização", 'abc-tech');

// Card 4
$c4_title = get_field('stack_card4_title') ?: __('Fluxo de Trabalho', 'abc-tech');
$c4_items = get_field('stack_card4_items') ?: __("Git / GitHub\nAmbiente Local (XAMPP/Local)\nAdvanced Custom Fields (ACF)\nHospedagem / cPanel", 'abc-tech');
?>

<section id="stack" class="stack-section editable-section">

    <?php
    // Ícone de Edição do Frontend (Foca diretamente no grupo de campos desta secção)
    if (function_exists('abc_tech_edit_section_icon')) {
        abc_tech_edit_section_icon('group_tech_stack_section');
    }
    ?>

    <div class="container">

        <div class="section-header reveal-element">
            <!-- Late Escaping na saída -->
            <h2><?php echo esc_html($stack_title); ?></h2>
            <p><?php echo esc_html($stack_subtitle); ?></p>
        </div>

        <div class="bento-grid">

            <div class="bento-card card-arquitetura reveal-element">
                <div class="card-header">
                    <h3><?php echo esc_html($c1_title); ?></h3>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--color-secondary)" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                        <path d="M2 17l10 5 10-5M2 12l10 5 10-5"></path>
                    </svg>
                </div>
                <p><?php echo esc_html($c1_desc); ?></p>

                <!-- Chips de Tecnologia (Loop Manual para ACF Free) -->
                <div class="tech-chips">
                    <?php
                    $has_tech = false;

                    // Loop de 1 a 10 para resgatar os campos manuais
                    for ($i = 1; $i <= 10; $i++) {
                        $tech_name = get_field("stack_c1_tech_{$i}_name");

                        // Se houver um nome cadastrado, ele renderiza o chip
                        if ($tech_name) {
                            $has_tech = true;
                            $tech_icon  = get_field("stack_c1_tech_{$i}_icon");
                            // Resgata o Hexadecimal do Color Picker (padrão: branco #ffffff)
                            $tech_color = get_field("stack_c1_tech_{$i}_color") ?: '#ffffff';
                    ?>
                            <span class="chip">
                                <!-- esc_attr para o CSS inline e esc_html para os textos -->
                                <span class="chip-icon" style="color: <?php echo esc_attr($tech_color); ?>;">
                                    <?php echo esc_html($tech_icon); ?>
                                </span>
                                <?php echo esc_html($tech_name); ?>
                            </span>
                        <?php
                        }
                    }

                    // Fallback visual traduzível
                    if (! $has_tech) : ?>
                        <span class="chip"><span class="chip-icon" style="color: #E3872D;">&lt;&gt;</span> <?php esc_html_e('HTML5 & CSS3', 'abc-tech'); ?></span>
                        <span class="chip"><span class="chip-icon" style="color: #F7DF1E;">JS</span> <?php esc_html_e('Vanilla JS', 'abc-tech'); ?></span>
                        <span class="chip"><span class="chip-icon" style="color: #777BB4;">PHP</span> <?php esc_html_e('PHP 8+', 'abc-tech'); ?></span>
                        <span class="chip"><span class="chip-icon" style="color: #FFFFFF;">W</span> <?php esc_html_e('WordPress Core', 'abc-tech'); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bento-card card-seo text-center reveal-element">
                <h3><?php echo esc_html($c2_title); ?></h3>
                <div class="seo-badge-container">
                    <div class="seo-badge">
                        <span class="seo-score"><?php echo esc_html($c2_score); ?></span>
                    </div>
                </div>
                <p class="label-technical text-white"><?php echo esc_html($c2_label); ?></p>
                <p class="small-text"><?php echo esc_html($c2_desc); ?></p>
            </div>

            <div class="bento-card card-performance reveal-element">
                <div class="card-header">
                    <h3><?php echo esc_html($c3_title); ?></h3>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path>
                    </svg>
                </div>
                <ul class="feature-list" role="list">
                    <?php
                    // Truque do Textarea: Separa as linhas num array e cria um <li> para cada uma
                    $features_array = preg_split("/\r\n|\n|\r/", $c3_features);
                    foreach ($features_array as $feature) :
                        if (trim($feature) !== '') :
                    ?>
                            <li>
                                <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <!-- Late Escaping no item da lista -->
                                <?php echo esc_html(trim($feature)); ?>
                            </li>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </ul>
            </div>

            <div class="bento-card card-infra reveal-element">
                <div class="card-header">
                    <h3><?php echo esc_html($c4_title); ?></h3>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                </div>
                <div class="infra-grid">
                    <?php
                    // O mesmo truque aplicado às tags do grid
                    $items_array = preg_split("/\r\n|\n|\r/", $c4_items);
                    foreach ($items_array as $item) :
                        if (trim($item) !== '') :
                    ?>
                            <div class="infra-item"><?php echo esc_html(trim($item)); ?></div>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </div>
            </div>

        </div>
    </div>
</section>