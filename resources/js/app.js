// resources/js/app.js

import "./bootstrap";
import Alpine from "alpinejs";

window.Alpine = Alpine;
Alpine.start();

// =================================================================
// LÓGICA DE GOOGLE MAPS (GLOBAL)
// =================================================================

// Vars para Mapa 1: "Solicitar Recojo"
let mapSolicitar, markerSolicitar, geocoderSolicitar;
// Vars para Mapa 2: "Modal Recompensas"
let mapModal, markerModal, geocoderModal;
// Vars para Mapa 3: "Modal Recolector" (NUEVO)
window.mapRecolector = null; // Hacemos estas globales para que Alpine (x-data) pueda acceder
window.markerRecolector = null;
let geocoderRecolector,
    autocompleteRecolectorInstance = null;
let mapShowRequest, markerShowRequest;

/**
 * Función global llamada por la API de Google Maps.
 * Se encarga de inicializar CUALQUIER mapa que esté en la página actual.
 */
window.initMap = function () {
    console.log("Google Maps API cargada. Callback ejecutado.");

    // ----------------------------------------------------
    // 1. LÓGICA PARA MAPA DE "SOLICITAR RECOJO" (id="map")
    // ----------------------------------------------------
    const mapElementSolicitar = document.getElementById("map");
    const latInputSolicitar = document.getElementById("latitude");
    const lngInputSolicitar = document.getElementById("longitude");
    const addressInputSolicitar = document.getElementById("address");

    if (
        mapElementSolicitar &&
        latInputSolicitar &&
        lngInputSolicitar &&
        addressInputSolicitar
    ) {
        console.log('Inicializando mapa de "Solicitar Recojo"...');
        const defaultPosition = { lat: -8.11599, lng: -79.02998 }; // Trujillo
        mapSolicitar = new google.maps.Map(mapElementSolicitar, {
            center: defaultPosition,
            zoom: 15,
        });
        markerSolicitar = new google.maps.Marker({
            position: defaultPosition,
            map: mapSolicitar,
            draggable: true,
        });
        geocoderSolicitar = new google.maps.Geocoder();
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const userPosition = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };
                    markerSolicitar.setPosition(userPosition);
                    mapSolicitar.setCenter(userPosition);
                    geocodePositionSolicitar(userPosition);
                },
                () => {
                    geocodePositionSolicitar(defaultPosition);
                }
            );
        } else {
            geocodePositionSolicitar(defaultPosition);
        }
        markerSolicitar.addListener("dragend", () => {
            geocodePositionSolicitar(markerSolicitar.getPosition());
        });
    }

    // ----------------------------------------------------
    // 2. LÓGICA PARA MAPA DE "MODAL RECOMPENSAS" (id="map-modal")
    // ----------------------------------------------------
    const mapElementModal = document.getElementById("map-modal");
    if (mapElementModal) {
        console.log('Inicializando mapa de "Modal Recompensas"...');
        const defaultPositionModal = { lat: -9.19, lng: -75.01 }; // Centro de Perú
        mapModal = new google.maps.Map(mapElementModal, {
            center: defaultPositionModal,
            zoom: 5,
        });
        markerModal = new google.maps.Marker({
            map: mapModal,
            draggable: true,
            anchorPoint: new google.maps.Point(0, -29),
        });
        geocoderModal = new google.maps.Geocoder();
        const addressInputModal = document.getElementById(
            "address-modal-input"
        );
        const autocomplete = new google.maps.places.Autocomplete(
            addressInputModal,
            {
                componentRestrictions: { country: "pe" },
                fields: ["address_components", "geometry", "name"],
                types: ["address"],
            }
        );
        autocomplete.addListener("place_changed", () => {
            const place = autocomplete.getPlace();
            if (place.geometry) {
                addressInputModal.value = place.name;
                updateMapAndFieldsModal(
                    place.geometry.location,
                    place.address_components
                );
            }
        });
        markerModal.addListener("dragend", () => {
            geocodePositionModal(markerModal.getPosition());
        });
    }

    // ----------------------------------------------------
    // 3. LÓGICA PARA MAPA DE "MODAL RECOLECTOR" (id="map-recolector") (NUEVO)
    // ----------------------------------------------------
    const mapElementRecolector = document.getElementById("map-recolector");
    if (mapElementRecolector) {
        console.log('Inicializando mapa de "Modal Recolector"...');
        const initialPosition = { lat: -9.19, lng: -75.01 }; // Perú // Asignamos a las variables globales que Alpine usará

        window.mapRecolector = new google.maps.Map(mapElementRecolector, {
            center: initialPosition,
            zoom: 5,
        });
        window.markerRecolector = new google.maps.Marker({
            map: window.mapRecolector,
            anchorPoint: new google.maps.Point(0, -29),
        });

        geocoderRecolector = new google.maps.Geocoder(); // !!! IMPORTANTE !!! // HEMOS QUITADO LA INICIALIZACIÓN DEL AUTOCOMPLETE Y SU LISTENER DE AQUÍ. // Se moverá al 'event listener' de abajo.
    }
    // ----------------------------------------------------
    // 4. LÓGICA PARA MAPA DE "VER SOLICITUD" (id="map-show-request") (NUEVO)
    // ----------------------------------------------------
    const mapElementShowRequest = document.getElementById("map-show-request");
    if (mapElementShowRequest) {
        console.log('Inicializando mapa de "Ver Solicitud"...');

        // Leemos las coordenadas desde los atributos data-* que pusimos en el Blade
        const lat = parseFloat(mapElementShowRequest.dataset.latitude);
        const lng = parseFloat(mapElementShowRequest.dataset.longitude);

        if (!isNaN(lat) && !isNaN(lng)) {
            const location = { lat: lat, lng: lng };

            // Creamos un mapa estático (no draggable)
            mapShowRequest = new google.maps.Map(mapElementShowRequest, {
                center: location,
                zoom: 16,
                draggable: false, // No se puede arrastrar
                scrollwheel: false,
                disableDoubleClickZoom: true,
                streetViewControl: false,
                mapTypeControl: false,
            });

            // Creamos un marcador estático
            markerShowRequest = new google.maps.Marker({
                position: location,
                map: mapShowRequest,
            });
        } else {
            console.error(
                'Coordenadas inválidas para el mapa "Ver Solicitud".'
            );
            mapElementShowRequest.innerHTML =
                '<p class="text-center text-red-500 p-4">Error: Ubicación no disponible.</p>';
        }
    }
}; // --- FIN DE window.initMap ---

