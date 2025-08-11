<?php
require_once 'api/conexao.php';

echo "Testando busca de projetos:\n\n";

try {
    $result = $conn->query("SELECT p.*, c.nome as cliente_nome FROM projetos p LEFT JOIN clientes c ON p.cliente_id = c.id");
    
    echo "Query executada com sucesso!\n";
    echo "Número de linhas: " . $result->num_rows . "\n\n";
    
    while ($row = $result->fetch_assoc()) {
        echo "Projeto ID: " . $row['id'] . "\n";
        echo "Nome: " . $row['nome'] . "\n";
        echo "Cliente: " . ($row['cliente_nome'] ?? 'Cliente não encontrado') . "\n";
        echo "Responsável: " . $row['responsavel'] . "\n";
        echo "Status: " . $row['status'] . "\n";
        echo "---\n";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}

$conn->close();
?>