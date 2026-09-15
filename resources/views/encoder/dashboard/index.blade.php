@extends('layouts.app')

@section('title', 'Encoder Dashboard')
@section('page_title', 'Data Encoder Workspace')

@section('content')
<!-- Metric Stat Cards Grid -->
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

<!-- Metric Stat Cards Grid (4 boxes per row) -->
<div class="stat-grid-4">
    <!-- Row 1: Flow & Inventory Volume (4 Boxes) -->
    <div class="stat-card" style="--stat-color: #0284C7; --stat-bg: #E0F2FE;">
        <div class="stat-header">
            <span class="stat-label">My Transmittals</span>
            <div class="stat-icon"><i class="bi bi-box-arrow-in-down"></i></div>
        </div>
        <div class="stat-value">{{ number_format($myEncodedTransmittals) }}</div>
        <div class="stat-desc">Transmittals encoded by you</div>
    </div>

    <div class="stat-card" style="--stat-color: #00205B; --stat-bg: #E0E7FF;">
        <div class="stat-header">
            <span class="stat-label">Batches Formed</span>
            <div class="stat-icon"><i class="bi bi-diagram-3-fill"></i></div>
        </div>
        <div class="stat-value">{{ number_format($totalBatches) }}</div>
        <div class="stat-desc">Traceability batches</div>
    </div>

    <div class="stat-card" style="--stat-color: #6366F1; --stat-bg: #EEF2FF;">
        <div class="stat-header">
            <span class="stat-label">Total Units</span>
            <div class="stat-icon"><i class="bi bi-cpu-fill"></i></div>
        </div>
        <div class="stat-value">{{ number_format($totalUnits) }}</div>
        <div class="stat-desc">Total units in system</div>
    </div>

    <div class="stat-card" style="--stat-color: #2563EB; --stat-bg: #DBEAFE;">
        <div class="stat-header">
            <span class="stat-label">In-Stock Units</span>
            <div class="stat-icon"><i class="bi bi-boxes"></i></div>
        </div>
        <div class="stat-value">{{ number_format($totalInStock) }}</div>
        <div class="stat-desc">Available warehouse units</div>
    </div>

    <!-- Row 2: Diagnostics & Release Status (4 Boxes) -->
    <div class="stat-card" style="--stat-color: #D97706; --stat-bg: #FEF3C7;">
        <div class="stat-header">
            <span class="stat-label">In Process</span>
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
        </div>
        <div class="stat-value">{{ number_format($totalInProcess) }}</div>
        <div class="stat-desc">Diagnostics & repair</div>
    </div>

    <div class="stat-card" style="--stat-color: #059669; --stat-bg: #D1FAE5;">
        <div class="stat-header">
            <span class="stat-label">Repaired OK</span>
            <div class="stat-icon"><i class="bi bi-check2-all"></i></div>
        </div>
        <div class="stat-value">{{ number_format($totalRepaired) }}</div>
        <div class="stat-desc">Repaired & tested OK</div>
    </div>

    <div class="stat-card" style="--stat-color: #DC2626; --stat-bg: #FEE2E2;">
        <div class="stat-header">
            <span class="stat-label">BER Units</span>
            <div class="stat-icon"><i class="bi bi-exclamation-octagon-fill"></i></div>
        </div>
        <div class="stat-value">{{ number_format($totalBer) }}</div>
        <div class="stat-desc">Beyond economic repair</div>
    </div>

    <div class="stat-card" style="--stat-color: #7C3AED; --stat-bg: #EDE9FE;">
        <div class="stat-header">
            <span class="stat-label">Released</span>
            <div class="stat-icon"><i class="bi bi-box-arrow-up-right"></i></div>
        </div>
        <div class="stat-value">{{ number_format($releasedUnits) }}</div>
        <div class="stat-desc">Dispatched to customers</div>
    </div>
</div>

