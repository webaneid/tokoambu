<div x-data="{
    open: {{ $errors->any() && !$errors->has('warehouse_id') ? 'true' : 'false' }},
    edit: false,
    formAction: '{{ route('warehouse.warehouses.store') }}',
    form: {
        code: @js(old('code', '')),
        name: @js(old('name', '')),
        address: @js(old('address', '')),
        location_template: @js(old('location_template', ['Lorong', 'Rak', 'Baris', 'Posisi'])),
        is_active: @js((bool) old('is_active', true)),
    },
    startCreate() {
        this.open = true;
        this.edit = false;
        this.formAction = '{{ route('warehouse.warehouses.store') }}';
        this.form = {
            code: '',
            name: '',
            address: '',
            location_template: ['Lorong', 'Rak', 'Baris', 'Posisi'],
            is_active: true
        };
    },
    startEdit(payload) {
        this.open = true;
        this.edit = true;
        this.formAction = payload.action;
        this.form = {
            code: payload.code,
            name: payload.name,
            address: payload.address ?? '',
            location_template: payload.location_template || ['Lorong', 'Rak', 'Baris', 'Posisi'],
            is_active: payload.is_active,
        };
    },
    addTemplateField() {
        this.form.location_template.push('');
    },
    removeTemplateField(index) {
        if (this.form.location_template.length > 1) {
            this.form.location_template.splice(index, 1);
        }
    }
}" x-on:warehouse-open.window="startCreate()">

    <div class="flex justify-end mb-6 px-6 pt-6">
        <button type="button" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition" @click="startCreate()">
            + Tambah Gudang
        </button>
    </div>

    <!-- Filter Card -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 mx-6">
        <div class="p-6 text-gray-900">
            <form method="GET" action="{{ route('warehouse.warehouses.index') }}">
                <div class="flex flex-wrap items-end gap-4 md:flex-nowrap">
                    <div class="w-full md:flex-1">
                        <label for="q" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                        <input type="text" id="q" name="q" value="{{ request('q') }}" placeholder="Kode, nama, atau alamat" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    </div>
                    <div class="flex items-end gap-2 md:flex-none">
                        <button type="submit" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover transition">Filter</button>
                        <a href="{{ route('warehouse.warehouses.index') }}" class="h-10 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-center flex items-center">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mx-6">
        @if ($warehouses->count())
            <div class="block sm:hidden divide-y divide-gray-100">
                @foreach($warehouses as $wh)
                    <div class="p-4 space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $wh->code }} · {{ $wh->name }}</p>
                                <p class="text-xs text-gray-500">{{ $wh->address ?? 'Alamat belum diisi' }}</p>
                            </div>
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $wh->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $wh->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                        <div class="text-xs text-gray-600">
                            <p class="font-medium text-gray-800">Template Lokasi</p>
                            @if($wh->location_template)
                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-700 mt-1">{{ implode(' → ', $wh->location_template) }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                        <div class="flex items-center justify-end gap-4 text-sm">
                            <button type="button"
                                    class="inline-flex items-center gap-2 text-blue hover:text-blue-light whitespace-nowrap"
                                    title="Edit"
                                    @click="startEdit({
                                        action: @js(route('warehouse.warehouses.update', $wh)),
                                        code: @js($wh->code),
                                        name: @js($wh->name),
                                        address: @js($wh->address),
                                        location_template: @js($wh->location_template),
                                        is_active: @js((bool) $wh->is_active),
                                    })">
                                <x-heroicon name="pencil-square" class="w-4 h-4" />
                                <span>Edit</span>
                            </button>
                            <form action="{{ route('warehouse.warehouses.destroy', $wh) }}" method="POST" class="inline-flex">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-2 text-red-600 hover:text-red-700 whitespace-nowrap" title="Hapus" onclick="return confirm('Yakin ingin menghapus?')">
                                    <x-heroicon name="trash" class="w-4 h-4" />
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="hidden sm:block overflow-x-auto">
            <table class="min-w-[720px] w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Template Lokasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($warehouses as $wh)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $wh->code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $wh->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $wh->address ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    @if($wh->location_template)
                                        <span class="text-xs bg-gray-100 px-2 py-1 rounded">{{ implode(' → ', $wh->location_template) }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $wh->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $wh->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="inline-flex items-center gap-3">
                                        <button type="button"
                                                class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-blue-100 bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 hover:border-blue-200 transition"
                                                title="Edit"
                                                aria-label="Edit gudang"
                                                @click="startEdit({
                                                    action: @js(route('warehouse.warehouses.update', $wh)),
                                                    code: @js($wh->code),
                                                    name: @js($wh->name),
                                                    address: @js($wh->address),
                                                    location_template: @js($wh->location_template),
                                                    is_active: @js((bool) $wh->is_active),
                                                })">
                                            <x-heroicon name="pencil-square" class="w-4 h-4" />
                                        </button>
                                        <form action="{{ route('warehouse.warehouses.destroy', $wh) }}" method="POST" class="inline-flex">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-red-100 bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 hover:border-red-200 transition"
                                                    title="Hapus"
                                                    aria-label="Hapus gudang"
                                                    onclick="return confirm('Yakin ingin menghapus?')">
                                                <x-heroicon name="trash" class="w-4 h-4" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada gudang</td>
                            </tr>
                        @endforelse
                    </tbody>
            </table>
            </div>
        @else
            <div class="text-center py-8">
                <p class="text-gray-500">Belum ada gudang</p>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    @if ($warehouses->lastPage() > 1)
        @php
            $currentPage = $warehouses->currentPage();
            $lastPage = $warehouses->lastPage();
            $pages = collect([
                1,
                2,
                $currentPage - 1,
                $currentPage,
                $currentPage + 1,
                $lastPage - 1,
                $lastPage,
            ])->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
                ->unique()
                ->sort()
                ->values();
        @endphp
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6 mx-6">
            <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-4 text-sm text-gray-600">
                <div>Total gudang: {{ number_format($warehouses->total()) }}</div>
                <div class="flex items-center gap-2">
                    <a href="{{ $warehouses->previousPageUrl() ?? '#' }}"
                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $warehouses->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}"
                       aria-label="Sebelumnya">
                        <x-heroicon name="chevron-left" class="w-4 h-4" />
                    </a>
                    @php $prev = null; @endphp
                    @foreach ($pages as $page)
                        @if ($prev && $page > $prev + 1)
                            <span class="px-2 text-gray-400">...</span>
                        @endif
                        <a href="{{ $warehouses->url($page) }}"
                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg border {{ $page === $currentPage ? 'border-primary bg-primary text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                            {{ $page }}
                        </a>
                        @php $prev = $page; @endphp
                    @endforeach
                    <a href="{{ $warehouses->nextPageUrl() ?? '#' }}"
                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $warehouses->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}"
                       aria-label="Berikutnya">
                        <x-heroicon name="chevron-right" class="w-4 h-4" />
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Warehouse Modal -->
    <div class="fixed inset-0 z-40 flex items-center justify-center px-4" x-show="open" x-cloak>
        <div class="absolute inset-0 bg-black/40" @click="open = false"></div>
        <div class="relative z-10 w-full max-w-2xl bg-white rounded-lg shadow-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 sticky top-0 bg-white">
                <h3 class="text-lg font-semibold text-gray-900" x-text="edit ? 'Edit Gudang' : 'Tambah Gudang'"></h3>
                <button type="button" class="text-gray-500 hover:text-gray-700" @click="open = false" aria-label="Tutup">
                    <x-heroicon name="x-mark" class="w-5 h-5" />
                </button>
            </div>
            <form x-bind:action="formAction" method="POST" class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <template x-if="edit">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode <span class="text-red-500">*</span></label>
                    <input type="text" name="code" x-model="form.code" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" required>
                    @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="form.name" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" required>
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <textarea name="address" x-model="form.address" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" rows="2"></textarea>
                    @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-700">Template Lokasi</label>
                        <button type="button" @click="addTemplateField()" class="text-xs text-primary hover:text-primary-hover">+ Tambah Field</button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(field, index) in form.location_template" :key="index">
                            <div class="flex items-center gap-2">
                                <input type="text" :name="'location_template[' + index + ']'" x-model="form.location_template[index]" placeholder="Contoh: Lorong, Rak, Baris, Posisi" class="flex-1 h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                <button type="button" @click="removeTemplateField(index)" :disabled="form.location_template.length === 1" :class="form.location_template.length === 1 ? 'text-gray-300 cursor-not-allowed' : 'text-red-600 hover:text-red-700'" class="flex-shrink-0">
                                    <x-heroicon name="trash" class="w-4 h-4" />
                                </button>
                            </div>
                        </template>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Template ini akan digunakan untuk membuat kode lokasi otomatis (contoh: A-1-1-1)</p>
                </div>
                <div class="md:col-span-2">
                    <label class="inline-flex items-center space-x-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300" x-model="form.is_active">
                        <span>Aktif</span>
                    </label>
                </div>
                <div class="md:col-span-2 flex justify-end gap-2 pt-4 border-t">
                    <button type="button" class="h-10 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition" @click="open = false">Batal</button>
                    <button type="submit" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
