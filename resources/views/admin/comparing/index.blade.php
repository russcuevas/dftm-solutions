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

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        .spin-icon {
            display: inline-block;
            animation: spin 1s linear infinite;
        }

        .draft-badge-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 700;
            background: #F8FAFC;
            border: 1px solid #CBD5E1;
            color: #334155;
            transition: all 0.2s ease;
        }

        .draft-badge-indicator.saving {
            background: #EFF6FF;
            border-color: #93C5FD;
            color: #1D4ED8;
        }

        .draft-badge-indicator.saved {
            background: #F0FDF4;
            border-color: #86EFAC;
            color: #15803D;
        }

        #draftAlertBanner {
            display: none;
            background: #EFF6FF;
            border: 1.5px solid #3B82F6;
            border-left: 5px solid #2563EB;
            border-radius: 6px;
            padding: 10px 16px;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.08);
        }

        /* Saved Drafts Manager Modal */
        .lookup-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(3px);
            z-index: 99999;
            align-items: center;
            justify-content: center;
        }

        .lookup-modal.active {
            display: flex;
        }

        .lookup-modal-content {
            background: #FFFFFF;
            width: 94%;
            max-width: 820px;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: modalPopIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes modalPopIn {
            from {
                transform: scale(0.95);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .draft-item-card {
            background: #FFFFFF;
            border: 1.5px solid #E2E8F0;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 12px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
        }

        .draft-item-card:hover {
            border-color: #3B82F6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
        }

        .draft-item-card.is-active-draft {
            border-color: #10B981;
            background: #F0FDF4;
            box-shadow: 0 0 0 1px #10B981;
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
            $draftsApiUrl = $isEncoder ? route('encoder.comparing.drafts') : route('admin.comparing.drafts');
        @endphp

        <!-- Top Navigation & Action Toolbar -->
        <div class="matrix-action-bar">
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <a href="{{ $traceabilityIndexUrl }}" class="btn btn-outline btn-sm">
                    <i class="bi bi-arrow-left"></i> View Traceability Records
                </a>

                <!-- Live Auto-Save Draft Status Indicator -->
                <div id="draftStatusIndicator" class="draft-badge-indicator"
                    title="Kusang nag-sa-save ang iyong batch progress bawat scan">
                    <i class="bi bi-cloud text-muted" id="draftStatusIcon"></i>
                    <span id="draftStatusText">Auto-save: Ready</span>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <button type="button" id="btnAudioToggle" class="btn btn-outline btn-sm"
                    style="font-size: 0.8rem; font-weight: 600;">
                    <i class="bi bi-volume-up-fill" id="audioIcon"></i> Sound: ON
                </button>
                <button type="button" id="btnAddScanRow" class="btn btn-secondary btn-sm"
                    style="font-weight: 700; background: #475569; color: #FFF; border: none;">
                    <i class="bi bi-plus-lg"></i> + Add Row
                </button>

                <!-- Saved Drafts Manager Modal Trigger -->
                <button type="button" id="btnOpenDraftsModal" class="btn btn-outline btn-sm"
                    style="font-weight: 700; color: #1E3A8A; border-color: #93C5FD; background: #F0F9FF; display: inline-flex; align-items: center; gap: 6px;"
                    title="Tingnan at gamitin ang mga naka-save na drafts">
                    <i class="bi bi-folder2-open" style="color: #2563EB;"></i> Saved Drafts
                    <span id="draftsCountBadge" class="badge"
                        style="background: #2563EB; color: #FFF; font-size: 0.72rem; padding: 2px 7px; border-radius: 10px;">0</span>
                </button>

                <!-- New Clean Batch Button -->
                <button type="button" id="btnStartNewBatch" class="btn btn-outline btn-sm"
                    style="font-weight: 700; color: #475569; border-color: #CBD5E1; background: #FFFFFF;"
                    title="Magsimula ng panibagong batch spreadsheet nang hindi nabubura ang mga naka-save na drafts">
                    <i class="bi bi-plus-circle"></i> + New Batch
                </button>

                <!-- Save Draft Button -->
                <button type="button" id="btnTopSaveDraft" class="btn btn-outline btn-sm"
                    style="font-weight: 700; color: #1D4ED8; border-color: #93C5FD; background: #EFF6FF;"
                    title="I-save o i-update ang kasalukuyang spreadsheet bilang draft">
                    <i class="bi bi-floppy"></i> Save Draft
                </button>

                <button type="button" id="btnTopSaveBatch" class="btn btn-sm"
                    style="background: #059669; color: #FFFFFF; font-weight: 700; border: none; padding: 6px 18px;">
                    <i class="bi bi-check2-circle"></i> Save Batch to Traceability
                </button>
            </div>
        </div>

        <!-- Draft Alert / Active Draft Banner -->
        <div id="draftAlertBanner">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="bi bi-folder-check" style="color: #2563EB; font-size: 1.35rem;"></i>
                <div style="font-size: 0.88rem; color: #1E3A8A;">
                    <strong id="draftAlertTitle">Aktibong Draft:</strong>
                    <span id="draftAlertDesc">Ipinagpapatuloy ang huling ginagawa. Kusang nag-sa-save bawat scan o
                        pagbabago.</span>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <button type="button" id="btnBannerOpenDrafts" class="btn btn-sm"
                    style="background: #FFFFFF; color: #1E3A8A; border: 1.5px solid #93C5FD; font-weight: 700; font-size: 0.82rem; padding: 5px 14px;">
                    <i class="bi bi-folder2-open"></i> Pumili ng Ibang Draft
                </button>
                <button type="button" id="btnBannerSaveDraft" class="btn btn-sm"
                    style="background: #2563EB; color: #FFFFFF; font-weight: 700; font-size: 0.82rem; border: none; padding: 5px 14px;">
                    <i class="bi bi-floppy"></i> Save Draft
                </button>
                <button type="button" id="btnBannerNewBatch" class="btn btn-sm"
                    style="background: #FFFFFF; color: #475569; border: 1px solid #CBD5E1; font-weight: 700; font-size: 0.82rem; padding: 5px 14px;">
                    <i class="bi bi-plus-circle"></i> Bagong Batch
                </button>
            </div>
        </div>

        <!-- Notification / Scan Toast Feedback Banner -->
        <div id="scanStatusBanner">
            <div id="scanStatusMsg">Ready to scan...</div>
            <span id="scanStatusTime" style="font-size: 0.75rem; opacity: 0.9;"></span>
        </div>

        <!-- Quick Bulk Applicator for Technical Diagnostic & Replaced Parts -->
        <div style="background: #F8FAFC; border: 1.5px solid #CBD5E1; border-radius: 8px; padding: 8px 14px; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                <!-- Bulk Diagnostic -->
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="font-weight: 800; font-size: 0.8rem; color: #1E3A8A; white-space: nowrap;">
                        <i class="bi bi-wrench-adjustable"></i> BULK DIAGNOSTIC:
                    </span>
                    <select id="quickBulkDiagnosticSelect" class="form-select form-select-sm" style="font-size: 0.82rem; font-weight: 600; width: 200px; height: 31px; border: 1px solid #94A3B8;">
                        <option value="">-- Pumili ng Diagnostic --</option>
                        @foreach (\App\Models\InventoryItem::TECHNICAL_DIAGNOSTICS as $diag)
                            <option value="{{ $diag }}">{{ $diag }}</option>
                        @endforeach
                    </select>
                    <button type="button" id="btnQuickApplyDiagnostic" class="btn btn-sm btn-primary" style="font-size: 0.78rem; font-weight: 700; height: 31px; padding: 2px 10px;">
                        Apply to All
                    </button>
                </div>

                <!-- Bulk Parts -->
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="font-weight: 800; font-size: 0.8rem; color: #1E3A8A; white-space: nowrap;">
                        <i class="bi bi-cpu"></i> BULK REPLACED PARTS:
                    </span>
                    <select id="quickBulkPartsSelect" class="form-select form-select-sm" style="font-size: 0.82rem; font-weight: 600; width: 180px; height: 31px; border: 1px solid #94A3B8;">
                        <option value="">-- Pumili ng Part --</option>
                        @foreach (\App\Models\InventoryItem::REPLACED_PARTS as $part)
                            <option value="{{ $part }}">{{ $part }}</option>
                        @endforeach
                    </select>
                    <button type="button" id="btnQuickApplyParts" class="btn btn-sm btn-primary" style="font-size: 0.78rem; font-weight: 700; height: 31px; padding: 2px 10px;">
                        Apply to All
                    </button>
                </div>
            </div>

            <div style="font-size: 0.76rem; color: #64748B; font-weight: 600;">
                <i class="bi bi-info-circle"></i> May dropdown din bawat row sa table sa ibaba.
            </div>
        </div>

        <!-- MAIN SPREADSHEET MATRIX (Immediately Visible on Page Load) -->
        <form action="{{ $storeRoute }}" method="POST" id="storeBatchComparingForm">
            @csrf
            <input type="hidden" name="company_name" id="formCompanyNameInput" value="{{ $selectedCompany }}">
            <input type="hidden" name="draft_id" id="formDraftIdInput" value="">

            <div class="excel-sheet-wrapper">
                <table class="excel-matrix-table" id="directTraceabilityTable">
                    <!-- Title Bar -->
                    <thead>
                        <tr>
                            <th colspan="7" class="excel-main-title">
                                REPAIRED TRACEABILITY MATRIX
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
                            <th rowspan="5" style="width: 100px; text-align: center; vertical-align: middle;">STATUS
                            </th>
                            <th colspan="2" style="text-align: center;">TYPE OF EQUIPMENT</th>
                            <th rowspan="4" style="width: 80px; text-align: center; vertical-align: middle;">BOX NO.
                            </th>
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

                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <button type="button" class="btn btn-outline btn-sm" id="btnBottomOpenDrafts"
                        style="font-weight: 700; color: #1E3A8A; border-color: #93C5FD; background: #F0F9FF; padding: 8px 16px;">
                        <i class="bi bi-folder2-open"></i> Saved Drafts (<span class="drafts-count-num">0</span>)
                    </button>
                    <button type="button" class="btn btn-outline btn-sm" id="btnBottomSaveDraft"
                        style="font-weight: 700; color: #1D4ED8; border-color: #93C5FD; background: #EFF6FF; padding: 8px 18px;">
                        <i class="bi bi-floppy"></i> Save Draft
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnBottomSaveBatch"
                        style="background: #059669; border-color: #059669; padding: 8px 24px; font-weight: 700; font-size: 0.95rem;">
                        <i class="bi bi-check2-circle"></i> Save Batch to Traceability
                    </button>
                </div>
            </div>
        </form>

    </div>

    <!-- Saved Drafts Manager Modal -->
    <div class="lookup-modal" id="savedDraftsModal">
        <div class="lookup-modal-content">
            <div
                style="background: #1E3A8A; color: #FFFFFF; padding: 14px 20px; display: flex; align-items: center; justify-content: space-between;">
                <div style="font-weight: 700; font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-folder2-open" style="color: #60A5FA;"></i> Mga Naka-save na Batch Drafts
                </div>
                <button type="button" id="btnCloseDraftsModal"
                    style="background: transparent; border: none; color: #FFFFFF; font-size: 1.25rem; cursor: pointer; padding: 0;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div
                style="padding: 14px 20px; background: #F8FAFC; border-bottom: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <div style="position: relative; flex: 1; min-width: 240px;">
                    <i class="bi bi-search" style="position: absolute; left: 10px; top: 9px; color: #94A3B8;"></i>
                    <input type="text" id="draftSearchInput" class="excel-cell-input"
                        style="padding-left: 32px; border: 1px solid #CBD5E1; border-radius: 6px; background: #FFF; height: 36px; font-weight: 600;"
                        placeholder="Mag-search ng draft (Company, Batch #, o Pangalan)...">
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="button" id="btnModalNewBatch" class="btn btn-outline btn-sm"
                        style="font-weight: 700; background: #FFF;">
                        <i class="bi bi-plus-circle"></i> Bagong Batch
                    </button>
                    <button type="button" id="btnModalSaveCurrentAsNew" class="btn btn-sm btn-primary"
                        style="font-weight: 700; background: #2563EB;">
                        <i class="bi bi-plus-lg"></i> I-save bilang Bagong Draft
                    </button>
                </div>
            </div>

            <div style="padding: 18px 20px; max-height: 480px; overflow-y: auto;">
                <div id="draftsListContainer">
                    <!-- Populated dynamically via JavaScript -->
                </div>
            </div>

            <div
                style="background: #F8FAFC; border-top: 1px solid #E2E8F0; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between;">
                <span id="draftsTotalSummary" style="font-size: 0.85rem; font-weight: 600; color: #64748B;">0
                    drafts</span>
                <button type="button" class="btn btn-outline btn-sm" id="btnCloseDraftsFooter">Isara (Close)</button>
            </div>
        </div>
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

                // Client-specified options for dropdowns
                const TECHNICAL_DIAGNOSTIC_OPTIONS = @json(\App\Models\InventoryItem::TECHNICAL_DIAGNOSTICS);
                const REPLACED_PARTS_OPTIONS = @json(\App\Models\InventoryItem::REPLACED_PARTS);

                function renderDiagnosticSelect(currentVal, rowKey, idx) {
                    const val = (currentVal || '').trim();
                    let hasMatch = false;
                    let optionsHtml = `<option value="">-- Select Diagnostic --</option>`;
                    
                    if (val && !TECHNICAL_DIAGNOSTIC_OPTIONS.includes(val)) {
                        optionsHtml += `<option value="${escapeHtml(val)}" selected>${escapeHtml(val)}</option>`;
                        hasMatch = true;
                    }
                    
                    TECHNICAL_DIAGNOSTIC_OPTIONS.forEach(opt => {
                        const isSel = (!hasMatch && val.toUpperCase() === opt.toUpperCase()) ? 'selected' : '';
                        if (isSel) hasMatch = true;
                        optionsHtml += `<option value="${escapeHtml(opt)}" ${isSel}>${escapeHtml(opt)}</option>`;
                    });

                    return `
                        <select name="row_diagnostic[${rowKey}]" 
                               class="excel-cell-select row-diag" 
                               data-idx="${idx}" 
                               style="font-size: 0.82rem; padding: 2px 4px; font-weight: 600;">
                            ${optionsHtml}
                        </select>
                    `;
                }

                function renderPartsSelect(currentVal, rowKey, idx) {
                    const val = (currentVal || '').trim();
                    let hasMatch = false;
                    let optionsHtml = `<option value="">-- Select Parts --</option>`;

                    if (val && !REPLACED_PARTS_OPTIONS.includes(val)) {
                        optionsHtml += `<option value="${escapeHtml(val)}" selected>${escapeHtml(val)}</option>`;
                        hasMatch = true;
                    }

                    REPLACED_PARTS_OPTIONS.forEach(opt => {
                        const isSel = (!hasMatch && val.toUpperCase() === opt.toUpperCase()) ? 'selected' : '';
                        if (isSel) hasMatch = true;
                        optionsHtml += `<option value="${escapeHtml(opt)}" ${isSel}>${escapeHtml(opt)}</option>`;
                    });

                    return `
                        <select name="row_parts[${rowKey}]" 
                               class="excel-cell-select row-parts" 
                               data-idx="${idx}" 
                               style="font-size: 0.82rem; padding: 2px 4px; font-weight: 600;">
                            ${optionsHtml}
                        </select>
                    `;
                }

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

                // --- DATABASE-BACKED MULTI-DRAFT PERSISTENCE ENGINE ---
                const DRAFTS_API_URL = '{{ $draftsApiUrl }}';
                const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const USER_STORAGE_SUFFIX = '{{ auth()->id() ?? 'guest' }}';
                const DRAFTS_LIST_KEY = 'dftm_comparing_drafts_' + USER_STORAGE_SUFFIX;
                const ACTIVE_DRAFT_KEY = 'dftm_comparing_active_draft_id_' + USER_STORAGE_SUFFIX;
                const OLD_SINGLE_DRAFT_KEY = 'dftm_comparing_draft_' + USER_STORAGE_SUFFIX;

                let currentDraftId = localStorage.getItem(ACTIVE_DRAFT_KEY) || null;
                let isRestoringDraft = false;
                let autoSaveDebounceTimer = null;
                let cachedDraftsList = [];

                const draftStatusIndicator = document.getElementById('draftStatusIndicator');
                const draftStatusIcon = document.getElementById('draftStatusIcon');
                const draftStatusText = document.getElementById('draftStatusText');
                const draftAlertBanner = document.getElementById('draftAlertBanner');
                const draftAlertTitle = document.getElementById('draftAlertTitle');
                const draftAlertDesc = document.getElementById('draftAlertDesc');
                const formDraftIdInput = document.getElementById('formDraftIdInput');

                // Toolbar & Action Buttons
                const btnTopSaveDraft = document.getElementById('btnTopSaveDraft');
                const btnBottomSaveDraft = document.getElementById('btnBottomSaveDraft');
                const btnBannerSaveDraft = document.getElementById('btnBannerSaveDraft');
                const btnOpenDraftsModal = document.getElementById('btnOpenDraftsModal');
                const btnBottomOpenDrafts = document.getElementById('btnBottomOpenDrafts');
                const btnBannerOpenDrafts = document.getElementById('btnBannerOpenDrafts');
                const btnStartNewBatch = document.getElementById('btnStartNewBatch');
                const btnBannerNewBatch = document.getElementById('btnBannerNewBatch');

                // Modal Elements
                const savedDraftsModal = document.getElementById('savedDraftsModal');
                const btnCloseDraftsModal = document.getElementById('btnCloseDraftsModal');
                const btnCloseDraftsFooter = document.getElementById('btnCloseDraftsFooter');
                const draftSearchInput = document.getElementById('draftSearchInput');
                const draftsListContainer = document.getElementById('draftsListContainer');
                const draftsTotalSummary = document.getElementById('draftsTotalSummary');
                const draftsCountBadge = document.getElementById('draftsCountBadge');
                const btnModalNewBatch = document.getElementById('btnModalNewBatch');
                const btnModalSaveCurrentAsNew = document.getElementById('btnModalSaveCurrentAsNew');

                // Sync currentDraftId to hidden form input for batch submission cleanup
                function syncActiveDraftInput() {
                    if (formDraftIdInput) {
                        formDraftIdInput.value = currentDraftId || '';
                    }
                }
                syncActiveDraftInput();

                // Fetch all drafts from the Database (with fallback to localStorage)
                async function fetchDraftsFromDB() {
                    try {
                        const res = await fetch(DRAFTS_API_URL, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await res.json();
                        if (data && data.success && Array.isArray(data.drafts)) {
                            cachedDraftsList = data.drafts;
                            try {
                                localStorage.setItem(DRAFTS_LIST_KEY, JSON.stringify(cachedDraftsList));
                            } catch (e) {}
                            updateDraftBadges(cachedDraftsList.length);
                            return cachedDraftsList;
                        }
                    } catch (err) {
                        console.warn('Could not reach drafts API, loading from localStorage cache', err);
                    }

                    // Fallback to local cache if offline
                    try {
                        const local = localStorage.getItem(DRAFTS_LIST_KEY);
                        if (local) {
                            cachedDraftsList = JSON.parse(local);
                        }
                    } catch (e) {
                        cachedDraftsList = [];
                    }
                    updateDraftBadges(cachedDraftsList.length);
                    return cachedDraftsList;
                }

                // Update badge numbers
                function updateDraftBadges(customCount = null) {
                    const count = customCount !== null ? customCount : cachedDraftsList.length;
                    if (draftsCountBadge) {
                        draftsCountBadge.textContent = count;
                    }
                    document.querySelectorAll('.drafts-count-num').forEach(el => {
                        el.textContent = count;
                    });
                    if (draftsTotalSummary) {
                        draftsTotalSummary.textContent =
                            `${count} ${count === 1 ? 'naka-save na draft sa database' : 'naka-save na mga draft sa database'}`;
                    }
                }

                // Open Saved Drafts Manager Modal
                async function openDraftsModal() {
                    if (!savedDraftsModal) return;
                    savedDraftsModal.classList.add('active');
                    if (draftSearchInput) {
                        draftSearchInput.value = '';
                    }

                    if (draftsListContainer) {
                        draftsListContainer.innerHTML = `
                            <div style="text-align: center; padding: 40px 20px; color: #64748B;">
                                <div class="spinner-border text-primary" role="status" style="width: 2.2rem; height: 2.2rem; margin-bottom: 12px;"></div>
                                <h6 style="font-weight: 700; color: #334155; margin-bottom: 4px;">Kinukuha ang mga drafts mula sa Database...</h6>
                                <p style="font-size: 0.85rem; margin: 0; color: #64748B;">Maa-access mo ang mga ito kahit saang computer o device.</p>
                            </div>
                        `;
                    }

                    await fetchDraftsFromDB();
                    renderDraftsList('');

                    if (draftSearchInput) {
                        setTimeout(() => draftSearchInput.focus(), 150);
                    }
                }

                // Close Saved Drafts Manager Modal
                function closeDraftsModal() {
                    if (!savedDraftsModal) return;
                    savedDraftsModal.classList.remove('active');
                }

                // Save or Update Draft to Database & LocalStorage
                async function saveDraft(silent = true, asNew = false, customTitle = '') {
                    if (isRestoringDraft) return false;

                    const companyVal = headerCompanySelect ? headerCompanySelect.value : '';
                    const brandVal = headerBrandSelect ? headerBrandSelect.value : '';
                    const modelVal = headerModelSelect ? headerModelSelect.value : '';
                    const batchNoVal = inputBatchNo ? inputBatchNo.value.trim() : '';
                    const dateVal = inputDateDelivered ? inputDateDelivered.value : '';
                    const statusVal = headerBatchStatus ? headerBatchStatus.value : 'GOOD';

                    const rowsWithData = tableRows.filter(r => (r.serial_number && r.serial_number.trim() !== '') ||
                        (r.mac_address && r.mac_address.trim() !== '') || r.id);
                    const verifiedUnits = tableRows.filter(r => r.is_matched && r.id);

                    // If completely blank and silent auto-save, skip saving
                    if (!companyVal && rowsWithData.length === 0 && silent && !currentDraftId) {
                        return false;
                    }

                    const now = new Date();
                    const timeOnly = now.toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });

                    let draftTitle = (customTitle || '').trim();
                    if (!draftTitle) {
                        const existingDraft = cachedDraftsList.find(d => String(d.id) === String(currentDraftId));
                        if (!asNew && existingDraft && existingDraft.title) {
                            draftTitle = existingDraft.title;
                        } else {
                            draftTitle = (companyVal ? companyVal + ' - ' : '') + (batchNoVal ? batchNoVal : (
                                'Draft (' + timeOnly + ')'));
                        }
                    }

                    const payload = {
                        id: asNew ? null : currentDraftId,
                        as_new: asNew,
                        title: draftTitle,
                        company_name: companyVal,
                        brand: brandVal,
                        model: modelVal,
                        batch_no: batchNoVal,
                        date_delivered: dateVal,
                        status: statusVal,
                        sound_enabled: soundEnabled,
                        rows: tableRows
                    };

                    // Local fast cache save
                    try {
                        localStorage.setItem('dftm_comparing_last_working_sheet_' + USER_STORAGE_SUFFIX, JSON
                            .stringify(payload));
                    } catch (e) {}

                    try {
                        const res = await fetch(DRAFTS_API_URL, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': CSRF_TOKEN,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await res.json();
                        if (data && data.success && data.draft_id) {
                            currentDraftId = data.draft_id;
                            localStorage.setItem(ACTIVE_DRAFT_KEY, currentDraftId);
                            syncActiveDraftInput();

                            // Update Indicators
                            if (draftStatusIndicator) {
                                draftStatusIndicator.classList.remove('saving');
                                draftStatusIndicator.classList.add('saved');
                                if (draftStatusIcon) draftStatusIcon.className =
                                'bi bi-database-check text-success';
                                if (draftStatusText) {
                                    draftStatusText.textContent =
                                        `DB Saved: ${timeOnly} (${verifiedUnits.length} verified)`;
                                }
                            }

                            if (draftAlertBanner) {
                                draftAlertBanner.style.display = 'flex';
                                if (draftAlertTitle) {
                                    draftAlertTitle.textContent =
                                        `Aktibong Database Draft: ${data.title || draftTitle}`;
                                }
                                if (draftAlertDesc) {
                                    draftAlertDesc.textContent =
                                        `${verifiedUnits.length} verified units para sa ${companyVal || 'kumpanya'}. Naka-sync sa database noong ${timeOnly}.`;
                                }
                            }

                            // Refresh cached list silently in background
                            fetchDraftsFromDB();

                            if (!silent) {
                                Swal.fire({
                                    title: 'Draft Naka-save sa Database!',
                                    html: `Matagumpay na nai-save ang draft na <strong>${escapeHtml(data.title || draftTitle)}</strong> na may <strong>${verifiedUnits.length} verified units</strong>.<br><br><span style="color: #059669; font-weight: 700;"><i class="bi bi-cloud-check-fill"></i> Naka-sync sa Database:</span> Maaari mo na itong buksan at ipagpatuloy kahit saang computer o device!`,
                                    icon: 'success',
                                    showCancelButton: true,
                                    confirmButtonColor: '#059669',
                                    cancelButtonColor: '#2563EB',
                                    confirmButtonText: '<i class="bi bi-play-fill"></i> Ipagpatuloy ang Pag-scan',
                                    cancelButtonText: '<i class="bi bi-folder2-open"></i> Buksan ang Saved Drafts'
                                }).then((res) => {
                                    if (res.dismiss === Swal.DismissReason.cancel) {
                                        openDraftsModal();
                                    }
                                });
                            }

                            return true;
                        }
                    } catch (e) {
                        console.error('Failed to sync draft to database', e);
                        if (draftStatusIndicator) {
                            draftStatusIndicator.classList.remove('saving');
                            if (draftStatusText) draftStatusText.textContent = 'Auto-saved locally (Offline)';
                        }
                        if (!silent) {
                            Swal.fire('Offline Save',
                                'Nai-save ang draft sa local browser cache, ngunit may aberya sa koneksyon sa database.',
                                'warning');
                        }
                        return false;
                    }
                }

                // Debounced Auto-Save
                function triggerAutoSave(immediate = false) {
                    if (isRestoringDraft) return;

                    if (draftStatusIndicator) {
                        draftStatusIndicator.classList.add('saving');
                        if (draftStatusIcon) draftStatusIcon.className = 'bi bi-arrow-repeat spin-icon text-primary';
                        if (draftStatusText) draftStatusText.textContent = 'Saving to database...';
                    }

                    if (immediate) {
                        if (autoSaveDebounceTimer) clearTimeout(autoSaveDebounceTimer);
                        saveDraft(true, false);
                        return;
                    }

                    if (autoSaveDebounceTimer) clearTimeout(autoSaveDebounceTimer);
                    autoSaveDebounceTimer = setTimeout(() => {
                        saveDraft(true, false);
                    }, 500);
                }

                // Prompt user to manually save draft or name a new draft
                function promptManualSaveDraft(forceNew = false) {
                    const activeDraft = currentDraftId ? cachedDraftsList.find(d => String(d.id) === String(
                        currentDraftId)) : null;
                    const companyVal = headerCompanySelect ? headerCompanySelect.value : '';
                    const batchNoVal = inputBatchNo ? inputBatchNo.value.trim() : '';

                    let defaultName = '';
                    if (!forceNew && activeDraft && activeDraft.title) {
                        defaultName = activeDraft.title;
                    } else {
                        defaultName = (companyVal ? companyVal + ' - ' : '') + (batchNoVal || ('Batch Draft (' +
                            new Date().toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit'
                            }) + ')'));
                    }

                    Swal.fire({
                        title: forceNew ?
                            '<i class="bi bi-database-add text-primary"></i> I-save bilang Bagong Database Draft' :
                            '<i class="bi bi-database-check text-primary"></i> I-save sa Database',
                        text: 'Maglagay ng pangalan o title para sa draft na ito upang madali mo itong makilala sa anumang device:',
                        input: 'text',
                        inputValue: defaultName,
                        inputPlaceholder: 'Hal: PLDT Batch 10 - Nov 15',
                        showCancelButton: true,
                        confirmButtonColor: '#2563EB',
                        cancelButtonColor: '#64748B',
                        confirmButtonText: '<i class="bi bi-floppy"></i> I-save sa Database',
                        cancelButtonText: 'Kanselahin',
                        inputValidator: (val) => {
                            if (!val || !val.trim()) {
                                return 'Pakilagay ang pangalan ng draft!';
                            }
                        }
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            await saveDraft(false, forceNew, result.value.trim());
                            if (savedDraftsModal && savedDraftsModal.classList.contains('active')) {
                                await fetchDraftsFromDB();
                                renderDraftsList(draftSearchInput ? draftSearchInput.value : '');
                            }
                        }
                    });
                }

                // Render dynamic card list in Saved Drafts Manager Modal
                function renderDraftsList(filterText = '') {
                    if (!draftsListContainer) return;

                    const drafts = cachedDraftsList;
                    updateDraftBadges(drafts.length);

                    const search = (filterText || '').toLowerCase().trim();
                    const filtered = drafts.filter(d => {
                        if (!search) return true;
                        const corpus =
                            `${d.title || ''} ${d.company_name || ''} ${d.batch_no || ''} ${d.brand || ''} ${d.model || ''} ${d.created_by || ''}`
                            .toLowerCase();
                        return corpus.includes(search);
                    });

                    if (filtered.length === 0) {
                        draftsListContainer.innerHTML = `
                            <div style="text-align: center; padding: 40px 20px; color: #64748B;">
                                <i class="bi bi-database-slash" style="font-size: 3rem; color: #CBD5E1; display: block; margin-bottom: 12px;"></i>
                                <h5 style="font-weight: 700; color: #334155; margin-bottom: 6px;">${search ? 'Walang Tumutugmang Draft sa Database' : 'Walang Naka-save na Drafts sa Database'}</h5>
                                <p style="font-size: 0.88rem; margin: 0 0 16px 0; color: #64748B;">
                                    ${search ? `Walang nahanap para sa query na "${escapeHtml(search)}".` : 'Wala pang nai-save na batch drafts sa database. I-click ang button sa ibaba upang i-save ang kasalukuyang spreadsheet.'}
                                </p>
                                <button type="button" class="btn btn-sm btn-primary" id="btnModalEmptySave" style="font-weight: 700; background: #2563EB;">
                                    <i class="bi bi-plus-lg"></i> I-save ang Kasalukuyang Spreadsheet bilang Draft
                                </button>
                            </div>
                        `;
                        const btnEmpty = document.getElementById('btnModalEmptySave');
                        if (btnEmpty) {
                            btnEmpty.addEventListener('click', () => promptManualSaveDraft(true));
                        }
                        return;
                    }

                    let html = '';
                    filtered.forEach(d => {
                        const isActive = String(d.id) === String(currentDraftId);
                        const verified = d.verified_count || 0;
                        const totalRows = d.total_rows || 0;
                        const company = d.company_name || 'Walang Company';
                        const batchNo = d.batch_no || 'Walang Batch #';
                        const brand = d.brand || 'Lahat ng Brand';
                        const model = d.model || 'Lahat ng Model';
                        const updatedTime = d.updated_at_formatted || 'kamakailan';
                        const creator = d.created_by || 'Staff';

                        html += `
                            <div class="draft-item-card ${isActive ? 'is-active-draft' : ''}" id="draftCard_${d.id}">
                                <div style="flex: 1; min-width: 280px;">
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap;">
                                        <h5 style="margin: 0; font-size: 1rem; font-weight: 800; color: #1E293B;">
                                            ${escapeHtml(d.title || 'Untitled Draft')}
                                        </h5>
                                        ${isActive ? `
                                                    <span class="badge" style="background: #10B981; color: #FFFFFF; font-size: 0.72rem; padding: 3px 8px; border-radius: 4px; font-weight: 700;">
                                                        <i class="bi bi-check-circle-fill"></i> Kasalukuyang Aktibo
                                                    </span>
                                                ` : ''}
                                        <span class="badge" style="background: #E0E7FF; color: #3730A3; font-size: 0.72rem; padding: 3px 8px; border-radius: 4px; font-weight: 600;">
                                            <i class="bi bi-person-fill"></i> ${escapeHtml(creator)}
                                        </span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 10px; font-size: 0.82rem; color: #475569; flex-wrap: wrap; margin-bottom: 6px;">
                                        <span><strong>Company:</strong> <span style="color: #1E3A8A; font-weight: 700;">${escapeHtml(company)}</span></span>
                                        <span>•</span>
                                        <span><strong>Batch #:</strong> <span style="font-weight: 700;">${escapeHtml(batchNo)}</span></span>
                                        <span>•</span>
                                        <span><strong>Model:</strong> ${escapeHtml(brand)} / ${escapeHtml(model)}</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 12px; font-size: 0.78rem; color: #64748B; flex-wrap: wrap;">
                                        <span style="background: #EFF6FF; color: #1D4ED8; padding: 2px 8px; border-radius: 4px; font-weight: 700;">
                                            <i class="bi bi-check2-circle"></i> ${verified} Verified Units
                                        </span>
                                        <span style="background: #F1F5F9; color: #475569; padding: 2px 8px; border-radius: 4px; font-weight: 600;">
                                            <i class="bi bi-table"></i> ${totalRows} Total Rows
                                        </span>
                                        <span style="color: #94A3B8;">
                                            <i class="bi bi-database"></i> Database: ${escapeHtml(updatedTime)}
                                        </span>
                                    </div>
                                </div>

                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <button type="button" class="btn btn-sm btn-use-draft" data-draft-id="${d.id}"
                                        style="font-weight: 700; font-size: 0.84rem; padding: 6px 16px; ${isActive ? 'background: #059669; color: #FFF; border: none;' : 'background: #2563EB; color: #FFF; border: none;'}"
                                        title="I-load at gamitin ang draft na ito mula sa database">
                                        <i class="bi ${isActive ? 'bi-arrow-repeat' : 'bi-box-arrow-in-right'}"></i> ${isActive ? 'Gamitin / I-reload' : 'Gamitin (Use Draft)'}
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm btn-rename-draft" data-draft-id="${d.id}"
                                        style="font-weight: 600; font-size: 0.8rem; padding: 6px 12px; color: #475569; border-color: #CBD5E1; background: #FFF;"
                                        title="Palitan ang pangalan ng draft sa database">
                                        <i class="bi bi-pencil"></i> Rename
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm btn-delete-draft" data-draft-id="${d.id}"
                                        style="font-weight: 600; font-size: 0.8rem; padding: 6px 12px; color: #DC2626; border-color: #FCA5A5; background: #FFF;"
                                        title="Burahin ang draft na ito sa database">
                                        <i class="bi bi-trash"></i> Burahin
                                    </button>
                                </div>
                            </div>
                        `;
                    });

                    draftsListContainer.innerHTML = html;

                    // Attach card action listeners
                    draftsListContainer.querySelectorAll('.btn-use-draft').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const id = this.getAttribute('data-draft-id');
                            loadAndUseDraft(id);
                        });
                    });

                    draftsListContainer.querySelectorAll('.btn-rename-draft').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const id = this.getAttribute('data-draft-id');
                            renameDraft(id);
                        });
                    });

                    draftsListContainer.querySelectorAll('.btn-delete-draft').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const id = this.getAttribute('data-draft-id');
                            deleteDraft(id);
                        });
                    });
                }

                // Load and switch to selected draft from Database
                async function loadAndUseDraft(draftId) {
                    Swal.fire({
                        title: 'Kinukuha mula sa Database...',
                        text: 'I-dinadownload ang draft records at mga rows...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        const res = await fetch(`${DRAFTS_API_URL}/${draftId}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await res.json();

                        if (!data || !data.success || !data.draft) {
                            Swal.fire('Draft Not Found', 'Hindi natagpuan ang draft sa database.', 'error');
                            return;
                        }

                        const targetDraft = data.draft;

                        // Silently save current draft before switching if user already has data on screen
                        if (currentDraftId && String(currentDraftId) !== String(draftId)) {
                            await saveDraft(true, false);
                        }

                        currentDraftId = targetDraft.id;
                        localStorage.setItem(ACTIVE_DRAFT_KEY, currentDraftId);
                        syncActiveDraftInput();
                        closeDraftsModal();

                        await restoreDraft(targetDraft);
                    } catch (err) {
                        console.error('Error loading draft from database', err);
                        Swal.fire('Error', 'Hindi ma-load ang draft mula sa database.', 'error');
                    }
                }

                // Rename draft title in Database
                async function renameDraft(draftId) {
                    const target = cachedDraftsList.find(d => String(d.id) === String(draftId));
                    const currentTitle = target ? target.title : '';

                    Swal.fire({
                        title: 'Palitan ang Pangalan ng Draft',
                        input: 'text',
                        inputValue: currentTitle,
                        inputPlaceholder: 'Bagong pangalan ng draft...',
                        showCancelButton: true,
                        confirmButtonColor: '#2563EB',
                        cancelButtonColor: '#64748B',
                        confirmButtonText: '<i class="bi bi-check-lg"></i> I-save sa Database',
                        cancelButtonText: 'Kanselahin',
                        inputValidator: (val) => {
                            if (!val || !val.trim()) {
                                return 'Pakilagay ang bagong pangalan!';
                            }
                        }
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            const newTitle = result.value.trim();
                            try {
                                const res = await fetch(`${DRAFTS_API_URL}/${draftId}/rename`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': CSRF_TOKEN,
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                    body: JSON.stringify({
                                        title: newTitle
                                    })
                                });
                                const data = await res.json();
                                if (data && data.success) {
                                    if (target) target.title = newTitle;
                                    if (String(currentDraftId) === String(draftId) && draftAlertTitle) {
                                        draftAlertTitle.textContent = `Aktibong Draft: ${newTitle}`;
                                    }
                                    renderDraftsList(draftSearchInput ? draftSearchInput.value : '');
                                    showStatus(
                                        `Pangalan ng draft na-update sa database: "${escapeHtml(newTitle)}"`,
                                        'success');
                                }
                            } catch (e) {
                                Swal.fire('Error', 'Hindi na-update ang pangalan sa database.',
                                'error');
                            }
                        }
                    });
                }

                // Delete a draft from Database
                function deleteDraft(draftId) {
                    const target = cachedDraftsList.find(d => String(d.id) === String(draftId));
                    const titleStr = target ? target.title : 'Draft';
                    const verified = target ? target.verified_count : 0;

                    Swal.fire({
                        title: 'Burahin ang Draft sa Database?',
                        html: `Sigurado ka bang nais mong burahin ang draft na <strong>${escapeHtml(titleStr)}</strong> mula sa database?<br><br>${verified > 0 ? `<span style="color: #DC2626; font-weight: 700;">Mawawala ang ${verified} verified units na na-scan sa draft na ito.</span>` : 'Mabubura ito para sa lahat ng workstations.'}`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#DC2626',
                        cancelButtonColor: '#64748B',
                        confirmButtonText: '<i class="bi bi-trash"></i> Oo, Burahin sa Database',
                        cancelButtonText: 'Kanselahin'
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            try {
                                const res = await fetch(`${DRAFTS_API_URL}/${draftId}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': CSRF_TOKEN,
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                });
                                const data = await res.json();
                                if (data && data.success) {
                                    cachedDraftsList = cachedDraftsList.filter(d => String(d.id) !== String(
                                        draftId));
                                    updateDraftBadges(cachedDraftsList.length);

                                    // If deleted draft was active, clear pointer
                                    if (String(currentDraftId) === String(draftId)) {
                                        currentDraftId = null;
                                        localStorage.removeItem(ACTIVE_DRAFT_KEY);
                                        syncActiveDraftInput();
                                        if (draftAlertBanner) draftAlertBanner.style.display = 'none';
                                        if (draftStatusIndicator) {
                                            draftStatusIndicator.classList.remove('saving', 'saved');
                                            if (draftStatusIcon) draftStatusIcon.className =
                                                'bi bi-database text-muted';
                                            if (draftStatusText) draftStatusText.textContent =
                                                'Auto-save: Ready';
                                        }
                                    }

                                    renderDraftsList(draftSearchInput ? draftSearchInput.value : '');
                                    showStatus(`Draft "${escapeHtml(titleStr)}" nabura sa database.`,
                                        'duplicate');
                                }
                            } catch (e) {
                                Swal.fire('Error', 'Hindi nabura ang draft sa database.', 'error');
                            }
                        }
                    });
                }

                // Start clean new batch without losing any database drafts
                function startNewBatch(confirmBefore = true) {
                    const doNewBatch = async () => {
                        // Silently save current draft before clearing if it has data
                        const hasData = tableRows.some(r => (r.serial_number && r.serial_number.trim() !==
                            '') || (r.mac_address && r.mac_address.trim() !== '') || r.id);
                        if (hasData && currentDraftId) {
                            await saveDraft(true, false);
                        }

                        // Clear active draft pointer
                        currentDraftId = null;
                        localStorage.removeItem(ACTIVE_DRAFT_KEY);
                        syncActiveDraftInput();

                        // Reset form fields
                        if (headerCompanySelect) headerCompanySelect.value = '';
                        currentCompany = '';
                        formCompanyInput.value = '';
                        loadedPoolUnits = [];
                        currentBrandsData = [];
                        if (headerBrandSelect) {
                            headerBrandSelect.innerHTML =
                            '<option value="">-- Select Company First --</option>';
                            headerBrandSelect.disabled = true;
                        }
                        if (headerModelSelect) {
                            headerModelSelect.innerHTML =
                            '<option value="">-- Select Company First --</option>';
                            headerModelSelect.disabled = true;
                        }
                        if (inputBatchNo) inputBatchNo.value = '';
                        if (inputDateDelivered) inputDateDelivered.value = '';
                        if (headerBatchStatus) headerBatchStatus.value = 'GOOD';

                        // Reset spreadsheet table
                        tableRows = [];
                        addBlankRows(1);
                        renderTable();
                        updateCounters();

                        // Reset UI indicators
                        if (draftAlertBanner) draftAlertBanner.style.display = 'none';
                        if (draftStatusIndicator) {
                            draftStatusIndicator.classList.remove('saving', 'saved');
                            if (draftStatusIcon) draftStatusIcon.className = 'bi bi-database text-muted';
                            if (draftStatusText) draftStatusText.textContent = 'Auto-save: Ready';
                        }

                        closeDraftsModal();
                        focusCell(0, 'serial');
                        showStatus(
                            '<i class="bi bi-stars"></i> Nakahanda na ang bagong spreadsheet. Ang iyong mga drafts ay ligtas sa Database.',
                            'success');
                    };

                    if (!confirmBefore) {
                        doNewBatch();
                        return;
                    }

                    const verifiedCount = tableRows.filter(r => r.is_matched && r.id).length;
                    const hasData = verifiedCount > 0 || tableRows.some(r => (r.serial_number && r.serial_number
                    .trim() !== '') || (r.mac_address && r.mac_address.trim() !== ''));

                    if (!hasData) {
                        doNewBatch();
                        return;
                    }

                    Swal.fire({
                        title: 'Magsimula ng Panibagong Batch?',
                        html: `
                            <p style="font-size: 0.95rem; margin-bottom: 12px; color: #334155;">
                                Nais mo bang magbukas ng malinis na spreadsheet para sa bagong batch?
                            </p>
                            <div style="background: #F0FDF4; border: 1.5px solid #86EFAC; border-radius: 8px; padding: 12px 16px; font-size: 0.88rem; color: #166534; text-align: left;">
                                <i class="bi bi-database-check"></i> <strong>Ligtas sa Database:</strong> Ang kasalukuyang spreadsheet ay mananatiling naka-save sa database at maaari mong balikan o i-load kahit saang computer anumang oras.
                            </div>
                        `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#2563EB',
                        cancelButtonColor: '#64748B',
                        confirmButtonText: '<i class="bi bi-plus-circle"></i> Oo, Buksan ang Bagong Batch',
                        cancelButtonText: 'Kanselahin'
                    }).then((res) => {
                        if (res.isConfirmed) {
                            doNewBatch();
                        }
                    });
                }

                // Restore a draft object into spreadsheet
                async function restoreDraft(draft) {
                    if (!draft) return;
                    isRestoringDraft = true;

                    Swal.fire({
                        title: 'Binubuksan ang Draft...',
                        text: `Kinukuha ang data para sa "${draft.title || draft.company_name || 'Draft'}"...`,
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        currentDraftId = draft.id;
                        localStorage.setItem(ACTIVE_DRAFT_KEY, currentDraftId);
                        syncActiveDraftInput();

                        // 1. Restore Company
                        if (draft.company_name) {
                            headerCompanySelect.value = draft.company_name;
                            currentCompany = draft.company_name;
                            formCompanyInput.value = draft.company_name;
                            await fetchCompanyUnits(draft.company_name);
                        }

                        // 2. Restore Brand & Model dropdowns
                        if (draft.brand) {
                            headerBrandSelect.value = draft.brand;
                            populateModelDropdown(draft.brand, draft.model || '');
                            if (draft.model) {
                                headerModelSelect.value = draft.model;
                            }
                        }

                        // 3. Restore Metadata
                        if (draft.batch_no) {
                            inputBatchNo.value = draft.batch_no;
                        }
                        if (draft.date_delivered && inputDateDelivered) {
                            inputDateDelivered.value = draft.date_delivered;
                        }
                        if (draft.status && headerBatchStatus) {
                            headerBatchStatus.value = draft.status;
                        }
                        if (typeof draft.sound_enabled === 'boolean') {
                            soundEnabled = draft.sound_enabled;
                            if (btnAudioToggle) {
                                btnAudioToggle.innerHTML = soundEnabled ?
                                    '<i class="bi bi-volume-up-fill"></i> Sound: ON' :
                                    '<i class="bi bi-volume-mute-fill"></i> Sound: OFF';
                            }
                        }

                        // 4. Restore Rows
                        if (Array.isArray(draft.rows) && draft.rows.length > 0) {
                            tableRows = JSON.parse(JSON.stringify(draft.rows));
                        } else {
                            tableRows = [];
                            addBlankRows(1);
                        }

                        renderTable();
                        updateCounters();

                        isRestoringDraft = false;
                        Swal.close();

                        // 5. Update Status Indicators & Alert Banner
                        const verifiedCount = tableRows.filter(r => r.is_matched && r.id).length;
                        if (draftStatusIndicator) {
                            draftStatusIndicator.classList.add('saved');
                            if (draftStatusIcon) draftStatusIcon.className = 'bi bi-database-check text-success';
                            if (draftStatusText) {
                                draftStatusText.textContent = `Draft: Loaded from DB (${verifiedCount} verified)`;
                            }
                        }

                        if (draftAlertBanner) {
                            draftAlertBanner.style.display = 'flex';
                            if (draftAlertTitle) {
                                draftAlertTitle.textContent =
                                    `Aktibong Draft: ${draft.title || draft.company_name || 'Draft'}`;
                            }
                            if (draftAlertDesc) {
                                draftAlertDesc.textContent =
                                    `${verifiedCount} verified units para sa ${draft.company_name || 'kumpanya'}. Huling binago: ${draft.updated_at_formatted || 'kamakailan'}.`;
                            }
                        }

                        showStatus(
                            `<i class="bi bi-check-circle-fill"></i> Naka-load na ang draft: <strong>${escapeHtml(draft.title || draft.company_name || 'Draft')}</strong> mula sa database! Maaari nang ipagpatuloy ang pag-scan.`,
                            'success');

                        // 6. Focus on next empty cell or missing MAC cell
                        let nextFocusIdx = tableRows.findIndex(r => !r.serial_number);
                        if (nextFocusIdx === -1) {
                            nextFocusIdx = tableRows.findIndex(r => r.serial_number && !r.mac_address);
                            if (nextFocusIdx !== -1) {
                                focusCell(nextFocusIdx, 'mac');
                            } else {
                                addBlankRows(1);
                                focusCell(tableRows.length - 1, 'serial');
                            }
                        } else {
                            focusCell(nextFocusIdx, 'serial');
                        }
                    } catch (err) {
                        console.error('Error during draft restore', err);
                        isRestoringDraft = false;
                        Swal.close();
                        showStatus('May naganap na error habang binubuksan ang draft.', 'error');
                    }
                }

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
                } // Add blank rows (Default starts with 1 row)
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
                            technical_diagnostic: '',
                            replace_parts: 'N/A',
                            repair_status: defaultStatus,
                            is_matched: false
                        });
                    }

                    renderTable();
                    updateCounters();
                    triggerAutoSave(true);
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
                                ${renderDiagnosticSelect(r.technical_diagnostic, r.id || 'row_' + idx, idx)}
                            </td>

                            <!-- REPLACE PARTS -->
                            <td>
                                ${renderPartsSelect(r.replace_parts, r.id || 'row_' + idx, idx)}
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
                            triggerAutoSave(false);
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
                            triggerAutoSave(false);
                        });
                    });

                    // Other cell inputs
                    matrixTbody.querySelectorAll('.row-diag').forEach(sel => {
                        sel.addEventListener('change', function() {
                            tableRows[parseInt(this.getAttribute('data-idx'))].technical_diagnostic =
                                this.value;
                            triggerAutoSave(true);
                        });
                    });

                    matrixTbody.querySelectorAll('.row-parts').forEach(sel => {
                        sel.addEventListener('change', function() {
                            tableRows[parseInt(this.getAttribute('data-idx'))].replace_parts = this
                                .value;
                            triggerAutoSave(true);
                        });
                    });

                    matrixTbody.querySelectorAll('.row-box').forEach(inp => {
                        inp.addEventListener('input', function() {
                            tableRows[parseInt(this.getAttribute('data-idx'))].box_no = this.value;
                            updateCounters();
                            triggerAutoSave(false);
                        });
                    });

                    matrixTbody.querySelectorAll('.row-stat').forEach(sel => {
                        sel.addEventListener('change', function() {
                            const idx = parseInt(this.getAttribute('data-idx'));
                            tableRows[idx].repair_status = this.value;
                            renderTable();
                            triggerAutoSave(true);
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
                                triggerAutoSave(true);
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
                                    .technical_diagnostic || matchedByMac.technical_diagnostic || '';
                                tableRows[rowIndex].replace_parts = tableRows[rowIndex].replace_parts ||
                                    matchedByMac.replace_parts || 'N/A';
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
                            matchedPoolItem.technical_diagnostic || '';
                        tableRows[rowIndex].replace_parts = tableRows[rowIndex].replace_parts || matchedPoolItem
                            .replace_parts || 'N/A';
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
                                item.technical_diagnostic || '';
                            tableRows[rowIndex].replace_parts = tableRows[rowIndex].replace_parts || item
                                .replace_parts || 'N/A';
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

                    // Automatically update draft state if not currently restoring
                    if (!isRestoringDraft) {
                        triggerAutoSave(true);
                    }
                }

                // Header Events
                if (headerCompanySelect) {
                    headerCompanySelect.addEventListener('change', function() {
                        const comp = this.value;
                        const currentVerified = tableRows.filter(r => (r.serial_number && r.serial_number
                        .trim() !== '') || r.id).length;

                        // Confirm before switching if user already has scanned units
                        if (currentCompany && comp !== currentCompany && currentVerified > 0) {
                            Swal.fire({
                                title: 'Palitan ang Company?',
                                html: `May <strong>${currentVerified}</strong> units ka nang na-scan para sa <strong>${escapeHtml(currentCompany)}</strong>.<br><br>Ang kasalukuyang sheet ay mai-save sa iyong <strong>Saved Drafts</strong> bago lumipat sa <strong>${escapeHtml(comp || 'ibang company')}</strong>. Nais mo bang magpatuloy?`,
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonColor: '#2563EB',
                                cancelButtonColor: '#64748B',
                                confirmButtonText: '<i class="bi bi-arrow-left-right"></i> Oo, I-save ang Draft at Lumipat',
                                cancelButtonText: 'Kanselahin'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Save current draft before switching company
                                    saveDraft(true, false);
                                    currentDraftId = null;
                                    localStorage.removeItem(ACTIVE_DRAFT_KEY);

                                    tableRows = [];
                                    addBlankRows(1);
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
                                    if (draftAlertBanner) draftAlertBanner.style.display = 'none';
                                } else {
                                    headerCompanySelect.value = currentCompany;
                                }
                            });
                            return;
                        }

                        if (comp) {
                            fetchCompanyUnits(comp);
                            triggerAutoSave(true);
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
                            triggerAutoSave(true);
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
                        triggerAutoSave(true);
                    });
                }

                if (headerModelSelect) {
                    headerModelSelect.addEventListener('change', function() {
                        if (currentCompany) {
                            fetchCompanyUnits(currentCompany);
                        }
                        triggerAutoSave(true);
                    });
                }

                if (inputBatchNo) {
                    inputBatchNo.addEventListener('input', function() {
                        triggerAutoSave(false);
                    });
                }

                const inputDateDelivered = document.getElementById('inputDateDelivered');
                if (inputDateDelivered) {
                    inputDateDelivered.addEventListener('change', function() {
                        triggerAutoSave(true);
                    });
                }

                if (headerBatchStatus) {
                    headerBatchStatus.addEventListener('change', function() {
                        triggerAutoSave(true);
                    });
                }

                // --- DRAFT & MODAL EVENT LISTENERS ---
                // Save Draft buttons
                if (btnTopSaveDraft) {
                    btnTopSaveDraft.addEventListener('click', () => promptManualSaveDraft(false));
                }
                if (btnBottomSaveDraft) {
                    btnBottomSaveDraft.addEventListener('click', () => promptManualSaveDraft(false));
                }
                if (btnBannerSaveDraft) {
                    btnBannerSaveDraft.addEventListener('click', () => promptManualSaveDraft(false));
                }

                // Open Saved Drafts Modal buttons
                if (btnOpenDraftsModal) {
                    btnOpenDraftsModal.addEventListener('click', openDraftsModal);
                }
                if (btnBottomOpenDrafts) {
                    btnBottomOpenDrafts.addEventListener('click', openDraftsModal);
                }
                if (btnBannerOpenDrafts) {
                    btnBannerOpenDrafts.addEventListener('click', openDraftsModal);
                }

                // Start New Batch buttons
                if (btnStartNewBatch) {
                    btnStartNewBatch.addEventListener('click', () => startNewBatch(true));
                }
                if (btnBannerNewBatch) {
                    btnBannerNewBatch.addEventListener('click', () => startNewBatch(true));
                }
                if (btnModalNewBatch) {
                    btnModalNewBatch.addEventListener('click', () => startNewBatch(true));
                }

                // Modal Close & Extra Buttons
                if (btnCloseDraftsModal) {
                    btnCloseDraftsModal.addEventListener('click', closeDraftsModal);
                }
                if (btnCloseDraftsFooter) {
                    btnCloseDraftsFooter.addEventListener('click', closeDraftsModal);
                }
                if (btnModalSaveCurrentAsNew) {
                    btnModalSaveCurrentAsNew.addEventListener('click', () => promptManualSaveDraft(true));
                }
                if (savedDraftsModal) {
                    savedDraftsModal.addEventListener('click', function(e) {
                        if (e.target === savedDraftsModal) closeDraftsModal();
                    });
                }
                if (draftSearchInput) {
                    draftSearchInput.addEventListener('input', function() {
                        renderDraftsList(this.value);
                    });
                }

                // Window Unload & Visibility Auto-Save Safety Net
                window.addEventListener('beforeunload', function() {
                    saveDraft(true, false);
                });

                window.addEventListener('visibilitychange', function() {
                    if (document.visibilityState === 'hidden') {
                        saveDraft(true, false);
                    }
                });

                if (btnAddScanRow) {
                    btnAddScanRow.addEventListener('click', function() {
                        addBlankRows(1);
                        focusCell(tableRows.length - 1, 'serial');
                    });
                }

                // Quick Bulk Applicators for Diagnostic & Parts
                const quickBulkDiagnosticSelect = document.getElementById('quickBulkDiagnosticSelect');
                const btnQuickApplyDiagnostic = document.getElementById('btnQuickApplyDiagnostic');
                const quickBulkPartsSelect = document.getElementById('quickBulkPartsSelect');
                const btnQuickApplyParts = document.getElementById('btnQuickApplyParts');

                if (btnQuickApplyDiagnostic && quickBulkDiagnosticSelect) {
                    btnQuickApplyDiagnostic.addEventListener('click', function() {
                        const val = quickBulkDiagnosticSelect.value;
                        if (!val) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Pumili muna ng Diagnostic',
                                text: 'Pakipili ang Technical Diagnostic na nais i-apply sa lahat ng rows.',
                                confirmButtonColor: '#2563EB'
                            });
                            return;
                        }
                        if (tableRows.length === 0) return;
                        tableRows.forEach(r => {
                            r.technical_diagnostic = val;
                        });
                        renderTable();
                        triggerAutoSave(true);
                        showStatus(`<i class="bi bi-check-circle-fill"></i> Na-apply ang diagnostic na "<strong>${escapeHtml(val)}</strong>" sa lahat ng ${tableRows.length} rows!`, 'success');
                    });
                }

                if (btnQuickApplyParts && quickBulkPartsSelect) {
                    btnQuickApplyParts.addEventListener('click', function() {
                        const val = quickBulkPartsSelect.value;
                        if (!val) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Pumili muna ng Replaced Part',
                                text: 'Pakipili ang Replaced Part na nais i-apply sa lahat ng rows.',
                                confirmButtonColor: '#2563EB'
                            });
                            return;
                        }
                        if (tableRows.length === 0) return;
                        tableRows.forEach(r => {
                            r.replace_parts = val;
                        });
                        renderTable();
                        triggerAutoSave(true);
                        showStatus(`<i class="bi bi-check-circle-fill"></i> Na-apply ang replaced part na "<strong>${escapeHtml(val)}</strong>" sa lahat ng ${tableRows.length} rows!`, 'success');
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

                                // Clean up active draft reference (draft will be deleted from DB upon successful batch store)
                                if (currentDraftId) {
                                    syncActiveDraftInput();
                                    const submittedDraftId = currentDraftId;
                                    currentDraftId = null;
                                    localStorage.removeItem(ACTIVE_DRAFT_KEY);
                                    cachedDraftsList = cachedDraftsList.filter(d => String(d.id) !==
                                        String(submittedDraftId));
                                    try {
                                        localStorage.setItem(DRAFTS_LIST_KEY, JSON.stringify(
                                            cachedDraftsList));
                                    } catch (e) {}
                                }

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

                // Page Initialization & Database Drafts Startup Check
                async function initPageAndDrafts() {
                    // 1. Check and migrate any legacy local-only drafts into the Database
                    try {
                        const oldSingleDraft = localStorage.getItem(OLD_SINGLE_DRAFT_KEY);
                        if (oldSingleDraft) {
                            const parsed = JSON.parse(oldSingleDraft);
                            if (parsed && (parsed.company_name || (Array.isArray(parsed.rows) && parsed.rows
                                    .length > 0))) {
                                parsed.title = (parsed.company_name ? parsed.company_name + ' - ' : '') + (parsed
                                    .batch_no || 'Migrated Local Draft');
                                parsed.as_new = true;
                                await fetch(DRAFTS_API_URL, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': CSRF_TOKEN,
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                    body: JSON.stringify(parsed)
                                });
                            }
                            localStorage.removeItem(OLD_SINGLE_DRAFT_KEY);
                        }
                    } catch (e) {
                        console.warn('Legacy single draft migration error:', e);
                    }

                    // 2. Fetch all drafts from DB
                    const startupDrafts = await fetchDraftsFromDB();
                    updateDraftBadges(startupDrafts.length);

                    let activeDraftSummary = null;
                    if (currentDraftId) {
                        activeDraftSummary = startupDrafts.find(d => String(d.id) === String(currentDraftId));
                    }

                    if (activeDraftSummary) {
                        const verifiedCount = activeDraftSummary.verified_count || 0;
                        const totalCount = activeDraftSummary.total_rows || 0;
                        const comp = activeDraftSummary.company_name || 'Hindi tinukoy';
                        const batchNo = activeDraftSummary.batch_no || 'N/A';
                        const timeStr = activeDraftSummary.updated_at_formatted || 'kamakailan';
                        const titleStr = activeDraftSummary.title || 'Aktibong Draft';
                        const creator = activeDraftSummary.created_by || 'Ikaw';

                        Swal.fire({
                            title: '<i class="bi bi-database-check text-primary"></i> May Aktibong Database Draft!',
                            html: `
                                <div style="text-align: left; font-size: 0.9rem; line-height: 1.6; background: #F8FAFC; padding: 14px 18px; border-radius: 8px; border: 1px solid #CBD5E1; margin: 12px 0;">
                                    <div style="margin-bottom: 6px;"><strong>Draft Title:</strong> <span style="color: #2563EB; font-weight: 800;">${escapeHtml(titleStr)}</span></div>
                                    <div style="margin-bottom: 6px;"><strong>Company:</strong> <strong>${escapeHtml(comp)}</strong></div>
                                    <div style="margin-bottom: 6px;"><strong>Batch #:</strong> <strong>${escapeHtml(batchNo)}</strong></div>
                                    <div style="margin-bottom: 6px;"><strong>Created By:</strong> <span>${escapeHtml(creator)}</span></div>
                                    <div style="margin-bottom: 6px;"><strong>Nai-scan na Units:</strong> <span style="color: #059669; font-weight: 800;">${verifiedCount} Verified</span> (${totalCount} kabuuang rows)</div>
                                    <div><strong>Huling Na-save sa DB:</strong> <span style="color: #64748B;">${escapeHtml(timeStr)}</span></div>
                                </div>
                                <p style="margin: 0; font-size: 0.92rem; color: #475569;">
                                    Nais mo bang <strong>ipagpatuloy</strong> ang draft na ito mula sa database, pumili mula sa mga naka-save na drafts (${startupDrafts.length} total), o magsimula ng bagong batch?
                                </p>
                            `,
                            icon: 'question',
                            showCancelButton: true,
                            showDenyButton: true,
                            confirmButtonColor: '#059669',
                            denyButtonColor: '#2563EB',
                            cancelButtonColor: '#64748B',
                            confirmButtonText: '<i class="bi bi-play-fill"></i> Ipagpatuloy ang Draft',
                            denyButtonText: `<i class="bi bi-folder2-open"></i> Pumili ng Ibang Draft (${startupDrafts.length})`,
                            cancelButtonText: '<i class="bi bi-plus-circle"></i> Bagong Batch',
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        }).then(async (result) => {
                            if (result.isConfirmed) {
                                await loadAndUseDraft(activeDraftSummary.id);
                            } else if (result.isDenied) {
                                addBlankRows(1);
                                if (headerCompanySelect && headerCompanySelect.value) {
                                    fetchCompanyUnits(headerCompanySelect.value);
                                }
                                openDraftsModal();
                            } else {
                                startNewBatch(false);
                            }
                        });
                    } else if (startupDrafts.length > 0) {
                        // Saved drafts exist in DB, start with clean row and inform user
                        addBlankRows(1);
                        if (headerCompanySelect && headerCompanySelect.value) {
                            fetchCompanyUnits(headerCompanySelect.value);
                        }
                        focusCell(0, 'serial');

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'info',
                            title: `May ${startupDrafts.length} naka-save na drafts sa database.`,
                            text: 'I-click ang "Saved Drafts" button sa toolbar upang tingnan at i-load ang mga ito kahit saang computer.',
                            showConfirmButton: false,
                            timer: 5000,
                            timerProgressBar: true
                        });
                    } else {
                        // Fresh initial load
                        addBlankRows(1);
                        if (headerCompanySelect && headerCompanySelect.value) {
                            fetchCompanyUnits(headerCompanySelect.value);
                        }
                        focusCell(0, 'serial');
                    }
                }

                initPageAndDrafts();
            });
        </script>
    @endpush
@endsection
