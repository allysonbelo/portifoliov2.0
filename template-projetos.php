<?php

/**
 * Template Name: Portfólio de Projetos
 * Description: Layout em Lista Horizontal com opção de alternância para Grid 4 colunas.
 */

get_header(); ?>

<main id="primary" class="site-main">
    <section class="projects-hero reveal-element">
        <div class="container">
            <h1 class="page-title">
                <?php esc_html_e('Portfólio de', 'abc-tech'); ?>
                <span class="text-blue"><?php esc_html_e('Projetos', 'abc-tech'); ?></span>
            </h1>
            <p class="page-description">
                <?php esc_html_e('Uma seleção de projetos de alta performance, focados em arquitetura WordPress, otimização técnica de SEO e interfaces conversivas.', 'abc-tech'); ?>
            </p>
        </div>
    </section>

    <section class="projects-showcase">
        <div class="container">

            <div class="projects-controls reveal-element">
                <span class="view-label"><?php esc_html_e('Visualização:', 'abc-tech'); ?></span>
                <div class="view-toggle-group">
                    <button id="view-list" class="view-btn is-active" aria-label="Ver em Lista">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                    </button>
                    <button id="view-grid" class="view-btn" aria-label="Ver em Grid">
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
                        // Custom Fields
                        $is_live = get_field('project_is_live');
                        $tech_stack = get_field('project_tech_stack');
                        $project_role = get_field('project_role') ?: 'Custom Theme Developer'; // Default fallback
                        $project_metric = get_field('project_metric') ?: 'PageSpeed 100'; // Default fallback

                        $delay = ($count > 0) ? 'style="transition-delay: 0.' . ($count % 4) . 's;"' : '';
                ?>

                        <article class="project-card reveal-element" <?php echo $delay; ?>>
                            <a href="<?php the_permalink(); ?>" class="project-link">

                                <div class="project-image-wrapper">
                                    <!-- Header do Terminal/Browser -->
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
                                                <span class="live-indicator"><span class="dot"></span>Live</span>
                                            <?php endif; ?>
                                            <span class="project-year"><?php echo get_the_date('Y'); ?></span>
                                        </div>

                                        <h2 class="project-title"><?php the_title(); ?></h2>

                                        <div class="project-excerpt">
                                            <?php
                                            $clean_excerpt = has_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_the_content()), 18, '...');
                                            echo esc_html($clean_excerpt);
                                            ?>
                                        </div>

                                        <!-- Novo Bloco de Especificações Técnicas -->
                                        <div class="project-specs">

                                            <div class="spec-item">
                                                <span class="spec-label">Role</span>
                                                <span class="spec-value token-keyword">Custom Theme Developer</span>
                                            </div>

                                            <div class="spec-item">
                                                <span class="spec-label">Highlights</span>
                                                <?php
                                                // Pega as métricas do ACF (separadas por vírgula) ou usa o padrão
                                                $highlights_raw = get_field('project_highlights') ?: 'PageSpeed 100/100, LCP < 1.2s, SEO Técnico';

                                                // Transforma a string em um array limpo e codifica em JSON para o JavaScript ler
                                                $highlights_array = array_map('trim', explode(',', $highlights_raw));
                                                $highlights_json = htmlspecialchars(json_encode($highlights_array), ENT_QUOTES, 'UTF-8');
                                                ?>

                                                <span class="spec-value type-wrap token-string">
                                                    <span class="typing-text" data-words="<?php echo $highlights_json; ?>"></span><span class="cursor">|</span>
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