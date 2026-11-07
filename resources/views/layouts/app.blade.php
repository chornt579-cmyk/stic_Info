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
                    <div class="sidebar-logo-wrapper">
                        <img src="{{ asset('images/stic-logo.png') }}" alt="STIC Logo" class="sidebar-logo">
                    </div>
                    <div class="sidebar-brand-text">
                        <span class="brand-title">{{ __('messages.institute_short') }}</span>
                        <span class="brand-subtitle">STIC Info Platform</span>
                    </div>
                </a>

                <!-- Navigation Links Hierarchy -->
                <nav class="sidebar-nav">
                    <span class="sidebar-nav-header">ផ្ទាំងចម្បង (Main Menu)</span>
                    <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard', 'admin.dashboard', 'teacher.dashboard', 'student.dashboard') ? 'active' : '' }}">
                        <span class="sidebar-icon" aria-hidden="true">📊</span>
                        <span>{{ __('messages.dashboard') }}</span>
                    </a>

                    @if(Auth::user()->isAdmin())
                        <span class="sidebar-nav-header">ការគ្រប់គ្រងស្ថាប័ន (Administration)</span>
                        <a href="{{ route('admin.dashboard') }}#approvals" class="sidebar-link">
                            <span class="sidebar-icon" aria-hidden="true">⏳</span>
                            <span>{{ __('messages.pending_approvals') }}</span>
                        </a>
                        <a href="{{ route('admin.dashboard') }}#departments" class="sidebar-link">
                            <span class="sidebar-icon" aria-hidden="true">📁</span>
                            <span>{{ __('messages.departments') }}</span>
                        </a>
                        <a href="{{ route('admin.dashboard') }}#classes" class="sidebar-link">
                            <span class="sidebar-icon" aria-hidden="true">🏫</span>
                            <span>{{ __('messages.classes') }}</span>
                        </a>
                        <a href="{{ route('admin.dashboard') }}#teachers" class="sidebar-link">
                            <span class="sidebar-icon" aria-hidden="true">👨‍🏫</span>
                            <span>{{ __('messages.teachers') }}</span>
                        </a>
                        <a href="{{ route('admin.dashboard') }}#students" class="sidebar-link">
                            <span class="sidebar-icon" aria-hidden="true">🎓</span>
                            <span>{{ __('messages.students') }}</span>
                        </a>
                        <a href="{{ route('admin.dashboard') }}#schedules" class="sidebar-link">
                            <span class="sidebar-icon" aria-hidden="true">🗓️</span>
                            <span>{{ __('messages.schedules') }}</span>
                        </a>
                        <a href="{{ route('admin.dashboard') }}#rooms" class="sidebar-link">
                            <span class="sidebar-icon" aria-hidden="true">🚪</span>
                            <span>{{ __('messages.rooms') }}</span>
                        </a>
                        <a href="{{ route('admin.dashboard') }}#audit-logs" class="sidebar-link">
                            <span class="sidebar-icon" aria-hidden="true">📜</span>
                            <span>{{ __('messages.audit_logs') }}</span>
                        </a>
                    @elseif(Auth::user()->isTeacher())
                        <span class="sidebar-nav-header">ភារកិច្ចបង្រៀន (Teaching)</span>
                        <a href="{{ route('teacher.dashboard') }}#tab-weekly" class="sidebar-link">
                            <span class="sidebar-icon" aria-hidden="true">🗓️</span>
                            <span>{{ __('messages.weekly_schedule') }}</span>
                        </a>
                        <a href="{{ route('teacher.dashboard') }}#tab-classes" class="sidebar-link">
                            <span class="sidebar-icon" aria-hidden="true">🏫</span>
                            <span>{{ __('messages.assigned_classes') }}</span>
                        </a>
                        <a href="{{ route('teacher.dashboard') }}#tab-announcements" class="sidebar-link">
                            <span class="sidebar-icon" aria-hidden="true">📢</span>
                            <span>{{ __('messages.announcements') }}</span>
                        </a>
                        <a href="{{ route('teacher.dashboard') }}#tab-profile" class="sidebar-link">
                            <span class="sidebar-icon" aria-hidden="true">⚙️</span>
                            <span>{{ __('messages.profile') }}</span>
                        </a>
                    @elseif(Auth::user()->isStudent())
                        <span class="sidebar-nav-header">ការសិក្សា (Academic)</span>
                        <a href="{{ route('student.dashboard') }}#tab-weekly" class="sidebar-link">
                            <span class="sidebar-icon" aria-hidden="true">🗓️</span>
                            <span>{{ __('messages.weekly_schedule') }}</span>
                        </a>
                        <a href="{{ route('student.dashboard') }}#tab-announcements" class="sidebar-link">
                            <span class="sidebar-icon" aria-hidden="true">📢</span>
                            <span>{{ __('messages.announcements') }}</span>
                        </a>
                        <a href="{{ route('student.dashboard') }}#tab-classmates" class="sidebar-link">
                            <span class="sidebar-icon" aria-hidden="true">👥</span>
                            <span>{{ __('messages.tab_my_class') }}</span>
                        </a>
                        <a href="{{ route('student.dashboard') }}#tab-profile" class="sidebar-link">
                            <span class="sidebar-icon" aria-hidden="true">⚙️</span>
                            <span>{{ __('messages.profile') }}</span>
                        </a>
                    @endif

                    <span class="sidebar-nav-header">ព័ត៌មានទូទៅ (General)</span>
                    <a href="{{ route('home') }}" class="sidebar-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        <span class="sidebar-icon" aria-hidden="true">🌐</span>
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
                        <button type="submit" class="btn btn-sm btn-outline" style="color: #ffffff; border-color: rgba(255,255,255,0.2); padding: 0.25rem 0.5rem;" title="{{ __('messages.logout') }}">
                            🚪
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
                            ☰
                        </button>
                        <h1 class="topbar-title">@yield('page_title', __('messages.app_title'))</h1>
                    </div>

                    <div class="topbar-right">
                        <!-- Bilingual Language Capsule Switcher -->
                        <div class="language-capsule">
                            <a href="{{ request()->fullUrlWithQuery(['lang' => 'km']) }}" class="language-btn {{ app()->getLocale() === 'km' ? 'active' : '' }}">🇰🇭 ខ្មែរ</a>
                            <span style="color: #bdc1c6;">|</span>
                            <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="language-btn {{ app()->getLocale() === 'en' ? 'active' : '' }}">🇬🇧 EN</a>
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
                            <span>✅</span>
                            <div>{{ session('success') }}</div>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            <span>⚠️</span>
                            <div>{{ session('error') }}</div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <span>⚠️</span>
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
                    <p>&copy; 2026 <span>{{ __('messages.institute_name') }}</span> (STIC Siem Reap) · STIC Info Platform</p>
                </footer>
            </div>
        </div>
    @else
        <!-- ========================================== -->
        <!-- GUEST & PUBLIC HEADER                      -->
        <!-- ========================================== -->
        <header style="background: #ffffff; border-bottom: 1px solid var(--stic-border); position: sticky; top: 0; z-index: 100; box-shadow: var(--shadow-xs);">
            <div style="max-width: 1440px; margin: 0 auto; padding: 0.85rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <a href="{{ route('home') }}" style="display: flex; align-items: center; gap: 0.85rem; text-decoration: none;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; background: #ffffff; border: 2px solid var(--stic-secondary); padding: 2px; display: flex; align-items: center; justify-content: center;">
                        <img src="{{ asset('images/stic-logo.png') }}" alt="STIC Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%;">
                    </div>
                    <div>
                        <div style="font-size: 1.05rem; font-weight: 700; color: var(--stic-primary-dark);">{{ __('messages.institute_short') }}</div>
                        <div style="font-size: 0.75rem; color: var(--stic-gold-dark); font-weight: 600; text-transform: uppercase;">STIC Info Platform</div>
                    </div>
                </a>

                <div style="display: flex; align-items: center; gap: 1rem;">
                    <!-- Bilingual Language Capsule -->
                    <div class="language-capsule">
                        <a href="{{ request()->fullUrlWithQuery(['lang' => 'km']) }}" class="language-btn {{ app()->getLocale() === 'km' ? 'active' : '' }}">🇰🇭 ខ្មែរ</a>
                        <span style="color: #bdc1c6;">|</span>
                        <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="language-btn {{ app()->getLocale() === 'en' ? 'active' : '' }}">🇬🇧 EN</a>
                    </div>

                    <a href="{{ route('home') }}" class="btn btn-outline btn-sm">{{ __('messages.home') }}</a>
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">{{ __('messages.login') }} / {{ __('messages.register') }}</a>
                </div>
            </div>
        </header>

        <main style="flex: 1; padding: 2rem 1rem; width: 100%; max-width: 1200px; margin: 0 auto;" role="main">
            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    <span>✅</span>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger" role="alert">
                    <span>⚠️</span>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <span>⚠️</span>
                    <ul style="margin-left: 1.25rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="app-footer">
            <p>&copy; 2026 <span>{{ __('messages.institute_name') }}</span> (STIC Siem Reap) · STIC Info Platform</p>
        </footer>
    @endauth

    @yield('scripts')
</body>
</html>
