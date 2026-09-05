@extends('layouts.app')

@section('title', 'User Accounts')
@section('page_title', 'System Users & Role Management')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title"><i class="bi bi-people-fill"></i> System Accounts (Admin & Encoder)</div>
            <div class="card-subtitle">Manage access credentials, assign Admin or Encoder roles</div>
        </div>
        <button type="button" class="btn btn-primary" data-modal-open="addUserModal">
            <i class="bi bi-person-plus-fill"></i> Add New Account
        </button>
    </div>

    <div class="table-responsive">
        <table class="dftm-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Contact Phone</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                {{ strtoupper(substr($u->name ?? 'U', 0, 1)) }}
                            </div>
                            <strong>{{ $u->name }}</strong>
                        </div>
                    </td>
                    <td><span class="mono">{{ $u->username ?? '-' }}</span></td>
                    <td>{{ $u->email }}</td>
                    <td>
                        <span class="user-role-badge {{ $u->isAdmin() ? 'role-admin' : 'role-encoder' }}" style="font-size: 0.72rem;">
                            {{ strtoupper($u->role) }}
                        </span>
                    </td>
                    <td>{{ $u->phone ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $u->status === 'active' ? 'badge-good' : 'badge-pending' }}">
                            {{ strtoupper($u->status ?? 'ACTIVE') }}
                        </span>
                    </td>
                    <td>{{ $u->created_at->format('M d, Y') }}</td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <button type="button" class="btn btn-outline btn-sm" onclick="openUserEditModal({{ json_encode($u) }})" title="Edit Account">
                                <i class="bi bi-pencil"></i>
                            </button>
                            @if($u->id !== auth()->id())
                            <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Delete user account {{ $u->name }}?');" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline btn-sm" style="color: #DC2626;" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--dftm-slate); padding: 32px;">No user accounts found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="card-footer">
        {{ $users->links() }}
    </div>
    @endif
</div>

<!-- Add User Modal -->
<div class="modal-backdrop" id="addUserModal">
    <div class="modal-card">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="modal-header">
                <div class="modal-title"><i class="bi bi-person-plus-fill"></i> Create New User Account</div>
                <button type="button" class="modal-close" data-modal-close><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. John Doe" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="e.g. jdoe" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. jdoe@dftm.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="e.g. 09171234567">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">System Role</label>
                        <select name="role" class="form-select" required>
                            <option value="encoder" selected>Encoder (Entry & Outgoing Operations)</option>
                            <option value="admin">Administrator (Full Access & Management)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Initial Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Default: password123">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal-backdrop" id="userEditModal">
    <div class="modal-card">
        <form id="userEditForm" method="POST" data-base-action="{{ url('admin/users/__ID__') }}">
            @csrf
            @method('PUT')
            <input type="hidden" id="editUserId">

            <div class="modal-header">
                <div class="modal-title"><i class="bi bi-person-gear"></i> Edit User Account</div>
                <button type="button" class="modal-close" data-modal-close><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" id="editUserName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" id="editUserUsername" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" id="editUserEmail" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" id="editUserPhone" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">System Role</label>
                        <select name="role" id="editUserRole" class="form-select">
                            <option value="encoder">Encoder</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Account Status</label>
                        <select name="status" id="editUserStatus" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">New Password (leave blank to keep current)</label>
                    <input type="password" name="password" class="form-control" placeholder="New Password">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
