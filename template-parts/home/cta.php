<?php
/**
 * Template Part: Call to Action (CTA) Section
 * Padrões aplicados: Late Escaping (Security), i18n (Tradução) e ACF Free.
 */

// Resgate dos Campos ACF com fallbacks traduzíveis
$cta_title    = get_field('cta_title') ?: __('Pronto para otimizar sua presença digital?', 'abc-tech');
$cta_desc     = get_field('cta_description') ?: __('Se você busca uma arquitetura WordPress robusta que passe nas métricas mais rígidas do Google, precisamos conversar.', 'abc-tech');

$cta_btn_text = get_field('cta_btn_text') ?: __('Vamos Conversar', 'abc-tech');
$cta_btn_link = get_field('cta_btn_link') ?: 'mailto:contato@allysonbelo.com';
?>

<section id="contato" class="cta-section editable-section">
    
    <?php 
    // Ícone de Edição Contextual
    if (function_exists('abc_tech_edit_section_icon')) {
        abc_tech_edit_section_icon('group_cta_section'); 
    }
    ?>

    <div class="container cta-inner reveal-element">
        
        <div class="cta-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="4" width="20" height="16" rx="2" ry="2"></rect>
                <polyline points="6 9 10 12 6 15"></polyline>
                <line x1="12" y1="15" x2="16" y2="15"></line>
            </svg>
        </div>

        <h2 class="cta-title">
            <?php echo esc_html( $cta_title ); ?>
        </h2>
        
        <p class="cta-description">
            <?php echo esc_html( $cta_desc ); ?>
        </p>

        <a href="<?php echo esc_url( $cta_btn_link ); ?>" class="btn btn-primary">
            <?php echo esc_html( $cta_btn_text ); ?>
        </a>

    </div>
</section>