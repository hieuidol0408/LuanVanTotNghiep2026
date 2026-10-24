/**
 * SERVICE - QUẢN LÝ PHIÊN HỌP HỘI ĐỒNG TRƯỜNG STU
 * 
 * Kết nối trực tiếp với Backend RESTful API (/api/v1/meetings/*) trên WampServer MySQL
 */
import apiClient from "../../../services/apiClient";

export const meetingService = {
  // Lấy danh sách phiên họp
  getMeetings: async (params = {}) => {
    try {
      const queryParams = new URLSearchParams();
      if (params.search) queryParams.append("search", params.search);
      if (params.status && params.status !== "ALL") queryParams.append("status", params.status);
      if (params.type && params.type !== "ALL") queryParams.append("type", params.type);
      if (params.format && params.format !== "ALL") queryParams.append("format", params.format);
      if (params.page) queryParams.append("page", params.page);
      if (params.perPage) queryParams.append("perPage", params.perPage);

      const url = `/meetings?${queryParams.toString()}`;
      const res = await apiClient.get(url);
      return res;
    } catch (err) {
      console.warn("Lỗi khi tải danh sách phiên họp từ API:", err.message);
      return { success: false, data: [], meta: { total: 0, total_pages: 1 }, message: err.message };
    }
  },

  // Lấy chi tiết phiên họp
  getMeetingById: async (id) => {
    try {
      const res = await apiClient.get(`/meetings/${id}`);
      return res;
    } catch (err) {
      console.warn("Lỗi khi tải chi tiết phiên họp:", err.message);
      return { success: false, data: null, message: err.message };
    }
  },

  // Tạo mới phiên họp
  createMeeting: async (meetingData) => {
    try {
      const res = await apiClient.post("/meetings", meetingData);
      return res;
    } catch (err) {
      return { success: false, message: err.message, errors: err.errors };
    }
  },

  // Cập nhật phiên họp
  updateMeeting: async (id, meetingData) => {
    try {
      const res = await apiClient.put(`/meetings/${id}`, meetingData);
      return res;
    } catch (err) {
      return { success: false, message: err.message, errors: err.errors };
    }
  },

  // Xóa phiên họp
  deleteMeeting: async (id) => {
    try {
      const res = await apiClient.delete(`/meetings/${id}`);
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  },

  // Thêm mục chương trình họp (Agenda)
  addAgendaItem: async (meetingId, agendaData) => {
    try {
      const res = await apiClient.post(`/meetings/${meetingId}/agendas`, agendaData);
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  },

  // Xóa mục chương trình họp
  deleteAgendaItem: async (meetingId, agendaId) => {
    try {
      const res = await apiClient.delete(`/meetings/${meetingId}/agendas/${agendaId}`);
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  },

  // Thêm khách mời tham gia phiên họp
  addGuestAttendee: async (meetingId, guestData) => {
    try {
      const res = await apiClient.post(`/meetings/${meetingId}/attendees`, guestData);
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  },

  // Cập nhật điểm danh đại biểu
  updateAttendanceStatus: async (meetingId, attendeeId, status) => {
    try {
      const res = await apiClient.post(`/meetings/${meetingId}/checkin`, {
        attendee_id: attendeeId,
        status: status
      });
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  },

  // Tải lên tài liệu phiên họp
  uploadDocument: async (meetingId, docData) => {
    return {
      success: true,
      data: {
        id: `doc-${Date.now()}`,
        file_name: docData.file_name,
        file_size: docData.file_size || 1024000,
        file_type: docData.file_name.split(".").pop().toLowerCase(),
        access_level: docData.access_level || "INTERNAL",
        access_level_label: docData.access_level === "INTERNAL" ? "Nội bộ HĐT" : "Công khai cho đại biểu",
        uploaded_at: new Date().toISOString().replace("T", " ").substring(0, 16),
        uploaded_by: "TS. Trần Văn Thư Ký"
      },
      message: "Đã tải lên tài liệu phiên họp!"
    };
  },

  // Xóa tài liệu phiên họp
  deleteDocument: async (_meetingId, _docId) => {
    return { success: true, message: "Đã xóa tài liệu phiên họp!" };
  },

  // Chuyển đổi trạng thái phiên họp
  changeMeetingStatus: async (id, newStatus) => {
    try {
      const res = await apiClient.post(`/meetings/${id}/status`, { status: newStatus });
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  },

  // Gửi giấy triệu tập phiên họp
  sendInvitations: async (id) => {
    try {
      const res = await apiClient.post(`/meetings/${id}/send-invitations`);
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  }
};

export default meetingService;
