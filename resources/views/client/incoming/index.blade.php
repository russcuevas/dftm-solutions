@extends('layouts.app')

@section('title', 'Incoming')
@section('page_title', 'Incoming')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title"><i class="bi bi-box-arrow-in-down"></i> Transmittal Records ({{ $companyName }})</div>
            <div class="card-subtitle">List of received repair shipments registered for your company</div>
        </div>
    </div>

    <!-- Search bar -->
    <div style="padding: 16px 20px; background: #F8FAFC; border-bottom: 1px solid var(--dftm-border-subtle);">
        <form action="{{ route('client.incoming.index') }}" method="GET" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 280px;">
                <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                    placeholder="Search by Transmittal #, Model, Serial Number, or MAC Address...">
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
            @if(request()->filled('search'))
                <a href="{{ route('client.incoming.index') }}" class="btn btn-outline">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="dftm-table">
            <thead>
                <tr>
                    <th>Transmittal #</th>
                    <th>Date Received</th>
                    <th>Brand & Model</th>
                    <th>Total Units</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transmittals as $t)
                <tr>
                    <td><span class="mono" style="font-weight: 700; color: var(--dftm-navy); font-size: 0.95rem;">{{ $t->transmittal_no }}</span></td>
                    <td>{{ $t->date_received ? $t->date_received->format('M d, Y') : '-' }}</td>
                    <td><strong>{{ $t->brand }}</strong> {{ $t->model }}</td>
                    <td><strong>{{ $t->total_quantity }}</strong> pcs</td>
                    <td>
                        <span class="badge {{ $t->status === 'COMPLETED' ? 'badge-repaired' : 'badge-in-process' }}">
                            {{ $t->status }}
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <a href="{{ route('client.incoming.show', $t->id) }}" class="btn btn-outline btn-sm">
                                <i class="bi bi-eye"></i> View Units
                            </a>
                            <a href="{{ route('client.incoming.print', $t->id) }}" target="_blank" class="btn btn-outline btn-sm" title="Print Official Report">
                                <i class="bi bi-printer"></i> Print Report
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--dftm-slate); padding: 36px;">
                        No transmittal records found under {{ $companyName }}.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transmittals->hasPages())
    <div class="card-footer">
        {{ $transmittals->links() }}
    </div>
    @endif
</div>
@endsection
