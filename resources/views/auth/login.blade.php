@extends('layouts.auth')

@section('title', 'System Login')

@section('content')
<div class="auth-card">
    <div class="auth-brand">
        <img src="{{ asset('images/logo.png') }}" alt="DFTM Logo" class="auth-logo">
        <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--dftm-navy); margin-top: 4px;">Inventory & Repair System</h2>
        <p class="auth-subtitle">Sign in to access your dashboard & records</p>
    </div>

    @include('partials.alerts')

    <form action="{{ route('login.submit') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label class="form-label" for="loginInput">Email or Username</label>
            <div style="position: relative;">
                <input type="text" name="login" id="loginInput" class="form-control" placeholder="Enter your email or username" value="{{ old('login') }}" required autofocus style="padding-left: 38px;">
                <i class="bi bi-person" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--dftm-slate-light); font-size: 1.1rem;"></i>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <label class="form-label" for="passwordInput" style="margin-bottom: 0;">Password</label>
                <a href="{{ route('password.request') }}" style="font-size: 0.82rem; color: #0284c7; text-decoration: none; font-weight: 600;">
                    Forgot Password?
                </a>
            </div>
            <div style="position: relative;">
                <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Enter your password" required style="padding-left: 38px; padding-right: 38px;">
                <i class="bi bi-lock" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--dftm-slate-light); font-size: 1.1rem;"></i>
                <button type="button" onclick="togglePassword()" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--dftm-slate); padding: 4px;">
                    <i class="bi bi-eye" id="toggleIcon"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 0.95rem; letter-spacing: 0.5px; font-weight: 700;">
            <i class="bi bi-box-arrow-in-right"></i> Sign In to Portal
        </button>
    </form>
</div>

<script>
    function togglePassword() {
        const input = document.getElementById('passwordInput');
        const icon = document.getElementById('toggleIcon');
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
</script>
@endsection
