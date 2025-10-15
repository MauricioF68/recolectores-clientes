<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Solicitar un Recojo - Paso 2') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('client.request.step-two.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div
                            x-data="{ 
                                items: [
                                    { waste_type: '', bag_quantity: 1, note: '', photos: [] }
                                ],
                                addRow() {
                                    this.items.push({ waste_type: '', bag_quantity: 1, note: '', photos: [] });
                                },
                                removeRow(index) {
                                    this.items.splice(index, 1);
                                }
                               
                            }">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">Detalles del Recojo</h3>
                            <p class="mt-1 text-sm text-gray-600">
                                Añade los tipos de basura que quieres que recojan.
                            </p>

                            <template x-for="(item, index) in items" :key="index">
                                <div class="mt-6 border-t border-gray-200 pt-6">
                                    <h4 class="text-md font-semibold text-gray-800" x-text="'Tipo de Basura #' + (index + 1)"></h4>   
                                                                    
                                    <div class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-6 sm:gap-x-6">


                                        <div class="sm:col-span-3">
                                            <label :for="'waste_type_' + index" class="block text-sm font-medium text-gray-700">Tipo</label>
                                            <select x-model="item.waste_type" :name="'items['+index+'][waste_type]'" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                                <option value="">Selecciona un tipo...</option>
                                                <option value="organico">Orgánico</option>
                                                <option value="plastico">Plástico</option>
                                                <option value="vidrio">Vidrio</option>
                                                <option value="papel">Papel</option>
                                                <option value="carton">Cartón</option>
                                                <option value="objetos">Objetos/Muebles</option>
                                                <option value="otros">Otros</option>
                                            </select>
                                        </div>                                        

                                        <div class="sm:col-span-3">
                                            <label :for="'bag_quantity_' + index" class="block text-sm font-medium text-gray-700">Cantidad de Bolsas/Unidades</label>
                                            <input x-model="item.bag_quantity" type="number" :name="'items['+index+'][bag_quantity]'" min="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                        </div>

                                        <div class="sm:col-span-6">
                                            <label :for="'note_' + index" class="block text-sm font-medium text-gray-700">Nota (Opcional)</label>
                                            <textarea x-model="item.note" :name="'items['+index+'][note]'" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Ej: Las botellas están en una caja"></textarea>
                                        </div>

                                        <div class="sm:col-span-6">
                                            <label :for="'photos_' + index" class="block text-sm font-medium text-gray-700">Fotografías (Opcional)</label>
                                            <input type="file" :name="'items['+index+'][photos][]'" multiple class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                        </div>
                                        <button x-show="items.length > 1" @click.prevent="removeRow(index)" type="button" class="text-red-500 hover:text-red-700">
                                            Eliminar
                                        </button> 
                                    </div>
                                </div>
                            </template>

                            <div class="mt-6 border-t border-gray-200 pt-5">
                                <button @click.prevent="addRow()" type="button" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded inline-flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <span>Añadir otro tipo de basura</span>
                                </button>
                            </div>
                        </div>

                        <div class="mt-8 pt-5 border-t border-gray-200">
                            <div class="flex justify-end">
                                <a href="{{ route('client.request.step-one.create') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Volver al Paso 1</a>
                                <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    Confirmar y Enviar Solicitud
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>