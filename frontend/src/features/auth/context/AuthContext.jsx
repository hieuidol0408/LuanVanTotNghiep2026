import React, { createContext, useContext, useState, useEffect, useCallback } from "react";
import authService, { ROLE_PERMISSIONS } from "../services/authService";

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(null);
  const [isLoading, setIsLoading] = useState(true);

  // Khởi tạo phiên đăng nhập khi tải ứng dụng
  useEffect(() => {
    async function initAuth() {
      try {
        const storedToken = authService.getToken();
        const storedUser = authService.getCurrentUser();

        if (storedToken && storedUser) {
          setUser(storedUser);
          setToken(storedToken);

          // Xác thực lại với API /auth/me trong nền
          authService.fetchCurrentUser().then((freshUser) => {
            if (freshUser) {
              setUser(freshUser);
            }
          });
        } else {
          setUser(null);
          setToken(null);
        }
      } catch (err) {
        console.error("Lỗi khởi tạo phiên xác thực:", err);
        setUser(null);
        setToken(null);
      } finally {
        setIsLoading(false);
      }
    }

    initAuth();

    // Lắng nghe sự kiện 401 Unauthorized toàn cục từ apiClient
    const handleUnauthorized = () => {
      setUser(null);
      setToken(null);
    };

    window.addEventListener("stu:unauthorized", handleUnauthorized);
    return () => window.removeEventListener("stu:unauthorized", handleUnauthorized);
  }, []);

  // Đăng nhập
  const login = useCallback(async (email, password, rememberMe = true) => {
    setIsLoading(true);
    try {
      const res = await authService.login(email, password, rememberMe);
      if (res.success && res.user) {
        setUser(res.user);
        setToken(res.token);
        return res;
      }
      return res;
    } finally {
      setIsLoading(false);
    }
  }, []);

  // Đăng xuất
  const logout = useCallback(async () => {
    setIsLoading(true);
    try {
      await authService.logout();
      setUser(null);
      setToken(null);
    } finally {
      setIsLoading(false);
    }
  }, []);

  // Chuyển đổi nhanh vai trò (Đăng nhập thật qua API Backend)
  const switchRole = useCallback(async (roleKey) => {
    setIsLoading(true);
    try {
      const updatedUser = await authService.switchRole(roleKey);
      if (updatedUser) {
        setUser(updatedUser);
        setToken(authService.getToken());
        return updatedUser;
      }
      return null;
    } finally {
      setIsLoading(false);
    }
  }, []);

  // Kiểm tra quyền truy cập đường dẫn
  const canAccessRoute = useCallback(
    (pathname) => {
      if (!user) return false;
      return authService.canAccessRoute(user.key || user.role, pathname);
    },
    [user]
  );

  // Kiểm tra vai trò
  const hasRole = useCallback(
    (roleKeys) => {
      if (!user) return false;
      const userRole = user.key || user.role;
      if (Array.isArray(roleKeys)) {
        return roleKeys.includes(userRole);
      }
      return userRole === roleKeys;
    },
    [user]
  );

  // Kiểm tra tính năng cụ thể
  const hasPermission = useCallback(
    (featureKey) => {
      if (!user || !user.permissions || !user.permissions.features) return false;
      return Boolean(user.permissions.features[featureKey]);
    },
    [user]
  );

  const value = {
    user,
    role: user?.key || user?.role || null,
    roleName: user?.role_name || user?.name || "Chưa xác thực",
    token,
    isAuthenticated: Boolean(user && token),
    isLoading,
    login,
    logout,
    switchRole,
    canAccessRoute,
    hasRole,
    hasPermission
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error("useAuth() phải được sử dụng bên trong <AuthProvider>");
  }
  return context;
}

export default AuthContext;
