/**
 * Script de Navegação Principal
 * Gerencia o toggle do menu mobile e as propriedades ARIA de acessibilidade.
 */

document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.getElementById('menu-toggle');
    // Correção: Pega diretamente a nav, pois ela mesma recebe a classe 'is-open' no CSS
    const mainNavigation = document.getElementById('site-navigation'); 

    // Previne erros caso os elementos não existam na página
    if (!menuToggle || !mainNavigation) {
        console.warn('Elementos do menu não encontrados no DOM.');
        return;
    }

    function toggleMenu(forceClose = false) {
        const isOpen = mainNavigation.classList.contains('is-open');

        if (forceClose || isOpen) {
            mainNavigation.classList.remove('is-open');
            menuToggle.classList.remove('is-active');
            menuToggle.setAttribute('aria-expanded', 'false');
        } else {
            mainNavigation.classList.add('is-open');
            menuToggle.classList.add('is-active');
            menuToggle.setAttribute('aria-expanded', 'true');
        }
    }

    menuToggle.addEventListener('click', () => toggleMenu());

    // Fechar menu mobile ao pressionar a tecla ESC (A11y Best Practice)
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && mainNavigation.classList.contains('is-open')) {
            toggleMenu(true);
            menuToggle.focus();
        }
    });
});