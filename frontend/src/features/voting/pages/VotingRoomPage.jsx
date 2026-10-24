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
  Award,
  UserCheck,
  FileText,
  ShieldCheck,
  ChevronDown,
  ChevronUp
} from "lucide-react";
import PageHeader from "../../../components/layout/PageHeader";
import StatusBadge from "../../../components/ui/StatusBadge";
import EmptyState from "../../../components/ui/EmptyState";
import ConfirmDialog from "../../../components/ui/ConfirmDialog";
import { useAuth } from "../../auth/context/AuthContext";
import { useToast } from "../../../components/ui/Toast";
import votingService from "../services/votingService";
import meetingService from "../../meetings/services/meetingService";
import councilService from "../../councils/services/councilService";

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

  // Quản lý tư cách biểu quyết (Bản thân hoặc theo ủy quyền)
  const [selectedProxyId, setSelectedProxyId] = useState("");

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

  // Modal Quản lý Giấy ủy quyền
  const [authModalOpen, setAuthModalOpen] = useState(false);
  const [meetingAuthorizations, setMeetingAuthorizations] = useState([]);
  const [councilMembers, setCouncilMembers] = useState([]);
  const [showAddAuthForm, setShowAddAuthForm] = useState(false);
  const [authFormData, setAuthFormData] = useState({
    authorizer_member_id: "",
    authorized_member_id: "",
    scope: "Toàn bộ nội dung biểu quyết tại phiên họp",
    document: "giay_uy_quyen.pdf",
    status: "Hợp lệ"
  });
  const [submittingAuth, setSubmittingAuth] = useState(false);

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

  // 2. Tải chi tiết nội dung biểu quyết (kèm danh sách ủy quyền và kiểm phiếu)
  const fetchVotingDetail = useCallback(async (votingId) => {
    try {
      const res = await votingService.getVotingById(votingId);
      if (res.success && res.data) {
        setSelectedVoting(res.data);
      }
    } catch (err) {
      console.warn("Lỗi khi tải chi tiết biểu quyết:", err);
    }
  }, []);

  // 3. Tải danh sách nội dung biểu quyết của phiên họp được chọn
  const fetchVotings = useCallback(async () => {
    if (!selectedMeetingId) return;
    setLoading(true);
    try {
      const res = await votingService.getMeetingVotings(selectedMeetingId);
      if (res.success) {
        setVotings(res.data);
        if (res.data.length > 0) {
          const target = res.data[0];
          fetchVotingDetail(target.id);
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
  }, [selectedMeetingId, fetchVotingDetail, toast]);

  useEffect(() => {
    fetchVotings();
  }, [fetchVotings]);

  // 4. Tải danh sách Giấy ủy quyền của phiên họp
  const fetchAuthorizations = useCallback(async () => {
    if (!selectedMeetingId) return;
    try {
      const [authRes, memberRes] = await Promise.all([
        votingService.getAuthorizations(selectedMeetingId),
        councilService.getCouncilMembers()
      ]);
      if (authRes.success && authRes.data) {
        setMeetingAuthorizations(authRes.data);
      }
      if (memberRes.success && memberRes.data) {
        setCouncilMembers(memberRes.data);
      }
    } catch (err) {
      console.warn("Lỗi tải ủy quyền:", err);
    }
  }, [selectedMeetingId]);

  // Mở modal ủy quyền
  const handleOpenAuthModal = () => {
    fetchAuthorizations();
    setShowAddAuthForm(false);
    setAuthModalOpen(true);
  };

  // Nộp giấy ủy quyền mới
  const handleCreateAuthSubmit = async (e) => {
    e.preventDefault();
    if (!authFormData.authorizer_member_id || !authFormData.authorized_member_id) {
      toast.warning("Vui lòng chọn người ủy quyền và người nhận ủy quyền.");
      return;
    }
    if (authFormData.authorizer_member_id === authFormData.authorized_member_id) {
      toast.error("Người nhận ủy quyền phải khác người ủy quyền.");
      return;
    }

    setSubmittingAuth(true);
    try {
      const payload = {
        authorizer_member_id: Number(authFormData.authorizer_member_id),
        authorized_member_id: Number(authFormData.authorized_member_id),
        scope: authFormData.scope,
        document: authFormData.document,
        status: authFormData.status
      };

      const res = await votingService.createAuthorization(selectedMeetingId, payload);
      if (res.success) {
        toast.success("Tiếp nhận giấy ủy quyền biểu quyết thành công!");
        setShowAddAuthForm(false);
        fetchAuthorizations();
        if (selectedVoting) {
          fetchVotingDetail(selectedVoting.id);
        }
      } else {
        toast.error(res.message || "Nộp giấy ủy quyền thất bại");
      }
    } catch (err) {
      toast.error("Lỗi kết nối khi nộp giấy ủy quyền");
    } finally {
      setSubmittingAuth(false);
    }
  };

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
            fetchVotingDetail(votingId);
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
            fetchVotingDetail(votingId);
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

  // Handler: Bỏ phiếu (Cast vote - Hỗ trợ bản thân hoặc theo ủy quyền)
  const handleCastVote = async () => {
    if (!selectedVoting || !voteChoice) {
      toast.error("Vui lòng chọn ý kiến biểu quyết trước khi gửi");
      return;
    }

    setCastingVote(true);
    try {
      const payload = {
        choice: voteChoice,
        authorization_id: selectedProxyId ? Number(selectedProxyId) : null
      };

      const res = await votingService.castVote(selectedVoting.id, payload);

      if (res.success) {
        toast.success(res.message || "Lá phiếu biểu quyết đã được ghi nhận thành công!");
        setVoteChoice("");
        fetchVotingDetail(selectedVoting.id);
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
        toast.error(res.message || "Không thể tạo biểu quyết");
      }
    } catch (err) {
      toast.error("Lỗi khi tạo biểu quyết");
    }
  };

  // Xác định xem tư cách biểu quyết hiện tại đã hoàn tất bỏ phiếu chưa
  const currentDelegation = selectedVoting?.delegations?.find(
    (d) => String(d.authorization_id) === String(selectedProxyId)
  );

  const isCurrentPersonaVoted = selectedProxyId
    ? currentDelegation?.has_voted
    : selectedVoting?.has_voted;

  const currentPersonaChoice = selectedProxyId
    ? currentDelegation?.choice
    : selectedVoting?.user_choice;

  return (
    <div>
      {/* 1. Tiêu đề trang & Thanh công cụ */}
      <PageHeader
        title="Biểu quyết & Kiểm phiếu Điện tử"
        description="Hệ thống bỏ phiếu trực tuyến thời gian thực của Hội đồng trường STU, hỗ trợ biểu quyết công khai, bỏ phiếu kín và biểu quyết theo ủy quyền hợp lệ."
        breadcrumbItems={[
          { label: "Bảng điều hành", path: "/" },
          { label: "Biểu quyết & Kiểm phiếu", path: "/voting" }
        ]}
        actions={
          <div style={{ display: "flex", gap: "var(--space-2)" }}>
            {/* Nút Giấy ủy quyền phiên họp */}
            <button
              className="btn btn-outline"
              onClick={handleOpenAuthModal}
              title="Quản lý danh sách ủy quyền biểu quyết"
            >
              <UserCheck size={16} />
              Giấy ủy quyền ({meetingAuthorizations.length})
            </button>

            {/* Nút Làm mới */}
            <button
              className="btn btn-outline"
              onClick={() => {
                fetchVotings();
                if (selectedVoting) fetchVotingDetail(selectedVoting.id);
              }}
              disabled={loading}
              title="Làm mới dữ liệu"
            >
              <RefreshCw size={16} className={loading ? "animate-spin" : ""} />
              Làm mới
            </button>

            {/* Nút Tạo biểu quyết (Thư ký / Chủ tịch) */}
            {isSecretaryOrChair && (
              <button
                className="btn btn-primary"
                onClick={() => setCreateModalOpen(true)}
              >
                <Plus size={16} />
                Thêm nội dung biểu quyết
              </button>
            )}
          </div>
        }
      />

      {/* 2. Thanh chọn Phiên họp */}
      <div
        className="card"
        style={{
          padding: "var(--space-4)",
          marginBottom: "var(--space-4)",
          display: "flex",
          alignItems: "center",
          gap: "var(--space-3)",
          backgroundColor: "var(--color-bg-surface)"
        }}
      >
        <Calendar size={18} style={{ color: "var(--color-primary)" }} />
        <span style={{ fontSize: "14px", fontWeight: "600", minWidth: "120px" }}>
          Phiên họp HĐT:
        </span>
        <select
          className="input"
          style={{ flex: 1 }}
          value={selectedMeetingId}
          onChange={(e) => setSelectedMeetingId(e.target.value)}
        >
          {meetings.map((m) => (
            <option key={m.id} value={m.id}>
              {m.title} ({m.date ? new Date(m.date).toLocaleDateString("vi-VN") : "Chưa ấn định"})
            </option>
          ))}
        </select>
      </div>

      {/* 3. Bố cục 2 cột: Danh sách nội dung bên trái, Phòng bỏ phiếu bên phải */}
      <div style={{ display: "grid", gridTemplateColumns: "360px 1fr", gap: "var(--space-4)" }}>
        {/* Cột trái: Danh sách các nội dung biểu quyết */}
        <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-2)" }}>
          <div style={{ fontSize: "13px", fontWeight: "600", color: "var(--color-text-secondary)", marginBottom: "4px" }}>
            DANH SÁCH NỘI DUNG BIỂU QUYẾT ({votings.length})
          </div>

          {loading ? (
            <div className="card" style={{ padding: "var(--space-6)", textAlign: "center", color: "var(--color-text-muted)" }}>
              <RefreshCw size={20} className="animate-spin" style={{ margin: "0 auto var(--space-2)" }} />
              Đang tải nội dung...
            </div>
          ) : votings.length === 0 ? (
            <div className="card" style={{ padding: "var(--space-4)" }}>
              <EmptyState
                icon={Vote}
                title="Chưa có biểu quyết nào"
                description="Phiên họp này hiện chưa có nội dung lấy ý kiến biểu quyết."
              />
            </div>
          ) : (
            votings.map((vote) => {
              const isSelected = selectedVoting && String(selectedVoting.id) === String(vote.id);
              const isOpening = vote.status === "OPENING";
              const isClosed = vote.status === "CLOSED";

              return (
                <div
                  key={vote.id}
                  className="card"
                  style={{
                    padding: "var(--space-3) var(--space-4)",
                    cursor: "pointer",
                    borderLeft: isSelected ? "4px solid var(--color-primary)" : "4px solid transparent",
                    backgroundColor: isSelected ? "var(--color-primary-subtle, #EFF6FF)" : "var(--color-bg-surface)",
                    transition: "all var(--transition-fast)"
                  }}
                  onClick={() => {
                    fetchVotingDetail(vote.id);
                    setSelectedProxyId("");
                  }}
                >
                  <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "4px" }}>
                    <span style={{ fontSize: "11px", fontWeight: "700", color: "var(--color-text-muted)" }}>
                      #{vote.id} • {vote.voting_type === "SECRET" ? "Bỏ phiếu kín" : "Công khai"}
                    </span>
                    <StatusBadge
                      status={isOpening ? "ACTIVE" : isClosed ? "PENDING" : "WAITING"}
                      text={vote.status_label}
                    />
                  </div>

                  <div style={{ fontWeight: "600", fontSize: "13px", color: "var(--color-text-primary)", lineHeight: 1.4, marginBottom: "6px" }}>
                    {vote.title}
                  </div>

                  <div style={{ display: "flex", justifyContent: "space-between", fontSize: "12px", color: "var(--color-text-muted)" }}>
                    <span>Đã nhận: <strong>{vote.stats?.total_votes || 0}</strong> phiếu</span>
                    {vote.result !== "PENDING" && (
                      <span style={{ fontWeight: "700", color: vote.result === "PASSED" ? "var(--color-success, #059669)" : "var(--color-danger, #DC2626)" }}>
                        {vote.result_label}
                      </span>
                    )}
                  </div>
                </div>
              );
            })
          )}
        </div>

        {/* Cột phải: Bảng điều khiển bỏ phiếu & Kiểm toán chi tiết */}
        <div>
          {selectedVoting ? (
            <div className="card" style={{ padding: "var(--space-5)", display: "flex", flexDirection: "column", gap: "var(--space-5)" }}>
              {/* Tiêu đề & Thông tin cơ bản */}
              <div style={{ borderBottom: "1px solid var(--color-border-subtle)", paddingBottom: "var(--space-4)" }}>
                <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "var(--space-2)" }}>
                  <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)" }}>
                    <span
                      style={{
                        fontSize: "12px",
                        fontWeight: "600",
                        padding: "2px 8px",
                        borderRadius: "12px",
                        backgroundColor: selectedVoting.voting_type === "SECRET" ? "#FEF3C7" : "#EFF6FF",
                        color: selectedVoting.voting_type === "SECRET" ? "#92400E" : "#1D4ED8",
                        border: `1px solid ${selectedVoting.voting_type === "SECRET" ? "#FCD34D" : "#BFDBFE"}`
                      }}
                    >
                      {selectedVoting.voting_type === "SECRET" ? "Bỏ phiếu kín (Bảo mật danh tính)" : "Biểu quyết công khai"}
                    </span>
                    <span style={{ fontSize: "12px", color: "var(--color-text-muted)" }}>
                      Ngưỡng thông qua: <strong>{selectedVoting.pass_threshold}%</strong> thành viên có mặt
                    </span>
                  </div>

                  <StatusBadge
                    status={selectedVoting.status === "OPENING" ? "ACTIVE" : selectedVoting.status === "CLOSED" ? "PENDING" : "WAITING"}
                    text={selectedVoting.status_label}
                  />
                </div>

                <h3 style={{ fontSize: "18px", fontWeight: "700", color: "var(--color-text-primary)", marginBottom: "var(--space-3)" }}>
                  {selectedVoting.title}
                </h3>

                {/* Nút hành động dành cho Thư ký / Chủ tịch */}
                {isSecretaryOrChair && (
                  <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)", marginTop: "var(--space-2)" }}>
                    {selectedVoting.status === "PENDING" && (
                      <button
                        type="button"
                        className="btn btn-primary btn-sm"
                        onClick={() => handleOpenVoting(selectedVoting.id)}
                      >
                        <Play size={14} />
                        Mở cổng biểu quyết
                      </button>
                    )}

                    {selectedVoting.status === "OPENING" && (
                      <button
                        type="button"
                        className="btn btn-danger btn-sm"
                        style={{ backgroundColor: "#DC2626", color: "#FFF" }}
                        onClick={() => handleCloseVoting(selectedVoting.id)}
                      >
                        <StopCircle size={14} />
                        Đóng cổng & Kiểm phiếu
                      </button>
                    )}
                  </div>
                )}
              </div>

              {/* KHU VỰC BỎ PHIẾU ĐIỆN TỬ (Khi trạng thái OPENING) */}
              {selectedVoting.status === "OPENING" && (
                <div
                  style={{
                    padding: "var(--space-4)",
                    backgroundColor: "rgba(37, 99, 235, 0.03)",
                    border: "1px dashed var(--color-primary)",
                    borderRadius: "8px"
                  }}
                >
                  <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "var(--space-3)" }}>
                    <div style={{ fontSize: "14px", fontWeight: "700", color: "var(--color-primary)" }}>
                      LÁ PHIẾU BIỂU QUYẾT ĐIỆN TỬ
                    </div>

                    {/* Bộ chọn tư cách bỏ phiếu (Bản thân hoặc theo Ủy quyền) */}
                    {selectedVoting.delegations && selectedVoting.delegations.length > 0 && (
                      <div style={{ display: "flex", alignItems: "center", gap: "8px" }}>
                        <span style={{ fontSize: "12px", color: "var(--color-text-secondary)", fontWeight: "500" }}>
                          Tư cách bỏ phiếu:
                        </span>
                        <select
                          className="input"
                          style={{ padding: "4px 8px", fontSize: "12px", fontWeight: "600" }}
                          value={selectedProxyId}
                          onChange={(e) => {
                            setSelectedProxyId(e.target.value);
                            setVoteChoice("");
                          }}
                        >
                          <option value="">
                            Bản thân tôi {selectedVoting.has_voted ? "(Đã bỏ phiếu)" : "(Chưa bỏ phiếu)"}
                          </option>
                          {selectedVoting.delegations.map((del) => (
                            <option key={del.authorization_id} value={del.authorization_id}>
                              Ủy quyền: {del.principal_name} {del.has_voted ? "(Đã bỏ phiếu)" : "(Chưa bỏ phiếu)"}
                            </option>
                          ))}
                        </select>
                      </div>
                    )}
                  </div>

                  {!isCouncilMember ? (
                    <div style={{ display: "flex", alignItems: "center", gap: "8px", color: "var(--color-warning-text)", fontSize: "13px" }}>
                      <ShieldAlert size={16} />
                      <span>Bạn không có quyền bỏ phiếu do không phải là Thành viên chính thức của Hội đồng trường STU.</span>
                    </div>
                  ) : isCurrentPersonaVoted ? (
                    /* Thông báo đã bỏ phiếu cho tư cách này */
                    <div
                      style={{
                        padding: "12px 16px",
                        borderRadius: "6px",
                        backgroundColor: "#ECFDF5",
                        border: "1px solid #A7F3D0",
                        display: "flex",
                        alignItems: "center",
                        gap: "10px"
                      }}
                    >
                      <CheckCircle2 size={20} style={{ color: "#059669" }} />
                      <div>
                        <div style={{ fontWeight: "600", color: "#065F46" }}>
                          {selectedProxyId
                            ? `Đã hoàn tất bỏ phiếu theo ủy quyền của [${currentDelegation?.principal_name}]!`
                            : "Lá phiếu của bạn đã được ghi nhận vào hệ thống an toàn!"}
                        </div>
                        <div style={{ fontSize: "12px", color: "#047857" }}>
                          {selectedVoting.voting_type === "SECRET"
                            ? "Lá phiếu được mã hóa niêm phong kín để bảo vệ quyền tự do biểu quyết."
                            : `Ý kiến đã chọn: ${currentPersonaChoice === "VOTE_FOR" ? "Tán thành" : currentPersonaChoice === "VOTE_AGAINST" ? "Không tán thành" : "Không ý kiến"}`}
                        </div>
                      </div>
                    </div>
                  ) : (
                    /* Các nút bấm chọn phương án */
                    <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)" }}>
                      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr 1fr", gap: "var(--space-3)" }}>
                        {/* Lựa chọn 1: Tán thành */}
                        <button
                          type="button"
                          onClick={() => setVoteChoice("VOTE_FOR")}
                          style={{
                            padding: "var(--space-4)",
                            borderRadius: "6px",
                            border: voteChoice === "VOTE_FOR" ? "2px solid #059669" : "1px solid var(--color-border-subtle)",
                            backgroundColor: voteChoice === "VOTE_FOR" ? "#ECFDF5" : "var(--color-bg-surface)",
                            color: "#059669",
                            fontWeight: "700",
                            cursor: "pointer",
                            display: "flex",
                            flexDirection: "column",
                            alignItems: "center",
                            gap: "8px",
                            transition: "all var(--transition-fast)"
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
                            padding: "var(--space-4)",
                            borderRadius: "6px",
                            border: voteChoice === "VOTE_AGAINST" ? "2px solid #DC2626" : "1px solid var(--color-border-subtle)",
                            backgroundColor: voteChoice === "VOTE_AGAINST" ? "#FEF2F2" : "var(--color-bg-surface)",
                            color: "#DC2626",
                            fontWeight: "700",
                            cursor: "pointer",
                            display: "flex",
                            flexDirection: "column",
                            alignItems: "center",
                            gap: "8px",
                            transition: "all var(--transition-fast)"
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
                            padding: "var(--space-4)",
                            borderRadius: "6px",
                            border: voteChoice === "VOTE_ABSTAIN" ? "2px solid #475569" : "1px solid var(--color-border-subtle)",
                            backgroundColor: voteChoice === "VOTE_ABSTAIN" ? "#F1F5F9" : "var(--color-bg-surface)",
                            color: "#475569",
                            fontWeight: "700",
                            cursor: "pointer",
                            display: "flex",
                            flexDirection: "column",
                            alignItems: "center",
                            gap: "8px",
                            transition: "all var(--transition-fast)"
                          }}
                        >
                          <MinusCircle size={24} />
                          <span>KHÔNG Ý KIẾN / TRẮNG</span>
                        </button>
                      </div>

                      <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginTop: "var(--space-2)" }}>
                        <span style={{ fontSize: "11px", color: "var(--color-text-muted)" }}>
                          * Đang biểu quyết với tư cách: <strong>{selectedProxyId ? `Ủy quyền của [${currentDelegation?.principal_name}]` : "Chính bản thân"}</strong>
                        </span>

                        <button
                          type="button"
                          className="btn btn-primary"
                          disabled={!voteChoice || castingVote}
                          onClick={handleCastVote}
                        >
                          {castingVote ? "Đang gửi phiếu..." : "Xác nhận gửi lá phiếu"}
                        </button>
                      </div>
                    </div>
                  )}
                </div>
              )}

              {/* BẢNG TỔNG HỢP TIẾN ĐỘ & KẾT QUẢ KIỂM PHIẾU */}
              <div>
                <div style={{ fontSize: "14px", fontWeight: "700", marginBottom: "var(--space-3)", color: "var(--color-text-primary)" }}>
                  Kết quả kiểm phiếu & Thống kê
                </div>

                {/* Kết quả chung */}
                {selectedVoting.result !== "PENDING" && (
                  <div
                    style={{
                      padding: "12px 16px",
                      borderRadius: "6px",
                      marginBottom: "var(--space-4)",
                      backgroundColor: selectedVoting.result === "PASSED" ? "#ECFDF5" : "#FEF2F2",
                      border: `1px solid ${selectedVoting.result === "PASSED" ? "#A7F3D0" : "#FECACA"}`,
                      display: "flex",
                      alignItems: "center",
                      justifyContent: "space-between"
                    }}
                  >
                    <div style={{ display: "flex", alignItems: "center", gap: "8px" }}>
                      {selectedVoting.result === "PASSED" ? (
                        <CheckCircle2 size={20} style={{ color: "#059669" }} />
                      ) : (
                        <XCircle size={20} style={{ color: "#DC2626" }} />
                      )}
                      <span style={{ fontWeight: "700", color: selectedVoting.result === "PASSED" ? "#065F46" : "#991B1B" }}>
                        KẾT LUẬN: {selectedVoting.result === "PASSED" ? "ĐÃ ĐƯỢC HỘI ĐỒNG THÔNG QUA" : "KHÔNG ĐƯỢC HỘI ĐỒNG THÔNG QUA"}
                      </span>
                    </div>

                    <span style={{ fontSize: "12px", color: "var(--color-text-muted)" }}>
                      Đóng lúc: {selectedVoting.closed_at || "Vừa xong"}
                    </span>
                  </div>
                )}

                {/* Thanh tiến độ trực quan */}
                <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)", marginBottom: "var(--space-4)" }}>
                  {/* Tán thành */}
                  <div>
                    <div style={{ display: "flex", justifyContent: "space-between", fontSize: "12px", marginBottom: "4px" }}>
                      <span style={{ color: "#059669", fontWeight: "600" }}>
                        Tán thành: {selectedVoting.stats?.votes_for || 0} phiếu ({selectedVoting.stats?.percentage_for || 0}%)
                      </span>
                      <span style={{ color: "var(--color-text-muted)" }}>
                        Chuẩn quy chế: {selectedVoting.pass_threshold}%
                      </span>
                    </div>
                    <div style={{ height: "10px", backgroundColor: "#E2E8F0", borderRadius: "999px", overflow: "hidden" }}>
                      <div
                        style={{
                          height: "100%",
                          width: `${selectedVoting.stats?.percentage_for || 0}%`,
                          backgroundColor: "#059669",
                          transition: "width 0.3s ease"
                        }}
                      />
                    </div>
                  </div>

                  {/* Không tán thành */}
                  <div>
                    <div style={{ display: "flex", justifyContent: "space-between", fontSize: "12px", marginBottom: "4px" }}>
                      <span style={{ color: "#DC2626", fontWeight: "600" }}>
                        Không tán thành: {selectedVoting.stats?.votes_against || 0} phiếu ({selectedVoting.stats?.percentage_against || 0}%)
                      </span>
                    </div>
                    <div style={{ height: "10px", backgroundColor: "#E2E8F0", borderRadius: "999px", overflow: "hidden" }}>
                      <div
                        style={{
                          height: "100%",
                          width: `${selectedVoting.stats?.percentage_against || 0}%`,
                          backgroundColor: "#DC2626",
                          transition: "width 0.3s ease"
                        }}
                      />
                    </div>
                  </div>

                  {/* Trắng / Ý kiến khác */}
                  <div>
                    <div style={{ display: "flex", justifyContent: "space-between", fontSize: "12px", marginBottom: "4px" }}>
                      <span style={{ color: "#64748B", fontWeight: "600" }}>
                        Không ý kiến / Phiếu trắng: {selectedVoting.stats?.votes_abstain || 0} phiếu ({selectedVoting.stats?.percentage_abstain || 0}%)
                      </span>
                    </div>
                    <div style={{ height: "10px", backgroundColor: "#E2E8F0", borderRadius: "999px", overflow: "hidden" }}>
                      <div
                        style={{
                          height: "100%",
                          width: `${selectedVoting.stats?.percentage_abstain || 0}%`,
                          backgroundColor: "#64748B",
                          transition: "width 0.3s ease"
                        }}
                      />
                    </div>
                  </div>

                  <div style={{ fontSize: "12px", color: "var(--color-text-muted)" }}>
                    Tổng số phiếu đã tiếp nhận: <strong>{selectedVoting.stats?.total_votes || 0}</strong> phiếu.
                  </div>
                </div>

                {/* DANH SÁCH KIỂM TOÁN LÁ PHIẾU (AUDIT TRAIL & BẢO MẬT BỎ PHIẾU KÍN) */}
                {selectedVoting.ballots && selectedVoting.ballots.length > 0 && (
                  <div style={{ borderTop: "1px solid var(--color-border-subtle)", paddingTop: "var(--space-3)" }}>
                    <div style={{ fontSize: "13px", fontWeight: "600", marginBottom: "8px", color: "var(--color-text-primary)" }}>
                      Kiểm toán các lá phiếu đã tiếp nhận ({selectedVoting.ballots.length}):
                    </div>
                    <div className="table-responsive">
                      <table className="table" style={{ width: "100%", fontSize: "12px" }}>
                        <thead>
                          <tr style={{ backgroundColor: "var(--color-bg-subtle)", textAlign: "left" }}>
                            <th style={{ padding: "8px 12px", width: "80px" }}>MÃ PHIẾU</th>
                            <th style={{ padding: "8px 12px" }}>CỬ TRI BIỂU QUYẾT</th>
                            <th style={{ padding: "8px 12px", width: "120px" }}>TƯ CÁCH</th>
                            <th style={{ padding: "8px 12px", width: "140px" }}>THỜI ĐIỂM</th>
                            <th style={{ padding: "8px 12px", textAlign: "right", width: "130px" }}>PHƯƠNG ÁN</th>
                          </tr>
                        </thead>
                        <tbody>
                          {selectedVoting.ballots.map((b) => (
                            <tr key={b.id} style={{ borderBottom: "1px solid var(--color-border-subtle)" }}>
                              <td style={{ padding: "8px 12px", fontWeight: "600", color: "var(--color-text-muted)" }}>
                                #{b.id}
                              </td>
                              <td style={{ padding: "8px 12px" }}>
                                <div style={{ display: "flex", alignItems: "center", gap: "6px" }}>
                                  {selectedVoting.voting_type === "SECRET" ? (
                                    <Lock size={12} style={{ color: "#D97706" }} />
                                  ) : (
                                    <ShieldCheck size={12} style={{ color: "var(--color-primary)" }} />
                                  )}
                                  <span style={{ fontWeight: "500" }}>{b.voter_name}</span>
                                </div>
                              </td>
                              <td style={{ padding: "8px 12px" }}>
                                {b.is_proxy ? (
                                  <span style={{ color: "#B45309", fontWeight: "600" }}>
                                    Ủy quyền {b.proxy_authorizer ? `(${b.proxy_authorizer})` : ""}
                                  </span>
                                ) : (
                                  <span style={{ color: "var(--color-text-secondary)" }}>Trực tiếp</span>
                                )}
                              </td>
                              <td style={{ padding: "8px 12px", color: "var(--color-text-muted)" }}>
                                {b.time}
                              </td>
                              <td style={{ padding: "8px 12px", textAlign: "right" }}>
                                {b.choice === "SECRET" ? (
                                  <span
                                    style={{
                                      padding: "2px 8px",
                                      borderRadius: "10px",
                                      fontSize: "11px",
                                      fontWeight: "600",
                                      backgroundColor: "#FEF3C7",
                                      color: "#92400E"
                                    }}
                                  >
                                    Đã niêm phong kín
                                  </span>
                                ) : b.choice === "VOTE_FOR" ? (
                                  <span style={{ color: "#059669", fontWeight: "700" }}>Tán thành</span>
                                ) : b.choice === "VOTE_AGAINST" ? (
                                  <span style={{ color: "#DC2626", fontWeight: "700" }}>Không tán thành</span>
                                ) : (
                                  <span style={{ color: "#64748B", fontWeight: "600" }}>Không ý kiến</span>
                                )}
                              </td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>
                  </div>
                )}
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

      {/* 4. MODAL: QUẢN LÝ GIẤY ỦY QUYỀN BIỂU QUYẾT (BẢNG 14) */}
      {authModalOpen && (
        <div
          style={{
            position: "fixed",
            inset: 0,
            backgroundColor: "rgba(0, 0, 0, 0.5)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            zIndex: 1000,
            padding: "var(--space-4)"
          }}
        >
          <div
            className="card"
            style={{
              width: "100%",
              maxWidth: "680px",
              padding: "var(--space-6)",
              backgroundColor: "var(--color-bg-surface)",
              boxShadow: "var(--shadow-xl)",
              maxHeight: "90vh",
              overflowY: "auto"
            }}
          >
            <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "var(--space-4)" }}>
              <div style={{ display: "flex", alignItems: "center", gap: "8px" }}>
                <UserCheck size={20} style={{ color: "var(--color-primary)" }} />
                <h3 style={{ fontSize: "18px", fontWeight: "700" }}>
                  Giấy Ủy quyền Biểu quyết Phiên họp
                </h3>
              </div>
              <button
                type="button"
                className="btn btn-outline btn-sm"
                onClick={() => setAuthModalOpen(false)}
              >
                ✕
              </button>
            </div>

            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: "var(--space-3)" }}>
              <span style={{ fontSize: "13px", color: "var(--color-text-secondary)" }}>
                Danh sách văn bản ủy quyền hợp lệ theo quy chế Hội đồng trường STU:
              </span>
              <button
                type="button"
                className="btn btn-primary btn-sm"
                onClick={() => setShowAddAuthForm(!showAddAuthForm)}
              >
                <Plus size={14} />
                {showAddAuthForm ? "Đóng form" : "Nộp giấy ủy quyền mới"}
              </button>
            </div>

            {/* Form nộp ủy quyền mới */}
            {showAddAuthForm && (
              <form
                onSubmit={handleCreateAuthSubmit}
                style={{
                  padding: "var(--space-4)",
                  backgroundColor: "var(--color-bg-subtle)",
                  borderRadius: "8px",
                  marginBottom: "var(--space-4)",
                  border: "1px solid var(--color-border-subtle)"
                }}
              >
                <div style={{ fontSize: "13px", fontWeight: "700", marginBottom: "var(--space-3)" }}>
                  Thông tin Văn bản Ủy quyền
                </div>

                <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "var(--space-3)", marginBottom: "var(--space-3)" }}>
                  <div>
                    <label className="label" style={{ fontWeight: "500", display: "block", marginBottom: "4px" }}>
                      Thành viên vắng mặt (Ủy quyền) *
                    </label>
                    <select
                      className="input"
                      style={{ width: "100%" }}
                      value={authFormData.authorizer_member_id}
                      onChange={(e) => setAuthFormData({ ...authFormData, authorizer_member_id: e.target.value })}
                      required
                    >
                      <option value="">-- Chọn thành viên ủy quyền --</option>
                      {councilMembers.map((m) => (
                        <option key={m.id} value={m.id}>
                          {m.full_name} ({m.council_role})
                        </option>
                      ))}
                    </select>
                  </div>

                  <div>
                    <label className="label" style={{ fontWeight: "500", display: "block", marginBottom: "4px" }}>
                      Thành viên có mặt (Nhận ủy quyền) *
                    </label>
                    <select
                      className="input"
                      style={{ width: "100%" }}
                      value={authFormData.authorized_member_id}
                      onChange={(e) => setAuthFormData({ ...authFormData, authorized_member_id: e.target.value })}
                      required
                    >
                      <option value="">-- Chọn người nhận ủy quyền --</option>
                      {councilMembers.map((m) => (
                        <option key={m.id} value={m.id}>
                          {m.full_name} ({m.council_role})
                        </option>
                      ))}
                    </select>
                  </div>
                </div>

                <div style={{ marginBottom: "var(--space-3)" }}>
                  <label className="label" style={{ fontWeight: "500", display: "block", marginBottom: "4px" }}>
                    Phạm vi ủy quyền
                  </label>
                  <input
                    type="text"
                    className="input"
                    style={{ width: "100%" }}
                    value={authFormData.scope}
                    onChange={(e) => setAuthFormData({ ...authFormData, scope: e.target.value })}
                  />
                </div>

                <div style={{ display: "flex", justifyContent: "flex-end", gap: "8px" }}>
                  <button
                    type="button"
                    className="btn btn-outline btn-sm"
                    onClick={() => setShowAddAuthForm(false)}
                    disabled={submittingAuth}
                  >
                    Hủy
                  </button>
                  <button
                    type="submit"
                    className="btn btn-primary btn-sm"
                    disabled={submittingAuth}
                  >
                    {submittingAuth ? "Đang lưu..." : "Xác nhận nộp ủy quyền"}
                  </button>
                </div>
              </form>
            )}

            {/* Bảng danh sách giấy ủy quyền */}
            {meetingAuthorizations.length === 0 ? (
              <div style={{ padding: "var(--space-4)", textAlign: "center", color: "var(--color-text-muted)" }}>
                Phiên họp này chưa ghi nhận giấy ủy quyền nào.
              </div>
            ) : (
              <div className="table-responsive">
                <table className="table" style={{ width: "100%", fontSize: "12px" }}>
                  <thead>
                    <tr style={{ backgroundColor: "var(--color-bg-subtle)", textAlign: "left" }}>
                      <th style={{ padding: "8px 12px" }}>THÀNH VIÊN ỦY QUYỀN</th>
                      <th style={{ padding: "8px 12px" }}>THÀNH VIÊN ĐƯỢC ỦY QUYỀN</th>
                      <th style={{ padding: "8px 12px" }}>PHẠM VI</th>
                      <th style={{ padding: "8px 12px" }}>TRẠNG THÁI</th>
                    </tr>
                  </thead>
                  <tbody>
                    {meetingAuthorizations.map((a) => (
                      <tr key={a.id} style={{ borderBottom: "1px solid var(--color-border-subtle)" }}>
                        <td style={{ padding: "8px 12px", fontWeight: "600", color: "#DC2626" }}>
                          {a.authorizer?.name}
                        </td>
                        <td style={{ padding: "8px 12px", fontWeight: "600", color: "#059669" }}>
                          {a.authorized?.name}
                        </td>
                        <td style={{ padding: "8px 12px", color: "var(--color-text-secondary)" }}>
                          {a.scope}
                        </td>
                        <td style={{ padding: "8px 12px" }}>
                          <StatusBadge status="ACTIVE" text={a.status_label || "Hợp lệ"} />
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </div>
      )}

      {/* 5. MODAL: TẠO NỘI DUNG BIỂU QUYẾT MỚI */}
      {createModalOpen && (
        <div
          style={{
            position: "fixed",
            inset: 0,
            backgroundColor: "rgba(0, 0, 0, 0.5)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            zIndex: 1000,
            padding: "var(--space-4)"
          }}
        >
          <div
            className="card"
            style={{
              width: "100%",
              maxWidth: "520px",
              padding: "var(--space-6)",
              backgroundColor: "var(--color-bg-surface)",
              boxShadow: "var(--shadow-xl)"
            }}
          >
            <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "var(--space-4)" }}>
              <h3 style={{ fontSize: "18px", fontWeight: "700" }}>
                Tạo nội dung Biểu quyết mới
              </h3>
              <button
                type="button"
                className="btn btn-outline btn-sm"
                onClick={() => setCreateModalOpen(false)}
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleCreateVoting} style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)" }}>
              <div>
                <label className="label" style={{ fontWeight: "500", display: "block", marginBottom: "4px" }}>
                  Mục Chương trình họp (Agenda) *
                </label>
                <select
                  className="input"
                  style={{ width: "100%" }}
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

              <div>
                <label className="label" style={{ fontWeight: "500", display: "block", marginBottom: "4px" }}>
                  Nội dung lấy ý kiến biểu quyết *
                </label>
                <textarea
                  className="input"
                  style={{ width: "100%" }}
                  rows={3}
                  required
                  placeholder="Ví dụ: Biểu quyết thông qua Đề án thành lập Khoa Vi điện tử..."
                  value={createFormData.title}
                  onChange={(e) => setCreateFormData({ ...createFormData, title: e.target.value })}
                />
              </div>

              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "var(--space-3)" }}>
                <div>
                  <label className="label" style={{ fontWeight: "500", display: "block", marginBottom: "4px" }}>
                    Hình thức bỏ phiếu
                  </label>
                  <select
                    className="input"
                    style={{ width: "100%" }}
                    value={createFormData.voting_type}
                    onChange={(e) => setCreateFormData({ ...createFormData, voting_type: e.target.value })}
                  >
                    <option value="SECRET">Bỏ phiếu kín</option>
                    <option value="OPEN">Biểu quyết công khai</option>
                  </select>
                </div>

                <div>
                  <label className="label" style={{ fontWeight: "500", display: "block", marginBottom: "4px" }}>
                    Ngưỡng thông qua (%)
                  </label>
                  <input
                    type="number"
                    min="50"
                    max="100"
                    className="input"
                    style={{ width: "100%" }}
                    value={createFormData.pass_threshold}
                    onChange={(e) => setCreateFormData({ ...createFormData, pass_threshold: Number(e.target.value) })}
                  />
                </div>
              </div>

              <div style={{ display: "flex", justifyContent: "flex-end", gap: "var(--space-2)", marginTop: "var(--space-3)" }}>
                <button
                  type="button"
                  className="btn btn-outline"
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
