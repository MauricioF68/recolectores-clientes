<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Programar Recojo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <form action="{{ route('recolector.request.schedule.store', $request) }}" method="POST">
                        @csrf
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-medium leading-6 text-gray-900">Proponer Horario</h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    Selecciona una fecha y un rango de horas para realizar el recojo en la dirección: 
                                    <span class="font-semibold">{{ $request->address }}</span>.
                                </p>
                            </div>

                            @if ($errors->any())
                                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="proposed_date" class="block text-sm font-medium text-gray-700">Fecha</label>
                                    <input type="date" name="proposed_date" id="proposed_date" min="{{ now()->toDateString() }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                </div>
                                <div>
                                    <label for="proposed_time_start" class="block text-sm font-medium text-gray-700">Desde las</label>
                                    <input type="time" name="proposed_time_start" id="proposed_time_start" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                </div>
                                <div>
                                    <label for="proposed_time_end" class="block text-sm font-medium text-gray-700">Hasta las</label>
                                    <input type="time" name="proposed_time_end" id="proposed_time_end" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 pt-5 border-t border-gray-200">
                            <div class="flex justify-end">
                                <a href="{{ route('recolector.request.show', $request) }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
                                <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    Proponer Horario
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>