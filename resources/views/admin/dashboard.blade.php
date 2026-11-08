@extends('layouts.app')

@section('title', __('messages.admin_dashboard_title') . ' — ' . config('app.name'))

@section('content')
<div style="margin-bottom: 2rem;">
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.6rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                {{ __('messages.admin_dashboard_title') }}
            </h1>
            <p style="color: #64748b; font-size: 0.92rem; margin-top: 0.25rem;">
                {{ __('messages.institute_name') }}
            </p>
        </div>
        <div>
            <span class="badge badge-admin" style="font-size: 0.88rem; padding: 0.4rem 0.9rem; font-weight: 600;">
                {{ __('messages.role_admin') }}
            </span>
        </div>
    </div>

    <!-- TABS SUB-NAVIGATION -->
    <div id="admin-tabs-nav" style="display: flex; gap: 0.5rem; border-bottom: 2px solid #e2e8f0; margin-bottom: 1.5rem; overflow-x: auto; padding-bottom: 0.4rem;" role="tablist" aria-label="Admin Navigation Tabs">
        <button type="button" class="admin-tab-btn tab-btn tab-active" data-tab="overview" role="tab" aria-selected="true">
            {{ __('messages.dashboard') }}
        </button>
        <button type="button" class="admin-tab-btn tab-btn tab-inactive" data-tab="approvals" role="tab" aria-selected="false">
            {{ __('messages.pending_approvals') }}
            @if ($pendingTeachers->count() > 0)
                <span class="badge badge-danger" style="margin-left: 6px; font-size: 0.75rem;">{{ $pendingTeachers->count() }}</span>
            @endif
        </button>
        <button type="button" class="admin-tab-btn tab-btn tab-inactive" data-tab="departments" role="tab" aria-selected="false">
            {{ __('messages.departments') }}
        </button>
        <button type="button" class="admin-tab-btn tab-btn tab-inactive" data-tab="classes" role="tab" aria-selected="false">
            {{ __('messages.classes') }}
        </button>
        <button type="button" class="admin-tab-btn tab-btn tab-inactive" data-tab="teachers" role="tab" aria-selected="false">
            {{ __('messages.teachers') }}
        </button>
        <button type="button" class="admin-tab-btn tab-btn tab-inactive" data-tab="students" role="tab" aria-selected="false">
            {{ __('messages.students') }}
        </button>
        <button type="button" class="admin-tab-btn tab-btn tab-inactive" data-tab="schedules" role="tab" aria-selected="false">
            {{ __('messages.schedules') }}
        </button>
        <button type="button" class="admin-tab-btn tab-btn tab-inactive" data-tab="rooms" role="tab" aria-selected="false">
            {{ __('messages.rooms') }}
        </button>
        <button type="button" class="admin-tab-btn tab-btn tab-inactive" data-tab="audit-logs" role="tab" aria-selected="false">
            {{ __('messages.audit_logs') }}
        </button>
    </div>

    <!-- ========================================== -->
    <!-- TAB 1: EXECUTIVE OVERVIEW (ទិដ្ឋភាពទូទៅ)    -->
    <!-- ========================================== -->
    <div id="panel-overview" class="admin-panel">
        <!-- 1. System Statistics Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <!-- Departments Card -->
            <div class="card" style="padding: 1.25rem; border-left: 4px solid #1a73e8; background: #ffffff;">
                <div style="color: #64748b; font-size: 0.85rem; font-weight: 600;">{{ __('messages.total_departments') }}</div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #1e293b; margin-top: 0.3rem;">{{ $stats['total_departments'] }}</div>
            </div>

            <!-- Classes Card -->
            <div class="card" style="padding: 1.25rem; border-left: 4px solid #34a853; background: #ffffff;">
                <div style="color: #64748b; font-size: 0.85rem; font-weight: 600;">{{ __('messages.total_classes') }}</div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #1e293b; margin-top: 0.3rem;">{{ $stats['total_classes'] }}</div>
            </div>

            <!-- Active Teachers Card -->
            <div class="card" style="padding: 1.25rem; border-left: 4px solid #fbbc04; background: #ffffff;">
                <div style="color: #64748b; font-size: 0.85rem; font-weight: 600;">{{ __('messages.total_teachers') }}</div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #1e293b; margin-top: 0.3rem;">{{ $stats['total_teachers'] }}</div>
            </div>

            <!-- Pending Approvals Card (Alert) -->
            <div class="card" style="padding: 1.25rem; border-left: 4px solid {{ $stats['pending_teachers'] > 0 ? '#ea4335' : '#bdc1c6' }}; background: {{ $stats['pending_teachers'] > 0 ? '#fef2f2' : '#ffffff' }};">
                <div style="color: {{ $stats['pending_teachers'] > 0 ? '#ea4335' : '#64748b' }}; font-size: 0.85rem; font-weight: 600;">
                    {{ __('messages.pending_approvals') }}
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: {{ $stats['pending_teachers'] > 0 ? '#ea4335' : '#1e293b' }}; margin-top: 0.3rem;">
                    {{ $stats['pending_teachers'] }}
                </div>
            </div>

            <!-- Students Card -->
            <div class="card" style="padding: 1.25rem; border-left: 4px solid #8b5cf6; background: #ffffff;">
                <div style="color: #64748b; font-size: 0.85rem; font-weight: 600;">{{ __('messages.total_students') }}</div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #1e293b; margin-top: 0.3rem;">{{ $stats['total_students'] }}</div>
            </div>

            <!-- Rooms Card -->
            <div class="card" style="padding: 1.25rem; border-left: 4px solid #06b6d4; background: #ffffff;">
                <div style="color: #64748b; font-size: 0.85rem; font-weight: 600;">{{ __('messages.rooms') }}</div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #1e293b; margin-top: 0.3rem;">{{ $stats['total_rooms'] }}</div>
            </div>

            <!-- Schedules Card -->
            <div class="card" style="padding: 1.25rem; border-left: 4px solid #f97316; background: #ffffff;">
                <div style="color: #64748b; font-size: 0.85rem; font-weight: 600;">{{ __('messages.active_schedules') }}</div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #1e293b; margin-top: 0.3rem;">{{ $stats['total_schedules'] }}</div>
            </div>

            <!-- Audit Logs Card -->
            <div class="card" style="padding: 1.25rem; border-left: 4px solid #0284c7; background: #ffffff;">
                <div style="color: #64748b; font-size: 0.85rem; font-weight: 600;">{{ __('messages.audit_logs') }}</div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #1e293b; margin-top: 0.3rem;">{{ $stats['total_logs'] }}</div>
            </div>
        </div>

        <!-- 2. Urgent Attention: Pending Teacher Approvals Alert (if any) -->
        @if ($pendingTeachers->isNotEmpty())
            <div class="card" style="background: #ffffff; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; border-top: 4px solid #ea4335;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <h2 style="font-size: 1.2rem; font-weight: 700; color: #b91c1c; display: flex; align-items: center; gap: 8px;">
                            <span>សំណើចុះឈ្មោះគ្រូបង្រៀនកំពុងរង់ចាំការអនុម័ត</span>
                            <span class="badge badge-danger">{{ $pendingTeachers->count() }}</span>
                        </h2>
                        <p style="font-size: 0.85rem; color: #64748b; margin-top: 0.2rem;">
                            ទាមទារការត្រួតពិនិត្យ និងអនុម័តដោយផ្ទាល់ពីអ្នកគ្រប់គ្រង មុនពេលគ្រូបង្រៀនអាចចូលប្រើប្រព័ន្ធ
                        </p>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary quick-jump-btn" data-jump-tab="approvals">
                        ពិនិត្យសំណើទាំងអស់ &rarr;
                    </button>
                </div>
                <div style="overflow-x: auto; width: 100%;">
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #fef2f2; border-bottom: 2px solid #fecaca; text-align: left;">
                                <th style="padding: 0.65rem;">{{ __('messages.full_name') }}</th>
                                <th style="padding: 0.65rem;">{{ __('messages.email') }}</th>
                                <th style="padding: 0.65rem;">{{ __('messages.phone') }}</th>
                                <th style="padding: 0.65rem;">{{ __('messages.departments') }}</th>
                                <th style="padding: 0.65rem;">កាលបរិច្ឆេទ</th>
                                <th style="padding: 0.65rem; text-align: right;">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingTeachers->take(5) as $pt)
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 0.65rem; font-weight: 600;">{{ $pt->name }}</td>
                                    <td style="padding: 0.65rem;">{{ $pt->email }}</td>
                                    <td style="padding: 0.65rem;">{{ $pt->phone ?? '-' }}</td>
                                    <td style="padding: 0.65rem;">
                                        <span class="badge" style="background: #e8f0fe; color: #1a73e8;">
                                            {{ $pt->department ? ($pt->department->name_kh ?? $pt->department->name) : '-' }}
                                        </span>
                                    </td>
                                    <td style="padding: 0.65rem; font-size: 0.85rem; color: #64748b;">
                                        {{ $pt->created_at->timezone('Asia/Phnom_Penh')->format('d/m/Y H:i') }}
                                    </td>
                                    <td style="padding: 0.65rem; text-align: right;">
                                        <div style="display: inline-flex; gap: 6px;">
                                            <form action="{{ route('admin.teachers.approve', $pt) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm" style="background: #34a853; color: #ffffff; padding: 0.3rem 0.75rem; font-size: 0.82rem; border-radius: 6px;">
                                                    {{ __('messages.approve') }}
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.teachers.reject', $pt) }}" method="POST" onsubmit="return confirm('តើអ្នកប្រាកដជាចង់បដិសេធគ្រូបង្រៀនរូបនេះមែនទេ?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm" style="background: #ea4335; color: #ffffff; padding: 0.3rem 0.75rem; font-size: 0.82rem; border-radius: 6px;">
                                                    {{ __('messages.reject') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- 3. Quick Data Navigation Hub -->
        <div style="margin-bottom: 2rem;">
            <div style="margin-bottom: 1rem;">
                <h2 style="font-size: 1.25rem; font-weight: 700; color: #1e293b;">
                    ទិន្នន័យគ្រប់គ្រងស្ថាប័ន (Institutional Management Data)
                </h2>
                <p style="font-size: 0.88rem; color: #64748b; margin-top: 0.2rem;">
                    ចុចលើផ្នែកនីមួយៗដើម្បីពិនិត្យ ស្វែងរក និងគ្រប់គ្រងទិន្នន័យលម្អិត
                </p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
                <!-- Departments Shortcut Card -->
                <div class="card" style="background: #ffffff; padding: 1.25rem; border-radius: 10px; border: 1px solid #e2e8f0; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span style="font-weight: 700; color: #1e293b; font-size: 1rem;">{{ __('messages.departments') }}</span>
                            <span class="badge" style="background: #e8f0fe; color: #1a73e8;">{{ $stats['total_departments'] }} សរុប</span>
                        </div>
                        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1rem;">
                            ដេប៉ាតឺម៉ង់បណ្តុះបណ្តាល និងជំនាញបច្ចេកទេសទាំងអស់ក្នុងវិទ្យាស្ថាន
                        </p>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline quick-jump-btn" data-jump-tab="departments" style="width: 100%; border-color: #1a73e8; color: #1a73e8;">
                        ពិនិត្យបញ្ជីដេប៉ាតឺម៉ង់ &rarr;
                    </button>
                </div>

                <!-- Classes Shortcut Card -->
                <div class="card" style="background: #ffffff; padding: 1.25rem; border-radius: 10px; border: 1px solid #e2e8f0; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span style="font-weight: 700; color: #1e293b; font-size: 1rem;">{{ __('messages.classes') }}</span>
                            <span class="badge" style="background: #e8f5ee; color: #21865b;">{{ $stats['total_classes'] }} ថ្នាក់</span>
                        </div>
                        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1rem;">
                            ថ្នាក់រៀនតាមវេនព្រឹក រសៀល យប់ និងកម្រិតឆ្នាំសិក្សានីមួយៗ
                        </p>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline quick-jump-btn" data-jump-tab="classes" style="width: 100%; border-color: #21865b; color: #21865b;">
                        ពិនិត្យបញ្ជីថ្នាក់រៀន &rarr;
                    </button>
                </div>

                <!-- Schedules Shortcut Card -->
                <div class="card" style="background: #ffffff; padding: 1.25rem; border-radius: 10px; border: 1px solid #e2e8f0; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span style="font-weight: 700; color: #1e293b; font-size: 1rem;">{{ __('messages.schedules') }}</span>
                            <span class="badge" style="background: #fef6e9; color: #d99a25;">{{ $stats['total_schedules'] }} ម៉ោង</span>
                        </div>
                        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1rem;">
                            កាលវិភាគបង្រៀន ៧ ថ្ងៃក្នុងមួយសប្តាហ៍ ជាមួយម៉ាស៊ីនការពារការជាន់ម៉ោង
                        </p>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline quick-jump-btn" data-jump-tab="schedules" style="width: 100%; border-color: #d99a25; color: #d99a25;">
                        ពិនិត្យកាលវិភាគ &rarr;
                    </button>
                </div>

                <!-- Rooms Shortcut Card -->
                <div class="card" style="background: #ffffff; padding: 1.25rem; border-radius: 10px; border: 1px solid #e2e8f0; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span style="font-weight: 700; color: #1e293b; font-size: 1rem;">{{ __('messages.rooms') }}</span>
                            <span class="badge" style="background: #ecfeff; color: #0891b2;">{{ $stats['total_rooms'] }} បន្ទប់</span>
                        </div>
                        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1rem;">
                            បន្ទប់ទ្រឹស្តី បន្ទប់ពិសោធន៍កុំព្យូទ័រ និងរោងជាងបច្ចេកទេស
                        </p>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline quick-jump-btn" data-jump-tab="rooms" style="width: 100%; border-color: #0891b2; color: #0891b2;">
                        ពិនិត្យបន្ទប់រៀន &rarr;
                    </button>
                </div>
            </div>
        </div>

        <!-- 4. Two-Column Activity & Announcements Snapshot -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.5rem;">
            <!-- Left: Recent System Audit Activities -->
            <div class="card" style="background: #ffffff; padding: 1.5rem; border-radius: 10px; border: 1px solid #e2e8f0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: #1e293b; margin: 0;">
                        កំណត់ត្រាសកម្មភាពថ្មីៗ (Recent Audit Logs)
                    </h3>
                    <button type="button" class="btn btn-sm btn-outline quick-jump-btn" data-jump-tab="audit-logs">
                        មើលទាំងអស់ &rarr;
                    </button>
                </div>
                <div style="overflow-x: auto;">
                    <table class="table" style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left;">
                                <th style="padding: 0.6rem;">កាលបរិច្ឆេទ</th>
                                <th style="padding: 0.6rem;">សកម្មភាព (Event)</th>
                                <th style="padding: 0.6rem;">អ្នកធ្វើ (Actor)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($auditLogs->take(6) as $log)
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 0.6rem; color: #64748b; white-space: nowrap;">
                                        {{ $log->created_at->timezone('Asia/Phnom_Penh')->format('d/m H:i') }}
                                    </td>
                                    <td style="padding: 0.6rem;">
                                        <span class="badge" style="background: #f1f5f9; color: #0f172a; font-family: monospace; font-size: 0.78rem;">
                                            {{ $log->action }}
                                        </span>
                                    </td>
                                    <td style="padding: 0.6rem; font-weight: 600;">
                                        {{ $log->user ? $log->user->name : 'System' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="padding: 1.5rem; text-align: center; color: #94a3b8;">
                                        {{ __('messages.empty_data') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right: Institutional Announcements Snapshot -->
            <div class="card" style="background: #ffffff; padding: 1.5rem; border-radius: 10px; border: 1px solid #e2e8f0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: #1e293b; margin: 0;">
                        {{ __('messages.official_announcements') }}
                    </h3>
                    <span class="badge" style="background: #e8f0fe; color: #1a73e8;">
                        {{ $announcements->count() }} ចុងក្រោយ
                    </span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    @forelse ($announcements->take(4) as $ann)
                        <div style="padding: 0.85rem; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.25rem;">
                                <h4 style="font-size: 0.95rem; font-weight: 700; color: #1e293b; margin: 0;">
                                    {{ $ann->title }}
                                </h4>
                                <span class="badge" style="background: #e8f0fe; color: #1a73e8; font-size: 0.72rem; white-space: nowrap;">
                                    {{ ucfirst($ann->target_audience) }}
                                </span>
                            </div>
                            <p style="font-size: 0.82rem; color: #475569; margin-bottom: 0.4rem; line-height: 1.4;">
                                {{ Str::limit($ann->content, 90) }}
                            </p>
                            <div style="font-size: 0.75rem; color: #94a3b8; display: flex; justify-content: space-between;">
                                <span>{{ $ann->author ? $ann->author->name : 'Admin' }}</span>
                                <span>{{ $ann->created_at->timezone('Asia/Phnom_Penh')->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    @empty
                        <div style="padding: 2rem; text-align: center; color: #94a3b8;">
                            {{ __('messages.no_public_announcements') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 2: PENDING TEACHER APPROVALS           -->
    <!-- ========================================== -->
    <div id="panel-approvals" class="admin-panel hidden">
        <div class="card" style="background: #ffffff; padding: 1.5rem; border-radius: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
                <div>
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0;">
                        {{ __('messages.pending_approvals') }} ({{ $pendingTeachers->count() }})
                    </h2>
                    <p style="font-size: 0.85rem; color: #64748b; margin-top: 0.2rem;">
                        រាល់ការអនុម័ត ឬបដិសេធ ត្រូវបានដំណើរការដោយសុវត្ថិភាព Database Transaction និង Pessimistic Row Locking
                    </p>
                </div>
            </div>

            @if ($pendingTeachers->isEmpty())
                <div style="text-align: center; padding: 3rem 1rem; color: #64748b;">
                    <p style="font-weight: 600; font-size: 1.05rem;">គ្មានសំណើសុំចុះឈ្មោះគ្រូដែលកំពុងរង់ចាំឡើយ!</p>
                    <p style="font-size: 0.88rem; margin-top: 0.25rem;">គណនីគ្រូទាំងអស់ត្រូវបានអនុម័តរួចរាល់។</p>
                </div>
            @else
                <div style="overflow-x: auto; width: 100%;">
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left;">
                                <th style="padding: 0.75rem;">{{ __('messages.full_name') }}</th>
                                <th style="padding: 0.75rem;">{{ __('messages.email') }}</th>
                                <th style="padding: 0.75rem;">{{ __('messages.phone') }}</th>
                                <th style="padding: 0.75rem;">{{ __('messages.departments') }}</th>
                                <th style="padding: 0.75rem;">កាលបរិច្ឆេទចុះឈ្មោះ</th>
                                <th style="padding: 0.75rem; text-align: right;">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingTeachers as $pt)
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 0.75rem; font-weight: 600;">{{ $pt->name }}</td>
                                    <td style="padding: 0.75rem;">{{ $pt->email }}</td>
                                    <td style="padding: 0.75rem;">{{ $pt->phone ?? '-' }}</td>
                                    <td style="padding: 0.75rem;">
                                        <span class="badge" style="background: #e8f0fe; color: #1a73e8;">
                                            {{ $pt->department ? ($pt->department->name_kh ?? $pt->department->name) : '-' }}
                                        </span>
                                    </td>
                                    <td style="padding: 0.75rem; font-size: 0.85rem; color: #64748b;">
                                        {{ $pt->created_at->timezone('Asia/Phnom_Penh')->format('d/m/Y H:i') }}
                                    </td>
                                    <td style="padding: 0.75rem; text-align: right;">
                                        <div style="display: inline-flex; gap: 8px;">
                                            <form action="{{ route('admin.teachers.approve', $pt) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm" style="background: #34a853; color: #ffffff; padding: 0.35rem 0.8rem; font-size: 0.85rem; border-radius: 6px;">
                                                    {{ __('messages.approve') }}
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.teachers.reject', $pt) }}" method="POST" onsubmit="return confirm('តើអ្នកប្រាកដជាចង់បដិសេធគ្រូបង្រៀនរូបនេះមែនទេ?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm" style="background: #ea4335; color: #ffffff; padding: 0.35rem 0.8rem; font-size: 0.85rem; border-radius: 6px;">
                                                    {{ __('messages.reject') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 3: DEPARTMENTS MANAGEMENT              -->
    <!-- ========================================== -->
    <div id="panel-departments" class="admin-panel hidden">
        <!-- Section Header with Title & Add New Button -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 style="font-size: 1.3rem; font-weight: 700; color: #1e293b; margin: 0;">
                    {{ __('messages.departments') }} ({{ $departments->count() }})
                </h2>
                <p style="font-size: 0.88rem; color: #64748b; margin-top: 0.2rem;">
                    បញ្ជីដេប៉ាតឺម៉ង់បណ្តុះបណ្តាលផ្លូវការរបស់វិទ្យាស្ថាន STIC
                </p>
            </div>
            <button type="button" class="btn btn-primary toggle-create-btn" data-target="create-dept-card" style="display: inline-flex; align-items: center; gap: 6px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>{{ __('messages.create') }} {{ __('messages.departments') }}</span>
            </button>
        </div>

        <!-- Collapsible Create Department Form (Hidden by default unless validation error) -->
        <div id="create-dept-card" class="card collapsible-form-card" style="background: #ffffff; padding: 1.5rem; border-radius: 10px; margin-bottom: 1.5rem; border: 1px solid #dbeafe; box-shadow: 0 4px 12px rgba(48, 58, 152, 0.06); {{ $errors->hasAny(['code', 'name', 'name_kh', 'description']) ? 'display: block;' : 'display: none;' }}">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid #e2e8f0;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--stic-primary-dark); margin: 0;">
                    {{ __('messages.create') }} {{ __('messages.departments') }}
                </h3>
                <button type="button" class="btn btn-sm btn-secondary close-create-btn" data-target="create-dept-card">
                    {{ __('messages.cancel') }}
                </button>
            </div>
            <form action="{{ route('admin.departments.store') }}" method="POST">
                @csrf
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="dept-code" class="form-label">{{ __('messages.dept_code') }} (e.g. IT, CE) *</label>
                        <input type="text" id="dept-code" name="code" class="form-control" required maxlength="10" placeholder="IT" value="{{ old('code') }}">
                    </div>
                    <div class="form-group">
                        <label for="dept-name" class="form-label">{{ __('messages.dept_name') }} (English) *</label>
                        <input type="text" id="dept-name" name="name" class="form-control" required placeholder="Information Technology" value="{{ old('name') }}">
                    </div>
                    <div class="form-group">
                        <label for="dept-name-kh" class="form-label">{{ __('messages.dept_name_kh') }}</label>
                        <input type="text" id="dept-name-kh" name="name_kh" class="form-control" placeholder="បច្ចេកវិទ្យាព័ត៌មាន" value="{{ old('name_kh') }}">
                    </div>
                </div>
                <div class="form-group" style="margin-top: 0.5rem;">
                    <label for="dept-desc" class="form-label">{{ __('messages.description') }}</label>
                    <textarea id="dept-desc" name="description" class="form-control" rows="2" placeholder="ព័ត៌មានបន្ថែមអំពីដេប៉ាតឺម៉ង់...">{{ old('description') }}</textarea>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem;">
                    <button type="button" class="btn btn-secondary close-create-btn" data-target="create-dept-card">
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{ __('messages.save') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Departments Table (Full-Width 100%) -->
        <div class="card" style="background: #ffffff; padding: 1.5rem; border-radius: 10px;">
            <div style="overflow-x: auto; width: 100%;">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left;">
                            <th style="padding: 0.75rem;">{{ __('messages.dept_code') }}</th>
                            <th style="padding: 0.75rem;">{{ __('messages.departments') }}</th>
                            <th style="padding: 0.75rem;">ថ្នាក់រៀនសរុប</th>
                            <th style="padding: 0.75rem;">សិស្ស និងគ្រូបង្រៀន</th>
                            <th style="padding: 0.75rem; text-align: right;">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($departments as $dept)
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 0.75rem; font-weight: 700; color: #1a73e8;">{{ $dept->code }}</td>
                                <td style="padding: 0.75rem;">
                                    <div style="font-weight: 600;">{{ $dept->name_kh ?? $dept->name }}</div>
                                    <div style="font-size: 0.8rem; color: #64748b;">{{ $dept->name }}</div>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <span class="badge" style="background: #f1f5f9; color: #334155;">
                                        {{ $dept->classes_count }} ថ្នាក់
                                    </span>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <span class="badge" style="background: #e8f0fe; color: #1a73e8;">
                                        {{ $dept->users_count }} នាក់
                                    </span>
                                </td>
                                <td style="padding: 0.75rem; text-align: right;">
                                    <form action="{{ route('admin.departments.destroy', $dept) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete') }}');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm" style="background: #ea4335; color: #ffffff; border-radius: 4px; padding: 0.3rem 0.7rem; font-size: 0.82rem;">
                                            {{ __('messages.delete') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding: 2.5rem; text-align: center; color: #94a3b8;">
                                    {{ __('messages.empty_data') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 4: CLASSES MANAGEMENT                  -->
    <!-- ========================================== -->
    <div id="panel-classes" class="admin-panel hidden">
        <!-- Section Header with Title & Add New Button -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 style="font-size: 1.3rem; font-weight: 700; color: #1e293b; margin: 0;">
                    {{ __('messages.classes') }} ({{ $classes->count() }})
                </h2>
                <p style="font-size: 0.88rem; color: #64748b; margin-top: 0.2rem;">
                    បញ្ជីថ្នាក់រៀនតាមវេន និងឆ្នាំសិក្សានីមួយៗក្នុងដេប៉ាតឺម៉ង់
                </p>
            </div>
            <button type="button" class="btn btn-primary toggle-create-btn" data-target="create-class-card" style="display: inline-flex; align-items: center; gap: 6px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>{{ __('messages.create') }} {{ __('messages.classes') }}</span>
            </button>
        </div>

        <!-- Collapsible Create Class Form -->
        <div id="create-class-card" class="card collapsible-form-card" style="background: #ffffff; padding: 1.5rem; border-radius: 10px; margin-bottom: 1.5rem; border: 1px solid #dbeafe; box-shadow: 0 4px 12px rgba(48, 58, 152, 0.06); {{ $errors->hasAny(['department_id', 'name', 'name_kh', 'year', 'shift', 'room', 'capacity']) ? 'display: block;' : 'display: none;' }}">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid #e2e8f0;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--stic-primary-dark); margin: 0;">
                    {{ __('messages.create') }} {{ __('messages.classes') }}
                </h3>
                <button type="button" class="btn btn-sm btn-secondary close-create-btn" data-target="create-class-card">
                    {{ __('messages.cancel') }}
                </button>
            </div>
            <form action="{{ route('admin.classes.store') }}" method="POST">
                @csrf
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="cls-dept" class="form-label">{{ __('messages.departments') }} *</label>
                        <select id="cls-dept" name="department_id" class="form-control" required>
                            <option value="">-- {{ __('messages.select_department') }} --</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name_kh ?? $dept->name }} ({{ $dept->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cls-name" class="form-label">{{ __('messages.class_name') }} (English) *</label>
                        <input type="text" id="cls-name" name="name" class="form-control" required placeholder="IT Year 1 Morning" value="{{ old('name') }}">
                    </div>
                    <div class="form-group">
                        <label for="cls-name-kh" class="form-label">{{ __('messages.class_name_kh') }}</label>
                        <input type="text" id="cls-name-kh" name="name_kh" class="form-control" placeholder="IT ឆ្នាំ១ ព្រឹក" value="{{ old('name_kh') }}">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-top: 0.75rem;">
                    <div class="form-group">
                        <label for="cls-year" class="form-label">{{ __('messages.year') }} (1-5) *</label>
                        <input type="number" id="cls-year" name="year" class="form-control" min="1" max="5" value="{{ old('year', 1) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="cls-shift" class="form-label">{{ __('messages.shift') }} *</label>
                        <select id="cls-shift" name="shift" class="form-control" required>
                            <option value="morning" {{ old('shift') == 'morning' ? 'selected' : '' }}>{{ __('messages.shift_morning') }}</option>
                            <option value="afternoon" {{ old('shift') == 'afternoon' ? 'selected' : '' }}>{{ __('messages.shift_afternoon') }}</option>
                            <option value="evening" {{ old('shift') == 'evening' ? 'selected' : '' }}>{{ __('messages.shift_evening') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cls-room" class="form-label">{{ __('messages.room') }}</label>
                        <input type="text" id="cls-room" name="room" class="form-control" placeholder="101" value="{{ old('room') }}">
                    </div>
                    <div class="form-group">
                        <label for="cls-capacity" class="form-label">{{ __('messages.capacity') }}</label>
                        <input type="number" id="cls-capacity" name="capacity" class="form-control" value="{{ old('capacity', 35) }}">
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem;">
                    <button type="button" class="btn btn-secondary close-create-btn" data-target="create-class-card">
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{ __('messages.save') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Classes Table (Full-Width 100%) -->
        <div class="card" style="background: #ffffff; padding: 1.5rem; border-radius: 10px;">
            <div style="overflow-x: auto; width: 100%;">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left;">
                            <th style="padding: 0.75rem;">{{ __('messages.classes') }}</th>
                            <th style="padding: 0.75rem;">{{ __('messages.departments') }}</th>
                            <th style="padding: 0.75rem;">{{ __('messages.shift') }}</th>
                            <th style="padding: 0.75rem;">{{ __('messages.room') }}</th>
                            <th style="padding: 0.75rem;">សិស្សចុះឈ្មោះ</th>
                            <th style="padding: 0.75rem; text-align: right;">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($classes as $c)
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 0.75rem; font-weight: 600;">
                                    {{ $c->name_kh ?? $c->name }}
                                    <div style="font-size: 0.8rem; color: #64748b;">ឆ្នាំទី {{ $c->year }} ({{ $c->name }})</div>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <span class="badge" style="background: #e8f0fe; color: #1a73e8;">
                                        {{ $c->department->code }}
                                    </span>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <span class="badge" style="background: #f1f5f9; color: #334155;">
                                        {{ __('messages.shift_' . $c->shift) }}
                                    </span>
                                </td>
                                <td style="padding: 0.75rem;">{{ $c->room ?? '-' }}</td>
                                <td style="padding: 0.75rem;">
                                    <span class="badge" style="background: #f3e8ff; color: #7e22ce;">
                                        {{ $c->students_count }} នាក់
                                    </span>
                                </td>
                                <td style="padding: 0.75rem; text-align: right;">
                                    <form action="{{ route('admin.classes.destroy', $c) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete') }}');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm" style="background: #ea4335; color: #ffffff; border-radius: 4px; padding: 0.3rem 0.7rem; font-size: 0.82rem;">
                                            {{ __('messages.delete') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 2.5rem; text-align: center; color: #94a3b8;">
                                    {{ __('messages.empty_data') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 5: TEACHERS DIRECTORY                  -->
    <!-- ========================================== -->
    <div id="panel-teachers" class="admin-panel hidden">
        <div class="card" style="background: #ffffff; padding: 1.5rem; border-radius: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
                <div>
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0;">
                        បញ្ជីគ្រូបង្រៀនទាំងអស់ (Teachers Directory)
                    </h2>
                    <p style="font-size: 0.88rem; color: #64748b; margin-top: 0.2rem;">
                        ទិន្នន័យលោកគ្រូ អ្នកគ្រូ ដេប៉ាតឺម៉ង់ និងថ្នាក់រៀនដែលបានចាត់តាំង
                    </p>
                </div>
            </div>

            <div style="overflow-x: auto; width: 100%;">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left;">
                            <th style="padding: 0.75rem;">{{ __('messages.full_name') }}</th>
                            <th style="padding: 0.75rem;">{{ __('messages.email') }}</th>
                            <th style="padding: 0.75rem;">{{ __('messages.phone') }}</th>
                            <th style="padding: 0.75rem;">{{ __('messages.departments') }}</th>
                            <th style="padding: 0.75rem;">ថ្នាក់បង្រៀន (Assigned Classes)</th>
                            <th style="padding: 0.75rem;">{{ __('messages.status') }}</th>
                            <th style="padding: 0.75rem; text-align: right;">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($teachers as $t)
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 0.75rem; font-weight: 600;">{{ $t->name }}</td>
                                <td style="padding: 0.75rem;">{{ $t->email }}</td>
                                <td style="padding: 0.75rem;">{{ $t->phone ?? '-' }}</td>
                                <td style="padding: 0.75rem;">
                                    <span class="badge" style="background: #e8f0fe; color: #1a73e8;">
                                        {{ $t->department ? ($t->department->name_kh ?? $t->department->name) : '-' }}
                                    </span>
                                </td>
                                <td style="padding: 0.75rem;">
                                    @if ($t->teachingClasses->isEmpty())
                                        <span style="color: #94a3b8; font-size: 0.85rem;">មិនទាន់ចាត់តាំង</span>
                                    @else
                                        @foreach ($t->teachingClasses as $tc)
                                            <span class="badge badge-student" style="font-size: 0.75rem; margin-right: 4px;">
                                                {{ $tc->name_kh ?? $tc->name }}
                                            </span>
                                        @endforeach
                                    @endif
                                </td>
                                <td style="padding: 0.75rem;">
                                    <span class="badge badge-{{ $t->status }}">
                                        {{ __('messages.status_' . $t->status) }}
                                    </span>
                                </td>
                                <td style="padding: 0.75rem; text-align: right;">
                                    <div style="display: inline-flex; gap: 6px;">
                                        @if ($t->status === 'active')
                                            <form action="{{ route('admin.users.status', $t) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="suspended">
                                                <button type="submit" class="btn btn-sm" style="background: #fbbc04; color: #1e293b; padding: 0.3rem 0.65rem; font-size: 0.8rem; border-radius: 4px;">
                                                    {{ __('messages.suspend') }}
                                                </button>
                                            </form>
                                        @elseif ($t->status === 'suspended')
                                            <form action="{{ route('admin.users.status', $t) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" class="btn btn-sm" style="background: #34a853; color: #ffffff; padding: 0.3rem 0.65rem; font-size: 0.8rem; border-radius: 4px;">
                                                    {{ __('messages.activate') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding: 2.5rem; text-align: center; color: #94a3b8;">
                                    {{ __('messages.empty_data') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1rem;">
                {{ $teachers->appends(['tab' => 'teachers'])->links() }}
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 6: STUDENTS DIRECTORY                  -->
    <!-- ========================================== -->
    <div id="panel-students" class="admin-panel hidden">
        <div class="card" style="background: #ffffff; padding: 1.5rem; border-radius: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0;">
                        បញ្ជីសិស្ស/និស្សិត (Students Directory)
                    </h2>
                    <p style="font-size: 0.88rem; color: #64748b; margin-top: 0.2rem;">
                        ទិន្នន័យសិស្ស/និស្សិតដែលបានចុះឈ្មោះក្នុងប្រព័ន្ធ
                    </p>
                </div>

                <!-- Search and Filter Form -->
                <form action="{{ route('admin.dashboard') }}" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <input type="hidden" name="tab" value="students">
                    <input type="text" name="student_search" class="form-control" style="width: 220px;" placeholder="ស្វែងរកតាមឈ្មោះ/Email..." value="{{ request('student_search') }}">
                    <select name="student_dept_id" class="form-control" style="width: 180px;">
                        <option value="">-- គ្រប់ដេប៉ាតឺម៉ង់ --</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('student_dept_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->code }} - {{ $dept->name_kh ?? $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">ស្វែងរក</button>
                    @if (request()->hasAny(['student_search', 'student_dept_id']))
                        <a href="{{ route('admin.dashboard') }}#students" class="btn btn-secondary btn-sm">កំណត់ឡើងវិញ</a>
                    @endif
                </form>
            </div>

            <div style="overflow-x: auto; width: 100%;">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left;">
                            <th style="padding: 0.75rem;">{{ __('messages.full_name') }}</th>
                            <th style="padding: 0.75rem;">{{ __('messages.email') }}</th>
                            <th style="padding: 0.75rem;">{{ __('messages.phone') }}</th>
                            <th style="padding: 0.75rem;">{{ __('messages.departments') }}</th>
                            <th style="padding: 0.75rem;">{{ __('messages.classes') }}</th>
                            <th style="padding: 0.75rem;">{{ __('messages.status') }}</th>
                            <th style="padding: 0.75rem; text-align: right;">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $st)
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 0.75rem; font-weight: 600;">{{ $st->name }}</td>
                                <td style="padding: 0.75rem;">{{ $st->email }}</td>
                                <td style="padding: 0.75rem;">{{ $st->phone ?? '-' }}</td>
                                <td style="padding: 0.75rem;">
                                    <span class="badge" style="background: #e8f0fe; color: #1a73e8;">
                                        {{ $st->department ? $st->department->code : '-' }}
                                    </span>
                                </td>
                                <td style="padding: 0.75rem;">
                                    {{ $st->schoolClass ? ($st->schoolClass->name_kh ?? $st->schoolClass->name) : '-' }}
                                </td>
                                <td style="padding: 0.75rem;">
                                    <span class="badge badge-{{ $st->status }}">
                                        {{ __('messages.status_' . $st->status) }}
                                    </span>
                                </td>
                                <td style="padding: 0.75rem; text-align: right;">
                                    <form action="{{ route('admin.users.destroy', $st) }}" method="POST" onsubmit="return confirm('តើអ្នកប្រាកដជាចង់លុបគណនីសិស្សនេះមែនទេ? (Soft Delete)');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm" style="background: #ea4335; color: #ffffff; border-radius: 4px; padding: 0.3rem 0.65rem; font-size: 0.8rem;">
                                            {{ __('messages.delete') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding: 2.5rem; text-align: center; color: #94a3b8;">
                                    {{ __('messages.empty_data') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1rem;">
                {{ $students->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 7: TIMETABLE & ADVANCED CONFLICT ENGINE -->
    <!-- ========================================== -->
    <div id="panel-schedules" class="admin-panel hidden">
        <!-- Section Header with Title, Filter & Add New Button -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 style="font-size: 1.3rem; font-weight: 700; color: #1e293b; margin: 0;">
                    {{ __('messages.schedules') }} ({{ $schedules->total() }})
                </h2>
                <p style="font-size: 0.88rem; color: #64748b; margin-top: 0.2rem;">
                    កាលវិភាគបង្រៀនពេញមួយសប្តាហ៍ ៧ ថ្ងៃ (ច័ន្ទ ដល់ អាទិត្យ)
                </p>
            </div>

            <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                <!-- Filter by Class and Day -->
                <form action="{{ route('admin.dashboard') }}" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <input type="hidden" name="tab" value="schedules">
                    <select name="schedule_class_id" class="form-control" style="width: 160px; font-size: 0.85rem;">
                        <option value="">-- គ្រប់ថ្នាក់ --</option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}" {{ request('schedule_class_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name_kh ?? $c->name }}
                            </option>
                        @endforeach
                    </select>
                    <select name="schedule_day" class="form-control" style="width: 140px; font-size: 0.85rem;">
                        <option value="">-- គ្រប់ថ្ងៃ --</option>
                        @foreach ($days as $d)
                            <option value="{{ $d }}" {{ request('schedule_day') == $d ? 'selected' : '' }}>
                                {{ __('messages.' . $d) }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-secondary btn-sm">ចម្រាញ់</button>
                    @if (request()->hasAny(['schedule_class_id', 'schedule_day']))
                        <a href="{{ route('admin.dashboard') }}#schedules" class="btn btn-sm" style="background: #e2e8f0; color: #475569;">កំណត់ឡើងវិញ</a>
                    @endif
                </form>

                <button type="button" class="btn btn-primary toggle-create-btn" data-target="create-schedule-card" style="display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>{{ __('messages.create') }} {{ __('messages.schedules') }}</span>
                </button>
            </div>
        </div>

        <!-- Collapsible Create Schedule Form -->
        <div id="create-schedule-card" class="card collapsible-form-card" style="background: #ffffff; padding: 1.5rem; border-radius: 10px; margin-bottom: 1.5rem; border: 1px solid #dbeafe; box-shadow: 0 4px 12px rgba(48, 58, 152, 0.06); {{ $errors->hasAny(['class_id', 'teacher_id', 'subject', 'day_of_week', 'shift', 'start_time', 'end_time', 'room_id', 'room', 'conflict']) ? 'display: block;' : 'display: none;' }}">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid #e2e8f0;">
                <div>
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--stic-primary-dark); margin: 0;">
                        {{ __('messages.create') }} {{ __('messages.schedules') }}
                    </h3>
                    <span class="badge" style="background: #e8f0fe; color: #1a73e8; font-size: 0.75rem; margin-top: 4px;">
                        ម៉ាស៊ីនទប់ស្កាត់ការជាន់ម៉ោងស្វ័យប្រវត្តិ (Overlap Math Engine)
                    </span>
                </div>
                <button type="button" class="btn btn-sm btn-secondary close-create-btn" data-target="create-schedule-card">
                    {{ __('messages.cancel') }}
                </button>
            </div>

            <p style="font-size: 0.82rem; color: #64748b; margin-bottom: 1rem;">
                ការពារការជាន់គ្នាចំពោះគ្រូ ថ្នាក់ និងបន្ទប់ (Math Formula: start &lt; exist.end &amp;&amp; end &gt; exist.start) ដោយអនុញ្ញាតម៉ោងបន្តបន្ទាប់។
            </p>

            <form action="{{ route('admin.schedules.store') }}" method="POST">
                @csrf
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="sch_class_id" class="form-label">{{ __('messages.classes') }} *</label>
                        <select name="class_id" id="sch_class_id" class="form-control" required>
                            <option value="">-- {{ __('messages.select_class') }} --</option>
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name_kh ?? $c->name }} ({{ $c->department->code ?? '' }} - ឆ្នាំទី {{ $c->year }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="sch_teacher_id" class="form-label">{{ __('messages.teachers') }} *</label>
                        <select name="teacher_id" id="sch_teacher_id" class="form-control" required>
                            <option value="">-- ជ្រើសរើសគ្រូបង្រៀន --</option>
                            @foreach ($allTeachers as $t)
                                <option value="{{ $t->id }}" {{ old('teacher_id') == $t->id ? 'selected' : '' }}>
                                    {{ $t->name }} ({{ $t->department->code ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="sch_subject" class="form-label">{{ __('messages.subject') }} *</label>
                        <input type="text" name="subject" id="sch_subject" class="form-control" required value="{{ old('subject') }}" placeholder="ឧ. Web Development, Database">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 0.75rem;">
                    <div class="form-group">
                        <label for="sch_day" class="form-label">ថ្ងៃសិក្សា (ច័ន្ទ-អាទិត្យ) *</label>
                        <select name="day_of_week" id="sch_day" class="form-control" required>
                            @foreach ($days as $d)
                                <option value="{{ $d }}" {{ old('day_of_week') == $d ? 'selected' : '' }}>
                                    {{ __('messages.' . $d) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sch_shift" class="form-label">{{ __('messages.shift') }} *</label>
                        <select name="shift" id="sch_shift" class="form-control" required>
                            <option value="morning" {{ old('shift') == 'morning' ? 'selected' : '' }}>{{ __('messages.shift_morning') }}</option>
                            <option value="afternoon" {{ old('shift') == 'afternoon' ? 'selected' : '' }}>{{ __('messages.shift_afternoon') }}</option>
                            <option value="evening" {{ old('shift') == 'evening' ? 'selected' : '' }}>{{ __('messages.shift_evening') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sch_start_time" class="form-label">{{ __('messages.start_time') }} (HH:MM)</label>
                        <input type="time" name="start_time" id="sch_start_time" class="form-control" value="{{ old('start_time') }}">
                    </div>
                    <div class="form-group">
                        <label for="sch_end_time" class="form-label">{{ __('messages.end_time') }} (HH:MM)</label>
                        <input type="time" name="end_time" id="sch_end_time" class="form-control" value="{{ old('end_time') }}">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-top: 0.75rem;">
                    <div class="form-group">
                        <label for="sch_room_id" class="form-label">{{ __('messages.rooms') }} (Room Entity)</label>
                        <select name="room_id" id="sch_room_id" class="form-control">
                            <option value="">-- ជ្រើសរើសបន្ទប់ផ្លូវការ --</option>
                            @foreach ($rooms as $r)
                                <option value="{{ $r->id }}" {{ old('room_id') == $r->id ? 'selected' : '' }}>
                                    {{ $r->display_name }} [{{ ucfirst($r->room_type) }} - {{ $r->building }}]
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="sch_room_custom" class="form-label">ឬ ឈ្មោះបន្ទប់ផ្សេងៗ (Custom Room Name)</label>
                        <input type="text" name="room" id="sch_room_custom" class="form-control" value="{{ old('room') }}" placeholder="ឧ. Lab 3, Workshop 1">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem;">
                    <button type="button" class="btn btn-secondary close-create-btn" data-target="create-schedule-card">
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{ __('messages.save') }} {{ __('messages.schedules') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Schedules Table (Full-Width 100%) -->
        <div class="card" style="background: #ffffff; padding: 1.5rem; border-radius: 10px;">
            <div style="overflow-x: auto; width: 100%;">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left;">
                            <th style="padding: 0.75rem;">ថ្ងៃ & ម៉ោង</th>
                            <th style="padding: 0.75rem;">{{ __('messages.classes') }}</th>
                            <th style="padding: 0.75rem;">{{ __('messages.subject') }}</th>
                            <th style="padding: 0.75rem;">{{ __('messages.teachers') }}</th>
                            <th style="padding: 0.75rem;">{{ __('messages.rooms') }}</th>
                            <th style="padding: 0.75rem; text-align: right;">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($schedules as $sch)
                            <tr style="border-bottom: 1px solid #e2e8f0; font-size: 0.88rem;">
                                <td style="padding: 0.75rem;">
                                    <strong>{{ __('messages.' . $sch->day_of_week) }}</strong>
                                    <div style="margin-top: 2px;">
                                        @if($sch->start_time && $sch->end_time)
                                            <span style="font-size: 0.78rem; background: #e8f0fe; color: #1a73e8; padding: 0.15rem 0.4rem; border-radius: 4px; font-weight: 600;">
                                                {{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }}
                                            </span>
                                        @endif
                                        <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.75rem;">
                                            {{ ucfirst($sch->shift) }}
                                        </span>
                                    </div>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <strong>{{ $sch->schoolClass->name_kh ?? $sch->schoolClass->name }}</strong>
                                    <div style="font-size: 0.78rem; color: #64748b;">
                                        {{ $sch->schoolClass->department->code ?? '' }}
                                    </div>
                                </td>
                                <td style="padding: 0.75rem; font-weight: 600; color: #1e293b;">
                                    {{ $sch->subject }}
                                </td>
                                <td style="padding: 0.75rem;">
                                    {{ $sch->teacher->name ?? '-' }}
                                </td>
                                <td style="padding: 0.75rem;">
                                    {{ $sch->display_room }}
                                </td>
                                <td style="padding: 0.75rem; text-align: right;">
                                    <form action="{{ route('admin.schedules.destroy', $sch) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete') }}');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm" style="background: #ea4335; color: #ffffff; border-radius: 4px; padding: 0.3rem 0.65rem; font-size: 0.8rem;">
                                            {{ __('messages.delete') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 2.5rem; text-align: center; color: #94a3b8;">
                                    {{ __('messages.empty_data') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1rem;">
                {{ $schedules->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 8: ROOMS & FACILITIES MANAGEMENT       -->
    <!-- ========================================== -->
    <div id="panel-rooms" class="admin-panel hidden">
        <!-- Section Header with Title & Add New Button -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 style="font-size: 1.3rem; font-weight: 700; color: #1e293b; margin: 0;">
                    {{ __('messages.rooms') }} ({{ $rooms->count() }})
                </h2>
                <p style="font-size: 0.88rem; color: #64748b; margin-top: 0.2rem;">
                    បញ្ជីបន្ទប់រៀន បន្ទប់ពិសោធន៍ និងរោងជាងបច្ចេកទេស
                </p>
            </div>
            <button type="button" class="btn btn-primary toggle-create-btn" data-target="create-room-card" style="display: inline-flex; align-items: center; gap: 6px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>{{ __('messages.create') }} {{ __('messages.rooms') }}</span>
            </button>
        </div>

        <!-- Collapsible Create Room Form -->
        <div id="create-room-card" class="card collapsible-form-card" style="background: #ffffff; padding: 1.5rem; border-radius: 10px; margin-bottom: 1.5rem; border: 1px solid #dbeafe; box-shadow: 0 4px 12px rgba(48, 58, 152, 0.06); {{ $errors->hasAny(['name', 'name_kh', 'building', 'floor', 'capacity', 'room_type']) ? 'display: block;' : 'display: none;' }}">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid #e2e8f0;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--stic-primary-dark); margin: 0;">
                    {{ __('messages.create') }} {{ __('messages.rooms') }}
                </h3>
                <button type="button" class="btn btn-sm btn-secondary close-create-btn" data-target="create-room-card">
                    {{ __('messages.cancel') }}
                </button>
            </div>
            <form action="{{ route('admin.rooms.store') }}" method="POST">
                @csrf
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="room_name_code" class="form-label">{{ __('messages.room_name') }} (Code/Name) *</label>
                        <input type="text" name="name" id="room_name_code" class="form-control" required placeholder="ឧ. Room 101, Lab 1" value="{{ old('name') }}">
                    </div>

                    <div class="form-group">
                        <label for="room_name_khmer" class="form-label">{{ __('messages.room_name_kh') }}</label>
                        <input type="text" name="name_kh" id="room_name_khmer" class="form-control" placeholder="ឧ. បន្ទប់ ១០១, បន្ទប់ពិសោធន៍ ១" value="{{ old('name_kh') }}">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-top: 0.75rem;">
                    <div class="form-group">
                        <label for="room_bld" class="form-label">{{ __('messages.building') }}</label>
                        <input type="text" name="building" id="room_bld" class="form-control" placeholder="ឧ. Building A" value="{{ old('building') }}">
                    </div>
                    <div class="form-group">
                        <label for="room_flr" class="form-label">{{ __('messages.floor') }}</label>
                        <input type="text" name="floor" id="room_flr" class="form-control" placeholder="ឧ. 1, 2" value="{{ old('floor') }}">
                    </div>
                    <div class="form-group">
                        <label for="room_cap" class="form-label">{{ __('messages.capacity') }}</label>
                        <input type="number" name="capacity" id="room_cap" class="form-control" value="{{ old('capacity', 35) }}" min="1" max="500">
                    </div>
                    <div class="form-group">
                        <label for="room_tp" class="form-label">{{ __('messages.room_type') }} *</label>
                        <select name="room_type" id="room_tp" class="form-control" required>
                            <option value="lecture" {{ old('room_type') == 'lecture' ? 'selected' : '' }}>{{ __('messages.room_type_lecture') }}</option>
                            <option value="lab" {{ old('room_type') == 'lab' ? 'selected' : '' }}>{{ __('messages.room_type_lab') }}</option>
                            <option value="workshop" {{ old('room_type') == 'workshop' ? 'selected' : '' }}>{{ __('messages.room_type_workshop') }}</option>
                            <option value="hall" {{ old('room_type') == 'hall' ? 'selected' : '' }}>{{ __('messages.room_type_hall') }}</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem;">
                    <button type="button" class="btn btn-secondary close-create-btn" data-target="create-room-card">
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{ __('messages.save') }} {{ __('messages.rooms') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Rooms Table (Full-Width 100%) -->
        <div class="card" style="background: #ffffff; padding: 1.5rem; border-radius: 10px;">
            <div style="overflow-x: auto; width: 100%;">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left;">
                            <th style="padding: 0.75rem;">{{ __('messages.rooms') }}</th>
                            <th style="padding: 0.75rem;">{{ __('messages.building') }} & ជាន់</th>
                            <th style="padding: 0.75rem;">{{ __('messages.room_type') }}</th>
                            <th style="padding: 0.75rem;">{{ __('messages.capacity') }}</th>
                            <th style="padding: 0.75rem;">ម៉ោងប្រើប្រាស់ក្នុងកាលវិភាគ</th>
                            <th style="padding: 0.75rem; text-align: right;">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rooms as $r)
                            <tr style="border-bottom: 1px solid #e2e8f0; font-size: 0.88rem;">
                                <td style="padding: 0.75rem;">
                                    <strong>{{ $r->name }}</strong>
                                    @if($r->name_kh && $r->name_kh !== $r->name)
                                        <div style="font-size: 0.78rem; color: #64748b;">{{ $r->name_kh }}</div>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem;">
                                    {{ $r->building ?? '-' }} (ជាន់ {{ $r->floor ?? '-' }})
                                </td>
                                <td style="padding: 0.75rem;">
                                    <span class="badge" style="background: #e8f0fe; color: #1a73e8; font-size: 0.78rem;">
                                        {{ ucfirst($r->room_type) }}
                                    </span>
                                </td>
                                <td style="padding: 0.75rem;">
                                    {{ $r->capacity }} នាក់
                                </td>
                                <td style="padding: 0.75rem;">
                                    <span class="badge" style="background: #f1f5f9; color: #475569;">
                                        {{ $r->schedules_count }} ម៉ោង
                                    </span>
                                </td>
                                <td style="padding: 0.75rem; text-align: right;">
                                    <form action="{{ route('admin.rooms.destroy', $r) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete') }}');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm" style="background: #ea4335; color: #ffffff; border-radius: 4px; padding: 0.3rem 0.65rem; font-size: 0.8rem;">
                                            {{ __('messages.delete') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 2.5rem; text-align: center; color: #94a3b8;">
                                    {{ __('messages.empty_data') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 9: AUDIT LOGS TRAIL                    -->
    <!-- ========================================== -->
    <div id="panel-audit-logs" class="admin-panel hidden">
        <div class="card" style="background: #ffffff; padding: 1.5rem; border-radius: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
                <div>
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0;">
                        កំណត់ត្រាសវនកម្មប្រព័ន្ធ (Immutable Audit Logs Trail)
                    </h2>
                    <p style="font-size: 0.88rem; color: #64748b; margin-top: 0.2rem;">
                        កត់ត្រារាល់សកម្មភាពរដ្ឋបាលសំខាន់ៗ រក្សាទុកអចិន្ត្រៃយ៍ គ្មាន Soft Delete គ្មាន Update
                    </p>
                </div>
                <span class="badge" style="background: #e2e8f0; color: #475569; font-size: 0.82rem;">
                    {{ $auditLogs->total() }} កំណត់ត្រាសរុប
                </span>
            </div>

            <div style="overflow-x: auto; width: 100%;">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left;">
                            <th style="padding: 0.75rem;">កាលបរិច្ឆេទ (Phnom Penh)</th>
                            <th style="padding: 0.75rem;">Action Event</th>
                            <th style="padding: 0.75rem;">អ្នកធ្វើសកម្មភាព (Actor)</th>
                            <th style="padding: 0.75rem;">IP Address</th>
                            <th style="padding: 0.75rem;">ព័ត៌មានលម្អិត (Sanitized Details)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($auditLogs as $log)
                            <tr style="border-bottom: 1px solid #e2e8f0; font-size: 0.88rem;">
                                <td style="padding: 0.75rem; white-space: nowrap; color: #64748b;">
                                    {{ $log->created_at->timezone('Asia/Phnom_Penh')->format('d/m/Y H:i:s') }}
                                </td>
                                <td style="padding: 0.75rem;">
                                    <span class="badge" style="background: #f1f5f9; color: #0f172a; font-family: monospace; font-size: 0.8rem;">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td style="padding: 0.75rem; font-weight: 600;">
                                    {{ $log->user ? $log->user->name : 'System/Guest' }}
                                </td>
                                <td style="padding: 0.75rem; font-family: monospace; font-size: 0.82rem; color: #64748b;">
                                    {{ $log->ip_address ?? '-' }}
                                </td>
                                <td style="padding: 0.75rem; font-size: 0.82rem; color: #334155;">
                                    @if ($log->details)
                                        <code>{{ json_encode($log->details, JSON_UNESCAPED_UNICODE) }}</code>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding: 2.5rem; text-align: center; color: #94a3b8;">
                                    {{ __('messages.empty_data') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1rem;">
                {{ $auditLogs->appends(['tab' => 'audit-logs'])->links() }}
            </div>
        </div>
    </div>
</div>
@endsection