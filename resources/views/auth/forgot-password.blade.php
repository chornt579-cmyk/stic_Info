@extends('layouts.app')

@section('title', __('messages.forgot_password') . ' — ' . config('app.name'))

@section('content')
<div class="auth-card-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <img src="{{ asset('images/stic-logo.png') }}" alt="{{ __('messages.institute_name') }}" class="auth-brand-logo">
            <h1 class="auth-title">{{ __('messages.forgot_password') }}</h1>
            <p class="auth-subtitle">
                {{ __('messages.reset_link_sent') }}
            </p>
            <div class="auth-divider" aria-hidden="true"></div>
        </div>

        @if (session('status'))
            <div class="alert alert-success" role="status">
                <span class="alert-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </span>
                <div>{{ session('status') }}</div>
            </div>
        @endif

        @if (session('dev_reset_url'))
            <div class="alert alert-warning" style="font-size: 0.85rem; margin-bottom: 1.25rem; word-break: break-all;" role="note">
                <div>
                    <strong>Development Reset Link:</strong><br>
                    <a href="{{ session('dev_reset_url') }}" style="color: var(--stic-primary); font-weight: 600;">
                        {{ session('dev_reset_url') }}
                    </a>
                </div>
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST" novalidate>
            @csrf
            <div class="form-group">
                <label for="reset-email" class="form-label">{{ __('messages.email') }}</label>
                <input type="email" id="reset-email" name="email" class="form-control @error('email') input-invalid @enderror" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="{{ __('messages.email_placeholder') }}" aria-describedby="@error('email') reset-email-error @enderror">
                @error('email')
                    <span id="reset-email-error" class="field-error" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 0.98rem; font-weight: 600; margin-bottom: 1.25rem;">
                ផ្ញើតំណភ្ជាប់កំណត់លេខសម្ងាត់
            </button>

            <div style="text-align: center;">
                <a href="{{ route('login') }}" style="color: var(--stic-muted); text-decoration: none; font-size: 0.9rem; font-weight: 500;">
                    ← {{ __('messages.already_have_account') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
