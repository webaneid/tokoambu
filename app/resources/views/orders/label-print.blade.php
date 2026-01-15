<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Label Pengiriman - {{ $order->order_number ?? $order->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            color: #1f2937;
            font-size: 11pt;
            padding: 20px;
        }

        .no-print {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f3f4f6;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .no-print button {
            padding: 10px 20px;
            background-color: #F17B0D;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }

        .no-print button:hover {
            background-color: #d66a0a;
        }

        .label-wrap {
            width: 200mm;
            margin: 0 auto;
        }

        .label-content {
            width: 100%;
            padding: 10px;
            border: 3px solid #000000;
            background: white;
            box-sizing: border-box;
        }

        .label-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .label-brand {
            font-size: 20px;
            font-weight: bold;
            color: #F17B0D;
        }

        .label-order-info {
            text-align: center;
            margin-bottom: 10px;
            padding: 8px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
        }

        .label-order-number {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 3px;
        }

        .label-courier-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3px;
        }

        .label-courier-name {
            font-size: 13px;
            font-weight: bold;
        }

        .label-tracking {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 4px;
        }

        .label-meta {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 10px;
            padding: 8px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
        }

        .label-meta-item {
            text-align: center;
        }

        .label-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 10px;
        }

        .label-block {
            border: 1px solid #e5e7eb;
            padding: 8px;
            background-color: #ffffff;
        }

        .label-section {
            margin-top: 8px;
            border: 1px solid #e5e7eb;
            padding: 8px;
        }

        .label-muted {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .label-strong {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .label-text {
            font-size: 10px;
            margin-top: 2px;
            line-height: 1.3;
        }

        .label-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .label-table th, .label-table td {
            border: 1px solid #e5e7eb;
            padding: 6px 4px;
            font-size: 10px;
            vertical-align: top;
        }

        .label-table th {
            text-align: left;
            color: #6b7280;
            font-size: 9px;
            background-color: #f9fafb;
            font-weight: 600;
        }

        .label-right { text-align: right; }

        .label-table th:nth-child(1),
        .label-table td:nth-child(1) { width: 60%; }
        .label-table th:nth-child(2),
        .label-table td:nth-child(2) { width: 25%; }
        .label-table th:nth-child(3),
        .label-table td:nth-child(3) { width: 15%; }
        .label-table td { word-break: break-word; }

        @media print {
            @page {
                size: A5 landscape;
                margin: 5mm;
            }

            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .label-wrap {
                width: 100%;
                margin: 0;
                border: 3px solid #000000;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <div>
            <strong>Label Pengiriman</strong>
            <p style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                Klik tombol Print untuk mencetak atau menyimpan sebagai PDF
            </p>
        </div>
        <button onclick="window.print()">🖨️ Print / Save PDF</button>
    </div>

    <div class="label-wrap">
        <div class="label-content">
            <x-shipping-label :order="$order" />
        </div>
    </div>

    <script>
        // Auto open print dialog when page loads (optional)
        // window.addEventListener('load', function() {
        //     setTimeout(function() {
        //         window.print();
        //     }, 500);
        // });
    </script>
</body>
</html>
