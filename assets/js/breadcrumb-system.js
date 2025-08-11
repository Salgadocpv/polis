/**
 * Sistema de Breadcrumbs para Polis Engenharia
 */

class BreadcrumbSystem {
    constructor() {
        this.container = null;
        this.breadcrumbs = [];
        this.maxItems = 4; // Máximo de itens visíveis
        this.routes = {
            '/polis/dashboard.php': { title: 'Dashboard', icon: 'fas fa-home' },
            '/polis/dashboard_usuario.php': { title: 'Dashboard', icon: 'fas fa-home' },
            '/polis/listas/lista_clientes.php': { title: 'Clientes', icon: 'fas fa-users' },
            '/polis/listas/lista_colaboradores.php': { title: 'Colaboradores', icon: 'fas fa-user-tie' },
            '/polis/listas/lista_projetos.php': { title: 'Projetos', icon: 'fas fa-project-diagram' },
            '/polis/registros/registrar_cliente.php': { title: 'Novo Cliente', icon: 'fas fa-user-plus' },
            '/polis/registros/registrar_colaborador.php': { title: 'Novo Colaborador', icon: 'fas fa-user-plus' },
            '/polis/registros/registrar_projeto.php': { title: 'Novo Projeto', icon: 'fas fa-plus-circle' },
            '/polis/calendario.php': { title: 'Calendário', icon: 'fas fa-calendar-alt' }
        };
        this.init();
    }

    init() {
        // Sistema de breadcrumb desabilitado - usando apenas page-headers
        this.removeBreadcrumbs();
        return;
    }
    
    removeBreadcrumbs() {
        // Remover qualquer breadcrumb existente
        const breadcrumbContainer = document.getElementById('breadcrumb-container');
        if (breadcrumbContainer) {
            breadcrumbContainer.remove();
        }
        
        // Remover outros possíveis breadcrumbs
        const breadcrumbLists = document.querySelectorAll('.breadcrumb-list, nav[aria-label="Navegação breadcrumb"], .breadcrumb-container');
        breadcrumbLists.forEach(element => {
            element.remove();
        });
        
        // Remover estilos de breadcrumb
        const breadcrumbStyles = document.getElementById('breadcrumb-styles');
        if (breadcrumbStyles) {
            breadcrumbStyles.remove();
        }
        
        // Observer para remover breadcrumbs que possam aparecer dinamicamente
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Element node
                        // Verificar se é um breadcrumb ou contém breadcrumbs
                        if (node.classList && (
                            node.classList.contains('breadcrumb-container') ||
                            node.classList.contains('breadcrumb-list') ||
                            node.getAttribute('aria-label') === 'Navegação breadcrumb'
                        )) {
                            node.remove();
                        }
                        
                        // Verificar breadcrumbs dentro do node
                        const innerBreadcrumbs = node.querySelectorAll && node.querySelectorAll('.breadcrumb-list, nav[aria-label="Navegação breadcrumb"], .breadcrumb-container');
                        if (innerBreadcrumbs) {
                            innerBreadcrumbs.forEach(element => element.remove());
                        }
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Adicionar CSS para ocultar qualquer breadcrumb que possa aparecer
        const hideStyles = document.createElement('style');
        hideStyles.id = 'hide-breadcrumbs';
        hideStyles.textContent = `
            .breadcrumb-container,
            .breadcrumb-list,
            nav[aria-label="Navegação breadcrumb"],
            .breadcrumb-item,
            .breadcrumb-link {
                display: none !important;
                visibility: hidden !important;
            }
        `;
        document.head.appendChild(hideStyles);
    }

    createBreadcrumbContainer() {
        // Verificar se já existe
        if (document.getElementById('breadcrumb-container')) return;

        const container = document.createElement('nav');
        container.id = 'breadcrumb-container';
        container.className = 'breadcrumb-container';
        container.setAttribute('aria-label', 'Navegação breadcrumb');

        // Inserir após o header
        const mainContent = document.querySelector('.main-content');
        if (mainContent) {
            mainContent.insertBefore(container, mainContent.firstChild);
        } else {
            // Fallback
            document.body.insertBefore(container, document.body.firstChild);
        }

        this.container = container;
    }

