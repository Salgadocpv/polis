/**
 * Sistema de Header de Página Padronizado para Polis Engenharia
 */

class PageHeader {
    constructor() {
        this.init();
    }

    init() {
        this.createPageHeader();
        this.addPageHeaderStyles();
    }

    createPageHeader() {
        // Verificar se já existe
        if (document.getElementById('page-header')) return;

        // Obter título da página
        const pageTitle = this.getPageTitle();
        const pageIcon = this.getPageIcon();
        const pageDescription = this.getPageDescription();

        // Criar header de página
        const pageHeader = document.createElement('div');
        pageHeader.id = 'page-header';
        pageHeader.className = 'page-header-glass';
        
        pageHeader.innerHTML = `
            <div class="page-header-content">
                <div class="page-header-info">
                    <div class="page-title-section">
                        <i class="page-icon ${pageIcon}"></i>
                        <h1 class="page-title">${pageTitle}</h1>
                    </div>
                    ${pageDescription ? `<p class="page-description">${pageDescription}</p>` : ''}
                </div>
                <div class="page-header-actions" id="page-header-actions">
                    <!-- Ações da página serão inseridas aqui -->
                </div>
            </div>
        `;

        // Inserir após o main-content começar
        const mainContent = document.querySelector('.main-content');
        if (mainContent) {
            // Inserir no início do main-content, após breadcrumbs se existir
            const breadcrumbContainer = document.getElementById('breadcrumb-container');
            if (breadcrumbContainer) {
                breadcrumbContainer.insertAdjacentElement('afterend', pageHeader);
            } else {
                mainContent.insertBefore(pageHeader, mainContent.firstChild);
            }
        }
    }

    addPageHeaderStyles() {
        if (document.getElementById('page-header-styles')) return;

        const styles = `
            <style id="page-header-styles">
                .page-header-glass {
                    background: rgba(0, 0, 0, 0.25);
                    backdrop-filter: blur(20px);
                    border: 1px solid rgba(255, 255, 255, 0.08);
                    border-radius: 16px;
                    padding: 2rem;
                    margin-bottom: 2rem;
                    position: relative;
                    transition: all 0.3s ease;
                    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
                }

                .page-header-glass:hover {
                    background: rgba(0, 0, 0, 0.35);
                    border-color: rgba(255, 255, 255, 0.12);
                    transform: translateY(-1px);
                    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
                }

                .page-header-content {
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    gap: 2rem;
                }

                .page-header-info {
                    flex: 1;
                }

                .page-title-section {
                    display: flex;
                    align-items: center;
                    gap: 1rem;
                    margin-bottom: 0.5rem;
                }

                .page-icon {
                    font-size: 2.5rem;
                    color: var(--cor-vibrante);
                    filter: drop-shadow(0 2px 8px rgba(0, 180, 216, 0.3));
                    transition: all 0.3s ease;
                }

                .page-header-glass:hover .page-icon {
                    transform: scale(1.05);
                    filter: drop-shadow(0 4px 12px rgba(0, 180, 216, 0.4));
                }

                .page-title {
                    font-size: 2.2rem;
                    font-weight: 700;
                    color: var(--cor-texto-claro);
                    margin: 0;
                    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
                    letter-spacing: -0.02em;
                    line-height: 1.2;
                }

                .page-description {
                    color: rgba(255, 255, 255, 0.8);
                    font-size: 1rem;
                    margin: 0;
                    font-weight: 400;
                    line-height: 1.5;
                    opacity: 0.9;
                }

                .page-header-actions {
                    display: flex;
                    align-items: center;
                    gap: 1rem;
                    flex-shrink: 0;
                }

                .page-action-btn {
                    background: rgba(255, 255, 255, 0.1);
                    border: 2px solid rgba(255, 255, 255, 0.2);
                    color: var(--cor-texto-claro);
                    padding: 0.75rem 1.5rem;
                    border-radius: 12px;
                    font-size: 0.95rem;
                    font-weight: 600;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    backdrop-filter: blur(10px);
                    text-decoration: none;
                    white-space: nowrap;
                }

                .page-action-btn:hover {
                    background: rgba(255, 255, 255, 0.15);
                    border-color: rgba(255, 255, 255, 0.3);
                    transform: translateY(-2px);
                    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
                    color: var(--cor-texto-claro);
                    text-decoration: none;
                }

                .page-action-btn.primary {
                    background: var(--cor-vibrante);
                    border-color: var(--cor-vibrante);
                    color: white;
                    box-shadow: 0 4px 15px rgba(0, 180, 216, 0.3);
                }

                .page-action-btn.primary:hover {
                    background: var(--cor-clara);
                    border-color: var(--cor-clara);
                    box-shadow: 0 8px 25px rgba(0, 180, 216, 0.4);
                    color: white;
                }

                .page-action-btn.success {
                    background: #10b981;
                    border-color: #10b981;
                    color: white;
                    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
                }

                .page-action-btn.success:hover {
                    background: #059669;
                    border-color: #059669;
                    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
                    color: white;
                }


                /* Responsividade */
                @media (max-width: 768px) {
                    .page-header-glass {
                        padding: 1.5rem;
                        margin-bottom: 1.5rem;
                    }

                    .page-header-content {
                        flex-direction: column;
                        gap: 1.5rem;
                        align-items: flex-start;
                    }

                    .page-title-section {
                        gap: 0.75rem;
                    }

                    .page-icon {
                        font-size: 2rem;
                    }

                    .page-title {
                        font-size: 1.8rem;
                    }

                    .page-description {
                        font-size: 0.9rem;
                    }

                    .page-header-actions {
                        width: 100%;
                        justify-content: flex-start;
                        flex-wrap: wrap;
                    }

                    .page-action-btn {
                        padding: 0.6rem 1.2rem;
                        font-size: 0.9rem;
                    }
                }

                @media (max-width: 480px) {
                    .page-header-glass {
                        padding: 1rem;
                        border-radius: 12px;
                    }

                    .page-title {
                        font-size: 1.5rem;
                    }

                    .page-icon {
                        font-size: 1.8rem;
                    }

                    .page-header-actions {
                        gap: 0.5rem;
                    }

                    .page-action-btn {
                        padding: 0.5rem 1rem;
                        font-size: 0.85rem;
                        flex: 1;
                        justify-content: center;
                    }
                }
            </style>
        `;

        document.head.insertAdjacentHTML('beforeend', styles);
    }

