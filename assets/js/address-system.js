/**
 * Sistema de Endereços - Polis Engenharia
 * Integração com API de endereços brasileiros
 * Suporte a cascata: Estado -> Cidade -> Bairro -> Rua + consulta por CEP
 */

class AddressSystem {
    constructor() {
        this.baseUrl = '/Polis/api/enderecos.php';
        this.cache = new Map();
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadEstados();
    }

    async fetchData(action, params = {}) {
        const cacheKey = `${action}-${JSON.stringify(params)}`;
        
        if (this.cache.has(cacheKey)) {
            return this.cache.get(cacheKey);
        }

        try {
            const url = new URL(this.baseUrl, window.location.origin);
            url.searchParams.append('action', action);
            
            Object.keys(params).forEach(key => {
                if (params[key]) {
                    url.searchParams.append(key, params[key]);
                }
            });

            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            this.cache.set(cacheKey, data);
            return data;
        } catch (error) {
            console.error(`Erro ao buscar ${action}:`, error);
            if (typeof Toast !== 'undefined') {
                Toast.error('Erro', `Não foi possível carregar ${action}`);
            }
            return [];
        }
    }

    setupEventListeners() {
        const estadoSelect = document.getElementById('estado');
        const cidadeSelect = document.getElementById('cidade');
        const bairroSelect = document.getElementById('bairro');
        const ruaSelect = document.getElementById('rua');
        const cepInput = document.getElementById('cep');

        if (estadoSelect) {
            estadoSelect.addEventListener('change', (e) => {
                this.loadCidades(e.target.value);
                this.clearDependentFields(['cidade', 'bairro', 'rua']);
            });
        }

        if (cidadeSelect) {
            cidadeSelect.addEventListener('change', (e) => {
                this.loadBairros(e.target.value);
                this.clearDependentFields(['bairro', 'rua']);
            });
        }

        if (bairroSelect) {
            bairroSelect.addEventListener('change', (e) => {
                this.loadRuas(e.target.value);
                this.clearDependentFields(['rua']);
            });
        }

        if (cepInput) {
            let timeout;
            cepInput.addEventListener('input', (e) => {
                clearTimeout(timeout);
                const cep = e.target.value.replace(/\D/g, '');
                
                // Aplica máscara do CEP
                if (cep.length <= 8) {
                    e.target.value = cep.replace(/(\d{5})(\d)/, '$1-$2');
                }
                
                if (cep.length === 8) {
                    timeout = setTimeout(() => {
                        this.loadAddressByCEP(cep);
                    }, 500);
                }
            });
        }
    }

    async loadEstados() {
        const estadoElement = document.getElementById('estado');
        if (!estadoElement) return;

        try {
            const estados = await this.fetchData('estados');
            this.populateSelect(estadoElement, estados, 'sigla', 'nome', 'Selecione o Estado');
        } catch (error) {
            console.error('Erro ao carregar estados:', error);
        }
    }

    async loadCidades(estado) {
        const cidadeElement = document.getElementById('cidade');
        if (!cidadeElement || !estado) return;

        this.setLoadingState(cidadeElement, 'Carregando cidades...');
        
        try {
            const cidades = await this.fetchData('cidades', { estado });
            this.populateSelect(cidadeElement, cidades, null, null, 'Selecione a Cidade');
        } catch (error) {
            console.error('Erro ao carregar cidades:', error);
        } finally {
            this.removeLoadingState(cidadeElement);
        }
    }

    async loadBairros(cidade) {
        const bairroElement = document.getElementById('bairro');
        if (!bairroElement || !cidade) return;

        this.setLoadingState(bairroElement, 'Carregando bairros...');
        
        try {
            const bairros = await this.fetchData('bairros', { cidade });
            this.populateSelect(bairroElement, bairros, null, null, 'Selecione o Bairro');
        } catch (error) {
            console.error('Erro ao carregar bairros:', error);
        } finally {
            this.removeLoadingState(bairroElement);
        }
    }

    async loadRuas(bairro) {
        const ruaElement = document.getElementById('rua');
        if (!ruaElement || !bairro) return;

        this.setLoadingState(ruaElement, 'Carregando ruas...');
        
        try {
            const ruas = await this.fetchData('ruas', { bairro });
            this.populateSelect(ruaElement, ruas, null, null, 'Selecione a Rua');
        } catch (error) {
            console.error('Erro ao carregar ruas:', error);
        } finally {
            this.removeLoadingState(ruaElement);
        }
    }

