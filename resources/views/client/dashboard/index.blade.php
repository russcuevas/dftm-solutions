@extends('layouts.app')

@section('title', 'Client Unit Tracker')
@section('page_title', 'Client Portal & Unit Status Tracker')

@section('content')
<!-- Company Header Banner -->
<div class="card" style="background: linear-gradient(135deg, #00205B 0%, #1E1B4B 100%); color: #FFFFFF; border: none; margin-bottom: 24px;">
    <div class="card-body" style="padding: 24px 28px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <span class="badge" style="background: rgba(168, 85, 247, 0.3); color: #E9D5FF; margin-bottom: 8px; font-weight: 700; letter-spacing: 0.5px;">
                    <i class="bi bi-shield-lock-fill"></i> SECURE CUSTOMER TRACKER
                </span>
                <h1 style="font-size: 1.5rem; font-weight: 900; margin: 0; color: #FFFFFF;">
                    {{ $companyName ?: 'Customer Portal' }}
                </h1>
                <p style="color: #94A3B8; font-size: 0.88rem; margin: 6px 0 0;">
                    Real-time repair progress, transmittal history, and release documentation strictly for your company.
                </p>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 0.75rem; color: #94A3B8;">LOGGED IN ACCOUNT</div>
                <strong style="font-size: 0.95rem; color: #FFFFFF;">{{ auth()->user()->name }} ({{ auth()->user()->username }})</strong>
            </div>
        </div>

        <!-- Serial / MAC Tracker Search Box -->
        <div style="margin-top: 20px; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 12px; padding: 14px 18px;">
            <form action="{{ route('client.dashboard') }}" method="GET" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                <div style="flex: 1; min-width: 260px;">
                    <div style="position: relative;">
                        <i class="bi bi-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 1rem;"></i>
                        <input type="text" name="track_serial" class="form-control mono" value="{{ request('track_serial') }}"
                            placeholder="Enter Serial Number or MAC Address to track unit status..."
                            style="padding-left: 40px; height: 44px; font-size: 0.95rem; background: #FFFFFF; color: #0F172A; border-radius: 8px;">
                    </div>
                </div>
                <button type="submit" class="btn btn-accent" style="height: 44px; padding: 0 24px; font-weight: 700;">
                    <i class="bi bi-crosshair"></i> Track Unit
                </button>
                @if(request()->filled('track_serial'))
                    <a href="{{ route('client.dashboard') }}" class="btn btn-outline" style="height: 44px; color: #FFFFFF; border-color: rgba(255,255,255,0.4); display: flex; align-items: center;">
                        Reset
                    </a>
                @endif
            </form>
        </div>
    </div>
</div>

