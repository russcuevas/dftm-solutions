@extends('layouts.app')

@section('title', 'Consumable Inventory Ledger')
@section('page_title', 'Consumable Inventory')

@section('content')
<!-- Header Section -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--dftm-navy); margin: 0;">Consumables & Supplies Monthly Ledger</h2>
        <p style="font-size: 0.85rem; color: var(--dftm-slate); margin: 4px 0 0 0;">Daily stock in/out ledger, beginning stocks, remaining balance, and valuation for <strong>{{ $monthName }}</strong></p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
        <button type="button" class="btn btn-accent" onclick="openLogModal()">
            <i class="bi bi-plus-circle-fill"></i> Log Daily In / Out
        </button>
        <button type="button" class="btn btn-outline" onclick="openBeginningModal()">
            <i class="bi bi-calendar-check"></i> Set Beginning Stock
        </button>
        <form action="{{ route('admin.consumables.carryover') }}" method="POST" style="margin: 0; display: inline-block;"
              onsubmit="return confirm('Are you sure you want to carry over the remaining stocks from {{ \Carbon\Carbon::createFromDate($year, $month, 1)->subMonth()->format('F Y') }} as Beginning Stocks for {{ $monthName }}?');">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <button type="submit" class="btn btn-outline" style="color: var(--dftm-accent); border-color: rgba(2, 132, 199, 0.35);" title="Automatically rollover remaining stocks into {{ $monthName }}">
                <i class="bi bi-arrow-repeat"></i> Carry Over Last Month
            </button>
        </form>
        <a href="{{ route('admin.consumables.print', ['year' => $year, 'month' => $month]) }}" target="_blank" class="btn btn-outline">
            <i class="bi bi-printer"></i> Print Sheet
        </a>
    </div>
</div>

<!-- Summary Metrics Bar -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: #FFFFFF; border: 1px solid var(--dftm-border); border-radius: var(--radius-md); padding: 16px 20px;">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--dftm-slate); letter-spacing: 0.5px;">ACTIVE ITEMS</span>
        <div style="font-size: 1.5rem; font-weight: 800; color: var(--dftm-navy); margin-top: 4px;">{{ number_format($totalItemsCount) }}</div>
    </div>

    <div style="background: #FFFFFF; border: 1px solid var(--dftm-border); border-radius: var(--radius-md); padding: 16px 20px;">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--dftm-slate); letter-spacing: 0.5px;">BEGINNING STOCKS</span>
        <div style="font-size: 1.5rem; font-weight: 800; color: #475569; margin-top: 4px;">{{ number_format($totalBeginningSum) }}</div>
    </div>

    <div style="background: #FFFFFF; border: 1px solid #A7F3D0; border-radius: var(--radius-md); padding: 16px 20px;">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #065F46; letter-spacing: 0.5px;">TOTAL IN (MONTH)</span>
        <div style="font-size: 1.5rem; font-weight: 800; color: #059669; margin-top: 4px;">+{{ number_format($totalInSum) }}</div>
    </div>

    <div style="background: #FFFFFF; border: 1px solid #FECACA; border-radius: var(--radius-md); padding: 16px 20px;">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #991B1B; letter-spacing: 0.5px;">TOTAL OUT (CONSUMED)</span>
        <div style="font-size: 1.5rem; font-weight: 800; color: #DC2626; margin-top: 4px;">-{{ number_format($totalOutSum) }}</div>
    </div>

    <div style="background: #FFFFFF; border: 1px solid #BFDBFE; border-radius: var(--radius-md); padding: 16px 20px;">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #1E40AF; letter-spacing: 0.5px;">REMAINING STOCK</span>
        <div style="font-size: 1.5rem; font-weight: 800; color: #2563EB; margin-top: 4px;">{{ number_format($totalRemainingSum) }}</div>
    </div>

    <div style="background: #FFFFFF; border: 1px solid #FDE68A; border-radius: var(--radius-md); padding: 16px 20px;">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #92400E; letter-spacing: 0.5px;">INVENTORY VALUATION</span>
        <div style="font-size: 1.5rem; font-weight: 800; color: #D97706; margin-top: 4px;">₱{{ number_format($totalValuation, 2) }}</div>
    </div>
</div>

<!-- Main Matrix Card -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div class="card-title"><i class="bi bi-grid-3x3-gap-fill"></i> Monthly Consumable Ledger Matrix</div>
            <div class="card-subtitle">Showing <strong>{{ $monthName }}</strong> ({{ $daysInMonth }} Days)</div>
        </div>
        <div>
            <span class="badge badge-stock">{{ $daysInMonth }} Days</span>
        </div>
    </div>

    <!-- Month Picker & Search Toolbar -->
    <div style="padding: 16px 24px; background: #F8FAFC; border-bottom: 1px solid var(--dftm-border);">
        <form method="GET" action="{{ route('admin.consumables.index') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <!-- Month Navigator -->
                <div style="display: flex; align-items: center; gap: 4px; background: #FFFFFF; padding: 4px; border: 1px solid var(--dftm-border); border-radius: var(--radius-md);">
                    @php
                        $prevMonthDate = \Carbon\Carbon::createFromDate($year, $month, 1)->subMonth();
                        $nextMonthDate = \Carbon\Carbon::createFromDate($year, $month, 1)->addMonth();
                    @endphp
                    <a href="{{ route('admin.consumables.index', ['year' => $prevMonthDate->year, 'month' => $prevMonthDate->month, 'category_id' => $categoryId, 'search' => $search]) }}" 
                       class="btn btn-outline btn-icon" style="padding: 4px 8px; font-size: 0.8rem;" title="Previous Month">
                        <i class="bi bi-chevron-left"></i>
                    </a>

                    <select name="month" class="form-select" style="width: 130px; font-size: 0.85rem; padding: 6px 10px;" onchange="this.form.submit()">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(2000, $m, 1)->format('F') }}
                            </option>
                        @endfor
                    </select>

                    <select name="year" class="form-select" style="width: 95px; font-size: 0.85rem; padding: 6px 10px;" onchange="this.form.submit()">
                        @for($y = 2024; $y <= 2030; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>

                    <a href="{{ route('admin.consumables.index', ['year' => $nextMonthDate->year, 'month' => $nextMonthDate->month, 'category_id' => $categoryId, 'search' => $search]) }}" 
                       class="btn btn-outline btn-icon" style="padding: 4px 8px; font-size: 0.8rem;" title="Next Month">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>

                <!-- Category Filter -->
                <div style="width: 180px;">
                    <select name="category_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($allCategoriesList as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Search -->
                <div style="width: 200px;">
                    <input type="text" name="search" class="form-control" placeholder="Search item or unit..." value="{{ $search }}">
                </div>

                <button type="submit" class="btn btn-outline"><i class="bi bi-funnel"></i> Filter</button>

                @if($categoryId || $search || $month != now()->month || $year != now()->year)
                    <a href="{{ route('admin.consumables.index') }}" class="btn btn-outline" style="color: #DC2626;">Reset</a>
                @endif
            </div>

            <div style="font-size: 0.82rem; color: var(--dftm-slate); font-weight: 600;">
                Active Sheet: <strong style="color: var(--dftm-navy);">{{ $monthName }}</strong>
            </div>
        </form>
    </div>

    <!-- Matrix Table -->
    <div class="consumable-table-wrapper">
        <table class="consumable-matrix-table">
            <thead>
                <!-- Row 1 Headers -->
                <tr>
                    <th class="sticky-col col-desc" rowspan="2">DESCRIPTION</th>
                    <th class="sticky-col col-unit" rowspan="2">UNIT</th>
                    <th class="sticky-col col-cost" rowspan="2">COST</th>
                    <th class="sticky-col col-beg" rowspan="2">BEGINNING</th>
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $colDate = \Carbon\Carbon::createFromDate($year, $month, $d);
                            $isToday = $colDate->isToday();
                            $isWeekend = $colDate->isWeekend();
                        @endphp
                        <th colspan="2" class="day-header {{ $isToday ? 'col-today' : '' }} {{ $isWeekend ? 'col-weekend' : '' }}">
                            {{ $d }}-{{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('M') }}
                        </th>
                    @endfor
                    <th class="summary-header col-total-in" rowspan="2">TOTAL IN</th>
                    <th class="summary-header col-total-out" rowspan="2">TOTAL OUT</th>
                    <th class="summary-header col-rem" rowspan="2">REMAINING</th>
                    <th class="summary-header col-amount" rowspan="2">AMOUNT</th>
                    <th class="summary-header col-action" rowspan="2">LOG</th>
                </tr>
                <!-- Row 2 In/Out Subheaders -->
                <tr>
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        <th class="sub-col-in">IN</th>
                        <th class="sub-col-out">OUT</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @forelse($matrixData as $catId => $data)
                    @if(count($data['items']) > 0)
                        <!-- Category Header Row -->
                        <tr class="category-header-row">
                            <td colspan="{{ 4 + ($daysInMonth * 2) + 5 }}" class="category-heading-cell">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <i class="bi bi-folder2-open" style="color: var(--dftm-accent);"></i>
                                    <span style="letter-spacing: 0.5px;">{{ $data['category']->name }}</span>
                                    <span class="badge badge-stock" style="font-size: 0.68rem;">{{ count($data['items']) }} items</span>
                                </div>
                            </td>
                        </tr>

                        @foreach($data['items'] as $itemRow)
                            @php
                                $item = $itemRow['item'];
                                $beg = $itemRow['beginning'];
                                $totIn = $itemRow['total_in'];
                                $totOut = $itemRow['total_out'];
                                $rem = $itemRow['remaining'];
                                $amt = $itemRow['amount'];
                                $dailyLogs = $itemRow['daily'];
                            @endphp
                            <tr class="item-row {{ $rem <= 0 ? 'row-zero-stock' : '' }}">
                                <!-- Sticky Columns -->
                                <td class="sticky-col col-desc item-name-cell">
                                    <div class="item-title" title="{{ $item->name }}">{{ $item->name }}</div>
                                </td>
                                <td class="sticky-col col-unit">
                                    <span class="badge-unit">{{ $item->unit }}</span>
                                </td>
                                <td class="sticky-col col-cost text-right mono">
                                    {{ $item->cost > 0 ? '₱' . number_format($item->cost, 2) : '—' }}
                                </td>
                                <td class="sticky-col col-beg text-center mono" 
                                    onclick="openQuickBeginningModal({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $beg }})"
                                    title="Click to update beginning stock">
                                    <span class="editable-val">{{ $beg > 0 ? number_format($beg) : '0' }}</span>
                                </td>

                                <!-- 1..31 Days In/Out Columns -->
                                @for($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $inVal = isset($dailyLogs[$d]['in']) && $dailyLogs[$d]['in'] > 0 ? $dailyLogs[$d]['in'] : '';
                                        $outVal = isset($dailyLogs[$d]['out']) && $dailyLogs[$d]['out'] > 0 ? $dailyLogs[$d]['out'] : '';
                                        $dayDate = sprintf('%04d-%02d-%02d', $year, $month, $d);
                                    @endphp
                                    <td class="day-cell day-in text-center mono {{ $inVal ? 'has-in' : '' }}"
                                        onclick="openQuickDailyLogModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $dayDate }}', 'in')"
                                        title="Click to log IN for Day {{ $d }}">
                                        {{ $inVal }}
                                    </td>
                                    <td class="day-cell day-out text-center mono {{ $outVal ? 'has-out' : '' }}"
                                        onclick="openQuickDailyLogModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $dayDate }}', 'out')"
                                        title="Click to log OUT for Day {{ $d }}">
                                        {{ $outVal }}
                                    </td>
                                @endfor

                                <!-- Summary Right Columns -->
                                <td class="summary-cell text-center mono total-in-val">
                                    {{ number_format($totIn) }}
                                </td>
                                <td class="summary-cell text-center mono total-out-val">
                                    {{ $totOut > 0 ? number_format($totOut) : '0' }}
                                </td>
                                <td class="summary-cell text-center mono remaining-val {{ $rem <= 0 ? 'text-danger' : 'text-primary' }}" style="font-weight: 800;">
                                    {{ number_format($rem) }}
                                </td>
                                <td class="summary-cell text-right mono amount-val">
                                    {{ $amt > 0 ? '₱' . number_format($amt, 2) : '₱0.00' }}
                                </td>
                                <td class="summary-cell text-center">
                                    <button type="button" class="btn btn-outline btn-icon" style="padding: 4px 6px; font-size: 0.75rem;" 
                                        title="Log Stock Movement"
                                        onclick="openQuickDailyLogModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ date('Y-m-d') }}')">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                @empty
                    <tr>
                        <td colspan="{{ 4 + ($daysInMonth * 2) + 5 }}" style="text-align: center; padding: 48px; color: var(--dftm-slate);">
                            <i class="bi bi-boxes" style="font-size: 2.5rem; display: block; margin-bottom: 8px;"></i>
                            No consumable items found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="grand-total-row">
                    <td class="sticky-col col-desc" style="font-weight: 800; letter-spacing: 0.5px;">TOTAL SUMMARY</td>
                    <td class="sticky-col col-unit"></td>
                    <td class="sticky-col col-cost"></td>
                    <td class="sticky-col col-beg text-center mono" style="font-weight: 800;">{{ number_format($totalBeginningSum) }}</td>
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        <td class="day-cell sub-col-in"></td>
                        <td class="day-cell sub-col-out"></td>
                    @endfor
                    <td class="summary-cell text-center mono" style="font-weight: 800; color: #34D399;">{{ number_format($totalInSum) }}</td>
                    <td class="summary-cell text-center mono" style="font-weight: 800; color: #F87171;">{{ number_format($totalOutSum) }}</td>
                    <td class="summary-cell text-center mono" style="font-weight: 800; color: #60A5FA;">{{ number_format($totalRemainingSum) }}</td>
                    <td class="summary-cell text-right mono" style="font-weight: 800; color: #FBBF24;">₱{{ number_format($totalValuation, 2) }}</td>
                    <td class="summary-cell"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Modal: Quick Daily Log -->
<div class="modal-backdrop-custom" id="dailyLogModal" style="display: none;">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h4 class="modal-title-custom" id="dailyLogModalTitle">
                <i class="bi bi-clock-history text-primary"></i> Log Daily Stock In / Out
            </h4>
            <button type="button" class="btn-close-custom" onclick="closeLogModal()">&times;</button>
        </div>
        <form action="{{ route('admin.consumables.dailyLog.store') }}" method="POST">
            @csrf
            <div class="modal-body-custom">
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Consumable Item <span style="color: #DC2626;">*</span></label>
                    <select name="consumable_item_id" id="logItemSelect" class="form-select" required>
                        <option value="">-- Select Item --</option>
                        @foreach($allCategoriesList as $cat)
                            <optgroup label="{{ $cat->name }}">
                                @foreach($cat->items as $it)
                                    <option value="{{ $it->id }}">{{ $it->name }} ({{ $it->unit }})</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Transaction Date <span style="color: #DC2626;">*</span></label>
                    <input type="date" name="log_date" id="logDateInput" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                    <div>
                        <label style="font-size: 0.82rem; font-weight: 700; color: #059669; display: block; margin-bottom: 6px;">
                            <i class="bi bi-box-arrow-in-down"></i> Stock IN Qty
                        </label>
                        <input type="number" name="in_qty" id="logInQty" class="form-control" min="0" placeholder="0" value="0">
                        <span style="font-size: 0.72rem; color: var(--dftm-slate);">Arrival / Delivery</span>
                    </div>
                    <div>
                        <label style="font-size: 0.82rem; font-weight: 700; color: #DC2626; display: block; margin-bottom: 6px;">
                            <i class="bi bi-box-arrow-up-right"></i> Stock OUT Qty
                        </label>
                        <input type="number" name="out_qty" id="logOutQty" class="form-control" min="0" placeholder="0" value="0">
                        <span style="font-size: 0.72rem; color: var(--dftm-slate);">Used / Dispensed</span>
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Reference No. (Optional)</label>
                    <input type="text" name="reference_no" class="form-control" placeholder="e.g. PO-2026-091 or SLIP #45">
                </div>

                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Remarks / Purpose (Optional)</label>
                    <input type="text" name="remarks" class="form-control" placeholder="e.g. Issued to Production Line 1">
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeLogModal()">Cancel</button>
                <button type="submit" class="btn btn-accent">
                    <i class="bi bi-check2-circle"></i> Save Transaction
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Set Beginning Stock -->
<div class="modal-backdrop-custom" id="beginningModal" style="display: none;">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h4 class="modal-title-custom">
                <i class="bi bi-calendar-check text-primary"></i> Set Beginning Inventory Stock
            </h4>
            <button type="button" class="btn-close-custom" onclick="closeBeginningModal()">&times;</button>
        </div>
        <form action="{{ route('admin.consumables.beginning.update') }}" method="POST">
            @csrf
            <div class="modal-body-custom">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">

                <div style="margin-bottom: 16px;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Month & Year</label>
                    <input type="text" class="form-control" value="{{ $monthName }}" readonly disabled>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Consumable Item <span style="color: #DC2626;">*</span></label>
                    <select name="consumable_item_id" id="begItemSelect" class="form-select" required>
                        <option value="">-- Select Item --</option>
                        @foreach($allCategoriesList as $cat)
                            <optgroup label="{{ $cat->name }}">
                                @foreach($cat->items as $it)
                                    <option value="{{ $it->id }}">{{ $it->name }} ({{ $it->unit }})</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--dftm-navy); display: block; margin-bottom: 6px;">Beginning Stock Quantity <span style="color: #DC2626;">*</span></label>
                    <input type="number" name="beginning_stock" id="begStockQty" class="form-control" min="0" placeholder="0" required>
                    <span style="font-size: 0.72rem; color: var(--dftm-slate);">Initial stock count at the beginning of {{ $monthName }}</span>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeBeginningModal()">Cancel</button>
                <button type="submit" class="btn btn-accent">
                    <i class="bi bi-check-lg"></i> Update Beginning Stock
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.consumable-table-wrapper {
    overflow-x: auto;
    max-height: 72vh;
    position: relative;
    background: #FFFFFF;
}

.consumable-matrix-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.8rem;
}

.consumable-matrix-table th, 
.consumable-matrix-table td {
    padding: 7px 10px;
    border-right: 1px solid var(--dftm-border);
    border-bottom: 1px solid var(--dftm-border);
    white-space: nowrap;
}

.consumable-matrix-table thead th {
    background-color: var(--dftm-navy);
    color: #FFFFFF;
    font-weight: 700;
    text-align: center;
    position: sticky;
    top: 0;
    z-index: 10;
}

.consumable-matrix-table thead tr:nth-child(2) th {
    top: 31px;
    font-size: 0.7rem;
    padding: 3px 6px;
    background-color: var(--dftm-navy-dark);
}

.sub-col-in {
    background-color: #064E3B !important;
    color: #A7F3D0 !important;
    min-width: 32px;
}

.sub-col-out {
    background-color: #7F1D1D !important;
    color: #FECACA !important;
    min-width: 32px;
}

.day-header {
    font-size: 0.72rem;
    font-weight: 700;
    min-width: 64px;
}

.col-today {
    background-color: var(--dftm-accent) !important;
    color: #FFFFFF !important;
}

.col-weekend {
    background-color: #0B192C !important;
}

/* Sticky Left Columns */
.sticky-col {
    position: sticky;
    z-index: 5;
    background: #FFFFFF;
}

thead .sticky-col {
    z-index: 20 !important;
    background: var(--dftm-navy) !important;
}

.col-desc {
    left: 0;
    min-width: 220px;
    max-width: 240px;
    font-weight: 700;
    color: var(--dftm-navy);
    overflow: hidden;
    text-overflow: ellipsis;
}

.col-unit {
    left: 220px;
    min-width: 75px;
    text-align: center;
}

.col-cost {
    left: 295px;
    min-width: 80px;
}

.col-beg {
    left: 375px;
    min-width: 85px;
    cursor: pointer;
    box-shadow: 2px 0 5px rgba(0,0,0,0.04);
}

.col-beg:hover {
    background-color: #EFF6FF !important;
}

.badge-unit {
    background-color: #F1F5F9;
    color: #475569;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 700;
    border: 1px solid #CBD5E1;
}

.category-header-row {
    background-color: #F8FAFC !important;
}

.category-heading-cell {
    padding: 8px 14px !important;
    font-weight: 800;
    color: var(--dftm-navy);
    font-size: 0.85rem;
    border-top: 2px solid var(--dftm-border) !important;
    border-bottom: 2px solid var(--dftm-border) !important;
    position: sticky;
    left: 0;
    background: #F1F5F9 !important;
    z-index: 4;
}

.item-row:hover {
    background-color: #F8FAFC;
}

.item-row:hover .sticky-col {
    background-color: #F8FAFC;
}

.day-cell {
    font-size: 0.78rem;
    cursor: pointer;
    min-width: 32px;
}

.day-cell:hover {
    background-color: #E2E8F0 !important;
}

.day-in.has-in {
    background-color: #ECFDF5;
    color: #059669;
    font-weight: 800;
}

.day-out.has-out {
    background-color: #FEF2F2;
    color: #DC2626;
    font-weight: 800;
}

.summary-header {
    background-color: var(--dftm-navy-dark) !important;
    color: var(--dftm-accent-light) !important;
    font-size: 0.75rem;
    font-weight: 800;
}

.col-total-in { min-width: 80px; }
.col-total-out { min-width: 80px; }
.col-rem { min-width: 85px; }
.col-amount { min-width: 100px; }
.col-action { min-width: 45px; }

.summary-cell {
    background-color: #F8FAFC;
    font-size: 0.82rem;
}

.total-in-val { color: #059669; font-weight: 700; }
.total-out-val { color: #DC2626; font-weight: 700; }
.amount-val { font-weight: 800; color: var(--dftm-navy); }

.grand-total-row {
    background-color: var(--dftm-navy) !important;
    color: #FFFFFF !important;
    position: sticky;
    bottom: 0;
    z-index: 15;
}

.grand-total-row .sticky-col {
    background-color: var(--dftm-navy) !important;
    color: #FFFFFF !important;
}

.grand-total-row .summary-cell {
    background-color: var(--dftm-navy-dark) !important;
}

/* Modals */
.modal-backdrop-custom {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 18, 53, 0.65);
    backdrop-filter: blur(4px);
    z-index: 999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-dialog-custom {
    background: #FFFFFF;
    border-radius: var(--radius-lg);
    width: 95%;
    max-width: 500px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25);
    overflow: hidden;
}

.modal-header-custom {
    padding: 16px 22px;
    background: #F8FAFC;
    border-bottom: 1px solid var(--dftm-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title-custom {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--dftm-navy);
    margin: 0;
}

.btn-close-custom {
    background: transparent;
    border: none;
    font-size: 1.5rem;
    line-height: 1;
    cursor: pointer;
    color: var(--dftm-slate);
}

.modal-body-custom {
    padding: 22px;
}

.modal-footer-custom {
    padding: 14px 22px;
    background: #F8FAFC;
    border-top: 1px solid var(--dftm-border);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
</style>
@endpush

@push('scripts')
<script>
function openLogModal() {
    document.getElementById('dailyLogModal').style.display = 'flex';
}

function closeLogModal() {
    document.getElementById('dailyLogModal').style.display = 'none';
}

function openBeginningModal() {
    document.getElementById('beginningModal').style.display = 'flex';
}

function closeBeginningModal() {
    document.getElementById('beginningModal').style.display = 'none';
}

function openQuickDailyLogModal(itemId, itemName, logDate, type = 'out') {
    document.getElementById('logItemSelect').value = itemId;
    document.getElementById('logDateInput').value = logDate;
    
    if (type === 'in') {
        document.getElementById('logInQty').value = 1;
        document.getElementById('logOutQty').value = 0;
        document.getElementById('logInQty').focus();
    } else {
        document.getElementById('logInQty').value = 0;
        document.getElementById('logOutQty').value = 1;
        document.getElementById('logOutQty').focus();
    }
    
    document.getElementById('dailyLogModalTitle').innerHTML = '<i class="bi bi-clock-history text-primary"></i> Log Daily Stock for <strong>' + itemName + '</strong>';
    openLogModal();
}

function openQuickBeginningModal(itemId, itemName, currentVal) {
    document.getElementById('begItemSelect').value = itemId;
    document.getElementById('begStockQty').value = currentVal;
    openBeginningModal();
    document.getElementById('begStockQty').focus();
}

window.onclick = function(event) {
    const logModal = document.getElementById('dailyLogModal');
    const begModal = document.getElementById('beginningModal');
    if (event.target == logModal) closeLogModal();
    if (event.target == begModal) closeBeginningModal();
}
</script>
@endpush
