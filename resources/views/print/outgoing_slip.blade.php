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
        <strong style="color: #00205B;">DFTM DIGITAL SOLUTIONS</strong>
    </div>
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 4px;">
            <label style="font-size: 0.8rem; font-weight: 700; color: #475569;">SI #:</label>
            <input type="text" id="customSiInput" placeholder="SI Number" value="{{ $siNumber }}" oninput="document.getElementById('displaySiNumber').innerText = this.value || '-'" style="width: 120px; padding: 4px 8px; font-size: 0.85rem; border: 1px solid #CBD5E1; border-radius: 4px;">
        </div>
        <div style="display: flex; align-items: center; gap: 4px;">
            <label style="font-size: 0.8rem; font-weight: 700; color: #475569;">DR #:</label>
            <input type="text" id="customDrInput" placeholder="DR Number" value="{{ $drNumber }}" oninput="document.getElementById('displayDrNumber').innerText = this.value || '-'" style="width: 120px; padding: 4px 8px; font-size: 0.85rem; border: 1px solid #CBD5E1; border-radius: 4px;">
        </div>
        <button type="button" id="btnExportExcel" class="btn" style="background: #107C41; color: #FFFFFF; font-weight: 700; border: none; box-shadow: 0 2px 6px rgba(16,124,65,0.3);">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M5.884 6.68a.5.5 0 1 0-.768.64L7.349 10l-2.233 2.68a.5.5 0 0 0 .768.64L8 10.748l2.116 2.572a.5.5 0 0 0 .768-.64L8.651 10l2.233-2.68a.5.5 0 0 0-.768-.64L8 9.252 5.884 6.68z"/><path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/></svg>
            Export to Excel (.xlsx)
        </button>
        <button onclick="window.print()" class="btn btn-primary">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/></svg>
            Print Outgoing Slip
        </button>
        <button onclick="window.close()" class="btn btn-outline">Close</button>
    </div>
</div>

<div class="slip-wrapper">
    <div style="text-align: center; margin-bottom: 12px;" class="brand-header">
        <img src="{{ asset('images/logo.png') }}" alt="DFTM Logo" style="height: 80px; max-width: 460px; object-fit: contain;">
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
        <tr>
            <td class="excel-label">SI NUMBER:</td>
            <td style="font-weight: 700; text-align: center;"><span id="displaySiNumber">{{ $siNumber ?: '-' }}</span></td>
            <td class="excel-label">DR NUMBER:</td>
            <td style="font-weight: 700; text-align: center;"><span id="displayDrNumber">{{ $drNumber ?: '-' }}</span></td>
        </tr>
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

@php
    $exportData = [
        'company_name' => $customerName ?: ($batch->company_name ?? 'DFTM DIGITAL SOLUTIONS'),
        'batch_no' => $batch->batch_no ?? '-',
        'date_released' => $dateReleased ?: now()->format('Y-m-d'),
        'total_quantity' => $items->count(),
        'si_number' => $siNumber ?: '-',
        'dr_number' => $drNumber ?: '-',
        'brand' => strtoupper($batch->brand ?? ''),
        'model' => strtoupper($batch->model ?? ''),
        'items' => $items->map(function($it, $idx) use ($batch) {
            return [
                'no' => $idx + 1,
                'brand' => $it->brand ?: ($batch->brand ?? '-'),
                'model' => $it->model ?: ($batch->model ?? '-'),
                'serial_number' => $it->serial_number ?? '-',
                'mac_address' => $it->mac_address ?? '-',
                'box_no' => $it->box_no ?? '-',
            ];
        })->values()
    ];
@endphp

<!-- Local ExcelJS script with CDN fallback -->
<script src="{{ asset('js/exceljs.min.js') }}"></script>
<script>
    if (typeof ExcelJS === 'undefined') {
        document.write('<script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"><\/script>');
    }
</script>

