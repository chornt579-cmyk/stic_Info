@extends('layouts.app')

@section('title', 'ផ្ទាំងគ្រប់គ្រងគ្រូបង្រៀន — STIC Info')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.6rem; color: var(--dark);">
            👨‍🏫 សួស្តី, លោកគ្រូ/អ្នកគ្រូ {{ $user->name }}
        </h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">
            🏫 ដេប៉ាតឺម៉ង់៖ <strong>{{ $user->department->name_kh ?? $user->department->name }}</strong>
        </p>
    </div>
    <span class="badge badge-teacher" style="font-size: 0.9rem; padding: 0.4rem 0.8rem;">គ្រូបង្រៀន (Teacher)</span>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <!-- Left Column: Teaching Schedule & Send Announcement -->
    <div>
        <!-- Teaching Schedule -->
        <div class="card" style="border-top: 4px solid var(--secondary);">
            <div class="card-header">
                <h2 class="card-title">📖 កាលវិភាគបង្រៀនរបស់ខ្ញុំ</h2>
            </div>
            @if ($schedules->isEmpty())
                <p style="text-align: center; color: var(--text-muted); padding: 1.5rem;">
                    មិនទាន់មានម៉ោងបង្រៀនត្រូវបានចាត់តាំងនៅឡើយទេ។
                </p>
            @else
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ថ្ងៃ</th>
                                <th>វេន</th>
                                <th>ថ្នាក់រៀន</th>
                                <th>មុខវិជ្ជា</th>
                                <th>បន្ទប់</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($schedules as $sch)
                                <tr>
                                    <td><strong>{{ ucfirst($sch->day_of_week) }}</strong></td>
                                    <td><span class="badge badge-teacher">{{ ucfirst($sch->shift) }}</span></td>
                                    <td>{{ $sch->schoolClass->name_kh ?? $sch->schoolClass->name }}</td>
                                    <td>{{ $sch->subject }}</td>
                                    <td>{{ $sch->room ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Post Announcement Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">✍️ ផ្ញើដំណឹងទៅកាន់ថ្នាក់ដែលខ្ញុំបង្រៀន</h2>
            </div>
            @if ($classes->isEmpty())
                <p style="color: var(--text-muted);">
                    ⚠️ លោកគ្រូ/អ្នកគ្រូ មិនទាន់ត្រូវបានចាត់តាំងឱ្យបង្រៀនថ្នាក់ណាមួយនៅឡើយទេ (សូមទាក់ទង Admin)។
                </p>
            @else
                <form action="{{ route('teacher.announcements.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">ជ្រើសរើសថ្នាក់រៀន</label>
                        <select name="class_id" class="form-control" required>
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name_kh ?? $c->name }} (បន្ទប់ {{ $c->room }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">ចំណងជើងដំណឹង</label>
                        <input type="text" name="title" class="form-control" required placeholder="ឧ. ថ្ងៃនេះសម្រាកម៉ោងរៀន Web...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">ខ្លឹមសារដំណឹង</label>
                        <textarea name="content" class="form-control" rows="3" required placeholder="សរសេរខ្លឹមសារលម្អិតនៅទីនេះ..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">ផ្ញើដំណឹង</button>
                </form>
            @endif
        </div>
    </div>

    <!-- Right Column: Announcements Sent & Internal Teacher News -->
    <div>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">📢 ដំណឹងផ្ទៃក្នុងគ្រូបង្រៀន</h2>
            </div>
            @if ($announcements->isEmpty())
                <p style="text-align: center; color: var(--text-muted); padding: 1rem;">
                    មិនទាន់មានដំណឹងទេ។
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
