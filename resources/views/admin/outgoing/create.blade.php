@extends('layouts.app')

@section('title', 'New Outgoing Repair Slip')
@section('page_title', 'Create Outgoing Repair Slip')

@section('content')
<form action="{{ route('admin.outgoing.store') }}" method="POST" id="outgoingForm">
    @csrf

    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title"><i class="bi bi-box-arrow-up-right"></i> Outgoing Repair Slip Details</div>
                <div class="card-subtitle">Select in-stock items to release to customer (Stock will automatically be deducted)</div>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <span class="badge badge-released" id="selectedUnitsCount" style="font-size: 0.9rem; padding: 6px 14px;">0 Selected</span>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send-check"></i> Process Outgoing & Deduct Stock
                </button>
            </div>
        </div>

        <div class="card-body">
            <!-- Header Row 1: Slip No, Company Name, SI Number, DR Number -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Slip Number</label>
                    <input type="text" name="slip_no" class="form-control mono" value="{{ old('slip_no') }}" placeholder="Enter Slip Number (e.g. ORS-20260905-001)" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" id="outgoingCompany" list="outgoingCompanySuggestions" class="form-control" placeholder="Enter Company Name (Optional)" value="{{ old('company_name') }}">
                    <datalist id="outgoingCompanySuggestions">
                        <option value="DFTM DIGITAL SOLUTIONS">
                        <option value="CONVERGE ICT">
                        <option value="PLDT / SMART">
                        <option value="GLOBE TELECOM">
                        <option value="DITO TELECOMMUNITY">
                    </datalist>
                </div>
                <div class="form-group">
                    <label class="form-label">SI Number (Sales Invoice)</label>
                    <input type="text" name="si_number" class="form-control mono" placeholder="e.g. SI-10293" value="{{ old('si_number') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">DR Number (Delivery Receipt)</label>
                    <input type="text" name="dr_number" class="form-control mono" placeholder="e.g. DR-58392" value="{{ old('dr_number') }}">
                </div>
            </div>

            <!-- Header Row 2: Date Delivered, Status, Box No -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Date Delivered</label>
                    <input type="date" name="date_delivered" class="form-control" value="{{ old('date_delivered', date('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Status (Optional)</label>
                    <input type="text" name="status" class="form-control" placeholder="Status (Optional)" value="{{ old('status') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Box No.</label>
                    <input type="text" name="box_no" class="form-control" placeholder="e.g. BOX 1" value="{{ old('box_no') }}">
                </div>
            </div>

            <hr style="margin: 24px 0; border: 0; border-top: 1px solid var(--dftm-border);">

            <!-- Available In-Stock Items Selector Table with Batch Filter -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--dftm-navy); margin-bottom: 2px;">
                        <i class="bi bi-boxes"></i> Select Available Units from Inventory ({{ $availableItems->count() }} Total Available)
                    </h3>
                    <div style="font-size: 0.78rem; color: var(--dftm-slate);">Pumili ng Batch mula sa dropdown para ma-filter at ma-Select All ang units ng batch na iyon nang mabilisan.</div>
                </div>
            </div>

            <!-- Batch Filter & Quick Action Bar -->
            <div style="background: #F8FAFC; border: 1px solid var(--dftm-border); border-radius: var(--radius-md); padding: 12px 16px; margin-bottom: 12px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 280px;">
                    <label style="font-weight: 700; font-size: 0.85rem; color: var(--dftm-navy); white-space: nowrap;">
                        <i class="bi bi-filter-square-fill" style="color: var(--dftm-accent);"></i> Batch Filter:
                    </label>
                    <select id="batchFilterSelector" class="form-select" style="max-width: 400px; font-weight: 600; border-color: var(--dftm-accent); background-color: #FFFFFF;">
                        <option value="all" data-count="{{ $availableItems->count() }}">
                            -- Show All Batches ({{ $availableItems->count() }} Available Units) --
                        </option>
                        @foreach($batches as $b)
                            @php
                                $batchItemsCount = $availableItems->where('batch_id', $b->id)->count();
                            @endphp
                            @if($batchItemsCount > 0)
                                <option value="{{ $b->id }}" 
                                        data-batch-no="{{ $b->batch_no }}" 
                                        data-brand="{{ $b->brand }}" 
                                        data-model="{{ $b->model }}" 
                                        data-company="{{ $b->company_name }}" 
                                        data-date-delivered="{{ $b->date_delivered ? $b->date_delivered->format('Y-m-d') : '' }}" 
                                        data-description="{{ $b->item_description }}"
                                        data-count="{{ $batchItemsCount }}"
                                        {{ ($selectedBatchId ?? null) == $b->id ? 'selected' : '' }}>
                                    {{ $b->batch_no }} &bull; {{ $b->brand }} {{ $b->model }} ({{ $batchItemsCount }} Units)
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="button" id="btnSelectAllBatch" class="btn btn-outline btn-sm" style="font-weight: 600;" title="Select all units visible in currently filtered batch">
                        <i class="bi bi-check-all"></i> Select All in Batch
                    </button>
                    <button type="button" id="btnDeselectAll" class="btn btn-outline btn-sm" style="color: var(--dftm-slate);" title="Clear selection">
                        <i class="bi bi-x-circle"></i> Clear
                    </button>
                    <div style="width: 220px;">
                        <input type="text" class="form-control" placeholder="Search serial/MAC..." data-table-search="availableItemsTable">
                    </div>
                </div>
            </div>

            <div class="table-responsive" style="max-height: 480px; overflow-y: auto; border: 1px solid var(--dftm-border); border-radius: var(--radius-md);">
                <table class="dftm-table" id="availableItemsTable">
                    <thead style="position: sticky; top: 0; z-index: 10;">
                        <tr>
                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" id="selectAllUnits" title="Select / Deselect all visible units in current batch" style="accent-color: var(--dftm-navy); transform: scale(1.2); cursor: pointer;">
                            </th>
                            <th>SERIAL NUMBER</th>
                            <th>MAC ADDRESS</th>
                            <th>BATCH</th>
                            <th>BRAND & MODEL</th>
                            <th>BOX NO.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($availableItems as $item)
                        <tr data-batch-id="{{ $item->batch_id }}">
                            <td style="text-align: center;">
                                <input type="checkbox" name="selected_items[]" value="{{ $item->id }}" 
                                       class="item-select-checkbox" 
                                       data-batch-id="{{ $item->batch_id }}"
                                       data-brand="{{ $item->brand }}" 
                                       data-model="{{ $item->model }}" 
                                       data-company="{{ $item->company_name }}"
                                       style="accent-color: var(--dftm-navy); transform: scale(1.2); cursor: pointer;">
                            </td>
                            <td><span class="mono" style="font-weight: 700; color: var(--dftm-navy);">{{ $item->serial_number ?? '-' }}</span></td>
                            <td><span class="mono">{{ $item->mac_address ?? '-' }}</span></td>
                            <td><span class="badge badge-stock">{{ $item->batch->batch_no ?? 'BATCH' }}</span></td>
                            <td><strong>{{ $item->brand }}</strong> {{ $item->model }}</td>
                            <td>{{ $item->box_no ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--dftm-slate); padding: 32px;">No items currently available in stock to release. Please encode incoming items first.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Notes -->
            <div class="form-group" style="margin-top: 20px;">
                <label class="form-label">Outgoing Slip Remarks / Notes</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Optional release notes, warranty info, or customer signatures notes">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('admin.outgoing.index') }}" class="btn btn-outline">
                <i class="bi bi-arrow-left"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-send-check"></i> Generate Outgoing Repair Slip
            </button>
        </div>
    </div>
</form>
@endsection
