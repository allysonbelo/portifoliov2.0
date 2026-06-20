<?php

/**
 * Template Name: Página de Contato
 * Padrões aplicados: Late Escaping (Security), i18n (Tradução) e Integração ACF.
 */
get_header();

// 1. Textos da Coluna Esquerda
$contact_badge = get_field('contact_badge') ?: __('INICIAR PROTOCOLO SEGURO', 'abc-tech');
$contact_title = get_field('contact_title') ?: __('Vamos construir algo extraordinário?', 'abc-tech');
$contact_desc  = get_field('contact_description') ?: __('Pronto para arquitetar soluções digitais de alta performance? Envie suas especificações abaixo e aguarde uma resposta precisa em até 24 horas.', 'abc-tech');
$contact_cf7   = get_field('contact_cf7_shortcode') ?: '[contact-form-7 id="0f6f24c" title="Formulário de contato PT"]';

// 2. Título do Card da Direita
$card_title    = get_field('contact_card_title') ?: __('Data Links', 'abc-tech');

// 3. Email
$email_label   = get_field('contact_email_label') ?: __('CORREIO ELETRÔNICO', 'abc-tech');
$email_address = get_field('contact_email_address') ?: 'contato@allysonbelo.com';

// 4. WhatsApp / Telefone
$phone_label   = get_field('contact_phone_label') ?: __('COMUNICAÇÃO CRIPTOGRAFADA', 'abc-tech');
$phone_text    = get_field('contact_phone_text') ?: '+55 (XX) XXXXX-XXXX';
$phone_url     = get_field('contact_phone_url') ?: 'tel:+5500000000000';

// 5. LinkedIn
$linkedin_label = get_field('contact_linkedin_label') ?: __('REDE PROFISSIONAL', 'abc-tech');
$linkedin_text  = get_field('contact_linkedin_text') ?: 'linkedin.com/in/allysonbelo';
$linkedin_url   = get_field('contact_linkedin_url') ?: 'https://www.linkedin.com/in/allysoncavalcante/';

// 6. GitHub
$github_label   = get_field('contact_github_label') ?: __('REPOSITÓRIOS DE CÓDIGO', 'abc-tech');
$github_text    = get_field('contact_github_text') ?: 'github.com/allysonbelo';
$github_url     = get_field('contact_github_url') ?: 'https://github.com/allysonbelo';
?>

<main id="primary" class="site-main page-contact">

    <section class="contact-section editable-section">

        <?php
        // Ícone de Edição do Frontend focado neste grupo
        if (function_exists('abc_tech_edit_section_icon')) {
            abc_tech_edit_section_icon('group_contact_page');
        }
        ?>

        <div class="container contact-grid">

            <div class="contact-form-col reveal-element">
                <div class="contact-header">
                    <span class="label-technical secure-protocol">
                        <?php echo esc_html($contact_badge); ?>
                    </span>
                    <h1 class="contact-title"><?php echo esc_html($contact_title); ?></h1>
                    <p class="contact-description">
                        <?php echo esc_html($contact_desc); ?>
                    </p>
                </div>

                <div class="cf7-terminal-wrapper">
                    <?php
                    // Renderiza o Shortcode. Como processa HTML interno, não usamos esc_html aqui.
                    echo do_shortcode($contact_cf7);
                    ?>
                </div>
            </div>

            <div class="contact-info-col reveal-element" style="transition-delay: 0.2s;">
                <div class="data-links-card">
                    <div class="card-dots">
                        <span class="dot dot-red"></span>
                        <span class="dot dot-yellow"></span>
                        <span class="dot dot-green"></span>
                    </div>

                    <h2 class="card-title"><?php echo esc_html($card_title); ?></h2>

                    <div class="data-list">
                        <div class="data-item">
                            <div class="data-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </div>
                            <div class="data-text">
                                <span class="data-label"><?php echo esc_html($email_label); ?></span>
                                <a href="mailto:<?php echo esc_attr(antispambot($email_address)); ?>" class="data-value">
                                    <?php echo esc_html(antispambot($email_address)); ?>
                                </a>
                            </div>
                        </div>

                        <div class="data-item">
                            <div class="data-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                                </svg>
                            </div>
                            <div class="data-text">
                                <span class="data-label"><?php echo esc_html($phone_label); ?></span>
                                <a href="<?php echo esc_url($phone_url); ?>" target="_blank" rel="noopener noreferrer" class="data-value">
                                    <?php echo esc_html($phone_text); ?>
                                </a>
                            </div>
                        </div>

                        <div class="data-item">
                            <div class="data-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                </svg>
                            </div>
                            <div class="data-text">
                                <span class="data-label"><?php echo esc_html($linkedin_label); ?></span>
                                <a href="<?php echo esc_url($linkedin_url); ?>" target="_blank" rel="noopener noreferrer" class="data-value"><?php echo esc_html($linkedin_text); ?></a>
                            </div>
                        </div>

                        <div class="data-item">
                            <div class="data-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="16 18 22 12 16 6"></polyline>
                                    <polyline points="8 6 2 12 8 18"></polyline>
                                </svg>
                            </div>
                            <div class="data-text">
                                <span class="data-label"><?php echo esc_html($github_label); ?></span>
                                <a href="<?php echo esc_url($github_url); ?>" target="_blank" rel="noopener noreferrer" class="data-value"><?php echo esc_html($github_text); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</main>

<?php get_footer(); ?>