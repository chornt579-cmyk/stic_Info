@extends('layouts.app')

@section('title', __('messages.student_dashboard_title') . ' — STIC Info')

@section('styles')
<style>
    .student-tabs {
        display: flex;
        gap: 0.5rem;
        border-bottom: 2px solid var(--border);
        margin-bottom: 1.5rem;
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 4px;
        -webkit-overflow-scrolling: touch;
    }
    .student-tab-btn {
        background: transparent;
        border: none;
        padding: 0.6rem 1.1rem;
        font-family: inherit;
        font-size: 0.95rem;
        font-weight: 500;
        color: var(--text-muted);
        cursor: pointer;
        border-radius: 6px 6px 0 0;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
    .student-tab-btn:hover {
        color: var(--primary);
        background: #f1f3f4;
    }
    .student-tab-btn.active {
        color: var(--primary);
        font-weight: 600;
        border-bottom: 3px solid var(--primary);
        background: #ffffff;
    }
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .stat-card {
        background: #ffffff;
        border-radius: 8px;
        padding: 1.2rem;
        border: 1px solid var(--border);
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .stat-title {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-bottom: 0.5rem;
    }
    .stat-value {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--dark);
    }
    .two-col-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
    }
    .day-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    .day-pill {
        padding: 0.4rem 0.9rem;
        border-radius: 20px;
        border: 1px solid var(--border);
        background: #ffffff;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--text-main);
        transition: all 0.2s;
    }
    .day-pill:hover {
        border-color: var(--primary);
        color: var(--primary);
    }
    .day-pill.active {
        background: var(--primary);
        color: #ffffff;
        border-color: var(--primary);
    }
    .day-pill.is-today {
        position: relative;
    }
    .day-pill.is-today::after {
        content: "•";
        color: var(--secondary);
        font-size: 1.2rem;
        position: absolute;
        top: -4px;
        right: 4px;
    }
    .day-pill.active.is-today::after {
        color: #ffffff;
    }
    .schedule-day-block {
        margin-bottom: 1.5rem;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: #ffffff;
        overflow: hidden;
    }
    .schedule-day-header {
        background: #f8f9fa;
        padding: 0.75rem 1.2rem;
        font-weight: 600;
        font-size: 1rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .time-slot {
        display: inline-block;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        background: #e8f0fe;
        color: var(--primary);
        font-weight: 600;
        font-size: 0.85rem;
    }
    .announcement-card {
        border: 1px solid var(--border);
        background: #ffffff;
        border-radius: 8px;
        padding: 1.2rem;
        margin-bottom: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .announcement-badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.2rem 0.6rem;
        border-radius: 12px;
        text-transform: uppercase;
        display: inline-block;
    }
    .badge-target-public {
        background: #e8f0fe;
        color: #1a73e8;
    }
    .badge-target-class {
        background: #e6f4ea;
        color: #137333;
    }
    .badge-target-dept {
        background: #fef7e0;
        color: #b06000;
    }
    .badge-target-all {
        background: #fce8e6;
        color: #c5221f;
    }
    .classmates-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1rem;
    }
    .classmate-box {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }
    .classmate-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e8f0fe;
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
    }

    @media (max-width: 900px) {
        .two-col-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 480px) {
        .stat-grid {
            grid-template-columns: 1fr;
        }
        .stat-value {
            font-size: 1.3rem;
        }
    }
</style>
@endsection

@section('content')
<!-- Header Greeting -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; color: var(--dark); display: flex; align-items: center; gap: 0.5rem;">
            👋 {{ __('messages.student_greeting') }}, {{ $user->name }}
        </h1>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 0.2rem;">
            🏫 {{ __('messages.departments') }}: <strong>{{ $user->department->name_kh ?? $user->department->name ?? 'STIC' }}</strong>
            · ថ្នាក់រៀន៖ <strong>{{ $user->schoolClass->name_kh ?? $user->schoolClass->name ?? __('messages.no_class_assigned_notice') }}</strong>
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <span class="badge badge-student" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">{{ __('messages.role_student') }}</span>
        <span class="badge" style="font-size: 0.85rem; padding: 0.4rem 0.8rem; background-color: #34a853; color: #ffffff;">{{ __('messages.status_active') }}</span>
    </div>
</div>

