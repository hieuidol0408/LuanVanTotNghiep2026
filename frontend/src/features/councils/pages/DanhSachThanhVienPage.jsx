import React, { useState, useEffect, useCallback } from "react";
import {
  Users,
  Search,
  RefreshCw,
  Building2,
  Calendar,
  Award,
  Mail,
  ShieldCheck,
  CheckCircle2,
  X,
  RotateCcw
} from "lucide-react";
import PageHeader from "../../../components/layout/PageHeader";
import StatusBadge from "../../../components/ui/StatusBadge";
import EmptyState from "../../../components/ui/EmptyState";
import { useToast } from "../../../components/ui/Toast";
import councilService from "../services/councilService";

export default function CouncilMemberListPage() {
  const toast = useToast();

  const [members, setMembers] = useState([]);
  const [terms, setTerms] = useState([]);
  const [selectedTerm, setSelectedTerm] = useState("ALL");
  const [search, setSearch] = useState("");
  const [loading, setLoading] = useState(false);

  // 1. Tải danh sách khóa nhiệm kỳ
  const fetchTerms = useCallback(async () => {
    try {
      const res = await councilService.getCouncilTerms();
      if (res.success && res.data) {
        setTerms(res.data);
      }
    } catch (err) {
      console.warn("Lỗi tải khóa nhiệm kỳ:", err);
    }
  }, []);

  useEffect(() => {
    fetchTerms();
  }, [fetchTerms]);

  // 2. Tải danh sách thành viên HĐT
  const fetchMembers = useCallback(async () => {
    setLoading(true);
    try {
      const res = await councilService.getCouncilMembers({
        term: selectedTerm,
        search
      });

      if (res.success) {
        setMembers(res.data);
      } else {
        toast.error(res.message || "Không thể tải danh sách thành viên");
      }
    } catch (err) {
      console.warn("Lỗi tải thành viên:", err);
      toast.error("Lỗi kết nối khi tải danh sách thành viên");
    } finally {
      setLoading(false);
    }
  }, [selectedTerm, search, toast]);

  useEffect(() => {
    fetchMembers();
  }, [fetchMembers]);

  return (
    <div>
      <PageHeader
        title="Quản lý Hội đồng trường & Thành viên"
        description="Danh sách chính thức các Thành viên Hội đồng trường STU qua các khóa nhiệm kỳ, cơ cấu chức danh và thẩm quyền biểu quyết."
        breadcrumbItems={[
          { label: "Bảng điều hành", path: "/" },
          { label: "Thành viên Hội đồng trường", path: "" }
        ]}
        actions={
          <button
            type="button"
            className="btn btn-secondary"
            onClick={fetchMembers}
            title="Tải lại dữ liệu"
          >
            <RefreshCw size={16} />
            <span>Làm mới</span>
          </button>
        }
      />

      {/* 1. Bộ lọc khóa nhiệm kỳ & Tìm kiếm */}
      <div
        className="card"
        style={{
          padding: "var(--space-4)",
          marginBottom: "var(--space-4)",
          display: "flex",
          flexWrap: "wrap",
          alignItems: "center",
          justifyContent: "space-between",
          gap: "var(--space-3)"
        }}
      >
        <div style={{ display: "flex", flex: 1, minWidth: "280px", maxWidth: "460px", position: "relative" }}>
          <Search
            size={18}
            style={{
              position: "absolute",
              left: "14px",
              top: "50%",
              transform: "translateY(-50%)",
              color: "var(--color-text-muted)",
              pointerEvents: "none"
            }}
          />
          <input
            type="text"
            className="form-control"
            placeholder="Tìm theo họ tên, chức danh hoặc email..."
            style={{
              paddingLeft: "42px",
              paddingRight: search ? "36px" : "14px"
            }}
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
          {search && (
            <button
              type="button"
              onClick={() => setSearch("")}
              style={{
                position: "absolute",
                right: "10px",
                top: "50%",
                transform: "translateY(-50%)",
                background: "none",
                border: "none",
                color: "var(--color-text-muted)",
                cursor: "pointer",
                padding: "4px",
                display: "flex",
                alignItems: "center",
                borderRadius: "4px"
              }}
              title="Xóa tìm kiếm"
            >
              <X size={16} />
            </button>
          )}
        </div>

        <div style={{ display: "flex", alignItems: "center", gap: "var(--space-3)", flexWrap: "wrap" }}>
          <div style={{ position: "relative", display: "flex", alignItems: "center" }}>
            <Calendar
              size={16}
              style={{
                position: "absolute",
                left: "14px",
                color: "var(--color-text-muted)",
                pointerEvents: "none",
                zIndex: 1
              }}
            />
            <select
              className="form-control"
              style={{
                paddingLeft: "38px",
                minWidth: "220px",
                fontWeight: "500"
              }}
              value={selectedTerm}
              onChange={(e) => setSelectedTerm(e.target.value)}
            >
              <option value="ALL">Tất cả khóa nhiệm kỳ</option>
              {terms.map((t, idx) => (
                <option key={idx} value={t}>
                  {t}
                </option>
              ))}
            </select>
          </div>

          {(search || selectedTerm !== "ALL") && (
            <button
              type="button"
              className="btn btn-secondary"
              onClick={() => {
                setSearch("");
                setSelectedTerm("ALL");
              }}
              title="Đặt lại bộ lọc"
            >
              <RotateCcw size={15} />
              <span>Đặt lại</span>
            </button>
          )}
        </div>
      </div>

      {/* 2. Danh sách thành viên dạng lưới card */}
      {loading ? (
        <div className="card" style={{ padding: "var(--space-8)", textAlign: "center", color: "var(--color-text-muted)" }}>
          Đang tải danh sách thành viên Hội đồng trường...
        </div>
      ) : members.length === 0 ? (
        <div className="card" style={{ padding: "var(--space-8)" }}>
          <EmptyState
            icon={Users}
            title="Không tìm thấy thành viên nào"
            description="Chưa có thành viên nào phù hợp với điều kiện tìm kiếm."
          />
        </div>
      ) : (
        <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(320px, 1fr))", gap: "var(--space-3)" }}>
          {members.map((member) => {
            const isChair = member.council_role.includes("Chủ tịch") && !member.council_role.includes("Phó");
            const isSecretary = member.council_role.includes("Thư ký");

            return (
              <div
                key={member.id}
                className="card"
                style={{
                  padding: "var(--space-4)",
                  display: "flex",
                  flexDirection: "column",
                  gap: "var(--space-3)",
                  borderTop: isChair ? "3px solid var(--color-primary)" : isSecretary ? "3px solid #2563eb" : "1px solid var(--color-border)"
                }}
              >
                <div style={{ display: "flex", alignItems: "flex-start", gap: "var(--space-3)" }}>
                  {/* Avatar */}
                  <div
                    style={{
                      width: "48px",
                      height: "48px",
                      borderRadius: "var(--radius-full)",
                      backgroundColor: "var(--color-primary-light)",
                      color: "var(--color-primary)",
                      display: "flex",
                      alignItems: "center",
                      justifyContent: "center",
                      fontSize: "var(--font-size-base)",
                      fontWeight: "var(--font-weight-bold)",
                      flexShrink: 0
                    }}
                  >
                    {member.full_name?.charAt(0) || "U"}
                  </div>

                  <div>
                    <h3 style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-bold)", color: "var(--color-text-primary)", marginBottom: "2px" }}>
                      {member.full_name}
                    </h3>
                    <div style={{ fontSize: "12px", fontWeight: "var(--font-weight-semibold)", color: "var(--color-primary)" }}>
                      {member.council_role}
                    </div>
                  </div>
                </div>

                <div style={{ display: "flex", flexDirection: "column", gap: "6px", fontSize: "12px", color: "var(--color-text-secondary)" }}>
                  <div style={{ display: "flex", alignItems: "center", gap: "6px" }}>
                    <Building2 size={14} style={{ color: "var(--color-text-muted)" }} />
                    <span>{member.department}</span>
                  </div>

                  <div style={{ display: "flex", alignItems: "center", gap: "6px" }}>
                    <Mail size={14} style={{ color: "var(--color-text-muted)" }} />
                    <span>{member.email}</span>
                  </div>

                  <div style={{ display: "flex", alignItems: "center", gap: "6px" }}>
                    <Calendar size={14} style={{ color: "var(--color-text-muted)" }} />
                    <span>Nhiệm kỳ: <strong>{member.term}</strong></span>
                  </div>
                </div>

                <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", borderTop: "1px solid var(--color-border)", paddingTop: "var(--space-3)", marginTop: "2px" }}>
                  <span style={{ fontSize: "11px", color: "var(--color-success)", display: "flex", alignItems: "center", gap: "4px", fontWeight: "var(--font-weight-medium)" }}>
                    <CheckCircle2 size={13} /> Quyền biểu quyết chính thức
                  </span>

                  <StatusBadge status="success" label="Đang công tác" size="sm" />
                </div>
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}