    async loadAddressByCEP(cep) {
        const cepInput = document.getElementById('cep');
        if (!cepInput) return;

        this.setLoadingState(cepInput, null, 'Buscando CEP...');
        
        try {
            const address = await this.fetchData('cep', { cep });
            
            if (address && !address.error) {
                await this.fillAddressFields(address);
                if (typeof Toast !== 'undefined') {
                    Toast.success('CEP encontrado', 'Endereço preenchido automaticamente');
                }
            } else {
                if (typeof Toast !== 'undefined') {
                    Toast.warning('CEP não encontrado', 'Preencha os campos manualmente');
                }
            }
        } catch (error) {
            console.error('Erro ao buscar CEP:', error);
        } finally {
            this.removeLoadingState(cepInput);
        }
    }

    async fillAddressFields(address) {
        const { estado, cidade, bairro, rua } = address;
        
        // Preenche estado e carrega cidades
        if (estado) {
            const estadoElement = document.getElementById('estado');
            if (estadoElement) {
                estadoElement.value = estado;
                await this.loadCidades(estado);
            }
        }
        
        // Pequeno delay para aguardar carregamento das cidades
        setTimeout(async () => {
            if (cidade) {
                const cidadeElement = document.getElementById('cidade');
                if (cidadeElement) {
                    cidadeElement.value = cidade;
                    await this.loadBairros(cidade);
                }
            }
            
            setTimeout(async () => {
                if (bairro) {
                    const bairroElement = document.getElementById('bairro');
                    if (bairroElement) {
                        bairroElement.value = bairro;
                        await this.loadRuas(bairro);
                    }
                }
                
                setTimeout(() => {
                    if (rua) {
                        const ruaElement = document.getElementById('rua');
                        if (ruaElement) {
                            ruaElement.value = rua;
                        }
                    }
                }, 300);
            }, 300);
        }, 300);
    }

    populateSelect(selectElement, options, valueKey = null, textKey = null, placeholder = 'Selecione') {
        selectElement.innerHTML = `<option value="">${placeholder}</option>`;
        
        if (Array.isArray(options)) {
            options.forEach(option => {
                const optionElement = document.createElement('option');
                
                if (typeof option === 'string') {
                    optionElement.value = option;
                    optionElement.textContent = option;
                } else {
                    optionElement.value = valueKey ? option[valueKey] : option;
                    optionElement.textContent = textKey ? option[textKey] : option;
                }
                
                selectElement.appendChild(optionElement);
            });
        }
        
        selectElement.disabled = options.length === 0;
    }

    clearDependentFields(fieldIds) {
        fieldIds.forEach(fieldId => {
            const element = document.getElementById(fieldId);
            if (element) {
                if (element.tagName === 'SELECT') {
                    element.innerHTML = '<option value="">Selecione</option>';
                    element.disabled = true;
                } else {
                    element.value = '';
                }
            }
        });
    }

    setLoadingState(element, placeholder = null, title = null) {
        element.classList.add('loading');
        if (element.tagName === 'SELECT' && placeholder) {
            element.innerHTML = `<option value="">${placeholder}</option>`;
            element.disabled = true;
        }
        if (title) {
            element.title = title;
        }
    }

    removeLoadingState(element) {
        element.classList.remove('loading');
        element.disabled = false;
        element.title = '';
    }

    // Converte campos de input para select quando necessário
    convertToSelect(fieldId) {
        const inputElement = document.getElementById(fieldId);
        if (!inputElement || inputElement.tagName === 'SELECT') return;

        const selectElement = document.createElement('select');
        selectElement.id = inputElement.id;
        selectElement.name = inputElement.name;
        selectElement.className = inputElement.className;
        selectElement.required = inputElement.required;
        
        const label = document.querySelector(`label[for="${fieldId}"]`);
        if (label) {
            selectElement.setAttribute('aria-label', label.textContent);
        }

        inputElement.parentNode.replaceChild(selectElement, inputElement);
        return selectElement;
    }

    // Inicialização automática dos selects de endereço
    initializeAddressFields() {
        const fieldsToConvert = ['estado', 'cidade', 'bairro', 'rua'];
        
        fieldsToConvert.forEach(fieldId => {
            this.convertToSelect(fieldId);
        });
        
        // Adiciona estilos para loading
        const style = document.createElement('style');
        style.textContent = `
            .loading {
                background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>') !important;
                background-repeat: no-repeat !important;
                background-position: right 8px center !important;
                background-size: 16px 16px !important;
                animation: spin 1s linear infinite !important;
            }
            
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    }
}

// Auto-inicialização quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    // Verifica se estamos na página de cadastro de cliente
    if (document.getElementById('estado') && document.getElementById('cidade')) {
        const addressSystem = new AddressSystem();
        addressSystem.initializeAddressFields();
        
        // Expõe globalmente para uso em outras partes do código se necessário
        window.AddressSystem = addressSystem;
    }
});