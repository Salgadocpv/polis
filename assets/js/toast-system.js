/**
 * Sistema de Toast Notifications para Polis Engenharia
 */

class ToastSystem {
    constructor() {
        this.container = null;
        this.toasts = new Map();
        this.init();
    }

    init() {
        this.createContainer();
        this.addStyles();
    }

    createContainer() {
        if (document.getElementById('toast-container')) return;

        this.container = document.createElement('div');
        this.container.id = 'toast-container';
        this.container.className = 'toast-container';
        document.body.appendChild(this.container);
    }

    addStyles() {
        if (document.getElementById('toast-styles')) return;

        const styles = `
            <style id="toast-styles">
                .toast-container {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 10001;
                    max-width: 420px;
                    width: 100%;
                    pointer-events: none;
                }

                .toast {
                    background: white;
                    border-radius: 12px;
                    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
                    margin-bottom: 12px;
                    padding: 16px 20px;
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    transform: translateX(100%);
                    opacity: 0;
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    pointer-events: all;
                    position: relative;
                    overflow: hidden;
                    backdrop-filter: blur(10px);
                    border-left: 4px solid;
                }

                .toast.show {
                    transform: translateX(0);
                    opacity: 1;
                }

                .toast.removing {
                    transform: translateX(100%);
                    opacity: 0;
                    margin-bottom: 0;
                    padding-top: 0;
                    padding-bottom: 0;
                    max-height: 0;
                }

                .toast-icon {
                    width: 24px;
                    height: 24px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 12px;
                    color: white;
                    flex-shrink: 0;
                }

                .toast-content {
                    flex-grow: 1;
                    min-width: 0;
                }

                .toast-title {
                    font-weight: 600;
                    font-size: 14px;
                    color: #1f2937;
                    margin-bottom: 2px;
                    line-height: 1.3;
                }

                .toast-message {
                    font-size: 13px;
                    color: #374151;
                    line-height: 1.4;
                    word-wrap: break-word;
                }

                .toast-close {
                    background: none;
                    border: none;
                    color: #9ca3af;
                    cursor: pointer;
                    padding: 4px;
                    border-radius: 4px;
                    transition: all 0.2s ease;
                    flex-shrink: 0;
                }

                .toast-close:hover {
                    background: rgba(156, 163, 175, 0.1);
                    color: #6b7280;
                }

                .toast-progress {
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    height: 3px;
                    background: currentColor;
                    opacity: 0.3;
                    transition: width linear;
                }

                /* Tipos de toast */
                .toast.success {
                    border-left-color: #10b981;
                    background: linear-gradient(135deg, rgba(16, 185, 129, 0.02) 0%, rgba(255, 255, 255, 1) 100%);
                }

                .toast.success .toast-icon {
                    background: #10b981;
                }

                .toast.error {
                    border-left-color: #ef4444;
                    background: linear-gradient(135deg, rgba(239, 68, 68, 0.02) 0%, rgba(255, 255, 255, 1) 100%);
                }

                .toast.error .toast-icon {
                    background: #ef4444;
                }

                .toast.warning {
                    border-left-color: #f59e0b;
                    background: linear-gradient(135deg, rgba(245, 158, 11, 0.02) 0%, rgba(255, 255, 255, 1) 100%);
                }

                .toast.warning .toast-icon {
                    background: #f59e0b;
                }

                .toast.info {
                    border-left-color: var(--cor-vibrante);
                    background: linear-gradient(135deg, rgba(0, 180, 216, 0.02) 0%, rgba(255, 255, 255, 1) 100%);
                }

                .toast.info .toast-icon {
                    background: var(--cor-vibrante);
                }

                /* Responsividade */
                @media (max-width: 480px) {
                    .toast-container {
                        top: 10px;
                        right: 10px;
                        left: 10px;
                        max-width: none;
                    }

                    .toast {
                        padding: 14px 16px;
                        gap: 10px;
                    }

                    .toast-title {
                        font-size: 13px;
                    }

                    .toast-message {
                        font-size: 12px;
                    }
                }

                /* Animações adicionais */
                @keyframes toastBounce {
                    0%, 60%, 100% { transform: translateX(0); }
                    30% { transform: translateX(-5px); }
                }

                .toast.bounce {
                    animation: toastBounce 0.6s ease;
                }
            </style>
        `;

        document.head.insertAdjacentHTML('beforeend', styles);
    }

    show(options) {
        const {
            type = 'info',
            title = '',
            message = '',
            duration = 5000,
            closable = true,
            persistent = false,
            actions = []
        } = options;

        const toastId = this.generateId();
        const toast = this.createToast({
            id: toastId,
            type,
            title,
            message,
            closable,
            actions
        });

        this.container.appendChild(toast);
        
        // Animar entrada
        setTimeout(() => {
            toast.classList.add('show');
        }, 50);

        // Auto-remover (se não for persistente)
        if (!persistent && duration > 0) {
            this.startProgress(toast, duration);
            setTimeout(() => {
                this.remove(toastId);
            }, duration);
        }

        // Armazenar referência
        this.toasts.set(toastId, {
            element: toast,
            type,
            title,
            message,
            timestamp: Date.now()
        });

        return toastId;
    }

