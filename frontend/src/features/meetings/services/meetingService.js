/**
 * MOCK SERVICE - QUẢN LÝ PHIÊN HỌP HỘI ĐỒNG TRƯỜNG STU
 * 
 * LƯU Ý KỸ THUẬT:
 * - Tệp này hoạt động như một stateful prototype service lưu trữ và thao tác dữ liệu phiên họp.
 * - Khi kết nối API thật, các hàm này sẽ gọi endpoint Axios (`/api/v1/meetings/*`) từ docs/api-contracts.md.
 */

let MEETINGS_STORAGE = [
  {
    id: "PH-2026-K4-05",
    title: "Phiên họp lần thứ 5 Hội đồng trường STU (Khóa IV)",
    term: "Khóa IV (2020-2025)",
    meeting_type: "REGULAR", // REGULAR | EXTRAORDINARY
    meeting_type_label: "Họp định kỳ",
    meeting_format: "IN_PERSON", // IN_PERSON | ONLINE | HYBRID
    meeting_format_label: "Trực tiếp",
    meeting_date: "2026-11-15",
    start_time: "08:30",
    end_time: "11:45",
    location: "Phòng họp A101, Nhà A - Trụ sở chính STU",
    chairperson: "PGS.TS. Nguyễn Văn Chủ Tịch",
    secretary: "TS. Trần Văn Thư Ký",
    description: "Phiên họp định kỳ quý IV xem xét đề án mở ngành mới, thảo luận điều chỉnh quy chế chi tiêu và kiện toàn nhân sự Ban Kiểm soát.",
    status: "SCHEDULED", // PREPARING | SCHEDULED | IN_PROGRESS | COMPLETED | CANCELLED
    status_label: "Đã triệu tập",
    agendas: [
      {
        id: "ag-1",
        item_order: 1,
        title: "Tuyên bố lý do, điểm danh thành phần tham dự và thông qua chương trình họp",
        presenter: "TS. Trần Văn Thư Ký",
        duration_minutes: 20,
        dossier_id: null,
        dossier_title: null,
        is_voting_required: false,
        status: "PENDING"
      },
      {
        id: "ag-2",
        item_order: 2,
        title: "Xem xét và thảo luận Đề án mở ngành đào tạo Kỹ thuật Robot & Trí tuệ nhân tạo (AI) bậc Đại học",
        presenter: "TS. Lê Hoàng B (Trưởng Khoa CNTT)",
        duration_minutes: 45,
        dossier_id: "HST-2026-002",
        dossier_title: "Đề án mở ngành đào tạo Kỹ thuật Robot & Trí tuệ nhân tạo (AI) bậc Đại học",
        is_voting_required: true,
        status: "PENDING"
      },
      {
        id: "ag-3",
        item_order: 3,
        title: "Thảo luận và cho ý kiến về Quy chế chi tiêu nội bộ năm tài chính 2026-2027",
        presenter: "ThS. Nguyễn Văn A (Trưởng phòng KHTC)",
        duration_minutes: 45,
        dossier_id: "HST-2026-001",
        dossier_title: "Tờ trình về việc ban hành Quy chế chi tiêu nội bộ năm tài chính 2026-2027",
        is_voting_required: true,
        status: "PENDING"
      },
      {
        id: "ag-4",
        item_order: 4,
        title: "Lấy ý kiến kiện toàn bổ sung Thành viên Ban Kiểm soát nội bộ Hội đồng trường",
        presenter: "PGS.TS. Nguyễn Văn Chủ Tịch",
        duration_minutes: 30,
        dossier_id: null,
        dossier_title: null,
        is_voting_required: true,
        status: "PENDING"
      }
    ],
    attendees: [
      {
        id: "att-1",
        user_id: 101,
        full_name: "PGS.TS. Nguyễn Văn Chủ Tịch",
        academic_title: "PGS.TS",
        role_in_council: "Chủ tịch Hội đồng trường",
        department: "Hội đồng trường",
        attendee_type: "OFFICIAL", // OFFICIAL | GUEST
        can_vote: true,
        attendance_status: "PRESENT", // INVITED | PRESENT | ABSENT | EXCUSED
        attendance_label: "Có mặt",
        checkin_time: "2026-11-15 08:15",
        notes: "Chủ trì phiên họp"
      },
      {
        id: "att-2",
        user_id: 102,
        full_name: "TS. Trần Văn Thư Ký",
        academic_title: "TS",
        role_in_council: "Thư ký Hội đồng trường",
        department: "Văn phòng HĐT",
        attendee_type: "OFFICIAL",
        can_vote: true,
        attendance_status: "PRESENT",
        attendance_label: "Có mặt",
        checkin_time: "2026-11-15 08:10",
        notes: "Ghi biên bản"
      },
      {
        id: "att-3",
        user_id: 103,
        full_name: "TS. Lê Hoàng Thành Viên",
        academic_title: "TS",
        role_in_council: "Thành viên Hội đồng trường",
        department: "Khoa Điện - Điện tử",
        attendee_type: "OFFICIAL",
        can_vote: true,
        attendance_status: "PRESENT",
        attendance_label: "Có mặt",
        checkin_time: "2026-11-15 08:20",
        notes: ""
      },
      {
        id: "att-4",
        user_id: 104,
        full_name: "PGS.TS. Đỗ Văn Đại Diện",
        academic_title: "PGS.TS",
        role_in_council: "Thành viên Hội đồng trường",
        department: "Khoa Cơ khí",
        attendee_type: "OFFICIAL",
        can_vote: true,
        attendance_status: "PRESENT",
        attendance_label: "Có mặt",
        checkin_time: "2026-11-15 08:22",
        notes: ""
      },
      {
        id: "att-5",
        user_id: 105,
        full_name: "ThS. Nguyễn Thị Thành Viên",
        academic_title: "ThS",
        role_in_council: "Thành viên Hội đồng trường",
        department: "Phòng Quản trị",
        attendee_type: "OFFICIAL",
        can_vote: false, // Vì vắng mặt nên can_vote chuyển sang false
        attendance_status: "EXCUSED",
        attendance_label: "Vắng có lý do",
        checkin_time: null,
        notes: "Đi công tác tại Hà Nội theo quyết định của BGH"
      },
      {
        id: "att-6",
        user_id: 201,
        full_name: "TS. Lê Hoàng B",
        academic_title: "TS",
        role_in_council: "Khách mời chuyên môn",
        department: "Khoa Công nghệ Thông tin",
        attendee_type: "GUEST",
        can_vote: false, // Bắt buộc false theo docs/business-rules.md
        attendance_status: "PRESENT",
        attendance_label: "Có mặt",
        checkin_time: "2026-11-15 08:25",
        notes: "Báo cáo đề án mở ngành AI"
      },
      {
        id: "att-7",
        user_id: 202,
        full_name: "ThS. Nguyễn Văn A",
        academic_title: "ThS",
        role_in_council: "Khách mời chuyên môn",
        department: "Phòng Kế hoạch - Tài chính",
        attendee_type: "GUEST",
        can_vote: false,
        attendance_status: "PRESENT",
        attendance_label: "Có mặt",
        checkin_time: "2026-11-15 08:18",
        notes: "Báo cáo quy chế chi tiêu"
      }
    ],
    documents: [
      {
        id: "doc-1",
        file_name: "Giay_trieu_tap_Phien_hop_5_HDT.pdf",
        file_size: 1258291, // 1.2 MB
        file_type: "pdf",
        access_level: "INTERNAL", // INTERNAL | PUBLIC_AGENDA
        access_level_label: "Nội bộ HĐT",
        uploaded_at: "2026-11-01 09:30",
        uploaded_by: "TS. Trần Văn Thư Ký"
      },
      {
        id: "doc-2",
        file_name: "Chuong_trinh_nghi_su_chi_tiet.pdf",
        file_size: 552960, // 540 KB
        file_type: "pdf",
        access_level: "PUBLIC_AGENDA",
        access_level_label: "Công khai cho đại biểu",
        uploaded_at: "2026-11-01 09:35",
        uploaded_by: "TS. Trần Văn Thư Ký"
      },
      {
        id: "doc-3",
        file_name: "De_an_mo_nganh_Robot_AI_trinh_HDT.pdf",
        file_size: 5033164, // 4.8 MB
        file_type: "pdf",
        access_level: "PUBLIC_AGENDA",
        access_level_label: "Công khai cho đại biểu",
        uploaded_at: "2026-11-03 14:20",
        uploaded_by: "Khoa Công nghệ Thông tin"
      },
      {
        id: "doc-4",
        file_name: "Du_thao_Quy_che_chi_tieu_2026_2027.pdf",
        file_size: 2621440, // 2.5 MB
        file_type: "pdf",
        access_level: "INTERNAL",
        access_level_label: "Nội bộ HĐT",
        uploaded_at: "2026-11-04 10:15",
        uploaded_by: "Phòng Kế hoạch - Tài chính"
      }
    ],
    votings: [
      {
        id: "vot-1",
        agenda_id: "ag-2",
        title: "Biểu quyết thông qua Đề án mở ngành đào tạo Kỹ thuật Robot & Trí tuệ nhân tạo (AI)",
        voting_type: "OPEN", // OPEN | SECRET
        voting_type_label: "Biểu quyết công khai",
        pass_threshold: "50%",
        status: "PENDING", // PENDING | OPENED | CLOSED
        status_label: "Chưa mở"
      },
      {
        id: "vot-2",
        agenda_id: "ag-3",
        title: "Biểu quyết thông qua Quy chế chi tiêu nội bộ năm tài chính 2026-2027",
        voting_type: "OPEN",
        voting_type_label: "Biểu quyết công khai",
        pass_threshold: "50%",
        status: "PENDING",
        status_label: "Chưa mở"
      },
      {
        id: "vot-3",
        agenda_id: "ag-4",
        title: "Bỏ phiếu tín nhiệm kiện toàn nhân sự Ban Kiểm soát nội bộ Hội đồng trường",
        voting_type: "SECRET",
        voting_type_label: "Bỏ phiếu kín",
        pass_threshold: "65%",
        status: "PENDING",
        status_label: "Chưa mở"
      }
    ]
  },
  {
    id: "PH-2026-K4-06",
    title: "Phiên họp chuyên đề về Đề án Tự chủ Đại học giai đoạn 2026-2030",
    term: "Khóa IV (2020-2025)",
    meeting_type: "EXTRAORDINARY",
    meeting_type_label: "Họp chuyên đề",
    meeting_format: "ONLINE",
    meeting_format_label: "Trực tuyến (Meet)",
    meeting_date: "2026-11-28",
    start_time: "14:00",
    end_time: "17:00",
    location: "https://meet.google.com/stu-hdt-2026",
    chairperson: "PGS.TS. Nguyễn Văn Chủ Tịch",
    secretary: "TS. Trần Văn Thư Ký",
    description: "Phiên họp trực tuyến thảo luận chiến lược mô hình tự chủ đại học, cơ cấu nguồn thu và phân cấp quản lý theo Nghị quyết của Bộ GD&ĐT.",
    status: "PREPARING",
    status_label: "Đang chuẩn bị",
    agendas: [
      {
        id: "ag-6-1",
        item_order: 1,
        title: "Trình bày báo cáo khả thi Đề án tự chủ tài chính và bộ máy STU",
        presenter: "Tổ công tác Xây dựng Đề án",
        duration_minutes: 60,
        dossier_id: null,
        dossier_title: null,
        is_voting_required: true,
        status: "PENDING"
      }
    ],
    attendees: [],
    documents: [],
    votings: []
  },
  {
    id: "PH-2026-K4-04",
    title: "Phiên họp lần thứ 4 Hội đồng trường STU (Khóa IV)",
    term: "Khóa IV (2020-2025)",
    meeting_type: "REGULAR",
    meeting_type_label: "Họp định kỳ",
    meeting_format: "IN_PERSON",
    meeting_format_label: "Trực tiếp",
    meeting_date: "2026-07-15",
    start_time: "08:30",
    end_time: "11:30",
    location: "Phòng họp A101, Nhà A",
    chairperson: "PGS.TS. Nguyễn Văn Chủ Tịch",
    secretary: "TS. Trần Văn Thư Ký",
    description: "Sơ kết hoạt động 6 tháng đầu năm 2026 và phê duyệt chỉ tiêu tuyển sinh đại học khóa 2026.",
    status: "COMPLETED",
    status_label: "Đã bế mạc",
    agendas: [],
    attendees: [],
    documents: [],
    votings: []
  },
  {
    id: "PH-2026-K4-03",
    title: "Phiên họp lần thứ 3 Hội đồng trường STU (Khóa IV)",
    term: "Khóa IV (2020-2025)",
    meeting_type: "REGULAR",
    meeting_type_label: "Họp định kỳ",
    meeting_format: "IN_PERSON",
    meeting_format_label: "Trực tiếp",
    meeting_date: "2026-03-18",
    start_time: "08:30",
    end_time: "11:30",
    location: "Phòng họp A101, Nhà A",
    chairperson: "PGS.TS. Nguyễn Văn Chủ Tịch",
    secretary: "TS. Trần Văn Thư Ký",
    description: "Phê duyệt kế hoạch đầu tư mua sắm trang thiết bị phòng thí nghiệm Cơ điện tử và CNTT.",
    status: "COMPLETED",
    status_label: "Đã bế mạc",
    agendas: [],
    attendees: [],
    documents: [],
    votings: []
  }
];

