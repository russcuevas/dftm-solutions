@extends('layouts.app')

@section('title', 'Master Inventory')
@section('page_title', 'Master Inventory & Serialized Stock')

@section('content')
<!-- Inventory Metrics Bar -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: #FFFFFF; border: 1px solid var(--dftm-border); border-radius: var(--radius-md); padding: 16px 20px;">
        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--dftm-slate);">TOTAL INVENTORY</span>
        <div style="font-size: 1.6rem; font-weight: 800; color: var(--dftm-navy);">{{ number_format($totalUnits) }} pcs</div>
    </div>
    <div style="background: #FFFFFF; border: 1px solid #BFDBFE; border-radius: var(--radius-md); padding: 16px 20px;">
        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #1E40AF;">AVAILABLE IN-STOCK</span>
        <div style="font-size: 1.6rem; font-weight: 800; color: #1E40AF;">{{ number_format($inStockCount) }} pcs</div>
    </div>
    <div style="background: #FFFFFF; border: 1px solid #DDD6FE; border-radius: var(--radius-md); padding: 16px 20px;">
        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #6D28D9;">RELEASED / OUTGOING</span>
        <div style="font-size: 1.6rem; font-weight: 800; color: #6D28D9;">{{ number_format($releasedCount) }} pcs</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title"><i class="bi bi-boxes"></i> All Serialized Inventory Items</div>
            <div class="card-subtitle">Live real-time stock records, serial & MAC addresses, and diagnostics</div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div style="padding: 16px 24px; background: #F8FAFC; border-bottom: 1px solid var(--dftm-border);">
        <form action="{{ route('admin.inventory.index') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 240px;">
                <input type="text" name="search" class="form-control" placeholder="Search Serial Number, MAC, Brand, Model, Box No, Customer, SI#, DR#..." value="{{ request('search') }}">
            </div>
            <div style="width: 140px;">
                <select name="batch_id" class="form-select">
                    <option value="">All Batches</option>
                    @foreach($batches as $b)
                        <option value="{{ $b->id }}" {{ request('batch_id') == $b->id ? 'selected' : '' }}>{{ $b->batch_no }}</option>
                    @endforeach
                </select>
            </div>
            <div style="width: 140px;">
                <select name="brand" class="form-select">
                    <option value="">All Brands</option>
                    @foreach($brands as $br)
                        <option value="{{ $br }}" {{ request('brand') === $br ? 'selected' : '' }}>{{ $br }}</option>
                    @endforeach
                </select>
            </div>
            <div style="width: 140px;">
                <select name="stock_status" class="form-select">
                    <option value="">Stock Status</option>
                    <option value="IN_STOCK" {{ request('stock_status') === 'IN_STOCK' ? 'selected' : '' }}>IN STOCK</option>
                    <option value="RELEASED" {{ request('stock_status') === 'RELEASED' ? 'selected' : '' }}>RELEASED</option>
                </select>
            </div>
            <div style="width: 140px;">
                <select name="repair_status" class="form-select">
                    <option value="">Repair Status</option>
                    <option value="In process" {{ request('repair_status') === 'In process' ? 'selected' : '' }}>In process</option>
                    <option value="Repaired" {{ request('repair_status') === 'Repaired' ? 'selected' : '' }}>Repaired</option>
                    <option value="BER" {{ request('repair_status') === 'BER' ? 'selected' : '' }}>BER</option>
                </select>
            </div>
            <button type="submit" class="btn btn-outline"><i class="bi bi-funnel"></i> Filter</button>
            @if(request()->anyFilled(['search', 'batch_id', 'brand', 'stock_status', 'repair_status']))
                <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline" style="color: #DC2626;">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="dftm-table">
            <thead>
                <tr>
                    <th>Serial Number</th>
                    <th>MAC Address</th>
                    <th>Batch</th>
                    <th>Brand & Model</th>
                    <th>Box No</th>
                    <th>Diagnostic</th>
                    <th>Replace Parts</th>
                    <th>Repair Status</th>
                    <th>Stock Status</th>
                    <th>Customer / Release</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td><span class="mono" style="font-weight: 700; color: var(--dftm-navy);">{{ $item->serial_number ?? '-' }}</span></td>
                    <td><span class="mono">{{ $item->mac_address ?? '-' }}</span></td>
                    <td>
                        @if($item->batch)
                            <a href="{{ route('admin.incoming.show', $item->batch_id) }}" style="font-weight: 600; color: var(--dftm-accent); text-decoration: none;">
                                {{ $item->batch->batch_no }}
                            </a>
                        @else
                            -
                        @endif
                    </td>
                    <td><strong>{{ $item->brand }}</strong> {{ $item->model }}</td>
                    <td>{{ $item->box_no ?? '-' }}</td>
                    <td><small>{{ $item->technical_diagnostic ?? '-' }}</small></td>
                    <td><small>{{ $item->replace_parts ?? '-' }}</small></td>
                    <td>
                        <span class="badge {{ $item->repair_status === 'Repaired' ? 'badge-repaired' : ($item->repair_status === 'BER' ? 'badge-ber' : 'badge-in-process') }}">
                            {{ $item->repair_status ?? 'In process' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $item->stock_status === 'IN_STOCK' ? 'badge-stock' : 'badge-released' }}">
                            {{ $item->stock_status }}
                        </span>
                    </td>
                    <td>
                        @if($item->outgoingSlip)
                            <a href="{{ route('admin.outgoing.show', $item->outgoing_slip_id) }}" style="font-size: 0.78rem; font-weight: 600; color: #7C3AED; text-decoration: none;">
                                {{ $item->customer_name ?? 'Released' }} ({{ $item->outgoingSlip->slip_no }})
                            </a>
                        @else
                            <span style="color: var(--dftm-slate-light); font-size: 0.78rem;">In Warehouse</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <button type="button" class="btn btn-outline btn-sm" onclick="openItemEditModal({{ json_encode($item) }})" title="Edit Item Details">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('admin.inventory.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Delete this item from inventory?');" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline btn-sm" style="color: #DC2626;" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" style="text-align: center; color: var(--dftm-slate); padding: 32px;">No inventory units found matching criteria.</td>
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

