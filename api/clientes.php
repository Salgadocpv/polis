<?php
/**
 * API RESTFUL PARA GERENCIAMENTO DE CLIENTES
 * 
 * Esta API oferece operações CRUD completas para o gerenciamento de clientes
 * do sistema Polis Engenharia, seguindo padrões REST modernos.
 * 
 * Endpoints disponíveis:
 * - GET /api/clientes.php           - Lista todos os clientes (com paginação/busca)
 * - GET /api/clientes.php?id=123    - Busca cliente específico (com dados de projetos)
 * - POST /api/clientes.php          - Cria novo cliente
 * - PUT /api/clientes.php?id=123    - Atualiza cliente existente
 * - DELETE /api/clientes.php?id=123 - Remove cliente
 * 
 * Recursos de segurança:
 * - Headers HTTP de segurança
 * - Sanitização de inputs
 * - Prepared statements (SQL injection protection)
 * - Logging de auditoria
 * - Validação de dados obrigatórios
 */

// ===== DEPENDÊNCIAS =====
require_once 'conexao.php';                    // Conexão com banco de dados
require_once '../includes/Security.php';       // Classe de segurança
require_once '../includes/DatabaseHelper.php'; // Helper para operações de banco

// ===== CONFIGURAÇÃO DE SEGURANÇA =====
Security::setSecurityHeaders();  // Aplica headers HTTP de segurança
header('Content-Type: application/json'); // Define resposta como JSON

// ===== ROTEAMENTO POR MÉTODO HTTP =====
// Captura o método HTTP da requisição e direciona para função adequada
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Busca/listagem de clientes
        handleGet($conn);
        break;
    case 'POST':
        // Criação de novo cliente
        handlePost($conn);
        break;
    case 'PUT':
        // Atualização de cliente existente
        handlePut($conn);
        break;
    case 'DELETE':
        // Remoção de cliente
        handleDelete($conn);
        break;
    default:
        // Método HTTP não suportado
        echo json_encode(['message' => 'Método não permitido']);
        http_response_code(405); // Method Not Allowed
        break;
}

/**
 * FUNÇÃO GET - BUSCA E LISTAGEM DE CLIENTES
 * 
 * Gerencia duas operações principais:
 * 1. Busca de cliente específico (quando ID é fornecido)
 * 2. Listagem de clientes (com busca opcional)
 * 
 * Recursos:
 * - JOIN com tabela projetos para dados agregados
 * - Busca em múltiplos campos
 * - Logging de auditoria
 * - Tratamento de erros
 * 
 * Parâmetros GET aceitos:
 * - id: ID específico do cliente (opcional)
 * - page: Página para paginação (opcional, padrão: 1)  
 * - per_page: Registros por página (opcional, padrão: 10)
 * - search: Termo de busca (opcional)
 */
