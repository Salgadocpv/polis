/**
 * Sistema de Lazy Loading Avançado para Polis Engenharia
 */

class LazyLoader {
    constructor() {
        this.imageObserver = null;
        this.contentObserver = null;
        this.loadedImages = new Set();
        this.loadedContent = new Set();
        this.init();
    }

    init() {
        this.setupImageLazyLoading();
        this.setupContentLazyLoading();
        this.setupCacheCleanup();
    }

    /**
     * Lazy loading para imagens
     */
    setupImageLazyLoading() {
        // Verificar suporte ao IntersectionObserver
        if (!('IntersectionObserver' in window)) {
            this.loadAllImages();
            return;
        }

        this.imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.loadImage(entry.target);
                    this.imageObserver.unobserve(entry.target);
                }
            });
        }, {
            rootMargin: '50px 0px', // Carregar 50px antes de entrar na tela
            threshold: 0.01
        });

        this.observeImages();
    }

    /**
     * Lazy loading para conteúdo dinâmico
     */
    setupContentLazyLoading() {
        if (!('IntersectionObserver' in window)) {
            return;
        }

        this.contentObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.loadContent(entry.target);
                    this.contentObserver.unobserve(entry.target);
                }
            });
        }, {
            rootMargin: '100px 0px',
            threshold: 0.01
        });

        this.observeContent();
    }

    /**
     * Observar imagens com lazy loading
     */
    observeImages() {
        const lazyImages = document.querySelectorAll('img[data-src], img[data-lazy]');
        lazyImages.forEach(img => {
            this.imageObserver.observe(img);
        });
    }

    /**
     * Observar conteúdo com lazy loading
     */
    observeContent() {
        const lazyContent = document.querySelectorAll('[data-lazy-load]');
        lazyContent.forEach(element => {
            this.contentObserver.observe(element);
        });
    }

    /**
     * Carregar uma imagem específica
     */
    async loadImage(img) {
        const src = img.dataset.src || img.dataset.lazy;
        if (!src || this.loadedImages.has(src)) return;

        try {
            // Mostrar placeholder de carregamento
            this.showImagePlaceholder(img);

            // Pré-carregar imagem
            const tempImg = new Image();
            tempImg.onload = () => {
                img.src = src;
                img.classList.add('lazy-loaded');
                this.hideImagePlaceholder(img);
                this.loadedImages.add(src);
            };
            tempImg.onerror = () => {
                this.handleImageError(img);
            };
            tempImg.src = src;

        } catch (error) {
            console.error('Erro ao carregar imagem:', error);
            this.handleImageError(img);
        }
    }

    /**
     * Carregar conteúdo dinâmico
     */
    async loadContent(element) {
        const endpoint = element.dataset.lazyLoad;
        const cacheKey = element.dataset.cacheKey || endpoint;

        if (!endpoint || this.loadedContent.has(cacheKey)) return;

        try {
            this.showContentPlaceholder(element);

            const response = await fetch(endpoint);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const data = await response.json();
            this.renderContent(element, data);
            this.loadedContent.add(cacheKey);

        } catch (error) {
            console.error('Erro ao carregar conteúdo:', error);
            this.handleContentError(element, error.message);
        }
    }

    /**
     * Placeholder de carregamento para imagens
     */
    showImagePlaceholder(img) {
        img.classList.add('lazy-loading');
        
        if (!img.dataset.placeholderAdded) {
            const placeholder = document.createElement('div');
            placeholder.className = 'image-placeholder';
            placeholder.innerHTML = `
                <div class="placeholder-shimmer">
                    <i class="fas fa-image"></i>
                    <div class="loading-text">Carregando...</div>
                </div>
            `;
            
            img.parentNode.insertBefore(placeholder, img);
            img.style.display = 'none';
            img.dataset.placeholderAdded = 'true';
        }
    }

    hideImagePlaceholder(img) {
        img.classList.remove('lazy-loading');
        img.style.display = '';
        
        const placeholder = img.parentNode.querySelector('.image-placeholder');
        if (placeholder) {
            placeholder.remove();
        }
    }

    /**
     * Placeholder de carregamento para conteúdo
     */
    showContentPlaceholder(element) {
        element.classList.add('content-loading');
        element.innerHTML = `
            <div class="content-placeholder">
                <div class="skeleton-loader">
                    <div class="skeleton-line"></div>
                    <div class="skeleton-line short"></div>
                    <div class="skeleton-line"></div>
                </div>
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>Carregando...</span>
                </div>
            </div>
        `;
    }

    /**
     * Renderizar conteúdo carregado
     */
    renderContent(element, data) {
        element.classList.remove('content-loading');
        element.classList.add('content-loaded');

        // Verificar se há template customizado
        const template = element.dataset.template;
        if (template && window[template]) {
            element.innerHTML = window[template](data);
        } else {
            // Template padrão
            if (Array.isArray(data)) {
                element.innerHTML = this.renderList(data);
            } else {
                element.innerHTML = this.renderObject(data);
            }
        }

        // Disparar evento personalizado
        element.dispatchEvent(new CustomEvent('contentLoaded', {
            detail: { data, element }
        }));
    }

    /**
     * Templates padrão para renderização
     */
    renderList(items) {
        if (!items.length) {
            return '<div class="empty-state">Nenhum item encontrado</div>';
        }

        return `
            <div class="lazy-content-list">
                ${items.map(item => `
                    <div class="list-item">
                        <h4>${item.title || item.nome || 'Item'}</h4>
                        <p>${item.description || item.descricao || ''}</p>
                    </div>
                `).join('')}
            </div>
        `;
    }

    renderObject(obj) {
        return `
            <div class="lazy-content-object">
                <pre>${JSON.stringify(obj, null, 2)}</pre>
            </div>
        `;
    }

    /**
     * Tratamento de erros
     */
    handleImageError(img) {
        img.classList.add('lazy-error');
        this.hideImagePlaceholder(img);
        
        // Imagem de fallback
        img.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0xMDAgMTIwSDEwMFY4MEgxMDBWMTIwWiIgZmlsbD0iIzlDQTNBRiIvPgo8cGF0aCBkPSJNMTAwIDEzNUg5OVYxMzVIMTAwWiIgZmlsbD0iIzlDQTNBRiIvPgo8L3N2Zz4K';
        img.alt = 'Imagem não encontrada';
    }

    handleContentError(element, message) {
        element.classList.remove('content-loading');
        element.classList.add('content-error');
        element.innerHTML = `
            <div class="error-state">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Erro ao carregar conteúdo</p>
                <small>${message}</small>
                <button class="retry-btn" onclick="lazyLoader.retryContent(this)">
                    Tentar novamente
                </button>
            </div>
        `;
    }

    /**
     * Retry para conteúdo com erro
     */
    retryContent(button) {
        const element = button.closest('[data-lazy-load]');
        const cacheKey = element.dataset.cacheKey || element.dataset.lazyLoad;
        
        // Remove do cache para forçar nova tentativa
        this.loadedContent.delete(cacheKey);
        
        // Recarrega o conteúdo
        this.loadContent(element);
    }

    /**
     * Carregar todas as imagens (fallback)
     */
    loadAllImages() {
        const lazyImages = document.querySelectorAll('img[data-src], img[data-lazy]');
        lazyImages.forEach(img => {
            const src = img.dataset.src || img.dataset.lazy;
            if (src) img.src = src;
        });
    }

    /**
     * Limpar cache periodicamente
     */
    setupCacheCleanup() {
        // Limpar cache de imagens a cada 30 minutos
        setInterval(() => {
            if (this.loadedImages.size > 100) {
                this.loadedImages.clear();
            }
        }, 30 * 60 * 1000);

        // Limpar cache de conteúdo a cada 15 minutos
        setInterval(() => {
            if (this.loadedContent.size > 50) {
                this.loadedContent.clear();
            }
        }, 15 * 60 * 1000);

        // Limpar cache do service worker diariamente
        setInterval(() => {
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.controller?.postMessage({
                    type: 'CLEAN_CACHE'
                });
            }
        }, 24 * 60 * 60 * 1000);
    }

    /**
     * Forçar carregamento de novo conteúdo
     */
    refresh() {
        this.observeImages();
        this.observeContent();
    }

    /**
     * Destruir observadores
     */
    destroy() {
        if (this.imageObserver) {
            this.imageObserver.disconnect();
        }
        if (this.contentObserver) {
            this.contentObserver.disconnect();
        }
    }
}

