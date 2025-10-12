@extends('admin.layouts.app')

@section('title', 'Gestión de Recolectores')

@section('content')
<div
    x-data="{ 
        isModalOpen: false, 
        editMode: false,
        formAction: '{{ route('admin.recolectores.store') }}',

        openCreateModal() {
            this.editMode = false;
            this.formAction = '{{ route('admin.recolectores.store') }}';
            this.$nextTick(() => {
                document.getElementById('collectorForm').reset();
                if (window.map) {
                    map.setCenter({ lat: -9.19, lng: -75.01 });
                    map.setZoom(5);
                    if (window.marker) marker.setVisible(false);
                }
            });
            this.isModalOpen = true;
        },

        async openEditModal(collectorId) {
            try {
                const response = await fetch(`/admin/recolectores/${collectorId}/edit`);
                if (!response.ok) throw new Error('Network response was not ok');
                const collector = await response.json();

                const form = document.getElementById('collectorForm');
                form.first_name.value = collector.first_name || '';
                form.middle_name.value = collector.middle_name || '';
                form.last_name.value = collector.last_name || '';
                form.second_last_name.value = collector.second_last_name || '';
                form.dni.value = collector.dni || '';
                form.email.value = collector.email || '';
                form.status.value = collector.status || 'activo';
                form.address.value = collector.address || '';
                form.department.value = collector.department || '';
                form.province.value = collector.province || '';
                form.district.value = collector.district || '';
                form.latitude.value = collector.latitude || '';
                form.longitude.value = collector.longitude || '';

                this.formAction = `/admin/recolectores/${collector.id}`;
                this.editMode = true;
                this.isModalOpen = true;

                if (collector.latitude && collector.longitude) {
                    const location = { lat: parseFloat(collector.latitude), lng: parseFloat(collector.longitude) };
                    setTimeout(() => {
                        if (window.google && window.map) {
                            google.maps.event.trigger(map, 'resize');
                            map.setCenter(location);
                            map.setZoom(15);
                            marker.setPosition(location);
                            marker.setVisible(true);
                        }
                    }, 200);
                }
            } catch (error) {
                console.error('Error fetching collector data:', error);
                alert('No se pudo cargar la información del recolector.');
            }
        },
        
        closeModal() {
            this.isModalOpen = false;
            this.editMode = false;
        }
    }"
    @keydown.escape.window="closeModal()">

    @if (session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <div class="mb-4">
        <button @click="openCreateModal()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            + Añadir Recolector
        </button>
    </div>

    <div x-show="isModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75" aria-hidden="true" style="display: none;"></div>
    <div x-show="isModalOpen" x-transition class="fixed inset-0 z-10 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-full p-4 text-center sm:items-center sm:p-0">
            <div @click.away="closeModal()" class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                <form id="collectorForm" :action="formAction" method="POST">
                    @csrf
                    <template x-if="editMode">
                        @method('PUT')
                    </template>
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" x-text="editMode ? 'Editar Recolector' : 'Añadir Nuevo Recolector'"></h3>
                        <div class="mt-4 grid grid-cols-1 gap-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <input id="first_name" type="text" name="first_name" placeholder="Primer Nombre (Obligatorio)" class="shadow-sm border-gray-300 rounded-md w-full" required>
                                <input id="middle_name" type="text" name="middle_name" placeholder="Segundo Nombre" class="shadow-sm border-gray-300 rounded-md w-full">
                                <input id="last_name" type="text" name="last_name" placeholder="Primer Apellido (Obligatorio)" class="shadow-sm border-gray-300 rounded-md w-full" required>
                                <input id="second_last_name" type="text" name="second_last_name" placeholder="Segundo Apellido" class="shadow-sm border-gray-300 rounded-md w-full">
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <input id="dni" type="text" name="dni" placeholder="DNI (Obligatorio)" class="shadow-sm border-gray-300 rounded-md w-full" required>
                                <input id="email" type="email" name="email" placeholder="Correo Electrónico" class="shadow-sm border-gray-300 rounded-md w-full">
                            </div>
                            <div x-show="editMode">
                                <label for="status" class="block text-sm font-medium text-gray-700">Estado</label>
                                <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="activo">Activo</option>
                                    <option value="suspendido">Suspendido</option>
                                </select>
                            </div>
                            <div>
                                <label for="address-input" class="block text-sm font-medium text-gray-700">Buscar Dirección</label>
                                <input id="address-input" type="text" name="address" placeholder="Escribe una dirección para autocompletar..." class="mt-1 shadow-sm border-gray-300 rounded-md w-full">
                            </div>
                            <div id="map" class="h-64 w-full bg-gray-200 rounded-md"></div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <input id="department-input" type="text" name="department" placeholder="Departamento" class="shadow-sm border-gray-300 rounded-md w-full bg-gray-100" readonly>
                                <input id="province-input" type="text" name="province" placeholder="Provincia" class="shadow-sm border-gray-300 rounded-md w-full bg-gray-100" readonly>
                                <input id="district-input" type="text" name="district" placeholder="Distrito" class="shadow-sm border-gray-300 rounded-md w-full bg-gray-100" readonly>
                            </div>
                            <input type="hidden" name="latitude" id="latitude-input">
                            <input type="hidden" name="longitude" id="longitude-input">
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm" x-text="editMode ? 'Guardar Cambios' : 'Guardar Recolector'"></button>
                        <button @click="closeModal()" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancelar</button>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre Completo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DNI</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($collectors as $collector)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $collector->first_name }} {{ $collector->last_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $collector->dni }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $collector->status == 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($collector->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button @click="openEditModal({{ $collector->id }})" class="text-indigo-600 hover:text-indigo-900">Editar</button>
                            <form action="{{ route('admin.recolectores.destroy', $collector->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 ml-4" onclick="return confirm('¿Estás seguro de que deseas eliminar a este recolector? Esta acción no se puede deshacer.')">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No hay recolectores registrados todavía.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let map;
    let marker;
    let autocomplete;

    function initMap() {
        const initialPosition = {
            lat: -9.19,
            lng: -75.01
        };
        map = new google.maps.Map(document.getElementById('map'), {
            center: initialPosition,
            zoom: 5
        });
        marker = new google.maps.Marker({
            map: map,
            anchorPoint: new google.maps.Point(0, -29)
        });
        const addressInput = document.getElementById('address-input');
        autocomplete = new google.maps.places.Autocomplete(addressInput, {
            componentRestrictions: {
                country: "pe"
            },
            fields: ["address_components", "geometry", "icon", "name", "formatted_address"],
        });
        autocomplete.addListener('place_changed', onPlaceChanged);
    }

    function onPlaceChanged() {
        marker.setVisible(false);
        const place = autocomplete.getPlace();
        if (place.geometry) {
            document.getElementById('address-input').value = place.formatted_address;
            updateMapAndFields(place.geometry.location, place.address_components, 15);
        }
    }

    function updateMapAndFields(location, components, zoom) {
        map.setCenter(location);
        map.setZoom(zoom);
        marker.setPosition(location);
        marker.setVisible(true);
        document.getElementById('latitude-input').value = location.lat();
        document.getElementById('longitude-input').value = location.lng();
        document.getElementById('department-input').value = '';
        document.getElementById('province-input').value = '';
        document.getElementById('district-input').value = '';
        for (const component of components) {
            const componentType = component.types[0];
            switch (componentType) {
                case "administrative_area_level_1":
                    document.getElementById('department-input').value = component.long_name;
                    break;
                case "administrative_area_level_2":
                    document.getElementById('province-input').value = component.long_name;
                    break;
                case "locality":
                    document.getElementById('district-input').value = component.long_name;
                    break;
            }
        }
    }
</script>
@endpush
@endsection