@extends('layouts.app')

@section('title', 'Company Folders & Client Dashboards')
@section('page_title', 'Client Company Folders')

@section('content')
<style>
.stat-grid-4 {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
@media (max-width: 1200px) {
    .stat-grid-4 {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 640px) {
    .stat-grid-4 {
        grid-template-columns: 1fr;
    }
}

.company-card-row:hover {
    background-color: #F8FAFC !important;
}

.company-badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 9999px;
    font-size: 0.78rem;
    font-weight: 700;
}
</style>

<!-- High-level Overall Company Metrics -->
<div class="stat-grid-4">
    <div class="stat-card" style="--stat-color: #0284C7; --stat-bg: #E0F2FE;">
        <div class="stat-header">
            <span class="stat-label">Client Companies</span>
            <div class="stat-icon"><i class="bi bi-buildings"></i></div>
        </div>
        <div class="stat-value">{{ number_format($summary->total_companies) }}</div>
        <div class="stat-desc">Registered company folders</div>
    </div>

    <div class="stat-card" style="--stat-color: #00205B; --stat-bg: #E0E7FF;">
        <div class="stat-header">
            <span class="stat-label">Total Transmittals</span>
            <div class="stat-icon"><i class="bi bi-box-arrow-in-down"></i></div>
        </div>
        <div class="stat-value">{{ number_format($summary->total_transmittals) }}</div>
        <div class="stat-desc">Across all client companies</div>
    </div>

    <div class="stat-card" style="--stat-color: #6366F1; --stat-bg: #EEF2FF;">
        <div class="stat-header">
            <span class="stat-label">Total Units</span>
            <div class="stat-icon"><i class="bi bi-cpu-fill"></i></div>
        </div>
        <div class="stat-value">{{ number_format($summary->total_units) }}</div>
        <div class="stat-desc">Total client inventory volume</div>
    </div>

    <div class="stat-card" style="--stat-color: #059669; --stat-bg: #D1FAE5;">
        <div class="stat-header">
            <span class="stat-label">Total Repaired</span>
            <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
        </div>
        <div class="stat-value">{{ number_format($summary->total_repaired) }}</div>
        <div class="stat-desc">Repaired OK client units</div>
    </div>
</div>

<!-- Main Table Card: All Company Folders -->
<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 14px;">
        <div>
            <div class="card-title" style="display: flex; align-items: center; gap: 10px;">
                <i class="bi bi-folder2-open" style="color: var(--dftm-accent);"></i> Company Folders & Individual Dashboards
            </div>
            <div class="card-subtitle">
                Select a client company to open its dedicated Flow Metrics, Diagnostic Matrix, and Inventory Dashboard
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <form method="GET" action="{{ route('admin.companies.index') }}" style="display: flex; gap: 8px;">
                <div style="position: relative;">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Search company or client..." value="{{ request('search') }}"
                        style="width: 250px; padding-left: 32px;">
                    <i class="bi bi-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--dftm-slate);"></i>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
                @if(request('search'))
                    <a href="{{ route('admin.companies.index') }}" class="btn btn-outline btn-sm" title="Clear search">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </form>
            <a href="{{ route('admin.clients.index') }}" class="btn btn-outline btn-sm">
                <i class="bi bi-person-gear"></i> Manage Accounts
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="dftm-table">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">#</th>
                    <th>Company Name</th>
                    <th>Client Portal Account</th>
                    <th style="text-align: center;">Transmittals</th>
                    <th style="text-align: center;">Total Units</th>
                    <th style="text-align: center;">In Process</th>
                    <th style="text-align: center;">Repaired</th>
                    <th style="text-align: center;">BER</th>
                    <th style="text-align: center;">Released</th>
                    <th style="text-align: center; width: 220px;">Company Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($companiesData as $company)
                <tr class="company-card-row">
                    <td style="text-align: center; font-weight: 700; color: var(--dftm-slate);">
                        {{ $loop->iteration }}
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 38px; height: 38px; border-radius: 8px; background: #EEF2FF; color: #4F46E5; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; border: 1px solid #E0E7FF;">
                                <i class="bi bi-building"></i>
                            </div>
                            <div>
                                <a href="{{ route('admin.companies.dashboard', $company->company_key) }}" style="font-weight: 800; font-size: 0.95rem; color: #00205B; text-decoration: none;">
                                    {{ $company->company_name }}
                                </a>
                                <div style="font-size: 0.74rem; color: var(--dftm-slate);">
                                    {{ $company->batches_count }} Batches formed
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($company->client_user)
                            <div>
                                <strong style="color: var(--dftm-navy);">{{ $company->client_user->name }}</strong>
                                <span class="badge badge-stock" style="font-size: 0.68rem; margin-left: 4px;">{{ $company->client_user->status ?? 'active' }}</span>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--dftm-slate);">
                                <i class="bi bi-person"></i> <span class="mono">{{ $company->client_user->username }}</span>
                                @if($company->client_user->email && !str_contains($company->client_user->email, '@dftm-client.local'))
                                    • <i class="bi bi-envelope"></i> {{ $company->client_user->email }}
                                @endif
                            </div>
                        @else
                            <span style="font-size: 0.78rem; color: #94A3B8; font-style: italic;">
                                <i class="bi bi-exclamation-circle"></i> Unregistered User Account
                            </span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <span class="company-badge-pill" style="background: #E0F2FE; color: #0369A1;">
                            <i class="bi bi-box-arrow-in-down"></i> {{ $company->transmittals_count }}
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <span class="company-badge-pill" style="background: #F1F5F9; color: #0F172A; font-size: 0.85rem;">
                            <i class="bi bi-cpu"></i> {{ number_format($company->total_units_count) }}
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <span class="badge badge-in-process">{{ $company->in_process_count }}</span>
                    </td>
                    <td style="text-align: center;">
                        <span class="badge badge-repaired">{{ $company->repaired_count }}</span>
                    </td>
                    <td style="text-align: center;">
                        <span class="badge badge-ber">{{ $company->ber_count }}</span>
                    </td>
                    <td style="text-align: center;">
                        <span class="badge badge-released">{{ $company->released_count }}</span>
                    </td>
                    <td style="text-align: center;">
                        <div style="display: inline-flex; align-items: center; gap: 6px;">
                            <a href="{{ route('admin.companies.dashboard', $company->company_key) }}"
                                class="btn btn-primary btn-sm" style="font-weight: 700; padding: 5px 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08);"
                                title="Open {{ $company->company_name }} Dashboard">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                            <a href="{{ route('admin.incoming.index', ['company' => $company->company_name]) }}"
                                class="btn btn-outline btn-sm btn-icon" title="View {{ $company->company_name }} Transmittals">
                                <i class="bi bi-box-arrow-in-down"></i>
                            </a>
                            <a href="{{ route('admin.traceability.index', ['company' => $company->company_name]) }}"
                                class="btn btn-outline btn-sm btn-icon" title="View {{ $company->company_name }} Traceability Matrix">
                                <i class="bi bi-tools"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align: center; color: var(--dftm-slate); padding: 48px 16px;">
                        <div style="font-size: 2.8rem; color: #CBD5E1; margin-bottom: 10px;">
                            <i class="bi bi-folder-x"></i>
                        </div>
                        <div style="font-weight: 700; font-size: 1.05rem; color: var(--dftm-navy);">No Company Folders Found</div>
                        <div style="font-size: 0.85rem; color: var(--dftm-slate); margin-top: 4px;">
                            Client company records will appear here once transmittals or client accounts are registered.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 0.82rem; color: var(--dftm-slate);">
            Showing {{ $companiesData->count() }} company folder(s)
        </span>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.incoming.create') }}" class="btn btn-outline btn-sm">
                <i class="bi bi-plus-circle"></i> + Create Incoming
            </a>
            <a href="{{ route('admin.clients.index') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-building-add"></i> + Register Client Account
            </a>
        </div>
    </div>
</div>
@endsection
