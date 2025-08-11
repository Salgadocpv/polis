/**
 * Sistema de Exportação Excel para Polis Engenharia
 * Utiliza SheetJS (xlsx) para geração de planilhas
 */

class ExcelExporter {
    constructor() {
        this.isLibraryLoaded = false;
        this.init();
    }

    async init() {
        await this.loadSheetJSLibrary();
        this.addExportButtons();
        this.addExportStyles();
    }

    async loadSheetJSLibrary() {
        if (this.isLibraryLoaded || window.XLSX) {
            this.isLibraryLoaded = true;
            return;
        }

        try {
            // Carregar SheetJS via CDN
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js';
            script.crossOrigin = 'anonymous';
            
            await new Promise((resolve, reject) => {
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });

            this.isLibraryLoaded = true;
            console.log('SheetJS library loaded successfully');
        } catch (error) {
            console.error('Failed to load SheetJS library:', error);
            if (window.toastSystem) {
                window.toastSystem.error('Erro', 'Falha ao carregar biblioteca de exportação');
            }
        }
    }

    addExportButtons() {
        // Verificar se a página já tem page-header (evita duplicação)
        const path = window.location.pathname;
        const skipPages = ['lista_clientes.php', 'lista_colaboradores.php', 'lista_projetos.php'];
        
        if (skipPages.some(page => path.includes(page))) {
            // Páginas de lista usam page-header, não adicionar botões automáticos
            return;
        }
        
        // Aguardar que as listas carreguem
        setTimeout(() => {
            this.addExportButtonsToTables();
        }, 1000);

        // Observer para detectar novas tabelas
        const observer = new MutationObserver(() => {
            this.addExportButtonsToTables();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    addExportButtonsToTables() {
        // Procurar por containers de listas
        const containers = [
            '.main-content',
            '.accordion-body',
            '.card',
            '[data-list-container]'
        ];

        containers.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(element => {
                if (element.dataset.exportAdded) return;
                
                const table = element.querySelector('table');
                const heading = element.querySelector('h1, h2, h3');
                
                if (table && heading && !element.querySelector('.export-excel-btn')) {
                    this.addExportButton(element, heading, table);
                    element.dataset.exportAdded = 'true';
                }
            });
        });
    }

    addExportButton(container, heading, table) {
        const exportBtn = document.createElement('button');
        exportBtn.className = 'export-excel-btn';
        exportBtn.innerHTML = `
            <i class="fas fa-file-excel"></i>
            <span>Exportar Excel</span>
        `;

        exportBtn.addEventListener('click', () => {
            this.exportTableToExcel(table, this.getTableTitle(heading));
        });

        // Inserir após o heading ou no canto superior direito
        const headerContainer = heading.parentElement;
        if (headerContainer.classList.contains('card') || headerContainer.style.display === 'flex') {
            // Header flexível - adicionar à direita
            headerContainer.style.display = 'flex';
            headerContainer.style.justifyContent = 'space-between';
            headerContainer.style.alignItems = 'center';
            headerContainer.appendChild(exportBtn);
        } else {
            // Inserir após o heading
            heading.insertAdjacentElement('afterend', exportBtn);
        }
    }

    addExportStyles() {
        if (document.getElementById('excel-export-styles')) return;

        const styles = `
            <style id="excel-export-styles">
                .export-excel-btn {
                    background: #217346;
                    color: white;
                    border: none;
                    padding: 8px 16px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 500;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    transition: all 0.3s ease;
                    margin: 8px 0;
                    box-shadow: 0 2px 4px rgba(33, 115, 70, 0.2);
                }

                .export-excel-btn:hover {
                    background: #1e6340;
                    transform: translateY(-1px);
                    box-shadow: 0 4px 12px rgba(33, 115, 70, 0.3);
                }

                .export-excel-btn:active {
                    transform: translateY(0);
                }

                .export-excel-btn.loading {
                    opacity: 0.7;
                    cursor: not-allowed;
                }

                .export-excel-btn.loading i {
                    animation: spin 1s linear infinite;
                }

                .export-advanced-btn {
                    background: #0078d4;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 8px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 500;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    transition: all 0.3s ease;
                    margin: 8px 0;
                    box-shadow: 0 2px 6px rgba(0, 120, 212, 0.2);
                }

                .export-advanced-btn:hover {
                    background: #106ebe;
                    transform: translateY(-1px);
                    box-shadow: 0 4px 12px rgba(0, 120, 212, 0.3);
                }

                .export-modal {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    display: none;
                    align-items: center;
                    justify-content: center;
                    z-index: 10000;
                }

                .export-modal-content {
                    background: white;
                    border-radius: 12px;
                    padding: 2rem;
                    width: 90%;
                    max-width: 500px;
                    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
                }

                .export-modal h3 {
                    margin: 0 0 1.5rem 0;
                    color: var(--cor-principal);
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                }

                .export-options {
                    display: flex;
                    flex-direction: column;
                    gap: 1rem;
                    margin-bottom: 2rem;
                }

                .export-option {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                }

                .export-option input[type="checkbox"] {
                    width: 18px;
                    height: 18px;
                }

                .export-option label {
                    font-size: 14px;
                    color: #374151;
                    cursor: pointer;
                }

                .export-actions {
                    display: flex;
                    gap: 1rem;
                    justify-content: flex-end;
                }

                .export-actions button {
                    padding: 0.75rem 1.5rem;
                    border-radius: 6px;
                    font-size: 14px;
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.2s ease;
                }

                .export-actions .cancel-btn {
                    background: #f3f4f6;
                    color: #374151;
                    border: 1px solid #d1d5db;
                }

                .export-actions .cancel-btn:hover {
                    background: #e5e7eb;
                }

                .export-actions .confirm-btn {
                    background: #217346;
                    color: white;
                    border: none;
                }

                .export-actions .confirm-btn:hover {
                    background: #1e6340;
                }

                @keyframes spin {
                    to { transform: rotate(360deg); }
                }

            </style>
        `;

        document.head.insertAdjacentHTML('beforeend', styles);
    }

    getTableTitle(heading) {
        if (!heading) return 'Dados';
        
        const text = heading.textContent.trim();
        return text || 'Dados';
    }

    async exportTableToExcel(table, title = 'Dados', options = {}) {
        if (!this.isLibraryLoaded || !window.XLSX) {
            if (window.toastSystem) {
                window.toastSystem.error('Erro', 'Biblioteca de exportação não carregada');
            }
            return;
        }

        try {
            // Mostrar loading
            const exportBtn = document.querySelector('.export-excel-btn');
            if (exportBtn) {
                exportBtn.classList.add('loading');
            }

            // Extrair dados da tabela
            const data = this.extractTableData(table, options);
            
            if (data.length === 0) {
                if (window.toastSystem) {
                    window.toastSystem.warning('Aviso', 'Nenhum dado encontrado para exportar');
                }
                return;
            }

            // Criar workbook
            const wb = window.XLSX.utils.book_new();
            
            // Criar worksheet
            const ws = window.XLSX.utils.json_to_sheet(data);
            
            // Aplicar estilos e formatação
            this.applyWorksheetFormatting(ws, data, title);
            
            // Adicionar worksheet ao workbook
            window.XLSX.utils.book_append_sheet(wb, ws, title);
            
            // Gerar nome do arquivo
            const filename = this.generateFilename(title);
            
            // Salvar arquivo
            window.XLSX.writeFile(wb, filename);
            
            if (window.toastSystem) {
                window.toastSystem.success('Exportado', `Arquivo ${filename} salvo com sucesso!`);
            }

        } catch (error) {
            console.error('Erro ao exportar Excel:', error);
            if (window.toastSystem) {
                window.toastSystem.error('Erro', 'Falha ao exportar para Excel');
            }
        } finally {
            // Remover loading
            const exportBtn = document.querySelector('.export-excel-btn');
            if (exportBtn) {
                exportBtn.classList.remove('loading');
            }
        }
    }

    extractTableData(table, options = {}) {
        const data = [];
        const rows = table.querySelectorAll('tbody tr');
        
        if (rows.length === 0) {
            return data;
        }

        // Obter headers
        const headers = [];
        const headerCells = table.querySelectorAll('thead th, thead td');
        headerCells.forEach((cell, index) => {
            const text = cell.textContent.trim();
            if (text && text !== 'Ações') { // Pular coluna de ações
                headers.push({
                    index: index,
                    name: text,
                    key: this.generateColumnKey(text)
                });
            }
        });

        // Extrair dados das linhas
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const rowData = {};

            headers.forEach(header => {
                if (cells[header.index]) {
                    let cellValue = cells[header.index].textContent.trim();
                    
                    // Limpar e formatar valores
                    cellValue = this.cleanCellValue(cellValue, header.name);
                    rowData[header.name] = cellValue;
                }
            });

            if (Object.keys(rowData).length > 0) {
                data.push(rowData);
            }
        });

