import React from "react";
import { UserCheck, ShieldCheck, Calendar, Building2, UserCircle2 } from "lucide-react";
import { PROTOTYPE_ROLES } from "../../../mock/prototypeData";

/**
 * WelcomeBanner - Lời chào, thông tin người dùng và bộ chuyển đổi vai trò (Role Switcher)
 */
export default function WelcomeBanner({ currentRole, onRoleChange }) {
  return (
    <div
      style={{
        backgroundColor: "var(--color-bg-card)",
        border: "1px solid var(--color-border)",
        borderLeft: "4px solid var(--color-primary)",
        borderRadius: "var(--radius-lg)",
        padding: "var(--space-5) var(--space-6)",
        marginBottom: "var(--space-6)",
        boxShadow: "var(--shadow-sm)"
      }}
    >
      <div
        style={{
          display: "flex",
          flexWrap: "wrap",
          alignItems: "center",
          justifyContent: "space-between",
          gap: "var(--space-4)"
        }}
      >
        {/* Lời chào và thông tin tài khoản */}
        <div style={{ display: "flex", alignItems: "center", gap: "var(--space-4)" }}>
          <div
            style={{
              width: "48px",
              height: "48px",
              borderRadius: "50%",
              backgroundColor: "var(--color-primary-light)",
              color: "var(--color-primary)",
              border: "1px solid var(--color-primary-border)",
              display: "flex",
              alignItems: "center",
              justifyContent: "center",
              fontWeight: "var(--font-weight-bold)",
              fontSize: "var(--font-size-base)",
              flexShrink: 0
            }}
          >
            {currentRole.avatar_initials}
          </div>

          <div>
            <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)", flexWrap: "wrap" }}>
              <span style={{ fontSize: "var(--font-size-base)", color: "var(--color-text-secondary)" }}>
                Kính chào,
              </span>
              <span
                style={{
                  fontSize: "var(--font-size-lg)",
                  fontWeight: "var(--font-weight-bold)",
                  color: "var(--color-text-primary)"
                }}
              >
                {currentRole.user_name}
              </span>
              <span
                style={{
                  fontSize: "11px",
                  fontWeight: "var(--font-weight-semibold)",
                  padding: "2px 8px",
                  borderRadius: "var(--radius-full)",
                  backgroundColor: "var(--color-primary)",
                  color: "#FFFFFF"
                }}
              >
                {currentRole.name}
              </span>
            </div>

            <div
              style={{
                display: "flex",
                flexWrap: "wrap",
                alignItems: "center",
                gap: "var(--space-4)",
                marginTop: "4px",
                fontSize: "var(--font-size-xs)",
                color: "var(--color-text-muted)"
              }}
            >
              <span style={{ display: "inline-flex", alignItems: "center", gap: "4px" }}>
                <Building2 size={13} />
                Đơn vị: {currentRole.department}
              </span>
              <span style={{ display: "inline-flex", alignItems: "center", gap: "4px" }}>
                <Calendar size={13} />
                Nhiệm kỳ: Hội đồng trường Khóa IV (2020-2025)
              </span>
              <span style={{ display: "inline-flex", alignItems: "center", gap: "4px", color: "var(--color-primary)" }}>
                <ShieldCheck size={13} />
                Nhiệm vụ: {currentRole.duties_badge}
              </span>
            </div>
          </div>
        </div>

        {/* Bộ chuyển đổi vai trò (Role Switcher Prototype) */}
        <div
          style={{
            display: "flex",
            flexDirection: "column",
            alignItems: "flex-end",
            gap: "4px",
            backgroundColor: "var(--color-bg-hover)",
            padding: "var(--space-2) var(--space-3)",
            borderRadius: "var(--radius-md)",
            border: "1px solid var(--color-border)"
          }}
        >
          <label
            htmlFor="role-switcher"
            style={{
              fontSize: "11px",
              fontWeight: "var(--font-weight-medium)",
              color: "var(--color-text-muted)",
              display: "inline-flex",
              alignItems: "center",
              gap: "4px"
            }}
          >
            <UserCircle2 size={13} />
            Xem giao diện theo vai trò (Prototype):
          </label>
          <select
            id="role-switcher"
            value={currentRole.key}
            onChange={(e) => {
              const selected = PROTOTYPE_ROLES.find((r) => r.key === e.target.value);
              if (selected) onRoleChange(selected);
            }}
            className="form-select"
            style={{
              height: "32px",
              fontSize: "var(--font-size-xs)",
              fontWeight: "var(--font-weight-semibold)",
              color: "var(--color-primary)",
              padding: "2px 24px 2px 8px",
              width: "auto"
            }}
          >
            {PROTOTYPE_ROLES.map((r) => (
              <option key={r.key} value={r.key}>
                {r.name}
              </option>
            ))}
          </select>
        </div>
      </div>
    </div>
  );
}
