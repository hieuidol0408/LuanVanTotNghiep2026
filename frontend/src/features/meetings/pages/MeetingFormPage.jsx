import React, { useState, useEffect } from "react";
import { useNavigate, useParams } from "react-router-dom";
import PageHeader from "../../../components/layout/PageHeader";
import { FormField, TextInput, SelectInput, TextareaInput } from "../../../components/ui/FormField";
import { useToast } from "../../../components/ui/Toast";
import { Save, ArrowLeft, Calendar, MapPin, Users } from "lucide-react";
import { meetingService } from "../services/meetingService";

export default function MeetingFormPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const toast = useToast();
  const isEditMode = Boolean(id);

  const [formData, setFormData] = useState({
    id: "",
    title: "",
    term: "Khóa IV (2020-2025)",
    meeting_type: "REGULAR",
    meeting_format: "IN_PERSON",
    meeting_date: "",
    start_time: "08:30",
    end_time: "11:30",
    location: "Phòng họp A101, Nhà A - Trụ sở chính STU",
    chairperson: "PGS.TS. Nguyễn Văn Chủ Tịch",
    secretary: "TS. Trần Văn Thư Ký",
    description: ""
  });

  const [errors, setErrors] = useState({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isLoadingData, setIsLoadingData] = useState(isEditMode);

  useEffect(() => {
    if (isEditMode) {
      meetingService.getMeetingById(id).then((res) => {
        if (res.success) {
          setFormData({
            id: res.data.id,
            title: res.data.title,
            term: res.data.term,
            meeting_type: res.data.meeting_type,
            meeting_format: res.data.meeting_format,
            meeting_date: res.data.meeting_date,
            start_time: res.data.start_time,
            end_time: res.data.end_time || "",
            location: res.data.location,
            chairperson: res.data.chairperson,
            secretary: res.data.secretary,
            description: res.data.description || ""
          });
        } else {
          toast.error("Không tìm thấy thông tin phiên họp.");
          navigate("/meetings");
        }
        setIsLoadingData(false);
      });
    } else {
      // Tự động gợi ý mã phiên họp mới
      setFormData((prev) => ({
        ...prev,
        id: `PH-2026-K4-${String(Date.now()).slice(-2)}`,
        meeting_date: new Date().toISOString().substring(0, 10)
      }));
    }
  }, [id, isEditMode, navigate]);

  const validate = () => {
    const errs = {};
    if (!formData.title.trim()) {
      errs.title = "Tên phiên họp không được để trống";
    }
    if (!formData.meeting_date) {
      errs.meeting_date = "Vui lòng chọn ngày tổ chức phiên họp";
    }
    if (!formData.start_time) {
      errs.start_time = "Vui lòng nhập giờ bắt đầu";
    }
    if (!formData.location.trim()) {
      errs.location = "Vui lòng nhập địa điểm phòng họp hoặc đường dẫn phòng họp trực tuyến";
    }
    setErrors(errs);
    return Object.keys(errs).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!validate()) {
      toast.error("Vui lòng hoàn thiện các trường dữ liệu bắt buộc.");
      return;
    }

    setIsSubmitting(true);
    let res;
    if (isEditMode) {
      res = await meetingService.updateMeeting(id, formData);
    } else {
      res = await meetingService.createMeeting(formData);
    }
    setIsSubmitting(false);

    if (res.success) {
      toast.success(res.message);
      navigate(isEditMode ? `/meetings/${id}` : "/meetings");
    } else {
      toast.error(res.message || "Đã xảy ra lỗi khi lưu phiên họp.");
    }
  };

  return (
    <div>
      {/* Page Header */}
      <PageHeader
        title={isEditMode ? `Chỉnh sửa: ${formData.title || id}` : "Lập Kế hoạch Phiên họp Mới"}
        description={
          isEditMode
            ? "Cập nhật thời gian, địa điểm hoặc thông tin thẩm quyền điều hành của phiên họp."
            : "Thiết lập thông tin chung để bắt đầu xây dựng chương trình nghị sự (Agenda) và triệu tập đại biểu."
        }
        breadcrumbItems={[
          { label: "Bảng điều hành", path: "/" },
          { label: "Phiên họp & Tài liệu", path: "/meetings" },
          { label: isEditMode ? "Chỉnh sửa" : "Tạo mới", path: "" }
        ]}
        actions={
          <button
            type="button"
            className="btn btn-secondary"
            onClick={() => navigate("/meetings")}
            style={{ gap: "6px" }}
          >
            <ArrowLeft size={15} />
            <span>Quay lại danh sách</span>
          </button>
        }
      />

      <form onSubmit={handleSubmit} style={{ maxWidth: "900px" }}>
        <div className="card" style={{ padding: "var(--space-6)", marginBottom: "var(--space-6)" }}>
          <h2 style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-semibold)", color: "var(--color-text-primary)", marginBottom: "var(--space-4)", display: "flex", alignItems: "center", gap: "8px" }}>
            <Calendar size={18} style={{ color: "var(--color-primary)" }} />
            1. Thông tin Định danh & Hình thức Tổ chức
          </h2>

          <div style={{ display: "grid", gridTemplateColumns: "1fr 2fr", gap: "var(--space-4)" }}>
            <FormField label="Mã phiên họp" htmlFor="meeting-code" required>
              <TextInput
                id="meeting-code"
                value={formData.id}
                onChange={(e) => setFormData({ ...formData, id: e.target.value })}
                disabled={isEditMode}
              />
            </FormField>

            <FormField label="Khóa / Nhiệm kỳ Hội đồng trường" htmlFor="meeting-term" required>
              <SelectInput
                id="meeting-term"
                value={formData.term}
                onChange={(e) => setFormData({ ...formData, term: e.target.value })}
                options={[
                  { value: "Khóa IV (2020-2025)", label: "Hội đồng trường Khóa IV (2020-2025) - Đương nhiệm" },
                  { value: "Khóa V (2025-2030)", label: "Hội đồng trường Khóa V (2025-2030) - Dự kiến" }
                ]}
              />
            </FormField>
          </div>

          <FormField
            label="Tên đầy đủ của phiên họp"
            htmlFor="meeting-title"
            required
            errorMessage={errors.title}
          >
            <TextInput
              id="meeting-title"
              value={formData.title}
              onChange={(e) => {
                setFormData({ ...formData, title: e.target.value });
                if (errors.title) setErrors({ ...errors, title: null });
              }}
              placeholder="Ví dụ: Phiên họp lần thứ 5 Hội đồng trường STU (Khóa IV)..."
              isError={Boolean(errors.title)}
            />
          </FormField>

          <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "var(--space-4)" }}>
            <FormField label="Loại kỳ họp" htmlFor="meeting-type" required>
              <SelectInput
                id="meeting-type"
                value={formData.meeting_type}
                onChange={(e) => setFormData({ ...formData, meeting_type: e.target.value })}
                options={[
                  { value: "REGULAR", label: "Họp định kỳ (theo Quy chế)" },
                  { value: "EXTRAORDINARY", label: "Họp chuyên đề / Bất thường" }
                ]}
              />
            </FormField>

            <FormField label="Hình thức họp" htmlFor="meeting-format" required>
              <SelectInput
                id="meeting-format"
                value={formData.meeting_format}
                onChange={(e) => setFormData({ ...formData, meeting_format: e.target.value })}
                options={[
                  { value: "IN_PERSON", label: "Họp trực tiếp tại trường" },
                  { value: "ONLINE", label: "Họp trực tuyến (Google Meet / Zoom)" },
                  { value: "HYBRID", label: "Họp kết hợp (Trực tiếp + Trực tuyến)" }
                ]}
              />
            </FormField>
          </div>

          <FormField
            label="Địa điểm phòng họp / Đường dẫn trực tuyến"
            htmlFor="meeting-location"
            required
            helperText="Nếu họp trực tiếp: Nhập số phòng, tòa nhà; nếu họp trực tuyến: Nhập link Google Meet/Zoom"
            errorMessage={errors.location}
          >
            <TextInput
              id="meeting-location"
              value={formData.location}
              onChange={(e) => {
                setFormData({ ...formData, location: e.target.value });
                if (errors.location) setErrors({ ...errors, location: null });
              }}
              placeholder="Ví dụ: Phòng họp A101, Nhà A hoặc https://meet.google.com/stu-hdt..."
              isError={Boolean(errors.location)}
            />
          </FormField>
        </div>

        {/* 2. Thời gian tổ chức */}
        <div className="card" style={{ padding: "var(--space-6)", marginBottom: "var(--space-6)" }}>
          <h2 style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-semibold)", color: "var(--color-text-primary)", marginBottom: "var(--space-4)", display: "flex", alignItems: "center", gap: "8px" }}>
            <MapPin size={18} style={{ color: "var(--color-primary)" }} />
            2. Lịch trình & Thời gian Tổ chức
          </h2>

          <div style={{ display: "grid", gridTemplateColumns: "1.5fr 1fr 1fr", gap: "var(--space-4)" }}>
            <FormField label="Ngày tổ chức" htmlFor="meeting-date" required errorMessage={errors.meeting_date}>
              <TextInput
                id="meeting-date"
                type="date"
                value={formData.meeting_date}
                onChange={(e) => {
                  setFormData({ ...formData, meeting_date: e.target.value });
                  if (errors.meeting_date) setErrors({ ...errors, meeting_date: null });
                }}
                isError={Boolean(errors.meeting_date)}
              />
            </FormField>

            <FormField label="Giờ bắt đầu" htmlFor="meeting-start" required errorMessage={errors.start_time}>
              <TextInput
                id="meeting-start"
                type="time"
                value={formData.start_time}
                onChange={(e) => {
                  setFormData({ ...formData, start_time: e.target.value });
                  if (errors.start_time) setErrors({ ...errors, start_time: null });
                }}
                isError={Boolean(errors.start_time)}
              />
            </FormField>

            <FormField label="Giờ kết thúc dự kiến" htmlFor="meeting-end">
              <TextInput
                id="meeting-end"
                type="time"
                value={formData.end_time}
                onChange={(e) => setFormData({ ...formData, end_time: e.target.value })}
              />
            </FormField>
          </div>
        </div>

        {/* 3. Thẩm quyền & Mục tiêu */}
        <div className="card" style={{ padding: "var(--space-6)", marginBottom: "var(--space-6)" }}>
          <h2 style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-semibold)", color: "var(--color-text-primary)", marginBottom: "var(--space-4)", display: "flex", alignItems: "center", gap: "8px" }}>
            <Users size={18} style={{ color: "var(--color-primary)" }} />
            3. Thẩm quyền Chủ trì & Mục tiêu Phiên họp
          </h2>

          <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "var(--space-4)" }}>
            <FormField label="Chủ tọa phiên họp" htmlFor="meeting-chair" required>
              <TextInput
                id="meeting-chair"
                value={formData.chairperson}
                onChange={(e) => setFormData({ ...formData, chairperson: e.target.value })}
              />
            </FormField>

            <FormField label="Thư ký ghi biên bản" htmlFor="meeting-secretary" required>
              <TextInput
                id="meeting-secretary"
                value={formData.secretary}
                onChange={(e) => setFormData({ ...formData, secretary: e.target.value })}
              />
            </FormField>
          </div>

          <FormField
            label="Mô tả mục tiêu & Nội dung trọng tâm"
            htmlFor="meeting-desc"
            helperText="Ghi tóm tắt mục đích kỳ họp để hiển thị trong giấy triệu tập gửi các đại biểu"
          >
            <TextareaInput
              id="meeting-desc"
              rows={4}
              value={formData.description}
              onChange={(e) => setFormData({ ...formData, description: e.target.value })}
              placeholder="Nhập tóm tắt các vấn đề lớn sẽ được quyết nghị trong phiên họp..."
            />
          </FormField>
        </div>

        {/* Action Buttons */}
        <div style={{ display: "flex", justifyContent: "flex-end", gap: "var(--space-3)", marginBottom: "var(--space-8)" }}>
          <button
            type="button"
            className="btn btn-secondary"
            onClick={() => navigate("/meetings")}
            disabled={isSubmitting}
          >
            Hủy bỏ
          </button>

          <button
            type="submit"
            className="btn btn-primary"
            disabled={isSubmitting}
            style={{ gap: "6px" }}
          >
            <Save size={15} />
            <span>{isSubmitting ? "Đang lưu..." : isEditMode ? "Lưu thay đổi" : "Tạo phiên họp"}</span>
          </button>
        </div>
      </form>
    </div>
  );
}
