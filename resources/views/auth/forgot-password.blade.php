@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<div class="auth-card">
    <div class="auth-brand">
        <img src="{{ asset('images/logo.png') }}" alt="DFTM Logo" class="auth-logo">
        <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--dftm-navy); margin-top: 4px;">Forgot Password</h2>
        <p class="auth-subtitle">Enter your registered email to receive a password reset link</p>
    </div>

    @include('partials.alerts')

    <form action="{{ route('password.email') }}" method="POST" id="forgotPasswordForm">
        @csrf
        
        <div class="form-group" style="margin-bottom: 24px;">
            <label class="form-label" for="emailInput">Registered Email Address</label>
            <div style="position: relative;">
                <input type="email" name="email" id="emailInput" class="form-control" placeholder="example@domain.com" value="{{ old('email') }}" required autofocus style="padding-left: 38px;">
                <i class="bi bi-envelope" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--dftm-slate-light); font-size: 1.1rem;"></i>
            </div>
        </div>

        <button type="submit" id="submitBtn" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 0.95rem; letter-spacing: 0.5px; font-weight: 700; margin-bottom: 16px;">
            <i class="bi bi-send-fill" style="margin-right: 6px;"></i> Send Reset Link
        </button>

        <div style="text-align: center; margin-top: 16px;">
            <a href="{{ route('login') }}" style="color: var(--dftm-slate); text-decoration: none; font-size: 0.88rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; transition: color 0.2s;">
                <i class="bi bi-arrow-left"></i> Return to Login
            </a>
        </div>
    </form>
</div>

<script>
    document.getElementById('forgotPasswordForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="margin-right: 6px;"></span> Sending Link...';
    });
</script>
@endsection
