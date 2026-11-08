@extends('layouts.app')

@section('title', __('messages.pending_approval_title') . ' — STIC Info')

@section('content')
<div class="auth-card-wrapper" style="max-width: 550px;">
    <div class="auth-card" style="text-align: center;">
        <div class="auth-header">
            <img src="{{ asset('images/stic-logo.png') }}" alt="{{ __('messages.institute_name') }}" class="auth-brand-logo">
            <h1 class="auth-title">{{ __('messages.pending_approval_title') }}</h1>
            <p class="auth-subtitle">{{ __('messages.institute_name') }}</p>
            <div class="auth-divider" aria-hidden="true"></div>
        </div>

        <p style="color: var(--stic-text); font-size: 0.98rem; line-height: 1.7; margin-bottom: 2rem;">
            {{ __('messages.pending_approval_desc') }}
        </p>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-secondary" style="padding: 0.65rem 2rem; font-weight: 600;">
                {{ __('messages.logout') }}
            </button>
        </form>
    </div>
</div>
@endsection
