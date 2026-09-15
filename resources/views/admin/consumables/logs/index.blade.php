@extends('layouts.app')

@section('title', 'Daily In/Out Logs')
@section('page_title', 'Consumable Transaction Logs')

@section('content')
<!-- Top Header & Action Row -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--dftm-navy); margin: 0;">Daily Stock In / Out Transaction Logs</h2>
        <p style="font-size: 0.85rem; color: var(--dftm-slate); margin: 4px 0 0 0;">Audit trail of all consumable deliveries, usages, and daily stock adjustments</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <a href="{{ route('admin.consumables.index') }}" class="btn btn-outline">
            <i class="bi bi-arrow-left"></i> Back to Ledger
        </a>
        <button type="button" class="btn btn-accent" onclick="openCreateLogModal()">
            <i class="bi bi-plus-circle-fill"></i> Record Daily Entry
        </button>
    </div>
</div>

<!-- Main Logs Card -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div class="card-title"><i class="bi bi-clock-history"></i> All Transaction Records</div>
            <div class="card-subtitle">Real-time daily stock entries and consumption history</div>
        </div>
        <div>
            <span class="badge badge-stock">{{ number_format($logs->total()) }} Total Logs</span>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div style="padding: 16px 24px; background: #F8FAFC; border-bottom: 1px solid var(--dftm-border);">
        <form action="{{ route('admin.consumables.logs.index') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <div style="width: 180px;">
                <select name="category_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="width: 200px;">
                <select name="item_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Items</option>
                    @foreach($items as $it)
                        <option value="{{ $it->id }}" {{ $itemId == $it->id ? 'selected' : '' }}>{{ $it->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; align-items: center; gap: 6px;">
                <input type="date" name="start_date" class="form-control" style="width: 145px;" value="{{ $startDate }}" title="From Date">
                <span style="color: var(--dftm-slate); font-size: 0.8rem; font-weight: 600;">to</span>
                <input type="date" name="end_date" class="form-control" style="width: 145px;" value="{{ $endDate }}" title="To Date">
            </div>

            <div style="flex: 1; min-width: 200px;">
                <input type="text" name="search" class="form-control" placeholder="Search reference, item, remarks..." value="{{ $search }}">
            </div>

            <button type="submit" class="btn btn-outline"><i class="bi bi-funnel"></i> Filter</button>

            @if($categoryId || $itemId || $startDate || $endDate || $search)
                <a href="{{ route('admin.consumables.logs.index') }}" class="btn btn-outline" style="color: #DC2626;">Reset</a>
            @endif
        </form>
    </div>

    <!-- Logs Table -->
    <div class="table-responsive">
        <table class="dftm-table">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th style="width: 130px;">Date</th>
                    <th>Item Description</th>
                    <th>Category</th>
                    <th style="width: 100px; text-align: center;">Type</th>
                    <th style="width: 130px; text-align: right;">Quantity</th>
                    <th>Reference / Purpose</th>
                    <th style="width: 150px;">Logged By</th>
                    <th style="width: 80px; text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $index => $log)
                    <tr>
                        <td style="color: var(--dftm-slate); font-size: 0.8rem;">{{ $logs->firstItem() + $index }}</td>
                        <td>
                            <strong style="color: var(--dftm-navy);">{{ \Carbon\Carbon::parse($log->log_date)->format('M d, Y') }}</strong>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--dftm-navy);">{{ $log->item->name ?? '—' }}</div>
                        </td>
                        <td>
                            <span class="badge badge-stock">{{ $log->item->category->name ?? '—' }}</span>
                        </td>
                        <td style="text-align: center;">
                            @if($log->in_qty > 0)
                                <span class="badge badge-repaired">
                                    <i class="bi bi-arrow-down-left"></i> IN
                                </span>
                            @else
                                <span class="badge badge-ber">
                                    <i class="bi bi-arrow-up-right"></i> OUT
                                </span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            @if($log->in_qty > 0)
                                <span class="mono" style="color: #059669; font-weight: 800; font-size: 0.95rem;">
                                    +{{ number_format($log->in_qty) }} <span style="font-size: 0.75rem; font-weight: 600;">{{ $log->item->unit ?? '' }}</span>
                                </span>
                            @else
                                <span class="mono" style="color: #DC2626; font-weight: 800; font-size: 0.95rem;">
                                    -{{ number_format($log->out_qty) }} <span style="font-size: 0.75rem; font-weight: 600;">{{ $log->item->unit ?? '' }}</span>
                                </span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                @if($log->reference_no)
                                    <span class="mono" style="font-size: 0.78rem; color: var(--dftm-accent); font-weight: 700;">{{ $log->reference_no }}</span>
                                @endif
                                <span style="color: var(--dftm-slate); font-size: 0.85rem;">{{ $log->remarks ?? '—' }}</span>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <div style="width: 24px; height: 24px; border-radius: 50%; background: #E2E8F0; color: var(--dftm-navy); display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700;">
                                    {{ strtoupper(substr($log->user->name ?? 'S', 0, 1)) }}
                                </div>
                                <span style="font-size: 0.82rem; color: #334155; font-weight: 600;">{{ $log->user->name ?? 'System' }}</span>
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <form action="{{ route('admin.consumables.logs.destroy', $log->id) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this transaction record?');"
                                  style="margin: 0; display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline btn-icon" style="color: #DC2626; border-color: rgba(220, 38, 38, 0.2);" title="Delete Log">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 48px 20px; color: var(--dftm-slate);">
                            <i class="bi bi-clock-history" style="font-size: 2.5rem; color: var(--dftm-slate-light); display: block; margin-bottom: 8px;"></i>
                            <div style="font-size: 1rem; font-weight: 700; color: var(--dftm-navy);">No transaction logs found</div>
                            <p style="font-size: 0.85rem; margin: 4px 0 0 0;">Try adjusting your search criteria or record a new daily entry.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div class="card-footer">
            {{ $logs->links() }}
        </div>
    @endif
</div>

<!-- Modal: Record Daily In/Out Entry -->
<div class="modal-backdrop-custom" id="createLogModal" style="display: none;">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h4 class="modal-title-custom">
                <i class="bi bi-plus-circle text-primary"></i> Record Daily In / Out Entry
            </h4>
            <button type="button" class="btn-close-custom" onclick="closeCreateLogModal()">&times;</button>
        </div>
        <form action="{{ route('admin.consumables.dailyLog.store') }}" method="POST">
            @csrf
            <div class="modal-body-custom">
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Consumable Item <span style="color: #DC2626;">*</span></label>
                    <select name="consumable_item_id" class="form-select" required>
                        <option value="">-- Select Consumable Item --</option>
                        @foreach($categories as $cat)
                            <optgroup label="{{ $cat->name }}">
                                @foreach($cat->items as $it)
                                    <option value="{{ $it->id }}">{{ $it->name }} ({{ $it->unit }})</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Date <span style="color: #DC2626;">*</span></label>
                    <input type="date" name="log_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                    <div>
                        <label style="font-size: 0.82rem; font-weight: 700; color: #059669; display: block; margin-bottom: 6px;">
                            <i class="bi bi-box-arrow-in-down"></i> Stock IN Qty
                        </label>
                        <input type="number" name="in_qty" class="form-control" min="0" value="0" placeholder="0">
                        <span style="font-size: 0.72rem; color: var(--dftm-slate);">Arrivals / Deliveries</span>
                    </div>
                    <div>
                        <label style="font-size: 0.82rem; font-weight: 700; color: #DC2626; display: block; margin-bottom: 6px;">
                            <i class="bi bi-box-arrow-up-right"></i> Stock OUT Qty
                        </label>
                        <input type="number" name="out_qty" class="form-control" min="0" value="0" placeholder="0">
                        <span style="font-size: 0.72rem; color: var(--dftm-slate);">Used / Consumed</span>
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Reference No. (Optional)</label>
                    <input type="text" name="reference_no" class="form-control" placeholder="e.g. SLIP-2026-001 or PO #102">
                </div>

                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Remarks / Purpose (Optional)</label>
                    <input type="text" name="remarks" class="form-control" placeholder="e.g. Issued to Production Line">
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeCreateLogModal()">Cancel</button>
                <button type="submit" class="btn btn-accent">
                    <i class="bi bi-check2-circle"></i> Save Entry
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.modal-backdrop-custom {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 18, 53, 0.65);
    backdrop-filter: blur(4px);
    z-index: 999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-dialog-custom {
    background: #FFFFFF;
    border-radius: var(--radius-lg);
    width: 95%;
    max-width: 500px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25);
    overflow: hidden;
}

.modal-header-custom {
    padding: 16px 22px;
    background: #F8FAFC;
    border-bottom: 1px solid var(--dftm-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title-custom {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--dftm-navy);
    margin: 0;
}

.btn-close-custom {
    background: transparent;
    border: none;
    font-size: 1.5rem;
    line-height: 1;
    cursor: pointer;
    color: var(--dftm-slate);
}

.modal-body-custom {
    padding: 22px;
}

.modal-footer-custom {
    padding: 14px 22px;
    background: #F8FAFC;
    border-top: 1px solid var(--dftm-border);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
</style>
@endpush

@push('scripts')
<script>
function openCreateLogModal() {
    document.getElementById('createLogModal').style.display = 'flex';
}

function closeCreateLogModal() {
    document.getElementById('createLogModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('createLogModal');
    if (event.target == modal) closeCreateLogModal();
}
</script>
@endpush
