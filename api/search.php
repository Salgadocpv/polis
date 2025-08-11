<?php
session_start();
require_once 'conexao.php';
require_once '../includes/Security.php';
require_once '../includes/DatabaseHelper.php';

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
    $searchTerm = Security::sanitizeInput($_GET['q'] ?? '');
    $page = intval($_GET['page'] ?? 1);
    $perPage = intval($_GET['per_page'] ?? 20);
    
    if (strlen($searchTerm) < 2) {
        echo json_encode([
            'data' => [],
            'pagination' => [
                'current_page' => 1,
                'per_page' => $perPage,
                'total' => 0,
                'total_pages' => 0,
                'has_next' => false,
                'has_prev' => false
            ],
            'message' => 'Digite pelo menos 2 caracteres para buscar'
        ]);
        exit();
    }
    
    $db = new DatabaseHelper($conn);
    $result = $db->globalSearch($searchTerm, $page, $perPage);
    
    Security::logActivity('GLOBAL_SEARCH', "Term: $searchTerm, Results: " . count($result['data']));
    
    echo json_encode($result);
} else {
    echo json_encode(['message' => 'Método não permitido']);
    http_response_code(405);
}

$conn->close();
?>