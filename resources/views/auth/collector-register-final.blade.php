<x-guest-layout>
    <form method="POST" action="{{ route('collector.register.store') }}">
        @csrf

        <h2 class="text-2xl font-bold text-center mb-4">Completar Registro</h2>
        <p class="text-center text-gray-600 mb-6">Tus datos han sido verificados. Por favor, crea una contraseña para activar tu cuenta.</p>

        <div class="space-y-4 mb-6">
            <div>
                <x-input-label for="name" value="Nombre Completo" />
                <x-text-input id="name" class="block mt-1 w-full bg-gray-100" type="text" name="name" :value="$collectorData->first_name . ' ' . $collectorData->last_name" readonly />
            </div>
            <div>
                <x-input-label for="dni" value="DNI" />
                <x-text-input id="dni" class="block mt-1 w-full bg-gray-100" type="text" name="dni_display" :value="$collectorData->dni" readonly />
                <input type="hidden" name="dni" value="{{ $collectorData->dni }}">
            </div>
             <div>
                <x-input-label for="email" value="Correo Electrónico" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="$collectorData->email" required />
            </div>
        </div>

        <div class="space-y-4">
            <div class="mt-4">
                <x-input-label for="password" value="Contraseña" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="password_confirmation" value="Confirmar Contraseña" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Registrar Cuenta') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>