export const meetingService = {
  // Lấy danh sách phiên họp có lọc và tìm kiếm
  getMeetings: async ({ search = "", status = "ALL", type = "ALL", format = "ALL", page = 1, perPage = 10 } = {}) => {
    // Giả lập độ trễ mạng nhẹ cho giao diện phản hồi
    await new Promise((r) => setTimeout(r, 150));

    let results = [...MEETINGS_STORAGE];

    if (search.trim()) {
      const q = search.toLowerCase();
      results = results.filter(
        (m) =>
          m.title.toLowerCase().includes(q) ||
          m.id.toLowerCase().includes(q) ||
          m.location.toLowerCase().includes(q) ||
          m.chairperson.toLowerCase().includes(q)
      );
    }

    if (status !== "ALL") {
      results = results.filter((m) => m.status === status);
    }

    if (type !== "ALL") {
      results = results.filter((m) => m.meeting_type === type);
    }

    if (format !== "ALL") {
      results = results.filter((m) => m.meeting_format === format);
    }

    const total = results.length;
    const startIndex = (page - 1) * perPage;
    const data = results.slice(startIndex, startIndex + perPage);

    return {
      success: true,
      data,
      meta: {
        page,
        per_page: perPage,
        total,
        total_pages: Math.ceil(total / perPage) || 1
      }
    };
  },

  // Lấy chi tiết phiên họp theo ID
  getMeetingById: async (id) => {
    await new Promise((r) => setTimeout(r, 120));
    const meeting = MEETINGS_STORAGE.find((m) => m.id === id);
    if (!meeting) {
      return { success: false, message: "Không tìm thấy phiên họp" };
    }
    return { success: true, data: { ...meeting } };
  },

  // Tạo mới phiên họp
  createMeeting: async (payload) => {
    await new Promise((r) => setTimeout(r, 200));

    const newMeeting = {
      id: payload.id || `PH-2026-K4-${String(MEETINGS_STORAGE.length + 1).padStart(2, "0")}`,
      title: payload.title,
      term: payload.term || "Khóa IV (2020-2025)",
      meeting_type: payload.meeting_type || "REGULAR",
      meeting_type_label: payload.meeting_type === "REGULAR" ? "Họp định kỳ" : "Họp chuyên đề",
      meeting_format: payload.meeting_format || "IN_PERSON",
      meeting_format_label:
        payload.meeting_format === "IN_PERSON"
          ? "Trực tiếp"
          : payload.meeting_format === "ONLINE"
          ? "Trực tuyến"
          : "Kết hợp",
      meeting_date: payload.meeting_date,
      start_time: payload.start_time,
      end_time: payload.end_time || null,
      location: payload.location,
      chairperson: payload.chairperson || "PGS.TS. Nguyễn Văn Chủ Tịch",
      secretary: payload.secretary || "TS. Trần Văn Thư Ký",
      description: payload.description || "",
      status: "PREPARING",
      status_label: "Đang chuẩn bị",
      agendas: [],
      attendees: [],
      documents: [],
      votings: []
    };

    MEETINGS_STORAGE.unshift(newMeeting);
    return { success: true, data: newMeeting, message: "Tạo phiên họp mới thành công!" };
  },

  // Cập nhật thông tin phiên họp
  updateMeeting: async (id, payload) => {
    await new Promise((r) => setTimeout(r, 150));
    const index = MEETINGS_STORAGE.findIndex((m) => m.id === id);
    if (index === -1) {
      return { success: false, message: "Không tìm thấy phiên họp để cập nhật" };
    }

    MEETINGS_STORAGE[index] = {
      ...MEETINGS_STORAGE[index],
      ...payload,
      meeting_type_label:
        payload.meeting_type === "REGULAR"
          ? "Họp định kỳ"
          : payload.meeting_type === "EXTRAORDINARY"
          ? "Họp chuyên đề"
          : MEETINGS_STORAGE[index].meeting_type_label,
      meeting_format_label:
        payload.meeting_format === "IN_PERSON"
          ? "Trực tiếp"
          : payload.meeting_format === "ONLINE"
          ? "Trực tuyến"
          : payload.meeting_format === "HYBRID"
          ? "Kết hợp"
          : MEETINGS_STORAGE[index].meeting_format_label
    };

    return { success: true, data: MEETINGS_STORAGE[index], message: "Cập nhật phiên họp thành công!" };
  },

  // Xóa phiên họp (khi chưa bế mạc)
  deleteMeeting: async (id) => {
    await new Promise((r) => setTimeout(r, 150));
    MEETINGS_STORAGE = MEETINGS_STORAGE.filter((m) => m.id !== id);
    return { success: true, message: "Đã xóa phiên họp thành công!" };
  },

  // Thêm mục vào chương trình họp (Agenda)
  addAgendaItem: async (meetingId, agendaData) => {
    await new Promise((r) => setTimeout(r, 150));
    const meeting = MEETINGS_STORAGE.find((m) => m.id === meetingId);
    if (!meeting) return { success: false, message: "Không tìm thấy phiên họp" };

    const newItem = {
      id: `ag-${Date.now()}`,
      item_order: meeting.agendas.length + 1,
      title: agendaData.title,
      presenter: agendaData.presenter || "Đang cập nhật",
      duration_minutes: Number(agendaData.duration_minutes) || 30,
      dossier_id: agendaData.dossier_id || null,
      dossier_title: agendaData.dossier_title || null,
      is_voting_required: Boolean(agendaData.is_voting_required),
      status: "PENDING"
    };

    meeting.agendas.push(newItem);
    return { success: true, data: newItem, message: "Đã thêm mục vào chương trình họp!" };
  },

  // Xóa mục khỏi chương trình họp
  deleteAgendaItem: async (meetingId, agendaId) => {
    await new Promise((r) => setTimeout(r, 100));
    const meeting = MEETINGS_STORAGE.find((m) => m.id === meetingId);
    if (!meeting) return { success: false, message: "Không tìm thấy phiên họp" };

    meeting.agendas = meeting.agendas.filter((a) => a.id !== agendaId);
    // Tái sắp xếp lại số thứ tự
    meeting.agendas.forEach((a, idx) => {
      a.item_order = idx + 1;
    });

    return { success: true, message: "Đã gỡ mục khỏi chương trình họp!" };
  },

  // Thêm khách mời tham gia phiên họp
  addGuestAttendee: async (meetingId, guestData) => {
    await new Promise((r) => setTimeout(r, 150));
    const meeting = MEETINGS_STORAGE.find((m) => m.id === meetingId);
    if (!meeting) return { success: false, message: "Không tìm thấy phiên họp" };

    const newGuest = {
      id: `att-${Date.now()}`,
      user_id: Date.now(),
      full_name: guestData.full_name,
      academic_title: guestData.academic_title || "",
      role_in_council: "Khách mời chuyên môn",
      department: guestData.department,
      attendee_type: "GUEST",
      can_vote: false, // Bắt buộc false đối với khách mời
      attendance_status: "INVITED",
      attendance_label: "Đã mời",
      checkin_time: null,
      notes: guestData.notes || ""
    };

    meeting.attendees.push(newGuest);
    return { success: true, data: newGuest, message: "Đã thêm khách mời thành công!" };
  },

  // Cập nhật điểm danh đại biểu
  updateAttendanceStatus: async (meetingId, attendeeId, status) => {
    await new Promise((r) => setTimeout(r, 100));
    const meeting = MEETINGS_STORAGE.find((m) => m.id === meetingId);
    if (!meeting) return { success: false, message: "Không tìm thấy phiên họp" };

    const att = meeting.attendees.find((a) => a.id === attendeeId);
    if (!att) return { success: false, message: "Không tìm thấy đại biểu" };

    att.attendance_status = status;
    att.attendance_label =
      status === "PRESENT"
        ? "Có mặt"
        : status === "ABSENT"
        ? "Vắng mặt"
        : status === "EXCUSED"
        ? "Vắng có lý do"
        : "Đã mời";

    if (status === "PRESENT") {
      att.checkin_time = new Date().toISOString().replace("T", " ").substring(0, 16);
      // Chỉ thành viên HĐT chính thức mới được cấp quyền can_vote
      att.can_vote = att.attendee_type === "OFFICIAL";
    } else {
      att.checkin_time = null;
      att.can_vote = false;
    }

    return { success: true, message: `Cập nhật điểm danh: ${att.full_name} -> ${att.attendance_label}` };
  },

  // Tải lên tài liệu phiên họp
  uploadDocument: async (meetingId, docData) => {
    await new Promise((r) => setTimeout(r, 150));
    const meeting = MEETINGS_STORAGE.find((m) => m.id === meetingId);
    if (!meeting) return { success: false, message: "Không tìm thấy phiên họp" };

    const newDoc = {
      id: `doc-${Date.now()}`,
      file_name: docData.file_name,
      file_size: docData.file_size || 1024000,
      file_type: docData.file_name.split(".").pop().toLowerCase(),
      access_level: docData.access_level || "INTERNAL",
      access_level_label: docData.access_level === "INTERNAL" ? "Nội bộ HĐT" : "Công khai cho đại biểu",
      uploaded_at: new Date().toISOString().replace("T", " ").substring(0, 16),
      uploaded_by: "TS. Trần Văn Thư Ký"
    };

    meeting.documents.push(newDoc);
    return { success: true, data: newDoc, message: "Đã tải lên tài liệu phiên họp!" };
  },

  // Xóa tài liệu phiên họp
  deleteDocument: async (meetingId, docId) => {
    await new Promise((r) => setTimeout(r, 100));
    const meeting = MEETINGS_STORAGE.find((m) => m.id === meetingId);
    if (!meeting) return { success: false, message: "Không tìm thấy phiên họp" };

    meeting.documents = meeting.documents.filter((d) => d.id !== docId);
    return { success: true, message: "Đã xóa tài liệu phiên họp!" };
  },

  // Chuyển đổi trạng thái phiên họp (Vòng đời)
  changeMeetingStatus: async (id, newStatus) => {
    await new Promise((r) => setTimeout(r, 150));
    const meeting = MEETINGS_STORAGE.find((m) => m.id === id);
    if (!meeting) return { success: false, message: "Không tìm thấy phiên họp" };

    const statusMap = {
      PREPARING: "Đang chuẩn bị",
      SCHEDULED: "Đã triệu tập",
      IN_PROGRESS: "Đang diễn ra",
      COMPLETED: "Đã bế mạc",
      CANCELLED: "Đã hủy"
    };

    meeting.status = newStatus;
    meeting.status_label = statusMap[newStatus] || newStatus;

    return {
      success: true,
      data: { ...meeting },
      message: `Đã chuyển trạng thái phiên họp sang "${meeting.status_label}"!`
    };
  },

  // Gửi giấy triệu tập phiên họp
  sendInvitations: async (id) => {
    await new Promise((r) => setTimeout(r, 200));
    const meeting = MEETINGS_STORAGE.find((m) => m.id === id);
    if (!meeting) return { success: false, message: "Không tìm thấy phiên họp" };

    meeting.status = "SCHEDULED";
    meeting.status_label = "Đã triệu tập";

    meeting.attendees.forEach((att) => {
      if (!att.attendance_status || att.attendance_status === "PENDING") {
        att.attendance_status = "INVITED";
        att.attendance_label = "Đã mời";
      }
    });

    return {
      success: true,
      data: { ...meeting },
      message: "Đã gửi giấy mời điện tử và mở quyền truy cập tài liệu cho toàn thể đại biểu!"
    };
  }
};

export default meetingService;
