<?php
session_start();
require_once 'conexao.php';
require_once '../includes/Security.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Não autorizado']);
    exit();
}

Security::setSecurityHeaders();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $type = Security::sanitizeInput($_GET['type'] ?? '');
    
    switch ($type) {
        case 'dashboard':
            getDashboardAnalytics($conn);
            break;
        case 'projects':
            getProjectAnalytics($conn);
            break;
        case 'clients':
            getClientAnalytics($conn);
            break;
        case 'timeline':
            getTimelineAnalytics($conn);
            break;
        case 'performance':
            getPerformanceMetrics($conn);
            break;
        default:
            echo json_encode(['message' => 'Tipo de analytics inválido']);
            http_response_code(400);
    }
} else {
    echo json_encode(['message' => 'Método não permitido']);
    http_response_code(405);
}

function getDashboardAnalytics($conn) {
    try {
        // Métricas principais com comparação mensal
        $currentMonth = date('Y-m');
        $lastMonth = date('Y-m', strtotime('-1 month'));
        
        $query = "
            SELECT 
                -- Clientes
                COUNT(DISTINCT c.id) as total_clientes,
                COUNT(DISTINCT CASE WHEN DATE_FORMAT(c.data_cadastro, '%Y-%m') = ? THEN c.id END) as novos_clientes_mes,
                COUNT(DISTINCT CASE WHEN DATE_FORMAT(c.data_cadastro, '%Y-%m') = ? THEN c.id END) as novos_clientes_mes_anterior,
                
                -- Projetos
                COUNT(DISTINCT p.id) as total_projetos,
                COUNT(DISTINCT CASE WHEN p.status = 'Em Andamento' THEN p.id END) as projetos_ativos,
                COUNT(DISTINCT CASE WHEN p.status = 'Concluído' THEN p.id END) as projetos_concluidos,
                COUNT(DISTINCT CASE WHEN p.status = 'Atrasado' THEN p.id END) as projetos_atrasados,
                COUNT(DISTINCT CASE WHEN DATE_FORMAT(p.data_cadastro, '%Y-%m') = ? THEN p.id END) as novos_projetos_mes,
                
                -- Colaboradores
                COUNT(DISTINCT col.id) as total_colaboradores,
                
                -- Valores financeiros
                COALESCE(SUM(p.orcamento), 0) as valor_total_projetos,
                COALESCE(SUM(CASE WHEN p.status = 'Em Andamento' THEN p.orcamento ELSE 0 END), 0) as valor_projetos_ativos,
                COALESCE(SUM(CASE WHEN p.status = 'Concluído' THEN p.orcamento ELSE 0 END), 0) as valor_projetos_concluidos
                
            FROM clientes c
            LEFT JOIN projetos p ON c.id = p.cliente_id
            LEFT JOIN colaboradores col ON 1=1
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $currentMonth, $lastMonth, $currentMonth);
        $stmt->execute();
        $result = $stmt->get_result();
        $metrics = $result->fetch_assoc();
        
        // Calcular percentuais de mudança
        $clientesGrowth = $metrics['novos_clientes_mes_anterior'] > 0 
            ? (($metrics['novos_clientes_mes'] - $metrics['novos_clientes_mes_anterior']) / $metrics['novos_clientes_mes_anterior']) * 100 
            : 0;
        
        // Taxa de conclusão de projetos
        $taxaConclusao = $metrics['total_projetos'] > 0 
            ? ($metrics['projetos_concluidos'] / $metrics['total_projetos']) * 100 
            : 0;
        
        // Gráfico de projetos por status
        $statusQuery = "
            SELECT 
                COALESCE(status, 'Indefinido') as status,
                COUNT(*) as count,
                COALESCE(SUM(orcamento), 0) as valor_total
            FROM projetos 
            GROUP BY status
            ORDER BY count DESC
        ";
        $statusResult = $conn->query($statusQuery);
        $projetosPorStatus = [];
        while ($row = $statusResult->fetch_assoc()) {
            $projetosPorStatus[] = $row;
        }
        
        // Projetos por mês (últimos 6 meses)
        $timelineQuery = "
            SELECT 
                DATE_FORMAT(data_cadastro, '%Y-%m') as mes,
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'Concluído' THEN 1 END) as concluidos
            FROM projetos 
            WHERE data_cadastro >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(data_cadastro, '%Y-%m')
            ORDER BY mes DESC
            LIMIT 6
        ";
        $timelineResult = $conn->query($timelineQuery);
        $timeline = [];
        while ($row = $timelineResult->fetch_assoc()) {
            $timeline[] = [
                'mes' => $row['mes'],
                'total' => intval($row['total']),
                'concluidos' => intval($row['concluidos']),
                'mes_nome' => date('M/Y', strtotime($row['mes'] . '-01'))
            ];
        }
        
        // Clientes mais valiosos
        $clientesQuery = "
            SELECT 
                c.nome,
                c.id,
                COUNT(p.id) as total_projetos,
                COALESCE(SUM(p.orcamento), 0) as valor_total,
                DATE_FORMAT(MAX(p.data_cadastro), '%d/%m/%Y') as ultimo_projeto
            FROM clientes c
            LEFT JOIN projetos p ON c.id = p.cliente_id
            GROUP BY c.id, c.nome
            HAVING total_projetos > 0
            ORDER BY valor_total DESC, total_projetos DESC
            LIMIT 5
        ";
        $clientesResult = $conn->query($clientesQuery);
        $topClientes = [];
        while ($row = $clientesResult->fetch_assoc()) {
            $topClientes[] = [
                'nome' => $row['nome'],
                'id' => intval($row['id']),
                'projetos' => intval($row['total_projetos']),
                'valor' => floatval($row['valor_total']),
                'ultimo_projeto' => $row['ultimo_projeto']
            ];
        }
        
        $response = [
            'metrics' => [
                'clientes' => [
                    'total' => intval($metrics['total_clientes']),
                    'novos_mes' => intval($metrics['novos_clientes_mes']),
                    'crescimento' => round($clientesGrowth, 1)
                ],
                'projetos' => [
                    'total' => intval($metrics['total_projetos']),
                    'ativos' => intval($metrics['projetos_ativos']),
                    'concluidos' => intval($metrics['projetos_concluidos']),
                    'atrasados' => intval($metrics['projetos_atrasados']),
                    'taxa_conclusao' => round($taxaConclusao, 1),
                    'novos_mes' => intval($metrics['novos_projetos_mes'])
                ],
                'colaboradores' => [
                    'total' => intval($metrics['total_colaboradores'])
                ],
                'financeiro' => [
                    'valor_total' => floatval($metrics['valor_total_projetos']),
                    'valor_ativo' => floatval($metrics['valor_projetos_ativos']),
                    'valor_concluido' => floatval($metrics['valor_projetos_concluidos'])
                ]
            ],
            'charts' => [
                'projetos_status' => $projetosPorStatus,
                'timeline' => array_reverse($timeline),
                'top_clientes' => $topClientes
            ],
            'generated_at' => date('Y-m-d H:i:s')
        ];
        
        Security::logActivity('VIEW_DASHBOARD_ANALYTICS', 'Dashboard analytics accessed');
        echo json_encode($response);
        
    } catch (Exception $e) {
        error_log("Analytics error: " . $e->getMessage());
        echo json_encode(['error' => 'Erro ao gerar analytics']);
        http_response_code(500);
    }
}

