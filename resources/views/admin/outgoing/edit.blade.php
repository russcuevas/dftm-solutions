@extends('layouts.app')

@section('title', 'Edit Outgoing Slip - ' . $slip->slip_no)
@section('page_title', 'Edit Outgoing Repair Slip')

@section('content')
<form action="{{ route('admin.outgoing.update', $slip->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title"><i class="bi bi-pencil-square"></i> Edit Outgoing Slip: {{ $slip->slip_no }}</div>
                <div class="card-subtitle">Manage released units, add more in-stock items, or remove items to return them to inventory stock</div>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('admin.outgoing.show', $slip->id) }}" class="btn btn-outline">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2-circle"></i> Save All Changes & Update Stock
                </button>
            </div>
        </div>

        <div class="card-body">
            <!-- Header Row 1: Slip No, Company Name, SI Number, DR Number -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Slip Number</label>
                    <input type="text" name="slip_no" class="form-control mono" value="{{ old('slip_no', $slip->slip_no) }}" placeholder="e.g. ORS-20260905-001" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" list="companySuggestions" class="form-control" value="{{ old('company_name', $slip->company_name) }}" placeholder="Enter Company Name">
                    <datalist id="companySuggestions">
                        <option value="DFTM DIGITAL SOLUTIONS">
                        <option value="CONVERGE ICT">
                        <option value="PLDT / SMART">
                        <option value="GLOBE TELECOM">
                        <option value="DITO TELECOMMUNITY">
                    </datalist>
                </div>
                <div class="form-group">
                    <label class="form-label">SI Number (Sales Invoice)</label>
                    <input type="text" name="si_number" class="form-control mono" value="{{ old('si_number', $slip->si_number) }}" placeholder="e.g. SI-10293">
                </div>
                <div class="form-group">
                    <label class="form-label">DR Number (Delivery Receipt)</label>
                    <input type="text" name="dr_number" class="form-control mono" value="{{ old('dr_number', $slip->dr_number) }}" placeholder="e.g. DR-58392">
                </div>
            </div>

            <!-- Header Row 2: Date Delivered, Status, Box No -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Date Delivered</label>
                    <input type="date" name="date_delivered" class="form-control" value="{{ old('date_delivered', $slip->date_delivered ? $slip->date_delivered->format('Y-m-d') : '') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Status (Optional)</label>
                    <input type="text" name="status" class="form-control" value="{{ old('status', $slip->status) }}" placeholder="e.g. Repaired / In process">
                </div>
                <div class="form-group">
                    <label class="form-label">Box No.</label>
                    <input type="text" name="box_no" class="form-control" value="{{ old('box_no', $slip->box_no) }}" placeholder="e.g. BOX 1">
                </div>
            </div>

            <!-- Header Row 3: Batch No, Brand, Model -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Batch No.</label>
                    <input type="text" name="batch_no" class="form-control" value="{{ old('batch_no', $slip->batch_no) }}" placeholder="e.g. BATCH 1">
                </div>
                <div class="form-group">
                    <label class="form-label">Brand</label>
                    <input type="text" name="brand" list="brandSuggestions" class="form-control" value="{{ old('brand', $slip->brand) }}" placeholder="Enter Brand">
                    <datalist id="brandSuggestions">
                        <option value="HUAWEI">
                        <option value="ZTE">
                        <option value="SKYWORTH">
                        <option value="FIBERHOME">
                        <option value="NOKIA">
                    </datalist>
                </div>
                <div class="form-group">
                    <label class="form-label">Model</label>
                    <input type="text" name="model" class="form-control" value="{{ old('model', $slip->model) }}" placeholder="Enter Model">
                </div>
            </div>

            <hr style="margin: 24px 0; border: 0; border-top: 1px solid var(--dftm-border);">

            <!-- SECTION 1: Current Items Attached to this Slip -->
            <div class="subtable-container" style="margin-bottom: 28px;">
                <div class="subtable-header">
                    <div>
                        <div class="subtable-title" style="color: var(--dftm-navy);">
                            <i class="bi bi-box-seam-fill"></i> Current Released Units in this Slip ({{ $slip->items->count() }} Units)
                        </div>
                        <div style="font-size: 0.78rem; color: var(--dftm-slate);">
                            Maaari mong baguhin ang serial/MAC/box o tanggalin ang row gamit ang trash icon (<strong>Awtomatikong ibabalik sa stock ang matatanggal na units pagka-save</strong>).
                        </div>
                    </div>
                </div>

                <!-- Column Headers -->
                <div style="display: grid; grid-template-columns: 45px 2fr 2fr 1.2fr 45px; gap: 10px; padding: 8px 14px; font-size: 0.75rem; font-weight: 700; color: var(--dftm-navy); text-transform: uppercase; background: #F8FAFC; border-bottom: 1px solid var(--dftm-border); border-radius: var(--radius-sm) var(--radius-sm) 0 0;">
                    <div style="text-align: center;">NO.</div>
                    <div>Serial Number</div>
                    <div>MAC Address</div>
                    <div>Box No</div>
                    <div style="text-align: center;">Action</div>
                </div>

                <div id="subtableRows">
                    @forelse($slip->items as $item)
                    <div class="subtable-row" style="grid-template-columns: 45px 2fr 2fr 1.2fr 45px;">
                        <input type="hidden" name="item_id[]" value="{{ $item->id }}">
                        <div class="subtable-row-num">{{ $loop->iteration }}</div>
                        <div>
                            <input type="text" name="serial_number[]" class="form-control mono" value="{{ $item->serial_number }}" placeholder="Serial Number">
                        </div>
                        <div>
                            <input type="text" name="mac_address[]" class="form-control mono" value="{{ $item->mac_address }}" placeholder="MAC Address">
                        </div>
                        <div>
                            <input type="text" name="box_no_item[]" class="form-control" value="{{ $item->box_no }}" placeholder="Box No">
                        </div>
                        <div style="text-align: center;">
                            <button type="button" class="btn btn-outline btn-icon btn-remove-row" title="Remove from slip and return to available inventory stock" style="color: #DC2626; border-color: #FECACA;">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    @empty
                    <div style="padding: 24px; text-align: center; color: var(--dftm-slate);">No units currently in this slip. Select units from the available inventory below.</div>
                    @endforelse
                </div>
            </div>

            <!-- SECTION 2: Select & Add Available Units from Inventory -->
            <div style="background: #F8FAFC; border: 1px solid var(--dftm-border); border-radius: var(--radius-md); padding: 18px; margin-bottom: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                    <div>
                        <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--dftm-navy); margin-bottom: 2px;">
                            <i class="bi bi-plus-circle-fill" style="color: var(--dftm-accent);"></i> Add More Units from Available Inventory ({{ $availableItems->count() }} Available)
                        </h3>
                        <div style="font-size: 0.78rem; color: var(--dftm-slate);">
                            Pumili ng mga units mula sa warehouse stock para idagdag sa slip na ito (<strong>Awtomatikong mababawas sa stock ang mga mapipiling units pagka-save</strong>).
                        </div>
                    </div>
                </div>

                <!-- Batch Filter & Action Bar -->
                <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px;">
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
                                    <option value="{{ $b->id }}" data-count="{{ $batchItemsCount }}">
                                        {{ $b->batch_no }} &bull; {{ $b->brand }} {{ $b->model }} ({{ $batchItemsCount }} Units Available)
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

                <div class="table-responsive" style="max-height: 380px; overflow-y: auto; border: 1px solid var(--dftm-border); border-radius: var(--radius-md); background: #FFFFFF;">
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
                            @forelse($availableItems as $availItem)
                            <tr data-batch-id="{{ $availItem->batch_id }}">
                                <td style="text-align: center;">
                                    <input type="checkbox" name="new_selected_items[]" value="{{ $availItem->id }}" 
                                           class="item-select-checkbox" 
                                           data-batch-id="{{ $availItem->batch_id }}"
                                           data-brand="{{ $availItem->brand }}" 
                                           data-model="{{ $availItem->model }}" 
                                           data-company="{{ $availItem->company_name }}"
                                           style="accent-color: var(--dftm-navy); transform: scale(1.2); cursor: pointer;">
                                </td>
                                <td><span class="mono" style="font-weight: 700; color: var(--dftm-navy);">{{ $availItem->serial_number ?? '-' }}</span></td>
                                <td><span class="mono">{{ $availItem->mac_address ?? '-' }}</span></td>
                                <td><span class="badge badge-stock">{{ $availItem->batch->batch_no ?? 'BATCH' }}</span></td>
                                <td><strong>{{ $availItem->brand }}</strong> {{ $availItem->model }}</td>
                                <td>{{ $availItem->box_no ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--dftm-slate); padding: 32px;">No extra in-stock units available in inventory right now.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Notes -->
            <div class="form-group" style="margin-top: 20px;">
                <label class="form-label">Outgoing Slip Remarks / Notes</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Release remarks...">{{ old('notes', $slip->notes) }}</textarea>
            </div>
        </div>
    </div>
</form>
@endsection
