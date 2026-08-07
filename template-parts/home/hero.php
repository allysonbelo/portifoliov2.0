<?php
/**
 * Template Part: Hero Section
 * Padrões aplicados: Late Escaping (Security), i18n e ACF Link Array handling.
 */

$status_text  = get_field('hero_status_text') ?: __('Status: Disponível para projetos', 'abc-tech');

$title_line_1 = get_field('hero_title_line_1') ?: __('Allyson Belo', 'abc-tech');
$title_line_2 = get_field('hero_title_line_2') ?: __('WordPress Architect', 'abc-tech');
$title_line_3 = get_field('hero_title_line_3') ?: __('SEO Strategist', 'abc-tech');

$description  = get_field('hero_description') ?: __('Desenvolvimento front-end de alta performance com arquitetura WordPress escalável. Foco obstinado em Core Web Vitals, semântica e resultados técnicos de SEO. Menos plugins, mais código puro.', 'abc-tech');

// Botão Primário: Extração de Array do campo Link
$btn1_text  = get_field('hero_btn_primary_text') ?: __('Vamos Conversar', 'abc-tech');
$btn1_field = get_field('hero_btn_primary_link');
$btn1_url   = is_array($btn1_field) ? $btn1_field['url'] : ($btn1_field ?: '#contato');
$btn1_target= is_array($btn1_field) && $btn1_field['target'] ? $btn1_field['target'] : '_self';

// Botão Secundário: Extração de Array do campo Link
$btn2_text  = get_field('hero_btn_secondary_text') ?: __('Ver Stack Técnico', 'abc-tech');
$btn2_field = get_field('hero_btn_secondary_link');
$btn2_url   = is_array($btn2_field) ? $btn2_field['url'] : ($btn2_field ?: '#stack');
$btn2_target= is_array($btn2_field) && $btn2_field['target'] ? $btn2_field['target'] : '_self';

$typing_texts = get_field('hero_code_snippet') ?: "function buildFast() {\n  return 'Core Web Vitals 100%';\n}";
?>

<section class="hero-section editable-section">
    <?php 
    if (function_exists('abc_tech_edit_section_icon')) {
        abc_tech_edit_section_icon('group_hero_section'); 
    }
    ?>
    <div class="container hero-inner">
        <div class="hero-content">
            
            <div class="hero-status">
                <span class="status-dot"></span>
                <span class="status-text"><?php echo esc_html( $status_text ); ?></span>
            </div>
            
            <h1 class="hero-title">
                <span class="title-line text-white"><?php echo esc_html( $title_line_1 ); ?></span><br>
                <span class="title-line text-blue"><?php echo esc_html( $title_line_2 ); ?></span> <span class="text-white">&amp;</span><br>
                <span class="title-line text-orange"><?php echo esc_html( $title_line_3 ); ?></span>
            </h1>
            
            <p class="hero-description">
                <?php echo esc_html( $description ); ?>
            </p>
            
            <div class="hero-actions">
                <a href="<?php echo esc_url( $btn1_url ); ?>" target="<?php echo esc_attr( $btn1_target ); ?>" class="btn btn-primary">
                    <?php echo esc_html( $btn1_text ); ?>
                </a>
                <a href="<?php echo esc_url( $btn2_url ); ?>" target="<?php echo esc_attr( $btn2_target ); ?>" class="btn btn-secondary">
                    <?php echo esc_html( $btn2_text ); ?>
                </a>
            </div>
        </div>

        <div class="hero-visual">
            <div class="code-window">
                <div class="window-header">
                    <div class="mac-dots">
                        <span class="dot dot-red"></span>
                        <span class="dot dot-yellow"></span>
                        <span class="dot dot-green"></span>
                    </div>
                </div>
                <div class="window-body">
                    <pre><code id="typing-text" data-typing="<?php echo esc_attr( $typing_texts ); ?>"></code></pre>
                </div>
            </div>
            <div class="hero-glow"></div>
        </div>
    </div>
</section>