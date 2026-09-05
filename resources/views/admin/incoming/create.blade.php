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
                <div class="card-subtitle">Enter incoming batch details to create draft slip (Serialized units can be added in Edit mode)</div>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save Incoming Batch
                </button>
            </div>
        </div>

        <div class="card-body">
            <!-- Header row 1: Slip No, Batch No, Company Name, Date Received -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Slip Number</label>
                    <input type="text" name="slip_no" class="form-control mono" value="{{ old('slip_no') }}" placeholder="Enter Slip Number (e.g. IRS-20260905-001)" required>
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
                    <label class="form-label">Date Received</label>
                    <input type="date" name="date_delivered" class="form-control" value="{{ old('date_delivered', date('Y-m-d')) }}">
                </div>
            </div>

            <!-- Header row 2: Brand, Model, Status (Optional) -->
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
            </div>

            <!-- Notes -->
            <div class="form-group" style="margin-top: 18px;">
                <label class="form-label">Batch Notes / Remarks</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes about batch packaging, courier, etc.">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('admin.incoming.index') }}" class="btn btn-outline">
                <i class="bi bi-arrow-left"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2-circle"></i> Save & Create Incoming Slip
            </button>
        </div>
    </div>
</form>
@endsection
