@extends('layouts.app')

@section('title', 'Outgoing Reports & Slips')
@section('page_title', 'Outgoing Reports & Slips')

@section('content')
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">
        <div>
            <div class="card-title"><i class="bi bi-box-arrow-right"></i> Outgoing Repair & Inspection Slips</div>
            <div class="card-subtitle">View and print official outgoing slips generated from completed traceability batches.</div>
        </div>
        @if($selectedBatch)
            <div style="display: flex; align-items: center; gap: 10px;">
                <a href="{{ route('client.outgoing.print', ['batch_id' => $selectedBatch->id]) }}" target="_blank" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="bi bi-printer-fill"></i> Print / Download Outgoing Slip
                </a>
            </div>
        @endif
    </div>

    <!-- Batch Selection Bar -->
    <div style="background: #F8FAFC; border-bottom: 1px solid var(--dftm-border); padding: 16px 24px;">
        <form action="{{ route('client.outgoing.index') }}" method="GET" style="display: flex; flex-wrap: wrap; align-items: center; gap: 14px;">
            <label style="font-weight: 800; font-size: 0.95rem; color: var(--dftm-navy); white-space: nowrap;">
                <i class="bi bi-diagram-3-fill" style="color: var(--dftm-accent);"></i> Select Traceability Batch:
            </label>
            <select name="batch_id" class="form-select" style="max-width: 440px; font-weight: 700; border-color: var(--dftm-accent);" onchange="this.form.submit();">
                @forelse($batches as $b)
                    <option value="{{ $b->id }}" {{ $selectedBatch && $selectedBatch->id == $b->id ? 'selected' : '' }}>
                        {{ $b->batch_no }} &bull; {{ $b->brand }} {{ $b->model ? '(' . $b->model . ')' : '' }} ({{ $b->items->count() }} Units)
                    </option>
                @empty
                    <option value="">-- No Batches Available for Outgoing --</option>
                @endforelse
            </select>
            <noscript><button type="submit" class="btn btn-primary">Load Batch</button></noscript>
        </form>
    </div>

    @if($selectedBatch)
        <!-- Material Description Header -->
        <div style="background: #EEF2FF; border-bottom: 1px solid #C7D2FE; padding: 18px 24px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; font-size: 0.85rem;">
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
                    <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--dftm-slate);">COMPANY NAME:</span>
                    <div style="font-weight: 700;">{{ $selectedBatch->company_name ?? $companyName }}</div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--dftm-slate);">TOTAL UNITS IN SLIP:</span>
                    <div><span class="badge badge-stock" style="font-size: 0.95rem; padding: 6px 14px;">{{ $items->count() }} Units</span></div>
                </div>
            </div>
        </div>

        <!-- Read-Only Units Table matching official layout -->
        <div class="table-responsive">
            <table class="dftm-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">NO.</th>
                        <th>BRAND</th>
                        <th>MODEL</th>
                        <th>SERIAL NUMBER</th>
                        <th>MAC ADDRESS</th>
                        <th style="width: 110px; text-align: center;">BOX #</th>
                        <th style="width: 120px; text-align: center;">STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td style="text-align: center; font-weight: 800; color: var(--dftm-navy);">{{ $loop->iteration }}</td>
                        <td><strong>{{ $item->brand ?: ($selectedBatch->brand ?? '-') }}</strong></td>
                        <td>{{ $item->model ?: ($selectedBatch->model ?? '-') }}</td>
                        <td><span class="mono" style="font-weight: 800; color: var(--dftm-navy); font-size: 0.92rem;">{{ $item->serial_number ?? '-' }}</span></td>
                        <td><span class="mono">{{ $item->mac_address ?? '-' }}</span></td>
                        <td style="text-align: center;">
                            @if($item->box_no)
                                <span class="badge" style="background: #E2E8F0; color: #334155; font-weight: 700;">{{ $item->box_no }}</span>
                            @else
                                <span style="color: #94A3B8;">-</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if($item->repair_status === 'Repaired')
                                <span class="badge badge-repaired">Repaired</span>
                            @elseif($item->repair_status === 'BER')
                                <span class="badge badge-ber">BER</span>
                            @else
                                <span class="badge badge-stock">In process</span>
                            @endif
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

        <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="font-size: 0.85rem; color: var(--dftm-slate);">
                <i class="bi bi-shield-check" style="color: #10B981; font-weight: 800;"></i> Verified 1:1 Serial Number and MAC Address match with DFTM Diagnostic Matrix.
            </div>
            <a href="{{ route('client.outgoing.print', ['batch_id' => $selectedBatch->id]) }}" target="_blank" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                <i class="bi bi-printer"></i> Print / Download Slip
            </a>
        </div>
    @else
        <div style="text-align: center; padding: 60px 20px; color: var(--dftm-slate);">
            <i class="bi bi-box-arrow-right" style="font-size: 3rem; color: #CBD5E1;"></i>
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--dftm-navy); margin-top: 12px;">No Outgoing Reports Available</h3>
            <p>Once DFTM technicians complete diagnostics and form outgoing repair slips for {{ $companyName }}, you will be able to review and print them here.</p>
        </div>
    @endif
</div>

<!-- Past Releases Section if Any -->
@if($recentOutgoing->isNotEmpty())
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title"><i class="bi bi-receipt"></i> Recorded Delivery & Release History</div>
                <div class="card-subtitle">Official outgoing releases logged with Sales Invoices or Delivery Receipts.</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="dftm-table">
                <thead>
                    <tr>
                        <th>DATE RELEASED</th>
                        <th>BATCH NO.</th>
                        <th>SI NUMBER</th>
                        <th>DR NUMBER</th>
                        <th style="text-align: center;">QUANTITY</th>
                        <th style="text-align: center;">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentOutgoing as $slip)
                        <tr>
                            <td>{{ $slip->date_released ? $slip->date_released->format('M d, Y') : '-' }}</td>
                            <td><strong style="color: var(--dftm-navy);">{{ $slip->batch_no ?? '-' }}</strong></td>
                            <td><span class="mono">{{ $slip->si_number ?? '-' }}</span></td>
                            <td><span class="mono">{{ $slip->dr_number ?? '-' }}</span></td>
                            <td style="text-align: center;">
                                <span class="badge badge-stock">{{ $slip->total_quantity ?? $slip->items->count() }} Units</span>
                            </td>
                            <td style="text-align: center;">
                                @php
                                    $slipBatchId = $slip->batch_id ?? ($batches->firstWhere('batch_no', $slip->batch_no)?->id ?? $slip->items->first()?->batch_id);
                                @endphp
                                @if($slipBatchId)
                                    <a href="{{ route('client.outgoing.print', ['batch_id' => $slipBatchId, 'si_number' => $slip->si_number, 'dr_number' => $slip->dr_number, 'date_released' => $slip->date_released?->format('Y-m-d')]) }}" target="_blank" class="btn btn-outline btn-sm">
                                        <i class="bi bi-printer"></i> Print Slip
                                    </a>
                                @else
                                    <span style="color: #94A3B8; font-size: 0.8rem;">Recorded</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