// CSS para placeholders e loading states
const lazyLoadingStyles = `
<style id="lazy-loading-styles">
.lazy-loading {
    opacity: 0.5;
    transition: opacity 0.3s ease;
}

.lazy-loaded {
    opacity: 1;
    animation: fadeIn 0.3s ease;
}

.lazy-error {
    opacity: 0.7;
    filter: grayscale(100%);
}

.image-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    min-height: 200px;
}

.placeholder-shimmer {
    text-align: center;
    color: #6c757d;
}

.placeholder-shimmer i {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.loading-text {
    font-size: 0.9rem;
    font-weight: 500;
}

.content-loading {
    min-height: 100px;
    position: relative;
}

.content-placeholder {
    padding: 2rem;
    text-align: center;
}

.skeleton-loader {
    margin-bottom: 1rem;
}

.skeleton-line {
    height: 1rem;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 4px;
    margin-bottom: 0.5rem;
}

.skeleton-line.short {
    width: 60%;
}

.loading-spinner {
    color: var(--cor-vibrante);
    font-size: 1.1rem;
}

.loading-spinner i {
    margin-right: 0.5rem;
}

.error-state {
    text-align: center;
    padding: 2rem;
    color: #dc3545;
}

.error-state i {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.retry-btn {
    background: var(--cor-vibrante);
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 1rem;
    transition: background 0.3s ease;
}

.retry-btn:hover {
    background: var(--cor-clara);
}

.empty-state {
    text-align: center;
    padding: 2rem;
    color: #6c757d;
    font-style: italic;
}

.content-loaded {
    animation: slideUp 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { 
        opacity: 0; 
        transform: translateY(20px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}
</style>
`;

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
    // Adicionar estilos
    document.head.insertAdjacentHTML('beforeend', lazyLoadingStyles);
    
    // Inicializar lazy loader
    window.lazyLoader = new LazyLoader();
    
    console.log('Lazy Loading System initialized');
});

// Disponibilizar globalmente
window.LazyLoader = LazyLoader;