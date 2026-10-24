/**
 * DỮ LIỆU MẪU DÀNH RIÊNG CHO PROTOTYPE GIAO DIỆN (FRONTEND PROTOTYPE ONLY)
 * 
 * LƯU Ý KỸ THUẬT:
 * - Toàn bộ dữ liệu dưới đây CHỈ PHỤC VỤ TRÌNH DIỄN & KIỂM THỬ TRỰC QUAN GIAO DIỆN.
 * - Không đại diện cho CSDL thật hay backend thật.
 * - Khi kết nối API thật, toàn bộ dữ liệu này sẽ được thay thế bằng kết quả từ axios service.
 */

// Danh sách đầy đủ 7 vai trò chuẩn nghiệp vụ theo docs/business-rules.md & docs/requirements.md
export const PROTOTYPE_ROLES = [
  {
    key: "ADMIN",
    name: "Quản trị viên hệ thống",
    role_name: "Quản trị viên hệ thống",
    user_name: "Kỹ sư Quản Trị Hệ Thống",
    full_name: "Kỹ sư Quản Trị Hệ Thống",
    email: "admin@stu.edu.vn",
    department: "Trung tâm CNTT & Truyền thông",
    department_name: "Trung tâm CNTT & Truyền thông",
    avatar_initials: "AD",
    duties_badge: "Quản trị tài khoản, phân quyền & hệ thống"
  },
  {
    key: "CHAIR",
    name: "Chủ tịch Hội đồng trường",
    role_name: "Chủ tịch Hội đồng trường",
    user_name: "PGS.TS. Nguyễn Văn Chủ Tịch",
    full_name: "PGS.TS. Nguyễn Văn Chủ Tịch",
    email: "chutich_hdt@stu.edu.vn",
    department: "Hội đồng trường STU",
    department_name: "Hội đồng trường STU",
    avatar_initials: "CT",
    duties_badge: "Chủ trì & Ký ban hành Nghị quyết"
  },
  {
    key: "SECRETARY",
    name: "Thư ký Hội đồng trường",
    role_name: "Thư ký Hội đồng trường",
    user_name: "TS. Trần Văn Thư Ký",
    full_name: "TS. Trần Văn Thư Ký",
    email: "thuky_hdt@stu.edu.vn",
    department: "Văn phòng Hội đồng trường",
    department_name: "Văn phòng Hội đồng trường",
    avatar_initials: "TK",
    duties_badge: "Thẩm tra & Điều hành phiên họp"
  },
  {
    key: "MEMBER",
    name: "Thành viên Hội đồng trường",
    role_name: "Thành viên Hội đồng trường",
    user_name: "TS. Lê Hoàng Thành Viên",
    full_name: "TS. Lê Hoàng Thành Viên",
    email: "thanhvien_hdt@stu.edu.vn",
    department: "Khoa Điện - Điện tử",
    department_name: "Khoa Điện - Điện tử",
    avatar_initials: "TV",
    duties_badge: "Tham dự họp & Thực hiện biểu quyết"
  },
  {
    key: "RECTOR_OFFICE",
    name: "Văn phòng Hiệu trưởng",
    role_name: "Văn phòng Hiệu trưởng",
    user_name: "ThS. Hoàng Thị Văn Phòng",
    full_name: "ThS. Hoàng Thị Văn Phòng",
    email: "vphieutruong@stu.edu.vn",
    department: "Văn phòng Hiệu trưởng STU",
    department_name: "Văn phòng Hiệu trưởng STU",
    avatar_initials: "VP",
    duties_badge: "Tham mưu Ban Giám hiệu & Giám sát thực thi"
  },
  {
    key: "UNIT_REP",
    name: "Đại diện Đơn vị trình hồ sơ",
    role_name: "Đại diện Đơn vị trình hồ sơ",
    user_name: "ThS. Đỗ Thị Đại Diện",
    full_name: "ThS. Đỗ Thị Đại Diện",
    email: "daotao@stu.edu.vn",
    department: "Phòng Đào tạo",
    department_name: "Phòng Đào tạo",
    avatar_initials: "ĐT",
    duties_badge: "Nộp tờ trình & Theo dõi thẩm tra"
  },
  {
    key: "EXECUTING_UNIT",
    name: "Đơn vị thực hiện Nghị quyết",
    role_name: "Đơn vị thực hiện Nghị quyết",
    user_name: "TS. Vũ Minh Thực Hiện",
    full_name: "TS. Vũ Minh Thực Hiện",
    email: "cntt@stu.edu.vn",
    department: "Khoa Công nghệ Thông tin",
    department_name: "Khoa Công nghệ Thông tin",
    avatar_initials: "TH",
    duties_badge: "Thực hiện nhiệm vụ & Nộp minh chứng"
  }
];

