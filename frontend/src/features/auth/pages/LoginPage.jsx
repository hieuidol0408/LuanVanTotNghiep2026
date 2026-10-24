import React, { useState } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import {
  Lock,
  Mail,
  Eye,
  EyeOff,
  LogIn,
  ShieldCheck,
  Sparkles
} from "lucide-react";

import { useAuth } from "../context/AuthContext";
import { PROTOTYPE_ROLES } from "../../../mock/prototypeData";
import Alert from "../../../components/ui/Alert";

export default function LoginPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const { login } = useAuth();

  // Form states
  const [email, setEmail] = useState("thuky_hdt@stu.edu.vn");
  const [password, setPassword] = useState("stu@2026");
  const [showPassword, setShowPassword] = useState(false);
  const [rememberMe, setRememberMe] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");

  // Đường dẫn cần chuyển đến sau khi đăng nhập (nếu bị ProtectedRoute chặn trước đó)
  const searchParams = new URLSearchParams(location.search);
  const isSessionExpired = searchParams.get("session_expired") === "1";
  const from = location.state?.from?.pathname || searchParams.get("from") || "/";

  // Xử lý gửi biểu mẫu đăng nhập
  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrorMessage("");

    if (!email.trim()) {
      setErrorMessage("Vui lòng nhập địa chỉ email hợp lệ.");
      return;
    }

    if (!password) {
      setErrorMessage("Vui lòng nhập mật khẩu tài khoản.");
      return;
    }

    setIsSubmitting(true);
    try {
      const res = await login(email, password, rememberMe);
      if (res.success) {
        navigate(from, { replace: true });
      } else {
        setErrorMessage(res.message || "Đăng nhập không thành công. Vui lòng kiểm tra lại thông tin.");
      }
    } catch (err) {
      console.error("Lỗi đăng nhập:", err);
      setErrorMessage("Đã xảy ra sự cố mạng khi kết nối máy chủ xác thực.");
    } finally {
      setIsSubmitting(false);
    }
  };

  // Chọn nhanh tài khoản demo
  const handleSelectDemoRole = (role) => {
    setEmail(role.email);
    setPassword("stu@2026");
    setErrorMessage("");
  };

  return (
    <div
      style={{
        minHeight: "100vh",
        backgroundColor: "var(--color-bg-app)",
        display: "flex",
        flexDirection: "column",
        justifyContent: "space-between",
        padding: "var(--space-6) var(--space-4)"
      }}
    >
      {/* 1. Header Nhận diện Thương hiệu STU */}
      <header
        style={{
          maxWidth: "1100px",
          width: "100%",
          margin: "0 auto",
          display: "flex",
          alignItems: "center",
          justifyContent: "space-between"
        }}
      >
        <div style={{ display: "flex", alignItems: "center", gap: "var(--space-3)" }}>
          <div
            style={{
              width: "44px",
              height: "44px",
              borderRadius: "var(--radius-md)",
              backgroundColor: "var(--color-primary)",
              color: "#FFFFFF",
              display: "flex",
              alignItems: "center",
              justifyContent: "center",
              fontWeight: "var(--font-weight-bold)",
              fontSize: "18px",
              boxShadow: "var(--shadow-sm)"
            }}
          >
            STU
          </div>
          <div>
            <div
              style={{
                fontSize: "var(--font-size-sm)",
                fontWeight: "var(--font-weight-bold)",
                color: "var(--color-primary)",
                letterSpacing: "0.02em"
              }}
            >
              TRƯỜNG ĐẠI HỌC CÔNG NGHỆ SÀI GÒN
            </div>
            <div
              style={{
                fontSize: "var(--font-size-xs)",
                color: "var(--color-text-muted)"
              }}
            >
              HỘI ĐỒNG TRƯỜNG • SAIGON TECHNOLOGY UNIVERSITY
            </div>
          </div>
        </div>

        <div
          style={{
            display: "none",
            fontSize: "var(--font-size-xs)",
            color: "var(--color-text-muted)"
          }}
          className="desktop-only"
        >
          Hệ thống điều hành tác nghiệp trực tuyến
        </div>
      </header>

      {/* 2. Main Container: Login Card & Demo Role Selector */}
      <main
        style={{
          maxWidth: "1000px",
          width: "100%",
          margin: "var(--space-8) auto",
          display: "grid",
          gridTemplateColumns: "repeat(auto-fit, minmax(360px, 1fr))",
          gap: "var(--space-8)",
          alignItems: "start"
        }}
      >
        {/* Card Đăng nhập Chính */}
        <div
          className="card"
          style={{
            backgroundColor: "var(--color-bg-card)",
            border: "1px solid var(--color-border)",
            borderRadius: "var(--radius-xl)",
            padding: "var(--space-8)",
            boxShadow: "0 10px 25px -5px rgba(11, 46, 89, 0.08), 0 8px 10px -6px rgba(11, 46, 89, 0.04)"
          }}
        >
          {/* Card Header */}
          <div style={{ marginBottom: "var(--space-6)" }}>
            <div
              style={{
                display: "inline-flex",
                alignItems: "center",
                gap: "6px",
                padding: "3px 10px",
                borderRadius: "var(--radius-full)",
                backgroundColor: "rgba(11, 46, 89, 0.08)",
                color: "var(--color-primary)",
                fontSize: "12px",
                fontWeight: "var(--font-weight-semibold)",
                marginBottom: "var(--space-3)"
              }}
            >
              <ShieldCheck size={14} />
              <span>Cổng Xác thực Tập trung</span>
            </div>
            <h1
              style={{
                fontSize: "var(--font-size-2xl)",
                fontWeight: "var(--font-weight-bold)",
                color: "var(--color-primary)",
                marginBottom: "var(--space-1)"
              }}
            >
              Đăng nhập Hệ thống
            </h1>
            <p
              style={{
                fontSize: "var(--font-size-sm)",
                color: "var(--color-text-secondary)"
              }}
            >
              Quản lý phiên họp và giám sát thực hiện nghị quyết Hội đồng trường.
            </p>
          </div>

          {/* Thông báo hết phiên nếu có */}
          {isSessionExpired && !errorMessage && (
            <div style={{ marginBottom: "var(--space-4)" }}>
              <Alert variant="warning" title="Hết phiên làm việc">
                Phiên làm việc trước đó của bạn đã kết thúc. Vui lòng đăng nhập lại để tiếp tục.
              </Alert>
            </div>
          )}

          {/* Thông báo lỗi nếu có */}
          {errorMessage && (
            <div style={{ marginBottom: "var(--space-4)" }}>
              <Alert variant="danger" title="Lỗi xác thực">
                {errorMessage}
              </Alert>
            </div>
          )}

          {/* Form Đăng nhập */}
          <form onSubmit={handleSubmit} style={{ display: "flex", flexDirection: "column", gap: "var(--space-4)" }}>
            {/* Trường Email */}
            <div>
              <label
                htmlFor="login-email"
                style={{
                  display: "block",
                  fontSize: "var(--font-size-sm)",
                  fontWeight: "var(--font-weight-semibold)",
                  color: "var(--color-text-primary)",
                  marginBottom: "var(--space-1)"
                }}
              >
                Tài khoản Email STU <span style={{ color: "var(--color-danger)" }}>*</span>
              </label>
              <div style={{ position: "relative" }}>
                <span
                  style={{
                    position: "absolute",
                    left: "12px",
                    top: "50%",
                    transform: "translateY(-50%)",
                    color: "var(--color-text-muted)",
                    display: "flex",
                    pointerEvents: "none"
                  }}
                >
                  <Mail size={16} />
                </span>
                <input
                  id="login-email"
                  type="email"
                  className="form-input"
                  style={{ paddingLeft: "38px" }}
                  placeholder="vidu@stu.edu.vn"
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
              <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: "var(--space-1)" }}>
                <label
                  htmlFor="login-password"
                  style={{
                    fontSize: "var(--font-size-sm)",
                    fontWeight: "var(--font-weight-semibold)",
                    color: "var(--color-text-primary)"
                  }}
                >
                  Mật khẩu đăng nhập <span style={{ color: "var(--color-danger)" }}>*</span>
                </label>
                <span
                  style={{
                    fontSize: "var(--font-size-xs)",
                    color: "var(--color-primary)",
                    cursor: "pointer"
                  }}
                  onClick={() => alert("Vui lòng liên hệ Quản trị viên hệ thống (admin@stu.edu.vn) hoặc Văn phòng Hội đồng trường để đặt lại mật khẩu.")}
                >
                  Quên mật khẩu?
                </span>
              </div>
              <div style={{ position: "relative" }}>
                <span
                  style={{
                    position: "absolute",
                    left: "12px",
                    top: "50%",
                    transform: "translateY(-50%)",
                    color: "var(--color-text-muted)",
                    display: "flex",
                    pointerEvents: "none"
                  }}
                >
                  <Lock size={16} />
                </span>
                <input
                  id="login-password"
                  type={showPassword ? "text" : "password"}
                  className="form-input"
                  style={{ paddingLeft: "38px", paddingRight: "40px" }}
                  placeholder="Nhập mật khẩu..."
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
                    color: "var(--color-text-muted)",
                    cursor: "pointer",
                    padding: "4px"
                  }}
                  title={showPassword ? "Ẩn mật khẩu" : "Hiện mật khẩu"}
                  aria-label={showPassword ? "Ẩn mật khẩu" : "Hiện mật khẩu"}
                >
                  {showPassword ? <EyeOff size={16} /> : <Eye size={16} />}
                </button>
              </div>
            </div>

            {/* Checkbox Ghi nhớ đăng nhập */}
            <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)" }}>
              <input
                type="checkbox"
                id="remember-me"
                checked={rememberMe}
                onChange={(e) => setRememberMe(e.target.checked)}
                disabled={isSubmitting}
                style={{ width: "16px", height: "16px", cursor: "pointer", accentColor: "var(--color-primary)" }}
              />
              <label
                htmlFor="remember-me"
                style={{
                  fontSize: "var(--font-size-sm)",
                  color: "var(--color-text-secondary)",
                  cursor: "pointer"
                }}
              >
                Duy trì trạng thái đăng nhập trên thiết bị này
              </label>
            </div>

            {/* Nút Đăng nhập */}
            <button
              type="submit"
              className="btn btn-primary"
              disabled={isSubmitting}
              style={{
                width: "100%",
                height: "44px",
                justifyContent: "center",
                marginTop: "var(--space-2)",
                fontSize: "var(--font-size-base)",
                fontWeight: "var(--font-weight-semibold)",
                boxShadow: "0 2px 4px rgba(11, 46, 89, 0.2)"
              }}
            >
              {isSubmitting ? (
                <>
                  <span className="spinner" style={{ width: "16px", height: "16px", borderWidth: "2px" }}></span>
                  <span>Đang xác thực...</span>
                </>
              ) : (
                <>
                  <LogIn size={18} />
                  <span>Đăng nhập hệ thống</span>
                </>
              )}
            </button>
          </form>

          {/* Ghi chú về an toàn bảo mật */}
          <div
            style={{
              marginTop: "var(--space-6)",
              paddingTop: "var(--space-4)",
              borderTop: "1px solid var(--color-border)",
              display: "flex",
              alignItems: "flex-start",
              gap: "8px",
              fontSize: "11px",
              color: "var(--color-text-muted)",
              lineHeight: "1.5"
            }}
          >
            <ShieldCheck size={16} style={{ color: "var(--color-primary)", flexShrink: 0, marginTop: "2px" }} />
            <span>
              Hệ thống bảo vệ bởi cơ chế kiểm soát truy cập dựa trên vai trò (RBAC) và xác thực Bearer Token.
              Mọi lượt truy cập bất hợp pháp đều được ghi nhận lại trong Nhật ký hệ thống (Audit Log).
            </span>
          </div>
        </div>

        {/* Khối Thử nghiệm Phân quyền theo 7 Vai trò (Prototype Helper) */}
        <div
          className="card"
          style={{
            backgroundColor: "var(--color-bg-card)",
            border: "1px solid var(--color-border)",
            borderRadius: "var(--radius-xl)",
            padding: "var(--space-6)",
            boxShadow: "var(--shadow-sm)"
          }}
        >
          <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "var(--space-4)" }}>
            <div style={{ display: "flex", alignItems: "center", gap: "8px" }}>
              <div
                style={{
                  width: "32px",
                  height: "32px",
                  borderRadius: "var(--radius-md)",
                  backgroundColor: "rgba(217, 119, 6, 0.1)",
                  color: "#D97706",
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center"
                }}
              >
                <Sparkles size={16} />
              </div>
              <div>
                <h3 style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-bold)", color: "var(--color-text-primary)" }}>
                  Thử nghiệm 7 Vai trò Nghiệp vụ
                </h3>
                <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
                  Nhấn chọn để tự động điền tài khoản tương ứng
                </div>
              </div>
            </div>
          </div>

          {/* Cảnh báo rõ ràng: Không tạo tài khoản mặc định trên production */}
          <div
            style={{
              padding: "var(--space-3)",
              backgroundColor: "rgba(217, 119, 6, 0.08)",
              border: "1px solid rgba(217, 119, 6, 0.25)",
              borderRadius: "var(--radius-md)",
              fontSize: "11px",
              color: "#92400E",
              marginBottom: "var(--space-4)",
              lineHeight: "1.5"
            }}
          >
            <strong>LƯU Ý THẨM QUYỀN & BẢO MẬT:</strong>
            <ul style={{ margin: "4px 0 0 16px", padding: 0 }}>
              <li>Tiện ích này chỉ phục vụ demo đánh giá giao diện cho đồ án tốt nghiệp.</li>
              <li>Môi trường Production tuyệt đối không lưu tài khoản mặc định, mọi tài khoản do Quản trị viên cấp.</li>
              <li>Mật khẩu mặc định kiểm thử: <code>stu@2026</code>.</li>
            </ul>
          </div>

          {/* Danh sách 7 vai trò để click chọn */}
          <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-2)" }}>
            {PROTOTYPE_ROLES.map((r) => {
              const isSelected = email.toLowerCase() === r.email.toLowerCase();

              return (
                <div
                  key={r.key}
                  onClick={() => handleSelectDemoRole(r)}
                  style={{
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "space-between",
                    padding: "var(--space-2) var(--space-3)",
                    borderRadius: "var(--radius-md)",
                    border: isSelected ? "2px solid var(--color-primary)" : "1px solid var(--color-border)",
                    backgroundColor: isSelected ? "rgba(11, 46, 89, 0.05)" : "var(--color-bg-card)",
                    cursor: "pointer",
                    transition: "all var(--transition-fast)"
                  }}
                  onMouseEnter={(e) => {
                    if (!isSelected) e.currentTarget.style.backgroundColor = "var(--color-bg-hover)";
                  }}
                  onMouseLeave={(e) => {
                    if (!isSelected) e.currentTarget.style.backgroundColor = "var(--color-bg-card)";
                  }}
                >
                  <div style={{ display: "flex", alignItems: "center", gap: "var(--space-3)" }}>
                    <div
                      style={{
                        width: "32px",
                        height: "32px",
                        borderRadius: "50%",
                        backgroundColor: isSelected ? "var(--color-primary)" : "var(--color-bg-muted)",
                        color: isSelected ? "#FFFFFF" : "var(--color-text-secondary)",
                        display: "flex",
                        alignItems: "center",
                        justifyContent: "center",
                        fontSize: "11px",
                        fontWeight: "var(--font-weight-bold)"
                      }}
                    >
                      {r.avatar_initials}
                    </div>
                    <div>
                      <div style={{ fontSize: "var(--font-size-xs)", fontWeight: "var(--font-weight-semibold)", color: "var(--color-text-primary)" }}>
                        {r.name}
                      </div>
                      <div style={{ fontSize: "11px", color: "var(--color-text-muted)" }}>
                        {r.user_name} • <code>{r.email}</code>
                      </div>
                    </div>
                  </div>

                  <span
                    style={{
                      fontSize: "11px",
                      padding: "2px 8px",
                      borderRadius: "var(--radius-full)",
                      backgroundColor: isSelected ? "var(--color-primary)" : "var(--color-bg-muted)",
                      color: isSelected ? "#FFFFFF" : "var(--color-text-muted)",
                      fontWeight: "var(--font-weight-semibold)"
                    }}
                  >
                    {r.key}
                  </span>
                </div>
              );
            })}
          </div>
        </div>
      </main>

      {/* 3. Footer */}
      <footer
        style={{
          textAlign: "center",
          fontSize: "var(--font-size-xs)",
          color: "var(--color-text-muted)",
          padding: "var(--space-4) 0"
        }}
      >
        © 2026 Hội đồng trường Trường Đại học Công nghệ Sài Gòn (STU). Bản quyền thuộc về ĐH Công nghệ Sài Gòn.
      </footer>
    </div>
  );
}
