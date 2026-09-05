@extends('layouts.app')

@section('title', 'Inventory Stock Status')
@section('page_title', 'Warehouse Inventory Stock Status')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title"><i class="bi bi-archive-fill"></i> Warehouse Inventory</div>
            <div class="card-subtitle">Check serialized product availability and location</div>
        </div>
    </div>

    <!-- Search Bar -->
    <div style="padding: 16px 24px; background: #F8FAFC; border-bottom: 1px solid var(--dftm-border);">
        <form action="{{ route('encoder.inventory.index') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 240px;">
                <input type="text" name="search" class="form-control" placeholder="Search Serial Number, MAC, Brand, Model, Box No, Batch..." value="{{ request('search') }}">
            </div>
            <div style="width: 150px;">
                <select name="batch_id" class="form-select">
                    <option value="">All Batches</option>
                    @foreach($batches as $b)
                        <option value="{{ $b->id }}" {{ request('batch_id') == $b->id ? 'selected' : '' }}>{{ $b->batch_no }}</option>
                    @endforeach
                </select>
            </div>
            <div style="width: 150px;">
                <select name="stock_status" class="form-select">
                    <option value="">Stock Status</option>
                    <option value="IN_STOCK" {{ request('stock_status') === 'IN_STOCK' ? 'selected' : '' }}>IN STOCK</option>
                    <option value="RELEASED" {{ request('stock_status') === 'RELEASED' ? 'selected' : '' }}>RELEASED</option>
                </select>
            </div>
            <button type="submit" class="btn btn-outline"><i class="bi bi-search"></i> Filter</button>
            @if(request()->anyFilled(['search', 'batch_id', 'stock_status']))
                <a href="{{ route('encoder.inventory.index') }}" class="btn btn-outline" style="color: #DC2626;">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="dftm-table">
            <thead>
                <tr>
                    <th>Serial Number</th>
                    <th>MAC Address</th>
                    <th>Batch</th>
                    <th>Brand & Model</th>
                    <th>Box No</th>
                    <th>Diagnostic</th>
                    <th>Repair Status</th>
                    <th>Stock Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td><span class="mono" style="font-weight: 700; color: var(--dftm-navy);">{{ $item->serial_number ?? '-' }}</span></td>
                    <td><span class="mono">{{ $item->mac_address ?? '-' }}</span></td>
                    <td><span class="badge badge-stock">{{ $item->batch->batch_no ?? 'BATCH' }}</span></td>
                    <td><strong>{{ $item->brand }}</strong> {{ $item->model }}</td>
                    <td>{{ $item->box_no ?? '-' }}</td>
                    <td><small>{{ $item->technical_diagnostic ?? '-' }}</small></td>
                    <td>
                        <span class="badge {{ $item->repair_status === 'Repaired' ? 'badge-repaired' : ($item->repair_status === 'BER' ? 'badge-ber' : 'badge-in-process') }}">
                            {{ $item->repair_status ?? 'In process' }}
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
                    <td colspan="8" style="text-align: center; color: var(--dftm-slate); padding: 32px;">No inventory items found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($items->hasPages())
    <div class="card-footer">
        {{ $items->links() }}
    </div>
    @endif
</div>
@endsection
