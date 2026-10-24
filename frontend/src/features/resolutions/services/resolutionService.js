/**
 * SERVICE - QUẢN LÝ BIÊN BẢN & NGHỊ QUYẾT HỘI ĐỒNG TRƯỜNG STU
 * 
 * Kết nối trực tiếp với Backend RESTful API (/api/v1/resolutions/* & /api/v1/meetings/{id}/minutes) trên WampServer MySQL
 */
import apiClient from "../../../services/apiClient";

export const resolutionService = {
  // Lấy danh sách Nghị quyết HĐT
  getResolutions: async (params = {}) => {
    try {
      const queryParams = new URLSearchParams();
      if (params.search) queryParams.append("search", params.search);
      if (params.status && params.status !== "ALL") queryParams.append("status", params.status);
      if (params.meeting_id && params.meeting_id !== "ALL") queryParams.append("meeting_id", params.meeting_id);
      if (params.year && params.year !== "ALL") queryParams.append("year", params.year);
      if (params.page) queryParams.append("page", params.page);
      if (params.perPage) queryParams.append("perPage", params.perPage);

      const url = `/resolutions?${queryParams.toString()}`;
      const res = await apiClient.get(url);
      return res;
    } catch (err) {
      console.warn("Lỗi khi tải danh sách Nghị quyết:", err.message);
      return { success: false, data: [], meta: { total: 0, total_pages: 1 }, message: err.message };
    }
  },

  // Lấy chi tiết Nghị quyết kèm danh sách nhiệm vụ phân rã
  getResolutionById: async (id) => {
    try {
      const res = await apiClient.get(`/resolutions/${id}`);
      return res;
    } catch (err) {
      console.warn("Lỗi khi tải chi tiết Nghị quyết:", err.message);
      return { success: false, data: null, message: err.message };
    }
  },

  // Tạo mới Nghị quyết (Dự thảo)
  createResolution: async (resolutionData) => {
    try {
      const res = await apiClient.post("/resolutions", resolutionData);
      return res;
    } catch (err) {
      return { success: false, message: err.message, errors: err.errors };
    }
  },

  // Cập nhật thông tin Nghị quyết
  updateResolution: async (id, resolutionData) => {
    try {
      const res = await apiClient.put(`/resolutions/${id}`, resolutionData);
      return res;
    } catch (err) {
      return { success: false, message: err.message, errors: err.errors };
    }
  },

  // Chủ tịch HĐT ký ban hành (SIGNED)
  signResolution: async (id) => {
    try {
      const res = await apiClient.post(`/resolutions/${id}/sign`);
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  },

  // Công bố chính thức Nghị quyết (PUBLISHED)
  publishResolution: async (id) => {
    try {
      const res = await apiClient.post(`/resolutions/${id}/publish`);
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  },

  // Xóa dự thảo Nghị quyết (DRAFT)
  deleteResolution: async (id) => {
    try {
      const res = await apiClient.delete(`/resolutions/${id}`);
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  },

  // Lấy biên bản phiên họp
  getMeetingMinutes: async (meetingId) => {
    try {
      const res = await apiClient.get(`/meetings/${meetingId}/minutes`);
      return res;
    } catch (err) {
      return { success: false, data: null, message: err.message };
    }
  },

  // Lưu/cập nhật dự thảo biên bản phiên họp
  saveMeetingMinutes: async (meetingId, content) => {
    try {
      const res = await apiClient.post(`/meetings/${meetingId}/minutes`, { content });
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  },

  // Chủ tịch phê duyệt biên bản phiên họp
  approveMeetingMinutes: async (meetingId) => {
    try {
      const res = await apiClient.post(`/meetings/${meetingId}/minutes/approve`);
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  },
};

export default resolutionService;
