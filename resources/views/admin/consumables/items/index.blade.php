@extends('layouts.app')

@section('title', 'Consumable Items')
@section('page_title', 'Consumable Items Catalog')

@section('content')
<!-- Header & Action Row -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--dftm-navy); margin: 0;">Consumable Items Directory</h2>
        <p style="font-size: 0.85rem; color: var(--dftm-slate); margin: 4px 0 0 0;">Manage item descriptions, customizable packaging units, and unit costs</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <a href="{{ route('admin.consumables.index') }}" class="btn btn-outline">
            <i class="bi bi-arrow-left"></i> Back to Ledger
        </a>
        <button type="button" class="btn btn-accent" onclick="openCreateItemModal()">
            <i class="bi bi-plus-circle-fill"></i> Add New Item
        </button>
    </div>
</div>

<!-- Items Table Card -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div class="card-title"><i class="bi bi-cart-check"></i> Registered Consumable Products</div>
            <div class="card-subtitle">Active items list, units, and inventory valuation unit pricing</div>
        </div>
        <div>
            <span class="badge badge-stock">{{ number_format($items->total()) }} Registered Items</span>
        </div>
    </div>

    <!-- Filters Bar -->
    <div style="padding: 16px 24px; background: #F8FAFC; border-bottom: 1px solid var(--dftm-border);">
        <form action="{{ route('admin.consumables.items.index') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <div style="width: 200px;">
                <select name="category_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="flex: 1; min-width: 220px;">
                <input type="text" name="search" class="form-control" placeholder="Search item description, unit..." value="{{ $search }}">
            </div>

            <button type="submit" class="btn btn-outline"><i class="bi bi-funnel"></i> Filter</button>

            @if($categoryId || $search)
                <a href="{{ route('admin.consumables.items.index') }}" class="btn btn-outline" style="color: #DC2626;">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="dftm-table">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Item Description</th>
                    <th style="width: 180px;">Category</th>
                    <th style="width: 120px; text-align: center;">Unit</th>
                    <th style="width: 140px; text-align: right;">Unit Cost</th>
                    <th style="width: 120px; text-align: center;">Status</th>
                    <th style="width: 110px; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $index => $item)
                    <tr>
                        <td style="color: var(--dftm-slate); font-size: 0.8rem;">{{ $items->firstItem() + $index }}</td>
                        <td>
                            <div style="font-weight: 700; color: var(--dftm-navy);">{{ $item->name }}</div>
                        </td>
                        <td>
                            <span class="badge badge-stock">{{ $item->category->name ?? 'Uncategorized' }}</span>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge" style="background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1;">{{ $item->unit }}</span>
                        </td>
                        <td style="text-align: right;">
                            <span class="mono" style="font-weight: 700; color: var(--dftm-navy);">
                                {{ $item->cost > 0 ? '₱' . number_format($item->cost, 2) : '—' }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            @if($item->is_active)
                                <span class="badge badge-repaired">Active</span>
                            @else
                                <span class="badge" style="background: #F1F5F9; color: #64748B;">Inactive</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 6px; justify-content: center;">
                                <button type="button" class="btn btn-outline btn-icon" 
                                        onclick="openEditItemModal({{ $item->id }}, {{ $item->category_id }}, '{{ addslashes($item->name) }}', '{{ addslashes($item->unit) }}', '{{ $item->cost }}', {{ $item->is_active ? 'true' : 'false' }})"
                                        title="Edit Item">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="{{ route('admin.consumables.items.destroy', $item->id) }}" method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete item \'{{ $item->name }}\'?');" 
                                      style="display: inline-block; margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline btn-icon" style="color: #DC2626; border-color: rgba(220, 38, 38, 0.2);" title="Delete Item">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 48px 20px; color: var(--dftm-slate);">
                            <i class="bi bi-boxes" style="font-size: 2.5rem; color: var(--dftm-slate-light); display: block; margin-bottom: 8px;"></i>
                            <div style="font-size: 1rem; font-weight: 700; color: var(--dftm-navy);">No consumable items found</div>
                            <p style="font-size: 0.85rem; margin: 4px 0 0 0;">Add your first consumable item to start tracking inventory.</p>
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

