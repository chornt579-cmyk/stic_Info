@extends('layouts.app')

@section('title', 'ផ្ទាំងគ្រប់គ្រងសិស្ស — STIC Info')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.6rem; color: var(--dark);">
            👋 សួស្តី, {{ $user->name }}
        </h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">
            🏫 ដេប៉ាតឺម៉ង់៖ <strong>{{ $user->department->name_kh ?? $user->department->name }}</strong> | 
            ថ្នាក់រៀន៖ <strong>{{ $user->schoolClass->name_kh ?? $user->schoolClass->name ?? 'មិនទាន់ចាត់តាំង' }}</strong>
        </p>
    </div>
    <span class="badge badge-student" style="font-size: 0.9rem; padding: 0.4rem 0.8rem;">សិស្ស (Student)</span>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <!-- Left Column: Schedules -->
    <div>
        <!-- Today's Schedule -->
        <div class="card" style="border-top: 4px solid var(--primary);">
            <div class="card-header">
                <h2 class="card-title">📅 កាលវិភាគរៀនថ្ងៃនេះ ({{ ucfirst($todayKey) }})</h2>
            </div>
            @if ($todaySchedules->isEmpty())
                <p style="text-align: center; color: var(--text-muted); padding: 1.5rem;">
                    🎉 ថ្ងៃនេះមិនមានម៉ោងរៀនទេ!
                </p>
            @else
                <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                    @foreach ($todaySchedules as $sch)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8f9fa; border-radius: 8px; border: 1px solid var(--border);">
                            <div>
                                <h3 style="font-size: 1.1rem; color: var(--dark); font-weight: 600; margin-bottom: 0.3rem;">
                                    {{ $sch->subject }}
                                </h3>
                                <p style="font-size: 0.85rem; color: var(--text-muted);">
                                    👨‍🏫 គ្រូបង្រៀន៖ <strong>{{ $sch->teacher->name }}</strong>
                                </p>
                            </div>
                            <div style="text-align: right;">
                                <span class="badge badge-student" style="margin-bottom: 0.3rem;">
                                    វេន {{ ucfirst($sch->shift) }}
                                </span>
                                <p style="font-size: 0.85rem; font-weight: 500;">
                                    🚪 បន្ទប់ {{ $sch->room ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Weekly Timetable -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">🗓️ កាលវិភាគប្រចាំសប្តាហ៍</h2>
            </div>
            @if ($weeklySchedules->isEmpty())
                <p style="text-align: center; color: var(--text-muted); padding: 1.5rem;">
                    មិនទាន់មានកាលវិភាគបង្រៀននៅឡើយទេ។
                </p>
            @else
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ថ្ងៃ</th>
                                <th>វេន</th>
                                <th>មុខវិជ្ជា</th>
                                <th>គ្រូបង្រៀន</th>
                                <th>បន្ទប់</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
                                @if (isset($weeklySchedules[$day]))
                                    @foreach ($weeklySchedules[$day] as $sch)
                                        <tr style="{{ $day === $todayKey ? 'background: #e8f0fe;' : '' }}">
                                            <td><strong>{{ ucfirst($day) }}</strong></td>
                                            <td><span class="badge badge-student">{{ ucfirst($sch->shift) }}</span></td>
                                            <td>{{ $sch->subject }}</td>
                                            <td>{{ $sch->teacher->name }}</td>
                                            <td>{{ $sch->room ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Right Column: Announcements -->
    <div>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">📢 ដំណឹងសម្រាប់អ្នក</h2>
            </div>
            @if ($announcements->isEmpty())
                <p style="text-align: center; color: var(--text-muted); padding: 1rem;">
                    មិនទាន់មានសេចក្តីប្រកាសដំណឹងទេ។
                </p>
            @else
                <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                    @foreach ($announcements as $ann)
                        <div style="border-left: 3px solid var(--secondary); padding: 0.8rem; background: #f8f9fa; border-radius: 4px;">
                            <h4 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 0.3rem;">{{ $ann->title }}</h4>
                            <p style="font-size: 0.85rem; color: var(--text-main); margin-bottom: 0.4rem; white-space: pre-line;">{{ $ann->content }}</p>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">
                                ✍️ {{ $ann->author->name }} · {{ $ann->created_at->diffForHumans() }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
