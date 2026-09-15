@extends('layouts.app')

@section('title', 'Outgoing Reports Generator')
@section('page_title', 'Outgoing Report Generator')

@section('content')
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">
        <div>
            <div class="card-title"><i class="bi bi-printer-fill"></i> Outgoing Report Generator (Galing sa Traceability)</div>
            <div class="card-subtitle">Pumili ng Batch mula sa Traceability para agad ma-generate ang Outgoing Slip na handa nang i-print.</div>
        </div>
        @if($selectedBatch)
            <div style="display: flex; align-items: center; gap: 10px;">
                <a href="{{ route('encoder.outgoing.print', ['batch_id' => $selectedBatch->id]) }}" target="_blank" class="btn btn-primary">
                    <i class="bi bi-printer"></i> Print Outgoing Slip
                </a>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('releaseModal').style.display='flex';">
                    <i class="bi bi-send-check"></i> Record Release (SI/DR)
                </button>
            </div>
        @endif
    </div>

    <!-- Batch Selection Bar -->
    <div style="background: #F8FAFC; border-bottom: 1px solid var(--dftm-border); padding: 16px 24px;">
        <form action="{{ route('encoder.outgoing.index') }}" method="GET" style="display: flex; flex-wrap: wrap; align-items: center; gap: 14px;">
            <label style="font-weight: 800; font-size: 0.95rem; color: var(--dftm-navy); white-space: nowrap;">
                <i class="bi bi-cpu-fill" style="color: var(--dftm-accent);"></i> Pumili ng Batch:
            </label>
            <select name="batch_id" class="form-select" style="max-width: 440px; font-weight: 700; border-color: var(--dftm-accent);" onchange="this.form.submit();">
                @forelse($batches as $b)
                    <option value="{{ $b->id }}" {{ $selectedBatch && $selectedBatch->id == $b->id ? 'selected' : '' }}>
                        {{ $b->batch_no }} &bull; {{ $b->company_name ?? 'DFTM' }} &bull; {{ $b->brand }} {{ $b->model }} ({{ $b->total_quantity }} Units)
                    </option>
                @empty
                    <option value="">-- No Batches in Traceability Yet --</option>
                @endforelse
            </select>
            <noscript><button type="submit" class="btn btn-primary">Load Batch</button></noscript>
        </form>
    </div>

    @if($selectedBatch)
        <!-- Material Description Header -->
        <div style="background: #EEF2FF; border-bottom: 1px solid #C7D2FE; padding: 16px 24px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; font-size: 0.85rem;">
                <div>
                    <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--dftm-slate);">MATERIAL DESCRIPTION:</span>
                    <div style="font-weight: 800; color: var(--dftm-navy); font-size: 1.05rem;">
                        {{ strtoupper($selectedBatch->brand ?? '') }} {{ strtoupper($selectedBatch->model ?? '') }}
                    </div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--dftm-slate);">TRACEABILITY BATCH:</span>
                    <div style="font-weight: 800; color: var(--dftm-navy); font-size: 1.05rem;">{{ $selectedBatch->batch_no }}</div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--dftm-slate);">CLIENT / COMPANY:</span>
                    <div style="font-weight: 700;">{{ $selectedBatch->company_name ?? 'DFTM DIGITAL SOLUTIONS' }}</div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--dftm-slate);">TOTAL READY FOR OUTGOING:</span>
                    <div><span class="badge badge-stock" style="font-size: 0.95rem; padding: 6px 14px;">{{ $items->count() }} Units Matched</span></div>
                </div>
            </div>
        </div>

        <!-- Table Columns: Brand – Model - Serial number - Mac Address - Box # - Action -->
        <div class="table-responsive">
            <table class="dftm-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">NO.</th>
                        <th>BRAND</th>
                        <th>MODEL</th>
                        <th>SERIAL NUMBER</th>
                        <th>MAC ADDRESS</th>
                        <th style="width: 100px; text-align: center;">BOX #</th>
                        <th style="width: 100px; text-align: center;">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td style="text-align: center; font-weight: 800; color: var(--dftm-navy);">{{ $loop->iteration }}</td>
                        <td><strong>{{ $item->brand ?: ($selectedBatch->brand ?? '-') }}</strong></td>
                        <td>{{ $item->model ?: ($selectedBatch->model ?? '-') }}</td>
                        <td><span class="mono" style="font-weight: 800; color: var(--dftm-navy); font-size: 0.95rem;">{{ $item->serial_number ?? '-' }}</span></td>
                        <td><span class="mono">{{ $item->mac_address ?? '-' }}</span></td>
                        <td style="text-align: center;">{{ $item->box_no ?? '-' }}</td>
                        <td style="text-align: center;">
                            <a href="{{ route('encoder.traceability.index', ['batch_id' => $selectedBatch->id]) }}" class="btn btn-outline btn-sm" title="View in Traceability">
                                <i class="bi bi-search"></i> Trace
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--dftm-slate); padding: 36px;">
                            No units found in this batch.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 0.85rem; color: var(--dftm-slate);">
                <i class="bi bi-check-all" style="color: #10B981; font-weight: 800;"></i> 100% Tugma ang Serial Number at MAC Address sa Traceability.
            </div>
            <a href="{{ route('encoder.outgoing.print', ['batch_id' => $selectedBatch->id]) }}" target="_blank" class="btn btn-primary">
                <i class="bi bi-printer"></i> Ready for Printing
            </a>
        </div>
    @else
        <div style="text-align: center; padding: 60px 20px; color: var(--dftm-slate);">
            <i class="bi bi-printer" style="font-size: 3rem; color: #CBD5E1;"></i>
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--dftm-navy); margin-top: 12px;">No Batches Available</h3>
            <p>Lumikha muna ng Batch sa Traceability para ma-generate ang Outgoing Slip.</p>
            <a href="{{ route('encoder.traceability.createBatch') }}" class="btn btn-primary" style="margin-top: 8px;">
                <i class="bi bi-plus-circle"></i> Create Batch in Traceability
            </a>
        </div>
    @endif
</div>

<!-- Optional Release Confirmation Modal -->
@if($selectedBatch)
<div id="releaseModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #FFFFFF; width: 90%; max-width: 500px; border-radius: 12px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <form action="{{ route('encoder.outgoing.release') }}" method="POST">
            @csrf
            <input type="hidden" name="batch_id" value="{{ $selectedBatch->id }}">

            <div style="background: #00205B; color: #FFFFFF; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center;">
                <div style="font-weight: 700; font-size: 1rem;"><i class="bi bi-send-check"></i> Record Release Details</div>
                <button type="button" onclick="document.getElementById('releaseModal').style.display='none';" style="background: none; border: none; color: #FFF; font-size: 1.2rem; cursor: pointer;">&times;</button>
            </div>

            <div style="padding: 20px;">
                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label">Client / Customer Name</label>
                    <input type="text" name="customer_name" class="form-control" value="{{ $selectedBatch->company_name }}">
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label">SI Number (Sales Invoice)</label>
                    <input type="text" name="si_number" class="form-control mono" placeholder="e.g. SI-10293">
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label">DR Number (Delivery Receipt)</label>
                    <input type="text" name="dr_number" class="form-control mono" placeholder="e.g. DR-58392">
                </div>
                <div class="form-group">
                    <label class="form-label">Date Released</label>
                    <input type="date" name="date_released" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
            </div>

            <div style="background: #F8FAFC; border-top: 1px solid var(--dftm-border); padding: 12px 20px; display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('releaseModal').style.display='none';">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Save & Confirm Release</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
