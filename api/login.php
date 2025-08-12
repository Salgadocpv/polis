<?php
/**
 * API DE AUTENTICAÇÃO DO SISTEMA
 * 
 * Este endpoint gerencia o processo completo de autenticação de usuários
 * com múltiplas camadas de segurança e logging detalhado
 * 
 * Fluxo de Segurança:
 * 1. Verificação de token CSRF
 * 2. Rate limiting (controle de tentativas)
 * 3. Sanitização de inputs
 * 4. Validação de credenciais
 * 5. Criação de sessão segura
 * 6. Redirecionamento baseado no nível de acesso
 * 
 * Resposta JSON: { success: boolean, message: string, redirect?: string }
 */

// Inicia sessão PHP para gerenciamento de estado do usuário
session_start();

// Carrega dependências essenciais
require_once 'conexao.php';      // Conexão com banco de dados
require_once '../includes/Security.php'; // Classe de segurança

// ===== CONFIGURAÇÃO DE HEADERS DE SEGURANÇA =====
// Aplica headers HTTP de segurança (CSP, XSS Protection, etc.)
Security::setSecurityHeaders();
// Define resposta como JSON para APIs modernas
header('Content-Type: application/json');

// ===== VERIFICAÇÃO DE MÉTODO HTTP =====
// Captura o método HTTP da requisição (GET, POST, PUT, DELETE, etc.)
$method = $_SERVER['REQUEST_METHOD'];

