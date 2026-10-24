/**
 * SERVICE - QUẢN LÝ BIỂU QUYẾT & KIỂM PHIẾU HỘI ĐỒNG TRƯỜNG STU
 * 
 * Kết nối trực tiếp với Backend RESTful API (/api/v1/votings/* & /api/v1/meetings/{id}/votings) trên WampServer MySQL
 */
import apiClient from "../../../services/apiClient";

export const votingService = {
  // Lấy danh sách nội dung biểu quyết của phiên họp
  getMeetingVotings: async (meetingId) => {
    try {
      const res = await apiClient.get(`/meetings/${meetingId}/votings`);
      return res;
    } catch (err) {
      console.warn("Lỗi khi tải danh sách biểu quyết phiên họp:", err.message);
      return { success: false, data: [], message: err.message };
    }
  },

  // Tạo nội dung biểu quyết mới liên kết với chương trình họp
  createVoting: async (meetingId, votingData) => {
    try {
      const res = await apiClient.post(`/meetings/${meetingId}/votings`, votingData);
      return res;
    } catch (err) {
      return { success: false, message: err.message, errors: err.errors };
    }
  },

  // Lấy chi tiết nội dung biểu quyết và tiến độ bỏ phiếu
  getVotingById: async (id) => {
    try {
      const res = await apiClient.get(`/votings/${id}`);
      return res;
    } catch (err) {
      console.warn("Lỗi khi tải chi tiết biểu quyết:", err.message);
      return { success: false, data: null, message: err.message };
    }
  },

  // Thư ký / Chủ tịch mở cổng biểu quyết (OPENING)
  openVoting: async (id) => {
    try {
      const res = await apiClient.post(`/votings/${id}/open`);
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  },

  // Thành viên Hội đồng trường gửi lá phiếu biểu quyết
  castVote: async (id, voteData) => {
    try {
      const res = await apiClient.post(`/votings/${id}/vote`, voteData);
      return res;
    } catch (err) {
      return { success: false, message: err.message, errors: err.errors };
    }
  },

  // Thư ký / Chủ tịch đóng biểu quyết và tự động tổng hợp kết quả (PASSED / REJECTED)
  closeVoting: async (id) => {
    try {
      const res = await apiClient.post(`/votings/${id}/close`);
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  },

  // Lấy danh sách giấy ủy quyền của phiên họp (Bảng 14 - uy_quyen_bieu_quyet)
  getAuthorizations: async (meetingId) => {
    try {
      const res = await apiClient.get(`/meetings/${meetingId}/authorizations`);
      return res;
    } catch (err) {
      console.warn("Lỗi khi tải danh sách ủy quyền:", err.message);
      return { success: false, data: [] };
    }
  },

  // Nộp văn bản ủy quyền cho phiên họp
  createAuthorization: async (meetingId, authData) => {
    try {
      const res = await apiClient.post(`/meetings/${meetingId}/authorizations`, authData);
      return res;
    } catch (err) {
      return { success: false, message: err.message, errors: err.errors };
    }
  },
};

export default votingService;
