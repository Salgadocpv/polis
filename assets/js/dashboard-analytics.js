/**
 * Dashboard Analytics para Polis Engenharia
 */

class DashboardAnalytics {
    constructor() {
        this.charts = {};
        this.data = null;
        this.updateInterval = null;
        this.init();
    }

    async init() {
        try {
            await this.loadData();
            this.setupAnalyticsContainer();
            this.renderCharts();
            this.startAutoUpdate();
            this.bindEvents();
        } catch (error) {
            console.error('Erro ao inicializar analytics:', error);
        }
    }

    async loadData() {
        try {
            const response = await fetch('/polis/api/analytics.php?type=dashboard');
            if (!response.ok) throw new Error('Falha ao carregar dados');
            
            this.data = await response.json();
            console.log('Analytics data loaded:', this.data);
        } catch (error) {
            console.error('Erro ao carregar analytics:', error);
            if (window.toastSystem) {
                window.toastSystem.error('Erro', 'Falha ao carregar dados de analytics');
            }
            throw error;
        }
    }

    setupAnalyticsContainer() {
        // Encontrar onde inserir os gráficos
        const accordionBody = document.querySelector('.accordion-item .accordion-body');
        if (!accordionBody) return;

        // Criar container de analytics se não existir
        let analyticsContainer = document.getElementById('analytics-container');
        if (!analyticsContainer) {
            analyticsContainer = document.createElement('div');
            analyticsContainer.id = 'analytics-container';
            analyticsContainer.className = 'analytics-container';
            
            // Inserir antes das métricas rápidas
            const metricsGrid = accordionBody.querySelector('.dashboard-grid');
            if (metricsGrid) {
                accordionBody.insertBefore(analyticsContainer, metricsGrid);
            } else {
                accordionBody.appendChild(analyticsContainer);
            }
        }

        // HTML do container de analytics
        analyticsContainer.innerHTML = `
            <div class="analytics-header">
                <h2><i class="fas fa-chart-line"></i> Analytics Avançado</h2>
                <div class="analytics-controls">
                    <button class="btn-analytics refresh-btn" onclick="dashboardAnalytics.refresh()">
                        <i class="fas fa-sync-alt"></i> Atualizar
                    </button>
                    <button class="btn-analytics export-btn" onclick="dashboardAnalytics.exportData()">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                </div>
            </div>
            
            <div class="analytics-grid">
                <!-- KPIs Cards -->
                <div class="analytics-section kpi-section">
                    <h3>Indicadores Principais</h3>
                    <div class="kpi-grid" id="kpi-cards"></div>
                </div>
                
                <!-- Gráfico de Status de Projetos -->
                <div class="analytics-section chart-section">
                    <h3>Projetos por Status</h3>
                    <div class="chart-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
                
                <!-- Timeline de Projetos -->
                <div class="analytics-section chart-section">
                    <h3>Timeline de Projetos (6 meses)</h3>
                    <div class="chart-container">
                        <canvas id="timelineChart"></canvas>
                    </div>
                </div>
                
                <!-- Top Clientes -->
                <div class="analytics-section table-section">
                    <h3>Top 5 Clientes</h3>
                    <div class="top-clients-container" id="top-clients"></div>
                </div>
                
                <!-- Métricas Financeiras -->
                <div class="analytics-section financial-section">
                    <h3>Resumo Financeiro</h3>
                    <div class="financial-cards" id="financial-metrics"></div>
                </div>
            </div>
        `;

        // Adicionar estilos se não existirem
        this.addAnalyticsStyles();
    }

