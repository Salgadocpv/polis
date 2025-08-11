<?php
/**
 * Utilitário de Administração do Banco de Dados - Polis
 * Este script permite executar comandos SQL diretamente no banco
 */
require_once 'api/conexao.php';

// Headers para API REST
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

$method = $_SERVER['REQUEST_METHOD'];
$response = ['success' => false, 'data' => null, 'error' => null, 'info' => null];

try {
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            throw new Exception('JSON inválido ou vazio');
        }
        
        $action = $input['action'] ?? '';
        $sql = $input['sql'] ?? '';
        
        switch ($action) {
            case 'execute':
                if (empty($sql)) {
                    throw new Exception('SQL não fornecido');
                }
                
                // Log da operação
                error_log("[DB_ADMIN] Executando: " . substr($sql, 0, 100) . "...");
                
                $result = $conn->query($sql);
                
                if ($result === false) {
                    throw new Exception("Erro SQL: " . $conn->error);
                }
                
                if ($result === true) {
                    // Comandos como INSERT, UPDATE, DELETE
                    $response['success'] = true;
                    $response['info'] = [
                        'type' => 'modification',
                        'affected_rows' => $conn->affected_rows,
                        'insert_id' => $conn->insert_id > 0 ? $conn->insert_id : null
                    ];
                    $response['data'] = "Comando executado com sucesso. Linhas afetadas: " . $conn->affected_rows;
                } else {
                    // Comandos SELECT
                    $data = [];
                    while ($row = $result->fetch_assoc()) {
                        $data[] = $row;
                    }
                    $response['success'] = true;
                    $response['data'] = $data;
                    $response['info'] = [
                        'type' => 'select',
                        'num_rows' => $result->num_rows
                    ];
                }
                break;
                
            case 'show_tables':
                $result = $conn->query("SHOW TABLES");
                $tables = [];
                while ($row = $result->fetch_array()) {
                    $tables[] = $row[0];
                }
                $response['success'] = true;
                $response['data'] = $tables;
                break;
                
            case 'describe':
                $table = $input['table'] ?? '';
                if (empty($table)) {
                    throw new Exception('Nome da tabela não fornecido');
                }
                
                $result = $conn->query("DESCRIBE `$table`");
                $columns = [];
                while ($row = $result->fetch_assoc()) {
                    $columns[] = $row;
                }
                $response['success'] = true;
                $response['data'] = $columns;
                break;
                
            case 'table_count':
                $table = $input['table'] ?? '';
                if (empty($table)) {
                    throw new Exception('Nome da tabela não fornecido');
                }
                
                $result = $conn->query("SELECT COUNT(*) as total FROM `$table`");
                $count = $result->fetch_assoc();
                $response['success'] = true;
                $response['data'] = $count['total'];
                break;
                
            default:
                throw new Exception('Ação não reconhecida: ' . $action);
        }
        
    } else if ($method === 'GET') {
        // Info básica do banco
        $dbInfo = [];
        $dbInfo['database'] = DB_NAME;
        $dbInfo['host'] = DB_HOST;
        $dbInfo['user'] = DB_USER;
        $dbInfo['charset'] = $conn->character_set_name();
        
        // Listar tabelas
        $result = $conn->query("SHOW TABLES");
        $tables = [];
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
        $dbInfo['tables'] = $tables;
        
        $response['success'] = true;
        $response['data'] = $dbInfo;
        
    } else {
        throw new Exception('Método HTTP não suportado: ' . $method);
    }
    
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
    error_log("[DB_ADMIN] Erro: " . $e->getMessage());
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$conn->close();
?>