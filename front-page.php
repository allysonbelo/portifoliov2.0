<?php
/**
 * O template da página inicial.
 */

get_header();

// 1. Hero Section
get_template_part( 'template-parts/home/hero' );

// 2. Tech Stack & Expertise
get_template_part( 'template-parts/home/stack' );

// 3. Experiência e Trajetória
get_template_part( 'template-parts/home/experience' );

// 4. Call to Action (Contato)
get_template_part( 'template-parts/home/cta' );

get_footer(); 
?>