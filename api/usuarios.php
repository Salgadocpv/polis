<?php
/**
 * ===== API REST PARA GERENCIAMENTO DE USUÁRIOS =====
 * 
 * Esta API oferece operações para gerenciamento de usuários do sistema
 * Polis Engenharia, incluindo verificação de existência de nomes de usuário,
 * criação automática de usuários e controle de primeiras senhas.
 * 
 * Funcionalidades principais:
 * - Verificação de disponibilidade de nome de usuário
 * - Criação de usuários vinculados a colaboradores
 * - Controle de primeira senha (obrigar troca no primeiro login)
 * - Autenticação e validação de senhas
 * 
 * Padrões seguidos:
 * - REST API com métodos HTTP apropriados
 * - Validação completa de dados de entrada
 * - Tratamento de erros padronizado
 * - Logs de auditoria para segurança
 * - Sanitização de inputs contra SQL injection
 */

require_once 'conexao.php';
require_once '../includes/Security.php';

// ===== CONFIGURAÇÃO DE HEADERS =====
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// ===== TRATAMENTO DE MÉTODO HTTP =====
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGet($conn);
        break;
    case 'POST':
        handlePost($conn);
        break;
    case 'PUT':
        handlePut($conn);
        break;
    case 'DELETE':
        handleDelete($conn);
        break;
    default:
        http_response_code(405); // Método não permitido
        echo json_encode(['message' => 'Método não permitido']);
        break;
}

/**
 * ===== FUNÇÃO PARA REQUISIÇÕES GET =====
 * 
 * Trata consultas de usuários, incluindo:
 * - Listar todos os usuários (admin)
 * - Buscar usuário específico por ID
 * - Verificar se nome de usuário existe (check_username)
 */
function handleGet($conn) {
    // ===== VERIFICAR SE É CONSULTA DE DISPONIBILIDADE DE USERNAME =====
    if (isset($_GET['check_username'])) {
        $username = Security::sanitizeInput($_GET['check_username']);
        
        if (empty($username)) {
            http_response_code(400);
            echo json_encode(['error' => 'Nome de usuário não fornecido']);
            return;
        }
        
        try {
            // Buscar se username já existe
            $stmt = $conn->prepare("SELECT u.id, u.colaborador_id FROM usuarios u WHERE u.username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
            
            echo json_encode([
                'exists' => !empty($user),
                'colaborador_id' => $user['colaborador_id'] ?? null
            ]);
        } catch (Exception $e) {
            error_log("Erro ao verificar username: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Erro interno do servidor']);
        }
        return;
    }
    
    // ===== BUSCAR USUÁRIO POR ID =====
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        
        try {
            $stmt = $conn->prepare("
                SELECT u.id, u.username, u.email, u.nivel_acesso, u.primeira_senha, 
                       u.senha_temporaria, u.ultimo_login, u.data_cadastro,
                       c.nome as colaborador_nome
                FROM usuarios u 
                LEFT JOIN colaboradores c ON u.colaborador_id = c.id 
                WHERE u.id = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if ($user) {
                // Remover dados sensíveis
                unset($user['password_hash']);
                echo json_encode($user);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Usuário não encontrado']);
            }
            $stmt->close();
        } catch (Exception $e) {
            error_log("Erro ao buscar usuário: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Erro interno do servidor']);
        }
        return;
    }
    
    // ===== LISTAR TODOS OS USUÁRIOS (ADMIN APENAS) =====
    // TODO: Implementar verificação de permissão administrativa
    try {
        $stmt = $conn->prepare("
            SELECT u.id, u.username, u.email, u.nivel_acesso, u.primeira_senha,
                   u.ultimo_login, u.data_cadastro, c.nome as colaborador_nome
            FROM usuarios u 
            LEFT JOIN colaboradores c ON u.colaborador_id = c.id
            ORDER BY u.data_cadastro DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $row;
        }
        
        echo json_encode($usuarios);
        $stmt->close();
    } catch (Exception $e) {
        error_log("Erro ao listar usuários: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Erro interno do servidor']);
    }
}

/**
 * ===== FUNÇÃO PARA REQUISIÇÕES POST =====
 * 
 * Cria novo usuário no sistema
 * Usado internamente pela API de colaboradores
 */
function handlePost($conn) {
    // ===== LEITURA E SANITIZAÇÃO DOS DADOS =====
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Dados não fornecidos ou inválidos']);
        return;
    }
    
    // ===== VALIDAÇÃO DOS CAMPOS OBRIGATÓRIOS =====
    $required_fields = ['username', 'email', 'password', 'colaborador_id', 'nivel_acesso'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Campo obrigatório: $field"]);
            return;
        }
    }
    
    // ===== SANITIZAÇÃO DOS DADOS =====
    $username = Security::sanitizeInput($input['username']);
    $email = Security::sanitizeInput($input['email']);
    $password = $input['password']; // Não sanitizar senha (pode ter caracteres especiais)
    $colaborador_id = intval($input['colaborador_id']);
    $nivel_acesso = Security::sanitizeInput($input['nivel_acesso']);
    $primeira_senha = isset($input['primeira_senha']) ? (bool)$input['primeira_senha'] : true;
    
    // ===== VALIDAÇÃO ADICIONAL =====
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'E-mail inválido']);
        return;
    }
    
    // Validar formato do username
    if (!preg_match('/^[a-zA-Z0-9._-]+$/', $username) || strlen($username) < 3) {
        http_response_code(400);
        echo json_encode(['error' => 'Nome de usuário inválido']);
        return;
    }
    
    try {
        // ===== VERIFICAR DUPLICATAS =====
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt->close();
            http_response_code(409);
            echo json_encode(['error' => 'Nome de usuário ou e-mail já existe']);
            return;
        }
        $stmt->close();
        
        // ===== CRIPTOGRAFAR SENHA =====
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // ===== INSERIR NOVO USUÁRIO =====
        $stmt = $conn->prepare("
            INSERT INTO usuarios (colaborador_id, username, email, password_hash, nivel_acesso, primeira_senha) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issssi", $colaborador_id, $username, $email, $password_hash, $nivel_acesso, $primeira_senha);
        
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            error_log("Usuário criado com sucesso: ID $user_id, Username: $username");
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Usuário criado com sucesso',
                'user_id' => $user_id
            ]);
        } else {
            error_log("Erro ao criar usuário: " . $stmt->error);
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao criar usuário']);
        }
        $stmt->close();
        
    } catch (Exception $e) {
        error_log("Erro ao criar usuário: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Erro interno do servidor']);
    }
}