// --- Funciones auxiliares para "Solicitar Recojo" (Las que ya tenías) ---
function geocodePositionSolicitar(pos) {
    if (!geocoderSolicitar) return;
    const latInput = document.getElementById("latitude");
    const lngInput = document.getElementById("longitude");
    const addressInput = document.getElementById("address");
    if (!latInput || !lngInput || !addressInput) return;

    geocoderSolicitar.geocode({ location: pos }, (results, status) => {
        if (status === "OK" && results[0]) {
            latInput.value =
                typeof pos.lat === "function" ? pos.lat() : pos.lat;
            lngInput.value =
                typeof pos.lng === "function" ? pos.lng() : pos.lng;
            addressInput.value = results[0].formatted_address;
            document.getElementById("department").value = "";
            document.getElementById("province").value = "";
            document.getElementById("district").value = "";
            for (const component of results[0].address_components) {
                const type = component.types[0];
                if (type === "administrative_area_level_1")
                    document.getElementById("department").value =
                        component.long_name;
                if (type === "administrative_area_level_2")
                    document.getElementById("province").value =
                        component.long_name;
                if (type === "locality")
                    document.getElementById("district").value =
                        component.long_name;
            }
        }
    });
}

// --- Funciones auxiliares para "Modal Recompensas" (Las que ya tenías) ---
function updateMapAndFieldsModal(location, addressComponents = null) {
    if (!mapModal || !markerModal) return;
    mapModal.setCenter(location);
    mapModal.setZoom(16);
    markerModal.setPosition(location);
    markerModal.setVisible(true);
    document.getElementById("latitude-modal-input").value = location.lat();
    document.getElementById("longitude-modal-input").value = location.lng();
    if (addressComponents) {
        fillAddressFieldsModal(addressComponents);
    } else {
        geocodePositionModal(location);
    }
}
function geocodePositionModal(pos) {
    if (!geocoderModal) return;
    geocoderModal.geocode({ location: pos }, (results, status) => {
        if (status === "OK" && results[0]) {
            document.getElementById("address-modal-input").value =
                results[0].formatted_address;
            fillAddressFieldsModal(results[0].address_components);
        }
    });
}
function fillAddressFieldsModal(components) {
    const dep = document.getElementById("department-modal-input");
    const prov = document.getElementById("province-modal-input");
    const dist = document.getElementById("district-modal-input");
    dep.value = "";
    prov.value = "";
    dist.value = "";
    for (const component of components) {
        const types = component.types;
        if (types.includes("administrative_area_level_1"))
            dep.value = component.long_name;
        if (types.includes("administrative_area_level_2"))
            prov.value = component.long_name;
        if (types.includes("locality")) dist.value = component.long_name;
    }
}
window.addEventListener("open-redeem-modal", (event) => {
    if (!mapModal || !markerModal) {
        setTimeout(
            () =>
                window.dispatchEvent(
                    new CustomEvent("open-redeem-modal", {
                        detail: event.detail,
                    })
                ),
            100
        );
        return;
    }
    const savedLocation = event.detail.location;
    const addressInput = document.getElementById("address-modal-input");
    const departmentInput = document.getElementById("department-modal-input");
    const provinceInput = document.getElementById("province-modal-input");
    const districtInput = document.getElementById("district-modal-input");
    const latInput = document.getElementById("latitude-modal-input");
    const lngInput = document.getElementById("longitude-modal-input");
    const defaultPositionModal = { lat: -9.19, lng: -75.01 };
    if (savedLocation && savedLocation.latitude && savedLocation.longitude) {
        const position = {
            lat: parseFloat(savedLocation.latitude),
            lng: parseFloat(savedLocation.longitude),
        };
        addressInput.value = savedLocation.address || "";
        departmentInput.value = savedLocation.department || "";
        provinceInput.value = savedLocation.province || "";
        districtInput.value = savedLocation.district || "";
        latInput.value = position.lat;
        lngInput.value = position.lng;
        mapModal.setCenter(position);
        mapModal.setZoom(16);
        markerModal.setPosition(position);
        markerModal.setVisible(true);
    } else if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const userPosition = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                };
                updateMapAndFieldsModal(userPosition);
            },
            () => {
                updateMapAndFieldsModal(defaultPositionModal);
            }
        );
    } else {
        updateMapAndFieldsModal(defaultPositionModal);
    }
});

