<?php
session_start();
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['nivel_acesso']) !== 'usuário') {
    // Se não estiver logado ou não for um usuário, redireciona para o login
    header('Location: index.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard do Usuário - Sistema de Engenharia</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: relative;
        }
        .main-content {
            padding-top: 90px; /* Espaço para o header */
            flex-grow: 1;
            padding-bottom: 2rem;
        }
        .welcome-message {
            text-align: center;
            padding: 2rem;
            color: var(--cor-texto-escuro);
        }
        .welcome-message h1 {
            color: var(--cor-principal);
            font-size: 2.5rem;
        }
        .welcome-message p {
            font-size: 1.2rem;
            color: var(--cor-secundaria);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content" id="mainContent">
        <div class="container">
            <div class="welcome-message">
                <h1>Bem-vindo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
                <p>Este é o seu painel de controle. Aqui você poderá ver suas tarefas e projetos.</p>
                <!-- Conteúdo específico para o usuário pode ser adicionado aqui -->
            </div>
        </div>
    </main>

</body>
</html>
