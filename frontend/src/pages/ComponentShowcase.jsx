import React, { useState } from "react";
import PageHeader from "../components/layout/PageHeader";
import StatusBadge from "../components/ui/StatusBadge";
import { FormField, TextInput, SelectInput, TextareaInput, Checkbox } from "../components/ui/FormField";
import ConfirmDialog from "../components/ui/ConfirmDialog";
import Alert from "../components/ui/Alert";
import { useToast } from "../components/ui/Toast";
import LoadingSkeleton from "../components/ui/LoadingSkeleton";
import EmptyState from "../components/ui/EmptyState";
import ErrorState from "../components/ui/ErrorState";
import FileUpload from "../components/ui/FileUpload";
import FileList from "../components/ui/FileList";
import { PROTOTYPE_ATTACHED_FILES } from "../mock/prototypeData";
import { Sparkles, Layers, CheckSquare, Bell, Upload, HelpCircle } from "lucide-react";

export default function ComponentShowcase() {
  const toast = useToast();

  // State cho Form
  const [formData, setFormData] = useState({
    title: "",
    category: "PERSONNEL",
    notes: "",
    isUrgent: false
  });
  const [formErrors, setFormErrors] = useState({});

  // State cho Dialog
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const [dialogVariant, setDialogVariant] = useState("danger");

  // State cho Files
  const [files, setFiles] = useState(PROTOTYPE_ATTACHED_FILES);

  // State cho Skeleton demo
  const [showSkeletonDemo, setShowSkeletonDemo] = useState(false);

  const handleValidateForm = () => {
    const errors = {};
    if (!formData.title.trim()) {
      errors.title = "Tiêu đề không được để trống (Ví dụ minh họa báo lỗi a11y)";
    }
    setFormErrors(errors);
    if (Object.keys(errors).length === 0) {
      toast.success("Dữ liệu biểu mẫu hợp lệ!");
    } else {
      toast.error("Vui lòng kiểm tra lại các trường báo đỏ.");
    }
  };

  const handleFilesSelected = (newFiles) => {
    const mapped = newFiles.map((f, i) => ({
      id: `new-${Date.now()}-${i}`,
      name: f.name,
      size: f.size,
      extension: f.name.split(".").pop(),
      uploaded_at: "Vừa tải lên",
      is_main: false
    }));
    setFiles((prev) => [...prev, ...mapped]);
    toast.success(`Đã thêm ${newFiles.length} tệp vào danh sách tải lên.`);
  };

  const handleDeleteFile = (fileToDelete) => {
    setFiles((prev) => prev.filter((f) => f.id !== fileToDelete.id));
    toast.info(`Đã gỡ tệp ${fileToDelete.name}.`);
  };

  return (
    <div>
      <PageHeader
        title="Kho Thành phần Giao diện Dùng chung (UI Library)"
        description="Tổng hợp 16 thành phần giao diện nền tảng chuẩn Enterprise Admin Dashboard, phục vụ xây dựng các module của Hội đồng trường STU."
        breadcrumbItems={[
          { label: "Trang chủ", path: "/" },
          { label: "Thành phần UI", path: "/components" }
        ]}
      />

      <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-6)" }}>
        {/* 1. STATUS BADGES */}
        <section className="card">
          <h2 style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-semibold)", marginBottom: "var(--space-1)" }}>
            1. StatusBadge (Huy hiệu Trạng thái Chuẩn hóa)
          </h2>
          <p style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)", marginBottom: "var(--space-4)" }}>
            Áp dụng nhất quán trên toàn hệ thống: Hoàn thành, Chờ xử lý/Sắp đến hạn, Lỗi/Quá hạn, Thông tin/Đang diễn ra.
          </p>

          <div style={{ display: "flex", flexWrap: "wrap", gap: "var(--space-3)", alignItems: "center" }}>
            <StatusBadge status="success" label="Đã hoàn thành" />
            <StatusBadge status="warning" label="Đang thẩm tra / Chờ duyệt" />
            <StatusBadge status="danger" label="Quá hạn thực hiện" />
            <StatusBadge status="info" label="Đang diễn ra phiên họp" />
            <StatusBadge status="neutral" label="Bản nháp" />
          </div>

          <div style={{ marginTop: "var(--space-3)", display: "flex", flexWrap: "wrap", gap: "var(--space-3)", alignItems: "center" }}>
            <span style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>Size nhỏ:</span>
            <StatusBadge status="success" label="Đạt chuẩn" size="sm" />
            <StatusBadge status="warning" label="Sắp đến hạn" size="sm" />
            <StatusBadge status="danger" label="Từ chối" size="sm" />
          </div>
        </section>

        {/* 2. BUTTON SYSTEM */}
        <section className="card">
          <h2 style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-semibold)", marginBottom: "var(--space-1)" }}>
            2. Button System (Hệ thống Nút thao tác)
          </h2>
          <p style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)", marginBottom: "var(--space-4)" }}>
            Phân định rõ thao tác chính (Primary), thao tác phụ (Secondary) và thao tác nguy hiểm (Danger).
          </p>

          <div style={{ display: "flex", flexWrap: "wrap", gap: "var(--space-3)", alignItems: "center" }}>
            <button type="button" className="btn btn-primary" onClick={() => toast.success("Bấm nút Primary!")}>
              Hành động chính (Primary)
            </button>
            <button type="button" className="btn btn-secondary" onClick={() => toast.info("Bấm nút Secondary!")}>
              Hành động phụ (Secondary)
            </button>
            <button
              type="button"
              className="btn btn-danger"
              onClick={() => {
                setDialogVariant("danger");
                setIsDialogOpen(true);
              }}
            >
              Hành động nguy hiểm (Danger)
            </button>
            <button type="button" className="btn btn-primary" disabled>
              Vô hiệu hóa (Disabled)
            </button>
            <button type="button" className="btn btn-primary btn-sm">
              Nút nhỏ (Small)
            </button>
          </div>
        </section>

        {/* 3. FORM FIELDS & INPUTS */}
        <section className="card">
          <h2 style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-semibold)", marginBottom: "var(--space-1)" }}>
            3. FormField & Thành phần Nhập liệu
          </h2>
          <p style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)", marginBottom: "var(--space-4)" }}>
            Có đầy đủ label, nhãn bắt buộc (*), thông báo lỗi tức thì và hỗ trợ tiếp cận (a11y).
          </p>

          <div style={{ maxWidth: "600px" }}>
            <FormField
              label="Tiêu đề hồ sơ trình"
              htmlFor="demo-title"
              required
              helperText="Nhập trích yếu ngắn gọn, đúng thẩm quyền HĐT"
              errorMessage={formErrors.title}
            >
              <TextInput
                id="demo-title"
                value={formData.title}
                onChange={(e) => {
                  setFormData({ ...formData, title: e.target.value });
                  if (formErrors.title) setFormErrors({ ...formErrors, title: null });
                }}
                placeholder="Ví dụ: Tờ trình về việc thành lập Khoa mới..."
                isError={Boolean(formErrors.title)}
              />
            </FormField>

            <FormField label="Nhóm lĩnh vực" htmlFor="demo-category">
              <SelectInput
                id="demo-category"
                value={formData.category}
                onChange={(e) => setFormData({ ...formData, category: e.target.value })}
                options={[
                  { value: "PERSONNEL", label: "Tổ chức & Nhân sự" },
                  { value: "FINANCE", label: "Kế hoạch & Tài chính" },
                  { value: "ACADEMICS", label: "Đào tạo & Khoa học Công nghệ" },
                  { value: "FACILITIES", label: "Cơ sở vật chất" }
                ]}
              />
            </FormField>

            <FormField label="Ghi chú thẩm tra bổ sung" htmlFor="demo-notes">
              <TextareaInput
                id="demo-notes"
                value={formData.notes}
                onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                placeholder="Nhập ý kiến hoặc yêu cầu chi tiết (nếu có)..."
              />
            </FormField>

            <div style={{ marginBottom: "var(--space-4)" }}>
              <Checkbox
                id="demo-urgent"
                label="Đánh dấu hồ sơ cần xử lý khẩn trong phiên họp bất thường"
                checked={formData.isUrgent}
                onChange={(e) => setFormData({ ...formData, isUrgent: e.target.checked })}
              />
            </div>

            <button type="button" className="btn btn-primary" onClick={handleValidateForm}>
              Kiểm tra Form Validation
            </button>
          </div>
        </section>

        {/* 4. FILE UPLOAD & FILE LIST */}
        <section className="card">
          <h2 style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-semibold)", marginBottom: "var(--space-1)" }}>
            4. FileUpload & FileList (Quản lý Tài liệu Đính kèm)
          </h2>
          <p style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)", marginBottom: "var(--space-4)" }}>
            Vùng kéo thả tải lên tệp kèm danh sách hiển thị tên, kích thước, định dạng và thao tác.
          </p>

          <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(320px, 1fr))", gap: "var(--space-6)" }}>
            <div>
              <FileUpload onFilesSelected={handleFilesSelected} />
            </div>
            <div>
              <div style={{ fontSize: "var(--font-size-sm)", fontWeight: "var(--font-weight-medium)", marginBottom: "var(--space-2)" }}>
                Tài liệu đã đính kèm ({files.length}):
              </div>
              <FileList
                files={files}
                onDelete={handleDeleteFile}
                onView={(f) => toast.info(`Xem tệp: ${f.name}`)}
                onDownload={(f) => toast.success(`Tải xuống tệp: ${f.name}`)}
              />
            </div>
          </div>
        </section>

        {/* 5. ALERTS & TOASTS */}
        <section className="card">
          <h2 style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-semibold)", marginBottom: "var(--space-1)" }}>
            5. Alert & Toast Notification (Thông báo Cảnh báo)
          </h2>
          <p style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)", marginBottom: "var(--space-4)" }}>
            Alert dùng cho banner tĩnh trong trang; Toast dùng cho thông báo nổi góc trên khi hoàn thành thao tác.
          </p>

          <Alert type="success" title="Thành công" message="Nghị quyết số 04/NQ-HĐTSTU đã được Chủ tịch ký duyệt và ban hành chính thức." />
          <Alert type="warning" title="Cảnh báo hạn chót" message="Còn 02 ngày nữa là đến hạn nộp báo cáo kết quả thực hiện nhiệm vụ quy chế chi tiêu." />
          <Alert type="danger" title="Lỗi hệ thống" message="Tệp tải lên vượt quá dung lượng cho phép (tối đa 50MB). Vui lòng nén lại tệp." />

          <div style={{ marginTop: "var(--space-4)", display: "flex", flexWrap: "wrap", gap: "var(--space-3)" }}>
            <span style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)", alignSelf: "center" }}>Bắn thử Toast:</span>
            <button type="button" className="btn btn-secondary btn-sm" onClick={() => toast.success("Thao tác thành công!")}>
              Toast Thành công
            </button>
            <button type="button" className="btn btn-secondary btn-sm" onClick={() => toast.warning("Cảnh báo cần lưu ý!")}>
              Toast Cảnh báo
            </button>
            <button type="button" className="btn btn-secondary btn-sm" onClick={() => toast.error("Đã xảy ra lỗi!")}>
              Toast Lỗi
            </button>
          </div>
        </section>

        {/* 6. LOADING SKELETON, EMPTY STATE & ERROR STATE */}
        <section className="card">
          <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "var(--space-4)" }}>
            <div>
              <h2 style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-semibold)" }}>
                6. LoadingSkeleton, EmptyState & ErrorState
              </h2>
              <p style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
                Đảm bảo trải nghiệm người dùng liền mạch khi đang tải hoặc khi có sự cố.
              </p>
            </div>
            <button
              type="button"
              className="btn btn-secondary btn-sm"
              onClick={() => setShowSkeletonDemo(!showSkeletonDemo)}
            >
              {showSkeletonDemo ? "Ẩn Skeleton" : "Bật Skeleton Demo"}
            </button>
          </div>

          {showSkeletonDemo ? (
            <div style={{ padding: "var(--space-4)", backgroundColor: "var(--color-bg-app)", borderRadius: "var(--radius-md)" }}>
              <LoadingSkeleton type="table" count={3} />
            </div>
          ) : (
            <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(300px, 1fr))", gap: "var(--space-4)" }}>
              <div style={{ border: "1px dashed var(--color-border)", borderRadius: "var(--radius-md)" }}>
                <EmptyState
                  title="Chưa có phiên họp nào"
                  description="Hãy lập lịch phiên họp mới để bắt đầu chương trình làm việc của Hội đồng trường."
                  actionButton={
                    <button type="button" className="btn btn-primary btn-sm" onClick={() => toast.info("Mở tạo lịch họp")}>
                      Lập lịch họp
                    </button>
                  }
                />
              </div>

              <div style={{ border: "1px dashed var(--color-border)", borderRadius: "var(--radius-md)" }}>
                <ErrorState
                  title="Không thể tải danh sách tài liệu"
                  description="Máy chủ phản hồi chậm hoặc phiên làm việc đã hết hạn."
                  onRetry={() => toast.info("Đang kết nối lại máy chủ...")}
                />
              </div>
            </div>
          )}
        </section>
      </div>

      {/* ConfirmDialog Demo */}
      <ConfirmDialog
        isOpen={isDialogOpen}
        title={dialogVariant === "danger" ? "Xác nhận hủy phiên họp" : "Xác nhận ban hành Nghị quyết"}
        description={
          dialogVariant === "danger"
            ? "Bạn có chắc chắn muốn hủy phiên họp này không? Giấy báo hủy sẽ được gửi tự động đến tất cả đại biểu đã triệu tập."
            : "Nghị quyết sau khi ban hành sẽ có hiệu lực thi hành và gửi đến tất cả phòng ban."
        }
        confirmLabel={dialogVariant === "danger" ? "Đồng ý hủy" : "Ban hành"}
        variant={dialogVariant}
        onConfirm={() => {
          setIsDialogOpen(false);
          toast.success("Đã xác nhận thao tác thành công.");
        }}
        onCancel={() => setIsDialogOpen(false)}
      />
    </div>
  );
}
