{{-- Modal Trình Xem Tài Liệu Trực Tuyến Toàn Hệ Thống --}}
<div class="stu-modal-backdrop" id="stuDocViewerModal" role="dialog" aria-modal="true" style="z-index: 9999;">
    <div class="stu-modal" style="width: 95vw; max-width: 1200px; height: 92vh; max-height: 92vh; display: flex; flex-direction: column; background: #f8fafc;">
        <!-- Header -->
        <div class="stu-modal-header" style="background: #ffffff; border-bottom: 1px solid var(--color-border); padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
            <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                <span id="docViewerBadge" style="background: var(--color-primary); color: #fff; font-size: 11px; font-weight: 800; padding: 4px 8px; border-radius: 4px; text-transform: uppercase;">
                    DOC
                </span>
                <div style="min-width: 0;">
                    <h3 id="docViewerTitle" style="margin: 0; font-size: 15px; font-weight: 700; color: var(--color-text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 550px;">
                        Tên tài liệu
                    </h3>
                    <div id="docViewerMeta" style="font-size: 11.5px; color: var(--color-text-muted); margin-top: 2px;">
                        Dung lượng: 0 KB &bull; Định dạng tệp
                    </div>
                </div>
            </div>

            <!-- Toolbar & Actions -->
            <div style="display: flex; align-items: center; gap: 8px;">
                <!-- Zoom controls -->
                <div class="stu-btn-group" id="docViewerZoomControls" style="display: inline-flex; align-items: center; background: #f1f5f9; border-radius: 6px; padding: 2px;">
                    <button type="button" id="docZoomOutBtn" class="stu-btn stu-btn-ghost stu-btn-xs" style="padding: 4px 8px;" title="Thu nhỏ (-)">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                    </button>
                    <span id="docZoomLevel" style="font-size: 11.5px; font-weight: 600; padding: 0 6px; color: var(--color-text-secondary);">100%</span>
                    <button type="button" id="docZoomInBtn" class="stu-btn stu-btn-ghost stu-btn-xs" style="padding: 4px 8px;" title="Phóng to (+)">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    </button>
                </div>

                <!-- Print button -->
                <button type="button" id="docViewerPrintBtn" class="stu-btn stu-btn-outline stu-btn-sm" style="display: inline-flex; align-items: center; gap: 4px;" title="In văn bản">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>In</span>
                </button>

                <!-- Open in new tab -->
                <a id="docViewerOpenTab" href="#" target="_blank" class="stu-btn stu-btn-outline stu-btn-sm" style="display: inline-flex; align-items: center; gap: 4px;" title="Mở trong tab mới">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>Tab mới</span>
                </a>

                <!-- Download -->
                <a id="docViewerDownloadBtn" href="#" class="stu-btn stu-btn-primary stu-btn-sm" style="display: inline-flex; align-items: center; gap: 4px;" title="Tải về máy">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Tải về</span>
                </a>

                <!-- Close -->
                <button type="button" class="stu-modal-close" data-modal-close aria-label="Đóng" style="margin-left: 6px;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="stu-modal-body" id="docViewerBody" style="flex: 1; padding: 20px; overflow-y: auto; display: flex; justify-content: center; background: #e2e8f0; position: relative;">
            <!-- Loading indicator -->
            <div id="docViewerLoading" style="position: absolute; inset: 0; background: rgba(255, 255, 255, 0.85); display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 10;">
                <div class="stu-spinner" style="width: 40px; height: 40px; border: 3px solid #cbd5e1; border-top-color: var(--color-primary); border-radius: 50%; animation: stuSpin 0.8s linear infinite;"></div>
                <div style="margin-top: 12px; font-size: 13px; font-weight: 600; color: var(--color-text-secondary);">Đang nạp và phân tích dữ liệu tài liệu...</div>
            </div>

            <!-- Content Container -->
            <div id="docViewerContentContainer" style="width: 100%; display: flex; justify-content: center; transition: transform 0.2s ease; transform-origin: top center;">
                <!-- Nội dung động được render ở đây qua JavaScript -->
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes stuSpin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .modal-doc-a4-sheet {
        background: #ffffff;
        width: 100%;
        max-width: 860px;
        min-height: 900px;
        padding: 40px 48px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
        border-radius: 4px;
        color: #1e293b;
        font-family: 'Times New Roman', 'Inter', serif;
        font-size: 14.5px;
        line-height: 1.65;
    }
    .modal-doc-a4-sheet p {
        margin: 0 0 6px 0;
    }
    .modal-doc-a4-sheet table {
        width: 100%;
        border-collapse: collapse;
        margin: 12px 0;
        font-size: 13px;
    }
    .modal-doc-a4-sheet table th, .modal-doc-a4-sheet table td {
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
    }
    .modal-doc-a4-sheet table th {
        background-color: #f1f5f9;
        font-weight: 700;
    }
