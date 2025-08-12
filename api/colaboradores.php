<?php
/**
 * Arquivo de API REST para gerenciar colaboradores.
 *
 * Este script lida com as operações CRUD (Create, Read, Update, Delete) para a entidade 'colaboradores'.
 * Ele suporta os métodos HTTP GET, POST, PUT e DELETE.
 * A principal melhoria é a correção para lidar com requisições PUT que incluem uploads de arquivos.
 * O PHP não preenche nativamente as variáveis $_POST e $_FILES para métodos PUT,
 * então uma função auxiliar foi criada para analisar o corpo da requisição.
 */
require_once 'conexao.php';

// Define o diretório de upload para as fotos dos colaboradores
// Caminho absoluto para a pasta img/colaboradores dentro do projeto
define('UPLOAD_DIR', __DIR__ . '/../img/colaboradores/');

// Garante que o diretório de upload exista
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true); // Cria o diretório recursivamente com permissões de escrita
}

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGet($conn);
        break;
    case 'POST':
        // A função POST agora também lida com o modo de edição, mas a requisição PUT é mais explícita.
        // O cliente envia uma requisição PUT com FormData, que precisa ser tratado manualmente.
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
 * Função auxiliar para analisar o corpo de uma requisição PUT.
 *
 * @return array Um array contendo os dados do formulário e os arquivos enviados.
 */
function parse_put_body() {
    $raw_data = file_get_contents('php://input');
    
    // ===== OBTER CONTENT-TYPE DE FORMA SEGURA =====
    // Usar $_SERVER em vez de getallheaders() para melhor compatibilidade
    $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
    
    // Fallback para getallheaders() se disponível
    if (empty($content_type) && function_exists('getallheaders')) {
        $headers = getallheaders();
        $content_type = $headers['Content-Type'] ?? $headers['content-type'] ?? '';
    }

    // ===== LOG DE DEBUG =====
    error_log("parse_put_body: Content-Type = " . $content_type);
    error_log("parse_put_body: Raw data length = " . strlen($raw_data));
    
    // Verifica se é uma requisição multipart/form-data
    if (strpos($content_type, 'multipart/form-data') !== false) {
        $data = [];
        $files = [];
        // Encontra o boundary
        preg_match('/boundary=(.*)$/', $content_type, $matches);
        
        if (!isset($matches[1])) {
            error_log("parse_put_body: Boundary não encontrado no Content-Type");
            throw new Exception("Boundary não encontrado no Content-Type");
        }
        
        $boundary = $matches[1];
        error_log("parse_put_body: Boundary encontrado = " . $boundary);

        // Divide o corpo da requisição pelo boundary
        $parts = array_slice(explode("--$boundary", $raw_data), 1, -1);

        foreach ($parts as $part) {
            // Ignora partes vazias ou mal formatadas
            if (empty($part) || strpos($part, 'Content-Disposition') === false) {
                continue;
            }

            // Divide o cabeçalho e o conteúdo de cada parte
            [$headers_str, $content] = explode("\r\n\r\n", $part, 2);
            $content = trim($content, "\r\n");
            $headers = explode("\r\n", $headers_str);

            $name = '';
            $filename = '';
            foreach ($headers as $header) {
                if (strpos($header, 'Content-Disposition') !== false) {
                    preg_match('/name="([^"]+)"/', $header, $name_match);
                    $name = $name_match[1];
                    preg_match('/filename="([^"]+)"/', $header, $filename_match);
                    if (isset($filename_match[1])) {
                        $filename = $filename_match[1];
                    }
                }
            }

            if ($filename) { // É um arquivo
                // Extrai o tipo do arquivo
                preg_match('/Content-Type: (.*)/', $headers_str, $type_match);
                $type = $type_match[1] ?? 'application/octet-stream';

                // Cria uma estrutura de arquivo similar ao $_FILES
                $files[$name] = [
                    'name' => $filename,
                    'type' => $type,
                    'tmp_name' => tempnam(sys_get_temp_dir(), 'php'),
                    'error' => UPLOAD_ERR_OK,
                    'size' => strlen($content),
                ];
                file_put_contents($files[$name]['tmp_name'], $content);
            } elseif ($name) { // É um campo de formulário
                $data[$name] = $content;
            }
        }
        
        error_log("parse_put_body: Processamento concluído - " . count($data) . " campos, " . count($files) . " arquivos");
        return ['data' => $data, 'files' => $files];
    } else {
        // Se não for multipart, tenta decodificar como JSON (caso o front mude)
        error_log("parse_put_body: Não é multipart, tentando JSON");
        $data = json_decode($raw_data, true);
        
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            error_log("parse_put_body: Erro ao decodificar JSON - " . json_last_error_msg());
            // Se não conseguir decodificar como JSON, retorna dados vazios
            return ['data' => [], 'files' => []];
        }
        
        return ['data' => $data ?? [], 'files' => []];
    }
}

