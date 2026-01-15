<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900">Halaman</h2>
            <a href="{{ route('pages.create') }}" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-hover">
                + Buat Halaman Baru
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($pages->count() > 0)
                        <div class="block sm:hidden divide-y divide-gray-200">
                            @foreach($pages as $page)
                                <div class="py-4 space-y-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            @if($page->featuredImage)
                                                <img src="{{ $page->featuredImage->url }}" alt="{{ $page->title }}" class="h-12 w-12 rounded object-cover">
                                            @else
                                                <div class="flex h-12 w-12 items-center justify-center rounded bg-gray-100">
                                                    <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-gray-900">{{ $page->title }}</p>
                                                <p class="text-xs text-gray-500">{{ $page->created_at->format('d M Y') }}</p>
                                            </div>
                                        </div>
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium {{ $page->is_published ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $page->is_published ? 'Published' : 'Draft' }}
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
                                        <span class="text-gray-400">Slug:</span>
                                        <span class="text-gray-900 font-medium">/{{ $page->slug }}</span>
                                    </div>
                                    <div class="flex items-center gap-3 pt-2 text-sm">
                                        <a href="{{ route('pages.edit', $page) }}" class="text-primary hover:text-primary-hover">Edit</a>
                                        <form action="{{ route('pages.destroy', $page) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus halaman ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-700">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="hidden sm:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr class="text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        <th class="px-4 py-3">Judul</th>
                                        <th class="px-4 py-3">Slug</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-4 py-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @foreach($pages as $page)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-4">
                                                <div class="flex items-center gap-3">
                                                    @if($page->featuredImage)
                                                        <img src="{{ $page->featuredImage->url }}" alt="{{ $page->title }}" class="h-12 w-12 rounded object-cover">
                                                    @else
                                                        <div class="flex h-12 w-12 items-center justify-center rounded bg-gray-100">
                                                            <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                            </svg>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">{{ $page->title }}</div>
                                                        <div class="text-xs text-gray-500">{{ $page->created_at->format('d M Y') }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 text-sm text-gray-600">/{{ $page->slug }}</td>
                                            <td class="px-4 py-4">
                                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $page->is_published ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                                    {{ $page->is_published ? 'Published' : 'Draft' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 text-right">
                                                <div class="inline-flex items-center gap-3 text-sm">
                                                    <a href="{{ route('pages.edit', $page) }}" class="text-primary hover:text-primary-hover">Edit</a>
                                                    <form action="{{ route('pages.destroy', $page) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus halaman ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-700">Hapus</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($pages->hasPages())
                            <div class="mt-6">
                                {{ $pages->links() }}
                            </div>
                        @endif
                    @else
                        <div class="py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada halaman</h3>
                            <p class="mt-1 text-sm text-gray-500">Mulai dengan membuat halaman baru.</p>
                            <div class="mt-6">
                                <a href="{{ route('pages.create') }}" class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-hover">
                                    + Buat Halaman Baru
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
