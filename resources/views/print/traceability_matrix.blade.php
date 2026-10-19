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
            font-family: Arial, Helvetica, sans-serif;
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
            height: 60px;
            max-width: 360px;
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
            font-family: Arial, Helvetica, sans-serif;
        }
        .excel-table th, .excel-table td {
            border: 1px solid #000000;
            padding: 6px 8px;
            font-size: 10pt;
            line-height: 1.3;
            text-align: center;
        }

        .header-label {
            font-weight: 800;
            white-space: nowrap;
            width: 18%;
            background: #FFFFFF;
            text-align: center;
        }
        .header-val {
            font-weight: 700;
            width: 32%;
            text-align: center;
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
            padding: 6px 8px;
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
                font-family: Arial, Helvetica, sans-serif !important;
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
                text-align: center;
            }
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
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
        <button type="button" id="btnExportExcel" class="btn" style="background: #107C41; color: #FFFFFF; font-weight: 700; border: none; box-shadow: 0 2px 6px rgba(16,124,65,0.3);">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M5.884 6.68a.5.5 0 1 0-.768.64L7.349 10l-2.233 2.68a.5.5 0 0 0 .768.64L8 10.748l2.116 2.572a.5.5 0 0 0 .768-.64L8.651 10l2.233-2.68a.5.5 0 0 0-.768-.64L8 9.252 5.884 6.68z"/><path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/></svg>
            Export to Excel (.xlsx)
        </button>
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
                $currentStatus = $firstItem->repair_status ?? ($items->count() > 0 ? 'GOOD' : '-');
            }
        }
        $statusVal = strtoupper($currentStatus ?: '-');
        $statusColor = match($statusVal) {
            'BER' => '#DC2626',
            'IN PROCESS', 'IN_PROCESS', 'PENDING' => '#D97706',
            'REPAIRED', 'GOOD' => '#059669',
            default => '#000000'
        };

        $batchHeaderStr = 'BATCH # 1';
        if ($batchObj) {
            $batchHeaderStr = str_contains(strtoupper($batchObj->batch_no), 'BATCH') ? strtoupper($batchObj->batch_no) : 'BATCH # ' . $batchObj->batch_no;
        } elseif ($slipObj && $slipObj->batch_no) {
            $batchHeaderStr = str_contains(strtoupper($slipObj->batch_no), 'BATCH') ? strtoupper($slipObj->batch_no) : 'BATCH # ' . $slipObj->batch_no;
        }

        $brandStr = strtoupper($batchObj->brand ?? ($slipObj->brand ?? ($firstItem->brand ?? 'HUAWEI')));
        $modelStr = strtoupper($batchObj->model ?? ($slipObj->model ?? ($firstItem->model ?? '')));
        $companyStr = $batchObj->company_name ?? ($slipObj->company_name ?? ($firstItem->company_name ?? 'DFTM DIGITAL SOLUTIONS'));
        $dateDelivStr = ($batchObj && $batchObj->date_delivered ? $batchObj->date_delivered->format('Y-m-d') : null) ?? ($slipObj && $slipObj->date_delivered ? $slipObj->date_delivered->format('Y-m-d') : ($firstItem && $firstItem->date_delivered ? $firstItem->date_delivered->format('Y-m-d') : '-'));

        $exportData = [
            'company_name' => $companyStr,
            'status' => $statusVal,
            'date_delivered' => $dateDelivStr,
            'total_quantity' => $items->count(),
            'batch_header' => $batchHeaderStr,
            'brand' => $brandStr,
            'model' => $modelStr,
            'items' => $items->map(function($it, $idx) {
                return [
                    'no' => $idx + 1,
                    'technical_diagnostic' => $it->technical_diagnostic ?? '-',
                    'replace_parts' => $it->replace_parts ?? '-',
                    'repair_status' => strtoupper($it->repair_status ?? 'GOOD'),
                    'serial_number' => $it->serial_number ?? '-',
                    'mac_address' => $it->mac_address ?? '-',
                    'box_no' => $it->box_no ?? '-',
                ];
            })->values()
        ];
    @endphp

    <!-- Header Block Table (Matching Reference Image) -->
    <table class="excel-table">
        <tr>
            <td class="header-label">COMPANY NAME:</td>
            <td class="header-val">{{ $companyStr }}</td>
            <td class="header-label">STATUS:</td>
            <td class="header-val" style="color: {{ $statusColor }}; font-weight: 800;">
                {{ $statusVal }}
            </td>
        </tr>
        <tr>
            <td class="header-label">DATE DELIVERED:</td>
            <td class="header-val">{{ $dateDelivStr }}</td>
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
                    {{ $batchHeaderStr }}
                </th>
            </tr>
            <!-- Level 3: BRAND : HUAWEI | MODEL: ... -->
            <tr class="sub-header-row">
                <th style="text-align: center; width: 25%; font-weight: 800;">
                    BRAND : {{ $brandStr }}
                </th>
                <th style="text-align: center; width: 25%; font-weight: 800;">
                    MODEL: {{ $modelStr }}
                </th>
            </tr>
            <!-- Level 4: SERIAL NUMBER | MAC ADDRESS -->
            <tr class="sub-header-row">
                <th style="text-align: center;">SERIAL NUMBER</th>
                <th style="text-align: center;">MAC ADDRESS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $idx => $item)
                <tr>
                    <td class="col-no">{{ $idx + 1 }}</td>
                    <td style="font-weight: 700; color: #00205B; text-align: center;">{{ $item->technical_diagnostic ?? '' }}</td>
                    <td style="font-weight: 600; text-align: center;">{{ $item->replace_parts ?? '' }}</td>
                    <td style="text-align: center; font-weight: 800; color: {{ $item->repair_status === 'BER' ? '#DC2626' : (in_array($item->repair_status, ['In process', 'IN_PROCESS', 'PENDING']) ? '#D97706' : '#059669') }};">
                        {{ strtoupper($item->repair_status ?? 'GOOD') }}
                    </td>
                    <td style="font-weight: 700; color: #00205B; text-align: center;">{{ $item->serial_number ?? '' }}</td>
                    <td style="text-align: center;">{{ $item->mac_address ?? '' }}</td>
                    <td style="text-align: center;">{{ $item->box_no ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 32px; font-weight: bold; color: #475569;">
                        No data available
                    </td>
                </tr>
            @endforelse
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

<!-- Local ExcelJS script with CDN fallback -->
<script src="{{ asset('js/exceljs.min.js') }}"></script>
<script>
    if (typeof ExcelJS === 'undefined') {
        document.write('<script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"><\/script>');
    }
</script>

<script>
    const matrixData = @json($exportData);

    async function getLogoBase64() {
        try {
            const img = document.querySelector('.brand-header img') || document.querySelector('img[alt="DFTM Digital Solutions"]');
            if (!img) return null;

            if (img.complete && img.naturalWidth > 0) {
                const canvas = document.createElement('canvas');
                canvas.width = img.naturalWidth;
                canvas.height = img.naturalHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0);
                return canvas.toDataURL('image/png').split(',')[1];
            }

            const response = await fetch(img.src);
            const blob = await response.blob();
            return new Promise((resolve) => {
                const reader = new FileReader();
                reader.onloadend = () => {
                    const base64data = reader.result ? reader.result.split(',')[1] : null;
                    resolve(base64data);
                };
                reader.onerror = () => resolve(null);
                reader.readAsDataURL(blob);
            });
        } catch (e) {
            console.warn('Could not extract logo base64:', e);
            return null;
        }
    }

    async function exportToExcel() {
        const btn = document.getElementById('btnExportExcel');
        const origHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="animation: spin 1s linear infinite;">
                    <path d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                </svg>
                Generating Excel...
            `;
        }

        try {
            if (typeof ExcelJS === 'undefined') {
                throw new Error('ExcelJS library is not loaded. Please check your connection.');
            }

            const workbook = new ExcelJS.Workbook();
            workbook.creator = 'DFTM Digital Solutions';
            workbook.created = new Date();

            const worksheet = workbook.addWorksheet('Traceability Matrix', {
                pageSetup: {
                    paperSize: 9, // A4
                    orientation: 'landscape',
                    fitToPage: true,
                    fitToWidth: 1,
                    fitToHeight: 0,
                    margins: {
                        left: 0.4,
                        right: 0.4,
                        top: 0.4,
                        bottom: 0.4,
                        header: 0.2,
                        footer: 0.2
                    }
                },
                views: [{ showGridLines: true }]
            });

            // 7 columns layout for Landscape
            worksheet.columns = [
                { key: 'colA', width: 8 },  // NO.
                { key: 'colB', width: 28 }, // TECHNICAL DIAGNOSTIC (S)
                { key: 'colC', width: 24 }, // REPLACE PARTS
                { key: 'colD', width: 16 }, // STATUS
                { key: 'colE', width: 26 }, // SERIAL NUMBER
                { key: 'colF', width: 26 }, // MAC ADDRESS
                { key: 'colG', width: 14 }  // BOX NO.
            ];

            const thinBorder = {
                top: { style: 'thin', color: { argb: 'FF000000' } },
                left: { style: 'thin', color: { argb: 'FF000000' } },
                bottom: { style: 'thin', color: { argb: 'FF000000' } },
                right: { style: 'thin', color: { argb: 'FF000000' } }
            };

            const greyFill = {
                type: 'pattern',
                pattern: 'solid',
                fgColor: { argb: 'FFF8FAFC' }
            };

            function setRangeBorder(startRow, startCol, endRow, endCol) {
                for (let r = startRow; r <= endRow; r++) {
                    for (let c = startCol; c <= endCol; c++) {
                        worksheet.getCell(r, c).border = thinBorder;
                    }
                }
            }

            // 1. Logo (Top Center)
            const logoBase64 = await getLogoBase64();
            if (logoBase64) {
                const imgId = workbook.addImage({
                    base64: logoBase64,
                    extension: 'png'
                });
                worksheet.addImage(imgId, {
                    tl: { col: 2.2, row: 0.2 },
                    ext: { width: 300, height: 50 }
                });
                worksheet.addRow([]); worksheet.getRow(1).height = 18;
                worksheet.addRow([]); worksheet.getRow(2).height = 18;
                worksheet.addRow([]); worksheet.getRow(3).height = 18;
            } else {
                const r = worksheet.addRow(['DFTM DIGITAL SOLUTIONS']);
                worksheet.mergeCells(`A${r.number}:G${r.number}`);
                r.height = 26;
                r.getCell(1).font = { name: 'Arial', size: 15, bold: true, color: { argb: 'FF00205B' } };
                r.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };
            }

            // 2. Document Title: REPAIR TRACEABILITY MATRIX
            const titleRow = worksheet.addRow(['REPAIR TRACEABILITY MATRIX']);
            worksheet.mergeCells(`A${titleRow.number}:G${titleRow.number}`);
            titleRow.height = 26;
            titleRow.getCell(1).font = { name: 'Arial', size: 15, bold: true, color: { argb: 'FF000000' } };
            titleRow.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };

            // Spacer
            const sp1 = worksheet.addRow([]);
            sp1.height = 6;

            // 3. Header Block Table (Metadata)
            // Determine Status Color
            let statusColorArgb = 'FF000000';
            const sUpper = (matrixData.status || '').toUpperCase();
            if (sUpper === 'BER') {
                statusColorArgb = 'FFDC2626';
            } else if (sUpper.includes('PROCESS') || sUpper === 'PENDING') {
                statusColorArgb = 'FFD97706';
            } else if (sUpper === 'GOOD' || sUpper === 'REPAIRED') {
                statusColorArgb = 'FF059669';
            }

            // Row 1: COMPANY NAME (Cols A-D) | STATUS (Cols E-G)
            const meta1 = worksheet.addRow([
                'COMPANY NAME:',
                '',
                matrixData.company_name || '-',
                '',
                'STATUS:',
                matrixData.status || '-',
                ''
            ]);
            meta1.height = 22;
            const m1 = meta1.number;
            worksheet.mergeCells(`A${m1}:B${m1}`);
            worksheet.mergeCells(`C${m1}:D${m1}`);
            worksheet.mergeCells(`F${m1}:G${m1}`);
            setRangeBorder(m1, 1, m1, 7);

            meta1.getCell(1).font = { name: 'Arial', size: 9.5, bold: true };
            meta1.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };

            meta1.getCell(3).font = { name: 'Arial', size: 10, bold: true };
            meta1.getCell(3).alignment = { horizontal: 'center', vertical: 'middle' };

            meta1.getCell(5).font = { name: 'Arial', size: 9.5, bold: true };
            meta1.getCell(5).alignment = { horizontal: 'center', vertical: 'middle' };

            meta1.getCell(6).font = { name: 'Arial', size: 10.5, bold: true, color: { argb: statusColorArgb } };
            meta1.getCell(6).alignment = { horizontal: 'center', vertical: 'middle' };

            // Row 2: DATE DELIVERED (Cols A-D) | TOTAL QUANTITY (Cols E-G)
            const meta2 = worksheet.addRow([
                'DATE DELIVERED:',
                '',
                matrixData.date_delivered || '-',
                '',
                'TOTAL QUANTITY:',
                (matrixData.total_quantity || 0) + ' PCS',
                ''
            ]);
            meta2.height = 22;
            const m2 = meta2.number;
            worksheet.mergeCells(`A${m2}:B${m2}`);
            worksheet.mergeCells(`C${m2}:D${m2}`);
            worksheet.mergeCells(`F${m2}:G${m2}`);
            setRangeBorder(m2, 1, m2, 7);

            meta2.getCell(1).font = { name: 'Arial', size: 9.5, bold: true };
            meta2.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };

            meta2.getCell(3).font = { name: 'Arial', size: 10, bold: false };
            meta2.getCell(3).alignment = { horizontal: 'center', vertical: 'middle' };

            meta2.getCell(5).font = { name: 'Arial', size: 9.5, bold: true };
            meta2.getCell(5).alignment = { horizontal: 'center', vertical: 'middle' };

            meta2.getCell(6).font = { name: 'Arial', size: 10.5, bold: true };
            meta2.getCell(6).alignment = { horizontal: 'center', vertical: 'middle' };

            // 4. Hierarchical Multi-Level Matrix Header (Rows 1 to 4)
            // Level 1: NO. | TECHNICAL DIAGNOSTIC (S) | REPLACE PARTS | STATUS | MATERIAL DESCRIPTION | | BOX NO.
            const hRow1 = worksheet.addRow([
                'NO.',
                'TECHNICAL DIAGNOSTIC (S)',
                'REPLACE PARTS',
                'STATUS',
                'MATERIAL DESCRIPTION',
                '',
                'BOX NO.'
            ]);
            hRow1.height = 22;
            const hr1 = hRow1.number;

            // Level 2: Batch Header across Cols E-F
            const hRow2 = worksheet.addRow([
                '',
                '',
                '',
                '',
                matrixData.batch_header || 'BATCH # 1',
                '',
                ''
            ]);
            hRow2.height = 22;
            const hr2 = hRow2.number;

            // Level 3: BRAND : ... | MODEL: ...
            const hRow3 = worksheet.addRow([
                '',
                '',
                '',
                '',
                `BRAND : ${matrixData.brand || ''}`.trim(),
                `MODEL: ${matrixData.model || ''}`.trim(),
                ''
            ]);
            hRow3.height = 22;
            const hr3 = hRow3.number;

            // Level 4: SERIAL NUMBER | MAC ADDRESS
            const hRow4 = worksheet.addRow([
                '',
                '',
                '',
                '',
                'SERIAL NUMBER',
                'MAC ADDRESS',
                ''
            ]);
            hRow4.height = 22;
            const hr4 = hRow4.number;

            // Merging Hierarchical Header Cells
            worksheet.mergeCells(`A${hr1}:A${hr4}`);
            worksheet.mergeCells(`B${hr1}:B${hr4}`);
            worksheet.mergeCells(`C${hr1}:C${hr4}`);
            worksheet.mergeCells(`D${hr1}:D${hr4}`);
            worksheet.mergeCells(`G${hr1}:G${hr4}`);

            worksheet.mergeCells(`E${hr1}:F${hr1}`);
            worksheet.mergeCells(`E${hr2}:F${hr2}`);

            setRangeBorder(hr1, 1, hr4, 7);

            for (let r = hr1; r <= hr4; r++) {
                const curRow = worksheet.getRow(r);
                for (let c = 1; c <= 7; c++) {
                    const cell = curRow.getCell(c);
                    cell.font = { name: 'Arial', size: 9.5, bold: true };
                    cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
                }
            }

            // 5. Data Rows
            if (matrixData.items && matrixData.items.length > 0) {
                matrixData.items.forEach(item => {
                    const row = worksheet.addRow([
                        item.no,
                        String(item.technical_diagnostic || '-'),
                        String(item.replace_parts || '-'),
                        String(item.repair_status || 'GOOD'),
                        String(item.serial_number || '-'),
                        String(item.mac_address || '-'),
                        String(item.box_no || '-')
                    ]);
                    row.height = 20;
                    const rNum = row.number;
                    setRangeBorder(rNum, 1, rNum, 7);

                    // Col 1: NO.
                    row.getCell(1).font = { name: 'Arial', size: 9.5, bold: true };
                    row.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };

                    // Col 2: TECHNICAL DIAGNOSTIC (S)
                    row.getCell(2).font = { name: 'Arial', size: 9.5, bold: true, color: { argb: 'FF00205B' } };
                    row.getCell(2).alignment = { horizontal: 'center', vertical: 'middle' };

                    // Col 3: REPLACE PARTS
                    row.getCell(3).font = { name: 'Arial', size: 9.5, bold: false };
                    row.getCell(3).alignment = { horizontal: 'center', vertical: 'middle' };

                    // Col 4: STATUS (Color-coded)
                    let stColor = 'FF059669';
                    const itSt = (item.repair_status || '').toUpperCase();
                    if (itSt === 'BER') {
                        stColor = 'FFDC2626';
                    } else if (itSt.includes('PROCESS') || itSt === 'PENDING') {
                        stColor = 'FFD97706';
                    }
                    row.getCell(4).font = { name: 'Arial', size: 9.5, bold: true, color: { argb: stColor } };
                    row.getCell(4).alignment = { horizontal: 'center', vertical: 'middle' };

                    // Col 5: SERIAL NUMBER (Bold, Navy, Text Format)
                    row.getCell(5).font = { name: 'Arial', size: 9.5, bold: true, color: { argb: 'FF00205B' } };
                    row.getCell(5).alignment = { horizontal: 'center', vertical: 'middle' };
                    row.getCell(5).numFmt = '@';

                    // Col 6: MAC ADDRESS (Text Format)
                    row.getCell(6).font = { name: 'Arial', size: 9.5 };
                    row.getCell(6).alignment = { horizontal: 'center', vertical: 'middle' };
                    row.getCell(6).numFmt = '@';

                    // Col 7: BOX NO.
                    row.getCell(7).font = { name: 'Arial', size: 9.5 };
                    row.getCell(7).alignment = { horizontal: 'center', vertical: 'middle' };
                    row.getCell(7).numFmt = '@';
                });
            } else {
                const emptyRow = worksheet.addRow(['No data available', '', '', '', '', '', '']);
                emptyRow.height = 26;
                const eNum = emptyRow.number;
                worksheet.mergeCells(`A${eNum}:G${eNum}`);
                setRangeBorder(eNum, 1, eNum, 7);
                emptyRow.getCell(1).font = { name: 'Arial', size: 10, italic: true };
                emptyRow.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };
            }

            // 6. Signature Block
            worksheet.addRow([]); worksheet.lastRow.height = 16;
            worksheet.addRow([]); worksheet.lastRow.height = 16;

            const sigLines = worksheet.addRow([
                '________________________________________',
                '',
                '',
                '',
                '________________________________________',
                '',
                ''
            ]);
            sigLines.height = 16;
            const slNum = sigLines.number;
            worksheet.mergeCells(`A${slNum}:C${slNum}`);
            worksheet.mergeCells(`E${slNum}:G${slNum}`);
            sigLines.getCell(1).font = { name: 'Arial', size: 9, bold: false };
            sigLines.getCell(1).alignment = { horizontal: 'center', vertical: 'top' };
            sigLines.getCell(5).font = { name: 'Arial', size: 9, bold: false };
            sigLines.getCell(5).alignment = { horizontal: 'center', vertical: 'top' };

            const sigLabels = worksheet.addRow([
                'Quality Control / Lead Technician',
                '',
                '',
                '',
                'Operations Manager Approval',
                '',
                ''
            ]);
            sigLabels.height = 20;
            const slabNum = sigLabels.number;
            worksheet.mergeCells(`A${slabNum}:C${slabNum}`);
            worksheet.mergeCells(`E${slabNum}:G${slabNum}`);
            sigLabels.getCell(1).font = { name: 'Arial', size: 9.5, bold: true };
            sigLabels.getCell(1).alignment = { horizontal: 'center', vertical: 'top' };
            sigLabels.getCell(5).font = { name: 'Arial', size: 9.5, bold: true };
            sigLabels.getCell(5).alignment = { horizontal: 'center', vertical: 'top' };

            // 7. Write to Buffer and Trigger Download
            const buffer = await workbook.xlsx.writeBuffer();
            const blob = new Blob([buffer], {
                type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            });
            const filename = `Repair_Traceability_Matrix_${matrixData.status || 'Report'}.xlsx`;

            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            setTimeout(() => {
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            }, 200);

        } catch (err) {
            console.error('Error generating Excel file:', err);
            alert('Nagka-problema sa pag-export sa Excel: ' + err.message);
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('btnExportExcel');
        if (btn) {
            btn.addEventListener('click', exportToExcel);
        }

        // Auto-export if URL has ?export=excel or ?export=1
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('export') === 'excel' || urlParams.get('export') === '1') {
            setTimeout(exportToExcel, 400);
        }
    });
</script>

</body>
</html>
