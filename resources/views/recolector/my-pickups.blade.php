<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mis Recojos Programados') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="space-y-4">
                @forelse ($requests as $request)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        @php
                            // --- INICIO DE LA LÓGICA DE TIEMPO ---
                            $isEnCaminoDisabled = true; // Deshabilitado por defecto
                            if($request->status == 'programado' && $request->proposed_date && $request->proposed_time_start) {
                                // Creamos el objeto de la fecha y hora programada
                                $scheduleDateTime = \Carbon\Carbon::parse($request->proposed_date . ' ' . $request->proposed_time_start);
                                // Comparamos con la hora actual (una hora antes)
                                if (now()->addHour()->isAfter($scheduleDateTime)) {
                                    $isEnCaminoDisabled = false;
                                }
                            }
                            // El botón de completado solo se activa si el estado es 'en_camino'
                            $isCompletadoDisabled = $request->status !== 'en_camino';
                            // --- FIN DE LA LÓGICA DE TIEMPO ---
                        @endphp

                        <div class="flex flex-col sm:flex-row justify-between">
                            <div>
                                <p class="font-semibold text-lg">{{ $request->address }}</p>
                                @if ($request->proposed_date)
                                    <p class="text-sm text-gray-600 mt-1">
                                        Programado para: <strong>{{ \Carbon\Carbon::parse($request->proposed_date)->format('d/m/Y') }}</strong> de 
                                        <strong>{{ \Carbon\Carbon::parse($request->proposed_time_start)->format('h:i A') }}</strong> a 
                                        <strong>{{ \Carbon\Carbon::parse($request->proposed_time_end)->format('h:i A') }}</strong>
                                    </p>
                                @else
                                    <p class="text-sm text-blue-600 mt-1">Aceptado - Pendiente de programación</p>
                                @endif

                                {{-- ===== INICIO DE LA MODIFICACIÓN (DATOS DEL CLIENTE) ===== --}}
                                {{-- Este bloque se mostrará si el estado es 'programado' O 'en_camino' --}}
                                @if (in_array($request->status, ['programado', 'en_camino']) && $request->user)
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        <p class="text-sm font-medium text-gray-900">
                                            Datos del Cliente:
                                        </p>
                                        
                                        {{-- Nombre del Cliente --}}
                                        <div class="flex items-center mt-2 text-sm text-gray-600">
                                            <!-- Icono de Usuario -->
                                            <svg class="w-4 h-4 mr-1.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                            </svg>
                                            <span>{{ $request->user->name }}</span>
                                        </div>
                                        
                                        {{-- Teléfono del Cliente --}}
                                        <div class="flex items-center mt-1 text-sm text-gray-600">
                                            <!-- Icono de Teléfono -->
                                            <svg class="w-4 h-4 mr-1.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.06-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                                            </svg>
                                            {{-- Usamos la columna 'phone' que me confirmaste --}}
                                            <span>{{ $request->user->phone ?? 'Teléfono no disponible' }}</span>
                                        </div>
                                    </div>
                                @endif
                                {{-- ===== FIN DE LA MODIFICACIÓN ===== --}}
                            </div>
                            <div class="mt-4 sm:mt-0 flex items-start gap-4">
                                <form action="{{ route('recolector.request.in-progress', $request) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" @if($isEnCaminoDisabled) disabled @endif class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white disabled:bg-gray-400 disabled:cursor-not-allowed bg-blue-600 hover:bg-blue-700">
                                        En Camino
                                    </button>
                                </form>
                                <form action="{{ route('recolector.request.completed', $request) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" @if($isCompletadoDisabled) disabled @endif class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white disabled:bg-gray-400 disabled:cursor-not-allowed bg-green-600 hover:bg-green-700">
                                        Marcar como Completado
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-center text-gray-500">No tienes recojos programados.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>