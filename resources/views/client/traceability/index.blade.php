@extends('layouts.app')

@section('title', 'Repair Traceability Matrix')
@section('page_title', 'Repair Traceability Matrix')

@section('content')
    <style>
        .client-matrix-table {
            width: 100%;
            border-collapse: collapse;
            background: #FFFFFF;
        }

        .client-matrix-table th,
        .client-matrix-table td {
            border: 1px solid #E2E8F0;
            padding: 10px 12px;
            font-size: 0.84rem;
            vertical-align: middle;
        }

        .client-matrix-table th {
            background: #F8FAFC;
            color: #00205B;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .client-matrix-table tbody tr:hover {
            background-color: #F8FAFC;
        }
    </style>

    <!-- Top Batch Selection & Actions -->
    <div class="card" style="margin-bottom: 16px;">
        <div class="card-body" style="padding: 16px 20px;">
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px;">
                <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 320px;">
                    <label style="font-weight: 800; color: var(--dftm-navy); white-space: nowrap; font-size: 0.95rem;">
                        <i class="bi bi-diagram-3-fill" style="color: var(--dftm-accent);"></i> Select Batch:
                    </label>
                    <select id="activeBatchSelector" class="form-select"
                        style="max-width: 420px; font-weight: 700; border-color: var(--dftm-accent);"
                        onchange="location.href='{{ route('client.traceability.index') }}?batch_id=' + this.value;">
                        @forelse($batches as $b)
                            <option value="{{ $b->id }}"
                                {{ $selectedBatch && $selectedBatch->id == $b->id ? 'selected' : '' }}>
                                {{ $b->batch_no }} &bull; {{ $b->brand }} {{ $b->model ? '(' . $b->model . ')' : '' }}
                                ({{ $b->items->count() }} Units)
                            </option>
                        @empty
                            <option value="">-- No Batches Available For Your Company --</option>
                        @endforelse
                    </select>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    @if ($selectedBatch)
                        <button type="button" class="btn btn-outline btn-sm" onclick="openTraceabilityPrintModal('print')"
                            style="font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="bi bi-printer-fill" style="color: var(--dftm-accent);"></i> Print Matrix
                        </button>
                        <button type="button" class="btn btn-outline btn-sm" onclick="openTraceabilityPrintModal('excel')"
                            style="font-weight: 700; display: inline-flex; align-items: center; gap: 6px; color: #107C41; border-color: #107C41;">
                            <i class="bi bi-file-earmark-excel"></i> Export Excel
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($selectedBatch)
        <!-- Material Description Header Block -->
        <div class="card" style="margin-bottom: 16px; border: 1px solid #CBD5E1;">
            <div class="card-header" style="background: #00205B; color: #FFFFFF; padding: 12px 20px;">
                <div class="card-title"
                    style="color: #FFFFFF; font-size: 0.98rem; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-info-circle-fill" style="color: #38BDF8;"></i> MATERIAL DESCRIPTION HEADER
                </div>
                <div style="font-weight: 700; font-size: 0.88rem; color: #E2E8F0;">
                    Batch: {{ $selectedBatch->batch_no }} &bull; Total:
                    {{ $selectedBatch->total_quantity ?? $selectedBatch->items->count() }} Units
                </div>
            </div>
            <div class="card-body" style="padding: 16px 20px; background: #F8FAFC;">
                <div
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; font-size: 0.85rem;">
                    <div>
                        <span
                            style="font-weight: 800; color: var(--dftm-slate); font-size: 0.72rem; text-transform: uppercase;">COMPANY
                            NAME:</span>
                        <div style="font-weight: 800; color: var(--dftm-navy); font-size: 1.05rem;">
                            {{ $selectedBatch->company_name ?? $companyName }}</div>
                    </div>
                    <div>
                        <span
                            style="font-weight: 800; color: var(--dftm-slate); font-size: 0.72rem; text-transform: uppercase;">BATCH
                            NUMBER:</span>
                        <div style="font-weight: 800; color: var(--dftm-navy); font-size: 1.05rem;">
                            {{ $selectedBatch->batch_no }}</div>
                    </div>
                    <div>
                        <span
                            style="font-weight: 800; color: var(--dftm-slate); font-size: 0.72rem; text-transform: uppercase;">BRAND
                            / MODEL:</span>
                        <div style="font-weight: 700; font-size: 0.95rem;">{{ $selectedBatch->brand ?? '-' }}
                            {{ $selectedBatch->model ? ' / ' . $selectedBatch->model : '' }}</div>
                    </div>
                    <div>
                        <span
                            style="font-weight: 800; color: var(--dftm-slate); font-size: 0.72rem; text-transform: uppercase;">DATE
                            DELIVERED:</span>
                        <div style="font-weight: 700; font-size: 0.95rem;">
                            {{ $selectedBatch->date_delivered ? \Carbon\Carbon::parse($selectedBatch->date_delivered)->format('M d, Y') : '-' }}
                        </div>
                    </div>
                    <div>
                        <span
                            style="font-weight: 800; color: var(--dftm-slate); font-size: 0.72rem; text-transform: uppercase;">REPAIR
                            PROGRESS:</span>
                        <div style="display: flex; gap: 6px; margin-top: 5px; flex-wrap: wrap;">
                            <span class="badge badge-repaired" style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0;" title="GOOD">{{ $goodCount ?? 0 }} GOOD</span>
                            <span class="badge badge-stock" title="In Process">{{ $inProcessCount }} In Process</span>
                            <span class="badge badge-repaired" title="Repaired">{{ $repairedCount }} Repaired</span>
                            <span class="badge badge-ber" title="BER">{{ $berCount }} BER</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="card" style="margin-bottom: 16px;">
            <div class="card-body" style="padding: 12px 20px;">
                <form method="GET" action="{{ route('client.traceability.index') }}"
                    style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;">
                    <input type="hidden" name="batch_id" value="{{ $selectedBatch->id }}">

                    <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 260px;">
                        <div class="search-bar" style="width: 100%; max-width: 380px;">
                            <i class="bi bi-search"></i>
                            <input type="text" name="search" class="form-control form-control-sm"
                                placeholder="Search Serial, MAC, Diagnostic, Parts..." value="{{ request('search') }}">
                        </div>
                        @if (request('search') || request('status'))
                            <a href="{{ route('client.traceability.index', ['batch_id' => $selectedBatch->id]) }}"
                                class="btn btn-outline btn-sm" title="Clear Filters">
                                Clear
                            </a>
                        @endif
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 0.8rem; font-weight: 700; color: var(--dftm-slate);">Status:</span>
                        <select name="status" class="form-select form-select-sm" style="width: 140px; font-weight: 600;"
                            onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="GOOD" {{ request('status') === 'GOOD' ? 'selected' : '' }}>GOOD</option>
                            <option value="In process" {{ request('status') === 'In process' ? 'selected' : '' }}>In
                                process</option>
                            <option value="Repaired" {{ request('status') === 'Repaired' ? 'selected' : '' }}>Repaired
                            </option>
                            <option value="BER" {{ request('status') === 'BER' ? 'selected' : '' }}>BER</option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Read-Only Traceability Matrix Table -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="bi bi-table"></i> Units in Batch {{ $selectedBatch->batch_no }}</div>
                    <div class="card-subtitle">Showing {{ $items->total() }} recorded units (Read-Only Customer View)</div>
                </div>
            </div>
            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                <table class="client-matrix-table">
                    <thead style="position: sticky; top: 0; z-index: 10;">
                        <tr>
                            <th style="width: 50px; text-align: center;">NO.</th>
                            <th style="min-width: 180px;">MATERIAL DIAGNOSTIC</th>
                            <th style="min-width: 150px;">REPLACE PARTS</th>
                            <th style="min-width: 120px; text-align: center;">STATUS</th>
                            <th style="min-width: 100px;">BRAND</th>
                            <th style="min-width: 110px;">MODEL</th>
                            <th style="min-width: 160px;">SERIAL NUMBER</th>
                            <th style="min-width: 150px;">MAC ADDRESS</th>
                            <th style="width: 90px; text-align: center;">BOX #</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td style="text-align: center; font-weight: 800; color: var(--dftm-navy);">
                                    {{ $items->firstItem() ? $items->firstItem() + $loop->index : $loop->iteration }}
                                </td>
                                <td>
                                    @if ($item->technical_diagnostic)
                                        <span
                                            style="font-weight: 600; color: #1E293B;">{{ $item->technical_diagnostic }}</span>
                                    @else
                                        <span style="color: #94A3B8; font-style: italic;">Pending diagnostic</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($item->replace_parts)
                                        <span style="font-weight: 600; color: #1E293B;">{{ $item->replace_parts }}</span>
                                    @else
                                        <span style="color: #94A3B8; font-style: italic;">None / Pending</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @if ($item->repair_status === 'GOOD')
                                        <span class="badge badge-repaired"><i class="bi bi-check-circle-fill"></i> GOOD</span>
                                    @elseif ($item->repair_status === 'Repaired')
                                        <span class="badge badge-repaired"><i class="bi bi-check-circle-fill"></i> Repaired</span>
                                    @elseif($item->repair_status === 'BER')
                                        <span class="badge badge-ber"><i class="bi bi-x-circle-fill"></i> BER</span>
                                    @else
                                        <span class="badge badge-stock"><i class="bi bi-hourglass-split"></i> In process</span>
                                    @endif
                                </td>
                                <td style="font-weight: 600;">{{ $item->brand ?? '-' }}</td>
                                <td style="font-weight: 600;">{{ $item->model ?? '-' }}</td>
                                <td>
                                    <code
                                        style="font-weight: 700; color: var(--dftm-navy); font-size: 0.86rem; background: #EEF2F6; padding: 2px 6px; border-radius: 4px;">{{ $item->serial_number }}</code>
                                </td>
                                <td>
                                    <code
                                        style="color: var(--dftm-slate); font-size: 0.84rem;">{{ $item->mac_address ?? '-' }}</code>
                                </td>
                                <td style="text-align: center;">
                                    @if ($item->box_no)
                                        <span class="badge"
                                            style="background: #E2E8F0; color: #334155; font-weight: 700;">{{ $item->box_no }}</span>
                                    @else
                                        <span style="color: #94A3B8;">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9"
                                    style="text-align: center; padding: 36px 16px; color: var(--dftm-slate);">
                                    <i class="bi bi-inbox"
                                        style="font-size: 2rem; display: block; margin-bottom: 8px; opacity: 0.5;"></i>
                                    No units matched the criteria in this batch.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($items->hasPages())
                <div
                    style="padding: 16px 20px; border-top: 1px solid var(--dftm-border); display: flex; justify-content: flex-end;">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="card">
            <div class="card-body" style="padding: 48px 24px; text-align: center; color: var(--dftm-slate);">
                <i class="bi bi-diagram-3"
                    style="font-size: 3rem; color: #CBD5E1; display: block; margin-bottom: 12px;"></i>
                <h5 style="font-weight: 800; color: var(--dftm-navy); margin-bottom: 6px;">No Batches Assigned Yet</h5>
                <p style="max-width: 480px; margin: 0 auto; font-size: 0.9rem;">
                    When DFTM Digital Solutions groups your delivered units into batches for diagnostics and repair, they
                    will appear here in real-time.
                </p>
            </div>
        </div>
    @endif

    @if ($selectedBatch)
        @php
            $batchModelGroups = $selectedBatch->items->groupBy(fn($i) => trim($i->model ?: 'Unassigned Model'));
        @endphp
        <!-- Model Selection Modal for Print / Export -->
        <div class="modal-backdrop" id="traceabilityModelModal">
            <div class="modal-card" style="max-width: 560px;">
                <div class="modal-header" style="background: #00205B; color: #FFFFFF; display: flex; justify-content: space-between; align-items: center; padding: 16px 20px;">
                    <div class="modal-title" style="color: #FFFFFF; font-size: 1.05rem; display: flex; align-items: center; gap: 8px; font-weight: 800;">
                        <i class="bi bi-printer-fill" style="color: #38BDF8;"></i> Print / Export Traceability Matrix
                    </div>
                    <button type="button" class="modal-close" data-modal-close style="color: #FFFFFF; background: none; border: none; font-size: 1.25rem; cursor: pointer; line-height: 1;">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="modal-body" style="padding: 22px;">
                    <!-- Active Batch Info Card -->
                    <div style="background: #F8FAFC; border: 1px solid var(--dftm-border); border-radius: 8px; padding: 14px 18px; margin-bottom: 18px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <span style="font-size: 0.72rem; font-weight: 800; color: var(--dftm-slate); text-transform: uppercase;">ACTIVE BATCH</span>
                            <div style="font-weight: 800; color: var(--dftm-navy); font-size: 1.1rem;">
                                {{ $selectedBatch->batch_no }}
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 0.72rem; font-weight: 800; color: var(--dftm-slate); text-transform: uppercase;">TOTAL UNITS</span>
                            <div style="font-weight: 900; color: var(--dftm-navy); font-size: 1.1rem;">
                                {{ $selectedBatch->items->count() }} <span style="font-size: 0.8rem; font-weight: 600;">PCS</span>
                            </div>
                        </div>
                    </div>

                    <!-- Instruction -->
                    <label style="font-weight: 800; color: var(--dftm-navy); font-size: 0.92rem; margin-bottom: 6px; display: block;">
                        <i class="bi bi-check2-circle" style="color: var(--dftm-accent);"></i> Piliin ang Model na I-piprint / I-eexport:
                    </label>
                    <div style="font-size: 0.8rem; color: var(--dftm-slate); margin-bottom: 14px; line-height: 1.4;">
                        Pumili ng specific model para <strong>pare-parehas</strong> ang units sa matrix sheet, o piliin ang <strong>All Models</strong> para i-hiwalay per model (same sa incoming).
                    </div>

                    <!-- Model Options Cards -->
                    <div style="display: flex; flex-direction: column; gap: 10px; max-height: 280px; overflow-y: auto; padding-right: 4px;">
                        <!-- Option: All Models (Separated Per Model) -->
                        <label style="border: 2px solid #00205B; background: #F0F4FF; border-radius: 8px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.2s;" class="matrix-model-option-card" onclick="selectMatrixModelCard(this)">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <input type="radio" name="matrix_print_model" value="all" checked style="accent-color: #00205B; width: 18px; height: 18px;">
                                <div>
                                    <div style="font-weight: 800; color: var(--dftm-navy); font-size: 0.92rem;">
                                        All Models (Separated Per Model)
                                    </div>
                                    <div style="font-size: 0.76rem; color: var(--dftm-slate); margin-top: 2px;">
                                        Hiwalay ang bawat model sa sariling table at subtotal (same sa incoming)
                                    </div>
                                </div>
                            </div>
                            <span class="badge" style="background: #EEF2FF; color: #4338CA; font-weight: 800; font-size: 0.82rem; padding: 4px 10px;">
                                {{ $selectedBatch->items->count() }} PCS Total
                            </span>
                        </label>

                        <!-- Specific Models -->
                        @foreach($batchModelGroups as $mName => $mItems)
                        <label style="border: 2px solid #CBD5E1; background: #FFFFFF; border-radius: 8px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.2s;" class="matrix-model-option-card" onclick="selectMatrixModelCard(this)">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <input type="radio" name="matrix_print_model" value="{{ $mName }}" style="accent-color: #00205B; width: 18px; height: 18px;">
                                <div>
                                    <div style="font-weight: 800; color: var(--dftm-navy); font-size: 0.92rem;">
                                        MODEL: {{ $mName }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: #16A34A; margin-top: 2px; font-weight: 600;">
                                        <i class="bi bi-shield-check"></i> 100% Pare-parehas (Model: {{ $mName }})
                                    </div>
                                </div>
                            </div>
                            <span class="badge" style="background: #F1F5F9; color: var(--dftm-navy); font-weight: 800; font-size: 0.82rem; padding: 4px 10px; border: 1px solid #CBD5E1;">
                                {{ $mItems->count() }} PCS
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="modal-footer" style="padding: 16px 22px; background: #F8FAFC; border-top: 1px solid var(--dftm-border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <button type="button" class="btn btn-outline btn-sm" data-modal-close>
                        Cancel
                    </button>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="btn btn-primary btn-sm" onclick="executeMatrixPrintAction(false)">
                            <i class="bi bi-printer"></i> Print Matrix Report
                        </button>
                        <button type="button" class="btn btn-sm" style="background: #107C41; color: #FFFFFF; font-weight: 700; border: none; box-shadow: 0 2px 6px rgba(16,124,65,0.25);" onclick="executeMatrixPrintAction(true)">
                            <i class="bi bi-file-earmark-excel"></i> Export Excel (.xlsx)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function selectMatrixModelCard(card) {
                document.querySelectorAll('.matrix-model-option-card').forEach(c => {
                    c.style.borderColor = '#CBD5E1';
                    c.style.backgroundColor = '#FFFFFF';
                });
                const radio = card.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    card.style.borderColor = '#00205B';
                    card.style.backgroundColor = '#F0F4FF';
                }
            }

            function openTraceabilityPrintModal(action = 'print') {
                const modal = document.getElementById('traceabilityModelModal');
                if (modal) {
                    modal.classList.add('active');
                    const checkedRadio = modal.querySelector('input[name="matrix_print_model"]:checked');
                    if (checkedRadio) {
                        const card = checkedRadio.closest('.matrix-model-option-card');
                        if (card) selectMatrixModelCard(card);
                    }
                }
            }

            function executeMatrixPrintAction(isExcel = false) {
                const modal = document.getElementById('traceabilityModelModal');
                const checkedRadio = modal ? modal.querySelector('input[name="matrix_print_model"]:checked') : null;
                const selectedModel = checkedRadio ? checkedRadio.value : 'all';

                let printUrl = "{{ route('client.traceability.print', ['batch_id' => $selectedBatch->id]) }}";
                const separator = printUrl.includes('?') ? '&' : '?';
                printUrl += separator + 'model=' + encodeURIComponent(selectedModel);

                if (isExcel) {
                    printUrl += '&export=excel';
                }

                window.open(printUrl, '_blank');
                if (modal) modal.classList.remove('active');
            }
        </script>
    @endif
@endsection