<!-- Item Quick Edit Modal -->
<div class="modal-backdrop" id="itemEditModal">
    <div class="modal-card">
        <form id="itemEditForm" method="POST" data-base-action="{{ url('admin/inventory/__ID__') }}">
            @csrf
            @method('PUT')
            <input type="hidden" id="editItemId">

            <div class="modal-header">
                <div class="modal-title"><i class="bi bi-pencil-square"></i> Edit Serialized Unit</div>
                <button type="button" class="modal-close" data-modal-close><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Serial Number</label>
                        <input type="text" name="serial_number" id="editSerialNumber" class="form-control mono">
                    </div>
                    <div class="form-group">
                        <label class="form-label">MAC Address</label>
                        <input type="text" name="mac_address" id="editMacAddress" class="form-control mono">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Brand</label>
                        <input type="text" name="brand" id="editBrand" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Model</label>
                        <input type="text" name="model" id="editModel" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Box No.</label>
                        <input type="text" name="box_no" id="editBoxNo" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Repair Status</label>
                        <select name="repair_status" id="editRepairStatus" class="form-select">
                            <option value="In process">In process</option>
                            <option value="Repaired">Repaired</option>
                            <option value="BER">BER (Beyond Economic Repair)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Technical Diagnostic</label>
                    <input type="text" name="technical_diagnostic" id="editDiagnostic" class="form-control" placeholder="e.g. NO POWER / CORRODED BOARD">
                </div>

                <div class="form-group">
                    <label class="form-label">Replaced Parts</label>
                    <input type="text" name="replace_parts" id="editReplaceParts" class="form-control" placeholder="e.g. SMD CAPACITOR / DC JACK / N/A">
                </div>

                <div class="form-group">
                    <label class="form-label">Stock Status</label>
                    <select name="stock_status" id="editStockStatus" class="form-select">
                        <option value="IN_STOCK">IN_STOCK (In Warehouse Inventory)</option>
                        <option value="RELEASED">RELEASED (Delivered to Customer)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Notes / Remarks</label>
                    <textarea name="notes" id="editNotes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openItemEditModal(item) {
    const modal = document.getElementById('itemEditModal');
    const form = document.getElementById('itemEditForm');
    if (!modal || !form) return;

    // Set Form Action URL
    form.action = form.dataset.baseAction.replace('__ID__', item.id);

    // Populate Fields
    if (document.getElementById('editItemId')) document.getElementById('editItemId').value = item.id;
    if (document.getElementById('editSerialNumber')) document.getElementById('editSerialNumber').value = item.serial_number || '';
    if (document.getElementById('editMacAddress')) document.getElementById('editMacAddress').value = item.mac_address || '';
    if (document.getElementById('editBrand')) document.getElementById('editBrand').value = item.brand || '';
    if (document.getElementById('editModel')) document.getElementById('editModel').value = item.model || '';
    if (document.getElementById('editBoxNo')) document.getElementById('editBoxNo').value = item.box_no || '';
    if (document.getElementById('editDiagnostic')) document.getElementById('editDiagnostic').value = item.technical_diagnostic || '';
    if (document.getElementById('editReplaceParts')) document.getElementById('editReplaceParts').value = item.replace_parts || '';
    if (document.getElementById('editRepairStatus')) document.getElementById('editRepairStatus').value = item.repair_status || 'In process';
    if (document.getElementById('editStockStatus')) document.getElementById('editStockStatus').value = item.stock_status || 'IN_STOCK';
    if (document.getElementById('editNotes')) document.getElementById('editNotes').value = item.notes || '';

    modal.classList.add('active');
}
</script>
@endsection