function handleGet($conn) {
    // ===== CAPTURA E SANITIZAÇÃO DE PARÂMETROS =====
    $id = $_GET['id'] ?? null;                                    // ID específico do cliente
    $page = intval($_GET['page'] ?? 1);                          // Página atual (paginação)
    $perPage = intval($_GET['per_page'] ?? 10);                  // Registros por página
    $search = Security::sanitizeInput($_GET['search'] ?? '');     // Termo de busca sanitizado

    // ===== BUSCA DE CLIENTE ESPECÍFICO =====
    if ($id) {
        // Query com LEFT JOIN para incluir dados agregados dos projetos
        // Retorna: dados do cliente + total_projetos + total_orcamentos
        $stmt = $conn->prepare("
            SELECT c.*, 
                   COUNT(p.id) as total_projetos,                    -- Conta projetos do cliente
                   COALESCE(SUM(p.orcamento), 0) as total_orcamentos -- Soma orçamentos (0 se nulo)
            FROM clientes c
            LEFT JOIN projetos p ON c.id = p.cliente_id              -- JOIN para relacionar cliente-projetos
            WHERE c.id = ?
            GROUP BY c.id                                             -- Agrupa para usar COUNT e SUM
        ");
        
        // Vincula parâmetro ID como integer
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cliente = $result->fetch_assoc();
        
        if ($cliente) {
            // ===== CLIENTE ENCONTRADO =====
            // Registra visualização no log de auditoria
            Security::logActivity('VIEW_CLIENT', "Client ID: $id");
            
            // Retorna dados do cliente com informações agregadas
            echo json_encode($cliente);
        } else {
            // ===== CLIENTE NÃO ENCONTRADO =====
            echo json_encode(['message' => 'Cliente não encontrado']);
            http_response_code(404); // Not Found
        }
        $stmt->close();
        
    } else {
        // ===== LISTAGEM DE CLIENTES (COM BUSCA OPCIONAL) =====
        try {
            if ($search) {
                // ===== BUSCA COM FILTRO =====
                // Prepara termo para busca com LIKE (wildcards)
                $searchTerm = "%$search%";
                
                // Query com busca em múltiplos campos relevantes
                $stmt = $conn->prepare("
                    SELECT * FROM clientes 
                    WHERE nome LIKE ? OR email LIKE ? OR cpf_cnpj LIKE ? OR cidade LIKE ? OR telefone LIKE ?
                    ORDER BY nome                                       -- Ordena alfabeticamente
                ");
                
                // Vincula o mesmo termo para todos os campos de busca
                $stmt->bind_param("sssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
                
            } else {
                // ===== LISTAGEM COMPLETA =====
                // Query simples para todos os clientes
                $stmt = $conn->prepare("SELECT * FROM clientes ORDER BY nome");
            }
            
            // Executa query preparada
            $stmt->execute();
            $result = $stmt->get_result();
            
            // ===== CONSTRUÇÃO DO ARRAY DE RESULTADOS =====
            $clientes = [];
            while ($row = $result->fetch_assoc()) {
                $clientes[] = $row; // Adiciona cada cliente ao array
            }
            
            // ===== LOGGING E RESPOSTA =====
            // Registra operação de listagem com contagem
            Security::logActivity('LIST_CLIENTS', "Total found: " . count($clientes));
            
            // Retorna array JSON com todos os clientes
            echo json_encode($clientes);
            $stmt->close();
            
        } catch (Exception $e) {
            // ===== TRATAMENTO DE ERROS =====
            // Em caso de erro na query, retorna erro 500
            echo json_encode(['error' => $e->getMessage()]);
            http_response_code(500); // Internal Server Error
        }
    }
}

/**
 * FUNÇÃO POST - CRIAÇÃO DE NOVO CLIENTE
 * 
 * Cria um novo registro de cliente no sistema com validação
 * de dados obrigatórios e tratamento de erros
 * 
 * Dados aceitos via JSON no corpo da requisição:
 * - nome (obrigatório): Nome completo do cliente
 * - cpf_cnpj (obrigatório): CPF ou CNPJ do cliente
 * - email (opcional): Endereço de e-mail
 * - telefone (opcional): Número de telefone
 * - rua, numero, bairro, cidade, estado, cep (opcionais): Endereço completo
 * - observacoes (opcional): Observações adicionais
 * 
 * Resposta: JSON com mensagem de sucesso e ID do cliente criado
 */
function handlePost($conn) {
    // ===== CAPTURA DE DADOS JSON =====
    // Decodifica JSON do corpo da requisição HTTP
    $data = json_decode(file_get_contents('php://input'), true);

    // ===== EXTRAÇÃO DE CAMPOS DO JSON =====
    // Extrai todos os campos possíveis, com fallback para null
    $nome = $data['nome'] ?? null;              // Nome completo (obrigatório)
    $cpf_cnpj = $data['cpf_cnpj'] ?? null;      // CPF ou CNPJ (obrigatório)
    $email = $data['email'] ?? null;            // E-mail de contato
    $telefone = $data['telefone'] ?? null;      // Telefone de contato
    $rua = $data['rua'] ?? null;                // Logradouro
    $numero = $data['numero'] ?? null;          // Número da residência/empresa
    $bairro = $data['bairro'] ?? null;          // Bairro
    $cidade = $data['cidade'] ?? null;          // Cidade
    $estado = $data['estado'] ?? null;          // Estado/UF
    $cep = $data['cep'] ?? null;                // Código postal
    $observacoes = $data['observacoes'] ?? null; // Observações adicionais

    // ===== VALIDAÇÃO DE CAMPOS OBRIGATÓRIOS =====
    if (!$nome || !$cpf_cnpj) {
        // Retorna erro 400 (Bad Request) para dados inválidos
        echo json_encode(['message' => 'Nome e CPF/CNPJ são obrigatórios']);
        http_response_code(400);
        return; // Interrompe execução
    }

    // ===== PREPARAÇÃO DA QUERY INSERT =====
    // Prepared statement para inserção segura (prevenção SQL injection)
    $stmt = $conn->prepare("INSERT INTO clientes (nome, cpf_cnpj, email, telefone, rua, numero, bairro, cidade, estado, cep, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Vincula todos os parâmetros como strings ("sssssssssss" = 11 strings)
    $stmt->bind_param("sssssssssss", $nome, $cpf_cnpj, $email, $telefone, $rua, $numero, $bairro, $cidade, $estado, $cep, $observacoes);

    // ===== EXECUÇÃO E TRATAMENTO DE RESULTADO =====
    if ($stmt->execute()) {
        // ===== CRIAÇÃO BEM-SUCEDIDA =====
        // $stmt->insert_id retorna o ID auto-increment do registro inserido
        echo json_encode([
            'message' => 'Cliente criado com sucesso', 
            'id' => $stmt->insert_id  // ID do novo cliente para referência
        ]);
        http_response_code(201); // Created
        
    } else {
        // ===== ERRO NA CRIAÇÃO =====
        // Retorna detalhes do erro SQL para debug
        echo json_encode([
            'message' => 'Erro ao criar cliente', 
            'error' => $stmt->error  // Mensagem de erro do MySQL
        ]);
        http_response_code(500); // Internal Server Error
    }
    
    // ===== LIMPEZA DE RECURSOS =====
    $stmt->close(); // Fecha statement para liberar recursos
}

/**
 * FUNÇÃO PUT - ATUALIZAÇÃO DE CLIENTE EXISTENTE
 * 
 * Atualiza todos os dados de um cliente específico identificado pelo ID
 * Requer que o ID seja fornecido como parâmetro GET na URL
 * 
 * URL esperada: /api/clientes.php?id=123
 * 
 * Validações:
 * - Verifica se ID foi fornecido
 * - Valida campos obrigatórios (nome, cpf_cnpj)
 * - Confirma se registro foi realmente modificado
 * 
 * Resposta: JSON com status da operação
 */
function handlePut($conn) {
    // ===== VALIDAÇÃO DO PARÂMETRO ID =====
    $id = $_GET['id'] ?? null;  // Captura ID da URL
    
    if (!$id) {
        // ID é obrigatório para atualização
        echo json_encode(['message' => 'ID do cliente é obrigatório']);
        http_response_code(400); // Bad Request
        return; // Interrompe execução
    }

    // ===== CAPTURA DE DADOS JSON =====
    // Decodifica dados de atualização do corpo da requisição
    $data = json_decode(file_get_contents('php://input'), true);

    // ===== EXTRAÇÃO DE CAMPOS DO JSON =====
    // Mesmo padrão da função POST - extrai todos os campos possíveis
    $nome = $data['nome'] ?? null;              // Nome completo (obrigatório)
    $cpf_cnpj = $data['cpf_cnpj'] ?? null;      // CPF ou CNPJ (obrigatório)
    $email = $data['email'] ?? null;            // E-mail de contato
    $telefone = $data['telefone'] ?? null;      // Telefone de contato
    $rua = $data['rua'] ?? null;                // Logradouro
    $numero = $data['numero'] ?? null;          // Número da residência/empresa
    $bairro = $data['bairro'] ?? null;          // Bairro
    $cidade = $data['cidade'] ?? null;          // Cidade
    $estado = $data['estado'] ?? null;          // Estado/UF
    $cep = $data['cep'] ?? null;                // Código postal
    $observacoes = $data['observacoes'] ?? null; // Observações adicionais

    // ===== VALIDAÇÃO DE CAMPOS OBRIGATÓRIOS =====
    if (!$nome || !$cpf_cnpj) {
        // Mesmo que POST, nome e CPF/CNPJ são obrigatórios
        echo json_encode(['message' => 'Nome e CPF/CNPJ são obrigatórios']);
        http_response_code(400); // Bad Request
        return; // Interrompe execução
    }

    // ===== PREPARAÇÃO DA QUERY UPDATE =====
    // Prepared statement para atualização segura
    // WHERE id = ? garante que apenas o registro específico seja alterado
    $stmt = $conn->prepare("UPDATE clientes SET nome = ?, cpf_cnpj = ?, email = ?, telefone = ?, rua = ?, numero = ?, bairro = ?, cidade = ?, estado = ?, cep = ?, observacoes = ? WHERE id = ?");
    
    // Vincula parâmetros: 11 strings + 1 integer (ID)
    // "sssssssssssi" = 11 strings + 1 integer
    $stmt->bind_param("sssssssssssi", $nome, $cpf_cnpj, $email, $telefone, $rua, $numero, $bairro, $cidade, $estado, $cep, $observacoes, $id);

    // ===== EXECUÇÃO E TRATAMENTO DE RESULTADO =====
    if ($stmt->execute()) {
        // Execução bem-sucedida, mas verifica se algo foi realmente alterado
        
        if ($stmt->affected_rows > 0) {
            // ===== ATUALIZAÇÃO BEM-SUCEDIDA =====
            // affected_rows > 0 significa que dados foram modificados
            echo json_encode(['message' => 'Cliente atualizado com sucesso']);
            http_response_code(200); // OK
            
        } else {
            // ===== NENHUM DADO ALTERADO =====
            // Pode significar: cliente não existe OU dados enviados são idênticos aos atuais
            echo json_encode(['message' => 'Cliente não encontrado ou nenhum dado para atualizar']);
            http_response_code(200); // OK (não é exatamente um erro)
        }
        
    } else {
        // ===== ERRO NA EXECUÇÃO =====
        // Erro SQL durante a atualização
        echo json_encode([
            'message' => 'Erro ao atualizar cliente', 
            'error' => $stmt->error  // Detalhes do erro MySQL
        ]);
        http_response_code(500); // Internal Server Error
    }
    
    // ===== LIMPEZA DE RECURSOS =====
    $stmt->close(); // Fecha statement para liberar recursos
}

/**
 * FUNÇÃO DELETE - REMOÇÃO DE CLIENTE
 * 
 * Remove permanentemente um cliente do sistema
 * Operação irreversível que requer confirmação do ID
 * 
 * URL esperada: /api/clientes.php?id=123
 * 
 * ⚠️  ATENÇÃO: Esta operação é IRREVERSÍVEL
 * ⚠️  Considere implementar soft-delete em produção
 * 
 * Validações:
 * - Verifica se ID foi fornecido
 * - Confirma se registro existia e foi removido
 * 
 * Resposta: JSON com status da operação
 */
function handleDelete($conn) {
    // ===== VALIDAÇÃO DO PARÂMETRO ID =====
    $id = $_GET['id'] ?? null;  // Captura ID da URL

    if (!$id) {
        // ID é obrigatório para identificar qual cliente remover
        echo json_encode(['message' => 'ID do cliente é obrigatório']);
        http_response_code(400); // Bad Request
        return; // Interrompe execução
    }

    // ===== PREPARAÇÃO DA QUERY DELETE =====
    // Prepared statement para remoção segura
    // WHERE id = ? garante que apenas o registro específico seja removido
    $stmt = $conn->prepare("DELETE FROM clientes WHERE id = ?");
    
    // Vincula parâmetro ID como integer
    $stmt->bind_param("i", $id);

    // ===== EXECUÇÃO E TRATAMENTO DE RESULTADO =====
    if ($stmt->execute()) {
        // Execução bem-sucedida, verifica se algo foi realmente removido
        
        if ($stmt->affected_rows > 0) {
            // ===== REMOÇÃO BEM-SUCEDIDA =====
            // affected_rows > 0 significa que um registro foi deletado
            echo json_encode(['message' => 'Cliente excluído com sucesso']);
            http_response_code(200); // OK
            
        } else {
            // ===== CLIENTE NÃO ENCONTRADO =====
            // affected_rows = 0 significa que nenhum registro tinha esse ID
            echo json_encode(['message' => 'Cliente não encontrado']);
            http_response_code(404); // Not Found
        }
        
    } else {
        // ===== ERRO NA EXECUÇÃO =====
        // Erro SQL durante a remoção (ex: constraint violation)
        echo json_encode([
            'message' => 'Erro ao excluir cliente', 
            'error' => $stmt->error  // Detalhes do erro MySQL
        ]);
        http_response_code(500); // Internal Server Error
    }
    
    // ===== LIMPEZA DE RECURSOS =====
    $stmt->close(); // Fecha statement para liberar recursos
}

// ===== FINALIZAÇÃO =====
// Fecha conexão com banco de dados após processamento
$conn->close();

?>