/**
 * Alternador de Visualização do Portfólio (List / Grid)
 * Com persistência de dados no LocalStorage (Memória do Navegador)
 */
document.addEventListener('DOMContentLoaded', () => {
    const btnList = document.getElementById('view-list');
    const btnGrid = document.getElementById('view-grid');
    const container = document.getElementById('projects-container');

    // Se não estiver na página de projetos, encerra a execução
    if (!btnList || !btnGrid || !container) return;

    // 1. Recupera a preferência salva pelo usuário ou define 'list' como padrão
    const savedView = localStorage.getItem('abc_portfolio_view') || 'list';

    // 2. Função centralizada para aplicar a visualização e salvar no navegador
    const setView = (viewName) => {
        if (viewName === 'grid') {
            container.classList.remove('list-view');
            container.classList.add('grid-view');
            
            btnGrid.classList.add('is-active');
            btnList.classList.remove('is-active');
        } else {
            container.classList.remove('grid-view');
            container.classList.add('list-view');
            
            btnList.classList.add('is-active');
            btnGrid.classList.remove('is-active');
        }
        
        // Salva a escolha silenciosamente no navegador do usuário
        localStorage.setItem('abc_portfolio_view', viewName);
    };

    // 3. Aplica a visualização inicial assim que a página carrega
    setView(savedView);

    // 4. Aguarda o clique do usuário para alternar a visualização
    btnList.addEventListener('click', () => setView('list'));
    btnGrid.addEventListener('click', () => setView('grid'));

    // 5. Filtro Dinâmico de Projetos por Tecnologia
    const filterBtns = document.querySelectorAll('.tech-filter-btn');
    const projectCards = document.querySelectorAll('.project-card');

    if (filterBtns.length && projectCards.length) {
        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const filter = btn.getAttribute('data-filter').toLowerCase();

                // Atualiza classe ativa dos botões
                filterBtns.forEach(b => {
                    b.classList.remove('is-active');
                    b.style.background = 'rgba(255,255,255,0.05)';
                    b.style.borderColor = 'rgba(255,255,255,0.1)';
                });
                btn.classList.add('is-active');
                btn.style.background = 'var(--color-blue, #0073aa)';
                btn.style.borderColor = 'var(--color-blue, #0073aa)';

                // Filtra os cards
                projectCards.forEach(card => {
                    const techData = (card.getAttribute('data-tech') || '').toLowerCase();

                    if (filter === 'all' || techData.includes(filter)) {
                        card.style.display = '';
                        card.style.opacity = '1';
                        card.style.transform = 'scale(1)';
                    } else {
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            if (!btn.classList.contains('is-active') || filter !== 'all' && !techData.includes(filter)) {
                                card.style.display = 'none';
                            }
                        }, 200);
                    }
                });
            });
        });
    }
});