    addAnalyticsStyles() {
        if (document.getElementById('analytics-styles')) return;

        const styles = `
            <style id="analytics-styles">
                .analytics-container {
                    background: var(--cor-fundo-card);
                    border-radius: 16px;
                    padding: 2rem;
                    margin-bottom: 2rem;
                    box-shadow: var(--sombra-media);
                }

                .analytics-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 2rem;
                    padding-bottom: 1rem;
                    border-bottom: 2px solid rgba(0, 180, 216, 0.1);
                }

                .analytics-header h2 {
                    color: var(--cor-principal);
                    font-size: 1.8rem;
                    margin: 0;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                }

                .analytics-controls {
                    display: flex;
                    gap: 1rem;
                }

                .btn-analytics {
                    padding: 0.5rem 1rem;
                    background: var(--cor-vibrante);
                    color: white;
                    border: none;
                    border-radius: 8px;
                    cursor: pointer;
                    font-size: 0.9rem;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                }

                .btn-analytics:hover {
                    background: var(--cor-clara);
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(0, 180, 216, 0.3);
                }

                .analytics-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 1.5rem;
                }

                .analytics-section {
                    background: white;
                    border-radius: 12px;
                    padding: 1.5rem;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
                    border: 1px solid rgba(0, 180, 216, 0.1);
                }

                .analytics-section h3 {
                    color: var(--cor-principal);
                    font-size: 1.1rem;
                    margin: 0 0 1rem 0;
                    font-weight: 600;
                }

                .kpi-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 1rem;
                }

                .kpi-card {
                    background: linear-gradient(135deg, var(--cor-vibrante), var(--cor-clara));
                    color: white;
                    padding: 1.5rem;
                    border-radius: 12px;
                    text-align: center;
                    position: relative;
                    overflow: hidden;
                }

                .kpi-card::before {
                    content: '';
                    position: absolute;
                    top: -50%;
                    right: -50%;
                    width: 100%;
                    height: 100%;
                    background: rgba(255, 255, 255, 0.1);
                    border-radius: 50%;
                }

                .kpi-value {
                    font-size: 2rem;
                    font-weight: bold;
                    margin-bottom: 0.5rem;
                }

                .kpi-label {
                    font-size: 0.9rem;
                    opacity: 0.9;
                    margin-bottom: 0.5rem;
                }

                .kpi-change {
                    font-size: 0.8rem;
                    font-weight: 500;
                }

                .kpi-change.positive {
                    color: #4ade80;
                }

                .kpi-change.negative {
                    color: #f87171;
                }

                .chart-container {
                    position: relative;
                    height: 300px;
                    width: 100%;
                }

                .chart-container canvas {
                    max-height: 100%;
                    max-width: 100%;
                }

                .top-clients-container {
                    max-height: 300px;
                    overflow-y: auto;
                }

                .client-item {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 1rem;
                    border: 1px solid #e5e7eb;
                    border-radius: 8px;
                    margin-bottom: 0.5rem;
                    transition: all 0.2s ease;
                }

                .client-item:hover {
                    background: rgba(0, 180, 216, 0.05);
                    border-color: var(--cor-vibrante);
                }

                .client-info h4 {
                    margin: 0;
                    color: var(--cor-principal);
                    font-size: 1rem;
                }

                .client-info p {
                    margin: 0.25rem 0 0 0;
                    font-size: 0.8rem;
                    color: #6b7280;
                }

                .client-stats {
                    text-align: right;
                }

                .client-value {
                    font-weight: bold;
                    color: var(--cor-vibrante);
                    font-size: 1.1rem;
                }

                .client-projects {
                    font-size: 0.8rem;
                    color: #6b7280;
                }

                .financial-cards {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                    gap: 1rem;
                }

                .financial-card {
                    text-align: center;
                    padding: 1rem;
                    border: 2px solid #e5e7eb;
                    border-radius: 8px;
                    transition: all 0.3s ease;
                }

                .financial-card:hover {
                    border-color: var(--cor-vibrante);
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(0, 180, 216, 0.2);
                }

                .financial-value {
                    font-size: 1.5rem;
                    font-weight: bold;
                    color: var(--cor-principal);
                    margin-bottom: 0.5rem;
                }

                .financial-label {
                    font-size: 0.9rem;
                    color: #6b7280;
                }


                /* Responsividade */
                @media (max-width: 768px) {
                    .analytics-container {
                        padding: 1rem;
                    }

                    .analytics-header {
                        flex-direction: column;
                        gap: 1rem;
                        align-items: flex-start;
                    }

                    .analytics-grid {
                        grid-template-columns: 1fr;
                    }

                    .kpi-grid {
                        grid-template-columns: repeat(2, 1fr);
                    }

                    .financial-cards {
                        grid-template-columns: repeat(2, 1fr);
                    }
                }

                /* Loading states */
                .analytics-loading {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 200px;
                    color: var(--cor-vibrante);
                }

                .analytics-loading i {
                    font-size: 2rem;
                    margin-right: 1rem;
                }
            </style>
        `;

        document.head.insertAdjacentHTML('beforeend', styles);
    }

