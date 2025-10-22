@extends('admin.layouts.app')

@section('title', 'Reclamos de Recompensas')

@section('content')
<div x-data="{ isModalOpen: false, formAction: '' }">

    @if (session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">DNI / Teléfono</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recompensa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dirección de Envío</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($claims as $claim)
                    <tr>
                        <td class="px-6 py-4">{{ $claim->user->name }}</td>
                        <td class="px-6 py-4">{{ $claim->user->dni }} / {{ $claim->user->phone }}</td>
                        <td class="px-6 py-4">{{ $claim->reward->name }} ({{ $claim->points_spent }} pts)</td>
                        <td class="px-6 py-4">{{ $claim->shipping_address }}</td>
                        <td class="px-6 py-4 text-sm font-medium">
                            <button @click="isModalOpen = true; formAction = '{{ route('admin.claims.fulfill', $claim) }}'" class="text-indigo-600 hover:text-indigo-900">
                                Enviar Comprobante
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            No hay reclamos pendientes.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div x-show="isModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75" aria-hidden="true" style="display: none;"></div>
    <div x-show="isModalOpen" x-transition class="fixed inset-0 z-10 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-full p-4 text-center sm:items-center sm:p-0">
            <div @click.away="isModalOpen = false" class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <form :action="formAction" method="POST" enctype="multipart/form-data">
                    @csrf @method('PATCH')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Enviar Comprobante de Envío</h3>
                        <div class="mt-4 space-y-6">
                            <div>
                                <label for="tracking_number" class="block text-sm font-medium text-gray-700">Número de Orden</label>
                                <input type="text" name="tracking_number" id="tracking_number" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>

                            <div>
                                <label for="tracking_code" class="block text-sm font-medium text-gray-700">Código</label>
                                <input type="text" name="tracking_code" id="tracking_code" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label for="agency_address" class="block text-sm font-medium text-gray-700">Dirección de Agencia (Opcional)</label>
                                <input type="text" name="agency_address" id="agency_address" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label for="pickup_password" class="block text-sm font-medium text-gray-700">Contraseña de Recojo (Opcional)</label>
                                <input type="text" name="pickup_password" id="pickup_password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label for="voucher_photo" class="block text-sm font-medium text-gray-700">Foto del Voucher (Opcional)</label>
                                <input type="file" name="voucher_photo" id="voucher_photo" accept="image/*" class="mt-1 block w-full text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white sm:ml-3 sm:w-auto sm:text-sm">Guardar y Enviar</button>
                        <button @click="isModalOpen = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection