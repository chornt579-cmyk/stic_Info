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

<div id="auth-card-wrapper" data-initial-tab="{{ $initialTab }}" style="max-width: 520px; margin: 1.5rem auto; width: 100%;">
    <div class="card" style="padding: 2rem; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); background: #ffffff;">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;" aria-hidden="true">🏫</div>
            <h1 style="font-size: 1.4rem; font-weight: 700; color: #1e293b; margin-bottom: 0.25rem;">{{ config('app.name') }}</h1>
            <p style="font-size: 0.88rem; color: #64748b;">{{ __('messages.institute_name') }}</p>
        </div>

        <!-- Accessible Tabs Navigation -->
        <div style="display: flex; border-bottom: 2px solid #e2e8f0; margin-bottom: 1.5rem;" role="tablist" aria-label="Authentication Options">
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
                        <a href="{{ route('password.request') }}" style="font-size: 0.82rem; color: #1a73e8; text-decoration: none; font-weight: 500;">
                            {{ __('messages.forgot_password') }}
                        </a>
                    </div>
                    <div class="password-input-wrapper">
                        <input type="password" id="login-password" name="password" class="form-control @error('password') input-invalid @enderror" required autocomplete="current-password" placeholder="••••••••" aria-describedby="@error('password') login-password-error @enderror">
                        <button type="button" class="password-toggle-btn" data-target="login-password" aria-label="{{ __('messages.password_toggle') }}" aria-pressed="false">👁️‍🗨️</button>
                    </div>
                    @error('password')
                        <span id="login-password-error" class="field-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
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
                        <button type="button" class="password-toggle-btn" data-target="reg-password" aria-label="{{ __('messages.password_toggle') }}" aria-pressed="false">👁️‍🗨️</button>
                    </div>
                    @error('password')
                        <span id="reg-password-error" class="field-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="reg-password-confirm" class="form-label">{{ __('messages.password_confirmation') }}</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="reg-password-confirm" name="password_confirmation" class="form-control" required autocomplete="new-password" placeholder="••••••••">
                        <button type="button" class="password-toggle-btn" data-target="reg-password-confirm" aria-label="{{ __('messages.password_toggle') }}" aria-pressed="false">👁️‍🗨️</button>
                    </div>
                </div>

                <div class="form-group">
                    <span class="form-label" id="role-label">{{ __('messages.role') }}</span>
                    <div style="display: flex; gap: 1.5rem; margin-top: 0.35rem;" role="radiogroup" aria-labelledby="role-label">
                        <label style="cursor: pointer; display: flex; align-items: center; gap: 6px;">
                            <input type="radio" name="role" value="student" {{ old('role', 'student') === 'student' ? 'checked' : '' }}>
                            <span>{{ __('messages.role_student') }}</span>
                        </label>
                        <label style="cursor: pointer; display: flex; align-items: center; gap: 6px;">
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

                <div id="teacher-notice" class="alert alert-warning hidden" style="font-size: 0.88rem; padding: 0.85rem; margin-bottom: 1.25rem; line-height: 1.5;" role="status">
                    <span aria-hidden="true">ℹ️</span> {{ __('messages.teacher_registration_notice') }}
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 1rem; font-weight: 600; background: #1a73e8; color: #ffffff;">
                    {{ __('messages.register') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
