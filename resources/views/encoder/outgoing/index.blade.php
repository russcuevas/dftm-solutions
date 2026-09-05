@extends('layouts.app')

@section('title', 'Outgoing Repair Slips')
@section('page_title', 'Outgoing Repair Releases')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title"><i class="bi bi-box-arrow-up-right"></i> Outgoing Repair Slips</div>
            <div class="card-subtitle">Manage customer deliveries & released products</div>
        </div>
        <a href="{{ route('encoder.outgoing.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create Outgoing Slip
        </a>
    </div>

    <!-- Search Bar -->
    <div style="padding: 16px 24px; background: #F8FAFC; border-bottom: 1px solid var(--dftm-border);">
        <form action="{{ route('encoder.outgoing.index') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 240px;">
                <input type="text" name="search" class="form-control" placeholder="Search Customer Name, Company, SI #, DR #, Brand, Model..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-outline"><i class="bi bi-search"></i> Search</button>
            @if(request()->filled('search'))
                <a href="{{ route('encoder.outgoing.index') }}" class="btn btn-outline" style="color: #DC2626;">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="dftm-table">
            <thead>
                <tr>
                    <th>Slip #</th>
                    <th>Company Name</th>
                    <th>SI Number</th>
                    <th>DR Number</th>
                    <th>Date Delivered</th>
                    <th>Brand & Model</th>
                    <th>Total Qty</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($outgoingSlips as $slip)
                <tr>
                    <td><span class="mono" style="font-weight: 700; color: var(--dftm-navy);">{{ $slip->slip_no }}</span></td>
                    <td>
                        <strong>{{ $slip->company_name ?? '—' }}</strong>
                        @if($slip->box_no)
                            <div style="font-size: 0.72rem; color: var(--dftm-slate);">{{ $slip->box_no }}</div>
                        @endif
                    </td>
                    <td><span class="mono">{{ $slip->si_number ?? '-' }}</span></td>
                    <td><span class="mono">{{ $slip->dr_number ?? '-' }}</span></td>
                    <td>{{ $slip->date_delivered ? $slip->date_delivered->format('M d, Y') : ($slip->date_released ? $slip->date_released->format('M d, Y') : 'N/A') }}</td>
                    <td>{{ $slip->brand }} {{ $slip->model }}</td>
                    <td><span class="badge badge-released">{{ $slip->total_quantity }} pcs</span></td>
                    <td>
                        @if($slip->status)
                            <span class="badge {{ strtoupper($slip->status) === 'BER' ? 'badge-ber' : (strtoupper($slip->status) === 'REPAIRED' ? 'badge-good' : 'badge-stock') }}">
                                {{ $slip->status }}
                            </span>
                        @else
                            <span style="color: var(--dftm-slate);">—</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <a href="{{ route('encoder.outgoing.show', $slip->id) }}" class="btn btn-outline btn-sm" title="View Slip">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('encoder.outgoing.edit', $slip->id) }}" class="btn btn-outline btn-sm" title="Edit Slip" style="color: var(--dftm-accent);">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <a href="{{ route('encoder.outgoing.print', $slip->id) }}" target="_blank" class="btn btn-outline btn-sm" title="Print Slip">
                                <i class="bi bi-printer"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align: center; color: var(--dftm-slate); padding: 32px;">No outgoing repair slips recorded.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($outgoingSlips->hasPages())
    <div class="card-footer">
        {{ $outgoingSlips->links() }}
    </div>
    @endif
</div>
@endsection
