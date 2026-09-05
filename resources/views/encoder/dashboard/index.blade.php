@extends('layouts.app')

@section('title', 'Encoder Dashboard')
@section('page_title', 'Data Encoder Workspace')

@section('content')
<!-- Metric Stat Cards Grid -->
<div class="stat-grid">
    <div class="stat-card" style="--stat-color: #0284C7; --stat-bg: #E0F2FE;">
        <div class="stat-header">
            <span class="stat-label">My Encoded Batches</span>
            <div class="stat-icon"><i class="bi bi-input-cursor-text"></i></div>
        </div>
        <div class="stat-value">{{ number_format($myEncodedBatches) }}</div>
        <div class="stat-desc">Batches registered by you</div>
    </div>

    <div class="stat-card" style="--stat-color: #00205B; --stat-bg: #E0E7FF;">
        <div class="stat-header">
            <span class="stat-label">In-Stock Units</span>
            <div class="stat-icon"><i class="bi bi-boxes"></i></div>
        </div>
        <div class="stat-value">{{ number_format($totalInStock) }}</div>
        <div class="stat-desc">Available in warehouse stock</div>
    </div>

    <div class="stat-card" style="--stat-color: #D97706; --stat-bg: #FEF3C7;">
        <div class="stat-header">
            <span class="stat-label">In Process</span>
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
        </div>
        <div class="stat-value">{{ number_format($totalInProcess) }}</div>
        <div class="stat-desc">For technical diagnostic & repair</div>
    </div>

    <div class="stat-card" style="--stat-color: #059669; --stat-bg: #D1FAE5;">
        <div class="stat-header">
            <span class="stat-label">Repaired</span>
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
</div>

<!-- Quick Action Encoding Banner -->
<div class="card" style="background: linear-gradient(135deg, #00205B 0%, #0A2540 100%); color: #FFFFFF; border: none; margin-bottom: 24px;">
    <div class="card-body" style="padding: 28px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div>
            <h2 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 6px; color: #FFFFFF;">Ready to encode incoming items?</h2>
            <p style="color: #94A3B8; font-size: 0.9rem;">Input batch information, serial numbers, and MAC addresses without complicated statuses</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('encoder.incoming.create') }}" class="btn btn-accent" style="padding: 12px 20px;">
                <i class="bi bi-plus-circle"></i> Encode Incoming Batch
            </a>
            <a href="{{ route('encoder.outgoing.create') }}" class="btn btn-outline" style="padding: 12px 20px; color: #FFFFFF; border-color: rgba(255, 255, 255, 0.3);">
                <i class="bi bi-send"></i> Process Outgoing Slip
            </a>
        </div>
    </div>
</div>

<!-- Two Columns for Recent Activities -->
<div class="encoder-dashboard-grid">
    <!-- Recent Incoming Batches -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title"><i class="bi bi-box-arrow-in-down"></i> Recent Incoming Slips</div>
                <div class="card-subtitle">Batches registered in system</div>
            </div>
            <a href="{{ route('encoder.incoming.index') }}" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-responsive">
            <table class="dftm-table">
                <thead>
                    <tr>
                        <th>Batch</th>
                        <th>Brand & Model</th>
                        <th>Total Qty</th>
                        <th>In Stock</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentBatches as $b)
                    <tr>
                        <td><strong>{{ $b->batch_no }}</strong></td>
                        <td>{{ $b->brand }} {{ $b->model }}</td>
                        <td>{{ $b->total_quantity }} pcs</td>
                        <td><span class="badge badge-stock">{{ $b->in_stock_quantity }} pcs</span></td>
                        <td>
                            <a href="{{ route('encoder.incoming.show', $b->id) }}" class="btn btn-outline btn-sm">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--dftm-slate); padding: 32px 16px;">
                            <div style="font-size: 1.8rem; color: #CBD5E1; margin-bottom: 6px;"><i class="bi bi-inboxes"></i></div>
                            <div style="font-weight: 600; font-size: 0.9rem; color: var(--dftm-navy);">No batches encoded yet</div>
                            <div style="font-size: 0.75rem; color: var(--dftm-slate-light); margin-top: 2px;">Your encoded incoming slips will appear here.</div>
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
                <div class="card-title"><i class="bi bi-send-check"></i> Recent Outgoing Slips</div>
                <div class="card-subtitle">Products released to clients</div>
            </div>
            <a href="{{ route('encoder.outgoing.index') }}" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-responsive">
            <table class="dftm-table">
                <thead>
                    <tr>
                        <th>Slip #</th>
                        <th>Customer</th>
                        <th>SI / DR #</th>
                        <th>Qty</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOutgoing as $o)
                    <tr>
                        <td><span class="mono">{{ $o->slip_no }}</span></td>
                        <td><strong>{{ $o->customer_name }}</strong></td>
                        <td><span class="mono">{{ $o->si_number ?? '-' }}</span></td>
                        <td><span class="badge badge-released">{{ $o->total_quantity }} pcs</span></td>
                        <td>
                            <a href="{{ route('encoder.outgoing.show', $o->id) }}" class="btn btn-outline btn-sm">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--dftm-slate); padding: 32px 16px;">
                            <div style="font-size: 1.8rem; color: #CBD5E1; margin-bottom: 6px;"><i class="bi bi-send-x"></i></div>
                            <div style="font-weight: 600; font-size: 0.9rem; color: var(--dftm-navy);">No outgoing slips recorded yet</div>
                            <div style="font-size: 0.75rem; color: var(--dftm-slate-light); margin-top: 2px;">Processed releases will show here.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
