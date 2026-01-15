<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            color: #333;
            background: #fff;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #F17B0D;
        }
        .store-info h1 {
            color: #F17B0D;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .invoice-title {
            text-align: right;
        }
        .invoice-title h2 {
            font-size: 18px;
            color: #333;
            margin-bottom: 5px;
        }
        .invoice-meta {
            display: flex;
            justify-content: space-between;
            gap: 40px;
            margin-bottom: 30px;
        }
        .invoice-meta div {
            flex: 1;
        }
        .invoice-meta label {
            font-weight: bold;
            color: #666;
            display: block;
            margin-bottom: 3px;
            font-size: 11px;
        }
        .invoice-meta p {
            color: #333;
            line-height: 1.6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table thead {
            background: #F9FAFB;
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
        }
        table thead th {
            padding: 10px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            color: #333;
        }
        table tbody td {
            padding: 10px;
            border-bottom: 1px solid #E5E7EB;
        }
        table tbody tr:last-child td {
            border-bottom: 2px solid #333;
        }
        .amount-right {
            text-align: right;
        }
        .summary {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            margin-bottom: 30px;
        }
        .summary-col {
            width: 250px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #E5E7EB;
            font-size: 12px;
        }
        .summary-row.total {
            border-bottom: 2px solid #F17B0D;
            padding-top: 10px;
            padding-bottom: 10px;
            font-weight: bold;
            font-size: 14px;
            color: #F17B0D;
        }
        .summary-row label {
            font-weight: 500;
        }
        .payment-info {
            background: #F9FAFB;
            padding: 15px;
            border-left: 3px solid #0D36AA;
            margin-bottom: 20px;
        }
        .payment-info strong {
            color: #0D36AA;
        }
        .notes {
            background: #FFF5F0;
            padding: 15px;
            border-left: 3px solid #F17B0D;
            margin-bottom: 20px;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #E5E7EB;
            padding-top: 15px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <div class="store-info">
                <h1>{{ strtoupper($store['name'] ?? 'TOKO AMBU') }}</h1>
                <p style="font-size: 11px; color: #666;">
                    @if(!empty($store['address']))
                        {{ $store['address'] }}<br>
                    @endif
                    @if(!empty($store['city']))
                        {{ $store['city'] }}<br>
                    @endif
                    @if(!empty($store['phone']))
                        Telepon: {{ $store['phone'] }}<br>
                    @endif
                    @if(!empty($store['email']))
                        Email: {{ $store['email'] }}
                    @endif
                </p>
            </div>
            <div class="invoice-title">
                <h2>INVOICE</h2>
                <p style="color: #F17B0D; font-weight: bold; font-size: 16px;">{{ $order->order_number }}</p>
            </div>
        </div>

        <div class="invoice-meta">
            <div>
                <label>CUSTOMER</label>
                <p>
                    <strong>{{ $order->customer->name }}</strong><br>
                    @if ($order->customer->phone)
                        {{ $order->customer->phone }}<br>
                    @endif
                    @if ($order->customer->email)
                        {{ $order->customer->email }}<br>
                    @endif
                    @if ($order->customer->address)
                        {{ $order->customer->address }}
                    @endif
                </p>
            </div>
            <div>
                <label>TANGGAL</label>
                <p>{{ $order->created_at->format('d M Y') }}</p>
                
                <label style="margin-top: 10px;">JATUH TEMPO</label>
                <p>{{ $order->created_at->addDays(14)->format('d M Y') }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Deskripsi</th>
                    <th style="width: 15%; text-align: right;">Qty</th>
                    <th style="width: 20%; text-align: right;">Harga</th>
                    <th style="width: 25%; text-align: right;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->product->name }}</strong><br>
                            <small style="color: #999;">{{ $item->product->sku }}</small>
                        </td>
                        <td class="amount-right">{{ $item->quantity }}</td>
                        <td class="amount-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="amount-right"><strong>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <div class="summary-col">
                <div class="summary-row">
                    <label>Ongkos Kirim{{ $order->shipping_courier ? ' (' . strtoupper($order->shipping_courier) . ')' : '' }}</label>
                    <span>Rp {{ number_format($order->shipping_cost ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="summary-row">
                    <label>Subtotal</label>
                    <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="summary-row">
                    <label>PPN (0%)</label>
                    <span>Rp 0</span>
                </div>
                <div class="summary-row total">
                    <label>TOTAL</label>
                    <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="summary-row">
                    <label>Sudah Dibayar</label>
                    <span>Rp {{ number_format($order->paid_amount, 0, ',', '.') }}</span>
                </div>
                <div class="summary-row" style="border-bottom: 2px solid #F17B0D; color: #F17B0D; font-weight: bold;">
                    <label>Sisa Bayar</label>
                    <span>Rp {{ number_format($order->remainingAmount(), 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        @if ($order->remainingAmount() > 0)
            <div class="payment-info">
                <strong>STATUS PEMBAYARAN: BELUM LUNAS</strong><br>
                <small>Sisa pembayaran: Rp {{ number_format($order->remainingAmount(), 0, ',', '.') }}</small><br><br>
                <small>Silakan transfer ke rekening berikut:</small><br>
                <small>Bank BCA - No. Rek. 1234567890 a.n. Toko Ambu</small>
            </div>
        @else
            <div class="payment-info">
                <strong>STATUS PEMBAYARAN: LUNAS</strong><br>
                <small>Terima kasih atas pembayaran Anda</small>
            </div>
        @endif

        @if ($order->notes)
            <div class="notes">
                <strong>CATATAN:</strong><br>
                <small>{{ $order->notes }}</small>
            </div>
        @endif

        <div class="footer">
            <p>Terima kasih telah berbelanja di Toko Ambu</p>
            <p>Dokumen ini dicetak secara otomatis dan tidak memerlukan tanda tangan</p>
        </div>
    </div>
</body>
</html>
