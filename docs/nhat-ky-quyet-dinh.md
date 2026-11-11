# NHẬT KÝ QUYẾT ĐỊNH KIẾN TRÚC & KỸ THUẬT (DECISION LOG / ADR)
**Dự án:** Ứng dụng web hỗ trợ quản lý phiên họp và thực hiện nghị quyết của Hội đồng trường STU  
**Nguyên tắc cốt lõi:** Mọi quyết định kỹ thuật đều được ghi chép minh bạch; tuyệt đối không âm thầm thay đổi công nghệ hoặc kiến trúc đã được phê duyệt.  

---

## Danh mục Quyết định Kiến trúc (Architectural Decision Records - ADR)

### ADR-001: Lựa chọn Mô hình Monorepo cho Frontend React.js và Backend Laravel
* **Ngày quyết định:** 24/10/2026
* **Trạng thái:** ĐÃ XÁC NHẬN (APPROVED)
* **Bối cảnh:** Dự án luận văn cần một giải pháp quản lý mã nguồn tinh gọn, giúp một nhà phát triển hoặc nhóm nhỏ có thể đồng bộ dễ dàng giữa giao diện người dùng và dịch vụ máy chủ, đồng thời thuận tiện khi nộp mã nguồn kèm theo báo cáo luận văn.
* **Quyết định:** Tổ chức mã nguồn theo mô hình Monorepo tại thư mục gốc `hoi-dong-truong-stu/` (tương ứng `D:\WAMP\www\LVTN/`), phân tách thành 2 thư mục độc lập `frontend/` (React Vite) và `backend/` (Laravel), cùng với `docs/` và `scripts/`.
* **Hệ quả & Lợi ích:**
  * Toàn bộ mã nguồn, tài liệu và kịch bản nằm trong một kho lưu trữ Git duy nhất.
  * Phân định rạch ròi trách nhiệm: Frontend không can thiệp vào CSDL, Backend hoàn toàn độc lập với giao diện và chỉ giao tiếp qua REST API.

---

### ADR-002: Sử dụng JavaScript thuần (ES6+, JSX) cho Frontend
* **Ngày quyết định:** 24/10/2026
* **Trạng thái:** ĐÃ XÁC NHẬN (APPROVED)
* **Bối cảnh:** Yêu cầu bắt buộc của đề tài luận văn tốt nghiệp quy định sử dụng React.js với JavaScript.
* **Quyết định:** Sử dụng JavaScript thuần (`.js`, `.jsx`), cấu hình ESLint chuẩn; **không tự ý chuyển sang TypeScript** khi chưa có văn bản phê duyệt bổ sung.
* **Hệ quả & Lợi ích:** Tuân thủ 100% đề cương đề tài đã đăng ký với nhà trường, giảm thiểu độ phức tạp về cấu hình biên dịch type, tập trung tối đa thời gian vào việc hoàn thiện logic nghiệp vụ.

---

### ADR-003: Sử dụng Laravel Sanctum cho Xác thực RESTful API
* **Ngày quyết định:** 24/10/2026
* **Trạng thái:** ĐÃ XÁC NHẬN (APPROVED)
* **Bối cảnh:** Hệ thống cần một cơ chế xác thực an toàn, nhẹ nhàng, không lưu session trạng thái (stateless) cho các yêu cầu API từ React SPA.
* **Quyết định:** Sử dụng thư viện chính thức **Laravel Sanctum** với cơ chế Bearer Token (Personal Access Tokens).
* **Hệ quả & Lợi ích:**
  * Cung cấp token xác thực đơn giản, an toàn, dễ dàng kiểm tra và thu hồi khi người dùng đăng xuất.
  * Nhẹ hơn đáng kể so với Laravel Passport (OAuth2 server) vốn không cần thiết cho ứng dụng nội bộ trường học.

---

### ADR-004: Tổ chức Mã nguồn Frontend theo Mô hình Tính năng (Feature-based)
* **Ngày quyết định:** 24/10/2026
* **Trạng thái:** ĐÃ XÁC NHẬN (APPROVED)
* **Bối cảnh:** Các ứng dụng React truyền thống thường gom chung toàn bộ views vào một thư mục và components vào một thư mục, dẫn đến tình trạng dự án phình to thì mã nguồn bị phân tán, khó tìm kiếm và khó bảo trì.
* **Quyết định:** Áp dụng Feature-based Architecture cho `frontend/src/features/` (`auth`, `councils`, `dossiers`, `meetings`, `voting`, `resolutions`, `tasks`, `dashboard`). Mỗi feature tự chứa `pages`, `components`, `services` riêng của nó.
* **Hệ quả & Lợi ích:** Tính đóng gói cao, các module nghiệp vụ tách biệt, dễ dàng phân công công việc và dễ viết tài liệu kiến trúc cho luận văn.

---

### ADR-005: Lưu trữ Tài liệu Bảo mật tại Private Storage & Tải về qua Controller
* **Ngày quyết định:** 24/10/2026
* **Trạng thái:** ĐÃ XÁC NHẬN (APPROVED)
* **Bối cảnh:** Tài liệu họp Hội đồng trường chứa nhiều thông tin nhạy cảm (nhân sự, tài chính, quy hoạch phát triển trường). Nếu lưu trong thư mục `public/storage`, bất kỳ ai biết URL trực tiếp đều có thể tải về mà không cần đăng nhập.
* **Quyết định:** Toàn bộ tệp hồ sơ trình, tài liệu phiên họp nội bộ, dự thảo nghị quyết được lưu trữ tại `storage/app/private/`. Người dùng chỉ có thể tải về thông qua API endpoint `GET /api/v1/documents/{id}/download` có kèm Bearer Token và qua lớp kiểm tra thẩm quyền (Laravel Policy).
* **Hệ quả & Lợi ích:** Bảo đảm an toàn thông tin tuyệt đối theo đúng quy chế hành chính của trường đại học, ngăn ngừa lộ lọt dữ liệu nội bộ.