@if(!$user->class_id)
    <div class="alert alert-warning" style="margin-bottom: 1.5rem;">
        ⚠️ <strong>{{ __('messages.no_class_assigned_notice') }}</strong>
    </div>
@endif

<!-- Key Workload Stats -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-title">🏫 {{ __('messages.classes') }}</div>
        <div class="stat-value" style="font-size: 1.25rem;">
            {{ $user->schoolClass->name_kh ?? $user->schoolClass->name ?? 'N/A' }}
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-title">🗓️ {{ __('messages.weekly_teaching_slots') }}</div>
        <div class="stat-value">{{ $schedules->count() }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-title">⏰ {{ __('messages.today_teaching_slots') }}</div>
        <div class="stat-value" style="color: var(--primary);">{{ $todaySchedules->count() }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-title">👥 {{ __('messages.classmates_count') }}</div>
        <div class="stat-value">{{ $classmates->count() }} នាក់</div>
    </div>
</div>

<!-- Navigation Tabs -->
<div class="student-tabs" role="tablist" aria-label="Student Portal Tabs">
    <button type="button" class="student-tab-btn active" data-student-tab="tab-overview" role="tab" aria-selected="true">
        📊 {{ __('messages.tab_overview') }}
    </button>
    <button type="button" class="student-tab-btn" data-student-tab="tab-weekly" role="tab" aria-selected="false">
        🗓️ {{ __('messages.tab_weekly_schedule') }}
    </button>
    <button type="button" class="student-tab-btn" data-student-tab="tab-announcements" role="tab" aria-selected="false">
        📢 {{ __('messages.announcements') }}
    </button>
    <button type="button" class="student-tab-btn" data-student-tab="tab-classmates" role="tab" aria-selected="false">
        👥 {{ __('messages.tab_my_class') }}
    </button>
    <button type="button" class="student-tab-btn" data-student-tab="tab-profile" role="tab" aria-selected="false">
        ⚙️ {{ __('messages.tab_profile') }}
    </button>
</div>

<!-- TAB 1: OVERVIEW & TODAY -->
<div id="tab-overview" class="student-tab-content">
    <div class="two-col-grid">
        <!-- Today's Schedule -->
        <div>
            <div class="card" style="border-top: 4px solid var(--primary);">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2 class="card-title">📖 {{ __('messages.today_schedule') }} ({{ __('messages.' . $today) }})</h2>
                    <span class="badge" style="background: #e8f0fe; color: var(--primary);">{{ now()->timezone('Asia/Phnom_Penh')->format('d M Y') }}</span>
                </div>

                @if ($todaySchedules->isEmpty())
                    <div style="text-align: center; color: var(--text-muted); padding: 2.5rem 1rem;">
                        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🎉</div>
                        <p style="font-size: 1rem; font-weight: 500;">{{ __('messages.no_classes_today') }}</p>
                        <p style="font-size: 0.85rem; margin-top: 0.3rem;">មិនមានកាលវិភាគសិក្សាសម្រាប់ថ្ងៃនេះឡើយ។</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>{{ __('messages.time') }} / {{ __('messages.shift') }}</th>
                                    <th>{{ __('messages.subject') }}</th>
                                    <th>{{ __('messages.teacher_name') }}</th>
                                    <th>{{ __('messages.room') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($todaySchedules as $sch)
                                    <tr>
                                        <td>
                                            @if($sch->start_time && $sch->end_time)
                                                <span class="time-slot">{{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }}</span>
                                            @endif
                                            <span class="badge badge-student">{{ ucfirst($sch->shift) }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $sch->subject }}</strong>
                                        </td>
                                        <td>
                                            👨‍🏫 {{ $sch->teacher->name ?? 'N/A' }}
                                        </td>
                                        <td>
                                            <span style="background: #f1f3f4; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.85rem;">
                                                🚪 {{ $sch->room ?? ($user->schoolClass->room ?? 'N/A') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Latest Announcements Preview -->
        <div>
            <div class="card" style="border-top: 4px solid var(--secondary);">
                <div class="card-header">
                    <h2 class="card-title">📢 {{ __('messages.announcements') }}</h2>
                </div>
                @if ($announcements->isEmpty())
                    <p style="text-align: center; color: var(--text-muted); padding: 1.5rem;">
                        {{ __('messages.empty_data') }}
                    </p>
                @else
                    <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                        @foreach ($announcements->take(5) as $ann)
                            <div style="border-left: 3px solid var(--secondary); padding: 0.8rem; background: #f8f9fa; border-radius: 4px;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.3rem;">
                                    <h4 style="font-size: 0.95rem; font-weight: 600; color: var(--dark);">{{ $ann->title }}</h4>
                                    @if($ann->target_type === 'class')
                                        <span class="announcement-badge badge-target-class">{{ __('messages.announcement_type_class') }}</span>
                                    @elseif($ann->target_type === 'dept_students')
                                        <span class="announcement-badge badge-target-dept">{{ __('messages.announcement_type_dept_students') }}</span>
                                    @elseif($ann->target_type === 'all_students')
                                        <span class="announcement-badge badge-target-all">{{ __('messages.announcement_type_all_students') }}</span>
                                    @else
                                        <span class="announcement-badge badge-target-public">{{ __('messages.announcement_type_public') }}</span>
                                    @endif
                                </div>
                                <p style="font-size: 0.85rem; color: var(--text-main); margin-bottom: 0.4rem; white-space: pre-line;">{{ $ann->content }}</p>
                                <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">
                                    ✍️ {{ $ann->author->name ?? 'STIC' }} · {{ $ann->created_at->diffForHumans() }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- TAB 2: WEEKLY SCHEDULE (7 DAYS: MON TO SUN) -->
<div id="tab-weekly" class="student-tab-content hidden" style="display: none;">
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.8rem;">
            <div>
                <h2 class="card-title">🗓️ {{ __('messages.weekly_schedule') }} (ច័ន្ទ - អាទិត្យ)</h2>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.2rem;">
                    កាលវិភាគសិក្សាពេញមួយសប្ដាហ៍សម្រាប់ថ្នាក់ <strong>{{ $user->schoolClass->name_kh ?? $user->schoolClass->name ?? '' }}</strong>
                </p>
            </div>
            <!-- Day Filter Pills -->
            <div class="day-pills">
                <button type="button" class="day-pill active" data-student-day-filter="all">{{ __('messages.all_days') }}</button>
                @foreach ($days as $dayKey)
                    <button type="button" 
                            class="day-pill {{ $dayKey === $today ? 'is-today' : '' }}" 
                            data-student-day-filter="{{ $dayKey }}">
                        {{ __('messages.' . $dayKey) }}
                        @if($dayKey === $today)
                            ({{ __('messages.today') }})
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        @if ($schedules->isEmpty())
            <div style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📅</div>
                <p style="font-size: 1rem; font-weight: 500;">{{ __('messages.no_schedule_assigned') }}</p>
            </div>
        @else
            @foreach ($days as $dayKey)
                @php
                    $dayItems = $schedulesByDay->get($dayKey, collect());
                @endphp
                <div class="schedule-day-block" data-student-day-section="{{ $dayKey }}">
                    <div class="schedule-day-header">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span>🗓️ {{ __('messages.' . $dayKey) }}</span>
                            @if ($dayKey === $today)
                                <span class="badge" style="background: #34a853; color: #ffffff; font-size: 0.75rem;">{{ __('messages.today') }}</span>
                            @endif
                        </div>
                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: normal;">
                            {{ $dayItems->count() }} ម៉ោងសិក្សា
                        </span>
                    </div>

                    @if ($dayItems->isEmpty())
                        <div style="padding: 1.2rem; text-align: center; color: var(--text-muted); font-size: 0.9rem;">
                            គ្មានម៉ោងសិក្សាសម្រាប់ {{ __('messages.' . $dayKey) }} ឡើយ។
                        </div>
                    @else
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th style="width: 25%;">{{ __('messages.time') }} / {{ __('messages.shift') }}</th>
                                        <th style="width: 35%;">{{ __('messages.subject') }}</th>
                                        <th style="width: 25%;">{{ __('messages.teacher_name') }}</th>
                                        <th style="width: 15%;">{{ __('messages.room') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dayItems as $sch)
                                        <tr>
                                            <td>
                                                @if($sch->start_time && $sch->end_time)
                                                    <span class="time-slot">{{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }}</span>
                                                @endif
                                                <span class="badge badge-student">{{ ucfirst($sch->shift) }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $sch->subject }}</strong>
                                            </td>
                                            <td>
                                                👨‍🏫 {{ $sch->teacher->name ?? 'N/A' }}
                                            </td>
                                            <td>
                                                <span style="background: #f1f3f4; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.85rem;">
                                                    🚪 {{ $sch->room ?? ($user->schoolClass->room ?? 'N/A') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
</div>

<!-- TAB 3: ANNOUNCEMENTS FEED -->
<div id="tab-announcements" class="student-tab-content hidden" style="display: none;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">📢 {{ __('messages.announcements') }}</h2>
        </div>

        @if ($announcements->isEmpty())
            <div style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📭</div>
                <p style="font-size: 1rem; font-weight: 500;">{{ __('messages.empty_data') }}</p>
            </div>
        @else
            <div>
                @foreach ($announcements as $ann)
                    <div class="announcement-card" style="border-left: 4px solid {{ $ann->target_type === 'class' ? 'var(--secondary)' : ($ann->target_type === 'dept_students' ? 'var(--warning)' : 'var(--primary)') }};">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.5rem; flex-wrap: wrap;">
                            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--dark);">
                                {{ $ann->title }}
                            </h3>
                            <div>
                                @if($ann->target_type === 'class')
                                    <span class="announcement-badge badge-target-class">🏷️ {{ __('messages.announcement_type_class') }}</span>
                                @elseif($ann->target_type === 'dept_students')
                                    <span class="announcement-badge badge-target-dept">🏷️ {{ __('messages.announcement_type_dept_students') }}</span>
                                @elseif($ann->target_type === 'all_students')
                                    <span class="announcement-badge badge-target-all">🏷️ {{ __('messages.announcement_type_all_students') }}</span>
                                @else
                                    <span class="announcement-badge badge-target-public">🏷️ {{ __('messages.announcement_type_public') }}</span>
                                @endif
                            </div>
                        </div>

                        <p style="font-size: 0.95rem; color: var(--text-main); margin-bottom: 0.8rem; white-space: pre-line; line-height: 1.7;">
                            {{ $ann->content }}
                        </p>

                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: var(--text-muted); border-top: 1px solid #f1f3f4; padding-top: 0.6rem;">
                            <span>✍️ {{ $ann->author->name ?? 'STIC' }}</span>
                            <span>🕒 {{ $ann->created_at->diffForHumans() }} ({{ $ann->created_at->format('d/m/Y H:i') }})</span>
                        </div>
                    </div>
                @endforeach

                <div style="margin-top: 1rem;">
                    {{ $announcements->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- TAB 4: MY CLASS & CLASSMATES (PRIVACY PROTECTED) -->
<div id="tab-classmates" class="student-tab-content hidden" style="display: none;">
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h2 class="card-title">🏫 ព័ត៌មានលម្អិតអំពីថ្នាក់រៀន</h2>
        </div>
        @if($user->schoolClass)
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div>
                    <span style="font-size: 0.85rem; color: var(--text-muted);">ឈ្មោះថ្នាក់៖</span>
                    <p style="font-size: 1.1rem; font-weight: 700;">{{ $user->schoolClass->name_kh ?? $user->schoolClass->name }}</p>
                </div>
                <div>
                    <span style="font-size: 0.85rem; color: var(--text-muted);">ដេប៉ាតឺម៉ង់៖</span>
                    <p style="font-weight: 600;">{{ $user->schoolClass->department->name_kh ?? $user->schoolClass->department->name }}</p>
                </div>
                <div>
                    <span style="font-size: 0.85rem; color: var(--text-muted);">ឆ្នាំសិក្សា៖</span>
                    <p style="font-weight: 600;">ឆ្នាំទី {{ $user->schoolClass->year }}</p>
                </div>
                <div>
                    <span style="font-size: 0.85rem; color: var(--text-muted);">វេនសិក្សា៖</span>
                    <p style="font-weight: 600;">{{ ucfirst($user->schoolClass->shift) }}</p>
                </div>
                <div>
                    <span style="font-size: 0.85rem; color: var(--text-muted);">បន្ទប់ / ជាន់៖</span>
                    <p style="font-weight: 600;">បន្ទប់ {{ $user->schoolClass->room ?? 'N/A' }} (ជាន់ {{ $user->schoolClass->floor ?? '-' }})</p>
                </div>
            </div>
        @else
            <p style="color: var(--text-muted);">{{ __('messages.no_class_assigned_notice') }}</p>
        @endif
    </div>

    <!-- Classmates Grid -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <h2 class="card-title">👥 {{ __('messages.tab_my_class') }} ({{ $classmates->count() }} នាក់)</h2>
                <small style="color: var(--secondary); font-size: 0.8rem;">
                    🔒 {{ __('messages.privacy_protected') }}
                </small>
            </div>
        </div>

        @if ($classmates->isEmpty())
            <p style="text-align: center; color: var(--text-muted); padding: 2rem 1rem;">
                មិនទាន់មានសិស្សផ្សេងទៀតក្នុងថ្នាក់នេះនៅឡើយទេ។
            </p>
        @else
            <div class="classmates-grid">
                @foreach ($classmates as $cm)
                    <div class="classmate-box">
                        <div class="classmate-avatar">
                            {{ mb_substr($cm->name, 0, 1) }}
                        </div>
                        <div>
                            <h4 style="font-size: 0.95rem; font-weight: 600; color: var(--dark); margin-bottom: 0.2rem;">
                                {{ $cm->name }}
                            </h4>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">
                                សមាជិកតាំងពី {{ $cm->created_at->format('M Y') }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- TAB 5: PROFILE & SECURITY -->
<div id="tab-profile" class="student-tab-content hidden" style="display: none;">
    <div class="two-col-grid">
        <!-- Contact & Profile Details -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">👤 {{ __('messages.profile') }}</h2>
            </div>
            
            <form action="{{ route('student.profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">{{ __('messages.full_name') }}</label>
                    <input type="text" class="form-control" value="{{ $user->name }}" readonly style="background: #f8f9fa;">
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.email') }}</label>
                    <input type="email" class="form-control" value="{{ $user->email }}" readonly style="background: #f8f9fa;">
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.departments') }}</label>
                    <input type="text" class="form-control" value="{{ $user->department->name_kh ?? $user->department->name ?? 'N/A' }}" readonly style="background: #f8f9fa;">
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.classes') }}</label>
                    <input type="text" class="form-control" value="{{ $user->schoolClass->name_kh ?? $user->schoolClass->name ?? 'N/A' }}" readonly style="background: #f8f9fa;">
                </div>

                <div class="form-group">
                    <label for="student_phone" class="form-label">{{ __('messages.phone') }}</label>
                    <input type="text" 
                           name="phone" 
                           id="student_phone" 
                           class="form-control" 
                           value="{{ old('phone', $user->phone) }}" 
                           placeholder="{{ __('messages.phone_placeholder') }}">
                    <small style="color: var(--text-muted); font-size: 0.8rem;">
                        ទម្រង់លេខទូរស័ព្ទត្រឹមត្រូវ (ឧ. 012 345 678)
                    </small>
                </div>

                <button type="submit" class="btn btn-primary">
                    💾 {{ __('messages.save') }}
                </button>
            </form>
        </div>

        <!-- Change Password Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">🔒 {{ __('messages.change_password') }}</h2>
            </div>

            <form action="{{ route('password.change') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="student_current_password" class="form-label">{{ __('messages.current_password') }} *</label>
                    <input type="password" 
                           name="current_password" 
                           id="student_current_password" 
                           class="form-control" 
                           required>
                </div>

                <div class="form-group">
                    <label for="student_new_password" class="form-label">{{ __('messages.new_password') }} *</label>
                    <input type="password" 
                           name="new_password" 
                           id="student_new_password" 
                           class="form-control" 
                           required 
                           minlength="8">
                    <small style="color: var(--text-muted); font-size: 0.8rem;">
                        {{ __('messages.password_min') }}
                    </small>
                </div>

                <div class="form-group">
                    <label for="student_new_password_confirmation" class="form-label">{{ __('messages.new_password_confirmation') }} *</label>
                    <input type="password" 
                           name="new_password_confirmation" 
                           id="student_new_password_confirmation" 
                           class="form-control" 
                           required>
                </div>

                <button type="submit" class="btn btn-warning" style="width: 100%;">
                    🔐 {{ __('messages.change_password') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
