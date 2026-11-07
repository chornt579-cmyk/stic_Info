<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('messages.app_title')) — STIC Info</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Khmer:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --primary: #1a73e8;
            --primary-dark: #1557b0;
            --secondary: #34a853;
            --danger: #ea4335;
            --warning: #fbbc04;
            --dark: #202124;
            --light: #f8f9fa;
            --border: #dadce0;
            --card-bg: #ffffff;
            --text-main: #3c4043;
            --text-muted: #5f6368;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Noto Sans Khmer', 'Roboto', sans-serif;
            background-color: #f1f3f4;
            color: var(--text-main);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar */
        .navbar {
            background: #ffffff;
            border-bottom: 1px solid var(--border);
            padding: 0.8rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--dark);
            font-weight: 700;
            font-size: 1.25rem;
        }

        .navbar-brand span {
            color: var(--primary);
        }

        .navbar-nav {
            display: flex;
            gap: 1rem;
            align-items: center;
            list-style: none;
        }

        .nav-link {
            text-decoration: none;
            color: var(--text-main);
            font-weight: 500;
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .nav-link:hover {
            background-color: var(--light);
            color: var(--primary);
        }

        .btn {
            display: inline-block;
            padding: 0.5rem 1.2rem;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            border: none;
            font-family: inherit;
            font-size: 0.95rem;
            transition: background 0.2s;
        }

        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-secondary { background: #e8f0fe; color: var(--primary); }
        .btn-secondary:hover { background: #d2e3fc; }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-success { background: var(--secondary); color: #fff; }
        .btn-sm { padding: 0.25rem 0.6rem; font-size: 0.85rem; }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
            flex: 1;
            width: 100%;
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }
        .alert-success { background: #e6f4ea; color: #137333; border: 1px solid #ceead6; }
        .alert-danger { background: #fce8e6; color: #c5221f; border: 1px solid #fad2cf; }

        /* Cards */
        .card {
            background: var(--card-bg);
            border-radius: 10px;
            border: 1px solid var(--border);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #f1f3f4;
        }

        .card-title {
            font-size: 1.15rem;
            font-weight: 600;
            color: var(--dark);
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.6rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .badge-student { background: #e8f0fe; color: var(--primary); }
        .badge-teacher { background: #e6f4ea; color: var(--secondary); }
        .badge-admin { background: #fce8e6; color: var(--danger); }
        .badge-pending { background: #fef7e0; color: #b06000; }

        /* Tables */
        .table-responsive {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        th, td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f1f3f4;
        }
        th {
            background: #f8f9fa;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* Forms */
        .form-group {
            margin-bottom: 1rem;
        }
        .form-label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .form-control {
            width: 100%;
            padding: 0.6rem 0.8rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-family: inherit;
            font-size: 0.95rem;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(26,115,232,0.2);
        }

        /* Footer */
        footer {
            background: #ffffff;
            border-top: 1px solid var(--border);
            text-align: center;
            padding: 1.5rem;
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: auto;
        }
    </style>
    @yield('styles')
</head>
<body>
    <nav class="navbar" role="navigation" aria-label="Main Navigation">
        <a href="{{ route('home') }}" class="navbar-brand">
            🏫 STIC <span>Info</span>
        </a>
        <ul class="navbar-nav">
            <li><a href="{{ route('home') }}" class="nav-link">{{ __('messages.home') }}</a></li>
            
            <!-- Language Switcher -->
            <li style="display: flex; gap: 4px; align-items: center; background: #e8f0fe; padding: 3px 8px; border-radius: 20px; font-size: 0.82rem;">
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'km']) }}" style="text-decoration: none; font-weight: {{ app()->getLocale() === 'km' ? '700' : '400' }}; color: {{ app()->getLocale() === 'km' ? '#1a73e8' : '#5f6368' }};">🇰🇭 ខ្មែរ</a>
                <span style="color: #bdc1c6;">|</span>
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" style="text-decoration: none; font-weight: {{ app()->getLocale() === 'en' ? '700' : '400' }}; color: {{ app()->getLocale() === 'en' ? '#1a73e8' : '#5f6368' }};">🇬🇧 EN</a>
            </li>

            @auth
                <li><a href="{{ route('dashboard') }}" class="nav-link">{{ __('messages.dashboard') }}</a></li>
                <li>
                    <span class="badge badge-{{ Auth::user()->role }}">
                        {{ strtoupper(Auth::user()->role) }}
                    </span>
                </li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm">{{ __('messages.logout') }}</button>
                    </form>
                </li>
            @else
                <li><a href="{{ route('login') }}" class="btn btn-primary btn-sm">{{ __('messages.login') }} / {{ __('messages.register') }}</a></li>
            @endauth
        </ul>
    </nav>

    <main class="container" role="main">
        @if (session('success'))
            <div class="alert alert-success">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul style="margin-left: 1.5rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer>
        <p>&copy; 2026 វិទ្យាស្ថានពហុបច្ចេកទេសភូមិភាគតេជោសែន សៀមរាប (STIC Siem Reap) · STIC Info Laravel</p>
    </footer>

    @yield('scripts')
</body>
</html>

