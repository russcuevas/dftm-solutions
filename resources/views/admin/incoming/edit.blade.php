@extends('layouts.app')

@section('title', 'Edit Incoming Batch - ' . $batch->batch_no)
@section('page_title', 'Edit Incoming Repair Slip')

@section('content')
    <form action="{{ route('admin.incoming.update', $batch->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="bi bi-pencil-square"></i> Edit Batch: {{ $batch->slip_no }}</div>
                    <div class="card-subtitle">Modify batch header details or serialized units</div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle"></i> Save All Changes
                    </button>
                </div>
            </div>

            <div class="card-body">
                <!-- Header row 1 -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Slip Number</label>
                        <input type="text" class="form-control mono" value="{{ $batch->slip_no }}" disabled
                            style="background: #F1F5F9;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Batch Number</label>
                        <input type="text" name="batch_no" class="form-control"
                            value="{{ old('batch_no', $batch->batch_no) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" list="companySuggestions" class="form-control"
                            value="{{ old('company_name', $batch->company_name) }}"
                            placeholder="Enter Company / Client Name">
                        <datalist id="companySuggestions">
                            <option value="DFTM DIGITAL SOLUTIONS">
                            <option value="CONVERGE ICT">
                            <option value="PLDT / SMART">
                            <option value="GLOBE TELECOM">
                            <option value="DITO TELECOMMUNITY">
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date Delivered</label>
                        <input type="date" name="date_delivered" class="form-control"
                            value="{{ old('date_delivered', $batch->date_delivered ? $batch->date_delivered->format('Y-m-d') : '') }}">
                    </div>
                </div>

                <!-- Header row 2: Brand, Model, Status (Optional), Item Description -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Brand</label>
                        <input type="text" name="brand" list="brandSuggestions" class="form-control"
                            value="{{ old('brand', $batch->brand) }}" placeholder="Enter Brand">
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
                        <input type="text" name="model" class="form-control" value="{{ old('model', $batch->model) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status (Optional)</label>
                        <input type="text" name="status" class="form-control"
                            value="{{ old('status', $batch->status) }}" placeholder="e.g. In process / For repair">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Item Description</label>
                        <input type="text" name="item_description" class="form-control"
                            value="{{ old('item_description', $batch->item_description) }}">
                    </div>
                </div>

                <!-- Subtable -->
                <div class="subtable-container">
                    <div class="subtable-header">
                        <div>
                            <div class="subtable-title"><i class="bi bi-list-ol"></i> Serialized Products Subtable</div>
                            <div style="font-size: 0.78rem; color: var(--dftm-slate);">Update serial numbers, MAC addresses,
                                and box numbers, or add new units to this batch</div>
                        </div>
                        <div class="subtable-actions">
                            <button type="button" class="btn btn-outline btn-sm" id="openBulkPasteBtn">
                                <i class="bi bi-clipboard-plus"></i> Bulk Paste Serials & MACs
                            </button>
                            <button type="button" class="btn btn-accent btn-sm" id="addRowBtn">
                                <i class="bi bi-plus-lg"></i> Add Item Row
                            </button>
                        </div>
                    </div>

                    <!-- Column Headers -->
                    <div
                        style="display: grid; grid-template-columns: 40px 2fr 2fr 1.2fr 40px; gap: 10px; padding: 6px 12px; font-size: 0.72rem; font-weight: 700; color: var(--dftm-navy); text-transform: uppercase;">
                        <div style="text-align: center;">NO.</div>
                        <div>Serial Number</div>
                        <div>MAC Address</div>
                        <div>Box No</div>
                        <div></div>
                    </div>

                    <div id="subtableRows">
                        @forelse($batch->items as $item)
                            <div class="subtable-row" style="grid-template-columns: 40px 2fr 2fr 1.2fr 40px;">
                                <input type="hidden" name="item_id[]" value="{{ $item->id }}">
                                <div class="subtable-row-num">{{ $loop->iteration }}</div>
                                <div>
                                    <input type="text" name="serial_number[]" class="form-control mono"
                                        value="{{ $item->serial_number }}" placeholder="Serial Number">
                                </div>
                                <div>
                                    <input type="text" name="mac_address[]" class="form-control mono"
                                        value="{{ $item->mac_address }}" placeholder="MAC Address">
                                </div>
                                <div>
                                    <input type="text" name="box_no[]" class="form-control"
                                        value="{{ $item->box_no }}" placeholder="Box No">
                                </div>
                                <div>
                                    <button type="button" class="btn btn-outline btn-icon btn-remove-row"
                                        title="Remove Row" style="color: #DC2626; border-color: #FECACA;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="subtable-row" style="grid-template-columns: 40px 2fr 2fr 1.2fr 40px;">
                                <div class="subtable-row-num">1</div>
                                <div><input type="text" name="serial_number[]" class="form-control mono"
                                        placeholder="Serial Number"></div>
                                <div><input type="text" name="mac_address[]" class="form-control mono"
                                        placeholder="MAC Address"></div>
                                <div><input type="text" name="box_no[]" class="form-control" placeholder="Box No">
                                </div>
                                <div>
                                    <button type="button" class="btn btn-outline btn-icon btn-remove-row"
                                        style="color: #DC2626;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Notes -->
                <div class="form-group" style="margin-top: 18px;">
                    <label class="form-label">Batch Notes / Remarks</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $batch->notes) }}</textarea>
                </div>
            </div>

            <div class="card-footer">
                <a href="{{ route('admin.incoming.index') }}" class="btn btn-outline">
                    <i class="bi bi-arrow-left"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2-circle"></i> Save Batch Changes
                </button>
            </div>
        </div>
    </form>

    <!-- Bulk Paste Modal -->
    <div class="modal-backdrop" id="bulkPasteModal">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title"><i class="bi bi-clipboard-data"></i> Bulk Paste Serials & MAC Addresses</div>
                <button type="button" class="modal-close" data-modal-close><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body">
                <p style="font-size: 0.85rem; color: var(--dftm-slate); margin-bottom: 12px;">
                    Paste multiple lines from Excel or text. Format:
                    <br><code>SERIAL_NUMBER [TAB or COMMA] MAC_ADDRESS [BOX_NO]</code>
                </p>
                <textarea id="bulkPasteText" class="form-control mono" rows="10"
                    placeholder="48575443F8A101	00:1A:2B:3C:4D:01	BOX-1
48575443F8A102	00:1A:2B:3C:4D:02	BOX-1
48575443F8A103	00:1A:2B:3C:4D:03	BOX-2"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
                <button type="button" class="btn btn-accent" id="applyBulkPasteBtn">
                    <i class="bi bi-arrow-down-circle"></i> Insert Items into Form
                </button>
            </div>
        </div>
    </div>
@endsection
