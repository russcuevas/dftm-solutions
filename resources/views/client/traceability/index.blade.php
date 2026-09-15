@extends('layouts.app')

@section('title', 'Traceability Matrix & Batches')
@section('page_title', 'Repair Traceability Matrix')

@section('content')
    <style>
        .client-matrix-table {
            width: 100%;
            border-collapse: collapse;
            background: #FFFFFF;
        }
        .client-matrix-table th, .client-matrix-table td {
            border: 1px solid #E2E8F0;
            padding: 10px 12px;
            font-size: 0.84rem;
            vertical-align: middle;
        }
        .client-matrix-table th {
            background: #F8FAFC;
            color: #00205B;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .client-matrix-table tbody tr:hover {
            background-color: #F8FAFC;
        }
    </style>

    <!-- Top Batch Selection & Actions -->
    <div class="card" style="margin-bottom: 16px;">
        <div class="card-body" style="padding: 16px 20px;">
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px;">
                <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 320px;">
                    <label style="font-weight: 800; color: var(--dftm-navy); white-space: nowrap; font-size: 0.95rem;">
                        <i class="bi bi-diagram-3-fill" style="color: var(--dftm-accent);"></i> Select Batch:
                    </label>
                    <select id="activeBatchSelector" class="form-select" style="max-width: 420px; font-weight: 700; border-color: var(--dftm-accent);" onchange="location.href='{{ route('client.traceability.index') }}?batch_id=' + this.value;">
                        @forelse($batches as $b)
                            <option value="{{ $b->id }}" {{ $selectedBatch && $selectedBatch->id == $b->id ? 'selected' : '' }}>
                                {{ $b->batch_no }} &bull; {{ $b->brand }} {{ $b->model ? '(' . $b->model . ')' : '' }} ({{ $b->items->count() }} Units)
                            </option>
                        @empty
                            <option value="">-- No Batches Available For Your Company --</option>
                        @endforelse
                    </select>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    @if($selectedBatch)
                        <a href="{{ route('client.traceability.print', ['batch_id' => $selectedBatch->id]) }}" target="_blank" class="btn btn-outline btn-sm" style="font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="bi bi-printer-fill" style="color: var(--dftm-accent);"></i> Print / Download Matrix Report
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($selectedBatch)
        <!-- Material Description Header Block -->
        <div class="card" style="margin-bottom: 16px; border: 1px solid #CBD5E1;">
            <div class="card-header" style="background: #00205B; color: #FFFFFF; padding: 12px 20px;">
                <div class="card-title" style="color: #FFFFFF; font-size: 0.98rem; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-info-circle-fill" style="color: #38BDF8;"></i> MATERIAL DESCRIPTION HEADER
                </div>
                <div style="font-weight: 700; font-size: 0.88rem; color: #E2E8F0;">
                    Batch: {{ $selectedBatch->batch_no }} &bull; Total: {{ $selectedBatch->total_quantity ?? $selectedBatch->items->count() }} Units
                </div>
            </div>
            <div class="card-body" style="padding: 16px 20px; background: #F8FAFC;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; font-size: 0.85rem;">
                    <div>
                        <span style="font-weight: 800; color: var(--dftm-slate); font-size: 0.72rem; text-transform: uppercase;">COMPANY NAME:</span>
                        <div style="font-weight: 800; color: var(--dftm-navy); font-size: 1.05rem;">{{ $selectedBatch->company_name ?? $companyName }}</div>
                    </div>
                    <div>
                        <span style="font-weight: 800; color: var(--dftm-slate); font-size: 0.72rem; text-transform: uppercase;">BATCH NUMBER:</span>
                        <div style="font-weight: 800; color: var(--dftm-navy); font-size: 1.05rem;">{{ $selectedBatch->batch_no }}</div>
                    </div>
                    <div>
                        <span style="font-weight: 800; color: var(--dftm-slate); font-size: 0.72rem; text-transform: uppercase;">BRAND / MODEL:</span>
                        <div style="font-weight: 700; font-size: 0.95rem;">{{ $selectedBatch->brand ?? '-' }} {{ $selectedBatch->model ? ' / ' . $selectedBatch->model : '' }}</div>
                    </div>
                    <div>
                        <span style="font-weight: 800; color: var(--dftm-slate); font-size: 0.72rem; text-transform: uppercase;">DATE DELIVERED:</span>
                        <div style="font-weight: 700; font-size: 0.95rem;">{{ $selectedBatch->date_delivered ? \Carbon\Carbon::parse($selectedBatch->date_delivered)->format('M d, Y') : '-' }}</div>
                    </div>
                    <div>
                        <span style="font-weight: 800; color: var(--dftm-slate); font-size: 0.72rem; text-transform: uppercase;">REPAIR PROGRESS:</span>
                        <div style="display: flex; gap: 6px; margin-top: 5px; flex-wrap: wrap;">
                            <span class="badge badge-stock" title="In Process">{{ $inProcessCount }} In Process</span>
                            <span class="badge badge-repaired" title="Repaired">{{ $repairedCount }} Repaired</span>
                            <span class="badge badge-ber" title="BER">{{ $berCount }} BER</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="card" style="margin-bottom: 16px;">
            <div class="card-body" style="padding: 12px 20px;">
                <form method="GET" action="{{ route('client.traceability.index') }}" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;">
                    <input type="hidden" name="batch_id" value="{{ $selectedBatch->id }}">

                    <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 260px;">
                        <div class="search-bar" style="width: 100%; max-width: 380px;">
                            <i class="bi bi-search"></i>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search Serial, MAC, Diagnostic, Parts..." value="{{ request('search') }}">
                        </div>
                        @if(request('search') || request('status'))
                            <a href="{{ route('client.traceability.index', ['batch_id' => $selectedBatch->id]) }}" class="btn btn-outline btn-sm" title="Clear Filters">
                                Clear
                            </a>
                        @endif
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 0.8rem; font-weight: 700; color: var(--dftm-slate);">Status:</span>
                        <select name="status" class="form-select form-select-sm" style="width: 140px; font-weight: 600;" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="In process" {{ request('status') === 'In process' ? 'selected' : '' }}>In process</option>
                            <option value="Repaired" {{ request('status') === 'Repaired' ? 'selected' : '' }}>Repaired</option>
                            <option value="BER" {{ request('status') === 'BER' ? 'selected' : '' }}>BER</option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Read-Only Traceability Matrix Table -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="bi bi-table"></i> Units in Batch {{ $selectedBatch->batch_no }}</div>
                    <div class="card-subtitle">Showing {{ $items->total() }} recorded units (Read-Only Customer View)</div>
                </div>
            </div>
            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                <table class="client-matrix-table">
                    <thead style="position: sticky; top: 0; z-index: 10;">
                        <tr>
                            <th style="width: 50px; text-align: center;">NO.</th>
                            <th style="min-width: 180px;">MATERIAL DIAGNOSTIC</th>
                            <th style="min-width: 150px;">REPLACE PARTS</th>
                            <th style="min-width: 120px; text-align: center;">STATUS</th>
                            <th style="min-width: 100px;">BRAND</th>
                            <th style="min-width: 110px;">MODEL</th>
                            <th style="min-width: 160px;">SERIAL NUMBER</th>
                            <th style="min-width: 150px;">MAC ADDRESS</th>
                            <th style="width: 90px; text-align: center;">BOX #</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td style="text-align: center; font-weight: 800; color: var(--dftm-navy);">
                                    {{ $items->firstItem() ? ($items->firstItem() + $loop->index) : $loop->iteration }}
                                </td>
                                <td>
                                    @if($item->technical_diagnostic)
                                        <span style="font-weight: 600; color: #1E293B;">{{ $item->technical_diagnostic }}</span>
                                    @else
                                        <span style="color: #94A3B8; font-style: italic;">Pending diagnostic</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->replace_parts)
                                        <span style="font-weight: 600; color: #1E293B;">{{ $item->replace_parts }}</span>
                                    @else
                                        <span style="color: #94A3B8; font-style: italic;">None / Pending</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @if($item->repair_status === 'Repaired')
                                        <span class="badge badge-repaired"><i class="bi bi-check-circle-fill"></i> Repaired</span>
                                    @elseif($item->repair_status === 'BER')
                                        <span class="badge badge-ber"><i class="bi bi-x-circle-fill"></i> BER</span>
                                    @else
                                        <span class="badge badge-stock"><i class="bi bi-hourglass-split"></i> In process</span>
                                    @endif
                                </td>
                                <td style="font-weight: 600;">{{ $item->brand ?? '-' }}</td>
                                <td style="font-weight: 600;">{{ $item->model ?? '-' }}</td>
                                <td>
                                    <code style="font-weight: 700; color: var(--dftm-navy); font-size: 0.86rem; background: #EEF2F6; padding: 2px 6px; border-radius: 4px;">{{ $item->serial_number }}</code>
                                </td>
                                <td>
                                    <code style="color: var(--dftm-slate); font-size: 0.84rem;">{{ $item->mac_address ?? '-' }}</code>
                                </td>
                                <td style="text-align: center;">
                                    @if($item->box_no)
                                        <span class="badge" style="background: #E2E8F0; color: #334155; font-weight: 700;">{{ $item->box_no }}</span>
                                    @else
                                        <span style="color: #94A3B8;">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 36px 16px; color: var(--dftm-slate);">
                                    <i class="bi bi-inbox" style="font-size: 2rem; display: block; margin-bottom: 8px; opacity: 0.5;"></i>
                                    No units matched the criteria in this batch.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($items->hasPages())
                <div style="padding: 16px 20px; border-top: 1px solid var(--dftm-border); display: flex; justify-content: flex-end;">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="card">
            <div class="card-body" style="padding: 48px 24px; text-align: center; color: var(--dftm-slate);">
                <i class="bi bi-diagram-3" style="font-size: 3rem; color: #CBD5E1; display: block; margin-bottom: 12px;"></i>
                <h5 style="font-weight: 800; color: var(--dftm-navy); margin-bottom: 6px;">No Batches Assigned Yet</h5>
                <p style="max-width: 480px; margin: 0 auto; font-size: 0.9rem;">
                    When DFTM Digital Solutions groups your delivered units into batches for diagnostics and repair, they will appear here in real-time.
                </p>
            </div>
        </div>
    @endif
@endsection
