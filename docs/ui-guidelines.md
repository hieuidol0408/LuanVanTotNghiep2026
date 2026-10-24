# HƯỚNG DẪN THIẾT KẾ GIAO DIỆN (UI/UX GUIDELINES)
**Dự án:** Ứng dụng web hỗ trợ quản lý phiên họp và thực hiện nghị quyết của Hội đồng trường STU  
**Thư viện giao diện:** Ant Design 5.x kết hợp CSS Modules / CSS Variables  
**Phong cách:** Hiện đại, hành chính công vụ chuẩn mực, trực quan, tối ưu cho desktop và linh hoạt trên mobile/tablet.  

---

## 1. Hệ thống Màu sắc (Color Palette)

Hệ màu được xây dựng dựa trên nhận diện thương hiệu học thuật của Trường Đại học Công nghệ Sài Gòn (STU):

### 1.1. Màu Chủ đạo & Thứ cấp (Primary & Secondary)
* **Primary Blue (Màu xanh chủ đạo STU):** `#003A8C` (Navy Blue đậm - thể hiện sự trang trọng, tin cậy, chuẩn mực hành chính).
* **Primary Hover / Active:** `#1890FF` (Xanh dương sáng - dùng cho trạng thái tương tác hover, link, focus).
* **Primary Light Background:** `#E6F7FF` (Dùng cho nền highlight, menu đang active).

### 1.2. Màu Ngữ nghĩa Trạng thái (Semantic Colors)
* **Success (Thành công, Đạt, Hoàn thành):**
  * Mã màu: `#52C41A` (Nền nhạt: `#F6FFED`, Viền: `#B7EB8F`).
  * Sử dụng: Nghị quyết đã ban hành, biểu quyết Đạt, nhiệm vụ Hoàn thành 100%, hồ sơ Đạt yêu cầu.
* **Warning (Cảnh báo, Sắp đến hạn, Chờ duyệt):**
  * Mã màu: `#FAAD14` (Nền nhạt: `#FFFBE6`, Viền: `#FFE58F`).
  * Sử dụng: Hồ sơ đang thẩm tra, nhiệm vụ gần đến hạn (còn dưới 7 ngày), phiên họp sắp diễn ra.
* **Danger / Error (Lỗi, Từ chối, Quá hạn, Không đạt):**
  * Mã màu: `#FF4D4F` (Nền nhạt: `#FFF2F0`, Viền: `#FFCCC7`).
  * Sử dụng: Nhiệm vụ quá hạn, biểu quyết Không đạt, hồ sơ bị từ chối, xóa dữ liệu.
* **Info (Thông tin, Đang diễn ra, Dự thảo):**
  * Mã màu: `#1890FF` (Nền nhạt: `#E6F7FF`, Viền: `#91D5FF`).
  * Sử dụng: Phiên họp đang diễn ra, hồ sơ bản nháp, thông báo hệ thống.

### 1.3. Màu Trung tính (Neutral Colors)
* **Tiêu đề chính (Heading Text):** `#1F1F1F` (Độ tương phản cao, dễ đọc).
* **Nội dung thân (Body Text):** `#434343`.
* **Văn bản phụ / Ghi chú (Secondary / Muted Text):** `#8C8C8C`.
* **Đường kẻ viền (Borders / Dividers):** `#D9D9D9` hoặc `#F0F0F0`.
* **Màu nền ứng dụng (App Background):** `#F0F2F5` (Xám nhạt dịu mắt, làm nổi bật các Card trắng).
* **Màu nền Container / Card:** `#FFFFFF`.

---

## 2. Hệ thống Kiểu chữ (Typography)

* **Phông chữ chủ đạo:** `'Be Vietnam Pro', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif`.
  *(Đảm bảo hiển thị hoàn hảo tiếng Việt có dấu, không bị lỗi chân chữ hoặc dính dấu).*
* **Thang kích thước chữ (Type Scale):**
  * **Display Title (Tiêu đề trang lớn):** `24px` | Line-height: `32px` | Font-weight: `600` (Semi-bold).
  * **Section Heading (Tiêu đề khối / Card):** `18px` | Line-height: `26px` | Font-weight: `600`.
  * **Subheading (Tiêu đề mục nhỏ, tiêu đề bảng):** `16px` | Line-height: `24px` | Font-weight: `500` (Medium).
  * **Base Body (Văn bản chuẩn nội dung):** `14px` | Line-height: `22px` | Font-weight: `400` (Regular).
  * **Caption / Meta (Ghi chú nhỏ, ngày giờ, số trang):** `12px` | Line-height: `18px` | Font-weight: `400`.

