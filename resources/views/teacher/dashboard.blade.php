@extends('layouts.app')

@section('title', __('messages.teacher_dashboard_title') . ' — STIC Info')

@section('styles')
<style>
    .teacher-tabs {
        display: flex;
        gap: 0.5rem;
        border-bottom: 2px solid var(--border);
        margin-bottom: 1.5rem;
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 4px;
        -webkit-overflow-scrolling: touch;
    }
    .teacher-tab-btn {
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
    .teacher-tab-btn:hover {
        color: var(--primary);
        background: #f1f3f4;
    }
    .teacher-tab-btn.active {
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
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--dark);
    }
    .two-col-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
    }
    .classes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.2rem;
    }
    .class-box {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 1.2rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .class-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
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
    .announcement-item {
        border-left: 3px solid var(--secondary);
        padding: 0.9rem;
        background: #f8f9fa;
        border-radius: 4px;
        margin-bottom: 0.8rem;
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
            font-size: 1.4rem;
        }
    }
</style>
@endsection

@section('content')
<!-- Header Greeting -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; color: var(--dark); display: flex; align-items: center; gap: 0.5rem;">
            👨‍🏫 {{ __('messages.teacher_greeting') }}, {{ $user->name }}
        </h1>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 0.2rem;">
            🏫 {{ __('messages.departments') }}: <strong>{{ $user->department->name_kh ?? $user->department->name ?? 'STIC' }}</strong>
            @if($user->phone)
                · 📞 {{ $user->phone }}
            @endif
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <span class="badge badge-teacher" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">{{ __('messages.role_teacher') }}</span>
        <span class="badge badge-student" style="font-size: 0.85rem; padding: 0.4rem 0.8rem; background-color: #34a853;">{{ __('messages.status_active') }}</span>
    </div>
</div>

<!-- Key Workload Stats -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-title">📚 {{ __('messages.assigned_classes') }}</div>
        <div class="stat-value">{{ $classes->count() }}</div>
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
        <div class="stat-title">👥 {{ __('messages.total_students_taught') }}</div>
        <div class="stat-value">{{ $totalStudentsTaught }}</div>
    </div>
</div>

<!-- Navigation Tabs -->
<div class="teacher-tabs" role="tablist" aria-label="Teacher Dashboard Tabs">
    <button type="button" class="teacher-tab-btn active" data-teacher-tab="tab-overview" role="tab" aria-selected="true">
        📊 {{ __('messages.tab_overview') }}
    </button>
    <button type="button" class="teacher-tab-btn" data-teacher-tab="tab-weekly" role="tab" aria-selected="false">
        🗓️ {{ __('messages.tab_weekly_schedule') }}
    </button>
    <button type="button" class="teacher-tab-btn" data-teacher-tab="tab-classes" role="tab" aria-selected="false">
        🏫 {{ __('messages.tab_classes') }}
    </button>
    <button type="button" class="teacher-tab-btn" data-teacher-tab="tab-announcements" role="tab" aria-selected="false">
        📢 {{ __('messages.tab_announcements') }}
    </button>
    <button type="button" class="teacher-tab-btn" data-teacher-tab="tab-profile" role="tab" aria-selected="false">
        ⚙️ {{ __('messages.tab_profile') }}
    </button>
</div>

