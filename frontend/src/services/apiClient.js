/**
 * API CLIENT - DÙNG CHUNG CHO FRONTEND REACT.JS
 * 
 * LƯU Ý KỸ THUẬT:
 * 1. Sử dụng biến môi trường `VITE_API_BASE_URL` (mặc định: http://127.0.0.1:8000/api/v1).
 * 2. Tự động đính kèm Bearer Token (Laravel Sanctum) từ storage vào header Authorization.
 * 3. Chuẩn hóa xử lý lỗi:
 *    - 401 Unauthorized: Xóa phiên và điều hướng về /login.
 *    - 403 Forbidden: Báo lỗi phân quyền hoặc chuyển sang /unauthorized.
 *    - 422 Unprocessable Entity: Trích xuất mảng errors của Laravel để form hiển thị lỗi từng trường.
 *    - 500 / Network Error: Báo lỗi kết nối máy chủ rõ ràng.
 * 4. Hỗ trợ đầy đủ GET, POST, PUT, PATCH, DELETE và Upload file (FormData).
 */

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api/v1";

// Định nghĩa các lớp lỗi tùy chỉnh
export class ApiError extends Error {
  constructor(message, status, data = null, errors = null) {
    super(message);
    this.name = "ApiError";
    this.status = status;
    this.data = data;
    this.errors = errors;
  }
}

export class ValidationError extends ApiError {
  constructor(message, errors) {
    super(message, 422, null, errors);
    this.name = "ValidationError";
  }
}

export class UnauthorizedError extends ApiError {
  constructor(message = "Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.") {
    super(message, 401);
    this.name = "UnauthorizedError";
  }
}

export class ForbiddenError extends ApiError {
  constructor(message = "Bạn không có quyền thực hiện tác vụ này.") {
    super(message, 403);
    this.name = "ForbiddenError";
  }
}

export class NetworkError extends ApiError {
  constructor(message = "Không thể kết nối đến máy chủ Backend. Vui lòng kiểm tra lại dịch vụ mạng.") {
    super(message, 0);
    this.name = "NetworkError";
  }
}

/**
 * Lấy Bearer Token từ bộ nhớ cục bộ
 */
function getAuthToken() {
  return localStorage.getItem("stu_auth_token") || sessionStorage.getItem("stu_auth_token");
}

/**
 * Hàm gọi API trung tâm
 */
async function request(endpoint, options = {}) {
  const url = endpoint.startsWith("http") ? endpoint : `${API_BASE_URL}${endpoint.startsWith("/") ? "" : "/"}${endpoint}`;

  const headers = new Headers(options.headers || {});

  // Tự động thêm Bearer Token nếu có
  const token = getAuthToken();
  if (token && !headers.has("Authorization")) {
    headers.set("Authorization", `Bearer ${token}`);
  }

  // Tự động set Accept JSON
  if (!headers.has("Accept")) {
    headers.set("Accept", "application/json");
  }

  // Nếu không phải FormData thì mặc định là application/json
  const isFormData = options.body instanceof FormData;
  if (!isFormData && !headers.has("Content-Type") && options.body) {
    headers.set("Content-Type", "application/json");
  }

  const config = {
    ...options,
    headers,
    body: isFormData ? options.body : (options.body && typeof options.body === "object" ? JSON.stringify(options.body) : options.body)
  };

  try {
    const response = await fetch(url, config);

    // Xử lý status 204 No Content
    if (response.status === 204) {
      return { success: true, message: "Thao tác thành công", data: null };
    }

    let result = null;
    const contentType = response.headers.get("content-type");
    if (contentType && contentType.includes("application/json")) {
      result = await response.json();
    } else {
      const text = await response.text();
      result = { message: text };
    }

    // 1. Phản hồi thành công (200 - 299)
    if (response.ok) {
      return result;
    }

    // 2. Xử lý lỗi xác thực 401 Unauthorized
    if (response.status === 401) {
      localStorage.removeItem("stu_auth_token");
      localStorage.removeItem("stu_auth_user");
      sessionStorage.removeItem("stu_auth_token");
      sessionStorage.removeItem("stu_auth_user");

      // Bắn sự kiện toàn cục để App hoặc AuthContext tự động chuyển hướng
      window.dispatchEvent(new CustomEvent("stu:unauthorized", { detail: result }));

      // Nếu không ở trang đăng nhập thì chuyển hướng
      if (!window.location.pathname.includes("/login")) {
        window.location.href = `/login?session_expired=1&from=${encodeURIComponent(window.location.pathname)}`;
      }

      throw new UnauthorizedError(result?.message || "Phiên đăng nhập đã hết hạn.");
    }

    // 3. Xử lý lỗi phân quyền 403 Forbidden
    if (response.status === 403) {
      window.dispatchEvent(new CustomEvent("stu:forbidden", { detail: result }));
      throw new ForbiddenError(result?.message || "Truy cập bị từ chối: Bạn không có thẩm quyền trên tài nguyên này.");
    }

    // 4. Xử lý lỗi kiểm thực dữ liệu 422 Unprocessable Entity
    if (response.status === 422) {
      const errorMessage = result?.message || "Dữ liệu gửi lên không hợp lệ.";
      const fieldErrors = result?.errors || {};
      throw new ValidationError(errorMessage, fieldErrors);
    }

    // 5. Các lỗi HTTP khác (400, 404, 500, v.v.)
    const message = result?.message || `Lỗi máy chủ (HTTP ${response.status})`;
    throw new ApiError(message, response.status, result?.data, result?.errors);
  } catch (error) {
    // Nếu là lỗi đã được chuẩn hóa ở trên thì ném tiếp
    if (error instanceof ApiError) {
      throw error;
    }

    // Lỗi mạng hoặc máy chủ không phản hồi
    console.error("Lỗi kết nối API:", error);
    throw new NetworkError(`Không thể kết nối đến máy chủ (${url}). Vui lòng đảm bảo backend đang chạy.`);
  }
}

/**
 * Các hàm tiện ích RESTful
 */
export const apiClient = {
  get: (endpoint, params = null, options = {}) => {
    let finalEndpoint = endpoint;
    if (params) {
      const searchParams = new URLSearchParams();
      Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== "") {
          searchParams.append(key, value);
        }
      });
      const queryString = searchParams.toString();
      if (queryString) {
        finalEndpoint += (endpoint.includes("?") ? "&" : "?") + queryString;
      }
    }
    return request(finalEndpoint, { ...options, method: "GET" });
  },

  post: (endpoint, data = null, options = {}) => {
    return request(endpoint, { ...options, method: "POST", body: data });
  },

  put: (endpoint, data = null, options = {}) => {
    return request(endpoint, { ...options, method: "PUT", body: data });
  },

  patch: (endpoint, data = null, options = {}) => {
    return request(endpoint, { ...options, method: "PATCH", body: data });
  },

  delete: (endpoint, options = {}) => {
    return request(endpoint, { ...options, method: "DELETE" });
  },

  upload: (endpoint, formData, options = {}) => {
    return request(endpoint, { ...options, method: "POST", body: formData });
  },

  getBaseUrl: () => API_BASE_URL
};

export default apiClient;