/**
 * Função auxiliar para fazer o upload da imagem.
 *
 * @param array $file O array de arquivo do $_FILES.
 * @return string|false Retorna o caminho relativo da imagem se o upload for bem-sucedido, ou false em caso de erro.
 */
function uploadImage($file) {
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        error_log("Erro de upload: " . ($file['error'] ?? 'Nenhum arquivo enviado'));
        return false;
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxFileSize = 5 * 1024 * 1024; // 5 MB

    if (!in_array($file['type'], $allowedTypes)) {
        error_log("Tipo de arquivo não permitido: " . $file['type']);
        return false;
    }

    if ($file['size'] > $maxFileSize) {
        error_log("Tamanho do arquivo excedido: " . $file['size']);
        return false;
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('colaborador_') . '.' . $extension;
    $destination = UPLOAD_DIR . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Retorna o caminho relativo para ser salvo no banco de dados
        return 'img/colaboradores/' . $filename;
    } else {
        error_log("Erro ao mover o arquivo para: " . $destination);
        return false;
    }
}

/**
 * Função para lidar com requisições GET.
 *
 * @param mysqli $conn A conexão com o banco de dados.
 */
function handleGet($conn) {
    $id = $_GET['id'] ?? null;

    if ($id) {
        // ===== BUSCAR COLABORADOR ESPECÍFICO COM DADOS DO USUÁRIO =====
        $stmt = $conn->prepare("
            SELECT c.*, u.username as usuario_sistema, u.primeira_senha, u.ultimo_login
            FROM colaboradores c
            LEFT JOIN usuarios u ON c.id = u.colaborador_id
            WHERE c.id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $colaborador = $result->fetch_assoc();
        if ($colaborador) {
            // Garante que a foto_url use barras normais para URLs
            if (isset($colaborador['foto_url'])) {
                $colaborador['foto_url'] = str_replace('\\', '/', $colaborador['foto_url']);
            }
            
            // Se não tem campo usuario na tabela colaboradores, usar usuario_sistema
            if (empty($colaborador['usuario']) && !empty($colaborador['usuario_sistema'])) {
                $colaborador['usuario'] = $colaborador['usuario_sistema'];
            }
            
            // Remover campos internos se existirem
            unset($colaborador['usuario_sistema']);
            
            echo json_encode($colaborador);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Colaborador não encontrado']);
        }
        $stmt->close();
    } else {
        // ===== LISTAR TODOS COLABORADORES =====
        $result = $conn->query("
            SELECT c.*, u.username as usuario_sistema, u.primeira_senha, u.ultimo_login
            FROM colaboradores c
            LEFT JOIN usuarios u ON c.id = u.colaborador_id
            ORDER BY c.data_cadastro DESC
        ");
        $colaboradores = [];
        while ($row = $result->fetch_assoc()) {
            // Garante que a foto_url use barras normais para URLs para todos os colaboradores
            if (isset($row['foto_url'])) {
                $row['foto_url'] = str_replace('\\', '/', $row['foto_url']);
            }
            
            // Se não tem campo usuario na tabela colaboradores, usar usuario_sistema
            if (empty($row['usuario']) && !empty($row['usuario_sistema'])) {
                $row['usuario'] = $row['usuario_sistema'];
            }
            
            // Remover campos internos
            unset($row['usuario_sistema']);
            
            $colaboradores[] = $row;
        }
        echo json_encode($colaboradores);
    }
}

/**
 * Função para lidar com requisições POST (Criação).
 *
 * @param mysqli $conn A conexão com o banco de dados.
 */
function handlePost($conn) {
    // Dados vêm de $_POST quando enctype="multipart/form-data"
    $nome = $_POST['nome'] ?? null;
    $cpf = $_POST['cpf'] ?? null;
    $cargo = $_POST['cargo'] ?? null;
    $departamento = $_POST['departamento'] ?? null;
    $email = $_POST['email'] ?? null;
    $usuario = $_POST['usuario'] ?? null;
    $telefone = $_POST['telefone'] ?? null;
    $data_contratacao = $_POST['data_contratacao'] ?? null;
    
    $nivel_acesso = $_POST['nivel_acesso'] ?? null;
    $observacoes = $_POST['observacoes'] ?? null;

    $foto_url = null;
    if (isset($_FILES['foto_url']) && $_FILES['foto_url']['error'] === UPLOAD_ERR_OK) {
        $uploadedPath = uploadImage($_FILES['foto_url']);
        if ($uploadedPath) {
            $foto_url = $uploadedPath;
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao fazer upload da imagem.']);
            return;
        }
    }

    if (!$nome || !$cpf || !$email || !$usuario) {
        http_response_code(400);
        echo json_encode(['message' => 'Nome, CPF, Email e Usuário são obrigatórios']);
        return;
    }

    $stmt = $conn->prepare("INSERT INTO colaboradores (nome, cpf, cargo, departamento, email, usuario, telefone, data_contratacao, nivel_acesso, foto_url, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssss", $nome, $cpf, $cargo, $departamento, $email, $usuario, $telefone, $data_contratacao, $nivel_acesso, $foto_url, $observacoes);

    if ($stmt->execute()) {
        $colaborador_id = $stmt->insert_id;
        
        // ===== CRIAR USUÁRIO AUTOMATICAMENTE =====
        // Após criar colaborador, criar usuário para login usando CPF como senha inicial
        $created_user = createUserForCollaborator($conn, $colaborador_id, $usuario, $email, $cpf, $nivel_acesso);
        
        if ($created_user['success']) {
            http_response_code(201);
            echo json_encode([
                'success' => true, 
                'message' => 'Colaborador e usuário criados com sucesso', 
                'id' => $colaborador_id, 
                'user_id' => $created_user['user_id'],
                'foto_url' => $foto_url
            ]);
        } else {
            // Se falhou ao criar usuário, ainda retorna sucesso do colaborador mas com aviso
            http_response_code(201);
            echo json_encode([
                'success' => true, 
                'message' => 'Colaborador criado com sucesso, mas houve erro ao criar usuário: ' . $created_user['message'], 
                'id' => $colaborador_id,
                'foto_url' => $foto_url,
                'user_creation_error' => true
            ]);
        }
    } else {
        if ($stmt->errno === 1062) { // Código de erro do MySQL para entrada duplicada
            $errorMessage = $stmt->error;
            $field = 'unknown';
            if (strpos($errorMessage, 'for key \'cpf\'') !== false || strpos($errorMessage, 'for key \'colaboradores.cpf\'') !== false) {
                $field = 'cpf';
            } elseif (strpos($errorMessage, 'for key \'email\'') !== false || strpos($errorMessage, 'for key \'colaboradores.email\'') !== false) {
                $field = 'email';
            }
            http_response_code(409); // Conflito
            echo json_encode(['success' => false, 'message' => 'Erro de duplicidade: ' . ($field === 'unknown' ? 'um campo único' : 'o campo ' . $field) . ' já existe.', 'field' => $field]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao criar colaborador', 'error' => $stmt->error]);
        }
    }
    $stmt->close();
}

/**
 * Função para lidar com requisições PUT (Edição).
 *
 * @param mysqli $conn A conexão com o banco de dados.
 */
function handlePut($conn) {
    // ===== LOG DE DEBUG =====
    error_log("PUT request iniciada para colaboradores");
    
    $id = $_GET['id'] ?? null;
    if (!$id) {
        error_log("PUT: ID do colaborador não fornecido");
        http_response_code(400);
        echo json_encode(['message' => 'ID do colaborador é obrigatório']);
        return;
    }
    
    error_log("PUT: Processando colaborador ID: " . $id);

    try {
        // A requisição PUT com multipart/form-data não preenche $_POST e $_FILES.
        // Usamos a função auxiliar para analisar o corpo da requisição.
        $parsed_body = parse_put_body();
        $data = $parsed_body['data'];
        $files = $parsed_body['files'];
        
        error_log("PUT: Dados parseados com sucesso - " . count($data) . " campos, " . count($files) . " arquivos");
    } catch (Exception $e) {
        error_log("PUT: Erro ao parsear dados - " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['message' => 'Erro ao processar dados da requisição: ' . $e->getMessage()]);
        return;
    }

    // Buscar dados existentes do colaborador para manter a foto_url se nenhuma nova for enviada
    $stmt = $conn->prepare("SELECT foto_url FROM colaboradores WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingColaborador = $result->fetch_assoc();
    $stmt->close();

    $current_foto_url = $existingColaborador['foto_url'] ?? null;

    $nome = $data['nome'] ?? null;
    $cpf = $data['cpf'] ?? null;
    $cargo = $data['cargo'] ?? null;
    $departamento = $data['departamento'] ?? null;
    $email = $data['email'] ?? null;
    $usuario = $data['usuario'] ?? null;
    $telefone = $data['telefone'] ?? null;
    $data_contratacao = $data['data_contratacao'] ?? null;
    
    $nivel_acesso = $data['nivel_acesso'] ?? null;
    $observacoes = $data['observacoes'] ?? null;

    $foto_url_to_save = $current_foto_url; // Por padrão, mantém a foto existente

    // Verifica se um novo arquivo foi enviado na requisição PUT
    if (isset($files['foto_url']) && $files['foto_url']['error'] === UPLOAD_ERR_OK) {
        $uploadedPath = uploadImage($files['foto_url']);
        if ($uploadedPath) {
            // Se um novo arquivo foi enviado com sucesso, exclui o antigo (se existir)
            if ($current_foto_url && file_exists(__DIR__ . '/../' . $current_foto_url)) {
                unlink(__DIR__ . '/../' . $current_foto_url);
            }
            $foto_url_to_save = $uploadedPath;
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao fazer upload da nova imagem.']);
            return;
        }
    } elseif (isset($data['foto_url_removed']) && $data['foto_url_removed'] === 'true') {
        // Se o frontend indicar que a foto foi removida
        if ($current_foto_url && file_exists(__DIR__ . '/../' . $current_foto_url)) {
            unlink(__DIR__ . '/../' . $current_foto_url);
        }
        $foto_url_to_save = null;
    }

    if (!$nome || !$cpf || !$email || !$usuario) {
        http_response_code(400);
        echo json_encode(['message' => 'Nome, CPF, Email e Usuário são obrigatórios']);
        return;
    }

    $stmt = $conn->prepare("UPDATE colaboradores SET nome = ?, cpf = ?, cargo = ?, departamento = ?, email = ?, usuario = ?, telefone = ?, data_contratacao = ?, nivel_acesso = ?, foto_url = ?, observacoes = ? WHERE id = ?");
    $stmt->bind_param("sssssssssssi", $nome, $cpf, $cargo, $departamento, $email, $usuario, $telefone, $data_contratacao, $nivel_acesso, $foto_url_to_save, $observacoes, $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Colaborador atualizado com sucesso', 'foto_url' => $foto_url_to_save]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Colaborador não encontrado ou nenhum dado para atualizar']);
        }
    } else {
        if ($stmt->errno === 1062) { // Código de erro do MySQL para entrada duplicada
            $errorMessage = $stmt->error;
            $field = 'unknown';
            if (strpos($errorMessage, 'for key \'cpf\'') !== false || strpos($errorMessage, 'for key \'colaboradores.cpf\'') !== false) {
                $field = 'cpf';
            } elseif (strpos($errorMessage, 'for key \'email\'') !== false || strpos($errorMessage, 'for key \'colaboradores.email\'') !== false) {
                $field = 'email';
            }
            http_response_code(409); // Conflito
            echo json_encode(['success' => false, 'message' => 'Erro de duplicidade: ' . ($field === 'unknown' ? 'um campo único' : 'o campo ' . $field) . ' já existe.', 'field' => $field]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar colaborador', 'error' => $stmt->error]);
        }
    }
    $stmt->close();
}

/**
 * Função para lidar com requisições DELETE.
 *
 * @param mysqli $conn A conexão com o banco de dados.
 */
function handleDelete($conn) {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['message' => 'ID do colaborador é obrigatório']);
        return;
    }

    // Antes de deletar o registro, buscar a foto_url para deletar o arquivo físico
    $stmt = $conn->prepare("SELECT foto_url FROM colaboradores WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $colaboradorToDelete = $result->fetch_assoc();
    $stmt->close();

    if ($colaboradorToDelete && $colaboradorToDelete['foto_url']) {
        $filePath = __DIR__ . '/../' . $colaboradorToDelete['foto_url'];
        if (file_exists($filePath)) {
            unlink($filePath); // Deleta o arquivo físico
        }
    }

    $stmt = $conn->prepare("DELETE FROM colaboradores WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['message' => 'Colaborador excluído com sucesso']);
        }
        else {
            http_response_code(404);
            echo json_encode(['message' => 'Colaborador não encontrado']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Erro ao excluir colaborador', 'error' => $stmt->error]);
    }
    $stmt->close();
}

/**
 * ===== FUNÇÃO PARA CRIAR USUÁRIO AUTOMATICAMENTE =====
 * 
 * Cria um usuário no sistema vinculado ao colaborador recém-criado
 * A senha inicial será o CPF do colaborador (apenas números)
 * O usuário será marcado como primeira_senha = TRUE para forçar troca no primeiro login
 * 
 * @param mysqli $conn Conexão com banco de dados
 * @param int $colaborador_id ID do colaborador criado
 * @param string $username Nome de usuário
 * @param string $email E-mail do colaborador
 * @param string $cpf CPF do colaborador (será usado como senha inicial)
 * @param string $nivel_acesso Nível de acesso do colaborador
 * @return array Array com success (bool) e message/user_id
 */
function createUserForCollaborator($conn, $colaborador_id, $username, $email, $cpf, $nivel_acesso) {
    try {
        error_log("Criando usuário para colaborador ID: $colaborador_id");
        
        // ===== LIMPAR CPF PARA USAR COMO SENHA =====
        // Remove pontos, hífens e espaços do CPF para usar como senha
        $senha_inicial = preg_replace('/[^0-9]/', '', $cpf);
        
        if (empty($senha_inicial) || strlen($senha_inicial) != 11) {
            return [
                'success' => false,
                'message' => 'CPF inválido para gerar senha inicial'
            ];
        }
        
        // ===== VERIFICAR SE USUÁRIO JÁ EXISTE =====
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt->close();
            return [
                'success' => false,
                'message' => 'Nome de usuário ou e-mail já existe no sistema'
            ];
        }
        $stmt->close();
        
        // ===== CRIAR HASH DA SENHA =====
        $password_hash = password_hash($senha_inicial, PASSWORD_DEFAULT);
        
        // ===== INSERIR USUÁRIO =====
        $stmt = $conn->prepare("
            INSERT INTO usuarios (colaborador_id, username, email, password_hash, nivel_acesso, primeira_senha) 
            VALUES (?, ?, ?, ?, ?, TRUE)
        ");
        $stmt->bind_param("issss", $colaborador_id, $username, $email, $password_hash, $nivel_acesso);
        
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            error_log("Usuário criado com sucesso: ID $user_id para colaborador $colaborador_id");
            $stmt->close();
            
            return [
                'success' => true,
                'user_id' => $user_id,
                'message' => 'Usuário criado com sucesso'
            ];
        } else {
            error_log("Erro ao inserir usuário: " . $stmt->error);
            $stmt->close();
            return [
                'success' => false,
                'message' => 'Erro ao inserir usuário no banco de dados'
            ];
        }
        
    } catch (Exception $e) {
        error_log("Exceção ao criar usuário: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Erro interno: ' . $e->getMessage()
        ];
    }
}

$conn->close();

?>