    renderCharts() {
        if (!this.data) return;

        this.renderKPICards();
        this.renderStatusChart();
        this.renderTimelineChart();
        this.renderTopClients();
        this.renderFinancialMetrics();
    }

    renderKPICards() {
        const container = document.getElementById('kpi-cards');
        if (!container || !this.data.metrics) return;

        const kpis = [
            {
                value: this.data.metrics.clientes.total,
                label: 'Total de Clientes',
                change: this.data.metrics.clientes.crescimento,
                icon: 'fas fa-users'
            },
            {
                value: this.data.metrics.projetos.total,
                label: 'Total de Projetos',
                change: null,
                icon: 'fas fa-project-diagram'
            },
            {
                value: this.data.metrics.projetos.ativos,
                label: 'Projetos Ativos',
                change: null,
                icon: 'fas fa-play-circle'
            },
            {
                value: `${this.data.metrics.projetos.taxa_conclusao}%`,
                label: 'Taxa de Conclusão',
                change: null,
                icon: 'fas fa-check-circle'
            }
        ];

        container.innerHTML = kpis.map(kpi => `
            <div class="kpi-card">
                <div class="kpi-icon"><i class="${kpi.icon}"></i></div>
                <div class="kpi-value">${kpi.value}</div>
                <div class="kpi-label">${kpi.label}</div>
                ${kpi.change !== null ? `
                    <div class="kpi-change ${kpi.change >= 0 ? 'positive' : 'negative'}">
                        <i class="fas ${kpi.change >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'}"></i>
                        ${Math.abs(kpi.change)}%
                    </div>
                ` : ''}
            </div>
        `).join('');
    }

    renderStatusChart() {
        // Implementação com Chart.js seria ideal, mas vamos usar uma versão simples
        const container = document.getElementById('statusChart');
        if (!container || !this.data.charts.projetos_status) return;

        const canvas = container;
        const ctx = canvas.getContext('2d');
        
        // Por simplicidade, vamos criar um gráfico de barras básico
        this.drawSimpleBarChart(ctx, this.data.charts.projetos_status);
    }

    drawSimpleBarChart(ctx, data) {
        const canvas = ctx.canvas;
        const width = canvas.width = canvas.offsetWidth;
        const height = canvas.height = canvas.offsetHeight;
        
        ctx.clearRect(0, 0, width, height);
        
        if (!data.length) return;
        
        const maxValue = Math.max(...data.map(d => d.count));
        const barWidth = width / data.length * 0.8;
        const barSpacing = width / data.length * 0.2;
        
        const colors = ['#00B4D8', '#90E0EF', '#2A9D8F', '#E76F51', '#F4A261'];
        
        data.forEach((item, index) => {
            const barHeight = (item.count / maxValue) * (height - 60);
            const x = index * (barWidth + barSpacing) + barSpacing / 2;
            const y = height - barHeight - 30;
            
            // Desenhar barra
            ctx.fillStyle = colors[index % colors.length];
            ctx.fillRect(x, y, barWidth, barHeight);
            
            // Desenhar label
            ctx.fillStyle = '#333';
            ctx.font = '12px Inter';
            ctx.textAlign = 'center';
            ctx.fillText(item.status, x + barWidth/2, height - 10);
            ctx.fillText(item.count.toString(), x + barWidth/2, y - 5);
        });
    }

    renderTimelineChart() {
        const canvas = document.getElementById('timelineChart');
        if (!canvas || !this.data.charts.timeline) return;
        
        const ctx = canvas.getContext('2d');
        this.drawTimelineChart(ctx, this.data.charts.timeline);
    }