<!-- Modal: Add Item -->
<div class="modal-backdrop-custom" id="createItemModal" style="display: none;">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h4 class="modal-title-custom">
                <i class="bi bi-box-seam text-primary"></i> Add New Consumable Item
            </h4>
            <button type="button" class="btn-close-custom" onclick="closeCreateItemModal()">&times;</button>
        </div>
        <form action="{{ route('admin.consumables.items.store') }}" method="POST">
            @csrf
            <div class="modal-body-custom">
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Category <span style="color: #DC2626;">*</span></label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Item Description / Name <span style="color: #DC2626;">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. White Rags 100's or SHORT BOND PAPER" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                    <div>
                        <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Unit <span style="color: #DC2626;">*</span></label>
                        <input type="text" name="unit" list="unitSuggestions" class="form-control" placeholder="BUNDLE, PCS, PCK..." required>
                        <datalist id="unitSuggestions">
                            @foreach($commonUnits as $u)
                                <option value="{{ $u }}"></option>
                            @endforeach
                        </datalist>
                    </div>

                    <div>
                        <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Unit Cost (₱)</label>
                        <input type="number" step="0.01" min="0" name="cost" class="form-control" placeholder="0.00" value="0.00">
                    </div>
                </div>

                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Initial Beginning Stock (Current Month)</label>
                    <input type="number" name="initial_beginning" class="form-control" min="0" value="0" placeholder="0">
                    <span style="font-size: 0.72rem; color: var(--dftm-slate);">Sets beginning stock count for {{ date('F Y') }}</span>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeCreateItemModal()">Cancel</button>
                <button type="submit" class="btn btn-accent">
                    <i class="bi bi-check2-circle"></i> Save Item
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Item -->
<div class="modal-backdrop-custom" id="editItemModal" style="display: none;">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h4 class="modal-title-custom">
                <i class="bi bi-pencil-square text-primary"></i> Edit Consumable Item
            </h4>
            <button type="button" class="btn-close-custom" onclick="closeEditItemModal()">&times;</button>
        </div>
        <form id="editItemForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body-custom">
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Category <span style="color: #DC2626;">*</span></label>
                    <select name="category_id" id="editItemCategory" class="form-select" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Item Description / Name <span style="color: #DC2626;">*</span></label>
                    <input type="text" name="name" id="editItemName" class="form-control" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                    <div>
                        <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Unit <span style="color: #DC2626;">*</span></label>
                        <input type="text" name="unit" id="editItemUnit" list="unitSuggestions" class="form-control" required>
                    </div>

                    <div>
                        <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Unit Cost (₱)</label>
                        <input type="number" step="0.01" min="0" name="cost" id="editItemCost" class="form-control">
                    </div>
                </div>

                <div>
                    <label style="font-size: 0.85rem; font-weight: 600; color: var(--dftm-navy); display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="is_active" id="editItemActive" value="1" style="width: 16px; height: 16px;">
                        <span>Active in Monthly Ledger</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeEditItemModal()">Cancel</button>
                <button type="submit" class="btn btn-accent">
                    <i class="bi bi-check-lg"></i> Update Item
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
function openCreateItemModal() {
    document.getElementById('createItemModal').style.display = 'flex';
}

function closeCreateItemModal() {
    document.getElementById('createItemModal').style.display = 'none';
}

function openEditItemModal(id, categoryId, name, unit, cost, isActive) {
    const form = document.getElementById('editItemForm');
    form.action = "{{ url('admin/consumables/items') }}/" + id;
    document.getElementById('editItemCategory').value = categoryId;
    document.getElementById('editItemName').value = name;
    document.getElementById('editItemUnit').value = unit;
    document.getElementById('editItemCost').value = cost;
    document.getElementById('editItemActive').checked = isActive;
    document.getElementById('editItemModal').style.display = 'flex';
}

function closeEditItemModal() {
    document.getElementById('editItemModal').style.display = 'none';
}

window.onclick = function(event) {
    const cModal = document.getElementById('createItemModal');
    const eModal = document.getElementById('editItemModal');
    if (event.target == cModal) closeCreateItemModal();
    if (event.target == eModal) closeEditItemModal();
}
</script>
@endpush