export const PROTOTYPE_USER = PROTOTYPE_ROLES[2]; // Mặc định Thư ký HĐT

export const PROTOTYPE_NAV_ITEMS = [
  { key: "overview", label: "Tổng quan Dashboard", icon: "LayoutDashboard", path: "/" },
  { key: "components", label: "Kho thành phần UI", icon: "Boxes", path: "/components" },
  { key: "councils", label: "Hội đồng trường", icon: "GraduationCap", path: "/councils", badge: "Khóa IV" },
  { key: "dossiers", label: "Hồ sơ trình", icon: "FileText", path: "/dossiers", count: 4 },
  { key: "meetings", label: "Phiên họp & Tài liệu", icon: "Calendar", path: "/meetings", count: 2 },
  { key: "voting", label: "Biểu quyết", icon: "Vote", path: "/voting" },
  { key: "resolutions", label: "Nghị quyết HĐT", icon: "ScrollText", path: "/resolutions" },
  { key: "tasks", label: "Nhiệm vụ & Tiến độ", icon: "CheckSquare", path: "/tasks", count: 3 },
  { key: "documents", label: "Kho văn bản", icon: "FolderArchive", path: "/documents" },
  { key: "users", label: "Người dùng & Quyền", icon: "Users", path: "/users" }
];

// 6 Thẻ Thống kê cốt lõi chuẩn nghiệp vụ Dashboard
export const PROTOTYPE_DASHBOARD_STATS = [
  {
    id: "total_meetings",
    title: "Tổng số phiên họp",
    value: "08",
    description: "06 định kỳ, 02 chuyên đề",
    trend: "Khóa IV (2020-2025)",
    trendType: "neutral",
    statusColor: "info",
    iconName: "Calendar",
    path: "/meetings"
  },
  {
    id: "pending_dossiers",
    title: "Hồ sơ trình chờ xử lý",
    value: "04",
    description: "02 đang thẩm tra, 02 yêu cầu bổ sung",
    trend: "Cần xử lý trước kỳ họp",
    trendType: "negative",
    statusColor: "warning",
    iconName: "FileText",
    path: "/dossiers"
  },
  {
    id: "issued_resolutions",
    title: "Nghị quyết đã ban hành",
    value: "14",
    description: "100% có văn bản ký số/scan",
    trend: "+3 nghị quyết trong năm nay",
    trendType: "positive",
    statusColor: "info",
    iconName: "ScrollText",
    path: "/resolutions"
  },
  {
    id: "tasks_in_progress",
    title: "Nhiệm vụ đang thực hiện",
    value: "09",
    description: "Thuộc 4 phòng ban và khoa",
    trend: "Tiến độ trung bình: 68%",
    trendType: "neutral",
    statusColor: "info",
    iconName: "Clock",
    path: "/tasks"
  },
  {
    id: "tasks_completed",
    title: "Nhiệm vụ hoàn thành",
    value: "26",
    description: "Đã nghiệm thu minh chứng đạt",
    trend: "Tỷ lệ hoàn thành: 81.2%",
    trendType: "positive",
    statusColor: "success",
    iconName: "CheckCircle2",
    path: "/tasks"
  },
  {
    id: "tasks_overdue",
    title: "Nhiệm vụ quá hạn",
    value: "03",
    description: "Vượt quá hạn chót giao việc",
    trend: "Cần đôn đốc báo cáo gấp",
    trendType: "negative",
    statusColor: "danger",
    iconName: "AlertTriangle",
    path: "/tasks"
  }
];

