@extends('layouts.app')

@section('title', 'ផ្ទាំងគ្រប់គ្រងអ្នកគ្រប់គ្រង — STIC Info')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.6rem; color: var(--dark);">
            🛠️ ផ្ទាំងគ្រប់គ្រង Admin (School Administration)
        </h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">
            គ្រប់គ្រងដេប៉ាតឺម៉ង់ ថ្នាក់រៀន គ្រូបង្រៀន សិស្ស កាលវិភាគ និងសេចក្តីប្រកាសដំណឹង
        </p>
    </div>
    <span class="badge badge-admin" style="font-size: 0.9rem; padding: 0.4rem 0.8rem;">អ្នកគ្រប់គ្រង (Admin)</span>
</div>

<!-- Navigation Tabs -->
<div style="display: flex; gap: 0.5rem; border-bottom: 2px solid var(--border); margin-bottom: 1.5rem; overflow-x: auto; padding-bottom: 0.5rem;">
    <button type="button" class="btn btn-primary btn-sm" onclick="showAdminTab('schedules')" id="btn-tab-schedules">🗓️ កាលវិភាគ (Schedules)</button>
    <button type="button" class="btn btn-secondary btn-sm" onclick="showAdminTab('teachers')" id="btn-tab-teachers">👨‍🏫 គ្រូបង្រៀន (Teachers)</button>
    <button type="button" class="btn btn-secondary btn-sm" onclick="showAdminTab('classes')" id="btn-tab-classes">🏫 ថ្នាក់រៀន (Classes)</button>
    <button type="button" class="btn btn-secondary btn-sm" onclick="showAdminTab('departments')" id="btn-tab-departments">📁 ដេប៉ាតឺម៉ង់ (Departments)</button>
    <button type="button" class="btn btn-secondary btn-sm" onclick="showAdminTab('announcements')" id="btn-tab-announcements">📢 ប្រកាសដំណឹង (Announcements)</button>
</div>

