/**
 * Script de Navegação Principal
 * Gerencia o Drawer mobile (deslizando da direita para a esquerda) com backdrop escuro.
 * Totalmente otimizado para dispositivos móveis reais (touch, iOS Safari, Android Chrome/Brave).
 */

(function () {
    'use strict';

    function initNavigation() {
        const menuToggle = document.getElementById('menu-toggle');
        const mainNavigation = document.getElementById('site-navigation');
        const drawerClose = document.getElementById('drawer-close');
        const backdrop = document.getElementById('menu-backdrop');

        // Previne erros caso os elementos fundamentais não existam na página
        if (!menuToggle || !mainNavigation) {
            return;
        }

        // Previne duplicação de listeners se o script for executado novamente
        if (menuToggle.dataset.navInitialized === 'true') {
            return;
        }
        menuToggle.dataset.navInitialized = 'true';

        // Lógica de Relocação do DOM (Corrige bug de fixed dentro de sticky no Safari/Android Chrome)
        const headerInner = document.querySelector('.header-inner');
        function handleDOMRelocation() {
            if (window.innerWidth < 1024) {
                // No mobile, move a navegação para o fim do body (logo antes do fechamento)
                if (mainNavigation.parentNode !== document.body) {
                    document.body.appendChild(mainNavigation);
                }
            } else {
                // No desktop, devolve para dentro do header-inner
                if (mainNavigation.parentNode !== headerInner && headerInner) {
                    headerInner.appendChild(mainNavigation);
                }
            }
        }
        
        // Executa imediatamente e no redimensionamento da tela
        handleDOMRelocation();
        window.addEventListener('resize', handleDOMRelocation);

        let isOpen = false;
        let lastActionTime = 0;

        function openMenu() {
            isOpen = true;
            mainNavigation.classList.add('is-open');
            menuToggle.classList.add('is-active');
            menuToggle.setAttribute('aria-expanded', 'true');
            if (backdrop) {
                backdrop.classList.add('is-active');
            }
            document.body.classList.add('menu-open');
        }

        function closeMenu() {
            isOpen = false;
            mainNavigation.classList.remove('is-open');
            menuToggle.classList.remove('is-active');
            menuToggle.setAttribute('aria-expanded', 'false');
            if (backdrop) {
                backdrop.classList.remove('is-active');
            }
            document.body.classList.remove('menu-open');
        }

        function toggleMenu() {
            if (isOpen) {
                closeMenu();
            } else {
                openMenu();
            }
        }

        // Handler unificado com debounce de 350ms para impedir duplo disparo entre touchstart e click sintetizado
        function onToggleEvent(e) {
            const now = Date.now();
            if (now - lastActionTime < 350) {
                e.preventDefault();
                e.stopPropagation();
                return;
            }
            lastActionTime = now;
            e.preventDefault();
            e.stopPropagation();
            toggleMenu();
        }

        function onCloseEvent(e) {
            const now = Date.now();
            if (now - lastActionTime < 350) {
                e.preventDefault();
                e.stopPropagation();
                return;
            }
            lastActionTime = now;
            e.preventDefault();
            e.stopPropagation();
            closeMenu();
        }

        // 1. Botão do menu hamburguer / X no header
        menuToggle.addEventListener('touchstart', onToggleEvent, { passive: false });
        menuToggle.addEventListener('click', onToggleEvent);

        // 2. Botão dedicado de fechar dentro do drawer (topo à direita)
        if (drawerClose) {
            drawerClose.addEventListener('touchstart', onCloseEvent, { passive: false });
            drawerClose.addEventListener('click', onCloseEvent);
        }

        // 3. Fundo escurecido (backdrop): tocar ou clicar fecha o drawer
        if (backdrop) {
            backdrop.addEventListener('touchstart', onCloseEvent, { passive: false });
            backdrop.addEventListener('click', onCloseEvent);
        }

        // 4. Fechar o drawer ao tocar em qualquer link de navegação interno
        const navLinks = mainNavigation.querySelectorAll('a');
        navLinks.forEach(function (link) {
            link.addEventListener('click', function (e) {
                // Não fechar se for o link principal do seletor de idiomas
                if (link.closest('.pll-parent-menu-item') && !link.closest('.sub-menu')) {
                    return; 
                }
                
                if (window.innerWidth < 1024) {
                    closeMenu();
                }
            });
        });

        // 5. Acessibilidade: fechar menu com a tecla Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isOpen) {
                closeMenu();
                menuToggle.focus();
            }
        });

        // 6. Ao redimensionar para tela de desktop (>= 1024px), fecha o drawer
        window.addEventListener(
            'resize',
            function () {
                if (window.innerWidth >= 1024 && isOpen) {
                    closeMenu();
                }
            },
            { passive: true }
        );
    }

    // Inicialização robusta compatível com scripts defer / cache
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNavigation);
    } else {
        initNavigation();
    }
})();