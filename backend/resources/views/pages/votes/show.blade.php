@extends('layouts.app')

@section('title', 'Nội dung biểu quyết: #BQ-' . str_pad($vote->MaNoiDung, 3, '0', STR_PAD_LEFT))

@section('content')
<div class="stu-page-container">
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="['Biểu quyết & Bỏ phiếu' => route('votes.index'), '#BQ-' . str_pad($vote->MaNoiDung, 3, '0', STR_PAD_LEFT) => null]" />

    @php
        $isOpening = in_array($vote->TrangThai, ['OPENING', 'Đang mở biểu quyết', 'Đang mở']);
        $isClosed = in_array($vote->TrangThai, ['CLOSED', 'Đã đóng']);
    @endphp

    {{-- Header --}}
    <div class="stu-page-header stu-flex stu-items-start stu-justify-between stu-mb-6" style="gap: 16px;">
        <div style="flex: 1;">
            <div class="stu-flex stu-items-center stu-gap-2 stu-mb-2">
                <span style="font-weight: 800; color: var(--color-primary); font-size: 14px; background: rgba(0, 43, 91, 0.08); padding: 3px 8px; border-radius: var(--radius-sm);">
                    #BQ-{{ str_pad($vote->MaNoiDung, 3, '0', STR_PAD_LEFT) }}
                </span>
                @if($votingType === 'SECRET')
                    <span class="stu-badge stu-badge-light" style="color: #0369a1; border-color: #bae6fd; background: #f0f9ff;">
                        🔒 Bỏ phiếu kín (BR-09)
                    </span>
                @else
                    <span class="stu-badge stu-badge-light">Biểu quyết công khai</span>
                @endif

                @if($isOpening)
                    <span class="stu-badge stu-badge-success" style="font-weight: 700;">● Đang mở nhận phiếu</span>
                @elseif($isClosed)
                    <span class="stu-badge stu-badge-neutral">Đã đóng kiểm phiếu</span>
                @else
                    <span class="stu-badge stu-badge-light">Chưa mở cổng</span>
                @endif

                @if($isClosed)
                    @if($voteStats['is_passed'])
                        <span class="stu-badge stu-badge-success" style="font-weight: 700;">✓ Đã thông qua</span>
                    @else
                        <span class="stu-badge stu-badge-danger" style="font-weight: 700;">✗ Không thông qua</span>
                    @endif
                @endif
            </div>

            <h1 class="stu-page-title" style="font-size: 22px; font-weight: 800; color: var(--color-primary); margin: 0; line-height: 1.4;">
                {{ $vote->NoiDung }}
            </h1>

            <div style="font-size: 13px; color: var(--color-text-muted); margin-top: 6px; display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                <span><strong>Phiên họp:</strong> {{ $meeting?->TenPhienHop ?? 'Phiên họp #' . ($vote->chuongTrinhHop?->MaPhienHop ?? '—') }}</span>
                @if($vote->chuongTrinhHop)
                    <span><strong>Mục chương trình:</strong> Mục số {{ $vote->chuongTrinhHop->ThuTu }}</span>
                @endif
                <span><strong>Ngưỡng thông qua:</strong> {{ $passThreshold }}% tổng số thành viên</span>
            </div>
        </div>

        {{-- Các nút điều hành biểu quyết (Dành cho Chủ tịch, Thư ký, Quản trị viên) --}}
        <div class="stu-flex stu-items-center stu-gap-2">
            @if(in_array($role, ['CHAIR', 'SECRETARY', 'ADMIN']))
                @if(! $isOpening && ! $isClosed)
                    <form method="POST" action="{{ route('votes.open', $vote->MaNoiDung) }}">
                        @csrf
                        <button type="submit" class="stu-btn stu-btn-success" style="height: 38px;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Mở cổng biểu quyết</span>
                        </button>
                    </form>
                @elseif($isOpening)
                    <form method="POST" action="{{ route('votes.close', $vote->MaNoiDung) }}" onsubmit="return confirm('Bạn có chắc chắn muốn đóng biểu quyết và chốt kết quả kiểm phiếu?');">
                        @csrf
                        <button type="submit" class="stu-btn stu-btn-danger" style="height: 38px;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>
                            <span>Đóng & Chốt kiểm phiếu</span>
                        </button>
                    </form>
                @endif

                <button type="button" class="stu-btn stu-btn-outline" style="height: 38px;" onclick="document.getElementById('delegationModal').classList.add('stu-modal-open');">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    <span>Ủy quyền biểu quyết (BR-04)</span>
                </button>
            @endif

            <a href="{{ route('votes.index') }}" class="stu-btn stu-btn-ghost" style="height: 38px;">
                Quay lại
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <x-alert type="success" :dismissible="true" class="stu-mb-6">
            {{ session('success') }}
        </x-alert>
    @endif
    @if(session('warning'))
        <x-alert type="warning" :dismissible="true" class="stu-mb-6">
            {{ session('warning') }}
        </x-alert>
    @endif
    @if(session('error'))
        <x-alert type="danger" :dismissible="true" class="stu-mb-6">
            {{ session('error') }}
        </x-alert>
    @endif

    {{-- Bố cục 2 Cột --}}
    <div class="stu-grid" style="grid-template-columns: 1.6fr 1.4fr; gap: 24px; align-items: flex-start;">
        {{-- Cột trái: Buồng bỏ phiếu điện tử --}}
        <div class="stu-flex stu-flex-col stu-gap-6">
            {{-- 1. Buồng bỏ phiếu trực tiếp của thành viên --}}
            <div class="stu-card" style="padding: 26px; border-top: 4px solid var(--color-primary);">
                <div class="stu-flex stu-items-center stu-justify-between stu-mb-4">
                    <h3 style="font-size: 16px; font-weight: 800; color: var(--color-primary); margin: 0; display: flex; align-items: center; gap: 8px;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Phiếu biểu quyết cá nhân (Trực tiếp)</span>
                    </h3>
                    @if($hasVotedDirect)
                        <span class="stu-badge stu-badge-success">✓ Đã bỏ phiếu thành công</span>
                    @endif
                </div>

                @if(! $isOpening && ! $isClosed)
                    <div style="padding: 32px; text-align: center; background: var(--color-bg-muted); border-radius: var(--radius-md); color: var(--color-text-muted);">
                        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin: 0 auto 10px; color: var(--color-border);"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div style="font-weight: 600; font-size: 15px;">Cổng biểu quyết chưa mở</div>
                        <div style="font-size: 13px; margin-top: 4px;">Vui lòng chờ Chủ tịch hoặc Thư ký phiên họp phát lệnh mở cổng bỏ phiếu điện tử.</div>
                    </div>
                @elseif($isClosed)
                    <div style="padding: 24px; background: #f8fafc; border-radius: var(--radius-md); border: 1px solid var(--color-border); text-align: center;">
                        <div style="font-size: 15px; font-weight: 700; color: var(--color-text-primary);">
                            Phiên biểu quyết đã chính thức kết thúc
                        </div>
                        <div style="font-size: 13px; color: var(--color-text-muted); margin-top: 4px;">
                            Cổng nhận phiếu đã đóng. Kết quả kiểm phiếu chi tiết hiển thị ở bảng bên phải.
                        </div>
                    </div>
                @else
                    {{-- Đang mở nhận phiếu --}}
                    @if(! $canVoteDirect)
                        <div style="padding: 24px; background: #fffbeb; border-radius: var(--radius-md); border: 1px solid #fde68a; color: #92400e; font-size: 13px; line-height: 1.6;">
                            <strong>Thông báo quyền biểu quyết:</strong>
                            <p style="margin: 6px 0 0 0;">
                                Tài khoản hiện tại không đủ điều kiện bỏ phiếu. Theo quy định, chỉ các <strong>Thành viên chính thức của Hội đồng trường đã được điểm danh có mặt</strong> tại phiên họp mới có quyền biểu quyết.
                            </p>
                        </div>
                    @elseif($hasVotedDirect)
                        <div style="padding: 20px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--radius-md); color: #166534; font-size: 13px;">
                            <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 14px;">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Hệ thống đã ghi nhận lá phiếu của bạn</span>
                            </div>
                            <p style="margin: 8px 0 0 0; line-height: 1.6;">
                                Tuân thủ nghiêm ngặt <strong>Ràng buộc BR-09 (Bảo mật bỏ phiếu kín)</strong>: Lựa chọn của bạn đã được niêm phong và bảo mật tuyệt đối, mỗi thành viên chỉ được bỏ phiếu một lần duy nhất.
                            </p>
                        </div>
                    @else
                        {{-- Form bỏ phiếu trực tiếp --}}
                        <form method="POST" action="{{ route('votes.vote', $vote->MaNoiDung) }}">
                            @csrf
                            <p style="font-size: 13px; color: var(--color-text-secondary); margin: 0 0 16px 0;">
                                Vui lòng cân nhắc kỹ lưỡng và chọn một trong 3 phương án dưới đây:
                            </p>

                            <div class="stu-flex stu-flex-col stu-gap-3 stu-mb-6">
                                <label style="display: flex; align-items: center; gap: 12px; padding: 14px 18px; border: 2px solid var(--color-border); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;" class="hover:border-primary">
                                    <input type="radio" name="choice" value="Tán thành" required style="width: 18px; height: 18px; accent-color: var(--color-success);">
                                    <div style="flex: 1;">
                                        <div style="font-size: 15px; font-weight: 700; color: var(--color-success); display: flex; align-items: center; gap: 6px;">
                                            <span>✓ TÁN THÀNH (ĐỒNG Ý)</span>
                                        </div>
                                        <div style="font-size: 12px; color: var(--color-text-muted); margin-top: 2px;">
                                            Ủng hộ thông qua nội dung nghị quyết này.
                                        </div>
                                    </div>
                                </label>

                                <label style="display: flex; align-items: center; gap: 12px; padding: 14px 18px; border: 2px solid var(--color-border); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;" class="hover:border-primary">
                                    <input type="radio" name="choice" value="Không tán thành" style="width: 18px; height: 18px; accent-color: var(--color-danger);">
                                    <div style="flex: 1;">
                                        <div style="font-size: 15px; font-weight: 700; color: var(--color-danger); display: flex; align-items: center; gap: 6px;">
                                            <span>✗ KHÔNG TÁN THÀNH</span>
                                        </div>
                                        <div style="font-size: 12px; color: var(--color-text-muted); margin-top: 2px;">
                                            Không đồng ý thông qua nội dung đề xuất.
                                        </div>
                                    </div>
                                </label>

                                <label style="display: flex; align-items: center; gap: 12px; padding: 14px 18px; border: 2px solid var(--color-border); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;" class="hover:border-primary">
                                    <input type="radio" name="choice" value="Không có ý kiến" style="width: 18px; height: 18px; accent-color: var(--color-neutral);">
                                    <div style="flex: 1;">
                                        <div style="font-size: 15px; font-weight: 700; color: var(--color-text-muted); display: flex; align-items: center; gap: 6px;">
                                            <span>— KHÔNG CÓ Ý KIẾN (PHIẾU TRẮNG)</span>
                                        </div>
                                        <div style="font-size: 12px; color: var(--color-text-muted); margin-top: 2px;">
                                            Bỏ quyền biểu quyết đối với nội dung này.
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <button type="submit" class="stu-btn stu-btn-primary" style="width: 100%; justify-content: center; height: 44px; font-size: 15px; font-weight: 700;">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Xác nhận gửi phiếu biểu quyết</span>
                            </button>
                        </form>
                    @endif
                @endif
            </div>

            {{-- 2. Buồng bỏ phiếu theo ủy quyền hợp lệ (BR-04, BR-11) --}}
            @if(count($validDelegations) > 0)
                <div class="stu-card" style="padding: 24px; border-left: 4px solid var(--color-accent);">
                    <h3 style="font-size: 15px; font-weight: 800; color: var(--color-primary); margin: 0 0 10px 0; display: flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span>Quyền Biểu Quyết Theo Ủy Quyền (BR-04)</span>
                    </h3>
                    <p style="font-size: 12px; color: var(--color-text-muted); margin: 0 0 16px 0;">
                        Bạn được nhận ủy quyền đại diện biểu quyết thay cho thành viên vắng mặt theo giấy ủy quyền đã được phê duyệt.
                    </p>

                    @foreach($validDelegations as $del)
                        <div style="background: var(--color-bg-muted); padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--color-border); margin-bottom: 12px;">
                            <div class="stu-flex stu-items-center stu-justify-between stu-mb-2">
                                <div style="font-size: 14px; font-weight: 700; color: var(--color-text-primary);">
                                    Đại diện cho: {{ $del['authorizer_name'] }}
                                </div>
                                @if($del['has_voted'])
                                    <span class="stu-badge stu-badge-success">✓ Đã bỏ phiếu ủy quyền</span>
                                @else
                                    <span class="stu-badge stu-badge-warning">Chờ bỏ phiếu thay</span>
                                @endif
                            </div>
                            <div style="font-size: 12px; color: var(--color-text-muted); margin-bottom: 12px;">
                                Phạm vi: <em>{{ $del['scope'] }}</em>
                            </div>

                            @if($isOpening && ! $del['has_voted'])
                                <form method="POST" action="{{ route('votes.vote', $vote->MaNoiDung) }}" class="stu-flex stu-items-center stu-gap-2">
                                    @csrf
                                    <input type="hidden" name="authorization_id" value="{{ $del['auth_id'] }}">
                                    <select name="choice" class="stu-form-control" style="font-size: 13px; height: 36px;" required>
                                        <option value="">-- Chọn ý kiến ủy quyền --</option>
                                        <option value="Tán thành">Tán thành</option>
                                        <option value="Không tán thành">Không tán thành</option>
                                        <option value="Không có ý kiến">Không có ý kiến</option>
                                    </select>
                                    <button type="submit" class="stu-btn stu-btn-secondary stu-btn-sm" style="white-space: nowrap; height: 36px;">
                                        Gửi phiếu thay
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Cột phải: Bảng kết quả tổng hợp kiểm phiếu & Bảo mật BR-09 --}}
        <div class="stu-flex stu-flex-col stu-gap-6">
            {{-- Thống kê kiểm phiếu --}}
            <div class="stu-card" style="padding: 24px;">
                <h3 style="font-size: 16px; font-weight: 800; color: var(--color-primary); margin: 0 0 16px 0; display: flex; align-items: center; justify-content: space-between;">
                    <span>Kết quả Kiểm phiếu Tổng hợp</span>
                    <span style="font-size: 12px; font-weight: 500; color: var(--color-text-muted);">
                        Đã thu: <strong>{{ $voteStats['total'] }}</strong> phiếu
                    </span>
                </h3>

                {{-- Tiến độ thanh Tán thành --}}
                <div class="stu-mb-5">
                    <div class="stu-flex stu-items-center stu-justify-between stu-mb-1" style="font-size: 13px;">
                        <span style="font-weight: 700; color: var(--color-success);">✓ Tán thành</span>
                        <span style="font-weight: 800; color: var(--color-success);">{{ $voteStats['agree'] }} phiếu ({{ $voteStats['agree_percent'] }}%)</span>
                    </div>
                    <div style="height: 10px; background: var(--color-bg-muted); border-radius: var(--radius-full); overflow: hidden;">
                        <div style="height: 100%; width: {{ min($voteStats['agree_percent'], 100) }}%; background: var(--color-success); border-radius: var(--radius-full); transition: width 0.3s;"></div>
                    </div>
                    <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 4px; display: flex; justify-content: space-between;">
                        <span>Ngưỡng yêu cầu: {{ $passThreshold }}%</span>
                        <span>{{ $voteStats['is_passed'] ? '✓ Đạt ngưỡng thông qua' : 'Chưa đạt ngưỡng' }}</span>
                    </div>
                </div>

                {{-- Tiến độ Không tán thành --}}
                <div class="stu-mb-5">
                    <div class="stu-flex stu-items-center stu-justify-between stu-mb-1" style="font-size: 13px;">
                        <span style="font-weight: 700; color: var(--color-danger);">✗ Không tán thành</span>
                        <span style="font-weight: 800; color: var(--color-danger);">{{ $voteStats['disagree'] }} phiếu ({{ $voteStats['disagree_percent'] }}%)</span>
                    </div>
                    <div style="height: 8px; background: var(--color-bg-muted); border-radius: var(--radius-full); overflow: hidden;">
                        <div style="height: 100%; width: {{ min($voteStats['disagree_percent'], 100) }}%; background: var(--color-danger); border-radius: var(--radius-full);"></div>
                    </div>
                </div>

                {{-- Tiến độ Không có ý kiến --}}
                <div class="stu-mb-6">
                    <div class="stu-flex stu-items-center stu-justify-between stu-mb-1" style="font-size: 13px;">
                        <span style="font-weight: 600; color: var(--color-text-muted);">— Không có ý kiến</span>
                        <span style="font-weight: 700; color: var(--color-text-muted);">{{ $voteStats['abstain'] }} phiếu ({{ $voteStats['abstain_percent'] }}%)</span>
                    </div>
                    <div style="height: 8px; background: var(--color-bg-muted); border-radius: var(--radius-full); overflow: hidden;">
                        <div style="height: 100%; width: {{ min($voteStats['abstain_percent'], 100) }}%; background: var(--color-border); border-radius: var(--radius-full);"></div>
                    </div>
                </div>

                {{-- Kết luận kiểm phiếu --}}
                <div style="padding: 16px; border-radius: var(--radius-md); background: {{ $voteStats['is_passed'] ? '#f0fdf4' : '#fef2f2' }}; border: 1px solid {{ $voteStats['is_passed'] ? '#bbf7d0' : '#fecaca' }};">
                    <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: {{ $voteStats['is_passed'] ? 'var(--color-success)' : 'var(--color-danger)' }};">
                        Kết luận kiểm phiếu:
                    </div>
                    <div style="font-size: 16px; font-weight: 800; color: {{ $voteStats['is_passed'] ? 'var(--color-success)' : 'var(--color-danger)' }}; margin-top: 2px;">
                        {{ $voteStats['is_passed'] ? 'ĐỦ ĐIỀU KIỆN THÔNG QUA NGHỊ QUYẾT' : 'KHÔNG ĐỦ TỶ LỆ THÔNG QUA' }}
                    </div>
                    <div style="font-size: 12px; color: var(--color-text-muted); margin-top: 4px;">
                        Căn cứ trên {{ $voteStats['present_count'] }} thành viên có mặt tại phiên họp Hội đồng trường.
                    </div>
                </div>
            </div>

            {{-- Cam kết bảo mật BR-09 --}}
            <div class="stu-card" style="padding: 20px; background: #f0f9ff; border: 1px solid #bae6fd;">
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <div style="color: #0369a1; margin-top: 2px;">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h4 style="font-size: 14px; font-weight: 700; color: #0369a1; margin: 0 0 4px 0;">
                            Bảo mật Bỏ phiếu kín (Ràng buộc BR-09)
                        </h4>
                        <p style="font-size: 12px; line-height: 1.6; color: #0c4a6e; margin: 0;">
                            Hệ thống bảo vệ tuyệt đối danh tính người bỏ phiếu. Toàn bộ thông tin lựa chọn cá nhân được mã hóa và ẩn danh, không thể truy vết hoặc xem bởi bất kỳ vai trò nào (kể cả Quản trị viên). Chỉ hiển thị số liệu thống kê tổng hợp phục vụ biên bản phiên họp.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Đăng ký Ủy quyền Biểu quyết (BR-04) --}}
