@extends('layouts.app')

@section('title', 'រង់ចាំការអនុម័ត — STIC Info')

@section('content')
<div style="max-width: 550px; margin: 3rem auto; text-align: center;">
    <div class="card" style="padding: 3rem 2rem;">
        <div style="font-size: 3.5rem; margin-bottom: 1rem;">⏳</div>
        <h2 style="font-size: 1.5rem; color: var(--dark); margin-bottom: 0.8rem;">
            គណនីកំពុងរង់ចាំការអនុម័ត (Pending Approval)
        </h2>
        <p style="color: var(--text-muted); font-size: 1rem; line-height: 1.6; margin-bottom: 2rem;">
            សូមអរគុណលោកគ្រូ/អ្នកគ្រូ <strong>{{ Auth::user()->name }}</strong> សម្រាប់ការចុះឈ្មោះ! គណនីគ្រូបង្រៀនត្រូវទទួលបានការអនុម័ត និងចាត់តាំងថ្នាក់បង្រៀនពីអ្នកគ្រប់គ្រង (Admin) ជាមុនសិន ទើបអាចចូលប្រើប្រាស់បាន។
        </p>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-secondary">ចាកចេញពីប្រព័ន្ធ (Logout)</button>
        </form>
    </div>
</div>
@endsection

