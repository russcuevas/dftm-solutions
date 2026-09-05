@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page_title', 'Admin Overview & Stock Metrics')

@section('content')
<!-- Metric Stat Cards Grid -->
<div class="stat-grid">
    <!-- In-Stock Inventory -->
    <div class="stat-card" style="--stat-color: #00205B; --stat-bg: #E0E7FF;">
        <div class="stat-header">
            <span class="stat-label">Units in Stock</span>
            <div class="stat-icon"><i class="bi bi-box-seam-fill"></i></div>
        </div>
        <div class="stat-value">{{ number_format($inStockUnits) }}</div>
        <div class="stat-desc">Available for repair / release</div>
    </div>

    <!-- In Process Units -->
    <div class="stat-card" style="--stat-color: #D97706; --stat-bg: #FEF3C7;">
        <div class="stat-header">
            <span class="stat-label">In Process</span>
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
        </div>
        <div class="stat-value">{{ number_format($inProcessCount) }}</div>
        <div class="stat-desc">Arrived / currently undergoing repair</div>
    </div>

    <!-- Repaired Units -->
    <div class="stat-card" style="--stat-color: #059669; --stat-bg: #D1FAE5;">
        <div class="stat-header">
            <span class="stat-label">Repaired</span>
            <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
        </div>
        <div class="stat-value">{{ number_format($repairedCount) }}</div>
        <div class="stat-desc">Diagnostics passed & parts replaced</div>
    </div>

    <!-- BER (Beyond Economic Repair) -->
    <div class="stat-card" style="--stat-color: #DC2626; --stat-bg: #FEE2E2;">
        <div class="stat-header">
            <span class="stat-label">BER Units</span>
            <div class="stat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
        </div>
        <div class="stat-value">{{ number_format($berCount) }}</div>
        <div class="stat-desc">Beyond Economic Repair</div>
    </div>

    <!-- Released / Outgoing -->
    <div class="stat-card" style="--stat-color: #7C3AED; --stat-bg: #EDE9FE;">
        <div class="stat-header">
            <span class="stat-label">Released / Outgoing</span>
            <div class="stat-icon"><i class="bi bi-box-arrow-up-right"></i></div>
        </div>
        <div class="stat-value">{{ number_format($releasedUnits) }}</div>
        <div class="stat-desc">Delivered to customers / clients</div>
    </div>
</div>

<!-- Stock Alerts Notification if low stock batches exist -->
@if($lowStockBatches->count() > 0)
<div class="card" style="border-left: 4px solid #F59E0B; margin-bottom: 24px;">
    <div class="card-header" style="background: #FFFBEB;">
        <div class="card-title" style="color: #B45309;">
            <i class="bi bi-bell-fill"></i> Inventory Stock Alerts
        </div>
        <span class="badge badge-in-process">{{ $lowStockBatches->count() }} Batches Low Stock</span>
    </div>
    <div class="card-body" style="padding: 16px 24px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px;">
            @foreach($lowStockBatches as $b)
                <div style="background: #FFFFFF; border: 1px solid #FDE68A; padding: 12px; border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="color: var(--dftm-navy);">{{ $b->batch_no }}</strong> - {{ $b->brand }} {{ $b->model }}
                        <div style="font-size: 0.75rem; color: var(--dftm-slate);">{{ $b->company_name }}</div>
                    </div>
                    <div style="text-align: right;">
                        <span class="badge badge-ber" style="font-size: 0.8rem;">{{ $b->in_stock_quantity }} Remaining</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Two Column Main Grid -->
<div class="dashboard-main-grid">
    <!-- Left Column: Recent Batches & Outgoing Slips -->
    <div>
        <!-- Recent Incoming Batches -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="bi bi-box-arrow-in-down"></i> Recent Incoming Batches</div>
                    <div class="card-subtitle">Latest batch repair deliveries received</div>
                </div>
                <a href="{{ route('admin.incoming.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> New Incoming Slip
                </a>
            </div>
            <div class="table-responsive">
                <table class="dftm-table">
                    <thead>
                        <tr>
                            <th>Slip #</th>
                            <th>Batch No</th>
                            <th>Brand / Model</th>
                            <th>Total Qty</th>
                            <th>In-Stock</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBatches as $batch)
                        <tr>
                            <td><span class="mono">{{ $batch->slip_no }}</span></td>
                            <td><strong>{{ $batch->batch_no }}</strong></td>
                            <td>{{ $batch->brand }} {{ $batch->model }}</td>
                            <td><strong>{{ $batch->total_quantity }}</strong> pcs</td>
                            <td><span class="badge badge-stock">{{ $batch->in_stock_quantity }} available</span></td>
                            <td>
                                <span class="badge {{ $batch->status === 'COMPLETED' ? 'badge-repaired' : ($batch->status === 'PARTIAL' ? 'badge-in-process' : 'badge-stock') }}">
                                    {{ $batch->status }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.incoming.show', $batch->id) }}" class="btn btn-outline btn-sm">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--dftm-slate); padding: 36px 16px;">
                                <div style="font-size: 2rem; color: #CBD5E1; margin-bottom: 8px;"><i class="bi bi-inboxes"></i></div>
                                <div style="font-weight: 600; font-size: 0.92rem; color: var(--dftm-navy);">No incoming batches recorded yet</div>
                                <div style="font-size: 0.8rem; color: var(--dftm-slate-light); margin-top: 4px;">Incoming repair batches will be listed here once encoded.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <span style="font-size: 0.8rem; color: var(--dftm-slate);">Showing latest 5 batches</span>
                <a href="{{ route('admin.incoming.index') }}" style="font-size: 0.85rem; font-weight: 600; color: var(--dftm-accent); text-decoration: none;">
                    View All Batches <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Recent Outgoing Slips -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="bi bi-box-arrow-up-right"></i> Recent Outgoing Repair Releases</div>
                    <div class="card-subtitle">Latest items delivered/released to customers</div>
                </div>
                <a href="{{ route('admin.outgoing.create') }}" class="btn btn-accent btn-sm">
                    <i class="bi bi-send-plus"></i> New Outgoing Slip
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
                            <td colspan="6" style="text-align: center; color: var(--dftm-slate); padding: 36px 16px;">
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
    <div>
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
