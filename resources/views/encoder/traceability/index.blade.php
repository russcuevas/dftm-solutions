@extends('layouts.app')

@section('title', 'Traceability Matrix & Batches')
@section('page_title', 'Repair Traceability Matrix')

@section('content')
    <style>
        .matrix-excel-table {
            width: 100%;
            border-collapse: collapse;
            background: #FFFFFF;
        }
        .matrix-excel-table th, .matrix-excel-table td {
            border: 1px solid #CBD5E1;
            padding: 6px 8px;
            font-size: 0.82rem;
            vertical-align: middle;
        }
        .matrix-excel-table th {
            background: #F8FAFC;
            color: #00205B;
            font-weight: 800;
            text-transform: uppercase;
        }
        .matrix-input {
            width: 100%;
            border: 1px solid transparent;
            background: transparent;
            padding: 4px 6px;
            font-size: 0.82rem;
            border-radius: 4px;
            transition: all 0.2s;
        }
        .matrix-input:hover {
            border-color: #CBD5E1;
            background: #FFFFFF;
        }
        .matrix-input:focus {
            border-color: var(--dftm-accent);
            background: #FFFFFF;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 32, 91, 0.1);
        }
        .bulk-bar {
            background: #F1F5F9;
            border: 1px solid var(--dftm-border);
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 16px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
    </style>

    <!-- Top Controls: Batch Selector, Create Batch, and Print -->
    <div class="card" style="margin-bottom: 16px;">
        <div class="card-body" style="padding: 16px 20px;">
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px;">
                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 16px; flex: 1; min-width: 320px;">
                    <!-- Company Filter -->
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label style="font-weight: 800; color: var(--dftm-navy); white-space: nowrap; font-size: 0.88rem;">
                            <i class="bi bi-building" style="color: var(--dftm-accent);"></i> Company:
                        </label>
                        <select id="companyFilterSelector" class="form-select" style="min-width: 180px; max-width: 250px; font-weight: 700; border-color: var(--dftm-border);" onchange="location.href='{{ route('encoder.traceability.index') }}?company=' + encodeURIComponent(this.value);">
                            <option value="all" {{ empty($companyFilter) || $companyFilter === 'all' ? 'selected' : '' }}>-- All Companies ({{ isset($companies) ? $companies->count() : '' }}) --</option>
                            @if(isset($companies))
                                @foreach($companies as $comp)
                                    <option value="{{ $comp }}" {{ ($companyFilter ?? '') === $comp ? 'selected' : '' }}>
                                        {{ $comp }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Batch Selector -->
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label style="font-weight: 800; color: var(--dftm-navy); white-space: nowrap; font-size: 0.88rem;">
                            <i class="bi bi-filter-circle-fill" style="color: var(--dftm-accent);"></i> Select Batch:
                        </label>
                        <select id="activeBatchSelector" class="form-select" style="min-width: 280px; max-width: 420px; font-weight: 700; border-color: var(--dftm-accent);" onchange="const c = document.getElementById('companyFilterSelector').value; location.href='{{ route('encoder.traceability.index') }}?' + (c && c !== 'all' ? 'company=' + encodeURIComponent(c) + '&' : '') + 'batch_id=' + this.value;">
                            @php
                                $batchesGrouped = $batches->groupBy(function($b) {
                                    return $b->company_name ?: ($b->items->first()?->company_name ?: ($b->items->first()?->transmittal?->company_name ?: 'DFTM DIGITAL SOLUTIONS'));
                                });
                            @endphp
                            @forelse($batchesGrouped as $cName => $cBatches)
                                <optgroup label="🏢 {{ $cName }}">
                                    @foreach($cBatches as $b)
                                        @php
                                            $cDisplay = $b->company_name ?: ($b->items->first()?->company_name ?: ($b->items->first()?->transmittal?->company_name ?: 'DFTM'));
                                        @endphp
                                        <option value="{{ $b->id }}" {{ $selectedBatch && $selectedBatch->id == $b->id ? 'selected' : '' }}>
                                            {{ $b->batch_no }} &bull; {{ $cDisplay }} ({{ $b->items->count() }} Units)
                                        </option>
                                    @endforeach
                                </optgroup>
                            @empty
                                <option value="">-- No Batches Found --</option>
                            @endforelse
                        </select>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <a href="{{ route('encoder.traceability.createBatch') }}" class="btn btn-accent btn-sm" style="font-weight: 700;">
                        <i class="bi bi-plus-lg"></i> + Create New Batch
                    </a>
                    @if($selectedBatch)
                        <a href="{{ route('encoder.traceability.print', ['batch_id' => $selectedBatch->id]) }}" target="_blank" class="btn btn-outline btn-sm">
                            <i class="bi bi-printer"></i> Print Matrix Report
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($selectedBatch)
        <!-- Material Description Header Block (Exact Reference Specification) -->
        <div class="card" style="margin-bottom: 16px;">
            <div class="card-header" style="background: #00205B; color: #FFFFFF; padding: 12px 20px;">
                <div class="card-title" style="color: #FFFFFF; font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-tag-fill" style="color: #38BDF8;"></i> MATERIAL DESCRIPTION HEADER
                </div>
                <div style="font-weight: 700; font-size: 0.9rem; color: #E2E8F0;">
                    {{ $selectedBatch->batch_no }} &bull; {{ $selectedBatch->total_quantity }} Units Total
                </div>
            </div>
            <div class="card-body" style="padding: 16px 20px; background: #F8FAFC;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; font-size: 0.85rem;">
                    <div>
                        <span style="font-weight: 800; color: var(--dftm-slate); font-size: 0.72rem; text-transform: uppercase;">COMPANY NAME:</span>
                        <div style="font-weight: 800; color: var(--dftm-navy); font-size: 1rem;">{{ $selectedBatch->company_name ?: ($selectedBatch->items->first()?->company_name ?: ($selectedBatch->items->first()?->transmittal?->company_name ?? 'DFTM DIGITAL SOLUTIONS')) }}</div>
                    </div>
                    <div>
                        <span style="font-weight: 800; color: var(--dftm-slate); font-size: 0.72rem; text-transform: uppercase;">BATCH NUMBER:</span>
                        <div style="font-weight: 800; color: var(--dftm-navy); font-size: 1rem;">{{ $selectedBatch->batch_no }}</div>
                    </div>
                    <div>
                        <span style="font-weight: 800; color: var(--dftm-slate); font-size: 0.72rem; text-transform: uppercase;">BRAND / MODEL:</span>
                        <div style="font-weight: 700;">{{ $selectedBatch->brand ?? '-' }} {{ $selectedBatch->model ? ' / ' . $selectedBatch->model : '' }}</div>
                    </div>
                    <div>
                        <span style="font-weight: 800; color: var(--dftm-slate); font-size: 0.72rem; text-transform: uppercase;">DATE DELIVERED:</span>
                        <div style="font-weight: 700;">{{ $selectedBatch->date_delivered ? $selectedBatch->date_delivered->format('M d, Y') : '-' }}</div>
                    </div>
                    <div>
                        <span style="font-weight: 800; color: var(--dftm-slate); font-size: 0.72rem; text-transform: uppercase;">REPAIR SUMMARY:</span>
                        <div style="display: flex; gap: 6px; margin-top: 4px;">
                            <span class="badge badge-stock" title="In Process">{{ $inProcessCount }} In Process</span>
                            <span class="badge badge-repaired" title="Repaired">{{ $repairedCount }} Repaired</span>
                            <span class="badge badge-ber" title="BER">{{ $berCount }} BER</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Applicator Bar (Diagnostic, Replace Parts, Status) -->
        <div class="bulk-bar">
            <div style="display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" id="selectAllRowsCheckbox" style="accent-color: var(--dftm-navy); transform: scale(1.2); cursor: pointer;" title="Select / Deselect all rows">
                <label for="selectAllRowsCheckbox" style="font-weight: 800; font-size: 0.85rem; color: var(--dftm-navy); cursor: pointer;">
                    Select All (<span id="selectedCountText">0</span> selected)
                </label>
            </div>

            <!-- Bulk Status Apply -->
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 0.78rem; font-weight: 700; color: var(--dftm-slate);">STATUS:</span>
                <select id="bulkStatusSelect" class="form-select form-select-sm" style="width: 130px; font-weight: 600;">
                    <option value="In process">In process</option>
                    <option value="Repaired">Repaired</option>
                    <option value="BER">BER</option>
                </select>
                <button type="button" class="btn btn-primary btn-sm" id="btnApplyBulkStatus">Apply</button>
            </div>

            <!-- Bulk Diagnostic Apply -->
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 0.78rem; font-weight: 700; color: var(--dftm-slate);">DIAGNOSTIC:</span>
                <input type="text" id="bulkDiagnosticInput" list="diagnosticSuggestions" class="form-control form-control-sm" placeholder="e.g. NO POWER / OK" style="width: 160px;">
                <datalist id="diagnosticSuggestions">
                    <option value="NO POWER">
                    <option value="CORRODED BOARD">
                    <option value="FIRMWARE CORRUPTED">
                    <option value="TEST OK / GOOD">
                    <option value="DEFECTIVE LAN PORT">
                    <option value="LOS / NO FIBER SIGNAL">
                </datalist>
                <button type="button" class="btn btn-primary btn-sm" id="btnApplyBulkDiagnostic">Apply</button>
            </div>

            <!-- Bulk Replace Parts Apply -->
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 0.78rem; font-weight: 700; color: var(--dftm-slate);">PARTS:</span>
                <input type="text" id="bulkPartsInput" list="partsSuggestions" class="form-control form-control-sm" placeholder="e.g. SMD CAPACITOR / N/A" style="width: 150px;">
                <datalist id="partsSuggestions">
                    <option value="SMD CAPACITOR">
                    <option value="POWER ADAPTER">
                    <option value="POWER IC">
                    <option value="OPTICAL TRANSCEIVER">
                    <option value="N/A">
                </datalist>
                <button type="button" class="btn btn-primary btn-sm" id="btnApplyBulkParts">Apply</button>
            </div>
        </div>

        <!-- Excel-Style Traceability Table -->
        <div class="card">
            <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                <table class="matrix-excel-table" id="traceabilityTable">
                    <thead style="position: sticky; top: 0; z-index: 10;">
                        <tr>
                            <th style="width: 36px; text-align: center;"></th>
                            <th style="width: 45px; text-align: center;">NO.</th>
                            <th style="min-width: 170px;">MATERIAL DIAGNOSTIC</th>
                            <th style="min-width: 150px;">REPLACE PARTS</th>
                            <th style="min-width: 120px;">STATUS</th>
                            <th style="min-width: 100px;">BRAND</th>
                            <th style="min-width: 110px;">MODEL</th>
                            <th style="min-width: 160px;">SERIAL NUMBER</th>
                            <th style="min-width: 150px;">MAC ADDRESS</th>
                            <th style="width: 85px;">BOX #</th>
                            <th style="width: 50px; text-align: center;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr class="trace-row" data-item-id="{{ $item->id }}">
                            <td style="text-align: center;">
                                <input type="checkbox" class="trace-row-cb" value="{{ $item->id }}" style="accent-color: var(--dftm-navy); transform: scale(1.15); cursor: pointer;">
                            </td>
                            <td style="text-align: center; font-weight: 800; color: var(--dftm-navy);">
                                {{ $items->firstItem() ? ($items->firstItem() + $loop->index) : $loop->iteration }}
                            </td>
                            <td>
                                <input type="text" class="matrix-input row-diagnostic" value="{{ $item->technical_diagnostic }}" placeholder="Diagnosis..." list="diagnosticSuggestions">
                            </td>
                            <td>
                                <input type="text" class="matrix-input row-parts" value="{{ $item->replace_parts }}" placeholder="Parts..." list="partsSuggestions">
                            </td>
                            <td>
                                <select class="matrix-input row-status" style="font-weight: 700; cursor: pointer;">
                                    <option value="In process" {{ in_array($item->repair_status, ['In process', 'IN_PROCESS', 'PENDING']) ? 'selected' : '' }}>In process</option>
                                    <option value="Repaired" {{ $item->repair_status === 'Repaired' ? 'selected' : '' }}>Repaired</option>
                                    <option value="BER" {{ $item->repair_status === 'BER' ? 'selected' : '' }}>BER</option>
                                </select>
                            </td>
                            <td>
                                <span style="font-weight: 700;">{{ $item->brand ?? '-' }}</span>
                            </td>
                            <td>
                                <span>{{ $item->model ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="mono" style="font-weight: 800; color: var(--dftm-navy);">{{ $item->serial_number ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="mono">{{ $item->mac_address ?? '-' }}</span>
                            </td>
                            <td>
                                <input type="text" class="matrix-input row-box" value="{{ $item->box_no }}" placeholder="Box #" style="text-align: center;">
                            </td>
                            <td style="text-align: center;">
                                <button type="button" class="btn btn-outline btn-icon btn-sm btn-save-row" title="Save Row" style="color: #059669; border-color: #A7F3D0;">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" style="text-align: center; padding: 36px; color: var(--dftm-slate);">
                                No units in this Batch.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($items->hasPages())
                <div class="card-footer">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="card" style="text-align: center; padding: 60px 20px;">
            <i class="bi bi-inboxes-fill" style="font-size: 3rem; color: #CBD5E1;"></i>
            <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--dftm-navy); margin-top: 12px;">
                No Traceability Batches Found
            </h3>
            <p style="color: var(--dftm-slate); max-width: 480px; margin: 8px auto 20px auto;">
                Create a Traceability Batch by selecting incoming scanned units to assign a Batch Number, diagnoses, and repair statuses.
            </p>
            <div>
                <a href="{{ route('encoder.traceability.createBatch') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle-fill"></i> Create First Batch
                </a>
            </div>
        </div>
    @endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const saveItemUrl = '{{ route('encoder.traceability.saveItem') }}';
    const bulkUpdateUrl = '{{ route('encoder.traceability.bulkUpdate') }}';
    const csrfToken = '{{ csrf_token() }}';

    const masterCb = document.getElementById('selectAllRowsCheckbox');
    const selectedCountText = document.getElementById('selectedCountText');
    const table = document.getElementById('traceabilityTable');

    function updateSelectedCount() {
        if (!table) return;
        const checked = table.querySelectorAll('.trace-row-cb:checked').length;
        if (selectedCountText) selectedCountText.textContent = checked;
    }

    if (masterCb && table) {
        masterCb.addEventListener('change', function() {
            const isChecked = this.checked;
            table.querySelectorAll('.trace-row-cb').forEach(cb => cb.checked = isChecked);
            updateSelectedCount();
        });

        table.addEventListener('change', function(e) {
            if (e.target.classList.contains('trace-row-cb')) {
                updateSelectedCount();
            }
        });
    }

    function getSelectedIds() {
        if (!table) return [];
        return Array.from(table.querySelectorAll('.trace-row-cb:checked')).map(cb => cb.value);
    }

    function saveRow(row) {
        const itemId = row.getAttribute('data-item-id');
        const diagnostic = row.querySelector('.row-diagnostic')?.value || '';
        const parts = row.querySelector('.row-parts')?.value || '';
        const status = row.querySelector('.row-status')?.value || 'In process';
        const box = row.querySelector('.row-box')?.value || '';
        const saveBtn = row.querySelector('.btn-save-row');

        if (saveBtn) saveBtn.innerHTML = '<i class="bi bi-arrow-repeat spin-icon" style="color: var(--dftm-accent);"></i>';

        fetch(saveItemUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                _token: csrfToken,
                item_id: itemId,
                technical_diagnostic: diagnostic,
                replace_parts: parts,
                repair_status: status,
                box_no: box
            })
        })
        .then(r => r.json())
        .then(data => {
            if (saveBtn) {
                saveBtn.innerHTML = '<i class="bi bi-check-lg"></i>';
                saveBtn.style.color = '#059669';
            }
        })
        .catch(err => {
            console.error('Save error:', err);
            if (saveBtn) saveBtn.innerHTML = '<i class="bi bi-x" style="color: #DC2626;"></i>';
        });
    }

    if (table) {
        table.querySelectorAll('.trace-row').forEach(row => {
            const inputs = row.querySelectorAll('.matrix-input');
            inputs.forEach(input => {
                input.addEventListener('change', function() {
                    saveRow(row);
                });
            });

            const btn = row.querySelector('.btn-save-row');
            if (btn) {
                btn.addEventListener('click', function() {
                    saveRow(row);
                });
            }
        });
    }

    const btnApplyBulkStatus = document.getElementById('btnApplyBulkStatus');
    if (btnApplyBulkStatus) {
        btnApplyBulkStatus.addEventListener('click', function() {
            const ids = getSelectedIds();
            if (ids.length === 0) return alert('Please check at least one row to apply bulk status.');

            const val = document.getElementById('bulkStatusSelect').value;
            btnApplyBulkStatus.disabled = true;
            btnApplyBulkStatus.textContent = 'Applying...';

            fetch(bulkUpdateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    _token: csrfToken,
                    item_ids: ids,
                    status: val
                })
            })
            .then(r => r.json())
            .then(data => {
                btnApplyBulkStatus.disabled = false;
                btnApplyBulkStatus.textContent = 'Apply';
                if (data.success) {
                    ids.forEach(id => {
                        const row = table.querySelector(`.trace-row[data-item-id="${id}"]`);
                        if (row) {
                            const sel = row.querySelector('.row-status');
                            if (sel) sel.value = val;
                        }
                    });
                }
            });
        });
    }

    const btnApplyBulkDiagnostic = document.getElementById('btnApplyBulkDiagnostic');
    if (btnApplyBulkDiagnostic) {
        btnApplyBulkDiagnostic.addEventListener('click', function() {
            const ids = getSelectedIds();
            if (ids.length === 0) return alert('Please check at least one row to apply bulk diagnostic.');

            const val = document.getElementById('bulkDiagnosticInput').value.trim();
            btnApplyBulkDiagnostic.disabled = true;
            btnApplyBulkDiagnostic.textContent = 'Applying...';

            fetch(bulkUpdateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    _token: csrfToken,
                    item_ids: ids,
                    technical_diagnostic: val
                })
            })
            .then(r => r.json())
            .then(data => {
                btnApplyBulkDiagnostic.disabled = false;
                btnApplyBulkDiagnostic.textContent = 'Apply';
                if (data.success) {
                    ids.forEach(id => {
                        const row = table.querySelector(`.trace-row[data-item-id="${id}"]`);
                        if (row) {
                            const inp = row.querySelector('.row-diagnostic');
                            if (inp) inp.value = val;
                        }
                    });
                }
            });
        });
    }

    const btnApplyBulkParts = document.getElementById('btnApplyBulkParts');
    if (btnApplyBulkParts) {
        btnApplyBulkParts.addEventListener('click', function() {
            const ids = getSelectedIds();
            if (ids.length === 0) return alert('Please check at least one row to apply bulk replace parts.');

            const val = document.getElementById('bulkPartsInput').value.trim();
            btnApplyBulkParts.disabled = true;
            btnApplyBulkParts.textContent = 'Applying...';

            fetch(bulkUpdateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    _token: csrfToken,
                    item_ids: ids,
                    replace_parts: val
                })
            })
            .then(r => r.json())
            .then(data => {
                btnApplyBulkParts.disabled = false;
                btnApplyBulkParts.textContent = 'Apply';
                if (data.success) {
                    ids.forEach(id => {
                        const row = table.querySelector(`.trace-row[data-item-id="${id}"]`);
                        if (row) {
                            const inp = row.querySelector('.row-parts');
                            if (inp) inp.value = val;
                        }
                    });
                }
            });
        });
    }
});
</script>
@endsection