</style>

<script>
(function () {
    let currentZoom = 1;

    function applyZoom(zoom) {
        currentZoom = Math.min(Math.max(zoom, 0.5), 2.0);
        const container = document.getElementById('docViewerContentContainer');
        const zoomText = document.getElementById('docZoomLevel');
        if (container) {
            container.style.transform = `scale(${currentZoom})`;
        }
        if (zoomText) {
            zoomText.textContent = Math.round(currentZoom * 100) + '%';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Zoom listeners
        const zoomInBtn = document.getElementById('docZoomInBtn');
        const zoomOutBtn = document.getElementById('docZoomOutBtn');
        if (zoomInBtn) zoomInBtn.addEventListener('click', () => applyZoom(currentZoom + 0.15));
        if (zoomOutBtn) zoomOutBtn.addEventListener('click', () => applyZoom(currentZoom - 0.15));

        // Print listener
        const printBtn = document.getElementById('docViewerPrintBtn');
        if (printBtn) {
            printBtn.addEventListener('click', function () {
                const iframe = document.querySelector('#docViewerContentContainer iframe');
                if (iframe && iframe.contentWindow) {
                    iframe.contentWindow.print();
                } else {
                    window.print();
                }
            });
        }

        // Bắt sự kiện click vào bất kỳ nút "Xem file" có class .btn-view-doc hoặc thuộc tính data-doc-id
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-view-doc, [data-doc-view]');
            if (!btn) return;

            e.preventDefault();
            const docId = btn.getAttribute('data-doc-id');
            const docTitle = btn.getAttribute('data-doc-title') || 'Tài liệu';
            const docExt = (btn.getAttribute('data-doc-ext') || 'doc').toLowerCase();
            const docUrl = btn.getAttribute('data-doc-url') || '';
            const previewUrl = btn.getAttribute('data-preview-url') || `/documents/${docId}/preview`;
            const downloadUrl = btn.getAttribute('data-download-url') || `/documents/${docId}/download`;
            const viewUrl = btn.getAttribute('data-view-url') || `/documents/${docId}/view`;

            // Reset zoom
            applyZoom(1.0);

            // Cập nhật tiêu đề và huy hiệu
            const titleEl = document.getElementById('docViewerTitle');
            const badgeEl = document.getElementById('docViewerBadge');
            const metaEl = document.getElementById('docViewerMeta');
            const openTabEl = document.getElementById('docViewerOpenTab');
            const downloadBtn = document.getElementById('docViewerDownloadBtn');
            const loadingEl = document.getElementById('docViewerLoading');
            const containerEl = document.getElementById('docViewerContentContainer');

            if (titleEl) titleEl.textContent = docTitle;
            if (badgeEl) {
                badgeEl.textContent = docExt.toUpperCase();
                badgeEl.style.background = (docExt === 'pdf') ? '#dc2626' : 
                                           (docExt.includes('xls') ? '#16a34a' : 
                                           (docExt.includes('ppt') ? '#ea580c' : '#2563eb'));
            }
            if (openTabEl) openTabEl.href = previewUrl;
            if (downloadBtn) downloadBtn.href = downloadUrl;
            if (loadingEl) loadingEl.style.display = 'flex';
            if (containerEl) containerEl.innerHTML = '';

            // Mở modal
            if (window.STUModal) {
                window.STUModal.open('stuDocViewerModal');
            }

            // Xử lý nạp nội dung theo định dạng
            if (docExt === 'pdf') {
                if (loadingEl) loadingEl.style.display = 'none';
                if (containerEl) {
                    containerEl.innerHTML = `
                        <iframe 
                            src="${viewUrl}" 
                            style="width: 100%; height: 78vh; border: none; border-radius: 6px; background: #ffffff;"
                            title="${docTitle}"
                        ></iframe>
                    `;
                }
            } else {
                // Gọi API lấy nội dung phân tích (Word, Excel, PPTX, Image)
                fetch(`/documents/${docId}/content`, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if (loadingEl) loadingEl.style.display = 'none';
                    if (!data.success) {
                        if (containerEl) {
                            containerEl.innerHTML = `<div class="stu-alert stu-alert-danger" style="margin: 20px;">${data.message || 'Không thể hiển thị tệp này.'}</div>`;
                        }
                        return;
                    }

                    if (metaEl) {
                        metaEl.innerHTML = `Phân loại: <strong>${data.type}</strong> &bull; Dung lượng: <strong>${data.size_formatted}</strong>`;
                    }

                    if (['docx', 'doc'].includes(docExt)) {
                        containerEl.innerHTML = `<div class="modal-doc-a4-sheet">${data.html}</div>`;
                    } else if (['xlsx', 'xls'].includes(docExt)) {
                        let sheetsHtml = '';
                        if (data.sheets && data.sheets.length > 0) {
                            sheetsHtml += '<div style="width: 100%; max-width: 1100px; background: #fff; padding: 20px; border-radius: 8px;">';
                            sheetsHtml += '<div style="display: flex; border-bottom: 2px solid #cbd5e1; margin-bottom: 14px;">';
                            data.sheets.forEach((sh, idx) => {
                                sheetsHtml += `<button type="button" class="stu-btn stu-btn-ghost stu-btn-xs modal-sheet-tab ${idx === 0 ? 'active' : ''}" data-sheet-idx="${idx}" style="font-weight: 700; margin-right: 4px; border-radius: 6px 6px 0 0;">${sh.name}</button>`;
                            });
                            sheetsHtml += '</div>';
                            data.sheets.forEach((sh, idx) => {
                                sheetsHtml += `<div id="modal_sheet_${idx}" class="modal-sheet-pane" style="${idx !== 0 ? 'display: none;' : ''}">${sh.html}</div>`;
                            });
                            sheetsHtml += '</div>';
                        } else {
                            sheetsHtml = '<div style="background: #fff; padding: 32px; border-radius: 8px;">Không có dữ liệu bảng tính.</div>';
                        }
                        containerEl.innerHTML = sheetsHtml;

                        // Tab event listener
                        containerEl.querySelectorAll('.modal-sheet-tab').forEach(tab => {
                            tab.addEventListener('click', function () {
                                const sIdx = this.getAttribute('data-sheet-idx');
                                containerEl.querySelectorAll('.modal-sheet-tab').forEach(t => t.classList.remove('active'));
                                containerEl.querySelectorAll('.modal-sheet-pane').forEach(p => p.style.display = 'none');
                                this.classList.add('active');
                                const pane = document.getElementById(`modal_sheet_${sIdx}`);
                                if (pane) pane.style.display = 'block';
                            });
                        });
                    } else if (['pptx', 'ppt'].includes(docExt)) {
                        let slidesHtml = '<div style="width: 100%; max-width: 860px;">';
                        if (data.slides && data.slides.length > 0) {
                            data.slides.forEach(sl => {
                                slidesHtml += `
                                    <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 18px; margin-bottom: 14px; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
                                        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 10px;">
                                            <span style="background: #ea580c; color: #fff; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 4px;">Slide ${sl.index}</span>
                                            <strong style="font-size: 14px; color: #0f172a;">${sl.title}</strong>
                                        </div>
                                        <ul style="margin: 0; padding-left: 20px; line-height: 1.6; color: #334155; font-size: 13.5px;">
                                            ${sl.content.map(c => `<li>${c}</li>`).join('')}
                                        </ul>
                                    </div>
                                `;
                            });
                        } else {
                            slidesHtml += '<div style="background: #fff; padding: 32px; border-radius: 8px;">Không trích xuất được slide.</div>';
                        }
                        slidesHtml += '</div>';
                        containerEl.innerHTML = slidesHtml;
                    } else if (['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'].includes(docExt)) {
                        containerEl.innerHTML = `<img src="${viewUrl}" style="max-width: 100%; max-height: 78vh; object-fit: contain; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">`;
                    } else {
                        containerEl.innerHTML = `<div class="modal-doc-a4-sheet">${data.html || 'Không thể xem trước tệp này.'}</div>`;
                    }
                })
                .catch(err => {
                    if (loadingEl) loadingEl.style.display = 'none';
                    if (containerEl) {
                        containerEl.innerHTML = `<div class="stu-alert stu-alert-danger" style="margin: 20px;">Lỗi kết nối khi tải tài liệu: ${err.message}</div>`;
                    }
                });
            }
        });
    });
})();
</script>
