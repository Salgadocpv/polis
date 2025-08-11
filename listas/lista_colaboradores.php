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
    <title>Lista de Colaboradores - Sistema de Engenharia</title>
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
        .collaborator-list-container {
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

        .collaborator-list-container h2 {
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
        .collaborator-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }

        .collaborator-table th,
        .collaborator-table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 0.95rem;
        }

        .collaborator-table th {
            background-color: var(--cor-principal);
            color: var(--cor-texto-claro);
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .collaborator-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .collaborator-table tbody tr:hover {
            background-color: #e0f2f7;
            cursor: pointer;
        }

        /* Estilo para o link da linha da tabela */
        .collaborator-table tbody tr a {
            display: block;
            text-decoration: none;
            color: inherit;
            padding: 12px 15px;
            margin: -12px -15px;
        }

        .collaborator-table tbody tr a:hover {
            color: var(--cor-principal);
        }

        /* Estilo para a foto do colaborador na tabela */
        .collaborator-photo-table {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            vertical-align: middle; /* Alinha a foto verticalmente no meio da célula */
            margin-right: 10px;
            border: 2px solid var(--cor-vibrante); /* Borda para destaque */
        }

        /* Estilos para os CARTÕES (Mobile View) */
        .collaborator-cards-mobile {
            display: none; /* Escondido por padrão */
            flex-direction: column;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .collaborator-card-item {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: var(--sombra-leve);
            padding: 1.5rem;
            text-align: left;
            border: 1px solid #eee;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }

        .collaborator-card-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--sombra-media);
        }

        .collaborator-card-item h3 {
            color: var(--cor-principal);
            margin-bottom: 0.8rem;
            font-size: 1.2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 10px; /* Espaçamento entre foto e nome no cartão */
        }

        .collaborator-card-item h3 .collaborator-photo-card {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--cor-vibrante);
        }

        .collaborator-card-item p {
            font-size: 0.95rem;
            color: var(--cor-texto-escuro);
            margin-bottom: 0.4rem;
        }

        .collaborator-card-item p strong {
            color: var(--cor-secundaria);
        }

        /* Media Queries para Responsividade */
        @media (max-width: 768px) {
            .collaborator-list-container {
                padding: 1rem;
                margin: 1rem auto;
            }
            .collaborator-list-container h2 {
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
            .collaborator-table {
                display: none;
            }
            .collaborator-cards-mobile {
                display: flex; /* Mostra os cartões em mobile */
            }
        }

        @media (min-width: 769px) {
            /* Garante que a tabela seja exibida e os cartões escondidos em desktop */
            .collaborator-table {
                display: table; /* Ou block, dependendo do contexto, mas table é o padrão */
            }
            .collaborator-cards-mobile {
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
        <div class="collaborator-list-container">
            <h2>Lista de Colaboradores</h2>

            <!-- Campo de Busca para Filtragem -->
            <div class="search-container">
                <select id="filterField">
                    <option value="all">Todos os Campos</option>
                    <option value="nome">Nome</option>
                    <option value="cpf">CPF</option>
                    <option value="cargo">Cargo</option>
                    <option value="departamento">Departamento</option>
                    <option value="email">E-mail</option>
                    <option value="telefone">Telefone</option>
                </select>
                <input type="text" id="collaboratorSearch" placeholder="Digite para filtrar...">
                <button class="btn btn-secondary" onclick="document.getElementById('collaboratorSearch').value = ''; filterCollaborators();">Limpar</button>
            </div>

            <!-- Tabela de Colaboradores (Desktop View) -->
            <table class="collaborator-table" id="collaboratorTable">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Cargo</th>
                        <th>Departamento</th>
                        <th>E-mail</th>
                        <th>Telefone</th>
                    </tr>
                </thead>
                <tbody id="collaboratorTableBody">
                    <!-- Colaboradores serão carregados aqui via JavaScript -->
                </tbody>
            </table>

            <!-- Cartões de Colaboradores (Mobile View) -->
            <div class="collaborator-cards-mobile" id="collaboratorCardsMobile">
                <!-- Colaboradores serão carregados aqui via JavaScript -->
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('collaboratorSearch');
            const filterFieldSelect = document.getElementById('filterField');
            const collaboratorTableBody = document.getElementById('collaboratorTableBody');
            const collaboratorCardsMobile = document.getElementById('collaboratorCardsMobile');
            let allCollaborators = [];

            async function fetchCollaborators() {
                try {
                    const response = await fetch('../api/colaboradores.php');
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    allCollaborators = await response.json();
                    renderCollaborators(allCollaborators);
                } catch (error) {
                    console.error('Erro ao buscar colaboradores:', error);
                    collaboratorTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Erro ao carregar colaboradores.</td></tr>';
                    collaboratorCardsMobile.innerHTML = '<div style="text-align: center; padding: 20px;">Erro ao carregar colaboradores.</div>';
                }
            }

            function renderCollaborators(collaboratorsToRender) {
                collaboratorTableBody.innerHTML = '';
                collaboratorCardsMobile.innerHTML = '';

                if (collaboratorsToRender.length === 0) {
                    collaboratorTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Nenhum colaborador cadastrado ainda.</td></tr>';
                    collaboratorCardsMobile.innerHTML = '<div style="text-align: center; padding: 20px;">Nenhum colaborador cadastrado ainda.</div>';
                    return;
                }

                collaboratorsToRender.forEach(collaborator => {
                    const photoUrl = collaborator.foto_url ? `../${collaborator.foto_url}` : 'https://placehold.co/40x40/00B4D8/FFFFFF?text=JS';

                    const row = document.createElement('tr');
                    const editUrl = `../registros/registrar_colaborador.php?id=${collaborator.id}`;
                    console.log('Collaborator ID:', collaborator.id);
                    console.log('Generated URL:', editUrl);

                    row.innerHTML = `
                        <td>
                            <a href="${editUrl}">
                                <img src="${photoUrl}" alt="Foto de ${collaborator.nome}" class="collaborator-photo-table">
                            </a>
                        </td>
                        <td><a href="${editUrl}">${collaborator.nome}</a></td>
                        <td><a href="${editUrl}">${collaborator.cpf}</a></td>
                        <td><a href="${editUrl}">${collaborator.cargo}</a></td>
                        <td><a href="${editUrl}">${collaborator.departamento}</a></td>
                        <td><a href="${editUrl}">${collaborator.email}</a></td>
                        <td><a href="${editUrl}">${collaborator.telefone}</a></td>
                    `;
                    collaboratorTableBody.appendChild(row);

                    const card = document.createElement('div');
                    card.className = 'collaborator-card-item';
                    card.onclick = () => window.location=editUrl;
                    card.innerHTML = `
                        <h3>
                            <img src="${photoUrl}" alt="Foto de ${collaborator.nome}" class="collaborator-photo-card">
                            ${collaborator.nome}
                        </h3>
                        <p><strong>CPF:</strong> ${collaborator.cpf}</p>
                        <p><strong>Cargo:</strong> ${collaborator.cargo}</p>
                        <p><strong>Departamento:</strong> ${collaborator.departamento}</p>
                        <p><strong>E-mail:</strong> ${collaborator.email}</p>
                        <p><strong>Telefone:</strong> ${collaborator.telefone}</p>
                    `;
                    collaboratorCardsMobile.appendChild(card);
                });
            }

            window.filterCollaborators = function() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedField = filterFieldSelect.value;

                const filteredCollaborators = allCollaborators.filter(collaborator => {
                    if (searchTerm === '') {
                        return true;
                    }
                    if (selectedField === 'all') {
                        return Object.values(collaborator).some(value =>
                            String(value).toLowerCase().includes(searchTerm)
                        );
                    } else {
                        return String(collaborator[selectedField]).toLowerCase().includes(searchTerm);
                    }
                });
                renderCollaborators(filteredCollaborators);
            };

            searchInput.addEventListener('keyup', filterCollaborators);
            filterFieldSelect.addEventListener('change', filterCollaborators);

            fetchCollaborators();
        });
    </script>
</body>
</html>