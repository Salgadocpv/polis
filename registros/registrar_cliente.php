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
    <title>Cadastro/Edição de Cliente - Sistema de Engenharia</title>
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
    <script src="../assets/js/address-system.js"></script>
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

        /* Estilos específicos para o formulário de cadastro de cliente */
        .client-form-container {
            background-color: var(--cor-fundo-card);
            color: var(--cor-texto-escuro);
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: var(--sombra-media);
            max-width: 800px; /* Largura máxima para o formulário */
            margin: 2rem auto; /* Centraliza e adiciona margem */
            text-align: left; /* Alinha o texto do formulário à esquerda */
        }

        .client-form-container h2 {
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
        .form-group textarea:focus {
            outline: none;
            border-color: var(--cor-vibrante);
            box-shadow: 0 0 0 3px rgba(0, 180, 216, 0.2);
        }

        .form-group textarea {
            min-height: 100px; /* Altura mínima para o campo de observações */
            resize: vertical; /* Permite redimensionamento vertical */
        }

        .form-actions {
            display: flex;
            justify-content: flex-end; /* Alinha botões à direita */
            gap: 1rem;
            margin-top: 2rem;
        }

        /* Responsividade para telas menores */
        @media (max-width: 768px) {
            .client-form-container {
                padding: 1.5rem;
                margin: 1rem auto;
            }
            .client-form-container h2 {
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
        <div class="client-form-container">
            <h2><span id="pageTitleText"></span></h2>
            <form id="clientForm">
                <!-- Adiciona um campo de ID oculto para uso no modo de edição -->
                <input type="hidden" id="clientId" name="id">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nome_completo">Nome Completo / Razão Social</label>
                        <input type="text" id="nome_completo" name="nome" placeholder="Nome ou Razão Social do Cliente" required>
                    </div>

                    <div class="form-group">
                        <label for="cpf_cnpj">CPF / CNPJ</label>
                        <input type="text" id="cpf_cnpj" name="cpf_cnpj" placeholder="CPF ou CNPJ do Cliente" required>
                    </div>

                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" placeholder="email@exemplo.com" required>
                    </div>

                    <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input type="tel" id="telefone" name="telefone" placeholder="(XX) XXXXX-XXXX">
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado" required>
                            <option value="">Carregando estados...</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="cep">CEP</label>
                        <input type="text" id="cep" name="cep" placeholder="XXXXX-XXX" pattern="^\d{5}-\d{3}$" title="Formato: XXXXX-XXX">
                    </div>

                    <div class="form-group">
                        <label for="cidade">Cidade</label>
                        <select id="cidade" name="cidade" required disabled>
                            <option value="">Selecione o Estado primeiro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="bairro">Bairro</label>
                        <select id="bairro" name="bairro" required disabled>
                            <option value="">Selecione a Cidade primeiro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="rua">Rua</label>
                        <select id="rua" name="rua" required disabled>
                            <option value="">Selecione o Bairro primeiro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="numero">Número</label>
                        <input type="text" id="numero" name="numero" placeholder="Número" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="observacoes">Observações</label>
                    <textarea id="observacoes" name="observacoes" placeholder="Informações adicionais sobre o cliente..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn btn-secondary">Limpar Formulário</button>
                    <button type="submit" class="btn btn-primary" id="submitButton">Cadastrar Cliente</button>
                </div>
            </form>
        </div>
    </main>

    <!-- Caixa de mensagem para feedback do usuário -->
    <div id="messageBox" class="message-box"></div>

    <!-- Script JavaScript para a lógica da página -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('clientForm');
            const submitButton = document.getElementById('submitButton');
            const pageTitleTextElement = document.getElementById('pageTitleText');
            const telefoneInput = document.getElementById('telefone');
            const clientIdInput = document.getElementById('clientId');
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

            // Função para aplicar a máscara de telefone (melhorada)
            function formatPhoneNumber(value) {
                value = value.replace(/\D/g, ''); // Remove tudo que não é dígito
                if (value.length > 11) {
                    value = value.slice(0, 11); // Limita o tamanho para DDD + 9 dígitos
                }
                if (value.length > 10) { // Formato (XX) XXXXX-XXXX
                    value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
                } else if (value.length > 6) { // Formato (XX) XXXX-XXXX
                    value = value.replace(/^(\d{2})(\d{4})(\d{4}).*/, '($1) $2-$3');
                } else if (value.length > 2) { // Formato (XX) XXXX
                    value = value.replace(/^(\d{2})(\d{0,5}).*/, '($1) $2');
                } else if (value.length > 0) { // Formato (XX
                    value = value.replace(/^(\d*)/, '($1');
                }
                return value;
            }

            // Adiciona o event listener para a máscara de telefone
            telefoneInput.addEventListener('input', (e) => {
                e.target.value = formatPhoneNumber(e.target.value);
            });

            // Sistema de endereços encadeados
            const estadoSelect = document.getElementById('estado');
            const cidadeSelect = document.getElementById('cidade');
            const bairroSelect = document.getElementById('bairro');
            const ruaSelect = document.getElementById('rua');
            const cepInput = document.getElementById('cep');

            // Carregar estados na inicialização
            async function loadEstados() {
                try {
                    const response = await fetch('../api/enderecos.php?action=estados');
                    const estados = await response.json();
                    
                    estadoSelect.innerHTML = '<option value="">Selecione o Estado</option>';
                    estados.forEach(estado => {
                        const option = document.createElement('option');
                        option.value = estado.sigla;
                        option.textContent = `${estado.nome} (${estado.sigla})`;
                        estadoSelect.appendChild(option);
                    });
                } catch (error) {
                    console.error('Erro ao carregar estados:', error);
                    showMessage('Erro ao carregar estados', true);
                }
            }

            // Carregar cidades quando estado for selecionado
            estadoSelect.addEventListener('change', async function() {
                const estado = this.value;
                
                // Resetar campos dependentes
                cidadeSelect.innerHTML = '<option value="">Carregando cidades...</option>';
                cidadeSelect.disabled = false;
                bairroSelect.innerHTML = '<option value="">Selecione a Cidade primeiro</option>';
                bairroSelect.disabled = true;
                ruaSelect.innerHTML = '<option value="">Selecione o Bairro primeiro</option>';
                ruaSelect.disabled = true;

                if (!estado) {
                    cidadeSelect.innerHTML = '<option value="">Selecione o Estado primeiro</option>';
                    cidadeSelect.disabled = true;
                    return;
                }

                try {
                    const response = await fetch(`../api/enderecos.php?action=cidades&estado=${estado}`);
                    const cidades = await response.json();
                    
                    cidadeSelect.innerHTML = '<option value="">Selecione a Cidade</option>';
                    cidades.forEach(cidade => {
                        const option = document.createElement('option');
                        option.value = cidade;
                        option.textContent = cidade;
                        cidadeSelect.appendChild(option);
                    });
                } catch (error) {
                    console.error('Erro ao carregar cidades:', error);
                    showMessage('Erro ao carregar cidades', true);
                    cidadeSelect.innerHTML = '<option value="">Erro ao carregar</option>';
                }
            });

            // Carregar bairros quando cidade for selecionada
            cidadeSelect.addEventListener('change', async function() {
                const cidade = this.value;
                
                // Resetar campos dependentes
                bairroSelect.innerHTML = '<option value="">Carregando bairros...</option>';
                bairroSelect.disabled = false;
                ruaSelect.innerHTML = '<option value="">Selecione o Bairro primeiro</option>';
                ruaSelect.disabled = true;

                if (!cidade) {
                    bairroSelect.innerHTML = '<option value="">Selecione a Cidade primeiro</option>';
                    bairroSelect.disabled = true;
                    return;
                }

                try {
                    const response = await fetch(`../api/enderecos.php?action=bairros&cidade=${encodeURIComponent(cidade)}`);
                    const bairros = await response.json();
                    
                    bairroSelect.innerHTML = '<option value="">Selecione o Bairro</option>';
                    bairros.forEach(bairro => {
                        const option = document.createElement('option');
                        option.value = bairro;
                        option.textContent = bairro;
                        bairroSelect.appendChild(option);
                    });
                } catch (error) {
                    console.error('Erro ao carregar bairros:', error);
                    showMessage('Erro ao carregar bairros', true);
                    bairroSelect.innerHTML = '<option value="">Erro ao carregar</option>';
                }
            });

            // Carregar ruas quando bairro for selecionado
            bairroSelect.addEventListener('change', async function() {
                const bairro = this.value;
                
                // Resetar campo dependente
                ruaSelect.innerHTML = '<option value="">Carregando ruas...</option>';
                ruaSelect.disabled = false;

                if (!bairro) {
                    ruaSelect.innerHTML = '<option value="">Selecione o Bairro primeiro</option>';
                    ruaSelect.disabled = true;
                    return;
                }

                try {
                    const response = await fetch(`../api/enderecos.php?action=ruas&bairro=${encodeURIComponent(bairro)}`);
                    const ruas = await response.json();
                    
                    ruaSelect.innerHTML = '<option value="">Selecione a Rua</option>';
                    ruas.forEach(rua => {
                        const option = document.createElement('option');
                        option.value = rua;
                        option.textContent = rua;
                        ruaSelect.appendChild(option);
                    });
                } catch (error) {
                    console.error('Erro ao carregar ruas:', error);
                    showMessage('Erro ao carregar ruas', true);
                    ruaSelect.innerHTML = '<option value="">Erro ao carregar</option>';
                }
            });

            // Busca automática por CEP
            cepInput.addEventListener('blur', async function() {
                const cep = this.value.replace(/\D/g, '');
                
                if (cep.length === 8) {
                    try {
                        const response = await fetch(`../api/enderecos.php?action=cep&cep=${cep}`);
                        const data = await response.json();
                        
                        if (data.estado && data.cidade && data.bairro && data.rua) {
                            // Preencher estado
                            estadoSelect.value = data.estado;
                            estadoSelect.dispatchEvent(new Event('change'));
                            
                            // Aguardar carregamento das cidades e preencher
                            setTimeout(async () => {
                                cidadeSelect.value = data.cidade;
                                cidadeSelect.dispatchEvent(new Event('change'));
                                
                                // Aguardar carregamento dos bairros e preencher
                                setTimeout(async () => {
                                    bairroSelect.value = data.bairro;
                                    bairroSelect.dispatchEvent(new Event('change'));
                                    
                                    // Aguardar carregamento das ruas e preencher
                                    setTimeout(() => {
                                        ruaSelect.value = data.rua;
                                    }, 1000);
                                }, 1000);
                            }, 1000);
                            
                            showMessage('Endereço preenchido automaticamente via CEP', false);
                        }
                    } catch (error) {
                        console.error('Erro ao buscar CEP:', error);
                        showMessage('Erro ao buscar endereço pelo CEP', true);
                    }
                }
            });

            // Máscara para CEP
            cepInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 5) {
                    value = value.replace(/^(\d{5})(\d)/, '$1-$2');
                }
                if (value.length > 9) {
                    value = value.slice(0, 9);
                }
                e.target.value = value;
            });

            // Inicializar carregamento de estados
            loadEstados();

            // Lógica para detectar se é modo de cadastro ou edição
            const urlParams = new URLSearchParams(window.location.search);
            const clientId = urlParams.get('id');

            const isViewer = <?php echo json_encode($is_viewer); ?>;

            if (clientId) {
                if (isViewer) {
                    pageTitleTextElement.textContent = 'Visualizar Registro de Cliente';
                } else {
                    pageTitleTextElement.textContent = 'Editar Registro de Cliente';
                }
                submitButton.textContent = 'Atualizar Cliente';
                clientIdInput.value = clientId; // Define o ID do cliente no input oculto
                fetchClientData(clientId);
            } else {
                // Verificar se visualizador está tentando cadastrar novo cliente
                if (isViewer) {
                    showMessage('Acesso negado: Visualizadores não podem criar novos registros.', true);
                    setTimeout(() => {
                        window.location.href = '../listas/lista_clientes.php';
                    }, 3000);
                    return;
                }
                pageTitleTextElement.textContent = 'Cadastro de Novo Cliente';
                submitButton.textContent = 'Cadastrar Cliente';
            }

            // Função para buscar os dados do cliente para edição
            async function fetchClientData(id) {
                try {
                    console.log('Iniciando fetchClientData para ID:', id);
                    const response = await fetch(`../api/clientes.php?id=${id}`);
                    console.log('Response status:', response.status);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const client = await response.json();
                    console.log('Dados recebidos do cliente:', client);
                    
                    if (client && client.id) {
                        console.log('Preenchendo campos do formulário...');
                        document.getElementById('nome_completo').value = client.nome || '';
                        document.getElementById('cpf_cnpj').value = client.cpf_cnpj || '';
                        document.getElementById('email').value = client.email || '';
                        document.getElementById('telefone').value = client.telefone || '';
                        document.getElementById('numero').value = client.numero || '';
                        document.getElementById('cep').value = client.cep || '';
                        document.getElementById('observacoes').value = client.observacoes || '';
                        
                        // Carregar endereço em cascata para edição
                        if (client.estado) {
                            estadoSelect.value = client.estado;
                            await estadoSelect.dispatchEvent(new Event('change'));
                            
                            // Aguardar carregamento e selecionar cidade
                            setTimeout(async () => {
                                if (client.cidade) {
                                    cidadeSelect.value = client.cidade;
                                    await cidadeSelect.dispatchEvent(new Event('change'));
                                    
                                    // Aguardar carregamento e selecionar bairro
                                    setTimeout(async () => {
                                        if (client.bairro) {
                                            bairroSelect.value = client.bairro;
                                            await bairroSelect.dispatchEvent(new Event('change'));
                                            
                                            // Aguardar carregamento e selecionar rua
                                            setTimeout(() => {
                                                if (client.rua) {
                                                    ruaSelect.value = client.rua;
                                                }
                                            }, 1000);
                                        }
                                    }, 1000);
                                }
                            }, 1000);
                        }
                        
                        console.log('Campos preenchidos com sucesso!');
                        showMessage('Dados carregados com sucesso!', false);
                    } else {
                        console.error('Cliente não encontrado ou dados inválidos:', client);
                        showMessage('Cliente não encontrado.', true);
                        // Redireciona para a lista de clientes após um tempo
                        setTimeout(() => window.location.href = '../listas/lista_clientes.php', 3000);
                    }
                } catch (error) {
                    console.error('Erro ao buscar dados do cliente:', error);
                    showMessage('Erro ao carregar dados do cliente: ' + error.message, true);
                }
            }

            // Lógica para o envio do formulário
            form.addEventListener('submit', async function(event) {
                event.preventDefault(); // Impede o envio padrão do formulário

                // Cria um objeto FormData a partir do formulário
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());

                const method = clientId ? 'PUT' : 'POST'; // Usa PUT para edição, POST para cadastro
                const url = '../api/clientes.php';

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
                        showMessage(result.message || 'Operação realizada com sucesso!');
                        // Limpa o formulário ou redireciona
                        if (!clientId) {
                            form.reset();
                        }
                        // Redireciona para a lista de clientes após o sucesso
                        setTimeout(() => window.location.href = '../listas/lista_clientes.php', 3000);
                    } else {
                        throw new Error(result.error || 'Erro na operação.');
                    }
                } catch (error) {
                    console.error('Erro ao enviar o formulário:', error);
                    showMessage(error.message, true);
                }
            });
        });
    </script>
</body>
</html>
