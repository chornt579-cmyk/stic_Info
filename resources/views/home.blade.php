@extends('layouts.app')

@section('title', __('messages.home') . ' — ' . __('messages.institute_short'))

@section('content')
<!-- ========================================================================== -->
<!-- 1. INSTITUTIONAL HERO SECTION                                              -->
<!-- ========================================================================== -->
<section class="home-hero" aria-label="Hero Section">
    <div class="hero-container">
        <!-- Hero Left Column: Academic Presentation & Action Controls -->
        <div class="hero-left">
            <div class="hero-kicker-badge">
                <span class="hero-kicker-dot" aria-hidden="true"></span>
                <span>{{ __('messages.official_platform') }}</span>
            </div>

            <h1 class="hero-title">
                {{ __('messages.hero_title') }}
            </h1>

            <div class="hero-subtitle">
                {{ __('messages.hero_subtitle') }}
            </div>

            <p class="hero-desc">
                {{ __('messages.hero_desc') }}
            </p>

            <div class="hero-actions">
                @guest
                    <a href="{{ route('login') }}" class="btn-hero-primary">
                        {{ __('messages.enter_portal') }}
                    </a>
                    <a href="{{ route('login') }}#register" class="btn-hero-secondary">
                        {{ __('messages.register_portal') }}
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="btn-hero-primary">
                        {{ __('messages.dashboard') }}
                    </a>
                    <a href="#departments" class="btn-hero-secondary">
                        {{ __('messages.academic_departments') }}
                    </a>
                @endguest
            </div>

            <!-- Institutional Key Indicators (Strictly Typographic - Zero Emojis) -->
            <div class="hero-stats-strip">
                <div class="stat-item">
                    <span class="stat-value">{{ __('messages.stat_week_coverage') }}</span>
                    <span class="stat-label">{{ __('messages.stat_week_desc') }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">{{ __('messages.stat_sync') }}</span>
                    <span class="stat-label">{{ __('messages.stat_sync_desc') }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">{{ __('messages.stat_standard') }}</span>
                    <span class="stat-label">{{ __('messages.stat_standard_desc') }}</span>
                </div>
            </div>
        </div>

        <!-- Hero Right Column: Official Institutional Emblem Showcase -->
        <div class="hero-right">
            <div class="hero-emblem-showcase">
                <div class="hero-emblem-halo">
                    <img src="{{ asset('images/stic-logo.png') }}" alt="{{ __('messages.institute_name') }}" class="hero-emblem-logo">
                </div>

                <div class="hero-emblem-credentials">
                    <span class="hero-emblem-kicker">{{ __('messages.institution_type') }}</span>
                    <div class="hero-emblem-divider" aria-hidden="true"></div>
                    <span class="hero-emblem-motto">{{ __('messages.institution_values') }}</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================================== -->
<!-- 2. ACADEMIC DEPARTMENTS SECTION                                            -->
<!-- ========================================================================== -->
<section class="home-section-alt" id="departments" aria-label="{{ __('messages.academic_departments') }}">
    <div class="section-container">
        <div class="section-header">
            <div class="section-kicker">{{ __('messages.academic_departments_kicker') }}</div>
            <h2 class="section-title">{{ __('messages.academic_departments') }}</h2>
            <div class="section-divider" aria-hidden="true"></div>
            <p class="section-subtitle">{{ __('messages.academic_departments_subtitle') }}</p>
        </div>

        <div class="dept-grid">
            @forelse ($departments as $dept)
                <div class="dept-card">
                    <div>
                        <span class="dept-code-pill">
                            {{ __('messages.department_code_label') }} · {{ $dept->code }}
                        </span>
                        <h3 class="dept-title-kh">{{ $dept->name_kh ?? $dept->name }}</h3>
                        <p class="dept-title-en">{{ $dept->name }}</p>
                    </div>

                    <div class="dept-meta-strip">
                        <span class="dept-classes-label">{{ __('messages.classes') }}</span>
                        <span class="dept-classes-count">
                            {{ $dept->classes_count }} {{ __('messages.classes_count_suffix') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="announcement-empty" style="grid-column: 1 / -1;">
                    {{ __('messages.empty_data') }}
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- ========================================================================== -->
<!-- 3. OFFICIAL ANNOUNCEMENTS SECTION                                          -->
<!-- ========================================================================== -->
<section class="home-section" id="announcements" aria-label="{{ __('messages.official_announcements') }}">
    <div class="section-container">
        <div class="section-header">
            <div class="section-kicker">{{ __('messages.official_announcements_kicker') }}</div>
            <h2 class="section-title">{{ __('messages.official_announcements') }}</h2>
            <div class="section-divider" aria-hidden="true"></div>
            <p class="section-subtitle">{{ __('messages.official_announcements_subtitle') }}</p>
        </div>

        @if ($announcements->isEmpty())
            <div class="announcement-empty">
                {{ __('messages.no_public_announcements') }}
            </div>
        @else
            <div class="announcements-feed">
                @foreach ($announcements as $ann)
                    <article class="announcement-card">
                        <div class="announcement-header">
                            <h3 class="announcement-title">{{ $ann->title }}</h3>
                            <time class="announcement-date" datetime="{{ $ann->created_at->toIso8601String() }}">
                                {{ $ann->created_at->format('d/m/Y') }} · {{ $ann->created_at->diffForHumans() }}
                            </time>
                        </div>

                        <p class="announcement-content">{{ $ann->content }}</p>

                        <div class="announcement-footer">
                            <div>
                                <span>{{ __('messages.published_by') }}៖</span>
                                <span class="announcement-author-text">
                                    {{ $ann->author ? $ann->author->name : __('messages.app_name') }}
                                </span>
                            </div>
                            <div>
                                <span>{{ __('messages.audience_label') }}៖ {{ __('messages.audience_public') }}</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

<!-- ========================================================================== -->
<!-- 4. INSTITUTIONAL ABOUT & PILLARS SECTION                                   -->
<!-- ========================================================================== -->
<section class="home-section-alt" id="about" aria-label="{{ __('messages.about_institute') }}">
    <div class="section-container">
        <div class="section-header">
            <div class="section-kicker">{{ __('messages.hero_subtitle') }}</div>
            <h2 class="section-title">{{ __('messages.about_institute') }}</h2>
            <div class="section-divider" aria-hidden="true"></div>
            <p class="section-subtitle">{{ __('messages.hero_desc') }}</p>
        </div>

        <div class="about-grid">
            <!-- Pillar 1: Mission -->
            <div class="about-card">
                <div class="about-accent-line" aria-hidden="true"></div>
                <h3 class="about-card-title">{{ __('messages.about_mission_title') }}</h3>
                <p class="about-card-text">{{ __('messages.about_mission_desc') }}</p>
            </div>

            <!-- Pillar 2: Vision -->
            <div class="about-card">
                <div class="about-accent-line" aria-hidden="true"></div>
                <h3 class="about-card-title">{{ __('messages.about_vision_title') }}</h3>
                <p class="about-card-text">{{ __('messages.about_vision_desc') }}</p>
            </div>

            <!-- Pillar 3: Digital Modernization Platform -->
            <div class="about-card">
                <div class="about-accent-line" aria-hidden="true"></div>
                <h3 class="about-card-title">{{ __('messages.about_digital_title') }}</h3>
                <p class="about-card-text">{{ __('messages.about_digital_desc') }}</p>
            </div>
        </div>
    </div>
</section>
@endsection
