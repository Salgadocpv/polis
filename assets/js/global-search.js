/**
 * Sistema de Busca Global para Polis Engenharia
 */

class GlobalSearch {
    constructor() {
        this.searchInput = null;
        this.searchResults = null;
        this.searchOverlay = null;
        this.currentQuery = '';
        this.searchTimeout = null;
        this.currentPage = 1;
        this.isLoading = false;
        this.cache = new Map();
        this.init();
    }

    init() {
        this.createSearchInterface();
        this.addSearchStyles();
        this.bindEvents();
        this.bindKeyboardShortcuts();
    }

    createSearchInterface() {
        // Verificar se já existe
        if (document.getElementById('global-search')) return;

        // Criar container de busca
        const searchContainer = document.createElement('div');
        searchContainer.id = 'global-search';
        searchContainer.className = 'global-search-container';
        
        searchContainer.innerHTML = `
            <div class="search-input-container">
                <div class="search-input-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input 
                        type="text" 
                        id="global-search-input" 
                        class="global-search-input" 
                        placeholder="Buscar em todo o sistema... (Ctrl + K)"
                        autocomplete="off"
                        spellcheck="false"
                    >
                    <div class="search-shortcut">Ctrl+K</div>
                </div>
            </div>
            
            <div class="search-overlay" id="search-overlay">
                <div class="search-modal">
                    <div class="search-modal-header">
                        <div class="search-modal-input-wrapper">
                            <i class="fas fa-search"></i>
                            <input 
                                type="text" 
                                id="search-modal-input" 
                                class="search-modal-input" 
                                placeholder="Digite para buscar..."
                                autocomplete="off"
                            >
                            <button class="search-close-btn" id="search-close-btn">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="search-modal-body">
                        <div class="search-suggestions" id="search-suggestions">
                            <div class="suggestion-group">
                                <div class="suggestion-header">Sugestões</div>
                                <div class="suggestion-item" data-query="projetos ativos">
                                    <i class="fas fa-project-diagram"></i>
                                    <span>Projetos Ativos</span>
                                </div>
                                <div class="suggestion-item" data-query="clientes novos">
                                    <i class="fas fa-users"></i>
                                    <span>Clientes Novos</span>
                                </div>
                                <div class="suggestion-item" data-query="colaboradores">
                                    <i class="fas fa-user-tie"></i>
                                    <span>Colaboradores</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="search-results" id="search-results">
                            <!-- Resultados da busca -->
                        </div>
                        
                        <div class="search-loading" id="search-loading">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>Buscando...</span>
                        </div>
                        
                        <div class="search-empty" id="search-empty">
                            <i class="fas fa-search"></i>
                            <p>Nenhum resultado encontrado</p>
                            <small>Tente usar termos diferentes ou menos específicos</small>
                        </div>
                    </div>
                    
                    <div class="search-modal-footer">
                        <div class="search-tips">
                            <span><kbd>↑</kbd><kbd>↓</kbd> para navegar</span>
                            <span><kbd>Enter</kbd> para abrir</span>
                            <span><kbd>Esc</kbd> para fechar</span>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Inserir no header
        const header = document.querySelector('.header-content');
        if (header) {
            // Inserir antes do user-info
            const userInfo = header.querySelector('.user-info');
            header.insertBefore(searchContainer, userInfo);
        } else {
            // Fallback: inserir no body
            document.body.appendChild(searchContainer);
        }

        // Referenciar elementos
        this.searchInput = document.getElementById('global-search-input');
        this.searchModalInput = document.getElementById('search-modal-input');
        this.searchOverlay = document.getElementById('search-overlay');
        this.searchResults = document.getElementById('search-results');
        this.searchLoading = document.getElementById('search-loading');
        this.searchEmpty = document.getElementById('search-empty');
        this.searchSuggestions = document.getElementById('search-suggestions');
    }

    addSearchStyles() {
        if (document.getElementById('global-search-styles')) return;

        const styles = `
            <style id="global-search-styles">
                .global-search-container {
                    position: relative;
                    margin: 0 2rem;
                    flex-grow: 1;
                    max-width: 400px;
                }

                .search-input-wrapper {
                    position: relative;
                    display: flex;
                    align-items: center;
                    background: rgba(255, 255, 255, 0.1);
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    border-radius: 12px;
                    transition: all 0.3s ease;
                    backdrop-filter: blur(10px);
                }

                .search-input-wrapper:focus-within {
                    background: rgba(255, 255, 255, 0.15);
                    border-color: rgba(255, 255, 255, 0.4);
                    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
                }

                .search-icon {
                    position: absolute;
                    left: 12px;
                    color: rgba(255, 255, 255, 0.6);
                    font-size: 14px;
                    z-index: 1;
                }

                .global-search-input {
                    width: 100%;
                    padding: 10px 12px 10px 36px;
                    background: transparent;
                    border: none;
                    color: white;
                    font-size: 14px;
                    outline: none;
                    border-radius: 12px;
                    padding-right: 60px;
                }

                .global-search-input::placeholder {
                    color: rgba(255, 255, 255, 0.6);
                }

                .search-shortcut {
                    position: absolute;
                    right: 8px;
                    background: rgba(255, 255, 255, 0.1);
                    color: rgba(255, 255, 255, 0.8);
                    font-size: 11px;
                    padding: 4px 6px;
                    border-radius: 4px;
                    font-family: monospace;
                    font-weight: 500;
                }

                .search-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.6);
                    backdrop-filter: blur(5px);
                    z-index: 10000;
                    display: none;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }

                .search-overlay.show {
                    display: flex;
                    align-items: flex-start;
                    justify-content: center;
                    padding-top: 10vh;
                    opacity: 1;
                }

                .search-modal {
                    background: white;
                    border-radius: 16px;
                    width: 90%;
                    max-width: 600px;
                    max-height: 80vh;
                    display: flex;
                    flex-direction: column;
                    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
                    transform: scale(0.95) translateY(-20px);
                    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
                }

                .search-overlay.show .search-modal {
                    transform: scale(1) translateY(0);
                }

                .search-modal-header {
                    padding: 20px;
                    border-bottom: 1px solid #e5e7eb;
                }

                .search-modal-input-wrapper {
                    display: flex;
                    align-items: center;
                    background: #f9fafb;
                    border: 2px solid #e5e7eb;
                    border-radius: 12px;
                    transition: all 0.3s ease;
                }

                .search-modal-input-wrapper:focus-within {
                    border-color: var(--cor-vibrante);
                    background: white;
                    box-shadow: 0 0 0 3px rgba(0, 180, 216, 0.1);
                }

                .search-modal-input-wrapper i {
                    margin-left: 12px;
                    color: #6b7280;
                    font-size: 16px;
                }

                .search-modal-input {
                    flex: 1;
                    padding: 12px 16px;
                    background: transparent;
                    border: none;
                    font-size: 16px;
                    color: #1f2937;
                    outline: none;
                }

                .search-modal-input::placeholder {
                    color: #9ca3af;
                }

                .search-close-btn {
                    background: none;
                    border: none;
                    color: #6b7280;
                    font-size: 16px;
                    cursor: pointer;
                    padding: 8px 12px;
                    border-radius: 6px;
                    transition: all 0.2s ease;
                    margin-right: 8px;
                }

                .search-close-btn:hover {
                    background: #f3f4f6;
                    color: #374151;
                }

                .search-modal-body {
                    flex: 1;
                    overflow: hidden;
                    display: flex;
                    flex-direction: column;
                }

                .search-suggestions,
                .search-results {
                    padding: 12px;
                    overflow-y: auto;
                    flex: 1;
                }

                .search-loading,
                .search-empty {
                    display: none;
                    padding: 60px 20px;
                    text-align: center;
                    color: #6b7280;
                }

                .search-loading.show,
                .search-empty.show {
                    display: block;
                }

                .search-loading i {
                    font-size: 2rem;
                    margin-bottom: 1rem;
                    color: var(--cor-vibrante);
                }

                .search-empty i {
                    font-size: 3rem;
                    margin-bottom: 1rem;
                    opacity: 0.5;
                }

                .suggestion-group {
                    margin-bottom: 1.5rem;
                }

                .suggestion-header {
                    font-size: 12px;
                    font-weight: 600;
                    color: #6b7280;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    margin-bottom: 8px;
                    padding-left: 12px;
                }

                .suggestion-item,
                .search-result-item {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    padding: 12px;
                    border-radius: 8px;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    margin-bottom: 4px;
                }

                .suggestion-item:hover,
                .search-result-item:hover,
                .suggestion-item.selected,
                .search-result-item.selected {
                    background: rgba(0, 180, 216, 0.1);
                    color: var(--cor-vibrante);
                }

                .suggestion-item i,
                .search-result-item i {
                    font-size: 16px;
                    width: 20px;
                    text-align: center;
                    color: var(--cor-vibrante);
                    flex-shrink: 0;
                }

                .search-result-item {
                    border-left: 3px solid transparent;
                }

                .search-result-item.selected {
                    border-left-color: var(--cor-vibrante);
                }

                .result-content {
                    flex: 1;
                    min-width: 0;
                }

                .result-title {
                    font-weight: 600;
                    color: #1f2937;
                    margin-bottom: 2px;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                .result-description {
                    font-size: 13px;
                    color: #6b7280;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                .result-type {
                    font-size: 11px;
                    background: var(--cor-vibrante);
                    color: white;
                    padding: 2px 6px;
                    border-radius: 4px;
                    text-transform: uppercase;
                    font-weight: 500;
                    flex-shrink: 0;
                }

                .search-modal-footer {
                    padding: 12px 20px;
                    border-top: 1px solid #e5e7eb;
                    background: #f9fafb;
                    border-radius: 0 0 16px 16px;
                }

                .search-tips {
                    display: flex;
                    gap: 16px;
                    font-size: 12px;
                    color: #6b7280;
                    align-items: center;
                    justify-content: center;
                }

                .search-tips kbd {
                    background: #e5e7eb;
                    color: #374151;
                    padding: 2px 4px;
                    border-radius: 3px;
                    font-size: 11px;
                    font-family: monospace;
                    margin: 0 1px;
                }

                .load-more-btn {
                    width: 100%;
                    background: transparent;
                    border: 2px dashed #d1d5db;
                    color: #6b7280;
                    padding: 12px;
                    border-radius: 8px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 500;
                    transition: all 0.2s ease;
                    margin-top: 12px;
                }

                .load-more-btn:hover {
                    border-color: var(--cor-vibrante);
                    color: var(--cor-vibrante);
                    background: rgba(0, 180, 216, 0.05);
                }


                /* Mobile responsive */
                @media (max-width: 768px) {
                    .global-search-container {
                        display: none; /* Esconder na navbar mobile */
                    }

                    .search-overlay.show {
                        padding-top: 5vh;
                    }

                    .search-modal {
                        width: 95%;
                        max-height: 90vh;
                    }

                    .search-tips {
                        flex-wrap: wrap;
                        gap: 8px;
                    }
                }
            </style>
        `;

        document.head.insertAdjacentHTML('beforeend', styles);
    }

    bindEvents() {
        // Focar input principal abre modal
        this.searchInput?.addEventListener('focus', (e) => {
            e.preventDefault();
            this.openSearchModal();
        });

        // Click no input principal abre modal
        this.searchInput?.addEventListener('click', (e) => {
            e.preventDefault();
            this.openSearchModal();
        });

        // Busca no modal
        this.searchModalInput?.addEventListener('input', (e) => {
            this.handleSearch(e.target.value);
        });

        // Fechar modal
        document.getElementById('search-close-btn')?.addEventListener('click', () => {
            this.closeSearchModal();
        });

        // Fechar modal clicando no overlay
        this.searchOverlay?.addEventListener('click', (e) => {
            if (e.target === this.searchOverlay) {
                this.closeSearchModal();
            }
        });

        // Sugestões
        this.searchSuggestions?.addEventListener('click', (e) => {
            const suggestionItem = e.target.closest('.suggestion-item');
            if (suggestionItem) {
                const query = suggestionItem.dataset.query;
                this.searchModalInput.value = query;
                this.handleSearch(query);
            }
        });

        // Navegação por teclado no modal
        this.searchModalInput?.addEventListener('keydown', (e) => {
            this.handleKeyNavigation(e);
        });

        // Clicks nos resultados
        this.searchResults?.addEventListener('click', (e) => {
            const resultItem = e.target.closest('.search-result-item');
            if (resultItem) {
                this.handleResultClick(resultItem);
            }
        });
    }

    bindKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl+K ou Cmd+K para abrir busca
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.openSearchModal();
            }

            // ESC para fechar
            if (e.key === 'Escape' && this.searchOverlay?.classList.contains('show')) {
                this.closeSearchModal();
            }
        });
    }

    openSearchModal() {
        this.searchOverlay?.classList.add('show');
        this.searchModalInput?.focus();
        this.showSuggestions();
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }

    closeSearchModal() {
        this.searchOverlay?.classList.remove('show');
        this.searchModalInput.value = '';
        this.currentQuery = '';
        this.currentPage = 1;
        this.showSuggestions();
        
        // Restore body scroll
        document.body.style.overflow = '';
    }

    showSuggestions() {
        this.searchSuggestions.style.display = 'block';
        this.searchResults.style.display = 'none';
        this.searchLoading.classList.remove('show');
        this.searchEmpty.classList.remove('show');
    }

    showResults() {
        this.searchSuggestions.style.display = 'none';
        this.searchResults.style.display = 'block';
        this.searchLoading.classList.remove('show');
        this.searchEmpty.classList.remove('show');
    }

    showLoading() {
        this.searchSuggestions.style.display = 'none';
        this.searchResults.style.display = 'none';
        this.searchLoading.classList.add('show');
        this.searchEmpty.classList.remove('show');
    }

    showEmpty() {
        this.searchSuggestions.style.display = 'none';
        this.searchResults.style.display = 'none';
        this.searchLoading.classList.remove('show');
        this.searchEmpty.classList.add('show');
    }

    handleSearch(query) {
        query = query.trim();
        
        if (query.length < 2) {
            this.showSuggestions();
            return;
        }

        if (query === this.currentQuery) return;
        
        this.currentQuery = query;
        this.currentPage = 1;

        // Debounce
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }

        this.searchTimeout = setTimeout(() => {
            this.performSearch(query);
        }, 300);
    }

    async performSearch(query, page = 1) {
        if (this.isLoading) return;

        this.isLoading = true;
        
        try {
            const cacheKey = `${query}-${page}`;
            
            // Verificar cache
            if (this.cache.has(cacheKey) && page === 1) {
                this.renderResults(this.cache.get(cacheKey), page === 1);
                this.isLoading = false;
                return;
            }

            this.showLoading();

            const response = await fetch(`/polis/api/search.php?q=${encodeURIComponent(query)}&page=${page}&per_page=10`);
            
            if (!response.ok) {
                throw new Error('Falha na busca');
            }

            const data = await response.json();
            
            // Cache apenas primeira página
            if (page === 1) {
                this.cache.set(cacheKey, data);
            }

            this.renderResults(data, page === 1);

        } catch (error) {
            console.error('Erro na busca:', error);
            if (window.toastSystem) {
                window.toastSystem.error('Erro', 'Falha ao realizar busca');
            }
            this.showEmpty();
        } finally {
            this.isLoading = false;
        }
    }

    renderResults(data, clearResults = true) {
        if (clearResults) {
            this.searchResults.innerHTML = '';
        }

        if (!data.data || data.data.length === 0) {
            this.showEmpty();
            return;
        }

        this.showResults();

        const resultsHTML = data.data.map(item => `
            <div class="search-result-item" data-type="${item.type}" data-id="${item.id}">
                <i class="${item.icon}"></i>
                <div class="result-content">
                    <div class="result-title">${this.highlightMatch(item.title, this.currentQuery)}</div>
                    <div class="result-description">${this.highlightMatch(item.description, this.currentQuery)}</div>
                </div>
                <div class="result-type">${this.getTypeLabel(item.type)}</div>
            </div>
        `).join('');

        if (clearResults) {
            this.searchResults.innerHTML = resultsHTML;
        } else {
            this.searchResults.insertAdjacentHTML('beforeend', resultsHTML);
        }

        // Botão carregar mais
        if (data.pagination.has_next) {
            const loadMoreBtn = document.createElement('button');
            loadMoreBtn.className = 'load-more-btn';
            loadMoreBtn.innerHTML = '<i class="fas fa-chevron-down"></i> Carregar mais resultados';
            loadMoreBtn.onclick = () => {
                loadMoreBtn.remove();
                this.performSearch(this.currentQuery, this.currentPage + 1);
                this.currentPage++;
            };
            this.searchResults.appendChild(loadMoreBtn);
        }
    }

    highlightMatch(text, query) {
        if (!query || !text) return text;
        
        const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return text.replace(regex, '<mark style="background: yellow; padding: 0 2px; border-radius: 2px;">$1</mark>');
    }

    getTypeLabel(type) {
        const labels = {
            'cliente': 'Cliente',
            'colaborador': 'Colaborador',
            'projeto': 'Projeto',
            'evento': 'Evento'
        };
        return labels[type] || type;
    }

    handleKeyNavigation(e) {
        const items = this.searchResults.querySelectorAll('.search-result-item');
        let currentIndex = -1;
        
        // Encontrar item atualmente selecionado
        items.forEach((item, index) => {
            if (item.classList.contains('selected')) {
                currentIndex = index;
            }
        });

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            const nextIndex = currentIndex + 1 < items.length ? currentIndex + 1 : 0;
            this.selectResultItem(items, nextIndex);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            const prevIndex = currentIndex - 1 >= 0 ? currentIndex - 1 : items.length - 1;
            this.selectResultItem(items, prevIndex);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (currentIndex >= 0) {
                this.handleResultClick(items[currentIndex]);
            }
        }
    }

    selectResultItem(items, index) {
        // Remove seleção atual
        items.forEach(item => item.classList.remove('selected'));
        
        // Adiciona nova seleção
        if (items[index]) {
            items[index].classList.add('selected');
            items[index].scrollIntoView({ block: 'nearest' });
        }
    }

    handleResultClick(resultItem) {
        const type = resultItem.dataset.type;
        const id = resultItem.dataset.id;
        
        // Definir URLs baseado no tipo
        const urls = {
            'cliente': `/polis/listas/lista_clientes.php?id=${id}`,
            'colaborador': `/polis/listas/lista_colaboradores.php?id=${id}`,
            'projeto': `/polis/listas/lista_projetos.php?id=${id}`
        };

        const url = urls[type];
        if (url) {
            this.closeSearchModal();
            window.location.href = url;
        }
    }

    // Limpar cache periodicamente
    clearCache() {
        this.cache.clear();
    }
}

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
    window.globalSearch = new GlobalSearch();
    
    // Limpar cache a cada 10 minutos
    setInterval(() => {
        window.globalSearch.clearCache();
    }, 10 * 60 * 1000);
    
    console.log('Global Search System initialized');
});

// Disponibilizar globalmente
window.GlobalSearch = GlobalSearch;