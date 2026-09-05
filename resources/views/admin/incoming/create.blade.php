@extends('layouts.app')

@section('title', 'New Incoming Repair Slip')
@section('page_title', 'Create Incoming Repair Slip')

@section('content')
<form action="{{ route('admin.incoming.store') }}" method="POST" id="incomingForm">
    @csrf

    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title"><i class="bi bi-file-earmark-plus-fill"></i> Incoming Repair Slip Header</div>
                <div class="card-subtitle">Enter incoming batch details and encode arriving serialized products</div>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <span class="badge badge-stock" id="totalQtyBadge" style="font-size: 0.9rem; padding: 6px 14px;">0 Units</span>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save Incoming Batch
                </button>
            </div>
        </div>

        <div class="card-body">
            <!-- Header row 1: Slip No, Batch No, Company Name, Date Delivered -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Slip Number (Auto-Generated)</label>
                    <input type="text" name="slip_no" class="form-control mono" value="{{ old('slip_no', $slipNo) }}" readonly style="background: #F8FAFC; color: var(--dftm-navy); font-weight: 700;">
                </div>
                <div class="form-group">
                    <label class="form-label">Batch Number</label>
                    <input type="text" name="batch_no" class="form-control" value="{{ old('batch_no', $nextBatchNumber) }}" placeholder="e.g. BATCH 1">
                </div>
                <div class="form-group">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" list="companySuggestions" class="form-control" value="{{ old('company_name') }}" placeholder="Enter Company / Client Name (e.g. Converge, PLDT, Globe)">
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
                    <input type="date" name="date_delivered" class="form-control" value="{{ old('date_delivered') }}">
                </div>
            </div>

            <!-- Header row 2: Brand, Model, Status (Optional), Item Description -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Brand</label>
                    <input type="text" name="brand" list="brandSuggestions" class="form-control" placeholder="Enter Brand (e.g. HUAWEI / ZTE / SKYWORTH)" value="{{ old('brand') }}">
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
                    <input type="text" name="model" class="form-control" placeholder="Enter Model (e.g. EG8145V5 / ZXHN F670L / GN5420)" value="{{ old('model') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Status (Optional)</label>
                    <input type="text" name="status" class="form-control" placeholder="e.g. In process / For repair" value="{{ old('status') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Item Description</label>
                    <input type="text" name="item_description" class="form-control" placeholder="Enter Item Description (e.g. GPON ONT WIFI ROUTER)" value="{{ old('item_description') }}">
                </div>
            </div>

            <!-- Subtable for Serialized Units -->
            <div class="subtable-container">
                <div class="subtable-header">
                    <div>
                        <div class="subtable-title"><i class="bi bi-list-ol"></i> Incoming Serialized Units Subtable (Serial Number & MAC Address)</div>
                        <div style="font-size: 0.78rem; color: var(--dftm-slate);">All encoded units will automatically be registered as <strong>In process</strong> in warehouse stock</div>
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
                <div style="display: grid; grid-template-columns: 40px 2fr 2fr 1.2fr 40px; gap: 10px; padding: 6px 12px; font-size: 0.72rem; font-weight: 700; color: var(--dftm-navy); text-transform: uppercase;">
                    <div style="text-align: center;">NO.</div>
                    <div>Serial Number</div>
                    <div>MAC Address</div>
                    <div>Box No</div>
                    <div></div>
                </div>

                <div id="subtableRows">
                    @for($i = 1; $i <= 5; $i++)
                    <div class="subtable-row" style="grid-template-columns: 40px 2fr 2fr 1.2fr 40px;">
                        <div class="subtable-row-num">{{ $i }}</div>
                        <div>
                            <input type="text" name="serial_number[]" class="form-control mono" placeholder="Serial Number (e.g. 48575443F8A...)">
                        </div>
                        <div>
                            <input type="text" name="mac_address[]" class="form-control mono" placeholder="MAC Address (e.g. 00:1A:2B:...)">
                        </div>
                        <div>
                            <input type="text" name="box_no[]" class="form-control" placeholder="Box No (e.g. BOX 1)">
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline btn-icon btn-remove-row" title="Remove Row" style="color: #DC2626; border-color: #FECACA;">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                    @endfor
                </div>
            </div>

            <!-- Notes -->
            <div class="form-group" style="margin-top: 18px;">
                <label class="form-label">Batch Notes / Remarks</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes about batch packaging, courier, etc.">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('admin.incoming.index') }}" class="btn btn-outline">
                <i class="bi bi-arrow-left"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2-circle"></i> Save and Submit Incoming Slip
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
            <textarea id="bulkPasteText" class="form-control mono" rows="10" placeholder="48575443F8A101	00:1A:2B:3C:4D:01	BOX-1
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
