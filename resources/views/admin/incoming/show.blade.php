@extends('layouts.app')

@section('title', 'Incoming Repair Slip - ' . $transmittal->transmittal_no)
@section('page_title', 'Incoming Repair Slip Summary')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">
                <i class="bi bi-file-earmark-text-fill"></i> Incoming Repair Slip: {{ $transmittal->transmittal_no }}
            </div>
            <div class="card-subtitle">Encoded by {{ $transmittal->encoder->name ?? 'System' }} on {{ $transmittal->created_at->format('M d, Y h:i A') }}</div>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.incoming.print', $transmittal->id) }}" target="_blank" class="btn btn-primary btn-sm">
                <i class="bi bi-printer"></i> Print Consolidated Report
            </a>
            <a href="{{ route('admin.incoming.edit', $transmittal->id) }}" class="btn btn-accent btn-sm">
                <i class="bi bi-barcode"></i> Continue Scanning
            </a>
            <a href="{{ route('admin.incoming.index') }}" class="btn btn-outline btn-sm">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="card-body">
        <!-- Transmittal Header Summary Card -->
        <div style="background: #F8FAFC; border: 1px solid var(--dftm-border); border-radius: var(--radius-md); padding: 20px; margin-bottom: 24px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                <div>
                    <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--dftm-slate);">TRANSMITTAL NO:</span>
                    <div style="font-weight: 800; color: var(--dftm-navy); font-size: 1.1rem;" class="mono">{{ $transmittal->transmittal_no }}</div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--dftm-slate);">COMPANY / CLIENT NAME:</span>
                    <div style="font-weight: 700; color: var(--dftm-navy); font-size: 1rem;">{{ $transmittal->company_name ?? 'N/A' }}</div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--dftm-slate);">DATE RECEIVED:</span>
                    <div style="font-weight: 600;">{{ $transmittal->date_received ? $transmittal->date_received->format('F d, Y') : 'N/A' }}</div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--dftm-slate);">TOTAL SCANNED QUANTITY:</span>
                    <div><span class="badge" style="background: #00205B; color: #FFFFFF; font-size: 0.95rem; padding: 6px 14px;">{{ $transmittal->total_quantity }} PCS</span></div>
                </div>
            </div>

            @if($transmittal->notes)
            <div style="margin-top: 14px; font-size: 0.85rem; color: var(--dftm-slate);">
                <strong>Notes:</strong> {{ $transmittal->notes }}
            </div>
            @endif
        </div>

        @php
            $itemsByModel = $transmittal->items->groupBy(function($it) {
                return trim($it->model ?: 'Unassigned Model');
            });
        @endphp

        <!-- Grouped Per Model Section ("isang buo pero naka per model lang") -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--dftm-navy); margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-cpu"></i> Breakdown of Scanned Units by Model ({{ $itemsByModel->count() }} Models, {{ $transmittal->total_quantity }} Total Units)
            </h3>
        </div>

        @forelse($itemsByModel as $modelName => $modelItems)
            <div style="border: 1px solid var(--dftm-border); border-radius: 8px; margin-bottom: 20px; overflow: hidden;">
                <div style="background: #EEF2FF; padding: 10px 16px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-weight: 800; color: var(--dftm-navy); font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-layers-fill" style="color: var(--dftm-accent);"></i> MODEL: {{ $modelName }}
                        @if($modelItems->first()?->brand)
                            <span style="color: var(--dftm-slate); font-weight: 600;">(Brand: {{ $modelItems->first()->brand }})</span>
                        @endif
                    </div>
                    <span class="badge badge-stock" style="font-weight: 800; font-size: 0.85rem;">
                        {{ $modelItems->count() }} PCS
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="dftm-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">NO.</th>
                                <th>SERIAL NUMBER</th>
                                <th>MAC ADDRESS</th>
                                <th>BRAND</th>
                                <th>BATCH STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($modelItems as $item)
                            <tr>
                                <td><strong>{{ $loop->iteration }}</strong></td>
                                <td><span class="mono" style="font-weight: 700; color: var(--dftm-navy);">{{ $item->serial_number ?? '-' }}</span></td>
                                <td><span class="mono">{{ $item->mac_address ?? '-' }}</span></td>
                                <td>{{ $item->brand ?? '-' }}</td>
                                <td>
                                    @if($item->batch_id)
                                        <span class="badge badge-stock">{{ $item->batch->batch_no ?? 'BATCH' }}</span>
                                    @else
                                        <span class="badge badge-light" style="color: #64748B;">Ready for Batching</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div style="text-align: center; color: var(--dftm-slate); padding: 36px; border: 1px dashed var(--dftm-border); border-radius: 8px;">
                No units encoded yet. Click "Continue Scanning" above to scan barcodes.
            </div>
        @endforelse
    </div>
</div>
@endsection
