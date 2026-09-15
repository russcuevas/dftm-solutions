@extends('layouts.app')

@section('title', 'Consumable Categories')
@section('page_title', 'Category Classification Manager')

@section('content')
<!-- Header & Action Row -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--dftm-navy); margin: 0;">Consumable Categories</h2>
        <p style="font-size: 0.85rem; color: var(--dftm-slate); margin: 4px 0 0 0;">Create and organize item groups (e.g., BOXES, RAGS, PAPER, PROD MATERIALS, etc.)</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <a href="{{ route('admin.consumables.index') }}" class="btn btn-outline">
            <i class="bi bi-arrow-left"></i> Back to Ledger
        </a>
        <button type="button" class="btn btn-accent" onclick="openCreateCategoryModal()">
            <i class="bi bi-plus-circle-fill"></i> Add New Category
        </button>
    </div>
</div>

<!-- Main Categories Card -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div class="card-title"><i class="bi bi-tags-fill"></i> Active Category List</div>
            <div class="card-subtitle">Manage dynamic category grouping and dropdown items</div>
        </div>
        <div>
            <span class="badge badge-stock">{{ $categories->count() }} Categories</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="dftm-table">
            <thead>
                <tr>
                    <th style="width: 70px;">#</th>
                    <th style="width: 250px;">Category Name</th>
                    <th>Description / Purpose</th>
                    <th style="width: 140px; text-align: center;">Assigned Items</th>
                    <th style="width: 120px; text-align: center;">Sort Order</th>
                    <th style="width: 120px; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $index => $category)
                    <tr>
                        <td style="color: var(--dftm-slate); font-size: 0.8rem;">{{ $index + 1 }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class="bi bi-folder2-open" style="color: var(--dftm-accent); font-size: 1.1rem;"></i>
                                <strong style="color: var(--dftm-navy); font-size: 0.92rem;">{{ $category->name }}</strong>
                            </div>
                        </td>
                        <td style="color: var(--dftm-slate);">
                            {{ $category->description ?? '—' }}
                        </td>
                        <td style="text-align: center;">
                            <span class="badge badge-stock">{{ $category->items_count }} items</span>
                        </td>
                        <td style="text-align: center;">
                            <span class="mono" style="color: var(--dftm-slate);">{{ $category->sort_order }}</span>
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 6px; justify-content: center;">
                                <button type="button" class="btn btn-outline btn-icon" 
                                        onclick="openEditCategoryModal({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->description ?? '') }}', {{ $category->sort_order }})"
                                        title="Edit Category">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="{{ route('admin.consumables.categories.destroy', $category->id) }}" method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete category \'{{ $category->name }}\'? Items in this category will also be removed!');" 
                                      style="display: inline-block; margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline btn-icon" style="color: #DC2626; border-color: rgba(220, 38, 38, 0.2);" title="Delete Category">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 48px 20px; color: var(--dftm-slate);">
                            <i class="bi bi-tags" style="font-size: 2.5rem; color: var(--dftm-slate-light); display: block; margin-bottom: 8px;"></i>
                            <div style="font-size: 1rem; font-weight: 700; color: var(--dftm-navy);">No categories found</div>
                            <p style="font-size: 0.85rem; margin: 4px 0 0 0;">Create categories to organize your consumable items.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add Category -->
<div class="modal-backdrop-custom" id="createCategoryModal" style="display: none;">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h4 class="modal-title-custom">
                <i class="bi bi-tag-fill text-primary"></i> Add New Category
            </h4>
            <button type="button" class="btn-close-custom" onclick="closeCreateCategoryModal()">&times;</button>
        </div>
        <form action="{{ route('admin.consumables.categories.store') }}" method="POST">
            @csrf
            <div class="modal-body-custom">
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Category Name <span style="color: #DC2626;">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. PACKAGING, CLEANING, TOOLS" required autofocus>
                    <span style="font-size: 0.72rem; color: var(--dftm-slate);">Will be formatted in uppercase automatically</span>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Description (Optional)</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Brief note about this category..."></textarea>
                </div>

                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Sort Order (Optional)</label>
                    <input type="number" name="sort_order" class="form-control" value="0" min="0">
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeCreateCategoryModal()">Cancel</button>
                <button type="submit" class="btn btn-accent">
                    <i class="bi bi-check2-circle"></i> Save Category
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Category -->
<div class="modal-backdrop-custom" id="editCategoryModal" style="display: none;">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h4 class="modal-title-custom">
                <i class="bi bi-pencil-square text-primary"></i> Edit Category
            </h4>
            <button type="button" class="btn-close-custom" onclick="closeEditCategoryModal()">&times;</button>
        </div>
        <form id="editCategoryForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body-custom">
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Category Name <span style="color: #DC2626;">*</span></label>
                    <input type="text" name="name" id="editCategoryName" class="form-control" required>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Description (Optional)</label>
                    <textarea name="description" id="editCategoryDesc" class="form-control" rows="2"></textarea>
                </div>

                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Sort Order (Optional)</label>
                    <input type="number" name="sort_order" id="editCategorySort" class="form-control" min="0">
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeEditCategoryModal()">Cancel</button>
                <button type="submit" class="btn btn-accent">
                    <i class="bi bi-check-lg"></i> Update Category
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
function openCreateCategoryModal() {
    document.getElementById('createCategoryModal').style.display = 'flex';
}

function closeCreateCategoryModal() {
    document.getElementById('createCategoryModal').style.display = 'none';
}

function openEditCategoryModal(id, name, desc, sort) {
    const form = document.getElementById('editCategoryForm');
    form.action = "{{ url('admin/consumables/categories') }}/" + id;
    document.getElementById('editCategoryName').value = name;
    document.getElementById('editCategoryDesc').value = desc;
    document.getElementById('editCategorySort').value = sort;
    document.getElementById('editCategoryModal').style.display = 'flex';
}

function closeEditCategoryModal() {
    document.getElementById('editCategoryModal').style.display = 'none';
}

window.onclick = function(event) {
    const cModal = document.getElementById('createCategoryModal');
    const eModal = document.getElementById('editCategoryModal');
    if (event.target == cModal) closeCreateCategoryModal();
    if (event.target == eModal) closeEditCategoryModal();
}
</script>
@endpush
