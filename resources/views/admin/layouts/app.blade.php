<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF--8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    

</head>
<body class="bg-gray-100">

    <div class="flex">
        <aside class="w-64 min-h-screen bg-gray-800 text-white p-4">
            <h2 class="text-xl font-bold mb-4">Admin Panel</h2>
            <nav>
                <ul>
                    <li class="mb-2">
                        <a href="#" class="block p-2 rounded hover:bg-gray-700">Recolectores</a>
                    </li>
                    </ul>
            </nav>
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
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initMap" defer></script>
    @stack('scripts')

</body>
</html>