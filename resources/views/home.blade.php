@extends('layouts.app')

@section('title', 'ទំព័រដើម — STIC Info')

@section('content')
<div style="text-align: center; padding: 2rem 1rem; margin-bottom: 2rem;">
    <h1 style="font-size: 2.2rem; color: var(--dark); margin-bottom: 0.8rem;">
        🎓 ប្រព័ន្ធព័ត៌មាន និងកាលវិភាគសិក្សា
    </h1>
    <p style="font-size: 1.1rem; color: var(--text-muted); max-width: 700px; margin: 0 auto 1.5rem auto;">
        វិទ្យាស្ថានពហុបច្ចេកទេសភូមិភាគតេជោសែន សៀមរាប (STIC Siem Reap) — ប្រមូលផ្តុំកាលវិភាគរៀន កាលវិភាគបង្រៀន និងសេចក្តីប្រកាសដំណឹងសំខាន់ៗនៅកន្លែងតែមួយ។
    </p>
    @guest
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <a href="{{ route('login') }}" class="btn btn-primary">ចូលប្រព័ន្ធ (Login)</a>
            <a href="{{ route('login') }}#register" class="btn btn-secondary">ចុះឈ្មោះគណនី (Register)</a>
        </div>
    @endguest
</div>

<!-- Departments -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">📁 ដេប៉ាតឺម៉ង់បណ្តុះបណ្តាល</h2>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
        @foreach ($departments as $dept)
            <div style="border: 1px solid var(--border); border-radius: 8px; padding: 1.2rem; background: #fff;">
                <span class="badge badge-student" style="margin-bottom: 0.5rem;">{{ $dept->code }}</span>
                <h3 style="font-size: 1.1rem; margin-bottom: 0.3rem;">{{ $dept->name_kh ?? $dept->name }}</h3>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem;">{{ $dept->name }}</p>
                <p style="font-size: 0.85rem; color: var(--secondary);">📚 {{ $dept->classes_count }} ថ្នាក់រៀន</p>
            </div>
        @endforeach
    </div>
</div>

<!-- Public Announcements -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">📢 សេចក្តីប្រកាសដំណឹងសាធារណៈ</h2>
    </div>
    @if ($announcements->isEmpty())
        <p style="color: var(--text-muted); text-align: center; padding: 2rem;">មិនទាន់មានការប្រកាសដំណឹងនៅឡើយទេ។</p>
    @else
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            @foreach ($announcements as $ann)
                <div style="border-left: 4px solid var(--primary); padding: 1rem; background: #f8f9fa; border-radius: 4px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <h3 style="font-size: 1.05rem; font-weight: 600;">{{ $ann->title }}</h3>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">{{ $ann->created_at->diffForHumans() }}</span>
                    </div>
                    <p style="font-size: 0.92rem; color: var(--text-main); white-space: pre-line;">{{ $ann->content }}</p>
                    <div style="margin-top: 0.5rem; font-size: 0.8rem; color: var(--text-muted);">
                        ✍️ ដោយ៖ {{ $ann->author->name }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

