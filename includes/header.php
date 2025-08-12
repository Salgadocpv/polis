<?php
// Este arquivo contém o HTML da barra superior para ser incluído em todas as páginas.

// Obter a data e hora atual no formato desejado
date_default_timezone_set('America/Sao_Paulo'); // Define o fuso horário para o Brasil
$current_date = date('d/m/Y');
$current_time = date('H:i');

// Dados de exemplo para o usuário (em um sistema real, viriam do banco de dados/sessão)
$user_name = "João Silva";
$user_photo_url = "https://placehold.co/40x40/00B4D8/FFFFFF?text=JS"; // Placeholder para a foto do usuário
?>

<style>
    /* Estilos específicos para a Barra Superior */
    .top-header {
        position: fixed; /* Fixa a barra no topo */
        top: 0;
        left: 0;
        width: 100%; /* Ocupa toda a largura */
        height: 70px; /* Altura fixa da barra */
        background-color: var(--cor-secundaria); /* Cor de fundo da barra */
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2); /* Sombra para a barra */
        z-index: 990; /* Abaixo do menu lateral, mas acima do conteúdo principal */
        display: flex;
        justify-content: center; /* Centraliza o conteúdo horizontalmente */
        align-items: center; /* Centraliza o conteúdo verticalmente */
        padding: 0 2rem; /* Padding nas laterais para telas grandes */
        /* Novo padding-left para dar espaço ao menu-toggle */
        padding-left: 7rem; /* Aproximadamente 2rem (toggle pos) + 3.5rem (toggle width + gap) */
    }

    .header-content {
        width: 100%;
        max-width: 1200px; /* Faixa central para o conteúdo */
        display: flex;
        justify-content: space-between; /* Alinha itens nas extremidades */
        align-items: center;
        color: var(--cor-texto-claro);
    }

    .header-logo {
        height: 50px; /* Altura do logotipo */
        width: 150px; /* Largura do logotipo */
        /* Removido background-color e border-radius para um visual mais limpo */
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: bold;
        font-size: 0.9rem;
        text-transform: uppercase;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 15px; /* Espaçamento padrão entre os elementos do lado direito */
        font-size: 0.95rem;
    }

    .user-info span {
        white-space: nowrap; /* Evita quebra de linha para nome e data/hora */
    }

    /* Novo estilo para o espaçamento maior entre data/hora e nome do usuário */
    .user-info .date-time-display {
        margin-right: 25px; /* Espaço maior após a data/hora */
    }

    .user-photo {
        width: 40px;
        height: 40px;
        border-radius: 50%; /* Torna a foto redonda */
        background-color: var(--cor-vibrante); /* Cor de fundo para o placeholder da foto */
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: bold;
        font-size: 0.8rem;
        overflow: hidden; /* Garante que a imagem se ajuste ao círculo */
    }

    .user-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover; /* Garante que a imagem preencha o círculo */
    }

    /* Responsividade para telas menores */
    @media (max-width: 768px) {
        .top-header {
            padding: 0 1rem; /* Ajusta padding da barra superior em telas menores */
            padding-left: 5vw; /* Logo fica a 5% da borda esquerda */
        }
        .header-content {
            justify-content: flex-start; /* Alinha logo à esquerda */
        }
        .header-logo {
            width: 100px;
            height: 40px;
            font-size: 0.8rem;
            margin-right: auto; /* Empurra user-info para direita */
        }
        .user-info {
            flex-direction: row; /* Mantém em linha */
            align-items: center;
            gap: 10px; /* Reduz o espaçamento */
            font-size: 0.85rem; /* Fonte ligeiramente menor */
        }
        .user-info span {
            white-space: normal; /* Permite quebra de linha para texto longo */
            text-align: right; /* Alinha o texto à direita */
        }
        .user-info .date-time-display {
            margin-right: 0; /* Remove margin-right em telas menores */
        }
    }

    @media (max-width: 480px) { /* Para celulares muito pequenos */
        .top-header {
            padding-left: 5vw; /* Mantém logo a 5% da borda esquerda */
        }
        .header-logo {
            width: 80px; /* Logo ainda menor em telas pequenas */
            height: 35px;
        }
        .user-info {
            gap: 5px; /* Espaçamento ainda mais apertado */
            font-size: 0.75rem; /* Fonte ainda menor */
        }
        .user-photo {
            width: 30px; /* Foto menor */
            height: 30px;
        }
        /* Opcional: Esconder a data/hora ou o nome se o espaço for crítico */
        /* .user-info .date-time-display { display: none; } */
        /* .user-info span:nth-of-type(2) { display: none; } */
    }

    .logo_polis {
        width: 100px;
        filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.4)); /* Sombra mais orgânica para o logo */
        border-radius: 4px; /* Borda arredondada para a imagem do logo */
    }