<!-- TAB 1: SCHEDULES -->
<div id="tab-schedules" class="admin-tab">
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
        <!-- Add Schedule Form -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">➕ បន្ថែមកាលវិភាគបង្រៀន</h3>
            </div>
            <form action="{{ route('admin.schedules.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">ថ្នាក់រៀន</label>
                    <select name="class_id" class="form-control" required>
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name_kh ?? $c->name }} ({{ $c->department->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">គ្រូបង្រៀន</label>
                    <select name="teacher_id" class="form-control" required>
                        @foreach ($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">មុខវិជ្ជា</label>
                    <input type="text" name="subject" class="form-control" required placeholder="ឧ. Web Development">
                </div>
                <div class="form-group">
                    <label class="form-label">ថ្ងៃ</label>
                    <select name="day_of_week" class="form-control" required>
                        <option value="monday">ច័ន្ទ (Monday)</option>
                        <option value="tuesday">អង្គារ (Tuesday)</option>
                        <option value="wednesday">ពុធ (Wednesday)</option>
                        <option value="thursday">ព្រហស្បតិ៍ (Thursday)</option>
                        <option value="friday">សុក្រ (Friday)</option>
                        <option value="saturday">សៅរ៍ (Saturday)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">វេន</label>
                    <select name="shift" class="form-control" required>
                        <option value="morning">ព្រឹក (Morning)</option>
                        <option value="afternoon">រសៀល (Afternoon)</option>
                        <option value="evening">យប់ (Evening)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">បន្ទប់រៀន</label>
                    <input type="text" name="room" class="form-control" placeholder="ឧ. 101, Lab 1">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">រក្សាទុកកាលវិភាគ</button>
            </form>
        </div>

        <!-- Schedules List -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">📋 បញ្ជីកាលវិភាគបង្រៀន ({{ $schedules->count() }})</h3>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ថ្ងៃ</th>
                            <th>វេន</th>
                            <th>ថ្នាក់រៀន</th>
                            <th>មុខវិជ្ជា</th>
                            <th>គ្រូបង្រៀន</th>
                            <th>បន្ទប់</th>
                            <th>សកម្មភាព</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($schedules as $sch)
                            <tr>
                                <td><strong>{{ ucfirst($sch->day_of_week) }}</strong></td>
                                <td><span class="badge badge-student">{{ ucfirst($sch->shift) }}</span></td>
                                <td>{{ $sch->schoolClass->name_kh ?? $sch->schoolClass->name }}</td>
                                <td>{{ $sch->subject }}</td>
                                <td>{{ $sch->teacher->name }}</td>
                                <td>{{ $sch->room ?? 'N/A' }}</td>
                                <td>
                                    <form action="{{ route('admin.schedules.destroy', $sch) }}" method="POST" onsubmit="return confirm('តើអ្នកប្រាកដជាចង់លុបកាលវិភាគនេះមែនទេ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">លុប</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">មិនទាន់មានកាលវិភាគនៅឡើយទេ។</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- TAB 2: TEACHERS -->
<div id="tab-teachers" class="admin-tab" style="display: none;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">👨‍🏫 គ្រប់គ្រងគ្រូបង្រៀន និងការអនុម័ត ({{ $teachers->count() }})</h3>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ឈ្មោះ</th>
                        <th>អ៊ីមែល / ទូរស័ព្ទ</th>
                        <th>ដេប៉ាតឺម៉ង់</th>
                        <th>ស្ថានភាព</th>
                        <th>ថ្នាក់ដែលបង្រៀន</th>
                        <th>សកម្មភាព</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($teachers as $t)
                        <tr>
                            <td><strong>{{ $t->name }}</strong></td>
                            <td>{{ $t->email }}<br><small>{{ $t->phone ?? 'គ្មាន' }}</small></td>
                            <td>{{ $t->department->code ?? 'N/A' }}</td>
                            <td>
                                @if ($t->status === 'active')
                                    <span class="badge badge-teacher">អនុម័តរួច</span>
                                @else
                                    <span class="badge badge-pending">រង់ចាំអនុម័ត</span>
                                @endif
                            </td>
                            <td>
                                @foreach ($t->teachingClasses as $tc)
                                    <span class="badge badge-student" style="margin-right: 2px;">{{ $tc->name_kh ?? $tc->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                @if ($t->status === 'pending')
                                    <form action="{{ route('admin.teachers.approve', $t) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-sm">អនុម័ត (Approve)</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TAB 3: CLASSES -->
<div id="tab-classes" class="admin-tab" style="display: none;">
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
        <div class="card">
            <div class="card-header"><h3 class="card-title">➕ បន្ថែមថ្នាក់រៀន</h3></div>
            <form action="{{ route('admin.classes.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">ដេប៉ាតឺម៉ង់</label>
                    <select name="department_id" class="form-control" required>
                        @foreach ($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name_kh ?? $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">ឈ្មោះថ្នាក់ (En)</label>
                    <input type="text" name="name" class="form-control" required placeholder="IT Year 1 Morning">
                </div>
                <div class="form-group">
                    <label class="form-label">ឈ្មោះថ្នាក់ (Kh)</label>
                    <input type="text" name="name_kh" class="form-control" placeholder="IT ឆ្នាំ១ ព្រឹក">
                </div>
                <div class="form-group">
                    <label class="form-label">ឆ្នាំសិក្សា</label>
                    <input type="number" name="year" class="form-control" value="1" min="1" max="5" required>
                </div>
                <div class="form-group">
                    <label class="form-label">វេនសិក្សា</label>
                    <select name="shift" class="form-control" required>
                        <option value="morning">ព្រឹក</option>
                        <option value="afternoon">រសៀល</option>
                        <option value="evening">យប់</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">បន្ទប់ / ជាន់</label>
                    <input type="text" name="room" class="form-control" placeholder="បន្ទប់ 101, ជាន់ទី 1">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">បង្កើតថ្នាក់</button>
            </form>
        </div>
        <div class="card">
            <div class="card-header"><h3 class="card-title">🏫 បញ្ជីថ្នាក់រៀន</h3></div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr><th>ឈ្មោះថ្នាក់</th><th>ដេប៉ាតឺម៉ង់</th><th>ឆ្នាំ</th><th>វេន</th><th>បន្ទប់</th><th>សកម្មភាព</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($classes as $c)
                            <tr>
                                <td><strong>{{ $c->name_kh ?? $c->name }}</strong></td>
                                <td>{{ $c->department->code }}</td>
                                <td>ឆ្នាំ {{ $c->year }}</td>
                                <td>{{ ucfirst($c->shift) }}</td>
                                <td>{{ $c->room ?? 'N/A' }}</td>
                                <td>
                                    <form action="{{ route('admin.classes.destroy', $c) }}" method="POST" onsubmit="return confirm('តើអ្នកពិតជាចង់លុបថ្នាក់នេះមែនទេ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">លុប</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- TAB 4: DEPARTMENTS -->
<div id="tab-departments" class="admin-tab" style="display: none;">
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
        <div class="card">
            <div class="card-header"><h3 class="card-title">➕ បន្ថែមដេប៉ាតឺម៉ង់</h3></div>
            <form action="{{ route('admin.departments.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">ឈ្មោះជាភាសាអង់គ្លេស</label>
                    <input type="text" name="name" class="form-control" required placeholder="Information Technology">
                </div>
                <div class="form-group">
                    <label class="form-label">ឈ្មោះជាភាសាខ្មែរ</label>
                    <input type="text" name="name_kh" class="form-control" placeholder="បច្ចេកវិទ្យាព័ត៌មាន">
                </div>
                <div class="form-group">
                    <label class="form-label">កូដកាត់ (Code)</label>
                    <input type="text" name="code" class="form-control" required placeholder="IT">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">បង្កើតដេប៉ាតឺម៉ង់</button>
            </form>
        </div>
        <div class="card">
            <div class="card-header"><h3 class="card-title">📁 បញ្ជីដេប៉ាតឺម៉ង់</h3></div>
            <div class="table-responsive">
                <table>
                    <thead><tr><th>កូដ</th><th>ឈ្មោះខ្មែរ</th><th>ឈ្មោះអង់គ្លេស</th><th>ចំនួនថ្នាក់</th><th>សកម្មភាព</th></tr></thead>
                    <tbody>
                        @foreach ($departments as $d)
                            <tr>
                                <td><span class="badge badge-student">{{ $d->code }}</span></td>
                                <td><strong>{{ $d->name_kh ?? 'N/A' }}</strong></td>
                                <td>{{ $d->name }}</td>
                                <td>{{ $d->classes_count }} ថ្នាក់</td>
                                <td>
                                    <form action="{{ route('admin.departments.destroy', $d) }}" method="POST" onsubmit="return confirm('តើអ្នកពិតជាចង់លុបមែនទេ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">លុប</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- TAB 5: ANNOUNCEMENTS -->
<div id="tab-announcements" class="admin-tab" style="display: none;">
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
        <div class="card">
            <div class="card-header"><h3 class="card-title">➕ បង្កើតសេចក្តីប្រកាសដំណឹង</h3></div>
            <form action="{{ route('admin.announcements.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">ទិសដៅដំណឹង (Target Audience)</label>
                    <select name="target_type" class="form-control" required>
                        <option value="public">📢 សាធារណៈ (Public - លើទំព័រមុខ)</option>
                        <option value="all_students">🧑‍🎓 សិស្សទាំងអស់ (All Students)</option>
                        <option value="all_teachers">👨‍🏫 គ្រូបង្រៀនទាំងអស់ (All Teachers)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">ចំណងជើង</label>
                    <input type="text" name="title" class="form-control" required placeholder="ចំណងជើងដំណឹង...">
                </div>
                <div class="form-group">
                    <label class="form-label">ខ្លឹមសារ</label>
                    <textarea name="content" class="form-control" rows="4" required placeholder="សរសេរខ្លឹមសារលម្អិត..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">ប្រកាសដំណឹង</button>
            </form>
        </div>
        <div class="card">
            <div class="card-header"><h3 class="card-title">📢 បញ្ជីដំណឹងដែលបានប្រកាស</h3></div>
            <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                @foreach ($announcements as $ann)
                    <div style="border-left: 4px solid var(--primary); padding: 0.8rem; background: #f8f9fa; border-radius: 4px; display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <span class="badge badge-student" style="margin-bottom: 0.3rem;">{{ $ann->target_type }}</span>
                            <h4 style="font-size: 1rem; font-weight: 600;">{{ $ann->title }}</h4>
                            <p style="font-size: 0.85rem; color: var(--text-main); margin: 0.3rem 0;">{{ $ann->content }}</p>
                            <small style="color: var(--text-muted);">{{ $ann->author->name }} · {{ $ann->created_at->diffForHumans() }}</small>
                        </div>
                        <form action="{{ route('admin.announcements.destroy', $ann) }}" method="POST" onsubmit="return confirm('តើអ្នកពិតជាចង់លុបដំណឹងនេះមែនទេ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">លុប</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function showAdminTab(tabName) {
        document.querySelectorAll('.admin-tab').forEach(el => el.style.display = 'none');
        document.querySelectorAll('[id^="btn-tab-"]').forEach(btn => {
            btn.className = 'btn btn-secondary btn-sm';
        });

        document.getElementById('tab-' + tabName).style.display = 'block';
        document.getElementById('btn-tab-' + tabName).className = 'btn btn-primary btn-sm';
    }
</script>
@endsection

