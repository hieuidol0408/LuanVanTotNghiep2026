import React, { useState } from "react";
import { FormField, TextInput, TextareaInput } from "../../../components/ui/FormField";
import { X, UserPlus, AlertCircle } from "lucide-react";

export default function AttendeeModal({ isOpen, onClose, onSave }) {
  const [formData, setFormData] = useState({
    full_name: "",
    academic_title: "ThS",
    department: "",
    notes: ""
  });
  const [errors, setErrors] = useState({});

  if (!isOpen) return null;

  const handleSubmit = (e) => {
    e.preventDefault();
    const newErrors = {};

    if (!formData.full_name.trim()) {
      newErrors.full_name = "Họ và tên khách mời không được để trống";
    }
    if (!formData.department.trim()) {
      newErrors.department = "Đơn vị hoặc cơ quan công tác không được để trống";
    }

    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      return;
    }

    onSave(formData);
  };

  return (
    <div
      style={{
        position: "fixed",
        inset: 0,
        zIndex: 1000,
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        backgroundColor: "rgba(15, 23, 42, 0.5)",
        backdropFilter: "blur(2px)",
        padding: "var(--space-4)"
      }}
      role="dialog"
      aria-modal="true"
      aria-labelledby="attendee-modal-title"
    >
      <div
        className="card"
        style={{
          width: "100%",
          maxWidth: "520px",
          backgroundColor: "var(--color-bg-card)",
          borderRadius: "var(--radius-lg)",
          boxShadow: "var(--shadow-modal)",
          padding: "var(--space-6)",
          position: "relative"
        }}
      >
        <button
          type="button"
          onClick={onClose}
          style={{
            position: "absolute",
            top: "16px",
            right: "16px",
            background: "none",
            border: "none",
            color: "var(--color-text-muted)",
            cursor: "pointer",
            padding: "4px"
          }}
          aria-label="Đóng cửa sổ"
        >
          <X size={18} />
        </button>

        <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)", marginBottom: "var(--space-4)" }}>
          <div
            style={{
              width: "36px",
              height: "36px",
              borderRadius: "var(--radius-md)",
              backgroundColor: "var(--color-primary-light)",
              color: "var(--color-primary)",
              display: "flex",
              alignItems: "center",
              justifyContent: "center"
            }}
          >
            <UserPlus size={20} />
          </div>
          <div>
            <h2 id="attendee-modal-title" style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-semibold)" }}>
              Thêm Khách mời tham dự phiên họp
            </h2>
            <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
              Mời đại diện đơn vị trình hoặc chuyên gia đóng góp ý kiến
            </div>
          </div>
        </div>

        {/* Cảnh báo quy chế bắt buộc về khách mời */}
        <div
          style={{
            padding: "var(--space-3)",
            backgroundColor: "var(--color-warning-bg)",
            border: "1px solid var(--color-warning-border)",
            borderRadius: "var(--radius-md)",
            fontSize: "var(--font-size-xs)",
            color: "var(--color-warning-text)",
            marginBottom: "var(--space-4)",
            display: "flex",
            alignItems: "flex-start",
            gap: "8px"
          }}
        >
          <AlertCircle size={16} style={{ flexShrink: 0, marginTop: "1px" }} />
          <span>
            <strong>Quy chế HĐT:</strong> Khách mời chỉ tham gia phát biểu ý kiến theo nội dung chuyên môn được chỉ định; <strong>TUYỆT ĐỐI KHÔNG CÓ QUYỀN BIỂU QUYẾT</strong> và không được tính vào quorum hợp lệ.
          </span>
        </div>

        <form onSubmit={handleSubmit}>
          <div style={{ display: "grid", gridTemplateColumns: "140px 1fr", gap: "var(--space-3)" }}>
            <FormField label="Học hàm, học vị" htmlFor="guest-title">
              <TextInput
                id="guest-title"
                value={formData.academic_title}
                onChange={(e) => setFormData({ ...formData, academic_title: e.target.value })}
                placeholder="GS, PGS, TS, ThS"
              />
            </FormField>

            <FormField
              label="Họ và tên khách mời"
              htmlFor="guest-name"
              required
              errorMessage={errors.full_name}
            >
              <TextInput
                id="guest-name"
                value={formData.full_name}
                onChange={(e) => {
                  setFormData({ ...formData, full_name: e.target.value });
                  if (errors.full_name) setErrors({ ...errors, full_name: null });
                }}
                placeholder="Nhập họ và tên..."
                isError={Boolean(errors.full_name)}
              />
            </FormField>
          </div>

          <FormField
            label="Đơn vị / Cơ quan công tác"
            htmlFor="guest-dept"
            required
            errorMessage={errors.department}
          >
            <TextInput
              id="guest-dept"
              value={formData.department}
              onChange={(e) => {
                setFormData({ ...formData, department: e.target.value });
                if (errors.department) setErrors({ ...errors, department: null });
              }}
              placeholder="Ví dụ: Khoa Công nghệ Thông tin STU"
              isError={Boolean(errors.department)}
            />
          </FormField>

          <FormField
            label="Mục đích mời / Ghi chú nội dung báo cáo"
            htmlFor="guest-notes"
            helperText="Nêu rõ nội dung chương trình mà khách mời sẽ phát biểu giải trình"
          >
            <TextareaInput
              id="guest-notes"
              rows={3}
              value={formData.notes}
              onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
              placeholder="Ví dụ: Báo cáo giải trình tính khả thi của Đề án mở ngành AI..."
            />
          </FormField>

          <div style={{ display: "flex", justifyContent: "flex-end", gap: "var(--space-3)", marginTop: "var(--space-4)" }}>
            <button type="button" className="btn btn-secondary" onClick={onClose}>
              Hủy bỏ
            </button>
            <button type="submit" className="btn btn-primary">
              Thêm vào danh sách triệu tập
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
