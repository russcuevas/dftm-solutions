@extends('layouts.app')

@section('title', 'Repair Traceability Matrix')
@section('page_title', 'Repair Traceability Matrix')

@section('content')
@php
    $hasBatch = !empty($activeSlipId) || !empty($activeBatchId) || !empty($tab);
    $activeStatus = request('status', '');
    $isAllStatus = $hasBatch && (empty($activeStatus) || $activeStatus === 'all');
    $isInProcess = $hasBatch && ($activeStatus === 'In process' || $activeStatus === 'IN_PROCESS');
    $isRepaired = $hasBatch && ($activeStatus === 'Repaired');
    $isBer = $hasBatch && ($activeStatus === 'BER');
@endphp

<!-- Metric Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px;">
    @if($hasBatch)
        <a href="{{ route('encoder.traceability.index', request()->except('status', 'tab', 'page')) }}" style="text-decoration: none;">
            <div style="background: {{ $isAllStatus ? 'var(--dftm-navy)' : '#FFFFFF' }}; border: 2px solid {{ $isAllStatus ? 'var(--dftm-navy)' : 'var(--dftm-border)' }}; border-radius: var(--radius-md); padding: 14px 18px; transition: all 0.2s; box-shadow: {{ $isAllStatus ? '0 4px 12px rgba(0,32,91,0.2)' : 'none' }};" onmouseover="if(!{{ $isAllStatus ? 'true' : 'false' }}) this.style.borderColor='var(--dftm-navy)'" onmouseout="if(!{{ $isAllStatus ? 'true' : 'false' }}) this.style.borderColor='var(--dftm-border)'">
                <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: {{ $isAllStatus ? '#E2E8F0' : 'var(--dftm-slate)' }};">ALL UNITS RECORDED</span>
                <div style="font-size: 1.5rem; font-weight: 800; color: {{ $isAllStatus ? '#FFFFFF' : 'var(--dftm-navy)' }};">{{ $repairedCount + $inProcessCount + $berCount }} pcs</div>
            </div>
        </a>
        <a href="{{ route('encoder.traceability.index', array_merge(request()->except('status', 'tab', 'page'), ['status' => 'In process'])) }}" style="text-decoration: none;">
            <div style="background: {{ $isInProcess ? '#D97706' : '#FFFFFF' }}; border: 2px solid {{ $isInProcess ? '#D97706' : '#FDE68A' }}; border-radius: var(--radius-md); padding: 14px 18px; transition: all 0.2s; box-shadow: {{ $isInProcess ? '0 4px 12px rgba(217,119,6,0.2)' : 'none' }};" onmouseover="if(!{{ $isInProcess ? 'true' : 'false' }}) this.style.borderColor='#D97706'" onmouseout="if(!{{ $isInProcess ? 'true' : 'false' }}) this.style.borderColor='#FDE68A'">
                <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: {{ $isInProcess ? '#FEF3C7' : '#D97706' }};">IN PROCESS</span>
                <div style="font-size: 1.5rem; font-weight: 800; color: {{ $isInProcess ? '#FFFFFF' : '#D97706' }};">{{ $inProcessCount }} pcs</div>
            </div>
        </a>
        <a href="{{ route('encoder.traceability.index', array_merge(request()->except('status', 'tab', 'page'), ['status' => 'Repaired'])) }}" style="text-decoration: none;">
            <div style="background: {{ $isRepaired ? '#059669' : '#FFFFFF' }}; border: 2px solid {{ $isRepaired ? '#059669' : '#A7F3D0' }}; border-radius: var(--radius-md); padding: 14px 18px; transition: all 0.2s; box-shadow: {{ $isRepaired ? '0 4px 12px rgba(5,150,105,0.2)' : 'none' }};" onmouseover="if(!{{ $isRepaired ? 'true' : 'false' }}) this.style.borderColor='#059669'" onmouseout="if(!{{ $isRepaired ? 'true' : 'false' }}) this.style.borderColor='#A7F3D0'">
                <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: {{ $isRepaired ? '#D1FAE5' : '#059669' }};">REPAIRED (GOOD)</span>
                <div style="font-size: 1.5rem; font-weight: 800; color: {{ $isRepaired ? '#FFFFFF' : '#059669' }};">{{ $repairedCount }} pcs</div>
            </div>
        </a>
        <a href="{{ route('encoder.traceability.index', array_merge(request()->except('status', 'tab', 'page'), ['status' => 'BER'])) }}" style="text-decoration: none;">
            <div style="background: {{ $isBer ? '#DC2626' : '#FFFFFF' }}; border: 2px solid {{ $isBer ? '#DC2626' : '#FECACA' }}; border-radius: var(--radius-md); padding: 14px 18px; transition: all 0.2s; box-shadow: {{ $isBer ? '0 4px 12px rgba(220,38,38,0.2)' : 'none' }};" onmouseover="if(!{{ $isBer ? 'true' : 'false' }}) this.style.borderColor='#DC2626'" onmouseout="if(!{{ $isBer ? 'true' : 'false' }}) this.style.borderColor='#FECACA'">
                <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: {{ $isBer ? '#FEE2E2' : '#DC2626' }};">BER (BEYOND REPAIR)</span>
                <div style="font-size: 1.5rem; font-weight: 800; color: {{ $isBer ? '#FFFFFF' : '#DC2626' }};">{{ $berCount }} pcs</div>
            </div>
        </a>
    @else
        <!-- Default Disabled / Non-Clickable State (0 pcs) -->
        <div style="background: #FFFFFF; border: 1px solid var(--dftm-border); border-radius: var(--radius-md); padding: 14px 18px; cursor: default;">
            <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--dftm-slate);">ALL UNITS RECORDED</span>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--dftm-slate);">0 pcs</div>
        </div>
        <div style="background: #FFFFFF; border: 1px solid #FDE68A; border-radius: var(--radius-md); padding: 14px 18px; cursor: default;">
            <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #D97706;">IN PROCESS</span>
            <div style="font-size: 1.5rem; font-weight: 800; color: #D97706;">0 pcs</div>
        </div>
        <div style="background: #FFFFFF; border: 1px solid #A7F3D0; border-radius: var(--radius-md); padding: 14px 18px; cursor: default;">
            <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #059669;">REPAIRED (GOOD)</span>
            <div style="font-size: 1.5rem; font-weight: 800; color: #059669;">0 pcs</div>
        </div>
        <div style="background: #FFFFFF; border: 1px solid #FECACA; border-radius: var(--radius-md); padding: 14px 18px; cursor: default;">
            <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #DC2626;">BER (BEYOND REPAIR)</span>
            <div style="font-size: 1.5rem; font-weight: 800; color: #DC2626;">0 pcs</div>
        </div>
    @endif
</div>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title"><i class="bi bi-cpu-fill"></i> Repair Traceability Matrix</div>
            <div class="card-subtitle">Technical diagnostics, component replacement records, and unit repair tracking</div>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('encoder.traceability.print', request()->query()) }}" target="_blank" class="btn btn-primary">
                <i class="bi bi-printer"></i> Print Matrix Report
            </a>
        </div>
    </div>

    <!-- Filter & Outgoing Batch Selector Bar -->
    <div style="padding: 14px 20px; background: #F8FAFC; border-bottom: 1px solid var(--dftm-border);">
        <form action="{{ route('encoder.traceability.index') }}" method="GET" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center;">
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div style="flex: 1; min-width: 280px;">
                <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--dftm-navy); margin-bottom: 4px; text-transform: uppercase;">
                    <i class="bi bi-layers-fill"></i> Select Outgoing Batch / Slip
                </label>
                <select name="outgoing_slip_id" class="form-select" onchange="this.form.submit()" style="font-weight: 700; font-size: 0.92rem;">
                    <option value="" {{ empty($activeSlipId) ? 'selected' : '' }}>-- Select Outgoing Batch / Slip --</option>
                    @foreach($outgoingSlips as $slip)
                        <option value="{{ $slip->id }}" {{ $activeSlipId == $slip->id ? 'selected' : '' }}>
                            {{ $slip->batch_no ? $slip->batch_no . ' • ' : '' }}{{ $slip->slip_no }} &bull; {{ $slip->brand }} {{ $slip->model ? '(' . $slip->model . ')' : '' }} ({{ $slip->total_quantity }} pcs)
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; gap: 8px; align-self: flex-end;">
                <button type="submit" class="btn btn-primary" style="height: 38px;">
                    <i class="bi bi-funnel-fill"></i> Filter
                </button>
                @if(request()->anyFilled(['outgoing_slip_id', 'batch_id', 'status', 'tab', 'brand']))
                    <a href="{{ route('encoder.traceability.index') }}" class="btn btn-outline" style="height: 38px; color: #DC2626; border-color: #FECACA;" title="Reset all filters">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- 4 Status Tabs / Filter Buttons Bar (Only visible when a batch/slip is selected) -->
    @if(!empty($activeSlipId) || !empty($activeBatchId) || request('tab'))
    <div style="padding: 12px 20px; background: #FFFFFF; border-bottom: 1px solid var(--dftm-border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-size: 0.75rem; font-weight: 800; color: var(--dftm-slate); text-transform: uppercase; margin-right: 4px;">
                <i class="bi bi-funnel"></i> Status Filter:
            </span>

            <!-- 1. All Status Button -->
            <a href="{{ route('encoder.traceability.index', request()->except('status', 'tab', 'page')) }}" 
               class="btn btn-sm" 
               style="{{ $isAllStatus 
                    ? 'background: var(--dftm-navy); color: #FFFFFF; font-weight: 800; border: 1px solid var(--dftm-navy); box-shadow: 0 2px 6px rgba(0,32,91,0.25);' 
                    : 'background: #F1F5F9; color: var(--dftm-slate); font-weight: 700; border: 1px solid #CBD5E1;' }}">
                <i class="bi bi-grid-fill"></i> All Status
                <span style="display: inline-block; padding: 2px 7px; margin-left: 6px; border-radius: 12px; font-size: 0.75rem; background: {{ $isAllStatus ? 'rgba(255,255,255,0.25)' : '#E2E8F0' }}; color: {{ $isAllStatus ? '#FFFFFF' : 'var(--dftm-navy)' }}; font-weight: 800;">
                    {{ $repairedCount + $inProcessCount + $berCount }}
                </span>
            </a>

            <!-- 2. In Process Button -->
            <a href="{{ route('encoder.traceability.index', array_merge(request()->except('status', 'tab', 'page'), ['status' => 'In process'])) }}" 
               class="btn btn-sm" 
               style="{{ $isInProcess 
                    ? 'background: #D97706; color: #FFFFFF; font-weight: 800; border: 1px solid #D97706; box-shadow: 0 2px 6px rgba(217,119,6,0.3);' 
                    : 'background: #FEF3C7; color: #B45309; font-weight: 700; border: 1px solid #FDE68A;' }}">
                <i class="bi bi-hourglass-split"></i> In Process
                <span style="display: inline-block; padding: 2px 7px; margin-left: 6px; border-radius: 12px; font-size: 0.75rem; background: {{ $isInProcess ? 'rgba(255,255,255,0.25)' : '#FDE68A' }}; color: {{ $isInProcess ? '#FFFFFF' : '#92400E' }}; font-weight: 800;">
                    {{ $inProcessCount }}
                </span>
            </a>

            <!-- 3. Repaired Button -->
            <a href="{{ route('encoder.traceability.index', array_merge(request()->except('status', 'tab', 'page'), ['status' => 'Repaired'])) }}" 
               class="btn btn-sm" 
               style="{{ $isRepaired 
                    ? 'background: #059669; color: #FFFFFF; font-weight: 800; border: 1px solid #059669; box-shadow: 0 2px 6px rgba(5,150,105,0.3);' 
                    : 'background: #D1FAE5; color: #047857; font-weight: 700; border: 1px solid #A7F3D0;' }}">
                <i class="bi bi-check-circle-fill"></i> Repaired
                <span style="display: inline-block; padding: 2px 7px; margin-left: 6px; border-radius: 12px; font-size: 0.75rem; background: {{ $isRepaired ? 'rgba(255,255,255,0.25)' : '#A7F3D0' }}; color: {{ $isRepaired ? '#FFFFFF' : '#065F46' }}; font-weight: 800;">
                    {{ $repairedCount }}
                </span>
            </a>

            <!-- 4. BER Button -->
            <a href="{{ route('encoder.traceability.index', array_merge(request()->except('status', 'tab', 'page'), ['status' => 'BER'])) }}" 
               class="btn btn-sm" 
               style="{{ $isBer 
                    ? 'background: #DC2626; color: #FFFFFF; font-weight: 800; border: 1px solid #DC2626; box-shadow: 0 2px 6px rgba(220,38,38,0.3);' 
                    : 'background: #FEE2E2; color: #B91C1C; font-weight: 700; border: 1px solid #FECACA;' }}">
                <i class="bi bi-x-octagon-fill"></i> BER
                <span style="display: inline-block; padding: 2px 7px; margin-left: 6px; border-radius: 12px; font-size: 0.75rem; background: {{ $isBer ? 'rgba(255,255,255,0.25)' : '#FECACA' }}; color: {{ $isBer ? '#FFFFFF' : '#991B1B' }}; font-weight: 800;">
                    {{ $berCount }}
                </span>
            </a>
        </div>

        @if(request('status'))
            <div style="font-size: 0.8rem; font-weight: 700; color: var(--dftm-slate);">
                Filtering by: <span style="font-weight: 800; text-transform: uppercase; color: {{ $isInProcess ? '#D97706' : ($isRepaired ? '#059669' : '#DC2626') }};">{{ request('status') }}</span>
                <a href="{{ route('encoder.traceability.index', request()->except('status', 'page')) }}" style="margin-left: 6px; color: #DC2626; text-decoration: none;" title="Clear status filter"><i class="bi bi-x-circle-fill"></i></a>
            </div>
        @endif
    </div>
    @endif

    <!-- Excel-like Header Summary Block (Matching Reference Image) -->
    <div style="background: #FFFFFF; border-bottom: 2px solid #00205B; padding: 18px 24px;">
        <div style="text-align: center; font-size: 1.25rem; font-weight: 900; color: var(--dftm-navy); letter-spacing: 0.5px; margin-bottom: 14px; text-transform: uppercase;">
            REPAIR TRACEABILITY MATRIX
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px 30px; font-size: 0.95rem;">
            <div style="display: flex; gap: 8px;">
                <span style="font-weight: 800; color: #000000; min-width: 140px;">COMPANY NAME:</span>
                <strong style="color: var(--dftm-navy);">{{ $selectedSlip->company_name ?? ($firstItem->company_name ?? 'DFTM DIGITAL SOLUTIONS') }}</strong>
            </div>
            <div style="display: flex; gap: 8px;">
                <span style="font-weight: 800; color: #000000; min-width: 140px;">STATUS:</span>
                @php
                    $currentStatus = request('status');
                    if (!$currentStatus && $tab) {
                        if (str_ends_with($tab, '-BER')) $currentStatus = 'BER';
                        elseif (str_ends_with($tab, '-INPROCESS')) $currentStatus = 'In process';
                        elseif (str_ends_with($tab, '-REPAIRED')) $currentStatus = 'Repaired';
                    }
                    
                    if (!$currentStatus) {
                        $uniqueStatuses = $items->pluck('repair_status')->filter()->unique();
                        if ($uniqueStatuses->count() === 1) {
                            $currentStatus = $uniqueStatuses->first();
                        } elseif ($uniqueStatuses->count() > 1) {
                            $currentStatus = 'ALL';
                        } else {
                            $currentStatus = $firstItem->repair_status ?? ($items->total() > 0 ? 'In process' : '-');
                        }
                    }
                    
                    $displayStatus = strtoupper($currentStatus ?: '-');
                    $statusColor = match($displayStatus) {
                        'BER' => '#DC2626',
                        'IN PROCESS', 'IN_PROCESS', 'PENDING' => '#D97706',
                        'REPAIRED' => '#059669',
                        '-' => 'var(--dftm-slate)',
                        default => 'var(--dftm-navy)'
                    };
                @endphp
                <strong style="color: {{ $statusColor }}; font-weight: 800;">
                    {{ $displayStatus }}
                </strong>
            </div>
            <div style="display: flex; gap: 8px;">
                <span style="font-weight: 800; color: #000000; min-width: 140px;">DATE DELIVERED:</span>
                <span>{{ $selectedSlip && $selectedSlip->date_delivered ? $selectedSlip->date_delivered->format('Y-m-d') : ($firstItem && $firstItem->date_delivered ? $firstItem->date_delivered->format('Y-m-d') : '-') }}</span>
            </div>
            <div style="display: flex; gap: 8px;">
                <span style="font-weight: 800; color: #000000; min-width: 140px;">TOTAL QUANTITY:</span>
                <strong>{{ $items->total() }} PCS</strong>
            </div>
        </div>
    </div>

    <!-- Hierarchical Excel-Style Matrix Table -->
    <div class="table-responsive" style="overflow-x: auto;">
        <table class="dftm-table" style="border-collapse: collapse; width: 100%; border-top: none;">
            <thead>
                <!-- Level 1: NO., TECHNICAL DIAGNOSTIC (S), REPLACE PARTS, STATUS, MATERIAL DESCRIPTION, BOX NO., ACTION -->
                <tr style="background: #FFFFFF;">
                    <th rowspan="4" style="width: 50px; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: 800;">NO.</th>
                    <th rowspan="4" style="min-width: 190px; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: 800;">TECHNICAL DIAGNOSTIC (S)</th>
                    <th rowspan="4" style="min-width: 170px; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: 800;">REPLACE PARTS</th>
                    <th rowspan="4" style="min-width: 120px; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: 800;">STATUS</th>
                    <th colspan="2" style="text-align: center; border: 1px solid #000; font-weight: 900; letter-spacing: 0.5px; background: #FFFFFF; font-size: 0.95rem;">
                        MATERIAL DESCRIPTION
                    </th>
                    <th rowspan="4" style="width: 100px; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: 800;">BOX NO.</th>
                    <th rowspan="4" style="width: 90px; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: 800;">ACTION</th>
                </tr>
                <!-- Level 2: BATCH # 1 -->
                <tr>
                    <th colspan="2" style="text-align: center; border: 1px solid #000; font-weight: 900; font-size: 1.05rem; background: #FFFFFF; letter-spacing: 0.5px;">
                        @if($selectedSlip && $selectedSlip->batch_no)
                            {{ str_contains(strtoupper($selectedSlip->batch_no), 'BATCH') ? strtoupper($selectedSlip->batch_no) : 'BATCH # ' . $selectedSlip->batch_no }}
                        @elseif($selectedSlip)
                            {{ $selectedSlip->slip_no }}
                        @else
                            BATCH # 1
                        @endif
                    </th>
                </tr>
                <!-- Level 3: BRAND : HUAWEI | MODEL: ... -->
                <tr>
                    <th style="text-align: center; border: 1px solid #000; font-weight: 800; background: #FFFFFF; width: 25%;">
                        BRAND : {{ strtoupper($selectedSlip->brand ?? ($firstItem->brand ?? 'HUAWEI')) }}
                    </th>
                    <th style="text-align: center; border: 1px solid #000; font-weight: 800; background: #FFFFFF; width: 25%;">
                        MODEL: {{ strtoupper($selectedSlip->model ?? ($firstItem->model ?? '')) }}
                    </th>
                </tr>
                <!-- Level 4: SERIAL NUMBER | MAC ADDRESS -->
                <tr style="background: #FFFFFF;">
                    <th style="text-align: center; border: 1px solid #000; font-weight: 800;">SERIAL NUMBER</th>
                    <th style="text-align: center; border: 1px solid #000; font-weight: 800;">MAC ADDRESS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr style="cursor: pointer;" onclick="openTraceabilityModal({{ json_encode($item) }})" title="Click row to update diagnostics & status">
                    <td style="text-align: center; font-weight: 800; border: 1px solid #000;">{{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}</td>
                    <td style="border: 1px solid #000; font-weight: 700; color: #00205B;">
                        {{ $item->technical_diagnostic ?? '' }}
                    </td>
                    <td style="border: 1px solid #000; font-weight: 600;">
                        {{ $item->replace_parts ?? '' }}
                    </td>
                    <td style="text-align: center; border: 1px solid #000;">
                        @if($item->repair_status === 'Repaired')
                            <span style="font-weight: 800; color: #059669;">REPAIRED</span>
                        @elseif($item->repair_status === 'BER')
                            <span style="font-weight: 800; color: #DC2626;">BER</span>
                        @else
                            <span style="font-weight: 700; color: #D97706;">{{ $item->repair_status ?? 'In process' }}</span>
                        @endif
                    </td>
                    <td style="border: 1px solid #000; font-family: 'Consolas', monospace; font-weight: 700; color: var(--dftm-navy);">
                        {{ $item->serial_number ?? '' }}
                    </td>
                    <td style="border: 1px solid #000; font-family: 'Consolas', monospace;">
                        {{ $item->mac_address ?? '' }}
                    </td>
                    <td style="text-align: center; border: 1px solid #000;">
                        {{ $item->box_no ?? '' }}
                    </td>
                    <td style="text-align: center; border: 1px solid #000;" onclick="event.stopPropagation();">
                        <button type="button" class="btn btn-outline btn-sm" onclick="openTraceabilityModal({{ json_encode($item) }})" title="Update Item">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 48px 20px; border: 1px solid #000;">
                        @if(request('tab') || request('status') || request('outgoing_slip_id'))
                            <div style="font-weight: 700; color: var(--dftm-slate); font-size: 0.95rem;">
                                No repair traceability records found for this selection.
                            </div>
                        @else
                            <div style="font-weight: 800; color: var(--dftm-navy); font-size: 1.05rem; margin-bottom: 6px;">
                                <i class="bi bi-arrow-up-circle-fill" style="color: #00205B; font-size: 1.3rem; vertical-align: middle; margin-right: 6px;"></i>
                                PUMILI NG OUTGOING BATCH SA ITAAS O TABS SA IBABA
                            </div>
                            <div style="font-size: 0.88rem; color: var(--dftm-slate); font-weight: 600;">
                                Gamitin ang <strong>Select Outgoing Batch</strong> dropdown sa itaas o pumili ng batch tab sa ilalim upang maipakita ang matrix.
                            </div>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($items->hasPages())
    <div class="card-footer" style="background: #F8FAFC; border-top: 1px solid var(--dftm-border);">
        {{ $items->links() }}
    </div>
    @endif



<!-- Traceability Edit Modal -->
<div class="modal-backdrop" id="traceabilityModal">
    <div class="modal-card">
        <form id="traceabilityForm" method="POST" data-base-action="{{ url('encoder/traceability/__ID__') }}">
            @csrf
            @method('PUT')
            
            <div class="modal-header">
                <div class="modal-title"><i class="bi bi-cpu-fill"></i> Update Unit Traceability & Diagnostic</div>
                <button type="button" class="modal-close" data-modal-close><i class="bi bi-x-lg"></i></button>
            </div>

            <div class="modal-body">
                <div style="background: #F8FAFC; padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 18px; border: 1px solid var(--dftm-border);">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 0.85rem;">
                        <div>SERIAL: <strong class="mono" id="modalSerial"></strong></div>
                        <div>MAC: <strong class="mono" id="modalMac"></strong></div>
                        <div>BRAND: <strong id="modalBrand"></strong></div>
                        <div>MODEL: <strong id="modalModel"></strong></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Technical Diagnostic(s)</label>
                    <input type="text" name="technical_diagnostic" id="modalDiag" list="diagnosticList" class="form-control" placeholder="e.g. NO POWER / CORRODED BOARD / OPTICAL LOSS">
                    <datalist id="diagnosticList">
                        <option value="NO POWER">
                        <option value="NO LAN / PORT DEFECTIVE">
                        <option value="NO WIFI SIGNAL">
                        <option value="LOS / OPTICAL RX LOSS">
                        <option value="CORRODED BOARD / WATER DAMAGE">
                        <option value="OVERHEATING / REBOOTING">
                        <option value="FIRMWARE CORRUPTED">
                        <option value="GOOD / TESTED OK">
                    </datalist>
                </div>

                <div class="form-group">
                    <label class="form-label">Replace Parts</label>
                    <input type="text" name="replace_parts" id="modalParts" list="partsList" class="form-control" placeholder="e.g. SMD CAPACITOR / DC JACK / POWER IC">
                    <datalist id="partsList">
                        <option value="SMD CAPACITOR">
                        <option value="DC JACK / CONNECTOR">
                        <option value="POWER IC / PMIC">
                        <option value="WIFI IC">
                        <option value="FLASH MEMORY / EEPROM">
                        <option value="TRANSCEIVER MODULE">
                        <option value="CLEANING / REFLOW">
                        <option value="N/A">
                    </datalist>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="repair_status" id="modalStatus" class="form-select" style="font-weight: 700;">
                            <option value="Repaired" style="color: #059669; font-weight: 700;">REPAIRED (Good / Replaced & Tested)</option>
                            <option value="In process" style="color: #D97706; font-weight: 700;">In process (Under Diagnostic / Repair)</option>
                            <option value="BER" style="color: #DC2626; font-weight: 700;">BER (Beyond Economic Repair)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Box No.</label>
                        <input type="text" name="box_no" id="modalBox" class="form-control" placeholder="e.g. BOX 1">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Technical Remarks / Notes</label>
                    <textarea name="notes" id="modalNotes" class="form-control" rows="2" placeholder="Technician notes..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2-circle"></i> Save Traceability Record
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openTraceabilityModal(item) {
    const modal = document.getElementById('traceabilityModal');
    const form = document.getElementById('traceabilityForm');
    if (!modal || !form) return;

    form.action = form.dataset.baseAction.replace('__ID__', item.id);

    document.getElementById('modalSerial').textContent = item.serial_number || 'N/A';
    document.getElementById('modalMac').textContent = item.mac_address || 'N/A';
    document.getElementById('modalBrand').textContent = item.brand || 'N/A';
    document.getElementById('modalModel').textContent = item.model || 'N/A';
    document.getElementById('modalDiag').value = item.technical_diagnostic || '';
    document.getElementById('modalParts').value = item.replace_parts || '';
    document.getElementById('modalStatus').value = item.repair_status || 'In process';
    document.getElementById('modalBox').value = item.box_no || '';
    document.getElementById('modalNotes').value = item.notes || '';

    modal.classList.add('active');
}
</script>
@endsection
