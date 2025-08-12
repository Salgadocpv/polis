/**
 * SERVICE WORKER PARA PWA POLIS ENGENHARIA
 * 
 * Service Worker configurado para desenvolvimento sem cache ativo
 * Permite que o sistema funcione como PWA (Progressive Web App)
 * mas não intercepta requests para facilitar desenvolvimento
 * 
 * Funcionalidades:
 * - Registra o app como PWA instalável
 * - Limpa caches existentes para evitar problemas
 * - Não intercepta requests (passa direto para rede)
 * - Ativação imediata para atualizações
 * 
 * Em produção, este service worker pode ser modificado para
 * implementar estratégias de cache mais sofisticadas
 */

// ===== LOG DE INICIALIZAÇÃO =====
console.log('🔧 Service Worker Polis carregado - Cache DESABILITADO (modo desenvolvimento)');

/**
 * EVENTO: INSTALL
 * 
 * Disparado quando service worker é instalado pela primeira vez
 * ou quando uma nova versão é detectada
 * 
 * Ações realizadas:
 * - Remove todos os caches existentes para evitar conflitos
 * - Pula fase de waiting para ativar imediatamente
 */
self.addEventListener('install', event => {
  console.log('📦 Service Worker instalando...');
  
  // waitUntil garante que operações assíncronas sejam concluídas
  // antes do service worker ser considerado "instalado"
  event.waitUntil(
    // ===== LIMPEZA DE CACHES EXISTENTES =====
    caches.keys().then(cacheNames => {
      console.log('🧹 Removendo todos os caches existentes:', cacheNames);
      
      // Remove cada cache individualmente
      return Promise.all(
        cacheNames.map(cacheName => {
          console.log(`   └─ Removendo cache: ${cacheName}`);
          return caches.delete(cacheName);
        })
      );
    }).then(() => {
      console.log('✅ Todos os caches removidos com sucesso');
      
      // ===== PULAR WAITING =====
      // skipWaiting() força ativação imediata sem esperar
      // que todas as abas sejam fechadas
      return self.skipWaiting();
    })
  );
});

/**
 * EVENTO: ACTIVATE
 * 
 * Disparado quando service worker se torna ativo
 * Momento ideal para limpezas e assumir controle das páginas
 * 
 * Ações realizadas:
 * - Limpeza final de caches residuais
 * - Assume controle de todas as páginas abertas
 */
self.addEventListener('activate', event => {
  console.log('🚀 Service Worker ativando...');
  
  event.waitUntil(
    // ===== LIMPEZA FINAL DE CACHES =====
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          console.log('🗑️  Removendo cache residual:', cacheName);
          return caches.delete(cacheName);
        })
      );
    }).then(() => {
      console.log('✅ Service Worker ativo - MODO SEM CACHE');
      
      // ===== ASSUMIR CONTROLE =====
      // claim() faz este SW assumir controle de páginas abertas
      // sem precisar recarregar as páginas
      return self.clients.claim();
    })
  );
});

/**
 * EVENTO: FETCH
 * 
 * Intercepta todas as requisições HTTP da aplicação
 * 
 * MODO DESENVOLVIMENTO: Não intercepta requisições
 * - Todas as requests passam direto para a rede
 * - Não implementa cache para facilitar desenvolvimento
 * - Assets sempre atualizados
 * 
 * Para produção, pode ser modificado para implementar:
 * - Cache de assets estáticos (CSS, JS, imagens)
 * - Cache de APIs com invalidação inteligente  
 * - Estratégias offline-first ou network-first
 */
self.addEventListener('fetch', event => {
  // ===== MODO PASS-THROUGH =====
  // Não intercepta requisições - deixa passar direto para rede
  // Comentário explicativo para desenvolvimento:
  
  console.log(`📡 Request pass-through: ${event.request.method} ${event.request.url}`);
  
  // Simplesmente retorna sem interceptar
  // A requisição segue normalmente para o servidor
  return;
  
  // ===== EXEMPLO DE CACHE PARA PRODUÇÃO (COMENTADO) =====
  /*
  event.respondWith(
    caches.match(event.request).then(response => {
      // Se encontrar no cache, retorna
      if (response) {
        return response;
      }
      
      // Senão, busca na rede e cacheia
      return fetch(event.request).then(response => {
        const responseClone = response.clone();
        caches.open('polis-v1').then(cache => {
          cache.put(event.request, responseClone);
        });
        return response;
      });
    })
  );
  */
});

/**
 * EVENTO: MESSAGE
 * 
 * Permite comunicação entre a aplicação e o service worker
 * Útil para comandos como limpeza de cache ou atualizações
 */
self.addEventListener('message', event => {
  console.log('💬 Mensagem recebida do cliente:', event.data);
  
  if (event.data && event.data.type === 'CLEAR_CACHE') {
    // Comando para limpar caches
    console.log('🧹 Limpando caches por solicitação');
    
    event.waitUntil(
      caches.keys().then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => caches.delete(cacheName))
        );
      }).then(() => {
        console.log('✅ Caches limpos por comando');
        // Responde de volta para o cliente
        event.ports[0].postMessage({ success: true });
      })
    );
  }
});

// Limpeza de cache quando solicitada
self.addEventListener('message', event => {
  if (event.data && event.data.type === 'CLEAR_ALL_CACHE') {
    event.waitUntil(
      caches.keys().then(cacheNames => {
        console.log('Limpando todos os caches por solicitação');
        return Promise.all(
          cacheNames.map(cacheName => caches.delete(cacheName))
        );
      })
    );
  }
  
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

console.log('Service Worker configurado: CACHE COMPLETAMENTE DESABILITADO');