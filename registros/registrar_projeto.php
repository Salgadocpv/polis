<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.html');
    exit();
}

$is_viewer = (isset($_SESSION['nivel_acesso']) && strtolower(trim($_SESSION['nivel_acesso'])) === 'visualizador');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro/Edição de Projeto - Sistema de Engenharia</title>
    <!-- Importa a fonte 'Inter' do Google Fonts para um visual moderno -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <!-- Link para o Font Awesome para os ícones (versão mais recente) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Link para o arquivo de estilos CSS global -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Estilos adicionais para o body e main-content */
        body {
            display: flex;
            flex-direction: column; /* Organiza o conteúdo em coluna */
            min-height: 100vh;
            text-align: center;
            position: relative; /* Necessário para z-index do menu-toggle */
        }

        /* O main-content já tem padding-top no style.css para a barra superior */
        .main-content {
            font-size: 1.5rem;
            color: var(--cor-texto-claro);
            width: 100%; /* Garante que o conteúdo ocupe a largura total */
            flex-grow: 1; /* Permite que o conteúdo ocupe o espaço restante */
            padding-bottom: 2rem; /* Adiciona padding na parte inferior do conteúdo */
        }

        /* Estilos específicos para o formulário de cadastro de projeto (reutilizando estilos existentes) */
        .project-form-container {
            background-color: var(--cor-fundo-card);
            color: var(--cor-texto-escuro);
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: var(--sombra-media);
            max-width: 800px; /* Largura máxima para o formulário */
            margin: 2rem auto; /* Centraliza e adiciona margem */
            text-align: left; /* Alinha o texto do formulário à esquerda */
        }

        .project-form-container h2 {
            color: var(--cor-principal);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            text-align: center; /* Centraliza o título */
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); /* Grid responsivo para campos */
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: var(--cor-texto-escuro);
            font-size: 0.95rem;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group input[type="date"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
            color: var(--cor-texto-escuro);
            background-color: var(--cor-fundo-card);
            transition: border-color 0.2s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="email"]:focus,
        .form-group input[type="tel"]:focus,
        .form-group input[type="date"]:focus,
        .form-group input[type="number"]:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--cor-vibrante);
            box-shadow: 0 0 0 3px rgba(0, 180, 216, 0.2);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }

        /* Responsividade para telas menores */
        @media (max-width: 768px) {
            .project-form-container {
                padding: 1.5rem;
                margin: 1rem auto;
            }
            .project-form-container h2 {
                font-size: 1.5rem;
            }
            .form-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .form-actions {
                flex-direction: column;
                gap: 0.8rem;
            }
            .form-actions .btn {
                width: 100%;
            }
        }

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
</head>
<body>
    <?php
        // Inclui a barra superior
        include '../includes/header.php';
        // Inclui o menu lateral
        include '../includes/sidebar.php';
    ?>

    <!-- Conteúdo Principal da Página -->
    <main class="main-content" id="mainContent">
        <div class="project-form-container">
            <h2><span id="pageTitleText"></span></h2>
            <!-- Caixa de mensagem para feedback -->
            <div id="messageBox" class="message-box"></div>
            <form id="projectForm" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nome_projeto">Nome do Projeto</label>
                        <input type="text" id="nome_projeto" name="nome" placeholder="Nome do projeto" required>
                    </div>

                    <div class="form-group">
                        <label for="cliente">Cliente</label>
                        <select id="cliente" name="cliente_id" required>
                            <option value="">Selecione o Cliente</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="responsavel">Responsável</label>
                        <input type="text" id="responsavel" name="responsavel" placeholder="Nome do colaborador responsável" required>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="">Carregando Status...</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="data_inicio">Data de Início</label>
                        <input type="date" id="data_inicio" name="data_inicio" required>
                    </div>

                    <div class="form-group">
                        <label for="data_conclusao_prevista">Data Prevista de Conclusão</label>
                        <input type="date" id="data_conclusao_prevista" name="data_conclusao_prevista">
                    </div>

                    <div class="form-group">
                        <label for="orcamento">Orçamento (R$)</label>
                        <input type="number" id="orcamento" name="orcamento" placeholder="Ex: 15000.00" step="0.01" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label for="descricao">Observações</label>
                    <textarea id="descricao" name="descricao" placeholder="Detalhes e observações sobre o projeto..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn btn-secondary">Limpar Formulário</button>
                    <button type="submit" class="btn btn-primary" id="submitButton">Cadastrar Projeto</button>
                </div>
            </form>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('projectForm');
            const submitButton = document.getElementById('submitButton');
            const pageTitleTextElement = document.getElementById('pageTitleText');
            const clienteSelect = document.getElementById('cliente');
            const statusSelect = document.getElementById('status');
            const isViewer = <?php echo json_encode($is_viewer); ?>;

            const urlParams = new URLSearchParams(window.location.search);
            const projectId = urlParams.get('id');

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

            async function fetchClientsForSelect() {
                try {
                    const response = await fetch('../api/clientes.php');
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    const clients = await response.json();
                    clienteSelect.innerHTML = '<option value="">Selecione o Cliente</option>';
                    clients.forEach(client => {
                        const option = document.createElement('option');
                        option.value = client.id;
                        option.textContent = client.nome;
                        clienteSelect.appendChild(option);
                    });
                } catch (error) {
                    console.error('Erro ao carregar clientes para o select:', error);
                    showMessage('Erro ao carregar clientes.', true);
                }
            }

            async function fetchProjectData(id) {
                try {
                    console.log('Iniciando fetchProjectData para ID:', id);
                    const url = `../api/projetos.php?id=${id}`;
                    console.log('URL sendo chamada:', url);
                    const response = await fetch(url);
                    console.log('Response status:', response.status);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const project = await response.json();
                    console.log('Dados recebidos do projeto:', project);
                    
                    if (project && project.id) {
                        console.log('Preenchendo campos do formulário...');
                        document.getElementById('nome_projeto').value = project.nome || '';
                        document.getElementById('cliente').value = project.cliente_id || '';
                        document.getElementById('responsavel').value = project.responsavel || '';
                        document.getElementById('status').value = project.status || '';
                        document.getElementById('data_inicio').value = project.data_inicio || '';
                        document.getElementById('data_conclusao_prevista').value = project.data_conclusao_prevista || '';
                        document.getElementById('orcamento').value = project.orcamento || '';
                        document.getElementById('descricao').value = project.descricao || '';
                        console.log('Campos preenchidos com sucesso!');
                        showMessage('Dados carregados com sucesso!', false);
                    } else {
                        console.error('Projeto não encontrado ou dados inválidos:', project);
                        showMessage('Projeto não encontrado.', true);
                        setTimeout(() => window.location.href = '../listas/lista_projetos.php', 3000);
                    }
                } catch (error) {
                    console.error('Erro ao buscar dados do projeto:', error);
                    showMessage('Erro ao carregar dados do projeto: ' + error.message, true);
                }
            }

            // Função para carregar status de projeto do backend
            async function loadStatusProjeto() {
                try {
                    const response = await fetch('../api/valores_fixos.php?tipo=status_projeto');
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    const statusList = await response.json();
                    statusSelect.innerHTML = '<option value="">Selecione o Status</option>';
                    statusList.forEach(status => {
                        const option = document.createElement('option');
                        option.value = status.valor;
                        option.textContent = status.valor;
                        statusSelect.appendChild(option);
                    });
                } catch (error) {
                    console.error('Erro ao carregar status de projeto:', error);
                    showMessage('Não foi possível carregar os status de projeto.', true);
                    statusSelect.innerHTML = '<option value="">Erro ao carregar</option>';
                }
            }

            // Carregar clientes e, se for edição, carregar dados do projeto
            fetchClientsForSelect().then(() => {
                if (projectId) {
                    pageTitleTextElement.textContent = 'Editar Registro de Projeto';
                    submitButton.textContent = 'Atualizar Projeto';
                    fetchProjectData(projectId);
                } else {
                    // Verificar se visualizador está tentando cadastrar novo projeto
                    if (isViewer) {
                        showMessage('Acesso negado: Visualizadores não podem criar novos registros.', true);
                        setTimeout(() => {
                            window.location.href = '../listas/lista_projetos.php';
                        }, 3000);
                        return;
                    }
                    pageTitleTextElement.textContent = 'Cadastro de Novo Projeto';
                    submitButton.textContent = 'Cadastrar Projeto';
                }
            });

            // Carregar status de projeto ao iniciar a página
            loadStatusProjeto();

            form.addEventListener('submit', async function(event) {
                event.preventDefault();

                const formData = new FormData(form);
                const data = {};
                formData.forEach((value, key) => {
                    data[key] = value;
                });

                let url = '../api/projetos.php';
                let method = 'POST';

                if (projectId) {
                    url = `../api/projetos.php?id=${projectId}`;
                    method = 'PUT';
                }

                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (response.ok) {
                        showMessage(result.message);
                        setTimeout(() => {
                            window.location.href = '../listas/lista_projetos.php';
                        }, 2000);
                    } else {
                        showMessage('Erro: ' + result.message, true);
                        console.error('Erro da API:', result.error);
                    }
                } catch (error) {
                    console.error('Erro ao enviar formulário:', error);
                    showMessage('Erro ao conectar com o servidor.', true);
                }
            });
        });
    </script>
</body>
</html>