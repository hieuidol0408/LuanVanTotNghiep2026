# TÀI LIỆU ĐẶC TẢ YÊU CẦU HỆ THỐNG (SYSTEM REQUIREMENTS)
**Dự án:** Ứng dụng web hỗ trợ quản lý phiên họp và thực hiện nghị quyết của Hội đồng trường STU  
**Văn bản tham chiếu:** Đề tài luận văn tốt nghiệp  
**Phiên bản:** 1.0  
**Trạng thái:** Bản chuẩn hóa kỹ thuật  

---

## 1. Bối cảnh và Phạm vi hệ thống

### 1.1. Bối cảnh
Hội đồng trường Trường Đại học Công nghệ Sài Gòn (STU) là cơ quan quản trị cao nhất của nhà trường, thực hiện quyền đại diện của chủ sở hữu và các bên có lợi ích liên quan. Hoạt động của Hội đồng trường bao gồm tiếp nhận các đề xuất, tờ trình từ các phòng/ban/khoa; tổ chức các phiên họp định kỳ và chuyên đề; thảo luận, lấy ý kiến và tiến hành biểu quyết để thông qua các chủ trương, quyết sách quan trọng; ban hành nghị quyết và giám sát các đơn vị trong toàn trường triển khai thực hiện các nghị quyết đó.

Hiện nay, nhiều khâu trong quy trình này còn thực hiện thủ công qua văn bản giấy, email rời rạc hoặc các công cụ giao tiếp không chuyên dụng, dẫn đến nguy cơ chậm trễ, khó theo dõi tiến độ thi hành nghị quyết và khó khăn trong việc tổng hợp, lưu trữ hồ sơ, minh chứng.

### 1.2. Phạm vi hệ thống
Hệ thống là một **ứng dụng web quản trị doanh nghiệp/hành chính học thuật** (Web Application), cung cấp giải pháp chuyển đổi số toàn diện theo chuỗi nghiệp vụ khép kín:
$$\text{Hồ sơ trình} \longrightarrow \text{Phiên họp} \longrightarrow \text{Biểu quyết} \longrightarrow \text{Nghị quyết} \longrightarrow \text{Nhiệm vụ} \longrightarrow \text{Kết quả thực hiện}$$

* **Trong phạm vi (In-Scope):**
  1. Quản lý xác thực, người dùng và phân quyền dựa trên vai trò (RBAC).
  2. Quản lý danh sách khóa/nhiệm kỳ và thành viên Hội đồng trường.
  3. Quản lý vòng đời hồ sơ trình (tạo mới, nộp, thẩm tra, xếp lịch họp, lưu trữ).
  4. Quản lý chuẩn bị và tổ chức phiên họp (lịch họp, chương trình họp, thành phần tham dự, điểm danh, phân phối tài liệu bảo mật).
  5. Quản lý biểu quyết phiên họp (tạo nội dung biểu quyết, kiểm soát quyền biểu quyết, bỏ phiếu công khai/kín, tổng hợp kết quả).
  6. Quản lý biên bản phiên họp và ban hành nghị quyết (sinh số nghị quyết, lưu trữ bản scan có ký duyệt, phân phối nghị quyết).
  7. Phân rã nghị quyết thành các nhiệm vụ cụ thể, giao đơn vị thực hiện, tiếp nhận báo cáo tiến độ và tài liệu minh chứng.
  8. Dashboard phân tích, tìm kiếm thông tin, thông báo tự động và lưu vết nhật ký hệ thống (Audit Trail).

* **Ngoài phạm vi (Out-of-Scope ở giai đoạn này):**
  * Tích hợp hội nghị truyền hình trực tuyến (Video Conference - WebRTC): Không tự xây dựng phòng họp video mà chỉ lưu trữ đường dẫn phòng họp trực tuyến (Google Meet/Zoom) nếu là họp từ xa.
  * Tích hợp phần cứng ký số chuyên dụng (Hardware Token PKI): Hệ thống quản lý tải lên và lưu trữ các tệp văn bản đã được ký số hoặc văn bản scan có mộc/chữ ký hợp lệ.

---

## 2. Các vai trò người dùng (User Roles)

