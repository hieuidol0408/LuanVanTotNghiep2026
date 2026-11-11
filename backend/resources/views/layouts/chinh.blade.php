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

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- STU Enterprise Design System CSS -->
    <link rel="stylesheet" href="{{ asset('css/stu-enterprise.css') }}">

    @stack('styles')
</head>
<body>
    <div class="stu-app" id="stuApp">
        <!-- Sidebar Navigation -->
        @include('layouts.thanhphan.thanhben')

        <!-- Main Wrapper -->
        <div class="stu-main-wrapper">
            <!-- Header -->
            @include('layouts.thanhphan.dautrang')

            <!-- Main Content Container -->
            <main class="stu-content">
                <!-- Flash Messages -->
                @if(session('success'))
                    <x-canh-bao type="success" title="Thành công" :message="session('success')" dismissible />
                @endif
                @if(session('warning'))
                    <x-canh-bao type="warning" title="Cảnh báo" :message="session('warning')" dismissible />
                @endif
                @if(session('error'))
                    <x-canh-bao type="danger" title="Lỗi thao tác" :message="session('error')" dismissible />
                @endif
                @if(session('info'))
                    <x-canh-bao type="info" title="Thông tin" :message="session('info')" dismissible />
                @endif

                <!-- Page Content Slot -->
                @yield('content')
                {{ $slot ?? '' }}
            </main>

            <!-- Footer -->
            @include('layouts.thanhphan.chantrang')
        </div>
    </div>

    <!-- STU Enterprise Dashboard JavaScript -->
    <script src="{{ asset('js/stu-enterprise.js') }}"></script>
    @stack('scripts')
</body>
</html>
