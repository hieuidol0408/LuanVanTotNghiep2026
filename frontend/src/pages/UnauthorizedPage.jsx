import React from "react";
import { useNavigate, useLocation } from "react-router-dom";
import { ShieldAlert, ArrowLeft, LogOut } from "lucide-react";
import { useAuth } from "../features/auth/context/AuthContext";
import { PROTOTYPE_ROLES } from "../mock/prototypeData";
import PageHeader from "../components/layout/PageHeader";

export default function UnauthorizedPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const { user, role, switchRole, logout } = useAuth();

  const handleRoleSwitch = (newRoleKey) => {
    switchRole(newRoleKey);
    // Reload lại trang hiện tại sau khi chuyển vai trò
    window.location.reload();
  };

  return (
    <div style={{ maxWidth: "800px", margin: "var(--space-8) auto", padding: "0 var(--space-4)" }}>
      <PageHeader
        title="Truy cập bị từ chối"
        description="Mã phản hồi HTTP 403: Không đủ thẩm quyền truy cập tài nguyên"
        breadcrumbItems={[
          { label: "Bảng điều hành", path: "/" },
          { label: "Quyền truy cập", path: "" }
        ]}
      />

      <div
        className="card"
        style={{
          padding: "var(--space-8)",
          textAlign: "center",
          backgroundColor: "var(--color-bg-card)",
          border: "1px solid var(--color-border)",
          borderRadius: "var(--radius-lg)",
          boxShadow: "var(--shadow-sm)"
        }}
      >
        <div
          style={{
            width: "64px",
            height: "64px",
            borderRadius: "50%",
            backgroundColor: "rgba(220, 38, 38, 0.1)",
            color: "var(--color-danger)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            margin: "0 auto var(--space-4)"
          }}
        >
          <ShieldAlert size={36} />
        </div>

        <h2
          style={{
            fontSize: "var(--font-size-xl)",
            fontWeight: "var(--font-weight-bold)",
            color: "var(--color-text-primary)",
            marginBottom: "var(--space-2)"
          }}
        >
          Bạn không có quyền truy cập trang này
        </h2>

        <p
          style={{
            fontSize: "var(--font-size-sm)",
            color: "var(--color-text-secondary)",
            maxWidth: "580px",
            margin: "0 auto var(--space-6)",
            lineHeight: "1.6"
          }}
        >
          Theo Quy chế tổ chức & hoạt động của Hội đồng trường Trường Đại học Công nghệ Sài Gòn (STU),
          tài khoản của bạn không được phân quyền thực hiện tác vụ hoặc truy cập đường dẫn{" "}
          <code
            style={{
              padding: "2px 6px",
              backgroundColor: "var(--color-bg-muted)",
              borderRadius: "var(--radius-sm)",
              color: "var(--color-danger)",
              fontWeight: "var(--font-weight-semibold)"
            }}
          >
            {location.pathname}
          </code>
          .
        </p>

        {/* Thông tin tài khoản hiện tại */}
        <div
          style={{
            backgroundColor: "var(--color-bg-hover)",
            border: "1px solid var(--color-border)",
            borderRadius: "var(--radius-md)",
            padding: "var(--space-4)",
            maxWidth: "500px",
            margin: "0 auto var(--space-6)",
            textAlign: "left"
          }}
        >
          <div style={{ fontSize: "var(--font-size-xs)", fontWeight: "var(--font-weight-bold)", color: "var(--color-text-muted)", textTransform: "uppercase", marginBottom: "var(--space-2)" }}>
            Thông tin phiên làm việc hiện tại:
          </div>
          <div style={{ display: "grid", gridTemplateColumns: "130px 1fr", gap: "var(--space-2)", fontSize: "var(--font-size-sm)" }}>
            <span style={{ color: "var(--color-text-muted)" }}>Họ và tên:</span>
            <span style={{ fontWeight: "var(--font-weight-semibold)", color: "var(--color-text-primary)" }}>{user?.user_name || "Chưa xác thực"}</span>

            <span style={{ color: "var(--color-text-muted)" }}>Vai trò hiện tại:</span>
            <span style={{ color: "var(--color-primary)", fontWeight: "var(--font-weight-bold)" }}>
              {user?.role_name || user?.name || role}
            </span>

            <span style={{ color: "var(--color-text-muted)" }}>Đơn vị:</span>
            <span style={{ color: "var(--color-text-secondary)" }}>{user?.department || "Không xác định"}</span>
          </div>
        </div>

        {/* Nút hành động */}
        <div
          style={{
            display: "flex",
            justifyContent: "center",
            alignItems: "center",
            gap: "var(--space-3)",
            flexWrap: "wrap",
            marginBottom: "var(--space-6)"
          }}
        >
          <button
            type="button"
            className="btn btn-primary"
            onClick={() => navigate("/")}
            style={{ gap: "var(--space-2)" }}
          >
            <ArrowLeft size={16} />
            <span>Quay về Bảng điều hành</span>
          </button>

          <button
            type="button"
            className="btn btn-secondary"
            onClick={async () => {
              await logout();
              navigate("/login");
            }}
            style={{ gap: "var(--space-2)" }}
          >
            <LogOut size={16} />
            <span>Đăng xuất tài khoản</span>
          </button>
        </div>

        {/* Chuyển đổi nhanh vai trò để kiểm thử tiếp */}
        <div
          style={{
            borderTop: "1px dashed var(--color-border)",
            paddingTop: "var(--space-4)",
            maxWidth: "600px",
            margin: "0 auto"
          }}
        >
          <div
            style={{
              fontSize: "var(--font-size-xs)",
              color: "var(--color-text-muted)",
              marginBottom: "var(--space-3)"
            }}
          >
            <strong>Chế độ Kiểm thử Đồ án:</strong> Chọn một vai trò có quyền truy cập hợp lệ để thử nghiệm:
          </div>

          <div
            style={{
              display: "flex",
              flexWrap: "wrap",
              justifyContent: "center",
              gap: "var(--space-2)"
            }}
          >
            {PROTOTYPE_ROLES.map((r) => {
              const isCurrent = r.key === role;
              return (
                <button
                  key={r.key}
                  type="button"
                  onClick={() => handleRoleSwitch(r.key)}
                  disabled={isCurrent}
                  style={{
                    padding: "4px 10px",
                    fontSize: "var(--font-size-xs)",
                    borderRadius: "var(--radius-md)",
                    border: isCurrent ? "1px solid var(--color-primary)" : "1px solid var(--color-border)",
                    backgroundColor: isCurrent ? "var(--color-primary-light)" : "var(--color-bg-card)",
                    color: isCurrent ? "var(--color-primary)" : "var(--color-text-secondary)",
                    cursor: isCurrent ? "default" : "pointer",
                    fontWeight: isCurrent ? "var(--font-weight-bold)" : "var(--font-weight-normal)"
                  }}
                >
                  {r.name}
                </button>
              );
            })}
          </div>
        </div>
      </div>
    </div>
  );
}
