<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('ledger.index') }}" class="text-blue-600 hover:underline">Buku Kas</a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-900">Detail Transaksi</span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Type Badge -->
                    <div class="mb-6">
                        @if($ledgerEntry->type === 'income')
                            <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Pemasukan
                            </span>
                        @else
                            <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Pengeluaran
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <!-- Transaction Information -->
                        <div>
                            <h4 class="text-sm font-semibold text-gray-500 uppercase mb-4">Informasi Transaksi</h4>

                            <div class="space-y-3">
                                <div>
                                    <label class="text-sm text-gray-500">Tanggal Transaksi</label>
                                    <p class="text-gray-900">{{ $ledgerEntry->entry_date ? $ledgerEntry->entry_date->format('d M Y') : '-' }}</p>
                                </div>

                                <div>
                                    <label class="text-sm text-gray-500">Deskripsi</label>
                                    <p class="text-gray-900">{{ $ledgerEntry->description }}</p>
                                </div>

                                <div>
                                    <label class="text-sm text-gray-500">Kategori</label>
                                    <p class="text-gray-900">{{ $ledgerEntry->category->name ?? '-' }}</p>
                                </div>

                                <div>
                                    <label class="text-sm text-gray-500">Jumlah</label>
                                    <p class="text-2xl font-bold {{ $ledgerEntry->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                        Rp {{ number_format($ledgerEntry->amount, 0, ',', '.') }}
                                    </p>
                                </div>

                                @if($ledgerEntry->notes)
                                    <div>
                                        <label class="text-sm text-gray-500">Catatan</label>
                                        <p class="text-gray-900 whitespace-pre-line">{{ $ledgerEntry->notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Payment Details -->
                        <div>
                            <h4 class="text-sm font-semibold text-gray-500 uppercase mb-4">Detail Pembayaran</h4>

                            <div class="space-y-3">
                                @if($ledgerEntry->payment_method)
                                    <div>
                                        <label class="text-sm text-gray-500">Metode Pembayaran</label>
                                        <p class="text-gray-900">
                                            @if($ledgerEntry->payment_method === 'cash')
                                                Cash
                                            @elseif($ledgerEntry->payment_method === 'debit')
                                                Debit
                                            @elseif($ledgerEntry->payment_method === 'credit_card')
                                                Credit Card
                                            @elseif($ledgerEntry->payment_method === 'transfer')
                                                Transfer
                                            @elseif($ledgerEntry->payment_method === 'qris')
                                                QRIS
                                            @else
                                                {{ $ledgerEntry->payment_method }}
                                            @endif
                                        </p>
                                    </div>
                                @endif

                                @if($ledgerEntry->payeeBankAccount)
                                    <div>
                                        <label class="text-sm text-gray-500">Rekening Penerima</label>
                                        <p class="text-gray-900">
                                            {{ $ledgerEntry->payeeBankAccount->bank_name }}<br>
                                            {{ $ledgerEntry->payeeBankAccount->account_number }}<br>
                                            <span class="text-sm text-gray-600">a.n. {{ $ledgerEntry->payeeBankAccount->account_name }}</span>
                                        </p>
                                    </div>
                                @endif

                                @if($ledgerEntry->payerBankAccount)
                                    <div>
                                        <label class="text-sm text-gray-500">Rekening Pengirim</label>
                                        <p class="text-gray-900">
                                            {{ $ledgerEntry->payerBankAccount->bank_name }}<br>
                                            {{ $ledgerEntry->payerBankAccount->account_number }}<br>
                                            <span class="text-sm text-gray-600">a.n. {{ $ledgerEntry->payerBankAccount->account_name }}</span>
                                        </p>
                                    </div>
                                @endif

                                @if($ledgerEntry->paymentMedia)
                                    <div>
                                        <label class="text-sm text-gray-500">Bukti Transfer/Kwitansi</label>
                                        <div class="mt-2">
                                            <a href="{{ Storage::url($ledgerEntry->paymentMedia->path) }}" target="_blank" class="inline-block">
                                                @if(str_starts_with($ledgerEntry->paymentMedia->mime_type, 'image/'))
                                                    <img src="{{ Storage::url($ledgerEntry->paymentMedia->path) }}" alt="Bukti Transfer" class="max-w-xs rounded-lg border border-gray-200 hover:border-primary">
                                                @else
                                                    <div class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                                        <span class="text-blue-600 hover:underline">{{ $ledgerEntry->paymentMedia->filename }}</span>
                                                    </div>
                                                @endif
                                            </a>
                                        </div>
                                    </div>
                                @endif

                                @if(!$ledgerEntry->payment_method && !$ledgerEntry->payeeBankAccount && !$ledgerEntry->payerBankAccount && !$ledgerEntry->paymentMedia)
                                    <div class="text-gray-500 text-sm italic">
                                        Detail pembayaran tidak tersedia
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Creator Information -->
                    <div class="border-t pt-6">
                        <h4 class="text-sm font-semibold text-gray-500 uppercase mb-4">Informasi Pencatatan</h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-3">
                                <div>
                                    <label class="text-sm text-gray-500">Dicatat Oleh</label>
                                    <p class="text-gray-900">{{ $ledgerEntry->creator->name ?? '-' }}</p>
                                </div>

                                <div>
                                    <label class="text-sm text-gray-500">Dicatat Pada</label>
                                    <p class="text-gray-900">{{ $ledgerEntry->created_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>

                            @if($ledgerEntry->source_type)
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm text-gray-500">Sumber</label>
                                        <p class="text-gray-900">
                                            @if($ledgerEntry->source_type === 'App\Models\Refund')
                                                <a href="{{ route('refunds.show', $ledgerEntry->source_id) }}" class="text-blue-600 hover:underline">
                                                    Refund #{{ $ledgerEntry->source_id }}
                                                </a>
                                            @elseif($ledgerEntry->source_type === 'App\Models\Payment' || $ledgerEntry->source_type === 'payment')
                                                @php
                                                    $payment = \App\Models\Payment::find($ledgerEntry->source_id);
                                                @endphp
                                                @if($payment && $payment->order_id)
                                                    <a href="{{ route('orders.show', $payment->order_id) }}" class="text-blue-600 hover:underline">
                                                        Order #{{ $payment->order->order_number ?? $payment->order_id }}
                                                    </a>
                                                @else
                                                    Payment #{{ $ledgerEntry->source_id }}
                                                @endif
                                            @elseif($ledgerEntry->source_type === 'App\Models\Purchase' || $ledgerEntry->source_type === 'purchase_payment')
                                                <a href="{{ route('purchases.show', $ledgerEntry->source_id) }}" class="text-blue-600 hover:underline">
                                                    Purchase #{{ $ledgerEntry->source_id }}
                                                </a>
                                            @elseif($ledgerEntry->source_type === 'ledger_transfer_fee')
                                                <a href="{{ route('ledger.show', $ledgerEntry->source_id) }}" class="text-blue-600 hover:underline">
                                                    Biaya Transfer Terkait
                                                </a>
                                            @else
                                                {{ $ledgerEntry->source_type }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