---

### ADR-006: Cơ chế Bỏ phiếu kín (Secret Ballot) bằng Mã băm Ẩn danh
* **Ngày quyết định:** 24/10/2026
* **Trạng thái:** ĐÃ XÁC NHẬN (APPROVED)
* **Bối cảnh:** Nghiệp vụ Hội đồng trường yêu cầu có những nội dung phải bỏ phiếu kín (nhân sự, khen thưởng, kỷ luật). Cần đảm bảo 2 mục tiêu mâu thuẫn nhau: (1) Kiểm soát mỗi người chỉ được bỏ phiếu đúng 1 lần; và (2) Tuyệt đối không ai (kể cả Admin) biết người cụ thể đã chọn phương án nào.
* **Quyết định:**
  * Trong bảng `voting_ballots`: Cột `user_id` sẽ nhận giá trị `NULL` khi hình thức biểu quyết là `SECRET`.
  * Thay vào đó, hệ thống sinh ra một chuỗi băm một chiều: `voter_hash = SHA256(voting_id + user_id + SECRET_SALT)`.
  * CSDL đặt ràng buộc: `UNIQUE(voting_id, voter_hash)`.
* **Hệ quả & Lợi ích:**
  * Nếu thành viên cố tình bỏ phiếu lần thứ 2, hệ thống sẽ chặn ngay lập tức do vi phạm ràng buộc UNIQUE.
  * Vì hàm băm là một chiều và không lưu liên kết `user_id` với `voting_option_id`, tính bảo mật của lá phiếu kín được đảm bảo 100% về mặt toán học và kỹ thuật.

---

### ADR-007: Phân định Rạch ròi giữa Thành viên có Quyền biểu quyết và Khách mời
* **Ngày quyết định:** 24/10/2026
* **Trạng thái:** ĐÃ XÁC NHẬN (APPROVED)
* **Bối cảnh:** Trong phiên họp Hội đồng trường, có thành phần khách mời (đại diện phòng ban trình bày, chuyên gia, đại diện đoàn thể). Nếu không kiểm soát chặt, khách mời có thể bị cấp nhầm quyền biểu quyết.
* **Quyết định:**
  * Bảng `meeting_attendees` có cột `can_vote` (Boolean). Mặc định mọi khách mời (`attendee_type = 'GUEST'`) đều có `can_vote = FALSE`.
  * API nộp phiếu biểu quyết (`POST /api/v1/votings/{id}/vote`) kiểm tra điều kiện nghiêm ngặt: User phải có trong danh sách thành viên HĐT chính thức đương nhiệm, trạng thái điểm danh phải là `PRESENT` và cờ `can_vote = TRUE`.
  * Giao diện React ẩn hoàn toàn khung bỏ phiếu đối với khách mời.
* **Hệ quả & Lợi ích:** Loại bỏ hoàn toàn nguy cơ sai lệch kết quả biểu quyết do khách mời can thiệp.

---

### ADR-008: Quản lý Trạng thái Hệ thống bằng PHP 8.1+ Backed Enums
* **Ngày quyết định:** 24/10/2026
* **Trạng thái:** ĐÃ XÁC NHẬN (APPROVED)
* **Bối cảnh:** Trạng thái hồ sơ, phiên họp, biểu quyết, nhiệm vụ trải qua nhiều bước. Việc sử dụng chuỗi ký tự cứng phân tán khắp nơi ("draft", "submitted", ...) dễ gây lỗi chính tả và khó refactor.
* **Quyết định:** Khởi tạo các Backed Enums trong `app/Enums/`: `RoleEnum`, `DossierStatus`, `MeetingStatus`, `VotingType`, `VotingStatus`, `TaskStatus`. Các Model Eloquent sẽ cast các trường trạng thái này sang Enum class.
* **Hệ quả & Lợi ích:** Kiểm soát chặt chẽ kiểu dữ liệu ở tầng mã nguồn PHP, IDE hỗ trợ gợi ý code thông minh và chống lỗi logic chuyển trạng thái.

---

### ADR-009: Cấu hình Tỷ lệ Thông qua Biểu quyết dạng Tham số Động
* **Ngày quyết định:** 24/10/2026
* **Trạng thái:** ĐÃ XÁC NHẬN (APPROVED)
* **Bối cảnh:** Quy chế hoạt động của Hội đồng trường có thể thay đổi hoặc có các quy định khác nhau giữa biểu quyết thông thường (> 50%) và biểu quyết đặc biệt (>= 65% hoặc 75%). Tài liệu chưa cung cấp tỷ lệ cố định của STU cho từng loại nội dung.
* **Quyết định:** Không gán cứng (hardcode) công thức trong mã nguồn. Mỗi nội dung biểu quyết (`votings`) lưu kèm:
  * `pass_threshold_percentage`: Tỷ lệ % tối thiểu để thông qua (mặc định 50.00%, cho phép Thư ký/Chủ tọa điều chỉnh khi tạo).
  * `base_calculation`: Mẫu số tính % (`ATTENDEES` - trên số người có mặt; hoặc `TOTAL_COUNCIL` - trên tổng số thành viên HĐT theo quy định).
* **Hệ quả & Lợi ích:** Hệ thống hoàn toàn linh hoạt, thích ứng với mọi quy chế pháp lý thực tế của nhà trường mà không cần viết lại mã nguồn.
