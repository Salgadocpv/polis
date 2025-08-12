/**
 * SCRIPT PARA PREVENIR ZOOM AUTOMÁTICO EM DISPOSITIVOS MÓVEIS
 * 
 * Este script detecta dispositivos móveis e aplica medidas para prevenir
 * o zoom automático que ocorre quando o usuário clica em inputs
 */

(function() {
    'use strict';
    
    // Detecta se é um dispositivo móvel
    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
               (window.innerWidth <= 768) ||
               ('ontouchstart' in window);
    }
    
    // Detecta se é iOS Safari especificamente
    function isIOSSafari() {
        return /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    }
    
    // Função para atualizar o meta viewport
    function updateViewportMeta() {
        const existingViewport = document.querySelector('meta[name="viewport"]');
        
        if (existingViewport) {
            // Atualiza viewport existente para prevenir zoom
            existingViewport.setAttribute('content', 
                'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover'
            );
        } else {
            // Cria nova meta tag viewport se não existir
            const viewport = document.createElement('meta');
            viewport.name = 'viewport';
            viewport.content = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover';
            document.head.appendChild(viewport);
        }
        
        console.log('📱 Viewport atualizado para prevenir zoom mobile');
    }
    
    // Função para aplicar estilos dinâmicos aos inputs
    function applyNoZoomStyles() {
        const style = document.createElement('style');
        style.id = 'no-zoom-dynamic-styles';
        
        style.textContent = `
            /* Estilos dinâmicos para prevenir zoom */
            @media screen and (max-width: 768px) {
                input, select, textarea {
                    font-size: 16px !important;
                    -webkit-text-size-adjust: 100% !important;
                    -moz-text-size-adjust: 100% !important;
                    text-size-adjust: 100% !important;
                }
                
                /* iOS Safari específico */
                input:focus, select:focus, textarea:focus {
                    -webkit-text-size-adjust: 100% !important;
                    zoom: 1 !important;
                }
            }
        `;
        
        // Remove estilo anterior se existir
        const existingStyle = document.getElementById('no-zoom-dynamic-styles');
        if (existingStyle) {
            existingStyle.remove();
        }
        
        document.head.appendChild(style);
        console.log('🎨 Estilos anti-zoom aplicados dinamicamente');
    }
    
    // Função para adicionar classe no-zoom aos inputs existentes
    function addNoZoomClass() {
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.classList.add('no-zoom');
        });
        
        console.log(`🔧 Classe no-zoom adicionada a ${inputs.length} elementos`);
    }
    
    // Função para observar novos inputs adicionados dinamicamente
    function observeNewInputs() {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        // Verifica se o próprio nó é um input
                        if (node.matches && node.matches('input, select, textarea')) {
                            node.classList.add('no-zoom');
                            node.style.fontSize = '16px';
                        }
                        
                        // Verifica inputs dentro do nó adicionado
                        const newInputs = node.querySelectorAll && node.querySelectorAll('input, select, textarea');
                        if (newInputs) {
                            newInputs.forEach(input => {
                                input.classList.add('no-zoom');
                                input.style.fontSize = '16px';
                            });
                        }
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        console.log('👁️ Observer ativo para novos inputs');
    }
    
    // Previne zoom em eventos específicos
    function preventZoomEvents() {
        // Previne zoom duplo no iOS
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function(event) {
            const now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
        
        // Adiciona listeners aos inputs para garantir que não zoomem
        document.addEventListener('focusin', function(event) {
            if (event.target.matches('input, select, textarea')) {
                event.target.style.fontSize = '16px';
                event.target.style.webkitTextSizeAdjust = '100%';
                event.target.style.textSizeAdjust = '100%';
            }
        });
        
        console.log('🛡️ Event listeners de prevenção de zoom ativados');
    }
    
    // Função principal
    function initNoZoom() {
        if (isMobileDevice()) {
            console.log('📱 Dispositivo móvel detectado - Ativando prevenção de zoom');
            
            // Aplica todas as medidas de prevenção
            updateViewportMeta();
            applyNoZoomStyles();
            addNoZoomClass();
            observeNewInputs();
            preventZoomEvents();
            
            // Adiciona classe ao body para identificação
            document.body.classList.add('mobile-no-zoom');
            
            // Re-aplica estilos após 500ms para pegar conteúdo dinâmico
            setTimeout(() => {
                addNoZoomClass();
                console.log('🔄 Re-aplicação de classes anti-zoom concluída');
            }, 500);
        } else {
            console.log('🖥️ Desktop detectado - Zoom normal mantido');
        }
    }
    
    // Inicializa quando DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNoZoom);
    } else {
        initNoZoom();
    }
    
    // Expõe função globalmente para uso manual se necessário
    window.initNoZoom = initNoZoom;
    
})();