@extends('layouts.app')

@section('title', 'Barcode Comparing & Batching')
@section('page_title', 'Barcode Comparing & Batch Creation')

@push('styles')
<style>
    .comparing-dashboard {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Scanner Terminal Card */
    .scanner-station-card {
        background: linear-gradient(135deg, #001A4E 0%, #002D80 50%, #0284C7 100%);
        color: #FFFFFF;
        border-radius: var(--radius-lg);
        padding: 24px;
        box-shadow: 0 10px 25px -5px rgba(0, 32, 91, 0.35);
        border: 1px solid rgba(255, 255, 255, 0.15);
        position: relative;
        overflow: hidden;
    }

    .scanner-station-card::before {
        content: "";
        position: absolute;
        top: -50px;
        right: -50px;
        width: 180px;
        height: 180px;
        background: radial-gradient(circle, rgba(56, 189, 248, 0.25) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .scanner-gun-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
        margin-top: 14px;
    }

    .scanner-gun-input {
        width: 100%;
        background: rgba(255, 255, 255, 0.96);
        color: #001A4E;
        font-family: 'JetBrains Mono', monospace;
        font-size: 1.35rem;
        font-weight: 700;
        padding: 16px 20px 16px 54px;
        border-radius: 12px;
        border: 3px solid #38BDF8;
        box-shadow: 0 0 20px rgba(56, 189, 248, 0.4);
        outline: none;
        transition: all 0.25s ease;
        letter-spacing: 1px;
    }

    .scanner-gun-input:focus {
        background: #FFFFFF;
        border-color: #00F0FF;
        box-shadow: 0 0 30px rgba(0, 240, 255, 0.65);
    }

    .scanner-gun-input::placeholder {
        color: #94A3B8;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1.05rem;
        font-weight: 500;
        letter-spacing: normal;
    }

    .scanner-gun-icon {
        position: absolute;
        left: 18px;
        font-size: 1.6rem;
        color: #0284C7;
        pointer-events: none;
        animation: laserPulse 2s infinite ease-in-out;
    }

    @keyframes laserPulse {
        0%, 100% { transform: scale(1); opacity: 0.9; }
        50% { transform: scale(1.15); opacity: 1; filter: drop-shadow(0 0 6px #38BDF8); }
    }

    /* Live Scan Metrics Ribbon */
    .metric-pill-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        margin-top: 16px;
    }

    .metric-pill {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        padding: 10px 14px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .metric-pill-icon {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .metric-pill-val {
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1.1;
        font-family: 'JetBrains Mono', monospace;
    }

    .metric-pill-lbl {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #E2E8F0;
        font-weight: 600;
    }

    /* Scan Log Toast Feedback */
    .scan-feedback-banner {
        margin-top: 12px;
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        display: none;
        align-items: center;
        justify-content: space-between;
        animation: fadeIn 0.25s ease-out;
    }

    .scan-feedback-success {
        background: #059669;
        color: #FFFFFF;
        border: 1px solid #34D399;
    }

    .scan-feedback-duplicate {
        background: #D97706;
        color: #FFFFFF;
        border: 1px solid #FBBF24;
    }

    .scan-feedback-error {
        background: #DC2626;
        color: #FFFFFF;
        border: 1px solid #F87171;
    }

    /* Comparison Table Styling */
    .table-matched-row {
        background-color: rgba(5, 150, 105, 0.08) !important;
        transition: background-color 0.5s ease;
    }

    .table-just-scanned {
        animation: scanGlow 1.8s ease-out;
    }

    @keyframes scanGlow {
        0% { background-color: #38BDF8 !important; color: #FFFFFF !important; transform: scale(1.01); }
        50% { background-color: #A7F3D0 !important; }
        100% { background-color: rgba(5, 150, 105, 0.08) !important; }
    }

    .box-badge {
        background: #EEF2FF;
        color: var(--dftm-navy);
        border: 1px solid #C7D2FE;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.8rem;
    }

    .transmittal-tag {
        background: #F1F5F9;
        color: #334155;
        border: 1px solid #CBD5E1;
        padding: 2px 7px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-family: 'JetBrains Mono', monospace;
    }
</style>
@endpush

@section('content')
<div class="comparing-dashboard">

    @php
        $isEncoder = auth()->user() && auth()->user()->role === 'encoder';
        $storeRoute = $isEncoder ? route('encoder.comparing.storeBatch') : route('admin.comparing.storeBatch');
        $companyUnitsUrl = $isEncoder ? route('encoder.comparing.companyUnits') : route('admin.comparing.companyUnits');
        $scanVerifyUrl = $isEncoder ? route('encoder.comparing.scan') : route('admin.comparing.scan');
        $traceabilityIndexUrl = $isEncoder ? route('encoder.traceability.index') : route('admin.traceability.index');
    @endphp

    <!-- Top Card: Company Selection & Mode Setup -->
    <div class="card">
        <div class="card-header" style="flex-wrap: wrap; gap: 12px;">
            <div>
                <div class="card-title" style="display: flex; align-items: center; gap: 10px;">
                    <span style="background: var(--dftm-navy); color: #FFF; width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center;">
                        <i class="bi bi-upc-scan"></i>
                    </span>
                    <span>Comparing & Batch Creation Station</span>
                </div>
                <div class="card-subtitle">
                    Select a company to pull all incoming encoded units across all transmittals, then shoot barcodes to verify & batch!
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 10px;">
                <a href="{{ $traceabilityIndexUrl }}" class="btn btn-outline btn-sm">
                    <i class="bi bi-arrow-left"></i> View Traceability Matrix
                </a>
            </div>
        </div>

        <div class="card-body">
            <!-- Step 1: Select Company Name -->
            <div style="background: #F8FAFC; border: 2px dashed #CBD5E1; border-radius: 12px; padding: 18px 20px;">
                <div class="form-row" style="align-items: flex-end;">
                    <div class="form-group" style="flex: 2; min-width: 280px; margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 800; font-size: 0.95rem; color: var(--dftm-navy); display: flex; align-items: center; gap: 8px;">
                            <i class="bi bi-building-fill" style="color: var(--dftm-accent);"></i>
                            STEP 1: SELECT COMPANY NAME
                            <span class="badge" style="background: #E0E7FF; color: #1E1B4B; font-size: 0.72rem;">REQUIRED</span>
                        </label>
                        <select id="mainCompanySelect" class="form-select" style="font-size: 1.05rem; font-weight: 700; padding: 10px 14px; border-color: #94A3B8;">
                            <option value="">-- Choose Company to Load All Incoming Units --</option>
                            @foreach($companiesWithCounts as $c)
                                <option value="{{ $c->company_name }}" {{ $selectedCompany == $c->company_name ? 'selected' : '' }} data-count="{{ $c->unbatched_count }}">
                                    {{ $c->company_name }} ({{ $c->unbatched_count }} Units Available in Incoming)
                                </option>
                            @endforeach
                            @foreach($allRegisteredCompanies as $rc)
                                @if(!$companiesWithCounts->contains('company_name', $rc))
                                    <option value="{{ $rc }}" {{ $selectedCompany == $rc ? 'selected' : '' }} data-count="0">
                                        {{ $rc }} (0 Incoming Units)
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <small style="color: var(--dftm-slate); font-size: 0.76rem; display: block; margin-top: 4px;">
                            <i class="bi bi-info-circle"></i> Automatic na kukunin lahat ng na-encode na items sa Incoming para sa kumpanyang ito kahit magkakaiba ang transmittal number.
                        </small>
                    </div>

                    <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                        <button type="button" id="btnReloadUnits" class="btn btn-primary" style="width: 100%; height: 46px; font-weight: 700;">
                            <i class="bi bi-arrow-clockwise"></i> Load Company Units
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Comparison & Scanner Area (Shown when Company is loaded) -->
    <div id="comparingWorkspace" style="display: none;">
        <form action="{{ $storeRoute }}" method="POST" id="storeBatchComparingForm">
            @csrf
            <input type="hidden" name="company_name" id="formCompanyNameInput">

            <!-- Scanner Station Card ("Babarilin") -->
            <div class="scanner-station-card">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <div style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 1px; color: #7DD3FC; font-weight: 700;">
                            <i class="bi bi-lightning-charge-fill"></i> High-Speed Barcode Gun Station ("Babarilin")
                        </div>
                        <h2 style="font-size: 1.5rem; font-weight: 800; margin: 4px 0 0 0; color: #FFFFFF;" id="scannerTargetCompanyTitle">
                            Scanning for: <span style="color: #38BDF8;">Company Name</span>
                        </h2>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button type="button" id="btnAudioToggle" class="btn btn-sm" style="background: rgba(255,255,255,0.2); color: #FFF; border: 1px solid rgba(255,255,255,0.3);">
                            <i class="bi bi-volume-up-fill" id="audioIcon"></i> Sound: ON
                        </button>
                        <button type="button" id="btnBoxManagerModal" class="btn btn-sm" style="background: rgba(255,255,255,0.2); color: #FFF; border: 1px solid rgba(255,255,255,0.3);">
                            <i class="bi bi-box-seam"></i> Set Active Box No. (<span id="activeBoxLabel">B1</span>)
                        </button>
                    </div>
                </div>

                <!-- Live Barcode Gun Input -->
                <div class="scanner-gun-input-wrap">
                    <i class="bi bi-upc-scan scanner-gun-icon"></i>
                    <input type="text" 
                           id="barcodeGunInput" 
                           class="scanner-gun-input" 
                           placeholder="POINT BARCODE GUN HERE & SHOOT SERIAL NUMBER OR MAC ADDRESS..." 
                           autocomplete="off" 
                           autofocus>
                </div>

                <!-- Instant Notification Banner -->
                <div id="scanFeedbackBanner" class="scan-feedback-banner">
                    <div id="scanFeedbackText">Ready for barcode gun input...</div>
                    <span id="scanFeedbackTime" style="font-size: 0.75rem; opacity: 0.9;"></span>
                </div>

                <!-- Live Metric Counters Ribbon -->
                <div class="metric-pill-grid">
                    <div class="metric-pill">
                        <div class="metric-pill-icon" style="color: #60A5FA;">
                            <i class="bi bi-box-arrow-in-down"></i>
                        </div>
                        <div>
                            <div class="metric-pill-val" id="countTotalPool">0</div>
                            <div class="metric-pill-lbl">Incoming Available</div>
                        </div>
                    </div>

                    <div class="metric-pill" style="border-color: #34D399; background: rgba(5, 150, 105, 0.2);">
                        <div class="metric-pill-icon" style="color: #34D399;">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div>
                            <div class="metric-pill-val" style="color: #6EE7B7;" id="countScannedMatched">0</div>
                            <div class="metric-pill-lbl">Scanned / Compared</div>
                        </div>
                    </div>

                    <div class="metric-pill" style="border-color: #FBBF24; background: rgba(217, 119, 6, 0.2);">
                        <div class="metric-pill-icon" style="color: #FBBF24;">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div>
                            <div class="metric-pill-val" style="color: #FDE68A;" id="countRemainingUnscanned">0</div>
                            <div class="metric-pill-lbl">Remaining to Scan</div>
                        </div>
                    </div>

                    <div class="metric-pill">
                        <div class="metric-pill-icon" style="color: #C084FC;">
                            <i class="bi bi-tags-fill"></i>
                        </div>
                        <div>
                            <div class="metric-pill-val" id="countTransmittalOrigins">0</div>
                            <div class="metric-pill-lbl">Source Transmittals</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Batch Details Setup Card -->
            <div class="card" style="margin-top: 20px;">
                <div class="card-header" style="background: #F8FAFC;">
                    <div class="card-title" style="font-size: 1.05rem;">
                        <i class="bi bi-gear-fill" style="color: var(--dftm-accent);"></i> STEP 2: BATCH DETAILS & TRACEABILITY SETUP
                    </div>
                    <span class="badge badge-stock" style="font-size: 0.85rem;" id="badgeBatchReadyCount">0 Units Ready for Batching</span>
                </div>

                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 700; color: var(--dftm-navy);">Batch Number</label>
                            <input type="text" name="batch_no" id="inputBatchNo" class="form-control" value="{{ $nextBatchNumber }}" required>
                            <small style="color: var(--dftm-slate); font-size: 0.75rem;">Assigned batch number in Traceability (e.g. BATCH 1 - TAGUIG)</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date Delivered / Batched</label>
                            <input type="date" name="date_delivered" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Initial Status</label>
                            <select name="status" id="defaultBatchStatus" class="form-select">
                                <option value="In process">In process</option>
                                <option value="Repaired">Repaired</option>
                                <option value="BER">BER</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Brand (Optional / Auto-detect)</label>
                            <input type="text" name="brand" id="inputBrand" list="brandSuggest" class="form-control" placeholder="e.g. HUAWEI / ZTE">
                            <datalist id="brandSuggest">
                                <option value="HUAWEI">
                                <option value="ZTE">
                                <option value="SKYWORTH">
                                <option value="FIBERHOME">
                                <option value="NOKIA">
                            </datalist>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Model (Optional / Auto-detect)</label>
                            <input type="text" name="model" id="inputModel" class="form-control" placeholder="e.g. EG8145V5 / 5V5">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Default Diagnostic</label>
                            <input type="text" id="defaultDiagnosticInput" class="form-control" value="Test and Clean" placeholder="e.g. Test and Clean">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Default Replace Parts</label>
                            <input type="text" id="defaultPartsInput" class="form-control" value="GOOD" placeholder="e.g. GOOD">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Traceability Matrix Compared Table (Excel Layout Standard) -->
            <div class="card" style="margin-top: 20px;">
                <div class="card-header" style="flex-wrap: wrap; gap: 12px; justify-content: space-between;">
                    <div>
                        <div class="card-title" style="display: flex; align-items: center; gap: 8px;">
                            <i class="bi bi-table" style="color: var(--dftm-accent);"></i>
                            <span>Compared & Verified Units for Batching</span>
                            <span class="badge" style="background: #059669; color: #FFF; font-size: 0.85rem;" id="matchedTableCountBadge">0 Units</span>
                        </div>
                        <div class="card-subtitle">
                            Layout conforms to Traceability Excel Standard (NO., SERIAL, MAC, BOX NO., DIAGNOSTIC, REPLACE PARTS, STATUS)
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <button type="button" id="btnAutoAssignBoxes" class="btn btn-outline btn-sm" title="Auto-assign B1, B2, B3 for every 20 units">
                            <i class="bi bi-boxes"></i> Auto-Box (20/Box)
                        </button>
                        <button type="button" id="btnCompareAllVisible" class="btn btn-outline btn-sm" style="font-weight: 700; color: #059669; border-color: #059669;">
                            <i class="bi bi-check-all"></i> Compare / Match All Available
                        </button>
                        <button type="button" id="btnClearAllScanned" class="btn btn-outline btn-sm" style="color: #DC2626; border-color: #FECACA;">
                            <i class="bi bi-x-circle"></i> Clear All Scanned
                        </button>
                    </div>
                </div>

                <div class="card-body" style="padding: 0;">
                    <!-- Matched Units Table -->
                    <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                        <table class="dftm-table" id="scannedComparedTable">
                            <thead style="position: sticky; top: 0; z-index: 10; background: var(--dftm-navy); color: #FFF;">
                                <tr>
                                    <th style="width: 55px; text-align: center;">NO.</th>
                                    <th>SERIAL NUMBER</th>
                                    <th>MAC ADDRESS</th>
                                    <th style="width: 110px;">BOX NO.</th>
                                    <th>BRAND & MODEL</th>
                                    <th>SOURCE TRANSMITTAL</th>
                                    <th style="width: 170px;">TECHNICAL DIAGNOSTIC</th>
                                    <th style="width: 150px;">REPLACE PARTS</th>
                                    <th style="width: 130px;">STATUS</th>
                                    <th style="width: 70px; text-align: center;">ACTION</th>
                                </tr>
                            </thead>
                            <tbody id="scannedComparedTbody">
                                <tr id="emptyScannedRow">
                                    <td colspan="10" style="text-align: center; color: var(--dftm-slate); padding: 50px 20px;">
                                        <i class="bi bi-upc" style="font-size: 2.5rem; display: block; margin-bottom: 10px; color: #94A3B8;"></i>
                                        <strong style="font-size: 1.05rem; display: block; margin-bottom: 4px; color: var(--dftm-navy);">No units scanned / compared yet!</strong>
                                        <span>Point your barcode scanner gun above at the unit's serial number or MAC address to begin matching.</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer" style="background: #F8FAFC; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <span style="font-size: 0.88rem; color: var(--dftm-slate);">
                            Total Scanned Units to Batch: <strong style="color: var(--dftm-navy);" id="footerScannedTotal">0</strong>
                        </span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px;">
                        <button type="submit" class="btn btn-primary" id="btnSubmitBatch" style="padding: 10px 24px; font-weight: 700; font-size: 1rem;" disabled>
                            <i class="bi bi-check2-circle"></i> Create Traceability Batch & Open Matrix
                        </button>
                    </div>
                </div>
            </div>

            <!-- Optional: Expandable Drawer for Unscanned Pool Units -->
            <div class="card" style="margin-top: 20px;">
                <div class="card-header" style="cursor: pointer;" id="headerToggleUnscannedPool">
                    <div class="card-title" style="font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-list-check" style="color: var(--dftm-accent);"></i>
                        <span>Unscanned Incoming Pool (<span id="unscannedListCount">0</span> units pending scan)</span>
                    </div>
                    <button type="button" class="btn btn-outline btn-sm" id="btnToggleUnscannedDetails">
                        <i class="bi bi-chevron-down" id="iconToggleUnscanned"></i> Show / Hide Pool List
                    </button>
                </div>

                <div class="card-body" id="bodyUnscannedPool" style="display: none; padding: 0;">
                    <div style="padding: 12px 16px; background: #F8FAFC; border-bottom: 1px solid var(--dftm-border); display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
                        <div style="font-size: 0.82rem; color: var(--dftm-slate);">
                            Ito ang listahan ng lahat ng units mula sa Incoming para sa kumpanyang ito na hindi pa nababaril.
                        </div>
                        <div style="width: 240px;">
                            <input type="text" id="filterPoolSearch" class="form-control form-control-sm" placeholder="Search pool serial/MAC...">
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="dftm-table" id="poolUnitsTable">
                            <thead>
                                <tr>
                                    <th>SERIAL NUMBER</th>
                                    <th>MAC ADDRESS</th>
                                    <th>BRAND & MODEL</th>
                                    <th>BOX NO.</th>
                                    <th>SOURCE TRANSMITTAL</th>
                                    <th>DATE RECEIVED</th>
                                    <th style="text-align: center;">QUICK ACTION</th>
                                </tr>
                            </thead>
                            <tbody id="poolUnitsTbody">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // State management
    let loadedPoolUnits = []; // All units for current company
    let scannedUnitsMap = new Map(); // id -> unitData + row overrides
    let currentCompany = '';
    let soundEnabled = true;
    let currentActiveBox = 'B1';

    // Elements
    const companySelect = document.getElementById('mainCompanySelect');
    const btnReload = document.getElementById('btnReloadUnits');
    const comparingWorkspace = document.getElementById('comparingWorkspace');
    const targetCompanyTitle = document.getElementById('scannerTargetCompanyTitle');
    const formCompanyInput = document.getElementById('formCompanyNameInput');
    const barcodeInput = document.getElementById('barcodeGunInput');
    const feedbackBanner = document.getElementById('scanFeedbackBanner');
    const feedbackText = document.getElementById('scanFeedbackText');
    const feedbackTime = document.getElementById('scanFeedbackTime');
    const countTotalPool = document.getElementById('countTotalPool');
    const countScannedMatched = document.getElementById('countScannedMatched');
    const countRemainingUnscanned = document.getElementById('countRemainingUnscanned');
    const countTransmittalOrigins = document.getElementById('countTransmittalOrigins');
    const matchedTableCountBadge = document.getElementById('matchedTableCountBadge');
    const badgeBatchReadyCount = document.getElementById('badgeBatchReadyCount');
    const footerScannedTotal = document.getElementById('footerScannedTotal');
    const btnSubmitBatch = document.getElementById('btnSubmitBatch');
    const scannedTbody = document.getElementById('scannedComparedTbody');
    const emptyScannedRow = document.getElementById('emptyScannedRow');
    const poolTbody = document.getElementById('poolUnitsTbody');
    const unscannedListCount = document.getElementById('unscannedListCount');
    const filterPoolSearch = document.getElementById('filterPoolSearch');
    const btnCompareAllVisible = document.getElementById('btnCompareAllVisible');
    const btnClearAllScanned = document.getElementById('btnClearAllScanned');
    const btnAutoAssignBoxes = document.getElementById('btnAutoAssignBoxes');
    const btnAudioToggle = document.getElementById('btnAudioToggle');
    const audioIcon = document.getElementById('audioIcon');
    const defaultDiagnosticInput = document.getElementById('defaultDiagnosticInput');
    const defaultPartsInput = document.getElementById('defaultPartsInput');
    const defaultBatchStatus = document.getElementById('defaultBatchStatus');
    const inputBrand = document.getElementById('inputBrand');
    const inputModel = document.getElementById('inputModel');
    const inputBatchNo = document.getElementById('inputBatchNo');
    const btnBoxManagerModal = document.getElementById('btnBoxManagerModal');
    const activeBoxLabel = document.getElementById('activeBoxLabel');
    const headerToggleUnscannedPool = document.getElementById('headerToggleUnscannedPool');
    const bodyUnscannedPool = document.getElementById('bodyUnscannedPool');
    const iconToggleUnscanned = document.getElementById('iconToggleUnscanned');

    // Web Audio Synthesizer for high-speed scanner feedback
    const audioCtx = (window.AudioContext || window.webkitAudioContext) ? new (window.AudioContext || window.webkitAudioContext)() : null;

    function playTone(freq, type, duration, delay = 0) {
        if (!soundEnabled || !audioCtx) return;
        try {
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            setTimeout(() => {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = type;
                osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + duration);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + duration);
            }, delay);
        } catch (e) {
            console.warn('Audio tone error', e);
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

    // Audio Toggle
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

    // Set Active Box
    if (btnBoxManagerModal) {
        btnBoxManagerModal.addEventListener('click', function() {
            Swal.fire({
                title: 'Set Active Box Number',
                text: 'Enter the box number to automatically assign to scanned units:',
                input: 'text',
                inputValue: currentActiveBox,
                showCancelButton: true,
                confirmButtonColor: '#00205B',
                confirmButtonText: 'Set Active Box'
            }).then((res) => {
                if (res.isConfirmed && res.value) {
                    currentActiveBox = res.value.trim().toUpperCase();
                    activeBoxLabel.textContent = currentActiveBox;
                }
            });
        });
    }

    // Toggle Unscanned Pool drawer
    if (headerToggleUnscannedPool) {
        headerToggleUnscannedPool.addEventListener('click', function(e) {
            if (e.target.closest('#filterPoolSearch')) return;
            const isHidden = bodyUnscannedPool.style.display === 'none';
            bodyUnscannedPool.style.display = isHidden ? 'block' : 'none';
            iconToggleUnscanned.className = isHidden ? 'bi bi-chevron-up' : 'bi bi-chevron-down';
        });
    }

    // Load Company Units function
    async function loadCompany(companyName) {
        if (!companyName) {
            Swal.fire('Please select a company', 'Pumili muna ng kumpanya mula sa listahan.', 'info');
            return;
        }

        currentCompany = companyName;
        formCompanyInput.value = companyName;
        targetCompanyTitle.innerHTML = `Scanning for: <span style="color: #38BDF8;">${escapeHtml(companyName)}</span>`;

        if (!inputBatchNo.value) {
            inputBatchNo.value = '{{ $nextBatchNumber }}';
        }

        // Show loading state
        Swal.fire({
            title: 'Loading Incoming Units...',
            text: `Kinukuha lahat ng units para sa ${companyName} mula sa lahat ng transmittals...`,
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        try {
            const res = await fetch(`{{ $companyUnitsUrl }}?company=${encodeURIComponent(companyName)}`);
            const data = await res.json();

            Swal.close();

            if (!data.success) {
                Swal.fire('Notice', data.message || 'No units found.', 'warning');
                return;
            }

            loadedPoolUnits = data.units || [];
            scannedUnitsMap.clear();

            // Auto-detect brand & model if available
            if (data.brands && data.brands.length === 1 && !inputBrand.value) {
                inputBrand.value = data.brands[0];
            }
            if (data.models && data.models.length === 1 && !inputModel.value) {
                inputModel.value = data.models[0];
            }

            comparingWorkspace.style.display = 'block';
            updateAllCounters();
            renderScannedTable();
            renderPoolTable();

            // Focus scanner input
            focusScanner();

            showFeedback(`Loaded ${loadedPoolUnits.length} incoming units for ${companyName}. Ready to shoot barcodes!`, 'success');
        } catch (err) {
            Swal.close();
            console.error(err);
            Swal.fire('Error', 'Failed to load incoming units. Check connection.', 'error');
        }
    }

    // Company select / reload button trigger
    if (companySelect) {
        companySelect.addEventListener('change', function () {
            if (this.value) {
                loadCompany(this.value);
            } else {
                comparingWorkspace.style.display = 'none';
            }
        });
    }

    if (btnReload) {
        btnReload.addEventListener('click', function() {
            if (companySelect.value) {
                loadCompany(companySelect.value);
            } else {
                Swal.fire('Select Company', 'Pumili muna ng kumpanya.', 'info');
            }
        });
    }

    // Auto-load if company preselected
    @if(!empty($selectedCompany))
        loadCompany("{{ $selectedCompany }}");
    @endif

    function focusScanner() {
        setTimeout(() => {
            if (barcodeInput) {
                barcodeInput.focus();
                barcodeInput.select();
            }
        }, 150);
    }

    // Keep scanner input focused on user click outside form controls
    document.addEventListener('click', function(e) {
        if (!comparingWorkspace || comparingWorkspace.style.display === 'none') return;
        const target = e.target;
        const isInteractive = target.closest('input, select, textarea, button, a, table');
        if (!isInteractive && barcodeInput) {
            barcodeInput.focus();
        }
    });

    // Show Scan Feedback banner
    function showFeedback(msg, type = 'success') {
        feedbackBanner.className = `scan-feedback-banner scan-feedback-${type}`;
        feedbackText.innerHTML = msg;
        feedbackTime.textContent = new Date().toLocaleTimeString();
        feedbackBanner.style.display = 'flex';
    }

    // Scanner Barcode Gun Processing ("Babarilin")
    if (barcodeInput) {
        barcodeInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                processScannedCode(this.value.trim());
                this.value = '';
            }
        });
    }

    function processScannedCode(rawCode) {
        if (!rawCode) return;

        const cleanCode = rawCode.replace(/[^A-Za-z0-9]/g, '').toUpperCase();

        // 1. Check if already scanned in current session
        for (let [id, u] of scannedUnitsMap.entries()) {
            const snClean = (u.serial_number || '').replace(/[^A-Za-z0-9]/g, '').toUpperCase();
            const macClean = (u.mac_address || '').replace(/[^A-Za-z0-9]/g, '').toUpperCase();
            if ((snClean && snClean === cleanCode) || (macClean && macClean === cleanCode)) {
                soundDuplicate();
                showFeedback(`<i class="bi bi-exclamation-triangle-fill"></i> ALREADY SCANNED: Unit <strong>${escapeHtml(u.serial_number)}</strong> is already matched in this batch!`, 'duplicate');
                highlightExistingRow(id);
                return;
            }
        }

        // 2. Check in loaded incoming pool for this company
        let matchedUnit = null;
        for (let unit of loadedPoolUnits) {
            const snClean = (unit.serial_number || '').replace(/[^A-Za-z0-9]/g, '').toUpperCase();
            const macClean = (unit.mac_address || '').replace(/[^A-Za-z0-9]/g, '').toUpperCase();
            if ((snClean && snClean === cleanCode) || (macClean && macClean === cleanCode)) {
                matchedUnit = unit;
                break;
            }
        }

        if (matchedUnit) {
            // Match found!
            addUnitToScannedBatch(matchedUnit, true);
            soundSuccess();
            showFeedback(`<i class="bi bi-check-circle-fill"></i> MATCHED: <strong>${escapeHtml(matchedUnit.serial_number)}</strong> (${escapeHtml(matchedUnit.brand)} ${escapeHtml(matchedUnit.model)}) from ${escapeHtml(matchedUnit.transmittal_no)}!`, 'success');
            return;
        }

        // 3. Not in loaded pool? Check server (maybe added recently or belongs to another company)
        verifyWithServer(rawCode);
    }

    async function verifyWithServer(code) {
        try {
            const res = await fetch(`{{ $scanVerifyUrl }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    code: code,
                    company: currentCompany
                })
            });
            const data = await res.json();

            if (data.found && !data.is_already_batched && data.item) {
                const item = data.item;
                addUnitToScannedBatch(item, true);
                soundSuccess();
                showFeedback(`<i class="bi bi-check-circle-fill"></i> VERIFIED & MATCHED: <strong>${escapeHtml(item.serial_number)}</strong> (${escapeHtml(item.brand)} ${escapeHtml(item.model)})!`, 'success');
            } else if (data.is_already_batched) {
                soundDuplicate();
                showFeedback(`<i class="bi bi-slash-circle-fill"></i> ALREADY BATCHED: Unit <strong>${escapeHtml(code)}</strong> is already in ${escapeHtml(data.batch_no || 'another batch')}!`, 'duplicate');
            } else if (data.is_other_company) {
                soundError();
                showFeedback(`<i class="bi bi-x-octagon-fill"></i> WRONG COMPANY: Unit <strong>${escapeHtml(code)}</strong> belongs to <em>${escapeHtml(data.other_company)}</em>, not ${escapeHtml(currentCompany)}!`, 'error');
            } else {
                soundError();
                showFeedback(`<i class="bi bi-x-circle-fill"></i> NOT FOUND: Barcode <strong>${escapeHtml(code)}</strong> is not in Incoming records.`, 'error');
            }
        } catch (err) {
            console.error(err);
            soundError();
            showFeedback(`<i class="bi bi-exclamation-octagon"></i> Error searching barcode: ${escapeHtml(code)}`, 'error');
        }
    }

    // Add Unit to Scanned / Matched Map
    function addUnitToScannedBatch(unit, triggerGlow = false) {
        const defaultDiag = defaultDiagnosticInput ? defaultDiagnosticInput.value.trim() : 'Test and Clean';
        const defaultPart = defaultPartsInput ? defaultPartsInput.value.trim() : 'GOOD';
        const defaultStat = defaultBatchStatus ? defaultBatchStatus.value : 'In process';

        scannedUnitsMap.set(unit.id, {
            ...unit,
            box_no: unit.box_no || currentActiveBox || 'B1',
            technical_diagnostic: unit.technical_diagnostic || defaultDiag,
            replace_parts: unit.replace_parts || defaultPart,
            repair_status: unit.repair_status || defaultStat,
            justScanned: triggerGlow
        });

        updateAllCounters();
        renderScannedTable();
        renderPoolTable();

        if (triggerGlow) {
            setTimeout(() => {
                const u = scannedUnitsMap.get(unit.id);
                if (u) u.justScanned = false;
            }, 2000);
        }
    }

    function removeUnitFromScannedBatch(unitId) {
        scannedUnitsMap.delete(unitId);
        updateAllCounters();
        renderScannedTable();
        renderPoolTable();
        focusScanner();
    }

    function highlightExistingRow(unitId) {
        const row = document.getElementById(`scannedRow_${unitId}`);
        if (row) {
            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            row.classList.remove('table-just-scanned');
            void row.offsetWidth; // trigger reflow
            row.classList.add('table-just-scanned');
        }
    }

    // Update Counters
    function updateAllCounters() {
        const totalPool = loadedPoolUnits.length;
        const matched = scannedUnitsMap.size;
        const remaining = Math.max(0, totalPool - matched);

        // Calculate unique transmittals involved
        const transmittals = new Set();
        for (let u of scannedUnitsMap.values()) {
            if (u.transmittal_no) transmittals.add(u.transmittal_no);
        }

        countTotalPool.textContent = totalPool;
        countScannedMatched.textContent = matched;
        countRemainingUnscanned.textContent = remaining;
        countTransmittalOrigins.textContent = transmittals.size;

        matchedTableCountBadge.textContent = `${matched} Units`;
        badgeBatchReadyCount.textContent = `${matched} Units Ready for Batching`;
        footerScannedTotal.textContent = matched;
        unscannedListCount.textContent = remaining;

        btnSubmitBatch.disabled = (matched === 0);
    }

    // Render Scanned / Compared Table
    function renderScannedTable() {
        if (scannedUnitsMap.size === 0) {
            scannedTbody.innerHTML = '';
            scannedTbody.appendChild(emptyScannedRow);
            return;
        }

        let html = '';
        let itemIndex = 1;

        for (let [id, u] of scannedUnitsMap.entries()) {
            const glowClass = u.justScanned ? 'table-just-scanned' : '';
            html += `
                <tr id="scannedRow_${id}" class="table-matched-row ${glowClass}">
                    <td style="text-align: center; font-weight: 800; color: var(--dftm-navy);">
                        ${itemIndex}
                        <input type="hidden" name="selected_items[]" value="${id}">
                    </td>
                    <td>
                        <span class="mono" style="font-weight: 700; color: var(--dftm-navy); font-size: 0.95rem;">
                            ${escapeHtml(u.serial_number || '-')}
                        </span>
                    </td>
                    <td>
                        <span class="mono" style="color: #475569;">
                            ${escapeHtml(u.mac_address || '-')}
                        </span>
                    </td>
                    <td>
                        <input type="text" name="row_box[${id}]" value="${escapeHtml(u.box_no || '')}" 
                               class="form-control form-control-sm row-box-input" 
                               data-id="${id}"
                               style="font-weight: 700; font-size: 0.82rem; padding: 4px 8px; text-transform: uppercase;" placeholder="e.g. B1">
                    </td>
                    <td>
                        <strong>${escapeHtml(u.brand || '')}</strong> ${escapeHtml(u.model || '')}
                    </td>
                    <td>
                        <span class="transmittal-tag" title="Source Incoming Transmittal">
                            ${escapeHtml(u.transmittal_no || 'TR-ORIGIN')}
                        </span>
                    </td>
                    <td>
                        <input type="text" name="row_diagnostic[${id}]" value="${escapeHtml(u.technical_diagnostic || 'Test and Clean')}" 
                               class="form-control form-control-sm row-diag-input" 
                               data-id="${id}"
                               style="font-size: 0.82rem; padding: 4px 8px;" placeholder="Diagnostic">
                    </td>
                    <td>
                        <input type="text" name="row_parts[${id}]" value="${escapeHtml(u.replace_parts || 'GOOD')}" 
                               class="form-control form-control-sm row-parts-input" 
                               data-id="${id}"
                               style="font-size: 0.82rem; padding: 4px 8px;" placeholder="Replace Parts">
                    </td>
                    <td>
                        <select name="row_status[${id}]" class="form-select form-select-sm row-status-select" data-id="${id}" style="font-size: 0.82rem; padding: 4px 8px; font-weight: 600;">
                            <option value="In process" ${u.repair_status === 'In process' ? 'selected' : ''}>In process</option>
                            <option value="Repaired" ${u.repair_status === 'Repaired' ? 'selected' : ''}>Repaired</option>
                            <option value="BER" ${u.repair_status === 'BER' ? 'selected' : ''}>BER</option>
                        </select>
                    </td>
                    <td style="text-align: center;">
                        <button type="button" class="btn btn-outline btn-icon btn-sm btn-remove-scanned" data-id="${id}" title="Remove from batch" style="color: #DC2626; border-color: #FECACA; padding: 2px 6px;">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </td>
                </tr>
            `;
            itemIndex++;
        }

        scannedTbody.innerHTML = html;

        // Attach listeners for row inputs to persist state
        scannedTbody.querySelectorAll('.row-box-input').forEach(inp => {
            inp.addEventListener('input', function() {
                const u = scannedUnitsMap.get(parseInt(this.getAttribute('data-id')));
                if (u) u.box_no = this.value;
            });
        });

        scannedTbody.querySelectorAll('.row-diag-input').forEach(inp => {
            inp.addEventListener('input', function() {
                const u = scannedUnitsMap.get(parseInt(this.getAttribute('data-id')));
                if (u) u.technical_diagnostic = this.value;
            });
        });

        scannedTbody.querySelectorAll('.row-parts-input').forEach(inp => {
            inp.addEventListener('input', function() {
                const u = scannedUnitsMap.get(parseInt(this.getAttribute('data-id')));
                if (u) u.replace_parts = this.value;
            });
        });

        scannedTbody.querySelectorAll('.row-status-select').forEach(sel => {
            sel.addEventListener('change', function() {
                const u = scannedUnitsMap.get(parseInt(this.getAttribute('data-id')));
                if (u) u.repair_status = this.value;
            });
        });

        scannedTbody.querySelectorAll('.btn-remove-scanned').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = parseInt(this.getAttribute('data-id'));
                removeUnitFromScannedBatch(id);
            });
        });
    }

    // Render Pool Table (Unscanned items)
    function renderPoolTable() {
        const query = filterPoolSearch ? filterPoolSearch.value.toLowerCase().trim() : '';
        const unscannedUnits = loadedPoolUnits.filter(u => !scannedUnitsMap.has(u.id));

        if (unscannedUnits.length === 0) {
            poolTbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; color: #059669; padding: 30px 20px; font-weight: 700;">
                        <i class="bi bi-check2-all" style="font-size: 1.6rem; display: block; margin-bottom: 4px;"></i>
                        All units for this company have been scanned and verified!
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        unscannedUnits.forEach(u => {
            const sn = u.serial_number || '';
            const mac = u.mac_address || '';
            const brandMod = `${u.brand || ''} ${u.model || ''}`;
            const trans = u.transmittal_no || '';

            if (query && !sn.toLowerCase().includes(query) && !mac.toLowerCase().includes(query) && !brandMod.toLowerCase().includes(query) && !trans.toLowerCase().includes(query)) {
                return;
            }

            html += `
                <tr>
                    <td><span class="mono" style="font-weight: 700; color: var(--dftm-navy);">${escapeHtml(sn || '-')}</span></td>
                    <td><span class="mono">${escapeHtml(mac || '-')}</span></td>
                    <td><strong>${escapeHtml(u.brand || '')}</strong> ${escapeHtml(u.model || '')}</td>
                    <td>${escapeHtml(u.box_no || '-')}</td>
                    <td><span class="transmittal-tag">${escapeHtml(trans)}</span></td>
                    <td>${escapeHtml(u.date_received || '-')}</td>
                    <td style="text-align: center;">
                        <button type="button" class="btn btn-outline btn-sm btn-quick-match" data-id="${u.id}" style="font-weight: 700; color: var(--dftm-navy);">
                            <i class="bi bi-plus-circle"></i> Match
                        </button>
                    </td>
                </tr>
            `;
        });

        poolTbody.innerHTML = html || `<tr><td colspan="7" style="text-align:center; color: var(--dftm-slate); padding: 20px;">No matches found for search query.</td></tr>`;

        poolTbody.querySelectorAll('.btn-quick-match').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = parseInt(this.getAttribute('data-id'));
                const unit = loadedPoolUnits.find(u => u.id === id);
                if (unit) {
                    addUnitToScannedBatch(unit, true);
                    soundSuccess();
                    showFeedback(`Matched unit: ${unit.serial_number}`, 'success');
                }
            });
        });
    }

    if (filterPoolSearch) {
        filterPoolSearch.addEventListener('input', renderPoolTable);
    }

    // Compare All Available
    if (btnCompareAllVisible) {
        btnCompareAllVisible.addEventListener('click', function () {
            if (loadedPoolUnits.length === 0) return;

            Swal.fire({
                title: 'Compare All Available Units?',
                text: `Isasama ba ang lahat ng ${loadedPoolUnits.length} units sa batch na ito?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#00205B',
                confirmButtonText: 'Yes, Match All'
            }).then((res) => {
                if (res.isConfirmed) {
                    loadedPoolUnits.forEach(u => {
                        if (!scannedUnitsMap.has(u.id)) {
                            addUnitToScannedBatch(u, false);
                        }
                    });
                    soundSuccess();
                    showFeedback(`Matched all ${scannedUnitsMap.size} available incoming units!`, 'success');
                }
            });
        });
    }

    // Clear All Scanned
    if (btnClearAllScanned) {
        btnClearAllScanned.addEventListener('click', function () {
            if (scannedUnitsMap.size === 0) return;

            Swal.fire({
                title: 'Clear Scanned List?',
                text: 'Tanggalin lahat ng na-scan na units sa listahang ito?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                confirmButtonText: 'Yes, Clear All'
            }).then((res) => {
                if (res.isConfirmed) {
                    scannedUnitsMap.clear();
                    updateAllCounters();
                    renderScannedTable();
                    renderPoolTable();
                    focusScanner();
                }
            });
        });
    }

    // Auto Assign Boxes (e.g. 20 per box)
    if (btnAutoAssignBoxes) {
        btnAutoAssignBoxes.addEventListener('click', function () {
            if (scannedUnitsMap.size === 0) {
                Swal.fire('No units scanned', 'Mag-scan muna ng mga units.', 'info');
                return;
            }

            Swal.fire({
                title: 'Auto-Assign Box Numbers',
                text: 'How many units per box? (e.g. 20 for B1, B2, B3...)',
                input: 'number',
                inputValue: 20,
                showCancelButton: true,
                confirmButtonColor: '#00205B',
                confirmButtonText: 'Apply Box Numbering'
            }).then((res) => {
                if (res.isConfirmed && res.value > 0) {
                    const unitsPerBox = parseInt(res.value);
                    let index = 0;
                    for (let [id, u] of scannedUnitsMap.entries()) {
                        const boxNumber = Math.floor(index / unitsPerBox) + 1;
                        u.box_no = `B${boxNumber}`;
                        index++;
                    }
                    renderScannedTable();
                    soundSuccess();
                    showFeedback(`Auto-assigned box numbers (${unitsPerBox} units per box).`, 'success');
                }
            });
        });
    }

    // Form Submission Confirmation
    const batchForm = document.getElementById('storeBatchComparingForm');
    if (batchForm) {
        batchForm.addEventListener('submit', function (e) {
            e.preventDefault();

            if (scannedUnitsMap.size === 0) {
                Swal.fire('Walang Na-scan', 'Mag-scan o pumili ng kahit isang unit para makagawa ng batch.', 'warning');
                return;
            }

            const batchNo = inputBatchNo.value.trim();
            const count = scannedUnitsMap.size;

            Swal.fire({
                title: 'Create Traceability Batch?',
                html: `Nais mo bang gawin ang <strong>${escapeHtml(batchNo)}</strong> na may <strong>${count}</strong> units para sa <strong>${escapeHtml(currentCompany)}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                confirmButtonText: '<i class="bi bi-check-circle-fill"></i> Confirm & Save Batch',
                cancelButtonText: 'Cancel'
            }).then((res) => {
                if (res.isConfirmed) {
                    Swal.fire({
                        title: 'Creating Batch...',
                        text: 'Saving batch and assigning units to Traceability Matrix...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                    batchForm.submit();
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
