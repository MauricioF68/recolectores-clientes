@extends('admin.layouts.app')

@section('title', 'Gestión de Recompensas')

@section('content')
<div
    x-data="{ 
        isModalOpen: false, 
        editMode: false,
        formAction: '',
        reward: {}
    }">
    @if (session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <div class="mb-4">
        <button @click="editMode = false; formAction = '{{ route('admin.rewards.store') }}'; reward = {}; isModalOpen = true" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            + Añadir Recompensa
        </button>
    </div>

    <div x-show="isModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75" aria-hidden="true" style="display: none;"></div>
    <div x-show="isModalOpen" x-transition class="fixed inset-0 z-10 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-full p-4 text-center sm:items-center sm:p-0">
            <div @click.away="isModalOpen = false" class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <form :action="formAction" method="POST" enctype="multipart/form-data">
                    @csrf
                    <template x-if="editMode">
                        @method('PUT')
                    </template>
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" x-text="editMode ? 'Editar Recompensa' : 'Añadir Recompensa'"></h3>
                        <div class="mt-4 space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                                <input type="text" name="name" id="name" :value="reward.name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            </div>
                            <div>
                                <label for="points_cost" class="block text-sm font-medium text-gray-700">Costo en Puntos</label>
                                <input type="number" name="points_cost" id="points_cost" :value="reward.points_cost" min="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            </div>
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
                                <textarea name="description" id="description" x-text="reward.description" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                            </div>
                            <div>
                                <label for="image" class="block text-sm font-medium text-gray-700">Imagen</label>
                                <input type="file" name="image" id="image" accept="image/*" class="mt-1 block w-full text-sm">
                                <template x-if="editMode && reward.image_path">
                                    <img :src="'/storage/' + reward.image_path" alt="Imagen actual" class="mt-2 h-20 w-20 object-cover">
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white sm:ml-3 sm:w-auto sm:text-sm" x-text="editMode ? 'Guardar Cambios' : 'Guardar Recompensa'"></button>
                        <button @click="isModalOpen = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Costo (Puntos)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($rewards as $reward)
                    <tr>
                        <td class="px-6 py-4">{{ $reward->name }}</td>
                        <td class="px-6 py-4">{{ $reward->points_cost }}</td>
                        <td class="px-6 py-4 text-sm font-medium">
                            <button @click="editMode = true; formAction = '{{ route('admin.rewards.update', $reward) }}'; reward = {{ Js::from($reward) }}; isModalOpen = true" class="text-indigo-600 hover:text-indigo-900">Editar</button>
                            <form action="{{ route('admin.rewards.destroy', $reward) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta recompensa?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 ml-4">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">No hay recompensas creadas todavía.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection