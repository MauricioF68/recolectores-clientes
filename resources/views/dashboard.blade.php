<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de Cliente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-2xl font-bold">¡Hola, {{ Auth::user()->name }}!</h3>
                    <p class="mt-2 text-gray-600">¿Listo para programar un recojo?</p>

                    <div class="mt-8 text-center">
                        <a href="{{ route('client.request.step-one.create') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Solicitar Recojo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-8">
        <h3 class="text-2xl font-bold text-gray-800 mb-4">Mis Solicitudes</h3>
        <div class="space-y-4">
            @forelse ($requests as $request)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-500">Solicitud del {{ $request->created_at->format('d/m/Y') }}</p>
                        <p class="font-semibold">{{ $request->address }}</p>
                    </div>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @switch($request->status)
                                        @case('pendiente') bg-yellow-100 text-yellow-800 @break
                                        @case('aceptado') bg-blue-100 text-blue-800 @break
                                        @case('programado') bg-green-100 text-green-800 @break
                                        @default bg-gray-100 text-gray-800
                                    @endswitch">
                        {{ ucfirst($request->status) }}
                    </span>
                </div>

                {{-- Lógica para mostrar la propuesta de horario --}}
                @if ($request->status == 'aceptado' && $request->proposed_date)
                <div class="mt-4 p-4 bg-blue-50 border-l-4 border-blue-400">
                    <p class="font-semibold">¡Un recolector ha propuesto un horario!</p>
                    <p class="text-sm text-gray-700">
                        Fecha: <strong>{{ \Carbon\Carbon::parse($request->proposed_date)->format('d \d\e F, Y') }}</strong>
                    </p>
                    <p class="text-sm text-gray-700">
                        Rango de Hora: <strong>{{ \Carbon\Carbon::parse($request->proposed_time_start)->format('h:i A') }} - {{ \Carbon\Carbon::parse($request->proposed_time_end)->format('h:i A') }}</strong>
                    </p>
                    <div class="mt-3 flex gap-4">
                        <form action="{{ route('client.request.confirm', $request) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-sm font-medium text-white bg-green-600 hover:bg-green-700 px-3 py-1 rounded-md">Aceptar Horario</button>
                        </form>
                        <form action="{{ route('client.request.reject', $request) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-sm font-medium text-white bg-red-600 hover:bg-red-700 px-3 py-1 rounded-md">Rechazar Horario</button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
            @empty
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-center text-gray-500">Aún no has creado ninguna solicitud.</p>
            </div>
            @endforelse
        </div>
    </div>
    </div>
    </div>
</x-app-layout>