Hệ thống phân biệt rõ ràng giữa **người dùng có tài khoản đăng nhập trực tiếp** và **các thực thể/khách mời chỉ tham gia trong quy trình hành chính**:

| Mã vai trò | Tên vai trò | Tính chất tài khoản | Mô tả trách nhiệm & Phạm vi tác vụ |
|:---|:---|:---|:---|
| `ADMIN` | **Quản trị viên hệ thống** | Tài khoản trực tiếp | Quản trị tài khoản, phân vai trò, gán quyền, quản lý danh mục dùng chung (đơn vị, phòng ban), cấu hình hệ thống, theo dõi Audit Log. Không can thiệp vào lá phiếu biểu quyết của Hội đồng trường. |
| `CHAIR` | **Chủ tịch Hội đồng trường** | Tài khoản trực tiếp | Phê duyệt chương trình họp, chủ trì phiên họp, ra lệnh mở/đóng biểu quyết, ký phê duyệt biên bản và ban hành nghị quyết, đánh giá nghiệm thu kết quả thực hiện nhiệm vụ của các đơn vị. |
| `SECRETARY` | **Thư ký Hội đồng trường** | Tài khoản trực tiếp | Tiếp nhận và thẩm tra hồ sơ trình từ các đơn vị; lập lịch họp, tạo chương trình họp (Agenda), lập danh sách triệu tập và gửi tài liệu; hỗ trợ vận hành phiên họp; lập biên bản họp; dự thảo nghị quyết; phân rã nhiệm vụ cho các đơn vị và theo dõi tiến độ báo cáo. |
| `MEMBER` | **Thành viên Hội đồng trường** | Tài khoản trực tiếp | Nhận giấy triệu tập, tiếp cận tài liệu họp nội bộ, tham dự phiên họp, tham gia ý kiến và **thực hiện quyền biểu quyết** (công khai hoặc bỏ phiếu kín). |
| `UNIT_REP` | **Đại diện đơn vị trình hồ sơ** | Tài khoản trực tiếp | Đại diện cho các Phòng/Ban/Khoa soạn thảo và gửi hồ sơ/tờ trình lên Thư ký HĐT; chỉnh sửa, bổ sung hồ sơ theo ý kiến thẩm tra; theo dõi tiến độ xử lý hồ sơ. |
| `EXECUTING_UNIT` | **Đơn vị thực hiện nghị quyết** | Tài khoản trực tiếp | Đầu mối phụ trách tại đơn vị được giao việc theo nghị quyết; cập nhật tỷ lệ hoàn thành (%), định kỳ nộp báo cáo tiến độ và tải lên các tệp tài liệu minh chứng. |
| `RECTOR_OFFICE` | **Văn phòng Hiệu trưởng** | Tài khoản trực tiếp | Theo dõi các nghị quyết đã ban hành để phối hợp tham mưu cho Ban Giám hiệu cụ thể hóa thành các quyết định/kế hoạch điều hành; giám sát tổng thể tiến độ thực thi các chủ trương lớn trong trường. |
| `GUEST` | **Khách mời tham dự họp** | Đối tượng quy trình | Được thêm vào danh sách triệu tập phiên họp để tham dự và đóng góp ý kiến chuyên môn. **TUYỆT ĐỐI KHÔNG CÓ QUYỀN BIỂU QUYẾT** và chỉ được cấp quyền xem tài liệu chỉ định nếu được Thư ký/Chủ tịch cho phép. |

---

## 3. Danh sách các yêu cầu chức năng (Functional Requirements - FR)

### Module 1: Quản trị Hệ thống, Người dùng & Phân quyền (Auth & RBAC)
* **FR-1.1:** Đăng nhập hệ thống an toàn bằng email/tên đăng nhập và mật khẩu, cấp Bearer Token (Laravel Sanctum).
* **FR-1.2:** Đổi mật khẩu, xem và cập nhật thông tin cá nhân.
* **FR-1.3:** Quản lý danh sách người dùng (thêm, sửa, khóa/mở khóa tài khoản, đặt lại mật khẩu).
* **FR-1.4:** Quản lý vai trò (Roles) và ma trận phân quyền (Permissions).
* **FR-1.5:** Quản lý danh mục đơn vị/phòng ban/khoa trực thuộc trường STU.

