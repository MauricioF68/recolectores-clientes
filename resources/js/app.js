// resources/js/app.js

import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// =================================================================
// LÓGICA DE GOOGLE MAPS
// initMap está disponible globalmente SIEMPRE.
// =================================================================
window.initMap = function() {
    console.log('Google Maps API cargada. Callback ejecutado.');

    // --- CÓDIGO DEL MAPA MOVIDO AQUÍ ---
    const mapElement = document.getElementById('map'); // Buscamos el div del mapa
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const addressInput = document.getElementById('address');
    const departmentInput = document.getElementById('department');
    const provinceInput = document.getElementById('province');
    const districtInput = document.getElementById('district');

    // Solo intentar inicializar si estamos en la página correcta (tiene el div 'map')
    if (mapElement && latInput && lngInput && addressInput) {

        const defaultPosition = { lat: -8.11599, lng: -79.02998 }; // Trujillo

        map = new google.maps.Map(mapElement, {
            center: defaultPosition,
            zoom: 15,
        });

        marker = new google.maps.Marker({
            position: defaultPosition,
            map: map,
            draggable: true
        });

        geocoder = new google.maps.Geocoder();

        // Intentar obtener ubicación actual
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const userPosition = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };
                    marker.setPosition(userPosition);
                    map.setCenter(userPosition);
                    geocodePosition(userPosition); // Actualizar inputs
                },
                () => { // Error al obtener ubicación, usar default
                    geocodePosition(defaultPosition);
                }
            );
        } else { // Navegador no soporta geolocalización
            geocodePosition(defaultPosition);
        }

        // Listener para cuando se arrastra el marcador
        marker.addListener('dragend', () => {
            geocodePosition(marker.getPosition());
        });

        console.log('Mapa inicializado.');

    } else {
        console.log('No se encontró el elemento #map en esta página. No se inicializa el mapa.');
    }
    // --- FIN DEL CÓDIGO DEL MAPA ---
}

// Función auxiliar para geocodificar (la dejamos fuera de initMap para claridad)
function geocodePosition(pos) {
    // ... (el código de geocodePosition que ya tenías) ...
    if (!geocoder || !document.getElementById('latitude')) return; 

    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const addressInput = document.getElementById('address');
    const departmentInput = document.getElementById('department');
    const provinceInput = document.getElementById('province');
    const districtInput = document.getElementById('district');

    geocoder.geocode({ location: pos }, (results, status) => {
        if (status === "OK") {
            if (results[0]) {
                const latitude = typeof pos.lat === 'function' ? pos.lat() : pos.lat;
                const longitude = typeof pos.lng === 'function' ? pos.lng() : pos.lng;

                latInput.value = latitude;
                lngInput.value = longitude;
                addressInput.value = results[0].formatted_address;

                departmentInput.value = '';
                provinceInput.value = '';
                districtInput.value = '';

                for (const component of results[0].address_components) {
                    const componentType = component.types[0];
                    switch (componentType) {
                        case "administrative_area_level_1": departmentInput.value = component.long_name; break;
                        case "administrative_area_level_2": provinceInput.value = component.long_name; break;
                        case "locality": districtInput.value = component.long_name; break;
                    }
                }
            } else { console.warn("No se encontraron resultados de dirección."); }
        } else { console.error("El geocodificador falló: " + status); }
    });
}

// --- IMPORTS DE FIREBASE ---
import { initializeApp } from "firebase/app";
import { getMessaging, getToken, onMessage } from "firebase/messaging";

// --- Variables globales para Firebase (inicializadas dentro de initFCM) ---
let firebaseApp;
let messaging;

// --- Función principal que inicia la lógica de notificaciones ---
function initFCM() {
    console.log('Iniciando lógica de Firebase Cloud Messaging...');

    // --- CONFIGURACIÓN DE FIREBASE (desde .env) ---
    // La leemos solo cuando es necesario
    const firebaseConfig = {
        apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
        authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
        projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
        storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
        messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
        appId: import.meta.env.VITE_FIREBASE_APP_ID
    };

    // --- INICIALIZACIÓN DE FIREBASE ---
    // Inicializamos solo si no lo hemos hecho antes
    if (!firebaseApp) {
        try {
            firebaseApp = initializeApp(firebaseConfig);
            messaging = getMessaging(firebaseApp);
            console.log('Firebase inicializado correctamente.');
            // Configuramos el manejador de mensajes en primer plano UNA SOLA VEZ
            setupOnMessageListener();
        } catch (error) {
            console.error("Error inicializando Firebase:", error);
            // Si Firebase falla, las notificaciones no funcionarán.
            return; // Salimos de la función initFCM
        }
    }

    // --- LÓGICA DE REGISTRO DEL SERVICE WORKER Y PETICIÓN DE TOKEN ---
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('Service Worker registrado con éxito:', registration);
                requestNotificationPermission(registration);
            })
            .catch(error => {
                console.error('Error al registrar el Service Worker:', error);
            });
    } else {
        console.warn("Service Workers no soportados en este navegador.");
    }
}
window.initFCM = initFCM; // Exponer la función globalmente


function requestNotificationPermission(registration) {
    if (!messaging) {
        console.warn("Firebase Messaging no está inicializado. No se puede pedir permiso.");
        return;
    }
    console.log('Pidiendo permiso para notificaciones...');
    Notification.requestPermission().then((permission) => {
        if (permission === 'granted') {
            console.log('Permiso de notificación concedido.');
            getToken(messaging, {
                vapidKey: import.meta.env.VITE_FIREBASE_VAPID_KEY,
                serviceWorkerRegistration: registration
            })
                .then((currentToken) => {
                    if (currentToken) {
                        console.log('Token FCM Obtenido:', currentToken);
                        sendTokenToServer(currentToken);
                    } else {
                        console.log('No se pudo obtener el token.');
                    }
                })
                .catch((err) => {
                    console.error('Error al obtener el token:', err);
                });
        } else {
            console.warn('Permiso de notificación DENEGADO.');
        }
    });
}

function sendTokenToServer(token) {
     fetch('/update-fcm-token', {
         method: 'POST',
         headers: {
             'Content-Type': 'application/json',
             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
         },
         body: JSON.stringify({
             token: token
         })
     })
     .then(response => {
        // Añadimos verificación por si el servidor devuelve error (ej. 404, 500)
        if (!response.ok) {
           return response.text().then(text => { // Intentamos leer el texto del error
               throw new Error(`Error del servidor (${response.status}): ${text}`);
           });
        }
        return response.json(); // Solo intentamos parsear JSON si la respuesta fue OK
     })
     .then(data => {
         console.log('Token guardado en el servidor:', data);
     })
     .catch((error) => {
         console.error('Error al guardar el token en el servidor:', error);
     });
}

// --- Manejador para notificaciones en PRIMER PLANO ---
// Lo definimos en una función separada para llamarlo solo una vez
function setupOnMessageListener() {
    if (messaging) {
        onMessage(messaging, (payload) => {
            console.log('¡[APP] MENSAJE EN PRIMER PLANO RECIBIDO!');
            console.log('Payload (Primer Plano):', payload);
            alert('Nueva Notificación (Primer Plano): ' + payload.notification.title);
        });
        console.log('Manejador de mensajes en primer plano configurado.');
    }
}

// =================================================================
// FIN DE LA LÓGICA DE FIREBASE/NOTIFICACIONES
// =================================================================