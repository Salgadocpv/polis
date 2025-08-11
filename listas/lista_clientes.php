<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Clientes - Sistema de Engenharia</title>
    <!-- Importa a fonte 'Inter' do Google Fonts para um visual moderno -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <!-- Link para o Font Awesome para os ícones (versão mais recente) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSC鉴定=" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Link para o arquivo de estilos CSS global -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Scripts do sistema -->
    <script src="../assets/js/toast-system.js"></script>
    <script src="../assets/js/breadcrumb-system.js"></script>
    <script src="../assets/js/lazy-loading.js"></script>
    <script src="../assets/js/global-search.js"></script>
    <script src="../assets/js/excel-export.js"></script>
    <script src="../assets/js/page-header.js"></script>
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
            font-size: 1rem; /* Ajuste para o texto do conteúdo */
            color: var(--cor-texto-claro);
            width: 100%; /* Garante que o conteúdo ocupe a largura total */
            flex-grow: 1; /* Permite que o conteúdo ocupe o espaço restante */
            padding-bottom: 2rem; /* Adiciona padding na parte inferior do conteúdo */
        }

        /* Estilos gerais para o container da lista */
        .client-list-container {
            background-color: var(--cor-fundo-card);
            color: var(--cor-texto-escuro);
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: var(--sombra-media);
            max-width: 1200px; /* Largura máxima */
            margin: 2rem auto; /* Centraliza e adiciona margem */
            text-align: left;
            overflow-x: auto; /* Permite rolagem horizontal para a tabela em telas pequenas */
        }

        .client-list-container h2 {
            display: none; /* Oculta o h2 pois será substituído pelo page-header */
        }

        /* Estilo para o campo de busca e seletor */
        .search-container {
            margin-bottom: 1.5rem;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap; /* Permite quebrar linha em telas pequenas */
        }

        .search-container input[type="text"],
        .search-container select {
            flex-grow: 1;
            padding: 10px 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
            color: var(--cor-texto-escuro);
            background-color: #fff;
            transition: border-color 0.2s ease;
            min-width: 150px;
        }

        .search-container input[type="text"]:focus,
        .search-container select:focus {
            outline: none;
            border-color: var(--cor-vibrante);
            box-shadow: 0 0 0 3px rgba(0, 180, 216, 0.2);
        }

        /* Estilos para a TABELA (Desktop View) */
        .client-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }

        .client-table th,
        .client-table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 0.95rem;
        }

        .client-table th {
            background-color: var(--cor-principal);
            color: var(--cor-texto-claro);
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .client-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .client-table tbody tr:hover {
            background-color: #e0f2f7;
            cursor: pointer;
        }

        /* Estilo para o link da linha da tabela */
        .client-table tbody tr a {
            display: block;
            text-decoration: none;
            color: inherit;
            padding: 12px 15px;
            margin: -12px -15px;
        }

        .client-table tbody tr a:hover {
            color: var(--cor-principal);
        }

        /* Estilos para os CARTÕES (Mobile View) */
        .client-cards-mobile {
            display: none; /* Escondido por padrão */
            flex-direction: column;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .client-card-item {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: var(--sombra-leve);
            padding: 1.5rem;
            text-align: left;
            border: 1px solid #eee;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }

        .client-card-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--sombra-media);
        }

        .client-card-item h3 {
            color: var(--cor-principal);
            margin-bottom: 0.8rem;
            font-size: 1.2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding-bottom: 0.5rem;
        }

        .client-card-item p {
            font-size: 0.95rem;
            color: var(--cor-texto-escuro);
            margin-bottom: 0.4rem;
        }

        .client-card-item p strong {
            color: var(--cor-secundaria);
        }

        /* Media Queries para Responsividade */
        @media (max-width: 768px) {
            .client-list-container {
                padding: 1rem;
                margin: 1rem auto;
            }
            .client-list-container h2 {
                font-size: 1.5rem;
            }
            .search-container {
                flex-direction: column;
                align-items: stretch;
            }
            .search-container input[type="text"],
            .search-container select {
                width: 100%;
                min-width: unset;
            }

            /* Esconde a tabela e mostra os cartões em mobile */
            .client-table {
                display: none;
            }
            .client-cards-mobile {
                display: flex; /* Mostra os cartões em mobile */
            }
        }

        @media (min-width: 769px) {
            /* Garante que a tabela seja exibida e os cartões escondidos em desktop */
            .client-table {
                display: table; /* Ou block, dependendo do contexto, mas table é o padrão */
            }
            .client-cards-mobile {
                display: none;
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
        <div class="client-list-container">
            <h2>Lista de Clientes Cadastrados</h2>

            <!-- Campo de Busca para Filtragem -->
            <div class="search-container">
                <select id="filterField">
                    <option value="all">Todos os Campos</option>
                    <option value="nome">Nome / Razão Social</option>
                    <option value="cpf_cnpj">CPF / CNPJ</option>
                    <option value="email">E-mail</option>
                    <option value="telefone">Telefone</option>
                    <option value="cidade">Cidade</option>
                </select>
                <input type="text" id="clientSearch" placeholder="Digite para filtrar...">
                <button class="btn btn-secondary" onclick="document.getElementById('clientSearch').value = ''; filterTable();">Limpar</button>
            </div>

            <!-- Tabela de Clientes (Desktop View) -->
            <table class="client-table" id="clientTable">
                <thead>
                    <tr>
                        <th>Nome / Razão Social</th>
                        <th>CPF / CNPJ</th>
                        <th>E-mail</th>
                        <th>Telefone</th>
                        <th>Cidade</th>
                    </tr>
                </thead>
                <tbody id="clientTableBody">
                    <!-- Clientes serão carregados aqui via JavaScript -->
                </tbody>
            </table>

            <!-- Cartões de Clientes (Mobile View) -->
            <div class="client-cards-mobile" id="clientCardsMobile">
                <!-- Clientes serão carregados aqui via JavaScript -->
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('clientSearch');
            const filterFieldSelect = document.getElementById('filterField');
            const clientTableBody = document.getElementById('clientTableBody');
            const clientCardsMobile = document.getElementById('clientCardsMobile');
            let allClients = []; // Para armazenar todos os clientes carregados

            // Função para buscar clientes da API
            async function fetchClients() {
                try {
                    console.log('Fazendo requisição para ../api/clientes.php');
                    const response = await fetch('../api/clientes.php');
                    console.log('Response status:', response.status);
                    
                    if (!response.ok) {
                        const errorText = await response.text();
                        throw new Error(`HTTP ${response.status}: ${errorText}`);
                    }
                    
                    const data = await response.json();
                    console.log('Dados recebidos:', data);
                    
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    allClients = data;
                    renderClients(allClients);
                } catch (error) {
                    console.error('Erro ao buscar clientes:', error);
                    const errorMessage = `Erro ao carregar clientes: ${error.message}`;
                    clientTableBody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: red;">${errorMessage}</td></tr>`;
                    clientCardsMobile.innerHTML = `<div style="text-align: center; padding: 20px; color: red;">${errorMessage}</div>`;
                    
                    // Mostrar toast de erro se disponível
                    if (window.toastSystem) {
                        window.toastSystem.error('Erro', errorMessage);
                    }
                }
            }

            // Função para renderizar clientes na tabela e nos cartões
            function renderClients(clientsToRender) {
                clientTableBody.innerHTML = ''; // Limpa a tabela
                clientCardsMobile.innerHTML = ''; // Limpa os cartões

                if (clientsToRender.length === 0) {
                    clientTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Nenhum cliente cadastrado ainda.</td></tr>';
                    clientCardsMobile.innerHTML = '<div style="text-align: center; padding: 20px;">Nenhum cliente cadastrado ainda.</div>';
                    return;
                }

                clientsToRender.forEach(client => {
                    // Renderizar na tabela
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td><a href="../registros/registrar_cliente.php?id=${client.id}">${client.nome}</a></td>
                        <td><a href="../registros/registrar_cliente.php?id=${client.id}">${client.cpf_cnpj}</a></td>
                        <td><a href="../registros/registrar_cliente.php?id=${client.id}">${client.email}</a></td>
                        <td><a href="../registros/registrar_cliente.php?id=${client.id}">${client.telefone}</a></td>
                        <td><a href="../registros/registrar_cliente.php?id=${client.id}">${client.cidade}</a></td>
                    `;
                    clientTableBody.appendChild(row);

                    // Renderizar nos cartões (mobile)
                    const card = document.createElement('div');
                    card.className = 'client-card-item';
                    card.onclick = () => window.location='../registros/registrar_cliente.php?id=${client.id}';
                    card.innerHTML = `
                        <h3>${client.nome}</h3>
                        <p><strong>CPF/CNPJ:</strong> ${client.cpf_cnpj}</p>
                        <p><strong>E-mail:</strong> ${client.email}</p>
                        <p><strong>Telefone:</strong> ${client.telefone}</p>
                        <p><strong>Cidade:</strong> ${client.cidade}</p>
                    `;
                    clientCardsMobile.appendChild(card);
                });
            }

            // Função para filtrar a lista de clientes
            window.filterClients = function() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedField = filterFieldSelect.value;

                const filteredClients = allClients.filter(client => {
                    if (searchTerm === '') {
                        return true;
                    }
                    if (selectedField === 'all') {
                        return Object.values(client).some(value =>
                            String(value).toLowerCase().includes(searchTerm)
                        );
                    } else {
                        return String(client[selectedField]).toLowerCase().includes(searchTerm);
                    }
                });
                renderClients(filteredClients);
            };

            // Adiciona event listeners para o campo de busca e o seletor de campo
            searchInput.addEventListener('keyup', filterClients);
            filterFieldSelect.addEventListener('change', filterClients);

            // Carrega os clientes ao iniciar a página
            fetchClients();
        });
    </script>
</body>
</html>
