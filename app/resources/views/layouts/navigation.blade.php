<div x-data="{ sidebarOpen: false }" @keydown.window.escape="sidebarOpen = false" class="relative min-h-screen bg-gray-50">
    <!-- Mobile Sidebar -->
    <div x-show="sidebarOpen" class="fixed inset-0 z-40 flex lg:hidden" x-transition.opacity>
        <div class="absolute inset-0 bg-gray-900/60" @click="sidebarOpen = false"></div>
        <div class="relative ml-auto flex h-full w-80 max-w-full flex-col border-l border-gray-200 bg-white shadow-xl">
            @include('layouts.partials.sidebar-menu', ['showCloseButton' => true])
        </div>
    </div>

    <!-- Desktop Sidebar -->
    <aside class="hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-30 lg:flex lg:w-72 lg:flex-col lg:border-r lg:border-gray-200 lg:bg-white">
        @include('layouts.partials.sidebar-menu')
    </aside>

    <!-- Main Column -->
    <div class="lg:pl-72">
        <div class="flex min-h-screen flex-col">
            <!-- Header -->
            <header class="sticky top-0 z-30 border-b border-gray-200 bg-white/95 backdrop-blur">
                <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-10">
                    <div class="flex items-center space-x-3">
                        <button class="rounded-lg border border-gray-200 p-2 text-gray-600 hover:border-gray-300 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/50 lg:hidden" @click="sidebarOpen = true">
                            <span class="sr-only">Buka navigasi</span>
                            <x-heroicon name="bars-3" class="h-5 w-5" />
                        </button>
                        @php
                            $storeName = \App\Models\Setting::get('store_name', config('app.name', 'Toko Ambu'));
                        @endphp
                        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                            <x-favicon-icon class="h-8 w-8" />
                            <div class="hidden sm:block">
                                <p class="text-sm font-semibold text-gray-900">{{ $storeName }}</p>
                                <p class="text-xs text-gray-500">Dashboard</p>
                            </div>
                        </a>
                    </div>

                    <div class="flex items-center space-x-3">
                        <x-dropdown align="right" width="72">
                            <x-slot name="trigger">
                                <button class="relative inline-flex items-center rounded-full border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary/30">
                                    <x-heroicon name="bell" class="h-5 w-5" />
                                    @if(($inventoryAlertCount ?? 0) > 0)
                                        <span class="absolute -top-1 -right-1 rounded-full bg-red-500 px-1 text-xs text-white">
                                            {{ $inventoryAlertCount }}
                                        </span>
                                    @endif
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <div class="px-4 py-2 text-sm font-semibold text-gray-700">Alert Stok</div>
                                <div class="max-h-60 overflow-y-auto">
                                    @forelse($inventoryAlerts ?? [] as $alert)
                                        <div class="border-b px-4 py-2">
                                            <div class="text-sm font-medium text-gray-900">{{ $alert->product->name ?? '-' }}</div>
                                            <div class="text-xs text-gray-600">
                                                {{ ($alert->location->warehouse->code ?? '') . '-' . ($alert->location->code ?? '') }}
                                                · Status: <span class="font-semibold capitalize">{{ str_replace('_',' ', $alert->status) }}</span>
                                            </div>
                                            <div class="text-xs text-gray-500">Last out: {{ $alert->last_out_date ?? '-' }}</div>
                                        </div>
                                    @empty
                                        <div class="px-4 py-2 text-sm text-gray-500">Tidak ada alert.</div>
                                    @endforelse
                                </div>
                                <div class="px-4 py-2">
                                    <a href="{{ route('warehouse.dashboard') }}" class="text-sm text-primary hover:underline">Lihat dashboard gudang</a>
                                </div>
                            </x-slot>
                        </x-dropdown>

                        @auth
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center rounded-full border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary/30">
                                        <div class="max-w-[8rem] truncate text-left">{{ Auth::user()->name }}</div>
                                        <div class="ms-2">
                                            <x-heroicon name="chevron-down" class="h-4 w-4" />
                                        </div>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('profile.edit')">
                                        {{ __('Profile') }}
                                    </x-dropdown-link>
                                    @if(auth()->user()?->hasRole('Super Admin') || auth()->user()?->can('manage_settings'))
                                        <x-dropdown-link :href="route('settings.index')">
                                            Pengaturan (Legacy)
                                        </x-dropdown-link>
                                        <x-dropdown-link :href="route('admin.settings.index')">
                                            Pengaturan Store Front
                                        </x-dropdown-link>
                                    @endif

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                            {{ __('Log Out') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        @endauth
                    </div>
                </div>
            </header>

            @isset($header)
                <div class="border-b border-gray-200 bg-white">
                    <div class="px-4 py-4 sm:px-6 lg:px-10">
                        {{ $header }}
                    </div>
                </div>
            @endisset

            <main class="flex-1 bg-gray-100 px-4 py-6 sm:px-6 lg:px-10">
                {{ $slot }}
            </main>
        </div>
    </div>
</div>
