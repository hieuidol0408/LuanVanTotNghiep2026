/**
 * AUTH SERVICE - QUẢN LÝ XÁC THỰC VÀ PHÂN QUYỀN HỘI ĐỒNG TRƯỜNG STU
 * 
 * LƯU Ý KỸ THUẬT & BẢO MẬT:
 * 1. Tệp này cung cấp cơ chế quản lý trạng thái phiên đăng nhập (Session/Token) cho React Frontend.
 * 2. Trong môi trường kiểm thử/luận văn, dữ liệu phiên được đồng bộ qua LocalStorage/SessionStorage.
 * 3. Khi kết nối Backend Laravel thực tế, mọi tác vụ đăng nhập sẽ gọi endpoint `/api/v1/auth/login`
 *    nhận Bearer Token (Laravel Sanctum), và Backend sẽ kiểm tra chặt chẽ Authorization trên từng API.
 * 4. Ẩn nút trên giao diện chỉ nhằm tối ưu trải nghiệm người dùng, KHÔNG THỂ thay thế lớp bảo vệ
 *    ở tầng Backend (Laravel Gate/Policy).
 * 5. KHÔNG tạo tài khoản mặc định cố định trong môi trường Production.
 */

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
   * Đăng nhập hệ thống (Prototype mô phỏng API `/api/v1/auth/login`)
   */
  login: async (email, password, rememberMe = true) => {
    // Giả lập độ trễ mạng thực tế 250ms
    await new Promise((r) => setTimeout(r, 250));

    if (!email || !password) {
      return {
        success: false,
        message: "Vui lòng nhập đầy đủ Email và Mật khẩu để đăng nhập."
      };
    }

    const cleanEmail = email.trim().toLowerCase();

    // Tìm tài khoản phù hợp trong danh mục vai trò
    const matchedRole = PROTOTYPE_ROLES.find(
      (r) => r.email.toLowerCase() === cleanEmail
    );

    // Mật khẩu mặc định kiểm thử là stu@2026 hoặc password123
    const validPasswords = ["stu@2026", "password123", "admin123"];

    if (!matchedRole || !validPasswords.includes(password)) {
      // Trường hợp nhập sai thông tin
      return {
        success: false,
        message: "Thông tin email hoặc mật khẩu không chính xác. Vui lòng kiểm tra lại."
      };
    }

    // Tạo payload người dùng kèm thông tin vai trò và thẩm quyền
    const permissions = ROLE_PERMISSIONS[matchedRole.key] || ROLE_PERMISSIONS.MEMBER;
    const userPayload = {
      ...matchedRole,
      id: `usr-${matchedRole.key.toLowerCase()}`,
      permissions
    };

    const mockToken = `stu_bearer_tok_${btoa(matchedRole.key + "_" + Date.now())}`;

    // Lưu vào Storage tương ứng
    const storage = rememberMe ? localStorage : sessionStorage;
    storage.setItem(STORAGE_KEY_TOKEN, mockToken);
    storage.setItem(STORAGE_KEY_USER, JSON.stringify(userPayload));

    return {
      success: true,
      token: mockToken,
      user: userPayload,
      message: `Đăng nhập thành công! Chào mừng ${userPayload.user_name}.`
    };
  },

  /**
   * Đăng xuất hệ thống (Hủy bỏ phiên làm việc)
   */
  logout: async () => {
    await new Promise((r) => setTimeout(r, 100));
    localStorage.removeItem(STORAGE_KEY_TOKEN);
    localStorage.removeItem(STORAGE_KEY_USER);
    sessionStorage.removeItem(STORAGE_KEY_TOKEN);
    sessionStorage.removeItem(STORAGE_KEY_USER);
    return { success: true };
  },

  /**
   * Lấy thông tin người dùng đang đăng nhập từ bộ nhớ
   */
  getCurrentUser: () => {
    try {
      const stored = localStorage.getItem(STORAGE_KEY_USER) || sessionStorage.getItem(STORAGE_KEY_USER);
      if (stored) {
        return JSON.parse(stored);
      }
    } catch (e) {
      console.error("Lỗi khi đọc phiên đăng nhập:", e);
    }
    // Mặc định trả về Thư ký HĐT nếu chưa đăng nhập lần nào trong chế độ prototype
    const defaultRole = PROTOTYPE_ROLES[2]; // SECRETARY
    return {
      ...defaultRole,
      id: `usr-${defaultRole.key.toLowerCase()}`,
      permissions: ROLE_PERMISSIONS[defaultRole.key]
    };
  },

  /**
   * Lấy Bearer Token hiện tại
   */
  getToken: () => {
    return localStorage.getItem(STORAGE_KEY_TOKEN) || sessionStorage.getItem(STORAGE_KEY_TOKEN) || "stu_prototype_token";
  },

  /**
   * Kiểm tra người dùng đã xác thực hay chưa
   */
  isAuthenticated: () => {
    const token = localStorage.getItem(STORAGE_KEY_TOKEN) || sessionStorage.getItem(STORAGE_KEY_TOKEN);
    return Boolean(token);
  },

  /**
   * Chuyển đổi nhanh vai trò (Role Switcher Prototype)
   */
  switchRole: (roleKey) => {
    const targetRole = PROTOTYPE_ROLES.find((r) => r.key === roleKey);
    if (!targetRole) return null;

    const permissions = ROLE_PERMISSIONS[targetRole.key] || ROLE_PERMISSIONS.MEMBER;
    const userPayload = {
      ...targetRole,
      id: `usr-${targetRole.key.toLowerCase()}`,
      permissions
    };

    const mockToken = `stu_bearer_tok_${btoa(targetRole.key + "_" + Date.now())}`;
    localStorage.setItem(STORAGE_KEY_TOKEN, mockToken);
    localStorage.setItem(STORAGE_KEY_USER, JSON.stringify(userPayload));

    return userPayload;
  },

  /**
   * Kiểm tra quyền truy cập URL theo vai trò
   */
  canAccessRoute: (roleKey, pathname) => {
    if (!roleKey) return false;
    const permissions = ROLE_PERMISSIONS[roleKey];
    if (!permissions) return false;

    // Chuẩn hóa path (ví dụ /meetings/create -> /meetings)
    const baseModule = "/" + pathname.split("/").filter(Boolean)[0] || "/";

    if (baseModule === "/") return true; // Mọi vai trò hợp lệ đều có quyền xem Bảng điều hành
    if (baseModule === "/components") return true; // Showcase chung

    return permissions.allowedRoutes.some((route) => {
      if (route === "/") return pathname === "/";
      return pathname === route || pathname.startsWith(route + "/");
    });
  }
};

export default authService;
