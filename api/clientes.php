<?php
require_once 'conexao.php';
require_once '../includes/Security.php';
require_once '../includes/DatabaseHelper.php';

Security::setSecurityHeaders();
header('Content-Type: application/json');

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
        echo json_encode(['message' => 'Método não permitido']);
        http_response_code(405);
        break;
}

function handleGet($conn) {
    $id = $_GET['id'] ?? null;
    $page = intval($_GET['page'] ?? 1);
    $perPage = intval($_GET['per_page'] ?? 10);
    $search = Security::sanitizeInput($_GET['search'] ?? '');

    if ($id) {
        // Busca cliente específico com contagem de projetos (JOIN)
        $stmt = $conn->prepare("
            SELECT c.*, 
                   COUNT(p.id) as total_projetos,
                   COALESCE(SUM(p.orcamento), 0) as total_orcamentos
            FROM clientes c
            LEFT JOIN projetos p ON c.id = p.cliente_id
            WHERE c.id = ?
            GROUP BY c.id
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cliente = $result->fetch_assoc();
        
        if ($cliente) {
            Security::logActivity('VIEW_CLIENT', "Client ID: $id");
            echo json_encode($cliente);
        } else {
            echo json_encode(['message' => 'Cliente não encontrado']);
            http_response_code(404);
        }
        $stmt->close();
    } else {
        try {
            // Query simplificada primeiro para testar
            if ($search) {
                $searchTerm = "%$search%";
                $stmt = $conn->prepare("
                    SELECT * FROM clientes 
                    WHERE nome LIKE ? OR email LIKE ? OR cpf_cnpj LIKE ? OR cidade LIKE ? OR telefone LIKE ?
                    ORDER BY nome
                ");
                $stmt->bind_param("sssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
            } else {
                $stmt = $conn->prepare("SELECT * FROM clientes ORDER BY nome");
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $clientes = [];
            
            while ($row = $result->fetch_assoc()) {
                $clientes[] = $row;
            }
            
            Security::logActivity('LIST_CLIENTS', "Total found: " . count($clientes));
            echo json_encode($clientes);
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500);
        }
    }
}

function handlePost($conn) {
    $data = json_decode(file_get_contents('php://input'), true);

    $nome = $data['nome'] ?? null;
    $cpf_cnpj = $data['cpf_cnpj'] ?? null;
    $email = $data['email'] ?? null;
    $telefone = $data['telefone'] ?? null;
    $rua = $data['rua'] ?? null;
    $numero = $data['numero'] ?? null;
    $bairro = $data['bairro'] ?? null;
    $cidade = $data['cidade'] ?? null;
    $estado = $data['estado'] ?? null;
    $cep = $data['cep'] ?? null;
    $observacoes = $data['observacoes'] ?? null;

    if (!$nome || !$cpf_cnpj) {
        echo json_encode(['message' => 'Nome e CPF/CNPJ são obrigatórios']);
        http_response_code(400);
        return;
    }

    $stmt = $conn->prepare("INSERT INTO clientes (nome, cpf_cnpj, email, telefone, rua, numero, bairro, cidade, estado, cep, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssss", $nome, $cpf_cnpj, $email, $telefone, $rua, $numero, $bairro, $cidade, $estado, $cep, $observacoes);

    if ($stmt->execute()) {
        echo json_encode(['message' => 'Cliente criado com sucesso', 'id' => $stmt->insert_id]);
        http_response_code(201);
    } else {
        echo json_encode(['message' => 'Erro ao criar cliente', 'error' => $stmt->error]);
        http_response_code(500);
    }
    $stmt->close();
}

function handlePut($conn) {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo json_encode(['message' => 'ID do cliente é obrigatório']);
        http_response_code(400);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    $nome = $data['nome'] ?? null;
    $cpf_cnpj = $data['cpf_cnpj'] ?? null;
    $email = $data['email'] ?? null;
    $telefone = $data['telefone'] ?? null;
    $rua = $data['rua'] ?? null;
    $numero = $data['numero'] ?? null;
    $bairro = $data['bairro'] ?? null;
    $cidade = $data['cidade'] ?? null;
    $estado = $data['estado'] ?? null;
    $cep = $data['cep'] ?? null;
    $observacoes = $data['observacoes'] ?? null;

    if (!$nome || !$cpf_cnpj) {
        echo json_encode(['message' => 'Nome e CPF/CNPJ são obrigatórios']);
        http_response_code(400);
        return;
    }

    $stmt = $conn->prepare("UPDATE clientes SET nome = ?, cpf_cnpj = ?, email = ?, telefone = ?, rua = ?, numero = ?, bairro = ?, cidade = ?, estado = ?, cep = ?, observacoes = ? WHERE id = ?");
    $stmt->bind_param("sssssssssssi", $nome, $cpf_cnpj, $email, $telefone, $rua, $numero, $bairro, $cidade, $estado, $cep, $observacoes, $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['message' => 'Cliente atualizado com sucesso']);
        } else {
            echo json_encode(['message' => 'Cliente não encontrado ou nenhum dado para atualizar']);
        }
    } else {
        echo json_encode(['message' => 'Erro ao atualizar cliente', 'error' => $stmt->error]);
        http_response_code(500);
    }
    $stmt->close();
}

function handleDelete($conn) {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        echo json_encode(['message' => 'ID do cliente é obrigatório']);
        http_response_code(400);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM clientes WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['message' => 'Cliente excluído com sucesso']);
        } else {
            echo json_encode(['message' => 'Cliente não encontrado']);
        }
    } else {
        echo json_encode(['message' => 'Erro ao excluir cliente', 'error' => $stmt->error]);
        http_response_code(500);
    }
    $stmt->close();
}

$conn->close();

?>