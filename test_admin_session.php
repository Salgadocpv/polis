<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['usuario'] = 'administrador';
$_SESSION['nivel_acesso'] = 'administrador';

echo "Sessão de administrador criada. Agora você pode acessar /polis/setup.php";
echo "<br><a href='setup.php'>Ir para Configurações</a>";
?>