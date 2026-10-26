import React, { useState, useRef, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { Menu, Bell, User, KeyRound, LogOut, ChevronDown, Check, Sun, Moon } from "lucide-react";
import { PROTOTYPE_NOTIFICATIONS } from "../../mock/prototypeData";
import { useAuth } from "../../features/auth/context/AuthContext";
import { useTheme } from "../../context/ThemeContext";

/**
 * Header - Thanh điều hướng trên cùng kèm thông báo và menu tài khoản
 */
export default function Header({
  isSidebarCollapsed,
  onToggleSidebar,
  pageTitle = "Hội đồng trường STU"
}) {
  const navigate = useNavigate();
  const { user, role, logout } = useAuth();
  const { theme, toggleTheme, isDark } = useTheme();

  const [showNotifications, setShowNotifications] = useState(false);
  const [showUserMenu, setShowUserMenu] = useState(false);
  const [notifications, setNotifications] = useState(PROTOTYPE_NOTIFICATIONS);

  const notifRef = useRef(null);
  const userMenuRef = useRef(null);

  const unreadCount = notifications.filter((n) => !n.is_read).length;

  // Đóng dropdown khi click ra ngoài
  useEffect(() => {
    const handleClickOutside = (e) => {
      if (notifRef.current && !notifRef.current.contains(e.target)) {
        setShowNotifications(false);
      }
      if (userMenuRef.current && !userMenuRef.current.contains(e.target)) {
        setShowUserMenu(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  const markAllAsRead = () => {
    setNotifications((prev) => prev.map((n) => ({ ...n, is_read: true })));
  };

  return (
    <header
      style={{
        height: "var(--header-height)",
        backgroundColor: "var(--color-bg-card)",
        borderBottom: "1px solid var(--color-border)",
        display: "flex",
        alignItems: "center",
        justifyContent: "space-between",
        padding: "0 var(--space-6)",
        position: "sticky",
        top: 0,
        zIndex: 40
      }}
    >
      {/* Nút bật/tắt Sidebar & Tiêu đề ngữ cảnh */}
      <div style={{ display: "flex", alignItems: "center", gap: "var(--space-3)" }}>
        <button
          type="button"
          onClick={onToggleSidebar}
          className="btn btn-secondary btn-sm"
          style={{ width: "36px", height: "36px", padding: 0 }}
          aria-label={isSidebarCollapsed ? "Mở rộng thanh bên" : "Thu gọn thanh bên"}
        >
          <Menu size={18} />
        </button>

        <span
          style={{
            fontSize: "var(--font-size-base)",
            fontWeight: "var(--font-weight-semibold)",
            color: "var(--color-primary)"
          }}
        >
          {pageTitle}
        </span>
      </div>

      {/* Cụm chức năng bên phải: Nút Sáng/Tối, Thông báo & Tài khoản */}
      <div style={{ display: "flex", alignItems: "center", gap: "var(--space-3)" }}>
        {/* Nút chuyển đổi Chế độ Sáng / Tối */}
        <button
          type="button"
          onClick={toggleTheme}
          style={{
            position: "relative",
            width: "38px",
            height: "38px",
            borderRadius: "var(--radius-md)",
            border: "1px solid var(--color-border)",
            backgroundColor: "var(--color-bg-card)",
            color: isDark ? "#FBBF24" : "var(--color-text-secondary)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            cursor: "pointer",
            transition: "all var(--transition-fast)"
          }}
          title={isDark ? "Đang bật Giao diện Tối - Bấm để chuyển sang Sáng" : "Đang bật Giao diện Sáng - Bấm để chuyển sang Tối"}
          aria-label={isDark ? "Chuyển sang giao diện Sáng" : "Chuyển sang giao diện Tối"}
        >
          {isDark ? <Sun size={18} /> : <Moon size={18} />}
        </button>

        {/* Chuông Thông báo */}
        <div style={{ position: "relative" }} ref={notifRef}>
          <button
            type="button"
            onClick={() => setShowNotifications(!showNotifications)}
            style={{
              position: "relative",
              width: "38px",
              height: "38px",
              borderRadius: "var(--radius-md)",
              border: "1px solid var(--color-border)",
              backgroundColor: "var(--color-bg-card)",
              color: "var(--color-text-secondary)",
              display: "flex",
              alignItems: "center",
              justifyContent: "center",
              cursor: "pointer"
            }}
            aria-label="Thông báo"
            aria-expanded={showNotifications}
          >
            <Bell size={18} />
            {unreadCount > 0 && (
              <span
                style={{
                  position: "absolute",
                  top: "-4px",
                  right: "-4px",
                  width: "18px",
                  height: "18px",
                  borderRadius: "50%",
                  backgroundColor: "var(--color-danger)",
                  color: "#FFFFFF",
                  fontSize: "11px",
                  fontWeight: "var(--font-weight-bold)",
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center"
                }}
              >
                {unreadCount}
              </span>
            )}
          </button>

          {/* Dropdown Thông báo */}
          {showNotifications && (
            <div
              style={{
                position: "absolute",
                top: "calc(100% + 8px)",
                right: 0,
                width: "320px",
                backgroundColor: "var(--color-bg-card)",
                border: "1px solid var(--color-border)",
                borderRadius: "var(--radius-lg)",
                boxShadow: "var(--shadow-lg)",
                zIndex: 50,
                overflow: "hidden"
              }}
            >
              <div
                style={{
                  padding: "var(--space-3) var(--space-4)",
                  borderBottom: "1px solid var(--color-border-subtle)",
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "space-between"
                }}
              >
                <span style={{ fontSize: "var(--font-size-sm)", fontWeight: "var(--font-weight-semibold)" }}>
                  Thông báo hệ thống
                </span>
                {unreadCount > 0 && (
                  <button
                    type="button"
                    onClick={markAllAsRead}
                    style={{
                      background: "none",
                      border: "none",
                      color: "var(--color-primary)",
                      fontSize: "var(--font-size-xs)",
                      cursor: "pointer",
                      display: "flex",
                      alignItems: "center",
                      gap: "3px"
                    }}
                  >
                    <Check size={12} /> Đã đọc hết
                  </button>
                )}
              </div>

              <div style={{ maxHeight: "280px", overflowY: "auto" }}>
                {notifications.map((item) => (
                  <div
                    key={item.id}
                    style={{
                      padding: "var(--space-3) var(--space-4)",
                      borderBottom: "1px solid var(--color-border-subtle)",
                      backgroundColor: item.is_read ? "transparent" : "var(--color-primary-light)",
                      fontSize: "var(--font-size-xs)"
                    }}
                  >
                    <div style={{ fontWeight: item.is_read ? "var(--font-weight-normal)" : "var(--font-weight-semibold)", color: "var(--color-text-primary)", marginBottom: "2px" }}>
                      {item.title}
                    </div>
                    <div style={{ color: "var(--color-text-muted)" }}>{item.time}</div>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>

        {/* Menu Tài khoản Người dùng */}
        <div style={{ position: "relative" }} ref={userMenuRef}>
          <button
            type="button"
            onClick={() => setShowUserMenu(!showUserMenu)}
            style={{
              display: "flex",
              alignItems: "center",
              gap: "var(--space-3)",
              background: "none",
              border: "none",
              cursor: "pointer",
              padding: "4px 8px",
              borderRadius: "var(--radius-md)"
            }}
            aria-expanded={showUserMenu}
          >
            {/* Avatar chữ cái viết tắt */}
            <div
              style={{
                width: "36px",
                height: "36px",
                borderRadius: "50%",
                backgroundColor: "var(--color-primary)",
                color: "#FFFFFF",
                fontWeight: "var(--font-weight-bold)",
                fontSize: "var(--font-size-sm)",
                display: "flex",
                alignItems: "center",
                justifyContent: "center"
              }}
            >
              {user?.avatar_initials || "STU"}
            </div>

            <div style={{ textAlign: "left", display: "none" }} className="user-text-md">
              <div style={{ fontSize: "var(--font-size-sm)", fontWeight: "var(--font-weight-semibold)", color: "var(--color-text-primary)" }}>
                {user?.user_name || user?.full_name || "Người dùng STU"}
              </div>
              <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
                {user?.role_name || user?.name || role}
              </div>
            </div>

            <ChevronDown size={14} style={{ color: "var(--color-text-muted)" }} />
          </button>

          {/* Dropdown Menu User */}
          {showUserMenu && (
            <div
              style={{
                position: "absolute",
                top: "calc(100% + 8px)",
                right: 0,
                width: "250px",
                backgroundColor: "var(--color-bg-card)",
                border: "1px solid var(--color-border)",
                borderRadius: "var(--radius-lg)",
                boxShadow: "var(--shadow-lg)",
                zIndex: 50,
                padding: "var(--space-2) 0"
              }}
            >
              <div style={{ padding: "var(--space-2) var(--space-4)", borderBottom: "1px solid var(--color-border-subtle)", marginBottom: "4px" }}>
                <div style={{ fontSize: "var(--font-size-sm)", fontWeight: "var(--font-weight-semibold)", color: "var(--color-text-primary)" }}>
                  {user?.user_name || user?.full_name}
                </div>
                <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
                  {user?.department || user?.department_name}
                </div>
                <div style={{ marginTop: "4px" }}>
                  <span
                    style={{
                      fontSize: "10px",
                      padding: "2px 8px",
                      borderRadius: "var(--radius-full)",
                      backgroundColor: "var(--color-primary-light)",
                      color: "var(--color-primary)",
                      fontWeight: "var(--font-weight-bold)"
                    }}
                  >
                    {user?.name || user?.role_name}
                  </span>
                </div>
              </div>

              <button
                type="button"
                style={{
                  width: "100%",
                  textAlign: "left",
                  padding: "var(--space-2) var(--space-4)",
                  background: "none",
                  border: "none",
                  fontSize: "var(--font-size-sm)",
                  color: "var(--color-text-secondary)",
                  display: "flex",
                  alignItems: "center",
                  gap: "var(--space-2)",
                  cursor: "pointer"
                }}
                onClick={() => setShowUserMenu(false)}
              >
                <User size={15} />
                <span>Hồ sơ cá nhân</span>
              </button>

              <button
                type="button"
                style={{
                  width: "100%",
                  textAlign: "left",
                  padding: "var(--space-2) var(--space-4)",
                  background: "none",
                  border: "none",
                  fontSize: "var(--font-size-sm)",
                  color: "var(--color-text-secondary)",
                  display: "flex",
                  alignItems: "center",
                  gap: "var(--space-2)",
                  cursor: "pointer"
                }}
                onClick={() => setShowUserMenu(false)}
              >
                <KeyRound size={15} />
                <span>Đổi mật khẩu</span>
              </button>

              {/* Chuyển đổi giao diện Sáng / Tối */}
              <button
                type="button"
                style={{
                  width: "100%",
                  textAlign: "left",
                  padding: "var(--space-2) var(--space-4)",
                  background: "none",
                  border: "none",
                  fontSize: "var(--font-size-sm)",
                  color: "var(--color-text-secondary)",
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "space-between",
                  cursor: "pointer"
                }}
                onClick={() => {
                  toggleTheme();
                  setShowUserMenu(false);
                }}
              >
                <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)" }}>
                  {isDark ? <Sun size={15} style={{ color: "#FBBF24" }} /> : <Moon size={15} />}
                  <span>{isDark ? "Giao diện Tối" : "Giao diện Sáng"}</span>
                </div>
                <span
                  style={{
                    fontSize: "10px",
                    padding: "2px 6px",
                    borderRadius: "4px",
                    backgroundColor: "var(--color-bg-hover)",
                    color: "var(--color-text-muted)"
                  }}
                >
                  {isDark ? "Đang bật" : "Đang tắt"}
                </span>
              </button>

              <div style={{ borderTop: "1px solid var(--color-border-subtle)", margin: "4px 0" }} />

              <button
                type="button"
                style={{
                  width: "100%",
                  textAlign: "left",
                  padding: "var(--space-2) var(--space-4)",
                  background: "none",
                  border: "none",
                  fontSize: "var(--font-size-sm)",
                  color: "var(--color-danger)",
                  display: "flex",
                  alignItems: "center",
                  gap: "var(--space-2)",
                  cursor: "pointer"
                }}
                onClick={async () => {
                  setShowUserMenu(false);
                  await logout();
                  navigate("/login");
                }}
              >
                <LogOut size={15} />
                <span>Đăng xuất</span>
              </button>
            </div>
          )}
        </div>
      </div>
    </header>
  );
}