---

## 3. Hệ thống Lưới & Khoảng cách (Spacing Grid)

Hệ thống tuân thủ nghiêm ngặt **hệ lưới 8 điểm (8-point Grid System)**:
* `4px`: Khoảng cách cực nhỏ (padding trong tag trạng thái, khoảng cách giữa icon và chữ).
* `8px`: Khoảng cách nhỏ (padding nút nhỏ, margin giữa các button inline).
* `16px`: Khoảng cách cơ sở (padding ô trong bảng, khoảng cách giữa các trường trong Form).
* `24px`: Khoảng cách tiêu chuẩn (padding bên trong Card, khoảng cách giữa các Card).
* `32px`: Khoảng cách lớn (khoảng cách phân cách giữa các Section chính của trang).

---

## 4. Thiết kế & Quy chuẩn Components Cốt lõi

### 4.1. Bố cục Khung Quản trị (AdminLayout)
* **Sidebar (Menu điều hướng bên trái):**
  * Chiều rộng khi mở: `256px`. Chiều rộng khi thu gọn (collapsed): `80px`.
  * Màu nền: Xanh đen sang trọng (`#001529`) hoặc Trắng (`#FFFFFF`) có đổ bóng nhẹ.
  * Phân nhóm menu theo vai trò: Người dùng chỉ thấy các menu mà mình có quyền truy cập.
  * Logo STU đặt ở đỉnh Sidebar kèm tên viết tắt "HĐT STU".
* **Header (Thanh công cụ trên cùng):**
  * Chiều cao: `64px`, nền trắng `#FFFFFF`, viền dưới mảnh `#F0F0F0`.
  * Bên trái: Nút bật/tắt Sidebar (Toggle Icon) và đường dẫn phân cấp (`Breadcrumb`).
  * Bên phải: Nút chuyển nhanh phiên họp, Chuông thông báo (kèm Badge đỏ số lượng chưa đọc), Avatar và Menu tài khoản người dùng (Thông tin cá nhân, Đổi mật khẩu, Đăng xuất).
* **Content Area:**
  * Nền xám `#F0F2F5`, padding `24px`. Toàn bộ nội dung hiển thị trong các khối Card trắng bo tròn nhẹ (`border-radius: 8px`), đổ bóng dịu (`box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03)`).
* **Footer:**
  * Căn giữa, chữ màu xám `#8C8C8C`, kích thước 13px: *"© 2026 Hội đồng trường Trường Đại học Công nghệ Sài Gòn (STU). Hệ thống Quản lý Phiên họp & Thực hiện Nghị quyết."*

### 4.2. Bảng Dữ liệu (DataTable Component)
* Bọc từ component `Table` của Ant Design, chuẩn hóa các thuộc tính:
  * Kích thước dòng vừa phải (`middle` hoặc `small`).
  * Dòng chẵn lẻ hoặc hover đổi màu nhẹ `#FAFAFA`.
  * Phân trang cố định ở góc dưới bên phải (`showSizeChanger: true`, tùy chọn 10, 20, 50 dòng/trang).
  * Luôn có cột thao tác (Actions) ở vị trí cuối cùng bên phải với các nút biểu tượng rõ ràng (Xem chi tiết, Sửa, Tải tệp, Xóa).
  * Bộ lọc nhanh và thanh tìm kiếm từ khóa đặt ngay trên đầu bảng.

### 4.3. Nút Thao tác (Button System)
* **Primary Button:** Màu xanh chủ đạo, dùng cho hành động chính duy nhất trên màn hình (ví dụ: "Tạo hồ sơ mới", "Mở biểu quyết", "Ban hành Nghị quyết").
* **Default Button:** Viền xám, nền trắng, dùng cho các thao tác phụ (ví dụ: "Lưu bản nháp", "Hủy bỏ", "Quay lại").
* **Danger Button:** Màu đỏ, dùng cho thao tác phá hủy hoặc từ chối (ví dụ: "Xóa hồ sơ", "Từ chối thẩm tra").
* Mọi nút Danger bắt buộc phải đi kèm hộp thoại xác nhận (`ConfirmModal` hoặc `Popconfirm`).

### 4.4. Thẻ Trạng thái (StatusTag Component)
* Thiết kế đồng bộ trên toàn bộ hệ thống bằng Antd `Tag`:
  * `DRAFT`: Tag màu xám (`default`).
  * `SUBMITTED` / `IN_REVIEW`: Tag màu cam cảnh báo (`warning`).
  * `APPROVED_FOR_MEETING` / `PASSED`: Tag màu xanh lá (`success`).
  * `REJECTED` / `OVERDUE`: Tag màu đỏ nguy hiểm (`error`).
  * `IN_PROGRESS`: Tag màu xanh dương đang xử lý (`processing`).

