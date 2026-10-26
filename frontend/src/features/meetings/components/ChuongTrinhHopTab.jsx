import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import { Plus, Clock, FileText, Vote, Trash2, ArrowUp, ArrowDown, ExternalLink } from "lucide-react";
import StatusBadge from "../../../components/ui/StatusBadge";
import EmptyState from "../../../components/ui/EmptyState";
import ThemChuongTrinhModal from "./ThemChuongTrinhModal";

export default function MeetingAgendaTab({
  agendas = [],
  onAddAgenda,
  onDeleteAgenda
}) {
  const navigate = useNavigate();
  const [isModalOpen, setIsModalOpen] = useState(false);

  const totalDuration = agendas.reduce((sum, item) => sum + (Number(item.duration_minutes) || 0), 0);
  const totalVotingRequired = agendas.filter((item) => item.is_voting_required).length;

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
            Chương trình Nghị sự Phiên họp (Agenda)
          </h3>
          <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
            Tổng cộng {agendas.length} mục • Thời lượng dự kiến: {totalDuration} phút (~{(totalDuration / 60).toFixed(1)} giờ) • {totalVotingRequired} nội dung cần biểu quyết
          </div>
        </div>

        <button
          type="button"
          className="btn btn-primary btn-sm"
          onClick={() => setIsModalOpen(true)}
          style={{ gap: "4px" }}
        >
          <Plus size={14} />
          <span>Thêm mục thảo luận</span>
        </button>
      </div>

      {/* Agenda Items List */}
      {agendas.length === 0 ? (
        <div className="card">
          <EmptyState
            title="Chưa có nội dung trong chương trình"
            description="Hãy thêm các mục thảo luận hoặc gắn hồ sơ trình đã thẩm tra đạt yêu cầu vào chương trình phiên họp."
            actionButton={
              <button type="button" className="btn btn-primary btn-sm" onClick={() => setIsModalOpen(true)}>
                Thêm mục thảo luận đầu tiên
              </button>
            }
          />
        </div>
      ) : (
        <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)" }}>
          {agendas.map((item, index) => (
            <div
              key={item.id}
              className="card"
              style={{
                padding: "var(--space-4)",
                display: "flex",
                alignItems: "flex-start",
                gap: "var(--space-4)",
                borderLeft: item.is_voting_required ? "4px solid var(--color-primary)" : "1px solid var(--color-border)"
              }}
            >
              {/* Thứ tự mục */}
              <div
                style={{
                  width: "32px",
                  height: "32px",
                  borderRadius: "var(--radius-md)",
                  backgroundColor: "var(--color-bg-hover)",
                  color: "var(--color-text-primary)",
                  fontWeight: "var(--font-weight-bold)",
                  fontSize: "var(--font-size-sm)",
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center",
                  flexShrink: 0
                }}
              >
                {item.item_order || index + 1}
              </div>

              {/* Chi tiết nội dung */}
              <div style={{ flex: 1 }}>
                <div style={{ display: "flex", alignItems: "flex-start", justifyContent: "space-between", gap: "var(--space-3)", marginBottom: "4px" }}>
                  <div style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-medium)", color: "var(--color-text-primary)", lineHeight: 1.4 }}>
                    {item.title}
                  </div>

                  <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)", flexShrink: 0 }}>
                    {item.is_voting_required ? (
                      <StatusBadge status="info" label="Cần biểu quyết" size="sm" />
                    ) : (
                      <span style={{ fontSize: "11px", color: "var(--color-text-muted)" }}>Không biểu quyết</span>
                    )}

                    <button
                      type="button"
                      className="btn btn-sm"
                      onClick={() => onDeleteAgenda(item.id)}
                      title="Gỡ bỏ mục này"
                      style={{ color: "var(--color-danger)", border: "1px solid var(--color-border)", padding: "4px 8px" }}
                    >
                      <Trash2 size={13} />
                    </button>
                  </div>
                </div>

                {/* Sub details: Người trình bày, thời lượng, hồ sơ gắn kèm */}
                <div style={{ display: "flex", flexWrap: "wrap", alignItems: "center", gap: "var(--space-4)", fontSize: "var(--font-size-xs)", color: "var(--color-text-secondary)", marginTop: "var(--space-2)" }}>
                  <span>
                    Người báo cáo: <strong>{item.presenter}</strong>
                  </span>

                  <span style={{ display: "inline-flex", alignItems: "center", gap: "4px", color: "var(--color-text-muted)" }}>
                    <Clock size={13} />
                    {item.duration_minutes} phút
                  </span>

                  {item.dossier_id && (
                    <button
                      type="button"
                      onClick={() => navigate(`/dossiers?search=${item.dossier_id}`)}
                      style={{
                        display: "inline-flex",
                        alignItems: "center",
                        gap: "4px",
                        padding: "2px 8px",
                        backgroundColor: "var(--color-bg-hover)",
                        border: "1px solid var(--color-border)",
                        borderRadius: "var(--radius-sm)",
                        color: "var(--color-primary)",
                        fontWeight: "var(--font-weight-medium)",
                        cursor: "pointer",
                        fontSize: "12px"
                      }}
                      title="Bấm để xem chi tiết hồ sơ trình"
                    >
                      <FileText size={12} />
                      <span>Hồ sơ: #{item.dossier_id}</span>
                      <ExternalLink size={10} />
                    </button>
                  )}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Modal Thêm Agenda */}
      <ThemChuongTrinhModal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        onSave={(data) => {
          onAddAgenda(data);
          setIsModalOpen(false);
        }}
      />
    </div>
  );
}
