@extends('layouts.app')

@section('title', 'My Profile')
@section('page_title', 'User Profile Settings')

@section('content')
<div class="card" style="max-width: 700px; margin: 0 auto;">
    <div class="card-header">
        <div>
            <div class="card-title"><i class="bi bi-person-gear"></i> Account Information</div>
            <div class="card-subtitle">Manage your account credentials and personal information</div>
        </div>
    </div>
    <form action="{{ route('profile.update') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="e.g. 09171234567">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">System Role</label>
                <input type="text" class="form-control" value="{{ strtoupper($user->role) }}" disabled style="background: #F1F5F9; font-weight: 700;">
            </div>

            <hr style="margin: 20px 0; border: 0; border-top: 1px solid var(--dftm-border);">

            <div class="form-group">
                <label class="form-label">Change Password (Leave blank to keep current)</label>
                <input type="password" name="password" class="form-control" placeholder="New Password">
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('encoder.dashboard') }}" class="btn btn-outline">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2-circle"></i> Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
