import React, { useState, useEffect, useCallback } from "react";
import { useParams, useNavigate, useSearchParams } from "react-router-dom";
import {
  Calendar,
  Clock,
  MapPin,
  ArrowLeft,
  Edit,
  Info,
  ListOrdered,
  Users,
  FileText,
  Vote,
  RefreshCw,
  AlertCircle
} from "lucide-react";

import PageHeader from "../../../components/layout/PageHeader";
import StatusBadge from "../../../components/ui/StatusBadge";
import LoadingSkeleton from "../../../components/ui/LoadingSkeleton";
import EmptyState from "../../../components/ui/EmptyState";
import ErrorState from "../../../components/ui/ErrorState";
import ConfirmDialog from "../../../components/ui/ConfirmDialog";
import { useToast } from "../../../components/ui/Toast";

import meetingService from "../services/meetingService";
import MeetingGeneralInfoTab from "../components/MeetingGeneralInfoTab";
import MeetingAgendaTab from "../components/MeetingAgendaTab";
import MeetingAttendeesTab from "../components/MeetingAttendeesTab";
import MeetingDocumentsTab from "../components/MeetingDocumentsTab";
import MeetingVotingTab from "../components/MeetingVotingTab";

export default function MeetingDetailPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [searchParams, setSearchParams] = useSearchParams();
  const toast = useToast();

  const [meeting, setMeeting] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Active tab management (query param or default "info")
  const activeTab = searchParams.get("tab") || "info";

  const setActiveTab = (tabKey) => {
    setSearchParams({ tab: tabKey });
  };

  // Confirm dialog state for lifecycle actions
  const [confirmDialog, setConfirmDialog] = useState({
    isOpen: false,
    title: "",
    message: "",
    confirmLabel: "",
    confirmVariant: "primary",
    onConfirm: () => {}
  });

  // Fetch meeting details
  const fetchMeeting = useCallback(async () => {
    try {
      setLoading(true);
      setError(null);
      const res = await meetingService.getMeetingById(id);
      if (res.success && res.data) {
        setMeeting(res.data);
      } else {
        setError(res.message || "Không tìm thấy dữ liệu phiên họp");
      }
    } catch (err) {
      console.error("Fetch meeting error:", err);
      setError("Đã xảy ra lỗi khi tải thông tin phiên họp.");
    } finally {
      setLoading(false);
    }
  }, [id]);

  useEffect(() => {
    fetchMeeting();
  }, [fetchMeeting]);

  // Lifecycle status change handler
  const handleStatusChange = (newStatus) => {
    const statusTitles = {
      PREPARING: "Chuyển về trạng thái Chuẩn bị",
      SCHEDULED: "Triệu tập phiên họp chính thức",
      IN_PROGRESS: "Bắt đầu phiên họp (Khai mạc)",
      COMPLETED: "Bế mạc phiên họp",
      CANCELLED: "Hủy bỏ phiên họp"
    };

    const statusMessages = {
      PREPARING: "Phiên họp sẽ quay về trạng thái soạn thảo và cập nhật chương trình họp.",
      SCHEDULED: "Hệ thống sẽ chuyển trạng thái phiên họp sang Đã triệu tập và sẵn sàng gửi giấy mời.",
      IN_PROGRESS: "Xác nhận khai mạc phiên họp? Quá trình thảo luận và phòng biểu quyết trực tuyến sẽ được kích hoạt.",
      COMPLETED: "Xác nhận bế mạc phiên họp? Biên bản họp sẽ được kết chuyển sang khâu soạn thảo Nghị quyết Hội đồng trường.",
      CANCELLED: "CẢNH BÁO: Bạn có chắc chắn muốn hủy phiên họp này không? Thao tác này sẽ thông báo đến toàn thể đại biểu."
    };

    setConfirmDialog({
      isOpen: true,
      title: statusTitles[newStatus] || "Chuyển trạng thái phiên họp",
      message: statusMessages[newStatus] || `Xác nhận chuyển trạng thái phiên họp sang ${newStatus}?`,
      confirmLabel: newStatus === "CANCELLED" ? "Xác nhận hủy" : "Đồng ý chuyển",
      confirmVariant: newStatus === "CANCELLED" ? "danger" : "primary",
      onConfirm: async () => {
        try {
          const res = await meetingService.changeMeetingStatus(id, newStatus);
          if (res.success) {
            toast.success(res.message);
            setMeeting(res.data);
          } else {
            toast.error(res.message || "Không thể chuyển trạng thái");
          }
        } catch (err) {
          toast.error("Lỗi kết nối khi cập nhật trạng thái");
        }
      }
    });
  };

  // Send invitations handler
  const handleSendInvitations = () => {
    setConfirmDialog({
      isOpen: true,
      title: "Phát hành giấy triệu tập & Tài liệu",
      message: "Giấy mời điện tử và đường link truy cập tài liệu bảo mật sẽ được gửi đến tất cả Thành viên Hội đồng trường và Khách mời chuyên môn.",
      confirmLabel: "Gửi giấy triệu tập",
      confirmVariant: "primary",
      onConfirm: async () => {
        try {
          const res = await meetingService.sendInvitations(id);
          if (res.success) {
            toast.success(res.message);
            setMeeting(res.data);
          } else {
            toast.error(res.message || "Không thể gửi giấy mời");
          }
        } catch (err) {
          toast.error("Lỗi hệ thống khi gửi giấy mời");
        }
      }
    });
  };

  // Agenda handlers
  const handleAddAgenda = async (agendaData) => {
    try {
      const res = await meetingService.addAgendaItem(id, agendaData);
      if (res.success) {
        toast.success(res.message);
        fetchMeeting();
      } else {
        toast.error(res.message || "Không thể thêm mục thảo luận");
      }
    } catch (err) {
      toast.error("Lỗi khi thêm mục thảo luận");
    }
  };

  const handleDeleteAgenda = (agendaId, agendaTitle) => {
    setConfirmDialog({
      isOpen: true,
      title: "Xóa mục chương trình họp",
      message: `Bạn có chắc chắn muốn xóa mục "${agendaTitle}" khỏi chương trình nghị sự của phiên họp này?`,
      confirmLabel: "Xóa mục",
      confirmVariant: "danger",
      onConfirm: async () => {
        try {
          const res = await meetingService.deleteAgendaItem(id, agendaId);
          if (res.success) {
            toast.success(res.message);
            fetchMeeting();
          } else {
            toast.error(res.message || "Không thể xóa mục");
          }
        } catch (err) {
          toast.error("Lỗi khi xóa mục chương trình");
        }
      }
    });
  };

  // Attendee attendance handler
  const handleUpdateAttendance = async (attendeeId, status) => {
    try {
      const res = await meetingService.updateAttendanceStatus(id, attendeeId, status);
      if (res.success) {
        toast.success(res.message);
        fetchMeeting();
      } else {
        toast.error(res.message || "Không thể cập nhật điểm danh");
      }
    } catch (err) {
      toast.error("Lỗi khi cập nhật điểm danh");
    }
  };

  // Add guest handler
  const handleAddGuest = async (guestData) => {
    try {
      const res = await meetingService.addGuestAttendee(id, guestData);
      if (res.success) {
        toast.success(res.message);
        fetchMeeting();
      } else {
        toast.error(res.message || "Không thể thêm khách mời");
      }
    } catch (err) {
      toast.error("Lỗi khi thêm khách mời");
    }
  };

  // Document handlers
  const handleUploadDocument = async (docData) => {
    try {
      const res = await meetingService.uploadDocument(id, docData);
      if (res.success) {
        fetchMeeting();
      } else {
        toast.error(res.message || "Không thể tải lên tài liệu");
      }
    } catch (err) {
      toast.error("Lỗi khi tải lên tài liệu");
    }
  };

  const handleDeleteDocument = (docId, fileName) => {
    setConfirmDialog({
      isOpen: true,
      title: "Xóa tài liệu phiên họp",
      message: `Bạn có chắc chắn muốn xóa tài liệu "${fileName}"?`,
      confirmLabel: "Xóa tài liệu",
      confirmVariant: "danger",
      onConfirm: async () => {
        try {
          const res = await meetingService.deleteDocument(id, docId);
          if (res.success) {
            toast.success(res.message);
            fetchMeeting();
          } else {
            toast.error(res.message || "Không thể xóa tài liệu");
          }
        } catch (err) {
          toast.error("Lỗi khi xóa tài liệu");
        }
      }
    });
  };

  // Render Loading
  if (loading) {
    return (
      <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-6)" }}>
        <LoadingSkeleton height="80px" borderRadius="var(--radius-lg)" />
        <LoadingSkeleton height="240px" borderRadius="var(--radius-lg)" />
        <LoadingSkeleton height="400px" borderRadius="var(--radius-lg)" />
      </div>
    );
  }

  // Render Error
  if (error || !meeting) {
    return (
      <div>
        <PageHeader
          title="Không tìm thấy phiên họp"
          description="Mã phiên họp không tồn tại hoặc đã bị xóa khỏi hệ thống."
          breadcrumbItems={[
            { label: "Bảng điều hành", path: "/" },
            { label: "Quản lý phiên họp", path: "/meetings" },
            { label: "Chi tiết", path: "" }
          ]}
          actions={
            <button
              type="button"
              className="btn btn-secondary"
              onClick={() => navigate("/meetings")}
            >
              <ArrowLeft size={16} />
              <span>Quay lại danh sách</span>
            </button>
          }
        />
        <div className="card">
          <ErrorState
            title="Phiên họp không khả dụng"
            message={error || "Vui lòng kiểm tra lại đường dẫn hoặc mã phiên họp."}
            onRetry={fetchMeeting}
          />
        </div>
      </div>
    );
  }

  // Tabs definition
  const tabs = [
    {
      key: "info",
      label: "Thông tin chung & Tiến trình",
      icon: Info,
      badge: null
    },
    {
      key: "agenda",
      label: "Chương trình họp (Agenda)",
      icon: ListOrdered,
      badge: meeting.agendas?.length || 0
    },
    {
      key: "attendees",
      label: "Thành phần & Điểm danh",
      icon: Users,
      badge: meeting.attendees?.length || 0
    },
    {
      key: "documents",
      label: "Tài liệu phiên họp",
      icon: FileText,
      badge: meeting.documents?.length || 0
    },
    {
      key: "voting",
      label: "Nội dung biểu quyết",
      icon: Vote,
      badge: meeting.votings?.length || 0
    }
  ];

  return (
    <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-6)" }}>
      {/* 1. Page Header with Meta & Actions */}
      <PageHeader
        title={
          <div style={{ display: "flex", alignItems: "center", gap: "var(--space-3)", flexWrap: "wrap" }}>
            <span>{meeting.title}</span>
            <StatusBadge status={meeting.status} label={meeting.status_label} />
          </div>
        }
        description={`Mã phiên họp: ${meeting.id} • Nhiệm kỳ: ${meeting.term} • Hình thức: ${meeting.meeting_format_label}`}
        breadcrumbItems={[
          { label: "Bảng điều hành", path: "/" },
          { label: "Quản lý phiên họp", path: "/meetings" },
          { label: meeting.title, path: "" }
        ]}
        actions={
          <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)" }}>
            <button
              type="button"
              className="btn btn-secondary"
              onClick={() => navigate("/meetings")}
              title="Quay lại danh sách"
            >
              <ArrowLeft size={16} />
              <span>Danh sách</span>
            </button>

            {meeting.status !== "COMPLETED" && meeting.status !== "CANCELLED" && (
              <button
                type="button"
                className="btn btn-secondary"
                onClick={() => navigate(`/meetings/${meeting.id}/edit`)}
                title="Chỉnh sửa thông tin phiên họp"
              >
                <Edit size={16} />
                <span>Chỉnh sửa</span>
              </button>
            )}

            <button
              type="button"
              className="btn btn-secondary"
              onClick={fetchMeeting}
              title="Tải lại dữ liệu"
            >
              <RefreshCw size={16} />
            </button>
          </div>
        }
      />

      {/* 2. Navigation Tabs Bar */}
      <div
        style={{
          display: "flex",
          alignItems: "center",
          gap: "var(--space-2)",
          borderBottom: "1px solid var(--color-border)",
          overflowX: "auto",
          paddingBottom: "var(--space-2)"
        }}
      >
        {tabs.map((tab) => {
          const Icon = tab.icon;
          const isActive = activeTab === tab.key;

          return (
            <button
              key={tab.key}
              type="button"
              onClick={() => setActiveTab(tab.key)}
              style={{
                display: "inline-flex",
                alignItems: "center",
                gap: "var(--space-2)",
                padding: "var(--space-2) var(--space-4)",
                fontSize: "var(--font-size-sm)",
                fontWeight: isActive ? "var(--font-weight-semibold)" : "var(--font-weight-medium)",
                color: isActive ? "var(--color-primary)" : "var(--color-text-secondary)",
                backgroundColor: isActive ? "rgba(11, 46, 89, 0.08)" : "transparent",
                border: "none",
                borderRadius: "var(--radius-md)",
                cursor: "pointer",
                whiteSpace: "nowrap",
                transition: "all var(--transition-fast)"
              }}
            >
              <Icon size={16} />
              <span>{tab.label}</span>
              {tab.badge !== null && (
                <span
                  style={{
                    display: "inline-flex",
                    alignItems: "center",
                    justifyContent: "center",
                    minWidth: "20px",
                    height: "20px",
                    padding: "0 6px",
                    fontSize: "var(--font-size-xs)",
                    borderRadius: "var(--radius-full)",
                    backgroundColor: isActive ? "var(--color-primary)" : "var(--color-bg-muted)",
                    color: isActive ? "#ffffff" : "var(--color-text-muted)",
                    fontWeight: "var(--font-weight-bold)"
                  }}
                >
                  {tab.badge}
                </span>
              )}
            </button>
          );
        })}
      </div>

      {/* 3. Tab Contents */}
      <div>
        {activeTab === "info" && (
          <MeetingGeneralInfoTab
            meeting={meeting}
            onStatusChange={handleStatusChange}
            onSendInvitations={handleSendInvitations}
          />
        )}

        {activeTab === "agenda" && (
          <MeetingAgendaTab
            agendas={meeting.agendas || []}
            onAddAgenda={handleAddAgenda}
            onDeleteAgenda={handleDeleteAgenda}
          />
        )}

        {activeTab === "attendees" && (
          <MeetingAttendeesTab
            attendees={meeting.attendees || []}
            onUpdateAttendance={handleUpdateAttendance}
            onAddGuest={handleAddGuest}
          />
        )}

        {activeTab === "documents" && (
          <MeetingDocumentsTab
            documents={meeting.documents || []}
            onUploadDocument={handleUploadDocument}
            onDeleteDocument={handleDeleteDocument}
          />
        )}

        {activeTab === "voting" && (
          <MeetingVotingTab
            votings={meeting.votings || []}
            meetingId={meeting.id}
          />
        )}
      </div>

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
