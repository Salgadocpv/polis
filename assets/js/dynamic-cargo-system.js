/**
 * Sistema de Cargos Dinâmicos - Polis Engenharia
 * Integração com a API de valores fixos para gerenciar cargos de colaboradores
 */

class DynamicCargoSystem {
    constructor() {
        this.baseUrl = '/Polis/api/valores_fixos.php';
        this.cache = new Map();
        this.init();
    }

    init() {
        this.loadCargos();
    }

    async fetchCargos() {
        const cacheKey = 'cargos';
        
        if (this.cache.has(cacheKey)) {
            return this.cache.get(cacheKey);
        }

        try {
            const url = `${this.baseUrl}?tipo=cargo`;
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            this.cache.set(cacheKey, data);
            return data;
        } catch (error) {
            console.error('Erro ao buscar cargos:', error);
            if (typeof Toast !== 'undefined') {
                Toast.error('Erro', 'Não foi possível carregar os cargos');
            }
            return [];
        }
    }

    async loadCargos() {
        const cargoElement = document.getElementById('cargo');
        if (!cargoElement) return;

        try {
            const cargos = await this.fetchCargos();
            this.populateCargoSelect(cargoElement, cargos);
        } catch (error) {
            console.error('Erro ao carregar cargos:', error);
        }
    }

    populateCargoSelect(selectElement, cargos) {
        // Guarda o valor atualmente selecionado (útil para edições)
        const currentValue = selectElement.value;
        
        // Limpa o select
        selectElement.innerHTML = '<option value="">Selecione um Cargo</option>';
        
        // Adiciona as opções de cargo
        if (Array.isArray(cargos) && cargos.length > 0) {
            cargos.forEach(cargo => {
                const option = document.createElement('option');
                option.value = cargo.valor;
                option.textContent = cargo.valor;
                selectElement.appendChild(option);
            });
            
            // Restaura o valor selecionado se existia
            if (currentValue) {
                selectElement.value = currentValue;
            }
            
            selectElement.disabled = false;
        } else {
            // Se não há cargos, adiciona opção informativa
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'Nenhum cargo cadastrado - Configure na página de Setup';
            selectElement.appendChild(option);
            selectElement.disabled = true;
        }
    }

    // Converte campo de input para select
    convertToSelect(fieldId) {
        const inputElement = document.getElementById(fieldId);
        if (!inputElement || inputElement.tagName === 'SELECT') return inputElement;

        const selectElement = document.createElement('select');
        selectElement.id = inputElement.id;
        selectElement.name = inputElement.name;
        selectElement.className = inputElement.className;
        selectElement.required = inputElement.required;
        
        const label = document.querySelector(`label[for="${fieldId}"]`);
        if (label) {
            selectElement.setAttribute('aria-label', label.textContent);
        }

        // Preserva o valor atual se existir
        const currentValue = inputElement.value;
        
        inputElement.parentNode.replaceChild(selectElement, inputElement);
        
        // Se havia um valor, armazena temporariamente
        if (currentValue) {
            selectElement.setAttribute('data-initial-value', currentValue);
        }
        
        return selectElement;
    }

    // Método para atualizar os cargos (útil quando a página de setup é alterada)
    async refreshCargos() {
        this.cache.delete('cargos');
        await this.loadCargos();
    }

    // Método para ser chamado quando a página de setup for atualizada
    onSetupUpdate() {
        this.refreshCargos();
    }

    // Inicialização automática dos campos de cargo
    initializeCargoField() {
        const cargoSelect = this.convertToSelect('cargo');
        if (cargoSelect) {
            this.loadCargos();
        }
    }
}

// Auto-inicialização quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    // Verifica se estamos na página de cadastro/edição de colaborador
    if (document.getElementById('cargo')) {
        const cargoSystem = new DynamicCargoSystem();
        cargoSystem.initializeCargoField();
        
        // Expõe globalmente para uso em outras partes do código se necessário
        window.DynamicCargoSystem = cargoSystem;
    }
});