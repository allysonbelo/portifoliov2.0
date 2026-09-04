/**
 * ABC Engagement Toast - Headless Universal Loader & Runner
 * 
 * Totalmente autônomo: busca os toasts na REST API, injeta o CSS, executa
 * todos os gatilhos (delay, scroll, exit-intent) e rastreia eventos no GA4 e GTM.
 */
(function() {
    'use strict';

    // Evita inicialização duplicada
    if (window.abcHeadlessToastLoaded) return;
    window.abcHeadlessToastLoaded = true;

    // 1. Detecta o elemento <script>
    let scriptEl = document.currentScript;
    if (!scriptEl) {
        const scripts = document.getElementsByTagName('script');
        for (let i = 0; i < scripts.length; i++) {
            if (scripts[i].src && (scripts[i].src.indexOf('headless-loader.js') !== -1 || scripts[i].hasAttribute('data-api'))) {
                scriptEl = scripts[i];
                break;
            }
        }
    }

    // 2. Extrai as URLs da API e dos assets dinamicamente
    let apiBase = scriptEl ? scriptEl.getAttribute('data-api') : '';
    let pluginBase = scriptEl ? scriptEl.getAttribute('data-plugin') : '';
    const src = (scriptEl && scriptEl.src) ? scriptEl.src : '';

    if (src) {
        if (!pluginBase && src.indexOf('/assets/js/') !== -1) {
            pluginBase = src.substring(0, src.indexOf('/assets/js/'));
        }
        if (!apiBase) {
            if (src.indexOf('/wp-content/') !== -1) {
                apiBase = src.substring(0, src.indexOf('/wp-content/'));
            } else {
                try {
                    apiBase = new URL(src).origin;
                } catch (e) {}
            }
        }
    }

    // Fallback de segurança se tudo falhar
    if (!apiBase) {
        apiBase = 'https://wordpress-bo.roteirodeviagem.org';
    }
    if (!pluginBase) {
        pluginBase = apiBase + '/wp-content/plugins/abc-toast';
    }

    // 3. Injeta o CSS oficial no <head> com controle de versão/cache
    const cssHref = pluginBase + '/assets/css/engagement-toast.css?v=1.4.1';
    if (!document.querySelector('link[href*="engagement-toast.css"]')) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = cssHref;
        (document.head || document.documentElement).appendChild(link);
    }

    // 4. Busca os Toasts ativos na REST API do WordPress (verificando a licença)
    const apiUrl = apiBase.replace(/\/$/, '') + '/wp-json/abc-toast/v1/toasts';

    fetch(apiUrl, {
        method: 'GET',
        headers: { 'Accept': 'application/json' }
    })
    .then(function(response) {
        if (!response.ok) {
            if (response.status === 403) {
                console.warn('[ABC Toast] Toasts bloqueados: Licença inativa no painel WordPress.');
            }
            return null;
        }
        return response.json();
    })
    .then(function(data) {
        if (!data || !data.success || !Array.isArray(data.toasts) || data.toasts.length === 0) {
            return;
        }

        console.log('[ABC Toast] Toasts carregados com sucesso:', data.toasts.length);
        window.abcEngagementToasts = data.toasts;

        // Executa o motor dos Toasts diretamente
        initToastEngine(data.toasts);
    })
    .catch(function(err) {
        console.warn('[ABC Toast] Erro na requisição da API:', err);
    });

    /**
     * Motor nativo de renderização, gatilhos e analytics dos Toasts
     */
    function initToastEngine(toastsData) {
        if (!toastsData || toastsData.length === 0) return;

        // Descrições humanas para os eventos de Analytics (para ninguém ficar perdido no GA4)
        const ACTION_DESCRIPTIONS = {
            view: 'Exibição: Popup apresentado na tela do visitante',
            click: 'Conversão: Visitante clicou no botão de ação principal (CTA)',
            dismiss: 'Fechamento: Visitante fechou o popup (clique no X ou fora)'
        };

        /**
         * Dispara eventos para o Google Analytics 4, Google Tag Manager e contador interno do WP
         */
        function trackToastEvent(action, data) {
            const desc = ACTION_DESCRIPTIONS[action] || action;
            const eventName = 'abc_toast_' + action;

            // 1. Google Analytics 4 nativo (gtag)
            if (typeof window.gtag === 'function') {
                window.gtag('event', eventName, {
                    event_category: 'Engajamento Popup',
                    event_label: data.title || ('Toast #' + data.id),
                    toast_id: data.id,
                    toast_title: data.title || '',
                    toast_action: action,
                    toast_action_description: desc,
                    toast_trigger: data.trigger || 'load',
                    toast_target_url: data.linkUrl || ''
                });
            }

            // 2. Google Tag Manager / DataLayer
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                event: eventName,
                toast_id: data.id,
                toast_title: data.title || '',
                toast_action: action,
                toast_action_description: desc,
                toast_trigger: data.trigger || 'load',
                toast_target_url: data.linkUrl || ''
            });

            // 3. Salva contador interno no WordPress (view, click e dismiss com página e dispositivo)
            if (apiBase) {
                const pagePath = window.location.pathname || '/';
                const deviceType = (window.innerWidth <= 768) ? 'mobile' : 'desktop';
                const trackUrl = apiBase.replace(/\/$/, '') + '/wp-json/abc-toast/v1/track';
                const payload = JSON.stringify({
                    toast_id: data.id,
                    action: action,
                    page: pagePath,
                    device: deviceType
                });
                let sent = false;
                if (navigator.sendBeacon) {
                    try {
                        const blob = new Blob([payload], { type: 'text/plain;charset=UTF-8' });
                        sent = navigator.sendBeacon(trackUrl, blob);
                    } catch (e) {
                        sent = false;
                    }
                }
                if (!sent) {
                    fetch(trackUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: payload,
                        keepalive: true,
                        mode: 'cors'
                    }).catch(function() {});
                }
            }
        }

        // Evita exibir popups que o usuário já fechou recentemente
        const shownToasts = JSON.parse(localStorage.getItem('abcShownToasts') || '[]');
        let availableToasts = toastsData.filter(function(t) { return !shownToasts.includes(t.id); });

        if (availableToasts.length === 0) {
            availableToasts = toastsData;
            localStorage.setItem('abcShownToasts', '[]');
        }

        function getToastContainer(position) {
            const posClass = 'pos-' + (position || 'bottom-right');
            let container = document.querySelector('.abc-toast-container.' + posClass);
            if (!container) {
                container = document.createElement('div');
                container.className = 'abc-toast-container ' + posClass;
                (document.body || document.documentElement).appendChild(container);
            }
            return container;
        }

        function showOverlay(color, blur, toastData) {
            let overlay = document.querySelector('.abc-toast-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.className = 'abc-toast-overlay';
                (document.body || document.documentElement).appendChild(overlay);

                overlay.addEventListener('click', function() {
                    document.querySelectorAll('.abc-engagement-toast.is-visible').forEach(function(toast) {
                        toast.classList.remove('is-visible');
                        toast.dataset.dismissed = 'true';
                        setTimeout(function() { toast.remove(); }, 300);
                    });
                    if (toastData) {
                        trackToastEvent('dismiss', toastData);
                    }
                    hideOverlay();
                });
            }

            overlay.style.backgroundColor = color || 'rgba(0, 0, 0, 0.65)';
            if (blur !== false) {
                overlay.style.backdropFilter = 'blur(6px)';
                overlay.style.webkitBackdropFilter = 'blur(6px)';
            } else {
                overlay.style.backdropFilter = 'none';
                overlay.style.webkitBackdropFilter = 'none';
            }

            requestAnimationFrame(function() {
                overlay.classList.add('is-visible');
            });
            return overlay;
        }

        function hideOverlay() {
            const overlay = document.querySelector('.abc-toast-overlay');
            if (overlay) {
                overlay.classList.remove('is-visible');
                setTimeout(function() {
                    if (!document.querySelector('.abc-toast-overlay.is-visible')) {
                        overlay.remove();
                    }
                }, 400);
            }
        }

        // Exibe apenas 1 popup por visualização de página (evita que múltiplos toasts apareçam empilhados)
        const activeToast = availableToasts[0];
        if (activeToast) {
            function createToastElement(data) {
                const toast = document.createElement('div');
                toast.className = 'abc-engagement-toast pos-' + (data.position || 'bottom-right');
                toast.style.backgroundColor = data.bgColor || '#ffffff';
                toast.style.color = data.textColor || '#212121';

                if (data.borderWidth !== undefined) {
                    toast.style.border = data.borderWidth + 'px solid ' + (data.borderColor || 'rgba(0,0,0,0.1)');
                }
                if (data.borderRadius !== undefined) {
                    toast.style.borderRadius = data.borderRadius + 'px';
                }

                // Botão Fechar
                const header = document.createElement('div');
                header.className = 'abc-engagement-toast-header';
                const closeBtn = document.createElement('button');
                closeBtn.className = 'abc-engagement-toast-close';
                closeBtn.type = 'button';
                closeBtn.setAttribute('aria-label', 'Fechar');
                closeBtn.style.color = data.closeColor || '#212121';
                closeBtn.style.backgroundColor = data.closeBgColor || 'transparent';
                closeBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';

                closeBtn.addEventListener('click', function() {
                    trackToastEvent('dismiss', data);
                    toast.classList.remove('is-visible');
                    toast.dataset.dismissed = 'true';
                    setTimeout(function() {
                        toast.remove();
                        if (document.querySelectorAll('.abc-engagement-toast.is-visible').length === 0) {
                            hideOverlay();
                        }
                    }, 300);
                });
                header.appendChild(closeBtn);
                toast.appendChild(header);

                // Wrapper de Conteúdo
                const contentWrapper = document.createElement('div');
                contentWrapper.className = 'abc-engagement-toast-content-wrapper';

                // Ícone / Imagem
                const iconContainer = document.createElement('div');
                iconContainer.className = 'abc-engagement-toast-icon';
                iconContainer.style.backgroundColor = data.iconBgColor || 'rgba(249, 160, 69, 0.1)';

                if (data.emoji) {
                    iconContainer.textContent = data.emoji;
                } else if (data.imageUrl) {
                    const img = document.createElement('img');
                    img.src = data.imageUrl;
                    img.alt = data.title || 'Ícone';
                    iconContainer.appendChild(img);
                } else {
                    iconContainer.textContent = '👋';
                }
                contentWrapper.appendChild(iconContainer);

                // Textos
                const textContainer = document.createElement('div');
                textContainer.className = 'abc-engagement-toast-text';

                if (data.title) {
                    const title = document.createElement('h4');
                    title.className = 'abc-engagement-toast-title';
                    title.textContent = data.title;
                    textContainer.appendChild(title);
                }

                if (data.content) {
                    const desc = document.createElement('p');
                    desc.className = 'abc-engagement-toast-desc';
                    desc.innerHTML = data.content;
                    textContainer.appendChild(desc);
                }

                if (data.linkUrl && data.linkText) {
                    const link = document.createElement('a');
                    link.className = 'abc-engagement-toast-link btn-align-' + (data.btnAlignment || 'left');
                    link.href = data.linkUrl;
                    link.target = data.linkTarget || '_self';
                    link.textContent = data.linkText || 'Saiba mais';
                    link.style.backgroundColor = data.btnBgColor || '#f9a045';
                    link.style.color = (data.btnTextColor || '#ffffff') + ' !important';

                    link.addEventListener('click', function() {
                        trackToastEvent('click', data);

                        let currentShown = JSON.parse(localStorage.getItem('abcShownToasts') || '[]');
                        if (!currentShown.includes(data.id)) {
                            currentShown.push(data.id);
                            localStorage.setItem('abcShownToasts', JSON.stringify(currentShown));
                        }
                    });
                    textContainer.appendChild(link);
                }

                contentWrapper.appendChild(textContainer);
                toast.appendChild(contentWrapper);
                return toast;
            }

            function triggerToast(data) {
                function showToast() {
                    if (document.querySelector('.abc-engagement-toast[data-id="' + data.id + '"]')) return;

                    const container = getToastContainer(data.position || 'bottom-right');
                    const toastElement = createToastElement(data);
                    toastElement.dataset.id = data.id;

                    if (data.exitOverlay && data.trigger === 'exit') {
                        showOverlay(data.exitOverlayColor, data.exitOverlayBlur, data);
                    }

                    container.appendChild(toastElement);

                    // Rastreia a Exibição (View)
                    trackToastEvent('view', data);

                    let currentShown = JSON.parse(localStorage.getItem('abcShownToasts') || '[]');
                    if (!currentShown.includes(data.id)) {
                        currentShown.push(data.id);
                        localStorage.setItem('abcShownToasts', JSON.stringify(currentShown));
                    }

                    setTimeout(function() {
                        toastElement.classList.add('is-visible');
                    }, 50);
                }

                switch (data.trigger) {
                    case 'delay':
                        const delayMs = (parseInt(data.triggerDelay, 10) || 5) * 1000;
                        setTimeout(showToast, delayMs);
                        break;

                    case 'scroll':
                        const scrollPct = parseInt(data.triggerScroll, 10) || 50;
                        const onScroll = function() {
                            const scrollTop = window.scrollY || document.documentElement.scrollTop;
                            const docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                            const scrolled = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;

                            if (scrolled >= scrollPct) {
                                showToast();
                                window.removeEventListener('scroll', onScroll);
                            }
                        };
                        window.addEventListener('scroll', onScroll);
                        onScroll();
                        break;

                    case 'exit':
                        const onMouseLeave = function(e) {
                            if (e.clientY <= 0) {
                                showToast();
                                document.removeEventListener('mouseleave', onMouseLeave);
                            }
                        };
                        document.addEventListener('mouseleave', onMouseLeave);
                        break;

                    case 'load':
                    default:
                        setTimeout(showToast, 500);
                        break;
                }
            }

            triggerToast(activeToast);
        }
    }
})();
