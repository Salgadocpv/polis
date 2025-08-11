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

    if ($id) {
        $stmt = $conn->prepare("SELECT * FROM projetos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $projeto = $result->fetch_assoc();
        if ($projeto) {
            echo json_encode($projeto);
        } else {
            echo json_encode(['message' => 'Projeto não encontrado']);
            http_response_code(404);
        }
        $stmt->close();
    } else {
        $result = $conn->query("SELECT p.*, c.nome as cliente_nome FROM projetos p LEFT JOIN clientes c ON p.cliente_id = c.id");
        $projetos = [];
        while ($row = $result->fetch_assoc()) {
            $projetos[] = $row;
        }
        echo json_encode($projetos);
    }
}

function handlePost($conn) {
    $data = json_decode(file_get_contents('php://input'), true);

    $nome = $data['nome'] ?? null;
    $cliente_id = $data['cliente_id'] ?? null;
    $responsavel = $data['responsavel'] ?? null;
    $status = $data['status'] ?? null;
    $data_inicio = $data['data_inicio'] ?? null;
    $data_conclusao_prevista = $data['data_conclusao_prevista'] ?? null;
    $orcamento = $data['orcamento'] ?? null;
    $descricao = $data['descricao'] ?? null;

    if (!$nome || !$cliente_id || !$responsavel || !$status || !$data_inicio) {
        echo json_encode(['message' => 'Campos obrigatórios faltando']);
        http_response_code(400);
        return;
    }

    $stmt = $conn->prepare("INSERT INTO projetos (nome, cliente_id, responsavel, status, data_inicio, data_conclusao_prevista, orcamento, descricao) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sissssds", $nome, $cliente_id, $responsavel, $status, $data_inicio, $data_conclusao_prevista, $orcamento, $descricao);

    if ($stmt->execute()) {
        echo json_encode(['message' => 'Projeto criado com sucesso', 'id' => $stmt->insert_id]);
        http_response_code(201);
    } else {
        echo json_encode(['message' => 'Erro ao criar projeto', 'error' => $stmt->error]);
        http_response_code(500);
    }
    $stmt->close();
}

function handlePut($conn) {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo json_encode(['message' => 'ID do projeto é obrigatório']);
        http_response_code(400);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    $nome = $data['nome'] ?? null;
    $cliente_id = $data['cliente_id'] ?? null;
    $responsavel = $data['responsavel'] ?? null;
    $status = $data['status'] ?? null;
    $data_inicio = $data['data_inicio'] ?? null;
    $data_conclusao_prevista = $data['data_conclusao_prevista'] ?? null;
    $orcamento = $data['orcamento'] ?? null;
    $descricao = $data['descricao'] ?? null;

    if (!$nome || !$cliente_id || !$responsavel || !$status || !$data_inicio) {
        echo json_encode(['message' => 'Campos obrigatórios faltando']);
        http_response_code(400);
        return;
    }

    $stmt = $conn->prepare("UPDATE projetos SET nome = ?, cliente_id = ?, responsavel = ?, status = ?, data_inicio = ?, data_conclusao_prevista = ?, orcamento = ?, descricao = ? WHERE id = ?");
    $stmt->bind_param("sissssdsi", $nome, $cliente_id, $responsavel, $status, $data_inicio, $data_conclusao_prevista, $orcamento, $descricao, $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['message' => 'Projeto atualizado com sucesso']);
        } else {
            echo json_encode(['message' => 'Projeto não encontrado ou nenhum dado para atualizar']);
        }
    } else {
        echo json_encode(['message' => 'Erro ao atualizar projeto', 'error' => $stmt->error]);
        http_response_code(500);
    }
    $stmt->close();
}

function handleDelete($conn) {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        echo json_encode(['message' => 'ID do projeto é obrigatório']);
        http_response_code(400);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM projetos WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['message' => 'Projeto excluído com sucesso']);
        } else {
            echo json_encode(['message' => 'Projeto não encontrado']);
        }
    } else {
        echo json_encode(['message' => 'Erro ao excluir projeto', 'error' => $stmt->error]);
        http_response_code(500);
    }
    $stmt->close();
}

$conn->close();

?>