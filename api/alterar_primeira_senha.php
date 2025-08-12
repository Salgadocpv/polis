<?php
/**
 * ===== API PARA ALTERAÇÃO DE PRIMEIRA SENHA =====
 * 
 * Esta API processa a alteração da primeira senha de usuários recém-cadastrados.
 * Verificações de segurança:
 * - Sessão temporária válida
 * - Senha atual (CPF) correta
 * - Nova senha atende aos requisitos
 * - Confirmação de senha
 * 
 * Após alteração bem-sucedida:
 * - Remove flag primeira_senha do usuário
 * - Cria sessão completa
 * - Redireciona para dashboard apropriado
 */

// ===== CONFIGURAÇÃO INICIAL =====
session_start();
require_once 'conexao.php';
require_once '../includes/Security.php';

// Headers de segurança e resposta JSON
Security::setSecurityHeaders();
header('Content-Type: application/json');

// Verifica método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

// ===== VERIFICAÇÃO DE SESSÃO TEMPORÁRIA =====
if (!isset($_SESSION['temp_user_id']) || !isset($_SESSION['first_login']) || $_SESSION['first_login'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Sessão inválida. Faça login novamente.']);
    exit();
}

$temp_user_id = $_SESSION['temp_user_id'];

// ===== RECEBIMENTO E VALIDAÇÃO DOS DADOS =====
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados não fornecidos']);
    exit();
}

$current_password = $input['current_password'] ?? '';
$new_password = $input['new_password'] ?? '';
$confirm_password = $input['confirm_password'] ?? '';

// Verificar campos obrigatórios
if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
    exit();
}

// Verificar se senhas coincidem
if ($new_password !== $confirm_password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nova senha e confirmação não coincidem']);
    exit();
}

// ===== VALIDAÇÃO DA NOVA SENHA =====
$password_errors = [];

if (strlen($new_password) < 6) {
    $password_errors[] = 'Senha deve ter pelo menos 6 caracteres';
}

if (!preg_match('/[A-Z]/', $new_password)) {
    $password_errors[] = 'Senha deve conter pelo menos uma letra maiúscula';
}

if (!preg_match('/[a-z]/', $new_password)) {
    $password_errors[] = 'Senha deve conter pelo menos uma letra minúscula';
}

if (!preg_match('/[0-9]/', $new_password)) {
    $password_errors[] = 'Senha deve conter pelo menos um número';
}

if (!empty($password_errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Requisitos de senha não atendidos: ' . implode(', ', $password_errors)
    ]);
    exit();
}

// Verificar se nova senha é diferente da atual
if ($current_password === $new_password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'A nova senha deve ser diferente da senha atual']);
    exit();
}

try {
    // ===== BUSCAR DADOS DO USUÁRIO =====
    $stmt = $conn->prepare("SELECT id, password_hash, nivel_acesso, colaborador_id FROM usuarios WHERE id = ? AND primeira_senha = TRUE");
    $stmt->bind_param("i", $temp_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado ou senha já foi alterada']);
        exit();
    }

    // ===== VERIFICAR SENHA ATUAL =====
    if (!password_verify($current_password, $user['password_hash'])) {
        // Registrar tentativa inválida
        Security::logActivity('FIRST_PASSWORD_CHANGE_FAIL', "Senha atual incorreta para user ID: $temp_user_id");
        
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Senha atual incorreta']);
        exit();
    }

    // ===== ATUALIZAR SENHA NO BANCO =====
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("
        UPDATE usuarios 
        SET password_hash = ?, primeira_senha = FALSE, ultimo_login = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    $stmt->bind_param("si", $new_password_hash, $temp_user_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao atualizar senha no banco de dados');
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('Nenhuma linha foi afetada na atualização');
    }
    
    $stmt->close();

    // ===== BUSCAR DADOS COMPLETOS DO USUÁRIO =====
    $stmt = $conn->prepare("
        SELECT u.id, u.username, u.email, u.nivel_acesso, u.colaborador_id, c.nome as colaborador_nome
        FROM usuarios u 
        LEFT JOIN colaboradores c ON u.colaborador_id = c.id 
        WHERE u.id = ?
    ");
    $stmt->bind_param("i", $temp_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $updated_user = $result->fetch_assoc();
    $stmt->close();

    if (!$updated_user) {
        throw new Exception('Erro ao buscar dados atualizados do usuário');
    }

    // ===== LIMPAR SESSÃO TEMPORÁRIA =====
    unset($_SESSION['temp_user_id']);
    unset($_SESSION['temp_username']);
    unset($_SESSION['first_login']);

    // ===== CRIAR SESSÃO COMPLETA =====
    $_SESSION['user_id'] = $updated_user['id'];
    $_SESSION['username'] = $updated_user['username'];
    $_SESSION['nivel_acesso'] = $updated_user['nivel_acesso'];
    $_SESSION['colaborador_id'] = $updated_user['colaborador_id'];

    // ===== REGISTRAR ATIVIDADE DE SUCESSO =====
    Security::logActivity(
        'FIRST_PASSWORD_CHANGED', 
        "Primeira senha alterada com sucesso. User: {$updated_user['username']}", 
        $updated_user['id']
    );

    // ===== DETERMINAR REDIRECIONAMENTO =====
    $redirect_url = 'dashboard.php'; // Padrão para Admin e Visualizador
    
    $nivel_acesso_tratado = strtolower(trim($updated_user['nivel_acesso']));
    if ($nivel_acesso_tratado === 'usuario') {
        $redirect_url = 'dashboard_usuario.php';
    }

    // ===== RESPOSTA DE SUCESSO =====
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Senha alterada com sucesso! Bem-vindo ao sistema.',
        'redirect' => $redirect_url,
        'user' => [
            'username' => $updated_user['username'],
            'nivel_acesso' => $updated_user['nivel_acesso'],
            'colaborador_nome' => $updated_user['colaborador_nome']
        ]
    ]);

} catch (Exception $e) {
    // ===== TRATAMENTO DE ERROS =====
    error_log("Erro ao alterar primeira senha: " . $e->getMessage());
    
    Security::logActivity(
        'FIRST_PASSWORD_CHANGE_ERROR', 
        "Erro ao alterar primeira senha: " . $e->getMessage(), 
        $temp_user_id
    );

    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erro interno do servidor. Tente novamente.'
    ]);
} finally {
    $conn->close();
}
?>