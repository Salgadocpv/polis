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
    <title>Lista de Projetos - Sistema de Engenharia</title>
    <!-- Importa a fonte 'Inter' do Google Fonts para um visual moderno -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <!-- Link para o Font Awesome para os ícones (versão mais recente) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSC鑑定=" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
        .project-list-container {
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

        .project-list-container h2 {
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
        .project-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }

        .project-table th,
        .project-table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 0.95rem;
        }

        .project-table th {
            background-color: var(--cor-principal);
            color: var(--cor-texto-claro);
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .project-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .project-table tbody tr:hover {
            background-color: #e0f2f7;
            cursor: pointer;
        }

        /* Estilo para o link da linha da tabela */
        .project-table tbody tr a {
            display: block;
            text-decoration: none;
            color: inherit;
            padding: 12px 15px;
            margin: -12px -15px;
        }

        .project-table tbody tr a:hover {
            color: var(--cor-principal);
        }

        /* Estilos para os CARTÕES (Mobile View) */
        .project-cards-mobile {
            display: none; /* Escondido por padrão */
            flex-direction: column;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .project-card-item {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: var(--sombra-leve);
            padding: 1.5rem;
            text-align: left;
            border: 1px solid #eee;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }

        .project-card-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--sombra-media);
        }

        .project-card-item h3 {
            color: var(--cor-principal);
            margin-bottom: 0.8rem;
            font-size: 1.2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding-bottom: 0.5rem;
        }

        .project-card-item p {
            font-size: 0.95rem;
            color: var(--cor-texto-escuro);
            margin-bottom: 0.4rem;
        }

        .project-card-item p strong {
            color: var(--cor-secundaria);
        }

        /* Estilos para o status do projeto */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 5px;
            font-size: 0.8rem;
            font-weight: bold;
            color: var(--cor-texto-claro);
            text-transform: uppercase;
        }

        .status-badge.ativo {
            background-color: var(--cor-sucesso);
        }
        .status-badge.concluido {
            background-color: #6c757d; /* Cinza */
        }
        .status-badge.pendente {
            background-color: orange;
        }
        .status-badge.cancelado {
            background-color: var(--cor-erro);
        }
        .status-badge.em-andamento {
            background-color: var(--cor-vibrante);
        }

        /* Media Queries para Responsividade */
        @media (max-width: 768px) {
            .project-list-container {
                padding: 1rem;
                margin: 1rem auto;
            }
            .project-list-container h2 {
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
            .project-table {
                display: none;
            }
            .project-cards-mobile {
                display: flex; /* Mostra os cartões em mobile */
            }
        }

        @media (min-width: 769px) {
            /* Garante que a tabela seja exibida e os cartões escondidos em desktop */
            .project-table {
                display: table; /* Ou block, dependendo do contexto, mas table é o padrão */
            }
            .project-cards-mobile {
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

        // Buscar projetos do banco de dados
        $projects = [];
        try {
            // Incluir conexão
            require_once '../api/conexao.php';
            
            $result = $conn->query("SELECT p.*, c.nome as cliente_nome FROM projetos p LEFT JOIN clientes c ON p.cliente_id = c.id");
            while ($row = $result->fetch_assoc()) {
                $projects[] = [
                    'id' => $row['id'],
                    'nome' => $row['nome'],
                    'cliente' => $row['cliente_nome'] ?? 'Cliente não encontrado',
                    'responsavel' => $row['responsavel'],
                    'status' => $row['status'],
                    'data_inicio' => $row['data_inicio'],
                    'data_conclusao_prevista' => $row['data_conclusao_prevista']
                ];
            }
        } catch (Exception $e) {
            // Em caso de erro, manter lista vazia ou dados de exemplo
            error_log("Erro ao buscar projetos: " . $e->getMessage());
        }
    ?>

    <!-- Conteúdo Principal da Página -->
    <main class="main-content" id="mainContent">
        <div class="project-list-container">
            <h2>Lista de Projetos</h2>

            <!-- Campo de Busca para Filtragem -->
            <div class="search-container">
                <select id="filterField">
                    <option value="all">Todos os Campos</option>
                    <option value="nome">Nome do Projeto</option>
                    <option value="cliente">Cliente</option>
                    <option value="responsavel">Responsável</option>
                    <option value="status">Status</option>
                    <option value="data_inicio">Data de Início</option>
                    <option value="data_conclusao_prevista">Data Prevista</option>
                </select>
                <input type="text" id="projectSearch" placeholder="Digite para filtrar...">
                <button class="btn btn-secondary" onclick="document.getElementById('projectSearch').value = ''; filterTable();">Limpar</button>
            </div>

            <!-- Tabela de Projetos (Desktop View) -->
            <table class="project-table" id="projectTable">
                <thead>
                    <tr>
                        <th>Nome do Projeto</th>
                        <th>Cliente</th>
                        <th>Responsável</th>
                        <th>Status</th>
                        <th>Data de Início</th>
                        <th>Data Prevista</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($projects)): ?>
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <!-- Cada célula contém um link para a página de cadastro/edição do projeto -->
                                <td><a href="../registros/registrar_projeto.php?id=<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['nome']); ?></a></td>
                                <td><a href="../registros/registrar_projeto.php?id=<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['cliente']); ?></a></td>
                                <td><a href="../registros/registrar_projeto.php?id=<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['responsavel']); ?></a></td>
                                <td><a href="../registros/registrar_projeto.php?id=<?php echo $project['id']; ?>"><span class="status-badge <?php echo strtolower(str_replace(' ', '-', $project['status'])); ?>"><?php echo htmlspecialchars($project['status']); ?></span></a></td>
                                <td><a href="../registros/registrar_projeto.php?id=<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['data_inicio']); ?></a></td>
                                <td><a href="../registros/registrar_projeto.php?id=<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['data_conclusao_prevista']); ?></a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Nenhum projeto cadastrado ainda.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Cartões de Projetos (Mobile View) -->
            <div class="project-cards-mobile" id="projectCardsMobile">
                <?php if (!empty($projects)): ?>
                    <?php foreach ($projects as $project): ?>
                        <div class="project-card-item" onclick="window.location='../registros/registrar_projeto.php?id=<?php echo $project['id']; ?>';">
                            <h3><?php echo htmlspecialchars($project['nome']); ?></h3>
                            <p><strong>Cliente:</strong> <?php echo htmlspecialchars($project['cliente']); ?></p>
                            <p><strong>Responsável:</strong> <?php echo htmlspecialchars($project['responsavel']); ?></p>
                            <p><strong>Status:</strong> <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $project['status'])); ?>"><?php echo htmlspecialchars($project['status']); ?></span></p>
                            <p><strong>Início:</strong> <?php echo htmlspecialchars($project['data_inicio']); ?></p>
                            <p><strong>Previsão:</strong> <?php echo htmlspecialchars($project['data_conclusao_prevista']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 20px;">Nenhum projeto cadastrado ainda.</div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('projectSearch');
            const filterFieldSelect = document.getElementById('filterField');
            const projectTable = document.getElementById('projectTable');
            const tableRows = projectTable.querySelectorAll('tbody tr');
            const projectCardsMobile = document.getElementById('projectCardsMobile');
            const cardItems = projectCardsMobile.querySelectorAll('.project-card-item');
            
            // Mapeamento de cabeçalhos para índices de coluna (0-indexed)
            const columnMap = {
                'nome': 0,
                'cliente': 1,
                'responsavel': 2,
                'status': 3,
                'data_inicio': 4,
                'data_conclusao_prevista': 5
            };

            // Função para filtrar a tabela e os cartões
            window.filterTable = function() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedField = filterFieldSelect.value;

                // Função auxiliar para verificar se o texto inclui o termo de busca
                const checkInclusion = (text, term) => text.toLowerCase().includes(term);

                // Filtrar a tabela (Desktop)
                tableRows.forEach(row => {
                    let shouldDisplay = false;
                    if (searchTerm === '') {
                        shouldDisplay = true;
                    } else if (selectedField === 'all') {
                        let rowText = '';
                        row.querySelectorAll('td').forEach(cell => {
                            rowText += cell.textContent + ' ';
                        });
                        if (checkInclusion(rowText, searchTerm)) {
                            shouldDisplay = true;
                        }
                    } else {
                        const columnIndex = columnMap[selectedField];
                        if (columnIndex !== undefined) {
                            const cell = row.querySelectorAll('td')[columnIndex];
                            if (cell && checkInclusion(cell.textContent, searchTerm)) {
                                shouldDisplay = true;
                            }
                        }
                    }
                    row.style.display = shouldDisplay ? '' : 'none';
                });

                // Filtrar os cartões (Mobile)
                cardItems.forEach(card => {
                    let shouldDisplay = false;
                    if (searchTerm === '') {
                        shouldDisplay = true;
                    } else if (selectedField === 'all') {
                        let cardText = card.textContent;
                        if (checkInclusion(cardText, searchTerm)) {
                            shouldDisplay = true;
                        }
                    } else {
                        // Para cartões, precisamos mapear o campo para o conteúdo textual
                        let textToSearch = '';
                        switch(selectedField) {
                            case 'nome':
                                textToSearch = card.querySelector('h3').textContent;
                                break;
                            case 'cliente':
                                textToSearch = card.querySelector('p:nth-of-type(1)').textContent;
                                break;
                            case 'responsavel':
                                textToSearch = card.querySelector('p:nth-of-type(2)').textContent;
                                break;
                            case 'status':
                                textToSearch = card.querySelector('p:nth-of-type(3)').textContent;
                                break;
                            case 'data_inicio':
                                textToSearch = card.querySelector('p:nth-of-type(4)').textContent;
                                break;
                            case 'data_conclusao_prevista':
                                textToSearch = card.querySelector('p:nth-of-type(5)').textContent;
                                break;
                        }
                        if (checkInclusion(textToSearch, searchTerm)) {
                            shouldDisplay = true;
                        }
                    }
                    card.style.display = shouldDisplay ? '' : 'none';
                });
            }

            // Adiciona event listeners para o campo de busca e o seletor de campo
            searchInput.addEventListener('keyup', filterTable);
            filterFieldSelect.addEventListener('change', filterTable);

            // Chama o filtro uma vez ao carregar a página para garantir o estado inicial
            filterTable();
        });
    </script>
</body>
</html>
