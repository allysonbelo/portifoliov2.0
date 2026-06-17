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

/**
 * FAQ Accordion Animation (Calculada Dinamicamente)
 */
document.addEventListener('DOMContentLoaded', () => {
    const faqQuestions = document.querySelectorAll('.rank-math-question');

    faqQuestions.forEach(question => {
        question.addEventListener('click', () => {
            const faqItem = question.closest('.rank-math-faq-item');
            const answer = faqItem.querySelector('.rank-math-answer');
            const isOpen = faqItem.classList.contains('is-open');

            // 1. Fecha todos os outros itens (para manter o visual limpo)
            document.querySelectorAll('.rank-math-faq-item').forEach(item => {
                item.classList.remove('is-open');
                const otherAnswer = item.querySelector('.rank-math-answer');
                if (otherAnswer) {
                    otherAnswer.style.maxHeight = null; // Reseta a altura inline
                }
            });

            // 2. Abre ou fecha o item clicado calculando a altura exata
            if (!isOpen) {
                faqItem.classList.add('is-open');
                // scrollHeight mede a altura exata que o conteúdo precisa
                answer.style.maxHeight = answer.scrollHeight + "px"; 
            } else {
                faqItem.classList.remove('is-open');
                answer.style.maxHeight = null; // Força a voltar a 0px
            }
        });
    });
});