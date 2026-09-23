@extends('layouts.app')

@section('title', 'Traceability Matrix Comparing & Batching')
@section('page_title', 'Traceability Matrix Comparing & Batching')

@push('styles')
    <style>
        .comparing-dashboard {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* Top Action Bar */
        .matrix-action-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            background: #FFFFFF;
            padding: 12px 18px;
            border-radius: var(--radius-md);
            border: 1px solid #CBD5E1;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        }

        /* Excel-Style Matrix Container */
        .excel-sheet-wrapper {
            background: #FFFFFF;
            border: 2px solid #2F5597;
            border-radius: 6px;
            box-shadow: 0 4px 16px rgba(47, 85, 151, 0.12);
            overflow: hidden;
        }

        /* Exact Excel Sheet Table Structure */
        .excel-matrix-table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 0.88rem;
            color: #000000;
        }

        .excel-matrix-table th,
        .excel-matrix-table td {
            border: 1px solid #4B6F96;
            padding: 6px 8px;
            vertical-align: middle;
        }

        /* Excel Header Color Blocks matching screenshot */
        .excel-main-title {
            background: #8EAADB !important;
            color: #000000 !important;
            font-weight: 800 !important;
            font-size: 1.25rem !important;
            text-align: center !important;
            letter-spacing: 1px;
            padding: 10px !important;
            border-bottom: 2px solid #2F5597 !important;
        }

        .excel-meta-row {
            background: #FFFFFF;
            font-weight: 700;
            font-size: 0.92rem;
        }

        .excel-status-good {
            background: #92D050 !important;
            color: #000000 !important;
            font-weight: 800 !important;
            text-align: center;
        }

        .excel-sub-header {
            background: #8EAADB !important;
            color: #000000 !important;
            font-weight: 700 !important;
            font-size: 0.85rem !important;
            text-align: center;
            text-transform: uppercase;
        }

        .excel-model-header {
            background: #8EAADB !important;
            color: #000000 !important;
            font-weight: 700 !important;
            font-size: 0.88rem !important;
            text-align: center;
        }

        /* In-Cell Input Controls */
        .excel-cell-input {
            width: 100%;
            border: 1px solid transparent;
            background: transparent;
            padding: 4px 6px;
            font-size: 0.88rem;
            font-weight: 600;
            color: #000000;
            outline: none;
            border-radius: 2px;
            transition: all 0.15s ease;
        }

        .excel-cell-input:hover {
            border-color: #94A3B8;
            background: #FFFFFF;
        }

        .excel-cell-input:focus {
            border-color: #2563EB;
            background: #EFF6FF;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }

        .excel-cell-input.scan-code {
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .excel-cell-input.scan-code:focus {
            border-color: #059669;
            background: #F0FDF4;
            box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.3);
        }

        .excel-cell-select {
            width: 100%;
            border: 1px solid #94A3B8;
            background: #FFFFFF;
            padding: 4px 8px;
            font-size: 0.88rem;
            font-weight: 700;
            color: #000000;
            outline: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .excel-cell-select:focus {
            border-color: #2563EB;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }

        /* Table Row States */
        .excel-matrix-table tbody tr:hover td {
            background-color: #F1F5F9;
        }

        .excel-matrix-table tbody tr.row-scanned-matched td {
            background-color: #F0FDF4;
        }

        .excel-matrix-table tbody tr.row-scanned-matched td.cell-status-good {
            background-color: #92D050 !important;
            font-weight: 800;
            color: #000000;
        }

        .excel-matrix-table tbody tr.row-just-hit td {
            animation: excelHitPulse 1.2s ease-out;
        }

        @keyframes excelHitPulse {
            0% {
                background-color: #38BDF8 !important;
            }

            40% {
                background-color: #86EFAC !important;
            }

            100% {
                background-color: #F0FDF4 !important;
            }
        }

        /* Scan Banner */
        #scanStatusBanner {
            display: none;
            padding: 8px 16px;
            font-weight: 700;
            font-size: 0.88rem;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #CBD5E1;
        }

        .pulse-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(37, 99, 235, 0.1);
            color: #1D4ED8;
            border: 1px solid #93C5FD;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
        }
    </style>
@endpush

@section('content')
    <div class="comparing-dashboard">

        @php
            $isEncoder = auth()->user() && auth()->user()->role === 'encoder';
            $storeRoute = $isEncoder ? route('encoder.comparing.storeBatch') : route('admin.comparing.storeBatch');
            $companyUnitsUrl = $isEncoder
                ? route('encoder.comparing.companyUnits')
                : route('admin.comparing.companyUnits');
            $scanVerifyUrl = $isEncoder ? route('encoder.comparing.scan') : route('admin.comparing.scan');
            $traceabilityIndexUrl = $isEncoder
                ? route('encoder.traceability.index')
                : route('admin.traceability.index');
        @endphp

        <!-- Top Navigation & Action Toolbar -->
        <div class="matrix-action-bar">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <a href="{{ $traceabilityIndexUrl }}" class="btn btn-outline btn-sm">
                    <i class="bi bi-arrow-left"></i> View Traceability Records
                </a>
            </div>

            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <button type="button" id="btnAudioToggle" class="btn btn-outline btn-sm"
                    style="font-size: 0.8rem; font-weight: 600;">
                    <i class="bi bi-volume-up-fill" id="audioIcon"></i> Sound: ON
                </button>
                <button type="button" id="btnAddScanRow" class="btn btn-secondary btn-sm"
                    style="font-weight: 700; background: #475569; color: #FFF; border: none;">
                    <i class="bi bi-plus-lg"></i> + Add Row
                </button>
                <button type="button" id="btnTopSaveBatch" class="btn btn-sm"
                    style="background: #059669; color: #FFFFFF; font-weight: 700; border: none; padding: 6px 18px;">
                    <i class="bi bi-check2-circle"></i> Save Batch to Traceability
                </button>
            </div>
        </div>

        <!-- Notification / Scan Toast Feedback Banner -->
        <div id="scanStatusBanner">
            <div id="scanStatusMsg">Ready to scan...</div>
            <span id="scanStatusTime" style="font-size: 0.75rem; opacity: 0.9;"></span>
        </div>

        <!-- MAIN SPREADSHEET MATRIX (Immediately Visible on Page Load) -->
        <form action="{{ $storeRoute }}" method="POST" id="storeBatchComparingForm">
            @csrf
            <input type="hidden" name="company_name" id="formCompanyNameInput" value="{{ $selectedCompany }}">

            <div class="excel-sheet-wrapper">
                <table class="excel-matrix-table" id="directTraceabilityTable">
                    <!-- Title Bar -->
                    <thead>
                        <tr>
                            <th colspan="7" class="excel-main-title">
                                GOOD TRACEABILITY MATRIX
                            </th>
                        </tr>

                        <!-- Metadata Row 1: Company Name Dropdown & Status -->
                        <tr class="excel-meta-row">
                            <td colspan="4" style="background: #FFFFFF; padding: 8px 12px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span
                                        style="font-weight: 800; font-size: 0.88rem; white-space: nowrap; color: #000000;">
                                        COMPANY NAME:
                                    </span>
                                    <select id="headerCompanySelect" class="excel-cell-select"
                                        style="font-size: 0.92rem; font-weight: 700; border: 1.5px solid #2F5597; background: #F8FAFC; max-width: 380px;">
                                        <option value="">-- Select Company --</option>
                                        @foreach ($companiesWithCounts as $c)
                                            <option value="{{ $c->company_name }}"
                                                {{ $selectedCompany == $c->company_name ? 'selected' : '' }}
                                                data-count="{{ $c->unbatched_count }}">
                                                {{ $c->company_name }} ({{ $c->unbatched_count }} Units)
                                            </option>
                                        @endforeach
                                        @foreach ($allRegisteredCompanies as $rc)
                                            @if (!$companiesWithCounts->contains('company_name', $rc))
                                                <option value="{{ $rc }}"
                                                    {{ $selectedCompany == $rc ? 'selected' : '' }} data-count="0">
                                                    {{ $rc }} (0 Units)
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </td>
                            <td colspan="3" class="excel-status-good" style="padding: 6px 12px;">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    <span style="font-weight: 800; font-size: 0.88rem; color: #000000;">STATUS:</span>
                                    <select name="status" id="headerBatchStatus" class="excel-cell-select"
                                        style="background: #92D050; border: 1px solid #4B6F96; font-weight: 800; text-align: center; width: auto; font-size: 0.9rem;">
                                        <option value="GOOD" selected>GOOD</option>
                                        <option value="In process">In process</option>
                                        <option value="Repaired">Repaired</option>
                                        <option value="BER">BER</option>
                                    </select>
                                </div>
                            </td>
                        </tr>

                        <!-- Metadata Row 2: Date Delivered & Total Quantity / Boxes -->
                        <tr class="excel-meta-row">
                            <td colspan="4" style="background: #FFFFFF; padding: 6px 12px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span
                                        style="font-weight: 800; font-size: 0.88rem; white-space: nowrap; color: #000000;">
                                        DATE DELIVERED:
                                    </span>
                                    <input type="date" name="date_delivered" id="inputDateDelivered"
                                        class="excel-cell-input"
                                        style="border: 1px solid #94A3B8; background: #FFFFFF; width: 170px; font-weight: 700; padding: 3px 8px;"
                                        value="{{ date('Y-m-d') }}">
                                </div>
                            </td>
                            <td colspan="3"
                                style="background: #FFFFFF; font-weight: 800; text-align: center; font-size: 0.95rem; color: #000000; padding: 6px 12px;">
                                TOTAL QTY: <span id="headerTotalQtyDisplay" style="color: #000000;">0 PCS / 0 BOXES</span>
                            </td>
                        </tr>

                        <!-- Sub-header Level 1: Column Titles & Type of Equipment Span -->
                        <tr class="excel-sub-header">
                            <th rowspan="5" style="width: 45px; text-align: center; vertical-align: middle;">NO.</th>
                            <th rowspan="5" style="width: 170px; text-align: center; vertical-align: middle;">TECHNICAL
                                DIAGNOSTIC (S)</th>
                            <th rowspan="5" style="width: 130px; text-align: center; vertical-align: middle;">REPLACE
                                PARTS</th>
                            <th rowspan="5" style="width: 100px; text-align: center; vertical-align: middle;">STATUS</th>
                            <th colspan="2" style="text-align: center;">TYPE OF EQUIPMENT</th>
                            <th rowspan="4" style="width: 80px; text-align: center; vertical-align: middle;">BOX NO.</th>
                        </tr>

                        <!-- Sub-header Level 2: Batch # -->
                        <tr class="excel-sub-header">
                            <th colspan="2" style="text-align: center; background: #8EAADB; padding: 4px 8px;">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                    <span>BATCH #</span>
                                    <input type="text" name="batch_no" id="inputBatchNo" class="excel-cell-input"
                                        style="border: 1px solid #4B6F96; background: #FFFFFF; text-align: center; width: 140px; font-weight: 800;"
                                        value="{{ $nextBatchNumber }}" required>
                                </div>
                            </th>
                        </tr>

                        <!-- Sub-header Level 3: Material Description Header -->
                        <tr class="excel-sub-header">
                            <th colspan="2" style="text-align: center; font-weight: 800; background: #8EAADB;">
                                MATERIAL DESCRIPTION
                            </th>
                        </tr>

                        <!-- Sub-header Level 4: Brand / Material Description Dropdown -->
                        <tr class="excel-sub-header">
                            <th colspan="2" style="text-align: center; background: #8EAADB; padding: 4px 10px;">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    <select name="brand" id="headerBrandSelect" class="excel-cell-select"
                                        style="max-width: 280px; text-align: center; font-weight: 800; background: #FFFFFF;"
                                        disabled>
                                        <option value="">-- Select Company First --</option>
                                    </select>
                                </div>
                            </th>
                        </tr>

                        <!-- Sub-header Level 5: Model Dropdown & B# -->
                        <tr class="excel-model-header">
                            <th colspan="2" style="text-align: center; background: #8EAADB; padding: 4px 10px;">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                    <span style="font-weight: 800;">MODEL:</span>
                                    <select name="model" id="headerModelSelect" class="excel-cell-select"
                                        style="max-width: 240px; text-align: center; font-weight: 800; background: #FFFFFF;"
                                        disabled>
                                        <option value="">-- Select Company First --</option>
                                    </select>
                                </div>
                            </th>
                            <th style="width: 80px; text-align: center; background: #8EAADB; font-weight: 800;">B#</th>
                        </tr>

                        <!-- Sub-header Level 6: Serial Number & MAC Address Split Headers -->
                        <tr style="background: #FFFFFF;">
                            <th style="background: #F1F5F9; border-top: none;"></th>
                            <th style="background: #F1F5F9; border-top: none;"></th>
                            <th style="background: #F1F5F9; border-top: none;"></th>
                            <th style="background: #F1F5F9; border-top: none;"></th>
                            <th
                                style="width: 220px; text-align: center; font-weight: 800; background: #F8FAFC; color: #000000; font-size: 0.84rem;">
                                SERIAL NUMBER
                            </th>
                            <th
                                style="width: 190px; text-align: center; font-weight: 800; background: #F8FAFC; color: #000000; font-size: 0.84rem;">
                                MAC ADDRESS
                            </th>
                            <th
                                style="background: #F8FAFC; text-align: center; font-weight: 800; color: #0284C7; font-size: 0.84rem;">
                                BOX
                            </th>
                        </tr>
                    </thead>

                    <!-- Dynamic Spreadsheet Table Body (Starts with 1 row) -->
                    <tbody id="matrixSpreadsheetTbody">
                        <!-- Rows rendered via JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Bottom Summary & Save Button Bar -->
            <div
                style="background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 6px; margin-top: 12px; padding: 12px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 16px; font-size: 0.88rem; font-weight: 600;">
                    <span>Available Incoming Units: <strong id="footerIncomingCount"
                            style="color: #2F5597;">0</strong></span>
                    <span>Verified in Batch: <strong id="footerVerifiedCount" style="color: #059669;">0</strong></span>
                    <span>Remaining Unbatched: <strong id="footerRemainingCount" style="color: #D97706;">0</strong></span>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <button type="submit" class="btn btn-primary" id="btnBottomSaveBatch"
                        style="background: #059669; border-color: #059669; padding: 8px 24px; font-weight: 700; font-size: 0.95rem;">
                        <i class="bi bi-check2-circle"></i> Save Batch to Traceability
                    </button>
                </div>
            </div>
        </form>

    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let loadedPoolUnits = []; // Incoming units for selected company
                let currentCompany = '';
                let currentBrandsData = [];
                let soundEnabled = true;

                // Table Rows Model: array of { id, item_no, serial_number, mac_address, box_no, brand, model, technical_diagnostic, replace_parts, repair_status, is_matched }
                let tableRows = [];

                const headerCompanySelect = document.getElementById('headerCompanySelect');
                const headerBrandSelect = document.getElementById('headerBrandSelect');
                const headerModelSelect = document.getElementById('headerModelSelect');
                const headerBatchStatus = document.getElementById('headerBatchStatus');
                const headerTotalQtyDisplay = document.getElementById('headerTotalQtyDisplay');
                const formCompanyInput = document.getElementById('formCompanyNameInput');
                const inputBatchNo = document.getElementById('inputBatchNo');
                const matrixTbody = document.getElementById('matrixSpreadsheetTbody');
                const scanStatusBanner = document.getElementById('scanStatusBanner');
                const scanStatusMsg = document.getElementById('scanStatusMsg');
                const scanStatusTime = document.getElementById('scanStatusTime');
                const btnAudioToggle = document.getElementById('btnAudioToggle');
                const btnAddScanRow = document.getElementById('btnAddScanRow');
                const btnTopSaveBatch = document.getElementById('btnTopSaveBatch');
                const storeBatchForm = document.getElementById('storeBatchComparingForm');
                const footerIncomingCount = document.getElementById('footerIncomingCount');
                const footerVerifiedCount = document.getElementById('footerVerifiedCount');
                const footerRemainingCount = document.getElementById('footerRemainingCount');

                // Web Audio Synthesizer
                const audioCtx = (window.AudioContext || window.webkitAudioContext) ? new(window.AudioContext || window
                    .webkitAudioContext)() : null;

                function playTone(freq, type, duration, delay = 0) {
                    if (!soundEnabled || !audioCtx) return;
                    try {
                        if (audioCtx.state === 'suspended') audioCtx.resume();
                        setTimeout(() => {
                            const osc = audioCtx.createOscillator();
                            const gain = audioCtx.createGain();
                            osc.type = type;
                            osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
                            gain.gain.setValueAtTime(0.18, audioCtx.currentTime);
                            gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + duration);
                            osc.connect(gain);
                            gain.connect(audioCtx.destination);
                            osc.start();
                            osc.stop(audioCtx.currentTime + duration);
                        }, delay);
                    } catch (e) {
                        console.warn(e);
                    }
                }

                function soundSuccess() {
                    playTone(880, 'sine', 0.08, 0);
                    playTone(1320, 'sine', 0.12, 60);
                }

                function soundDuplicate() {
                    playTone(440, 'triangle', 0.15, 0);
                    playTone(440, 'triangle', 0.15, 120);
                }

                function soundError() {
                    playTone(220, 'sawtooth', 0.25, 0);
                }

                if (btnAudioToggle) {
                    btnAudioToggle.addEventListener('click', function() {
                        soundEnabled = !soundEnabled;
                        if (soundEnabled) {
                            btnAudioToggle.innerHTML = '<i class="bi bi-volume-up-fill"></i> Sound: ON';
                            soundSuccess();
                        } else {
                            btnAudioToggle.innerHTML = '<i class="bi bi-volume-mute-fill"></i> Sound: OFF';
                        }
                    });
                }

                function showStatus(msg, type = 'success') {
                    scanStatusBanner.style.display = 'flex';
                    scanStatusTime.textContent = new Date().toLocaleTimeString();
                    if (type === 'success') {
                        scanStatusBanner.style.background = '#059669';
                        scanStatusBanner.style.color = '#FFFFFF';
                    } else if (type === 'duplicate') {
                        scanStatusBanner.style.background = '#D97706';
                        scanStatusBanner.style.color = '#FFFFFF';
                    } else {
                        scanStatusBanner.style.background = '#DC2626';
                        scanStatusBanner.style.color = '#FFFFFF';
                    }
                    scanStatusMsg.innerHTML = msg;
                }

                // Populate Brand Dropdown from company units
                function populateBrandDropdown(brandsData, selectedBrand = '') {
                    if (!brandsData || brandsData.length === 0) {
                        headerBrandSelect.innerHTML = '<option value="">-- No Brands in Incoming --</option>';
                        headerBrandSelect.disabled = true;
                        headerModelSelect.innerHTML = '<option value="">-- No Models Found --</option>';
                        headerModelSelect.disabled = true;
                        return;
                    }

                    headerBrandSelect.disabled = false;
                    let html = '<option value="">-- Select Material Description / Brand --</option>';
                    brandsData.forEach(b => {
                        html +=
                            `<option value="${escapeHtml(b.brand)}" ${selectedBrand === b.brand ? 'selected' : ''}>${escapeHtml(b.brand)} (${b.count} Units)</option>`;
                    });

                    headerBrandSelect.innerHTML = html;
                    if (selectedBrand) {
                        populateModelDropdown(selectedBrand, '');
                    } else if (brandsData.length === 1) {
                        headerBrandSelect.value = brandsData[0].brand;
                        populateModelDropdown(brandsData[0].brand, '');
                    } else {
                        headerModelSelect.innerHTML = '<option value="">-- Select Brand First --</option>';
                        headerModelSelect.disabled = true;
                    }
                }

                // Populate Model Dropdown based on Brand
                function populateModelDropdown(selectedBrand, selectedModel = '') {
                    if (!currentBrandsData || currentBrandsData.length === 0 || !selectedBrand) {
                        headerModelSelect.innerHTML = '<option value="">-- Select Brand First --</option>';
                        headerModelSelect.disabled = true;
                        return;
                    }

                    const foundBrand = currentBrandsData.find(b => b.brand === selectedBrand);
                    const modelsList = foundBrand ? (foundBrand.models || []) : [];

                    if (modelsList.length === 0) {
                        headerModelSelect.innerHTML = '<option value="">-- No Models Found --</option>';
                        headerModelSelect.disabled = true;
                        return;
                    }

                    headerModelSelect.disabled = false;
                    let html = '<option value="">-- Select Model --</option>';
                    if (modelsList.length > 1) {
                        html += `<option value="all">-- All Models (${foundBrand.count} Units) --</option>`;
                    }
                    modelsList.forEach(m => {
                        html +=
                            `<option value="${escapeHtml(m.model)}" ${selectedModel === m.model ? 'selected' : ''}>${escapeHtml(m.model)} (${m.count} Units)</option>`;
                    });

                    headerModelSelect.innerHTML = html;
                    if (modelsList.length === 1) {
                        headerModelSelect.value = modelsList[0].model;
                    }
                }

                // Fetch Incoming units for selected company
                async function fetchCompanyUnits(companyName) {
                    if (!companyName) {
                        currentCompany = '';
                        formCompanyInput.value = '';
                        loadedPoolUnits = [];
                        currentBrandsData = [];
                        updateCounters();
                        return;
                    }

                    currentCompany = companyName;
                    formCompanyInput.value = companyName;

                    showStatus(`Kinukuha ang incoming units para sa ${companyName}...`, 'success');

                    try {
                        const selectedBrand = headerBrandSelect ? headerBrandSelect.value : '';
                        const selectedModel = headerModelSelect ? headerModelSelect.value : '';

                        let url = `{{ $companyUnitsUrl }}?company=${encodeURIComponent(companyName)}`;
                        if (selectedBrand && selectedBrand !== 'all') url +=
                            `&brand=${encodeURIComponent(selectedBrand)}`;
                        if (selectedModel && selectedModel !== 'all') url +=
                            `&model=${encodeURIComponent(selectedModel)}`;

                        const res = await fetch(url);
                        const data = await res.json();

                        if (!data.success) {
                            showStatus(data.message || 'No units found.', 'duplicate');
                            loadedPoolUnits = [];
                            updateCounters();
                            return;
                        }

                        loadedPoolUnits = data.units || [];
                        currentBrandsData = data.brands_data || [];
                        populateBrandDropdown(currentBrandsData, selectedBrand);

                        updateCounters();
                        showStatus(`Ready! ${loadedPoolUnits.length} incoming units available for ${companyName}.`,
                            'success');
                    } catch (err) {
                        console.error(err);
                        showStatus('Failed to load company units.', 'error');
                    }
                }

                // Add blank rows (Default starts with 1 row)
                function addBlankRows(qty = 1) {
                    const startIdx = tableRows.length;
                    const activeBrand = headerBrandSelect ? headerBrandSelect.value : '';
                    const activeModel = headerModelSelect ? headerModelSelect.value : '';
                    const defaultStatus = headerBatchStatus ? headerBatchStatus.value : 'GOOD';

                    for (let i = 0; i < qty; i++) {
                        const rowIdx = startIdx + i;
                        // Auto-calculate Box: rows 0-19 -> B1, rows 20-39 -> B2, rows 40-59 -> B3...
                        const autoBox = `B${Math.floor(rowIdx / 20) + 1}`;

                        tableRows.push({
                            id: null,
                            item_no: rowIdx + 1,
                            serial_number: '',
                            mac_address: '',
                            box_no: autoBox,
                            brand: activeBrand,
                            model: activeModel,
                            technical_diagnostic: 'Test and Clean',
                            replace_parts: '',
                            repair_status: defaultStatus,
                            is_matched: false
                        });
                    }

                    renderTable();
                    updateCounters();
                }

                // Render Table rows matching spreadsheet view
                function renderTable() {
                    let html = '';
                    tableRows.forEach((r, idx) => {
                        const isVerified = r.is_matched && r.id;
                        const rowClass = isVerified ? 'row-scanned-matched' : '';
                        const autoBox = `B${Math.floor(idx / 20) + 1}`;
                        const isGoodStatus = (r.repair_status === 'GOOD' || r.repair_status === 'In process' ||
                            isVerified);

                        html += `
                        <tr id="matrixRow_${idx}" class="${rowClass}">
                            <!-- NO. -->
                            <td style="text-align: center; font-weight: 800; font-size: 0.9rem; color: #000000; width: 45px;">
                                ${idx + 1}
                                ${isVerified ? `<input type="hidden" name="selected_items[]" value="${r.id}">` : ''}
                            </td>

                            <!-- TECHNICAL DIAGNOSTIC (S) -->
                            <td>
                                <input type="text" name="row_diagnostic[${r.id || 'row_' + idx}]" 
                                       value="${escapeHtml(r.technical_diagnostic || 'Test and Clean')}" 
                                       class="excel-cell-input row-diag" 
                                       data-idx="${idx}" placeholder="Diagnostic">
                            </td>

                            <!-- REPLACE PARTS -->
                            <td>
                                <input type="text" name="row_parts[${r.id || 'row_' + idx}]" 
                                       value="${escapeHtml(r.replace_parts || '')}" 
                                       class="excel-cell-input row-parts" 
                                       data-idx="${idx}" placeholder="Replace Parts">
                            </td>

                            <!-- STATUS -->
                            <td class="${r.repair_status === 'GOOD' ? 'excel-status-good' : ''}" style="text-align: center;">
                                <select name="row_status[${r.id || 'row_' + idx}]" 
                                        class="excel-cell-select row-stat" 
                                        data-idx="${idx}" 
                                        style="font-size: 0.84rem; padding: 2px 4px; font-weight: 700; ${r.repair_status === 'GOOD' ? 'background: #92D050;' : ''}">
                                    <option value="GOOD" ${r.repair_status === 'GOOD' ? 'selected' : ''}>GOOD</option>
                                    <option value="In process" ${r.repair_status === 'In process' ? 'selected' : ''}>In process</option>
                                    <option value="Repaired" ${r.repair_status === 'Repaired' ? 'selected' : ''}>Repaired</option>
                                    <option value="BER" ${r.repair_status === 'BER' ? 'selected' : ''}>BER</option>
                                </select>
                            </td>

                            <!-- SERIAL NUMBER (BARCODE GUN SCAN TARGET) -->
                            <td>
                                <input type="text" 
                                       id="cell_sn_${idx}"
                                       name="row_sn[${r.id || 'row_' + idx}]" 
                                       value="${escapeHtml(r.serial_number || '')}" 
                                       class="excel-cell-input scan-code cell-sn-input" 
                                       data-idx="${idx}" 
                                       placeholder="Barilin ang Serial..." 
                                       autocomplete="off">
                            </td>

                            <!-- MAC ADDRESS -->
                            <td>
                                <input type="text" 
                                       id="cell_mac_${idx}"
                                       name="row_mac[${r.id || 'row_' + idx}]" 
                                       value="${escapeHtml(r.mac_address || '')}" 
                                       class="excel-cell-input scan-code cell-mac-input" 
                                       data-idx="${idx}" 
                                       placeholder="MAC Address..." 
                                       autocomplete="off">
                            </td>

                            <!-- BOX NO. (Auto increments: 1-20 -> B1, 21-40 -> B2) -->
                            <td style="text-align: center;">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 4px;">
                                    <input type="text" name="row_box[${r.id || 'row_' + idx}]" 
                                           value="${escapeHtml(r.box_no || autoBox)}" 
                                           class="excel-cell-input row-box" 
                                           data-idx="${idx}" 
                                           style="text-align: center; font-weight: 800; color: #0284C7; text-transform: uppercase; width: 50px;">
                                    ${tableRows.length > 1 ? `
                                                        <button type="button" class="btn btn-outline btn-icon btn-sm btn-del-row" data-idx="${idx}" style="color: #DC2626; border: none; padding: 2px 4px; font-size: 0.8rem;" title="Remove Row">
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    ` : ''}
                                </div>
                            </td>
                        </tr>
                        `;
                    });

                    matrixTbody.innerHTML = html;
                    attachTableEvents();
                }

                function attachTableEvents() {
                    // Serial Number Input & Enter Key Handler
                    matrixTbody.querySelectorAll('.cell-sn-input').forEach(inp => {
                        inp.addEventListener('keydown', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                const idx = parseInt(this.getAttribute('data-idx'));
                                handleCellScan(idx, this.value.trim(), 'serial');
                            }
                        });

                        inp.addEventListener('input', function() {
                            const idx = parseInt(this.getAttribute('data-idx'));
                            tableRows[idx].serial_number = this.value;
                        });
                    });

                    // MAC Address Input & Enter Key Handler
                    matrixTbody.querySelectorAll('.cell-mac-input').forEach(inp => {
                        inp.addEventListener('keydown', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                const idx = parseInt(this.getAttribute('data-idx'));
                                handleCellScan(idx, this.value.trim(), 'mac');
                            }
                        });

                        inp.addEventListener('input', function() {
                            const idx = parseInt(this.getAttribute('data-idx'));
                            tableRows[idx].mac_address = this.value;
                        });
                    });

                    // Other cell inputs
                    matrixTbody.querySelectorAll('.row-diag').forEach(inp => {
                        inp.addEventListener('input', function() {
                            tableRows[parseInt(this.getAttribute('data-idx'))].technical_diagnostic =
                                this.value;
                        });
                    });

                    matrixTbody.querySelectorAll('.row-parts').forEach(inp => {
                        inp.addEventListener('input', function() {
                            tableRows[parseInt(this.getAttribute('data-idx'))].replace_parts = this
                                .value;
                        });
                    });

                    matrixTbody.querySelectorAll('.row-box').forEach(inp => {
                        inp.addEventListener('input', function() {
                            tableRows[parseInt(this.getAttribute('data-idx'))].box_no = this.value;
                            updateCounters();
                        });
                    });

                    matrixTbody.querySelectorAll('.row-stat').forEach(sel => {
                        sel.addEventListener('change', function() {
                            const idx = parseInt(this.getAttribute('data-idx'));
                            tableRows[idx].repair_status = this.value;
                            renderTable();
                        });
                    });

                    // Delete Row
                    matrixTbody.querySelectorAll('.btn-del-row').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const idx = parseInt(this.getAttribute('data-idx'));
                            tableRows.splice(idx, 1);
                            // Re-index & re-box
                            tableRows.forEach((r, i) => {
                                r.item_no = i + 1;
                                r.box_no = `B${Math.floor(i / 20) + 1}`;
                            });
                            if (tableRows.length === 0) {
                                addBlankRows(1);
                            } else {
                                renderTable();
                                updateCounters();
                            }
                        });
                    });
                }

                // Handle Barcode Scan / Enter
                async function handleCellScan(rowIndex, scannedCode, fieldType) {
                    if (!scannedCode) {
                        if (fieldType === 'serial') {
                            focusCell(rowIndex, 'mac');
                        } else {
                            focusCell(rowIndex + 1, 'serial');
                        }
                        return;
                    }

                    if (!currentCompany && headerCompanySelect.value) {
                        currentCompany = headerCompanySelect.value;
                        formCompanyInput.value = currentCompany;
                    }

                    const cleanCode = scannedCode.replace(/[^A-Za-z0-9]/g, '').toUpperCase();

                    // 1. Duplicate check in table
                    const duplicateIdx = tableRows.findIndex((r, idx) => {
                        if (idx === rowIndex) return false;
                        const snC = (r.serial_number || '').replace(/[^A-Za-z0-9]/g, '').toUpperCase();
                        const macC = (r.mac_address || '').replace(/[^A-Za-z0-9]/g, '').toUpperCase();
                        return (snC && snC === cleanCode) || (macC && macC === cleanCode);
                    });

                    if (duplicateIdx !== -1) {
                        soundDuplicate();
                        showStatus(
                            `<i class="bi bi-exclamation-triangle-fill"></i> NAI-SCAN NA: Barcode <strong>${escapeHtml(scannedCode)}</strong> ay nasa Row #${duplicateIdx + 1} na!`,
                            'duplicate'
                        );
                        highlightRow(duplicateIdx);
                        if (fieldType === 'serial') {
                            tableRows[rowIndex].serial_number = '';
                        } else {
                            tableRows[rowIndex].mac_address = '';
                        }
                        renderTable();
                        focusCell(rowIndex, fieldType);
                        return;
                    }

                    // If user is scanning MAC on a row
                    if (fieldType === 'mac') {
                        const chosenBrand = (headerBrandSelect && headerBrandSelect.value) ? headerBrandSelect.value
                            .trim() : '';
                        const chosenModel = (headerModelSelect && headerModelSelect.value) ? headerModelSelect.value
                            .trim() : '';

                        // Check if row already has a matched item (e.g. from serial scan)
                        let targetUnit = null;
                        if (tableRows[rowIndex].id) {
                            targetUnit = loadedPoolUnits.find(u => u.id === tableRows[rowIndex].id);
                        } else if (tableRows[rowIndex].serial_number) {
                            const cleanSn = tableRows[rowIndex].serial_number.replace(/[^A-Za-z0-9]/g, '')
                                .toUpperCase();
                            targetUnit = loadedPoolUnits.find(u => (u.serial_number || '').replace(/[^A-Za-z0-9]/g,
                                '').toUpperCase() === cleanSn);
                        }

                        if (targetUnit) {
                            const expectedMac = (targetUnit.mac_address || '').trim();
                            const cleanExpected = expectedMac.replace(/[^A-Za-z0-9]/g, '').toUpperCase();

                            if (cleanExpected && cleanCode !== cleanExpected) {
                                soundError();
                                showStatus(
                                    `<i class="bi bi-x-octagon-fill"></i> MALI ANG MAC ADDRESS: Ang nai-scan na MAC <strong>${escapeHtml(scannedCode)}</strong> ay HINDI tugma sa Serial Number <strong>${escapeHtml(targetUnit.serial_number)}</strong>! (Dapat ay: <strong>${escapeHtml(expectedMac)}</strong>)`,
                                    'error'
                                );
                                tableRows[rowIndex].mac_address = '';
                                renderTable();
                                focusCell(rowIndex, 'mac');
                                return;
                            }

                            // Match is valid!
                            tableRows[rowIndex].mac_address = expectedMac || scannedCode;
                            soundSuccess();
                            showStatus(
                                `<i class="bi bi-check-circle-fill"></i> ROW #${rowIndex + 1} MAC VERIFIED: <strong>${escapeHtml(tableRows[rowIndex].mac_address)}</strong>! Lilipat sa Row #${rowIndex + 2}...`,
                                'success'
                            );
                            renderTable();
                            updateCounters();
                            highlightRow(rowIndex);
                            // Advance to the next row's serial number!
                            focusCell(rowIndex + 1, 'serial');
                            return;
                        } else {
                            // If user scanned MAC first before Serial Number, search incoming pool for this MAC
                            const matchedByMac = loadedPoolUnits.find(u => (u.mac_address || '').replace(
                                /[^A-Za-z0-9]/g, '').toUpperCase() === cleanCode);
                            if (matchedByMac) {
                                // Validate Brand
                                if (chosenBrand && chosenBrand !== 'all') {
                                    const itemBrand = (matchedByMac.brand || '').trim();
                                    if (itemBrand && itemBrand.toUpperCase() !== chosenBrand.toUpperCase()) {
                                        soundError();
                                        showStatus(
                                            `<i class="bi bi-x-octagon-fill"></i> MALI ANG BRAND: Ang unit na ito ay Brand <strong>${escapeHtml(itemBrand)}</strong>, ngunit ang napili sa header ay <strong>${escapeHtml(chosenBrand)}</strong>!`,
                                            'error'
                                        );
                                        tableRows[rowIndex].mac_address = '';
                                        renderTable();
                                        focusCell(rowIndex, 'mac');
                                        return;
                                    }
                                }

                                // Validate Model
                                if (chosenModel && chosenModel !== 'all') {
                                    const itemModel = (matchedByMac.model || '').trim();
                                    if (itemModel && itemModel.toUpperCase() !== chosenModel.toUpperCase()) {
                                        soundError();
                                        showStatus(
                                            `<i class="bi bi-x-octagon-fill"></i> MALI ANG MODEL: Ang unit na ito ay Model <strong>${escapeHtml(itemModel)}</strong>, ngunit ang napili sa header ay <strong>${escapeHtml(chosenModel)}</strong>!`,
                                            'error'
                                        );
                                        tableRows[rowIndex].mac_address = '';
                                        renderTable();
                                        focusCell(rowIndex, 'mac');
                                        return;
                                    }
                                }

                                const autoBoxNo = `B${Math.floor(rowIndex / 20) + 1}`;
                                tableRows[rowIndex].id = matchedByMac.id;
                                tableRows[rowIndex].serial_number = matchedByMac.serial_number || '';
                                tableRows[rowIndex].mac_address = matchedByMac.mac_address || scannedCode;
                                tableRows[rowIndex].brand = matchedByMac.brand || headerBrandSelect.value;
                                tableRows[rowIndex].model = matchedByMac.model || headerModelSelect.value;
                                tableRows[rowIndex].technical_diagnostic = tableRows[rowIndex]
                                    .technical_diagnostic || matchedByMac.technical_diagnostic || 'Test and Clean';
                                tableRows[rowIndex].replace_parts = tableRows[rowIndex].replace_parts ||
                                    matchedByMac.replace_parts || '';
                                tableRows[rowIndex].repair_status = 'GOOD';
                                tableRows[rowIndex].box_no = tableRows[rowIndex].box_no || autoBoxNo;
                                tableRows[rowIndex].is_matched = true;

                                soundSuccess();
                                showStatus(
                                    `<i class="bi bi-check-circle-fill"></i> ROW #${rowIndex + 1} MAC VERIFIED: <strong>${escapeHtml(matchedByMac.mac_address)}</strong> (SN: ${escapeHtml(matchedByMac.serial_number)})!`,
                                    'success'
                                );
                                renderTable();
                                updateCounters();
                                highlightRow(rowIndex);
                                focusCell(rowIndex + 1, 'serial');
                                return;
                            } else {
                                soundError();
                                showStatus(
                                    `<i class="bi bi-x-circle-fill"></i> HINDI KILALANG MAC: Ang MAC Address <strong>${escapeHtml(scannedCode)}</strong> ay wala sa Incoming records para sa ${escapeHtml(currentCompany || 'kumpanya')}!`,
                                    'error'
                                );
                                tableRows[rowIndex].mac_address = '';
                                renderTable();
                                focusCell(rowIndex, 'mac');
                                return;
                            }
                        }
                    }

                    // 2. Search in local loaded incoming pool for Serial Number
                    const chosenBrand = (headerBrandSelect && headerBrandSelect.value) ? headerBrandSelect.value
                        .trim() : '';
                    const chosenModel = (headerModelSelect && headerModelSelect.value) ? headerModelSelect.value
                        .trim() : '';

                    const matchedPoolItem = loadedPoolUnits.find(u => {
                        const snC = (u.serial_number || '').replace(/[^A-Za-z0-9]/g, '').toUpperCase();
                        return snC && snC === cleanCode;
                    });

                    const autoBoxNo = `B${Math.floor(rowIndex / 20) + 1}`;

                    if (matchedPoolItem) {
                        // Strict Brand Verification
                        if (chosenBrand && chosenBrand !== 'all') {
                            const itemBrand = (matchedPoolItem.brand || '').trim();
                            if (itemBrand && itemBrand.toUpperCase() !== chosenBrand.toUpperCase()) {
                                soundError();
                                showStatus(
                                    `<i class="bi bi-x-octagon-fill"></i> MALI ANG BRAND: Ang unit na ito ay Brand <strong>${escapeHtml(itemBrand)}</strong>, ngunit ang napiling Material Description sa header ay <strong>${escapeHtml(chosenBrand)}</strong>!`,
                                    'error'
                                );
                                tableRows[rowIndex].serial_number = '';
                                renderTable();
                                focusCell(rowIndex, 'serial');
                                return;
                            }
                        }

                        // Strict Model Verification
                        if (chosenModel && chosenModel !== 'all') {
                            const itemModel = (matchedPoolItem.model || '').trim();
                            if (itemModel && itemModel.toUpperCase() !== chosenModel.toUpperCase()) {
                                soundError();
                                showStatus(
                                    `<i class="bi bi-x-octagon-fill"></i> MALI ANG MODEL: Ang unit na ito ay Model <strong>${escapeHtml(itemModel)}</strong>, ngunit ang napiling Model sa header ay <strong>${escapeHtml(chosenModel)}</strong>!`,
                                    'error'
                                );
                                tableRows[rowIndex].serial_number = '';
                                renderTable();
                                focusCell(rowIndex, 'serial');
                                return;
                            }
                        }

                        tableRows[rowIndex].id = matchedPoolItem.id;
                        tableRows[rowIndex].serial_number = matchedPoolItem.serial_number || scannedCode;
                        // DO NOT auto-fill mac_address! User will scan it manually into the MAC cell
                        tableRows[rowIndex].brand = matchedPoolItem.brand || headerBrandSelect.value;
                        tableRows[rowIndex].model = matchedPoolItem.model || headerModelSelect.value;
                        tableRows[rowIndex].technical_diagnostic = tableRows[rowIndex].technical_diagnostic ||
                            matchedPoolItem.technical_diagnostic || 'Test and Clean';
                        tableRows[rowIndex].replace_parts = tableRows[rowIndex].replace_parts || matchedPoolItem
                            .replace_parts || '';
                        tableRows[rowIndex].repair_status = 'GOOD';
                        tableRows[rowIndex].box_no = tableRows[rowIndex].box_no || autoBoxNo;
                        tableRows[rowIndex].is_matched = true;

                        soundSuccess();
                        showStatus(
                            `<i class="bi bi-check-circle-fill"></i> ROW #${rowIndex + 1} SERIAL VERIFIED: <strong>${escapeHtml(matchedPoolItem.serial_number)}</strong> (${escapeHtml(matchedPoolItem.brand || '')} ${escapeHtml(matchedPoolItem.model || '')}). I-shoot naman ang MAC Address!`,
                            'success'
                        );

                        renderTable();
                        updateCounters();
                        highlightRow(rowIndex);

                        // Focus on the MAC ADDRESS cell of the SAME ROW so user can scan MAC!
                        focusCell(rowIndex, 'mac');
                        return;
                    }

                    // 3. Fallback server verification (via fast AJAX verify scan)
                    try {
                        const res = await fetch(`{{ $scanVerifyUrl }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content')
                            },
                            body: JSON.stringify({
                                code: scannedCode,
                                company: currentCompany
                            })
                        });
                        const data = await res.json();

                        if (data.found && !data.is_already_batched && data.item) {
                            const item = data.item;

                            // Strict Brand Verification
                            if (chosenBrand && chosenBrand !== 'all') {
                                const itemBrand = (item.brand || '').trim();
                                if (itemBrand && itemBrand.toUpperCase() !== chosenBrand.toUpperCase()) {
                                    soundError();
                                    showStatus(
                                        `<i class="bi bi-x-octagon-fill"></i> MALI ANG BRAND: Ang unit na ito ay Brand <strong>${escapeHtml(itemBrand)}</strong>, ngunit ang napiling Material Description sa header ay <strong>${escapeHtml(chosenBrand)}</strong>!`,
                                        'error'
                                    );
                                    tableRows[rowIndex].serial_number = '';
                                    renderTable();
                                    focusCell(rowIndex, 'serial');
                                    return;
                                }
                            }

                            // Strict Model Verification
                            if (chosenModel && chosenModel !== 'all') {
                                const itemModel = (item.model || '').trim();
                                if (itemModel && itemModel.toUpperCase() !== chosenModel.toUpperCase()) {
                                    soundError();
                                    showStatus(
                                        `<i class="bi bi-x-octagon-fill"></i> MALI ANG MODEL: Ang unit na ito ay Model <strong>${escapeHtml(itemModel)}</strong>, ngunit ang napiling Model sa header ay <strong>${escapeHtml(chosenModel)}</strong>!`,
                                        'error'
                                    );
                                    tableRows[rowIndex].serial_number = '';
                                    renderTable();
                                    focusCell(rowIndex, 'serial');
                                    return;
                                }
                            }

                            tableRows[rowIndex].id = item.id;
                            tableRows[rowIndex].serial_number = item.serial_number || scannedCode;
                            // DO NOT auto-fill mac_address!
                            tableRows[rowIndex].brand = item.brand || headerBrandSelect.value;
                            tableRows[rowIndex].model = item.model || headerModelSelect.value;
                            tableRows[rowIndex].technical_diagnostic = tableRows[rowIndex].technical_diagnostic ||
                                item.technical_diagnostic || 'Test and Clean';
                            tableRows[rowIndex].replace_parts = tableRows[rowIndex].replace_parts || item
                                .replace_parts || '';
                            tableRows[rowIndex].repair_status = 'GOOD';
                            tableRows[rowIndex].box_no = tableRows[rowIndex].box_no || autoBoxNo;
                            tableRows[rowIndex].is_matched = true;

                            soundSuccess();
                            showStatus(
                                `<i class="bi bi-check-circle-fill"></i> ROW #${rowIndex + 1} SERIAL VERIFIED: <strong>${escapeHtml(item.serial_number)}</strong> (${escapeHtml(item.brand || '')} ${escapeHtml(item.model || '')})! I-shoot naman ang MAC Address!`,
                                'success'
                            );
                            renderTable();
                            updateCounters();
                            highlightRow(rowIndex);
                            // Focus on MAC cell of the same row
                            focusCell(rowIndex, 'mac');
                        } else if (data.is_already_batched) {
                            soundDuplicate();
                            showStatus(
                                `<i class="bi bi-slash-circle-fill"></i> ALREADY BATCHED: Unit <strong>${escapeHtml(scannedCode)}</strong> is in ${escapeHtml(data.batch_no)}!`,
                                'duplicate'
                            );
                            tableRows[rowIndex].serial_number = '';
                            renderTable();
                            focusCell(rowIndex, 'serial');
                        } else if (data.is_other_company) {
                            soundError();
                            showStatus(
                                `<i class="bi bi-x-octagon-fill"></i> IBANG COMPANY: Ang unit <strong>${escapeHtml(scannedCode)}</strong> ay para sa <em>${escapeHtml(data.other_company)}</em>!`,
                                'error'
                            );
                            tableRows[rowIndex].serial_number = '';
                            renderTable();
                            focusCell(rowIndex, 'serial');
                        } else {
                            soundError();
                            showStatus(
                                `<i class="bi bi-x-circle-fill"></i> NOT FOUND: Ang Barcode <strong>${escapeHtml(scannedCode)}</strong> ay wala sa Incoming records para sa ${escapeHtml(currentCompany || 'selected company')}!`,
                                'error'
                            );
                            tableRows[rowIndex].serial_number = '';
                            renderTable();
                            focusCell(rowIndex, 'serial');
                        }
                    } catch (err) {
                        console.error(err);
                        soundError();
                        showStatus(`Error verifying barcode: ${escapeHtml(scannedCode)}`, 'error');
                    }
                }

                function focusCell(idx, field = 'serial') {
                    setTimeout(() => {
                        if (idx >= tableRows.length) {
                            addBlankRows(1);
                        }
                        const cellId = field === 'serial' ? `cell_sn_${idx}` : `cell_mac_${idx}`;
                        const targetCell = document.getElementById(cellId);
                        if (targetCell) {
                            targetCell.focus();
                            targetCell.select();
                            targetCell.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }
                    }, 100);
                }

                function highlightRow(idx) {
                    const row = document.getElementById(`matrixRow_${idx}`);
                    if (row) {
                        row.classList.remove('row-just-hit');
                        void row.offsetWidth;
                        row.classList.add('row-just-hit');
                    }
                }

                function updateCounters() {
                    const matched = tableRows.filter(r => r.is_matched && r.id).length;
                    const remaining = Math.max(0, loadedPoolUnits.length - matched);
                    const totalBoxes = new Set(tableRows.filter(r => r.box_no && r.is_matched).map(r => r.box_no))
                        .size || (matched > 0 ? 1 : 0);

                    headerTotalQtyDisplay.textContent = `${matched} PCS / ${totalBoxes} BOXES`;
                    footerIncomingCount.textContent = loadedPoolUnits.length;
                    footerVerifiedCount.textContent = matched;
                    footerRemainingCount.textContent = remaining;
                }

                // Header Events
                if (headerCompanySelect) {
                    headerCompanySelect.addEventListener('change', function() {
                        const comp = this.value;
                        if (comp) {
                            fetchCompanyUnits(comp);
                        } else {
                            currentCompany = '';
                            formCompanyInput.value = '';
                            loadedPoolUnits = [];
                            currentBrandsData = [];
                            headerBrandSelect.innerHTML =
                                '<option value="">-- Select Company First --</option>';
                            headerBrandSelect.disabled = true;
                            headerModelSelect.innerHTML =
                                '<option value="">-- Select Company First --</option>';
                            headerModelSelect.disabled = true;
                            updateCounters();
                        }
                    });
                }

                if (headerBrandSelect) {
                    headerBrandSelect.addEventListener('change', function() {
                        const chosenBrand = this.value;
                        populateModelDropdown(chosenBrand, '');
                        if (currentCompany) {
                            fetchCompanyUnits(currentCompany);
                        }
                    });
                }

                if (headerModelSelect) {
                    headerModelSelect.addEventListener('change', function() {
                        if (currentCompany) {
                            fetchCompanyUnits(currentCompany);
                        }
                    });
                }

                if (btnAddScanRow) {
                    btnAddScanRow.addEventListener('click', function() {
                        addBlankRows(1);
                        focusCell(tableRows.length - 1, 'serial');
                    });
                }

                if (btnTopSaveBatch) {
                    btnTopSaveBatch.addEventListener('click', function() {
                        storeBatchForm.requestSubmit();
                    });
                }

                // Form Submit Handler
                if (storeBatchForm) {
                    storeBatchForm.addEventListener('submit', function(e) {
                        e.preventDefault();

                        if (!headerCompanySelect.value) {
                            Swal.fire('Company Required', 'Pumili muna ng Company Name sa spreadsheet header.',
                                'warning');
                            return;
                        }

                        const validUnits = tableRows.filter(r => r.id && r.is_matched);
                        if (validUnits.length === 0) {
                            Swal.fire('Walang Verified Units',
                                'Mag-scan o mag-input muna ng mga existing serial numbers sa table bago i-save ang batch.',
                                'warning');
                            return;
                        }

                        const batchNo = inputBatchNo.value.trim();
                        Swal.fire({
                            title: 'Save Traceability Batch?',
                            html: `Nais mo bang i-save ang <strong>${escapeHtml(batchNo)}</strong> na may <strong>${validUnits.length}</strong> verified units para sa <strong>${escapeHtml(headerCompanySelect.value)}</strong>?`,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#059669',
                            confirmButtonText: '<i class="bi bi-check-circle-fill"></i> Save & Open Matrix',
                            cancelButtonText: 'Cancel'
                        }).then((res) => {
                            if (res.isConfirmed) {
                                Swal.fire({
                                    title: 'Saving Batch...',
                                    text: 'Creating batch and linking to Repair Traceability Matrix...',
                                    allowOutsideClick: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });
                                storeBatchForm.submit();
                            }
                        });
                    });
                }

                function escapeHtml(str) {
                    if (!str) return '';
                    return String(str)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }

                // Initialize: Start with 1 row immediately
                addBlankRows(1);

                // Auto-load if company is pre-selected in URL
                if (headerCompanySelect && headerCompanySelect.value) {
                    fetchCompanyUnits(headerCompanySelect.value);
                }

                // Focus first serial number cell on initial page load
                focusCell(0, 'serial');
            });
        </script>
    @endpush
@endsection
