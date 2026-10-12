@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<div class="auth-card">
    <div class="auth-brand">
        <img src="{{ asset('images/logo.png') }}" alt="DFTM Logo" class="auth-logo">
        <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--dftm-navy); margin-top: 4px;">Set New Password</h2>
        <p class="auth-subtitle">Create a secure new password for your account</p>
    </div>

    @include('partials.alerts')

    <form action="{{ route('password.update') }}" method="POST" id="resetPasswordForm">
        @csrf
        
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-group">
            <label class="form-label" for="emailInput">Email Address</label>
            <div style="position: relative;">
                <input type="email" name="email" id="emailInput" class="form-control" placeholder="Enter your email" value="{{ $email ?? old('email') }}" required readonly style="padding-left: 38px; background-color: #f8fafc; cursor: not-allowed;">
                <i class="bi bi-envelope" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--dftm-slate-light); font-size: 1.1rem;"></i>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="newPassword">New Password</label>
            <div style="position: relative;">
                <input type="password" name="password" id="newPassword" class="form-control" placeholder="Minimum 6 characters" required autofocus style="padding-left: 38px; padding-right: 38px;">
                <i class="bi bi-lock" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--dftm-slate-light); font-size: 1.1rem;"></i>
                <button type="button" onclick="togglePassword('newPassword', 'toggleIcon1')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--dftm-slate); padding: 4px;">
                    <i class="bi bi-eye" id="toggleIcon1"></i>
                </button>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
            <label class="form-label" for="confirmPassword">Confirm New Password</label>
            <div style="position: relative;">
                <input type="password" name="password_confirmation" id="confirmPassword" class="form-control" placeholder="Re-enter your new password" required style="padding-left: 38px; padding-right: 38px;">
                <i class="bi bi-shield-check" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--dftm-slate-light); font-size: 1.1rem;"></i>
                <button type="button" onclick="togglePassword('confirmPassword', 'toggleIcon2')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--dftm-slate); padding: 4px;">
                    <i class="bi bi-eye" id="toggleIcon2"></i>
                </button>
            </div>
        </div>

        <button type="submit" id="resetBtn" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 0.95rem; letter-spacing: 0.5px; font-weight: 700; margin-bottom: 16px;">
            <i class="bi bi-check-circle-fill" style="margin-right: 6px;"></i> Update Password
        </button>

        <div style="text-align: center; margin-top: 16px;">
            <a href="{{ route('login') }}" style="color: var(--dftm-slate); text-decoration: none; font-size: 0.88rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                <i class="bi bi-arrow-left"></i> Return to Login
            </a>
        </div>
    </form>
</div>

<script>
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

    document.getElementById('resetPasswordForm').addEventListener('submit', function() {
        const btn = document.getElementById('resetBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="margin-right: 6px;"></span> Updating Password...';
    });
</script>
@endsection