    drawTimelineChart(ctx, data) {
        const canvas = ctx.canvas;
        const width = canvas.width = canvas.offsetWidth;
        const height = canvas.height = canvas.offsetHeight;
        
        ctx.clearRect(0, 0, width, height);
        
        if (!data.length) return;
        
        const maxValue = Math.max(...data.map(d => d.total));
        const stepX = width / (data.length - 1);
        
        // Desenhar linha
        ctx.strokeStyle = '#00B4D8';
        ctx.lineWidth = 3;
        ctx.beginPath();
        
        data.forEach((item, index) => {
            const x = index * stepX;
            const y = height - ((item.total / maxValue) * (height - 40)) - 20;
            
            if (index === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
            
            // Desenhar ponto
            ctx.fillStyle = '#00B4D8';
            ctx.beginPath();
            ctx.arc(x, y, 4, 0, 2 * Math.PI);
            ctx.fill();
            
            // Desenhar label
            ctx.fillStyle = '#333';
            ctx.font = '10px Inter';
            ctx.textAlign = 'center';
            ctx.fillText(item.mes_nome, x, height - 5);
        });
        
        ctx.stroke();
    }

    renderTopClients() {
        const container = document.getElementById('top-clients');
        if (!container || !this.data.charts.top_clientes) return;

        container.innerHTML = this.data.charts.top_clientes.map((client, index) => `
            <div class="client-item">
                <div class="client-info">
                    <h4>#${index + 1} ${client.nome}</h4>
                    <p>${client.projetos} projeto${client.projetos !== 1 ? 's' : ''} • Último: ${client.ultimo_projeto || 'N/A'}</p>
                </div>
                <div class="client-stats">
                    <div class="client-value">R$ ${this.formatCurrency(client.valor)}</div>
                    <div class="client-projects">${client.projetos} projeto${client.projetos !== 1 ? 's' : ''}</div>
                </div>
            </div>
        `).join('');
    }

    renderFinancialMetrics() {
        const container = document.getElementById('financial-metrics');
        if (!container || !this.data.metrics.financeiro) return;

        const metrics = [
            {
                value: this.formatCurrency(this.data.metrics.financeiro.valor_total),
                label: 'Valor Total'
            },
            {
                value: this.formatCurrency(this.data.metrics.financeiro.valor_ativo),
                label: 'Em Andamento'
            },
            {
                value: this.formatCurrency(this.data.metrics.financeiro.valor_concluido),
                label: 'Concluído'
            }
        ];

        container.innerHTML = metrics.map(metric => `
            <div class="financial-card">
                <div class="financial-value">${metric.value}</div>
                <div class="financial-label">${metric.label}</div>
            </div>
        `).join('');
    }

    formatCurrency(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value);
    }

    async refresh() {
        try {
            // Mostrar loading
            const container = document.getElementById('analytics-container');
            if (container) {
                container.classList.add('loading');
            }

            await this.loadData();
            this.renderCharts();

            if (window.toastSystem) {
                window.toastSystem.success('Atualizado', 'Analytics atualizados com sucesso');
            }

        } catch (error) {
            console.error('Erro ao atualizar analytics:', error);
            if (window.toastSystem) {
                window.toastSystem.error('Erro', 'Falha ao atualizar analytics');
            }
        } finally {
            const container = document.getElementById('analytics-container');
            if (container) {
                container.classList.remove('loading');
            }
        }
    }

    exportData() {
        if (!this.data) {
            if (window.toastSystem) {
                window.toastSystem.warning('Aviso', 'Nenhum dado para exportar');
            }
            return;
        }

        const exportData = {
            generated_at: new Date().toISOString(),
            metrics: this.data.metrics,
            charts: this.data.charts
        };

        const blob = new Blob([JSON.stringify(exportData, null, 2)], {
            type: 'application/json'
        });

        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `polis-analytics-${new Date().toISOString().split('T')[0]}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        if (window.toastSystem) {
            window.toastSystem.success('Exportado', 'Dados exportados com sucesso');
        }
    }

    startAutoUpdate() {
        // Atualizar a cada 5 minutos
        this.updateInterval = setInterval(() => {
            this.refresh();
        }, 5 * 60 * 1000);
    }

    bindEvents() {
        // Limpar interval quando a página sair
        window.addEventListener('beforeunload', () => {
            if (this.updateInterval) {
                clearInterval(this.updateInterval);
            }
        });

        // Atualizar quando voltar para a aba
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && this.data) {
                this.refresh();
            }
        });
    }

    destroy() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
        }
    }
}

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
    // Aguardar que outros sistemas carreguem primeiro
    setTimeout(() => {
        window.dashboardAnalytics = new DashboardAnalytics();
        console.log('Dashboard Analytics initialized');
    }, 1000);
});

// Disponibilizar globalmente
window.DashboardAnalytics = DashboardAnalytics;