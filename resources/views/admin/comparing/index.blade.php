@extends('layouts.app')

@section('title', 'Barcode Comparing & Traceability Matrix Batching')
@section('page_title', 'Barcode Comparing & Traceability Batching')

@push('styles')
    <style>
        .comparing-dashboard {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* Excel-Style Matrix Table */
        .excel-matrix-table {
            width: 100%;
            border-collapse: collapse;
            background: #FFFFFF;
            font-size: 0.85rem;
        }

        .excel-matrix-table th {
            background: #00205B;
            color: #FFFFFF;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.76rem;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            border: 1px solid #001235;
            vertical-align: middle;
            white-space: nowrap;
        }

        .excel-matrix-table td {
            border: 1px solid #CBD5E1;
            padding: 4px 6px;
            vertical-align: middle;
            background: #FFFFFF;
            transition: background-color 0.2s ease;
        }

        .excel-matrix-table tr:hover td {
            background-color: #F8FAFC;
        }

        .excel-matrix-table tr.row-scanned-matched td {
            background-color: #F0FDF4;
            border-color: #BBF7D0;
        }

        .excel-matrix-table tr.row-just-hit td {
            animation: tableHitPulse 1.5s ease-out;
        }

        @keyframes tableHitPulse {
            0% {
                background-color: #38BDF8 !important;
                color: #FFFFFF !important;
            }

            40% {
                background-color: #86EFAC !important;
            }

            100% {
                background-color: #F0FDF4 !important;
            }
        }

        /* Table In-Cell Inputs */
        .table-cell-input {
            width: 100%;
            border: 1px solid transparent;
            background: transparent;
            padding: 6px 8px;
            font-size: 0.86rem;
            border-radius: 4px;
            outline: none;
            transition: all 0.2s ease;
        }

        .table-cell-input:hover {
            border-color: #94A3B8;
            background: #FFFFFF;
        }

        .table-cell-input:focus {
            border-color: #0284C7;
            background: #FFFFFF;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.2);
        }

        .table-cell-input.scan-target {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            color: var(--dftm-navy);
            letter-spacing: 0.5px;
        }

        .table-cell-input.scan-target:focus {
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.25);
            background: #F0FDF4;
        }

        /* Top Excel Header Banner */
        .excel-header-card {
            background: #FFFFFF;
            border: 2px solid #00205B;
            border-radius: var(--radius-md);
            box-shadow: 0 4px 12px rgba(0, 32, 91, 0.08);
            overflow: hidden;
        }

        .excel-title-ribbon {
            background: linear-gradient(90deg, #00205B 0%, #003380 60%, #0284C7 100%);
            color: #FFFFFF;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .excel-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            background: #F8FAFC;
            border-bottom: 1px solid #CBD5E1;
        }

        .excel-meta-item {
            padding: 10px 14px;
            border-right: 1px solid #E2E8F0;
            border-bottom: 1px solid #E2E8F0;
        }

        .excel-meta-label {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            color: #64748B;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .excel-meta-value {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--dftm-navy);
        }

        /* Sticky Scan Helper Toolbar */
        .scan-toolbar {
            background: #001A4E;
            color: #FFFFFF;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            border-bottom: 2px solid #38BDF8;
        }

        .pulse-scan-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(56, 189, 248, 0.2);
            color: #38BDF8;
            border: 1px solid #38BDF8;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            animation: badgePulse 2s infinite;
        }

        @keyframes badgePulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        .transmittal-badge {
            background: #EEF2FF;
            color: #1E1B4B;
            border: 1px solid #C7D2FE;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.72rem;
            font-family: 'JetBrains Mono', monospace;
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

        <!-- Step 1: Cascading Company, Brand, and Model Selection Card -->
        <div class="card">
            <div class="card-header" style="flex-wrap: wrap; gap: 12px; justify-content: space-between;">
                <div>
                    <div class="card-title" style="display: flex; align-items: center; gap: 10px;">
                        <span
                            style="background: var(--dftm-navy); color: #FFF; width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                        </span>
                        <span>Excel Barcode Comparing & Traceability Batching</span>
                    </div>
                    <div class="card-subtitle">
                        Pumili ng Company, Brand, at Model para i-load ang mga kaugnay na units mula sa Incoming, tapos
                        barilin sa table!
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <a href="{{ $traceabilityIndexUrl }}" class="btn btn-outline btn-sm">
                        <i class="bi bi-arrow-left"></i> View Traceability Matrix
                    </a>
                </div>
            </div>

            <div class="card-body" style="padding: 16px 20px;">
                <div style="background: #F8FAFC; border: 2px dashed #CBD5E1; border-radius: 10px; padding: 14px 18px;">
                    <div class="form-row" style="align-items: flex-end; margin-bottom: 0;">

                        <!-- 1. Company Selector -->
                        <div class="form-group" style="flex: 2; min-width: 240px; margin-bottom: 0;">
                            <label class="form-label"
                                style="font-weight: 800; font-size: 0.88rem; color: var(--dftm-navy); display: flex; align-items: center; gap: 6px;">
                                <i class="bi bi-building-fill" style="color: var(--dftm-accent);"></i>
                                1. COMPANY NAME
                                <span class="badge"
                                    style="background: #E0E7FF; color: #1E1B4B; font-size: 0.68rem;">REQUIRED</span>
                            </label>
                            <select id="mainCompanySelect" class="form-select"
                                style="font-size: 0.95rem; font-weight: 700; padding: 8px 12px; border-color: #94A3B8;">
                                <option value="">-- Choose Company --</option>
                                @foreach ($companiesWithCounts as $c)
                                    <option value="{{ $c->company_name }}"
                                        {{ $selectedCompany == $c->company_name ? 'selected' : '' }}
                                        data-count="{{ $c->unbatched_count }}">
                                        {{ $c->company_name }} ({{ $c->unbatched_count }} Units)
                                    </option>
                                @endforeach
                                @foreach ($allRegisteredCompanies as $rc)
                                    @if (!$companiesWithCounts->contains('company_name', $rc))
                                        <option value="{{ $rc }}" {{ $selectedCompany == $rc ? 'selected' : '' }}
                                            data-count="0">
                                            {{ $rc }} (0 Units)
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- 2. Brand Selector (Filtered by Company) -->
                        <div class="form-group" style="flex: 1.5; min-width: 180px; margin-bottom: 0;">
                            <label class="form-label"
                                style="font-weight: 800; font-size: 0.88rem; color: var(--dftm-navy); display: flex; align-items: center; gap: 6px;">
                                <i class="bi bi-tags-fill" style="color: var(--dftm-accent);"></i>
                                2. BRAND
                            </label>
                            <select id="mainBrandSelect" class="form-select"
                                style="font-size: 0.95rem; font-weight: 700; padding: 8px 12px; border-color: #94A3B8;"
                                disabled>
                                <option value="all">-- Select Company First --</option>
                            </select>
                        </div>

                        <!-- 3. Model Selector (Filtered by Brand & Company) -->
                        <div class="form-group" style="flex: 1.5; min-width: 180px; margin-bottom: 0;">
                            <label class="form-label"
                                style="font-weight: 800; font-size: 0.88rem; color: var(--dftm-navy); display: flex; align-items: center; gap: 6px;">
                                <i class="bi bi-cpu" style="color: var(--dftm-accent);"></i>
                                3. MODEL
                            </label>
                            <select id="mainModelSelect" class="form-select"
                                style="font-size: 0.95rem; font-weight: 700; padding: 8px 12px; border-color: #94A3B8;"
                                disabled>
                                <option value="all">-- Select Brand First --</option>
                            </select>
                        </div>

                        <!-- 4. Load Action Button -->
                        <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
                            <button type="button" id="btnReloadUnits" class="btn btn-primary"
                                style="width: 100%; height: 42px; font-weight: 700;">
                                <i class="bi bi-arrow-clockwise"></i> Load Matrix
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Main Excel Traceability Matrix Table Workspace (Shown when Company is loaded) -->
        <div id="comparingWorkspace" style="display: none;">
            <form action="{{ $storeRoute }}" method="POST" id="storeBatchComparingForm">
                @csrf
                <input type="hidden" name="company_name" id="formCompanyNameInput">

                <!-- Excel Header Block (Exact Specification matching TRACEABILITY Excel) -->
                <div class="excel-header-card">
                    <div class="excel-title-ribbon">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="bi bi-cpu-fill" style="color: #38BDF8; font-size: 1.3rem;"></i>
                            <strong style="font-size: 1.15rem; letter-spacing: 0.5px;">TRACEABILITY MATRIX BATCHING</strong>
                        </div>

                        <div style="display: flex; align-items: center; gap: 10px;">
                            <button type="button" id="btnAudioToggle" class="btn btn-sm"
                                style="background: rgba(255,255,255,0.2); color: #FFF; border: 1px solid rgba(255,255,255,0.3); font-size: 0.8rem;">
                                <i class="bi bi-volume-up-fill" id="audioIcon"></i> Sound: ON
                            </button>
                            <button type="submit" class="btn btn-sm" id="btnTopSubmitBatch"
                                style="background: #059669; color: #FFF; font-weight: 700; border: none; padding: 6px 16px;">
                                <i class="bi bi-check2-circle"></i> Save Batch to Traceability
                            </button>
                        </div>
                    </div>

                    <!-- Excel Metadata Header Grid -->
                    <div class="excel-meta-grid">
                        <div class="excel-meta-item">
                            <div class="excel-meta-label">Company Name</div>
                            <div class="excel-meta-value" id="headerCompanyDisplay">CONVERGE ICT</div>
                        </div>
                        <div class="excel-meta-item">
                            <div class="excel-meta-label">Batch Number</div>
                            <input type="text" name="batch_no" id="inputBatchNo" class="form-control form-control-sm"
                                value="{{ $nextBatchNumber }}" required style="font-weight: 700; color: var(--dftm-navy);">
                        </div>
                        <div class="excel-meta-item">
                            <div class="excel-meta-label">Date Delivered / Batched</div>
                            <input type="date" name="date_delivered" class="form-control form-control-sm"
                                value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="excel-meta-item">
                            <div class="excel-meta-label">Brand</div>
                            <input type="text" name="brand" id="inputBrand" list="brandSuggest"
                                class="form-control form-control-sm" placeholder="e.g. HUAWEI">
                            <datalist id="brandSuggest">
                                <option value="HUAWEI">
                                <option value="ZTE">
                                <option value="SKYWORTH">
                                <option value="FIBERHOME">
                                <option value="NOKIA">
                            </datalist>
                        </div>
                        <div class="excel-meta-item">
                            <div class="excel-meta-label">Model</div>
                            <input type="text" name="model" id="inputModel" class="form-control form-control-sm"
                                placeholder="e.g. 5V5 / EG8145V5">
                        </div>
                        <div class="excel-meta-item">
                            <div class="excel-meta-label">Batch Status</div>
                            <select name="status" id="defaultBatchStatus" class="form-select form-select-sm"
                                style="font-weight: 700;">
                                <option value="In process">In process</option>
                                <option value="Repaired">Repaired</option>
                                <option value="BER">BER</option>
                            </select>
                        </div>
                        <div class="excel-meta-item" style="background: #EFF6FF;">
                            <div class="excel-meta-label" style="color: #1E40AF;">Total Scanned / Verified Qty</div>
                            <div class="excel-meta-value" style="color: #1D4ED8; font-size: 1.05rem;"
                                id="headerTotalCount">0 PCS / 0 BOXES</div>
                        </div>
                    </div>

                    <!-- Live Scan Toolbar ("Sa Table Mismong Ibabaril") -->
                    <div class="scan-toolbar">
                        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                            <span class="pulse-scan-badge">
                                <i class="bi bi-upc-scan"></i> DIRECT TABLE SCANNER ACTIVE
                            </span>
                            <span style="font-size: 0.85rem; color: #E2E8F0;">
                                I-click o i-focus ang Serial Number cell sa table at barilin gamit ang barcode gun. Kusang
                                lilipat sa susunod na row pagka-scan!
                            </span>
                        </div>

                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <button type="button" id="btnAddScanRows" class="btn btn-sm"
                                style="background: rgba(255,255,255,0.15); color: #FFF; border: 1px solid rgba(255,255,255,0.3);">
                                <i class="bi bi-plus-lg"></i> + Add Row
                            </button>
                        </div>
                    </div>

                    <!-- Toast / Notification Banner for Scan Feedback -->
                    <div id="scanStatusBanner"
                        style="display: none; padding: 8px 16px; font-weight: 700; font-size: 0.88rem; align-items: center; justify-content: space-between;">
                        <div id="scanStatusMsg">Ready to scan...</div>
                        <span id="scanStatusTime" style="font-size: 0.75rem; opacity: 0.9;"></span>
                    </div>

                    <!-- The Interactive Excel Traceability Table -->
                    <div class="table-responsive" style="max-height: 580px; overflow-y: auto;">
                        <table class="excel-matrix-table" id="matrixScanTable">
                            <thead style="position: sticky; top: 0; z-index: 10;">
                                <tr>
                                    <th style="width: 45px; text-align: center;">NO.</th>
                                    <th style="width: 170px;">TECHNICAL DIAGNOSTIC (S)</th>
                                    <th style="width: 140px;">REPLACE PARTS</th>
                                    <th style="width: 125px;">STATUS</th>
                                    <th style="min-width: 220px;">SERIAL NUMBER (IBABARIL)</th>
                                    <th style="min-width: 170px;">MAC ADDRESS</th>
                                    <th style="width: 90px; text-align: center;">BOX NO.</th>
                                    <th style="width: 150px;">BRAND & MODEL</th>
                                    <th style="width: 60px; text-align: center;">ACTION</th>
                                </tr>
                            </thead>
                            <tbody id="matrixScanTbody">
                                <!-- Rows loaded and added dynamically -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Bottom Footer Summary Bar -->
                    <div
                        style="background: #F8FAFC; border-top: 1px solid #CBD5E1; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 16px; font-size: 0.88rem;">
                            <span>Filtered Units: <strong id="footerPoolCount"
                                    style="color: var(--dftm-navy);">0</strong></span>
                            <span>Verified in Batch: <strong id="footerMatchedCount"
                                    style="color: #059669;">0</strong></span>
                            <span>Remaining to Scan: <strong id="footerRemainingCount"
                                    style="color: #D97706;">0</strong></span>
                        </div>

                        <div style="display: flex; align-items: center; gap: 10px;">
                            <button type="submit" class="btn btn-primary" id="btnBottomSubmitBatch"
                                style="padding: 9px 24px; font-weight: 700; font-size: 0.95rem;">
                                <i class="bi bi-check2-circle"></i> Create Traceability Batch & Open Matrix
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let loadedPoolUnits = []; // All units for current company
                let currentCompany = '';
                let currentBrandsData = []; // [{ brand: 'HUAWEI', count: 10, models: [{ model: '5V5', count: 5 }] }]
                let soundEnabled = true;
                let
                    tableRows = []; // array of { id, item_no, serial_number, mac_address, box_no, brand, model, transmittal_no, technical_diagnostic, replace_parts, repair_status, is_matched, is_custom }

                const companySelect = document.getElementById('mainCompanySelect');
                const brandSelect = document.getElementById('mainBrandSelect');
                const modelSelect = document.getElementById('mainModelSelect');
                const btnReload = document.getElementById('btnReloadUnits');
                const comparingWorkspace = document.getElementById('comparingWorkspace');
                const formCompanyInput = document.getElementById('formCompanyNameInput');
                const headerCompanyDisplay = document.getElementById('headerCompanyDisplay');
                const headerTotalCount = document.getElementById('headerTotalCount');
                const footerPoolCount = document.getElementById('footerPoolCount');
                const footerMatchedCount = document.getElementById('footerMatchedCount');
                const footerRemainingCount = document.getElementById('footerRemainingCount');
                const matrixTbody = document.getElementById('matrixScanTbody');
                const scanStatusBanner = document.getElementById('scanStatusBanner');
                const scanStatusMsg = document.getElementById('scanStatusMsg');
                const scanStatusTime = document.getElementById('scanStatusTime');
                const btnAudioToggle = document.getElementById('btnAudioToggle');
                const audioIcon = document.getElementById('audioIcon');
                const btnFocusFirstUnscanned = document.getElementById('btnFocusFirstUnscanned');
                const btnAutoBox20 = document.getElementById('btnAutoBox20');
                const btnMatchAll = document.getElementById('btnMatchAll');
                const btnAddScanRows = document.getElementById('btnAddScanRows');
                const inputBatchNo = document.getElementById('inputBatchNo');
                const inputBrand = document.getElementById('inputBrand');
                const inputModel = document.getElementById('inputModel');
                const defaultBatchStatus = document.getElementById('defaultBatchStatus');
                const storeBatchForm = document.getElementById('storeBatchComparingForm');

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

                // Populate Brand Dropdown based on company brandsData
                function populateBrandDropdown(brandsData, selectedBrand = '') {
                    if (!brandsData || brandsData.length === 0) {
                        brandSelect.innerHTML = '<option value="">-- No Brands Found --</option>';
                        brandSelect.disabled = true;
                        modelSelect.innerHTML = '<option value="">-- No Models Found --</option>';
                        modelSelect.disabled = true;
                        return;
                    }

                    let html = '<option value="">-- Choose Brand --</option>';

                    brandsData.forEach(b => {
                        html +=
                            `<option value="${escapeHtml(b.brand)}" ${selectedBrand === b.brand ? 'selected' : ''}>${escapeHtml(b.brand)} (${b.count} Units)</option>`;
                    });

                    brandSelect.innerHTML = html;
                    brandSelect.disabled = false;

                    // Reset model dropdown
                    modelSelect.innerHTML = '<option value="">-- Select Brand First --</option>';
                    modelSelect.disabled = true;
                }

                // Populate Model Dropdown based on selected Brand
                function populateModelDropdown(selectedBrand, selectedModel = '') {
                    if (!currentBrandsData || currentBrandsData.length === 0 || !selectedBrand) {
                        modelSelect.innerHTML = '<option value="">-- Select Brand First --</option>';
                        modelSelect.disabled = true;
                        return;
                    }

                    let modelsList = [];
                    let totalCount = 0;

                    if (selectedBrand === 'all') {
                        // Combine all models across all brands
                        let modelMap = new Map();
                        currentBrandsData.forEach(b => {
                            b.models.forEach(m => {
                                modelMap.set(m.model, (modelMap.get(m.model) || 0) + m.count);
                                totalCount += m.count;
                            });
                        });
                        for (let [mName, mCount] of modelMap.entries()) {
                            modelsList.push({
                                model: mName,
                                count: mCount
                            });
                        }
                    } else {
                        const foundBrand = currentBrandsData.find(b => b.brand === selectedBrand);
                        if (foundBrand) {
                            modelsList = foundBrand.models || [];
                            totalCount = foundBrand.count || 0;
                        }
                    }

                    if (modelsList.length === 0) {
                        modelSelect.innerHTML = '<option value="">-- No Models Found --</option>';
                        modelSelect.disabled = true;
                        return;
                    }

                    let html = '<option value="">-- Choose Model --</option>';
                    if (modelsList.length > 1) {
                        html += `<option value="all">-- All Models (${totalCount} Units) --</option>`;
                    }
                    modelsList.forEach(m => {
                        html +=
                            `<option value="${escapeHtml(m.model)}" ${selectedModel === m.model ? 'selected' : ''}>${escapeHtml(m.model)} (${m.count} Units)</option>`;
                    });

                    modelSelect.innerHTML = html;
                    modelSelect.disabled = false;
                }

                // 1. Fetch Company Brands & Models metadata first (does NOT show matrix table yet)
                async function fetchCompanyMetadata(companyName) {
                    if (!companyName) {
                        brandSelect.innerHTML = '<option value="">-- Select Company First --</option>';
                        brandSelect.disabled = true;
                        modelSelect.innerHTML = '<option value="">-- Select Brand First --</option>';
                        modelSelect.disabled = true;
                        comparingWorkspace.style.display = 'none';
                        return;
                    }

                    currentCompany = companyName;
                    formCompanyInput.value = companyName;
                    headerCompanyDisplay.textContent = companyName;

                    // Hide table workspace when company changes until brand & model are selected
                    comparingWorkspace.style.display = 'none';

                    Swal.fire({
                        title: 'Fetching Brands & Models...',
                        text: `Kinukuha ang available brands para sa ${companyName}...`,
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        const url = `{{ $companyUnitsUrl }}?company=${encodeURIComponent(companyName)}`;
                        const res = await fetch(url);
                        const data = await res.json();

                        Swal.close();

                        if (!data.success) {
                            Swal.fire('Notice', data.message || 'No units found for this company.', 'warning');
                            brandSelect.innerHTML = '<option value="">-- No Brands Found --</option>';
                            brandSelect.disabled = true;
                            modelSelect.innerHTML = '<option value="">-- No Models Found --</option>';
                            modelSelect.disabled = true;
                            return;
                        }

                        currentBrandsData = data.brands_data || [];
                        populateBrandDropdown(currentBrandsData);

                        if (currentBrandsData.length === 0) {
                            Swal.fire('No Units Available',
                                `Walang unbatched units na nakitang pumasok sa Incoming para sa ${companyName}.`,
                                'info');
                        } else {
                            showStatus(`Pumili ng Brand at Model para sa ${companyName}.`, 'success');
                        }
                    } catch (err) {
                        Swal.close();
                        console.error(err);
                        Swal.fire('Error', 'Failed to fetch company brands.', 'error');
                    }
                }

                // 2. Load Matrix Table with selected Company, Brand, and Model filters
                async function loadMatrixTable() {
                    const companyName = companySelect.value;
                    if (!companyName) {
                        Swal.fire('Please select a company', 'Pumili muna ng kumpanya mula sa listahan.', 'info');
                        return;
                    }

                    const selectedBrand = brandSelect ? brandSelect.value : '';
                    const selectedModel = modelSelect ? modelSelect.value : '';

                    currentCompany = companyName;
                    formCompanyInput.value = companyName;
                    headerCompanyDisplay.textContent = companyName;

                    Swal.fire({
                        title: 'Loading Incoming Matrix...',
                        text: `Kinukuha ang units para sa ${companyName}...`,
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        let url = `{{ $companyUnitsUrl }}?company=${encodeURIComponent(companyName)}`;
                        if (selectedBrand && selectedBrand !== 'all') {
                            url += `&brand=${encodeURIComponent(selectedBrand)}`;
                        }
                        if (selectedModel && selectedModel !== 'all') {
                            url += `&model=${encodeURIComponent(selectedModel)}`;
                        }

                        const res = await fetch(url);
                        const data = await res.json();

                        Swal.close();

                        if (!data.success) {
                            Swal.fire('Notice', data.message || 'No units found.', 'warning');
                            return;
                        }

                        loadedPoolUnits = data.units || [];

                        // Auto-update batch header fields
                        const activeB = selectedBrand && selectedBrand !== 'all' ? selectedBrand : '';
                        const activeM = selectedModel && selectedModel !== 'all' ? selectedModel : '';
                        if (activeB) inputBrand.value = activeB;
                        if (activeM) inputModel.value = activeM;

                        // Start with blank rows for manual scanning (start with ONLY 1 blank row)
                        tableRows = [];
                        addBlankRows(1);

                        comparingWorkspace.style.display = 'block';
                        renderTable();
                        updateCounters();

                        // Focus on first serial number cell in table
                        focusCell(0, 'serial');

                        showStatus(
                            `Ready to shoot barcodes! ${loadedPoolUnits.length} incoming units available for ${companyName}${activeB ? ' (' + activeB + (activeM ? ' - ' + activeM : '') + ')' : ''}.`,
                            'success');
                    } catch (err) {
                        Swal.close();
                        console.error(err);
                        Swal.fire('Error', 'Failed to load incoming units.', 'error');
                    }
                }

                // Event: Company Select Change -> Fetches brands & models first, hides matrix table
                if (companySelect) {
                    companySelect.addEventListener('change', function() {
                        if (this.value) {
                            fetchCompanyMetadata(this.value);
                        } else {
                            comparingWorkspace.style.display = 'none';
                            brandSelect.innerHTML = '<option value="">-- Select Company First --</option>';
                            brandSelect.disabled = true;
                            modelSelect.innerHTML = '<option value="">-- Select Brand First --</option>';
                            modelSelect.disabled = true;
                        }
                    });
                }

                // Event: Brand Select Change -> Populates models and keeps workspace hidden until model is selected
                if (brandSelect) {
                    brandSelect.addEventListener('change', function() {
                        const chosenBrand = this.value;
                        if (chosenBrand) {
                            populateModelDropdown(chosenBrand, '');
                            inputBrand.value = chosenBrand !== 'all' ? chosenBrand : '';
                            comparingWorkspace.style.display = 'none';
                        } else {
                            modelSelect.innerHTML = '<option value="">-- Select Brand First --</option>';
                            modelSelect.disabled = true;
                            comparingWorkspace.style.display = 'none';
                        }
                    });
                }

                // Event: Model Select Change -> Loads table when Model is selected
                if (modelSelect) {
                    modelSelect.addEventListener('change', function() {
                        const chosenModel = this.value;
                        if (chosenModel) {
                            inputModel.value = chosenModel !== 'all' ? chosenModel : '';
                            loadMatrixTable();
                        } else {
                            comparingWorkspace.style.display = 'none';
                        }
                    });
                }

                // Event: Load / Reload Matrix Button
                if (btnReload) {
                    btnReload.addEventListener('click', function() {
                        if (companySelect.value) {
                            loadMatrixTable();
                        } else {
                            Swal.fire('Select Company', 'Pumili muna ng kumpanya.', 'info');
                        }
                    });
                }

                @if (!empty($selectedCompany))
                    fetchCompanyMetadata("{{ $selectedCompany }}");
                @endif

                function addBlankRows(qty = 1) {
                    const startIdx = tableRows.length;
                    const activeB = brandSelect && brandSelect.value && brandSelect.value !== 'all' ? brandSelect
                        .value : (inputBrand ? inputBrand.value : '');
                    const activeM = modelSelect && modelSelect.value && modelSelect.value !== 'all' ? modelSelect
                        .value : (inputModel ? inputModel.value : '');

                    for (let i = 0; i < qty; i++) {
                        const rowIdx = startIdx + i;
                        tableRows.push({
                            id: null,
                            item_no: rowIdx + 1,
                            serial_number: '',
                            mac_address: '',
                            box_no: `B${Math.floor(rowIdx / 20) + 1}`,
                            brand: activeB,
                            model: activeM,
                            technical_diagnostic: '',
                            replace_parts: '',
                            repair_status: defaultBatchStatus ? defaultBatchStatus.value : 'In process',
                            is_matched: false,
                            is_custom: false
                        });
                    }
                    renderTable();
                    updateCounters();
                }

                if (btnAddScanRows) {
                    btnAddScanRows.addEventListener('click', () => {
                        addBlankRows(1);
                        showStatus('Added 1 new blank row to table.', 'success');
                    });
                }

                function updateCounters() {
                    const matched = tableRows.filter(r => r.is_matched && r.id).length;
                    const remaining = Math.max(0, loadedPoolUnits.length - matched);
                    const totalBoxes = new Set(tableRows.filter(r => r.box_no && r.is_matched).map(r => r.box_no))
                        .size || (matched > 0 ? 1 : 0);

                    headerTotalCount.textContent = `${matched} PCS / ${totalBoxes} BOXES`;
                    footerPoolCount.textContent = loadedPoolUnits.length;
                    footerMatchedCount.textContent = matched;
                    footerRemainingCount.textContent = remaining;
                }

                function renderTable() {
                    const activeB = brandSelect && brandSelect.value && brandSelect.value !== 'all' ? brandSelect
                        .value : (inputBrand ? inputBrand.value : '');
                    const activeM = modelSelect && modelSelect.value && modelSelect.value !== 'all' ? modelSelect
                        .value : (inputModel ? inputModel.value : '');

                    let html = '';
                    tableRows.forEach((r, idx) => {
                        const isVerified = r.is_matched && r.id;
                        const rowClass = isVerified ? 'row-scanned-matched' : '';
                        const displayBrand = r.brand || activeB;
                        const displayModel = r.model || activeM;
                        const defaultBox = `B${Math.floor(idx / 20) + 1}`;

                        html += `
                <tr id="matrixRow_${idx}" class="${rowClass}">
                    <td style="text-align: center; font-weight: 800; color: var(--dftm-navy);">
                        ${idx + 1}
                        ${isVerified ? `<input type="hidden" name="selected_items[]" value="${r.id}">` : ''}
                    </td>
                    <td>
                        <input type="text" name="row_diagnostic[${r.id || 'row_' + idx}]" 
                               value="${escapeHtml(r.technical_diagnostic || '')}" 
                               class="table-cell-input row-diag" 
                               data-idx="${idx}" placeholder="Diagnostic">
                    </td>
                    <td>
                        <input type="text" name="row_parts[${r.id || 'row_' + idx}]" 
                               value="${escapeHtml(r.replace_parts || '')}" 
                               class="table-cell-input row-parts" 
                               data-idx="${idx}" placeholder="Replace Parts">
                    </td>
                    <td>
                        <select name="row_status[${r.id || 'row_' + idx}]" class="form-select form-select-sm row-stat" data-idx="${idx}" style="font-size: 0.8rem; padding: 3px 6px; font-weight: 600;">
                            <option value="In process" ${r.repair_status === 'In process' ? 'selected' : ''}>In process</option>
                            <option value="Repaired" ${r.repair_status === 'Repaired' ? 'selected' : ''}>Repaired</option>
                            <option value="BER" ${r.repair_status === 'BER' ? 'selected' : ''}>BER</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" 
                               id="cell_sn_${idx}"
                               name="row_sn[${r.id || 'row_' + idx}]" 
                               value="${escapeHtml(r.serial_number || '')}" 
                               class="table-cell-input scan-target cell-sn-input" 
                               data-idx="${idx}" 
                               placeholder="Barilin ang Serial..." 
                               autocomplete="off">
                    </td>
                    <td>
                        <input type="text" 
                               id="cell_mac_${idx}"
                               name="row_mac[${r.id || 'row_' + idx}]" 
                               value="${escapeHtml(r.mac_address || '')}" 
                               class="table-cell-input scan-target cell-mac-input" 
                               data-idx="${idx}" 
                               placeholder="MAC Address..." 
                               autocomplete="off">
                    </td>
                    <td style="text-align: center;">
                        <input type="text" name="row_box[${r.id || 'row_' + idx}]" 
                               value="${escapeHtml(r.box_no || defaultBox)}" 
                               class="table-cell-input row-box" 
                               data-idx="${idx}" 
                               style="text-align: center; font-weight: 700; text-transform: uppercase;" placeholder="${defaultBox}">
                    </td>
                    <td>
                        ${displayBrand || displayModel ? `<span style="font-weight: 700; color: var(--dftm-navy);">${escapeHtml(displayBrand)}</span> <span style="color: #475569;">${escapeHtml(displayModel)}</span>` : '<span style="color: #94A3B8; font-style: italic;">-</span>'}
                    </td>
                    <td style="text-align: center;">
                        <button type="button" class="btn btn-outline btn-icon btn-sm btn-del-row" data-idx="${idx}" style="color: #DC2626; border-color: #FECACA; padding: 2px 6px;" title="Remove Row">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
                    });

                    matrixTbody.innerHTML = html;
                    attachTableEvents();
                }

                function attachTableEvents() {
                    // Serial Number Barcode Gun Scanning inside cell
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

                    // MAC Address Scanning
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

                    // Diagnostic, Parts, Box, Status Inputs
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
                            tableRows[parseInt(this.getAttribute('data-idx'))].repair_status = this
                                .value;
                        });
                    });

                    // Delete Row
                    matrixTbody.querySelectorAll('.btn-del-row').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const idx = parseInt(this.getAttribute('data-idx'));
                            tableRows.splice(idx, 1);
                            // Re-index and re-box
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

                // In-Table Barcode Shooting Handler (Strictly verifies and accepts ONLY existing incoming units)
                async function handleCellScan(rowIndex, scannedCode, fieldType) {
                    if (!scannedCode) {
                        focusCell(rowIndex + 1, 'serial');
                        return;
                    }

                    const cleanCode = scannedCode.replace(/[^A-Za-z0-9]/g, '').toUpperCase();

                    // 1. Check if already scanned in another row in this batch table
                    const duplicateIdx = tableRows.findIndex((r, idx) => {
                        if (idx === rowIndex) return false;
                        const snC = (r.serial_number || '').replace(/[^A-Za-z0-9]/g, '').toUpperCase();
                        const macC = (r.mac_address || '').replace(/[^A-Za-z0-9]/g, '').toUpperCase();
                        return (snC && snC === cleanCode) || (macC && macC === cleanCode);
                    });

                    if (duplicateIdx !== -1) {
                        soundDuplicate();
                        showStatus(
                            `<i class="bi bi-exclamation-triangle-fill"></i> ALREADY SCANNED: Barcode <strong>${escapeHtml(scannedCode)}</strong> is already on Row #${duplicateIdx + 1}!`,
                            'duplicate');
                        highlightRow(duplicateIdx);
                        tableRows[rowIndex].serial_number = '';
                        renderTable();
                        focusCell(rowIndex, 'serial');
                        return;
                    }

                    // 2. Find match in loaded pool for selected Company, Brand, and Model
                    const matchedPoolItem = loadedPoolUnits.find(u => {
                        const snC = (u.serial_number || '').replace(/[^A-Za-z0-9]/g, '').toUpperCase();
                        const macC = (u.mac_address || '').replace(/[^A-Za-z0-9]/g, '').toUpperCase();
                        return (snC && snC === cleanCode) || (macC && macC === cleanCode);
                    });

                    const autoBoxNo = `B${Math.floor(rowIndex / 20) + 1}`;

                    if (matchedPoolItem) {
                        tableRows[rowIndex].id = matchedPoolItem.id;
                        tableRows[rowIndex].serial_number = matchedPoolItem.serial_number || scannedCode;
                        tableRows[rowIndex].mac_address = matchedPoolItem.mac_address || tableRows[rowIndex]
                            .mac_address;
                        tableRows[rowIndex].brand = matchedPoolItem.brand || tableRows[rowIndex].brand;
                        tableRows[rowIndex].model = matchedPoolItem.model || tableRows[rowIndex].model;
                        tableRows[rowIndex].technical_diagnostic = tableRows[rowIndex].technical_diagnostic ||
                            matchedPoolItem.technical_diagnostic || 'Test and Clean';
                        tableRows[rowIndex].replace_parts = tableRows[rowIndex].replace_parts || matchedPoolItem
                            .replace_parts || 'GOOD';
                        tableRows[rowIndex].repair_status = tableRows[rowIndex].repair_status || matchedPoolItem
                            .repair_status || 'In process';
                        tableRows[rowIndex].box_no = tableRows[rowIndex].box_no || autoBoxNo;
                        tableRows[rowIndex].is_matched = true;

                        soundSuccess();
                        const verifiedCount = tableRows.filter(r => r.is_matched && r.id).length;
                        showStatus(
                            `<i class="bi bi-check-circle-fill"></i> ROW #${rowIndex + 1} VERIFIED: <strong>${escapeHtml(matchedPoolItem.serial_number)}</strong> (${escapeHtml(matchedPoolItem.brand)} ${escapeHtml(matchedPoolItem.model)}) [Box: ${tableRows[rowIndex].box_no}]! [${verifiedCount}/${loadedPoolUnits.length} Units]`,
                            'success');

                        renderTable();
                        updateCounters();
                        highlightRow(rowIndex);

                        // Automatically advance focus to the next row's serial number cell!
                        focusCell(rowIndex + 1, 'serial');
                        return;
                    }

                    // 3. If not in loadedPoolUnits, check server (maybe wrong company/brand/model or already batched)
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
                            const chosenB = brandSelect ? brandSelect.value : '';
                            const chosenM = modelSelect ? modelSelect.value : '';

                            if (chosenB && chosenB !== 'all' && item.brand && item.brand.toUpperCase() !== chosenB
                                .toUpperCase()) {
                                soundError();
                                showStatus(
                                    `<i class="bi bi-x-octagon-fill"></i> WRONG BRAND: Unit <strong>${escapeHtml(scannedCode)}</strong> is Brand <em>${escapeHtml(item.brand)}</em>, expected <strong>${escapeHtml(chosenB)}</strong>!`,
                                    'error');
                                tableRows[rowIndex].serial_number = '';
                                renderTable();
                                focusCell(rowIndex, 'serial');
                                return;
                            }

                            if (chosenM && chosenM !== 'all' && item.model && item.model.toUpperCase() !== chosenM
                                .toUpperCase()) {
                                soundError();
                                showStatus(
                                    `<i class="bi bi-x-octagon-fill"></i> WRONG MODEL: Unit <strong>${escapeHtml(scannedCode)}</strong> is Model <em>${escapeHtml(item.model)}</em>, expected <strong>${escapeHtml(chosenM)}</strong>!`,
                                    'error');
                                tableRows[rowIndex].serial_number = '';
                                renderTable();
                                focusCell(rowIndex, 'serial');
                                return;
                            }

                            tableRows[rowIndex].id = item.id;
                            tableRows[rowIndex].serial_number = item.serial_number || scannedCode;
                            tableRows[rowIndex].mac_address = item.mac_address || tableRows[rowIndex].mac_address;
                            tableRows[rowIndex].brand = item.brand || tableRows[rowIndex].brand;
                            tableRows[rowIndex].model = item.model || tableRows[rowIndex].model;
                            tableRows[rowIndex].technical_diagnostic = tableRows[rowIndex].technical_diagnostic ||
                                item.technical_diagnostic || 'Test and Clean';
                            tableRows[rowIndex].replace_parts = tableRows[rowIndex].replace_parts || item
                                .replace_parts || 'GOOD';
                            tableRows[rowIndex].repair_status = tableRows[rowIndex].repair_status || item
                                .repair_status || 'In process';
                            tableRows[rowIndex].box_no = tableRows[rowIndex].box_no || autoBoxNo;
                            tableRows[rowIndex].is_matched = true;

                            soundSuccess();
                            const verifiedCount = tableRows.filter(r => r.is_matched && r.id).length;
                            showStatus(
                                `<i class="bi bi-check-circle-fill"></i> ROW #${rowIndex + 1} VERIFIED: <strong>${escapeHtml(item.serial_number)}</strong> [Box: ${tableRows[rowIndex].box_no}]! [${verifiedCount}/${loadedPoolUnits.length} Units]`,
                                'success');
                            renderTable();
                            updateCounters();
                            highlightRow(rowIndex);
                            focusCell(rowIndex + 1, 'serial');
                        } else if (data.is_already_batched) {
                            soundDuplicate();
                            showStatus(
                                `<i class="bi bi-slash-circle-fill"></i> ALREADY BATCHED: Unit <strong>${escapeHtml(scannedCode)}</strong> is in ${escapeHtml(data.batch_no)}!`,
                                'duplicate');
                            tableRows[rowIndex].serial_number = '';
                            renderTable();
                            focusCell(rowIndex, 'serial');
                        } else if (data.is_other_company) {
                            soundError();
                            showStatus(
                                `<i class="bi bi-x-octagon-fill"></i> WRONG COMPANY: Unit <strong>${escapeHtml(scannedCode)}</strong> belongs to <em>${escapeHtml(data.other_company)}</em>!`,
                                'error');
                            tableRows[rowIndex].serial_number = '';
                            renderTable();
                            focusCell(rowIndex, 'serial');
                        } else {
                            soundError();
                            showStatus(
                                `<i class="bi bi-x-circle-fill"></i> NOT FOUND: Barcode <strong>${escapeHtml(scannedCode)}</strong> does not exist in Incoming records for ${escapeHtml(currentCompany)}!`,
                                'error');
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

                // Focus Next Empty Row button
                if (btnFocusFirstUnscanned) {
                    btnFocusFirstUnscanned.addEventListener('click', () => {
                        const firstUnscannedIdx = tableRows.findIndex(r => !r.is_matched);
                        if (firstUnscannedIdx !== -1) {
                            focusCell(firstUnscannedIdx, 'serial');
                            showStatus(`Focused on Row #${firstUnscannedIdx + 1}`, 'success');
                        } else {
                            addBlankRows(5);
                            focusCell(tableRows.length - 5, 'serial');
                        }
                    });
                }

                // Auto-Box 20/Box
                if (btnAutoBox20) {
                    btnAutoBox20.addEventListener('click', () => {
                        tableRows.forEach((r, idx) => {
                            r.box_no = `B${Math.floor(idx / 20) + 1}`;
                        });
                        renderTable();
                        updateCounters();
                        soundSuccess();
                        showStatus('Auto-assigned 20 units per box (B1, B2, B3...).', 'success');
                    });
                }

                // Form Submit (Validates that verified scanned units exist)
                if (storeBatchForm) {
                    storeBatchForm.addEventListener('submit', function(e) {
                        e.preventDefault();

                        const validUnits = tableRows.filter(r => r.id && r.is_matched);
                        if (validUnits.length === 0) {
                            Swal.fire('Walang Verified Units',
                                'Mag-scan ng mga valid at existing unit serial numbers sa table bago i-save ang batch.',
                                'warning');
                            return;
                        }

                        const batchNo = inputBatchNo.value.trim();
                        Swal.fire({
                            title: 'Create Traceability Batch?',
                            html: `Nais mo bang i-save ang <strong>${escapeHtml(batchNo)}</strong> na may <strong>${validUnits.length}</strong> verified units para sa <strong>${escapeHtml(currentCompany)}</strong>?`,
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
            });
        </script>
    @endpush
@endsection
