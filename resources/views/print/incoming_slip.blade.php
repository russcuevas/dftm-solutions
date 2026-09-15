<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Incoming Transmittal Report - {{ $transmittal->transmittal_no }}</title>
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

        .brand-header {
            text-align: center;
            margin-bottom: 8px;
        }
        .brand-header img {
            height: 48px;
            max-width: 240px;
            object-fit: contain;
            margin-bottom: 4px;
        }

        .slip-title {
            text-align: center;
            font-size: 16pt;
            font-weight: 900;
            letter-spacing: 0.5px;
            margin: 6px 0 14px 0;
            color: #000000;
            text-transform: uppercase;
        }

        .excel-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .excel-table th, .excel-table td {
            border: 1px solid #000000;
            padding: 5px 8px;
            font-size: 10pt;
            line-height: 1.3;
        }

        .header-label {
            font-weight: 800;
            white-space: nowrap;
            width: 20%;
            background: #FFFFFF;
        }
        .header-val {
            font-weight: 700;
            width: 30%;
        }

        .model-banner {
            text-align: left;
            font-weight: 900;
            font-size: 11pt;
            letter-spacing: 0.5px;
            background: #F8FAFC;
            padding: 6px 10px;
            text-transform: uppercase;
            border: 1px solid #000000;
            margin-top: 14px;
            display: flex;
            justify-content: space-between;
        }

        .sub-header-row th {
            font-weight: 800;
            text-align: center;
            background: #FFFFFF;
            padding: 6px 8px;
        }

        .col-no {
            width: 45px;
            text-align: center;
            font-weight: bold;
        }
        .col-serial {
            width: 42%;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 10pt;
        }
        .col-mac {
            width: 42%;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 10pt;
        }
        .col-box {
            width: 16%;
            text-align: center;
            font-size: 10pt;
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
            .excel-table th, .excel-table td, .model-banner {
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
    <div class="slip-title">INCOMING TRANSMITTAL REPORT</div>

    <!-- Header Information Block (Excel-Style) -->
    <table class="excel-table">
        <tr>
            <td class="header-label">TRANSMITTAL NO:</td>
            <td class="header-val" style="font-family: monospace; font-size: 11pt;">{{ $transmittal->transmittal_no }}</td>
            <td class="header-label">COMPANY NAME:</td>
            <td class="header-val">{{ $transmittal->company_name ?? 'DFTM DIGITAL SOLUTIONS' }}</td>
        </tr>
        <tr>
            <td class="header-label">DATE RECEIVED:</td>
            <td class="header-val">{{ $transmittal->date_received ? $transmittal->date_received->format('Y-m-d') : '-' }}</td>
            <td class="header-label">TOTAL QUANTITY:</td>
            <td class="header-val" style="font-weight: 900; font-size: 11pt;">{{ $transmittal->total_quantity }} PCS</td>
        </tr>
    </table>

    <!-- Consolidated Report Grouped Per Model ("isang buo pero naka per model lang") -->
    @php
        $grandTotal = 0;
    @endphp

    @forelse($itemsByModel as $modelName => $modelItems)
        @php
            $firstItem = $modelItems->first();
            $brandName = $firstItem?->brand ?: ($transmittal->brand ?: 'N/A');
            $grandTotal += $modelItems->count();
        @endphp

        <div class="model-banner">
            <span>MODEL: {{ strtoupper($modelName) }} &nbsp;&bull;&nbsp; BRAND: {{ strtoupper($brandName) }}</span>
            <span>SUBTOTAL: {{ $modelItems->count() }} PCS</span>
        </div>

        <table class="excel-table" style="border-top: none;">
            <thead>
                <tr class="sub-header-row">
                    <th class="col-no">NO.</th>
                    <th class="col-serial">SERIAL NUMBER</th>
                    <th class="col-mac">MAC ADDRESS</th>
                    <th class="col-box">BOX NO.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($modelItems as $item)
                <tr>
                    <td class="col-no">{{ $loop->iteration }}</td>
                    <td class="col-serial">{{ $item->serial_number ?? '-' }}</td>
                    <td class="col-mac">{{ $item->mac_address ?? '-' }}</td>
                    <td class="col-box">{{ $item->box_no ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <div style="text-align: center; padding: 24px; border: 1px solid #000000; margin-top: 14px;">
            No units recorded for this transmittal.
        </div>
    @endforelse

    <!-- Grand Summary Footer Block -->
    <table class="excel-table" style="margin-top: 16px;">
        <tr>
            <td style="font-weight: 900; text-align: right; width: 75%; background: #F8FAFC;">
                CONSOLIDATED GRAND TOTAL RECEIVED:
            </td>
            <td style="font-weight: 900; text-align: center; width: 25%; font-size: 11pt; background: #F8FAFC;">
                {{ $transmittal->total_quantity }} PCS
            </td>
        </tr>
    </table>

    <!-- Signatures -->
    <table style="width: 100%; margin-top: 36px; border-collapse: collapse;">
        <tr>
            <td style="width: 50%; text-align: center; padding-right: 20px;">
                <div style="font-size: 9.5pt; font-weight: 700; margin-bottom: 40px;">RECEIVED & ENCODED BY:</div>
                <div style="border-bottom: 1px solid #000000; width: 80%; margin: 0 auto;"></div>
                <div style="font-size: 9pt; margin-top: 4px; font-weight: 600;">{{ $transmittal->encoder?->name ?? 'DFTM Staff' }}</div>
            </td>
            <td style="width: 50%; text-align: center; padding-left: 20px;">
                <div style="font-size: 9.5pt; font-weight: 700; margin-bottom: 40px;">CHECKED & VERIFIED BY:</div>
                <div style="border-bottom: 1px solid #000000; width: 80%; margin: 0 auto;"></div>
                <div style="font-size: 9pt; margin-top: 4px; font-weight: 600;">Supervisor / QA Personnel</div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
