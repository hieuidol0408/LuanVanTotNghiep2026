import React from "react";
import { useNavigate } from "react-router-dom";
import { Vote, Lock, Unlock, ArrowRight, ShieldCheck } from "lucide-react";
import StatusBadge from "../../../components/ui/StatusBadge";
import EmptyState from "../../../components/ui/EmptyState";

export default function MeetingVotingTab({ votings = [], meetingId }) {
  const navigate = useNavigate();

  return (
    <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-4)" }}>
      {/* Header Bar */}
      <div
        style={{
          display: "flex",
          flexWrap: "wrap",
          alignItems: "center",
          justifyContent: "space-between",
          gap: "var(--space-3)",
          padding: "var(--space-4)",
          backgroundColor: "var(--color-bg-card)",
          border: "1px solid var(--color-border)",
          borderRadius: "var(--radius-lg)"
        }}
      >
        <div>
          <h3 style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-semibold)", color: "var(--color-text-primary)" }}>
            Nội dung Biểu quyết Phiên họp ({votings.length})
          </h3>
          <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
            Chỉ Thành viên Hội đồng trường có mặt hợp lệ mới được cấp quyền bỏ phiếu. Khách mời bị loại trừ hoàn toàn.
          </div>
        </div>

        <button
          type="button"
          className="btn btn-primary btn-sm"
          onClick={() => navigate("/voting")}
          style={{ gap: "4px" }}
        >
          <span>Mở phòng Biểu quyết</span>
          <ArrowRight size={14} />
        </button>
      </div>

      {votings.length === 0 ? (
        <div className="card">
          <EmptyState
            icon={Vote}
            title="Chưa có nội dung biểu quyết"
            description="Các nội dung cần lấy ý kiến sẽ được tự động kích hoạt dựa trên các mục trong Chương trình họp (Agenda)."
          />
        </div>
      ) : (
        <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)" }}>
          {votings.map((vote, idx) => {
            const isSecret = vote.voting_type === "SECRET";

            return (
              <div
                key={vote.id || idx}
                className="card"
                style={{
                  padding: "var(--space-4)",
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "space-between",
                  gap: "var(--space-4)"
                }}
              >
                <div style={{ display: "flex", alignItems: "flex-start", gap: "var(--space-3)" }}>
                  <div
                    style={{
                      width: "36px",
                      height: "36px",
                      borderRadius: "var(--radius-md)",
                      backgroundColor: isSecret ? "var(--color-warning-bg)" : "var(--color-primary-light)",
                      color: isSecret ? "var(--color-warning)" : "var(--color-primary)",
                      display: "flex",
                      alignItems: "center",
                      justifyContent: "center",
                      flexShrink: 0
                    }}
                  >
                    {isSecret ? <Lock size={18} /> : <Unlock size={18} />}
                  </div>

                  <div>
                    <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)", marginBottom: "2px" }}>
                      <span
                        style={{
                          fontSize: "11px",
                          fontWeight: "var(--font-weight-semibold)",
                          padding: "1px 6px",
                          borderRadius: "var(--radius-sm)",
                          backgroundColor: isSecret ? "var(--color-warning-bg)" : "var(--color-primary-light)",
                          color: isSecret ? "var(--color-warning-text)" : "var(--color-primary)",
                          border: `1px solid ${isSecret ? "var(--color-warning-border)" : "var(--color-primary-border)"}`
                        }}
                      >
                        {vote.voting_type_label}
                      </span>
                      <span style={{ fontSize: "11px", color: "var(--color-text-muted)" }}>
                        • Tỷ lệ thông qua chuẩn: <strong>{vote.pass_threshold}</strong>
                      </span>
                    </div>

                    <div style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-medium)", color: "var(--color-text-primary)" }}>
                      {vote.title}
                    </div>
                  </div>
                </div>

                <div style={{ display: "flex", alignItems: "center", gap: "var(--space-3)", flexShrink: 0 }}>
                  <StatusBadge
                    status={vote.status === "OPENED" ? "success" : "neutral"}
                    label={vote.status_label}
                    size="sm"
                  />

                  <button
                    type="button"
                    className="btn btn-secondary btn-sm"
                    onClick={() => navigate("/voting")}
                  >
                    Vào phòng bỏ phiếu
                  </button>
                </div>
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}