<!-- Quick Action Encoding Banner -->
<div class="card" style="background: linear-gradient(135deg, #00205B 0%, #0A2540 100%); color: #FFFFFF; border: none; margin-bottom: 24px;">
    <div class="card-body" style="padding: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div>
            <h2 style="font-size: 1.3rem; font-weight: 800; margin-bottom: 6px; color: #FFFFFF;">Ready to process units in the new system flow?</h2>
            <p style="color: #94A3B8; font-size: 0.88rem; margin: 0;">Scan incoming shipments with Transmittal No, form Traceability Batches, and generate Outgoing Reports</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('encoder.incoming.create') }}" class="btn btn-accent" style="padding: 10px 18px;">
                <i class="bi bi-plus-circle"></i> 1. Encode Transmittal
            </a>
            <a href="{{ route('encoder.traceability.createBatch') }}" class="btn btn-outline" style="padding: 10px 18px; color: #FFFFFF; border-color: rgba(255, 255, 255, 0.4);">
                <i class="bi bi-diagram-3"></i> 2. Form Traceability Batch
            </a>
            <a href="{{ route('encoder.outgoing.index') }}" class="btn btn-outline" style="padding: 10px 18px; color: #FFFFFF; border-color: rgba(255, 255, 255, 0.4);">
                <i class="bi bi-file-earmark-text"></i> 3. Outgoing Reports
            </a>
        </div>
    </div>
</div>

<!-- Grid for Recent Activities -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 24px;">
    <!-- Recent Incoming Transmittals -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title"><i class="bi bi-box-arrow-in-down"></i> Recent Incoming Transmittals</div>
                <div class="card-subtitle">Transmittals registered in system</div>
            </div>
            <a href="{{ route('encoder.incoming.index') }}" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-responsive">
            <table class="dftm-table">
                <thead>
                    <tr>
                        <th>Transmittal #</th>
                        <th>Company</th>
                        <th>Brand & Model</th>
                        <th>Total Qty</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransmittals as $t)
                    <tr>
                        <td><span class="mono" style="font-weight: 700; color: var(--dftm-navy);">{{ $t->transmittal_no }}</span></td>
                        <td>{{ $t->company_name ?? '-' }}</td>
                        <td>{{ $t->brand }} {{ $t->model }}</td>
                        <td><strong>{{ $t->total_quantity }}</strong> pcs</td>
                        <td>
                            <a href="{{ route('encoder.incoming.show', $t->id) }}" class="btn btn-outline btn-sm">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--dftm-slate); padding: 32px 16px;">
                            <div style="font-size: 1.8rem; color: #CBD5E1; margin-bottom: 6px;"><i class="bi bi-inboxes"></i></div>
                            <div style="font-weight: 600; font-size: 0.9rem; color: var(--dftm-navy);">No transmittals encoded yet</div>
                            <div style="font-size: 0.75rem; color: var(--dftm-slate-light); margin-top: 2px;">Your encoded transmittals will appear here.</div>
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
                <div class="card-subtitle">Batches with diagnostics & repair status</div>
            </div>
            <a href="{{ route('encoder.traceability.index') }}" class="btn btn-outline btn-sm">View Matrix</a>
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
                        <td>{{ $b->total_quantity }} pcs</td>
                        <td>
                            <span class="badge {{ $b->status === 'COMPLETED' ? 'badge-repaired' : ($b->status === 'PARTIAL' ? 'badge-in-process' : 'badge-stock') }}">
                                {{ $b->status }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('encoder.traceability.index', ['batch_id' => $b->id]) }}" class="btn btn-outline btn-sm">
                                <i class="bi bi-sliders"></i> Diagnostic
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--dftm-slate); padding: 32px 16px;">
                            <div style="font-size: 1.8rem; color: #CBD5E1; margin-bottom: 6px;"><i class="bi bi-boxes"></i></div>
                            <div style="font-weight: 600; font-size: 0.9rem; color: var(--dftm-navy);">No batches created yet</div>
                            <div style="font-size: 0.75rem; color: var(--dftm-slate-light); margin-top: 2px;">Traceability batches will show here.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
