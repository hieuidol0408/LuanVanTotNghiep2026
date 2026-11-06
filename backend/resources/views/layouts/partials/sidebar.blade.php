<aside class="stu-sidebar" id="stuSidebar">
    <!-- Brand / Logo -->
    <a href="{{ url('/') }}" class="stu-sidebar-brand">
        <img src="{{ asset('images/stu-logo.png') }}" alt="Logo Đại học Công nghệ Sài Gòn" onerror="this.style.display='none'">
        <div class="stu-sidebar-brand-title">
            <span class="stu-sidebar-brand-name">HỘI ĐỒNG TRƯỜNG</span>
            <span class="stu-sidebar-brand-sub">ĐH CÔNG NGHỆ SÀI GÒN</span>
        </div>
    </a>

    <!-- Navigation Menu -->
    <div class="stu-sidebar-nav">
        <!-- Nhóm: Tổng quan -->
        <div class="stu-nav-group">
            <div class="stu-nav-group-label">Tổng quan</div>
            <a href="{{ url('/dashboard') }}" class="stu-nav-link {{ request()->is('dashboard*') || request()->is('/') ? 'active' : '' }}">
                <span class="stu-nav-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </span>
                <span class="stu-nav-text">Bảng điều hành</span>
            </a>
        </div>

        <!-- Nhóm: Công tác phiên họp -->
        <div class="stu-nav-group">
            <div class="stu-nav-group-label">Phiên họp & Hồ sơ</div>
            <a href="{{ url('/meetings') }}" class="stu-nav-link {{ request()->is('meetings*') ? 'active' : '' }}">
                <span class="stu-nav-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </span>
                <span class="stu-nav-text">Quản lý phiên họp</span>
            </a>
            <a href="{{ url('/submissions') }}" class="stu-nav-link {{ request()->is('submissions*') ? 'active' : '' }}">
                <span class="stu-nav-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
                <span class="stu-nav-text">Hồ sơ trình</span>
            </a>
            <a href="{{ url('/agendas') }}" class="stu-nav-link {{ request()->is('agendas*') ? 'active' : '' }}">
                <span class="stu-nav-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </span>
                <span class="stu-nav-text">Chương trình họp</span>
            </a>
        </div>

        <!-- Nhóm: Quyết nghị & Biểu quyết -->
        <div class="stu-nav-group">
            <div class="stu-nav-group-label">Quyết nghị & Biểu quyết</div>
            <a href="{{ url('/voting') }}" class="stu-nav-link {{ request()->is('voting*') ? 'active' : '' }}">
                <span class="stu-nav-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <span class="stu-nav-text">Biểu quyết & Bỏ phiếu</span>
            </a>
            <a href="{{ url('/resolutions') }}" class="stu-nav-link {{ request()->is('resolutions*') ? 'active' : '' }}">
                <span class="stu-nav-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                </span>
                <span class="stu-nav-text">Nghị quyết HĐT</span>
            </a>
        </div>

        <!-- Nhóm: Theo dõi thực hiện -->
        <div class="stu-nav-group">
            <div class="stu-nav-group-label">Thực hiện & Báo cáo</div>
            <a href="{{ url('/tasks') }}" class="stu-nav-link {{ request()->is('tasks*') ? 'active' : '' }}">
                <span class="stu-nav-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </span>
                <span class="stu-nav-text">Nhiệm vụ phân công</span>
            </a>
            <a href="{{ route('task-reports.index') }}" class="stu-nav-link {{ request()->is('task-reports*') || request()->is('reports*') ? 'active' : '' }}">
                <span class="stu-nav-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </span>
                <span class="stu-nav-text">Báo cáo tiến độ</span>
            </a>
        </div>

        <!-- Nhóm: Quản trị & Hệ thống -->
        <div class="stu-nav-group">
            <div class="stu-nav-group-label">Quản trị & Dữ liệu</div>
            <a href="{{ url('/documents') }}" class="stu-nav-link {{ request()->is('documents*') ? 'active' : '' }}">
                <span class="stu-nav-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                </span>
                <span class="stu-nav-text">Kho tài liệu số</span>
            </a>
            <a href="{{ url('/members') }}" class="stu-nav-link {{ request()->is('members*') ? 'active' : '' }}">
                <span class="stu-nav-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
                <span class="stu-nav-text">Thành viên Hội đồng</span>
            </a>
            <a href="{{ url('/users') }}" class="stu-nav-link {{ request()->is('users*') ? 'active' : '' }}">
                <span class="stu-nav-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </span>
                <span class="stu-nav-text">Quản lý người dùng</span>
            </a>
        </div>
    </div>

    <!-- Sidebar Footer -->
    <div class="stu-sidebar-footer">
        <div class="stu-nav-link" style="margin: 0; cursor: default; background: var(--color-bg-subtle);">
            <span class="stu-nav-icon" style="color: var(--color-success);">
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="6"/></svg>
            </span>
            <div class="stu-nav-text" style="line-height: 1.2;">
                <div style="font-size: var(--font-size-xs); font-weight: var(--font-weight-bold); color: var(--color-text-main);">Khóa IV (2020-2025)</div>
                <div style="font-size: 11px; color: var(--color-text-muted);">Trực tuyến • WampServer</div>
            </div>
        </div>
    </div>
</aside>
