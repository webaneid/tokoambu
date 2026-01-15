<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-900">Master Gudang</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($message = Session::get('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ $message }}
                </div>
            @endif
            @if ($message = Session::get('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ $message }}
                </div>
            @endif

            <!-- Tab Navigation -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6" x-data="{ activeTab: 'warehouses' }">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <button
                            @click="activeTab = 'warehouses'"
                            :class="activeTab === 'warehouses' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="px-6 py-4 border-b-2 font-medium text-sm transition">
                            Gudang
                        </button>
                        <button
                            @click="activeTab = 'locations'"
                            :class="activeTab === 'locations' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="px-6 py-4 border-b-2 font-medium text-sm transition">
                            Lokasi Gudang
                        </button>
                    </nav>
                </div>

                <!-- Tab 1: Gudang -->
                <div x-show="activeTab === 'warehouses'" x-cloak>
                    @include('warehouse.warehouses.partials.warehouse-tab')
                </div>

                <!-- Tab 2: Lokasi Gudang -->
                <div x-show="activeTab === 'locations'" x-cloak>
                    @include('warehouse.warehouses.partials.location-tab')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
