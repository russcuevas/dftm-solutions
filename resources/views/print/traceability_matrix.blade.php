<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Repair Traceability Matrix - DFTM Solutions</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 12mm;
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
        .matrix-wrapper {
            width: 100%;
            max-width: 1150px;
            margin: 0 auto;
            background: #FFFFFF;
            padding: 24px 30px;
            border: 1px solid #CBD5E1;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        .print-btn-bar {
            max-width: 1150px;
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
            margin-bottom: 12px;
        }
        .brand-header img {
            height: 48px;
            max-width: 240px;
            object-fit: contain;
            margin-bottom: 4px;
        }

        .matrix-title {
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
            width: 18%;
            background: #FFFFFF;
        }
        .header-val {
            font-weight: 700;
            width: 32%;
        }

        .batch-banner {
            text-align: center;
            font-weight: 900;
            font-size: 11pt;
            letter-spacing: 0.5px;
            background: #FFFFFF;
            padding: 5px 8px;
            text-transform: uppercase;
        }

        .sub-header-row th {
            font-weight: 800;
            text-align: center;
            background: #FFFFFF;
            padding: 5px 8px;
        }

        .col-no {
            width: 45px;
            text-align: center;
            font-weight: bold;
        }

        @media print {
            body {
                background: #FFFFFF;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .matrix-wrapper {
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
            Print Matrix Report
        </button>
        <button onclick="window.close()" class="btn btn-outline">Close</button>
    </div>
</div>

<div class="matrix-wrapper">
    <!-- Top Logo -->
    <div class="brand-header">
        <img src="{{ asset('images/logo.png') }}" alt="DFTM Digital Solutions">
    </div>

    <!-- Main Title -->
    <div class="matrix-title">REPAIR TRACEABILITY MATRIX</div>

    @php
        $firstItem = $items->first();
        $slipObj = $selectedSlip ?? ($firstItem?->outgoingSlip);
        $batchObj = $selectedBatch ?? ($firstItem?->batch);

        $currentStatus = request('status');
        if (!$currentStatus) {
            $uniqueStatuses = $items->pluck('repair_status')->filter()->unique();
            if ($uniqueStatuses->count() === 1) {
                $currentStatus = $uniqueStatuses->first();
            } elseif ($uniqueStatuses->count() > 1) {
                $currentStatus = 'ALL';
            } else {
                $currentStatus = $firstItem->repair_status ?? ($items->count() > 0 ? 'In process' : '-');
            }
        }
        $statusVal = strtoupper($currentStatus ?: '-');
        $statusColor = match($statusVal) {
            'BER' => '#DC2626',
            'IN PROCESS', 'IN_PROCESS', 'PENDING' => '#D97706',
            'REPAIRED' => '#059669',
            default => '#000000'
        };
    @endphp

    <!-- Header Block Table (Matching Reference Image) -->
    <table class="excel-table">
        <tr>
            <td class="header-label">COMPANY NAME:</td>
            <td class="header-val">{{ $batchObj->company_name ?? ($slipObj->company_name ?? ($firstItem->company_name ?? 'DFTM DIGITAL SOLUTIONS')) }}</td>
            <td class="header-label">STATUS:</td>
            <td class="header-val" style="color: {{ $statusColor }}; font-weight: 800;">
                {{ $statusVal }}
            </td>
        </tr>
        <tr>
            <td class="header-label">DATE DELIVERED:</td>
            <td class="header-val">{{ ($batchObj && $batchObj->date_delivered ? $batchObj->date_delivered->format('Y-m-d') : null) ?? ($slipObj && $slipObj->date_delivered ? $slipObj->date_delivered->format('Y-m-d') : ($firstItem && $firstItem->date_delivered ? $firstItem->date_delivered->format('Y-m-d') : '-')) }}</td>
            <td class="header-label">TOTAL QUANTITY:</td>
            <td class="header-val">{{ $items->count() }} PCS</td>
        </tr>
    </table>

    <!-- Hierarchical Matrix Data Table (Matching Reference Image) -->
    <table class="excel-table" style="border-top: none; margin-top: -1px;">
        <thead>
            <!-- Level 1: NO., TECHNICAL DIAGNOSTIC (S), REPLACE PARTS, STATUS, MATERIAL DESCRIPTION, BOX NO. -->
            <tr class="sub-header-row">
                <th rowspan="4" class="col-no" style="vertical-align: middle;">NO.</th>
                <th rowspan="4" style="min-width: 170px; vertical-align: middle;">TECHNICAL DIAGNOSTIC (S)</th>
                <th rowspan="4" style="min-width: 150px; vertical-align: middle;">REPLACE PARTS</th>
                <th rowspan="4" style="min-width: 100px; vertical-align: middle;">STATUS</th>
                <th colspan="2" style="text-align: center; font-weight: 900; letter-spacing: 0.5px;">
                    MATERIAL DESCRIPTION
                </th>
                <th rowspan="4" style="width: 80px; vertical-align: middle;">BOX NO.</th>
            </tr>
            <!-- Level 2: BATCH # 1 -->
            <tr class="sub-header-row">
                <th colspan="2" style="text-align: center; font-weight: 900; font-size: 11pt; letter-spacing: 0.5px;">
                    @if($batchObj)
                        {{ str_contains(strtoupper($batchObj->batch_no), 'BATCH') ? strtoupper($batchObj->batch_no) : 'BATCH # ' . $batchObj->batch_no }}
                    @elseif($slipObj && $slipObj->batch_no)
                        {{ str_contains(strtoupper($slipObj->batch_no), 'BATCH') ? strtoupper($slipObj->batch_no) : 'BATCH # ' . $slipObj->batch_no }}
                    @else
                        BATCH # 1
                    @endif
                </th>
            </tr>
            <!-- Level 3: BRAND : HUAWEI | MODEL: ... -->
            <tr class="sub-header-row">
                <th style="text-align: center; width: 25%; font-weight: 800;">
                    BRAND : {{ strtoupper($batchObj->brand ?? ($slipObj->brand ?? ($firstItem->brand ?? 'HUAWEI'))) }}
                </th>
                <th style="text-align: center; width: 25%; font-weight: 800;">
                    MODEL: {{ strtoupper($batchObj->model ?? ($slipObj->model ?? ($firstItem->model ?? ''))) }}
                </th>
            </tr>
            <!-- Level 4: SERIAL NUMBER | MAC ADDRESS -->
            <tr class="sub-header-row">
                <th style="text-align: center;">SERIAL NUMBER</th>
                <th style="text-align: center;">MAC ADDRESS</th>
            </tr>
        </thead>
        <tbody>
            @if($items->count() === 0)
                <tr>
                    <td colspan="7" style="text-align: center; padding: 32px; font-weight: bold; color: #475569;">
                        No data available
                    </td>
                </tr>
            @else
                @php $count = max(20, $items->count()); @endphp
                @for($i = 0; $i < $count; $i++)
                    @php $item = $items[$i] ?? null; @endphp
                    <tr>
                        <td class="col-no">{{ $i + 1 }}</td>
                        <td style="font-weight: 700; color: #00205B;">{{ $item?->technical_diagnostic ?? '' }}</td>
                        <td style="font-weight: 600;">{{ $item?->replace_parts ?? '' }}</td>
                        <td style="text-align: center; font-weight: 800; color: {{ $item?->repair_status === 'BER' ? '#DC2626' : ($item?->repair_status === 'In process' ? '#D97706' : '#059669') }};">
                            {{ $item ? strtoupper($item->repair_status ?? 'In process') : '' }}
                        </td>
                        <td style="font-family: 'Consolas', monospace; font-weight: 700; color: #00205B;">{{ $item?->serial_number ?? '' }}</td>
                        <td style="font-family: 'Consolas', monospace;">{{ $item?->mac_address ?? '' }}</td>
                        <td style="text-align: center;">{{ $item?->box_no ?? '' }}</td>
                    </tr>
                @endfor
            @endif
        </tbody>
    </table>

    <!-- Signature Block -->
    <div style="margin-top: 28px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; font-size: 10pt;">
        <div>
            <div style="border-bottom: 1px solid #000; height: 35px; margin-bottom: 5px;"></div>
            <div style="text-align: center; font-weight: bold;">Quality Control / Lead Technician</div>
        </div>
        <div>
            <div style="border-bottom: 1px solid #000; height: 35px; margin-bottom: 5px;"></div>
            <div style="text-align: center; font-weight: bold;">Operations Manager Approval</div>
        </div>
    </div>
</div>

</body>
</html>
