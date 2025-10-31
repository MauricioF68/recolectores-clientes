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

        
    </script>
    @endpush
</x-app-layout>