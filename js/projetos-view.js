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

    // 4. Aguarda o clique do usuário para alternar
    btnList.addEventListener('click', () => setView('list'));
    btnGrid.addEventListener('click', () => setView('grid'));
});