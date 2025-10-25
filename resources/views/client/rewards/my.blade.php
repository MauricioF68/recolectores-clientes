<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mis Recompensas Canjeadas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="space-y-6">
                @forelse ($claims as $claim)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex flex-col md:flex-row justify-between md:items-center">
                            <div>
                                <p class="text-sm text-gray-500">Canjeado el {{ $claim->created_at->format('d/m/Y') }}</p>
                                <h3 class="text-lg font-bold">{{ $claim->reward->name }}</h3>
                                <p class="text-sm text-gray-600">{{ $claim->points_spent }} puntos</p>
                            </div>
                            <div class="mt-4 md:mt-0 md:text-right">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($claim->status == 'solicitado') bg-yellow-100 text-yellow-800 @else bg-green-100 text-green-800 @endif">
                                    {{ ucfirst($claim->status) }}
                                </span>

                                @if($claim->status == 'enviado')
                                    <div class="mt-2 text-sm text-gray-700 space-y-1">
                                        @if($claim->tracking_number) <p><strong>Nro. Orden:</strong> {{ $claim->tracking_number }}</p> @endif
                                        @if($claim->tracking_code) <p><strong>Código:</strong> {{ $claim->tracking_code }}</p> @endif
                                        @if($claim->agency_address) <p><strong>Agencia:</strong> {{ $claim->agency_address }}</p> @endif
                                        @if($claim->pickup_password) <p><strong>Contraseña:</strong> {{ $claim->pickup_password }}</p> @endif
                                        @if($claim->voucher_path) 
                                            <p>
                                                <a href="{{ asset('storage/' . $claim->voucher_path) }}" target="_blank" class="text-blue-600 hover:underline">Ver Voucher</a>
                                            </p> 
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-center text-gray-500">Aún no has canjeado ninguna recompensa.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>