### Module 2: Quản lý Hội đồng trường & Thành viên (Council & Members)
* **FR-2.1:** Quản lý danh sách các khóa/nhiệm kỳ của Hội đồng trường STU (ví dụ: Nhiệm kỳ 2020-2025, 2025-2030) kèm thời gian hiệu lực và trạng thái đương nhiệm.
* **FR-2.2:** Quản lý hồ sơ thành viên Hội đồng trường theo từng nhiệm kỳ (Họ tên, học hàm, học vị, đơn vị công tác, chức vụ trong HĐT: Chủ tịch, Phó Chủ tịch, Thư ký, Thành viên).
* **FR-2.3:** Quản lý trạng thái hoạt động của thành viên (Đang công tác, Tạm đình chỉ, Miễn nhiệm).

### Module 3: Quản lý Hồ sơ trình (Dossiers)
* **FR-3.1:** Đại diện đơn vị tạo mới hồ sơ trình (tiêu đề, trích yếu nội dung đề xuất, căn cứ pháp lý, đơn vị trình, phân loại nhóm vấn đề).
* **FR-3.2:** Tải lên tệp đính kèm tờ trình (PDF, Word, bảng biểu tính toán).
* **FR-3.3:** Nộp hồ sơ chuyển trạng thái từ `Dự thảo` sang `Đã nộp`.
* **FR-3.4:** Thư ký HĐT tiếp nhận, thẩm tra hồ sơ:
  * Duyệt hồ sơ đạt yêu cầu $\rightarrow$ Đưa vào trạng thái `Sẵn sàng xếp lịch họp`.
  * Yêu cầu bổ sung/chỉnh sửa $\rightarrow$ Đơn vị nhận thông báo kèm lý do cụ thể và nộp lại bản cập nhật.
  * Từ chối hồ sơ $\rightarrow$ Nêu rõ lý do không phù hợp thẩm quyền.
* **FR-3.5:** Xem lịch sử thẩm tra và nhật ký thay đổi của hồ sơ trình.

### Module 4: Quản lý Phiên họp & Tài liệu (Meetings & Agendas)
* **FR-4.1:** Lập kế hoạch phiên họp: Tên phiên họp, loại hình (Định kỳ / Chuyên đề bất thường), hình thức (Trực tiếp / Trực tuyến / Kết hợp), thời gian bắt đầu, địa điểm hoặc link họp.
* **FR-4.2:** Xây dựng chương trình họp (Agenda): Thêm các nội dung thảo luận, liên kết trực tiếp với hồ sơ trình đã được thẩm tra đạt yêu cầu, phân bổ thời lượng và người trình bày.
* **FR-4.3:** Lập danh sách thành phần tham dự: Tự động tải danh sách thành viên HĐT đương nhiệm; cho phép bổ sung đại diện đơn vị trình và khách mời chuyên môn.
* **FR-4.4:** Quản lý và phân phối tài liệu phiên họp có kiểm soát quyền truy cập: Thành viên HĐT xem toàn bộ tài liệu; khách mời chỉ xem tài liệu theo nội dung được phân công.
* **FR-4.5:** Phát hành giấy mời/thông báo triệu tập họp đến từng người tham dự qua email và thông báo trong hệ thống.
* **FR-4.6:** Điểm danh thành phần tham dự phiên họp (Có mặt, Vắng mặt, Có lý do). Ghi nhận thông tin đại biểu tham dự hợp lệ phục vụ kiểm tra điều kiện tiến hành họp và biểu quyết.

