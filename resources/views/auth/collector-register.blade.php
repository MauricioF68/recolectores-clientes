<x-guest-layout>
    <form method="POST" action="{{ route('collector.register.verify') }}">
        @csrf

        <h2 class="text-2xl font-bold text-center mb-4">Registro de Recolector</h2>
        <p class="text-center text-gray-600 mb-6">Por favor, ingrese su DNI para verificar si está autorizado.</p>

        <div>
            <x-input-label for="dni" value="DNI" />
            <x-text-input id="dni" class="block mt-1 w-full" type="text" name="dni" :value="old('dni')" required autofocus />
            <x-input-error :messages="$errors->get('dni')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Verificar DNI') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>