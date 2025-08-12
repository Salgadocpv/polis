/**
 * SISTEMA DE BREADCRUMBS REMOVIDO - POLIS ENGENHARIA
 * 
 * Este arquivo foi completamente removido conforme solicitado.
 * O sistema de navegação agora depende apenas dos page-headers
 * e da navegação principal do sidebar.
 * 
 * Sistema removido em: 2025
 * Motivo: Simplificação da interface e foco em page-headers
 */

class BreadcrumbSystem {
    constructor() {
        // ===== SISTEMA COMPLETAMENTE REMOVIDO =====
        console.log('🗑️  Breadcrumb System: REMOVIDO - Sistema desabilitado permanentemente');
        this.removeAllBreadcrumbs();
        return;
    }
    /**
     * REMOÇÃO COMPLETA DO SISTEMA DE BREADCRUMBS
     * 
     * Remove todos os elementos de breadcrumb da página e previne
     * que novos breadcrumbs sejam criados dinamicamente
     */
    removeAllBreadcrumbs() {
        // ===== REMOÇÃO DE ELEMENTOS EXISTENTES =====
        console.log('🧹 Removendo todos os breadcrumbs existentes...');
        
        // Lista de seletores para remover
        const breadcrumbSelectors = [
            '#breadcrumb-container',
            '.breadcrumb-container', 
            '.breadcrumb-list',
            'nav[aria-label="Navegação breadcrumb"]',
            'nav[aria-label="breadcrumb"]',
            '.breadcrumb',
            '.breadcrumbs'
        ];
        
        // Remove todos os elementos encontrados
        breadcrumbSelectors.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(element => {
                console.log(`   └─ Removendo: ${selector}`);
                element.remove();
            });
        });
        
        // ===== REMOÇÃO DE ESTILOS =====
        const stylesToRemove = [
            '#breadcrumb-styles',
            '#hide-breadcrumbs'
        ];
        
        stylesToRemove.forEach(selector => {
            const styleElement = document.querySelector(selector);
            if (styleElement) {
                console.log(`   └─ Removendo estilos: ${selector}`);
                styleElement.remove();
            }
        });
        
        // ===== OBSERVER PARA PREVENIR NOVOS BREADCRUMBS =====
        // Monitora DOM para remover breadcrumbs que possam ser criados dinamicamente
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Element node
                        this.removeBreadcrumbFromNode(node);
                    }
                });
            });
        });
        
        // Inicia observação do DOM completo
        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class', 'id', 'aria-label']
        });
        
        // ===== CSS PARA OCULTAR BREADCRUMBS =====
        // Adiciona CSS que força ocultação de qualquer breadcrumb
        this.addHidingStyles();
        
        // ===== LOG DE CONFIRMAÇÃO =====
        console.log('✅ Todos os breadcrumbs foram removidos permanentemente');
    }
    
    /**
     * REMOÇÃO DE BREADCRUMBS DE UM NÓ ESPECÍFICO
     * 
     * Verifica e remove breadcrumbs de um elemento DOM específico
     * Usado pelo observer para limpeza dinâmica
     */
    removeBreadcrumbFromNode(node) {
        // Verifica se o próprio nó é um breadcrumb
        if (this.isBreadcrumbElement(node)) {
            console.log('🗑️  Removendo breadcrumb detectado dinamicamente');
            node.remove();
            return;
        }
        
        // Verifica breadcrumbs dentro do nó
        const innerBreadcrumbs = node.querySelectorAll && node.querySelectorAll(
            '.breadcrumb-container, .breadcrumb-list, nav[aria-label*="breadcrumb"], .breadcrumb'
        );
        
        if (innerBreadcrumbs && innerBreadcrumbs.length > 0) {
            console.log(`🗑️  Removendo ${innerBreadcrumbs.length} breadcrumbs internos`);
            innerBreadcrumbs.forEach(element => element.remove());
        }
    }
    
    /**
     * VERIFICAÇÃO SE ELEMENTO É BREADCRUMB
     * 
     * Verifica se um elemento DOM é um breadcrumb baseado
     * em classes, IDs e atributos comuns
     */
    isBreadcrumbElement(element) {
        if (!element.classList && !element.id && !element.getAttribute) {
            return false;
        }
        
        // Verificações por classe
        const breadcrumbClasses = [
            'breadcrumb-container',
            'breadcrumb-list', 
            'breadcrumb',
            'breadcrumbs'
        ];
        
        for (let className of breadcrumbClasses) {
            if (element.classList && element.classList.contains(className)) {
                return true;
            }
        }
        
        // Verificação por ID
        if (element.id && element.id.includes('breadcrumb')) {
            return true;
        }
        
        // Verificação por aria-label
        const ariaLabel = element.getAttribute && element.getAttribute('aria-label');
        if (ariaLabel && ariaLabel.toLowerCase().includes('breadcrumb')) {
            return true;
        }
        
        return false;
    }
    
    /**
     * ADIÇÃO DE ESTILOS PARA OCULTAR BREADCRUMBS
     * 
     * Adiciona CSS que força a ocultação de qualquer breadcrumb
     * que possa aparecer no futuro
     */
    addHidingStyles() {
        const hideStyles = document.createElement('style');
        hideStyles.id = 'hide-all-breadcrumbs';
        hideStyles.textContent = `
            /* ===== OCULTAÇÃO FORÇADA DE BREADCRUMBS ===== */
            /* Remove todos os breadcrumbs possíveis */
            .breadcrumb-container,
            .breadcrumb-list,
            .breadcrumb,
            .breadcrumbs,
            nav[aria-label*="breadcrumb"],
            nav[aria-label*="Breadcrumb"],
            #breadcrumb-container,
            [class*="breadcrumb"] {
                display: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
                height: 0 !important;
                width: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
            }
            
            /* Remove espaçamento que breadcrumbs poderiam deixar */
            .main-content > nav:first-child,
            .content-wrapper > nav:first-child {
                display: none !important;
            }
        `;
        
        document.head.appendChild(hideStyles);
        console.log('🎨 Estilos de ocultação de breadcrumbs aplicados');
    }

}