window.addEventListener('open-recolector-modal', (event) => {
    
    // 1. Asegurarnos de que el mapa y marcador están inicializados
    // (initMap ya debió correr y crear las variables)
    if (!window.mapRecolector || !window.markerRecolector) {
        console.warn("Mapa recolector no listo. Reintentando en 100ms...");
        // Si el modal se abre muy rápido, reintenta
        setTimeout(() => window.dispatchEvent(new CustomEvent('open-recolector-modal', { detail: event.detail })), 100);
        return;
    }

    // 2. Esperamos un breve momento para que el modal se redibuje
    setTimeout(() => {
        if (!window.google || !window.mapRecolector) return;
        
        // 3. Forzamos al mapa a recalcular su tamaño (clave para modales)
        google.maps.event.trigger(window.mapRecolector, 'resize');
        
        const collector = event.detail.collector; // 'collector' vendrá del Blade

        if (collector && collector.latitude && collector.longitude) {
            // MODO EDITAR: Centrar en la ubicación guardada
            const location = { lat: parseFloat(collector.latitude), lng: parseFloat(collector.longitude) };
            window.mapRecolector.setCenter(location);
            window.mapRecolector.setZoom(15);
            window.markerRecolector.setPosition(location);
            window.markerRecolector.setVisible(true);
        } else {
            // MODO CREAR: Centrar en Perú
            const defaultPosition = { lat: -9.19, lng: -75.01 };
            window.mapRecolector.setCenter(defaultPosition);
            window.mapRecolector.setZoom(5);
            window.markerRecolector.setVisible(false);
        }

        // 4. INICIALIZAMOS EL AUTOCOMPLETE (LA SOLUCIÓN AL BUG)
        // Solo lo creamos UNA VEZ usando la variable de control
        if (!autocompleteRecolectorInstance) {
            console.log("Creando instancia de Autocomplete para Recolector...");
            const addressInput = document.getElementById("address-recolector-input");
            
            autocompleteRecolectorInstance = new google.maps.places.Autocomplete(
                addressInput,
                {
                    componentRestrictions: { country: "pe" },
                    fields: [
                        "address_components",
                        "geometry",
                        "icon",
                        "name",
                        "formatted_address",
                    ],
                    types: ["address"],
                }
            );

            // Añadimos el listener que ahora SÍ FUNCIONARÁ
            autocompleteRecolectorInstance.addListener("place_changed", () => {
                window.markerRecolector.setVisible(false);
                const place = autocompleteRecolectorInstance.getPlace();
                
                if (place.geometry) {
                    // Usamos el ID único
                    document.getElementById("address-recolector-input").value =
                        place.formatted_address;
                B:   // Llamamos a la función de ayuda
                    updateMapAndFieldsRecolector(
                        place.geometry.location,
                        place.address_components,
                        15
                    );
                }
          });
        }

    }, 200); // 200ms de retraso para que el modal se dibuje
});

