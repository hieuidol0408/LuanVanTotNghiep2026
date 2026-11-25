<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hệ thống Quản lý Hoạt động Hội đồng trường STU')</title>

    <!-- Google Fonts Inter (Tải bất đồng bộ không chặn giao diện) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"></noscript>

    <!-- Bootstrap Icons (Tải trực tiếp nội bộ Local, không phụ thuộc mạng CDN) -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">

    <!-- STU Enterprise Design System CSS -->
    <link rel="stylesheet" href="{{ asset('css/stu-enterprise.css') }}?v={{ file_exists(public_path('css/stu-enterprise.css')) ? filemtime(public_path('css/stu-enterprise.css')) : '1' }}">

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
                @elseif($errors->any())
                    <x-canh-bao type="danger" title="Lỗi ràng buộc dữ liệu" :message="$errors->first()" dismissible />
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
    <script src="{{ asset('js/stu-enterprise.js') }}?v={{ file_exists(public_path('js/stu-enterprise.js')) ? filemtime(public_path('js/stu-enterprise.js')) : '1' }}"></script>
    <x-trinh-xem-tai-lieu />
    @stack('scripts')
</body>
</html>
