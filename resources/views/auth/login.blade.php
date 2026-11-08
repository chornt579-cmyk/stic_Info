@extends('layouts.app')

@section('title', __('messages.login_title') . ' — ' . config('app.name'))

@section('content')
@php
    $initialTab = ($errors->has('name') || $errors->has('phone') || $errors->has('department_id') || $errors->has('class_id') || $errors->has('password_confirmation') || old('role')) ? 'register' : 'login';
    
    // Prepare classes list for JavaScript DOM reconstruction (Safari/iOS compatible)
    $classesJson = [];
    foreach ($departments as $dept) {
        foreach ($dept->classes as $c) {
            $shiftLabel = __('messages.shift_' . $c->shift);
            $classesJson[] = [
                'id' => $c->id,
                'department_id' => $dept->id,
                'label' => ($c->name_kh ?? $c->name) . ' (' . $shiftLabel . ')',
            ];
        }
    }
@endphp

<!-- JSON data storage for Safari-safe dynamic select -->
<script type="application/json" id="classes-data-json">
    {!! json_encode($classesJson, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
</script>

<div id="auth-card-wrapper" class="auth-card-wrapper" data-initial-tab="{{ $initialTab }}">
    <div class="auth-card">
        <!-- Institutional Header with Official STIC Logo -->
        <div class="auth-header">
            <img src="{{ asset('images/stic-logo.png') }}" alt="{{ __('messages.institute_name') }}" class="auth-brand-logo">
            <h1 class="auth-title">STIC Info</h1>
            <p class="auth-subtitle">{{ __('messages.institute_name') }}</p>
            <div class="auth-divider" aria-hidden="true"></div>
        </div>

        <!-- Accessible Segmented Tabs Navigation -->
        <div class="auth-tabs" role="tablist" aria-label="Authentication Options">
            <button type="button" id="tab-login-btn" class="tab-btn tab-active" role="tab" aria-selected="true" aria-controls="login-panel" tabindex="0">
                {{ __('messages.login') }}
            </button>
            <button type="button" id="tab-register-btn" class="tab-btn tab-inactive" role="tab" aria-selected="false" aria-controls="register-panel" tabindex="-1">
                {{ __('messages.register') }}
            </button>
        </div>

        <!-- Panel 1: Login Form -->
        <div id="login-panel" role="tabpanel" aria-labelledby="tab-login-btn">
            <form action="{{ route('login') }}" method="POST" novalidate>
                @csrf
                <div class="form-group">
                    <label for="login-email" class="form-label">{{ __('messages.email') }}</label>
                    <input type="email" id="login-email" name="email" class="form-control @error('email') input-invalid @enderror" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="{{ __('messages.email_placeholder') }}" aria-describedby="@error('email') login-email-error @enderror">
                    @error('email')
                        <span id="login-email-error" class="field-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <label for="login-password" class="form-label" style="margin-bottom: 0;">{{ __('messages.password') }}</label>
                        <a href="{{ route('password.request') }}" style="font-size: 0.82rem; color: var(--stic-primary); text-decoration: none; font-weight: 600;">
                            {{ __('messages.forgot_password') }}
                        </a>
                    </div>
                    <div class="password-input-wrapper">
                        <input type="password" id="login-password" name="password" class="form-control @error('password') input-invalid @enderror" required autocomplete="current-password" placeholder="••••••••" aria-describedby="@error('password') login-password-error @enderror">
                        <button type="button" class="password-toggle-btn" data-target="login-password" aria-label="{{ __('messages.password_toggle') }}" aria-pressed="false">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                    @error('password')
                        <span id="login-password-error" class="field-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <label style="font-size: 0.88rem; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span>{{ __('messages.remember_me') }}</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 1rem; font-weight: 600;">
                    {{ __('messages.login') }}
                </button>
            </form>
        </div>

        <!-- Panel 2: Register Form -->
        <div id="register-panel" class="hidden" role="tabpanel" aria-labelledby="tab-register-btn">
            <form action="{{ route('register') }}" method="POST" novalidate>
                @csrf
                <!-- Hidden honeypot to stop automated spam bots -->
                <input type="text" name="spambot_trap" class="spambot-trap" tabindex="-1" autocomplete="off">

                <div class="form-group">
                    <label for="reg-name" class="form-label">{{ __('messages.full_name') }}</label>
                    <input type="text" id="reg-name" name="name" class="form-control @error('name') input-invalid @enderror" value="{{ old('name') }}" required autocomplete="name" placeholder="{{ __('messages.name_placeholder') }}" aria-describedby="@error('name') reg-name-error @enderror">
                    @error('name')
                        <span id="reg-name-error" class="field-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="reg-email" class="form-label">{{ __('messages.email') }}</label>
                    <input type="email" id="reg-email" name="email" class="form-control @error('email') input-invalid @enderror" value="{{ old('email') }}" required autocomplete="email" placeholder="{{ __('messages.email_placeholder') }}" aria-describedby="@error('email') reg-email-error @enderror">
                    @error('email')
                        <span id="reg-email-error" class="field-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="reg-phone" class="form-label">{{ __('messages.phone') }}</label>
                    <input type="tel" id="reg-phone" name="phone" class="form-control @error('phone') input-invalid @enderror" value="{{ old('phone') }}" inputmode="tel" pattern="[0-9\s\-\+\(\)]{8,20}" autocomplete="tel" placeholder="{{ __('messages.phone_placeholder') }}" aria-describedby="@error('phone') reg-phone-error @enderror">
                    @error('phone')
                        <span id="reg-phone-error" class="field-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="reg-password" class="form-label">{{ __('messages.password_min') }}</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="reg-password" name="password" class="form-control @error('password') input-invalid @enderror" required autocomplete="new-password" placeholder="••••••••" aria-describedby="@error('password') reg-password-error @enderror">
                        <button type="button" class="password-toggle-btn" data-target="reg-password" aria-label="{{ __('messages.password_toggle') }}" aria-pressed="false">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                    @error('password')
                        <span id="reg-password-error" class="field-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="reg-password-confirm" class="form-label">{{ __('messages.password_confirmation') }}</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="reg-password-confirm" name="password_confirmation" class="form-control" required autocomplete="new-password" placeholder="••••••••">
                        <button type="button" class="password-toggle-btn" data-target="reg-password-confirm" aria-label="{{ __('messages.password_toggle') }}" aria-pressed="false">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <span class="form-label" id="role-label">{{ __('messages.role') }}</span>
                    <div class="role-selector-grid" role="radiogroup" aria-labelledby="role-label">
                        <label class="role-pill">
                            <input type="radio" name="role" value="student" {{ old('role', 'student') === 'student' ? 'checked' : '' }}>
                            <span>{{ __('messages.role_student') }}</span>
                        </label>
                        <label class="role-pill">
                            <input type="radio" name="role" value="teacher" {{ old('role') === 'teacher' ? 'checked' : '' }}>
                            <span>{{ __('messages.role_teacher') }}</span>
                        </label>
                    </div>
                    @error('role')
                        <span class="field-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="reg-dept" class="form-label">{{ __('messages.departments') }}</label>
                    <select id="reg-dept" name="department_id" class="form-control @error('department_id') input-invalid @enderror" required aria-describedby="@error('department_id') reg-dept-error @enderror">
                        <option value="">-- {{ __('messages.select_department') }} --</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name_kh ?? $dept->name }} ({{ $dept->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <span id="reg-dept-error" class="field-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" id="class-container">
                    <label for="reg-class" class="form-label">{{ __('messages.classes') }}</label>
                    <select id="reg-class" name="class_id" class="form-control @error('class_id') input-invalid @enderror" data-placeholder="-- {{ __('messages.select_class') }} --" data-dept-first="{{ __('messages.select_dept_first') }}" data-old-value="{{ old('class_id') }}" aria-describedby="@error('class_id') reg-class-error @enderror">
                        <option value="">{{ __('messages.select_dept_first') }}</option>
                    </select>
                    @error('class_id')
                        <span id="reg-class-error" class="field-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div id="teacher-notice" class="alert alert-warning hidden" role="status">
                    <span class="alert-icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    </span>
                    <div>{{ __('messages.teacher_registration_notice') }}</div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 1rem; font-weight: 600; margin-top: 1rem;">
                    {{ __('messages.register') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