<div class="stu-modal-backdrop" id="delegationModal" role="dialog" aria-modal="true">
    <div class="stu-modal" style="max-width: 600px;">
        <div class="stu-modal-header">
            <h3 class="stu-modal-title">Đăng ký Văn bản Ủy quyền Biểu quyết (BR-04)</h3>
            <button type="button" class="stu-modal-close" onclick="document.getElementById('delegationModal').classList.remove('stu-modal-open');">&times;</button>
        </div>
        <div class="stu-modal-body">
            <form method="POST" action="{{ route('votes.delegations.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="MaPhienHop" value="{{ $meeting?->MaPhienHop ?? 1 }}">

                <div class="stu-form-group stu-mb-4">
                    <label class="stu-form-label" style="font-size: 12px; font-weight: 700;">Thành viên ủy quyền (Vắng mặt) <span style="color: var(--color-danger);">*</span></label>
                    <select name="MaThanhVienUyQuyen" class="stu-form-control" required>
                        <option value="">-- Chọn thành viên ủy quyền --</option>
                        @foreach($officialMembers as $m)
                            <option value="{{ $m->MaThanhVien }}">{{ $m->nguoiDung?->HoTen }} ({{ $m->ChucVu }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="stu-form-group stu-mb-4">
                    <label class="stu-form-label" style="font-size: 12px; font-weight: 700;">Thành viên nhận ủy quyền (Đại diện) <span style="color: var(--color-danger);">*</span></label>
                    <select name="MaThanhVienNhanUyQuyen" class="stu-form-control" required>
                        <option value="">-- Chọn người nhận ủy quyền --</option>
                        @foreach($officialMembers as $m)
                            <option value="{{ $m->MaThanhVien }}">{{ $m->nguoiDung?->HoTen }} ({{ $m->ChucVu }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="stu-form-group stu-mb-4">
                    <label class="stu-form-label" style="font-size: 12px; font-weight: 700;">Phạm vi ủy quyền <span style="color: var(--color-danger);">*</span></label>
                    <input type="text" name="PhamViUyQuyen" value="Toàn bộ các nội dung biểu quyết tại phiên họp" class="stu-form-control" required>
                </div>

                <div class="stu-grid stu-grid-cols-2 stu-gap-3 stu-mb-4">
                    <div class="stu-form-group" style="margin-bottom: 0;">
                        <label class="stu-form-label" style="font-size: 12px; font-weight: 700;">Ngày lập giấy <span style="color: var(--color-danger);">*</span></label>
                        <input type="date" name="NgayUyQuyen" value="{{ date('Y-m-d') }}" class="stu-form-control" required>
                    </div>
                    <div class="stu-form-group" style="margin-bottom: 0;">
                        <label class="stu-form-label" style="font-size: 12px; font-weight: 700;">Tập tin đính kèm (nếu có)</label>
                        <input type="file" name="file" class="stu-form-control" accept=".pdf,.doc,.docx">
                    </div>
                </div>

                <div class="stu-flex stu-justify-end stu-gap-3" style="margin-top: 20px; border-top: 1px solid var(--color-border); padding-top: 16px;">
                    <button type="button" class="stu-btn stu-btn-secondary" onclick="document.getElementById('delegationModal').classList.remove('stu-modal-open');">Hủy</button>
                    <button type="submit" class="stu-btn stu-btn-primary">Lưu văn bản ủy quyền</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
