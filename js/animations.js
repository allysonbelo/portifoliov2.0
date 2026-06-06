/**
 * Animações de Scroll (Intersection Observer)
 * Foco em alta performance sem depender do jQuery.
 */

document.addEventListener('DOMContentLoaded', () => {
    // Configura o observador
    const observerOptions = {
        root: null,
        rootMargin: '0px 0px -10% 0px', // Aciona quando o elemento entra 10% da tela
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Adiciona a classe que dispara a animação CSS
                entry.target.classList.add('is-visible');
                
                // Remove o observador do elemento após a primeira animação (para não repetir)
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Seleciona todos os elementos com a classe de revelação e os observa
    const revealElements = document.querySelectorAll('.reveal-element');
    revealElements.forEach(element => {
        observer.observe(element);
    });
});