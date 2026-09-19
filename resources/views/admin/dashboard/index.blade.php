@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page_title', 'Admin Overview & Flow Metrics')

@section('content')
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
    <!-- 1. Incoming Transmittals -->
    <div class="stat-card" style="--stat-color: #0284C7; --stat-bg: #E0F2FE;">
        <div class="stat-header">
            <span class="stat-label">Transmittals</span>
            <div class="stat-icon"><i class="bi bi-box-arrow-in-down"></i></div>
        </div>
        <div class="stat-value">{{ number_format($totalTransmittals) }}</div>
        <div class="stat-desc">Incoming repair shipments</div>
    </div>

    <!-- 2. Traceability Batches -->
    <div class="stat-card" style="--stat-color: #00205B; --stat-bg: #E0E7FF;">
        <div class="stat-header">
            <span class="stat-label">Batches Formed</span>
            <div class="stat-icon"><i class="bi bi-diagram-3-fill"></i></div>
        </div>
        <div class="stat-value">{{ number_format($totalBatches) }}</div>
        <div class="stat-desc">Traceability batches</div>
    </div>

    <!-- 3. Total Inventory Units -->
    <div class="stat-card" style="--stat-color: #6366F1; --stat-bg: #EEF2FF;">
        <div class="stat-header">
            <span class="stat-label">Total Units</span>
            <div class="stat-icon"><i class="bi bi-cpu-fill"></i></div>
        </div>
        <div class="stat-value">{{ number_format($totalUnits) }}</div>
        <div class="stat-desc">Total units in system</div>
    </div>

    <!-- 4. In-Stock Inventory -->
    <div class="stat-card" style="--stat-color: #2563EB; --stat-bg: #DBEAFE;">
        <div class="stat-header">
            <span class="stat-label">Units in Stock</span>
            <div class="stat-icon"><i class="bi bi-box-seam-fill"></i></div>
        </div>
        <div class="stat-value">{{ number_format($inStockUnits) }}</div>
        <div class="stat-desc">Available warehouse units</div>
    </div>

    <!-- Row 2: Diagnostics & Release Status (4 Boxes) -->
    <!-- 5. In Process Units -->
    <div class="stat-card" style="--stat-color: #D97706; --stat-bg: #FEF3C7;">
        <div class="stat-header">
            <span class="stat-label">In Process</span>
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
        </div>
        <div class="stat-value">{{ number_format($inProcessCount) }}</div>
        <div class="stat-desc">Undergoing diagnostic / repair</div>
    </div>

    <!-- 6. Repaired Units -->
    <div class="stat-card" style="--stat-color: #059669; --stat-bg: #D1FAE5;">
        <div class="stat-header">
            <span class="stat-label">Repaired OK</span>
            <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
        </div>
        <div class="stat-value">{{ number_format($repairedCount) }}</div>
        <div class="stat-desc">Diagnostics passed & parts replaced</div>
    </div>

    <!-- 7. BER (Beyond Economic Repair) -->
    <div class="stat-card" style="--stat-color: #DC2626; --stat-bg: #FEE2E2;">
        <div class="stat-header">
            <span class="stat-label">BER Units</span>
            <div class="stat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
        </div>
        <div class="stat-value">{{ number_format($berCount) }}</div>
        <div class="stat-desc">Beyond Economic Repair</div>
    </div>

    <!-- 8. Released / Outgoing -->
    <div class="stat-card" style="--stat-color: #7C3AED; --stat-bg: #EDE9FE;">
        <div class="stat-header">
            <span class="stat-label">Released</span>
            <div class="stat-icon"><i class="bi bi-box-arrow-up-right"></i></div>
        </div>
        <div class="stat-value">{{ number_format($releasedUnits) }}</div>
        <div class="stat-desc">Dispatched to customers</div>
    </div>
</div>

