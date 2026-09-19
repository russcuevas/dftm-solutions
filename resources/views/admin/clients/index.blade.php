@extends('layouts.app')

@section('title', 'Client Accounts')
@section('page_title', 'Customer & Client Accounts Directory')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title"><i class="bi bi-buildings"></i> Client Accounts (Customer Portals)</div>
            <div class="card-subtitle">Manage customer accounts with view-only tracking for Incoming, Batches, and Outgoing Slips</div>
        </div>
        <button type="button" class="btn btn-primary" data-modal-open="addClientModal" onclick="document.getElementById('addClientModal').classList.add('active');">
            <i class="bi bi-building-add"></i> + Register New Client
        </button>
    </div>

    <!-- Filter & Search Bar -->
    <div style="padding: 16px 24px; background: #F8FAFC; border-bottom: 1px solid var(--dftm-border);">
        <form action="{{ route('admin.clients.index') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <div style="flex: 1; min-width: 280px;">
                <input type="text" name="search" class="form-control" placeholder="Search by Company Name, Contact Person, Username, Email..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i> Search
            </button>
            @if(request('search'))
                <a href="{{ route('admin.clients.index') }}" class="btn btn-outline" style="color: #DC2626;">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="dftm-table">
            <thead>
                <tr>
                    <th>Company Name</th>
                    <th>Contact Person</th>
                    <th>Username</th>
                    <th>Email & Contact</th>
                    <th style="text-align: center;">Portal Status</th>
                    <th>Created At</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $c)
                <tr>
                    <td>
                        <strong style="color: var(--dftm-navy); font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                            <i class="bi bi-building" style="color: var(--dftm-accent);"></i> {{ $c->company_name ?? 'N/A' }}
                        </strong>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: #1E293B;">{{ $c->name }}</div>
                    </td>
                    <td>
                        <span class="mono" style="font-weight: 700; color: #6D28D9; background: #EDE9FE; border: 1px solid #DDD6FE; padding: 3px 8px; border-radius: 6px; font-size: 0.85rem;">
                            {{ $c->username }}
                        </span>
                    </td>
                    <td>
                        <div style="font-size: 0.88rem; color: #1E293B;">{{ $c->email }}</div>
                        @if($c->phone)
                            <div style="font-size: 0.78rem; color: var(--dftm-slate);"><i class="bi bi-telephone"></i> {{ $c->phone }}</div>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <span class="badge {{ $c->status === 'active' ? 'badge-good' : 'badge-pending' }}">
                            {{ strtoupper($c->status ?? 'ACTIVE') }}
                        </span>
                    </td>
                    <td>{{ $c->created_at->format('M d, Y') }}</td>
                    <td style="text-align: center;">
                        <div style="display: flex; gap: 6px; justify-content: center;">
                            <button type="button" class="btn btn-outline btn-sm" onclick="openClientEditModal({{ json_encode($c) }})" title="Edit Client Account">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('admin.clients.destroy', $c->id) }}" method="POST" onsubmit="return confirm('Delete client account {{ $c->company_name }} ({{ $c->name }})?');" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline btn-sm" style="color: #DC2626;" title="Delete Client Account">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--dftm-slate); padding: 48px 16px;">
                        <div style="font-size: 2.4rem; color: #CBD5E1; margin-bottom: 8px;"><i class="bi bi-buildings"></i></div>
                        <div style="font-weight: 700; font-size: 1.05rem; color: var(--dftm-navy);">No Client Accounts Registered</div>
                        <div style="font-size: 0.85rem; color: var(--dftm-slate); margin-top: 4px;">Click "+ Register New Client" to create accounts for companies like Converge ICT, PLDT, or Globe.</div>
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
    <div class="modal-card" style="max-width: 600px;">
        <form action="{{ route('admin.clients.store') }}" method="POST">
            @csrf
            <div class="modal-header">
                <div class="modal-title"><i class="bi bi-building-add"></i> Register New Client Account</div>
                <button type="button" class="modal-close" data-modal-close><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 700; color: var(--dftm-navy);">Company / Client Name *</label>
                    <input type="text" name="company_name" class="form-control" placeholder="e.g. CONVERGE ICT SOLUTIONS / PLDT / GLOBE" required autofocus>
                    <small style="color: var(--dftm-slate); font-size: 0.76rem; display: block; margin-top: 4px;">
                        <i class="bi bi-info-circle"></i> This exact company name will appear in the Transmittal creation dropdown.
                    </small>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Contact Person / Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Juan dela Cruz" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Username (Client Login) *</label>
                        <input type="text" name="username" class="form-control mono" placeholder="e.g. converge_user" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email Address (Optional)</label>
                        <input type="email" name="email" class="form-control" placeholder="client@company.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Phone (Optional)</label>
                        <input type="text" name="phone" class="form-control" placeholder="e.g. 0917-123-4567">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" value="password123" placeholder="Default: password123">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Portal Status</label>
                        <select name="status" class="form-select">
                            <option value="active" selected>ACTIVE</option>
                            <option value="inactive">INACTIVE</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Register Client Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Client Account -->
<div class="modal-backdrop" id="editClientModal">
    <div class="modal-card" style="max-width: 600px;">
        <form id="editClientForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <div class="modal-title"><i class="bi bi-pencil-square"></i> Edit Client Account</div>
                <button type="button" class="modal-close" data-modal-close><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 700; color: var(--dftm-navy);">Company / Client Name *</label>
                    <input type="text" name="company_name" id="editClientCompanyName" class="form-control" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Contact Person / Name *</label>
                        <input type="text" name="name" id="editClientName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Username (Client Login) *</label>
                        <input type="text" name="username" id="editClientUsername" class="form-control mono" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" id="editClientEmail" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Phone</label>
                        <input type="text" name="phone" id="editClientPhone" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">New Password (leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control" placeholder="Optional new password">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Portal Status</label>
                        <select name="status" id="editClientStatus" class="form-select">
                            <option value="active">ACTIVE</option>
                            <option value="inactive">INACTIVE</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openClientEditModal(client) {
    document.getElementById('editClientForm').action = '/admin/clients/' + client.id;
    document.getElementById('editClientCompanyName').value = client.company_name || '';
    document.getElementById('editClientName').value = client.name || '';
    document.getElementById('editClientUsername').value = client.username || '';
    document.getElementById('editClientEmail').value = client.email || '';
    document.getElementById('editClientPhone').value = client.phone || '';
    document.getElementById('editClientStatus').value = client.status || 'active';

    const modal = document.getElementById('editClientModal');
    if (modal) modal.classList.add('active');
}
</script>
@endsection