/**
 * ===== FUNÇÃO PARA REQUISIÇÕES PUT =====
 * 
 * Atualiza dados do usuário
 * Usado para alteração de primeira senha
 */
function handlePut($conn) {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID do usuário é obrigatório']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Dados não fornecidos']);
        return;
    }
    
    try {
        // ===== ATUALIZAÇÃO DE SENHA (PRIMEIRA SENHA) =====
        if (isset($input['new_password'])) {
            $new_password = $input['new_password'];
            
            if (strlen($new_password) < 6) {
                http_response_code(400);
                echo json_encode(['error' => 'Nova senha deve ter pelo menos 6 caracteres']);
                return;
            }
            
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("
                UPDATE usuarios 
                SET password_hash = ?, primeira_senha = FALSE, senha_temporaria = FALSE 
                WHERE id = ?
            ");
            $stmt->bind_param("si", $password_hash, $id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    error_log("Senha atualizada para usuário ID: $id");
                    echo json_encode(['success' => true, 'message' => 'Senha atualizada com sucesso']);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Usuário não encontrado']);
                }
            } else {
                throw new Exception($stmt->error);
            }
            $stmt->close();
            return;
        }
        
        // ===== OUTRAS ATUALIZAÇÕES =====
        // TODO: Implementar outras atualizações se necessário
        
    } catch (Exception $e) {
        error_log("Erro ao atualizar usuário: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Erro interno do servidor']);
    }
}

/**
 * ===== FUNÇÃO PARA REQUISIÇÕES DELETE =====
 * 
 * Remove usuário do sistema
 */
function handleDelete($conn) {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID do usuário é obrigatório']);
        return;
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                error_log("Usuário deletado: ID $id");
                echo json_encode(['success' => true, 'message' => 'Usuário excluído com sucesso']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Usuário não encontrado']);
            }
        } else {
            throw new Exception($stmt->error);
        }
        $stmt->close();
        
    } catch (Exception $e) {
        error_log("Erro ao deletar usuário: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Erro interno do servidor']);
    }
}

$conn->close();
?>