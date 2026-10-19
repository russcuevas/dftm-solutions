<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Incoming Repair Slip - {{ $transmittal->transmittal_no }}</title>
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

        .brand-header {
            text-align: center;
            margin-bottom: 12px;
        }
        .brand-header img {
            height: 80px;
            max-width: 460px;
            width: auto;
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
            width: 22%;
            background: #FFFFFF;
            text-align: center;
        }
        .header-val {
            font-weight: 700;
            width: 28%;
            text-align: center;
        }

        .model-banner {
            text-align: center;
            font-weight: 900;
            font-size: 11pt;
            letter-spacing: 0.5px;
            background: #F8FAFC;
            padding: 7px 10px;
            text-transform: uppercase;
            border: 1px solid #000000;
            margin-top: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: Arial, Helvetica, sans-serif;
        }

        .sub-header-row th {
            font-weight: 800;
            text-align: center;
            background: #FFFFFF;
            padding: 6px 8px;
        }

        .col-no {
            width: 50px;
            text-align: center;
            font-weight: bold;
        }
        .col-serial {
            width: 48%;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            font-weight: 700;
            text-align: center;
        }
        .col-mac {
            width: 48%;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            font-weight: 600;
            text-align: center;
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
            Print Document
        </button>
        <button onclick="window.close()" class="btn btn-outline">Close</button>
    </div>
</div>

<div class="slip-wrapper">
    <!-- Top Logo (Large & Prominent) -->
    <div class="brand-header">
        <img src="{{ asset('images/logo.png') }}" alt="DFTM Digital Solutions">
    </div>

    <!-- Main Title -->
    <div class="slip-title">INCOMING REPAIR SLIP</div>

    <!-- Header Information Block (Excel-Style) -->
    <table class="excel-table">
        <tr>
            <td class="header-label">TRANSMITTAL NO:</td>
            <td class="header-val" style="font-weight: 800; font-size: 11pt;">{{ $transmittal->transmittal_no }}</td>
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

    <!-- Consolidated Report Grouped Per Model -->
    @php
        $grandTotal = 0;
        if (!isset($itemsByModel)) {
            $sourceItems = isset($items) ? $items : (isset($allItems) ? $allItems : $transmittal->items);
            $itemsByModel = $sourceItems->groupBy(function($item) {
                return trim($item->model ?: 'Unassigned Model');
            });
        }

        $exportData = [
            'transmittal_no' => $transmittal->transmittal_no,
            'company_name' => $transmittal->company_name ?? 'DFTM DIGITAL SOLUTIONS',
            'date_received' => $transmittal->date_received ? $transmittal->date_received->format('Y-m-d') : '-',
            'total_quantity' => $transmittal->total_quantity,
            'encoder_name' => $transmittal->encoder?->name ?? 'DFTM Staff',
            'groups' => []
        ];
    @endphp

    @forelse($itemsByModel as $modelName => $modelItems)
        @php
            $firstItem = $modelItems->first();
            $brandName = $firstItem?->brand ?: ($transmittal->brand ?: 'N/A');
            $grandTotal += $modelItems->count();

            $groupItems = [];
            foreach ($modelItems as $it) {
                $groupItems[] = [
                    'serial_number' => $it->serial_number ?? '-',
                    'mac_address' => $it->mac_address ?? '-'
                ];
            }
            $exportData['groups'][] = [
                'model' => $modelName,
                'brand' => $brandName,
                'subtotal' => count($groupItems),
                'items' => $groupItems
            ];
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
                </tr>
            </thead>
            <tbody>
                @foreach($modelItems as $item)
                <tr>
                    <td class="col-no">{{ $loop->iteration }}</td>
                    <td class="col-serial">{{ $item->serial_number ?? '-' }}</td>
                    <td class="col-mac">{{ $item->mac_address ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <div style="text-align: center; padding: 24px; border: 1px solid #000000; margin-top: 14px;">
            No units recorded for this repair slip.
        </div>
    @endforelse

    <!-- Grand Summary Footer Block -->
    <table class="excel-table" style="margin-top: 16px;">
        <tr>
            <td style="font-weight: 900; text-align: center; width: 70%; background: #F8FAFC;">
                CONSOLIDATED GRAND TOTAL RECEIVED:
            </td>
            <td style="font-weight: 900; text-align: center; width: 30%; font-size: 11pt; background: #F8FAFC;">
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
            const img = document.querySelector('.brand-header img');
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

            const worksheet = workbook.addWorksheet('Incoming Repair Slip', {
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

            // Column Widths calibrated for exact print reproduction on A4
            worksheet.columns = [
                { key: 'colA', width: 16 }, // NO. / Labels
                { key: 'colB', width: 36 }, // Serial / Values
                { key: 'colC', width: 18 }, // Labels / Left-MAC
                { key: 'colD', width: 34 }  // Values / Right-MAC
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
                    tl: { col: 1.15, row: 0.2 },
                    ext: { width: 320, height: 60 }
                });
                worksheet.addRow([]); worksheet.getRow(1).height = 18;
                worksheet.addRow([]); worksheet.getRow(2).height = 18;
                worksheet.addRow([]); worksheet.getRow(3).height = 18;
            } else {
                const r = worksheet.addRow(['DFTM DIGITAL SOLUTIONS']);
                worksheet.mergeCells(`A${r.number}:D${r.number}`);
                r.height = 26;
                r.getCell(1).font = { name: 'Arial', size: 15, bold: true, color: { argb: 'FF00205B' } };
                r.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };
            }

            // 2. Document Title: INCOMING REPAIR SLIP
            const titleRow = worksheet.addRow(['INCOMING REPAIR SLIP']);
            worksheet.mergeCells(`A${titleRow.number}:D${titleRow.number}`);
            titleRow.height = 26;
            titleRow.getCell(1).font = { name: 'Arial', size: 14.5, bold: true, color: { argb: 'FF000000' } };
            titleRow.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };

            // Spacer
            const sp1 = worksheet.addRow([]);
            sp1.height = 6;

            // 3. Metadata Table (Excel Box Style with Black Borders)
            const meta1 = worksheet.addRow([
                'TRANSMITTAL NO:',
                slipData.transmittal_no || '-',
                'COMPANY NAME:',
                slipData.company_name || '-'
            ]);
            meta1.height = 22;
            meta1.getCell(1).font = { name: 'Arial', size: 10, bold: true };
            meta1.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };
            meta1.getCell(2).font = { name: 'Arial', size: 11, bold: true };
            meta1.getCell(2).alignment = { horizontal: 'center', vertical: 'middle' };
            meta1.getCell(3).font = { name: 'Arial', size: 10, bold: true };
            meta1.getCell(3).alignment = { horizontal: 'center', vertical: 'middle' };
            meta1.getCell(4).font = { name: 'Arial', size: 10, bold: true };
            meta1.getCell(4).alignment = { horizontal: 'center', vertical: 'middle' };
            setRangeBorder(meta1.number, 1, meta1.number, 4);

            const meta2 = worksheet.addRow([
                'DATE RECEIVED:',
                slipData.date_received || '-',
                'TOTAL QUANTITY:',
                (slipData.total_quantity || 0) + ' PCS'
            ]);
            meta2.height = 22;
            meta2.getCell(1).font = { name: 'Arial', size: 10, bold: true };
            meta2.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };
            meta2.getCell(2).font = { name: 'Arial', size: 10, bold: false };
            meta2.getCell(2).alignment = { horizontal: 'center', vertical: 'middle' };
            meta2.getCell(3).font = { name: 'Arial', size: 10, bold: true };
            meta2.getCell(3).alignment = { horizontal: 'center', vertical: 'middle' };
            meta2.getCell(4).font = { name: 'Arial', size: 11, bold: true };
            meta2.getCell(4).alignment = { horizontal: 'center', vertical: 'middle' };
            setRangeBorder(meta2.number, 1, meta2.number, 4);

            // 4. Model Groups with Subtotals and Serial/MAC tables
            if (slipData.groups && slipData.groups.length > 0) {
                slipData.groups.forEach(group => {
                    // Small gap before group
                    const grpSpacer = worksheet.addRow([]);
                    grpSpacer.height = 10;

                    // Model Banner Header
                    const bannerRow = worksheet.addRow([
                        `MODEL: ${String(group.model).toUpperCase()} • BRAND: ${String(group.brand).toUpperCase()}`,
                        '',
                        `SUBTOTAL: ${group.subtotal} PCS`,
                        ''
                    ]);
                    bannerRow.height = 24;
                    const bNum = bannerRow.number;
                    worksheet.mergeCells(`A${bNum}:B${bNum}`);
                    worksheet.mergeCells(`C${bNum}:D${bNum}`);
                    setRangeBorder(bNum, 1, bNum, 4);

                    bannerRow.getCell(1).font = { name: 'Arial', size: 10.5, bold: true };
                    bannerRow.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };
                    bannerRow.getCell(1).fill = greyFill;

                    bannerRow.getCell(3).font = { name: 'Arial', size: 10.5, bold: true };
                    bannerRow.getCell(3).alignment = { horizontal: 'center', vertical: 'middle' };
                    bannerRow.getCell(3).fill = greyFill;

                    // Column Headers: NO. | SERIAL NUMBER | MAC ADDRESS (merged across C-D)
                    const thRow = worksheet.addRow(['NO.', 'SERIAL NUMBER', 'MAC ADDRESS', '']);
                    thRow.height = 20;
                    const thNum = thRow.number;
                    worksheet.mergeCells(`C${thNum}:D${thNum}`);
                    setRangeBorder(thNum, 1, thNum, 4);

                    thRow.getCell(1).font = { name: 'Arial', size: 10, bold: true };
                    thRow.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };

                    thRow.getCell(2).font = { name: 'Arial', size: 10, bold: true };
                    thRow.getCell(2).alignment = { horizontal: 'center', vertical: 'middle' };

                    thRow.getCell(3).font = { name: 'Arial', size: 10, bold: true };
                    thRow.getCell(3).alignment = { horizontal: 'center', vertical: 'middle' };

                    // Items rows
                    group.items.forEach((item, idx) => {
                        const itRow = worksheet.addRow([
                            idx + 1,
                            String(item.serial_number || '-'),
                            String(item.mac_address || '-'),
                            ''
                        ]);
                        itRow.height = 20;
                        const itNum = itRow.number;
                        worksheet.mergeCells(`C${itNum}:D${itNum}`);
                        setRangeBorder(itNum, 1, itNum, 4);

                        itRow.getCell(1).font = { name: 'Arial', size: 10, bold: true };
                        itRow.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };

                        itRow.getCell(2).font = { name: 'Arial', size: 10, bold: true };
                        itRow.getCell(2).alignment = { horizontal: 'center', vertical: 'middle' };
                        itRow.getCell(2).numFmt = '@'; // Force text format

                        itRow.getCell(3).font = { name: 'Arial', size: 10, bold: false };
                        itRow.getCell(3).alignment = { horizontal: 'center', vertical: 'middle' };
                        itRow.getCell(3).numFmt = '@'; // Force text format
                    });
                });
            } else {
                const emptyRow = worksheet.addRow(['No units recorded for this repair slip.', '', '', '']);
                worksheet.mergeCells(`A${emptyRow.number}:D${emptyRow.number}`);
                setRangeBorder(emptyRow.number, 1, emptyRow.number, 4);
                emptyRow.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };
            }

            // 5. Consolidated Grand Summary
            const gtSpacer = worksheet.addRow([]);
            gtSpacer.height = 10;

            const gtRow = worksheet.addRow([
                'CONSOLIDATED GRAND TOTAL RECEIVED:',
                '',
                '',
                (slipData.total_quantity || 0) + ' PCS'
            ]);
            gtRow.height = 24;
            const gtNum = gtRow.number;
            worksheet.mergeCells(`A${gtNum}:C${gtNum}`);
            setRangeBorder(gtNum, 1, gtNum, 4);

            gtRow.getCell(1).font = { name: 'Arial', size: 11, bold: true };
            gtRow.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };
            gtRow.getCell(1).fill = greyFill;

            gtRow.getCell(4).font = { name: 'Arial', size: 11, bold: true };
            gtRow.getCell(4).alignment = { horizontal: 'center', vertical: 'middle' };
            gtRow.getCell(4).fill = greyFill;

            // 6. Signature Block
            const sigSpacer = worksheet.addRow([]);
            sigSpacer.height = 22;

            const sigLabels = worksheet.addRow([
                'RECEIVED & ENCODED BY:',
                '',
                'CHECKED & VERIFIED BY:',
                ''
            ]);
            sigLabels.height = 18;
            const sLNum = sigLabels.number;
            worksheet.mergeCells(`A${sLNum}:B${sLNum}`);
            worksheet.mergeCells(`C${sLNum}:D${sLNum}`);
            sigLabels.getCell(1).font = { name: 'Arial', size: 9.5, bold: true };
            sigLabels.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };
            sigLabels.getCell(3).font = { name: 'Arial', size: 9.5, bold: true };
            sigLabels.getCell(3).alignment = { horizontal: 'center', vertical: 'middle' };

            const sigSpaceRow = worksheet.addRow([]);
            sigSpaceRow.height = 30;

            const sigLines = worksheet.addRow([
                '________________________________________',
                '',
                '________________________________________',
                ''
            ]);
            sigLines.height = 16;
            const sLinNum = sigLines.number;
            worksheet.mergeCells(`A${sLinNum}:B${sLinNum}`);
            worksheet.mergeCells(`C${sLinNum}:D${sLinNum}`);
            sigLines.getCell(1).font = { name: 'Arial', size: 9, bold: false };
            sigLines.getCell(1).alignment = { horizontal: 'center', vertical: 'top' };
            sigLines.getCell(3).font = { name: 'Arial', size: 9, bold: false };
            sigLines.getCell(3).alignment = { horizontal: 'center', vertical: 'top' };

            const sigNames = worksheet.addRow([
                slipData.encoder_name || 'DFTM Staff',
                '',
                'Supervisor / QA Personnel',
                ''
            ]);
            sigNames.height = 18;
            const sNNum = sigNames.number;
            worksheet.mergeCells(`A${sNNum}:B${sNNum}`);
            worksheet.mergeCells(`C${sNNum}:D${sNNum}`);
            sigNames.getCell(1).font = { name: 'Arial', size: 9, bold: true };
            sigNames.getCell(1).alignment = { horizontal: 'center', vertical: 'top' };
            sigNames.getCell(3).font = { name: 'Arial', size: 9, bold: true };
            sigNames.getCell(3).alignment = { horizontal: 'center', vertical: 'top' };

            // 7. Write to Buffer and Trigger Download
            const buffer = await workbook.xlsx.writeBuffer();
            const blob = new Blob([buffer], {
                type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            });
            const filename = `Incoming_Repair_Slip_${slipData.transmittal_no || 'Document'}.xlsx`;

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
