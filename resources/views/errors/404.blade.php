<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — រកមិនឃើញទំព័រ | STIC Info</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Khmer:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans Khmer', 'Roboto', sans-serif;
            background: #f8fafc;
            color: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 1rem;
            text-align: center;
        }
        .error-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            max-width: 480px;
            width: 100%;
            padding: 2.5rem 2rem;
        }
        .code {
            font-size: 4rem;
            font-weight: 700;
            color: #fbbc04;
            line-height: 1;
            margin-bottom: 1rem;
        }
        .title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        .desc {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1.75rem;
        }
        .btn {
            display: inline-block;
            background: #1a73e8;
            color: #ffffff;
            padding: 0.65rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #1557b0;
        }
    </style>
</head>
<body>
    <div class="error-card" role="alert">
        <div class="code">404</div>
        <h1 class="title">រកមិនឃើញទំព័រដែលអ្នកស្នើសុំឡើយ (Page Not Found)</h1>
        <p class="desc">ទំព័រដែលអ្នកកំពុងស្វែងរកអាចត្រូវបានផ្លាស់ប្តូរទីតាំង ឬលុបចេញពីប្រព័ន្ធ។</p>
        <a href="{{ route('home') }}" class="btn">{{ __('messages.home') }}</a>
    </div>
</body>
</html>

