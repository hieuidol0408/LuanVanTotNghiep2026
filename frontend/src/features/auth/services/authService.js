/**
 * AUTH SERVICE - TÍCH HỢP RESTFUL API BACKEND LARAVEL STU
 * 
 * LƯU Ý KỸ THUẬT:
 * 1. Gọi trực tiếp endpoint `/api/v1/auth/login`, `/api/v1/auth/me`, `/api/v1/auth/logout` thông qua apiClient.
 * 2. Lưu trữ Bearer Token (Laravel Sanctum) an toàn trong Storage và đính kèm vào mọi request tiếp theo.
 * 3. Đồng bộ đầy đủ thông tin vai trò, phòng ban và thẩm quyền (RBAC) trả về từ CSDL MySQL.
 */

import apiClient, { ValidationError, ApiError, NetworkError } from "../../../services/apiClient";
import { PROTOTYPE_ROLES } from "../../../mock/prototypeData";

// Danh mục ma trận phân quyền chi tiết cho 7 vai trò theo docs/business-rules.md
export const ROLE_PERMISSIONS = {
  ADMIN: {
    roleKey: "ADMIN",
    roleName: "Quản trị viên hệ thống",
    canVote: false, // TUYỆT ĐỐI KHÔNG CAN THIỆP VÀO LÁ PHIẾU BIỂU QUYẾT
    allowedRoutes: ["/", "/components", "/users", "/councils", "/documents", "/resolutions"],
    features: {
      manageUsers: true,
      manageRoles: true,
      manageCouncils: true,
      manageMeetings: false,
      manageDossiers: false,
      castVote: false,
      manageVoting: false,
      viewResolutions: true,
      manageResolutions: false,
      manageTasks: false,
      updateTaskProgress: false
    }
  },
  CHAIR: {
    roleKey: "CHAIR",
    roleName: "Chủ tịch Hội đồng trường",
    canVote: true, // CHỦ TỌA & BỎ PHIẾU
    allowedRoutes: ["/", "/components", "/councils", "/dossiers", "/meetings", "/voting", "/resolutions", "/tasks", "/documents"],
    features: {
      manageUsers: false,
      manageRoles: false,
      manageCouncils: true,
      manageMeetings: true,
      manageDossiers: true,
      castVote: true,
      manageVoting: true,
      viewResolutions: true,
      manageResolutions: true,
      manageTasks: true,
      updateTaskProgress: false
    }
  },
  SECRETARY: {
    roleKey: "SECRETARY",
    roleName: "Thư ký Hội đồng trường",
    canVote: true, // THƯ KÝ BỎ PHIẾU NẾU LÀ THÀNH VIÊN HĐT
    allowedRoutes: ["/", "/components", "/councils", "/dossiers", "/meetings", "/voting", "/resolutions", "/tasks", "/documents"],
    features: {
      manageUsers: false,
      manageRoles: false,
      manageCouncils: true,
      manageMeetings: true,
      manageDossiers: true,
      castVote: true,
      manageVoting: true,
      viewResolutions: true,
      manageResolutions: true,
      manageTasks: true,
      updateTaskProgress: false
    }
  },
  MEMBER: {
    roleKey: "MEMBER",
    roleName: "Thành viên Hội đồng trường",
    canVote: true, // QUYỀN BIỂU QUYẾT CHÍNH THỨC
    allowedRoutes: ["/", "/components", "/councils", "/meetings", "/voting", "/resolutions", "/tasks", "/documents"],
    features: {
      manageUsers: false,
      manageRoles: false,
      manageCouncils: false,
      manageMeetings: false,
      manageDossiers: false,
      castVote: true,
      manageVoting: false,
      viewResolutions: true,
      manageResolutions: false,
      manageTasks: false,
      updateTaskProgress: false
    }
  },
  RECTOR_OFFICE: {
    roleKey: "RECTOR_OFFICE",
    roleName: "Văn phòng Hiệu trưởng",
    canVote: false, // TUYỆT ĐỐI KHÔNG CÓ QUYỀN BIỂU QUYẾT
    allowedRoutes: ["/", "/components", "/resolutions", "/tasks", "/documents"],
    features: {
      manageUsers: false,
      manageRoles: false,
      manageCouncils: false,
      manageMeetings: false,
      manageDossiers: false,
      castVote: false,
      manageVoting: false,
      viewResolutions: true,
      manageResolutions: false,
      manageTasks: false,
      updateTaskProgress: false
    }
  },
  UNIT_REP: {
    roleKey: "UNIT_REP",
    roleName: "Đại diện Đơn vị trình hồ sơ",
    canVote: false, // TUYỆT ĐỐI KHÔNG CÓ QUYỀN BIỂU QUYẾT
    allowedRoutes: ["/", "/components", "/dossiers", "/resolutions", "/documents"],
    features: {
      manageUsers: false,
      manageRoles: false,
      manageCouncils: false,
      manageMeetings: false,
      manageDossiers: true,
      castVote: false,
      manageVoting: false,
      viewResolutions: true,
      manageResolutions: false,
      manageTasks: false,
      updateTaskProgress: false
    }
  },
  EXECUTING_UNIT: {
    roleKey: "EXECUTING_UNIT",
    roleName: "Đơn vị thực hiện Nghị quyết",
    canVote: false, // TUYỆT ĐỐI KHÔNG CÓ QUYỀN BIỂU QUYẾT
    allowedRoutes: ["/", "/components", "/tasks", "/resolutions", "/documents"],
    features: {
      manageUsers: false,
      manageRoles: false,
      manageCouncils: false,
      manageMeetings: false,
      manageDossiers: false,
      castVote: false,
      manageVoting: false,
      viewResolutions: true,
      manageResolutions: false,
      manageTasks: false,
      updateTaskProgress: true
    }
  }
};