// Danh sách công việc cần chú ý (Action Items / Urgent Notices)
export const PROTOTYPE_URGENT_TASKS = [
  {
    id: "ACT-01",
    title: "Hồ sơ HST-2026-002 (Mở ngành Robot & AI) cần hoàn tất thẩm tra",
    deadline: "Trước 05/11/2026",
    role_target: "SECRETARY",
    severity: "danger",
    type_label: "Thẩm tra hồ sơ",
    assigned_dept: "Khoa Công nghệ Thông tin",
    days_left: 2
  },
  {
    id: "ACT-02",
    title: "Nhiệm vụ NV-NQ02-03 (Khảo sát doanh nghiệp AI) đã quá hạn 5 ngày",
    deadline: "Hạn chót: 19/10/2026",
    role_target: "EXECUTING_UNIT",
    severity: "danger",
    type_label: "Quá hạn",
    assigned_dept: "Khoa Công nghệ Thông tin",
    days_left: -5
  },
  {
    id: "ACT-03",
    title: "Nhiệm vụ NV-NQ01-02 (Rà soát quy chế chi tiêu) sắp đến hạn",
    deadline: "Hạn chót: 28/10/2026",
    role_target: "EXECUTING_UNIT",
    severity: "warning",
    type_label: "Sắp đến hạn",
    assigned_dept: "Phòng Kế hoạch - Tài chính",
    days_left: 4
  },
  {
    id: "ACT-04",
    title: "Nghiên cứu trước 06 tệp tài liệu Phiên họp thứ 5 HĐT",
    deadline: "Khai mạc: 15/11/2026",
    role_target: "MEMBER",
    severity: "info",
    type_label: "Nghiên cứu tài liệu",
    assigned_dept: "Thành viên Hội đồng trường",
    days_left: 22
  }
];

// Lịch phiên họp sắp tới (Upcoming Meetings)
export const PROTOTYPE_UPCOMING_MEETINGS = [
  {
    id: "PH-2026-K4-05",
    title: "Phiên họp lần thứ 5 Hội đồng trường STU (Khóa IV)",
    type: "REGULAR",
    type_label: "Họp định kỳ",
    format: "IN_PERSON",
    format_label: "Trực tiếp",
    start_time: "15/11/2026 08:30",
    location: "Phòng họp A101, Nhà A - Cơ sở chính STU",
    chairperson: "PGS.TS. Nguyễn Văn Chủ Tịch",
    secretary: "TS. Trần Văn Thư Ký",
    agendas_count: 4,
    documents_count: 6,
    status: "SCHEDULED",
    status_label: "Đã triệu tập"
  },
  {
    id: "PH-2026-K4-06",
    title: "Phiên họp chuyên đề về Đề án Tự chủ Đại học giai đoạn 2026-2030",
    type: "EXTRAORDINARY",
    type_label: "Họp chuyên đề",
    format: "ONLINE",
    format_label: "Trực tuyến (Meet)",
    start_time: "28/11/2026 14:00",
    location: "meet.google.com/stu-hdt-2026",
    chairperson: "PGS.TS. Nguyễn Văn Chủ Tịch",
    secretary: "TS. Trần Văn Thư Ký",
    agendas_count: 2,
    documents_count: 3,
    status: "PREPARING",
    status_label: "Đang chuẩn bị"
  }
];

// Hồ sơ trình chờ xử lý (Pending Dossiers)
export const PROTOTYPE_PENDING_DOSSIERS = [
  {
    id: "HST-2026-002",
    title: "Đề án mở ngành đào tạo Kỹ thuật Robot & Trí tuệ nhân tạo (AI) bậc Đại học",
    department: "Khoa Công nghệ Thông tin",
    submitter: "TS. Lê Hoàng B",
    submitted_date: "20/10/2026",
    status: "in_review",
    status_label: "Đang thẩm tra",
    category: "Đào tạo & Đề án",
    files_count: 5
  },
  {
    id: "HST-2026-003",
    title: "Kế hoạch nâng cấp cơ sở vật chất khu thực hành thí nghiệm giai đoạn 2",
    department: "Phòng Quản trị Thiết bị",
    submitter: "ThS. Phạm Quốc C",
    submitted_date: "21/10/2026",
    status: "revision_required",
    status_label: "Yêu cầu bổ sung",
    category: "Cơ sở vật chất",
    files_count: 2
  },
  {
    id: "HST-2026-004",
    title: "Báo cáo điều chỉnh chỉ tiêu tuyển sinh các ngành kỹ thuật khóa 2026",
    department: "Phòng Đào tạo",
    submitter: "ThS. Trần Thị D",
    submitted_date: "22/10/2026",
    status: "in_review",
    status_label: "Đang thẩm tra",
    category: "Tuyển sinh",
    files_count: 1
  },
  {
    id: "HST-2026-006",
    title: "Quy hoạch nhân sự chủ chốt các đơn vị trực thuộc năm 2027",
    department: "Phòng Tổ chức - Hành chính",
    submitter: "ThS. Hoàng Thị H",
    submitted_date: "23/10/2026",
    status: "in_review",
    status_label: "Đang thẩm tra",
    category: "Tổ chức & Nhân sự",
    files_count: 3
  }
];

