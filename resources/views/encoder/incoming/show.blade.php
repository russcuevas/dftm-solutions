@extends('layouts.app')

@section('title', 'Incoming Slip - ' . $batch->slip_no)
@section('page_title', 'Incoming Repair Slip Details')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">
                <i class="bi bi-file-earmark-text-fill"></i> Incoming Slip: {{ $batch->slip_no }}
            </div>
            <div class="card-subtitle">Encoded by {{ $batch->encoder->name ?? 'System' }} on {{ $batch->created_at->format('M d, Y h:i A') }}</div>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('encoder.incoming.print', $batch->id) }}" target="_blank" class="btn btn-primary btn-sm">
                <i class="bi bi-printer"></i> Print Slip
            </a>
            <a href="{{ route('encoder.incoming.edit', $batch->id) }}" class="btn btn-outline btn-sm">
                <i class="bi bi-pencil-square"></i> Edit Slip
            </a>
            <a href="{{ route('encoder.incoming.index') }}" class="btn btn-outline btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Incoming List
            </a>
        </div>
    </div>

    <div class="card-body">
        <div style="background: #F8FAFC; border: 1px solid var(--dftm-border); border-radius: var(--radius-md); padding: 20px; margin-bottom: 24px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                <div>
                    <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--dftm-slate);">COMPANY NAME:</span>
                    <div style="font-weight: 700; color: var(--dftm-navy); font-size: 1rem;">{{ $batch->company_name ?? 'N/A' }}</div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--dftm-slate);">DATE DELIVERED:</span>
                    <div style="font-weight: 600;">{{ $batch->date_delivered ? $batch->date_delivered->format('F d, Y') : 'N/A' }}</div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--dftm-slate);">TOTAL QUANTITY:</span>
                    <div><span class="badge badge-stock" style="font-size: 0.9rem;">{{ $batch->total_quantity }} PCS</span></div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--dftm-slate);">BATCH STATUS:</span>
                    <div>
                        @if($batch->status)
                            <span class="badge {{ $batch->status === 'Repaired' || $batch->status === 'COMPLETED' ? 'badge-repaired' : ($batch->status === 'BER' ? 'badge-ber' : 'badge-stock') }}">
                                {{ $batch->status }}
                            </span>
                        @else
                            <span style="font-weight: 700; color: var(--dftm-slate);">-</span>
                        @endif
                    </div>
                </div>
            </div>

            <hr style="margin: 16px 0; border: 0; border-top: 1px solid var(--dftm-border);">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                <div>
                    <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--dftm-slate);">BATCH:</span>
                    <div style="font-weight: 700; color: var(--dftm-navy);">{{ $batch->batch_no }}</div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--dftm-slate);">ITEM DESCRIPTION:</span>
                    <div style="font-weight: 600;">{{ $batch->item_description ?? 'N/A' }}</div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--dftm-slate);">BRAND & MODEL:</span>
                    <div style="font-weight: 600;">{{ $batch->brand }} {{ $batch->model }}</div>
                </div>
            </div>
        </div>

        <h3 style="font-size: 1rem; font-weight: 700; color: var(--dftm-navy); margin-bottom: 12px;">
            <i class="bi bi-cpu"></i> Serialized Products List ({{ $batch->items->count() }} items)
        </h3>

        <div class="table-responsive">
            <table class="dftm-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">NO.</th>
                        <th>SERIAL NUMBER</th>
                        <th>MAC ADDRESS</th>
                        <th>BOX NO.</th>
                        <th>STOCK STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batch->items as $item)
                    <tr>
                        <td><strong>{{ $item->item_no ?? $loop->iteration }}</strong></td>
                        <td><span class="mono" style="font-weight: 600;">{{ $item->serial_number ?? '-' }}</span></td>
                        <td><span class="mono">{{ $item->mac_address ?? '-' }}</span></td>
                        <td>{{ $item->box_no ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $item->stock_status === 'IN_STOCK' ? 'badge-stock' : 'badge-released' }}">
                                {{ $item->stock_status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--dftm-slate); padding: 24px;">No serialized units registered for this batch.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
