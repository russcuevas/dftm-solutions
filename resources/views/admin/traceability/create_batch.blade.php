@extends('layouts.app')

@section('title', 'Create Traceability Batch')
@section('page_title', 'Create Traceability Batch')

@section('content')
<form action="{{ route('admin.traceability.storeBatch') }}" method="POST" id="batchForm">
    @csrf

    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title"><i class="bi bi-cpu-fill"></i> Create Traceability Batch</div>
                <div class="card-subtitle">Group incoming scanned units into a Batch for diagnosis, parts replacement, and repair tracking</div>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <span class="badge badge-stock" id="selectedUnitsCount" style="font-size: 0.9rem; padding: 6px 14px;">0 Selected</span>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle-fill"></i> Create Batch & Open Matrix
                </button>
            </div>
        </div>

        <div class="card-body">
            <!-- Header Row: Batch No, Company Name, Date Received/Batched -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 700; color: var(--dftm-navy);">Batch Number</label>
                    <input type="text" name="batch_no" class="form-control" value="{{ old('batch_no', $nextBatchNo) }}" placeholder="e.g. BATCH 1" required>
                    <small style="color: var(--dftm-slate); font-size: 0.75rem;">Assigned batch number in Traceability</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" list="companySuggestions" class="form-control" placeholder="Enter Company Name" value="{{ old('company_name') }}">
                    <datalist id="companySuggestions">
                        @if(isset($registeredClients))
                            @foreach($registeredClients as $rc)
                                <option value="{{ $rc }}">
                            @endforeach
                        @endif
                        <option value="DFTM DIGITAL SOLUTIONS">
                        <option value="CONVERGE ICT">
                        <option value="PLDT / SMART">
                        <option value="GLOBE TELECOM">
                        <option value="DITO TELECOMMUNITY">
                    </datalist>
                </div>
                <div class="form-group">
                    <label class="form-label">Date Delivered / Batched</label>
                    <input type="date" name="date_delivered" class="form-control" value="{{ old('date_delivered', date('Y-m-d')) }}">
                </div>
            </div>

            <!-- Header Row 2: Default Brand, Default Model, Default Status -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Brand (Optional)</label>
                    <input type="text" name="brand" list="brandSuggestions" class="form-control" placeholder="e.g. HUAWEI / ZTE" value="{{ old('brand') }}">
                    <datalist id="brandSuggestions">
                        <option value="HUAWEI">
                        <option value="ZTE">
                        <option value="SKYWORTH">
                        <option value="FIBERHOME">
                        <option value="NOKIA">
                    </datalist>
                </div>
                <div class="form-group">
                    <label class="form-label">Model (Optional)</label>
                    <input type="text" name="model" class="form-control" placeholder="e.g. EG8145V5" value="{{ old('model') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Initial Batch Status</label>
                    <select name="status" class="form-select">
                        <option value="In process">In process</option>
                        <option value="Repaired">Repaired</option>
                        <option value="BER">BER</option>
                    </select>
                </div>
            </div>

            <hr style="margin: 20px 0; border: 0; border-top: 1px solid var(--dftm-border);">

            <!-- Filter & Selection Bar -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--dftm-navy); margin-bottom: 2px;">
                        <i class="bi bi-boxes"></i> Select Incoming Scanned Units ({{ $availableItems->count() }} Available to Batch)
                    </h3>
                    <div style="font-size: 0.78rem; color: var(--dftm-slate);">Pumili ng mga units mula sa Incoming para isama sa batch na ito.</div>
                </div>
            </div>

            <!-- Transmittal Filter & Quick Actions -->
            <div style="background: #F8FAFC; border: 1px solid var(--dftm-border); border-radius: var(--radius-md); padding: 12px 16px; margin-bottom: 12px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 280px;">
                    <label style="font-weight: 700; font-size: 0.85rem; color: var(--dftm-navy); white-space: nowrap;">
                        <i class="bi bi-filter-square-fill" style="color: var(--dftm-accent);"></i> Filter by Transmittal:
                    </label>
                    <select id="transmittalFilterSelector" class="form-select" style="max-width: 400px; font-weight: 600;">
                        <option value="all">-- Show All Incoming Units ({{ $availableItems->count() }}) --</option>
                        @foreach($transmittals as $t)
                            @php
                                $tCount = $availableItems->where('transmittal_id', $t->id)->count();
                            @endphp
                            @if($tCount > 0)
                                <option value="{{ $t->id }}" data-brand="{{ $t->brand }}" data-model="{{ $t->model }}" data-company="{{ $t->company_name }}">
                                    {{ $t->transmittal_no }} &bull; {{ $t->brand }} {{ $t->model }} ({{ $tCount }} Units)
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="button" id="btnSelectAllVisible" class="btn btn-outline btn-sm" style="font-weight: 700;">
                        <i class="bi bi-check-all"></i> Select All Visible
                    </button>
                    <button type="button" id="btnDeselectAll" class="btn btn-outline btn-sm" style="color: var(--dftm-slate);">
                        <i class="bi bi-x-circle"></i> Clear Selection
                    </button>
                    <div style="width: 200px;">
                        <input type="text" id="quickUnitSearch" class="form-control form-control-sm" placeholder="Search serial/MAC...">
                    </div>
                </div>
            </div>

            <!-- Available Units Table -->
            <div class="table-responsive" style="max-height: 480px; overflow-y: auto; border: 1px solid var(--dftm-border); border-radius: var(--radius-md);">
                <table class="dftm-table" id="availableUnitsTable">
                    <thead style="position: sticky; top: 0; z-index: 10;">
                        <tr>
                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" id="masterSelectCheckbox" title="Select all visible" style="accent-color: var(--dftm-navy); transform: scale(1.2); cursor: pointer;">
                            </th>
                            <th>SERIAL NUMBER</th>
                            <th>MAC ADDRESS</th>
                            <th>TRANSMITTAL NO.</th>
                            <th>BRAND & MODEL</th>
                            <th>BOX NO.</th>
                            <th>DATE RECEIVED</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($availableItems as $item)
                        <tr class="unit-row" data-transmittal-id="{{ $item->transmittal_id }}">
                            <td style="text-align: center;">
                                <input type="checkbox" name="selected_items[]" value="{{ $item->id }}" 
                                       class="unit-checkbox" 
                                       data-transmittal-id="{{ $item->transmittal_id }}"
                                       data-brand="{{ $item->brand }}"
                                       data-model="{{ $item->model }}"
                                       data-company="{{ $item->company_name ?: ($item->transmittal?->company_name ?? '') }}"
                                       style="accent-color: var(--dftm-navy); transform: scale(1.2); cursor: pointer;">
                            </td>
                            <td><span class="mono" style="font-weight: 700; color: var(--dftm-navy);">{{ $item->serial_number ?? '-' }}</span></td>
                            <td><span class="mono">{{ $item->mac_address ?? '-' }}</span></td>
                            <td>
                                <span class="badge" style="background: #EEF2FF; color: var(--dftm-navy);">
                                    {{ $item->transmittal->transmittal_no ?? 'TR' }}
                                </span>
                            </td>
                            <td><strong>{{ $item->brand }}</strong> {{ $item->model }}</td>
                            <td>{{ $item->box_no ?? '-' }}</td>
                            <td>{{ $item->date_delivered ? $item->date_delivered->format('M d, Y') : '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--dftm-slate); padding: 40px;">
                                <i class="bi bi-inbox" style="font-size: 2rem; display: block; margin-bottom: 8px;"></i>
                                No unassigned units found in Incoming. All units have been assigned to batches!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Notes -->
            <div class="form-group" style="margin-top: 20px;">
                <label class="form-label">Batch Notes / Remarks</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes for this Traceability Batch">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('admin.traceability.index') }}" class="btn btn-outline">
                <i class="bi bi-arrow-left"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2-circle"></i> Create Traceability Batch
            </button>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('availableUnitsTable');
    const masterCheckbox = document.getElementById('masterSelectCheckbox');
    const transmittalSelector = document.getElementById('transmittalFilterSelector');
    const btnSelectAllVisible = document.getElementById('btnSelectAllVisible');
    const btnDeselectAll = document.getElementById('btnDeselectAll');
    const countBadge = document.getElementById('selectedUnitsCount');
    const quickSearch = document.getElementById('quickUnitSearch');

    function updateCount() {
        const checkedBoxes = table.querySelectorAll('.unit-checkbox:checked');
        countBadge.textContent = `${checkedBoxes.length} Selected`;

        // Auto-fill company_name, brand, model from first selected item if empty
        if (checkedBoxes.length > 0) {
            const first = checkedBoxes[0];
            const c = first.getAttribute('data-company');
            const b = first.getAttribute('data-brand');
            const m = first.getAttribute('data-model');
            const companyInput = document.querySelector('[name="company_name"]');
            const brandInput = document.querySelector('[name="brand"]');
            const modelInput = document.querySelector('[name="model"]');
            if (c && companyInput && !companyInput.value) companyInput.value = c;
            if (b && brandInput && !brandInput.value) brandInput.value = b;
            if (m && modelInput && !modelInput.value) modelInput.value = m;
        }
    }

    function filterRows() {
        const selectedTId = transmittalSelector.value;
        const q = quickSearch.value.toLowerCase().trim();
        const rows = table.querySelectorAll('tbody tr.unit-row');

        rows.forEach(r => {
            const rowTId = r.getAttribute('data-transmittal-id');
            const matchesT = (selectedTId === 'all' || rowTId === selectedTId);
            const matchesSearch = !q || r.textContent.toLowerCase().includes(q);

            if (matchesT && matchesSearch) {
                r.style.display = '';
            } else {
                r.style.display = 'none';
            }
        });
    }

    transmittalSelector.addEventListener('change', function() {
        filterRows();
        const selectedOpt = transmittalSelector.options[transmittalSelector.selectedIndex];
        if (selectedOpt && selectedOpt.value !== 'all') {
            const b = selectedOpt.getAttribute('data-brand');
            const m = selectedOpt.getAttribute('data-model');
            const c = selectedOpt.getAttribute('data-company');
            if (b && !document.querySelector('[name="brand"]').value) document.querySelector('[name="brand"]').value = b;
            if (m && !document.querySelector('[name="model"]').value) document.querySelector('[name="model"]').value = m;
            if (c && !document.querySelector('[name="company_name"]').value) document.querySelector('[name="company_name"]').value = c;
        }
    });

    quickSearch.addEventListener('input', filterRows);

    btnSelectAllVisible.addEventListener('click', function() {
        table.querySelectorAll('tbody tr.unit-row').forEach(r => {
            if (r.style.display !== 'none') {
                const cb = r.querySelector('.unit-checkbox');
                if (cb) cb.checked = true;
            }
        });
        updateCount();
    });

    btnDeselectAll.addEventListener('click', function() {
        table.querySelectorAll('.unit-checkbox').forEach(cb => cb.checked = false);
        if (masterCheckbox) masterCheckbox.checked = false;
        updateCount();
    });

    if (masterCheckbox) {
        masterCheckbox.addEventListener('change', function() {
            const checked = this.checked;
            table.querySelectorAll('tbody tr.unit-row').forEach(r => {
                if (r.style.display !== 'none') {
                    const cb = r.querySelector('.unit-checkbox');
                    if (cb) cb.checked = checked;
                }
            });
            updateCount();
        });
    }

    table.addEventListener('change', function(e) {
        if (e.target.classList.contains('unit-checkbox')) {
            updateCount();
        }
    });

    updateCount();
});
</script>
@endsection
