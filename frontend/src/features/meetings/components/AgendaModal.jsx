import React, { useState } from "react";
import { FormField, TextInput, SelectInput, Checkbox } from "../../../components/ui/FormField";
import { X, ListPlus } from "lucide-react";
import { PROTOTYPE_PENDING_DOSSIERS } from "../../../mock/prototypeData";

export default function AgendaModal({ isOpen, onClose, onSave }) {
  const [formData, setFormData] = useState({
    title: "",
    presenter: "",
    duration_minutes: 30,
    dossier_id: "",
    is_voting_required: false
  });
  const [errors, setErrors] = useState({});

  if (!isOpen) return null;

  const handleSubmit = (e) => {
    e.preventDefault();
    const newErrors = {};

    if (!formData.title.trim()) {
      newErrors.title = "Tiêu đề nội dung thảo luận không được để trống";
    }
    if (!formData.presenter.trim()) {
      newErrors.presenter = "Người hoặc đơn vị trình bày không được để trống";
    }

    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      return;
    }

    const selectedDossier = PROTOTYPE_PENDING_DOSSIERS.find((d) => d.id === formData.dossier_id);

    onSave({
      ...formData,
      dossier_title: selectedDossier ? selectedDossier.title : null
    });
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
      aria-labelledby="agenda-modal-title"
    >
      <div
        className="card"
        style={{
          width: "100%",
          maxWidth: "560px",
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
            <ListPlus size={20} />
          </div>
          <div>
            <h2 id="agenda-modal-title" style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-semibold)" }}>
              Thêm mục vào chương trình họp (Agenda)
            </h2>
            <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
              Liên kết nội dung thảo luận với hồ sơ trình đã thẩm tra đạt yêu cầu
            </div>
          </div>
        </div>

        <form onSubmit={handleSubmit}>
          <FormField
            label="Nội dung thảo luận"
            htmlFor="agenda-title"
            required
            errorMessage={errors.title}
          >
            <TextInput
              id="agenda-title"
              value={formData.title}
              onChange={(e) => {
                setFormData({ ...formData, title: e.target.value });
                if (errors.title) setErrors({ ...errors, title: null });
              }}
              placeholder="Ví dụ: Xem xét và thông qua Đề án mở ngành đào tạo mới..."
              isError={Boolean(errors.title)}
            />
          </FormField>

          <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "var(--space-4)" }}>
            <FormField
              label="Người / Đơn vị trình bày"
              htmlFor="agenda-presenter"
              required
              errorMessage={errors.presenter}
            >
              <TextInput
                id="agenda-presenter"
                value={formData.presenter}
                onChange={(e) => {
                  setFormData({ ...formData, presenter: e.target.value });
                  if (errors.presenter) setErrors({ ...errors, presenter: null });
                }}
                placeholder="Ví dụ: TS. Lê Hoàng B (Trưởng khoa CNTT)"
                isError={Boolean(errors.presenter)}
              />
            </FormField>

            <FormField label="Thời lượng dự kiến (phút)" htmlFor="agenda-duration">
              <TextInput
                id="agenda-duration"
                type="number"
                min="5"
                max="180"
                value={formData.duration_minutes}
                onChange={(e) => setFormData({ ...formData, duration_minutes: e.target.value })}
              />
            </FormField>
          </div>

          <FormField
            label="Liên kết Hồ sơ trình (tùy chọn)"
            htmlFor="agenda-dossier"
            helperText="Chỉ các hồ sơ đã qua thẩm tra mới nên đưa vào biểu quyết"
          >
            <SelectInput
              id="agenda-dossier"
              value={formData.dossier_id}
              onChange={(e) => setFormData({ ...formData, dossier_id: e.target.value })}
              placeholder="-- Chọn hồ sơ trình đã thẩm tra --"
              options={[
                { value: "", label: "-- Không gắn hồ sơ (Nội dung thủ tục) --" },
                ...PROTOTYPE_PENDING_DOSSIERS.map((d) => ({
                  value: d.id,
                  label: `[${d.id}] ${d.title} (${d.department})`
                }))
              ]}
            />
          </FormField>

          <div style={{ marginBottom: "var(--space-5)" }}>
            <Checkbox
              id="agenda-voting-required"
              label="Yêu cầu Biểu quyết / Lấy ý kiến biểu quyết đối với nội dung này"
              checked={formData.is_voting_required}
              onChange={(e) => setFormData({ ...formData, is_voting_required: e.target.checked })}
            />
          </div>

          <div style={{ display: "flex", justifyContent: "flex-end", gap: "var(--space-3)" }}>
            <button type="button" className="btn btn-secondary" onClick={onClose}>
              Hủy bỏ
            </button>
            <button type="submit" className="btn btn-primary">
              Lưu vào chương trình
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
