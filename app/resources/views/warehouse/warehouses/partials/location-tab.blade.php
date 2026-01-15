<div x-data="locationManager()" x-init="init()">
    <!-- Filter Card -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 mx-6 mt-6">
        <div class="p-6">
            <div class="flex items-end gap-4">
                <div class="flex-1">
                    <label for="warehouse_select" class="block text-sm font-medium text-gray-700 mb-1">Pilih Gudang</label>
                    <select id="warehouse_select" x-model="selectedWarehouseId" @change="selectWarehouse()" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        <option value="">-- Pilih Gudang --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->code }} - {{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="button" @click="startCreateLocation()" :disabled="!selectedWarehouseId" :class="selectedWarehouseId ? 'bg-primary hover:bg-primary-hover' : 'bg-gray-300 cursor-not-allowed'" class="h-10 px-4 text-white rounded-lg transition">
                        + Tambah Lokasi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Locations Table -->
    <div x-show="selectedWarehouseId" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mx-6">
        <div class="text-center py-8" x-show="loadingLocations">
            <p class="text-gray-500">Memuat lokasi...</p>
        </div>

        <div class="text-center py-8" x-show="!loadingLocations && locations.length === 0 && selectedWarehouseId">
            <p class="text-gray-500">Belum ada lokasi untuk gudang ini</p>
        </div>

        <div class="block sm:hidden divide-y divide-gray-100" x-show="!loadingLocations && locations.length > 0">
            <template x-for="location in locations" :key="location.id">
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900" x-text="location.display_code"></p>
                            <p class="text-xs text-gray-500" x-text="location.description || '-'"></p>
                        </div>
                        <span class="px-2 py-0.5 text-xs rounded-full" :class="location.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" x-text="location.is_active ? 'Aktif' : 'Nonaktif'"></span>
                    </div>
                    <div class="text-xs text-gray-600">
                        <p class="font-medium text-gray-800">Atribut</p>
                        <template x-if="location.location_attributes && Object.keys(location.location_attributes).length > 0">
                            <div class="flex flex-wrap gap-1 mt-1">
                                <template x-for="(value, key) in location.location_attributes" :key="key">
                                    <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700" x-text="key + ': ' + value"></span>
                                </template>
                            </div>
                        </template>
                        <template x-if="!location.location_attributes || Object.keys(location.location_attributes).length === 0">
                            <span class="text-gray-400">-</span>
                        </template>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        <button type="button" @click="startEditLocation(location)" class="inline-flex items-center gap-1 text-blue hover:text-blue-light" title="Edit">
                            <x-heroicon name="pencil-square" class="w-4 h-4" /> Edit
                        </button>
                        <button type="button" @click="deleteLocation(location.id)" class="inline-flex items-center gap-1 text-red-600 hover:text-red-700" title="Hapus">
                            <x-heroicon name="trash" class="w-4 h-4" /> Hapus
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <div class="hidden sm:block overflow-x-auto" x-show="!loadingLocations && locations.length > 0">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Lokasi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Atribut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <template x-for="location in locations" :key="location.id">
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="location.display_code"></td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <template x-if="location.location_attributes && Object.keys(location.location_attributes).length > 0">
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="(value, key) in location.location_attributes" :key="key">
                                        <span class="text-xs bg-gray-100 px-2 py-1 rounded" x-text="key + ': ' + value"></span>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!location.location_attributes || Object.keys(location.location_attributes).length === 0">
                                <span class="text-gray-400">-</span>
                            </template>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500" x-text="location.description || '-'"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span :class="location.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 text-xs rounded-full" x-text="location.is_active ? 'Aktif' : 'Nonaktif'"></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="inline-flex items-center gap-3">
                                <button type="button"
                                        @click="startEditLocation(location)"
                                        class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-blue-100 bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 hover:border-blue-200 transition"
                                        title="Edit"
                                        aria-label="Edit lokasi">
                                    <x-heroicon name="pencil-square" class="w-4 h-4" />
                                </button>
                                <button type="button"
                                        @click="deleteLocation(location.id)"
                                        class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-red-100 bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 hover:border-red-200 transition"
                                        title="Hapus"
                                        aria-label="Hapus lokasi">
                                    <x-heroicon name="trash" class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
        </div>
    </div>

    <!-- Location Modal -->
    <div class="fixed inset-0 z-40 flex items-center justify-center px-4" x-show="openLocation" x-cloak>
        <div class="absolute inset-0 bg-black/40" @click="openLocation = false"></div>
        <div class="relative z-10 w-full max-w-2xl bg-white rounded-lg shadow-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 sticky top-0 bg-white">
                <h3 class="text-lg font-semibold text-gray-900" x-text="editLocation ? 'Edit Lokasi' : 'Tambah Lokasi'"></h3>
                <button type="button" class="text-gray-500 hover:text-gray-700" @click="openLocation = false" aria-label="Tutup">
                    <x-heroicon name="x-mark" class="w-5 h-5" />
                </button>
            </div>
            <form @submit.prevent="submitLocationForm()" class="p-6 space-y-4">
                <input type="hidden" name="warehouse_id" x-model="locationForm.warehouse_id">

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-gray-700 mb-1">Preview Kode Lokasi:</p>
                    <p class="text-lg font-semibold text-primary" x-text="getPreviewCode()"></p>
                </div>

                <div class="space-y-3" x-show="selectedWarehouse && selectedWarehouse.location_template">
                    <template x-for="(field, index) in (selectedWarehouse && selectedWarehouse.location_template ? selectedWarehouse.location_template : [])" :key="index">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" x-text="field + ' *'"></label>
                            <input
                                type="text"
                                x-model="locationForm.location_attributes[field]"
                                @input="$el.dispatchEvent(new Event('change', { bubbles: true }))"
                                class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                                required>
                        </div>
                    </template>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" x-model="locationForm.description" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" rows="2"></textarea>
                </div>

                <div>
                    <label class="inline-flex items-center space-x-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300" x-model="locationForm.is_active">
                        <span>Aktif</span>
                    </label>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t">
                    <button type="button" class="h-10 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition" @click="openLocation = false">Batal</button>
                    <button type="submit" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function locationManager() {
    return {
        selectedWarehouseId: '',
        selectedWarehouse: null,
        openLocation: false,
        editLocation: false,
        locationFormAction: '',
        locationForm: {
            warehouse_id: '',
            location_attributes: {},
            description: '',
            is_active: true,
        },
        locations: [],
        loadingLocations: false,
        warehouses: @json($warehouses->items()),

        init() {
            // Initialization if needed
        },

        selectWarehouse() {
            const warehouseId = this.selectedWarehouseId;
            if (!warehouseId) {
                this.selectedWarehouse = null;
                this.locations = [];
                return;
            }

            this.selectedWarehouse = this.warehouses.find(w => w.id == warehouseId);

            const template = this.selectedWarehouse?.location_template || [];
            this.locationForm.warehouse_id = warehouseId;
            this.locationForm.location_attributes = {};
            template.forEach(field => {
                this.locationForm.location_attributes[field] = '';
            });

            this.loadLocations();
        },

        loadLocations() {
            if (!this.selectedWarehouseId) return;

            this.loadingLocations = true;
            fetch('{{ route('warehouse.locations.index') }}?warehouse_id=' + this.selectedWarehouseId)
                .then(res => res.json())
                .then(data => {
                    this.locations = data.locations || [];
                })
                .catch(err => console.error('Error loading locations:', err))
                .finally(() => this.loadingLocations = false);
        },

        startCreateLocation() {
            if (!this.selectedWarehouseId) {
                alert('Pilih gudang terlebih dahulu');
                return;
            }
            this.openLocation = true;
            this.editLocation = false;
            this.locationFormAction = '{{ route('warehouse.locations.store') }}';

            const template = this.selectedWarehouse?.location_template || [];
            this.locationForm = {
                warehouse_id: this.selectedWarehouseId,
                location_attributes: {},
                description: '',
                is_active: true,
            };
            template.forEach(field => {
                this.locationForm.location_attributes[field] = '';
            });
        },

        startEditLocation(location) {
            this.openLocation = true;
            this.editLocation = true;
            this.locationFormAction = '/warehouse/locations/' + location.id;
            this.locationForm = {
                warehouse_id: location.warehouse_id,
                location_attributes: location.location_attributes || {},
                description: location.description || '',
                is_active: location.is_active,
            };
        },

        getPreviewCode() {
            if (!this.locationForm.location_attributes) return '-';

            const values = Object.values(this.locationForm.location_attributes).filter(v => v);
            return values.length > 0 ? values.join('-') : '-';
        },

        async submitLocationForm() {
            const formData = new FormData();
            formData.append('warehouse_id', this.locationForm.warehouse_id);
            formData.append('description', this.locationForm.description || '');
            formData.append('is_active', this.locationForm.is_active ? '1' : '0');

            Object.keys(this.locationForm.location_attributes).forEach(key => {
                formData.append('location_attributes[' + key + ']', this.locationForm.location_attributes[key]);
            });

            if (this.editLocation) {
                formData.append('_method', 'PUT');
            }

            const url = this.locationFormAction;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (response.ok) {
                    this.openLocation = false;
                    this.loadLocations();
                    alert(data.message || 'Lokasi berhasil disimpan');
                } else {
                    alert(data.message || 'Terjadi kesalahan');
                }
            } catch (err) {
                console.error('Error:', err);
                alert('Terjadi kesalahan saat menyimpan lokasi');
            }
        },

        async deleteLocation(locationId) {
            if (!confirm('Yakin ingin menghapus lokasi ini?')) return;

            try {
                const response = await fetch('/warehouse/locations/' + locationId, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (response.ok) {
                    this.loadLocations();
                    alert(data.message || 'Lokasi berhasil dihapus');
                } else {
                    alert(data.message || 'Terjadi kesalahan');
                }
            } catch (err) {
                console.error('Error:', err);
                alert('Terjadi kesalahan saat menghapus lokasi');
            }
        }
    }
}
</script>