// ===== FUNÇÕES GLOBAIS REMOVIDAS =====
// Todas as funções globais de breadcrumb foram desabilitadas

/**
 * FUNÇÃO GLOBAL DESABILITADA - addBreadcrumb
 * 
 * Função anteriormente usada para adicionar breadcrumbs customizados
 * Agora apenas registra que foi chamada mas não executa nenhuma ação
 */
window.addBreadcrumb = function(title, icon, url) {
    console.log('📵 addBreadcrumb chamada mas DESABILITADA:', { title, icon, url });
    // Não faz nada - sistema removido
    return false;
};

/**
 * INICIALIZAÇÃO DO SISTEMA REMOVIDO
 * 
 * Sistema de breadcrumbs foi completamente desabilitado
 * Apenas inicializa o processo de remoção
 */
document.addEventListener('DOMContentLoaded', () => {
    // ===== INICIALIZAÇÃO DA REMOÇÃO =====
    console.log('🗑️  Inicializando remoção do sistema de breadcrumbs...');
    
    // Aguarda um pouco para garantir que DOM foi carregado
    setTimeout(() => {
        // Cria instância apenas para executar remoção
        window.breadcrumbSystem = new BreadcrumbSystem();
        
        console.log('✅ Sistema de breadcrumbs REMOVIDO e desabilitado permanentemente');
    }, 100);
});

/**
 * DISPONIBILIZAÇÃO DA CLASSE (APENAS PARA REMOÇÃO)
 * 
 * Classe disponível globalmente apenas para fins de remoção
 * Não deve ser usada para criar novos breadcrumbs
 */
window.BreadcrumbSystem = BreadcrumbSystem;