    addStyles() {
        if (document.getElementById('breadcrumb-styles')) return;

        const styles = `
            <style id="breadcrumb-styles">
                .breadcrumb-container {
                    background: rgba(255, 255, 255, 0.05);
                    backdrop-filter: blur(10px);
                    border-radius: 12px;
                    padding: 12px 20px;
                    margin-bottom: 24px;
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    position: sticky;
                    top: 80px;
                    z-index: 100;
                    transition: all 0.3s ease;
                }

                .breadcrumb-list {
                    display: flex;
                    align-items: center;
                    flex-wrap: wrap;
                    gap: 8px;
                    margin: 0;
                    padding: 0;
                    list-style: none;
                }

                .breadcrumb-item {
                    display: flex;
                    align-items: center;
                    font-size: 14px;
                    color: rgba(255, 255, 255, 0.7);
                    transition: all 0.2s ease;
                }

                .breadcrumb-item:last-child {
                    color: var(--cor-texto-claro);
                    font-weight: 500;
                }

                .breadcrumb-link {
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    color: inherit;
                    text-decoration: none;
                    padding: 4px 8px;
                    border-radius: 6px;
                    transition: all 0.2s ease;
                    max-width: 150px;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }

                .breadcrumb-link:hover {
                    background: rgba(255, 255, 255, 0.1);
                    color: var(--cor-texto-claro);
                    transform: translateY(-1px);
                }

                .breadcrumb-icon {
                    font-size: 12px;
                    flex-shrink: 0;
                }

                .breadcrumb-separator {
                    color: rgba(255, 255, 255, 0.4);
                    font-size: 12px;
                    margin: 0 4px;
                    user-select: none;
                }

                .breadcrumb-ellipsis {
                    color: rgba(255, 255, 255, 0.5);
                    cursor: pointer;
                    padding: 4px 8px;
                    border-radius: 6px;
                    transition: all 0.2s ease;
                    position: relative;
                }

                .breadcrumb-ellipsis:hover {
                    background: rgba(255, 255, 255, 0.1);
                    color: var(--cor-texto-claro);
                }

                .breadcrumb-dropdown {
                    position: absolute;
                    top: 100%;
                    left: 0;
                    background: white;
                    border: 1px solid #e2e8f0;
                    border-radius: 8px;
                    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
                    opacity: 0;
                    visibility: hidden;
                    transform: translateY(-10px);
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    z-index: 1000;
                    min-width: 200px;
                    margin-top: 8px;
                }

                .breadcrumb-ellipsis.active .breadcrumb-dropdown {
                    opacity: 1;
                    visibility: visible;
                    transform: translateY(0);
                }

                .breadcrumb-dropdown-item {
                    width: 100%;
                    background: none;
                    border: none;
                    padding: 12px 16px;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    font-size: 14px;
                    color: var(--cor-texto-escuro);
                    cursor: pointer;
                    transition: all 0.2s ease;
                    text-decoration: none;
                    border-radius: 6px;
                    margin: 4px;
                    width: calc(100% - 8px);
                }

                .breadcrumb-dropdown-item:hover {
                    background: rgba(0, 180, 216, 0.1);
                    color: var(--cor-vibrante);
                }


                /* Mobile responsivo */
                @media (max-width: 768px) {
                    .breadcrumb-container {
                        padding: 10px 16px;
                        margin-bottom: 16px;
                        top: 70px;
                        position: relative; /* Remove sticky no mobile */
                    }

                    .breadcrumb-list {
                        gap: 4px;
                    }

                    .breadcrumb-item {
                        font-size: 13px;
                    }

                    .breadcrumb-link {
                        max-width: 100px;
                        padding: 3px 6px;
                    }

                    .breadcrumb-text {
                        display: none; /* Só mostra ícones no mobile */
                    }

                    .breadcrumb-item:last-child .breadcrumb-text {
                        display: inline; /* Mostra texto só no último item */
                    }
                }

                /* Animações */
                .breadcrumb-item {
                    animation: slideInBreadcrumb 0.3s ease;
                }

                @keyframes slideInBreadcrumb {
                    from {
                        opacity: 0;
                        transform: translateX(-10px);
                    }
                    to {
                        opacity: 1;
                        transform: translateX(0);
                    }
                }
            </style>
        `;

        document.head.insertAdjacentHTML('beforeend', styles);
    }

    updateBreadcrumbs() {
        if (!this.container) return;

        const currentPath = window.location.pathname;
        const breadcrumbs = this.generateBreadcrumbs(currentPath);
        
        this.breadcrumbs = breadcrumbs;
        this.render();
    }

    generateBreadcrumbs(currentPath) {
        const breadcrumbs = [];
        
        // Sempre começar com Home/Dashboard
        breadcrumbs.push({
            title: 'Dashboard',
            icon: 'fas fa-home',
            url: this.getUserDashboard(),
            active: false
        });

        // Mapear rota atual
        const currentRoute = this.routes[currentPath];
        if (currentRoute && currentPath !== this.getUserDashboard()) {
            breadcrumbs.push({
                title: currentRoute.title,
                icon: currentRoute.icon,
                url: currentPath,
                active: true
            });
        }

        // Se temos parâmetros de query, podem indicar contexto adicional
        const urlParams = new URLSearchParams(window.location.search);
        
        if (urlParams.has('id')) {
            const id = urlParams.get('id');
            const type = this.getEntityTypeFromPath(currentPath);
            
            if (type) {
                breadcrumbs.push({
                    title: `${type} #${id}`,
                    icon: 'fas fa-info-circle',
                    url: currentPath + window.location.search,
                    active: true
                });
            }
        }

        // Se estamos editando
        if (urlParams.has('edit')) {
            breadcrumbs[breadcrumbs.length - 1].title = 'Editando - ' + breadcrumbs[breadcrumbs.length - 1].title;
            breadcrumbs[breadcrumbs.length - 1].icon = 'fas fa-edit';
        }

        return breadcrumbs;
    }

