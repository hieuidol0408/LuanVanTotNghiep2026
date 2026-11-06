<header class="stu-header">
    <div class="stu-header-left">
        <!-- Nút thu gọn / mở rộng Sidebar -->
        <button type="button" class="stu-sidebar-toggle" data-stu-sidebar-toggle aria-label="Đóng mở thanh điều hướng">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <!-- Tiêu đề phân hệ / Breadcrumb slot -->
        <div class="stu-header-title-box">
            @yield('header_title')
        </div>
    </div>

    <div class="stu-header-right">
        <!-- Nút chuyển đổi Light / Dark Mode -->
        <button type="button" class="stu-icon-btn" data-stu-theme-toggle title="Chuyển chế độ sáng/tối">
            <span class="stu-icon-moon">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </span>
            <span class="stu-icon-sun" style="display: none;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </span>
        </button>

        <!-- Thông báo hệ thống -->
        <div class="stu-dropdown">
            <button type="button" class="stu-icon-btn" data-stu-dropdown-toggle title="Thông báo hệ thống">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span style="position: absolute; top: 6px; right: 6px; width: 8px; height: 8px; background: var(--color-danger); border-radius: 50%;"></span>
            </button>
            <div class="stu-dropdown-menu" style="width: 300px;">
                <div style="padding: 10px 14px; font-weight: 600; border-bottom: 1px solid var(--color-border); font-size: var(--font-size-sm);">
                    Thông báo mới (2)
                </div>
                <a href="{{ url('/meetings') }}" class="stu-dropdown-item" style="padding: 10px 14px; align-items: flex-start;">
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--color-primary); margin-top: 5px; flex-shrink: 0;"></div>
                    <div style="font-size: 13px;">
                        <div style="font-weight: 500;">Phiên họp thường kỳ Lần thứ 5</div>
                        <div style="font-size: 11px; color: var(--color-text-muted);">Sắp diễn ra ngày 20/11/2026</div>
                    </div>
                </a>
                <a href="{{ url('/tasks') }}" class="stu-dropdown-item" style="padding: 10px 14px; align-items: flex-start;">
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--color-warning); margin-top: 5px; flex-shrink: 0;"></div>
                    <div style="font-size: 13px;">
                        <div style="font-weight: 500;">Báo cáo tiến độ cần duyệt</div>
                        <div style="font-size: 11px; color: var(--color-text-muted);">Khoa CNTT vừa nộp báo cáo</div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Thông tin người dùng & Menu tài khoản -->
        <div class="stu-dropdown">
            <div class="stu-user-badge" data-stu-dropdown-toggle>
                <div class="stu-user-avatar">
                    {{ strtoupper(substr(session('user_name', 'HĐT'), 0, 1)) }}
                </div>
                <div class="stu-user-info">
                    <span class="stu-user-name">{{ session('user_name', 'Thành viên HĐT') }}</span>
                    <span class="stu-user-role">{{ session('user_role', 'CHAIR') }}</span>
                </div>
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--color-text-muted);">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            <div class="stu-dropdown-menu">
                <a href="{{ url('/profile') }}" class="stu-dropdown-item">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Hồ sơ cá nhân</span>
                </a>
                <a href="{{ url('/settings') }}" class="stu-dropdown-item">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Cài đặt hệ thống</span>
                </a>
                <div class="stu-dropdown-divider"></div>
                <form action="{{ url('/logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="stu-dropdown-item" style="color: var(--color-danger);">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Đăng xuất</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
