@extends('layouts.app')

@section('title', 'Client Accounts')
@section('page_title', 'Client & Company Account Directory')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title"><i class="bi bi-buildings"></i> Registered Clients & Companies</div>
            <div class="card-subtitle">Accounts enabled for company unit viewing & tracking</div>
        </div>
        <button type="button" class="btn btn-primary" data-modal-open="addClientModal">
            <i class="bi bi-building-add"></i> + Register New Client
        </button>
    </div>

    <div class="table-responsive">
        <table class="dftm-table">
            <thead>
                <tr>
                    <th>Company Name</th>
                    <th>Contact Person</th>
                    <th>Username</th>
                    <th>Email / Contact</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $c)
                <tr>
                    <td>
                        <strong style="color: var(--dftm-navy); font-size: 0.95rem;">
                            <i class="bi bi-building"></i> {{ $c->company_name ?? 'DFTM CLIENT' }}
                        </strong>
                    </td>
                    <td>{{ $c->name }}</td>
                    <td><span class="mono" style="font-weight: 700; color: var(--dftm-accent);">{{ $c->username }}</span></td>
                    <td>
                        <div>{{ $c->email }}</div>
                        @if($c->phone)<small style="color: var(--dftm-slate);">{{ $c->phone }}</small>@endif
                    </td>
                    <td>
                        <span class="badge {{ $c->status === 'active' ? 'badge-good' : 'badge-pending' }}">
                            {{ strtoupper($c->status ?? 'ACTIVE') }}
                        </span>
                    </td>
                    <td>{{ $c->created_at->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--dftm-slate); padding: 36px 16px;">
                        <div style="font-size: 2rem; color: #CBD5E1; margin-bottom: 8px;"><i class="bi bi-buildings"></i></div>
                        <div style="font-weight: 600; font-size: 0.95rem; color: var(--dftm-navy);">No client accounts registered yet</div>
                        <div style="font-size: 0.8rem; color: var(--dftm-slate-light); margin-top: 4px;">Register client companies so they appear in the Transmittal dropdown.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($clients->hasPages())
    <div class="card-footer">
        {{ $clients->links() }}
    </div>
    @endif
</div>

<!-- Modal: Register Client Account -->
<div class="modal-backdrop" id="addClientModal">
    <div class="modal-card" style="max-width: 560px;">
        <form action="{{ route('encoder.clients.store') }}" method="POST">
            @csrf
            <div class="modal-header">
                <div class="modal-title"><i class="bi bi-building-add"></i> Register New Client Account</div>
                <button type="button" class="modal-close" data-modal-close><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 700; color: var(--dftm-navy);">Company / Client Name *</label>
                    <input type="text" name="company_name" class="form-control" placeholder="e.g. CONVERGE ICT / PLDT / GLOBE" required autofocus>
                    <small style="color: var(--dftm-slate); font-size: 0.75rem;">This company name will appear in the Transmittal creation dropdown.</small>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Contact Person Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. John Santos" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Username (Login ID) *</label>
                        <input type="text" name="username" class="form-control" placeholder="e.g. converge_tracker" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email Address (Optional)</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. client@converge.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number (Optional)</label>
                        <input type="text" name="phone" class="form-control" placeholder="e.g. 09171234567">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Default: password123">
                    <small style="color: var(--dftm-slate); font-size: 0.75rem;">Leave blank to default to password123</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary">Save Client Account</button>
            </div>
        </form>
    </div>
</div>
@endsection
