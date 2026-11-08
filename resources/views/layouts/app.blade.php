<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('messages.app_title')) — STIC Info</title>
    
    <!-- Google Fonts: Inter & Noto Sans Khmer -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Noto+Sans+Khmer:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('styles')
</head>
<body>
    @auth
        <div class="app-wrapper" id="app-wrapper">
            <!-- Mobile Sidebar Overlay Backdrop -->
            <div class="sidebar-overlay" id="sidebar-overlay"></div>

            <!-- ========================================== -->
            <!-- INSTITUTIONAL SIDEBAR NAVIGATION           -->
            <!-- ========================================== -->
            <aside class="app-sidebar" id="app-sidebar" role="navigation" aria-label="Main Navigation">
                <!-- Brand Header with Official STIC Logo -->
                <a href="{{ route('dashboard') }}" class="sidebar-brand">
                    <img src="{{ asset('images/stic-logo.png') }}" alt="STIC Logo" class="sidebar-brand-logo">
                    <div class="sidebar-brand-text">
                        <span class="brand-title">{{ __('messages.institute_short') }}</span>
                        <span class="brand-subtitle">STIC Info Platform</span>
                    </div>
                </a>

                <!-- Navigation Links Hierarchy -->
                <nav class="sidebar-nav">
                    <span class="sidebar-nav-header">ផ្ទាំងចម្បង (Main Menu)</span>
                    <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') . '#overview' : (Auth::user()->isTeacher() ? route('teacher.dashboard') . '#tab-overview' : route('student.dashboard') . '#tab-overview') }}" class="sidebar-link {{ request()->routeIs('dashboard', 'admin.dashboard', 'teacher.dashboard', 'student.dashboard') ? 'active' : '' }}" data-sidebar-tab="{{ Auth::user()->isAdmin() ? 'overview' : 'tab-overview' }}">
                        <span>{{ __('messages.dashboard') }}</span>
                    </a>

                    @if(Auth::user()->isAdmin())
                        <span class="sidebar-nav-header">ការគ្រប់គ្រងស្ថាប័ន (Administration)</span>
                        <a href="{{ route('admin.dashboard') }}#approvals" class="sidebar-link" data-sidebar-tab="approvals">
                            <span>{{ __('messages.pending_approvals') }}</span>
                        </a>
                        <a href="{{ route('admin.dashboard') }}#departments" class="sidebar-link" data-sidebar-tab="departments">
                            <span>{{ __('messages.departments') }}</span>
                        </a>
                        <a href="{{ route('admin.dashboard') }}#classes" class="sidebar-link" data-sidebar-tab="classes">
                            <span>{{ __('messages.classes') }}</span>
                        </a>
                        <a href="{{ route('admin.dashboard') }}#teachers" class="sidebar-link" data-sidebar-tab="teachers">
                            <span>{{ __('messages.teachers') }}</span>
                        </a>
                        <a href="{{ route('admin.dashboard') }}#students" class="sidebar-link" data-sidebar-tab="students">
                            <span>{{ __('messages.students') }}</span>
                        </a>
                        <a href="{{ route('admin.dashboard') }}#schedules" class="sidebar-link" data-sidebar-tab="schedules">
                            <span>{{ __('messages.schedules') }}</span>
                        </a>
                        <a href="{{ route('admin.dashboard') }}#rooms" class="sidebar-link" data-sidebar-tab="rooms">
                            <span>{{ __('messages.rooms') }}</span>
                        </a>
                        <a href="{{ route('admin.dashboard') }}#audit-logs" class="sidebar-link" data-sidebar-tab="audit-logs">
                            <span>{{ __('messages.audit_logs') }}</span>
                        </a>
                    @elseif(Auth::user()->isTeacher())
                        <span class="sidebar-nav-header">ភារកិច្ចបង្រៀន (Teaching)</span>
                        <a href="{{ route('teacher.dashboard') }}#tab-overview" class="sidebar-link" data-sidebar-tab="tab-overview">
                            <span>{{ __('messages.tab_overview') }}</span>
                        </a>
                        <a href="{{ route('teacher.dashboard') }}#tab-weekly" class="sidebar-link" data-sidebar-tab="tab-weekly">
                            <span>{{ __('messages.weekly_schedule') }}</span>
                        </a>
                        <a href="{{ route('teacher.dashboard') }}#tab-classes" class="sidebar-link" data-sidebar-tab="tab-classes">
                            <span>{{ __('messages.assigned_classes') }}</span>
                        </a>
                        <a href="{{ route('teacher.dashboard') }}#tab-announcements" class="sidebar-link" data-sidebar-tab="tab-announcements">
                            <span>{{ __('messages.announcements') }}</span>
                        </a>
                        <a href="{{ route('teacher.dashboard') }}#tab-profile" class="sidebar-link" data-sidebar-tab="tab-profile">
                            <span>{{ __('messages.profile') }}</span>
                        </a>
                    @elseif(Auth::user()->isStudent())
                        <span class="sidebar-nav-header">ការសិក្សា (Academic)</span>
                        <a href="{{ route('student.dashboard') }}#tab-overview" class="sidebar-link" data-sidebar-tab="tab-overview">
                            <span>{{ __('messages.tab_overview') }}</span>
                        </a>
                        <a href="{{ route('student.dashboard') }}#tab-weekly" class="sidebar-link" data-sidebar-tab="tab-weekly">
                            <span>{{ __('messages.weekly_schedule') }}</span>
                        </a>
                        <a href="{{ route('student.dashboard') }}#tab-announcements" class="sidebar-link" data-sidebar-tab="tab-announcements">
                            <span>{{ __('messages.announcements') }}</span>
                        </a>
                        <a href="{{ route('student.dashboard') }}#tab-classmates" class="sidebar-link" data-sidebar-tab="tab-classmates">
                            <span>{{ __('messages.tab_my_class') }}</span>
                        </a>
                        <a href="{{ route('student.dashboard') }}#tab-profile" class="sidebar-link" data-sidebar-tab="tab-profile">
                            <span>{{ __('messages.profile') }}</span>
                        </a>
                    @endif

                    <span class="sidebar-nav-header">ព័ត៌មានទូទៅ (General)</span>
                    <a href="{{ route('home') }}" class="sidebar-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        <span>{{ __('messages.home') }}</span>
                    </a>
                </nav>

                <!-- Sidebar Footer: User Summary -->
                <div class="sidebar-footer">
                    <div class="sidebar-user">
                        <div class="sidebar-avatar">
                            {{ mb_substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="sidebar-user-info">
                            <span class="sidebar-user-name">{{ Auth::user()->name }}</span>
                            <span class="sidebar-user-role">{{ ucfirst(Auth::user()->role) }}</span>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline" style="color: #ffffff; border-color: rgba(255,255,255,0.2); padding: 0.25rem 0.6rem; font-size: 0.75rem;" title="{{ __('messages.logout') }}">
                            {{ __('messages.logout') }}
                        </button>
                    </form>
                </div>
            </aside>

            <!-- ========================================== -->
            <!-- MAIN APPLICATION CONTENT SHELL             -->
            <!-- ========================================== -->
            <div class="app-main">
                <!-- Institutional Topbar Header -->
                <header class="app-topbar" role="banner">
                    <div class="topbar-left">
                        <button type="button" class="topbar-toggle-btn" id="sidebar-toggle" aria-label="Toggle Navigation Drawer" aria-expanded="false">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                        </button>
                        <h1 class="topbar-title">@yield('page_title', __('messages.app_title'))</h1>
                    </div>

                    <div class="topbar-right">
                        <!-- Bilingual Language Capsule Switcher -->
                        <div class="language-capsule">
                            <a href="{{ request()->fullUrlWithQuery(['lang' => 'km']) }}" class="language-btn {{ app()->getLocale() === 'km' ? 'active' : '' }}">ខ្មែរ</a>
                            <span style="color: #bdc1c6;">|</span>
                            <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="language-btn {{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
                        </div>

                        <!-- Role Badge Indicator -->
                        <span class="badge badge-{{ Auth::user()->role }}">
                            {{ strtoupper(Auth::user()->role) }}
                        </span>

                        <!-- User Profile Link or Logout Action -->
                        <form action="{{ route('logout') }}" method="POST" style="display: inline-block;">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-sm">
                                {{ __('messages.logout') }}
                            </button>
                        </form>
                    </div>
                </header>

                <!-- Page Content Body -->
                <main class="app-content" role="main">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            <span class="alert-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </span>
                            <div>{{ session('success') }}</div>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            <span class="alert-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            </span>
                            <div>{{ session('error') }}</div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <span class="alert-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            </span>
                            <ul style="margin-left: 1.25rem;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </main>

                <!-- Footer -->
                <footer class="app-footer">
                    <p>&copy; {{ date('Y') }} <span>{{ __('messages.institute_name') }}</span> (STIC Siem Reap) · STIC Info Platform</p>
                </footer>
            </div>
        </div>
    @else
        <!-- ========================================== -->
        <!-- GUEST & PUBLIC INSTITUTIONAL HEADER        -->
        <!-- ========================================== -->
        <header class="public-header" role="banner">
            <div class="public-header-container">
                <a href="{{ route('home') }}" class="public-brand" aria-label="{{ __('messages.institute_name') }}">
                    <img src="{{ asset('images/stic-logo.png') }}" alt="STIC Logo" class="public-brand-logo">
                    <div class="public-brand-text">
                        <span class="public-brand-title">{{ __('messages.institute_short') }}</span>
                        <span class="public-brand-subtitle">{{ __('messages.hero_tagline') }}</span>
                    </div>
                </a>

                <div class="public-nav-wrapper" id="public-nav-wrapper">
                    <ul class="public-nav-links" role="navigation" aria-label="Public Navigation">
                        <li><a href="{{ route('home') }}" class="public-nav-link">{{ __('messages.home') }}</a></li>
                        <li><a href="{{ route('home') }}#departments" class="public-nav-link">{{ __('messages.academic_departments') }}</a></li>
                        <li><a href="{{ route('home') }}#announcements" class="public-nav-link">{{ __('messages.official_announcements') }}</a></li>
                        <li><a href="{{ route('home') }}#about" class="public-nav-link">{{ __('messages.about_institute') }}</a></li>
                    </ul>

                    <div class="public-nav-actions">
                        <!-- Bilingual Language Capsule -->
                        <div class="language-capsule" aria-label="Language Selector">
                            <a href="{{ request()->fullUrlWithQuery(['lang' => 'km']) }}" class="language-btn {{ app()->getLocale() === 'km' ? 'active' : '' }}">ខ្មែរ</a>
                            <span style="color: #bdc1c6;">|</span>
                            <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="language-btn {{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
                        </div>

                        <a href="{{ route('login') }}" class="btn btn-outline btn-sm">{{ __('messages.login') }}</a>
                        <a href="{{ route('login') }}#register" class="btn btn-primary btn-sm">{{ __('messages.register') }}</a>
                    </div>
                </div>

                <!-- Mobile Hamburger Toggle -->
                <button type="button" class="public-mobile-toggle" id="public-nav-toggle" aria-label="Toggle Public Menu" aria-expanded="false">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
            </div>
        </header>

        <!-- Main Content Area -->
        @if (request()->routeIs('home'))
            <div class="public-home-wrapper" role="main">
                @if (session('success'))
                    <div style="max-width: 1320px; margin: 1.5rem auto 0 auto; padding: 0 1.5rem;">
                        <div class="alert alert-success" role="alert">
                            <span class="alert-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </span>
                            <div>{{ session('success') }}</div>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div style="max-width: 1320px; margin: 1.5rem auto 0 auto; padding: 0 1.5rem;">
                        <div class="alert alert-danger" role="alert">
                            <span class="alert-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            </span>
                            <div>{{ session('error') }}</div>
                        </div>
                    </div>
                @endif

                @yield('content')
            </div>
        @else
            <main style="flex: 1; padding: 2.5rem 1.5rem; width: 100%; max-width: 1200px; margin: 0 auto;" role="main">
                @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        <span class="alert-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </span>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger" role="alert">
                        <span class="alert-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        </span>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <span class="alert-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        </span>
                        <ul style="margin-left: 1.25rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        @endif

        <!-- ========================================== -->
        <!-- INSTITUTIONAL FULL-WIDTH FOOTER            -->
        <!-- ========================================== -->
        <footer class="institutional-footer" role="contentinfo">
            <div class="footer-container">
                <div class="footer-grid">
                    <!-- Brand Column -->
                    <div class="footer-brand-block">
                        <img src="{{ asset('images/stic-logo.png') }}" alt="STIC Logo" class="footer-brand-logo">
                        <h2 class="footer-title">{{ __('messages.institute_name') }}</h2>
                        <div class="footer-subtitle">{{ __('messages.hero_subtitle') }}</div>
                        <p class="footer-desc">
                            {{ __('messages.hero_desc') }}
                        </p>
                    </div>

                    <!-- Quick Navigation Links -->
                    <div>
                        <h3 class="footer-heading">{{ __('messages.quick_links') }}</h3>
                        <ul class="footer-nav-list">
                            <li><a href="{{ route('home') }}" class="footer-nav-link">{{ __('messages.home') }}</a></li>
                            <li><a href="{{ route('home') }}#departments" class="footer-nav-link">{{ __('messages.academic_departments') }}</a></li>
                            <li><a href="{{ route('home') }}#announcements" class="footer-nav-link">{{ __('messages.official_announcements') }}</a></li>
                            <li><a href="{{ route('home') }}#about" class="footer-nav-link">{{ __('messages.about_institute') }}</a></li>
                            @guest
                                <li><a href="{{ route('login') }}" class="footer-nav-link">{{ __('messages.login') }}</a></li>
                                <li><a href="{{ route('login') }}#register" class="footer-nav-link">{{ __('messages.register') }}</a></li>
                            @else
                                <li><a href="{{ route('dashboard') }}" class="footer-nav-link">{{ __('messages.dashboard') }}</a></li>
                            @endguest
                        </ul>
                    </div>

                    <!-- Campus Information & Location -->
                    <div>
                        <h3 class="footer-heading">{{ __('messages.contact_title') }}</h3>
                        <div class="footer-info-block">
                            <div>
                                <span class="footer-info-label">{{ __('messages.location_title') }}</span>
                                <span>{{ __('messages.location_address') }}</span>
                            </div>
                            <div>
                                <span class="footer-info-label">{{ __('messages.contact_title') }}</span>
                                <span>info@stic.edu.kh · (+855) 63 760 000</span>
                            </div>
                            <div>
                                <span class="footer-info-label">{{ __('messages.official_platform') }}</span>
                                <span>STIC Information & Timetable Management System</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Copyright Bar -->
            <div class="footer-bottom-bar">
                <div class="footer-bottom-container">
                    <div>
                        &copy; {{ date('Y') }} <span class="footer-bottom-brand">{{ __('messages.institute_name') }}</span> (STIC Siem Reap). {{ __('messages.all_rights_reserved') }}.
                    </div>
                    <div>
                        <span>{{ __('messages.institution_values') }}</span>
                    </div>
                </div>
            </div>
        </footer>
    @endauth

    @yield('scripts')
</body>
</html>