</style>

<header class="top-header" id="topHeader">
    <div class="header-content">
        <!-- Logotipo da Empresa (canto esquerdo) -->
        <div class="header-logo">
            <img class="logo_polis" src="assets/images/logo-polis-branco-194w.png" alt="Logotipo da Empresa">
        </div>

        <!-- Informações do Usuário (canto direito) -->
        <div class="user-info">
            <!-- Data e Hora (primeiro à esquerda) -->
            <span class="date-time-display"><?php echo $current_date; ?> - <?php echo $current_time; ?></span>
            <!-- Nome do Usuário (depois da data/hora) -->
            <span><?php echo $user_name; ?> <span style="font-weight: bold; color: var(--cor-vibrante);"> (<?php echo $_SESSION['nivel_acesso']; ?>)</span></span>
            <!-- Foto do Usuário (último à direita) -->
            <div class="user-photo">
                <img src="<?php echo $user_photo_url; ?>" alt="Foto do Usuário" onerror="this.onerror=null;this.src='https://placehold.co/40x40/00B4D8/FFFFFF?text=JS';">
            </div>
        </div>
    </div>
</header>

<?php
// Lógica de Controle de Acesso para o nível "Visualizador"
if (isset($_SESSION['nivel_acesso']) && strtolower(trim($_SESSION['nivel_acesso'])) === 'visualizador') {
    echo <<<HTML
        <style>
        /* Oculta botões de ação primários e secundários */
        .btn-danger, .form-actions button, .actions a.delete {
            display: none !important;
        }
        /* Oculta botões de ação genéricos que possam ter onclick para modificação */
        button[onclick*="delete"], button[onclick*="edit"], button[onclick*="save"], button[onclick*="add"] {
            display: none !important;
        }
        /* Garante que links de edição/exclusão em tabelas sejam ocultados */
        .table-actions a[href*="registrar_"], .table-actions button {
             display: none !important;
        }
        /* Mostra apenas o link de visualização, se houver */
        .table-actions a[href*="visualizar_"] {
            display: inline-block !important;
        }

    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Função para desabilitar elementos interativos
            function disableElements() {
                // Desabilita apenas campos de formulário
                const formFields = document.querySelectorAll('input, textarea, select');
                formFields.forEach(element => {
                    element.disabled = true;
                });

                // Desabilita botões de ação específicos (salvar, deletar, etc.)
                const actionButtons = document.querySelectorAll('.form-actions button, .actions a.delete, button[onclick*="delete"], button[onclick*="edit"], button[onclick*="save"], button[onclick*="add"]');
                actionButtons.forEach(button => {
                    button.disabled = true;
                    button.style.pointerEvents = 'none';
                    button.style.opacity = '0.5';
                });
            }

            // Desabilita elementos no carregamento inicial
            disableElements();

            // Desabilita elementos após um pequeno atraso para pegar conteúdo dinâmico
            setTimeout(disableElements, 500); // 500ms de atraso
        });
        
        // CORREÇÃO DE LOGOTIPO INLINE NO HEADER
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            let basePath = '';
            
            if (currentPath.includes('/polis/')) {
                const afterPolis = currentPath.split('/polis/')[1] || '';
                const subPaths = afterPolis.split('/').filter(segment => segment.length > 0);
                
                if (subPaths.length > 0 && subPaths[subPaths.length - 1].includes('.php')) {
                    subPaths.pop();
                }
                
                basePath = subPaths.length > 0 ? '../'.repeat(subPaths.length) : './';
            } else if (currentPath.includes('/Polis/')) {
                const afterPolis = currentPath.split('/Polis/')[1] || '';
                const subPaths = afterPolis.split('/').filter(segment => segment.length > 0);
                
                if (subPaths.length > 0 && subPaths[subPaths.length - 1].includes('.php')) {
                    subPaths.pop();
                }
                
                basePath = subPaths.length > 0 ? '../'.repeat(subPaths.length) : './';
            } else {
                basePath = './';
            }
            
            // Corrige logo no header
            const headerLogo = document.querySelector('.header-logo .logo_polis');
            if (headerLogo) {
                const correctSrc = basePath + 'assets/images/logo-polis-branco-194w.png';
                headerLogo.src = correctSrc;
                console.log('🔧 Header logo corrigido:', correctSrc);
            }
        });
    </script>
HTML;
}
?>