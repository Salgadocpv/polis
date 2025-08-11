<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'polis_db');

// Conexão MySQLi (mantida para compatibilidade)
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Erro de Conexão: " . $conn->connect_error);
}

// Definir o charset para UTF-8
$conn->set_charset("utf8mb4");

// Conexão PDO (para APIs novas)
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log("Erro de conexão PDO: " . $e->getMessage());
    // Não usar die() aqui para não quebrar APIs que esperam JSON
    $pdo = null;
}

?>