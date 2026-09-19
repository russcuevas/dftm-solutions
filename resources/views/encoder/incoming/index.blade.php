@extends('layouts.app')

@section('title', 'Incoming')
@section('page_title', 'Incoming')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title"><i class="bi bi-box-arrow-in-down"></i> Incoming</div>
            <div class="card-subtitle">Encode and scan incoming repair slips in real-time</div>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('encoder.incoming.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> +Create Incoming
            </a>
        </div>
    </div>

    <div style="padding: 16px 24px; background: #F8FAFC; border-bottom: 1px solid var(--dftm-border);">
        <form action="{{ route('encoder.incoming.index') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <div style="flex: 1; min-width: 240px;">
                <input type="text" name="search" class="form-control" placeholder="Search by Transmittal #, Brand, Model, Company..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-outline"><i class="bi bi-funnel"></i> Search</button>
            @if(request()->filled('search'))
                <a href="{{ route('encoder.incoming.index') }}" class="btn btn-outline" style="color: #DC2626;">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="dftm-table">
            <thead>
                <tr>
                    <th>Transmittal #</th>
                    <th>Company Name</th>
                    <th>Date Received</th>
                    <th>Brand & Model</th>
                    <th>Total Scanned Qty</th>
                    <th>Status</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transmittals as $transmittal)
                <tr>
                    <td><span class="mono" style="font-weight: 700; color: var(--dftm-navy);">{{ $transmittal->transmittal_no }}</span></td>
                    <td><strong>{{ $transmittal->company_name ?? 'N/A' }}</strong></td>
                    <td>{{ $transmittal->date_received ? $transmittal->date_received->format('M d, Y') : 'N/A' }}</td>
                    <td>
                        <strong>{{ $transmittal->brand }}</strong> {{ $transmittal->model }}
                    </td>
                    <td><span class="badge" style="background: #EEF2FF; color: var(--dftm-navy); font-weight: 800; font-size: 0.88rem;">{{ $transmittal->total_quantity }} pcs</span></td>
                    <td>
                        @if($transmittal->status)
                            <span class="badge {{ $transmittal->status === 'Repaired' ? 'badge-repaired' : ($transmittal->status === 'BER' ? 'badge-ber' : 'badge-stock') }}">
                                {{ $transmittal->status }}
                            </span>
                        @else
                            <span style="color: var(--dftm-slate); font-weight: 600;">-</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <div style="display: flex; gap: 6px; justify-content: center;">
                            <a href="{{ route('encoder.incoming.edit', $transmittal->id) }}" class="btn btn-accent btn-sm" title="Scan Barcodes">
                                <i class="bi bi-barcode"></i> Scan
                            </a>
                            <a href="{{ route('encoder.incoming.show', $transmittal->id) }}" class="btn btn-outline btn-sm" title="View Summary">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('encoder.incoming.print', $transmittal->id) }}" target="_blank" class="btn btn-outline btn-sm" title="Print Report">
                                <i class="bi bi-printer"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--dftm-slate); padding: 32px;">No incoming repair slips found. Click "+Create Incoming" above to start.</td>
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