    getEntityTypeFromPath(path) {
        if (path.includes('cliente')) return 'Cliente';
        if (path.includes('colaborador')) return 'Colaborador';
        if (path.includes('projeto')) return 'Projeto';
        return null;
    }

    getUserDashboard() {
        // Verificar se é usuário ou admin baseado na sessão ou URL atual
        if (window.location.pathname.includes('dashboard_usuario')) {
            return '/polis/dashboard_usuario.php';
        }
        return '/polis/dashboard.php';
    }

    render() {
        if (!this.container || !this.breadcrumbs.length) return;

        let items = [...this.breadcrumbs];
        let showEllipsis = false;
        let hiddenItems = [];

        // Se temos muitos itens, mostrar ellipsis
        if (items.length > this.maxItems) {
            hiddenItems = items.slice(1, -2); // Esconder itens do meio
            items = [items[0], ...items.slice(-2)]; // Manter primeiro e últimos 2
            showEllipsis = hiddenItems.length > 0;
        }

        const breadcrumbHTML = `
            <ol class="breadcrumb-list">
                ${this.renderBreadcrumbItem(items[0], false)}
                
                ${showEllipsis ? `
                    <li class="breadcrumb-separator">/</li>
                    <li class="breadcrumb-ellipsis" onclick="this.classList.toggle('active')">
                        <span>...</span>
                        <div class="breadcrumb-dropdown">
                            ${hiddenItems.map(item => `
                                <a href="${item.url}" class="breadcrumb-dropdown-item">
                                    <i class="${item.icon}"></i>
                                    <span>${item.title}</span>
                                </a>
                            `).join('')}
                        </div>
                    </li>
                ` : ''}
                
                ${items.slice(1).map((item, index) => `
                    <li class="breadcrumb-separator">/</li>
                    ${this.renderBreadcrumbItem(item, item.active)}
                `).join('')}
            </ol>
        `;

        this.container.innerHTML = breadcrumbHTML;
    }

    renderBreadcrumbItem(item, isActive) {
        const classes = `breadcrumb-item ${isActive ? 'active' : ''}`;
        
        if (isActive) {
            return `
                <li class="${classes}">
                    <span class="breadcrumb-link">
                        <i class="breadcrumb-icon ${item.icon}"></i>
                        <span class="breadcrumb-text">${item.title}</span>
                    </span>
                </li>
            `;
        }

        return `
            <li class="${classes}">
                <a href="${item.url}" class="breadcrumb-link">
                    <i class="breadcrumb-icon ${item.icon}"></i>
                    <span class="breadcrumb-text">${item.title}</span>
                </a>
            </li>
        `;
    }

    bindEvents() {
        // Atualizar breadcrumbs quando a página muda (SPA-like)
        window.addEventListener('popstate', () => {
            this.updateBreadcrumbs();
        });

        // Fechar dropdown ao clicar fora
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.breadcrumb-ellipsis')) {
                const ellipsis = document.querySelectorAll('.breadcrumb-ellipsis.active');
                ellipsis.forEach(el => el.classList.remove('active'));
            }
        });
    }

    // Método para adicionar breadcrumb customizado
    addCustomBreadcrumb(title, icon = 'fas fa-circle', url = null) {
        this.breadcrumbs.push({
            title,
            icon,
            url: url || window.location.href,
            active: true
        });

        // Desativar o anterior
        if (this.breadcrumbs.length > 1) {
            this.breadcrumbs[this.breadcrumbs.length - 2].active = false;
        }

        this.render();
    }

    // Método para limpar e reconstruir
    rebuild() {
        this.updateBreadcrumbs();
    }
}

// Função para adicionar breadcrumb customizado globalmente
window.addBreadcrumb = function(title, icon, url) {
    if (window.breadcrumbSystem) {
        window.breadcrumbSystem.addCustomBreadcrumb(title, icon, url);
    }
};

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
    // Aguardar um pouco para garantir que outros elementos carregaram
    setTimeout(() => {
        window.breadcrumbSystem = new BreadcrumbSystem();
        console.log('Breadcrumb System initialized');
    }, 100);
});

// Disponibilizar globalmente
window.BreadcrumbSystem = BreadcrumbSystem;