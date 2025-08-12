<?php
// Este arquivo contém o HTML e o JavaScript do menu lateral.
// Ele deve ser incluído em todas as páginas onde o menu for necessário.
?>

<style>
    /* Estilos específicos para o Menu Lateral */
    .sidebar {
        position: fixed;
        top: 0;
        left: -280px; /* Escondido inicialmente, largura do menu */
        width: 280px; /* Largura do menu */
        height: 100%;
        background-color: var(--cor-secundaria);
        transition: left 0.4s cubic-bezier(0.25, 0.8, 0.25, 1); /* Transição suave e moderna */
        z-index: 1000; /* Garante que fique acima de outros elementos */
        display: flex;
        flex-direction: column;
        box-shadow: 4px 0 15px rgba(0, 0, 0, 0.3); /* Sombra para destacar o menu */
        border-right: 1px solid rgba(255, 255, 255, 0.05); /* Pequena borda para definição */
    }

    .sidebar.open {
        left: 0; /* Mostra o menu */
    }

    .sidebar-header {
        padding: 1.5rem 1rem; /* Ajustado para dar mais espaço */
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1); /* Linha divisória */
        display: flex;
        justify-content: center;
        align-items: center;
        height: 80px; /* Altura fixa para o cabeçalho do menu */
        position: relative; /* Necessário para posicionar o botão de fechar absolutamente */
    }

    .sidebar-logo-placeholder {
        width: 180px; /* Largura do espaço do logotipo */
        height: 50px;
        /* background-color: var(--cor-principal); Removido o background sólido */
        border-radius: 8px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: bold;
        color: var(--cor-texto-claro);
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .logo_polis {
        filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.4)); /* Sombra mais orgânica para o logo */
        border-radius: 4px; /* Borda arredondada para a imagem do logo */
        margin-right: 10px;
    }

    /* Botão de fechar dentro do menu */
    .sidebar-close-btn {
        position: absolute;
        top: 1.5rem; /* Ajuste a posição vertical */
        right: 1.5rem; /* Ajuste a posição horizontal */
        background: none;
        border: none;
        color: var(--cor-texto-claro);
        font-size: 1.5rem; /* Tamanho do ícone */
        cursor: pointer;
        transition: color 0.2s ease, transform 0.2s ease;
        z-index: 1002; /* Garante que fique acima de outros elementos do cabeçalho */
    }

    .sidebar-close-btn:hover {
        color: var(--cor-vibrante);
        transform: rotate(90deg); /* Efeito visual ao passar o mouse */
    }

    .sidebar-nav {
        flex-grow: 1; /* Ocupa o espaço restante */
        padding-top: 1rem;
    }

    .sidebar-nav ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar-nav li {
        margin-bottom: 8px; /* Espaçamento aumentado entre os itens */
    }

    .sidebar-nav li a {
        display: flex; /* Para alinhar ícone e texto */
        align-items: center;
        padding: 14px 1.5rem; /* Padding maior para melhor toque */
        color: var(--cor-texto-claro);
        text-decoration: none;
        transition: background-color 0.2s ease, color 0.2s ease, transform 0.1s ease;
        border-left: 4px solid transparent; /* Para o indicador de item ativo */
    }

    .sidebar-nav li a:hover {
        background-color: rgba(255, 255, 255, 0.15); /* Fundo sutil ao passar o mouse */
        color: var(--cor-clara); /* Cor do texto mais clara ao passar o mouse */
        transform: translateX(5px); /* Pequeno deslocamento ao passar o mouse */
    }

    .sidebar-nav li a.active {
        background-color: var(--cor-principal); /* Fundo mais escuro para o item ativo */
        color: var(--cor-clara); /* Texto mais claro para o item ativo */
        border-left-color: var(--cor-vibrante); /* Linha vibrante no lado esquerdo */
        font-weight: bold;
    }

    .sidebar-nav li a i {
        margin-right: 15px; /* Espaçamento maior entre ícone e texto */
        font-size: 1.3rem; /* Tamanho do ícone */
        width: 20px; /* Largura fixa para ícones para alinhamento */
        text-align: center;
    }

    /* Botão para alternar o menu (para abrir) */
    .menu-toggle {
        position: fixed;
        top: 0.7rem; /* Posição ajustada conforme sua correção */
        left: 2rem;
        z-index: 1001; /* Acima do menu para ser clicável */
        background: linear-gradient(45deg, var(--cor-vibrante), var(--cor-clara));
        color: var(--cor-texto-claro);
        padding: 12px 18px; /* Padding ajustado para o botão */
        border: none;
        border-radius: 8px;
        cursor: pointer;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        font-size: 1.2rem; /* Tamanho do ícone no botão */
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .menu-toggle:hover {
        transform: translateY(-2px); /* Mantém o deslocamento ao passar o mouse */
        box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
    }

    /* Overlay para escurecer o conteúdo quando o menu está aberto */
    .overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6); /* Fundo semi-transparente mais escuro */
        z-index: 999; /* Abaixo do menu, mas acima do conteúdo */
        opacity: 0;
        visibility: hidden; /* Escondido por padrão */
        transition: opacity 0.4s ease, visibility 0.4s ease;
    }

    .overlay.active {
        opacity: 1;
        visibility: visible;
    }

    /* Menu Radial Mobile - Esconde sidebar tradicional em mobile */
    @media (max-width: 768px) {
        .menu-toggle {
            display: none; /* Esconde o botão tradicional em mobile */
        }
        .sidebar {
            display: none; /* Esconde a sidebar tradicional em mobile */
        }
        .overlay {
            display: none; /* Esconde o overlay tradicional em mobile */
        }
    }

    /* Menu Radial Mobile */
    .mobile-menu-container {
        display: none; /* Escondido por padrão - só aparece em mobile */
        position: fixed;
        bottom: 20px; /* Mantém posição vertical */
        left: 50%; /* Centraliza horizontalmente */
        transform: translateX(-50%); /* Ajusta para centralização perfeita */
        z-index: 1000;
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    .mobile-menu-container.active {
        /* Quando ativo, move para posição que permite círculo completo */
        bottom: 170px; /* Move para cima para acomodar raio maior */
        left: 50%; /* Mantém centralizado horizontalmente */
        transform: translateX(-50%); /* Mantém centralização */
    }

    @media (max-width: 768px) {
        .mobile-menu-container {
            display: block;
        }
    }

    /* Botão principal do menu móvel */
    .mobile-menu-toggle {
        position: relative;
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: linear-gradient(45deg, var(--cor-vibrante), var(--cor-clara));
        border: none;
        cursor: pointer;
        box-shadow: 0 6px 20px rgba(0, 180, 216, 0.4);
        z-index: 1002;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    .mobile-menu-toggle:hover {
        transform: scale(1.1);
        box-shadow: 0 8px 25px rgba(0, 180, 216, 0.6);
    }

    .mobile-menu-toggle.active {
        transform: rotate(45deg);
        background: linear-gradient(45deg, #ff6b6b, #ffa726);
    }

    .mobile-menu-toggle i {
        color: white;
        font-size: 24px;
        transition: transform 0.3s ease;
    }


    /* Itens do menu radial */
    .mobile-menu-items {
        position: absolute;
        top: 35px; /* Centro do botão principal (70px/2) */
        left: 35px; /* Centro do botão principal (70px/2) */
        pointer-events: none;
    }

    .mobile-menu-item {
        position: absolute;
        width: 75px;
        height: 75px;
        border-radius: 50%;
        background: var(--cor-secundaria);
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        opacity: 0;
        transform: scale(0);
        pointer-events: none;
        text-decoration: none;
        color: white;
        top: -37.5px; /* Centraliza verticalmente (-75px/2) */
        left: -37.5px; /* Centraliza horizontalmente (-75px/2) */
    }

    .mobile-menu-item.show {
        opacity: 1;
        transform: scale(1);
        pointer-events: auto;
    }

    .mobile-menu-item:hover {
        transform: scale(1.1);
        background: var(--cor-vibrante);
    }

    .mobile-menu-item i {
        font-size: 22px;
        color: white;
        margin-bottom: 4px;
    }

    .mobile-menu-item span {
        font-size: 6px;
        color: white;
        text-align: center;
        line-height: 1;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.1px;
        max-width: 50px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Posições específicas para cada item do menu - 8 botões perfeitamente distribuídos (45° cada) - Raio 140px */
    .mobile-menu-item:nth-child(1) { /* Dashboard - 270° (topo) */
        top: calc(-37.5px - 140px); /* -37.5px (centro) - 140px (raio) */
        left: -37.5px; /* Centro horizontal */
        transition-delay: 0.1s;
    }

    .mobile-menu-item:nth-child(2) { /* Clientes - 315° */
        top: calc(-37.5px - 99px); /* -37.5px (centro) - 99px (sen 315° * 140) */
        left: calc(-37.5px + 99px); /* -37.5px (centro) + 99px (cos 315° * 140) */
        transition-delay: 0.15s;
    }

    .mobile-menu-item:nth-child(3) { /* Colaboradores - 0° (direita) */
        top: -37.5px; /* Centro vertical */
        left: calc(-37.5px + 140px); /* -37.5px (centro) + 140px (raio) */
        transition-delay: 0.2s;
    }

    .mobile-menu-item:nth-child(4) { /* Projetos - 45° */
        top: calc(-37.5px + 99px); /* -37.5px (centro) + 99px (sen 45° * 140) */
        left: calc(-37.5px + 99px); /* -37.5px (centro) + 99px (cos 45° * 140) */
        transition-delay: 0.25s;
    }

    .mobile-menu-item:nth-child(5) { /* Calendário - 90° (base) */
        top: calc(-37.5px + 140px); /* -37.5px (centro) + 140px (raio) */
        left: -37.5px; /* Centro horizontal */
        transition-delay: 0.3s;
    }

    .mobile-menu-item:nth-child(6) { /* Relatórios - 135° */
        top: calc(-37.5px + 99px); /* -37.5px (centro) + 99px (sen 135° * 140) */
        left: calc(-37.5px - 99px); /* -37.5px (centro) - 99px (cos 135° * 140) */
        transition-delay: 0.35s;
    }

    .mobile-menu-item:nth-child(7) { /* Configurações - 180° (esquerda) */
        top: -37.5px; /* Centro vertical */
        left: calc(-37.5px - 140px); /* -37.5px (centro) - 140px (raio) */
        transition-delay: 0.4s;
    }

    .mobile-menu-item:nth-child(8) { /* Sair - 225° */
        top: calc(-37.5px - 99px); /* -37.5px (centro) - 99px (sen 225° * 140) */
        left: calc(-37.5px - 99px); /* -37.5px (centro) - 99px (cos 225° * 140) */
        transition-delay: 0.45s;
    }

    /* Ajustes responsivos para telas muito pequenas */
    @media (max-width: 480px) {
        .mobile-menu-container.active {
            bottom: 180px; /* Mais espaço em telas pequenas para acomodar botões maiores */
            left: 50%; /* Mantém centralizado horizontalmente */
            transform: translateX(-50%); /* Mantém centralização */
        }
        
        /* Raio menor para telas pequenas - 115px de raio, 8 botões perfeitamente distribuídos (45° cada) */
        .mobile-menu-item:nth-child(1) { top: calc(-37.5px - 115px); left: -37.5px; } /* 270° */
        .mobile-menu-item:nth-child(2) { top: calc(-37.5px - 81px); left: calc(-37.5px + 81px); } /* 315° */
        .mobile-menu-item:nth-child(3) { top: -37.5px; left: calc(-37.5px + 115px); } /* 0° */
        .mobile-menu-item:nth-child(4) { top: calc(-37.5px + 81px); left: calc(-37.5px + 81px); } /* 45° */
        .mobile-menu-item:nth-child(5) { top: calc(-37.5px + 115px); left: -37.5px; } /* 90° */
        .mobile-menu-item:nth-child(6) { top: calc(-37.5px + 81px); left: calc(-37.5px - 81px); } /* 135° */
        .mobile-menu-item:nth-child(7) { top: -37.5px; left: calc(-37.5px - 115px); } /* 180° */
        .mobile-menu-item:nth-child(8) { top: calc(-37.5px - 81px); left: calc(-37.5px - 81px); } /* 225° */
    }

    /* Responsividade para telas maiores - mantém o menu tradicional */
    @media (min-width: 769px) {
        .menu-toggle {
            left: 1rem;
            top: 1rem;
        }
        .sidebar {
            width: 220px;
            left: -220px;
        }
        .mobile-menu-container {
            display: none !important;
        }
    }
</style>

<!-- Botão para alternar o menu lateral (para abrir) -->
<button class="menu-toggle" id="menuToggle">
    <i class="fas fa-bars"></i>
</button>

<!-- Menu Lateral Fixo -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo-placeholder">
            <img class="logo_polis" src="assets/images/logo-polis-branco-194w.png" alt="Logotipo da Empresa" style="width: 170px;">
        </div>
        <!-- Botão para fechar o menu, posicionado à direita -->
        <button class="sidebar-close-btn" id="closeSidebarBtn">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li><a href="/polis/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="/polis/listas/lista_clientes.php"><i class="fas fa-users"></i> Clientes</a></li>
            <li><a href="/polis/listas/lista_colaboradores.php"><i class="fas fa-user-tie"></i> Colaboradores</a></li>
            <li><a href="/polis/listas/lista_projetos.php"><i class="fas fa-project-diagram"></i> Projetos</a></li>
            <li><a href="/polis/calendario.php?view=today"><i class="fas fa-calendar-alt"></i> Calendário</a></li>
            <li><a href="/polis/setup.php"><i class="fas fa-cog"></i> Configurações</a></li>
            <li><a href="#"><i class="fas fa-chart-line"></i> Relatórios</a></li>
            <li><a href="/polis/api/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
        </ul>
    </nav>
</aside>

<!-- Overlay para quando o menu estiver aberto -->
<div class="overlay" id="overlay"></div>

<!-- Menu Mobile Radial -->
<div class="mobile-menu-container">
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>
    <div class="mobile-menu-items" id="mobileMenuItems">
        <a href="/polis/dashboard.php" class="mobile-menu-item">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        <a href="/polis/listas/lista_clientes.php" class="mobile-menu-item">
            <i class="fas fa-users"></i>
            <span>Clientes</span>
        </a>
        <a href="/polis/listas/lista_colaboradores.php" class="mobile-menu-item">
            <i class="fas fa-user-tie"></i>
            <span>Colaboradores</span>
        </a>
        <a href="/polis/listas/lista_projetos.php" class="mobile-menu-item">
            <i class="fas fa-project-diagram"></i>
            <span>Projetos</span>
        </a>
        <a href="/polis/calendario.php?view=today" class="mobile-menu-item">
            <i class="fas fa-calendar-alt"></i>
            <span>Calendário</span>
        </a>
        <a href="#" class="mobile-menu-item">
            <i class="fas fa-chart-line"></i>
            <span>Relatórios</span>
        </a>
        <a href="/polis/setup.php" class="mobile-menu-item">
            <i class="fas fa-cog"></i>
            <span>Configurações</span>
        </a>
        <a href="/polis/api/logout.php" class="mobile-menu-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Sair</span>
        </a>
    </div>
</div>

<script>
    // Este script controla o comportamento do menu lateral.
    // Ele é incluído junto com o HTML do menu.
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const closeSidebarBtn = document.getElementById('closeSidebarBtn');

    function openMenu() {
        sidebar.classList.add('open');
        overlay.classList.add('active');
        menuToggle.style.display = 'none'; // Esconde o botão de abrir o menu
    }

    function closeMenu() {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
        menuToggle.style.display = 'flex'; // Mostra o botão de abrir o menu novamente
    }

    // Event listener para abrir o menu (desktop)
    if (menuToggle) {
        menuToggle.addEventListener('click', openMenu);
    }

    // Event listener para fechar o menu ao clicar no botão 'X'
    if (closeSidebarBtn) {
        closeSidebarBtn.addEventListener('click', closeMenu);
    }

    // Event listener para fechar o menu ao clicar no overlay (fora do menu)
    if (overlay) {
        overlay.addEventListener('click', closeMenu);
    }

    // Fechar o menu ao clicar em um item de navegação (desktop)
    const navLinks = document.querySelectorAll('.sidebar-nav a');
    navLinks.forEach(link => {
        link.addEventListener('click', (event) => {
            // Remove 'active' de todos os links e adiciona ao clicado
            navLinks.forEach(l => l.classList.remove('active'));
            event.currentTarget.classList.add('active');
            closeMenu(); // Fecha o menu ao clicar em um item
        });
    });

    // Script para o Menu Mobile Radial
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mobileMenuContainer = document.querySelector('.mobile-menu-container');
    const mobileMenuItems = document.querySelectorAll('.mobile-menu-item');
    let mobileMenuOpen = false;

    function toggleMobileMenu() {
        mobileMenuOpen = !mobileMenuOpen;
        const icon = mobileMenuToggle.querySelector('i');
        
        if (mobileMenuOpen) {
            // Abrir menu - move container e mostra itens
            mobileMenuContainer.classList.add('active');
            mobileMenuToggle.classList.add('active');
            icon.className = 'fas fa-times'; // Muda para X quando aberto
            mobileMenuItems.forEach((item, index) => {
                setTimeout(() => {
                    item.classList.add('show');
                }, index * 20); // Delay escalonado mais rápido para animação
            });
        } else {
            // Fechar menu - esconde itens e move container de volta
            mobileMenuToggle.classList.remove('active');
            icon.className = 'fas fa-bars'; // Volta para menu quando fechado
            mobileMenuItems.forEach((item, index) => {
                setTimeout(() => {
                    item.classList.remove('show');
                }, (mobileMenuItems.length - index - 1) * 15); // Animação reversa mais rápida
            });
            
            // Remove classe active do container após animação dos itens
            setTimeout(() => {
                mobileMenuContainer.classList.remove('active');
            }, (mobileMenuItems.length * 15) + 50);
        }
    }

    // Event listener para o botão do menu mobile
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', toggleMobileMenu);
    }

    // Fechar menu mobile ao clicar em um item
    mobileMenuItems.forEach(item => {
        item.addEventListener('click', () => {
            if (mobileMenuOpen) {
                toggleMobileMenu();
            }
        });
    });

    // Fechar menu mobile ao tocar fora dele
    document.addEventListener('touchstart', (e) => {
        if (mobileMenuOpen && 
            !mobileMenuToggle.contains(e.target) && 
            !Array.from(mobileMenuItems).some(item => item.contains(e.target))) {
            toggleMobileMenu();
        }
    });

    // Fechar menu mobile ao redimensionar a tela
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768 && mobileMenuOpen) {
            toggleMobileMenu();
        }
    });
