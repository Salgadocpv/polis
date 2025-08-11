<?php
// Página de teste sem verificação de sessão
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste - Lista de Clientes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .client-item { padding: 10px; border: 1px solid #ddd; margin: 10px 0; }
        .error { color: red; padding: 20px; }
        .success { color: green; padding: 20px; }
    </style>
</head>
<body>
    <h1>Teste - Lista de Clientes</h1>
    <div id="result">Carregando...</div>

    <script>
        async function testClientes() {
            try {
                console.log('Fazendo requisição...');
                const response = await fetch('api/clientes.php');
                console.log('Response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                console.log('Dados recebidos:', data);
                
                const resultDiv = document.getElementById('result');
                if (data.length === 0) {
                    resultDiv.innerHTML = '<div class="error">Nenhum cliente encontrado</div>';
                } else {
                    resultDiv.innerHTML = '<div class="success">Clientes carregados com sucesso!</div>';
                    data.forEach(cliente => {
                        resultDiv.innerHTML += `
                            <div class="client-item">
                                <strong>${cliente.nome}</strong><br>
                                CPF/CNPJ: ${cliente.cpf_cnpj}<br>
                                Email: ${cliente.email}<br>
                                Telefone: ${cliente.telefone}<br>
                                Cidade: ${cliente.cidade}
                            </div>
                        `;
                    });
                }
            } catch (error) {
                console.error('Erro:', error);
                document.getElementById('result').innerHTML = `<div class="error">Erro: ${error.message}</div>`;
            }
        }
        
        testClientes();
    </script>
</body>
</html>