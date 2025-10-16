<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalles de la Solicitud') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-8">

            <div class="md:col-span-2 space-y-6">
                @foreach ($request->items as $item)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold">{{ ucfirst($item->waste_type) }}</h3>
                        <p class="text-sm text-gray-600">Cantidad: {{ $item->bag_quantity }}</p>
                        @if($item->note)
                        <p class="mt-2 text-sm text-gray-800 bg-gray-50 p-3 rounded-md"><strong>Nota:</strong> {{ $item->note }}</p>
                        @endif

                        {{-- Sección para mostrar fotos (si existen) --}}
                        @if($item->photos->isNotEmpty())
                        <div class="mt-4">
                            <h4 class="font-semibold text-sm">Fotos:</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mt-2">
                                @foreach($item->photos as $photo)
                                <a href="{{ asset('storage/' . $photo->path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $photo->path) }}" alt="Foto del recojo" class="rounded-lg object-cover h-24 w-full">
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <div class="md:col-span-1 space-y-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold">Ubicación</h3>
                        <div id="map" class="h-64 w-full bg-gray-200 rounded-md mt-4"></div>
                        <p class="mt-2 text-sm text-gray-700">{{ $request->address }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form action="{{ route('recolector.request.accept', $request) }}" method="POST">
                            @csrf 
                            @method('PATCH')
                            <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                Aceptar Recojo
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        function initMap() {
            const location = {
                lat: parseFloat('{{ $request->latitude }}'),
                lng: parseFloat('{{ $request->longitude }}')
            };
            const map = new google.maps.Map(document.getElementById("map"), {
                center: location,
                zoom: 16,
            });
            const marker = new google.maps.Marker({
                position: location,
                map: map,
            });
        }
    </script>
    @endpush
</x-app-layout>