</script>

<!-- Correção inline de logotipo e prevenção de zoom -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // CORREÇÃO DE LOGOTIPO INLINE
    const currentPath = window.location.pathname;
    let basePath = '';
    
    // Detecta path correto baseado na URL
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
    
    console.log('🔍 Logo Fix - currentPath:', currentPath, 'basePath:', basePath);
    
    // Corrige todas as imagens de logotipo
    const logoImages = document.querySelectorAll('.logo_polis, img[alt*="Logotipo"], img[alt*="logo"], img[src*="logo-polis"]');
    
    logoImages.forEach(img => {
        const correctSrc = basePath + 'assets/images/logo-polis-branco-194w.png';
        img.src = correctSrc;
        
        img.onerror = function() {
            console.warn('Logo não encontrado em:', this.src);
            
            // Tenta paths alternativos
            const alternatives = [
                './assets/images/logo-polis-branco-194w.png',
                '../assets/images/logo-polis-branco-194w.png',
                '../../assets/images/logo-polis-branco-194w.png',
                '/polis/assets/images/logo-polis-branco-194w.png',
                'assets/images/logo-polis-branco-194w.png'
            ];
            
            let tryIndex = 0;
            const tryNext = () => {
                if (tryIndex < alternatives.length) {
                    this.src = alternatives[tryIndex];
                    tryIndex++;
                } else {
                    // Fallback final: placeholder texto
                    this.style.display = 'none';
                    const placeholder = document.createElement('div');
                    placeholder.innerHTML = 'POLIS<br>ENGENHARIA';
                    placeholder.style.cssText = `
                        color: white; text-align: center; font-weight: bold;
                        font-size: 12px; line-height: 1.2; padding: 10px;
                        background: rgba(255,255,255,0.1); border-radius: 4px;
                        width: 170px; height: 50px; display: flex;
                        align-items: center; justify-content: center;
                    `;
                    this.parentNode.appendChild(placeholder);
                }
            };
            
            this.onerror = tryNext;
            tryNext();
        };
        
        console.log('✅ Logo path corrigido:', correctSrc);
    });
    
    // PREVENÇÃO DE ZOOM MOBILE INLINE
    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
               (window.innerWidth <= 768);
    }
    
    if (isMobileDevice()) {
        console.log('📱 Mobile detectado - Aplicando prevenção de zoom');
        
        // Atualiza viewport
        const viewport = document.querySelector('meta[name="viewport"]') || document.createElement('meta');
        viewport.name = 'viewport';
        viewport.content = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover';
        if (!document.querySelector('meta[name="viewport"]')) {
            document.head.appendChild(viewport);
        }
        
        // Aplica font-size 16px a todos inputs
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.style.fontSize = '16px';
            input.classList.add('no-zoom');
        });
        
        // Observer para novos inputs
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) {
                        if (node.matches && node.matches('input, select, textarea')) {
                            node.style.fontSize = '16px';
                            node.classList.add('no-zoom');
                        }
                        const newInputs = node.querySelectorAll && node.querySelectorAll('input, select, textarea');
                        if (newInputs) {
                            newInputs.forEach(input => {
                                input.style.fontSize = '16px';
                                input.classList.add('no-zoom');
                            });
                        }
                    }
                });
            });
        });
        
        observer.observe(document.body, { childList: true, subtree: true });
        
        console.log('✅ Prevenção de zoom aplicada');
    }
});
</script>