<!-- Search Result Tracker Card if user searched -->
@if(request()->filled('track_serial'))
    <div class="card" style="margin-bottom: 24px; border-left: 4px solid var(--dftm-accent);">
        <div class="card-header" style="background: #F8FAFC;">
            <div class="card-title">
                <i class="bi bi-search"></i> Unit Tracking Search Results for "{{ request('track_serial') }}"
            </div>
            <span class="badge badge-stock">{{ $searchResult ? $searchResult->count() : 0 }} Units Found</span>
        </div>
        <div class="table-responsive">
            <table class="dftm-table">
                <thead>
                    <tr>
                        <th>Serial Number</th>
                        <th>MAC Address</th>
                        <th>Brand & Model</th>
                        <th>Transmittal No</th>
                        <th>Batch</th>
                        <th>Diagnostic</th>
                        <th>Repair Status</th>
                        <th>Stock Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($searchResult as $item)
                    <tr>
                        <td><span class="mono" style="font-weight: 800; color: var(--dftm-navy);">{{ $item->serial_number }}</span></td>
                        <td><span class="mono">{{ $item->mac_address ?: '-' }}</span></td>
                        <td><strong>{{ $item->brand }}</strong> {{ $item->model }}</td>
                        <td>
                            @if($item->transmittal)
                                <a href="{{ route('client.incoming.show', $item->transmittal_id) }}" style="font-weight: 600; color: var(--dftm-accent);">
                                    {{ $item->transmittal->transmittal_no }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($item->batch)
                                <a href="{{ route('client.traceability.index', ['batch_id' => $item->batch_id]) }}" style="font-weight: 700; color: var(--dftm-navy);">
                                    {{ $item->batch->batch_no }}
                                </a>
                            @else
                                <span class="badge" style="background: #F1F5F9; color: #64748B;">Unbatched</span>
                            @endif
                        </td>
                        <td>{{ $item->technical_diagnostic ?: 'Under Diagnostic' }}</td>
                        <td>
                            <span class="badge {{ $item->repair_status === 'Repaired' ? 'badge-repaired' : ($item->repair_status === 'BER' ? 'badge-ber' : 'badge-in-process') }}">
                                {{ $item->repair_status ?: 'In process' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $item->stock_status === 'IN_STOCK' ? 'badge-stock' : 'badge-released' }}">
                                {{ $item->stock_status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 28px; color: var(--dftm-slate);">
                            No unit found matching "<strong>{{ request('track_serial') }}</strong>" under {{ $companyName }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

<!-- Metric Stat Cards Grid (4 boxes per row) -->
<style>
.stat-grid-4 {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
@media (max-width: 1200px) {
    .stat-grid-4 {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 640px) {
    .stat-grid-4 {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="stat-grid-4">
    <!-- 1. Incoming Transmittals -->
    <div class="stat-card" style="--stat-color: #0284C7; --stat-bg: #E0F2FE;">
        <div class="stat-header">
            <span class="stat-label">My Transmittals</span>
            <div class="stat-icon"><i class="bi bi-box-arrow-in-down"></i></div>
        </div>
        <div class="stat-value">{{ number_format($totalTransmittals) }}</div>
        <div class="stat-desc">Shipments received by DFTM</div>
    </div>

    <!-- 2. Traceability Batches -->
    <div class="stat-card" style="--stat-color: #00205B; --stat-bg: #E0E7FF;">
        <div class="stat-header">
            <span class="stat-label">Batches Formed</span>
            <div class="stat-icon"><i class="bi bi-diagram-3-fill"></i></div>
        </div>
        <div class="stat-value">{{ number_format($totalBatches) }}</div>
        <div class="stat-desc">Batches in repair matrix</div>
    </div>

    <!-- 3. Total Units Tracked -->
    <div class="stat-card" style="--stat-color: #6366F1; --stat-bg: #EEF2FF;">
        <div class="stat-header">
            <span class="stat-label">Total Units</span>
            <div class="stat-icon"><i class="bi bi-cpu-fill"></i></div>
        </div>
        <div class="stat-value">{{ number_format($totalUnits) }}</div>
        <div class="stat-desc">Units under your account</div>
    </div>

    <!-- 4. Units In Process -->
    <div class="stat-card" style="--stat-color: #D97706; --stat-bg: #FEF3C7;">
        <div class="stat-header">
            <span class="stat-label">In Process</span>
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
        </div>
        <div class="stat-value">{{ number_format($inProcessCount) }}</div>
        <div class="stat-desc">Undergoing diagnostic & repair</div>
    </div>

    <!-- 5. Repaired Units -->
    <div class="stat-card" style="--stat-color: #059669; --stat-bg: #D1FAE5;">
        <div class="stat-header">
            <span class="stat-label">Repaired OK</span>
            <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
        </div>
        <div class="stat-value">{{ number_format($repairedCount) }}</div>
        <div class="stat-desc">Passed QC diagnostics</div>
    </div>

    <!-- 6. BER Units -->
    <div class="stat-card" style="--stat-color: #DC2626; --stat-bg: #FEE2E2;">
        <div class="stat-header">
            <span class="stat-label">BER Units</span>
            <div class="stat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
        </div>
        <div class="stat-value">{{ number_format($berCount) }}</div>
        <div class="stat-desc">Beyond Economic Repair</div>
    </div>

    <!-- 7. In Stock Inventory -->
    <div class="stat-card" style="--stat-color: #2563EB; --stat-bg: #DBEAFE;">
        <div class="stat-header">
            <span class="stat-label">In Warehouse</span>
            <div class="stat-icon"><i class="bi bi-box-seam-fill"></i></div>
        </div>
        <div class="stat-value">{{ number_format($inStockUnits) }}</div>
        <div class="stat-desc">Ready for release</div>
    </div>

    <!-- 8. Released / Outgoing -->
    <div class="stat-card" style="--stat-color: #7C3AED; --stat-bg: #EDE9FE;">
        <div class="stat-header">
            <span class="stat-label">Dispatched</span>
            <div class="stat-icon"><i class="bi bi-box-arrow-up-right"></i></div>
        </div>
        <div class="stat-value">{{ number_format($releasedUnits) }}</div>
        <div class="stat-desc">Released & delivered back</div>
    </div>
</div>

<!-- Two Columns for Recent Activities -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 24px;">
    <!-- Recent Incoming Transmittals -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title"><i class="bi bi-box-arrow-in-down"></i> Recent Incoming Transmittals</div>
                <div class="card-subtitle">Shipment lots delivered to DFTM</div>
            </div>
            <a href="{{ route('client.incoming.index') }}" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-responsive">
            <table class="dftm-table">
                <thead>
                    <tr>
                        <th>Transmittal #</th>
                        <th>Brand & Model</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransmittals as $t)
                    <tr>
                        <td><span class="mono" style="font-weight: 700; color: var(--dftm-navy);">{{ $t->transmittal_no }}</span></td>
                        <td>{{ $t->brand }} {{ $t->model }}</td>
                        <td><strong>{{ $t->total_quantity }}</strong> pcs</td>
                        <td>
                            <span class="badge {{ $t->status === 'COMPLETED' ? 'badge-repaired' : 'badge-in-process' }}">
                                {{ $t->status }}
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <a href="{{ route('client.incoming.show', $t->id) }}" class="btn btn-outline btn-sm">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <a href="{{ route('client.incoming.print', $t->id) }}" target="_blank" class="btn btn-outline btn-sm" title="Print Report">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--dftm-slate); padding: 32px 16px;">
                            No incoming transmittals recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Traceability Batches -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title"><i class="bi bi-diagram-3"></i> Traceability Batches</div>
                <div class="card-subtitle">Repair diagnostics & unit test matrix</div>
            </div>
            <a href="{{ route('client.traceability.index') }}" class="btn btn-outline btn-sm">View Matrix</a>
        </div>
        <div class="table-responsive">
            <table class="dftm-table">
                <thead>
                    <tr>
                        <th>Batch #</th>
                        <th>Brand & Model</th>
                        <th>Total Qty</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentBatches as $b)
                    <tr>
                        <td><strong style="color: var(--dftm-navy);">{{ $b->batch_no }}</strong></td>
                        <td>{{ $b->brand }} {{ $b->model }}</td>
                        <td>{{ $b->items->count() }} pcs</td>
                        <td>
                            <span class="badge {{ $b->status === 'COMPLETED' ? 'badge-repaired' : ($b->status === 'PARTIAL' ? 'badge-in-process' : 'badge-stock') }}">
                                {{ $b->status }}
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <a href="{{ route('client.traceability.index', ['batch_id' => $b->id]) }}" class="btn btn-outline btn-sm">
                                    <i class="bi bi-sliders"></i> Diagnostic
                                </a>
                                <a href="{{ route('client.traceability.print', ['batch_id' => $b->id]) }}" target="_blank" class="btn btn-outline btn-sm" title="Print Matrix">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--dftm-slate); padding: 32px 16px;">
                            No batches created yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
