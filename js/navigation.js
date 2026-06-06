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

    menuToggle.addEventListener('click', () => {
        // Alterna a classe de visibilidade do menu
        mainNavigation.classList.toggle('is-open');
        
        // Alterna a classe para a animação do botão (Hamburger para X)
        menuToggle.classList.toggle('is-active');

        // Atualiza os atributos de acessibilidade (Screen Readers)
        const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
        menuToggle.setAttribute('aria-expanded', !isExpanded);
    });
});