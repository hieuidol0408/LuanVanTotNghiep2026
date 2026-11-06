<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hệ thống Quản lý Hoạt động Hội đồng trường STU')</title>

    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- STU Enterprise Design System CSS -->
    <link rel="stylesheet" href="{{ asset('css/stu-enterprise.css') }}">

    @stack('styles')
</head>
<body>
    <div class="stu-app" id="stuApp">
        <!-- Sidebar Navigation -->
        @include('layouts.partials.sidebar')

        <!-- Main Wrapper -->
        <div class="stu-main-wrapper">
            <!-- Header -->
            @include('layouts.partials.header')

            <!-- Main Content Container -->
            <main class="stu-content">
                <!-- Flash Messages -->
                @if(session('success'))
                    <x-alert type="success" title="Thành công" :message="session('success')" dismissible />
                @endif
                @if(session('warning'))
                    <x-alert type="warning" title="Cảnh báo" :message="session('warning')" dismissible />
                @endif
                @if(session('error'))
                    <x-alert type="danger" title="Lỗi thao tác" :message="session('error')" dismissible />
                @endif
                @if(session('info'))
                    <x-alert type="info" title="Thông tin" :message="session('info')" dismissible />
                @endif

                <!-- Page Content Slot -->
                @yield('content')
                {{ $slot ?? '' }}
            </main>

            <!-- Footer -->
            @include('layouts.partials.footer')
        </div>
    </div>

    <!-- STU Enterprise Dashboard JavaScript -->
    <script src="{{ asset('js/stu-enterprise.js') }}"></script>
    @stack('scripts')
</body>
</html>
