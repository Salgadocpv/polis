<?php
session_start();
if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['nivel_acesso'])) !== 'administrador') {
    header('Location: index.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - Polis Engenharia</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Scripts do sistema -->
    <script src="assets/js/toast-system.js"></script>
    <script src="assets/js/breadcrumb-system.js"></script>
    <script src="assets/js/lazy-loading.js"></script>
    <script src="assets/js/global-search.js"></script>
    <script src="assets/js/page-header.js"></script>
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            text-align: center;
            position: relative;
        }
        .main-content {
            font-size: 1rem;
            color: var(--cor-texto-claro);
            width: 100%;
            flex-grow: 1;
            padding-bottom: 2rem;
        }
        .setup-container {
            background-color: var(--cor-fundo-card);
            color: var(--cor-texto-escuro);
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: var(--sombra-media);
            max-width: 1200px;
            margin: 2rem auto;
            text-align: left;
        }
        .setup-container h2 {
            color: var(--cor-principal);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            text-align: center;
        }
        .setup-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        .setup-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: var(--sombra-leve);
            padding: 1.5rem;
            text-align: left;
            border: 1px solid #eee;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .setup-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--sombra-media);
        }
        .setup-card h3 {
            color: var(--cor-principal);
            margin-bottom: 1rem;
            font-size: 1.25rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding-bottom: 0.5rem;
        }
        .setup-card ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .setup-card li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .setup-card li:last-child {
            border-bottom: none;
        }
        .setup-card .add-form {
            display: flex;
            gap: 10px;
            margin-top: 1rem;
        }
        .setup-card .add-form input {
            flex-grow: 1;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        .setup-card .add-form button {
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
        }
        .setup-card .delete-btn {
            background: none;
            border: none;
            color: var(--cor-erro);
            cursor: pointer;
            font-size: 1.1rem;
            transition: color 0.2s ease;
        }
        .setup-card .delete-btn:hover {
            color: #cc0000;
        }
        @media (max-width: 768px) {
            .setup-container {
                padding: 1.5rem 1rem; /* Reduz o padding lateral */
                margin: 1rem 0; /* Remove margem lateral */
                width: 100%;
                box-sizing: border-box;
            }
            .setup-container h2 {
                font-size: 1.5rem;
            }
            .setup-grid {
                grid-template-columns: 1fr;
            }
            .setup-card {
                padding: 1rem;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }
            .setup-card .add-form {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php
        include 'includes/header.php';
        include 'includes/sidebar.php';
    ?>

    <main class="main-content" id="mainContent">
        <div class="setup-container">
            <h2>Configurações de Valores Fixos</h2>
            <div class="setup-grid">

                <div class="setup-card">
                    <h3>Gerenciar Departamentos</h3>
                    <ul id="departamentos-list">
                        </ul>
                    <div class="add-form">
                        <input type="text" id="new-departamento-input" placeholder="Novo Departamento">
                        <button class="btn btn-primary" id="add-departamento-btn">Adicionar</button>
                    </div>
                </div>

                <div class="setup-card">
                    <h3>Gerenciar Status de Projeto</h3>
                    <ul id="status-projeto-list">
                        </ul>
                    <div class="add-form">
                        <input type="text" id="new-status-projeto-input" placeholder="Novo Status">
                        <button class="btn btn-primary" id="add-status-projeto-btn">Adicionar</button>
                    </div>
                </div>

                <div class="setup-card">
                    <h3>Gerenciar Cargos</h3>
                    <ul id="cargos-list">
                        </ul>
                    <div class="add-form">
                        <input type="text" id="new-cargo-input" placeholder="Novo Cargo">
                        <button class="btn btn-primary" id="add-cargo-btn">Adicionar</button>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Caixa de mensagem para feedback -->
    <div id="messageBox" class="message-box"></div>

    <style>
        /* Estilo para a caixa de mensagem */
        .message-box {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
            min-width: 280px;
            max-width: 400px;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.4;
            word-wrap: break-word;
        }
        .message-box.show {
            opacity: 1;
            visibility: visible;
        }
        .message-box.error {
            background-color: #f44336;
        }
        
        /* Responsividade para toasts em mobile */
        @media (max-width: 768px) {
            .message-box {
                top: 10px;
                right: 10px;
                left: 10px;
                min-width: auto;
                max-width: none;
                width: calc(100% - 20px);
                font-size: 13px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elemento da caixa de mensagem
            const messageBox = document.getElementById('messageBox');

            // Função para exibir mensagens de feedback ao usuário
            function showMessage(message, isError = false) {
                messageBox.textContent = message;
                messageBox.className = 'message-box show';
                if (isError) {
                    messageBox.classList.add('error');
                } else {
                    messageBox.classList.remove('error');
                }
                setTimeout(() => {
                    messageBox.classList.remove('show');
                }, 3000);
            }

            // Sistema de Modal completo
            function createModal() {
                // Remove modal existente se houver
                const existingModal = document.getElementById('universalModal');
                if (existingModal) {
                    existingModal.remove();
                }

                // Criar estrutura do modal
                const modalHTML = `
                    <div id="universalModal" class="universal-modal" style="display: none;">
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
                                <button id="modalCancelBtn" class="btn btn-secondary modal-btn" style="display: none;">Cancelar</button>
                                <button id="modalConfirmBtn" class="btn btn-primary modal-btn">OK</button>
                            </div>
                        </div>
                    </div>
                `;

                document.body.insertAdjacentHTML('beforeend', modalHTML);
                addModalStyles();
            }

            function addModalStyles() {
                if (document.getElementById('modal-styles')) return;

                const styles = `
                    <style id="modal-styles">
                        .universal-modal {
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            z-index: 10000;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            opacity: 0;
                            visibility: hidden;
                            transition: opacity 0.3s ease, visibility 0.3s ease;
                        }

                        .universal-modal.show {
                            opacity: 1;
                            visibility: visible;
                        }

                        .modal-backdrop {
                            position: absolute;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: rgba(0, 0, 0, 0.5);
                            backdrop-filter: blur(4px);
                        }

                        .modal-container {
                            position: relative;
                            background: white;
                            border-radius: 12px;
                            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
                            max-width: 480px;
                            width: 90%;
                            max-height: 80vh;
                            overflow: hidden;
                            transform: scale(0.8) translateY(-20px);
                            transition: transform 0.3s ease;
                        }

                        .universal-modal.show .modal-container {
                            transform: scale(1) translateY(0);
                        }

                        .modal-header {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            padding: 24px 24px 0 24px;
                        }

                        .modal-icon {
                            width: 48px;
                            height: 48px;
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 24px;
                            color: white;
                        }

                        .modal-icon.success { background: #10b981; }
                        .modal-icon.error { background: #ef4444; }
                        .modal-icon.warning { background: #f59e0b; }
                        .modal-icon.info { background: #3b82f6; }
                        .modal-icon.confirm { background: #8b5cf6; }

                        .modal-close-btn {
                            background: none;
                            border: none;
                            color: #9ca3af;
                            cursor: pointer;
                            padding: 8px;
                            border-radius: 6px;
                            transition: all 0.2s ease;
                            font-size: 18px;
                        }

                        .modal-close-btn:hover {
                            background: rgba(156, 163, 175, 0.1);
                            color: #6b7280;
                        }

                        .modal-body {
                            padding: 24px;
                            text-align: center;
                        }

                        .modal-body h3 {
                            margin: 0 0 12px 0;
                            font-size: 20px;
                            font-weight: 600;
                            color: #1f2937;
                        }

                        .modal-body p {
                            margin: 0;
                            color: #6b7280;
                            line-height: 1.5;
                            font-size: 16px;
                        }

                        .modal-footer {
                            display: flex;
                            gap: 12px;
                            padding: 0 24px 24px 24px;
                            justify-content: flex-end;
                        }

                        .modal-btn {
                            padding: 10px 24px;
                            border: none;
                            border-radius: 8px;
                            font-weight: 500;
                            cursor: pointer;
                            transition: all 0.2s ease;
                            font-size: 14px;
                        }

                        .btn-primary {
                            background: var(--cor-principal);
                            color: white;
                        }

                        .btn-primary:hover {
                            background: var(--cor-secundaria);
                        }

                        .btn-secondary {
                            background: #f3f4f6;
                            color: #374151;
                        }

                        .btn-secondary:hover {
                            background: #e5e7eb;
                        }

                        @media (max-width: 480px) {
                            .modal-container {
                                width: 95%;
                                margin: 20px;
                            }
                            
                            .modal-footer {
                                flex-direction: column;
                            }
                            
                            .modal-btn {
                                width: 100%;
                            }
                        }
                    </style>
                `;

                document.head.insertAdjacentHTML('beforeend', styles);
            }

            function showModal(type, title, message, options = {}) {
                return new Promise((resolve) => {
                    const modal = document.getElementById('universalModal');
                    const modalIcon = document.getElementById('modalIcon');
                    const modalTitle = document.getElementById('modalTitle');
                    const modalMessage = document.getElementById('modalMessage');
                    const modalConfirmBtn = document.getElementById('modalConfirmBtn');
                    const modalCancelBtn = document.getElementById('modalCancelBtn');
                    const modalCloseBtn = document.getElementById('modalCloseBtn');

                    // Configurar ícone e cor
                    const iconClass = modalIcon.parentElement;
                    iconClass.className = `modal-icon ${type}`;
                    
                    const icons = {
                        success: 'fas fa-check',
                        error: 'fas fa-exclamation-triangle',
                        warning: 'fas fa-exclamation-circle',
                        info: 'fas fa-info-circle',
                        confirm: 'fas fa-question-circle'
                    };
                    
                    modalIcon.className = icons[type] || icons.info;
                    modalTitle.textContent = title;
                    modalMessage.textContent = message;

                    // Configurar botões
                    if (type === 'confirm') {
                        modalCancelBtn.style.display = 'inline-block';
                        modalConfirmBtn.textContent = 'Confirmar';
                        modalCancelBtn.textContent = 'Cancelar';
                    } else {
                        modalCancelBtn.style.display = 'none';
                        modalConfirmBtn.textContent = 'OK';
                    }

                    // Mostrar modal
                    modal.style.display = 'flex';
                    setTimeout(() => modal.classList.add('show'), 10);

                    // Event listeners
                    const closeModal = (result = false) => {
                        modal.classList.remove('show');
                        setTimeout(() => {
                            modal.style.display = 'none';
                        }, 300);
                        resolve(result);
                    };

                    modalConfirmBtn.onclick = () => closeModal(true);
                    modalCancelBtn.onclick = () => closeModal(false);
                    modalCloseBtn.onclick = () => closeModal(false);
                    
                    // Fechar ao clicar no backdrop
                    modal.querySelector('.modal-backdrop').onclick = () => closeModal(false);
                    
                    // Fechar com ESC
                    const escHandler = (e) => {
                        if (e.key === 'Escape') {
                            document.removeEventListener('keydown', escHandler);
                            closeModal(false);
                        }
                    };
                    document.addEventListener('keydown', escHandler);
                });
            }

            // Criar modal na inicialização
            createModal();

            // Funções para gerenciar Departamentos
            const departamentosList = document.getElementById('departamentos-list');
            const newDepartamentoInput = document.getElementById('new-departamento-input');
            const addDepartamentoBtn = document.getElementById('add-departamento-btn');

            async function fetchDepartamentos() {
                try {
                    const response = await fetch('api/valores_fixos.php?tipo=departamento');
                    const data = await response.json();
                    departamentosList.innerHTML = '';
                    data.forEach(item => {
                        const li = document.createElement('li');
                        li.innerHTML = `
                            <span>${item.valor}</span>
                            <button class="delete-btn" data-id="${item.id}" data-tipo="departamento"><i class="fas fa-trash"></i></button>
                        `;
                        departamentosList.appendChild(li);
                    });
                } catch (error) {
                    console.error('Erro ao carregar departamentos:', error);
                    showModal('error', 'Erro', 'Não foi possível carregar os departamentos.');
                }
            }

            addDepartamentoBtn.addEventListener('click', async function() {
                const valor = newDepartamentoInput.value.trim();
                if (!valor) return;

                try {
                    const response = await fetch('api/valores_fixos.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ tipo: 'departamento', valor: valor })
                    });
                    const result = await response.json();
                    if (response.ok) {
                        showModal('success', 'Sucesso', result.message);
                        newDepartamentoInput.value = '';
                        fetchDepartamentos();
                    } else {
                        showModal('error', 'Erro', result.message || 'Erro ao adicionar departamento.');
                    }
                } catch (error) {
                    console.error('Erro ao adicionar departamento:', error);
                    showModal('error', 'Erro', 'Erro ao conectar com o servidor.');
                }
            });

            // Funções para gerenciar Status de Projeto
            const statusProjetoList = document.getElementById('status-projeto-list');
            const newStatusProjetoInput = document.getElementById('new-status-projeto-input');
            const addStatusProjetoBtn = document.getElementById('add-status-projeto-btn');

            async function fetchStatusProjeto() {
                try {
                    const response = await fetch('api/valores_fixos.php?tipo=status_projeto');
                    const data = await response.json();
                    statusProjetoList.innerHTML = '';
                    data.forEach(item => {
                        const li = document.createElement('li');
                        li.innerHTML = `
                            <span>${item.valor}</span>
                            <button class="delete-btn" data-id="${item.id}" data-tipo="status_projeto"><i class="fas fa-trash"></i></button>
                        `;
                        statusProjetoList.appendChild(li);
                    });
                } catch (error) {
                    console.error('Erro ao carregar status de projeto:', error);
                    showModal('error', 'Erro', 'Não foi possível carregar os status de projeto.');
                }
            }

            addStatusProjetoBtn.addEventListener('click', async function() {
                const valor = newStatusProjetoInput.value.trim();
                if (!valor) return;

                try {
                    const response = await fetch('api/valores_fixos.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ tipo: 'status_projeto', valor: valor })
                    });
                    const result = await response.json();
                    if (response.ok) {
                        showModal('success', 'Sucesso', result.message);
                        newStatusProjetoInput.value = '';
                        fetchStatusProjeto();
                    } else {
                        showModal('error', 'Erro', result.message || 'Erro ao adicionar status de projeto.');
                    }
                } catch (error) {
                    console.error('Erro ao adicionar status de projeto:', error);
                    showModal('error', 'Erro', 'Erro ao conectar com o servidor.');
                }
            });

            // Deleção de itens
            document.addEventListener('click', async function(event) {
                if (event.target.closest('.delete-btn')) {
                    const button = event.target.closest('.delete-btn');
                    const id = button.dataset.id;
                    const tipo = button.dataset.tipo;

                    const confirmation = await showModal('confirm', 'Confirmação de Exclusão', `Tem certeza que deseja excluir este ${tipo}?`);
                    if (confirmation) {
                        try {
                            const response = await fetch(`api/valores_fixos.php?id=${id}`, {
                                method: 'DELETE'
                            });
                            const result = await response.json();
                            if (response.ok) {
                                showModal('success', 'Sucesso', result.message);
                                if (tipo === 'departamento') {
                                    fetchDepartamentos();
                                } else if (tipo === 'status_projeto') {
                                    fetchStatusProjeto();
                                } else if (tipo === 'cargo') {
                                    fetchCargos();
                                }
                            } else {
                                showModal('error', 'Erro', result.message || 'Erro ao excluir.');
                            }
                        } catch (error) {
                            console.error('Erro ao excluir:', error);
                            showModal('error', 'Erro', 'Erro ao conectar com o servidor.');
                        }
                    }
                }
            });

            // Funções para gerenciar Cargos
            const cargosList = document.getElementById('cargos-list');
            const newCargoInput = document.getElementById('new-cargo-input');
            const addCargoBtn = document.getElementById('add-cargo-btn');

            async function fetchCargos() {
                try {
                    const response = await fetch('api/valores_fixos.php?tipo=cargo');
                    const data = await response.json();
                    cargosList.innerHTML = '';
                    data.forEach(item => {
                        const li = document.createElement('li');
                        li.innerHTML = `
                            <span>${item.valor}</span>
                            <button class="delete-btn" data-id="${item.id}" data-tipo="cargo"><i class="fas fa-trash"></i></button>
                        `;
                        cargosList.appendChild(li);
                    });
                } catch (error) {
                    console.error('Erro ao carregar cargos:', error);
                    showModal('error', 'Erro', 'Não foi possível carregar os cargos.');
                }
            }

            addCargoBtn.addEventListener('click', async function() {
                const valor = newCargoInput.value.trim();
                if (!valor) return;

                try {
                    const response = await fetch('api/valores_fixos.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ tipo: 'cargo', valor: valor })
                    });
                    const result = await response.json();
                    if (response.ok) {
                        showModal('success', 'Sucesso', result.message);
                        newCargoInput.value = '';
                        fetchCargos();
                    } else {
                        showModal('error', 'Erro', result.message || 'Erro ao adicionar cargo.');
                    }
                } catch (error) {
                    console.error('Erro ao adicionar cargo:', error);
                    showModal('error', 'Erro', 'Erro ao conectar com o servidor.');
                }
            });

            // Carregar dados iniciais
            fetchDepartamentos();
            fetchStatusProjeto();
            fetchCargos();
        });
    </script>
</body>
</html>
