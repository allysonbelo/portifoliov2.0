<?php

/**
 * The template for displaying all single projects
 */

get_header(); ?>

<div class="project-single">
    <?php while (have_posts()) : the_post();
        // Lembre-se de criar estes dois campos no ACF se quiser que os botões apareçam!
        $live_url = get_field('project_live_url');
        $code_url = get_field('project_code_url');

        $project_role = get_field('project_role') ?: 'Performance Architecture';
    ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

            <!-- =====================================
             1. HERO SECTION (50/50 Layout Correto)
             ===================================== -->
            <section class="project-hero">
                <div class="container hero-grid">

                    <div class="project-content-left reveal-element">
                        <span class="label-technical project-badge">
                            <span class="dot-indicator"></span>
                            <?php echo esc_html($project_role); ?>
                        </span>

                        <h1 class="project-title"><?php the_title(); ?></h1>

                        <div class="project-description">
                            <?php
                            if (has_excerpt()) {
                                the_excerpt();
                            } else {
                                echo '<p>' . wp_trim_words(wp_strip_all_tags(get_the_content()), 30, '...') . '</p>';
                            }
                            ?>
                        </div>

                        <?php
                        // Resgata os highlights do banco de dados
                        $highlights_raw = get_field('project_highlights');

                        if ($highlights_raw):
                            // Transforma a string separada por vírgulas num array
                            $highlights_array = explode(',', $highlights_raw);
                        ?>
                            <div class="project-highlights-single" style="margin-top: 1.5rem; margin-bottom: 2rem;">
                                <h4 style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: var(--color-gray, #888); margin-bottom: 0.8rem;">
                                    Destaques da Arquitetura:
                                </h4>
                                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; gap: 8px;">
                                    <?php foreach ($highlights_array as $highlight): ?>
                                        <li style="background: linear-gradient(135deg, #00ffb233 0%, rgba(0, 255, 178, 0.02) 100%); color: var(--color-seo-green); padding: 4px 12px; border-radius: 4px; font-size: 0.85rem; border: 1px solid #00ffb291; text-shadow: 0 0 8px rgba(0, 255, 178, 0.2);">
                                            <?php echo esc_html(trim($highlight)); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="project-actions">
                            <?php if ($live_url): ?>
                                <a href="<?php echo esc_url($live_url); ?>" class="btn-primary" target="_blank">
                                    Live Demo
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                        <polyline points="15 3 21 3 21 9"></polyline>
                                        <line x1="10" y1="14" x2="21" y2="3"></line>
                                    </svg>
                                </a>
                            <?php endif; ?>

                            <?php if ($code_url): ?>
                                <a href="<?php echo esc_url($code_url); ?>" class="btn-secondary" target="_blank">
                                    View Code <span>&lt;&gt;</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="project-visual-right reveal-element" style="transition-delay: 0.2s;">
                        <div class="browser-frame">
                            <div class="window-header-mini">
                                <div class="mac-dots">
                                    <span class="dot dot-red"></span>
                                    <span class="dot dot-yellow"></span>
                                    <span class="dot dot-green"></span>
                                </div>
                            </div>
                            <div class="browser-content">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('large', array('class' => 'featured-project-img')); ?>
                                <?php else: ?>
                                    <img src="<?php echo esc_url(get_template_directory_uri()); ?>/images/placeholder.jpg" alt="Mockup do Projeto" class="featured-project-img">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            <!-- =====================================
             2. EDITORIAL BODY (Onde entra o texto completo)
             ===================================== -->
            <section class="project-editorial">
                <div class="container">
                    <div class="editorial-content">
                        <?php the_content(); ?>
                    </div>
                </div>
            </section>

            <!-- =====================================
             3. TECH STACK SECTION (Corrigido para o seu ACF)
             ===================================== -->
            <section class="project-tech-stack">
                <div class="container">
                    <div class="section-heading-wrapper reveal-element">
                        <h3 class="section-title">Tech Stack</h3>
                    </div>

                    <div class="tech-grid">
                        <?php
                        // Puxa a string separada por vírgulas do seu ACF atual
                        $tech_string = get_field('project_tech_stack');

                        if ($tech_string):
                            // Transforma a string numa array
                            $stacks = explode(',', $tech_string);
                            $delay = 0;

                            foreach ($stacks as $stack):
                                $stack_name = trim($stack);
                                if (empty($stack_name)) continue;
                        ?>
                                <div class="stack-card reveal-element" style="transition-delay: <?php echo $delay; ?>s;">
                                    <div class="stack-icon">
                                        <span class="text-blue" style="font-size: 24px;">&lt;/&gt;</span>
                                    </div>
                                    <h4 class="stack-name"><?php echo esc_html($stack_name); ?></h4>
                                </div>
                        <?php
                                $delay += 0.1;
                            endforeach;
                        endif; ?>
                    </div>
                </div>
            </section>

        </article>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>