### Module 5: Quản lý Biểu quyết & Kiểm phiếu (Voting)
* **FR-5.1:** Tạo nội dung biểu quyết gắn với một mục trong chương trình họp hoặc hồ sơ trình.
* **FR-5.2:** Thiết lập hình thức biểu quyết: **Biểu quyết công khai (Open Voting)** hoặc **Bỏ phiếu kín (Secret Ballot)**.
* **FR-5.3:** Cấu hình các phương án lựa chọn (mặc định: "Tán thành", "Không tán thành", "Không có ý kiến" / hoặc các phương án tùy biến).
* **FR-5.4:** Khởi động phiên biểu quyết (Mở cổng bỏ phiếu) do Chủ tọa hoặc Thư ký thực hiện.
* **FR-5.5:** **Kiểm soát quyền biểu quyết nghiêm ngặt**:
  * Chỉ những tài khoản có vai trò là **Thành viên HĐT đương nhiệm** và có trạng thái **Có mặt hợp lệ** mới được cấp giao diện bỏ phiếu.
  * Khách mời và các vai trò khác tuyệt đối không thể bỏ phiếu.
  * Mỗi thành viên chỉ được bỏ phiếu duy nhất 01 lần trong mỗi nội dung biểu quyết; không thể chỉnh sửa sau khi đã xác nhận gửi.
* **FR-5.6:** Đối với bỏ phiếu kín: Hệ thống ghi nhận lượt đã bỏ phiếu để kiểm soát số lượng nhưng tách rời/mã hóa mối quan hệ giữa danh tính người bỏ phiếu và phương án được chọn, chống truy vết trong database.
* **FR-5.7:** Đóng phiên biểu quyết, hệ thống tự động tổng hợp số phiếu, tính toán tỷ lệ phần trăm (%), kết luận kết quả theo quy chuẩn và xuất biên bản kiểm phiếu.

### Module 6: Quản lý Biên bản & Ban hành Nghị quyết (Resolutions)
* **FR-6.1:** Thư ký soạn thảo và quản lý biên bản phiên họp (ghi nhận diễn biến, các ý kiến phát biểu thảo luận, tổng hợp kết quả biểu quyết).
* **FR-6.2:** Chủ tịch HĐT và Thư ký duyệt biên bản họp.
* **FR-6.3:** Soạn thảo dự thảo Nghị quyết Hội đồng trường dựa trên các nội dung đã được biểu quyết thông qua.
* **FR-6.4:** Cấp số nghị quyết, ngày ban hành theo đúng quy tắc văn thư hành chính của STU.
* **FR-6.5:** Đăng tải văn bản Nghị quyết chính thức (tệp PDF đã ký duyệt và đóng dấu).
* **FR-6.6:** Phân phối và công bố nghị quyết đến các đơn vị, cá nhân liên quan.

### Module 7: Phân rã Nhiệm vụ & Theo dõi thực hiện (Tasks & Execution)
* **FR-7.1:** Trích xuất và phân rã các điều khoản trong Nghị quyết thành các nhiệm vụ cụ thể.
* **FR-7.2:** Giao nhiệm vụ: Chỉ định Đơn vị chủ trì, Đơn vị phối hợp, Người theo dõi, Hạn chót hoàn thành (Deadline) và mức độ ưu tiên.
* **FR-7.3:** Đơn vị thực hiện tiếp nhận nhiệm vụ, cập nhật định kỳ tỷ lệ phần trăm tiến độ (0% đến 100%) và mô tả chi tiết khối lượng công việc đã làm.
* **FR-7.4:** Đơn vị thực hiện tải lên tệp minh chứng kết quả (Quyết định, Báo cáo, Kế hoạch, Biên bản nghiệm thu).
* **FR-7.5:** Thư ký HĐT / Chủ tịch HĐT đánh giá báo cáo tiến độ: Chấp nhận, Yêu cầu bổ sung minh chứng, hoặc Xác nhận hoàn thành nhiệm vụ.
* **FR-7.6:** Cảnh báo tự động về thời hạn: Đúng hạn, Sắp đến hạn (còn 7 ngày/3 ngày), Quá hạn (Overdue).

### Module 8: Dashboard, Tìm kiếm, Thông báo & Nhật ký (Reports & Audit)
* **FR-8.1:** Dashboard trực quan:
  * Biểu đồ tổng quan số lượng phiên họp, nghị quyết đã ban hành trong năm/nhiệm kỳ.
  * Biểu đồ tiến độ thực hiện nhiệm vụ theo từng đơn vị (Hoàn thành, Đang làm, Quá hạn).
  * Danh sách các nhiệm vụ sắp đến hạn hoặc quá hạn cần đôn đốc.
