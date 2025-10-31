<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8"> <!-- Corregido: 'UTF--8' a 'UTF-8' -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="theme-color" content="#6777ef" />
    <link rel="apple-touch-icon" href="{{ asset('logo.PNG') }}">
    
    <!-- Corregido: La ruta debe ser 'manifest.json' para ser consistente -->
    <link rel="manifest" href="{{ asset('manifest.json') }}"> 

    <title>Panel de Administrador</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="theme-color" content="#6777ef" />
    <link rel="apple-touch-icon" href="{{ asset('logo.PNG') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- Esto carga app.css (con Tailwind) y app.js (con Alpine.js y Firebase v9) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100">

    <div class="flex">
        <!-- MODIFICACIÓN 1: Añadimos 'flex flex-col' para empujar el menú de logout al fondo -->
        <aside class="w-64 min-h-screen bg-gray-800 text-white p-4 flex flex-col">
            
            <!-- Contenido principal del menú lateral -->
            <div>
                <h2 class="text-xl font-bold mb-4">Admin Panel</h2>
                <nav>
                    <ul>
                        <li class="mb-2">
                            <a href="{{ route('admin.recolectores.index') }}" class="block p-2 rounded hover:bg-gray-700">Recolectores</a>
                        </li>
                        <li class="mb-2">
                            <a href="{{ route('admin.rewards.index') }}" class="block p-2 rounded hover:bg-gray-700">Recompensas</a>
                        </li>
                        <li class="mb-2">
                            <a href="{{ route('admin.claims.index') }}" class="block p-2 rounded hover:bg-gray-700">Reclamos</a>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- MODIFICACIÓN 2: Menú de Perfil y Logout -->
            <!-- 'mt-auto' empuja este bloque al fondo de la barra lateral -->
            <nav class="mt-auto pt-4 border-t border-gray-700">
                <ul>
                    <li class="mb-2">
                        <a href="{{ route('profile.edit') }}" class="block p-2 rounded hover:bg-gray-700">
                            Mi Perfil
                        </a>
                    </li>
                    <li class="mb-2">
                        <!-- Este enlace dispara el formulario de logout de abajo -->
                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form-admin').submit();"
                           class="block p-2 rounded hover:bg-gray-700">
                            Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </nav>
            <!-- Formulario de Logout Oculto (necesario para CSRF) -->
            <form id="logout-form-admin" method="POST" action="{{ route('logout') }}" style="display: none;">
                @csrf
            </form>
            <!-- FIN DE LA MODIFICACIÓN 2 -->

        </aside>

        <main class="flex-1 p-8">
            <header class="mb-8">
                <h1 class="text-3xl font-bold">@yield('title')</h1>
            </header>

            <div>
                @yield('content')
            </div>
        </main>
    </div>

    <!-- MODIFICACIÓN 3: SCRIPTS LIMPIOS -->

    <!-- Script de Google Maps (Estaba bien, pero lo ponemos aquí) -->
    <!-- (Recuerda que tu app.js ahora define 'initMap' para que esto no falle) -->
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initMap" defer></script>

    <!-- 
      ¡IMPORTANTE!
      BORRAMOS TODOS LOS SCRIPTS ANTIGUOS de sw.js y Firebase v8
      que estaban aquí y causaban todos los conflictos y errores.
    -->

    <!-- Iniciador de Notificaciones (Llama a la función 'initFCM' en app.js) -->
    @auth
    <script>
        // Esperamos a que la página cargue completamente
        window.addEventListener('load', () => {
            // Verificamos que la función que definimos en app.js exista
            if (typeof window.initFCM === 'function') {
                // ¡Iniciamos la lógica de notificaciones!
                window.initFCM();
            }
        });
    </script>
    @endauth
    
    @stack('scripts')
    <!-- FIN DE LA MODIFICACIÓN 3 -->

</body>
</html>
