/**
 * SERVICE - PHÂN RÃ NHIỆM VỤ & THEO DÕI THỰC HIỆN HỘI ĐỒNG TRƯỜNG STU
 * 
 * Kết nối trực tiếp với Backend RESTful API (/api/v1/tasks/*) trên WampServer MySQL
 */
import apiClient from "../../../services/apiClient";

export const taskService = {
  // Lấy danh sách nhiệm vụ phân rã
  getTasks: async (params = {}) => {
    try {
      const queryParams = new URLSearchParams();
      if (params.search) queryParams.append("search", params.search);
      if (params.status && params.status !== "ALL") queryParams.append("status", params.status);
      if (params.department_id && params.department_id !== "ALL") queryParams.append("department_id", params.department_id);
      if (params.resolution_id && params.resolution_id !== "ALL") queryParams.append("resolution_id", params.resolution_id);
      if (params.is_overdue) queryParams.append("is_overdue", "true");
      if (params.page) queryParams.append("page", params.page);
      if (params.perPage) queryParams.append("perPage", params.perPage);

      const url = `/tasks?${queryParams.toString()}`;
      const res = await apiClient.get(url);
      return res;
    } catch (err) {
      console.warn("Lỗi khi tải danh sách nhiệm vụ:", err.message);
      return { success: false, data: [], meta: { total: 0, total_pages: 1 }, message: err.message };
    }
  },

  // Lấy chi tiết nhiệm vụ kèm nhật ký tiến độ & minh chứng
  getTaskById: async (id) => {
    try {
      const res = await apiClient.get(`/tasks/${id}`);
      return res;
    } catch (err) {
      console.warn("Lỗi khi tải chi tiết nhiệm vụ:", err.message);
      return { success: false, data: null, message: err.message };
    }
  },

  // Phân rã nhiệm vụ mới từ Nghị quyết
  createTask: async (taskData) => {
    try {
      const res = await apiClient.post("/tasks", taskData);
      return res;
    } catch (err) {
      return { success: false, message: err.message, errors: err.errors };
    }
  },

  // Điều chỉnh phân công nhiệm vụ
  updateTask: async (id, taskData) => {
    try {
      const res = await apiClient.put(`/tasks/${id}`, taskData);
      return res;
    } catch (err) {
      return { success: false, message: err.message, errors: err.errors };
    }
  },

  // Xóa nhiệm vụ
  deleteTask: async (id) => {
    try {
      const res = await apiClient.delete(`/tasks/${id}`);
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  },

  // Đơn vị nộp báo cáo tiến độ (%) và minh chứng
  submitProgressLog: async (taskId, reportData) => {
    try {
      const res = await apiClient.post(`/tasks/${taskId}/progress-logs`, reportData);
      return res;
    } catch (err) {
      return { success: false, message: err.message, errors: err.errors };
    }
  },

  // Thư ký / Chủ tịch nghiệm thu báo cáo tiến độ
  reviewProgressLog: async (logId, reviewData) => {
    try {
      const res = await apiClient.post(`/tasks/progress-logs/${logId}/review`, reviewData);
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  },
};

export default taskService;
