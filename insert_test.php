<?php
require_once 'api/conexao.php';

try {
    // Inserir projeto de teste
    $sql = "INSERT INTO projetos (nome, cliente_id, responsavel, status, data_inicio, data_conclusao_prevista, orcamento, descricao) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    $nome = "Projeto de Teste";
    $cliente_id = 1;
    $responsavel = "João Silva";
    $status = "Em Andamento";
    $data_inicio = "2025-01-01";
    $data_conclusao_prevista = "2025-06-01";
    $orcamento = 50000.00;
    $descricao = "Projeto de teste para verificar o carregamento de dados";
    
    $stmt->bind_param("sissssds", $nome, $cliente_id, $responsavel, $status, $data_inicio, $data_conclusao_prevista, $orcamento, $descricao);
    
    if ($stmt->execute()) {
        echo "Projeto inserido com ID: " . $stmt->insert_id . "\n";
        
        // Testar busca
        $id = $stmt->insert_id;
        $stmt2 = $conn->prepare("SELECT * FROM projetos WHERE id = ?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $result = $stmt2->get_result();
        $projeto = $result->fetch_assoc();
        
        echo "Projeto encontrado:\n";
        print_r($projeto);
        
        $stmt2->close();
    } else {
        echo "Erro ao inserir: " . $stmt->error . "\n";
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}

$conn->close();
?>