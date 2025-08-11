<?php
require_once 'conexao.php';

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
        $stmt = $conn->prepare("SELECT * FROM eventos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $evento = $result->fetch_assoc();
        if ($evento) {
            echo json_encode($evento);
        } else {
            echo json_encode(['message' => 'Evento não encontrado']);
            http_response_code(404);
        }
        $stmt->close();
    } else {
        $result = $conn->query("SELECT * FROM eventos");
        $eventos = [];
        while ($row = $result->fetch_assoc()) {
            $eventos[] = $row;
        }
        echo json_encode($eventos);
    }
}

function handlePost($conn) {
    $data = json_decode(file_get_contents('php://input'), true);

    $titulo = $data['titulo'] ?? null;
    $status = $data['status'] ?? null;
    $data_inicio = $data['data_inicio'] ?? null;
    $data_fim = $data['data_fim'] ?? null;
    $descricao = $data['descricao'] ?? null;

    if (!$titulo || !$data_inicio) {
        echo json_encode(['message' => 'Título e Data de Início são obrigatórios']);
        http_response_code(400);
        return;
    }

    $stmt = $conn->prepare("INSERT INTO eventos (titulo, status, data_inicio, data_fim, descricao) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $titulo, $status, $data_inicio, $data_fim, $descricao);

    if ($stmt->execute()) {
        echo json_encode(['message' => 'Evento criado com sucesso', 'id' => $stmt->insert_id]);
        http_response_code(201);
    } else {
        echo json_encode(['message' => 'Erro ao criar evento', 'error' => $stmt->error]);
        http_response_code(500);
    }
    $stmt->close();
}

function handlePut($conn) {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo json_encode(['message' => 'ID do evento é obrigatório']);
        http_response_code(400);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    $titulo = $data['titulo'] ?? null;
    $status = $data['status'] ?? null;
    $data_inicio = $data['data_inicio'] ?? null;
    $data_fim = $data['data_fim'] ?? null;
    $descricao = $data['descricao'] ?? null;

    if (!$titulo || !$data_inicio) {
        echo json_encode(['message' => 'Título e Data de Início são obrigatórios']);
        http_response_code(400);
        return;
    }

    $stmt = $conn->prepare("UPDATE eventos SET titulo = ?, status = ?, data_inicio = ?, data_fim = ?, descricao = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $titulo, $status, $data_inicio, $data_fim, $descricao, $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['message' => 'Evento atualizado com sucesso']);
        } else {
            echo json_encode(['message' => 'Evento não encontrado ou nenhum dado para atualizar']);
        }
    } else {
        echo json_encode(['message' => 'Erro ao atualizar evento', 'error' => $stmt->error]);
        http_response_code(500);
    }
    $stmt->close();
}

function handleDelete($conn) {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        echo json_encode(['message' => 'ID do evento é obrigatório']);
        http_response_code(400);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM eventos WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['message' => 'Evento excluído com sucesso']);
        } else {
            echo json_encode(['message' => 'Evento não encontrado']);
        }
    } else {
        echo json_encode(['message' => 'Erro ao excluir evento', 'error' => $stmt->error]);
        http_response_code(500);
    }
    $stmt->close();
}

$conn->close();

?>