    getPageTitle() {
        // Tentar extrair título da página
        const h1 = document.querySelector('h1');
        if (h1) {
            return h1.textContent.trim();
        }

        // Extrair do title da página
        const title = document.title;
        if (title.includes(' - ')) {
            return title.split(' - ')[0].trim();
        }

        // Baseado na URL
        const path = window.location.pathname;
        const titleMap = {
            'dashboard.php': 'Dashboard',
            'dashboard_usuario.php': 'Dashboard',
            'lista_clientes.php': 'Lista de Clientes',
            'lista_colaboradores.php': 'Lista de Colaboradores',  
            'lista_projetos.php': 'Lista de Projetos',
            'registrar_cliente.php': 'Novo Cliente',
            'registrar_colaborador.php': 'Novo Colaborador',
            'registrar_projeto.php': 'Novo Projeto',
            'calendario.php': 'Calendário',
            'setup.php': 'Configurações'
        };

        for (const [key, value] of Object.entries(titleMap)) {
            if (path.includes(key)) {
                return value;
            }
        }

        return 'Polis Engenharia';
    }

    getPageIcon() {
        const path = window.location.pathname;
        const iconMap = {
            'dashboard.php': 'fas fa-home',
            'dashboard_usuario.php': 'fas fa-home',
            'lista_clientes.php': 'fas fa-users',
            'lista_colaboradores.php': 'fas fa-user-tie',
            'lista_projetos.php': 'fas fa-project-diagram',
            'registrar_cliente.php': 'fas fa-user-plus',
            'registrar_colaborador.php': 'fas fa-user-plus',
            'registrar_projeto.php': 'fas fa-plus-circle',
            'calendario.php': 'fas fa-calendar-alt',
            'setup.php': 'fas fa-cog'
        };

        for (const [key, value] of Object.entries(iconMap)) {
            if (path.includes(key)) {
                return value;
            }
        }

        return 'fas fa-home';
    }

