/**
 * SERVICE - QUẢN LÝ TÀI KHOẢN NGƯỜI DÙNG & PHÂN QUYỀN (UC6)
 *
 * Kết nối trực tiếp với Backend RESTful API (/api/v1/users/*, /api/v1/roles)
 */
import apiClient from "../../../services/apiClient";

export const userService = {
  // Lấy danh sách tài khoản người dùng
  getUsers: async (params = {}) => {
    try {
      const queryParams = new URLSearchParams();
      if (params.search) queryParams.append("search", params.search);
      if (params.role && params.role !== "ALL") queryParams.append("role", params.role);
      if (params.department_id && params.department_id !== "ALL") queryParams.append("department_id", params.department_id);
      if (params.status && params.status !== "ALL") queryParams.append("status", params.status);
      if (params.page) queryParams.append("page", params.page);
      if (params.perPage) queryParams.append("perPage", params.perPage);

      const url = `/users?${queryParams.toString()}`;
      const res = await apiClient.get(url);
      return res;
    } catch (err) {
      console.warn("Lỗi khi tải danh sách người dùng:", err.message);
      return { success: false, data: [], meta: { total: 0, current_page: 1, last_page: 1 }, message: err.message };
    }
  },

  // Lấy danh mục các vai trò trong hệ thống
  getRoles: async () => {
    try {
      const res = await apiClient.get("/roles");
      return res;
    } catch (err) {
      console.warn("Lỗi khi lấy danh mục vai trò:", err.message);
      return { success: false, data: [] };
    }
  },

  // Tạo mới tài khoản người dùng
  createUser: async (userData) => {
    try {
      const res = await apiClient.post("/users", userData);
      return res;
    } catch (err) {
      return { success: false, message: err.message, errors: err.errors };
    }
  },

  // Chi tiết người dùng
  getUserById: async (id) => {
    try {
      const res = await apiClient.get(`/users/${id}`);
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  },

  // Cập nhật thông tin người dùng
  updateUser: async (id, userData) => {
    try {
      const res = await apiClient.put(`/users/${id}`, userData);
      return res;
    } catch (err) {
      return { success: false, message: err.message, errors: err.errors };
    }
  },

  // Khóa / Mở khóa tài khoản
  toggleUserStatus: async (id) => {
    try {
      const res = await apiClient.post(`/users/${id}/toggle-status`);
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  },

  // Đặt lại mật khẩu mặc định
  resetPassword: async (id, newPassword) => {
    try {
      const payload = newPassword ? { MatKhau: newPassword } : {};
      const res = await apiClient.post(`/users/${id}/reset-password`, payload);
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  },

  // Xóa / Vô hiệu hóa người dùng
  deleteUser: async (id) => {
    try {
      const res = await apiClient.delete(`/users/${id}`);
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  },
};