// ===== PROCESSAMENTO DE LOGIN (MÉTODO POST) =====
if ($method === 'POST') {
    // Captura dados JSON do corpo da requisição
    // file_get_contents('php://input') lê o raw body da requisição HTTP
    $data = json_decode(file_get_contents('php://input'), true);

    // ===== VERIFICAÇÃO DE TOKEN CSRF =====
    // Extrai token CSRF enviado pelo cliente (proteção contra ataques CSRF)
    $csrfToken = $data['csrf_token'] ?? '';
    
    // Valida se o token CSRF é válido e corresponde ao gerado pelo servidor
    if (!Security::verifyCSRFToken($csrfToken)) {
        // Registra tentativa de login com token CSRF inválido
        Security::logActivity('LOGIN_ATTEMPT_CSRF_FAIL', 'Token CSRF inválido');
        
        // Retorna erro 403 (Forbidden) para token inválido
        echo json_encode(['success' => false, 'message' => 'Token de segurança inválido.']);
        http_response_code(403);
        exit(); // Interrompe execução
    }

    // ===== VERIFICAÇÃO DE RATE LIMITING =====
    // Verifica se o IP atual não ultrapassou o limite de tentativas de login
    $loginCheck = Security::checkLoginAttempts();
    
    // Se o IP estiver bloqueado por muitas tentativas
    if ($loginCheck['blocked']) {
        // Registra tentativa bloqueada por rate limiting
        Security::logActivity('LOGIN_ATTEMPT_BLOCKED', 'Rate limit exceeded');
        
        // Retorna erro 429 (Too Many Requests) com mensagem do bloqueio
        echo json_encode(['success' => false, 'message' => $loginCheck['message']]);
        http_response_code(429);
        exit(); // Interrompe execução
    }

    // ===== SANITIZAÇÃO E VALIDAÇÃO DE INPUTS =====
    // Sanitiza o campo username/email usando filtros de segurança
    $username_or_email = Security::sanitizeInput($data['username'] ?? '', 'email');
    // Senha é mantida como recebida (será verificada com hash)
    $password = $data['password'] ?? '';

    // Verifica se ambos os campos foram preenchidos
    if (empty($username_or_email) || empty($password)) {
        // Incrementa contador de tentativas inválidas
        Security::incrementLoginAttempts();
        
        // Registra tentativa com campos vazios
        Security::logActivity('LOGIN_ATTEMPT_EMPTY_FIELDS', "Username/Email: $username_or_email");
        
        // Retorna erro 400 (Bad Request) para campos obrigatórios
        echo json_encode(['success' => false, 'message' => 'Usuário/E-mail e senha são obrigatórios.']);
        http_response_code(400);
        exit(); // Interrompe execução
    }

    // ===== BUSCA DO USUÁRIO NO BANCO =====
    // Prepared statement para buscar usuário por username OU email
    // Inclui campos para controle de primeira senha
    $stmt = $conn->prepare("SELECT id, username, email, password_hash, nivel_acesso, primeira_senha, colaborador_id FROM usuarios WHERE username = ? OR email = ?");
    
    // Vincula os mesmos parâmetros para ambas as condições WHERE
    $stmt->bind_param("ss", $username_or_email, $username_or_email);
    
    // Executa a query preparada
    $stmt->execute();
    
    // Obtém resultado da consulta
    $result = $stmt->get_result();
    
    // Converte resultado em array associativo (ou null se não encontrou)
    $user = $result->fetch_assoc();
    
    // Fecha statement para liberar recursos
    $stmt->close();

    // ===== VERIFICAÇÃO DE CREDENCIAIS =====
    // Verifica se usuário existe E se a senha está correta
    // password_verify() compara senha plain text com hash armazenado no banco
    if ($user && password_verify($password, $user['password_hash'])) {
        
        // ===== LOGIN BEM-SUCEDIDO =====
        
        // Reseta contador de tentativas falhas para este IP
        Security::resetLoginAttempts();
        
        // Registra login bem-sucedido com detalhes do usuário
        Security::logActivity('LOGIN_SUCCESS', "User: {$user['username']} | Email: {$user['email']}", $user['id']);
        
        // ===== VERIFICAÇÃO DE PRIMEIRA SENHA =====
        // Verifica se é o primeiro login e precisa trocar a senha
        if ($user['primeira_senha'] == 1) {
            // Registra primeiro login detectado
            Security::logActivity('FIRST_LOGIN_DETECTED', "User: {$user['username']} precisa trocar senha");
            
            // Cria sessão temporária apenas para troca de senha
            $_SESSION['temp_user_id'] = $user['id'];
            $_SESSION['temp_username'] = $user['username'];
            $_SESSION['first_login'] = true;
            
            // Não cria sessão completa até que a senha seja alterada
            // Redireciona para página de troca de primeira senha
            echo json_encode([
                'success' => true, 
                'message' => 'Primeira senha detectada. É necessário alterar sua senha.',
                'first_login' => true,
                'redirect' => 'alterar_primeira_senha.php'
            ]);
            http_response_code(200);
            return; // Para aqui, não continua com login normal
        }

        // ===== CRIAÇÃO DE SESSÃO SEGURA =====
        // Armazena dados essenciais na sessão PHP
        $_SESSION['user_id'] = $user['id'];              // ID único do usuário
        $_SESSION['username'] = $user['username'];        // Nome de usuário
        $_SESSION['nivel_acesso'] = $user['nivel_acesso']; // Nível de permissão
        $_SESSION['colaborador_id'] = $user['colaborador_id']; // ID do colaborador vinculado

        // ===== ATUALIZAR ÚLTIMO LOGIN =====
        // Registra timestamp do último login no banco
        $update_stmt = $conn->prepare("UPDATE usuarios SET ultimo_login = CURRENT_TIMESTAMP WHERE id = ?");
        $update_stmt->bind_param("i", $user['id']);
        $update_stmt->execute();
        $update_stmt->close();

        // ===== LÓGICA DE REDIRECIONAMENTO =====
        // Define página de destino baseada no nível de acesso do usuário
        $redirect_url = 'dashboard.php'; // Padrão para Admin e Visualizador
        
        // Normaliza o nível de acesso (remove espaços e converte para minúsculo)
        $nivel_acesso_tratado = strtolower(trim($user['nivel_acesso']));

        // Usuários comuns vão para dashboard específico
        if ($nivel_acesso_tratado === 'usuario') {
            $redirect_url = 'dashboard_usuario.php';
        }

        // Retorna sucesso com URL de redirecionamento
        echo json_encode(['success' => true, 'message' => 'Login bem-sucedido!', 'redirect' => $redirect_url]);
        http_response_code(200); // Sucesso
        
    } else {
        
        // ===== CREDENCIAIS INVÁLIDAS =====
        
        // Incrementa contador de tentativas falhas
        Security::incrementLoginAttempts();
        
        // Registra tentativa de login falhada
        Security::logActivity('LOGIN_FAIL', "Username/Email: $username_or_email");
        
        // Retorna erro genérico (não especifica se é usuário ou senha)
        // Isso evita enumeration attacks (descobrir usuários válidos)
        echo json_encode(['success' => false, 'message' => 'Credenciais inválidas.']);
        http_response_code(401); // Unauthorized
    }
    
} else {
    // ===== MÉTODO HTTP NÃO SUPORTADO =====
    // API só aceita POST para login
    echo json_encode(['message' => 'Método não permitido.']);
    http_response_code(405); // Method Not Allowed
}

// ===== LIMPEZA DE RECURSOS =====
// Fecha conexão com banco de dados
$conn->close();
?>