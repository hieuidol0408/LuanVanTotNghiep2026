/**
 * SERVICE - QUẢN LÝ HỒ SƠ TRÌNH HỘI ĐỒNG TRƯỜNG STU
 * 
 * Kết nối trực tiếp với Backend RESTful API (/api/v1/dossiers/*) trên WampServer MySQL
 */
import apiClient from "../../../services/apiClient";

export const dossierService = {
  // Lấy danh sách hồ sơ trình
  getDossiers: async (params = {}) => {
    try {
      const queryParams = new URLSearchParams();
      if (params.search) queryParams.append("search", params.search);
      if (params.status && params.status !== "ALL") queryParams.append("status", params.status);
      if (params.department_id && params.department_id !== "ALL") queryParams.append("department_id", params.department_id);
      if (params.page) queryParams.append("page", params.page);
      if (params.perPage) queryParams.append("perPage", params.perPage);

      const url = `/dossiers?${queryParams.toString()}`;
      const res = await apiClient.get(url);
      return res;
    } catch (err) {
      console.warn("Lỗi khi tải danh sách hồ sơ trình:", err.message);
      return { success: false, data: [], meta: { total: 0, total_pages: 1 }, message: err.message };
    }
  },

  // Lấy chi tiết hồ sơ trình
  getDossierById: async (id) => {
    try {
      const res = await apiClient.get(`/dossiers/${id}`);
      return res;
    } catch (err) {
      console.warn("Lỗi khi tải chi tiết hồ sơ trình:", err.message);
      return { success: false, data: null, message: err.message };
    }
  },

  // Tạo mới hồ sơ trình (DRAFT)
  createDossier: async (dossierData) => {
    try {
      const res = await apiClient.post("/dossiers", dossierData);
      return res;
    } catch (err) {
      return { success: false, message: err.message, errors: err.errors };
    }
  },

  // Cập nhật hồ sơ trình
  updateDossier: async (id, dossierData) => {
    try {
      const res = await apiClient.put(`/dossiers/${id}`, dossierData);
      return res;
    } catch (err) {
      return { success: false, message: err.message, errors: err.errors };
    }
  },

  // Nộp hồ sơ chính thức lên Thư ký HĐT
  submitDossier: async (id) => {
    try {
      const res = await apiClient.post(`/dossiers/${id}/submit`);
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  },

  // Thẩm tra hồ sơ trình (Duyệt đạt / Yêu cầu sửa / Từ chối)
  reviewDossier: async (id, reviewData) => {
    try {
      const res = await apiClient.post(`/dossiers/${id}/review`, reviewData);
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  },

  // Xóa hồ sơ trình (chỉ khi đang ở trạng thái DRAFT)
  deleteDossier: async (id) => {
    try {
      const res = await apiClient.delete(`/dossiers/${id}`);
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  }
};

export default dossierService;
