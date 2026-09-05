<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Incoming Repair Slip - {{ $batch->slip_no }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            background: #F1F5F9;
            padding: 20px;
            font-family: Arial, Calibri, 'Segoe UI', Tahoma, sans-serif;
            color: #000000;
            font-size: 11pt;
            margin: 0;
        }
        .slip-wrapper {
            max-width: 850px;
            margin: 0 auto;
            background: #FFFFFF;
            padding: 24px 30px;
            border: 1px solid #CBD5E1;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        .print-btn-bar {
            max-width: 850px;
            margin: 0 auto 12px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 0.88rem;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid transparent;
        }
        .btn-primary {
            background: #00205B;
            color: #FFFFFF;
        }
        .btn-primary:hover {
            background: #00153D;
        }
        .btn-outline {
            background: #FFFFFF;
            border-color: #CBD5E1;
            color: #475569;
        }
        .btn-outline:hover {
            background: #F8FAFC;
        }

        /* Top Brand Header */
        .brand-header {
            text-align: center;
            margin-bottom: 12px;
        }
        .brand-header img {
            height: 48px;
            max-width: 240px;
            object-fit: contain;
            margin-bottom: 4px;
        }

        /* Main Sheet Title */
        .slip-title {
            text-align: center;
            font-size: 17pt;
            font-weight: 900;
            letter-spacing: 0.5px;
            margin: 8px 0 16px 0;
            color: #000000;
            text-transform: uppercase;
        }

        /* Excel-Style Bordered Table */
        .excel-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .excel-table th, .excel-table td {
            border: 1px solid #000000;
            padding: 6px 10px;
            font-size: 10.5pt;
            line-height: 1.3;
        }

        .header-label {
            font-weight: 800;
            white-space: nowrap;
            width: 17%;
            background: #FFFFFF;
        }
        .header-val {
            font-weight: 700;
            width: 33%;
        }

        .batch-banner {
            text-align: center;
            font-weight: 900;
            font-size: 12pt;
            letter-spacing: 0.5px;
            background: #FFFFFF;
            padding: 6px 10px;
            text-transform: uppercase;
        }

        .desc-banner {
            text-align: center;
            font-weight: 800;
            font-size: 11pt;
            background: #FFFFFF;
            padding: 5px 10px;
            text-transform: uppercase;
        }

        .sub-header-row th {
            font-weight: 800;
            text-align: center;
            background: #FFFFFF;
            padding: 6px 10px;
        }

        .col-no {
            width: 55px;
            text-align: center;
            font-weight: bold;
        }
        .col-serial {
            width: 50%;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 10.5pt;
        }
        .col-mac {
            width: 50%;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 10.5pt;
        }

        .cell-bold {
            font-weight: 700;
        }

        @media print {
            body {
                background: #FFFFFF;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .slip-wrapper {
                border: none;
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .excel-table th, .excel-table td {
                border: 1px solid #000000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

<div class="print-btn-bar no-print">
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="{{ asset('images/logo.png') }}" alt="DFTM Logo" style="height: 32px;">
        <span style="font-weight: 700; color: #00205B;">DFTM DIGITAL SOLUTIONS</span>
    </div>
    <div style="display: flex; gap: 8px;">
        <button onclick="window.print()" class="btn btn-primary">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/></svg>
            Print Document
        </button>
        <button onclick="window.close()" class="btn btn-outline">Close</button>
    </div>
</div>

<div class="slip-wrapper">
    <!-- Top Logo -->
    <div class="brand-header">
        <img src="{{ asset('images/logo.png') }}" alt="DFTM Digital Solutions">
    </div>

    <!-- Main Title -->
    <div class="slip-title">INCOMING REPAIR SLIP</div>

    <!-- Exact Excel Structure from Reference Layout -->
    <table class="excel-table">
        <!-- Header Info Row 1: Company Name & Status -->
        <tr>
            <td class="header-label">COMPANY NAME:</td>
            <td class="header-val">{{ $batch->company_name ?? '' }}</td>
            <td class="header-label">STATUS:</td>
            <td class="header-val">{{ $batch->status ?: '-' }}</td>
        </tr>

        <!-- Header Info Row 2: Date Delivered & Total Quantity -->
        <tr>
            <td class="header-label">DATE DELIVERED:</td>
            <td class="header-val">{{ $batch->date_delivered ? $batch->date_delivered->format('Y-m-d') : '' }}</td>
            <td class="header-label">TOTAL QUANTITY:</td>
            <td class="header-val">{{ $batch->total_quantity ?? $batch->items->count() }}</td>
        </tr>

        <!-- Batch Banner Row -->
        <tr>
            <td colspan="4" class="batch-banner">{{ $batch->batch_no ?? 'BATCH 1' }}</td>
        </tr>

        <!-- Item Description Banner Row -->
        <tr>
            <td colspan="4" class="desc-banner">{{ $batch->item_description ?? 'ITEM DESCRIPTION' }}</td>
        </tr>
    </table>

    <!-- Items Subtable -->
    <table class="excel-table" style="border-top: none; margin-top: -1px;">
        <thead>
            <!-- Sub-Header Row 1: NO., BRAND, MODEL -->
            <tr class="sub-header-row">
                <th class="col-no" rowspan="2">NO.</th>
                <th>BRAND: {{ $batch->brand ?? '' }}</th>
                <th>MODEL: {{ $batch->model ?? '' }}</th>
            </tr>
            <!-- Sub-Header Row 2: SERIAL NUMBER, MAC ADDRESS -->
            <tr class="sub-header-row">
                <th>SERIAL NUMBER</th>
                <th>MAC ADDRESS</th>
            </tr>
        </thead>
        <tbody>
            @php
                $itemRows = $batch->items;
                $rowCount = max(20, $itemRows->count());
            @endphp
            @for($i = 0; $i < $rowCount; $i++)
                @php $item = $itemRows[$i] ?? null; @endphp
                <tr>
                    <td class="col-no">{{ $i + 1 }}</td>
                    <td class="col-serial {{ $item ? 'cell-bold' : '' }}">{{ $item?->serial_number ?? '' }}</td>
                    <td class="col-mac">{{ $item?->mac_address ?? '' }}</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- Signatures block -->
    <div style="margin-top: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; font-size: 10pt;">
        <div>
            <div style="border-bottom: 1px solid #000; height: 35px; margin-bottom: 5px;"></div>
            <div style="text-align: center; font-weight: bold;">Encoded / Received By (DFTM)</div>
        </div>
        <div>
            <div style="border-bottom: 1px solid #000; height: 35px; margin-bottom: 5px;"></div>
            <div style="text-align: center; font-weight: bold;">Delivered / Endorsed By</div>
        </div>
    </div>
</div>

</body>
</html>
