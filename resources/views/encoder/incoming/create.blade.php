@extends('layouts.app')

@section('title', 'Encode Incoming Transmittal')
@section('page_title', 'Encode Incoming Transmittal')

@section('content')
<form action="{{ route('encoder.incoming.store') }}" method="POST" id="incomingForm">
    @csrf

    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title"><i class="bi bi-file-earmark-plus-fill"></i> Incoming Transmittal Header</div>
                <div class="card-subtitle">Enter transmittal details to start barcode scanning</div>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save & Open Scanner
                </button>
            </div>
        </div>

        <div class="card-body">
            <!-- Header row 1: Transmittal No, Company Name, Date Received -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 700; color: var(--dftm-navy);">Transmittal Number</label>
                    <input type="text" name="transmittal_no" class="form-control mono" value="{{ old('transmittal_no') }}" placeholder="Enter Transmittal Number" required autofocus>
                    <small style="color: var(--dftm-slate); font-size: 0.75rem;">Enter the company / client transmittal number</small>
                </div>
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <label class="form-label" style="font-weight: 700; color: var(--dftm-navy); margin-bottom: 0;">Company / Client Name *</label>
                        <a href="{{ route('encoder.clients.index') }}" target="_blank" style="font-size: 0.75rem; color: var(--dftm-accent); text-decoration: none; font-weight: 600;">
                            <i class="bi bi-building-add"></i> + Register Client
                        </a>
                    </div>
                    <select name="company_name" class="form-select" required style="font-weight: 700;">
                        <option value="">-- Select Registered Client / Company --</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->company_name }}" {{ old('company_name') == $c->company_name ? 'selected' : '' }}>
                                {{ $c->company_name }}
                            </option>
                        @endforeach
                    </select>
                    <small style="color: var(--dftm-slate); font-size: 0.75rem;">Client account must be registered first so units are securely mapped to client portal.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Date Received</label>
                    <input type="date" name="date_received" class="form-control" value="{{ old('date_received', date('Y-m-d')) }}">
                </div>
            </div>

            <!-- Header row 2: Default Brand, Default Model, Status (Optional) -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Default Brand (Optional)</label>
                    <input type="text" name="brand" list="brandSuggestions" class="form-control" placeholder="e.g. HUAWEI / ZTE / SKYWORTH" value="{{ old('brand') }}">
                    <datalist id="brandSuggestions">
                        <option value="HUAWEI">
                        <option value="ZTE">
                        <option value="SKYWORTH">
                        <option value="FIBERHOME">
                        <option value="NOKIA">
                    </datalist>
                </div>
                <div class="form-group">
                    <label class="form-label">Default Model (Optional)</label>
                    <input type="text" name="model" class="form-control" placeholder="e.g. EG8145V5 / ZXHN F670L" value="{{ old('model') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Initial Status (Optional)</label>
                    <input type="text" name="status" class="form-control" placeholder="e.g. In process / Received" value="{{ old('status', 'In process') }}">
                </div>
            </div>

            <!-- Notes -->
            <div class="form-group" style="margin-top: 18px;">
                <label class="form-label">Transmittal Notes / Remarks</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes...">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('encoder.incoming.index') }}" class="btn btn-outline">
                <i class="bi bi-arrow-left"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2-circle"></i> Save & Proceed to Barcode Scanning
            </button>
        </div>
    </div>
</form>
@endsection