### 4.5. Hộp thoại (Modal) & Ngăn kéo (Drawer)
* **Modal:** Dùng cho các thao tác nhanh, tập trung (ví dụ: Form điểm danh, Xác nhận bỏ phiếu, Đổi mật khẩu). Chiều rộng chuẩn: `520px` hoặc `720px`.
* **Drawer (Trượt từ phải sang):** Dùng cho xem nhanh chi tiết hồ sơ, lịch sử thẩm tra, hoặc danh sách tài liệu đính kèm mà không cần rời khỏi trang hiện tại. Chiều rộng chuẩn: `600px`.

---

## 5. Quy tắc Phản hồi & Trạng thái Tương tác (State Feedback)

1. **Trạng thái Đang tải (Loading State):**
   * Đối với tải dữ liệu trang: Sử dụng **Skeleton screen** (khung xương mô phỏng bảng/card) thay vì chỉ hiện vòng xoay tròn đơn điệu, giúp người dùng cảm thấy tốc độ tải nhanh hơn.
   * Đối với việc gửi Form: Nút bấm hiển thị trạng thái `loading={true}` và bị vô hiệu hóa (disabled) để chống việc người dùng click nhiều lần (Double Submit).
2. **Trạng thái Trống (Empty State):**
   * Khi một danh sách không có dữ liệu (chưa có hồ sơ, chưa có phiên họp): Hiển thị component `Empty` với hình minh họa nhã nhặn, dòng chữ thông báo rõ ràng (ví dụ: *"Hiện tại chưa có hồ sơ nào cần thẩm tra"*) và kèm theo một nút hành động khuyến khích (ví dụ: *"Tạo hồ sơ mới"*).
3. **Thông báo Phản hồi (Toast / Notification):**
   * Sử dụng Ant Design `message` (Toast xuất hiện ở góc trên chính giữa màn hình trong 3 giây) cho các tác vụ thông thường (ví dụ: *"Cập nhật tiến độ thành công"*).
   * Sử dụng `notification` (thông báo góc trên bên phải) cho các sự kiện quan trọng cần đọc lại (ví dụ: *"Bạn nhận được giấy triệu tập phiên họp số 05"*).

---

## 6. Tiêu chuẩn Đáp ứng Giao diện (Responsive Design)

Hệ thống được thiết kế theo tư duy **Desktop-first** (do công việc quản trị, soạn thảo, tổng hợp diễn ra chủ yếu trên máy tính), nhưng **được tối ưu hóa hoàn chỉnh cho Tablet và Mobile**:

| Thiết bị | Breakpoint | Hành vi giao diện |
|:---|:---|:---|
| **Desktop** | $\ge 1200\text{px}$ | Sidebar mở rộng đầy đủ; Bảng hiển thị đầy đủ các cột dữ liệu; Dashboard hiển thị 3-4 cột thẻ chỉ số. |
| **Tablet** | $768\text{px} - 1199\text{px}$ | Sidebar tự động thu gọn về dạng icon; Bảng ẩn bớt các cột ít quan trọng (ngày tạo, ghi chú); hỗ trợ xoay ngang màn hình để xem tài liệu. |
| **Mobile** | $< 768\text{px}$ | Sidebar biến thành ngăn kéo trượt (Drawer Menu); Bảng tự động chuyển đổi thành dạng danh sách thẻ (Card List View); Màn hình bỏ phiếu biểu quyết được thiết kế nút to, dễ chạm ngón tay (Touch-friendly). |

---

## 7. Khả năng Tiếp cận & Thân thiện Người dùng (Accessibility - a11y)

* **Tương phản màu sắc:** Tỷ lệ tương phản giữa chữ và nền (Contrast Ratio) luôn đạt chuẩn WCAG AA ($\ge 4.5:1$ đối với văn bản chuẩn).
* **Hỗ trợ bàn phím:** Người dùng có thể sử dụng phím `Tab`, `Enter`, `Esc` để điều hướng, chọn phương án biểu quyết và đóng các cửa sổ Modal một cách thuận tiện.
* **Biểu mẫu rõ ràng:** Mọi trường nhập liệu đều có nhãn (Label) rõ ràng, dấu sao đỏ `*` đối với các trường bắt buộc, và thông báo lỗi hiển thị ngay dưới trường bị sai khi bấm gửi.
