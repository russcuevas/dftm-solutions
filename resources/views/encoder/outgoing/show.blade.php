@extends('layouts.app')

@section('title', 'Outgoing Slip - ' . $slip->slip_no)
@section('page_title', 'Outgoing Repair Slip Details')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">
                <i class="bi bi-file-earmark-check-fill"></i> Outgoing Slip: {{ $slip->slip_no }}
            </div>
            <div class="card-subtitle">Processed on {{ $slip->created_at->format('M d, Y h:i A') }}</div>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('encoder.outgoing.edit', $slip->id) }}" class="btn btn-accent btn-sm">
                <i class="bi bi-pencil-square"></i> Edit Slip
            </a>
            <a href="{{ route('encoder.outgoing.print', $slip->id) }}" target="_blank" class="btn btn-primary btn-sm">
                <i class="bi bi-printer"></i> Print Slip
            </a>
            <a href="{{ route('encoder.outgoing.index') }}" class="btn btn-outline btn-sm">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="card-body">
        <div style="background: #F8FAFC; border: 1px solid var(--dftm-border); border-radius: var(--radius-md); padding: 20px; margin-bottom: 24px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                <div>
                    <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--dftm-slate);">COMPANY NAME:</span>
                    <div style="font-weight: 700; color: var(--dftm-navy); font-size: 1.05rem;">
                        {{ $slip->company_name ?? 'N/A' }}
                    </div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--dftm-slate);">DATE RELEASED:</span>
                    <div style="font-weight: 600;">{{ $slip->date_released ? $slip->date_released->format('F d, Y') : 'N/A' }}</div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--dftm-slate);">TOTAL QUANTITY:</span>
                    <div><span class="badge badge-released" style="font-size: 0.9rem;">{{ $slip->total_quantity }} PCS</span></div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--dftm-slate);">STATUS:</span>
                    <div>
                        @if($slip->status)
                            <span class="badge badge-stock">{{ $slip->status }}</span>
                        @else
                            <span style="color: var(--dftm-slate); font-weight: 600;">—</span>
                        @endif
                    </div>
                </div>
            </div>

            <hr style="margin: 16px 0; border: 0; border-top: 1px solid var(--dftm-border);">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
                <div>
                    <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--dftm-slate);">SI NUMBER:</span>
                    <div class="mono" style="font-weight: 700; color: var(--dftm-navy);">{{ $slip->si_number ?? 'N/A' }}</div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--dftm-slate);">DR NUMBER:</span>
                    <div class="mono" style="font-weight: 700; color: var(--dftm-navy);">{{ $slip->dr_number ?? 'N/A' }}</div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--dftm-slate);">BRAND & MODEL:</span>
                    <div style="font-weight: 600;">{{ $slip->brand }} {{ $slip->model }}</div>
                </div>
            </div>
        </div>

        <h3 style="font-size: 1rem; font-weight: 700; color: var(--dftm-navy); margin-bottom: 12px;">
            <i class="bi bi-box-seam"></i> Released Serialized Items ({{ $slip->items->count() }} items)
        </h3>

        <div class="table-responsive">
            <table class="dftm-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">NO.</th>
                        <th>BRAND</th>
                        <th>MODEL</th>
                        <th>SERIAL NUMBER</th>
                        <th>MAC ADDRESS</th>
                        <th>BOX NO.</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($slip->items as $item)
                    <tr>
                        <td><strong>{{ $loop->iteration }}</strong></td>
                        <td>{{ $item->brand }}</td>
                        <td>{{ $item->model }}</td>
                        <td><span class="mono" style="font-weight: 700; color: var(--dftm-navy);">{{ $item->serial_number ?? '-' }}</span></td>
                        <td><span class="mono">{{ $item->mac_address ?? '-' }}</span></td>
                        <td>{{ $item->box_no ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--dftm-slate); padding: 24px;">No serialized units linked to this outgoing slip.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
