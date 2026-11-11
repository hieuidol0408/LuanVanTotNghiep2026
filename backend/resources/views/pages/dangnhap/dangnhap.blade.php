@extends('layouts.xacthuc')

@section('title', 'Đăng nhập - Hệ thống Hội đồng trường STU')

@section('content')
    <!-- Thông báo lỗi đăng nhập nếu có -->
    @if ($errors->any())
        <x-canh-bao type="danger" :title="$errors->first('email')" :dismissible="false" />
    @endif

    @if (session('info'))
        <x-canh-bao type="info" :message="session('info')" :dismissible="false" />
    @endif

    <form method="POST" action="{{ route('login.submit') }}" id="loginForm" novalidate>
        @csrf

        <!-- Ô nhập Email -->
        <div class="stu-form-group">
            <x-bieu-mau.nhan for="loginEmail" required>
                Địa chỉ Email
            </x-bieu-mau.nhan>
            <input 
                type="text" 
                name="email" 
                id="loginEmail" 
                class="stu-input @error('email') is-invalid @enderror" 
                placeholder="nhanvien@stu.edu.vn" 
                value="{{ old('email') }}" 
                required 
                autocomplete="email"
                autofocus
            >
            <div id="emailClientError" class="stu-form-error" style="display: none;"></div>
            <x-bieu-mau.loi field="email" />
        </div>

        <!-- Ô nhập Mật khẩu kèm nút Ẩn/Hiện -->
        <div class="stu-form-group">
            <x-bieu-mau.nhan for="loginPassword" required>
                Mật khẩu
            </x-bieu-mau.nhan>
            <div style="position: relative; display: flex; align-items: center;">
                <input 
                    type="password" 
                    name="password" 
                    id="loginPassword" 
                    class="stu-input @error('password') is-invalid @enderror" 
                    placeholder="••••••••" 
                    required 
                    autocomplete="current-password"
                    style="padding-right: 42px;"
                >
                <button 
                    type="button" 
                    id="togglePasswordBtn" 
                    aria-label="Ẩn hiện mật khẩu"
                    style="position: absolute; right: 10px; background: none; border: none; cursor: pointer; color: var(--color-text-muted); display: flex; align-items: center; justify-content: center; padding: 4px;"
                >
                    <svg id="eyeIcon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg id="eyeOffIcon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                    </svg>
                </button>
            </div>
            <div id="passwordClientError" class="stu-form-error" style="display: none;"></div>
            <x-bieu-mau.loi field="password" />
        </div>

        <!-- Ghi nhớ đăng nhập -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-5);">
            <label style="display: flex; align-items: center; gap: var(--space-2); cursor: pointer; font-size: var(--font-size-xs); color: var(--color-text-secondary); user-select: none;">
                <input type="checkbox" name="remember" id="remember" style="accent-color: var(--color-primary);" {{ old('remember') ? 'checked' : '' }}>
                <span>Ghi nhớ đăng nhập</span>
            </label>
            <a href="#" style="font-size: var(--font-size-xs); color: var(--color-primary); font-weight: 500;" onclick="alert('Vui lòng liên hệ Thư ký Hội đồng hoặc Quản trị viên để đặt lại mật khẩu.')">
                Quên mật khẩu?
            </a>
        </div>

        <!-- Nút Đăng nhập -->
        <x-nut-bam type="submit" variant="primary" style="width: 100%; justify-content: center; padding: 12px; font-weight: 600;" id="submitBtn">
            <span id="btnText">Đăng nhập vào Hệ thống</span>
            <span id="btnSpinner" style="display: none; align-items: center; gap: 6px;">
                <svg class="animate-spin" width="16" height="16" fill="none" viewBox="0 0 24 24" style="animation: spin 1s linear infinite;">
                    <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Đang xác thực...
            </span>
        </x-nut-bam>
    </form>

    <!-- Hỗ trợ chọn nhanh tài khoản kiểm thử cho 7 vai trò -->
    <div style="margin-top: var(--space-6); padding-top: var(--space-4); border-top: 1px dashed var(--color-border);">
        <div style="font-size: var(--font-size-xs); font-weight: 600; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: var(--space-2); text-align: center;">
            Chọn nhanh tài khoản kiểm thử (7 vai trò)
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 6px; justify-content: center;">
            <button type="button" class="stu-badge stu-badge-info" style="cursor: pointer;" onclick="fillLogin('chutich_hdt@stu.edu.vn', 'stu@2026')">
                Chủ tịch
            </button>
            <button type="button" class="stu-badge stu-badge-info" style="cursor: pointer;" onclick="fillLogin('thuky_hdt@stu.edu.vn', 'stu@2026')">
                Thư ký
            </button>
            <button type="button" class="stu-badge stu-badge-info" style="cursor: pointer;" onclick="fillLogin('thanhvien_hdt@stu.edu.vn', 'stu@2026')">
                Thành viên
            </button>
            <button type="button" class="stu-badge stu-badge-info" style="cursor: pointer;" onclick="fillLogin('vphieutruong@stu.edu.vn', 'stu@2026')">
                VP Hiệu trưởng
            </button>
            <button type="button" class="stu-badge stu-badge-info" style="cursor: pointer;" onclick="fillLogin('daotao@stu.edu.vn', 'stu@2026')">
                Đơn vị trình
            </button>
            <button type="button" class="stu-badge stu-badge-info" style="cursor: pointer;" onclick="fillLogin('cntt@stu.edu.vn', 'stu@2026')">
                Đơn vị thi hành
            </button>
            <button type="button" class="stu-badge stu-badge-info" style="cursor: pointer;" onclick="fillLogin('admin@stu.edu.vn', 'stu@2026')">
                Quản trị viên
            </button>
        </div>
    </div>

    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>

    <script>
        const emailInput = document.getElementById('loginEmail');
        const passInput = document.getElementById('loginPassword');
        const emailError = document.getElementById('emailClientError');
        const passError = document.getElementById('passwordClientError');

        function clearLoginErrors() {
            if (emailError) { emailError.style.display = 'none'; emailError.textContent = ''; }
            if (passError) { passError.style.display = 'none'; passError.textContent = ''; }
            if (emailInput) emailInput.classList.remove('is-invalid');
            if (passInput) passInput.classList.remove('is-invalid');
        }

        function fillLogin(email, pass) {
            clearLoginErrors();
            emailInput.value = email;
            passInput.value = pass;
        }

        function validateEmailClient(val) {
            const v = (val || '').trim();
            if (!v) {
                return 'Vui lòng nhập địa chỉ email đăng nhập.';
            }
            if (!v.includes('@')) {
                return 'Địa chỉ email bắt buộc phải chứa ký tự "@".';
            }
            const parts = v.split('@');
            if (parts.length !== 2 || !parts[0]) {
                return 'Địa chỉ email không hợp lệ (thiếu phần tên trước "@").';
            }
            const domain = parts[1];
            if (!domain || !domain.includes('.') || domain.startsWith('.') || domain.endsWith('.')) {
                return 'Phần tên miền phía sau "@" bắt buộc phải có dấu chấm "." hợp lệ (ví dụ: @stu.edu.vn).';
            }
            const emailRegex = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
            if (!emailRegex.test(v)) {
                return 'Địa chỉ email không đúng định dạng chuẩn.';
            }
            return '';
        }

        function validatePasswordClient(val) {
            const v = val || '';
            if (!v.trim()) {
                return 'Vui lòng nhập mật khẩu đăng nhập.';
            }
            if (v.length < 6) {
                return 'Mật khẩu phải có độ dài tối thiểu 6 ký tự.';
            }
            return '';
        }

        if (emailInput) {
            emailInput.addEventListener('input', function () {
                const err = validateEmailClient(this.value);
                if (!err) {
                    emailError.style.display = 'none';
                    this.classList.remove('is-invalid');
                }
            });
        }

        if (passInput) {
            passInput.addEventListener('input', function () {
                const err = validatePasswordClient(this.value);
                if (!err) {
                    passError.style.display = 'none';
                    this.classList.remove('is-invalid');
                }
            });
        }

        // Toggle show/hide password
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeOffIcon = document.getElementById('eyeOffIcon');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                const isPassword = passInput.getAttribute('type') === 'password';
                passInput.setAttribute('type', isPassword ? 'text' : 'password');
                eyeIcon.style.display = isPassword ? 'none' : 'block';
                eyeOffIcon.style.display = isPassword ? 'block' : 'none';
            });
        }

        // Client-side validation & Loading state on submit
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');

        if (form) {
            form.addEventListener('submit', function (e) {
                clearLoginErrors();
                const eMsg = validateEmailClient(emailInput.value);
                const pMsg = validatePasswordClient(passInput.value);

                let hasErr = false;
                if (eMsg) {
                    emailError.textContent = eMsg;
                    emailError.style.display = 'block';
                    emailInput.classList.add('is-invalid');
                    hasErr = true;
                }
                if (pMsg) {
                    passError.textContent = pMsg;
                    passError.style.display = 'block';
                    passInput.classList.add('is-invalid');
                    hasErr = true;
                }

                if (hasErr) {
                    e.preventDefault();
                    return false;
                }

                submitBtn.setAttribute('disabled', 'disabled');
                btnText.style.display = 'none';
                btnSpinner.style.display = 'inline-flex';
            });
        }
    </script>
@endsection
