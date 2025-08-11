<?php
session_start();
require_once '../includes/Security.php';

Security::setSecurityHeaders();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $token = Security::generateCSRFToken();
    echo json_encode(['csrf_token' => $token]);
    http_response_code(200);
} else {
    echo json_encode(['message' => 'Método não permitido']);
    http_response_code(405);
}
?>