* **FR-8.2:** Tìm kiếm đa năng: Tra cứu nhanh hồ sơ, phiên họp, nghị quyết, nhiệm vụ theo từ khóa, số hiệu, đơn vị, khoảng thời gian.
* **FR-8.3:** Hệ thống thông báo (In-app Notification & Email): Thông báo lịch họp, tài liệu mới, thông báo mở biểu quyết, giao nhiệm vụ mới, nhắc hạn báo cáo.
* **FR-8.4:** Nhật ký hệ thống (Audit Trail): Ghi nhận toàn bộ thao tác quan trọng (thời gian, người thực hiện, địa chỉ IP, hành động, dữ liệu trước và sau thay đổi) nhằm đảm bảo tính minh bạch và phục vụ kiểm tra, giải trình.

---

## 4. Yêu cầu phi chức năng (Non-Functional Requirements - NFR)

### 4.1. Hiệu năng và Tải (Performance)
* **NFR-1.1:** Thời gian phản hồi của API trung bình $\le 1.0$ giây đối với các tác vụ thông thường.
* **NFR-1.2:** Thao tác nộp phiếu biểu quyết có độ trễ $\le 0.5$ giây, đảm bảo khả năng chịu tải đồng thời khi toàn bộ thành viên Hội đồng trường gửi phiếu cùng lúc.
* **NFR-1.3:** Tối ưu hóa tải tệp tài liệu: hỗ trợ tải lên và truyền tải tệp văn bản dung lượng tối đa 50MB/tệp với cơ chế chunking/streaming an toàn.

### 4.2. Bảo mật và Toàn vẹn dữ liệu (Security & Integrity)
* **NFR-2.1:** Mọi mật khẩu người dùng phải được mã hóa một chiều bằng thuật toán `bcrypt` với chi phí phù hợp (work factor $\ge 12$).
* **NFR-2.2:** Giao tiếp giữa React và Laravel bắt buộc qua giao thức an toàn với xác thực Bearer Token (Sanctum). Token hết hạn theo cấu hình và có thể bị thu hồi khi đăng xuất.
* **NFR-2.3:** Kiểm soát truy cập nghiêm ngặt (Authorization): Mọi endpoint thay đổi dữ liệu đều phải kiểm tra quyền qua FormRequest và Laravel Policy. Người dùng tuyệt đối không thể thao tác trên tài nguyên ngoài phạm vi vai trò của mình.
* **NFR-2.4:** Bảo mật tài liệu: Các tệp tài liệu nhạy cảm (tờ trình chưa thẩm tra, tài liệu họp nội bộ, kết quả bỏ phiếu kín) phải lưu trữ tại thư mục private trên máy chủ và chỉ được đọc qua API có kiểm tra quyền, không để lộ đường dẫn tĩnh trực tiếp.
* **NFR-2.5:** Chống các lỗ hổng web phổ biến theo chuẩn OWASP Top 10: SQL Injection (sử dụng Eloquent ORM và PDO Parameter Binding), XSS (thoát mã hiển thị trên React), CSRF, Mass Assignment (chỉ định `$fillable` chặt chẽ trong Model).

### 4.3. Khả năng sử dụng và Trải nghiệm người dùng (Usability & UX)
* **NFR-3.1:** Giao diện thiết kế theo phong cách hành chính học thuật hiện đại, chuẩn mực, đồng bộ, sử dụng font chữ tiếng Việt rõ ràng, bố cục trực quan.
* **NFR-3.2:** **Responsive Design**: Tối ưu hóa trước hết cho giao diện máy tính để bàn (Desktop $\ge 1200\text{px}$) dành cho công tác quản trị, soạn thảo; đồng thời hiển thị hoàn hảo, không vỡ layout trên Tablet (768px - 1199px) và Mobile (< 768px) để phục vụ lãnh đạo và thành viên HĐT tra cứu tài liệu, điểm danh, biểu quyết nhanh trên máy tính bảng hoặc điện thoại.
* **NFR-3.3:** Mọi thao tác gửi biểu mẫu quan trọng hoặc xóa dữ liệu phải có hộp thoại xác nhận (Confirm Modal) và thông báo phản hồi kết quả rõ ràng (Toast message).

