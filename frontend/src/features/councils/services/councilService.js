/**
 * SERVICE - QUẢN LÝ THÀNH VIÊN HỘI ĐỒNG TRƯỜNG & ĐƠN VỊ STU
 * 
 * Kết nối trực tiếp với Backend RESTful API (/api/v1/councils/* & /api/v1/departments) trên WampServer MySQL
 */
import apiClient from "../../../services/apiClient";

export const councilService = {
  // Lấy danh sách thành viên Hội đồng trường
  getCouncilMembers: async (params = {}) => {
    try {
      const queryParams = new URLSearchParams();
      if (params.term && params.term !== "ALL") queryParams.append("term", params.term);
      if (params.search) queryParams.append("search", params.search);

      const url = `/councils/members?${queryParams.toString()}`;
      const res = await apiClient.get(url);
      return res;
    } catch (err) {
      console.warn("Lỗi khi tải danh sách thành viên HĐT:", err.message);
      return { success: false, data: [], message: err.message };
    }
  },

  // Lấy danh sách các khóa nhiệm kỳ
  getCouncilTerms: async () => {
    try {
      const res = await apiClient.get("/councils/terms");
      return res;
    } catch (err) {
      return { success: false, data: ["Khóa IV (2020-2025)", "Khóa V (2025-2030)"], message: err.message };
    }
  },

  // Lấy danh sách các đơn vị / phòng ban
  getDepartments: async () => {
    try {
      const res = await apiClient.get("/departments");
      return res;
    } catch (err) {
      return { success: false, data: [], message: err.message };
    }
  },
};

export default councilService;