const STORAGE_KEY_TOKEN = "stu_auth_token";
const STORAGE_KEY_USER = "stu_auth_user";

export const authService = {
  /**
   * Đăng nhập qua API Backend Laravel: POST /api/v1/auth/login
   */
  login: async (email, password, rememberMe = true) => {
    if (!email || !password) {
      return {
        success: false,
        message: "Vui lòng nhập đầy đủ Email và Mật khẩu để đăng nhập."
      };
    }

    try {
      const res = await apiClient.post("/auth/login", {
        email: email.trim().toLowerCase(),
        password: password
      });

      if (res.success && res.data?.token && res.data?.user) {
        const token = res.data.token;
        const user = {
          ...res.data.user,
          permissions: ROLE_PERMISSIONS[res.data.user.role] || ROLE_PERMISSIONS.MEMBER
        };

        const storage = rememberMe ? localStorage : sessionStorage;
        storage.setItem(STORAGE_KEY_TOKEN, token);
        storage.setItem(STORAGE_KEY_USER, JSON.stringify(user));

        return {
          success: true,
          token,
          user,
          message: res.message || `Đăng nhập thành công! Chào mừng ${user.user_name}.`
        };
      }

      return {
        success: false,
        message: res.message || "Đăng nhập không thành công."
      };
    } catch (error) {
      console.error("Lỗi xác thực đăng nhập API:", error);

      if (error instanceof ValidationError) {
        return {
          success: false,
          message: error.message,
          errors: error.errors
        };
      }

      return {
        success: false,
        message: error.message || "Đã xảy ra lỗi khi kết nối tới máy chủ xác thực."
      };
    }
  },

  /**
   * Lấy thông tin tài khoản hiện tại từ Backend: GET /api/v1/auth/me
   */
  fetchCurrentUser: async () => {
    try {
      const token = authService.getToken();
      if (!token) return null;

      const res = await apiClient.get("/auth/me");
      if (res.success && res.data?.user) {
        const user = {
          ...res.data.user,
          permissions: ROLE_PERMISSIONS[res.data.user.role] || ROLE_PERMISSIONS.MEMBER
        };
        localStorage.setItem(STORAGE_KEY_USER, JSON.stringify(user));
        return user;
      }
      return null;
    } catch (error) {
      console.warn("Không thể tải thông tin người dùng từ máy chủ:", error.message);
      return null;
    }
  },

  /**
   * Đăng xuất hệ thống: POST /api/v1/auth/logout
   */
  logout: async () => {
    try {
      await apiClient.post("/auth/logout");
    } catch (e) {
      console.warn("Lỗi khi gọi API đăng xuất:", e.message);
    } finally {
      localStorage.removeItem(STORAGE_KEY_TOKEN);
      localStorage.removeItem(STORAGE_KEY_USER);
      sessionStorage.removeItem(STORAGE_KEY_TOKEN);
      sessionStorage.removeItem(STORAGE_KEY_USER);
    }
    return { success: true };
  },

  /**
   * Lấy thông tin người dùng đang lưu trong bộ nhớ
   */
  getCurrentUser: () => {
    try {
      const stored = localStorage.getItem(STORAGE_KEY_USER) || sessionStorage.getItem(STORAGE_KEY_USER);
      if (stored) {
        return JSON.parse(stored);
      }
    } catch (e) {
      console.error("Lỗi khi đọc phiên đăng nhập từ storage:", e);
    }
    return null;
  },

  /**
   * Lấy Bearer Token hiện tại
   */
  getToken: () => {
    return localStorage.getItem(STORAGE_KEY_TOKEN) || sessionStorage.getItem(STORAGE_KEY_TOKEN) || null;
  },

  /**
   * Kiểm tra người dùng đã xác thực hay chưa
   */
  isAuthenticated: () => {
    return Boolean(authService.getToken());
  },

  /**
   * Chuyển đổi nhanh vai trò (Đăng nhập thật qua API với tài khoản vai trò tương ứng)
   */
  switchRole: async (roleKey) => {
    const targetRole = PROTOTYPE_ROLES.find((r) => r.key === roleKey);
    if (!targetRole) return null;

    const res = await authService.login(targetRole.email, "stu@2026", true);
    if (res.success) {
      return res.user;
    }
    return null;
  },

  /**
   * Kiểm tra quyền truy cập URL theo vai trò
   */
  canAccessRoute: (roleKey, pathname) => {
    if (!roleKey) return false;
    const permissions = ROLE_PERMISSIONS[roleKey];
    if (!permissions) return false;

    const baseModule = "/" + pathname.split("/").filter(Boolean)[0] || "/";

    if (baseModule === "/") return true;
    if (baseModule === "/components") return true;

    return permissions.allowedRoutes.some((route) => {
      if (route === "/") return pathname === "/";
      return pathname === route || pathname.startsWith(route + "/");
    });
  }
};

export default authService;
