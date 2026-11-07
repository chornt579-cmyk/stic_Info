@extends('layouts.app')

@section('title', 'កំណត់លេខសម្ងាត់ឡើងវិញ — ' . config('app.name'))

@section('content')
<div style="max-width: 480px; margin: 2rem auto; width: 100%;">
    <div class="card" style="padding: 2rem; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); background: #ffffff;">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;" aria-hidden="true">🔒</div>
            <h1 style="font-size: 1.35rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">កំណត់លេខសម្ងាត់ឡើងវិញ</h1>
            <p style="font-size: 0.9rem; color: #64748b;">{{ __('messages.password_min') }}</p>
        </div>

        <form action="{{ route('password.update') }}" method="POST" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label for="reset-email" class="form-label">{{ __('messages.email') }}</label>
                <input type="email" id="reset-email" name="email" class="form-control" value="{{ old('email', $email) }}" required readonly autocomplete="email" style="background-color: #f8f9fa;">
            </div>

            <div class="form-group">
                <label for="new-password" class="form-label">{{ __('messages.password') }}</label>
                <div class="password-input-wrapper">
                    <input type="password" id="new-password" name="password" class="form-control @error('password') input-invalid @enderror" required autocomplete="new-password" autofocus placeholder="••••••••" aria-describedby="@error('password') new-password-error @enderror">
                    <button type="button" class="password-toggle-btn" data-target="new-password" aria-label="{{ __('messages.password_toggle') }}" aria-pressed="false">👁️‍🗨️</button>
                </div>
                @error('password')
                    <span id="new-password-error" class="field-error" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="new-password-confirm" class="form-label">{{ __('messages.password_confirmation') }}</label>
                <div class="password-input-wrapper">
                    <input type="password" id="new-password-confirm" name="password_confirmation" class="form-control" required autocomplete="new-password" placeholder="••••••••">
                    <button type="button" class="password-toggle-btn" data-target="new-password-confirm" aria-label="{{ __('messages.password_toggle') }}" aria-pressed="false">👁️‍🗨️</button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 0.98rem; font-weight: 600;">
                រក្សាទុកលេខសម្ងាត់ថ្មី
            </button>
        </form>
    </div>
</div>
@endsection
