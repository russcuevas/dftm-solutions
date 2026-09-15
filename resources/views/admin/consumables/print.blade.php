<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DFTM Consumable Inventory - {{ $monthName }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @page {
            size: landscape;
            margin: 8mm 6mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }
        body {
            background-color: #f8fafc;
            color: #000;
            font-size: 8.5pt;
            padding: 15px;
        }
        .no-print-bar {
            background: #00205B;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .btn-print {
            background: #0284C7;
            color: #fff;
            border: none;
            padding: 8px 16px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-close {
            background: transparent;
            color: #cbd5e1;
            border: 1px solid #475569;
            padding: 8px 14px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .report-header {
            text-align: center;
            margin-bottom: 12px;
        }
        .report-header h2 {
            font-size: 14pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #00205B;
        }
        .report-header h3 {
            font-size: 11pt;
            color: #334155;
            font-weight: normal;
            margin-top: 2px;
        }
        .inventory-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            background: #fff;
        }
        .inventory-table th, 
        .inventory-table td {
            border: 1px solid #94a3b8;
            padding: 3px 4px;
            white-space: nowrap;
        }
        .inventory-table thead th {
            background-color: #00205B;
            color: #fff;
            text-align: center;
            font-weight: bold;
        }
        .sub-in {
            background-color: #064E3B !important;
            color: #fff !important;
            min-width: 18px;
        }
        .sub-out {
            background-color: #7F1D1D !important;
            color: #fff !important;
            min-width: 18px;
        }
        .cat-row {
            background-color: #e2e8f0;
            font-weight: bold;
            font-size: 8.5pt;
            text-transform: uppercase;
            padding: 4px 6px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .sum-col {
            background-color: #f1f5f9;
            font-weight: bold;
        }
        .grand-total {
            background-color: #00205B;
            color: #fff;
            font-weight: bold;
        }
        .grand-total td {
            border-color: #00205B;
        }
        .footer-signatures {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }
        .sig-block {
            width: 200px;
            text-align: center;
        }
        .sig-line {
            border-top: 1px solid #000;
            margin-top: 35px;
            padding-top: 4px;
            font-weight: bold;
        }
        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                background: #fff;
                padding: 0;
            }
            .inventory-table {
                font-size: 6.8pt;
            }
            .inventory-table th, .inventory-table td {
                padding: 2px 3px;
            }
        }
    </style>
</head>
<body>
    <div class="no-print-bar">
        <div>
            <strong>DFTM Solutions</strong> — Monthly Consumable Inventory Sheet ({{ $monthName }})
        </div>
        <div style="display: flex; gap: 10px;">
            <button class="btn-print" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Document
            </button>
            <a href="javascript:window.close()" class="btn-close">Close</a>
        </div>
    </div>

    <div class="report-header">
        <h2>DFTM DIGITAL SOLUTIONS</h2>
        <h3>CONSUMABLE & SUPPLIES MONTHLY INVENTORY REPORT — {{ strtoupper($monthName) }}</h3>
    </div>

    <table class="inventory-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 180px;">DESCRIPTION</th>
                <th rowspan="2" style="width: 50px;">UNIT</th>
                <th rowspan="2" style="width: 55px;">COST</th>
                <th rowspan="2" style="width: 55px;">BEGINNING</th>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    <th colspan="2">{{ $d }}-{{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('M') }}</th>
                @endfor
                <th rowspan="2" style="width: 55px;">TOTAL IN</th>
                <th rowspan="2" style="width: 55px;">TOTAL OUT</th>
                <th rowspan="2" style="width: 55px;">REMAINING</th>
                <th rowspan="2" style="width: 75px;">AMOUNT</th>
            </tr>
            <tr>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    <th class="sub-in">IN</th>
                    <th class="sub-out">OUT</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($matrixData as $catId => $data)
                @if(count($data['items']) > 0)
                    <tr>
                        <td colspan="{{ 4 + ($daysInMonth * 2) + 4 }}" class="cat-row">
                            {{ $data['category']->name }}
                        </td>
                    </tr>
                    @foreach($data['items'] as $itemRow)
                        @php
                            $item = $itemRow['item'];
                            $beg = $itemRow['beginning'];
                            $totIn = $itemRow['total_in'];
                            $totOut = $itemRow['total_out'];
                            $rem = $itemRow['remaining'];
                            $amt = $itemRow['amount'];
                            $dailyLogs = $itemRow['daily'];
                        @endphp
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td class="text-center">{{ $item->unit }}</td>
                            <td class="text-right">{{ $item->cost > 0 ? number_format($item->cost, 2) : '0.00' }}</td>
                            <td class="text-center">{{ $beg > 0 ? number_format($beg) : '0' }}</td>

                            @for($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $inVal = isset($dailyLogs[$d]['in']) && $dailyLogs[$d]['in'] > 0 ? $dailyLogs[$d]['in'] : '';
                                    $outVal = isset($dailyLogs[$d]['out']) && $dailyLogs[$d]['out'] > 0 ? $dailyLogs[$d]['out'] : '';
                                @endphp
                                <td class="text-center" style="{{ $inVal ? 'font-weight:bold; background:#ecfdf5;' : '' }}">{{ $inVal }}</td>
                                <td class="text-center" style="{{ $outVal ? 'font-weight:bold; background:#fef2f2;' : '' }}">{{ $outVal }}</td>
                            @endfor

                            <td class="text-center sum-col">{{ number_format($totIn) }}</td>
                            <td class="text-center sum-col">{{ number_format($totOut) }}</td>
                            <td class="text-center sum-col">{{ number_format($rem) }}</td>
                            <td class="text-right sum-col">{{ $amt > 0 ? '₱' . number_format($amt, 2) : '0.00' }}</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr class="grand-total">
                <td colspan="3" style="text-align: right; padding-right: 8px;">GRAND TOTAL</td>
                <td class="text-center">{{ number_format($totalBeginningSum) }}</td>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    <td></td>
                    <td></td>
                @endfor
                <td class="text-center">{{ number_format($totalInSum) }}</td>
                <td class="text-center">{{ number_format($totalOutSum) }}</td>
                <td class="text-center">{{ number_format($totalRemainingSum) }}</td>
                <td class="text-right">₱{{ number_format($totalValuation, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-signatures">
        <div class="sig-block">
            <div class="sig-line">Prepared By (Encoder)</div>
        </div>
        <div class="sig-block">
            <div class="sig-line">Checked By (Inventory Head)</div>
        </div>
        <div class="sig-block">
            <div class="sig-line">Approved By (Management)</div>
        </div>
    </div>
</body>
</html>
