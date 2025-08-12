<?php
/**
 * ARQUIVO DE EXEMPLO - CONFIGURAÇÃO DE CONEXÃO PARA PRODUÇÃO
 * 
 * 1. Copie este arquivo para api/conexao.php no servidor de produção
 * 2. Atualize as credenciais do banco de dados da Hostinger
 * 3. Este arquivo não deve ser commitado por segurança
 */

// ===== CONFIGURAÇÕES DO BANCO DE DADOS =====
// IMPORTANTE: Substitua pelas credenciais da sua hospedagem Hostinger
define('DB_HOST', 'srv1310.hstgr.io');        // Host do banco Hostinger
define('DB_USER', 'u461266905_polis');        // Usuário do banco
define('DB_PASS', 'SUA_SENHA_AQUI');          // Senha do banco (substituir)
define('DB_NAME', 'u461266905_polis');        // Nome do banco

// ===== CONEXÃO MySQLi (COMPATIBILIDADE) =====
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    // Em produção, log o erro em vez de mostrar na tela
    error_log("Erro de Conexão MySQLi: " . $conn->connect_error);
    die("Erro de conexão com o banco de dados. Contate o administrador.");
}

$conn->set_charset("utf8mb4");

// ===== CONEXÃO PDO (MODERNA) =====
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log("Erro de conexão PDO: " . $e->getMessage());
    $pdo = null;
}

?>