<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('refunds.index') }}" class="text-blue-600 hover:underline">Refund</a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-900">Detail Refund</span>
            </div>
            <div class="flex space-x-2">
                @if($refund->status === 'pending')
                    <form action="{{ route('refunds.approve', $refund) }}" method="POST" class="inline" onsubmit="return confirm('Approve refund ini? Ledger entry akan dibuat otomatis.')">
                        @csrf
                        @method('POST')
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Approve</button>
                    </form>
                    <button type="button" onclick="openRejectModal()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Reject</button>
                    <form action="{{ route('refunds.destroy', $refund) }}" method="POST" class="inline" onsubmit="return confirm('Hapus refund ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Hapus</button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Status Badge -->
                    <div class="mb-6">
                        @if($refund->status === 'pending')
                            <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Pending
                            </span>
                        @elseif($refund->status === 'approved')
                            <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Approved
                            </span>
                        @else
                            <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Rejected
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <!-- Refund Information -->
                        <div>
                            <h4 class="text-sm font-semibold text-gray-500 uppercase mb-4">Informasi Refund</h4>

                            <div class="space-y-3">
                                <div>
                                    <label class="text-sm text-gray-500">Order</label>
                                    <p class="text-gray-900">
                                        <a href="{{ route('orders.show', $refund->order) }}" class="text-blue-600 hover:underline">
                                            {{ $refund->order->order_number }}
                                        </a>
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm text-gray-500">Customer</label>
                                    <p class="text-gray-900">{{ $refund->order->customer->name ?? '-' }}</p>
                                </div>

                                <div>
                                    <label class="text-sm text-gray-500">Jumlah Refund</label>
                                    <p class="text-2xl font-bold text-primary">Rp {{ number_format($refund->amount, 0, ',', '.') }}</p>
                                </div>

                                <div>
                                    <label class="text-sm text-gray-500">Alasan</label>
                                    <p class="text-gray-900">{{ $refund->reason }}</p>
                                </div>

                                @if($refund->notes)
                                    <div>
                                        <label class="text-sm text-gray-500">Catatan</label>
                                        <p class="text-gray-900 whitespace-pre-line">{{ $refund->notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Payment Details -->
                        <div>
                            <h4 class="text-sm font-semibold text-gray-500 uppercase mb-4">Detail Pembayaran Refund</h4>

                            <div class="space-y-3">
                                @if($refund->payment_method)
                                    <div>
                                        <label class="text-sm text-gray-500">Metode Pembayaran</label>
                                        <p class="text-gray-900">
                                            @if($refund->payment_method === 'cash')
                                                Cash
                                            @elseif($refund->payment_method === 'debit')
                                                Debit
                                            @elseif($refund->payment_method === 'credit_card')
                                                Credit Card
                                            @elseif($refund->payment_method === 'transfer')
                                                Transfer
                                            @elseif($refund->payment_method === 'qris')
                                                QRIS
                                            @else
                                                {{ $refund->payment_method }}
                                            @endif
                                        </p>
                                    </div>
                                @endif

                                @if($refund->customerBankAccount)
                                    <div>
                                        <label class="text-sm text-gray-500">Rekening Customer (Penerima)</label>
                                        <p class="text-gray-900">
                                            {{ $refund->customerBankAccount->bank_name }}<br>
                                            {{ $refund->customerBankAccount->account_number }}<br>
                                            <span class="text-sm text-gray-600">a.n. {{ $refund->customerBankAccount->account_name }}</span>
                                        </p>
                                    </div>
                                @endif

                                @if($refund->shopBankAccount)
                                    <div>
                                        <label class="text-sm text-gray-500">Rekening Toko (Pengirim)</label>
                                        <p class="text-gray-900">
                                            {{ $refund->shopBankAccount->bank_name }}<br>
                                            {{ $refund->shopBankAccount->account_number }}<br>
                                            <span class="text-sm text-gray-600">a.n. {{ $refund->shopBankAccount->account_name }}</span>
                                        </p>
                                    </div>
                                @endif

                                @if($refund->transfer_fee && $refund->transfer_fee > 0)
                                    <div>
                                        <label class="text-sm text-gray-500">Biaya Transfer</label>
                                        <p class="text-gray-900">Rp {{ number_format($refund->transfer_fee, 0, ',', '.') }}</p>
                                    </div>
                                @endif

                                @if($refund->paymentMedia)
                                    <div>
                                        <label class="text-sm text-gray-500">Bukti Transfer/Kwitansi</label>
                                        <div class="mt-2">
                                            <a href="{{ Storage::url($refund->paymentMedia->path) }}" target="_blank" class="inline-block">
                                                @if(str_starts_with($refund->paymentMedia->mime_type, 'image/'))
                                                    <img src="{{ Storage::url($refund->paymentMedia->path) }}" alt="Bukti Transfer" class="max-w-xs rounded-lg border border-gray-200 hover:border-primary">
                                                @else
                                                    <div class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                                        <span class="text-blue-600 hover:underline">{{ $refund->paymentMedia->filename }}</span>
                                                    </div>
                                                @endif
                                            </a>
                                        </div>
                                    </div>
                                @endif

                                @if(!$refund->payment_method && !$refund->customerBankAccount && !$refund->shopBankAccount && !$refund->paymentMedia)
                                    <div class="text-gray-500 text-sm italic">
                                        Detail pembayaran tidak tersedia
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Approval/Rejection Information -->
                    <div class="border-t pt-6">
                        <h4 class="text-sm font-semibold text-gray-500 uppercase mb-4">Status & Approval</h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-3">
                                <div>
                                    <label class="text-sm text-gray-500">Dibuat Oleh</label>
                                    <p class="text-gray-900">{{ $refund->createdBy->name ?? '-' }}</p>
                                </div>

                                <div>
                                    <label class="text-sm text-gray-500">Dibuat Pada</label>
                                    <p class="text-gray-900">{{ $refund->created_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>

                            @if($refund->status !== 'pending')
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm text-gray-500">{{ $refund->status === 'approved' ? 'Diapprove' : 'Direject' }} Oleh</label>
                                        <p class="text-gray-900">{{ $refund->approvedBy->name ?? '-' }}</p>
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-500">{{ $refund->status === 'approved' ? 'Diapprove' : 'Direject' }} Pada</label>
                                        <p class="text-gray-900">{{ $refund->approved_at ? $refund->approved_at->format('d M Y H:i') : '-' }}</p>
                                    </div>

                                    @if($refund->status === 'rejected' && $refund->rejection_reason)
                                        <div>
                                            <label class="text-sm text-gray-500">Alasan Reject</label>
                                            <p class="text-gray-900">{{ $refund->rejection_reason }}</p>
                                        </div>
                                    @endif

                                    @if($refund->status === 'approved' && $refund->ledgerEntry)
                                        <div>
                                            <label class="text-sm text-gray-500">Ledger Entry</label>
                                            <p class="text-gray-900">
                                                <a href="{{ route('ledger.index') }}" class="text-blue-600 hover:underline">
                                                    Lihat di Buku Kas
                                                </a>
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Reject Refund</h3>
            <form action="{{ route('refunds.reject', $refund) }}" method="POST">
                @csrf
                @method('POST')

                <div class="mb-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">Alasan Reject <span class="text-red-500">*</span></label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Masukkan alasan reject"></textarea>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeRejectModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Reject</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRejectModal() {
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }

        // Close modal on outside click
        document.getElementById('rejectModal')?.addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                closeRejectModal();
            }
        });
    </script>
</x-app-layout>
