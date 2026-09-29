@extends('layouts.app')

@section('title', $companyName . ' - Company Dashboard')
@section('page_title', 'Company Dashboard: ' . $companyName)

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

.company-hero-banner {
    background: linear-gradient(135deg, #00205B 0%, #1E3A8A 100%);
    color: #FFFFFF;
    border-radius: 12px;
    padding: 20px 24px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    box-shadow: 0 10px 15px -3px rgba(0, 32, 91, 0.15);
}
</style>

<!-- Top Company Context Header Banner -->
<div class="company-hero-banner">
    <div style="display: flex; align-items: center; gap: 16px;">
        <div style="width: 52px; height: 52px; border-radius: 12px; background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; border: 1px solid rgba(255, 255, 255, 0.25);">
            <i class="bi bi-building"></i>
        </div>
        <div>
            <div style="font-size: 0.76rem; text-transform: uppercase; letter-spacing: 1px; color: #93C5FD; font-weight: 700;">
                <i class="bi bi-folder-symlink-fill"></i> Client Dedicated Dashboard
            </div>
            <h2 style="font-size: 1.45rem; font-weight: 800; margin: 2px 0 0 0; color: #FFFFFF;">
                {{ $companyName }}
            </h2>
            @if($clientUser)
                <div style="font-size: 0.8rem; color: #E2E8F0; margin-top: 4px;">
                    Portal User: <strong>{{ $clientUser->name }}</strong> ({{ $clientUser->username }})
                    @if($clientUser->phone) • <i class="bi bi-telephone"></i> {{ $clientUser->phone }} @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions & Company Switcher -->
    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
        <!-- Switch Company Dropdown -->
        <select class="form-select form-select-sm" style="width: 220px; font-weight: 700; background: #FFFFFF; color: #00205B;"
            onchange="if(this.value) window.location.href=this.value;">
            <option value="">-- Switch Company --</option>
            @foreach($allCompanies as $comp)
                <option value="{{ route('admin.companies.dashboard', urlencode($comp)) }}" {{ strtolower(trim($comp)) === strtolower(trim($companyName)) ? 'selected' : '' }}>
                    {{ $comp }}
                </option>
            @endforeach
        </select>

        <a href="{{ route('admin.companies.index') }}" class="btn btn-outline btn-sm" style="background: rgba(255,255,255,0.1); color: #FFFFFF; border-color: rgba(255,255,255,0.3);">
            <i class="bi bi-arrow-left"></i> All Folders
        </a>
        <a href="{{ route('admin.incoming.create') }}" class="btn btn-primary btn-sm" style="background: var(--dftm-accent); border: none;">
            <i class="bi bi-plus-lg"></i> + New Transmittal
        </a>
    </div>
</div>

<!-- 8 Metric Stat Cards Scoped to This Company -->
<div class="stat-grid-4">
    <!-- 1. Incoming Transmittals -->
    <div class="stat-card" style="--stat-color: #0284C7; --stat-bg: #E0F2FE;">
        <div class="stat-header">
            <span class="stat-label">Transmittals</span>
            <div class="stat-icon"><i class="bi bi-box-arrow-in-down"></i></div>
        </div>
        <div class="stat-value">{{ number_format($totalTransmittals) }}</div>
        <div class="stat-desc">Shipments received from {{ $companyName }}</div>
    </div>

    <!-- 2. Traceability Batches -->
    <div class="stat-card" style="--stat-color: #00205B; --stat-bg: #E0E7FF;">
        <div class="stat-header">
            <span class="stat-label">Batches Formed</span>
            <div class="stat-icon"><i class="bi bi-diagram-3-fill"></i></div>
        </div>
        <div class="stat-value">{{ number_format($totalBatches) }}</div>
        <div class="stat-desc">Diagnostics batches for this client</div>
    </div>

    <!-- 3. Total Inventory Units -->
    <div class="stat-card" style="--stat-color: #6366F1; --stat-bg: #EEF2FF;">
        <div class="stat-header">
            <span class="stat-label">Total Units</span>
            <div class="stat-icon"><i class="bi bi-cpu-fill"></i></div>
        </div>
        <div class="stat-value">{{ number_format($totalUnits) }}</div>
        <div class="stat-desc">All recorded units for this company</div>
    </div>

    <!-- 4. Units in Stock -->
    <div class="stat-card" style="--stat-color: #2563EB; --stat-bg: #DBEAFE;">
        <div class="stat-header">
            <span class="stat-label">Units in Stock</span>
            <div class="stat-icon"><i class="bi bi-box-seam-fill"></i></div>
        </div>
        <div class="stat-value">{{ number_format($inStockUnits) }}</div>
        <div class="stat-desc">Currently in warehouse / repair</div>
    </div>

    <!-- 5. In Process -->
    <div class="stat-card" style="--stat-color: #D97706; --stat-bg: #FEF3C7;">
        <div class="stat-header">
            <span class="stat-label">In Process</span>
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
        </div>
        <div class="stat-value">{{ number_format($inProcessCount) }}</div>
        <div class="stat-desc">Undergoing diagnostic / parts replace</div>
    </div>

    <!-- 6. Repaired OK -->
    <div class="stat-card" style="--stat-color: #059669; --stat-bg: #D1FAE5;">
        <div class="stat-header">
            <span class="stat-label">Repaired OK</span>
            <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
        </div>
        <div class="stat-value">{{ number_format($repairedCount) }}</div>
        <div class="stat-desc">Repairs passed & ready</div>
    </div>

    <!-- 7. BER -->
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
        <div class="stat-desc">Dispatched back to {{ $companyName }}</div>
    </div>
</div>

<!-- Quick Live Serial Tracker for this Company -->
<div class="card" style="margin-bottom: 24px; border-left: 4px solid var(--dftm-accent);">
    <div class="card-body" style="padding: 16px 20px;">
        <form method="GET" action="{{ route('admin.companies.dashboard', $clientUser ? $clientUser->id : urlencode($companyName)) }}" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <div style="font-weight: 700; font-size: 0.9rem; color: var(--dftm-navy); display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-upc-scan" style="color: var(--dftm-accent); font-size: 1.1rem;"></i> Quick Unit Tracker ({{ $companyName }}):
            </div>
            <div style="flex: 1; min-width: 250px;">
                <input type="text" name="track_serial" class="form-control mono"
                    placeholder="Search Serial Number or MAC for this company..."
                    value="{{ request('track_serial') }}">
            </div>
            <button type="submit" class="btn btn-primary btn-sm" style="font-weight: 700;">
                <i class="bi bi-search"></i> Track Unit
            </button>
            @if(request('track_serial'))
                <a href="{{ route('admin.companies.dashboard', $clientUser ? $clientUser->id : urlencode($companyName)) }}" class="btn btn-outline btn-sm">
                    Clear Tracker
                </a>
            @endif
        </form>

        @if($searchResult !== null)
            <div style="margin-top: 16px; border-top: 1px solid var(--dftm-border); padding-top: 14px;">
                @if($searchResult->count() > 0)
                    <div style="font-size: 0.85rem; font-weight: 700; color: #059669; margin-bottom: 8px;">
                        <i class="bi bi-check-circle-fill"></i> Found {{ $searchResult->count() }} unit(s) matching "{{ request('track_serial') }}":
                    </div>
                    <div class="table-responsive">
                        <table class="dftm-table">
                            <thead>
                                <tr>
                                    <th>Serial Number</th>
                                    <th>MAC Address</th>
                                    <th>Model / Brand</th>
                                    <th>Transmittal</th>
                                    <th>Batch</th>
                                    <th>Repair Status</th>
                                    <th>Stock Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($searchResult as $item)
                                <tr>
                                    <td><strong class="mono" style="color: #00205B;">{{ $item->serial_number }}</strong></td>
                                    <td><span class="mono">{{ $item->mac_address ?: '-' }}</span></td>
                                    <td>{{ $item->brand }} {{ $item->model }}</td>
                                    <td>
                                        @if($item->transmittal)
                                            <a href="{{ route('admin.incoming.show', $item->transmittal_id) }}" class="mono" style="font-weight: 600;">
                                                {{ $item->transmittal->transmittal_no }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $item->batch?->batch_no ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $item->repair_status === 'Repaired' ? 'badge-repaired' : ($item->repair_status === 'BER' ? 'badge-ber' : 'badge-in-process') }}">
                                            {{ $item->repair_status }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-stock">{{ $item->stock_status }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="color: #DC2626; font-size: 0.88rem; font-weight: 600;">
                        <i class="bi bi-exclamation-octagon"></i> No units found for "{{ request('track_serial') }}" under {{ $companyName }}.
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Two-Column Main Grid -->
<div class="dashboard-main-grid">
    <!-- Left Column: Recent Transmittals, Batches, & Outgoing for this Company -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Recent Incoming Transmittals -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="bi bi-box-arrow-in-down"></i> Recent Incoming Repair Slips</div>
                    <div class="card-subtitle">Latest repair slips received from {{ $companyName }}</div>
                </div>
                <a href="{{ route('admin.incoming.index', ['company' => $companyName]) }}" class="btn btn-outline btn-sm">
                    View All ({{ $totalTransmittals }})
                </a>
            </div>
            <div class="table-responsive">
                <table class="dftm-table">
                    <thead>
                        <tr>
                            <th>Transmittal #</th>
                            <th>Brand / Model</th>
                            <th>Date Received</th>
                            <th>Total Qty</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransmittals as $transmittal)
                        <tr>
                            <td><span class="mono" style="font-weight: 700; color: var(--dftm-navy);">{{ $transmittal->transmittal_no }}</span></td>
                            <td>{{ $transmittal->brand ?? 'Mixed' }} {{ $transmittal->model ?? '' }}</td>
                            <td>{{ $transmittal->date_received ? $transmittal->date_received->format('M d, Y') : '-' }}</td>
                            <td><strong>{{ $transmittal->total_quantity }}</strong> pcs</td>
                            <td>
                                <span class="badge {{ $transmittal->status === 'COMPLETED' ? 'badge-repaired' : 'badge-in-process' }}">
                                    {{ $transmittal->status ?: 'In process' }}
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
                            <td colspan="6" style="text-align: center; color: var(--dftm-slate); padding: 28px 16px;">
                                <div style="font-size: 1.8rem; color: #CBD5E1; margin-bottom: 6px;"><i class="bi bi-inboxes"></i></div>
                                <div style="font-weight: 600; font-size: 0.9rem; color: var(--dftm-navy);">No transmittals found for {{ $companyName }}</div>
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
                    <div class="card-subtitle">Diagnostic and repair matrix batches for this client</div>
                </div>
                <a href="{{ route('admin.traceability.index', ['company' => $companyName]) }}" class="btn btn-outline btn-sm">
                    View Matrix ({{ $totalBatches }})
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
                                    <i class="bi bi-sliders"></i> Matrix
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--dftm-slate); padding: 28px 16px;">
                                <div style="font-size: 1.8rem; color: #CBD5E1; margin-bottom: 6px;"><i class="bi bi-boxes"></i></div>
                                <div style="font-weight: 600; font-size: 0.9rem; color: var(--dftm-navy);">No batches formed for {{ $companyName }}</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Outgoing Releases -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="bi bi-box-arrow-up-right"></i> Recent Outgoing Slips</div>
                    <div class="card-subtitle">Dispatch documentation for {{ $companyName }}</div>
                </div>
                <a href="{{ route('admin.outgoing.index') }}" class="btn btn-outline btn-sm">
                    View Outgoing
                </a>
            </div>
            <div class="table-responsive">
                <table class="dftm-table">
                    <thead>
                        <tr>
                            <th>Slip #</th>
                            <th>SI # / DR #</th>
                            <th>Brand / Model</th>
                            <th>Qty</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOutgoing as $out)
                        <tr>
                            <td><span class="mono" style="font-weight: 700;">{{ $out->slip_no }}</span></td>
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
                            <td colspan="5" style="text-align: center; color: var(--dftm-slate); padding: 28px 16px;">
                                <div style="font-size: 1.8rem; color: #CBD5E1; margin-bottom: 6px;"><i class="bi bi-send-x"></i></div>
                                <div style="font-weight: 600; font-size: 0.9rem; color: var(--dftm-navy);">No outgoing release slips for {{ $companyName }}</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Brand Breakdown & Recent Units for this Company -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Brand Breakdown for this Company -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-pie-chart-fill"></i> Brand Inventory Breakdown</div>
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
                        <div style="font-weight: 600; font-size: 0.88rem; color: var(--dftm-navy);">No brand data available</div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Latest Units for this Company -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-cpu"></i> Recently Recorded Units</div>
            </div>
            <div class="card-body" style="padding: 12px 16px;">
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    @forelse($recentItems as $item)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; background: #F8FAFC; border: 1px solid var(--dftm-border); border-radius: 6px; font-size: 0.82rem;">
                            <div>
                                <div class="mono" style="font-weight: 800; color: #00205B;">{{ $item->serial_number }}</div>
                                <div style="font-size: 0.74rem; color: var(--dftm-slate);">
                                    {{ $item->brand }} {{ $item->model }} • <span class="mono">{{ $item->mac_address ?: 'No MAC' }}</span>
                                </div>
                            </div>
                            <div>
                                <span class="badge {{ $item->repair_status === 'Repaired' ? 'badge-repaired' : ($item->repair_status === 'BER' ? 'badge-ber' : 'badge-in-process') }}">
                                    {{ $item->repair_status }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 20px; color: var(--dftm-slate);">
                            No units recorded yet for this company.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