// Tình hình thực hiện các Nghị quyết đã ban hành (Resolution Implementation Progress)
export const PROTOTYPE_RESOLUTIONS_PROGRESS = [
  {
    id: "01/NQ-HĐTSTU",
    title: "Nghị quyết về việc ban hành Quy chế chi tiêu nội bộ năm tài chính 2026",
    issued_date: "15/01/2026",
    lead_department: "Phòng Kế hoạch - Tài chính",
    total_tasks: 4,
    completed_tasks: 4,
    progress_percentage: 100,
    status: "COMPLETED",
    status_label: "Hoàn thành 100%"
  },
  {
    id: "02/NQ-HĐTSTU",
    title: "Nghị quyết thông qua Chủ trương xây dựng đề án mở ngành Kỹ thuật Robot & AI",
    issued_date: "18/03/2026",
    lead_department: "Khoa Công nghệ Thông tin",
    total_tasks: 3,
    completed_tasks: 2,
    progress_percentage: 75,
    status: "IN_PROGRESS",
    status_label: "Đang thực hiện (75%)"
  },
  {
    id: "03/NQ-HĐTSTU",
    title: "Nghị quyết về kế hoạch kiểm định chất lượng 03 chương trình đào tạo kỹ thuật",
    issued_date: "20/05/2026",
    lead_department: "Phòng Khảo thí & ĐBCL",
    total_tasks: 5,
    completed_tasks: 3,
    progress_percentage: 60,
    status: "IN_PROGRESS",
    status_label: "Đang thực hiện (60%)"
  },
  {
    id: "04/NQ-HĐTSTU",
    title: "Nghị quyết về nâng cấp trang thiết bị phòng thí nghiệm Cơ điện tử",
    issued_date: "10/08/2026",
    lead_department: "Phòng Quản trị Thiết bị",
    total_tasks: 4,
    completed_tasks: 1,
    progress_percentage: 30,
    status: "OVERDUE",
    status_label: "Chậm tiến độ (30%)"
  }
];

export const PROTOTYPE_NOTIFICATIONS = [
  {
    id: "n-1",
    title: "Triệu tập Phiên họp thứ 5 Hội đồng trường Khóa IV",
    time: "10 phút trước",
    is_read: false,
    type: "meeting"
  },
  {
    id: "n-2",
    title: "Nhiệm vụ số NV-NQ02-03 đã quá hạn 5 ngày (Khoa CNTT)",
    time: "2 giờ trước",
    is_read: false,
    type: "task"
  },
  {
    id: "n-3",
    title: "Khoa CNTT đã cập nhật báo cáo tiến độ nhiệm vụ mở ngành Robot",
    time: "Hôm qua",
    is_read: true,
    type: "report"
  }
];


// Danh sách tệp đính kèm mẫu phục vụ kiểm thử FileUpload & FileList
export const PROTOTYPE_ATTACHED_FILES = [
  {
    id: "f-1",
    name: "To_trinh_so_12_KHTC_2026.pdf",
    size: 2457600, // 2.34 MB
    extension: "pdf",
    uploaded_at: "18/10/2026 14:30",
    is_main: true
  },
  {
    id: "f-2",
    name: "Phu_luc_1_Du_toan_chi_tiet_2026.xlsx",
    size: 1572864, // 1.5 MB
    extension: "xlsx",
    uploaded_at: "18/10/2026 14:32",
    is_main: false
  },
  {
    id: "f-3",
    name: "Bien_ban_hop_lay_y_kien_can_bo.docx",
    size: 845200, // 825 KB
    extension: "docx",
    uploaded_at: "18/10/2026 14:35",
    is_main: false
  }
];
