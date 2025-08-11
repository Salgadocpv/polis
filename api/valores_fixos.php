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
    case 'DELETE':
        handleDelete($conn);
        break;
    default:
        http_response_code(405); // Método não permitido
        echo json_encode(['message' => 'Método não permitido']);
        break;
}

function handleGet($conn) {
    $tipo = $_GET['tipo'] ?? null;

    if ($tipo) {
        $stmt = $conn->prepare("SELECT id, valor FROM valores_fixos WHERE tipo = ? ORDER BY valor");
        $stmt->bind_param("s", $tipo);
        $stmt->execute();
        $result = $stmt->get_result();
        $valores = [];
        while ($row = $result->fetch_assoc()) {
            $valores[] = $row;
        }
        echo json_encode($valores);
    } else {
        // Se nenhum tipo for especificado, retorna todos os valores fixos agrupados por tipo
        $result = $conn->query("SELECT id, tipo, valor FROM valores_fixos ORDER BY tipo, valor");
        $valores_agrupados = [];
        while ($row = $result->fetch_assoc()) {
            $valores_agrupados[$row['tipo']][] = ['id' => $row['id'], 'valor' => $row['valor']];
        }
        echo json_encode($valores_agrupados);
    }
}

function handlePost($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $tipo = $data['tipo'] ?? null;
    $valor = $data['valor'] ?? null;

    if (!$tipo || !$valor) {
        http_response_code(400);
        echo json_encode(['message' => 'Tipo e valor são obrigatórios.']);
        return;
    }

    $stmt = $conn->prepare("INSERT INTO valores_fixos (tipo, valor) VALUES (?, ?)");
    $stmt->bind_param("ss", $tipo, $valor);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(['message' => 'Valor fixo adicionado com sucesso!', 'id' => $conn->insert_id]);
    } else {
        if ($stmt->errno === 1062) { // Duplicate entry
            http_response_code(409);
            echo json_encode(['message' => 'Este valor já existe para este tipo.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Erro ao adicionar valor fixo.', 'error' => $stmt->error]);
        }
    }
}

function handleDelete($conn) {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['message' => 'ID é obrigatório.']);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM valores_fixos WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['message' => 'Valor fixo excluído com sucesso!']);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Valor fixo não encontrado.']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Erro ao excluir valor fixo.', 'error' => $stmt->error]);
    }
}

$conn->close();
?>