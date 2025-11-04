<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Catálogo de Recompensas') }}
        </h2>
    </x-slot>

    <div
        x-data="{ 
            isModalOpen: false, 
            formAction: '',
            rewardName: '' ,
            lastLocation: {{ json_encode($last_location) ?? 'null' }}
        }"
        @keydown.escape.window="isModalOpen = false"
        class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif
            @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold">Tus Puntos:
                        <span class="text-blue-600">{{ Auth::user()->points_balance ?? 0 }}</span>
                    </h3>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @forelse ($rewards as $reward)
                @php
                $canRedeem = (Auth::user()->points_balance ?? 0) >= $reward->points_cost;
                @endphp
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex flex-col">
                    <img src="{{ $reward->image_path ? asset('storage/' . $reward->image_path) : 'https://via.placeholder.com/300' }}" alt="{{ $reward->name }}" class="w-full h-48 object-cover">
                    <div class="p-6 flex flex-col flex-grow">
                        <h4 class="font-bold text-lg">{{ $reward->name }}</h4>
                        <p class="text-sm text-gray-600 mt-1 flex-grow">{{ $reward->description }}</p>
                        <p class="mt-4 font-bold text-blue-600">{{ $reward->points_cost }} Puntos</p>
                        <button
                            type="button"
                            @click="
                                    isModalOpen = true; 
                                    formAction = '{{ route('client.rewards.redeem', $reward) }}'; 
                                    rewardName = '{{ $reward->name }}';
                                    $nextTick(() => {
                                        $dispatch('open-redeem-modal', { location: lastLocation });
                                    });
                                "
                            @if(!$canRedeem) disabled @endif
                            class="w-full mt-4 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white {{ $canRedeem ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 cursor-not-allowed' }}">
                            Canjear
                        </button>
                    </div>
                </div>
                @empty
                <p class="col-span-full text-center text-gray-500">No hay recompensas disponibles.</p>
                @endforelse
            </div>

            <div x-show="isModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75" aria-hidden="true" style="display: none;"></div>
            <div x-show="isModalOpen" x-transition class="fixed inset-0 z-10 overflow-y-auto" style="display: none;">
                <div class="flex items-end justify-center min-h-full p-4 text-center sm:items-center sm:p-0">
                    <div @click.away="isModalOpen = false" class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                        <form :action="formAction" method="POST" id="redeem-form">
                            @csrf
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Confirmar Dirección de Envío</h3>
                                <p class="mt-1 text-sm text-gray-600">Confirma o ajusta la dirección para recibir tu recompensa: <strong x-text="rewardName"></strong>.</p>
                                <div class="mt-4 grid grid-cols-1 gap-6">
                                    <div>
                                        <label for="dni" class="block text-sm font-medium text-gray-700">DNI</label>
                                        <input id="dni" type="text" name="dni" value="{{ Auth::user()->dni }}" class="mt-1 shadow-sm border-gray-300 rounded-md w-full" required>
                                    </div>
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700">Teléfono</label>
                                        <input id="phone" type="text" name="phone" value="{{ Auth::user()->phone }}" class="mt-1 shadow-sm border-gray-300 rounded-md w-full" required>
                                    </div>
                                    <div>
                                        <label for="address-modal-input" class="block text-sm font-medium text-gray-700">Dirección</label>
                                        <input id="address-modal-input" type="text" name="address" required class="mt-1 shadow-sm border-gray-300 rounded-md w-full">
                                    </div>
                                    <div id="map-modal" class="h-64 w-full bg-gray-200 rounded-md"></div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <input id="department-modal-input" type="text" name="department" placeholder="Departamento" class="shadow-sm border-gray-300 rounded-md w-full bg-gray-100" readonly>
                                        <input id="province-modal-input" type="text" name="province" placeholder="Provincia" class="shadow-sm border-gray-300 rounded-md w-full bg-gray-100" readonly>
                                        <input id="district-modal-input" type="text" name="district" placeholder="Distrito" class="shadow-sm border-gray-300 rounded-md w-full bg-gray-100" readonly>
                                    </div>
                                    <input type="hidden" name="latitude" id="latitude-modal-input">
                                    <input type="hidden" name="longitude" id="longitude-modal-input">
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Confirmar Canje</button>
                                <button @click="isModalOpen = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
</x-app-layout>