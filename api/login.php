<?php
session_start();
require_once 'conexao.php';
require_once '../includes/Security.php';

// Headers de segurança
Security::setSecurityHeaders();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Verifica CSRF token
    $csrfToken = $data['csrf_token'] ?? '';
    if (!Security::verifyCSRFToken($csrfToken)) {
        Security::logActivity('LOGIN_ATTEMPT_CSRF_FAIL', 'Token CSRF inválido');
        echo json_encode(['success' => false, 'message' => 'Token de segurança inválido.']);
        http_response_code(403);
        exit();
    }

    // Verifica rate limiting
    $loginCheck = Security::checkLoginAttempts();
    if ($loginCheck['blocked']) {
        Security::logActivity('LOGIN_ATTEMPT_BLOCKED', 'Rate limit exceeded');
        echo json_encode(['success' => false, 'message' => $loginCheck['message']]);
        http_response_code(429);
        exit();
    }

    // Sanitiza inputs
    $username_or_email = Security::sanitizeInput($data['username'] ?? '', 'email');
    $password = $data['password'] ?? '';

    if (empty($username_or_email) || empty($password)) {
        Security::incrementLoginAttempts();
        Security::logActivity('LOGIN_ATTEMPT_EMPTY_FIELDS', "Username/Email: $username_or_email");
        echo json_encode(['success' => false, 'message' => 'Usuário/E-mail e senha são obrigatórios.']);
        http_response_code(400);
        exit();
    }

    // Tenta encontrar o usuário pelo username ou email
    $stmt = $conn->prepare("SELECT id, username, email, password_hash, nivel_acesso FROM usuarios WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username_or_email, $username_or_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Login bem-sucedido
        Security::resetLoginAttempts();
        Security::logActivity('LOGIN_SUCCESS', "User: {$user['username']} | Email: {$user['email']}", $user['id']);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nivel_acesso'] = $user['nivel_acesso'];

        // Define o redirecionamento com base no nível de acesso
        $redirect_url = 'dashboard.php'; // Padrão para Admin e Visualizador
        
        // Limpa e padroniza o nível de acesso para uma verificação robusta
        $nivel_acesso_tratado = strtolower(trim($user['nivel_acesso']));

        if ($nivel_acesso_tratado === 'usuário') {
            $redirect_url = 'dashboard_usuario.php';
        }

        echo json_encode(['success' => true, 'message' => 'Login bem-sucedido!', 'redirect' => $redirect_url]);
        http_response_code(200);
    } else {
        // Credenciais inválidas
        Security::incrementLoginAttempts();
        Security::logActivity('LOGIN_FAIL', "Username/Email: $username_or_email");
        echo json_encode(['success' => false, 'message' => 'Credenciais inválidas.']);
        http_response_code(401);
    }
} else {
    echo json_encode(['message' => 'Método não permitido.']);
    http_response_code(405);
}

$conn->close();
?>