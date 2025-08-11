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
    <title>Cadastro/Edição de Colaborador - Sistema de Engenharia</title>
    <!-- Importa a fonte 'Inter' do Google Fonts para um visual moderno -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <!-- Link para o Font Awesome para os ícones (versão mais recente) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Link para o arquivo de estilos CSS global -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Scripts do sistema -->
    <script src="../assets/js/toast-system.js"></script>
    <script src="../assets/js/breadcrumb-system.js"></script>
    <script src="../assets/js/lazy-loading.js"></script>
    <script src="../assets/js/global-search.js"></script>
    <script src="../assets/js/page-header.js"></script>
    <script src="../assets/js/dynamic-cargo-system.js"></script>
    
    <!-- Utilitários de Validação -->
    <script src="../assets/js/validation-utils.js"></script>
    <style>
        /* Estilos adicionais para o body e main-content */
        body {
            display: flex;
            flex-direction: column; /* Organiza o conteúdo em coluna */
            min-height: 100vh;
            text-align: center;
            position: relative; /* Necessário para z-index do menu-toggle */
            font-family: 'Inter', sans-serif; /* Aplica a fonte Inter */
        }

        /* O main-content já tem padding-top no style.css para a barra superior */
        .main-content {
            font-size: 1.5rem;
            color: var(--cor-texto-claro);
            width: 100%; /* Garante que o conteúdo ocupe a largura total */
            flex-grow: 1; /* Permite que o conteúdo ocupe o espaço restante */
            padding-bottom: 2rem; /* Adiciona padding na parte inferior do conteúdo */
        }

        /* Estilos específicos para o formulário de cadastro de colaborador */
        .collaborator-form-container {
            background-color: var(--cor-fundo-card);
            color: var(--cor-texto-escuro);
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: var(--sombra-media);
            max-width: 800px; /* Largura máxima para o formulário */
            margin: 2rem auto; /* Centraliza e adiciona margem */
            text-align: left; /* Alinha o texto do formulário à esquerda */
        }

        .collaborator-form-container h2 {
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
            min-height: 100px; /* Altura mínima para o campo de observações */
            resize: vertical; /* Permite redimensionamento vertical */
        }
        
        .form-group input[type="file"] {
            padding: 0.75rem; /* Ajusta o padding para inputs de arquivo */
            background-color: var(--cor-fundo-card-hover);
            cursor: pointer;
        }

        /* Estilos para a pré-visualização da imagem */
        .image-preview-container {
            margin-top: 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            grid-column: 1 / -1; /* Ocupa a largura total do grid */
            position: relative;
        }

        .image-preview {
            width: 120px; /* Tamanho da imagem */
            height: 120px; /* Tamanho da imagem */
            border-radius: 50%; /* Torna a imagem redonda */
            object-fit: cover; /* Garante que a imagem preencha o círculo */
            border: 2px solid var(--cor-principal); /* Borda para destaque */
            display: none; /* Esconde por padrão */
        }

        .remove-photo-btn {
            position: absolute;
            top: 0;
            right: calc(50% - 70px);
            background: var(--cor-erro);
            color: white;
            border: none;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 0.8rem;
            display: none;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .remove-photo-btn:hover {
            background: #dc2626;
            transform: scale(1.1);
        }

        .image-preview-container:hover .remove-photo-btn {
            display: flex;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end; /* Alinha botões à direita */
            gap: 1rem;
            margin-top: 2rem;
        }

        /* Estilos para campos com erro */
        .form-group input.error,
        .form-group select.error,
        .form-group textarea.error {
            border: 2px solid var(--cor-erro);
            box-shadow: 0 0 0 3px rgba(255, 0, 0, 0.2);
            animation: shake 0.5s ease-in-out;
        }

        .error-message {
            color: var(--cor-erro);
            font-size: 0.8rem;
            margin-top: 0.5rem;
            display: none; /* Oculto por padrão */
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Loading states para botões */
        .btn.loading {
            opacity: 0.7;
            cursor: not-allowed;
            position: relative;
        }

        .btn.loading::after {
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


        /* Responsividade para telas menores */
        @media (max-width: 768px) {
            .collaborator-form-container {
                padding: 1.5rem;
                margin: 1rem auto;
            }
            .collaborator-form-container h2 {
                font-size: 1.5rem;
            }
            .form-grid {
                grid-template-columns: 1fr; /* Uma única coluna em telas pequenas */
                gap: 1rem;
            }
            .form-actions {
                flex-direction: column; /* Empilha botões em telas pequenas */
                gap: 0.8rem;
            }
            .form-actions .btn {
                width: 100%; /* Botões ocupam a largura total */
            }
            .modal-content {
                width: 90%;
                padding: 20px;
            }
            .modal-content h3 {
                font-size: 1.3rem;
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
        <div class="collaborator-form-container">
            <h2><span id="pageTitleText"></span></h2>
            <!-- Caixa de mensagem para feedback -->
            <div id="messageBox" class="message-box"></div>
            <form id="collaboratorForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="collaboratorId" name="id"> <!-- Campo de ID oculto para o modo de edição -->
                <div class="form-grid">
                    <div class="image-preview-container">
                        <label for="foto_url">Foto de Perfil</label>
                        <input type="file" id="foto_url" name="foto_url" accept="image/*">
                        <img id="previewImage" class="image-preview" src="#" alt="Pré-visualização da imagem">
                        <button type="button" id="removePhotoBtn" class="remove-photo-btn" title="Remover foto">
                            <i class="fas fa-times"></i>
                        </button>
                        <span id="previewText" style="font-size: 0.9rem; color: var(--cor-secundaria);">Nenhuma imagem selecionada</span>
                    </div>

                    <div class="form-group">
                        <label for="nome_completo">Nome Completo</label>
                        <input type="text" id="nome_completo" name="nome" placeholder="Nome completo do colaborador" required>
                    </div>

                    <div class="form-group">
                        <label for="cpf">CPF</label>
                        <input type="text" id="cpf" name="cpf" placeholder="XXX.XXX.XXX-XX" pattern="\d{3}\.\d{3}\.\d{3}-\d{2}" title="Formato: 000.000.000-00" required>
                        <span id="cpf-error" class="error-message"></span>
                    </div>

                    <div class="form-group">
                        <label for="cargo">Cargo</label>
                        <select id="cargo" name="cargo" required>
                            <option value="">Carregando cargos...</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="departamento">Departamento</label>
                        <select id="departamento" name="departamento" required>
                            <option value="">Carregando Departamentos...</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" placeholder="colaborador@empresa.com" required>
                    </div>

                    <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input type="tel" id="telefone" name="telefone" placeholder="(XX) XXXXX-XXXX">
                    </div>

                    <div class="form-group">
                        <label for="data_contratacao">Data de Contratação</label>
                        <input type="date" id="data_contratacao" name="data_contratacao" required>
                    </div>

                    

                    <div class="form-group">
                        <label for="nivel_acesso">Nível de Acesso</label>
                        <select id="nivel_acesso" name="nivel_acesso" required>
                            <option value="">Selecione o Nível</option>
                            <option value="Administrador">Administrador</option>
                            <option value="Usuário">Usuário</option>
                            <option value="Visualizador">Visualizador</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="observacoes">Observações</label>
                    <textarea id="observacoes" name="observacoes" placeholder="Informações adicionais sobre o colaborador..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn btn-secondary">Limpar Formulário</button>
                    <button type="submit" class="btn btn-primary" id="submitButton">Cadastrar Colaborador</button>
                </div>
            </form>
        </div>
    </main>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elementos do DOM
            const form = document.getElementById('collaboratorForm');
            const submitButton = document.getElementById('submitButton');
            const resetButton = form.querySelector('button[type="reset"]');
            const pageTitleTextElement = document.getElementById('pageTitleText');
            const telefoneInput = document.getElementById('telefone');
            const cpfInput = document.getElementById('cpf');
            const cpfError = document.getElementById('cpf-error');
            const emailInput = document.getElementById('email');
            const departamentoSelect = document.getElementById('departamento');
            
            const dataContratacaoInput = document.getElementById('data_contratacao');
            const fotoUrlInput = document.getElementById('foto_url');
            const previewImage = document.getElementById('previewImage');
            const previewText = document.getElementById('previewText');
            const removePhotoBtn = document.getElementById('removePhotoBtn');
            const collaboratorIdInput = document.getElementById('collaboratorId');

            // Estado da aplicação
            let isEditing = false;
            let originalPhotoUrl = '';
            let photoRemoved = false;

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

            // Aplicar máscaras nos campos
            telefoneInput.addEventListener('input', (e) => {
                e.target.value = MaskUtils.phoneMask(e.target.value);
            });

            cpfInput.addEventListener('input', (e) => {
                e.target.value = MaskUtils.cpfMask(e.target.value);
            });

            // Validação em tempo real
            cpfInput.addEventListener('blur', function() {
                if (this.value && !ValidationUtils.validateCPF(this.value)) {
                    this.classList.add('error');
                    cpfError.textContent = 'Por favor, digite um CPF válido.';
                    cpfError.style.display = 'block';
                    Modal.error('CPF Inválido', 'Por favor, digite um CPF válido.');
                } else {
                    this.classList.remove('error');
                    cpfError.style.display = 'none';
                }
            });

            emailInput.addEventListener('blur', function() {
                if (this.value && !ValidationUtils.validateEmail(this.value)) {
                    this.classList.add('error');
                    Modal.error('E-mail Inválido', 'Por favor, digite um e-mail válido.');
                } else {
                    this.classList.remove('error');
                }
            });

            dataContratacaoInput.addEventListener('blur', function() {
                if (this.value && !ValidationUtils.validateDateNotFuture(this.value)) {
                    this.classList.add('error');
                    Modal.error('Data Inválida', 'A data de contratação não pode ser futura.');
                } else {
                    this.classList.remove('error');
                }
            });

            

            // Remove classe de erro ao digitar
            form.addEventListener('input', function(event) {
                if (event.target.classList.contains('error')) {
                    event.target.classList.remove('error');
                }
                // Esconde a mensagem de erro do CPF ao digitar
                if (event.target.id === 'cpf') {
                    cpfError.style.display = 'none';
                }
            });

            // Gerenciamento de foto
            fotoUrlInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    // Validar tamanho do arquivo (máximo 5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        Modal.error('Arquivo muito grande', 'A imagem deve ter no máximo 5MB.');
                        this.value = '';
                        return;
                    }

                    // Validar tipo do arquivo
                    if (!file.type.startsWith('image/')) {
                        Modal.error('Tipo inválido', 'Por favor, selecione apenas arquivos de imagem.');
                        this.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        previewImage.style.display = 'block';
                        previewText.style.display = 'none';
                        photoRemoved = false;
                    };
                    reader.readAsDataURL(file);
                } else {
                    resetPhotoPreview();
                }
            });

            // Botão de remover foto
            removePhotoBtn.addEventListener('click', function() {
                Modal.confirm(
                    'Remover Foto', 
                    'Tem certeza de que deseja remover a foto?'
                ).then(() => {
                    resetPhotoPreview();
                    fotoUrlInput.value = '';
                    photoRemoved = true;
                }).catch(() => {
                    // Usuário cancelou
                });
            });

            function resetPhotoPreview() {
                previewImage.src = '#';
                previewImage.style.display = 'none';
                previewText.style.display = 'block';
            }

            // Confirmação antes de limpar formulário
            resetButton.addEventListener('click', function(event) {
                event.preventDefault();
                Modal.confirm(
                    'Limpar Formulário', 
                    'Tem certeza de que deseja limpar todos os campos?'
                ).then(() => {
                    form.reset();
                    resetPhotoPreview();
                    photoRemoved = false;
                    // Remove todos os erros
                    form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
                }).catch(() => {
                    // Usuário cancelou
                });
            });

            // Detectar modo de cadastro ou edição
            const urlParams = new URLSearchParams(window.location.search);
            const collaboratorId = urlParams.get('id');

            const isViewer = <?php echo json_encode($is_viewer); ?>;

            if (collaboratorId) {
                isEditing = true;
                if (isViewer) {
                    pageTitleTextElement.textContent = 'Visualizar Registro de Colaborador';
                } else {
                    pageTitleTextElement.textContent = 'Editar Registro de Colaborador';
                }
                submitButton.textContent = 'Atualizar Colaborador';
                collaboratorIdInput.value = collaboratorId;
                fetchCollaboratorData(collaboratorId);
            } else {
                // Verificar se visualizador está tentando cadastrar novo colaborador
                if (isViewer) {
                    showMessage('Acesso negado: Visualizadores não podem criar novos registros.', true);
                    setTimeout(() => {
                        window.location.href = '../listas/lista_colaboradores.php';
                    }, 3000);
                    return;
                }
                pageTitleTextElement.textContent = 'Cadastro de Novo Colaborador';
                submitButton.textContent = 'Cadastrar Colaborador';
            }

            // Buscar dados do colaborador para edição
            async function fetchCollaboratorData(id) {
                try {
                    console.log('Iniciando fetchCollaboratorData para ID:', id);
                    const response = await fetch(`../api/colaboradores.php?id=${id}`);
                    console.log('Response status:', response.status);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const collaborator = await response.json();
                    console.log('Dados recebidos do colaborador:', collaborator);
                    
                    if (collaborator && collaborator.id) {
                        console.log('Preenchendo campos do formulário...');
                        // Preencher campos
                        document.getElementById('nome_completo').value = collaborator.nome || '';
                        document.getElementById('cpf').value = collaborator.cpf || '';
                        document.getElementById('cargo').value = collaborator.cargo || '';
                        document.getElementById('departamento').value = collaborator.departamento || '';
                        document.getElementById('email').value = collaborator.email || '';
                        document.getElementById('telefone').value = collaborator.telefone || '';
                        document.getElementById('data_contratacao').value = collaborator.data_contratacao || '';
                        
                        document.getElementById('nivel_acesso').value = collaborator.nivel_acesso || '';
                        document.getElementById('observacoes').value = collaborator.observacoes || '';
                        console.log('Campos preenchidos com sucesso!');
                        showMessage('Dados carregados com sucesso!', false);

                        // Foto existente
                        if (collaborator.foto_url) {
                            originalPhotoUrl = collaborator.foto_url;
                            previewImage.src = '../' + collaborator.foto_url;
                            previewImage.style.display = 'block';
                            previewText.style.display = 'none';
                        }
                    } else {
                        console.error('Colaborador não encontrado ou dados inválidos:', collaborator);
                        showMessage('Colaborador não encontrado.', true);
                        setTimeout(() => window.location.href = '../listas/lista_colaboradores.php', 3000);
                    }
                } catch (error) {
                    console.error('Erro ao buscar dados do colaborador:', error);
                    showMessage('Erro ao carregar dados do colaborador: ' + error.message, true);
                }
            }

            // Função para carregar departamentos do backend
            async function loadDepartamentos() {
                try {
                    const response = await fetch('../api/valores_fixos.php?tipo=departamento');
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    const departamentos = await response.json();
                    departamentoSelect.innerHTML = '<option value="">Selecione um Departamento</option>';
                    departamentos.forEach(dep => {
                        const option = document.createElement('option');
                        option.value = dep.valor;
                        option.textContent = dep.valor;
                        departamentoSelect.appendChild(option);
                    });
                } catch (error) {
                    console.error('Erro ao carregar departamentos:', error);
                    Modal.error('Erro', 'Não foi possível carregar os departamentos.');
                    departamentoSelect.innerHTML = '<option value="">Erro ao carregar</option>';
                }
            }

            // Carregar departamentos ao iniciar a página
            loadDepartamentos();

            // Validação completa do formulário
            function validateForm() {
                const errors = [];

                // Validações obrigatórias
                if (!ValidationUtils.validateRequired(document.getElementById('nome_completo').value)) {
                    errors.push({ field: 'nome_completo', message: 'Nome completo é obrigatório.' });
                }

                if (!ValidationUtils.validateRequired(document.getElementById('cpf').value)) {
                    errors.push({ field: 'cpf', message: 'CPF é obrigatório.' });
                } else if (!ValidationUtils.validateCPF(document.getElementById('cpf').value)) {
                    errors.push({ field: 'cpf', message: 'CPF inválido.' });
                }

                if (!ValidationUtils.validateRequired(document.getElementById('cargo').value)) {
                    errors.push({ field: 'cargo', message: 'Cargo é obrigatório.' });
                }

                if (!ValidationUtils.validateRequired(document.getElementById('departamento').value)) {
                    errors.push({ field: 'departamento', message: 'Departamento é obrigatório.' });
                }

                if (!ValidationUtils.validateRequired(document.getElementById('email').value)) {
                    errors.push({ field: 'email', message: 'E-mail é obrigatório.' });
                } else if (!ValidationUtils.validateEmail(document.getElementById('email').value)) {
                    errors.push({ field: 'email', message: 'E-mail inválido.' });
                }

                if (!ValidationUtils.validateRequired(document.getElementById('data_contratacao').value)) {
                    errors.push({ field: 'data_contratacao', message: 'Data de contratação é obrigatória.' });
                } else if (!ValidationUtils.validateDateNotFuture(document.getElementById('data_contratacao').value)) {
                    errors.push({ field: 'data_contratacao', message: 'Data de contratação não pode ser futura.' });
                }

                

                if (!ValidationUtils.validateRequired(document.getElementById('nivel_acesso').value)) {
                    errors.push({ field: 'nivel_acesso', message: 'Nível de acesso é obrigatório.' });
                }

                // Validação de telefone (se preenchido)
                const telefone = document.getElementById('telefone').value;
                if (telefone && !ValidationUtils.validatePhone(telefone)) {
                    errors.push({ field: 'telefone', message: 'Telefone inválido.' });
                }

                return errors;
            }

            // Envio do formulário
            form.addEventListener('submit', async function(event) {
                event.preventDefault();

                // Validar formulário
                const errors = validateForm();
                if (errors.length > 0) {
                    // Destacar primeiro campo com erro
                    const firstError = errors[0];
                    const field = document.getElementById(firstError.field);
                    field.classList.add('error');
                    field.focus();
                    
                    // Mostrar erro
                    Modal.error('Erro de Validação', firstError.message);
                    return;
                }

                // Mostrar loading
                setButtonLoading(submitButton, true);

                const formData = new FormData(form);
                
                // Se foto foi removida, adicionar flag
                if (photoRemoved) {
                    formData.append('foto_url_removed', 'true');
                }

                let url = '../api/colaboradores.php';
                let method = 'POST';

                if (isEditing) {
                    url = `../api/colaboradores.php?id=${collaboratorId}`;
                    method = 'PUT';
                }

                try {
                    const response = await fetch(url, {
                        method: method,
                        body: formData
                    });

                    const result = await response.json();

                    if (response.ok) {
                        const message = isEditing ? 
                            'Colaborador atualizado com sucesso!' : 
                            'Colaborador cadastrado com sucesso!';
                        
                        Modal.success('Sucesso', message).then(() => {
                            window.location.href = '../listas/lista_colaboradores.php';
                        });

                        // Limpar formulário se for cadastro
                        if (!isEditing) {
                            setTimeout(() => {
                                form.reset();
                                resetPhotoPreview();
                                photoRemoved = false;
                            }, 1000);
                        }
                    } else {
                        // Erro da API
                        if (result.field) {
                            const field = document.getElementById(result.field);
                            if (field) {
                                field.classList.add('error');
                                field.focus();
                            }
                        }
                        Modal.error('Erro', result.message || 'Ocorreu um erro desconhecido.');
                    }
                } catch (error) {
                    console.error('Erro ao enviar formulário:', error);
                    Modal.error('Erro', 'Erro ao conectar com o servidor.');
                } finally {
                    setButtonLoading(submitButton, false);
                }
            });

            // Função para loading nos botões
            function setButtonLoading(button, loading) {
                if (loading) {
                    button.disabled = true;
                    button.classList.add('loading');
                    button.dataset.originalText = button.textContent;
                    button.textContent = 'Processando...';
                } else {
                    button.disabled = false;
                    button.classList.remove('loading');
                    if (button.dataset.originalText) {
                        button.textContent = button.dataset.originalText;
                    }
                }
            }
        });
    </script>
</body>
</html>
