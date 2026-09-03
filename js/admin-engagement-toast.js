/**
 * Admin Script for Real-Time Toast Preview
 */
jQuery(document).ready(function($) {
    if ($('#abc-toast-admin-preview-container').length === 0) return;

    // Inject CSS for 2-column layout inside ACF
    const layoutStyles = `
        <style>
        #acf-group_abc_popup_settings > .acf-fields {
            display: grid !important;
            grid-template-columns: 60% 40%;
            grid-template-areas: "tabs preview" "content preview";
            align-items: start;
        }
        #acf-group_abc_popup_settings > .acf-fields > .acf-tab-wrap {
            grid-area: tabs;
        }
        #acf-group_abc_popup_settings > .acf-fields > .acf-field[data-name="popup_preview_html"] {
            grid-area: preview;
            position: sticky;
            top: 40px;
            padding-top: 40px; /* Alinhar com o conteúdo */
            border-top: none !important;
        }
        #acf-group_abc_popup_settings > .acf-fields > .acf-field:not([data-name="popup_preview_html"]):not(.acf-tab-wrap) {
            grid-column: 1;
        }
        @media (max-width: 900px) {
            #acf-group_abc_popup_settings > .acf-fields {
                grid-template-columns: 100%;
                grid-template-areas: "preview" "tabs" "content";
            }
            #acf-group_abc_popup_settings > .acf-fields > .acf-field[data-name="popup_preview_html"] {
                position: static;
                padding-top: 0;
            }
            #acf-group_abc_popup_settings > .acf-fields > .acf-field:not([data-name="popup_preview_html"]):not(.acf-tab-wrap) {
                grid-column: 1;
            }
        }
        </style>
    `;
    $('head').append(layoutStyles);

    function updatePreview() {
        // ACF field keys based on the field names
        // In ACF, the input name attribute is typically acf[field_key]
        // But we can also select by data-name attribute on the acf-field wrapper
        
        const getFieldVal = (name) => {
            const $field = $('.acf-field[data-name="' + name + '"]');
            if ($field.length) {
                const $input = $field.find('input[type="text"], input[type="hidden"], input[type="url"], input[type="number"], select, textarea').first();
                return $input.val();
            }
            return '';
        };

        const title = getFieldVal('popup_display_title') || $('#title').val() || 'Título de Exemplo';
        const desc = getFieldVal('popup_description') || 'A descrição do seu toast vai aparecer bem aqui. É recomendável usar de duas a três linhas curtas.';
        
        // Handling ACF Link field
        const $linkField = $('.acf-field[data-name="popup_button_link"]');
        let linkText = 'Saiba mais';
        let linkUrl = '#';
        if ($linkField.length) {
            const tempTitle = $linkField.find('.input-title').val();
            if(tempTitle) linkText = tempTitle;
            const tempUrl = $linkField.find('.input-url').val();
            if(tempUrl) linkUrl = tempUrl;
        }

        const bgColor = getFieldVal('popup_bg_color') || '#ffffff';
        const textColor = getFieldVal('popup_text_color') || '#212121';
        const emoji = getFieldVal('popup_icon_emoji');
        const iconBgColor = getFieldVal('popup_icon_bg_color') || 'rgba(249, 160, 69, 0.1)';
        const btnBgColor = getFieldVal('popup_btn_bg_color') || '#f9a045';
        const btnTextColor = getFieldVal('popup_btn_text_color') || '#ffffff';
        const btnAlignment = getFieldVal('popup_btn_alignment') || 'left';
        
        let borderWidth = getFieldVal('popup_border_width');
        borderWidth = (borderWidth !== undefined && borderWidth !== '') ? borderWidth : 1;
        const borderColor = getFieldVal('popup_border_color') || 'rgba(0,0,0,0.1)';
        let borderRadius = getFieldVal('popup_border_radius');
        borderRadius = (borderRadius !== undefined && borderRadius !== '') ? borderRadius : 12;

        // Handling ACF Image field
        const $imgField = $('.acf-field[data-name="popup_image"]');
        let imageUrl = '';
        if ($imgField.length && $imgField.find('img').length > 0 && $imgField.find('img').attr('src')) {
            imageUrl = $imgField.find('img').attr('src');
        }

        let iconHtml = '';
        if (emoji) {
            iconHtml = emoji;
        } else if (imageUrl) {
            iconHtml = `<img src="${imageUrl}" alt="Ícone" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
        } else {
            iconHtml = '👋';
        }

        let buttonHtml = '';
        if (linkUrl && linkUrl !== '#' && linkText) {
            buttonHtml = `<a href="${linkUrl}" class="abc-engagement-toast-link btn-align-${btnAlignment}" style="background-color: ${btnBgColor}; color: ${btnTextColor} !important;" onclick="event.preventDefault();">${linkText}</a>`;
        }
        
        const closeColor = getFieldVal('popup_close_color') || '#212121';
        const closeBgColor = getFieldVal('popup_close_bg_color') || 'transparent';

        // Build HTML
        const html = `
            <div class="abc-engagement-toast is-visible" style="position:relative; bottom:auto; right:auto; transform:none; opacity:1; background-color:${bgColor}; color:${textColor}; border: ${borderWidth}px solid ${borderColor}; border-radius: ${borderRadius}px; pointer-events:auto; margin:0 auto;">
                <div class="abc-engagement-toast-header">
                    <button class="abc-engagement-toast-close" type="button" aria-label="Close" style="color:${closeColor}; background-color:${closeBgColor};">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
                <div class="abc-engagement-toast-content-wrapper">
                    <div class="abc-engagement-toast-icon" style="background-color: ${iconBgColor};">${iconHtml}</div>
                    <div class="abc-engagement-toast-text">
                        <h4 class="abc-engagement-toast-title" style="color:inherit;">${title}</h4>
                        <p class="abc-engagement-toast-desc" style="color:inherit;">${desc.replace(/\n/g, '<br>')}</p>
                        ${buttonHtml}
                    </div>
                </div>
            </div>
        `;

        $('#abc-toast-admin-preview-container').html(html);
    }

    // Initial render
    setTimeout(updatePreview, 500);

    // Listen to changes on ACF inputs
    $(document).on('input change', '.acf-field input, .acf-field textarea, .acf-field select, #title', function() {
        updatePreview();
    });
});
