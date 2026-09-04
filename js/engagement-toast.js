/**
 * ABC Engagement Toast - Monolithic Frontend Engine
 * 
 * Utiliza os dados de window.abcEngagementToasts injetados pelo WordPress,
 * respeitando o idioma atual (Polylang/WPML) e exibindo apenas 1 popup por página.
 */
(function() {
    'use strict';

    if (window.abcEngagementToastInitialized) return;
    window.abcEngagementToastInitialized = true;

    const apiBase = window.location.origin;

    function initToastEngine(toastsData) {
        if (!toastsData || toastsData.length === 0) return;

        // Descrições dos eventos para o Google Analytics 4
        const ACTION_DESCRIPTIONS = {
            view: 'Exibição: Popup apresentado na tela do visitante',
            click: 'Conversão: Visitante clicou no botão de ação principal (CTA)',
            dismiss: 'Fechamento: Visitante fechou o popup (clique no X ou fora)'
        };

        function trackToastEvent(action, data) {
            if (window.abcIsAdmin) {
                console.log('ABC Toast: Evento "' + action + '" ignorado (Sessão de Admin)');
                return;
            }

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

        // 1. Processa fechamentos globais (dismissed) de 24 horas
        let dismissedToasts = JSON.parse(localStorage.getItem('abcDismissedToasts') || '[]');
        const now = Date.now();
        // Limpa expirações
        dismissedToasts = dismissedToasts.filter(function(d) { return d.expires > now; });
        localStorage.setItem('abcDismissedToasts', JSON.stringify(dismissedToasts));
        const dismissedIds = dismissedToasts.map(function(d) { return d.id; });

        // Remove os permanentemente/temporariamente fechados da pool
        toastsData = toastsData.filter(function(t) { return !dismissedIds.includes(t.id); });

        if (toastsData.length === 0) return; // Se todos foram fechados, não exibe nada

        // 2. Filtra popups já visualizados (round-robin)
        const shownToasts = JSON.parse(localStorage.getItem('abcShownToasts') || '[]');
        let availableToasts = toastsData.filter(function(t) { return !shownToasts.includes(t.id); });

        // Se todos os disponíveis já foram vistos, reseta o round-robin (mas ainda exclui os fechados)
        if (availableToasts.length === 0) {
            availableToasts = toastsData;
            localStorage.setItem('abcShownToasts', '[]');
        }

        // EXIBE APENAS 1 ÚNICO TOAST POR VEZ
        const activeToast = availableToasts[0];
        if (!activeToast) return;

        // Função utilitária para registrar fechamento
        function markAsDismissed(toastId) {
            let currentDismissed = JSON.parse(localStorage.getItem('abcDismissedToasts') || '[]');
            if (!currentDismissed.some(function(d) { return d.id === toastId; })) {
                currentDismissed.push({ id: toastId, expires: Date.now() + 24 * 60 * 60 * 1000 }); // 24 horas
                localStorage.setItem('abcDismissedToasts', JSON.stringify(currentDismissed));
            }
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
                        markAsDismissed(toastData.id);
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
                markAsDismissed(data.id);
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
                    markAsDismissed(data.id); // Se converteu, não mostra mais

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

    // Inicialização segura
    function start() {
        const toasts = window.abcEngagementToasts || [];
        if (toasts.length > 0) {
            initToastEngine(toasts);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