function getProjectAnalytics($conn) {
    try {
        // Análise detalhada de projetos
        $query = "
            SELECT 
                p.*,
                c.nome as cliente_nome,
                DATEDIFF(COALESCE(p.data_conclusao_prevista, NOW()), p.data_inicio) as duracao_dias,
                CASE 
                    WHEN p.data_conclusao_prevista < NOW() AND p.status != 'Concluído' THEN 'Atrasado'
                    WHEN p.data_conclusao_prevista >= NOW() AND p.status != 'Concluído' THEN 'No Prazo'
                    ELSE p.status
                END as status_real
            FROM projetos p
            LEFT JOIN clientes c ON p.cliente_id = c.id
            ORDER BY p.data_cadastro DESC
        ";
        
        $result = $conn->query($query);
        $projetos = [];
        
        while ($row = $result->fetch_assoc()) {
            $projetos[] = [
                'id' => intval($row['id']),
                'nome' => $row['nome'],
                'cliente' => $row['cliente_nome'],
                'status' => $row['status'],
                'status_real' => $row['status_real'],
                'orcamento' => floatval($row['orcamento']),
                'responsavel' => $row['responsavel'],
                'data_inicio' => $row['data_inicio'],
                'data_prevista' => $row['data_conclusao_prevista'],
                'duracao_dias' => intval($row['duracao_dias'])
            ];
        }
        
        echo json_encode([
            'projetos' => $projetos,
            'total' => count($projetos)
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Erro ao buscar projetos']);
        http_response_code(500);
    }
}

function getTimelineAnalytics($conn) {
    try {
        $months = intval($_GET['months'] ?? 12);
        
        $query = "
            SELECT 
                DATE_FORMAT(data_cadastro, '%Y-%m') as periodo,
                'projetos' as tipo,
                COUNT(*) as quantidade,
                COALESCE(SUM(orcamento), 0) as valor
            FROM projetos 
            WHERE data_cadastro >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(data_cadastro, '%Y-%m')
            
            UNION ALL
            
            SELECT 
                DATE_FORMAT(data_cadastro, '%Y-%m') as periodo,
                'clientes' as tipo,
                COUNT(*) as quantidade,
                0 as valor
            FROM clientes 
            WHERE data_cadastro >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(data_cadastro, '%Y-%m')
            
            ORDER BY periodo DESC, tipo
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $months, $months);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $timeline = [];
        while ($row = $result->fetch_assoc()) {
            $timeline[] = [
                'periodo' => $row['periodo'],
                'periodo_nome' => date('M/Y', strtotime($row['periodo'] . '-01')),
                'tipo' => $row['tipo'],
                'quantidade' => intval($row['quantidade']),
                'valor' => floatval($row['valor'])
            ];
        }
        
        echo json_encode(['timeline' => $timeline]);
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Erro ao gerar timeline']);
        http_response_code(500);
    }
}

function getPerformanceMetrics($conn) {
    try {
        // Métricas de performance do sistema
        $metrics = [
            'database' => [
                'total_records' => 0,
                'tables_info' => []
            ],
            'activity' => [
                'last_login' => null,
                'total_users' => 0
            ]
        ];
        
        // Info das tabelas
        $tables = ['clientes', 'projetos', 'colaboradores', 'usuarios'];
        foreach ($tables as $table) {
            $result = $conn->query("SELECT COUNT(*) as count FROM $table");
            $count = $result->fetch_assoc()['count'];
            $metrics['database']['tables_info'][$table] = intval($count);
            $metrics['database']['total_records'] += intval($count);
        }
        
        // Info dos usuários
        $userResult = $conn->query("SELECT COUNT(*) as total FROM usuarios");
        $metrics['activity']['total_users'] = intval($userResult->fetch_assoc()['total']);
        
        echo json_encode($metrics);
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Erro ao obter métricas de performance']);
        http_response_code(500);
    }
}

$conn->close();
?>