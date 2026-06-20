<?php
/**
 * Template Part: Experiência & Trajetória Section
 * Padrões aplicados: Late Escaping (Security), i18n (Tradução) e ACF Free Loop.
 */

// Resgate dos Textos Principais com Fallback i18n
$exp_title    = get_field('exp_title') ?: __('Experiência & Trajetória', 'abc-tech');
$exp_subtitle = get_field('exp_subtitle') ?: __('Anos de foco em refinar a ponte entre design, código limpo e visibilidade em motores de busca para projetos de alto impacto.', 'abc-tech');
?>

<section id="experiencia" class="experience-section editable-section">
    
    <?php 
    // Ícone de Edição do Frontend
    if (function_exists('abc_tech_edit_section_icon')) {
        abc_tech_edit_section_icon('group_experience_section'); 
    }
    ?>

    <div class="container experience-inner">

        <div class="experience-intro reveal-element">
            <h2><?php echo esc_html($exp_title); ?></h2>
            <p><?php echo esc_html($exp_subtitle); ?></p>
        </div>

        <div class="experience-list">
            <?php
            $has_exp = false;
            $delay = 0.1; // Delay inicial da animação CSS

            // Loop Manual de 1 a 4 (Limitação contornada do ACF Free)
            for ($i = 1; $i <= 4; $i++) {
                $item_title = get_field("exp_item_{$i}_title");
                
                // Se a experiência tiver um título, ela é renderizada
                if ($item_title) {
                    $has_exp = true;
                    $item_badge = get_field("exp_item_{$i}_badge");
                    $item_color = get_field("exp_item_{$i}_color") ?: 'text-muted'; // Cor Padrão
                    $item_desc  = get_field("exp_item_{$i}_desc");
                    
                    // O primeiro item ganha o destaque 'is-active'
                    $active_class = ($i === 1) ? 'is-active' : '';
                    ?>
                    
                    <div class="experience-card <?php echo esc_attr($active_class); ?> reveal-element" style="transition-delay: <?php echo esc_attr($delay); ?>s;">
                        <div class="experience-card-header">
                            <h3><?php echo esc_html($item_title); ?></h3>
                            
                            <?php if ($item_badge) : ?>
                                <span class="experience-badge <?php echo esc_attr($item_color); ?>">
                                    <?php echo esc_html($item_badge); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <p><?php echo esc_html($item_desc); ?></p>
                    </div>

                    <?php
                    $delay += 0.1; // Aumenta o delay para o próximo card surgir depois
                }
            }

            // Fallback: Se não houver nada no WordPress, carrega o conteúdo padrão traduzível
            if (!$has_exp) :
            ?>
                <div class="experience-card is-active reveal-element" style="transition-delay: 0.1s;">
                    <div class="experience-card-header">
                        <h3><?php esc_html_e('Lead WP Developer & SEO Technical', 'abc-tech'); ?></h3>
                        <span class="experience-badge text-blue"><?php esc_html_e('Atual', 'abc-tech'); ?></span>
                    </div>
                    <p><?php esc_html_e('Liderança técnica em projetos de migração complexa, auditoria profunda de Core Web Vitals e desenvolvimento de arquiteturas Headless WordPress focadas em performance extrema para e-commerces e portais de conteúdo.', 'abc-tech'); ?></p>
                </div>

                <div class="experience-card reveal-element" style="transition-delay: 0.2s;">
                    <div class="experience-card-header">
                        <h3><?php esc_html_e('Front-End Developer (WP Spec)', 'abc-tech'); ?></h3>
                        <span class="experience-badge text-muted"><?php esc_html_e('Anterior', 'abc-tech'); ?></span>
                    </div>
                    <p><?php esc_html_e('Criação de temas personalizados a partir de protótipos de alta fidelidade. Otimização de scripts de terceiros, implementação de schema markup dinâmico e redução sistemática do tempo de carregamento (TTFB).', 'abc-tech'); ?></p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>