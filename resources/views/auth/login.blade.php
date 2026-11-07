@extends('layouts.app')

@section('title', 'ចូលប្រព័ន្ធ / ចុះឈ្មោះ — STIC Info')

@section('content')
<div style="max-width: 600px; margin: 0 auto;">
    <div class="card">
        <!-- Tabs -->
        <div style="display: flex; border-bottom: 2px solid #f1f3f4; margin-bottom: 1.5rem;">
            <button type="button" id="tab-login-btn" onclick="showTab('login')" style="flex: 1; padding: 0.8rem; background: none; border: none; font-weight: 600; font-size: 1rem; cursor: pointer; border-bottom: 3px solid var(--primary); color: var(--primary); font-family: inherit;">
                ចូលប្រព័ន្ធ (Login)
            </button>
            <button type="button" id="tab-register-btn" onclick="showTab('register')" style="flex: 1; padding: 0.8rem; background: none; border: none; font-weight: 600; font-size: 1rem; cursor: pointer; border-bottom: 3px solid transparent; color: var(--text-muted); font-family: inherit;">
                ចុះឈ្មោះ (Register)
            </button>
        </div>

        <!-- Login Form -->
        <div id="login-form">
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">អ៊ីមែល (Email)</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="admin@stic.edu.kh">
                </div>
                <div class="form-group">
                    <label class="form-label">លេខសម្ងាត់ (Password)</label>
                    <input type="password" name="password" class="form-control" required placeholder="••••••••">
                </div>
                <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                    <label style="font-size: 0.9rem; cursor: pointer;">
                        <input type="checkbox" name="remember"> ចងចាំខ្ញុំ
                    </label>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">ចូលប្រព័ន្ធ</button>
            </form>
        </div>

        <!-- Register Form -->
        <div id="register-form" style="display: none;">
            <form action="{{ route('register') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">ឈ្មោះពេញ (Full Name)</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="សុខ ចាន់">
                </div>
                <div class="form-group">
                    <label class="form-label">អ៊ីមែល (Email)</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="sokchan@example.com">
                </div>
                <div class="form-group">
                    <label class="form-label">លេខទូរស័ព្ទ (Phone Number)</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="012 345 678">
                </div>
                <div class="form-group">
                    <label class="form-label">លេខសម្ងាត់ (Password)</label>
                    <input type="password" name="password" class="form-control" required placeholder="យ៉ាងតិច ៦ ខ្ទង់">
                </div>
                <div class="form-group">
                    <label class="form-label">បញ្ជាក់លេខសម្ងាត់ (Confirm Password)</label>
                    <input type="password" name="password_confirmation" class="form-control" required placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label class="form-label">តួនាទី (Role)</label>
                    <select name="role" id="role-select" class="form-control" onchange="toggleRoleFields()" required>
                        <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>សិស្ស (Student)</option>
                        <option value="teacher" {{ old('role') === 'teacher' ? 'selected' : '' }}>គ្រូបង្រៀន (Teacher) - ត្រូវការ Admin អនុម័ត</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">ដេប៉ាតឺម៉ង់ (Department)</label>
                    <select name="department_id" id="department-select" class="form-control" onchange="filterClasses()" required>
                        <option value="">-- ជ្រើសរើសដេប៉ាតឺម៉ង់ --</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name_kh ?? $dept->name }} ({{ $dept->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" id="class-group">
                    <label class="form-label">ថ្នាក់រៀន (Class)</label>
                    <select name="class_id" id="class-select" class="form-control">
                        <option value="">-- ជ្រើសរើសថ្នាក់រៀន --</option>
                        @foreach ($departments as $dept)
                            @foreach ($dept->classes as $c)
                                <option value="{{ $c->id }}" data-dept="{{ $dept->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name_kh ?? $c->name }} (បន្ទប់ {{ $c->room }})
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-success" style="width: 100%;">ចុះឈ្មោះគណនី</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function showTab(tab) {
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        const loginBtn = document.getElementById('tab-login-btn');
        const registerBtn = document.getElementById('tab-register-btn');

        if (tab === 'login') {
            loginForm.style.display = 'block';
            registerForm.style.display = 'none';
            loginBtn.style.borderBottomColor = 'var(--primary)';
            loginBtn.style.color = 'var(--primary)';
            registerBtn.style.borderBottomColor = 'transparent';
            registerBtn.style.color = 'var(--text-muted)';
            window.location.hash = '';
        } else {
            loginForm.style.display = 'none';
            registerForm.style.display = 'block';
            registerBtn.style.borderBottomColor = 'var(--primary)';
            registerBtn.style.color = 'var(--primary)';
            loginBtn.style.borderBottomColor = 'transparent';
            loginBtn.style.color = 'var(--text-muted)';
            window.location.hash = 'register';
        }
    }

    function toggleRoleFields() {
        const role = document.getElementById('role-select').value;
        const classGroup = document.getElementById('class-group');
        if (role === 'teacher') {
            classGroup.style.display = 'none';
        } else {
            classGroup.style.display = 'block';
        }
    }

    function filterClasses() {
        const deptId = document.getElementById('department-select').value;
        const classSelect = document.getElementById('class-select');
        const options = classSelect.querySelectorAll('option');

        options.forEach(opt => {
            if (!opt.value) return;
            if (opt.getAttribute('data-dept') === deptId) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        });
    }

    // Check hash on load
    if (window.location.hash === '#register') {
        showTab('register');
    }
    toggleRoleFields();
    filterClasses();
</script>
@endsection

