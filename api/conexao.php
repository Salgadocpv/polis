<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'polis_db');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Erro de Conexão: " . $conn->connect_error);
}

// Definir o charset para UTF-8
$conn->set_charset("utf8mb4");

?>