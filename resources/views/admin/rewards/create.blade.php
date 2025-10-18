@extends('admin.layouts.app')

@section('title', 'Añadir Nueva Recompensa')

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <form action="{{ route('admin.rewards.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nombre de la Recompensa</label>
                        <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>

                    <div>
                        <label for="points_cost" class="block text-sm font-medium text-gray-700">Costo en Puntos</label>
                        <input type="number" name="points_cost" id="points_cost" min="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Descripción (Opcional)</label>
                        <textarea name="description" id="description" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    </div>

                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700">Imagen (Opcional)</label>
                        <input type="file" name="image" id="image" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                </div>

                <div class="mt-8 pt-5 border-t border-gray-200">
                    <div class="flex justify-end">
                        <a href="{{ route('admin.rewards.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
                        <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Guardar Recompensa
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection