/**
 * Sistema de Modal Reutilizável para Polis Engenharia
 * Suporta diferentes tipos: success, error, warning, info, confirm
 */

class ModalSystem {
    constructor() {
        // A criação da estrutura e vinculação de eventos será feita após o DOMContentLoaded
    }

    init() {
        this.createModalStructure();
    }

    createModalStructure() {
        // Remove modal existente se houver
        const existingModal = document.getElementById('universalModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Criar estrutura do modal
        const modalHTML = `
            <div id="universalModal" class="universal-modal">
                <div class="modal-backdrop"></div>
                <div class="modal-container">
                    <div class="modal-header">
                        <div class="modal-icon">
                            <i id="modalIcon" class="fas fa-info-circle"></i>
                        </div>
                        <button class="modal-close-btn" id="modalCloseBtn">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h3 id="modalTitle">Título</h3>
                        <p id="modalMessage">Mensagem</p>
                    </div>
                    <div class="modal-footer">
                        <button id="modalCancelBtn" class="btn btn-secondary modal-btn">Cancelar</button>
                        <button id="modalConfirmBtn" class="btn btn-primary modal-btn">OK</button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.addModalStyles();
        this.bindEvents();
    }

    addModalStyles() {
        const styleId = 'universal-modal-styles';
        if (document.getElementById(styleId)) return;

        const styles = `
            <style id="${styleId}">
                .universal-modal {
                    display: none;
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    z-index: 10000;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }

                .universal-modal.show {
                    display: flex;
                    opacity: 1;
                }

                .modal-backdrop {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.6);
                    backdrop-filter: blur(5px);
                }

                .modal-container {
                    position: relative;
                    background: white;
                    border-radius: 16px;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                    margin: auto;
                    max-width: 450px;
                    width: 90%;
                    max-height: 80vh;
                    overflow: hidden;
                    transform: scale(0.8) translateY(20px);
                    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
                }

                .universal-modal.show .modal-container {
                    transform: scale(1) translateY(0);
                }

                .modal-header {
                    position: relative;
                    padding: 1.5rem 1.5rem 1rem;
                    text-align: center;
                    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
                }

                .modal-icon {
                    width: 60px;
                    height: 60px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 1rem;
                    font-size: 1.5rem;
                    color: white;
                    background: var(--cor-vibrante);
                }

                .modal-icon.success {
                    background: linear-gradient(45deg, #10b981, #059669);
                }

                .modal-icon.error {
                    background: linear-gradient(45deg, #ef4444, #dc2626);
                }

                .modal-icon.warning {
                    background: linear-gradient(45deg, #f59e0b, #d97706);
                }

                .modal-icon.info {
                    background: linear-gradient(45deg, var(--cor-vibrante), var(--cor-clara));
                }

                .modal-icon.confirm {
                    background: linear-gradient(45deg, var(--cor-principal), var(--cor-secundaria));
                }

                .modal-close-btn {
                    position: absolute;
                    top: 1rem;
                    right: 1rem;
                    background: none;
                    border: none;
                    font-size: 1.2rem;
                    color: #999;
                    cursor: pointer;
                    width: 32px;
                    height: 32px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: all 0.2s ease;
                }

                .modal-close-btn:hover {
                    background: rgba(0, 0, 0, 0.1);
                    color: #333;
                }

                .modal-body {
                    padding: 1rem 1.5rem;
                    text-align: center;
                }

                .modal-body h3 {
                    margin: 0 0 0.5rem 0;
                    font-size: 1.25rem;
                    font-weight: 600;
                    color: var(--cor-principal);
                }

                .modal-body p {
                    margin: 0;
                    color: var(--cor-texto-escuro);
                    line-height: 1.5;
                    font-size: 0.95rem;
                }

                .modal-footer {
                    padding: 1rem 1.5rem 1.5rem;
                    display: flex;
                    gap: 0.75rem;
                    justify-content: flex-end;
                    border-top: 1px solid rgba(0, 0, 0, 0.1);
                }

                .modal-btn {
                    padding: 0.6rem 1.2rem;
                    border-radius: 8px;
                    font-size: 0.9rem;
                    font-weight: 500;
                    border: none;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    min-width: 80px;
                }

                .modal-btn.btn-secondary {
                    background: #f3f4f6;
                    color: #374151;
                    border: 1px solid #d1d5db;
                }

                .modal-btn.btn-secondary:hover {
                    background: #e5e7eb;
                }

                .modal-btn.btn-primary {
                    background: var(--cor-vibrante);
                    color: white;
                }

                .modal-btn.btn-primary:hover {
                    background: var(--cor-clara);
                    transform: translateY(-1px);
                    box-shadow: 0 4px 12px rgba(0, 180, 216, 0.3);
                }

                .modal-btn.btn-danger {
                    background: #ef4444;
                    color: white;
                }

                .modal-btn.btn-danger:hover {
                    background: #dc2626;
                    transform: translateY(-1px);
                    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
                }

                .modal-btn.loading {
                    opacity: 0.7;
                    cursor: not-allowed;
                    position: relative;
                }

                .modal-btn.loading::after {
                    content: '';
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    width: 16px;
                    height: 16px;
                    border: 2px solid rgba(255, 255, 255, 0.3);
                    border-top: 2px solid white;
                    border-radius: 50%;
                    animation: spin 0.8s linear infinite;
                }

                @keyframes spin {
                    to { transform: translate(-50%, -50%) rotate(360deg); }
                }

                /* Responsividade */
                @media (max-width: 480px) {
                    .modal-container {
                        width: 95%;
                        margin: 1rem auto;
                    }

                    .modal-header,
                    .modal-body,
                    .modal-footer {
                        padding-left: 1rem;
                        padding-right: 1rem;
                    }

                    .modal-footer {
                        flex-direction: column;
                    }

                    .modal-btn {
                        width: 100%;
                    }
                }

                /* Esconder elementos por tipo */
                .universal-modal.info .modal-footer,
                .universal-modal.success .modal-footer,
                .universal-modal.error .modal-footer {
                    justify-content: center;
                }

                .universal-modal.info #modalCancelBtn,
                .universal-modal.success #modalCancelBtn,
                .universal-modal.error #modalCancelBtn {
                    display: none;
                }

                .universal-modal.confirm #modalConfirmBtn {
                    background: var(--cor-principal);
                }
            </style>
        `;

        document.head.insertAdjacentHTML('beforeend', styles);
    }

    bindEvents() {
        const modal = document.getElementById('universalModal');
        const backdrop = modal.querySelector('.modal-backdrop');
        const closeBtn = document.getElementById('modalCloseBtn');
        const cancelBtn = document.getElementById('modalCancelBtn');
        const confirmBtn = document.getElementById('modalConfirmBtn');

        // Fechar modal
        const closeModal = () => {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                if (this.currentReject) {
                    this.currentReject('Modal fechado');
                }
                this.currentResolve = null;
                this.currentReject = null;
            }, 300);
        };

        backdrop.addEventListener('click', closeModal);
        closeBtn.addEventListener('click', closeModal);
        
        cancelBtn.addEventListener('click', () => {
            if (this.currentReject) {
                this.currentReject('Cancelado');
            }
            closeModal();
        });

        confirmBtn.addEventListener('click', () => {
            if (this.currentResolve) {
                this.currentResolve(true);
            }
            closeModal();
        });

        // ESC para fechar
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('show')) {
                closeModal();
            }
        });
    }

    show(options) {
        return new Promise((resolve, reject) => {
            this.currentResolve = resolve;
            this.currentReject = reject;

            const {
                type = 'info',
                title = 'Informação',
                message = '',
                confirmText = 'OK',
                cancelText = 'Cancelar',
                showCancel = false
            } = options;

            const modal = document.getElementById('universalModal');
            const icon = document.getElementById('modalIcon');
            const modalTitle = document.getElementById('modalTitle');
            const modalMessage = document.getElementById('modalMessage');
            const confirmBtn = document.getElementById('modalConfirmBtn');
            const cancelBtn = document.getElementById('modalCancelBtn');

            // Configurar tipo
            modal.className = `universal-modal ${type}`;
            
            // Configurar ícone
            const iconElement = modal.querySelector('.modal-icon');
            iconElement.className = `modal-icon ${type}`;
            
            const iconMap = {
                success: 'fas fa-check',
                error: 'fas fa-exclamation-triangle',
                warning: 'fas fa-exclamation-triangle',
                info: 'fas fa-info-circle',
                confirm: 'fas fa-question-circle'
            };
            
            icon.className = iconMap[type] || iconMap.info;

            // Configurar conteúdo
            modalTitle.textContent = title;
            modalMessage.textContent = message;
            confirmBtn.textContent = confirmText;
            cancelBtn.textContent = cancelText;

            // Mostrar/esconder botão cancelar
            cancelBtn.style.display = showCancel ? 'block' : 'none';

            // Mostrar modal
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);

            // Auto-fechar para mensagens informativas após 3 segundos
            if (['success', 'error', 'info'].includes(type)) {
                setTimeout(() => {
                    if (modal.classList.contains('show')) {
                        resolve(true);
                        modal.classList.remove('show');
                        setTimeout(() => modal.style.display = 'none', 300);
                    }
                }, 3000);
            }
        });
    }

    // Métodos de conveniência
    success(title, message = '', options = {}) {
        return this.show({
            type: 'success',
            title,
            message,
            confirmText: 'OK',
            ...options
        });
    }

    error(title, message = '', options = {}) {
        return this.show({
            type: 'error',
            title,
            message,
            confirmText: 'OK',
            ...options
        });
    }

    warning(title, message = '', options = {}) {
        return this.show({
            type: 'warning',
            title,
            message,
            confirmText: 'OK',
            ...options
        });
    }

    info(title, message = '', options = {}) {
        return this.show({
            type: 'info',
            title,
            message,
            confirmText: 'OK',
            ...options
        });
    }

    confirm(title, message = '', options = {}) {
        return this.show({
            type: 'confirm',
            title,
            message,
            confirmText: 'Confirmar',
            cancelText: 'Cancelar',
            showCancel: true,
            ...options
        });
    }
}

// Instância global
document.addEventListener('DOMContentLoaded', function() {
    window.Modal = new ModalSystem();
    window.Modal.init(); // Chamar init() após a criação da instância

    // Função de conveniência para compatibilidade
    window.showModal = (type, title, message, options = {}) => {
        return window.Modal[type](title, message, options);
    };
});