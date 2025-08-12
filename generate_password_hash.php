<?php
// Arquivo para gerar o hash de uma senha para inserção no banco de dados

// Defina a senha que você deseja hashear aqui
$password_to_hash = 'a'; // *** MUDE ESTA SENHA PARA UMA SENHA FORTE E REAL ***

// Gera o hash da senha
$hashed_password = password_hash($password_to_hash, PASSWORD_DEFAULT);

 echo "<h2>Gerador de Hash de Senha</h2>";
 echo "<p><b>Senha Original:</b> " . htmlspecialchars($password_to_hash) . "</p>";
 echo "<p><b>Hash Gerado:</b> <code style=\"background-color: #eee; padding: 5px; border-radius: 3px;\">" . htmlspecialchars($hashed_password) . "</code></p>";
 echo "<p>Copie o <b>Hash Gerado</b> acima e use-o para inserir um novo usuário na tabela `usuarios` do seu banco de dados.</p>";
 echo "<p><b>Exemplo de SQL para inserir no banco de dados:</b></p>";
 echo "<pre style=\"background-color: #f0f0f0; padding: 10px; border-radius: 5px; overflow-x: auto;\"><code>INSERT INTO usuarios (username, email, password_hash, nivel_acesso) VALUES
('seu_usuario', 'seu_email@exemplo.com', '' . htmlspecialchars($hashed_password) . '', 'administrador');</code></pre>";

// Lembre-se de DELETAR este arquivo do seu servidor após gerar o hash e usá-lo!
?>