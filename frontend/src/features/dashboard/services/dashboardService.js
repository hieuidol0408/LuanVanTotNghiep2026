/**
 * SERVICE - BẢNG ĐIỀU HÀNH TỔNG QUAN (DASHBOARD)
 *
 * Kết nối trực tiếp với Backend RESTful API (/api/v1/dashboard/summary) trên WampServer MySQL
 */
import apiClient from "../../../services/apiClient";

export const dashboardService = {
  // Lấy toàn bộ dữ liệu thống kê thời gian thực từ 16 bảng MySQL
  getSummary: async () => {
    try {
      const res = await apiClient.get("/dashboard/summary");
      return res;
    } catch (err) {
      console.warn("Lỗi khi tải dữ liệu tổng quan dashboard:", err.message);
      return { success: false, data: null, message: err.message };
    }
  },
};

export default dashboardService;
