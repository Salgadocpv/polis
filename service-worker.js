// Service Worker sem cache - apenas para PWA básico
console.log('Service Worker carregado - Cache DESABILITADO');

// Instalação - limpar todos os caches existentes
self.addEventListener('install', event => {
  console.log('Service Worker instalando...');
  
  event.waitUntil(
    // Limpar todos os caches existentes
    caches.keys().then(cacheNames => {
      console.log('Removendo todos os caches:', cacheNames);
      return Promise.all(
        cacheNames.map(cacheName => {
          return caches.delete(cacheName);
        })
      );
    }).then(() => {
      console.log('Todos os caches removidos');
      // Pular waiting e ativar imediatamente
      return self.skipWaiting();
    })
  );
});

// Ativação - assumir controle imediato
self.addEventListener('activate', event => {
  console.log('Service Worker ativando...');
  
  event.waitUntil(
    // Limpar qualquer cache restante
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          console.log('Removendo cache:', cacheName);
          return caches.delete(cacheName);
        })
      );
    }).then(() => {
      console.log('Service Worker ativo - SEM CACHE');
      return self.clients.claim();
    })
  );
});

// Fetch - sempre buscar da rede, nunca usar cache
self.addEventListener('fetch', event => {
  // Simplesmente deixa a requisição passar direto para a rede
  // Não intercepta nem cacheia nada
  return;
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