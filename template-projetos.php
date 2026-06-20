<?php
/**
 * Template Name: Portfólio de Projetos
 * Description: Layout em Lista Horizontal com opção de alternância para Grid 4 colunas.
 * Padrões: Late Escaping, i18n e Integração ACF.
 */

get_header(); 

// Resgate dos Campos da Página (Hero) com fallbacks traduzíveis
$hero_title_1 = get_field('portfolio_title_1') ?: __('Portfólio de', 'abc-tech');
$hero_title_2 = get_field('portfolio_title_2') ?: __('Projetos', 'abc-tech');
$hero_desc    = get_field('portfolio_description') ?: __('Uma seleção de projetos de alta performance, focados em arquitetura WordPress, otimização técnica de SEO e interfaces conversivas.', 'abc-tech');
?>

<main id="primary" class="site-main">
    
    <section class="projects-hero editable-section reveal-element">
        <?php 
        // Ícone de Edição para o Hero do Portfólio
        if (function_exists('abc_tech_edit_section_icon')) {
            abc_tech_edit_section_icon('group_portfolio_page'); 
        }
        ?>
        <div class="container">
            <h1 class="page-title">
                <?php echo esc_html($hero_title_1); ?>
                <span class="text-blue"><?php echo esc_html($hero_title_2); ?></span>
            </h1>
            <p class="page-description">
                <?php echo esc_html($hero_desc); ?>
            </p>
        </div>
    </section>

    <section class="projects-showcase">
        <div class="container">

            <div class="projects-controls reveal-element">
                <span class="view-label"><?php esc_html_e('Visualização:', 'abc-tech'); ?></span>
                <div class="view-toggle-group">
                    <button id="view-list" class="view-btn is-active" aria-label="<?php esc_attr_e('Ver em Lista', 'abc-tech'); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                    </button>
                    <button id="view-grid" class="view-btn" aria-label="<?php esc_attr_e('Ver em Grid', 'abc-tech'); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                    </button>
                </div>
            </div>

            <div id="projects-container" class="modern-project-layout list-view">

                <?php
                $args = array(
                    'post_type'      => 'project',
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                    'orderby'        => 'menu_order date',
                );

                $project_query = new WP_Query($args);

                if ($project_query->have_posts()) :
                    $count = 0;

                    while ($project_query->have_posts()) : $project_query->the_post();
                        
                        // Custom Fields do CPT "project"
                        $is_live          = get_field('project_is_live');
                        $tech_stack       = get_field('project_tech_stack');
                        $project_role     = get_field('project_role') ?: __('Custom Theme Developer', 'abc-tech'); 
                        $highlights_raw   = get_field('project_highlights') ?: __('PageSpeed 100/100, LCP < 1.2s, SEO Técnico', 'abc-tech');

                        $delay = ($count > 0) ? 'style="transition-delay: 0.' . ($count % 4) . 's;"' : '';
                ?>

                        <article class="project-card reveal-element" <?php echo $delay; ?>>
                            <a href="<?php echo esc_url(get_permalink()); ?>" class="project-link">

                                <div class="project-image-wrapper">
                                    <div class="window-header-mini">
                                        <div class="mac-dots">
                                            <span class="dot dot-red"></span>
                                            <span class="dot dot-yellow"></span>
                                            <span class="dot dot-green"></span>
                                        </div>
                                    </div>

                                    <div class="img-container">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <?php the_post_thumbnail('large', array('class' => 'project-img')); ?>
                                        <?php else: ?>
                                            <img src="<?php echo esc_url(get_template_directory_uri()); ?>/images/placeholder.jpg" alt="<?php the_title_attribute(); ?>" class="project-img">
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="project-content">
                                    <div class="project-content-inner">

                                        <div class="project-meta-top">
                                            <?php if ($is_live) : ?>
                                                <span class="live-indicator"><span class="dot"></span><?php esc_html_e('Live', 'abc-tech'); ?></span>
                                            <?php endif; ?>
                                            <span class="project-year"><?php echo esc_html(get_the_date('Y')); ?></span>
                                        </div>

                                        <h2 class="project-title"><?php the_title(); ?></h2>

                                        <div class="project-excerpt">
                                            <?php
                                            $clean_excerpt = has_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_the_content()), 18, '...');
                                            echo esc_html($clean_excerpt);
                                            ?>
                                        </div>

                                        <div class="project-specs">

                                            <div class="spec-item">
                                                <span class="spec-label"><?php esc_html_e('Role', 'abc-tech'); ?></span>
                                                <span class="spec-value token-keyword"><?php echo esc_html($project_role); ?></span>
                                            </div>

                                            <div class="spec-item">
                                                <span class="spec-label"><?php esc_html_e('Highlights', 'abc-tech'); ?></span>
                                                <?php
                                                // Transforma a string em um array limpo e codifica em JSON para o JavaScript ler
                                                $highlights_array = array_map('trim', explode(',', $highlights_raw));
                                                // ENT_QUOTES blinda contra XSS em atributos de dados HTML
                                                $highlights_json = htmlspecialchars(json_encode($highlights_array), ENT_QUOTES, 'UTF-8');
                                                ?>
                                                <span class="spec-value type-wrap token-string">
                                                    <span class="typing-text" data-words="<?php echo esc_attr($highlights_json); ?>"></span><span class="cursor">|</span>
                                                </span>
                                            </div>

                                        </div>

                                        <div class="project-footer">
                                            <?php if ($tech_stack) : ?>
                                                <div class="tech-stack">
                                                    <?php
                                                    $techs = explode(',', $tech_stack);
                                                    $techs = array_slice($techs, 0, 3);
                                                    foreach ($techs as $tech) {
                                                        echo '<span class="tech-pill">' . esc_html(trim($tech)) . '</span>';
                                                    }
                                                    ?>
                                                </div>
                                            <?php endif; ?>

                                            <span class="btn-view-case">
                                                <?php esc_html_e('Ver Case', 'abc-tech'); ?>
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                                    <polyline points="12 5 19 12 12 19"></polyline>
                                                </svg>
                                            </span>
                                        </div>

                                    </div>
                                </div>
                            </a>
                        </article>

                    <?php
                        $count++;
                    endwhile;
                    wp_reset_postdata();
                else : ?>
                    <p class="text-white"><?php esc_html_e('Nenhum projeto encontrado.', 'abc-tech'); ?></p>
                <?php endif; ?>

            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>