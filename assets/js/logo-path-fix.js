/**
 * CORREÇÃO AUTOMÁTICA DE PATHS DO LOGOTIPO
 * 
 * Este script detecta automaticamente se estamos em ambiente local ou produção
 * e ajusta os caminhos das imagens do logotipo para funcionar corretamente
 */

document.addEventListener('DOMContentLoaded', function() {
    // Detecta se estamos em ambiente local ou produção
    const isLocal = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    const currentPath = window.location.pathname;
    
    // Determina o prefixo correto baseado no ambiente e localização
    let basePath = '';
    
    console.log('🔍 Logo Debug - currentPath:', currentPath);
    
    if (isLocal) {
        // Ambiente local
        if (currentPath.includes('/Polis/')) {
            const afterPolis = currentPath.split('/Polis/')[1] || '';
            const subPaths = afterPolis.split('/').filter(segment => segment.length > 0);
            
            if (subPaths.length > 0 && subPaths[subPaths.length - 1].includes('.php')) {
                subPaths.pop();
            }
            
            basePath = subPaths.length > 0 ? '../'.repeat(subPaths.length) : './';
        } else if (currentPath.includes('/polis/')) {
            const afterPolis = currentPath.split('/polis/')[1] || '';
            const subPaths = afterPolis.split('/').filter(segment => segment.length > 0);
            
            if (subPaths.length > 0 && subPaths[subPaths.length - 1].includes('.php')) {
                subPaths.pop();
            }
            
            basePath = subPaths.length > 0 ? '../'.repeat(subPaths.length) : './';
        } else {
            basePath = './';
        }
    } else {
        // Ambiente de produção
        if (currentPath.includes('/polis/')) {
            const afterPolis = currentPath.split('/polis/')[1] || '';
            const subPaths = afterPolis.split('/').filter(segment => segment.length > 0);
            
            if (subPaths.length > 0 && subPaths[subPaths.length - 1].includes('.php')) {
                subPaths.pop();
            }
            
            basePath = subPaths.length > 0 ? '../'.repeat(subPaths.length) : './';
            console.log('🔍 Logo Debug - afterPolis:', afterPolis, 'subPaths:', subPaths, 'depth:', subPaths.length);
        } else {
            basePath = '/polis/';
        }
    }
    
    console.log(`📁 Logo Detectado - Path: ${currentPath}, BasePath: ${basePath}`);
    
    // Seleciona todas as imagens de logotipo
    const logoImages = document.querySelectorAll('.logo_polis, img[alt*="Logotipo"], img[alt*="logo"], img[src*="logo-polis"]');
    
    logoImages.forEach(img => {
        const currentSrc = img.getAttribute('src');
        
        // Se o src é relativo (não começa com http, https, / ou .)
        if (currentSrc && !currentSrc.match(/^(https?:\/\/|\/|\.)/)) {
            // Adiciona o basePath
            img.src = basePath + currentSrc;
        } else if (currentSrc && currentSrc.startsWith('assets/')) {
            // Se começa com assets/, adiciona o basePath
            img.src = basePath + currentSrc;
        } else if (currentSrc && currentSrc.includes('logo-polis-branco-194w.png')) {
            // Força o path correto para o logotipo específico
            img.src = basePath + 'assets/images/logo-polis-branco-194w.png';
        }
        
        // Adiciona tratamento de erro para imagens
        img.onerror = function() {
            console.warn('Logo não encontrado em:', this.src);
            
            // Tenta paths alternativos baseado na estrutura
            const alternatives = [
                basePath + 'assets/images/logo-polis-branco-194w.png',
                './assets/images/logo-polis-branco-194w.png',
                '../assets/images/logo-polis-branco-194w.png',
                '../../assets/images/logo-polis-branco-194w.png',
                '/polis/assets/images/logo-polis-branco-194w.png',
                '/Polis/assets/images/logo-polis-branco-194w.png',
                'assets/images/logo-polis-branco-194w.png'
            ];
            
            const currentIndex = alternatives.indexOf(this.src);
            const nextIndex = currentIndex + 1;
            
            if (nextIndex < alternatives.length) {
                console.log('Tentando path alternativo:', alternatives[nextIndex]);
                this.src = alternatives[nextIndex];
            } else {
                // Se todos os paths falharam, mostra placeholder texto
                this.style.display = 'none';
                const placeholder = document.createElement('div');
                placeholder.innerHTML = 'POLIS<br>ENGENHARIA';
                placeholder.style.cssText = `
                    color: white;
                    text-align: center;
                    font-weight: bold;
                    font-size: 12px;
                    line-height: 1.2;
                    padding: 10px;
                    background: rgba(255,255,255,0.1);
                    border-radius: 4px;
                    width: 170px;
                    height: 50px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                `;
                this.parentNode.appendChild(placeholder);
                console.warn('Logotipo substituído por placeholder texto');
            }
        };
        
        // Adiciona log para debug
        console.log('Logo path corrigido:', img.src);
    });
});

// Função para recarregar logos se necessário
window.reloadLogos = function() {
    const event = new Event('DOMContentLoaded');
    document.dispatchEvent(event);
};