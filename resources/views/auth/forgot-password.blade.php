@extends('layouts.app')

@section('title', __('messages.forgot_password') . ' — ' . config('app.name'))

@section('content')
<div style="max-width: 480px; margin: 2rem auto; width: 100%;">
    <div class="card" style="padding: 2rem; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); background: #ffffff;">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;" aria-hidden="true">🔑</div>
            <h1 style="font-size: 1.35rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">{{ __('messages.forgot_password') }}</h1>
            <p style="font-size: 0.9rem; color: #64748b; line-height: 1.5;">
                {{ __('messages.reset_link_sent') }}
            </p>
        </div>

        @if (session('status'))
            <div class="alert alert-success" style="font-size: 0.9rem; margin-bottom: 1rem;" role="status">
                <span aria-hidden="true">✅</span> {{ session('status') }}
            </div>
        @endif

        @if (session('dev_reset_url'))
            <div class="alert alert-warning" style="font-size: 0.85rem; margin-bottom: 1rem; word-break: break-all;" role="note">
                <span aria-hidden="true">🛠️</span> <strong>Development Reset Link:</strong><br>
                <a href="{{ session('dev_reset_url') }}" style="color: #1a73e8; font-weight: 600;">
                    {{ session('dev_reset_url') }}
                </a>
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

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 0.98rem; font-weight: 600; margin-bottom: 1rem;">
                ផ្ញើតំណភ្ជាប់កំណត់លេខសម្ងាត់
            </button>

            <div style="text-align: center;">
                <a href="{{ route('login') }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem;">
                    ← {{ __('messages.already_have_account') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
