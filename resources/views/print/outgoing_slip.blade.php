<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Outgoing Repair Slip - {{ $batch->batch_no }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            background: #F8FAFC;
            padding: 20px;
            font-family: Arial, Helvetica, sans-serif;
            color: #000000;
            font-size: 10.5pt;
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
        .btn-outline {
            background: #FFFFFF;
            border-color: #CBD5E1;
            color: #475569;
        }

        .excel-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
            font-family: Arial, Helvetica, sans-serif;
        }
        .excel-table th, .excel-table td {
            border: 1px solid #000000;
            padding: 6px 8px;
            font-size: 10pt;
            line-height: 1.3;
            text-align: center;
        }
        .excel-title-header {
            background: #00205B;
            color: #FFFFFF;
            text-align: center;
            font-size: 15pt;
            font-weight: 900;
            padding: 8px;
            letter-spacing: 0.5px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .excel-label {
            font-weight: 800;
            background: #F8FAFC;
            width: 20%;
            text-align: center;
        }
        .col-no {
            width: 45px;
            text-align: center;
            font-weight: bold;
        }
        .sub-header-row th {
            font-weight: 800;
            text-align: center;
            background: #F8FAFC;
            padding: 6px 8px;
        }

        @media print {
            body { padding: 0; background: #FFFFFF; font-family: Arial, Helvetica, sans-serif !important; }
            .print-btn-bar { display: none; }
            .slip-wrapper { border: none; box-shadow: none; padding: 0; max-width: 100%; }
            .excel-table th, .excel-table td {
                border: 1px solid #000000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<div class="print-btn-bar no-print">
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="{{ asset('images/logo.png') }}" alt="DFTM Logo" style="height: 32px;">
        <strong style="color: #00205B;">DFTM DIGITAL SOLUTIONS</strong>
    </div>
    <div style="display: flex; gap: 8px;">
        <button onclick="window.print()" class="btn btn-primary">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/></svg>
            Print Outgoing Slip
        </button>
        <button onclick="window.close()" class="btn btn-outline">Close</button>
    </div>
</div>

<div class="slip-wrapper">
    <div style="text-align: center; margin-bottom: 12px;">
        <img src="{{ asset('images/logo.png') }}" alt="DFTM Logo" style="height: 60px; max-width: 360px; object-fit: contain;">
    </div>

    <!-- Material Description Header Block -->
    <table class="excel-table">
        <tr>
            <td colspan="4" class="excel-title-header">OUTGOING REPAIR SLIP</td>
        </tr>
        <tr>
            <td class="excel-label">COMPANY NAME:</td>
            <td style="font-weight: 700; width: 30%; text-align: center;">{{ $customerName ?: ($batch->company_name ?? 'DFTM DIGITAL SOLUTIONS') }}</td>
            <td class="excel-label">BATCH NUMBER:</td>
            <td style="font-weight: 900; width: 30%; color: #00205B; text-align: center;">{{ $batch->batch_no }}</td>
        </tr>
        <tr>
            <td class="excel-label">DATE RELEASED:</td>
            <td style="text-align: center;">{{ $dateReleased ?: now()->format('Y-m-d') }}</td>
            <td class="excel-label">TOTAL QUANTITY:</td>
            <td style="font-weight: 800; text-align: center;">{{ $items->count() }} PCS</td>
        </tr>
        @if($siNumber || $drNumber)
        <tr>
            <td class="excel-label">SI NUMBER:</td>
            <td style="font-weight: 700; text-align: center;">{{ $siNumber ?: '-' }}</td>
            <td class="excel-label">DR NUMBER:</td>
            <td style="font-weight: 700; text-align: center;">{{ $drNumber ?: '-' }}</td>
        </tr>
        @endif
        <tr>
            <td colspan="4" style="text-align: center; font-weight: 800; background: #F8FAFC; letter-spacing: 0.5px;">
                MATERIAL DESCRIPTION: {{ strtoupper($batch->brand ?? '') }} {{ strtoupper($batch->model ?? '') }}
            </td>
        </tr>
    </table>

    <!-- Items Subtable: Brand – Model - Serial number - Mac Address - Box # -->
    <table class="excel-table" style="border-top: none; margin-top: -1px;">
        <thead>
            <tr class="sub-header-row">
                <th class="col-no">NO.</th>
                <th>BRAND</th>
                <th>MODEL</th>
                <th>SERIAL NUMBER</th>
                <th>MAC ADDRESS</th>
                <th style="width: 80px;">BOX NO.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $idx => $item)
                <tr>
                    <td class="col-no">{{ $idx + 1 }}</td>
                    <td>{{ $item->brand ?: ($batch->brand ?? '') }}</td>
                    <td>{{ $item->model ?: ($batch->model ?? '') }}</td>
                    <td style="font-weight: 700; color: #00205B; text-align: center;">{{ $item->serial_number ?? '' }}</td>
                    <td style="text-align: center;">{{ $item->mac_address ?? '' }}</td>
                    <td style="text-align: center;">{{ $item->box_no ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 24px; color: #64748B;">No items in this batch report.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signatures block -->
    <div style="margin-top: 36px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; font-size: 10pt;">
        <div>
            <div style="border-bottom: 1px solid #000; height: 35px; margin-bottom: 5px;"></div>
            <div style="text-align: center; font-weight: bold;">Released / Inspected By (DFTM)</div>
        </div>
        <div>
            <div style="border-bottom: 1px solid #000; height: 35px; margin-bottom: 5px;"></div>
            <div style="text-align: center; font-weight: bold;">Received By (Client / Customer Signature)</div>
        </div>
    </div>
</div>

</body>
</html>
