@extends('layouts.app')

@section('title', __('messages.login_title') . ' — STIC Info')

@section('content')
<div style="max-width: 520px; margin: 1.5rem auto; width: 100%;">
    <div class="card" style="padding: 2rem; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); background: #ffffff;">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🏫</div>
            <h1 style="font-size: 1.4rem; font-weight: 700; color: #1e293b; margin-bottom: 0.25rem;">{{ __('messages.app_name') }}</h1>
            <p style="font-size: 0.88rem; color: #64748b;">{{ __('messages.institute_name') }}</p>
        </div>

        <!-- Toggle Tabs -->
        <div style="display: flex; border-bottom: 2px solid #e2e8f0; margin-bottom: 1.5rem;" role="tablist">
            <button type="button" id="tab-login-btn" onclick="showTab('login')" role="tab" aria-selected="true" style="flex: 1; padding: 0.75rem; background: none; border: none; font-weight: 600; font-size: 1rem; cursor: pointer; border-bottom: 3px solid var(--primary); color: var(--primary); font-family: inherit;">
                {{ __('messages.login') }}
            </button>
            <button type="button" id="tab-register-btn" onclick="showTab('register')" role="tab" aria-selected="false" style="flex: 1; padding: 0.75rem; background: none; border: none; font-weight: 600; font-size: 1rem; cursor: pointer; border-bottom: 3px solid transparent; color: var(--text-muted); font-family: inherit;">
                {{ __('messages.register') }}
            </button>
        </div>

        <!-- Login Form -->
        <div id="login-form">
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="login-email" class="form-label">{{ __('messages.email') }}</label>
                    <input type="email" id="login-email" name="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="example@stic.edu.kh">
                </div>
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <label for="login-password" class="form-label" style="margin-bottom: 0;">{{ __('messages.password') }}</label>
                        <a href="{{ route('password.request') }}" style="font-size: 0.82rem; color: var(--primary); text-decoration: none;">
                            {{ __('messages.forgot_password') }}
                        </a>
                    </div>
                    <input type="password" id="login-password" name="password" class="form-control" required placeholder="••••••••">
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

        <!-- Register Form -->
        <div id="register-form" style="display: none;">
            <form action="{{ route('register') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="reg-name" class="form-label">{{ __('messages.full_name') }}</label>
                    <input type="text" id="reg-name" name="name" class="form-control" value="{{ old('name') }}" required placeholder="សុខ ចាន់">
                </div>
                <div class="form-group">
                    <label for="reg-email" class="form-label">{{ __('messages.email') }}</label>
                    <input type="email" id="reg-email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="sokchan@stic.edu.kh">
                </div>
                <div class="form-group">
                    <label for="reg-phone" class="form-label">{{ __('messages.phone') }}</label>
                    <input type="tel" id="reg-phone" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="012 345 678">
                </div>
                <div class="form-group">
                    <label for="reg-password" class="form-label">{{ __('messages.password') }} (Min 8)</label>
                    <input type="password" id="reg-password" name="password" class="form-control" required placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label for="reg-password-confirm" class="form-label">{{ __('messages.password_confirmation') }}</label>
                    <input type="password" id="reg-password-confirm" name="password_confirmation" class="form-control" required placeholder="••••••••">
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.role') }}</label>
                    <div style="display: flex; gap: 1.5rem; margin-top: 0.3rem;">
                        <label style="cursor: pointer; display: flex; align-items: center; gap: 6px;">
                            <input type="radio" name="role" value="student" checked onchange="handleRoleChange(this.value)">
                            <span>{{ __('messages.role_student') }}</span>
                        </label>
                        <label style="cursor: pointer; display: flex; align-items: center; gap: 6px;">
                            <input type="radio" name="role" value="teacher" onchange="handleRoleChange(this.value)">
                            <span>{{ __('messages.role_teacher') }}</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="reg-dept" class="form-label">{{ __('messages.departments') }}</label>
                    <select id="reg-dept" name="department_id" class="form-control" required onchange="filterClassesByDepartment(this.value)">
                        <option value="">-- {{ __('messages.select_department') }} --</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name_kh ?? $dept->name }} ({{ $dept->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" id="class-group">
                    <label for="reg-class" class="form-label">{{ __('messages.classes') }}</label>
                    <select id="reg-class" name="class_id" class="form-control">
                        <option value="">-- {{ __('messages.select_class') }} --</option>
                        @foreach ($departments as $dept)
                            @foreach ($dept->classes as $c)
                                <option value="{{ $c->id }}" data-dept="{{ $dept->id }}">
                                    {{ $c->name_kh ?? $c->name }} ({{ ucfirst($c->shift) }})
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                <div id="teacher-notice" class="alert alert-warning" style="display: none; font-size: 0.85rem; padding: 0.75rem; margin-bottom: 1rem;">
                    ℹ️ <strong>ចំណាំសម្រាប់គ្រូបង្រៀន៖</strong> បន្ទាប់ពីចុះឈ្មោះ គណនីរបស់អ្នកនឹងស្ថិតក្នុងស្ថានភាព <em>«រង់ចាំការអនុម័ត (Pending Approval)»</em> ពីអ្នកគ្រប់គ្រង (Admin) មុនពេលអាចចូលប្រើប្រព័ន្ធបាន។
                </div>

                <button type="submit" class="btn btn-secondary" style="width: 100%; padding: 0.75rem; font-size: 1rem; font-weight: 600;">
                    {{ __('messages.register') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function showTab(tab) {
        const loginBtn = document.getElementById('tab-login-btn');
        const regBtn = document.getElementById('tab-register-btn');
        const loginForm = document.getElementById('login-form');
        const regForm = document.getElementById('register-form');

        if (tab === 'login') {
            loginBtn.style.borderBottomColor = 'var(--primary)';
            loginBtn.style.color = 'var(--primary)';
            loginBtn.setAttribute('aria-selected', 'true');
            regBtn.style.borderBottomColor = 'transparent';
            regBtn.style.color = 'var(--text-muted)';
            regBtn.setAttribute('aria-selected', 'false');
            loginForm.style.display = 'block';
            regForm.style.display = 'none';
        } else {
            regBtn.style.borderBottomColor = 'var(--secondary)';
            regBtn.style.color = 'var(--secondary)';
            regBtn.setAttribute('aria-selected', 'true');
            loginBtn.style.borderBottomColor = 'transparent';
            loginBtn.style.color = 'var(--text-muted)';
            loginBtn.setAttribute('aria-selected', 'false');
            loginForm.style.display = 'none';
            regForm.style.display = 'block';
        }
    }

    function handleRoleChange(role) {
        const classGroup = document.getElementById('class-group');
        const teacherNotice = document.getElementById('teacher-notice');
        const classSelect = document.getElementById('reg-class');

        if (role === 'teacher') {
            classGroup.style.display = 'none';
            teacherNotice.style.display = 'block';
            classSelect.removeAttribute('required');
        } else {
            classGroup.style.display = 'block';
            teacherNotice.style.display = 'none';
            classSelect.setAttribute('required', 'required');
        }
    }

    function filterClassesByDepartment(deptId) {
        const classSelect = document.getElementById('reg-class');
        const options = classSelect.querySelectorAll('option');

        options.forEach(opt => {
            if (!opt.value) return; // Keep placeholder
            if (!deptId || opt.getAttribute('data-dept') === deptId) {
                opt.style.display = 'block';
            } else {
                opt.style.display = 'none';
            }
        });
        classSelect.value = '';
    }

    // Auto switch to register tab if there are registration validation errors
    @if ($errors->has('name') || $errors->has('phone') || $errors->has('department_id') || $errors->has('class_id') || $errors->has('password_confirmation'))
        showTab('register');
    @endif
</script>
@endsection
