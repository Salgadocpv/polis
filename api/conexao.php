<?php
/**
 * ARQUIVO DE CONFIGURAÇÃO E CONEXÃO COM BANCO DE DADOS
 * 
 * Este arquivo centraliza todas as configurações de conexão com o banco MySQL
 * e fornece duas instâncias de conexão: MySQLi (legado) e PDO (moderno)
 * 
 * Responsabilidades:
 * - Definir constantes de conexão do banco
 * - Criar conexão MySQLi para compatibilidade com código legado
 * - Criar conexão PDO para novas implementações
 * - Configurar charset UTF-8 para suporte adequado ao português
 * - Tratar erros de conexão de forma adequada
 */

// ===== CONFIGURAÇÕES DO BANCO DE DADOS =====
// Define as credenciais de acesso ao MySQL local (XAMPP)
define('DB_HOST', 'srv1310.hstgr.io');    // Servidor MySQL (localhost para desenvolvimento)
define('DB_USER', 'u461266905_polis');         // Usuário padrão do XAMPP
define('DB_PASS', '4580951Ga@');             // Senha vazia (padrão XAMPP)
define('DB_NAME', 'u461266905_polis');     // Nome do banco de dados do sistema

// ===== CONEXÃO MySQLi (COMPATIBILIDADE) =====
// Cria instância MySQLi para manter compatibilidade com código existente
// MySQLi é usado principalmente nas páginas legadas e algumas funções específicas
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificação de erro na conexão MySQLi
// Se falhar, interrompe a execução (adequado para páginas que dependem do banco)
if ($conn->connect_error) {
    die("Erro de Conexão MySQLi: " . $conn->connect_error);
}

// Configura charset UTF-8 para suporte completo ao português
// utf8mb4 suporta todos os caracteres Unicode, incluindo emojis
$conn->set_charset("utf8mb4");

// ===== CONEXÃO PDO (MODERNA) =====
// PDO é usado para novas APIs e implementações modernas
// Oferece melhor segurança, prepared statements mais robustos e portabilidade
try {
    // Constrói DSN (Data Source Name) para conexão PDO
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    // Cria instância PDO com opções de segurança configuradas
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        // Configurações de segurança e comportamento do PDO:
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // Lança exceções em caso de erro
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // Retorna arrays associativos por padrão
        PDO::ATTR_EMULATE_PREPARES => false,                // Usa prepared statements nativos (mais seguro)
    ]);
} catch (PDOException $e) {
    // Em caso de erro na conexão PDO, registra no log do sistema
    error_log("Erro de conexão PDO: " . $e->getMessage());
    
    // Define $pdo como null em vez de usar die()
    // Isso permite que APIs continuem funcionando e retornem erro JSON adequado
    $pdo = null;
}

?>