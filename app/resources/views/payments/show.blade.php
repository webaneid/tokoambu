<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-900">Detail Pembayaran</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pembayaran</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-500">No. Order</p>
                            <p class="text-lg font-medium text-gray-900">{{ $payment->order->order_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Customer</p>
                            <p class="text-gray-900">{{ $payment->order->customer->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Jumlah</p>
                            <p class="text-lg font-bold text-primary">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Metode</p>
                            <p class="text-gray-900">{{ ucfirst($payment->method) }}</p>
                        </div>
                        @if($payment->method === 'transfer')
                            @if($payment->sender_name)
                                <div>
                                    <p class="text-sm text-gray-500">Nama Pengirim</p>
                                    <p class="text-gray-900">{{ $payment->sender_name }}</p>
                                </div>
                            @endif
                            @if($payment->sender_bank)
                                <div>
                                    <p class="text-sm text-gray-500">Bank Pengirim</p>
                                    <p class="text-gray-900">{{ $payment->sender_bank }}</p>
                                </div>
                            @endif
                            @if($payment->shopBankAccount)
                                <div>
                                    <p class="text-sm text-gray-500">Transfer ke Rekening Toko</p>
                                    <p class="text-gray-900">{{ $payment->shopBankAccount->bank_name }} - {{ $payment->shopBankAccount->account_number }}</p>
                                    <p class="text-xs text-gray-500">a.n {{ $payment->shopBankAccount->account_name }}</p>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Status</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-500">Status Verifikasi</p>
                            <span class="px-3 py-1 rounded-full text-sm font-medium 
                                {{ $payment->status === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $payment->status === 'verified' ? 'Terverifikasi' : 'Pending Verifikasi' }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Tanggal Pembayaran</p>
                            <p class="text-gray-900">{{ $payment->paid_at?->format('d M Y H:i') ?? $payment->created_at->format('d M Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Catatan</p>
                            <p class="text-gray-900">{{ $payment->notes ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Bukti Pembayaran</h3>
                @php
                    $proofs = $payment->paymentProofs;
                @endphp
                @if($proofs->count())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($proofs as $proof)
                            <div class="border border-gray-300 rounded-lg overflow-hidden">
                                @if(in_array(strtolower(pathinfo($proof->path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                    <img src="{{ Storage::url($proof->path) }}" alt="{{ $proof->filename }}" class="w-full h-auto">
                                    <div class="p-3 bg-gray-50">
                                        <a href="{{ Storage::url($proof->path) }}" target="_blank" class="text-sm text-primary hover:underline">
                                            {{ $proof->filename }}
                                        </a>
                                    </div>
                                @else
                                    <div class="p-4 text-center">
                                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                        <a href="{{ Storage::url($proof->path) }}" target="_blank" class="text-sm text-primary hover:underline">
                                            {{ $proof->filename }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @elseif($payment->attachments->count())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($payment->attachments as $attachment)
                            <div class="border border-gray-300 rounded-lg overflow-hidden">
                                @if(in_array(strtolower(pathinfo($attachment->file_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                    <img src="{{ Storage::url($attachment->file_path) }}" alt="{{ basename($attachment->file_path) }}" class="w-full h-auto">
                                    <div class="p-3 bg-gray-50">
                                        <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="text-sm text-primary hover:underline">
                                            {{ basename($attachment->file_path) }}
                                        </a>
                                    </div>
                                @else
                                    <div class="p-4 text-center">
                                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                        <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="text-sm text-primary hover:underline">
                                            {{ basename($attachment->file_path) }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">Tidak ada bukti pembayaran</p>
                @endif
            </div>

            <div class="mt-6 flex gap-3">
                @if($payment->status !== 'verified')
                    <form action="{{ route('payments.verify', $payment) }}" method="POST" class="flex-1">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition">
                            Verifikasi Pembayaran
                        </button>
                    </form>
                @endif
                <a href="{{ route('payments.index') }}" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 text-center">
                    Kembali
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
