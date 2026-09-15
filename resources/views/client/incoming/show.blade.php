@extends('layouts.app')

@section('title', 'Transmittal Details')
@section('page_title', 'Transmittal ' . $transmittal->transmittal_no)

@section('content')
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header" style="background: #F8FAFC;">
        <div>
            <div class="card-title">
                <i class="bi bi-box-arrow-in-down"></i> Transmittal Summary: <span class="mono">{{ $transmittal->transmittal_no }}</span>
            </div>
            <div class="card-subtitle">Received on {{ $transmittal->date_received ? $transmittal->date_received->format('M d, Y') : '-' }} &bull; {{ $transmittal->company_name }}</div>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('client.incoming.print', $transmittal->id) }}" target="_blank" class="btn btn-primary btn-sm">
                <i class="bi bi-printer"></i> Print Report
            </a>
            <a href="{{ route('client.incoming.index') }}" class="btn btn-outline btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Transmittals
            </a>
        </div>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <div>
                <small style="color: var(--dftm-slate); text-transform: uppercase; font-weight: 700; font-size: 0.72rem;">Company Name</small>
                <div style="font-weight: 800; font-size: 1.05rem; color: var(--dftm-navy);">{{ $transmittal->company_name }}</div>
            </div>
            <div>
                <small style="color: var(--dftm-slate); text-transform: uppercase; font-weight: 700; font-size: 0.72rem;">Default Brand & Model</small>
                <div style="font-weight: 700; font-size: 1.05rem;">{{ $transmittal->brand ?: 'Mixed' }} {{ $transmittal->model }}</div>
            </div>
            <div>
                <small style="color: var(--dftm-slate); text-transform: uppercase; font-weight: 700; font-size: 0.72rem;">Total Quantity</small>
                <div style="font-weight: 800; font-size: 1.05rem; color: var(--dftm-accent);">{{ $transmittal->total_quantity }} pcs</div>
            </div>
            <div>
                <small style="color: var(--dftm-slate); text-transform: uppercase; font-weight: 700; font-size: 0.72rem;">Processing Status</small>
                <div>
                    <span class="badge {{ $transmittal->status === 'COMPLETED' ? 'badge-repaired' : 'badge-in-process' }}" style="font-size: 0.85rem;">
                        {{ $transmittal->status }}
                    </span>
                </div>
            </div>
        </div>

        @if($transmittal->notes)
            <div style="margin-top: 14px; padding: 10px 14px; background: #F1F5F9; border-radius: 6px; font-size: 0.82rem;">
                <strong>Notes / Instructions:</strong> {{ $transmittal->notes }}
            </div>
        @endif
    </div>
</div>

<!-- Scanned Units Table -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="bi bi-list-check"></i> Units Encoded in this Transmittal</div>
        <span class="badge badge-stock">{{ $transmittal->items->count() }} Total Units</span>
    </div>
    <div class="table-responsive">
        <table class="dftm-table">
            <thead>
                <tr>
                    <th style="width: 50px;">No.</th>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>Serial Number</th>
                    <th>MAC Address</th>
                    <th>Box No</th>
                    <th>Batch</th>
                    <th>Repair Status</th>
                    <th>Stock Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transmittal->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $item->brand ?: $transmittal->brand }}</td>
                    <td><strong>{{ $item->model ?: $transmittal->model }}</strong></td>
                    <td><span class="mono" style="font-weight: 800; color: var(--dftm-navy);">{{ $item->serial_number }}</span></td>
                    <td><span class="mono">{{ $item->mac_address ?: '-' }}</span></td>
                    <td>{{ $item->box_no ?: '-' }}</td>
                    <td>
                        @if($item->batch)
                            <a href="{{ route('client.traceability.index', ['batch_id' => $item->batch_id]) }}" style="font-weight: 700; color: var(--dftm-navy);">
                                {{ $item->batch->batch_no }}
                            </a>
                        @else
                            <span class="badge" style="background: #F1F5F9; color: #64748B;">Pending Batch</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $item->repair_status === 'Repaired' ? 'badge-repaired' : ($item->repair_status === 'BER' ? 'badge-ber' : 'badge-in-process') }}">
                            {{ $item->repair_status ?: 'In process' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $item->stock_status === 'IN_STOCK' ? 'badge-stock' : 'badge-released' }}">
                            {{ $item->stock_status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: var(--dftm-slate); padding: 32px;">
                        No units registered under this transmittal.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
