<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print Label - Multiple Orders</title>
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

        .labels-container {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            gap: 5mm;
            justify-content: flex-start;
            max-width: 800px;
            margin: 0 auto;
        }

        .label-content {
            width: calc(50% - 2.5mm);
            padding: 8px;
            border: 2px solid #000000;
            background: white;
            box-sizing: border-box;
        }

        .label-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }

        .label-brand {
            font-size: 14px;
            font-weight: bold;
            color: #F17B0D;
        }

        .label-order-info {
            text-align: center;
            margin-bottom: 6px;
            padding: 4px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
        }

        .label-order-number {
            font-size: 7px;
            color: #6b7280;
            margin-bottom: 2px;
        }

        .label-courier-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2px;
        }

        .label-courier-name {
            font-size: 9px;
            font-weight: bold;
        }

        .label-tracking {
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        .label-meta {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 4px;
            margin-bottom: 6px;
            padding: 4px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
        }

        .label-meta-item {
            text-align: center;
        }

        .label-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px;
            margin-bottom: 6px;
        }

        .label-block {
            border: 1px solid #e5e7eb;
            padding: 4px;
            background-color: #ffffff;
        }

        .label-section {
            margin-top: 4px;
            border: 1px solid #e5e7eb;
            padding: 4px;
        }

        .label-muted {
            font-size: 6px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 1px;
        }

        .label-strong {
            font-size: 8px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .label-text {
            font-size: 7px;
            margin-top: 1px;
            line-height: 1.2;
        }

        .label-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .label-table th, .label-table td {
            border: 1px solid #e5e7eb;
            padding: 3px 2px;
            font-size: 7px;
            vertical-align: top;
        }

        .label-table th {
            text-align: left;
            color: #6b7280;
            font-size: 6px;
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
                size: A4 portrait;
                margin: 5mm;
            }

            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .labels-container {
                display: flex;
                flex-direction: row;
                flex-wrap: wrap;
                gap: 3mm;
                max-width: 100%;
                margin: 0;
            }

            .label-content {
                width: calc(50% - 1.5mm);
                margin: 0;
                padding: 5px;
                page-break-inside: avoid;
                border: 2px solid #000000;
            }

            /* Page break setiap 4 label (2 baris) */
            .label-content:nth-child(4n) {
                page-break-after: always;
            }

            .label-content:last-child {
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <div>
            <strong>Print Multiple Labels</strong>
            <p style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                Total {{ count($orders) }} label siap dicetak. Klik tombol Print untuk mencetak atau menyimpan sebagai PDF
            </p>
        </div>
        <button onclick="window.print()">🖨️ Print / Save PDF</button>
    </div>

    <div class="labels-container">
        @foreach($orders as $order)
            <div class="label-content">
                <x-shipping-label :order="$order" />
            </div>
        @endforeach
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
