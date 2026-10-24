import React from "react";
import { NavLink } from "react-router-dom";
import * as Icons from "lucide-react";
import { PROTOTYPE_NAV_ITEMS } from "../../mock/prototypeData";
import { useAuth } from "../../features/auth/context/AuthContext";

/**
 * Sidebar - Menu điều hướng bên trái có thể thu gọn và phân quyền theo Vai trò
 */
export default function Sidebar({ isCollapsed, onToggle }) {
  const { user, role, canAccessRoute } = useAuth();

  // Lọc danh mục Menu điều hướng dựa trên thẩm quyền của vai trò hiện tại
  const visibleNavItems = PROTOTYPE_NAV_ITEMS.filter((item) => {
    return canAccessRoute(item.path);
  });

  return (
    <aside
      style={{
        width: isCollapsed ? "var(--sidebar-width-collapsed)" : "var(--sidebar-width)",
        backgroundColor: "var(--color-bg-sidebar)",
        color: "var(--color-text-inverse)",
        height: "100vh",
        position: "sticky",
        top: 0,
        zIndex: 50,
        display: "flex",
        flexDirection: "column",
        transition: "width var(--transition-normal)",
        overflow: "hidden",
        flexShrink: 0
      }}
    >
      {/* Brand Header */}
      <div
        style={{
          height: "var(--header-height)",
          display: "flex",
          alignItems: "center",
          padding: isCollapsed ? "0 var(--space-4)" : "0 var(--space-5)",
          borderBottom: "1px solid rgba(255, 255, 255, 0.1)",
          gap: "var(--space-3)",
          overflow: "hidden",
          whiteSpace: "nowrap"
        }}
      >
        <div
          style={{
            width: "36px",
            height: "36px",
            borderRadius: "var(--radius-md)",
            backgroundColor: "var(--color-primary-hover)",
            border: "1px solid rgba(255, 255, 255, 0.2)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            fontWeight: "var(--font-weight-bold)",
            fontSize: "14px",
            color: "#FFFFFF",
            flexShrink: 0
          }}
        >
          STU
        </div>

        {!isCollapsed && (
          <div style={{ display: "flex", flexDirection: "column" }}>
            <span style={{ fontSize: "var(--font-size-sm)", fontWeight: "var(--font-weight-bold)", color: "#FFFFFF" }}>
              HỘI ĐỒNG TRƯỜNG
            </span>
            <span style={{ fontSize: "11px", color: "var(--color-text-muted)" }}>
              ĐH Công nghệ Sài Gòn
            </span>
          </div>
        )}
      </div>

      {/* Role Indicator Banner */}
      {!isCollapsed && user && (
        <div
          style={{
            padding: "var(--space-2) var(--space-4)",
            backgroundColor: "rgba(255, 255, 255, 0.04)",
            borderBottom: "1px solid rgba(255, 255, 255, 0.06)",
            display: "flex",
            alignItems: "center",
            justifyContent: "space-between"
          }}
        >
          <div style={{ overflow: "hidden" }}>
            <div style={{ fontSize: "10px", color: "#94A3B8", textTransform: "uppercase", letterSpacing: "0.05em" }}>
              Vai trò đang xem:
            </div>
            <div
              style={{
                fontSize: "11px",
                fontWeight: "var(--font-weight-semibold)",
                color: "#38BDF8",
                whiteSpace: "nowrap",
                overflow: "hidden",
                textOverflow: "ellipsis"
              }}
              title={user.name}
            >
              {user.name}
            </div>
          </div>
          <span
            style={{
              fontSize: "10px",
              padding: "2px 6px",
              borderRadius: "var(--radius-sm)",
              backgroundColor: "rgba(56, 189, 248, 0.15)",
              color: "#38BDF8",
              fontWeight: "var(--font-weight-bold)"
            }}
          >
            {user.key}
          </span>
        </div>
      )}

      {/* Navigation Menu (Filtered by RBAC) */}
      <nav
        style={{
          flex: 1,
          padding: "var(--space-3) var(--space-2)",
          overflowY: "auto",
          display: "flex",
          flexDirection: "column",
          gap: "4px"
        }}
      >
        {visibleNavItems.map((item) => {
          const Icon = Icons[item.icon] || Icons.Circle;

          return (
            <NavLink
              key={item.key}
              to={item.path}
              end={item.path === "/"}
              title={isCollapsed ? item.label : undefined}
              style={({ isActive }) => ({
                display: "flex",
                alignItems: "center",
                gap: "var(--space-3)",
                padding: isCollapsed ? "10px 0" : "10px var(--space-3)",
                justifyContent: isCollapsed ? "center" : "flex-start",
                borderRadius: "var(--radius-md)",
                fontSize: "var(--font-size-sm)",
                fontWeight: isActive ? "var(--font-weight-semibold)" : "var(--font-weight-normal)",
                color: isActive ? "#FFFFFF" : "#94A3B8",
                backgroundColor: isActive ? "var(--color-bg-sidebar-active)" : "transparent",
                textDecoration: "none",
                transition: "all var(--transition-fast)",
                position: "relative"
              })}
            >
              <Icon size={19} style={{ flexShrink: 0 }} />

              {!isCollapsed && (
                <div style={{ flex: 1, display: "flex", alignItems: "center", justifyContent: "space-between", overflow: "hidden" }}>
                  <span style={{ whiteSpace: "nowrap", overflow: "hidden", textOverflow: "ellipsis" }}>
                    {item.label}
                  </span>

                  {item.badge && (
                    <span
                      style={{
                        fontSize: "10px",
                        padding: "1px 6px",
                        backgroundColor: "rgba(255, 255, 255, 0.15)",
                        color: "#E2E8F0",
                        borderRadius: "var(--radius-sm)",
                        fontWeight: "var(--font-weight-medium)"
                      }}
                    >
                      {item.badge}
                    </span>
                  )}

                  {item.count !== undefined && (
                    <span
                      style={{
                        fontSize: "11px",
                        padding: "1px 7px",
                        backgroundColor: "var(--color-primary)",
                        color: "#FFFFFF",
                        borderRadius: "var(--radius-full)",
                        fontWeight: "var(--font-weight-semibold)"
                      }}
                    >
                      {item.count}
                    </span>
                  )}
                </div>
              )}
            </NavLink>
          );
        })}
      </nav>

      {/* Footer / Info / Quick Collapse Indicator */}
      <div
        style={{
          padding: "var(--space-3)",
          borderTop: "1px solid rgba(255, 255, 255, 0.1)",
          fontSize: "var(--font-size-xs)",
          color: "var(--color-text-muted)",
          textAlign: isCollapsed ? "center" : "left",
          whiteSpace: "nowrap"
        }}
      >
        {!isCollapsed ? (
          <div>
            <div style={{ color: "#E2E8F0", fontWeight: "var(--font-weight-medium)" }}>Phiên bản 1.0</div>
            <div style={{ fontSize: "10px", color: "#64748B" }}>Luận văn tốt nghiệp STU</div>
          </div>
        ) : (
          <div style={{ fontSize: "10px", color: "#64748B" }}>v1.0</div>
        )}
      </div>
    </aside>
  );
}