<!-- TAB 1: OVERVIEW & TODAY -->
<div id="tab-overview" class="teacher-tab-content">
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
                        <p style="font-size: 0.85rem; margin-top: 0.3rem;">លោកគ្រូ/អ្នកគ្រូមិនមានកាលវិភាគបង្រៀនសម្រាប់ថ្ងៃនេះឡើយ។</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>{{ __('messages.time') }} / {{ __('messages.shift') }}</th>
                                    <th>{{ __('messages.classes') }}</th>
                                    <th>{{ __('messages.subject') }}</th>
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
                                            <span class="badge badge-teacher">{{ ucfirst($sch->shift) }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $sch->schoolClass->name_kh ?? $sch->schoolClass->name }}</strong>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                                {{ $sch->schoolClass->department->name_kh ?? $sch->schoolClass->department->name }}
                                            </div>
                                        </td>
                                        <td><span style="font-weight: 500; color: var(--dark);">{{ $sch->subject }}</span></td>
                                        <td>
                                            <span style="background: #f1f3f4; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.85rem;">
                                                🚪 {{ $sch->room ?? ($sch->schoolClass->room ?? 'N/A') }}
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

        <!-- Right Column: Internal Announcements for Teachers -->
        <div>
            <div class="card" style="border-top: 4px solid var(--secondary);">
                <div class="card-header">
                    <h2 class="card-title">📢 {{ __('messages.internal_announcements') }}</h2>
                </div>
                @if ($internalAnnouncements->isEmpty())
                    <p style="text-align: center; color: var(--text-muted); padding: 1.5rem;">
                        {{ __('messages.empty_data') }}
                    </p>
                @else
                    <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                        @foreach ($internalAnnouncements as $ann)
                            <div class="announcement-item">
                                <h4 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 0.3rem; color: var(--dark);">
                                    {{ $ann->title }}
                                </h4>
                                <p style="font-size: 0.85rem; color: var(--text-main); margin-bottom: 0.4rem; white-space: pre-line;">
                                    {{ $ann->content }}
                                </p>
                                <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">
                                    ✍️ {{ $ann->author->name ?? 'Admin' }} · {{ $ann->created_at->diffForHumans() }}
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
<div id="tab-weekly" class="teacher-tab-content hidden" style="display: none;">
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.8rem;">
            <div>
                <h2 class="card-title">🗓️ {{ __('messages.weekly_schedule') }} (ច័ន្ទ - អាទិត្យ)</h2>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.2rem;">
                    គ្របដណ្ដប់ទាំង ៧ ថ្ងៃក្នុងសប្ដាហ៍ ទាំងថ្នាក់ធម្មតា និងវគ្គបច្ចេកទេសចុងសប្ដាហ៍
                </p>
            </div>
            <!-- Day Filter Pills -->
            <div class="day-pills">
                <button type="button" class="day-pill active" data-day-filter="all">{{ __('messages.all_days') }}</button>
                @foreach ($days as $dayKey)
                    <button type="button" 
                            class="day-pill {{ $dayKey === $today ? 'is-today' : '' }}" 
                            data-day-filter="{{ $dayKey }}">
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
                <div class="schedule-day-block" data-day-section="{{ $dayKey }}">
                    <div class="schedule-day-header">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span>🗓️ {{ __('messages.' . $dayKey) }}</span>
                            @if ($dayKey === $today)
                                <span class="badge" style="background: #34a853; color: #ffffff; font-size: 0.75rem;">{{ __('messages.today') }}</span>
                            @endif
                        </div>
                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: normal;">
                            {{ $dayItems->count() }} ម៉ោងបង្រៀន
                        </span>
                    </div>

                    @if ($dayItems->isEmpty())
                        <div style="padding: 1.2rem; text-align: center; color: var(--text-muted); font-size: 0.9rem;">
                            គ្មានម៉ោងបង្រៀនសម្រាប់ {{ __('messages.' . $dayKey) }} ឡើយ។
                        </div>
                    @else
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th style="width: 25%;">{{ __('messages.time') }} / {{ __('messages.shift') }}</th>
                                        <th style="width: 30%;">{{ __('messages.classes') }}</th>
                                        <th style="width: 30%;">{{ __('messages.subject') }}</th>
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
                                                <span class="badge badge-teacher">{{ ucfirst($sch->shift) }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $sch->schoolClass->name_kh ?? $sch->schoolClass->name }}</strong>
                                                <div style="font-size: 0.8rem; color: var(--text-muted);">
                                                    {{ $sch->schoolClass->department->name_kh ?? $sch->schoolClass->department->name }}
                                                </div>
                                            </td>
                                            <td>{{ $sch->subject }}</td>
                                            <td>
                                                <span style="background: #f1f3f4; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.85rem;">
                                                    🚪 {{ $sch->room ?? ($sch->schoolClass->room ?? 'N/A') }}
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

