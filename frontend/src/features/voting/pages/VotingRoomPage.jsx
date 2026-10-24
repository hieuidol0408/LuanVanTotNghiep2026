import React, { useState, useEffect, useCallback } from "react";
import { useSearchParams } from "react-router-dom";
import {
  Vote,
  Lock,
  Unlock,
  CheckCircle2,
  XCircle,
  MinusCircle,
  Play,
  StopCircle,
  RefreshCw,
  Plus,
  ShieldAlert,
  Calendar,
  Award
} from "lucide-react";
import PageHeader from "../../../components/layout/PageHeader";
import StatusBadge from "../../../components/ui/StatusBadge";
import EmptyState from "../../../components/ui/EmptyState";
import ConfirmDialog from "../../../components/ui/ConfirmDialog";
import { useAuth } from "../../auth/context/AuthContext";
import { useToast } from "../../../components/ui/Toast";
import votingService from "../services/votingService";
import meetingService from "../../meetings/services/meetingService";

export default function VotingRoomPage() {
  const { user } = useAuth();
  const toast = useToast();
  const [searchParams] = useSearchParams();

  const [meetings, setMeetings] = useState([]);
  const [selectedMeetingId, setSelectedMeetingId] = useState(searchParams.get("meeting_id") || "");
  const [votings, setVotings] = useState([]);
  const [selectedVoting, setSelectedVoting] = useState(null);
  const [loading, setLoading] = useState(false);
  const [castingVote, setCastingVote] = useState(false);
  const [voteChoice, setVoteChoice] = useState("");

  // Modal / Confirm states
  const [confirmDialog, setConfirmDialog] = useState({
    isOpen: false,
    title: "",
    message: "",
    confirmLabel: "Xác nhận",
    confirmVariant: "primary",
    onConfirm: () => {}
  });

  const [createModalOpen, setCreateModalOpen] = useState(false);
  const [createFormData, setCreateFormData] = useState({
    agenda_id: "",
    title: "",
    voting_type: "SECRET",
    pass_threshold: 50,
    notes: ""
  });
  const [currentMeetingAgendas, setCurrentMeetingAgendas] = useState([]);

  const isSecretaryOrChair = user && ["CHAIR", "SECRETARY", "ADMIN"].includes(user.role);
  const isCouncilMember = user && ["CHAIR", "VICE_CHAIR", "SECRETARY", "MEMBER"].includes(user.role);

  // 1. Tải danh sách phiên họp
  const fetchMeetings = useCallback(async () => {
    try {
      const res = await meetingService.getMeetings({ perPage: 20 });
      if (res.success && res.data && res.data.length > 0) {
        setMeetings(res.data);
        if (!selectedMeetingId) {
          setSelectedMeetingId(String(res.data[0].id));
        }
      }
    } catch (err) {
      console.warn("Lỗi khi tải danh sách phiên họp:", err);
    }
  }, [selectedMeetingId]);

  useEffect(() => {
    fetchMeetings();
  }, [fetchMeetings]);

  // 2. Tải danh sách nội dung biểu quyết của phiên họp được chọn
  const fetchVotings = useCallback(async () => {
    if (!selectedMeetingId) return;
    setLoading(true);
    try {
      const res = await votingService.getMeetingVotings(selectedMeetingId);
      if (res.success) {
        setVotings(res.data);
        if (res.data.length > 0) {
          setSelectedVoting((prev) => {
            const found = res.data.find((v) => prev && v.id === prev.id);
            return found || res.data[0];
          });
        } else {
          setSelectedVoting(null);
        }
      } else {
        toast.error(res.message || "Không thể tải danh sách biểu quyết");
      }

      // Tải kèm agenda của phiên họp để dùng cho modal tạo biểu quyết
      const meetingDetail = await meetingService.getMeetingById(selectedMeetingId);
      if (meetingDetail.success && meetingDetail.data?.agendas) {
        setCurrentMeetingAgendas(meetingDetail.data.agendas);
      }
    } catch (err) {
      toast.error("Lỗi kết nối khi tải danh sách biểu quyết");
    } finally {
      setLoading(false);
    }
  }, [selectedMeetingId, toast]);

  useEffect(() => {
    fetchVotings();
  }, [fetchVotings]);

  // Handler: Mở cổng biểu quyết
  const handleOpenVoting = (votingId) => {
    setConfirmDialog({
      isOpen: true,
      title: "Mở cổng biểu quyết",
      message: "Khi mở cổng, các thành viên Hội đồng trường có mặt sẽ được phép bỏ phiếu trực tuyến. Bạn có chắc chắn?",
      confirmLabel: "Mở cổng biểu quyết",
      confirmVariant: "primary",
      onConfirm: async () => {
        try {
          const res = await votingService.openVoting(votingId);
          if (res.success) {
            toast.success(res.message);
            fetchVotings();
          } else {
            toast.error(res.message || "Không thể mở cổng biểu quyết");
          }
        } catch (err) {
          toast.error("Lỗi khi mở biểu quyết");
        }
      }
    });
  };

  // Handler: Đóng cổng & Kiểm phiếu
  const handleCloseVoting = (votingId) => {
    setConfirmDialog({
      isOpen: true,
      title: "Đóng cổng & Tự động kiểm phiếu",
      message: "Hệ thống sẽ khóa nhận thêm phiếu mới và đối soát tỷ lệ tán thành với ngưỡng quy định để xác định kết quả thông qua. Bạn có chắc chắn?",
      confirmLabel: "Đóng & Kiểm phiếu",
      confirmVariant: "danger",
      onConfirm: async () => {
        try {
          const res = await votingService.closeVoting(votingId);
          if (res.success) {
            toast.success(res.message);
            fetchVotings();
          } else {
            toast.error(res.message || "Không thể đóng biểu quyết");
          }
        } catch (err) {
          toast.error("Lỗi khi đóng biểu quyết");
        }
      }
    });
  };

  // Handler: Bỏ phiếu (Cast vote)
  const handleCastVote = async () => {
    if (!selectedVoting || !voteChoice) {
      toast.error("Vui lòng chọn ý kiến biểu quyết trước khi gửi");
      return;
    }

    setCastingVote(true);
    try {
      const res = await votingService.castVote(selectedVoting.id, {
        choice: voteChoice,
        notes: null
      });

      if (res.success) {
        toast.success("Lá phiếu của bạn đã được ghi nhận vào hệ thống thành công!");
        setVoteChoice("");
        fetchVotings();
      } else {
        toast.error(res.message || "Không thể gửi phiếu biểu quyết");
      }
    } catch (err) {
      toast.error("Lỗi kết nối khi gửi phiếu biểu quyết");
    } finally {
      setCastingVote(false);
    }
  };

  // Handler: Tạo nội dung biểu quyết mới
  const handleCreateVoting = async (e) => {
    e.preventDefault();
    if (!createFormData.agenda_id) {
      toast.error("Vui lòng chọn mục chương trình họp liên kết");
      return;
    }
    if (!createFormData.title.trim()) {
      toast.error("Vui lòng nhập nội dung biểu quyết");
      return;
    }

    try {
      const res = await votingService.createVoting(selectedMeetingId, createFormData);
      if (res.success) {
        toast.success(res.message);
        setCreateModalOpen(false);
        setCreateFormData({
          agenda_id: "",
          title: "",
          voting_type: "SECRET",
          pass_threshold: 50,
          notes: ""
        });
        fetchVotings();
      } else {
        toast.error(res.message || "Không thể tạo nội dung biểu quyết");
      }
    } catch (err) {
      toast.error("Lỗi khi tạo nội dung biểu quyết");
    }
  };

  return (
    <div>
      <PageHeader
        title="Phòng Biểu quyết & Kiểm phiếu Điện tử"
        description="Khởi tạo nội dung lấy ý kiến, bỏ phiếu kín/công khai và tự động đối soát tỷ lệ thông qua theo Quy chế Hội đồng trường STU."
        breadcrumbItems={[
          { label: "Bảng điều hành", path: "/" },
          { label: "Phiên họp", path: "/meetings" },
          { label: "Biểu quyết & Kiểm phiếu", path: "" }
        ]}
        actions={
          <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)" }}>
            <button
              type="button"
              className="btn btn-secondary"
              onClick={fetchVotings}
              title="Tải lại dữ liệu"
            >
              <RefreshCw size={16} />
              <span>Làm mới</span>
            </button>

            {isSecretaryOrChair && (
              <button
                type="button"
                className="btn btn-primary"
                onClick={() => setCreateModalOpen(true)}
              >
                <Plus size={16} />
                <span>Tạo nội dung biểu quyết</span>
              </button>
            )}
          </div>
        }
      />

      {/* 1. Thanh chọn Phiên họp */}
      <div
        className="card"
        style={{
          padding: "var(--space-3) var(--space-4)",
          marginBottom: "var(--space-4)",
          display: "flex",
          flexWrap: "wrap",
          alignItems: "center",
          justifyContent: "space-between",
          gap: "var(--space-3)"
        }}
      >
        <div style={{ display: "flex", alignItems: "center", gap: "var(--space-3)" }}>
          <Calendar size={18} style={{ color: "var(--color-primary)" }} />
          <span style={{ fontSize: "var(--font-size-sm)", fontWeight: "var(--font-weight-medium)" }}>
            Chọn phiên họp:
          </span>
          <select
            className="form-control"
            style={{ width: "auto", minWidth: "280px" }}
            value={selectedMeetingId}
            onChange={(e) => setSelectedMeetingId(e.target.value)}
          >
            {meetings.map((m) => (
              <option key={m.id} value={m.id}>
                {m.title} ({m.meeting_date})
              </option>
            ))}
          </select>
        </div>

        <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
          {isCouncilMember ? (
            <span style={{ color: "var(--color-success)", display: "flex", alignItems: "center", gap: "4px" }}>
              <Award size={14} /> Bạn có thẩm quyền biểu quyết với tư cách Thành viên HĐT
            </span>
          ) : (
            <span style={{ color: "var(--color-warning)", display: "flex", alignItems: "center", gap: "4px" }}>
              <ShieldAlert size={14} /> Bạn đang tham gia với tư cách Khách mời/Đơn vị (Chỉ xem)
            </span>
          )}
        </div>
      </div>

      {/* 2. Bố cục 2 cột: Danh sách nội dung bên trái, Phòng bỏ phiếu & Kết quả bên phải */}
      <div style={{ display: "grid", gridTemplateColumns: "1fr 2fr", gap: "var(--space-4)" }}>
        {/* Cột trái: Danh sách các nội dung */}
        <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)" }}>
          <div style={{ fontSize: "var(--font-size-sm)", fontWeight: "var(--font-weight-bold)", color: "var(--color-text-primary)" }}>
            Nội dung biểu quyết ({votings.length})
          </div>

          {loading ? (
            <div className="card" style={{ padding: "var(--space-6)", textAlign: "center", color: "var(--color-text-muted)" }}>
              Đang tải danh sách biểu quyết...
            </div>
          ) : votings.length === 0 ? (
            <div className="card">
              <EmptyState
                icon={Vote}
                title="Chưa có nội dung biểu quyết"
                description="Thư ký hoặc Chủ tịch có thể nhấn 'Tạo nội dung biểu quyết' để bắt đầu."
              />
            </div>
          ) : (
            votings.map((v) => {
              const isSelected = selectedVoting && selectedVoting.id === v.id;
              const isSecret = v.voting_type === "SECRET";

              return (
                <div
                  key={v.id}
                  onClick={() => setSelectedVoting(v)}
                  className="card"
                  style={{
                    padding: "var(--space-3) var(--space-4)",
                    cursor: "pointer",
                    borderLeft: isSelected ? "4px solid var(--color-primary)" : "1px solid var(--color-border)",
                    backgroundColor: isSelected ? "rgba(11, 46, 89, 0.04)" : "var(--color-bg-card)",
                    transition: "all var(--transition-fast)"
                  }}
                >
                  <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "4px" }}>
                    <span
                      style={{
                        fontSize: "11px",
                        fontWeight: "var(--font-weight-bold)",
                        display: "inline-flex",
                        alignItems: "center",
                        gap: "4px",
                        color: isSecret ? "var(--color-warning)" : "var(--color-primary)"
                      }}
                    >
                      {isSecret ? <Lock size={12} /> : <Unlock size={12} />}
                      {v.voting_type_label}
                    </span>

                    <StatusBadge
                      status={v.status === "OPENING" ? "success" : v.status === "CLOSED" ? "neutral" : "warning"}
                      label={v.status_label}
                      size="sm"
                    />
                  </div>

                  <div style={{ fontSize: "var(--font-size-sm)", fontWeight: "var(--font-weight-semibold)", color: "var(--color-text-primary)", marginBottom: "4px" }}>
                    {v.title}
                  </div>

                  <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", fontSize: "12px", color: "var(--color-text-muted)" }}>
                    <span>Chuẩn: <strong>{v.pass_threshold}%</strong></span>
                    {v.result !== "PENDING" && (
                      <span style={{ fontWeight: "var(--font-weight-bold)", color: v.result === "PASSED" ? "var(--color-success)" : "var(--color-danger)" }}>
                        {v.result_label}
                      </span>
                    )}
                  </div>
                </div>
              );
            })
          )}
        </div>

        {/* Cột phải: Bảng điều khiển bỏ phiếu & Kết quả */}
        <div>
          {selectedVoting ? (
            <div className="card" style={{ padding: "var(--space-5)", display: "flex", flexDirection: "column", gap: "var(--space-5)" }}>
              {/* Tiêu đề & Thông tin cơ bản */}
              <div style={{ borderBottom: "1px solid var(--color-border)", paddingBottom: "var(--space-4)" }}>
                <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "var(--space-2)" }}>
                  <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)" }}>
                    <span
                      style={{
                        fontSize: "12px",
                        fontWeight: "var(--font-weight-bold)",
                        padding: "2px 8px",
                        borderRadius: "var(--radius-sm)",
                        backgroundColor: selectedVoting.voting_type === "SECRET" ? "var(--color-warning-bg)" : "var(--color-primary-light)",
                        color: selectedVoting.voting_type === "SECRET" ? "var(--color-warning-text)" : "var(--color-primary)"
                      }}
                    >
                      {selectedVoting.voting_type_label}
                    </span>
                    <span style={{ fontSize: "12px", color: "var(--color-text-muted)" }}>
                      Ngưỡng quy định: <strong>{selectedVoting.pass_threshold}%</strong> thành viên có mặt
                    </span>
                  </div>

                  <StatusBadge
                    status={selectedVoting.status === "OPENING" ? "success" : selectedVoting.status === "CLOSED" ? "neutral" : "warning"}
                    label={selectedVoting.status_label}
                  />
                </div>

                <h3 style={{ fontSize: "var(--font-size-lg)", fontWeight: "var(--font-weight-bold)", color: "var(--color-text-primary)", marginBottom: "var(--space-2)" }}>
                  {selectedVoting.title}
                </h3>

                {/* Nút hành động dành cho Thư ký / Chủ tịch */}
                {isSecretaryOrChair && (
                  <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)", marginTop: "var(--space-3)" }}>
                    {selectedVoting.status === "PENDING" && (
                      <button
                        type="button"
                        className="btn btn-primary btn-sm"
                        onClick={() => handleOpenVoting(selectedVoting.id)}
                      >
                        <Play size={14} />
                        <span>Mở cổng biểu quyết</span>
                      </button>
                    )}

                    {selectedVoting.status === "OPENING" && (
                      <button
                        type="button"
                        className="btn btn-danger btn-sm"
                        onClick={() => handleCloseVoting(selectedVoting.id)}
                      >
                        <StopCircle size={14} />
                        <span>Đóng cổng & Kiểm phiếu</span>
                      </button>
                    )}
                  </div>
                )}
              </div>

              {/* KHU VỰC BỎ PHIẾU (Dành cho thành viên HĐT khi trạng thái OPENING) */}
              {selectedVoting.status === "OPENING" && (
                <div
                  style={{
                    padding: "var(--space-4)",
                    backgroundColor: "rgba(11, 46, 89, 0.03)",
                    border: "1px dashed var(--color-primary)",
                    borderRadius: "var(--radius-lg)"
                  }}
                >
                  <div style={{ fontSize: "var(--font-size-sm)", fontWeight: "var(--font-weight-bold)", marginBottom: "var(--space-3)", color: "var(--color-primary)" }}>
                    LÁ PHIẾU BIỂU QUYẾT ĐIỆN TỬ
                  </div>

                  {!isCouncilMember ? (
                    <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)", color: "var(--color-warning)", fontSize: "var(--font-size-sm)" }}>
                      <ShieldAlert size={16} />
                      <span>Bạn không có quyền bỏ phiếu do không phải là Thành viên chính thức của Hội đồng trường.</span>
                    </div>
                  ) : (
                    <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)" }}>
                      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr 1fr", gap: "var(--space-3)" }}>
                        {/* Lựa chọn 1: Tán thành */}
                        <button
                          type="button"
                          onClick={() => setVoteChoice("VOTE_FOR")}
                          style={{
                            padding: "var(--space-3)",
                            borderRadius: "var(--radius-md)",
                            border: voteChoice === "VOTE_FOR" ? "2px solid var(--color-success)" : "1px solid var(--color-border)",
                            backgroundColor: voteChoice === "VOTE_FOR" ? "rgba(16, 185, 129, 0.1)" : "var(--color-bg-card)",
                            color: "var(--color-success)",
                            fontWeight: "var(--font-weight-bold)",
                            cursor: "pointer",
                            display: "flex",
                            flexDirection: "column",
                            alignItems: "center",
                            gap: "var(--space-2)"
                          }}
                        >
                          <CheckCircle2 size={24} />
                          <span>TÁN THÀNH</span>
                        </button>

                        {/* Lựa chọn 2: Không tán thành */}
                        <button
                          type="button"
                          onClick={() => setVoteChoice("VOTE_AGAINST")}
                          style={{
                            padding: "var(--space-3)",
                            borderRadius: "var(--radius-md)",
                            border: voteChoice === "VOTE_AGAINST" ? "2px solid var(--color-danger)" : "1px solid var(--color-border)",
                            backgroundColor: voteChoice === "VOTE_AGAINST" ? "rgba(239, 68, 68, 0.1)" : "var(--color-bg-card)",
                            color: "var(--color-danger)",
                            fontWeight: "var(--font-weight-bold)",
                            cursor: "pointer",
                            display: "flex",
                            flexDirection: "column",
                            alignItems: "center",
                            gap: "var(--space-2)"
                          }}
                        >
                          <XCircle size={24} />
                          <span>KHÔNG TÁN THÀNH</span>
                        </button>

                        {/* Lựa chọn 3: Phiếu trắng / Ý kiến khác */}
                        <button
                          type="button"
                          onClick={() => setVoteChoice("VOTE_ABSTAIN")}
                          style={{
                            padding: "var(--space-3)",
                            borderRadius: "var(--radius-md)",
                            border: voteChoice === "VOTE_ABSTAIN" ? "2px solid var(--color-text-muted)" : "1px solid var(--color-border)",
                            backgroundColor: voteChoice === "VOTE_ABSTAIN" ? "rgba(100, 116, 139, 0.1)" : "var(--color-bg-card)",
                            color: "var(--color-text-muted)",
                            fontWeight: "var(--font-weight-bold)",
                            cursor: "pointer",
                            display: "flex",
                            flexDirection: "column",
                            alignItems: "center",
                            gap: "var(--space-2)"
                          }}
                        >
                          <MinusCircle size={24} />
                          <span>KHÔNG Ý KIẾN / TRẮNG</span>
                        </button>
                      </div>

                      <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginTop: "var(--space-2)" }}>
                        <span style={{ fontSize: "11px", color: "var(--color-text-muted)" }}>
                          * Lưu ý: Mỗi thành viên chỉ được gửi phiếu 01 lần duy nhất theo quy định.
                        </span>

                        <button
                          type="button"
                          className="btn btn-primary"
                          disabled={!voteChoice || castingVote}
                          onClick={handleCastVote}
                        >
                          {castingVote ? "Đang gửi phiếu..." : "Xác nhận gửi phiếu"}
                        </button>
                      </div>
                    </div>
                  )}
                </div>
              )}

              {/* BẢNG TỔNG HỢP TIẾN ĐỘ & KẾT QUẢ KIỂM PHIẾU */}
              <div>
                <div style={{ fontSize: "var(--font-size-sm)", fontWeight: "var(--font-weight-bold)", marginBottom: "var(--space-3)", color: "var(--color-text-primary)" }}>
                  Kết quả kiểm phiếu & Thống kê
                </div>

                {/* Kết quả chung */}
                {selectedVoting.result !== "PENDING" && (
                  <div
                    style={{
                      padding: "var(--space-3) var(--space-4)",
                      borderRadius: "var(--radius-md)",
                      marginBottom: "var(--space-4)",
                      backgroundColor: selectedVoting.result === "PASSED" ? "rgba(16, 185, 129, 0.1)" : "rgba(239, 68, 68, 0.1)",
                      border: `1px solid ${selectedVoting.result === "PASSED" ? "var(--color-success)" : "var(--color-danger)"}`,
                      display: "flex",
                      alignItems: "center",
                      justifyContent: "space-between"
                    }}
                  >
                    <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)" }}>
                      {selectedVoting.result === "PASSED" ? (
                        <CheckCircle2 size={20} style={{ color: "var(--color-success)" }} />
                      ) : (
                        <XCircle size={20} style={{ color: "var(--color-danger)" }} />
                      )}
                      <span style={{ fontWeight: "var(--font-weight-bold)", color: selectedVoting.result === "PASSED" ? "var(--color-success)" : "var(--color-danger)" }}>
                        KẾT LUẬN: {selectedVoting.result === "PASSED" ? "ĐÃ ĐƯỢC HỘI ĐỒNG THÔNG QUA" : "KHÔNG ĐƯỢC HỘI ĐỒNG THÔNG QUA"}
                      </span>
                    </div>

                    <span style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
                      Đóng lúc: {selectedVoting.closed_at || "Vừa xong"}
                    </span>
                  </div>
                )}

                {/* Thanh tiến độ trực quan */}
                <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)" }}>
                  {/* Tán thành */}
                  <div>
                    <div style={{ display: "flex", justifyContent: "space-between", fontSize: "12px", marginBottom: "4px" }}>
                      <span style={{ color: "var(--color-success)", fontWeight: "var(--font-weight-semibold)" }}>
                        Tán thành: {selectedVoting.stats?.votes_for || 0} phiếu ({selectedVoting.stats?.percentage_for || 0}%)
                      </span>
                      <span style={{ color: "var(--color-text-muted)" }}>
                        Chuẩn: {selectedVoting.pass_threshold}%
                      </span>
                    </div>
                    <div style={{ height: "10px", backgroundColor: "var(--color-bg-muted)", borderRadius: "var(--radius-full)", overflow: "hidden" }}>
                      <div
                        style={{
                          height: "100%",
                          width: `${selectedVoting.stats?.percentage_for || 0}%`,
                          backgroundColor: "var(--color-success)",
                          transition: "width 0.3s ease"
                        }}
                      />
                    </div>
                  </div>

                  {/* Không tán thành */}
                  <div>
                    <div style={{ display: "flex", justifyContent: "space-between", fontSize: "12px", marginBottom: "4px" }}>
                      <span style={{ color: "var(--color-danger)", fontWeight: "var(--font-weight-semibold)" }}>
                        Không tán thành: {selectedVoting.stats?.votes_against || 0} phiếu ({selectedVoting.stats?.percentage_against || 0}%)
                      </span>
                    </div>
                    <div style={{ height: "10px", backgroundColor: "var(--color-bg-muted)", borderRadius: "var(--radius-full)", overflow: "hidden" }}>
                      <div
                        style={{
                          height: "100%",
                          width: `${selectedVoting.stats?.percentage_against || 0}%`,
                          backgroundColor: "var(--color-danger)",
                          transition: "width 0.3s ease"
                        }}
                      />
                    </div>
                  </div>

                  {/* Trắng / Ý kiến khác */}
                  <div>
                    <div style={{ display: "flex", justifyContent: "space-between", fontSize: "12px", marginBottom: "4px" }}>
                      <span style={{ color: "var(--color-text-muted)", fontWeight: "var(--font-weight-semibold)" }}>
                        Không ý kiến / Phiếu trắng: {selectedVoting.stats?.votes_abstain || 0} phiếu ({selectedVoting.stats?.percentage_abstain || 0}%)
                      </span>
                    </div>
                    <div style={{ height: "10px", backgroundColor: "var(--color-bg-muted)", borderRadius: "var(--radius-full)", overflow: "hidden" }}>
                      <div
                        style={{
                          height: "100%",
                          width: `${selectedVoting.stats?.percentage_abstain || 0}%`,
                          backgroundColor: "var(--color-text-muted)",
                          transition: "width 0.3s ease"
                        }}
                      />
                    </div>
                  </div>

                  <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)", marginTop: "var(--space-2)" }}>
                    Tổng số phiếu đã tiếp nhận: <strong>{selectedVoting.stats?.total_votes || 0}</strong> phiếu.
                  </div>
                </div>
              </div>
            </div>
          ) : (
            <div className="card">
              <EmptyState
                icon={Vote}
                title="Chọn một nội dung biểu quyết"
                description="Vui lòng nhấn vào một nội dung biểu quyết ở danh sách bên trái để mở phòng bỏ phiếu hoặc theo dõi kiểm phiếu."
              />
            </div>
          )}
        </div>
      </div>

      {/* Modal: Tạo nội dung biểu quyết mới */}
      {createModalOpen && (
        <div
          style={{
            position: "fixed",
            top: 0,
            left: 0,
            right: 0,
            bottom: 0,
            backgroundColor: "rgba(0, 0, 0, 0.5)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            zIndex: 9999,
            padding: "var(--space-4)"
          }}
        >
          <div
            className="card"
            style={{
              width: "100%",
              maxWidth: "520px",
              padding: "var(--space-5)",
              display: "flex",
              flexDirection: "column",
              gap: "var(--space-4)"
            }}
          >
            <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between" }}>
              <h3 style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-bold)" }}>
                Tạo nội dung Biểu quyết mới
              </h3>
              <button
                type="button"
                className="btn btn-secondary btn-sm"
                onClick={() => setCreateModalOpen(false)}
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleCreateVoting} style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)" }}>
              {/* Mục chương trình */}
              <div>
                <label className="form-label" style={{ display: "block", marginBottom: "4px", fontSize: "var(--font-size-sm)" }}>
                  Liên kết mục Chương trình họp (Agenda) *
                </label>
                <select
                  className="form-control"
                  required
                  value={createFormData.agenda_id}
                  onChange={(e) => setCreateFormData({ ...createFormData, agenda_id: e.target.value })}
                >
                  <option value="">-- Chọn mục chương trình --</option>
                  {currentMeetingAgendas.map((ag) => (
                    <option key={ag.id} value={ag.id}>
                      {ag.item_order ? `Mục ${ag.item_order}: ` : ""}{ag.title}
                    </option>
                  ))}
                </select>
              </div>

              {/* Tên nội dung biểu quyết */}
              <div>
                <label className="form-label" style={{ display: "block", marginBottom: "4px", fontSize: "var(--font-size-sm)" }}>
                  Nội dung lấy ý kiến biểu quyết *
                </label>
                <textarea
                  className="form-control"
                  rows={3}
                  required
                  placeholder="Ví dụ: Biểu quyết thông qua Đề án thành lập Khoa Vi điện tử..."
                  value={createFormData.title}
                  onChange={(e) => setCreateFormData({ ...createFormData, title: e.target.value })}
                />
              </div>

              {/* Hình thức & Ngưỡng thông qua */}
              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "var(--space-3)" }}>
                <div>
                  <label className="form-label" style={{ display: "block", marginBottom: "4px", fontSize: "var(--font-size-sm)" }}>
                    Hình thức bỏ phiếu
                  </label>
                  <select
                    className="form-control"
                    value={createFormData.voting_type}
                    onChange={(e) => setCreateFormData({ ...createFormData, voting_type: e.target.value })}
                  >
                    <option value="SECRET">Bỏ phiếu kín</option>
                    <option value="OPEN">Biểu quyết công khai</option>
                  </select>
                </div>

                <div>
                  <label className="form-label" style={{ display: "block", marginBottom: "4px", fontSize: "var(--font-size-sm)" }}>
                    Ngưỡng thông qua (%)
                  </label>
                  <input
                    type="number"
                    min="50"
                    max="100"
                    className="form-control"
                    value={createFormData.pass_threshold}
                    onChange={(e) => setCreateFormData({ ...createFormData, pass_threshold: Number(e.target.value) })}
                  />
                </div>
              </div>

              <div style={{ display: "flex", justifyContent: "flex-end", gap: "var(--space-2)", marginTop: "var(--space-3)" }}>
                <button
                  type="button"
                  className="btn btn-secondary"
                  onClick={() => setCreateModalOpen(false)}
                >
                  Hủy bỏ
                </button>
                <button
                  type="submit"
                  className="btn btn-primary"
                >
                  Tạo biểu quyết
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Lifecycle Confirm Dialog */}
      <ConfirmDialog
        isOpen={confirmDialog.isOpen}
        title={confirmDialog.title}
        message={confirmDialog.message}
        confirmLabel={confirmDialog.confirmLabel}
        confirmVariant={confirmDialog.confirmVariant}
        onConfirm={() => {
          confirmDialog.onConfirm();
          setConfirmDialog((prev) => ({ ...prev, isOpen: false }));
        }}
        onCancel={() => setConfirmDialog((prev) => ({ ...prev, isOpen: false }))}
      />
    </div>
  );
}
