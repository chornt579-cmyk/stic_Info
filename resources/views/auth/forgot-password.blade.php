@extends('layouts.app')

@section('title', __('messages.forgot_password') . ' — STIC Info')

@section('content')
<div style="max-width: 480px; margin: 2rem auto; width: 100%;">
    <div class="card" style="padding: 2rem; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); background: #ffffff;">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🔑</div>
            <h1 style="font-size: 1.35rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">{{ __('messages.forgot_password') }}</h1>
            <p style="font-size: 0.9rem; color: #64748b; line-height: 1.5;">
                បញ្ចូលអ៊ីមែលគណនីរបស់អ្នក ដើម្បីទទួលបានតំណភ្ជាប់កំណត់លេខសម្ងាត់ឡើងវិញ។
            </p>
        </div>

        @if (session('status'))
            <div class="alert alert-success" style="font-size: 0.9rem; margin-bottom: 1rem;">
                ✅ {{ session('status') }}
            </div>
        @endif

        @if (session('dev_reset_url'))
            <div class="alert alert-warning" style="font-size: 0.85rem; margin-bottom: 1rem; word-break: break-all;">
                🛠️ <strong>Development Reset Link:</strong><br>
                <a href="{{ session('dev_reset_url') }}" style="color: #1a73e8; font-weight: 600;">
                    {{ session('dev_reset_url') }}
                </a>
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="reset-email" class="form-label">{{ __('messages.email') }}</label>
                <input type="email" id="reset-email" name="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="example@stic.edu.kh">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 0.98rem; font-weight: 600; margin-bottom: 1rem;">
                ផ្ញើតំណភ្ជាប់កំណត់លេខសម្ងាត់
            </button>

            <div style="text-align: center;">
                <a href="{{ route('login') }}" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem;">
                    ← {{ __('messages.already_have_account') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