<!-- TAB 3: ASSIGNED CLASSES & STUDENTS -->
<div id="tab-classes" class="teacher-tab-content hidden" style="display: none;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">🏫 {{ __('messages.assigned_classes') }} ({{ $classes->count() }})</h2>
        </div>

        @if ($classes->isEmpty())
            <div style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">👨‍🏫</div>
                <p style="font-size: 1rem; font-weight: 500;">{{ __('messages.no_classes_assigned') }}</p>
            </div>
        @else
            <div class="classes-grid">
                @foreach ($classes as $c)
                    <div class="class-box">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.8rem;">
                            <div>
                                <h3 style="font-size: 1.15rem; color: var(--dark); font-weight: 700;">
                                    {{ $c->name_kh ?? $c->name }}
                                </h3>
                                <p style="font-size: 0.85rem; color: var(--text-muted);">
                                    {{ $c->department->name_kh ?? $c->department->name }}
                                </p>
                            </div>
                            <span class="badge" style="background: #e8f0fe; color: var(--primary);">
                                ឆ្នាំទី {{ $c->year }}
                            </span>
                        </div>

                        <div style="margin-bottom: 1rem; font-size: 0.9rem; display: flex; flex-direction: column; gap: 0.3rem;">
                            <div>🕒 {{ __('messages.shift') }}: <strong>{{ ucfirst($c->shift) }}</strong></div>
                            <div>🚪 {{ __('messages.room') }}: <strong>{{ $c->room ?? 'N/A' }}</strong> ({{ __('messages.floor') }} {{ $c->floor ?? '-' }})</div>
                            @if($c->pivot->subject)
                                <div>📘 {{ __('messages.subject') }}: <strong>{{ $c->pivot->subject }}</strong></div>
                            @endif
                            <div>👥 {{ __('messages.students_count') }}: <strong>{{ $c->students_count }} នាក់</strong></div>
                        </div>

                        <div style="border-top: 1px solid var(--border); padding-top: 0.8rem;">
                            <button type="button" 
                                    class="btn btn-secondary btn-sm" 
                                    style="width: 100%; text-align: center;"
                                    data-announce-class-id="{{ $c->id }}">
                                📢 {{ __('messages.post_announcement') }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- TAB 4: CLASS ANNOUNCEMENTS -->
<div id="tab-announcements" class="teacher-tab-content hidden" style="display: none;">
    <div class="two-col-grid">
        <!-- Post Announcement Form -->
        <div>
            <div class="card" style="border-top: 4px solid var(--primary);">
                <div class="card-header">
                    <h2 class="card-title">✍️ {{ __('messages.post_announcement') }}</h2>
                </div>

                @if ($classes->isEmpty())
                    <p style="color: var(--text-muted); padding: 1.5rem;">
                        ⚠️ {{ __('messages.no_classes_assigned') }}
                    </p>
                @else
                    <form action="{{ route('teacher.announcements.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="announcement_class_id" class="form-label">{{ __('messages.select_class') }} *</label>
                            <select name="class_id" id="announcement_class_id" class="form-control" required>
                                <option value="">-- {{ __('messages.select_class') }} --</option>
                                @foreach ($classes as $c)
                                    <option value="{{ $c->id }}">
                                        {{ $c->name_kh ?? $c->name }} ({{ $c->department->name_kh ?? $c->department->name }})
                                    </option>
                                @endforeach
                            </select>
                            <small style="color: var(--text-muted); font-size: 0.8rem;">
                                🔒 {{ __('messages.only_teaching_classes_allowed') }}
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="announcement_title" class="form-label">{{ __('messages.announcement_title') }} *</label>
                            <input type="text" 
                                   name="title" 
                                   id="announcement_title" 
                                   class="form-control" 
                                   required 
                                   maxlength="255" 
                                   placeholder="ឧ. ដំណឹងស្ដីពីការប្រគល់កិច្ចការផ្ទះ ឬការប្ដូរម៉ោងរៀន...">
                        </div>

                        <div class="form-group">
                            <label for="announcement_content" class="form-label">{{ __('messages.announcement_content') }} *</label>
                            <textarea name="content" 
                                      id="announcement_content" 
                                      class="form-control" 
                                      rows="5" 
                                      required 
                                      placeholder="សរសេរខ្លឹមសារលម្អិតនៅទីនេះ..."></textarea>
                            <small style="color: var(--text-muted); font-size: 0.8rem;">
                                ⏱️ កម្រិតការពារ Rate Limit: អនុញ្ញាតអតិបរមា ១០ ដង/ម៉ោង
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            🚀 {{ __('messages.publish') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Sent Announcements History -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">📋 {{ __('messages.sent_announcements') }}</h2>
                </div>

                @if ($sentAnnouncements->isEmpty())
                    <p style="text-align: center; color: var(--text-muted); padding: 2rem 1rem;">
                        {{ __('messages.no_announcements_sent') }}
                    </p>
                @else
                    <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                        @foreach ($sentAnnouncements as $ann)
                            <div class="announcement-item" style="border-left-color: var(--primary);">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem;">
                                    <h4 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 0.3rem; color: var(--dark);">
                                        {{ $ann->title }}
                                    </h4>
                                    <!-- Delete Button -->
                                    <form action="{{ route('teacher.announcements.destroy', $ann) }}" 
                                          method="POST" 
                                          class="delete-announcement-form"
                                          data-confirm="{{ __('messages.confirm_delete') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-danger btn-sm" 
                                                style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" 
                                                title="{{ __('messages.delete') }}">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                                <p style="font-size: 0.85rem; color: var(--text-main); margin-bottom: 0.4rem; white-space: pre-line;">
                                    {{ $ann->content }}
                                </p>
                                <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">
                                    🕒 {{ $ann->created_at->diffForHumans() }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- TAB 5: PROFILE & SECURITY -->
<div id="tab-profile" class="teacher-tab-content hidden" style="display: none;">
    <div class="two-col-grid">
        <!-- Contact & Profile Details -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">👤 {{ __('messages.profile') }}</h2>
            </div>
            
            <form action="{{ route('teacher.profile.update') }}" method="POST">
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
                    <label for="teacher_phone" class="form-label">{{ __('messages.phone') }}</label>
                    <input type="text" 
                           name="phone" 
                           id="teacher_phone" 
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
                    <label for="teacher_current_password" class="form-label">{{ __('messages.current_password') }} *</label>
                    <input type="password" 
                           name="current_password" 
                           id="teacher_current_password" 
                           class="form-control" 
                           required>
                </div>

                <div class="form-group">
                    <label for="teacher_new_password" class="form-label">{{ __('messages.new_password') }} *</label>
                    <input type="password" 
                           name="new_password" 
                           id="teacher_new_password" 
                           class="form-control" 
                           required 
                           minlength="8">
                    <small style="color: var(--text-muted); font-size: 0.8rem;">
                        {{ __('messages.password_min') }}
                    </small>
                </div>

                <div class="form-group">
                    <label for="teacher_new_password_confirmation" class="form-label">{{ __('messages.new_password_confirmation') }} *</label>
                    <input type="password" 
                           name="new_password_confirmation" 
                           id="teacher_new_password_confirmation" 
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
