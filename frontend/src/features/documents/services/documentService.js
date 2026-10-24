/**
 * SERVICE - QUẢN LÝ KHO VĂN BẢN & TÀI LIỆU AN TOÀN HỘI ĐỒNG TRƯỜNG STU
 * 
 * Kết nối trực tiếp với Backend RESTful API (/api/v1/documents/*) trên WampServer MySQL
 */
import apiClient from "../../../services/apiClient";

export const documentService = {
  // Lấy danh sách tài liệu
  getDocuments: async (params = {}) => {
    try {
      const queryParams = new URLSearchParams();
      if (params.search) queryParams.append("search", params.search);
      if (params.type && params.type !== "ALL") queryParams.append("type", params.type);
      if (params.meeting_id && params.meeting_id !== "ALL") queryParams.append("meeting_id", params.meeting_id);
      if (params.resolution_id && params.resolution_id !== "ALL") queryParams.append("resolution_id", params.resolution_id);
      if (params.page) queryParams.append("page", params.page);
      if (params.perPage) queryParams.append("perPage", params.perPage);

      const url = `/documents?${queryParams.toString()}`;
      const res = await apiClient.get(url);
      return res;
    } catch (err) {
      console.warn("Lỗi khi tải danh sách tài liệu:", err.message);
      return { success: false, data: [], meta: { total: 0, total_pages: 1 }, message: err.message };
    }
  },

  // Tải lên tài liệu mới
  uploadDocument: async (docData) => {
    try {
      const res = await apiClient.post("/documents", docData);
      return res;
    } catch (err) {
      return { success: false, message: err.message, errors: err.errors };
    }
  },

  // Tải tệp an toàn (Safe Download)
  downloadDocument: async (id, fileName = "tai_lieu.pdf") => {
    try {
      // Dùng fetch với auth header để nhận blob
      const token = localStorage.getItem("stu_auth_token");
      const baseURL = import.meta.env.VITE_API_BASE_URL || "/api/v1";
      const response = await fetch(`${baseURL}/documents/${id}/download`, {
        headers: {
          Authorization: token ? `Bearer ${token}` : "",
          Accept: "application/json, application/pdf, */*",
        },
      });

      if (!response.ok) {
        if (response.status === 403) {
          throw new Error("Bạn không có quyền truy cập tài liệu nội bộ bảo mật của Hội đồng trường.");
        }
        throw new Error("Không thể tải tệp từ máy chủ.");
      }

      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = fileName;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
      document.body.removeChild(a);

      return { success: true };
    } catch (err) {
      return { success: false, message: err.message };
    }
  },

  // Xóa tài liệu
  deleteDocument: async (id) => {
    try {
      const res = await apiClient.delete(`/documents/${id}`);
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  },
};

export default documentService;
