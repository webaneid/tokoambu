<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Label Pengiriman</title>
    <style>
        @page { margin: 5mm; }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            color: #1f2937;
            font-size: 11pt;
        }
        .page {
            width: 100%;
            height: auto;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        .label-wrap {
            width: calc(100% - 26px);
            margin: 0;
            padding: 10px;
            border: 3px solid #000000;
            box-sizing: content-box;
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
        .label-title {
            font-size: 14px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: 600;
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
        .label-note {
            margin-top: 12px;
            padding: 8px 10px;
            background-color: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 4px;
            font-size: 10px;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="page">
        <x-shipping-label :order="$order" />
    </div>
</body>
</html>
