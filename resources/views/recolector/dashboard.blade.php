<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Solicitudes Disponibles') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="space-y-4">
                        @forelse ($requests as $request)
                            <div class="p-4 border rounded-lg shadow-sm">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-semibold">{{ $request->address }}</h3>
                                    <span class="text-sm font-bold text-blue-600">
                                        {{-- Mostramos la distancia formateada a 2 decimales --}}
                                        Aprox. {{ number_format($request->distance, 2) }} km
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ $request->department }}, {{ $request->province }}, {{ $request->district }}
                                </p>
                                <div class="mt-4 flex justify-end">
                                    <a href="{{ route('recolector.request.show', $request) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                                        Ver Detalles
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <p class="text-gray-500">No hay solicitudes de recojo pendientes por el momento.</p>
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>