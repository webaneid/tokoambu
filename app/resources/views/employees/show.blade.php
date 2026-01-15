<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('employees.index') }}" class="text-blue-600 hover:underline">Karyawan</a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-900">Detail Karyawan</span>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('employees.edit', $employee) }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition">Edit</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-2xl font-semibold text-gray-900 mb-2">{{ $employee->name }}</h3>
                        @if($employee->is_active)
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Aktif
                            </span>
                        @else
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                Nonaktif
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-500 uppercase mb-4">Informasi Dasar</h4>

                            <div class="space-y-3">
                                <div>
                                    <label class="text-sm text-gray-500">Posisi/Jabatan</label>
                                    <p class="text-gray-900">{{ $employee->position ?? '-' }}</p>
                                </div>

                                <div>
                                    <label class="text-sm text-gray-500">Email</label>
                                    <p class="text-gray-900">{{ $employee->email ?? '-' }}</p>
                                </div>

                                <div>
                                    <label class="text-sm text-gray-500">No. Telepon</label>
                                    <p class="text-gray-900">{{ $employee->phone ?? '-' }}</p>
                                </div>

                                <div>
                                    <label class="text-sm text-gray-500">Alamat</label>
                                    <p class="text-gray-900">{{ $employee->address ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold text-gray-500 uppercase mb-4">Rekening Bank</h4>

                            @if($employee->bankAccounts->count() > 0)
                                <div class="space-y-3">
                                    @foreach($employee->bankAccounts as $account)
                                        <div class="p-4 border border-gray-200 rounded-lg">
                                            <div class="font-medium text-gray-900">{{ $account->bank_name }}</div>
                                            <div class="text-sm text-gray-600 mt-1">{{ $account->account_number }}</div>
                                            <div class="text-sm text-gray-600">a.n. {{ $account->account_name }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 italic">Belum ada rekening bank terdaftar</p>
                            @endif
                        </div>
                    </div>

                    @if($employee->notes)
                        <div class="border-t pt-6">
                            <h4 class="text-sm font-semibold text-gray-500 uppercase mb-2">Catatan</h4>
                            <p class="text-gray-900 whitespace-pre-line">{{ $employee->notes }}</p>
                        </div>
                    @endif

                    <div class="border-t pt-6 mt-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-500">
                            <div>
                                <span class="font-medium">Dibuat:</span> {{ $employee->created_at->format('d M Y H:i') }}
                            </div>
                            <div>
                                <span class="font-medium">Terakhir diubah:</span> {{ $employee->updated_at->format('d M Y H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