    getPageDescription() {
        const path = window.location.pathname;
        const descMap = {
            'dashboard.php': 'Visão geral do sistema e métricas principais',
            'dashboard_usuario.php': 'Painel do usuário com informações relevantes',
            'lista_clientes.php': 'Gerencie e visualize todos os clientes cadastrados',
            'lista_colaboradores.php': 'Visualize e gerencie colaboradores da empresa',
            'lista_projetos.php': 'Acompanhe todos os projetos em andamento',
            'registrar_cliente.php': 'Cadastre um novo cliente no sistema',
            'registrar_colaborador.php': 'Adicione um novo colaborador à equipe',
            'registrar_projeto.php': 'Crie um novo projeto para acompanhamento',
            'calendario.php': 'Visualize e gerencie eventos e compromissos',
            'setup.php': 'Configure parâmetros do sistema'
        };

        for (const [key, value] of Object.entries(descMap)) {
            if (path.includes(key)) {
                return value;
            }
        }

        return null;
    }

    // Método para adicionar ações ao header
    addAction(action) {
        const actionsContainer = document.getElementById('page-header-actions');
        if (!actionsContainer) return;

        const actionBtn = document.createElement('a');
        actionBtn.href = action.href || '#';
        actionBtn.className = `page-action-btn ${action.type || ''}`;
        actionBtn.innerHTML = `
            ${action.icon ? `<i class="${action.icon}"></i>` : ''}
            <span>${action.text}</span>
        `;

        if (action.onclick) {
            actionBtn.onclick = action.onclick;
        }

        actionsContainer.appendChild(actionBtn);
    }

    // Método para limpar ações
    clearActions() {
        const actionsContainer = document.getElementById('page-header-actions');
        if (actionsContainer) {
            actionsContainer.innerHTML = '';
        }
    }
}

// Função global para adicionar ações
window.addPageAction = function(action) {
    if (window.pageHeader) {
        window.pageHeader.addAction(action);
    }
};

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
    // Aguardar outros sistemas carregarem
    setTimeout(() => {
        window.pageHeader = new PageHeader();
        
        // Adicionar ações padrão baseado na página
        setTimeout(() => {
            window.pageHeader.addDefaultActions();
        }, 100);
        
        console.log('Page Header System initialized');
    }, 200);
});

// Adicionar ações padrão
PageHeader.prototype.addDefaultActions = function() {
    const path = window.location.pathname;
    
    // Ações para listas
    if (path.includes('lista_')) {
        this.addAction({
            text: 'Exportar Excel',
            icon: 'fas fa-file-excel',
            type: 'success',
            onclick: (e) => {
                e.preventDefault();
                const table = document.querySelector('table');
                if (table && window.excelExporter) {
                    const title = this.getPageTitle();
                    window.excelExporter.exportTableToExcel(table, title);
                } else {
                    console.error('Tabela não encontrada ou sistema de exportação não disponível');
                }
            }
        });

        // Verificar nível de acesso do usuário
        const userInfo = document.querySelector('.user-info');
        let isVisualizer = false;
        
        if (userInfo) {
            const userText = userInfo.textContent || '';
            isVisualizer = userText.toLowerCase().includes('visualizador');
        }
        
        // Botão para adicionar novo registro - apenas se não for visualizador
        if (!isVisualizer) {
            const addMap = {
                'lista_clientes.php': {
                    text: 'Novo Cliente',
                    icon: 'fas fa-plus',
                    href: '../registros/registrar_cliente.php',
                    type: 'primary'
                },
                'lista_colaboradores.php': {
                    text: 'Novo Colaborador', 
                    icon: 'fas fa-plus',
                    href: '../registros/registrar_colaborador.php',
                    type: 'primary'
                },
                'lista_projetos.php': {
                    text: 'Novo Projeto',
                    icon: 'fas fa-plus', 
                    href: '../registros/registrar_projeto.php',
                    type: 'primary'
                }
            };

            for (const [key, action] of Object.entries(addMap)) {
                if (path.includes(key)) {
                    this.addAction(action);
                    break;
                }
            }
        }
    }
    
    // Ações para registros
    if (path.includes('registrar_')) {
        const backMap = {
            'registrar_cliente.php': '../listas/lista_clientes.php',
            'registrar_colaborador.php': '../listas/lista_colaboradores.php',
            'registrar_projeto.php': '../listas/lista_projetos.php'
        };

        for (const [key, href] of Object.entries(backMap)) {
            if (path.includes(key)) {
                this.addAction({
                    text: 'Voltar à Lista',
                    icon: 'fas fa-arrow-left',
                    href: href
                });
                break;
            }
        }
    }
};

// Disponibilizar globalmente
window.PageHeader = PageHeader;