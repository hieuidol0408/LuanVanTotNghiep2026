import React, { createContext, useContext, useState, useEffect, useCallback } from "react";
import authService, { ROLE_PERMISSIONS } from "../services/authService";

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(null);
  const [isLoading, setIsLoading] = useState(true);

  // Khởi tạo phiên đăng nhập khi load ứng dụng
  useEffect(() => {
    try {
      const currentUser = authService.getCurrentUser();
      const currentToken = authService.getToken();

      if (currentUser && currentToken) {
        setUser(currentUser);
        setToken(currentToken);
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

  // Chuyển đổi nhanh vai trò (Phục vụ Demo & Nghiệm thu đồ án)
  const switchRole = useCallback((roleKey) => {
    const updatedUser = authService.switchRole(roleKey);
    if (updatedUser) {
      setUser(updatedUser);
      setToken(authService.getToken());
      return updatedUser;
    }
    return null;
  }, []);

  // Kiểm tra quyền truy cập đường dẫn
  const canAccessRoute = useCallback(
    (pathname) => {
      if (!user) return false;
      return authService.canAccessRoute(user.key, pathname);
    },
    [user]
  );

  // Kiểm tra vai trò
  const hasRole = useCallback(
    (roleKeys) => {
      if (!user) return false;
      if (Array.isArray(roleKeys)) {
        return roleKeys.includes(user.key);
      }
      return user.key === roleKeys;
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
    role: user?.key || null,
    roleName: user?.name || "Chưa xác thực",
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
