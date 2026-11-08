@extends('layouts.app')

@section('title', 'កំណត់លេខសម្ងាត់ឡើងវិញ — ' . config('app.name'))

@section('content')
<div class="auth-card-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <img src="{{ asset('images/stic-logo.png') }}" alt="{{ __('messages.institute_name') }}" class="auth-brand-logo">
            <h1 class="auth-title">កំណត់លេខសម្ងាត់ឡើងវិញ</h1>
            <p class="auth-subtitle">{{ __('messages.password_min') }}</p>
            <div class="auth-divider" aria-hidden="true"></div>
        </div>

        <form action="{{ route('password.update') }}" method="POST" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label for="reset-email" class="form-label">{{ __('messages.email') }}</label>
                <input type="email" id="reset-email" name="email" class="form-control" value="{{ old('email', $email) }}" required readonly autocomplete="email" style="background-color: var(--stic-surface-muted);">
            </div>

            <div class="form-group">
                <label for="new-password" class="form-label">{{ __('messages.password') }}</label>
                <div class="password-input-wrapper">
                    <input type="password" id="new-password" name="password" class="form-control @error('password') input-invalid @enderror" required autocomplete="new-password" autofocus placeholder="••••••••" aria-describedby="@error('password') new-password-error @enderror">
                    <button type="button" class="password-toggle-btn" data-target="new-password" aria-label="{{ __('messages.password_toggle') }}" aria-pressed="false">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
                @error('password')
                    <span id="new-password-error" class="field-error" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="new-password-confirm" class="form-label">{{ __('messages.password_confirmation') }}</label>
                <div class="password-input-wrapper">
                    <input type="password" id="new-password-confirm" name="password_confirmation" class="form-control" required autocomplete="new-password" placeholder="••••••••">
                    <button type="button" class="password-toggle-btn" data-target="new-password-confirm" aria-label="{{ __('messages.password_toggle') }}" aria-pressed="false">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 0.98rem; font-weight: 600; margin-top: 1rem;">
                រក្សាទុកលេខសម្ងាត់ថ្មី
            </button>
        </form>
    </div>
</div>
@endsection
