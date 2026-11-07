@extends('layouts.app')

@section('title', __('messages.pending_approval_title') . ' — STIC Info')

@section('content')
<div style="max-width: 550px; margin: 3rem auto; text-align: center; width: 100%;">
    <div class="card" style="padding: 3rem 2rem; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); background: #ffffff;">
        <div style="font-size: 3.5rem; margin-bottom: 1rem;">⏳</div>
        <h1 style="font-size: 1.45rem; font-weight: 700; color: #1e293b; margin-bottom: 0.8rem;">
            {{ __('messages.pending_approval_title') }}
        </h1>
        <p style="color: #64748b; font-size: 0.98rem; line-height: 1.6; margin-bottom: 2rem;">
            {{ __('messages.pending_approval_desc') }}
        </p>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-secondary" style="padding: 0.65rem 1.75rem; font-weight: 600;">
                {{ __('messages.logout') }}
            </button>
        </form>
    </div>
</div>
@endsection
