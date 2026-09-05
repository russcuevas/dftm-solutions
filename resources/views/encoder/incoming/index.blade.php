@extends('layouts.app')

@section('title', 'Incoming Repair Slips')
@section('page_title', 'Incoming Repair Batches')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title"><i class="bi bi-box-arrow-in-down"></i> Incoming Repair Slips</div>
            <div class="card-subtitle">Encode new batches or review incoming records</div>
        </div>
        <a href="{{ route('encoder.incoming.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Encode Incoming Slip
        </a>
    </div>

    <!-- Search Bar -->
    <div style="padding: 16px 24px; background: #F8FAFC; border-bottom: 1px solid var(--dftm-border);">
        <form action="{{ route('encoder.incoming.index') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 240px;">
                <input type="text" name="search" class="form-control" placeholder="Search by Slip #, Batch No, Brand, Model, Company..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-outline"><i class="bi bi-search"></i> Search</button>
            @if(request()->filled('search'))
                <a href="{{ route('encoder.incoming.index') }}" class="btn btn-outline" style="color: #DC2626;">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="dftm-table">
            <thead>
                <tr>
                    <th>Slip #</th>
                    <th>Batch No</th>
                    <th>Company Name</th>
                    <th>Date Delivered</th>
                    <th>Brand & Model</th>
                    <th>Total Qty</th>
                    <th>In Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batches as $batch)
                <tr>
                    <td><span class="mono" style="font-weight: 700; color: var(--dftm-navy);">{{ $batch->slip_no }}</span></td>
                    <td><strong>{{ $batch->batch_no }}</strong></td>
                    <td>{{ $batch->company_name ?? 'N/A' }}</td>
                    <td>{{ $batch->date_delivered ? $batch->date_delivered->format('M d, Y') : 'N/A' }}</td>
                    <td><strong>{{ $batch->brand }}</strong> {{ $batch->model }}</td>
                    <td><span class="badge" style="background: #F1F5F9; color: var(--dftm-navy);">{{ $batch->total_quantity }} pcs</span></td>
                    <td><span class="badge badge-stock">{{ $batch->in_stock_quantity }} pcs</span></td>
                    <td>
                        @if($batch->status)
                            <span class="badge {{ $batch->status === 'Repaired' || $batch->status === 'COMPLETED' ? 'badge-repaired' : ($batch->status === 'BER' ? 'badge-ber' : 'badge-stock') }}">
                                {{ $batch->status }}
                            </span>
                        @else
                            <span style="color: var(--dftm-slate); font-weight: 600;">-</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <a href="{{ route('encoder.incoming.show', $batch->id) }}" class="btn btn-outline btn-sm" title="View Slip">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('encoder.incoming.print', $batch->id) }}" target="_blank" class="btn btn-outline btn-sm" title="Print Slip">
                                <i class="bi bi-printer"></i>
                            </a>
                            <a href="{{ route('encoder.incoming.edit', $batch->id) }}" class="btn btn-outline btn-sm" title="Edit Incoming Slip">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: var(--dftm-slate); padding: 32px;">No incoming repair batches recorded.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($batches->hasPages())
    <div class="card-footer">
        {{ $batches->links() }}
    </div>
    @endif
</div>
@endsection