    createToast({ id, type, title, message, closable, actions }) {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.dataset.toastId = id;

        const icons = {
            success: 'fas fa-check',
            error: 'fas fa-times',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info'
        };

        toast.innerHTML = `
            <div class="toast-icon">
                <i class="${icons[type] || icons.info}"></i>
            </div>
            <div class="toast-content">
                ${title ? `<div class="toast-title">${title}</div>` : ''}
                <div class="toast-message">${message}</div>
                ${actions.length > 0 ? this.createActions(actions) : ''}
            </div>
            ${closable ? `
                <button class="toast-close" onclick="toastSystem.remove('${id}')">
                    <i class="fas fa-times"></i>
                </button>
            ` : ''}
            <div class="toast-progress"></div>
        `;

        return toast;
    }

    createActions(actions) {
        return `
            <div class="toast-actions" style="margin-top: 8px; display: flex; gap: 8px;">
                ${actions.map(action => `
                    <button 
                        onclick="${action.handler}" 
                        style="
                            background: ${action.primary ? 'var(--cor-vibrante)' : 'transparent'};
                            color: ${action.primary ? 'white' : 'var(--cor-vibrante)'};
                            border: 1px solid var(--cor-vibrante);
                            border-radius: 4px;
                            padding: 4px 8px;
                            font-size: 12px;
                            cursor: pointer;
                            transition: all 0.2s ease;
                        "
                        onmouseover="this.style.background = 'var(--cor-vibrante)'; this.style.color = 'white';"
                        onmouseout="this.style.background = '${action.primary ? 'var(--cor-vibrante)' : 'transparent'}'; this.style.color = '${action.primary ? 'white' : 'var(--cor-vibrante)'}';"
                    >
                        ${action.text}
                    </button>
                `).join('')}
            </div>
        `;
    }

    startProgress(toast, duration) {
        const progressBar = toast.querySelector('.toast-progress');
        if (!progressBar) return;

        progressBar.style.width = '100%';
        progressBar.style.transitionDuration = `${duration}ms`;
        
        setTimeout(() => {
            progressBar.style.width = '0%';
        }, 50);
    }

    remove(toastId) {
        const toastData = this.toasts.get(toastId);
        if (!toastData) return;

        const toast = toastData.element;
        toast.classList.add('removing');

        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
            this.toasts.delete(toastId);
        }, 300);
    }

    removeAll() {
        this.toasts.forEach((_, id) => {
            this.remove(id);
        });
    }

    generateId() {
        return 'toast_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    // Métodos de conveniência
    success(title, message, options = {}) {
        return this.show({
            type: 'success',
            title,
            message,
            ...options
        });
    }

    error(title, message, options = {}) {
        return this.show({
            type: 'error',
            title,
            message,
            duration: 8000, // Erros ficam mais tempo
            ...options
        });
    }

    warning(title, message, options = {}) {
        return this.show({
            type: 'warning',
            title,
            message,
            duration: 6000,
            ...options
        });
    }

    info(title, message, options = {}) {
        return this.show({
            type: 'info',
            title,
            message,
            ...options
        });
    }

    // Toast com ação de confirmação
    confirm(title, message, onConfirm, onCancel) {
        return this.show({
            type: 'warning',
            title,
            message,
            persistent: true,
            closable: false,
            actions: [
                {
                    text: 'Cancelar',
                    handler: () => {
                        if (onCancel) onCancel();
                        this.remove(toastId);
                    }
                },
                {
                    text: 'Confirmar',
                    primary: true,
                    handler: () => {
                        if (onConfirm) onConfirm();
                        this.remove(toastId);
                    }
                }
            ]
        });
    }

    // Toast de loading
    loading(title, message = 'Processando...') {
        return this.show({
            type: 'info',
            title,
            message: `<i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>${message}`,
            persistent: true,
            closable: false
        });
    }

    // Update de toast existente
    update(toastId, options) {
        const toastData = this.toasts.get(toastId);
        if (!toastData) return;

        const toast = toastData.element;
        const { title, message, type } = options;

        if (type && type !== toastData.type) {
            toast.className = `toast ${type} show`;
            const icon = toast.querySelector('.toast-icon i');
            const icons = {
                success: 'fas fa-check',
                error: 'fas fa-times',
                warning: 'fas fa-exclamation-triangle',
                info: 'fas fa-info'
            };
            icon.className = icons[type] || icons.info;
        }

        if (title !== undefined) {
            const titleEl = toast.querySelector('.toast-title');
            if (titleEl) {
                titleEl.textContent = title;
            }
        }

        if (message !== undefined) {
            const messageEl = toast.querySelector('.toast-message');
            if (messageEl) {
                messageEl.innerHTML = message;
            }
        }

        // Bounce animation
        toast.classList.add('bounce');
        setTimeout(() => {
            toast.classList.remove('bounce');
        }, 600);

        // Atualizar dados armazenados
        Object.assign(toastData, options);
    }
}

// Inicialização global
document.addEventListener('DOMContentLoaded', () => {
    window.toastSystem = new ToastSystem();
    
    // Função de conveniência global
    window.showToast = (type, title, message, options) => {
        return window.toastSystem[type](title, message, options);
    };

    console.log('Toast System initialized');
});

// Disponibilizar classe globalmente
window.ToastSystem = ToastSystem;