<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Đăng nhập - Hội đồng trường STU')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/stu-enterprise.css') }}">
    <style>
        .stu-auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at 50% 10%, var(--stu-blue-50) 0%, var(--color-bg-app) 70%);
            padding: var(--space-4);
            position: relative;
        }
        [data-theme="dark"] .stu-auth-container {
            background: radial-gradient(circle at 50% 10%, rgba(0, 58, 140, 0.2) 0%, var(--color-bg-app) 70%);
        }
        .stu-auth-card {
            width: 100%;
            max-width: 440px;
            background-color: var(--color-bg-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-xl);
            padding: var(--space-8);
        }
        .stu-auth-header {
            text-align: center;
            margin-bottom: var(--space-6);
        }
        .stu-auth-header img {
            height: 64px;
            margin-bottom: var(--space-3);
        }
        .stu-auth-title {
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-bold);
            color: var(--color-primary);
        }
        .stu-auth-subtitle {
            font-size: var(--font-size-xs);
            color: var(--color-text-muted);
            margin-top: 4px;
        }
        .stu-auth-theme-btn {
            position: absolute;
            top: var(--space-4);
            right: var(--space-4);
        }
    </style>
</head>
<body>
    <div class="stu-auth-container">
        <!-- Nút đổi theme ở góc phải -->
        <div class="stu-auth-theme-btn">
            <button type="button" class="stu-icon-btn" data-stu-theme-toggle title="Chuyển chế độ sáng/tối">
                <span class="stu-icon-moon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </span>
                <span class="stu-icon-sun" style="display: none;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </span>
            </button>
        </div>

        <div class="stu-auth-card">
            <div class="stu-auth-header">
                <img src="{{ asset('images/stu-logo.png') }}" alt="STU Logo" onerror="this.style.display='none'">
                <h1 class="stu-auth-title">HỘI ĐỒNG TRƯỜNG STU</h1>
                <p class="stu-auth-subtitle">HỆ THỐNG QUẢN LÝ HOẠT ĐỘNG & NGHỊ QUYẾT</p>
            </div>

            @yield('content')
            {{ $slot ?? '' }}
        </div>
    </div>

    <script src="{{ asset('js/stu-enterprise.js') }}"></script>
</body>
</html>
