// Ruta del archivo: public/sw.js

// 1. NOMBRES DE CACHÉ
const STATIC_CACHE_NAME = 'static-cache-v1';
const DYNAMIC_CACHE_NAME = 'dynamic-cache-v1';

// 2. APP SHELL (Archivos estáticos para cachear en la instalación)
// IMPORTANTE: Ajusta las rutas de 'app.css' y 'app.js' si cambian con Vite
const STATIC_FILES = [
    '/',
    '/manifest.json',
    '/logo.PNG', // El logo que ya configuramos
    '/build/assets/app.css', // ¡Revisa esta ruta después de un 'npm run build'!
    '/build/assets/app.js',  // ¡Revisa esta ruta después de un 'npm run build'!
    'https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap' // Fuente
];

// 3. EVENTO "INSTALL" (Se dispara cuando el SW se instala)
self.addEventListener('install', event => {
    console.log('[SW] Instalando Service Worker...');
    event.waitUntil(
        caches.open(STATIC_CACHE_NAME).then(cache => {
            console.log('[SW] Pre-cargando App Shell en caché estática');
            return cache.addAll(STATIC_FILES).catch(error => {
                console.error('[SW] Fallo al cachear App Shell:', error);
            });
        })
    );
});

// 4. EVENTO "ACTIVATE" (Se dispara cuando el SW se activa, limpia cachés antiguas)
self.addEventListener('activate', event => {
    console.log('[SW] Activando Service Worker...');
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(keys.map(key => {
                if (key !== STATIC_CACHE_NAME && key !== DYNAMIC_CACHE_NAME) {
                    console.log('[SW] Eliminando caché antigua:', key);
                    return caches.delete(key);
                }
            }));
        })
    );
    return self.clients.claim();
});


self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') {
        return; // Deja que el navegador lo maneje
    }
    if (!event.request.url.startsWith('http')) {
        return;
    }
    const isStaticAsset = event.request.url.match(/\.(css|js|png|jpg|jpeg|svg|gif|woff|woff2|eot|ttf)$/);
    const isFont = event.request.url.startsWith('https://fonts.bunny.net'); // Cachear las fuentes

    if (isStaticAsset || isFont) {
        // Estrategia "Cache First" para assets
        event.respondWith(
            caches.match(event.request).then(response => {
                // Si está en caché, lo devuelve
                if (response) {
                    return response;
                }
                
                // Si no, va a la red, lo descarga y lo guarda en la caché dinámica
                return fetch(event.request).then(res => {
                    return caches.open(DYNAMIC_CACHE_NAME).then(cache => {
                        cache.put(event.request.url, res.clone());
                        return res;
                    });
                });
            })
        );
        return; // Importante: Salir de la función aquí
    }
    return fetch(event.request);

}); 


try {
    importScripts("https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js");
    importScripts("https://www.gstatic.com/firebasejs/9.6.1/firebase-messaging-compat.js");
} catch (e) {
    console.error("[SW] Error importando scripts de Firebase:", e);
}


const firebaseConfig = {
    apiKey: "AIzaSyBtwIUXXP4M0fDsghgau9WC1nr12SGtpOw",
    authDomain: "recolector-clientes.firebaseapp.com",
    projectId: "recolector-clientes",
    storageBucket: "recolector-clientes.firebasestorage.app",
    messagingSenderId: "401387741103",
    appId: "1:401387741103:web:46274311fa48a91470195a"
};

// Inicializar Firebase en el Service Worker
if (typeof firebase !== 'undefined') {
    firebase.initializeApp(firebaseConfig);

    // Obtener la instancia de Messaging
    const messaging = firebase.messaging();

    // Manejador para notificaciones en SEGUNDO PLANO (app cerrada o en otra pestaña)
   // Manejador para notificaciones en SEGUNDO PLANO (app cerrada o en otra pestaña)
messaging.onBackgroundMessage(function(payload) {
    
    console.log('¡[SW] MENSAJE EN SEGUNDO PLANO RECIBIDO!');
    console.log('Payload (Segundo Plano):', payload);

    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: payload.notification.icon || '/logo.PNG'
    };

    return self.registration.showNotification(notificationTitle, notificationOptions);
});
}