<script>
    const slipData = @json($exportData);

    async function getLogoBase64() {
        try {
            const img = document.querySelector('.brand-header img') || document.querySelector('img[alt="DFTM Logo"]');
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

            // Capture live values of SI and DR in case user updated them on page
            const liveSi = document.getElementById('customSiInput')?.value || slipData.si_number || '-';
            const liveDr = document.getElementById('customDrInput')?.value || slipData.dr_number || '-';

            const workbook = new ExcelJS.Workbook();
            workbook.creator = 'DFTM Digital Solutions';
            workbook.created = new Date();

            const worksheet = workbook.addWorksheet('Outgoing Repair Slip', {
                pageSetup: {
                    paperSize: 9, // A4
                    orientation: 'portrait',
                    fitToPage: true,
                    fitToWidth: 1,
                    fitToHeight: 0,
                    margins: {
                        left: 0.45,
                        right: 0.45,
                        top: 0.5,
                        bottom: 0.5,
                        header: 0.2,
                        footer: 0.2
                    }
                },
                views: [{ showGridLines: true }]
            });

            // 6-column layout: A(NO.), B(BRAND), C(MODEL), D(SERIAL), E(MAC), F(BOX NO.)
            worksheet.columns = [
                { key: 'colA', width: 8 },  // NO.
                { key: 'colB', width: 18 }, // BRAND
                { key: 'colC', width: 18 }, // MODEL
                { key: 'colD', width: 28 }, // SERIAL NUMBER
                { key: 'colE', width: 26 }, // MAC ADDRESS
                { key: 'colF', width: 14 }  // BOX NO.
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

            const navyFill = {
                type: 'pattern',
                pattern: 'solid',
                fgColor: { argb: 'FF00205B' }
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
                    tl: { col: 1.6, row: 0.2 },
                    ext: { width: 300, height: 60 }
                });
                worksheet.addRow([]); worksheet.getRow(1).height = 18;
                worksheet.addRow([]); worksheet.getRow(2).height = 18;
                worksheet.addRow([]); worksheet.getRow(3).height = 18;
            } else {
                const r = worksheet.addRow(['DFTM DIGITAL SOLUTIONS']);
                worksheet.mergeCells(`A${r.number}:F${r.number}`);
                r.height = 26;
                r.getCell(1).font = { name: 'Arial', size: 15, bold: true, color: { argb: 'FF00205B' } };
                r.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };
            }

            // 2. Title Row: OUTGOING REPAIR SLIP (Navy background, White text)
            const titleRow = worksheet.addRow(['OUTGOING REPAIR SLIP', '', '', '', '', '']);
            titleRow.height = 28;
            const tNum = titleRow.number;
            worksheet.mergeCells(`A${tNum}:F${tNum}`);
            setRangeBorder(tNum, 1, tNum, 6);
            titleRow.getCell(1).font = { name: 'Arial', size: 14.5, bold: true, color: { argb: 'FFFFFFFF' } };
            titleRow.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };
            titleRow.getCell(1).fill = navyFill;

            // 3. Metadata Header (Excel Table with solid black borders)
            // Row 1: COMPANY NAME (Cols A to C) | BATCH NUMBER (Cols D to F)
            const meta1 = worksheet.addRow([
                'COMPANY NAME:',
                slipData.company_name || '-',
                '',
                'BATCH NUMBER:',
                slipData.batch_no || '-',
                ''
            ]);
            meta1.height = 22;
            const m1Num = meta1.number;
            worksheet.mergeCells(`B${m1Num}:C${m1Num}`);
            worksheet.mergeCells(`E${m1Num}:F${m1Num}`);
            setRangeBorder(m1Num, 1, m1Num, 6);

            meta1.getCell(1).font = { name: 'Arial', size: 9.5, bold: true };
            meta1.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };
            meta1.getCell(1).fill = greyFill;

            meta1.getCell(2).font = { name: 'Arial', size: 10, bold: true };
            meta1.getCell(2).alignment = { horizontal: 'center', vertical: 'middle' };

            meta1.getCell(4).font = { name: 'Arial', size: 9.5, bold: true };
            meta1.getCell(4).alignment = { horizontal: 'center', vertical: 'middle' };
            meta1.getCell(4).fill = greyFill;

            meta1.getCell(5).font = { name: 'Arial', size: 10.5, bold: true, color: { argb: 'FF00205B' } };
            meta1.getCell(5).alignment = { horizontal: 'center', vertical: 'middle' };

            // Row 2: DATE RELEASED | TOTAL QUANTITY
            const meta2 = worksheet.addRow([
                'DATE RELEASED:',
                slipData.date_released || '-',
                '',
                'TOTAL QUANTITY:',
                (slipData.total_quantity || 0) + ' PCS',
                ''
            ]);
            meta2.height = 22;
            const m2Num = meta2.number;
            worksheet.mergeCells(`B${m2Num}:C${m2Num}`);
            worksheet.mergeCells(`E${m2Num}:F${m2Num}`);
            setRangeBorder(m2Num, 1, m2Num, 6);

            meta2.getCell(1).font = { name: 'Arial', size: 9.5, bold: true };
            meta2.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };
            meta2.getCell(1).fill = greyFill;

            meta2.getCell(2).font = { name: 'Arial', size: 10, bold: false };
            meta2.getCell(2).alignment = { horizontal: 'center', vertical: 'middle' };

            meta2.getCell(4).font = { name: 'Arial', size: 9.5, bold: true };
            meta2.getCell(4).alignment = { horizontal: 'center', vertical: 'middle' };
            meta2.getCell(4).fill = greyFill;

            meta2.getCell(5).font = { name: 'Arial', size: 10.5, bold: true };
            meta2.getCell(5).alignment = { horizontal: 'center', vertical: 'middle' };

            // Row 3: SI NUMBER | DR NUMBER
            const meta3 = worksheet.addRow([
                'SI NUMBER:',
                liveSi,
                '',
                'DR NUMBER:',
                liveDr,
                ''
            ]);
            meta3.height = 22;
            const m3Num = meta3.number;
            worksheet.mergeCells(`B${m3Num}:C${m3Num}`);
            worksheet.mergeCells(`E${m3Num}:F${m3Num}`);
            setRangeBorder(m3Num, 1, m3Num, 6);

            meta3.getCell(1).font = { name: 'Arial', size: 9.5, bold: true };
            meta3.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };
            meta3.getCell(1).fill = greyFill;

            meta3.getCell(2).font = { name: 'Arial', size: 10, bold: true };
            meta3.getCell(2).alignment = { horizontal: 'center', vertical: 'middle' };

            meta3.getCell(4).font = { name: 'Arial', size: 9.5, bold: true };
            meta3.getCell(4).alignment = { horizontal: 'center', vertical: 'middle' };
            meta3.getCell(4).fill = greyFill;

            meta3.getCell(5).font = { name: 'Arial', size: 10, bold: true };
            meta3.getCell(5).alignment = { horizontal: 'center', vertical: 'middle' };

            // Row 4: MATERIAL DESCRIPTION
            const desc = `MATERIAL DESCRIPTION: ${slipData.brand || ''} ${slipData.model || ''}`.trim();
            const meta4 = worksheet.addRow([desc, '', '', '', '', '']);
            meta4.height = 22;
            const m4Num = meta4.number;
            worksheet.mergeCells(`A${m4Num}:F${m4Num}`);
            setRangeBorder(m4Num, 1, m4Num, 6);
            meta4.getCell(1).font = { name: 'Arial', size: 10, bold: true };
            meta4.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };
            meta4.getCell(1).fill = greyFill;

            // 4. Subtable Columns Header
            const subHead = worksheet.addRow(['NO.', 'BRAND', 'MODEL', 'SERIAL NUMBER', 'MAC ADDRESS', 'BOX NO.']);
            subHead.height = 22;
            const shNum = subHead.number;
            setRangeBorder(shNum, 1, shNum, 6);
            for (let c = 1; c <= 6; c++) {
                subHead.getCell(c).font = { name: 'Arial', size: 9.5, bold: true };
                subHead.getCell(c).alignment = { horizontal: 'center', vertical: 'middle' };
                subHead.getCell(c).fill = greyFill;
            }

            // 5. Items Rows
            if (slipData.items && slipData.items.length > 0) {
                slipData.items.forEach(item => {
                    const row = worksheet.addRow([
                        item.no,
                        String(item.brand || ''),
                        String(item.model || ''),
                        String(item.serial_number || ''),
                        String(item.mac_address || ''),
                        String(item.box_no || '')
                    ]);
                    row.height = 20;
                    const rNum = row.number;
                    setRangeBorder(rNum, 1, rNum, 6);

                    // Col 1: NO.
                    row.getCell(1).font = { name: 'Arial', size: 9.5, bold: true };
                    row.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };

                    // Col 2: BRAND
                    row.getCell(2).font = { name: 'Arial', size: 9.5 };
                    row.getCell(2).alignment = { horizontal: 'center', vertical: 'middle' };

                    // Col 3: MODEL
                    row.getCell(3).font = { name: 'Arial', size: 9.5 };
                    row.getCell(3).alignment = { horizontal: 'center', vertical: 'middle' };

                    // Col 4: SERIAL NUMBER (Bold, Navy, Text Format)
                    row.getCell(4).font = { name: 'Arial', size: 9.5, bold: true, color: { argb: 'FF00205B' } };
                    row.getCell(4).alignment = { horizontal: 'center', vertical: 'middle' };
                    row.getCell(4).numFmt = '@';

                    // Col 5: MAC ADDRESS (Text Format)
                    row.getCell(5).font = { name: 'Arial', size: 9.5 };
                    row.getCell(5).alignment = { horizontal: 'center', vertical: 'middle' };
                    row.getCell(5).numFmt = '@';

                    // Col 6: BOX NO.
                    row.getCell(6).font = { name: 'Arial', size: 9.5 };
                    row.getCell(6).alignment = { horizontal: 'center', vertical: 'middle' };
                    row.getCell(6).numFmt = '@';
                });
            } else {
                const emptyRow = worksheet.addRow(['No items in this batch report.', '', '', '', '', '']);
                emptyRow.height = 24;
                const eNum = emptyRow.number;
                worksheet.mergeCells(`A${eNum}:F${eNum}`);
                setRangeBorder(eNum, 1, eNum, 6);
                emptyRow.getCell(1).font = { name: 'Arial', size: 10, italic: true };
                emptyRow.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };
            }

            // 6. Signatures Block
            worksheet.addRow([]); worksheet.lastRow.height = 14;
            worksheet.addRow([]); worksheet.lastRow.height = 14;

            const sigLines = worksheet.addRow([
                '________________________________________',
                '',
                '',
                '________________________________________',
                '',
                ''
            ]);
            sigLines.height = 16;
            const sLinNum = sigLines.number;
            worksheet.mergeCells(`A${sLinNum}:C${sLinNum}`);
            worksheet.mergeCells(`D${sLinNum}:F${sLinNum}`);
            sigLines.getCell(1).font = { name: 'Arial', size: 9, bold: false };
            sigLines.getCell(1).alignment = { horizontal: 'center', vertical: 'top' };
            sigLines.getCell(4).font = { name: 'Arial', size: 9, bold: false };
            sigLines.getCell(4).alignment = { horizontal: 'center', vertical: 'top' };

            const sigLabels = worksheet.addRow([
                'Released / Inspected By (DFTM)',
                '',
                '',
                'Received By (Client / Customer Signature)',
                '',
                ''
            ]);
            sigLabels.height = 20;
            const sLabNum = sigLabels.number;
            worksheet.mergeCells(`A${sLabNum}:C${sLabNum}`);
            worksheet.mergeCells(`D${sLabNum}:F${sLabNum}`);
            sigLabels.getCell(1).font = { name: 'Arial', size: 9.5, bold: true };
            sigLabels.getCell(1).alignment = { horizontal: 'center', vertical: 'top' };
            sigLabels.getCell(4).font = { name: 'Arial', size: 9.5, bold: true };
            sigLabels.getCell(4).alignment = { horizontal: 'center', vertical: 'top' };

            // 7. Write to Buffer and Trigger Download
            const buffer = await workbook.xlsx.writeBuffer();
            const blob = new Blob([buffer], {
                type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            });
            const filename = `Outgoing_Repair_Slip_${slipData.batch_no || 'Document'}.xlsx`;

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
