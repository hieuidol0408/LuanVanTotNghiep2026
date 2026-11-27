@php
    $selectedDossierIds = array_map('intval', $selectedDossierIds ?? []);
    $selectedVotingIds = array_map('intval', $selectedVotingIds ?? []);
    $containerId = $containerId ?? ('linkEvidence_' . uniqid());
    $currentMeetingId = (int) ($currentMeetingId ?? 0);
@endphp

<div id="{{ $containerId }}" class="stu-grid stu-grid-cols-2" style="gap: 12px; background: #F8FAFC; padding: 14px; border-radius: 10px; border: 1px solid var(--color-border); margin-bottom: 14px; width: 100%; box-sizing: border-box;">
    <div style="min-width: 0;">
        <label class="stu-form-label" style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px;">
            Căn cứ Hồ sơ tờ trình ({{ count($dossiers) }})
        </label>
        <div data-dossier-list style="max-height: 145px; overflow-y: auto; background: #fff; padding: 8px 10px; border-radius: 8px; border: 1px solid var(--color-border);">
            @forelse($dossiers as $dos)
                @php
                    $dosMeetings = $dos->chuongTrinhHop->pluck('MaPhienHop')->map(fn ($v) => (int) $v)->all();
                    $inCurrentMeeting = $currentMeetingId > 0 && in_array($currentMeetingId, $dosMeetings, true);
                @endphp
                <label
                    data-meeting-ids="{{ implode(',', $dosMeetings) }}"
                    style="display: flex; align-items: flex-start; gap: 7px; font-size: 12px; line-height: 1.4; padding: 4px 5px; margin-bottom: 3px; border-radius: 6px; cursor: pointer; {{ $inCurrentMeeting ? 'background: #EFF6FF;' : '' }}"
                >
                    <input
                        type="checkbox"
                        name="dossier_ids[]"
                        value="{{ $dos->MaHoSo }}"
                        {{ in_array((int) $dos->MaHoSo, $selectedDossierIds, true) ? 'checked' : '' }}
                        style="margin-top: 2px; flex-shrink: 0;"
                    >
                    <span style="min-width: 0; word-break: break-word;">
                        <strong style="color: var(--color-primary);">{{ $dos->ma_ho_so_hien_thi }}</strong>: {{ Str::limit($dos->TieuDe, 65) }}
                        @if($dos->donVi)
                            <span style="color: var(--color-text-muted); font-size: 11px;">({{ $dos->donVi->TenDonVi }})</span>
                        @endif
                    </span>
                </label>
            @empty
                <span style="font-size: 12px; color: var(--color-text-muted);">Chưa có hồ sơ trình trong hệ thống.</span>
            @endforelse
        </div>
    </div>

    <div style="min-width: 0;">
        <label class="stu-form-label" style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px;">
            Căn cứ Kết quả biểu quyết ({{ count($votings) }})
        </label>
        <div data-voting-list style="max-height: 145px; overflow-y: auto; background: #fff; padding: 8px 10px; border-radius: 8px; border: 1px solid var(--color-border);">
            @forelse($votings as $vot)
                @php
                    $votMeetingId = (int) ($vot->chuongTrinhHop?->MaPhienHop ?? 0);
                    $inCurrentMeeting = $currentMeetingId > 0 && $votMeetingId === $currentMeetingId;
                @endphp
                <label
                    data-meeting-ids="{{ $votMeetingId > 0 ? $votMeetingId : '' }}"
                    style="display: flex; align-items: flex-start; gap: 7px; font-size: 12px; line-height: 1.4; padding: 4px 5px; margin-bottom: 3px; border-radius: 6px; cursor: pointer; {{ $inCurrentMeeting ? 'background: #EFF6FF;' : '' }}"
                >
                    <input
                        type="checkbox"
                        name="voting_ids[]"
                        value="{{ $vot->MaNoiDung }}"
                        {{ in_array((int) $vot->MaNoiDung, $selectedVotingIds, true) ? 'checked' : '' }}
                        style="margin-top: 2px; flex-shrink: 0;"
                    >
                    <span style="min-width: 0; word-break: break-word;">
                        <strong style="color: var(--color-primary);">#BQ-{{ str_pad($vot->MaNoiDung, 3, '0', STR_PAD_LEFT) }}</strong>: {{ Str::limit($vot->NoiDung, 65) }}
                        @if($votMeetingId > 0)
                            <span style="color: var(--color-text-muted); font-size: 11px;">(Phiên #{{ $votMeetingId }})</span>
                        @endif
                    </span>
                </label>
            @empty
                <span style="font-size: 12px; color: var(--color-text-muted);">Chưa có nội dung biểu quyết trong hệ thống.</span>
            @endforelse
        </div>
    </div>
</div>