<!-- Two Column Main Grid -->
<div class="dashboard-main-grid">
    <!-- Left Column: Recent Transmittals, Batches & Outgoing Slips -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Recent Incoming -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="bi bi-box-arrow-in-down"></i> Recent Incoming</div>
                    <div class="card-subtitle">Latest repair slips received and encoded</div>
                </div>
                <a href="{{ route('admin.incoming.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> +Create Incoming
                </a>
            </div>
            <div class="table-responsive">
                <table class="dftm-table">
                    <thead>
                        <tr>
                            <th>Transmittal #</th>
                            <th>Company</th>
                            <th>Brand / Model</th>
                            <th>Total Qty</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransmittals as $transmittal)
                        <tr>
                            <td><span class="mono" style="font-weight: 700; color: var(--dftm-navy);">{{ $transmittal->transmittal_no }}</span></td>
                            <td>{{ $transmittal->company_name ?? '-' }}</td>
                            <td>{{ $transmittal->brand ?? 'Mixed' }} {{ $transmittal->model ?? '' }}</td>
                            <td><strong>{{ $transmittal->total_quantity }}</strong> pcs</td>
                            <td>
                                <span class="badge {{ $transmittal->status === 'COMPLETED' ? 'badge-repaired' : 'badge-in-process' }}">
                                    {{ $transmittal->status }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.incoming.show', $transmittal->id) }}" class="btn btn-outline btn-sm">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--dftm-slate); padding: 32px 16px;">
                                <div style="font-size: 2rem; color: #CBD5E1; margin-bottom: 8px;"><i class="bi bi-inboxes"></i></div>
                                <div style="font-weight: 600; font-size: 0.92rem; color: var(--dftm-navy);">No incoming repair slips recorded yet</div>
                                <div style="font-size: 0.8rem; color: var(--dftm-slate-light); margin-top: 4px;">Incoming repair slips will be listed here once encoded.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <span style="font-size: 0.8rem; color: var(--dftm-slate);">Showing latest 5 transmittals</span>
                <a href="{{ route('admin.incoming.index') }}" style="font-size: 0.85rem; font-weight: 600; color: var(--dftm-accent); text-decoration: none;">
                    View All Transmittals <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Recent Traceability Batches -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="bi bi-diagram-3"></i> Traceability Batches</div>
                    <div class="card-subtitle">Units grouped into repair & diagnostics batches</div>
                </div>
                <a href="{{ route('admin.traceability.createBatch') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-folder-plus"></i> Form Batch
                </a>
            </div>
            <div class="table-responsive">
                <table class="dftm-table">
                    <thead>
                        <tr>
                            <th>Batch No</th>
                            <th>Brand / Model</th>
                            <th>Total Units</th>
                            <th>In-Stock</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBatches as $batch)
                        <tr>
                            <td><strong style="color: var(--dftm-navy);">{{ $batch->batch_no }}</strong></td>
                            <td>{{ $batch->brand }} {{ $batch->model }}</td>
                            <td><strong>{{ $batch->total_quantity }}</strong> pcs</td>
                            <td><span class="badge badge-stock">{{ $batch->in_stock_quantity }} pcs</span></td>
                            <td>
                                <span class="badge {{ $batch->status === 'COMPLETED' ? 'badge-repaired' : ($batch->status === 'PARTIAL' ? 'badge-in-process' : 'badge-stock') }}">
                                    {{ $batch->status }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.traceability.index', ['batch_id' => $batch->id]) }}" class="btn btn-outline btn-sm">
                                    <i class="bi bi-sliders"></i> Diagnostic Matrix
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--dftm-slate); padding: 32px 16px;">
                                <div style="font-size: 2rem; color: #CBD5E1; margin-bottom: 8px;"><i class="bi bi-boxes"></i></div>
                                <div style="font-weight: 600; font-size: 0.92rem; color: var(--dftm-navy);">No traceability batches created yet</div>
                                <div style="font-size: 0.8rem; color: var(--dftm-slate-light); margin-top: 4px;">Form batches from incoming units to track diagnostic & repair matrix.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <span style="font-size: 0.8rem; color: var(--dftm-slate);">Showing latest 5 batches</span>
                <a href="{{ route('admin.traceability.index') }}" style="font-size: 0.85rem; font-weight: 600; color: var(--dftm-accent); text-decoration: none;">
                    View Traceability <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Recent Outgoing Releases -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="bi bi-box-arrow-up-right"></i> Recent Outgoing Reports & Releases</div>
                    <div class="card-subtitle">Batch release documentation generated for dispatch</div>
                </div>
                <a href="{{ route('admin.outgoing.index') }}" class="btn btn-accent btn-sm">
                    <i class="bi bi-file-earmark-arrow-down"></i> Generate Outgoing Report
                </a>
            </div>
            <div class="table-responsive">
                <table class="dftm-table">
                    <thead>
                        <tr>
                            <th>Slip #</th>
                            <th>Customer / Company</th>
                            <th>SI # / DR #</th>
                            <th>Brand / Model</th>
                            <th>Qty</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOutgoing as $out)
                        <tr>
                            <td><span class="mono">{{ $out->slip_no }}</span></td>
                            <td>
                                <strong>{{ $out->customer_name ?? 'N/A' }}</strong>
                                <div style="font-size: 0.72rem; color: var(--dftm-slate);">{{ $out->company_name }}</div>
                            </td>
                            <td>
                                <div><small>SI:</small> <span class="mono">{{ $out->si_number ?? '-' }}</span></div>
                                <div><small>DR:</small> <span class="mono">{{ $out->dr_number ?? '-' }}</span></div>
                            </td>
                            <td>{{ $out->brand }} {{ $out->model }}</td>
                            <td><span class="badge badge-released">{{ $out->total_quantity }} pcs</span></td>
                            <td>
                                <a href="{{ route('admin.outgoing.show', $out->id) }}" class="btn btn-outline btn-sm">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--dftm-slate); padding: 32px 16px;">
                                <div style="font-size: 2rem; color: #CBD5E1; margin-bottom: 8px;"><i class="bi bi-send-x"></i></div>
                                <div style="font-weight: 600; font-size: 0.92rem; color: var(--dftm-navy);">No outgoing slips created yet</div>
                                <div style="font-size: 0.8rem; color: var(--dftm-slate-light); margin-top: 4px;">Released repair slips and items will be displayed here.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <span style="font-size: 0.8rem; color: var(--dftm-slate);">Showing latest 5 outgoing records</span>
                <a href="{{ route('admin.outgoing.index') }}" style="font-size: 0.85rem; font-weight: 600; color: var(--dftm-accent); text-decoration: none;">
                    View All Outgoing <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Right Column: Brand Distribution & Recent Activity Audit -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Inventory Brand Distribution -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-pie-chart-fill"></i> Brand Stock Breakdown</div>
            </div>
            <div class="card-body">
                @forelse($brandDistribution as $brand)
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                            <span>{{ $brand->brand_name }}</span>
                            <span style="color: var(--dftm-navy);">{{ $brand->count }} pcs</span>
                        </div>
                        <div style="height: 6px; background: #E2E8F0; border-radius: var(--radius-full); overflow: hidden;">
                            <div style="height: 100%; width: {{ $totalUnits > 0 ? ($brand->count / $totalUnits) * 100 : 0 }}%; background: var(--dftm-accent);"></div>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 24px 12px; color: var(--dftm-slate);">
                        <div style="font-size: 1.8rem; color: #CBD5E1; margin-bottom: 6px;"><i class="bi bi-pie-chart"></i></div>
                        <div style="font-weight: 600; font-size: 0.88rem; color: var(--dftm-navy);">No inventory data available</div>
                        <div style="font-size: 0.75rem; color: var(--dftm-slate-light); margin-top: 2px;">Brand distribution will show here automatically.</div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Activity Audit Log -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-clock-history"></i> System Activity Log</div>
            </div>
            <div class="card-body" style="padding: 16px;">
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    @forelse($recentLogs as $log)
                    <div style="display: flex; gap: 10px; font-size: 0.82rem; border-bottom: 1px solid var(--dftm-border-subtle); padding-bottom: 10px;">
                        <div style="color: var(--dftm-accent); font-size: 1rem;"><i class="bi bi-dot"></i></div>
                        <div>
                            <div><strong>{{ $log->user_name }}</strong>: {{ $log->description }}</div>
                            <div style="font-size: 0.72rem; color: var(--dftm-slate-light); margin-top: 2px;">
                                {{ $log->created_at->setTimezone('Asia/Manila')->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div style="text-align: center; padding: 24px 12px; color: var(--dftm-slate);">
                        <div style="font-size: 1.8rem; color: #CBD5E1; margin-bottom: 6px;"><i class="bi bi-clock-history"></i></div>
                        <div style="font-weight: 600; font-size: 0.88rem; color: var(--dftm-navy);">No activity recorded yet</div>
                        <div style="font-size: 0.75rem; color: var(--dftm-slate-light); margin-top: 2px;">User activities and transactions will log here.</div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
