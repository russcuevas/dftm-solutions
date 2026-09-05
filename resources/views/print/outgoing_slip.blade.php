<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Outgoing Repair Slip - {{ $slip->slip_no }}</title>
    <link rel="stylesheet" href="{{ asset('css/dftm-theme.css') }}">
    <style>
        body {
            background: #F8FAFC;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #000;
        }
        .slip-wrapper {
            max-width: 900px;
            margin: 0 auto;
            background: #FFFFFF;
            padding: 30px;
            border: 2px solid #00205B;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .print-btn-bar {
            max-width: 900px;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .excel-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .excel-table th, .excel-table td {
            border: 1px solid #000000;
            padding: 6px 10px;
            font-size: 11pt;
        }
        .excel-title-header {
            background: #00205B;
            color: #FFFFFF;
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            padding: 10px;
            letter-spacing: 1px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .excel-label {
            font-weight: bold;
            background: #F1F5F9;
            width: 18%;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .excel-sec-header {
            background: #E2E8F0;
            font-weight: bold;
            text-align: center;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        @media print {
            body { padding: 0; background: #FFF; }
            .print-btn-bar { display: none; }
            .slip-wrapper { border: 2px solid #000; box-shadow: none; padding: 10px; }
        }
    </style>
</head>
<body>

<div class="print-btn-bar no-print">
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="{{ asset('images/logo.png') }}" alt="DFTM Logo" style="height: 36px;">
        <strong>DFTM DIGITAL SOLUTIONS</strong>
    </div>
    <div>
        <button onclick="window.print()" class="btn btn-primary btn-sm">
            <i class="bi bi-printer"></i> Print Outgoing Slip
        </button>
        <button onclick="window.close()" class="btn btn-outline btn-sm">Close</button>
    </div>
</div>

<div class="slip-wrapper">
    <!-- Header Block (Matches Outgoing-repair-slip.xlsx exactly) -->
    <div style="text-align: center; margin-bottom: 12px;">
        <img src="{{ asset('images/logo.png') }}" alt="DFTM Logo" style="height: 55px; max-width: 100%; object-fit: contain;">
    </div>

    <table class="excel-table" style="margin-top: 0;">
        <tr>
            <td colspan="4" class="excel-title-header">OUTGOING REPAIR SLIP</td>
        </tr>
        <tr>
            <td class="excel-label">COMPANY NAME:</td>
            <td style="font-weight: bold; width: 32%;">{{ $slip->company_name ?? '' }}</td>
            <td class="excel-label">STATUS:</td>
            <td style="font-weight: bold; width: 32%;">{{ $slip->status ?? '' }}</td>
        </tr>
        <tr>
            <td class="excel-label">DATE DELIVERED:</td>
            <td>{{ $slip->date_delivered ? $slip->date_delivered->format('Y-m-d') : '' }}</td>
            <td class="excel-label">TOTAL QUANTITY:</td>
            <td style="font-weight: bold;">{{ $slip->total_quantity ? ($slip->total_quantity . ' PCS') : '' }}</td>
        </tr>
        <tr>
            <td class="excel-label">SI NUMBER:</td>
            <td style="font-family: monospace; font-weight: bold;">{{ $slip->si_number }}</td>
            <td class="excel-label">DR NUMBER:</td>
            <td style="font-family: monospace; font-weight: bold;">{{ $slip->dr_number }}</td>
        </tr>
        <tr>
            <td colspan="4" style="text-align: center; font-weight: bold; background: #F8FAFC; letter-spacing: 0.5px;">{{ $slip->batch_no ?? 'BATCH 1' }}</td>
        </tr>
        <tr>
            <td colspan="4" style="text-align: center; font-weight: bold; background: #FFFFFF; letter-spacing: 0.5px;">ITEM DESCRIPTION: {{ $slip->item_description ?? '' }}</td>
        </tr>
    </table>

    <!-- Subtable for Outgoing Serialized Items (Matches Excel Columns Exactly) -->
    <table class="excel-table" style="margin-top: -1px;">
        <thead>
            <tr class="excel-sec-header">
                <th rowspan="2" style="width: 50px; text-align: center; vertical-align: middle;">NO.</th>
                <th style="text-align: center;">BRAND: {{ $slip->brand ?? ($slip->items->first()?->brand ?? '') }}</th>
                <th style="text-align: center;">MODEL: {{ $slip->model ?? ($slip->items->first()?->model ?? '') }}</th>
                <th rowspan="2" style="width: 100px; text-align: center; vertical-align: middle;">BOX NO.</th>
            </tr>
            <tr class="excel-sec-header">
                <th style="text-align: center;">SERIAL NUMBER</th>
                <th style="text-align: center;">MAC ADDRESS</th>
            </tr>
        </thead>
        <tbody>
            @php $count = max(20, $slip->items->count()); @endphp
            @for($i = 0; $i < $count; $i++)
                @php $item = $slip->items[$i] ?? null; @endphp
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $i + 1 }}</td>
                    <td style="font-family: monospace; font-weight: bold; text-align: center;">{{ $item?->serial_number }}</td>
                    <td style="font-family: monospace; text-align: center;">{{ $item?->mac_address }}</td>
                    <td style="text-align: center;">{{ $item?->box_no ?? ($item ? $slip->box_no : '') }}</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- Signatures block -->
    <div style="margin-top: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; font-size: 10pt;">
        <div>
            <div style="border-bottom: 1px solid #000; height: 35px; margin-bottom: 5px;"></div>
            <div style="text-align: center; font-weight: bold;">Released / Checked By (DFTM)</div>
        </div>
        <div>
            <div style="border-bottom: 1px solid #000; height: 35px; margin-bottom: 5px;"></div>
            <div style="text-align: center; font-weight: bold;">Received By (Customer / Client Signature)</div>
        </div>
    </div>
</div>

</body>
</html>
