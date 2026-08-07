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

/**
 * Script do Botão Voltar ao Topo (Back to Top)
 */
document.addEventListener('DOMContentLoaded', () => {
    const backToTopBtn = document.getElementById('back-to-top');

    if (!backToTopBtn) return;

    // Controla a visibilidade do botão com base no scroll da página
    const toggleBackToTop = () => {
        if (window.scrollY > 300) {
            backToTopBtn.classList.add('is-visible');
        } else {
            backToTopBtn.classList.remove('is-visible');
        }
    };

    window.addEventListener('scroll', toggleBackToTop, { passive: true });

    // Scroll suave ao topo ao clicar
    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});

/**
 * WebMCP API Integration (Agent-Native Browser Capabilities)
 */
document.addEventListener('DOMContentLoaded', () => {
    if (typeof navigator !== 'undefined' && navigator.modelContext && typeof navigator.modelContext.provideContext === 'function') {
        try {
            navigator.modelContext.provideContext({
                tools: [
                    {
                        name: "get_portfolio_projects",
                        description: "Fetch list of high-performance WordPress engineering projects",
                        inputSchema: { type: "object", properties: {} },
                        execute: async () => {
                            const response = await fetch('/wp-json/wp/v2/project');
                            return await response.json();
                        }
                    },
                    {
                        name: "submit_contact_inquiry",
                        description: "Submit a project inquiry or contact request",
                        inputSchema: {
                            type: "object",
                            properties: {
                                name: { type: "string" },
                                email: { type: "string" },
                                message: { type: "string" }
                            },
                            required: ["name", "email", "message"]
                        },
                        execute: async (args) => {
                            return { status: "success", message: "Inquiry received. Will respond within 24 hours.", data: args };
                        }
                    }
                ]
            });
        } catch (e) {
            // WebMCP not supported in current browser
        }
    }
});