        return data;
    }

    generateColumnKey(headerText) {
        return headerText
            .toLowerCase()
            .replace(/[^a-z0-9]/g, '_')
            .replace(/_+/g, '_')
            .replace(/^_|_$/g, '');
    }

    cleanCellValue(value, headerName) {
        // Remove espaços extras
        value = value.replace(/\s+/g, ' ').trim();
        
        // Tratar valores monetários
        if (headerName.toLowerCase().includes('valor') || 
            headerName.toLowerCase().includes('orçamento') ||
            value.includes('R$')) {
            const numericValue = value.replace(/[^\d,.-]/g, '').replace(',', '.');
            const parsed = parseFloat(numericValue);
            return isNaN(parsed) ? value : parsed;
        }
        
        // Tratar datas
        if (headerName.toLowerCase().includes('data') && 
            /\d{2}\/\d{2}\/\d{4}/.test(value)) {
            return value; // Manter formato brasileiro
        }
        
        // Tratar números
        if (/^\d+$/.test(value)) {
            return parseInt(value);
        }
        
        return value;
    }

    applyWorksheetFormatting(ws, data, title) {
        if (!data.length) return;

        // Auto-ajustar largura das colunas
        const colWidths = [];
        const headers = Object.keys(data[0]);
        
        headers.forEach((header, index) => {
            let maxWidth = header.length;
            
            data.forEach(row => {
                const cellValue = String(row[header] || '');
                maxWidth = Math.max(maxWidth, cellValue.length);
            });
            
            colWidths[index] = { width: Math.min(maxWidth + 2, 50) };
        });
        
        ws['!cols'] = colWidths;
        
        // Configurar range
        const range = window.XLSX.utils.decode_range(ws['!ref']);
        
        // Aplicar formatação aos headers (primeira linha)
        for (let col = range.s.c; col <= range.e.c; col++) {
            const cellAddress = window.XLSX.utils.encode_cell({ r: 0, c: col });
            if (ws[cellAddress]) {
                ws[cellAddress].s = {
                    font: { bold: true, color: { rgb: "FFFFFF" } },
                    fill: { fgColor: { rgb: "217346" } },
                    alignment: { horizontal: "center" }
                };
            }
        }
    }

    generateFilename(title) {
        const date = new Date().toISOString().split('T')[0];
        const time = new Date().toLocaleTimeString('pt-BR', { 
            hour12: false 
        }).replace(/:/g, '-');
        
        const safeName = title
            .replace(/[^a-z0-9\s]/gi, '')
            .replace(/\s+/g, '_')
            .toLowerCase();
        
        return `polis_${safeName}_${date}_${time}.xlsx`;
    }

    // Exportação avançada com opções
    async exportAdvanced(dataSource, title, options = {}) {
        if (!this.isLibraryLoaded || !window.XLSX) {
            await this.loadSheetJSLibrary();
        }

        return new Promise((resolve, reject) => {
            this.showAdvancedExportModal(dataSource, title, options, resolve, reject);
        });
    }

    showAdvancedExportModal(dataSource, title, options, resolve, reject) {
        // Criar modal de exportação avançada
        const modal = document.createElement('div');
        modal.className = 'export-modal';
        modal.style.display = 'flex';
        
        modal.innerHTML = `
            <div class="export-modal-content">
                <h3>
                    <i class="fas fa-file-excel"></i>
                    Exportar ${title}
                </h3>
                
                <div class="export-options">
                    <div class="export-option">
                        <input type="checkbox" id="include-headers" checked>
                        <label for="include-headers">Incluir cabeçalhos</label>
                    </div>
                    
                    <div class="export-option">
                        <input type="checkbox" id="auto-filter" checked>
                        <label for="auto-filter">Ativar filtros automáticos</label>
                    </div>
                    
                    <div class="export-option">
                        <input type="checkbox" id="freeze-header" checked>
                        <label for="freeze-header">Congelar linha de cabeçalho</label>
                    </div>
                    
                    <div class="export-option">
                        <input type="checkbox" id="format-currency">
                        <label for="format-currency">Formatar valores monetários</label>
                    </div>
                </div>
                
                <div class="export-actions">
                    <button type="button" class="cancel-btn">Cancelar</button>
                    <button type="button" class="confirm-btn">Exportar</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Bind events
        modal.querySelector('.cancel-btn').onclick = () => {
            document.body.removeChild(modal);
            reject(new Error('Cancelado pelo usuário'));
        };

        modal.querySelector('.confirm-btn').onclick = async () => {
            const exportOptions = {
                includeHeaders: modal.querySelector('#include-headers').checked,
                autoFilter: modal.querySelector('#auto-filter').checked,
                freezeHeader: modal.querySelector('#freeze-header').checked,
                formatCurrency: modal.querySelector('#format-currency').checked
            };

            document.body.removeChild(modal);

            try {
                await this.performAdvancedExport(dataSource, title, exportOptions);
                resolve();
            } catch (error) {
                reject(error);
            }
        };

        // Fechar clicando fora
        modal.onclick = (e) => {
            if (e.target === modal) {
                document.body.removeChild(modal);
                reject(new Error('Cancelado pelo usuário'));
            }
        };
    }

    async performAdvancedExport(dataSource, title, options) {
        // Implementar exportação avançada baseada nas opções
        // Este método seria expandido conforme necessário
        console.log('Advanced export:', { dataSource, title, options });
    }

    // Método para exportar dados customizados (não apenas tabelas)
    async exportCustomData(data, title, columnConfig = {}) {
        if (!this.isLibraryLoaded || !window.XLSX) {
            await this.loadSheetJSLibrary();
        }

        try {
            const wb = window.XLSX.utils.book_new();
            const ws = window.XLSX.utils.json_to_sheet(data);
            
            // Aplicar configurações de coluna se fornecidas
            if (Object.keys(columnConfig).length > 0) {
                this.applyColumnConfig(ws, columnConfig);
            }
            
            window.XLSX.utils.book_append_sheet(wb, ws, title);
            
            const filename = this.generateFilename(title);
            window.XLSX.writeFile(wb, filename);
            
            if (window.toastSystem) {
                window.toastSystem.success('Exportado', `${filename} criado com sucesso!`);
            }
            
        } catch (error) {
            console.error('Erro na exportação customizada:', error);
            if (window.toastSystem) {
                window.toastSystem.error('Erro', 'Falha na exportação');
            }
        }
    }

    applyColumnConfig(ws, columnConfig) {
        // Implementar configuração personalizada de colunas
        // Larguras, formatação, etc.
    }
}

// Funções de conveniência globais
window.exportTableToExcel = function(tableSelector, title) {
    const table = document.querySelector(tableSelector);
    if (table && window.excelExporter) {
        window.excelExporter.exportTableToExcel(table, title);
    }
};

window.exportDataToExcel = function(data, title, columnConfig) {
    if (window.excelExporter) {
        window.excelExporter.exportCustomData(data, title, columnConfig);
    }
};

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
    window.excelExporter = new ExcelExporter();
    console.log('Excel Export System initialized');
});

// Disponibilizar globalmente
window.ExcelExporter = ExcelExporter;