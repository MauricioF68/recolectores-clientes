<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Solicitar un Recojo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('client.request.step-one.post') }}" method="POST">
                        @csrf
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-medium leading-6 text-gray-900">Confirma tu Ubicación</h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    Hemos detectado tu ubicación. Puedes arrastrar el pin para ajustar la posición exacta.
                                </p>
                            </div>

                            <div id="map" class="h-96 w-full bg-gray-200 rounded-md"></div>

                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700">Dirección</label>
                                <input type="text" name="address" id="address" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            </div>

                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700">Notas Adicionales (Opcional)</label>
                                <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Ej: Bolsas negras en la puerta, casa de rejas blancas..."></textarea>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label for="department" class="block text-sm font-medium text-gray-700">Departamento</label>
                                    <input type="text" name="department" id="department" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100" readonly>
                                </div>
                                <div>
                                    <label for="province" class="block text-sm font-medium text-gray-700">Provincia</label>
                                    <input type="text" name="province" id="province" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100" readonly>
                                </div>
                                <div>
                                    <label for="district" class="block text-sm font-medium text-gray-700">Distrito</label>
                                    <input type="text" name="district" id="district" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100" readonly>
                                </div>
                            </div>

                            <input type="hidden" name="latitude" id="latitude">
                            <input type="hidden" name="longitude" id="longitude">
                        </div>

                        <div class="mt-8 pt-5">
                            <div class="flex justify-end">
                                <a href="{{ route('dashboard') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
                                <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    Siguiente: Añadir Detalles
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let map;
        let marker;
        let geocoder;

        function initMap() {
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            const addressInput = document.getElementById('address');
            const departmentInput = document.getElementById('department');
            const provinceInput = document.getElementById('province');
            const districtInput = document.getElementById('district');

            const defaultPosition = {
                lat: -8.11599,
                lng: -79.02998
            };

            map = new google.maps.Map(document.getElementById("map"), {
                center: defaultPosition,
                zoom: 15,
            });

            marker = new google.maps.Marker({
                position: defaultPosition,
                map: map,
                draggable: true
            });

            geocoder = new google.maps.Geocoder();

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const userPosition = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        };
                        marker.setPosition(userPosition);
                        map.setCenter(userPosition);
                        geocodePosition(userPosition);
                    },
                    () => {
                        geocodePosition(defaultPosition);
                    }
                );
            } else {
                geocodePosition(defaultPosition);
            }

            marker.addListener('dragend', () => {
                geocodePosition(marker.getPosition());
            });

            function geocodePosition(pos) {
                geocoder.geocode({
                    location: pos,
                }, (results, status) => {
                    if (status === "OK") {
                        if (results[0]) {
                            const latitude = typeof pos.lat === 'function' ? pos.lat() : pos.lat;
                            const longitude = typeof pos.lng === 'function' ? pos.lng() : pos.lng;

                            latInput.value = latitude;
                            lngInput.value = longitude;
                            addressInput.value = results[0].formatted_address;

                            // Limpiamos y llenamos los nuevos campos
                            departmentInput.value = '';
                            provinceInput.value = '';
                            districtInput.value = '';

                            for (const component of results[0].address_components) {
                                const componentType = component.types[0];
                                switch (componentType) {
                                    case "administrative_area_level_1":
                                        departmentInput.value = component.long_name;
                                        break;
                                    case "administrative_area_level_2":
                                        provinceInput.value = component.long_name;
                                        break;
                                    case "locality":
                                        districtInput.value = component.long_name;
                                        break;
                                }
                            }
                        } else {
                            window.alert("No se encontraron resultados de dirección.");
                        }
                    } else {
                        window.alert("El geocodificador falló debido a: " + status);
                    }
                });
            }
        }
    </script>
    @endpush
</x-app-layout>