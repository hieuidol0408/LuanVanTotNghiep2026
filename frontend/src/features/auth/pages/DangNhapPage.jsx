import React, { useState, useEffect, useRef } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import {
  Lock,
  Mail,
  Eye,
  EyeOff,
  LogIn,
  ShieldCheck,
  Sparkles,
  ChevronDown,
  ChevronUp,
  HelpCircle,
  X,
  CheckCircle2,
  Sun,
  Moon
} from "lucide-react";

import { useAuth } from "../context/AuthContext";
import { useTheme } from "../../../context/ThemeContext";
import { PROTOTYPE_ROLES } from "../../../mock/prototypeData";
import Alert from "../../../components/ui/Alert";

export default function LoginPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const { login } = useAuth();
  const { toggleTheme, isDark } = useTheme();

  // Form states - Mặc định để trống như trang đăng nhập chuẩn mực
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [rememberMe, setRememberMe] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");

  // Modal / Dropdown states
  const [showForgotModal, setShowForgotModal] = useState(false);
  const [showDemoSelector, setShowDemoSelector] = useState(true);
  const [selectedDemoRoleKey, setSelectedDemoRoleKey] = useState(null);

  // Chế độ bí mật (Cách 1: Phím F2 hoặc click 3 lần vào logo STU)
  const [isDemoModeActive, setIsDemoModeActive] = useState(false);
  const logoClickCountRef = useRef(0);
  const logoClickTimerRef = useRef(null);

  // Lắng nghe phím tắt F2 để bật/tắt chế độ chọn nhanh tài khoản kiểm thử
  useEffect(() => {
    const handleKeyDown = (e) => {
      if (e.key === "F2") {
        e.preventDefault();
        setIsDemoModeActive((prev) => !prev);
      }
    };

    window.addEventListener("keydown", handleKeyDown);
    return () => window.removeEventListener("keydown", handleKeyDown);
  }, []);

  // Xử lý click 3 lần liên tiếp vào logo STU để kích hoạt bí mật (phù hợp cả thiết bị cảm ứng)
  const handleLogoClick = () => {
    logoClickCountRef.current += 1;
    if (logoClickTimerRef.current) clearTimeout(logoClickTimerRef.current);

    if (logoClickCountRef.current >= 3) {
      setIsDemoModeActive((prev) => !prev);
      logoClickCountRef.current = 0;
    } else {
      logoClickTimerRef.current = setTimeout(() => {
        logoClickCountRef.current = 0;
      }, 500);
    }
  };

  // Đường dẫn cần chuyển đến sau khi đăng nhập
  const searchParams = new URLSearchParams(location.search);
  const isSessionExpired = searchParams.get("session_expired") === "1";
  const from = location.state?.from?.pathname || searchParams.get("from") || "/";

  // Xử lý gửi biểu mẫu đăng nhập
  const handleSubmit = async (e) => {
    e?.preventDefault?.();
    setErrorMessage("");

    if (!email.trim()) {
      setErrorMessage("Vui lòng nhập địa chỉ email hoặc tài khoản đăng nhập.");
      return;
    }

    if (!password) {
      setErrorMessage("Vui lòng nhập mật khẩu tài khoản.");
      return;
    }

    setIsSubmitting(true);
    try {
      const res = await login(email.trim(), password, rememberMe);
      if (res.success) {
        navigate(from, { replace: true });
      } else {
        setErrorMessage(res.message || "Tài khoản hoặc mật khẩu không chính xác. Vui lòng thử lại.");
      }
    } catch (err) {
      console.error("Lỗi đăng nhập:", err);
      setErrorMessage("Không thể kết nối đến máy chủ xác thực. Vui lòng kiểm tra lại đường truyền mạng.");
    } finally {
      setIsSubmitting(false);
    }
  };

  // Chọn nhanh tài khoản demo (Dành cho kiểm thử / Hội đồng chấm thi)
  const handleSelectDemoRole = (role) => {
    setEmail(role.email);
    setPassword("stu@2026");
    setSelectedDemoRoleKey(role.key);
    setErrorMessage("");
    setShowDemoSelector(false);
  };

  return (
    <div
      style={{
        minHeight: "100vh",
        width: "100%",
        display: "flex",
        flexDirection: "column",
        justifyContent: "space-between",
        alignItems: "center",
        padding: "var(--space-6) var(--space-4)",
        background: isDark
          ? "radial-gradient(ellipse at 50% 10%, #0f172a 0%, #090d16 65%, #030712 100%)"
          : "radial-gradient(ellipse at 50% 10%, #e0edfd 0%, #eef2f6 55%, #e2e8f0 100%)",
        position: "relative",
        overflowX: "hidden",
        transition: "background 0.3s ease"
      }}
    >
      {/* Background Decor Subtle Circles */}
      <div
        style={{
          position: "absolute",
          top: "-120px",
          left: "50%",
          transform: "translateX(-50%)",
          width: "700px",
          height: "400px",
          background: isDark
            ? "radial-gradient(circle, rgba(56, 189, 248, 0.15) 0%, rgba(0, 0, 0, 0) 70%)"
            : "radial-gradient(circle, rgba(0, 58, 140, 0.12) 0%, rgba(0, 0, 0, 0) 70%)",
          filter: "blur(60px)",
          pointerEvents: "none",
          zIndex: 0
        }}
      />

      {/* Nút bật Sáng / Tối góc trên bên phải */}
      <button
        type="button"
        onClick={toggleTheme}
        style={{
          position: "absolute",
          top: "16px",
          right: "16px",
          width: "42px",
          height: "42px",
          borderRadius: "50%",
          border: isDark ? "1px solid rgba(255, 255, 255, 0.2)" : "1px solid #CBD5E1",
          backgroundColor: isDark ? "rgba(15, 23, 42, 0.75)" : "rgba(255, 255, 255, 0.85)",
          backdropFilter: "blur(6px)",
          color: isDark ? "#FBBF24" : "#0F172A",
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          cursor: "pointer",
          zIndex: 20,
          boxShadow: isDark ? "0 4px 12px rgba(0,0,0,0.5)" : "0 4px 12px rgba(0,0,0,0.08)",
          transition: "all var(--transition-fast)"
        }}
        title={isDark ? "Đang bật Giao diện Tối - Bấm để chuyển sang Sáng" : "Đang bật Giao diện Sáng - Bấm để chuyển sang Tối"}
        aria-label="Chuyển đổi giao diện Sáng / Tối"
      >
        {isDark ? <Sun size={20} /> : <Moon size={20} />}
      </button>

      {/* 1. Header Trang: Tên Trường & Hội đồng trường */}
      <header
        style={{
          width: "100%",
          maxWidth: "520px",
          display: "flex",
          flexDirection: "column",
          alignItems: "center",
          textAlign: "center",
          zIndex: 1,
          marginTop: "var(--space-2)",
          marginBottom: "var(--space-4)"
        }}
      >
        <div
          style={{
            color: isDark ? "rgba(255, 255, 255, 0.85)" : "#475569",
            fontSize: "12px",
            fontWeight: "700",
            letterSpacing: "1.5px",
            textTransform: "uppercase",
            transition: "color 0.3s ease"
          }}
        >
          Trường Đại học Công nghệ Sài Gòn
        </div>

        <div
          style={{
            color: isDark ? "#38BDF8" : "#003A8C",
            fontSize: "14px",
            fontWeight: "800",
            letterSpacing: "0.5px",
            textTransform: "uppercase",
            marginTop: "2px",
            transition: "color 0.3s ease"
          }}
        >
          Hội đồng trường
        </div>
      </header>

      {/* 2. Main Login Card Căn Giữa Chuẩn Mực */}
      <main
        style={{
          width: "100%",
          maxWidth: "460px",
          zIndex: 1,
          margin: "0 auto"
        }}
      >
        <div
          style={{
            backgroundColor: isDark ? "#1E293B" : "#FFFFFF",
            borderRadius: "20px",
            padding: "var(--space-8) var(--space-8)",
            boxShadow: isDark
              ? "0 20px 40px -15px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(255, 255, 255, 0.08)"
              : "0 20px 40px -15px rgba(0, 58, 140, 0.15), 0 2px 8px rgba(0, 0, 0, 0.04)",
            border: isDark ? "1px solid #334155" : "1px solid #E2E8F0",
            position: "relative",
            transition: "background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease"
          }}
        >
          {/* Logo Trường STU Chính thức (Hỗ trợ click 3 lần để bật chế độ kiểm thử) */}
          <div style={{ textAlign: "center", marginBottom: "var(--space-3)" }}>
            <img
              src="/stu-logo.png"
              alt="Logo Trường Đại học Công nghệ Sài Gòn (STU)"
              onClick={handleLogoClick}
              title="Trường Đại học Công nghệ Sài Gòn (STU)"
              style={{
                height: "82px",
                maxWidth: "220px",
                objectFit: "contain",
                margin: "0 auto",
                display: "block",
                cursor: "pointer",
                userSelect: "none"
              }}
            />
          </div>

          {/* Tiêu đề Đăng nhập */}
          <div style={{ textAlign: "center", marginBottom: "var(--space-5)" }}>
            <h1
              style={{
                fontSize: "22px",
                fontWeight: "800",
                color: isDark ? "#F8FAFC" : "#0F172A",
                marginBottom: "4px",
                transition: "color 0.3s ease"
              }}
            >
              Đăng nhập hệ thống
            </h1>
            <p
              style={{
                fontSize: "13px",
                color: isDark ? "#94A3B8" : "#64748B",
                lineHeight: "1.5",
                margin: 0,
                transition: "color 0.3s ease"
              }}
            >
              Cổng quản lý phiên họp và thực hiện nghị quyết
            </p>
          </div>

          {/* Cảnh báo phiên làm việc hết hạn */}
          {isSessionExpired && !errorMessage && (
            <div style={{ marginBottom: "var(--space-4)" }}>
              <Alert variant="warning" title="Hết phiên làm việc">
                Phiên làm việc trước đó đã hết hạn. Vui lòng đăng nhập lại để tiếp tục.
              </Alert>
            </div>
          )}

          {/* Cảnh báo lỗi xác thực */}
          {errorMessage && (
            <div style={{ marginBottom: "var(--space-4)" }}>
              <Alert variant="danger" title="Đăng nhập không thành công">
                {errorMessage}
              </Alert>
            </div>
          )}

          {/* Biểu mẫu đăng nhập */}
          <form onSubmit={handleSubmit} style={{ display: "flex", flexDirection: "column", gap: "var(--space-4)" }}>
            {/* Trường Email / Tên đăng nhập */}
            <div>
              <label
                htmlFor="login-email"
                style={{
                  display: "block",
                  fontSize: "13px",
                  fontWeight: "600",
                  color: isDark ? "#E2E8F0" : "#334155",
                  marginBottom: "6px"
                }}
              >
                Email hoặc Tên đăng nhập <span style={{ color: "var(--color-danger)" }}>*</span>
              </label>
              <div style={{ position: "relative" }}>
                <span
                  style={{
                    position: "absolute",
                    left: "14px",
                    top: "50%",
                    transform: "translateY(-50%)",
                    color: isDark ? "#64748B" : "#94A3B8",
                    display: "flex",
                    pointerEvents: "none"
                  }}
                >
                  <Mail size={18} />
                </span>
                <input
                  id="login-email"
                  type="email"
                  className="form-input"
                  style={{
                    width: "100%",
                    height: "44px",
                    paddingLeft: "42px",
                    paddingRight: "14px",
                    borderRadius: "10px",
                    border: isDark ? "1px solid #475569" : "1px solid #CBD5E1",
                    fontSize: "14px",
                    backgroundColor: isDark ? "#0F172A" : "#F8FAFC",
                    color: isDark ? "#F8FAFC" : "#0F172A",
                    transition: "all var(--transition-fast)"
                  }}
                  placeholder="name@stu.edu.vn"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  disabled={isSubmitting}
                  autoComplete="email"
                  required
                />
              </div>
            </div>

            {/* Trường Mật khẩu */}
            <div>
              <div
                style={{
                  display: "flex",
                  justifyContent: "space-between",
                  alignItems: "center",
                  marginBottom: "6px"
                }}
              >
                <label
                  htmlFor="login-password"
                  style={{
                    fontSize: "13px",
                    fontWeight: "600",
                    color: isDark ? "#E2E8F0" : "#334155"
                  }}
                >
                  Mật khẩu <span style={{ color: "var(--color-danger)" }}>*</span>
                </label>
                <button
                  type="button"
                  onClick={() => setShowForgotModal(true)}
                  style={{
                    background: "none",
                    border: "none",
                    padding: 0,
                    fontSize: "12px",
                    color: isDark ? "#38BDF8" : "#003A8C",
                    fontWeight: "600",
                    cursor: "pointer",
                    textDecoration: "none"
                  }}
                >
                  Quên mật khẩu?
                </button>
              </div>

              <div style={{ position: "relative" }}>
                <span
                  style={{
                    position: "absolute",
                    left: "14px",
                    top: "50%",
                    transform: "translateY(-50%)",
                    color: isDark ? "#64748B" : "#94A3B8",
                    display: "flex",
                    pointerEvents: "none"
                  }}
                >
                  <Lock size={18} />
                </span>
                <input
                  id="login-password"
                  type={showPassword ? "text" : "password"}
                  className="form-input"
                  style={{
                    width: "100%",
                    height: "44px",
                    paddingLeft: "42px",
                    paddingRight: "44px",
                    borderRadius: "10px",
                    border: isDark ? "1px solid #475569" : "1px solid #CBD5E1",
                    fontSize: "14px",
                    backgroundColor: isDark ? "#0F172A" : "#F8FAFC",
                    color: isDark ? "#F8FAFC" : "#0F172A",
                    transition: "all var(--transition-fast)"
                  }}
                  placeholder="••••••••"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  disabled={isSubmitting}
                  autoComplete="current-password"
                  required
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  style={{
                    position: "absolute",
                    right: "10px",
                    top: "50%",
                    transform: "translateY(-50%)",
                    background: "none",
                    border: "none",
                    color: isDark ? "#94A3B8" : "#64748B",
                    cursor: "pointer",
                    padding: "6px",
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "center"
                  }}
                  title={showPassword ? "Ẩn mật khẩu" : "Hiện mật khẩu"}
                  aria-label={showPassword ? "Ẩn mật khẩu" : "Hiện mật khẩu"}
                >
                  {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                </button>
              </div>
            </div>

            {/* Checkbox Ghi nhớ đăng nhập */}
            <div style={{ display: "flex", alignItems: "center", gap: "8px", marginTop: "2px" }}>
              <input
                type="checkbox"
                id="remember-me"
                checked={rememberMe}
                onChange={(e) => setRememberMe(e.target.checked)}
                disabled={isSubmitting}
                style={{
                  width: "16px",
                  height: "16px",
                  cursor: "pointer",
                  accentColor: isDark ? "#38BDF8" : "#003A8C"
                }}
              />
              <label
                htmlFor="remember-me"
                style={{
                  fontSize: "13px",
                  color: isDark ? "#94A3B8" : "#64748B",
                  cursor: "pointer",
                  userSelect: "none"
                }}
              >
                Ghi nhớ đăng nhập trên thiết bị này
              </label>
            </div>

            {/* Nút Đăng nhập Chính */}
            <button
              type="submit"
              disabled={isSubmitting}
              style={{
                width: "100%",
                height: "46px",
                marginTop: "var(--space-2)",
                backgroundColor: isDark ? "#2563EB" : "#003A8C",
                color: "#FFFFFF",
                border: "none",
                borderRadius: "10px",
                fontSize: "15px",
                fontWeight: "700",
                display: "inline-flex",
                alignItems: "center",
                justifyContent: "center",
                gap: "8px",
                cursor: isSubmitting ? "not-allowed" : "pointer",
                boxShadow: isDark ? "0 4px 16px rgba(37, 99, 235, 0.4)" : "0 4px 12px rgba(0, 58, 140, 0.3)",
                transition: "all var(--transition-fast)"
              }}
              onMouseEnter={(e) => {
                if (!isSubmitting) e.currentTarget.style.backgroundColor = isDark ? "#1D4ED8" : "#002766";
              }}
              onMouseLeave={(e) => {
                if (!isSubmitting) e.currentTarget.style.backgroundColor = isDark ? "#2563EB" : "#003A8C";
              }}
            >
              {isSubmitting ? (
                <>
                  <span className="spinner" style={{ width: "18px", height: "18px", borderWidth: "2px" }}></span>
                  <span>Đang xác thực...</span>
                </>
              ) : (
                <>
                  <LogIn size={18} />
                  <span>ĐĂNG NHẬP</span>
                </>
              )}
            </button>
          </form>

          {/* Tiện ích chọn nhanh tài khoản Demo (Cách 1: Mặc định ẩn, kích hoạt qua phím F2 hoặc click 3 lần vào logo STU) */}
          {isDemoModeActive && (
            <div style={{ animation: "fadeIn 0.2s ease-in-out" }}>
              {/* Dải phân cách nhẹ */}
              <div
                style={{
                  margin: "var(--space-6) 0 var(--space-4) 0",
                  borderTop: isDark ? "1px dashed #334155" : "1px dashed #93C5FD",
                  position: "relative",
                  textAlign: "center"
                }}
              >
                <span
                  style={{
                    position: "absolute",
                    top: "-10px",
                    left: "50%",
                    transform: "translateX(-50%)",
                    backgroundColor: isDark ? "#1E293B" : "#FFFFFF",
                    padding: "0 10px",
                    fontSize: "11px",
                    color: isDark ? "#60A5FA" : "#2563EB",
                    fontWeight: "700",
                    textTransform: "uppercase",
                    display: "flex",
                    alignItems: "center",
                    gap: "4px"
                  }}
                >
                  <Sparkles size={12} />
                  Chế độ Kiểm thử Luận văn (F2)
                </span>
              </div>

              {/* Tiện ích chọn nhanh tài khoản Demo */}
              <div>
                <button
                  type="button"
                  onClick={() => setShowDemoSelector(!showDemoSelector)}
                  style={{
                    width: "100%",
                    padding: "9px 12px",
                    borderRadius: "8px",
                    border: isDark ? "1px solid #3B82F6" : "1px solid #93C5FD",
                    backgroundColor: isDark ? "#1E293B" : "#EFF6FF",
                    color: isDark ? "#93C5FD" : "#1E40AF",
                    fontSize: "12px",
                    fontWeight: "600",
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "space-between",
                    cursor: "pointer",
                    transition: "all var(--transition-fast)"
                  }}
                >
                  <span style={{ display: "flex", alignItems: "center", gap: "6px" }}>
                    <Sparkles size={14} style={{ color: isDark ? "#60A5FA" : "#2563EB" }} />
                    <span>Chọn nhanh tài khoản kiểm thử (7 vai trò)</span>
                  </span>
                  <div style={{ display: "flex", alignItems: "center", gap: "6px" }}>
                    {showDemoSelector ? <ChevronUp size={14} /> : <ChevronDown size={14} />}
                    <span
                      onClick={(e) => {
                        e.stopPropagation();
                        setIsDemoModeActive(false);
                      }}
                      title="Đóng chế độ kiểm thử (F2)"
                      style={{
                        padding: "2px",
                        display: "flex",
                        alignItems: "center",
                        borderRadius: "4px",
                        color: isDark ? "#94A3B8" : "#64748B"
                      }}
                    >
                      <X size={14} />
                    </span>
                  </div>
                </button>

                {/* Menu Sổ xuống chọn 7 vai trò khi click */}
                {showDemoSelector && (
                  <div
                    style={{
                      marginTop: "8px",
                      padding: "8px",
                      borderRadius: "10px",
                      backgroundColor: isDark ? "#0F172A" : "#F8FAFC",
                      border: isDark ? "1px solid #334155" : "1px solid #E2E8F0",
                      display: "flex",
                      flexDirection: "column",
                      gap: "6px",
                      maxHeight: "230px",
                      overflowY: "auto"
                    }}
                  >
                    <div style={{ fontSize: "11px", color: isDark ? "#94A3B8" : "#64748B", padding: "2px 6px 4px 6px" }}>
                      Bấm vào vai trò để tự động điền tài khoản (Mật khẩu: <code>stu@2026</code>):
                    </div>

                    {PROTOTYPE_ROLES.map((r) => {
                      const isSelected = selectedDemoRoleKey === r.key || email.toLowerCase() === r.email.toLowerCase();

                      return (
                        <div
                          key={r.key}
                          onClick={() => handleSelectDemoRole(r)}
                          style={{
                            display: "flex",
                            alignItems: "center",
                            justifyContent: "space-between",
                            padding: "6px 8px",
                            borderRadius: "6px",
                            border: isSelected ? "1px solid #2563EB" : "1px solid transparent",
                            backgroundColor: isSelected ? (isDark ? "#1E3A5F" : "#DBEAFE") : (isDark ? "#1E293B" : "#FFFFFF"),
                            cursor: "pointer",
                            transition: "background-color 0.15s ease"
                          }}
                          onMouseEnter={(e) => {
                            if (!isSelected) e.currentTarget.style.backgroundColor = isDark ? "#334155" : "#F1F5F9";
                          }}
                          onMouseLeave={(e) => {
                            if (!isSelected) e.currentTarget.style.backgroundColor = isDark ? "#1E293B" : "#FFFFFF";
                          }}
                        >
                          <div style={{ display: "flex", alignItems: "center", gap: "8px" }}>
                            <div
                              style={{
                                width: "26px",
                                height: "26px",
                                borderRadius: "50%",
                                backgroundColor: isSelected ? "#1E40AF" : (isDark ? "#334155" : "#E2E8F0"),
                                color: isSelected ? "#FFFFFF" : (isDark ? "#CBD5E1" : "#475569"),
                                display: "flex",
                                alignItems: "center",
                                justifyContent: "center",
                                fontSize: "10px",
                                fontWeight: "bold"
                              }}
                            >
                              {r.avatar_initials}
                            </div>
                            <div>
                              <div style={{ fontSize: "12px", fontWeight: "600", color: isDark ? "#F8FAFC" : "#0F172A" }}>
                                {r.name}
                              </div>
                              <div style={{ fontSize: "10px", color: isDark ? "#94A3B8" : "#64748B" }}>
                                {r.email}
                              </div>
                            </div>
                          </div>

                          {isSelected && (
                            <CheckCircle2 size={14} style={{ color: "#2563EB" }} />
                          )}
                        </div>
                      );
                    })}
                  </div>
                )}
              </div>
            </div>
          )}

          {/* Chú thích an ninh bảo mật */}
          <div
            style={{
              marginTop: "var(--space-4)",
              display: "flex",
              alignItems: "center",
              justifyContent: "center",
              gap: "6px",
              fontSize: "11px",
              color: isDark ? "#94A3B8" : "#64748B"
            }}
          >
            <ShieldCheck size={14} style={{ color: "#10B981" }} />
            <span>Hệ thống bảo vệ bởi xác thực tập trung & RBAC</span>
          </div>
        </div>
      </main>

      {/* 3. Footer Bản quyền */}
      <footer
        style={{
          width: "100%",
          maxWidth: "520px",
          textAlign: "center",
          fontSize: "12px",
          color: isDark ? "rgba(255, 255, 255, 0.6)" : "#64748B",
          padding: "var(--space-4) 0 var(--space-2) 0",
          zIndex: 1,
          transition: "color 0.3s ease"
        }}
      >
        <div>
          © 2026 Hội đồng trường • Trường Đại học Công nghệ Sài Gòn (STU)
        </div>
        <div style={{ fontSize: "11px", color: isDark ? "rgba(255, 255, 255, 0.4)" : "#94A3B8", marginTop: "4px" }}>
          Hệ thống điều hành trực tuyến • Phiên bản 2.0
        </div>
      </footer>

      {/* MODAL HƯỚNG DẪN QUÊN MẬT KHẨU */}
      {showForgotModal && (
        <div
          style={{
            position: "fixed",
            inset: 0,
            zIndex: 1000,
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            padding: "var(--space-4)",
            backgroundColor: "rgba(15, 23, 42, 0.65)",
            backdropFilter: "blur(4px)"
          }}
          onClick={() => setShowForgotModal(false)}
        >
          <div
            style={{
              backgroundColor: isDark ? "#1E293B" : "#FFFFFF",
              borderRadius: "16px",
              padding: "var(--space-6)",
              maxWidth: "420px",
              width: "100%",
              boxShadow: "0 25px 50px -12px rgba(0, 0, 0, 0.5)",
              border: isDark ? "1px solid #334155" : "1px solid #E2E8F0"
            }}
            onClick={(e) => e.stopPropagation()}
          >
            <div style={{ display: "flex", alignItems: "flex-start", justifyContent: "space-between", marginBottom: "var(--space-4)" }}>
              <div style={{ display: "flex", alignItems: "center", gap: "10px" }}>
                <div
                  style={{
                    width: "36px",
                    height: "36px",
                    borderRadius: "50%",
                    backgroundColor: isDark ? "#0F172A" : "#EFF6FF",
                    color: isDark ? "#38BDF8" : "#003A8C",
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "center"
                  }}
                >
                  <HelpCircle size={20} />
                </div>
                <h3 style={{ fontSize: "16px", fontWeight: "700", color: isDark ? "#F8FAFC" : "#0F172A", margin: 0 }}>
                  Hỗ trợ khôi phục mật khẩu
                </h3>
              </div>
              <button
                type="button"
                onClick={() => setShowForgotModal(false)}
                style={{
                  background: "none",
                  border: "none",
                  cursor: "pointer",
                  color: isDark ? "#CBD5E1" : "#94A3B8",
                  padding: "4px"
                }}
              >
                <X size={18} />
              </button>
            </div>

            <p style={{ fontSize: "13px", color: isDark ? "#CBD5E1" : "#475569", lineHeight: "1.6", marginBottom: "var(--space-4)" }}>
              Theo Quy chế bảo mật thông tin của Hội đồng trường STU, tài khoản được quản lý và cấp phát tập trung.
              Để đặt lại mật khẩu, quý Thầy/Cô vui lòng liên hệ:
            </p>

            <div
              style={{
                backgroundColor: isDark ? "#0F172A" : "#F8FAFC",
                border: isDark ? "1px solid #334155" : "1px solid #E2E8F0",
                borderRadius: "10px",
                padding: "var(--space-3) var(--space-4)",
                fontSize: "12px",
                color: isDark ? "#E2E8F0" : "#334155",
                marginBottom: "var(--space-4)",
                lineHeight: "1.8"
              }}
            >
              <div>• <strong>Văn phòng Hội đồng trường:</strong> Phòng A.102</div>
              <div>• <strong>Quản trị hệ thống:</strong> <code>admin@stu.edu.vn</code></div>
              <div>• <strong>Số nội bộ:</strong> (028) 3850 5520 - Ext: 102</div>
            </div>

            <button
              type="button"
              className="btn btn-primary"
              style={{ width: "100%", justifyContent: "center", height: "40px" }}
              onClick={() => setShowForgotModal(false)}
            >
              Đã hiểu
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
