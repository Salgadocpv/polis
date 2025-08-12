<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Engenharia</title>
    <!-- Importa a fonte 'Inter' do Google Fonts para um visual moderno -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <!-- Link para o Font Awesome para os ícones (versão mais recente) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Link para o arquivo de estilos CSS global -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Scripts do sistema -->
    <script src="assets/js/toast-system.js"></script>
    <script src="assets/js/breadcrumb-system.js"></script>
    <script src="assets/js/lazy-loading.js"></script>
    <script src="assets/js/global-search.js"></script>
    <script src="assets/js/excel-export.js"></script>
    <script src="assets/js/page-header.js"></script>
    <script src="assets/js/dashboard-analytics.js"></script>
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

        /* Estilo para cards de métricas rápidas */
        .metric-card {
            background-color: var(--cor-fundo-card);
            color: var(--cor-texto-escuro);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: var(--sombra-leve);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            min-height: 150px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--sombra-media);
        }

        .metric-card .icon {
            font-size: 2.5rem;
            color: var(--cor-vibrante);
            margin-bottom: 0.5rem;
        }

        .metric-card .value {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--cor-principal);
        }

        .metric-card .label {
            font-size: 1rem;
            color: var(--cor-secundaria);
            margin-top: 0.5rem;
        }

        /* Responsividade para metric cards em mobile */
        @media (max-width: 768px) {
            .metric-card {
                min-height: 120px;
                padding: 1rem;
                margin-bottom: 1rem;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }
            
            .metric-card .icon {
                font-size: 2rem;
                margin-bottom: 0.3rem;
            }
            
            .metric-card .value {
                font-size: 2rem;
            }
            
            .metric-card .label {
                font-size: 0.9rem;
                margin-top: 0.3rem;
            }
        }

        /* Estilo para listas dentro de cards */
        .card ul {
            list-style: none;
            padding: 0;
            margin: 1rem 0 0;
            text-align: left;
        }

        .card ul li {
            padding: 8px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            font-size: 0.95rem;
            color: var(--cor-texto-escuro);
        }

        .card ul li:last-child {
            border-bottom: none;
        }

        .card ul li i {
            margin-right: 10px;
            color: var(--cor-vibrante);
        }

        .card ul li .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
            display: inline-block;
        }

        .status-dot.success { background-color: var(--cor-sucesso); }
        .status-dot.warning { background-color: orange; }
        .status-dot.error { background-color: var(--cor-erro); }
        .status-dot.info { background-color: var(--cor-clara); }

        /* Responsividade para o grid de cards */
        @media (min-width: 769px) {
            .container.dashboard-grid {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                grid-auto-rows: minmax(180px, auto); /* Altura mínima para os cards */
            }
            .container.dashboard-grid .card.full-width {
                grid-column: 1 / -1; /* Ocupa a largura total */
            }
            .container.dashboard-grid .card.two-thirds {
                grid-column: span 2; /* Ocupa 2/3 da largura em um grid de 3 colunas */
            }
        }

        @media (max-width: 768px) {
            .container.dashboard-grid {
                grid-template-columns: 1fr; /* Uma única coluna em telas pequenas */
                gap: 1rem; /* Espaçamento menor entre cards */
                margin: 0; /* Remove margem extra */
                padding: 0; /* Remove padding que pode causar overflow */
                width: 100%; /* Garante que não ultrapasse a tela */
                box-sizing: border-box; /* Inclui padding no cálculo da largura */
            }
            
            /* Todos os cards ocupam largura total em mobile */
            .container.dashboard-grid .card {
                width: 100%;
                max-width: 100%; /* Impede que ultrapasse o container */
                margin: 0;
                padding: 1rem; /* Padding menor em mobile */
                box-sizing: border-box; /* Inclui padding no cálculo da largura */
                overflow-x: hidden; /* Impede o estouro horizontal */
                word-wrap: break-word; /* Quebra palavras longas para evitar estouro */
                overflow-wrap: break-word; /* Padrão mais recente para quebra de palavras */
            }
            
            .container.dashboard-grid .card.full-width,
            .container.dashboard-grid .card.two-thirds {
                grid-column: 1; /* Reset da largura especial */
                width: 100%;
                max-width: 100%;
            }
        }

        /* Estilos para as novas listas de tarefas */
        .task-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .task-list-item:last-child {
            border-bottom: none;
        }

        .task-list-item .task-text {
            flex-grow: 1;
            text-align: left;
        }

        .task-list-item .task-status {
            font-size: 0.85rem;
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 5px;
            color: white;
        }

        .task-list-item .task-status.completed { background-color: var(--cor-sucesso); }
        .task-list-item .task-status.pending { background-color: var(--cor-vibrante); }
        .task-list-item .task-status.overdue { background-color: var(--cor-erro); }

        .task-list-item.completed .task-text {
            text-decoration: line-through;
            color: #888;
        }

        /* Responsividade para task lists e botões */
        @media (max-width: 768px) {
            .task-list-item {
                padding: 0.75rem 0;
                flex-wrap: wrap;
                gap: 0.5rem;
            }
            
            .task-list-item .task-text {
                font-size: 0.9rem;
                line-height: 1.4;
                min-width: 0; /* Permite quebra de linha */
                flex: 1 1 100%; /* Ocupa linha inteira se necessário */
            }
            
            .task-list-item .task-status {
                font-size: 0.75rem;
                padding: 0.3rem 0.6rem;
                border-radius: 4px;
                align-self: flex-start;
            }
            
            /* Botões menores em mobile */
            .btn {
                padding: 0.6rem 1rem;
                font-size: 0.85rem;
                border-radius: 8px;
            }
            
            .btn.btn-primary,
            .btn.btn-secondary {
                width: 100%; /* Botões ocupam largura total em mobile */
                margin-top: 0.5rem;
                text-align: center;
            }
            
            /* Listas dentro dos cards */
            .card ul li {
                font-size: 0.85rem;
                padding: 0.5rem 0;
                line-height: 1.4;
            }
            
            .card ul li i {
                margin-right: 0.5rem;
                font-size: 0.8rem;
            }
        }

        /* Botão de controle da sanfona */
        .accordion-controls {
            position: fixed;
            top: 90px; /* Aumentando para criar espaço igual */
            right: 15px;
            z-index: 999;
            background: rgba(255, 255, 255, 0.95);
            padding: 8px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
        }

        .accordion-control-btn {
            background: var(--cor-fundo-card);
            border: 2px solid var(--cor-principal);
            color: var(--cor-principal);
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.65rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 3px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            min-width: 70px;
            justify-content: center;
        }

        .accordion-control-btn:hover {
            background: var(--cor-principal);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(1, 42, 74, 0.3);
        }

        .accordion-control-btn i {
            font-size: 0.6rem;
            transition: transform 0.3s ease;
        }

        .accordion-control-btn .btn-text {
            transition: all 0.3s ease;
        }

        .accordion-control-btn.collapsed i {
            transform: rotate(180deg);
        }

        .accordion-control-btn.collapsed {
            background: var(--cor-principal);
            color: white;
        }

        .accordion-control-btn.collapsed:hover {
            background: var(--cor-secundaria);
            color: white;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 0 0.5rem 2rem 0.5rem; /* Padding lateral menor em mobile */
                font-size: 1.2rem; /* Fonte menor em mobile */
            }
            
            .accordion-controls {
                right: 0.5rem;
                top: 75px; /* Ajustado para manter proporção */
                padding: 0.25rem;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }
            
            .accordion-control-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.7rem;
                min-width: 70px;
                gap: 0.3rem;
            }
            
            /* Melhorar espaçamento dos acordeões */
            .accordion {
                margin: 1rem 0.5rem;
                max-width: none; /* Remove max-width em mobile */
            }
            
            .accordion-item {
                margin-bottom: 0.75rem; /* Espaçamento menor entre itens */
            }
        }

        /* Estilos para Acordeão/Sanfona */
        .accordion {
            margin: 1.5rem auto 1rem;
            max-width: 1200px;
        }

        .accordion-item {
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .accordion-header {
            background-color: var(--cor-fundo-card);
            color: var(--cor-texto-escuro);
            padding: 0.8rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
            user-select: none;
            border-radius: 12px;
            box-shadow: var(--sombra-leve);
        }

        .accordion-header:hover {
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-1px);
        }

        .accordion-header:hover .accordion-toggle-btn {
            background: rgba(1, 42, 74, 0.1);
            border-color: rgba(1, 42, 74, 0.2);
        }

        .accordion-toggle-btn {
            background: rgba(1, 42, 74, 0.05);
            border: 2px solid rgba(1, 42, 74, 0.1);
            border-radius: 8px;
            padding: 0.5rem 0.8rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .accordion-toggle-btn .icon {
            font-size: 1rem;
            color: var(--cor-principal);
            transition: transform 0.3s ease;
        }

        .accordion-toggle-btn.active .icon {
            transform: rotate(180deg);
        }

        .accordion-header h2 {
            margin: 0;
            font-size: 1rem;
            font-weight: 500;
            text-align: left;
            flex-grow: 1;
            padding-right: 1rem; /* Espaço para o botão */
        }

        /* Responsividade para headers e conteúdo dos acordeões */
        @media (max-width: 768px) {
            .accordion-header {
                padding: 0.6rem 0.8rem;
                border-radius: 10px;
            }
            
            .accordion-header h2 {
                font-size: 0.9rem;
                padding-right: 0.5rem;
            }
            
            .accordion-header h2 i {
                margin-right: 0.5rem;
                font-size: 0.85rem;
            }
            
            .accordion-toggle-btn {
                padding: 0.4rem 0.6rem;
                border-radius: 6px;
            }
            
            .accordion-toggle-btn .icon {
                font-size: 0.85rem;
            }
            
            .accordion-content {
                border-radius: 0 0 10px 10px;
                margin-top: 0.3rem;
            }
            
            .accordion-body {
                padding: 1rem 0.8rem;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
                overflow-x: hidden; /* Previne overflow horizontal */
            }
            
            .accordion-body h2 {
                font-size: 1.1rem;
                margin-bottom: 1rem;
                word-wrap: break-word; /* Quebra palavras longas */
            }
            
            .accordion-body h3 {
                font-size: 1rem;
                margin-bottom: 0.8rem;
                word-wrap: break-word;
            }
            
            /* Garantir que o container dentro do accordion-body não ultrapasse */
            .accordion-body .container.dashboard-grid {
                width: 100%;
                max-width: 100%;
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
        }


        .accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background-color: var(--cor-fundo-card);
            border-radius: 0 0 12px 12px;
            margin-top: 0.5rem;
            box-shadow: var(--sombra-leve);
        }

        .accordion-content.active {
            max-height: 2000px; /* Valor alto o suficiente para acomodar o conteúdo */
        }

        .accordion-body {
            padding: 1.5rem;
        }

        /* Ajustes para o conteúdo dentro do acordeão */
        .accordion-body .container.dashboard-grid {
            margin: 0;
            padding: 0;
        }

        .accordion-body h2 {
            color: var(--cor-principal);
            margin: 0 0 1.5rem 0;
        }

        /* Media query para telas muito pequenas (smartphones pequenos) */
        @media (max-width: 480px) {
            .main-content {
                padding: 0 0.3rem 1.5rem 0.3rem;
                font-size: 1.1rem;
            }
            
            .accordion {
                margin: 0.8rem 0.3rem;
            }
            
            .accordion-controls {
                right: 0.3rem;
                top: 70px;
                padding: 0.2rem;
            }
            
            .accordion-control-btn {
                padding: 0.3rem 0.6rem;
                font-size: 0.65rem;
                min-width: 60px;
            }
            
            .metric-card {
                min-height: 100px;
                padding: 0.8rem;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }
            
            .metric-card .icon {
                font-size: 1.8rem;
            }
            
            .metric-card .value {
                font-size: 1.8rem;
            }
            
            .metric-card .label {
                font-size: 0.8rem;
            }
            
            .accordion-header {
                padding: 0.5rem 0.6rem;
            }
            
            .accordion-header h2 {
                font-size: 0.85rem;
            }
            
            .accordion-body {
                padding: 0.8rem;
            }
            
            .container.dashboard-grid {
                padding: 0;
            }
            
            .card {
                padding: 0.8rem !important;
            }
            
            .task-list-item .task-text {
                font-size: 0.85rem;
            }
            
            .btn {
                padding: 0.5rem 0.8rem;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <?php
        include 'includes/header.php';
        include 'includes/sidebar.php';

        // Dados fictícios para as tarefas
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $tomorrow = date('Y-m-d', strtotime('+1 day'));

        $all_tasks = [
            ['text' => 'Revisar Projeto X', 'status' => 'pending', 'due_date' => $today],
            ['text' => 'Enviar Relatório Semanal', 'status' => 'completed', 'due_date' => $today],
            ['text' => 'Preparar Apresentação Cliente A', 'status' => 'pending', 'due_date' => $today],
            ['text' => 'Responder E-mails Urgentes', 'status' => 'completed', 'due_date' => $today],
            ['text' => 'Finalizar Orçamento Y', 'status' => 'pending', 'due_date' => $yesterday],
            ['text' => 'Agendar Reunião Equipe', 'status' => 'pending', 'due_date' => $yesterday],
            ['text' => 'Atualizar Documentação Z', 'status' => 'completed', 'due_date' => $yesterday],
            ['text' => 'Planejar Próxima Sprint', 'status' => 'pending', 'due_date' => $tomorrow],
        ];

        $today_tasks = [];
        $overdue_tasks = [];

        foreach ($all_tasks as $task) {
            if ($task['due_date'] === $today) {
                $today_tasks[] = $task;
            } elseif ($task['due_date'] < $today && $task['status'] === 'pending') {
                $overdue_tasks[] = $task;
            }
        }
    ?>

    <!-- Conteúdo Principal da Página -->
    <main class="main-content" id="mainContent">
        <!-- Botão de Controle da Sanfona -->
        <div class="accordion-controls">
            <button class="accordion-control-btn" id="toggleAllBtn">
                <i class="fas fa-expand-alt"></i>
                <span class="btn-text">Abrir</span>
            </button>
        </div>

        <!-- Dashboard em Formato Acordeão/Sanfona -->
        <div class="accordion">
            <!-- Grupo 1: Métricas Rápidas -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleAccordion(this)">
                    <h2><i class="fas fa-chart-bar"></i> Métricas Rápidas</h2>
                    <div class="accordion-toggle-btn">
                        <i class="fas fa-chevron-down icon"></i>
                    </div>
                </div>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <div class="container dashboard-grid">
                            <div class="metric-card">
                                <i class="fas fa-clipboard-list icon"></i>
                                <div class="value">15</div>
                                <div class="label">Projetos Ativos</div>
                            </div>
                            <div class="metric-card">
                                <i class="fas fa-users icon"></i>
                                <div class="value">45</div>
                                <div class="label">Membros da Equipe</div>
                            </div>
                            <div class="metric-card">
                                <i class="fas fa-check-circle icon"></i>
                                <div class="value">92%</div>
                                <div class="label">Taxa de Conclusão</div>
                            </div>
                            <div class="metric-card">
                                <i class="fas fa-clock icon"></i>
                                <div class="value">3</div>
                                <div class="label">Tarefas Atrasadas</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grupo 2: Tarefas -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleAccordion(this)">
                    <h2><i class="fas fa-tasks"></i> Tarefas</h2>
                    <div class="accordion-toggle-btn">
                        <i class="fas fa-chevron-down icon"></i>
                    </div>
                </div>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <div class="container dashboard-grid">
                            <div class="card full-width">
                                <h3>Tarefas para Hoje (<?php echo date('d/m/Y'); ?>)</h3>
                                <ul>
                                    <?php if (empty($today_tasks)): ?>
                                        <li>Nenhuma tarefa ativa para hoje.</li>
                                    <?php else: ?>
                                        <?php foreach ($today_tasks as $task): ?>
                                            <li class="task-list-item <?php echo $task['status']; ?>">
                                                <div class="task-text"><?php echo htmlspecialchars($task['text']); ?></div>
                                                <div class="task-status <?php echo $task['status']; ?>">
                                                    <?php echo ($task['status'] === 'completed') ? 'Concluída' : 'Pendente'; ?>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </ul>

                                <h3 style="margin-top: 2rem;">Tarefas Atrasadas</h3>
                                <ul>
                                    <?php if (empty($overdue_tasks)): ?>
                                        <li>Nenhuma tarefa atrasada.</li>
                                    <?php else: ?>
                                        <?php foreach ($overdue_tasks as $task): ?>
                                            <li class="task-list-item overdue">
                                                <div class="task-text"><?php echo htmlspecialchars($task['text']); ?> (Vencida em: <?php echo date('d/m/Y', strtotime($task['due_date'])); ?>)</div>
                                                <div class="task-status overdue">Atrasada</div>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grupo 3: Acompanhamento de Elétrica -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleAccordion(this)">
                    <h2><i class="fas fa-bolt"></i> Acompanhamento de Elétrica</h2>
                    <div class="accordion-toggle-btn">
                        <i class="fas fa-chevron-down icon"></i>
                    </div>
                </div>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <div class="container dashboard-grid">
                            <div class="card two-thirds">
                                <h3>Projetos Elétricos Ativos</h3>
                                <ul>
                                    <li><span class="status-dot success"></span><i class="fas fa-bolt"></i> Instalação Predial - Torre A (85% Concluído)</li>
                                    <li><span class="status-dot warning"></span><i class="fas fa-lightbulb"></i> Manutenção de Iluminação Pública (50% Concluído)</li>
                                    <li><span class="status-dot error"></span><i class="fas fa-charging-station"></i> Projeto de Subestação Industrial (Atrasado)</li>
                                    <li><span class="status-dot info"></span><i class="fas fa-solar-panel"></i> Instalação Solar Residencial (Planejamento)</li>
                                </ul>
                                <div style="margin-top: 1rem; text-align: right;">
                                    <button class="btn btn-primary">Ver Todos os Projetos Elétricos</button>
                                </div>
                            </div>

                            <div class="card">
                                <h3>Manutenções Elétricas Pendentes</h3>
                                <ul>
                                    <li><i class="fas fa-tools"></i> Quadro de Distribuição - Bloco C</li>
                                    <li><i class="fas fa-wrench"></i> Fiação Externa - Setor 3</li>
                                    <li><i class="fas fa-exclamation-triangle"></i> Disjuntor Principal - Fábrica</li>
                                </ul>
                                <div style="margin-top: 1rem; text-align: right;">
                                    <button class="btn btn-secondary">Agendar Manutenção</button>
                                </div>
                            </div>

                            <div class="card full-width">
                                <h3>Status de Equipamentos Críticos</h3>
                                <p>Monitore a condição e o funcionamento de equipamentos elétricos essenciais.</p>
                                <ul>
                                    <li><span class="status-dot success"></span> Transformador T1 - Operacional</li>
                                    <li><span class="status-dot warning"></span> Gerador G2 - Manutenção Preventiva Necessária</li>
                                    <li><span class="status-dot error"></span> Painel de Controle P3 - Falha Detectada</li>
                                </ul>
                                <div style="margin-top: 1rem; text-align: right;">
                                    <button class="btn btn-primary">Detalhes dos Equipamentos</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grupo 4: Tarefas e Notificações Gerais -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleAccordion(this)">
                    <h2><i class="fas fa-bell"></i> Tarefas e Notificações</h2>
                    <div class="accordion-toggle-btn">
                        <i class="fas fa-chevron-down icon"></i>
                    </div>
                </div>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <div class="container dashboard-grid">
                            <div class="card">
                                <h3>Minhas Tarefas Urgentes</h3>
                                <ul>
                                    <li><i class="fas fa-bell"></i> Revisar orçamento Projeto Beta</li>
                                    <li><i class="fas fa-user-plus"></i> Integrar novo membro à Equipe Alpha</li>
                                    <li><i class="fas fa-calendar-check"></i> Aprovar cronograma de férias</li>
                                </ul>
                                <div style="margin-top: 1rem; text-align: right;">
                                    <button class="btn btn-primary">Ver Todas</button>
                                </div>
                            </div>

                            <div class="card">
                                <h3>Notificações Recentes</h3>
                                <ul>
                                    <li><span class="status-dot info"></span> Novo comentário no Projeto Gamma.</li>
                                    <li><span class="status-dot success"></span> Tarefa "Instalação Rede" concluída.</li>
                                    <li><span class="status-dot error"></span> Prazo de "Relatório Mensal" expirado.</li>
                                </ul>
                                <div style="margin-top: 1rem; text-align: right;">
                                    <button class="btn btn-secondary">Limpar Notificações</button>
                                </div>
                            </div>

                            <div class="card">
                                <h3>Próximos Eventos</h3>
                                <ul>
                                    <li><i class="fas fa-calendar-day"></i> Reunião de Equipe (Hoje, 14:00)</li>
                                    <li><i class="fas fa-calendar-day"></i> Auditoria de Segurança (08/08)</li>
                                    <li><i class="fas fa-calendar-day"></i> Treinamento de Software (15/08)</li>
                                </ul>
                                <div style="margin-top: 1rem; text-align: right;">
                                    <button class="btn btn-primary">Ver Calendário</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function toggleAccordion(header) {
            const accordionItem = header.closest('.accordion-item');
            const content = accordionItem.querySelector('.accordion-content');
            const toggleBtn = accordionItem.querySelector('.accordion-toggle-btn');
            
            // Se for desktop, permite múltiplas seções abertas
            const isDesktop = window.innerWidth > 768;
            
            // Toggle classes
            toggleBtn.classList.toggle('active');
            content.classList.toggle('active');
            
            // Close other accordions (só em mobile)
            if (!isDesktop) {
                const allItems = document.querySelectorAll('.accordion-item');
                allItems.forEach(item => {
                    if (item !== accordionItem) {
                        const otherToggleBtn = item.querySelector('.accordion-toggle-btn');
                        const otherContent = item.querySelector('.accordion-content');
                        
                        otherToggleBtn.classList.remove('active');
                        otherContent.classList.remove('active');
                    }
                });
            }
            
            // Smooth scroll para o item ativo (só em mobile)
            if (!isDesktop && content.classList.contains('active')) {
                setTimeout(() => {
                    accordionItem.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'nearest' 
                    });
                }, 300);
            }
        }

        // Função para inicializar o estado da sanfona baseado no tamanho da tela
        function initializeAccordion() {
            const isDesktop = window.innerWidth > 768;
            const allItems = document.querySelectorAll('.accordion-item');
            const toggleBtn = document.getElementById('toggleAllBtn');
            const icon = toggleBtn?.querySelector('i');
            const text = toggleBtn?.querySelector('.btn-text');
            
            if (isDesktop) {
                // Desktop: abrir todas as seções
                allItems.forEach(item => {
                    const itemToggleBtn = item.querySelector('.accordion-toggle-btn');
                    const content = item.querySelector('.accordion-content');
                    
                    itemToggleBtn.classList.add('active');
                    content.classList.add('active');
                });
                
                // Atualizar o botão de controle
                if (toggleBtn) {
                    icon.className = 'fas fa-compress-alt';
                    text.textContent = 'Fechar';
                    toggleBtn.classList.add('collapsed');
                    isExpanded = true;
                }
            } else {
                // Mobile: fechar todas as seções (comportamento atual)
                allItems.forEach(item => {
                    const itemToggleBtn = item.querySelector('.accordion-toggle-btn');
                    const content = item.querySelector('.accordion-content');
                    
                    itemToggleBtn.classList.remove('active');
                    content.classList.remove('active');
                });
                
                // Atualizar o botão de controle
                if (toggleBtn) {
                    icon.className = 'fas fa-expand-alt';
                    text.textContent = 'Abrir';
                    toggleBtn.classList.remove('collapsed');
                    isExpanded = false;
                }
            }
        }

        // Inicializar quando a página carregar
        document.addEventListener('DOMContentLoaded', function() {
            initializeAccordion();
        });

        // Funções para expandir/recolher todas as seções
        function expandAllSections() {
            const allItems = document.querySelectorAll('.accordion-item');
            allItems.forEach(item => {
                const toggleBtn = item.querySelector('.accordion-toggle-btn');
                const content = item.querySelector('.accordion-content');
                
                toggleBtn.classList.add('active');
                content.classList.add('active');
            });
        }

        function collapseAllSections() {
            const allItems = document.querySelectorAll('.accordion-item');
            allItems.forEach(item => {
                const toggleBtn = item.querySelector('.accordion-toggle-btn');
                const content = item.querySelector('.accordion-content');
                
                toggleBtn.classList.remove('active');
                content.classList.remove('active');
            });
        }

        // Variável para controlar o estado do toggle
        let isExpanded = false;

        // Função para alternar entre expandir e recolher todas as seções
        function toggleAllSections() {
            const toggleBtn = document.getElementById('toggleAllBtn');
            const icon = toggleBtn.querySelector('i');
            const text = toggleBtn.querySelector('.btn-text');

            if (isExpanded) {
                // Se está expandido, recolher
                collapseAllSections();
                icon.className = 'fas fa-expand-alt';
                text.textContent = 'Abrir';
                toggleBtn.classList.remove('collapsed');
                isExpanded = false;
            } else {
                // Se está recolhido, expandir
                expandAllSections();
                icon.className = 'fas fa-compress-alt';
                text.textContent = 'Fechar';
                toggleBtn.classList.add('collapsed');
                isExpanded = true;
            }
        }

        // Event listener para o botão de controle
        document.getElementById('toggleAllBtn').addEventListener('click', toggleAllSections);

        // Reinicializar apenas quando mudar entre desktop e mobile
        let currentScreenType = window.innerWidth > 768 ? 'desktop' : 'mobile';
        
        window.addEventListener('resize', function() {
            const newScreenType = window.innerWidth > 768 ? 'desktop' : 'mobile';
            
            // Só reinicializa se mudou o tipo de tela (desktop <-> mobile)
            if (currentScreenType !== newScreenType) {
                currentScreenType = newScreenType;
                initializeAccordion();
            }
        });
    </script>
</body>
</html>