### 4.4. Tính sẵn sàng và Dễ bảo trì (Maintainability)
* **NFR-4.1:** Kiến trúc mã nguồn phân tách rõ ràng giữa Frontend (React Vite) và Backend (Laravel REST API).
* **NFR-4.2:** Tuân thủ quy chuẩn viết code quốc tế: PSR-12 đối với PHP/Laravel, ESLint và chuẩn Component hướng tính năng (Feature-based) đối với JavaScript/React.

---

## 5. Tiêu chí nghiệm thu hệ thống (System Acceptance Criteria)

| Mã Module | Tiêu chí nghiệm thu bắt buộc (Pass/Fail) |
|:---|:---|
| **M1: Auth & RBAC** | 1. Đăng nhập thành công trả về token và thông tin quyền tương ứng.<br>2. Đăng nhập sai quá số lần quy định bị khóa tạm thời.<br>3. Người dùng chỉ truy cập được menu và API đúng thẩm quyền vai trò.<br>4. Đăng xuất lập tức vô hiệu hóa token hiện tại. |
| **M2: HĐT & Thành viên**| 1. Tạo và quản lý được thông tin khóa/nhiệm kỳ.<br>2. Quản lý đầy đủ danh sách thành viên đương nhiệm và vai trò trong HĐT.<br>3. Lịch sử thay đổi nhân sự nhiệm kỳ được lưu trữ đầy đủ. |
| **M3: Hồ sơ trình** | 1. Đơn vị trình tải lên được hồ sơ và các tệp đính kèm hợp lệ.<br>2. Thư ký thực hiện đầy đủ quy trình thẩm tra (Duyệt đạt / Yêu cầu sửa / Từ chối).<br>3. Hồ sơ chỉ được xếp lịch họp khi Thư ký đã thẩm tra đạt yêu cầu. |
| **M4: Phiên họp & Tài liệu**| 1. Tạo được phiên họp với đầy đủ chương trình nghị sự gắn với hồ sơ.<br>2. Gửi thông báo triệu tập và điểm danh thành viên có mặt thực tế.<br>3. Phân quyền truy cập tài liệu chính xác: Thành viên HĐT xem đầy đủ, Khách mời chỉ xem tài liệu được cho phép. |
| **M5: Biểu quyết** | 1. **Chỉ thành viên HĐT có mặt hợp lệ mới thấy và nộp được phiếu biểu quyết**.<br>2. Mỗi thành viên chỉ bỏ phiếu đúng 1 lần cho mỗi nội dung.<br>3. Bỏ phiếu kín bảo đảm không để lộ liên kết danh tính người chọn phương án.<br>4. Hệ thống tự động tính tỷ lệ % và kết luận Đạt/Không đạt chính xác theo công thức quy định. |
| **M6: Nghị quyết** | 1. Lưu trữ biên bản phiên họp đầy đủ ý kiến và kết quả kiểm phiếu.<br>2. Đăng ký và sinh số hiệu nghị quyết duy nhất, đính kèm văn bản scan chính thức.<br>3. Nghị quyết ban hành lập tức hiển thị cho các đối tượng được phân phối. |
| **M7: Nhiệm vụ & Tiến độ** | 1. Tách được nhiều nhiệm vụ từ một nghị quyết, gán đúng đơn vị và hạn chót.<br>2. Đơn vị cập nhật được tiến độ (%) kèm tệp văn bản minh chứng.<br>3. Trạng thái nhiệm vụ chuyển sang `Quá hạn` tự động khi vượt quá hạn chót mà chưa hoàn thành. |
| **M8: Dashboard & Audit** | 1. Biểu đồ thống kê hiển thị đúng số liệu thực tế từ cơ sở dữ liệu.<br>2. Tra cứu tìm kiếm đa tiêu chí trả về kết quả chính xác trong $\le 1$ giây.<br>3. Nhật ký Audit Log ghi nhận đầy đủ IP, User, thời gian và dữ liệu thay đổi của các thao tác cốt lõi. |
