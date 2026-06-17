<?php
/**
 * The template for displaying all single projects
 */

get_header(); ?>

<main id="primary" class="site-main project-single">
    <?php while ( have_posts() ) : the_post(); 
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
                        // AQUI ESTÁ A MÁGICA: Puxa só o resumo (excerpt) para não quebrar o layout
                        if ( has_excerpt() ) {
                            the_excerpt();
                        } else {
                            // Se não tiver resumo, pega apenas as primeiras 30 palavras do texto
                            echo '<p>' . wp_trim_words( wp_strip_all_tags( get_the_content() ), 30, '...' ) . '</p>';
                        }
                        ?>
                    </div>

                    <div class="project-actions">
                        <?php if($live_url): ?>
                            <a href="<?php echo esc_url($live_url); ?>" class="btn-primary" target="_blank">
                                Live Demo 
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                            </a>
                        <?php endif; ?>
                        
                        <?php if($code_url): ?>
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
                            <?php if ( has_post_thumbnail() ) : ?>
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
                    <?php 
                    // Agora sim, o texto completo entra aqui, numa coluna de leitura agradável
                    the_content(); 
                    ?>
                </div>
            </div>
        </section>

        <!-- =====================================
             3. TECH STACK SECTION
             ===================================== -->
        <section class="project-tech-stack">
            <div class="container">
                <div class="section-heading-wrapper reveal-element">
                    <h3 class="section-title">Tech Stack</h3>
                </div>
                
                <div class="tech-grid">
                    <?php 
                    $stacks = get_field('tech_stack_items'); 
                    if( $stacks ): 
                        $delay = 0;
                        foreach( $stacks as $stack ): ?>
                        <div class="stack-card reveal-element" style="transition-delay: <?php echo $delay; ?>s;">
                            <div class="stack-icon">
                                <?php if( !empty($stack['icon']) ): ?>
                                    <img src="<?php echo esc_url($stack['icon']['url']); ?>" alt="<?php echo esc_attr($stack['title']); ?>">
                                <?php endif; ?>
                            </div>
                            <h4 class="stack-name"><?php echo esc_html($stack['title']); ?></h4>
                            <p class="stack-desc"><?php echo esc_html($stack['description']); ?></p>
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
</main>

<?php get_footer(); ?>