// --- Funciones auxiliares para Mapa 3: "Modal Recolector" (NUEVO) ---
function updateMapAndFieldsRecolector(location, components, zoom) {
    // Verificamos que las variables globales existan
    if (!window.mapRecolector || !window.markerRecolector) return;

    window.mapRecolector.setCenter(location);
    window.mapRecolector.setZoom(zoom);
    window.markerRecolector.setPosition(location);
    window.markerRecolector.setVisible(true);

    // Usamos los IDs únicos de este formulario
    document.getElementById("latitude-recolector-input").value = location.lat();
    document.getElementById("longitude-recolector-input").value =
        location.lng();
    document.getElementById("department-recolector-input").value = "";
    document.getElementById("province-recolector-input").value = "";
    document.getElementById("district-recolector-input").value = "";

    for (const component of components) {
        const componentType = component.types[0];
        switch (componentType) {
            case "administrative_area_level_1":
                document.getElementById("department-recolector-input").value =
                    component.long_name;
                break;
            case "administrative_area_level_2":
                document.getElementById("province-recolector-input").value =
                    component.long_name;
                break;
            case "locality":
                document.getElementById("district-recolector-input").value =
                    component.long_name;
                break;
        }
    }
}
// =================================================================
// FIN DE LA LÓGICA DE GOOGLE MAPS
// =================================================================

// =================================================================
// LÓGICA DE FIREBASE/NOTIFICACIONES (Esta parte no cambia)
// =================================================================
import { initializeApp } from "firebase/app";
import { getMessaging, getToken, onMessage } from "firebase/messaging";
let firebaseApp;
let messaging;
function initFCM() {
    console.log("Iniciando lógica de Firebase Cloud Messaging...");
    const firebaseConfig = {
        apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
        authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
        projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
        storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
        messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
        appId: import.meta.env.VITE_FIREBASE_APP_ID,
    };
    if (!firebaseApp) {
        try {
            firebaseApp = initializeApp(firebaseConfig);
            messaging = getMessaging(firebaseApp);
            console.log("Firebase inicializado correctamente.");
            setupOnMessageListener();
        } catch (error) {
            console.error("Error inicializando Firebase:", error);
            return;
        }
    }
    if ("serviceWorker" in navigator) {
        navigator.serviceWorker
            .register("/sw.js")
            .then((registration) => {
                console.log(
                    "Service Worker registrado con éxito:",
                    registration
                );
                requestNotificationPermission(registration);
            })
            .catch((error) => {
                console.error("Error al registrar el Service Worker:", error);
            });
    } else {
        console.warn("Service Workers no soportados en este navegador.");
    }
}
window.initFCM = initFCM;
function requestNotificationPermission(registration) {
    if (!messaging) return;
    console.log("Pidiendo permiso para notificaciones...");
    Notification.requestPermission().then((permission) => {
        if (permission === "granted") {
            console.log("Permiso de notificación concedido.");
            getToken(messaging, {
                vapidKey: import.meta.env.VITE_FIREBASE_VAPID_KEY,
                serviceWorkerRegistration: registration,
            })
                .then((currentToken) => {
                    if (currentToken) {
                        console.log("Token FCM Obtenido:", currentToken);
                        sendTokenToServer(currentToken);
                    } else {
                        console.log("No se pudo obtener el token.");
                    }
                })
                .catch((err) => {
                    console.error("Error al obtener el token:", err);
                });
        } else {
            console.warn("Permiso de notificación DENEGADO.");
        }
    });
}
function sendTokenToServer(token) {
    fetch("/update-fcm-token", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
        body: JSON.stringify({ token: token }),
    })
        .then((response) => {
            if (!response.ok) {
                return response.text().then((text) => {
                    throw new Error(
                        `Error del servidor (${response.status}): ${text}`
                    );
                });
            }
            return response.json();
        })
        .then((data) => {
            console.log("Token guardado en el servidor:", data);
        })
        .catch((error) => {
            console.error("Error al guardar el token en el servidor:", error);
        });
}
function setupOnMessageListener() {
    if (messaging) {
        onMessage(messaging, (payload) => {
            console.log("¡[APP] MENSAJE EN PRIMER PLANO RECIBIDO!");
            console.log("Payload (Primer Plano):", payload);
            alert(
                "Nueva Notificación (Primer Plano): " +
                    payload.notification.title
            );
        });
        console.log("Manejador de mensajes en primer plano configurado.");
    }
}
// =================================================================
// FIN DE LA LÓGICA DE FIREBASE/NOTIFICACIONES
// =================================================================
