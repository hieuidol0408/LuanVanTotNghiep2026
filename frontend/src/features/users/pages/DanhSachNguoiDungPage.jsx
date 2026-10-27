import React, { useState, useEffect, useCallback } from "react";
import {
  Users,
  Search,
  Plus,
  RefreshCw,
  Building2,
  ShieldCheck,
  Lock,
  Unlock,
  KeyRound,
  Edit2,
  CheckCircle2,
  AlertTriangle,
  Mail,
  UserCheck,
  X
} from "lucide-react";
import PageHeader from "../../../components/layout/PageHeader";
import StatusBadge from "../../../components/ui/StatusBadge";
import EmptyState from "../../../components/ui/EmptyState";
import { useToast } from "../../../components/ui/Toast";
import { userService } from "../services/userService";
import councilService from "../../councils/services/councilService";
import { useAuth } from "../../auth/context/AuthContext";

export default function UserListPage() {
  const toast = useToast();
  const { user: currentUser } = useAuth();

  const [users, setUsers] = useState([]);
  const [roles, setRoles] = useState([]);
  const [departments, setDepartments] = useState([]);

  const [search, setSearch] = useState("");
  const [selectedRole, setSelectedRole] = useState("ALL");
  const [selectedDept, setSelectedDept] = useState("ALL");
  const [selectedStatus, setSelectedStatus] = useState("ALL");
  const [loading, setLoading] = useState(false);

  // Modal states
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [isResetPassModalOpen, setIsResetPassModalOpen] = useState(false);
  const [selectedUser, setSelectedUser] = useState(null);

  // Form states
  const [formData, setFormData] = useState({
    HoTen: "",
    email: "",
    MatKhau: "STU@123456",
    MaVaiTro: "",
    MaDonVi: "",
    TrangThai: "Hoạt động"
  });
  const [resetPasswordVal, setResetPasswordVal] = useState("STU@123456");
  const [submitting, setSubmitting] = useState(false);

  // 1. Tải danh mục vai trò & phòng ban
  const loadCategories = useCallback(async () => {
    try {
      const [roleRes, deptRes] = await Promise.all([
        userService.getRoles(),
        councilService.getDepartments()
      ]);
      if (roleRes.success && roleRes.data) {
        setRoles(roleRes.data);
      }
      if (deptRes.success && deptRes.data) {
        setDepartments(deptRes.data);
      }
    } catch (err) {
      console.warn("Lỗi tải danh mục:", err);
    }
  }, []);

  useEffect(() => {
    loadCategories();
  }, [loadCategories]);

  // 2. Tải danh sách người dùng từ API
  const fetchUsers = useCallback(async () => {
    setLoading(true);
    try {
      const res = await userService.getUsers({
        search,
        role: selectedRole,
        department_id: selectedDept,
        status: selectedStatus
      });

      if (res.success) {
        setUsers(res.data);
      } else {
        toast.error(res.message || "Không thể tải danh sách người dùng");
      }
    } catch (err) {
      console.warn("Lỗi khi tải người dùng:", err);
      toast.error("Lỗi kết nối khi tải danh sách người dùng");
    } finally {
      setLoading(false);
    }
  }, [search, selectedRole, selectedDept, selectedStatus, toast]);

  useEffect(() => {
    fetchUsers();
  }, [fetchUsers]);

  // 3. Mở Modal tạo mới
  const handleOpenCreateModal = () => {
    setFormData({
      HoTen: "",
      email: "",
      MatKhau: "STU@123456",
      MaVaiTro: roles.length > 0 ? roles[0].id : "",
      MaDonVi: departments.length > 0 ? departments[0].id : "",
      TrangThai: "Hoạt động"
    });
    setIsCreateModalOpen(true);
  };

  // 4. Mở Modal chỉnh sửa
  const handleOpenEditModal = (userItem) => {
    setSelectedUser(userItem);
    setFormData({
      HoTen: userItem.full_name,
      email: userItem.email,
      MatKhau: "",
      MaVaiTro: userItem.role_id || "",
      MaDonVi: userItem.department_id || "",
      TrangThai: userItem.status === "ACTIVE" ? "Hoạt động" : "Khóa"
    });
    setIsEditModalOpen(true);
  };

  // 5. Mở Modal đặt lại mật khẩu
  const handleOpenResetPassModal = (userItem) => {
    setSelectedUser(userItem);
    setResetPasswordVal("STU@123456");
    setIsResetPassModalOpen(true);
  };

  // 6. Xử lý Tạo tài khoản
  const handleCreateSubmit = async (e) => {
    e.preventDefault();
    if (!formData.HoTen.trim() || !formData.email.trim() || !formData.MaVaiTro) {
      toast.warning("Vui lòng điền đầy đủ họ tên, email và vai trò.");
      return;
    }

    setSubmitting(true);
    try {
      const payload = {
        HoTen: formData.HoTen.trim(),
        email: formData.email.trim(),
        MatKhau: formData.MatKhau || "STU@123456",
        MaVaiTro: Number(formData.MaVaiTro),
        MaDonVi: formData.MaDonVi ? Number(formData.MaDonVi) : null,
        TrangThai: formData.TrangThai
      };

      const res = await userService.createUser(payload);
      if (res.success) {
        toast.success("Tạo tài khoản người dùng thành công!");
        setIsCreateModalOpen(false);
        fetchUsers();
      } else {
        toast.error(res.message || "Tạo tài khoản thất bại");
      }
    } catch (err) {
      toast.error("Lỗi kết nối khi tạo tài khoản");
    } finally {
      setSubmitting(false);
    }
  };

  // 7. Xử lý Cập nhật tài khoản
  const handleEditSubmit = async (e) => {
    e.preventDefault();
    if (!selectedUser) return;

    setSubmitting(true);
    try {
      const payload = {
        HoTen: formData.HoTen.trim(),
        email: formData.email.trim(),
        MaVaiTro: Number(formData.MaVaiTro),
        MaDonVi: formData.MaDonVi ? Number(formData.MaDonVi) : null,
        TrangThai: formData.TrangThai
      };
      if (formData.MatKhau) {
        payload.MatKhau = formData.MatKhau;
      }

      const res = await userService.updateUser(selectedUser.id, payload);
      if (res.success) {
        toast.success("Cập nhật thông tin tài khoản thành công!");
        setIsEditModalOpen(false);
        fetchUsers();
      } else {
        toast.error(res.message || "Cập nhật tài khoản thất bại");
      }
    } catch (err) {
      toast.error("Lỗi kết nối khi cập nhật tài khoản");
    } finally {
      setSubmitting(false);
    }
  };

  // 8. Xử lý Khóa / Mở khóa
  const handleToggleStatus = async (userItem) => {
    const isLocking = userItem.status === "ACTIVE";
    const confirmMsg = isLocking
      ? `Bạn có chắc chắn muốn KHÓA tài khoản của [${userItem.full_name}]? Người dùng này sẽ không thể đăng nhập.`
      : `Bạn có muốn MỞ KHÓA tài khoản của [${userItem.full_name}] để kích hoạt lại quyền truy cập?`;

    if (!window.confirm(confirmMsg)) return;

    try {
      const res = await userService.toggleUserStatus(userItem.id);
      if (res.success) {
        toast.success(res.message);
        fetchUsers();
      } else {
        toast.error(res.message || "Thao tác đổi trạng thái thất bại");
      }
    } catch (err) {
      toast.error("Lỗi khi đổi trạng thái tài khoản");
    }
  };

  // 9. Xử lý Đặt lại mật khẩu
  const handleResetPasswordSubmit = async (e) => {
    e.preventDefault();
    if (!selectedUser || !resetPasswordVal.trim()) return;

    setSubmitting(true);
    try {
      const res = await userService.resetPassword(selectedUser.id, resetPasswordVal.trim());
      if (res.success) {
        toast.success(res.message);
        setIsResetPassModalOpen(false);
      } else {
        toast.error(res.message || "Không thể đặt lại mật khẩu");
      }
    } catch (err) {
      toast.error("Lỗi kết nối khi đặt lại mật khẩu");
    } finally {
      setSubmitting(false);
    }
  };

  // Helper hiển thị Role Badge màu sắc chuyên nghiệp
  const renderRoleBadge = (roleCode, roleName) => {
    let bg = "#F1F5F9";
    let color = "#475569";
    let border = "#CBD5E1";

    switch (roleCode) {
      case "ADMIN":
        bg = "#F3E8FF";
        color = "#6B21A8";
        border = "#D8B4FE";
        break;
      case "CHAIR":
        bg = "#DBEAFE";
        color = "#1E40AF";
        border = "#93C5FD";
        break;
      case "SECRETARY":
        bg = "#CFFAFE";
        color = "#0E7490";
        border = "#67E8F9";
        break;
      case "MEMBER":
        bg = "#D1FAE5";
        color = "#065F46";
        border = "#6EE7B7";
        break;
      case "RECTOR_OFFICE":
        bg = "#FEF3C7";
        color = "#92400E";
        border = "#FCD34D";
        break;
      case "UNIT_REP":
        bg = "#FFEDD5";
        color = "#9A3412";
        border = "#FDBA74";
        break;
      default:
        break;
    }

    return (
      <span
        style={{
          display: "inline-flex",
          alignItems: "center",
          gap: "4px",
          padding: "2px 8px",
          borderRadius: "12px",
          fontSize: "12px",
          fontWeight: "600",
          backgroundColor: bg,
          color: color,
          border: `1px solid ${border}`
        }}
      >
        <ShieldCheck size={12} />
        {roleName || roleCode}
      </span>
    );
  };

  return (
    <div>
      {/* 1. Tiêu đề trang & Nút hành động */}
      <PageHeader
        title="Quản lý Người dùng & Phân quyền"
        description="Quản trị danh sách tài khoản hệ thống Hội đồng trường STU, quản lý vai trò và trạng thái truy cập."
        breadcrumbItems={[
          { label: "Bảng điều hành", path: "/" },
          { label: "Người dùng & Phân quyền", path: "/users" }
        ]}
        actions={
          <div style={{ display: "flex", gap: "var(--space-2)" }}>
            <button
              className="btn btn-outline"
              onClick={fetchUsers}
              disabled={loading}
              title="Làm mới danh sách"
            >
              <RefreshCw size={16} className={loading ? "animate-spin" : ""} />
              Làm mới
            </button>
            <button
              className="btn btn-primary"
              onClick={handleOpenCreateModal}
            >
              <Plus size={16} />
              Thêm người dùng mới
            </button>
          </div>
        }
      />

      {/* 2. Thanh tìm kiếm và Bộ lọc */}
      <div
        className="card"
        style={{
          padding: "var(--space-4)",
          marginBottom: "var(--space-4)",
          display: "flex",
          flexWrap: "wrap",
          gap: "var(--space-3)",
          alignItems: "center",
          backgroundColor: "var(--color-bg-card)"
        }}
      >
        {/* Tìm kiếm */}
        <div style={{ flex: "1 1 240px", position: "relative" }}>
          <Search
            size={16}
            style={{
              position: "absolute",
              left: "10px",
              top: "50%",
              transform: "translateY(-50%)",
              color: "var(--color-text-muted)"
            }}
          />
          <input
            type="text"
            className="input"
            style={{ paddingLeft: "32px", width: "100%" }}
            placeholder="Tìm theo họ tên, email..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
        </div>

        {/* Lọc theo Vai trò */}
        <div style={{ width: "200px" }}>
          <select
            className="input"
            value={selectedRole}
            onChange={(e) => setSelectedRole(e.target.value)}
          >
            <option value="ALL">-- Tất cả vai trò --</option>
            {roles.map((r) => (
              <option key={r.id} value={r.code}>
                {r.name}
              </option>
            ))}
          </select>
        </div>

        {/* Lọc theo Đơn vị */}
        <div style={{ width: "220px" }}>
          <select
            className="input"
            value={selectedDept}
            onChange={(e) => setSelectedDept(e.target.value)}
          >
            <option value="ALL">-- Tất cả đơn vị --</option>
            {departments.map((d) => (
              <option key={d.id} value={d.id}>
                {d.name}
              </option>
            ))}
          </select>
        </div>

        {/* Lọc theo Trạng thái */}
        <div style={{ width: "160px" }}>
          <select
            className="input"
            value={selectedStatus}
            onChange={(e) => setSelectedStatus(e.target.value)}
          >
            <option value="ALL">-- Tất cả trạng thái --</option>
            <option value="ACTIVE">Đang hoạt động</option>
            <option value="LOCKED">Đã khóa</option>
          </select>
        </div>
      </div>

      {/* 3. Bảng danh sách người dùng */}
      <div className="card" style={{ padding: 0, overflow: "hidden" }}>
        {loading ? (
          <div style={{ padding: "var(--space-8)", textAlign: "center", color: "var(--color-text-muted)" }}>
            <RefreshCw size={24} className="animate-spin" style={{ margin: "0 auto var(--space-2)" }} />
            Đang tải danh sách tài khoản từ máy chủ...
          </div>
        ) : users.length === 0 ? (
          <div style={{ padding: "var(--space-6)" }}>
            <EmptyState
              icon={Users}
              title="Không tìm thấy người dùng nào"
              description="Thử thay đổi từ khóa tìm kiếm hoặc bộ lọc để xem các kết quả khác."
            />
          </div>
        ) : (
          <div className="table-responsive">
            <table className="table" style={{ width: "100%", minWidth: "980px", borderCollapse: "collapse" }}>
              <thead>
                <tr style={{ backgroundColor: "var(--color-bg-subtle)", textAlign: "left", fontSize: "12px", borderBottom: "1px solid var(--color-border-subtle)" }}>
                  <th style={{ padding: "12px 16px", width: "70px" }}>MÃ</th>
                  <th style={{ padding: "12px 16px" }}>HỌ VÀ TÊN</th>
                  <th style={{ padding: "12px 16px" }}>EMAIL</th>
                  <th style={{ padding: "12px 16px" }}>VAI TRÒ HỆ THỐNG</th>
                  <th style={{ padding: "12px 16px" }}>ĐƠN VỊ PHÒNG BAN</th>
                  <th style={{ padding: "12px 16px", width: "130px" }}>TRẠNG THÁI</th>
                  <th style={{ padding: "12px 16px", textAlign: "right", width: "160px" }}>THAO TÁC</th>
                </tr>
              </thead>
              <tbody>
                {users.map((u) => {
                  const isActive = u.status === "ACTIVE";
                  return (
                    <tr
                      key={u.id}
                      style={{
                        borderBottom: "1px solid var(--color-border-subtle)",
                        transition: "background-color var(--transition-fast)"
                      }}
                    >
                      <td style={{ padding: "12px 16px", fontWeight: "600", color: "var(--color-text-muted)" }}>
                        #{u.id}
                      </td>
                      <td style={{ padding: "12px 16px" }}>
                        <div style={{ display: "flex", alignItems: "center", gap: "8px" }}>
                          <div
                            style={{
                              width: "32px",
                              height: "32px",
                              borderRadius: "50%",
                              backgroundColor: "var(--color-primary-subtle, #EFF6FF)",
                              color: "var(--color-primary, #2563EB)",
                              display: "flex",
                              alignItems: "center",
                              justifyContent: "center",
                              fontWeight: "600",
                              fontSize: "13px"
                            }}
                          >
                            {u.full_name ? u.full_name.charAt(0).toUpperCase() : "U"}
                          </div>
                          <div>
                            <div style={{ fontWeight: "600", color: "var(--color-text-primary)" }}>
                              {u.full_name}
                            </div>
                            {currentUser && currentUser.id === u.id && (
                              <span style={{ fontSize: "11px", color: "var(--color-primary)", fontWeight: "500" }}>
                                (Tài khoản của bạn)
                              </span>
                            )}
                          </div>
                        </div>
                      </td>
                      <td style={{ padding: "12px 16px", color: "var(--color-text-secondary)" }}>
                        <div style={{ display: "flex", alignItems: "center", gap: "6px" }}>
                          <Mail size={14} style={{ color: "var(--color-text-muted)" }} />
                          {u.email}
                        </div>
                      </td>
                      <td style={{ padding: "12px 16px" }}>
                        {renderRoleBadge(u.role, u.role_name)}
                      </td>
                      <td style={{ padding: "12px 16px", color: "var(--color-text-secondary)" }}>
                        <div style={{ display: "flex", alignItems: "center", gap: "6px" }}>
                          <Building2 size={14} style={{ color: "var(--color-text-muted)" }} />
                          {u.department_name}
                        </div>
                      </td>
                      <td style={{ padding: "12px 16px" }}>
                        <StatusBadge
                          status={isActive ? "ACTIVE" : "REJECTED"}
                          text={isActive ? "Hoạt động" : "Đã khóa"}
                        />
                      </td>
                      <td style={{ padding: "12px 16px", textAlign: "right" }}>
                        <div style={{ display: "inline-flex", gap: "6px" }}>
                          {/* Nút Sửa */}
                          <button
                            className="btn btn-outline btn-sm"
                            style={{ padding: "4px 8px" }}
                            onClick={() => handleOpenEditModal(u)}
                            title="Chỉnh sửa thông tin"
                          >
                            <Edit2 size={14} />
                          </button>

                          {/* Nút Đổi mật khẩu */}
                          <button
                            className="btn btn-outline btn-sm"
                            style={{ padding: "4px 8px" }}
                            onClick={() => handleOpenResetPassModal(u)}
                            title="Đặt lại mật khẩu"
                          >
                            <KeyRound size={14} />
                          </button>

                          {/* Nút Khóa / Mở khóa */}
                          <button
                            className={`btn btn-sm ${isActive ? "btn-outline" : "btn-primary"}`}
                            style={{
                              padding: "4px 8px",
                              color: isActive ? "var(--color-danger, #DC2626)" : undefined,
                              borderColor: isActive ? "var(--color-danger, #DC2626)" : undefined
                            }}
                            onClick={() => handleToggleStatus(u)}
                            title={isActive ? "Khóa tài khoản" : "Mở khóa tài khoản"}
                          >
                            {isActive ? <Lock size={14} /> : <Unlock size={14} />}
                          </button>
                        </div>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* 4. MODAL THÊM NGƯỜI DÙNG MỚI */}
      {isCreateModalOpen && (
        <div
          style={{
            position: "fixed",
            inset: 0,
            backgroundColor: "rgba(15, 23, 42, 0.65)",
            backdropFilter: "blur(4px)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            zIndex: 1000,
            padding: "var(--space-4)"
          }}
          onClick={() => setIsCreateModalOpen(false)}
        >
          <div
            className="card"
            style={{
              width: "100%",
              maxWidth: "520px",
              padding: "var(--space-6)",
              backgroundColor: "var(--color-bg-card, #FFFFFF)",
              borderRadius: "16px",
              border: "1px solid var(--color-border)",
              boxShadow: "var(--shadow-modal)"
            }}
            onClick={(e) => e.stopPropagation()}
          >
            <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "var(--space-4)" }}>
              <h3 style={{ fontSize: "18px", fontWeight: "700", margin: 0, color: "var(--color-text-primary)" }}>
                Thêm Người dùng mới
              </h3>
              <button
                type="button"
                onClick={() => setIsCreateModalOpen(false)}
                style={{
                  background: "none",
                  border: "none",
                  color: "var(--color-text-muted)",
                  cursor: "pointer",
                  padding: "4px",
                  display: "flex",
                  alignItems: "center",
                  borderRadius: "4px"
                }}
                title="Đóng"
              >
                <X size={18} />
              </button>
            </div>
            <form onSubmit={handleCreateSubmit}>
              <div style={{ marginBottom: "var(--space-3)" }}>
                <label className="label" style={{ fontWeight: "500", display: "block", marginBottom: "4px" }}>
                  Họ và tên <span style={{ color: "red" }}>*</span>
                </label>
                <input
                  type="text"
                  className="input"
                  style={{ width: "100%" }}
                  placeholder="Ví dụ: PGS. TS. Nguyễn Văn A"
                  value={formData.HoTen}
                  onChange={(e) => setFormData({ ...formData, HoTen: e.target.value })}
                  required
                />
              </div>

              <div style={{ marginBottom: "var(--space-3)" }}>
                <label className="label" style={{ fontWeight: "500", display: "block", marginBottom: "4px" }}>
                  Địa chỉ Email <span style={{ color: "red" }}>*</span>
                </label>
                <input
                  type="email"
                  className="input"
                  style={{ width: "100%" }}
                  placeholder="email@stu.edu.vn"
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                  required
                />
              </div>

              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "var(--space-3)", marginBottom: "var(--space-3)" }}>
                <div>
                  <label className="label" style={{ fontWeight: "500", display: "block", marginBottom: "4px" }}>
                    Vai trò hệ thống <span style={{ color: "red" }}>*</span>
                  </label>
                  <select
                    className="input"
                    style={{ width: "100%" }}
                    value={formData.MaVaiTro}
                    onChange={(e) => setFormData({ ...formData, MaVaiTro: e.target.value })}
                    required
                  >
                    <option value="">-- Chọn vai trò --</option>
                    {roles.map((r) => (
                      <option key={r.id} value={r.id}>
                        {r.name}
                      </option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className="label" style={{ fontWeight: "500", display: "block", marginBottom: "4px" }}>
                    Đơn vị phòng ban
                  </label>
                  <select
                    className="input"
                    style={{ width: "100%" }}
                    value={formData.MaDonVi}
                    onChange={(e) => setFormData({ ...formData, MaDonVi: e.target.value })}
                  >
                    <option value="">-- Không phân bổ --</option>
                    {departments.map((d) => (
                      <option key={d.id} value={d.id}>
                        {d.name}
                      </option>
                    ))}
                  </select>
                </div>
              </div>

              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "var(--space-3)", marginBottom: "var(--space-5)" }}>
                <div>
                  <label className="label" style={{ fontWeight: "500", display: "block", marginBottom: "4px" }}>
                    Mật khẩu khởi tạo
                  </label>
                  <input
                    type="text"
                    className="input"
                    style={{ width: "100%" }}
                    value={formData.MatKhau}
                    onChange={(e) => setFormData({ ...formData, MatKhau: e.target.value })}
                  />
                </div>

                <div>
                  <label className="label" style={{ fontWeight: "500", display: "block", marginBottom: "4px" }}>
                    Trạng thái
                  </label>
                  <select
                    className="input"
                    style={{ width: "100%" }}
                    value={formData.TrangThai}
                    onChange={(e) => setFormData({ ...formData, TrangThai: e.target.value })}
                  >
                    <option value="Hoạt động">Hoạt động</option>
                    <option value="Khóa">Khóa tài khoản</option>
                  </select>
                </div>
              </div>

              <div style={{ display: "flex", justifyContent: "flex-end", gap: "var(--space-2)" }}>
                <button
                  type="button"
                  className="btn btn-outline"
                  onClick={() => setIsCreateModalOpen(false)}
                  disabled={submitting}
                >
                  Hủy bỏ
                </button>
                <button
                  type="submit"
                  className="btn btn-primary"
                  disabled={submitting}
                >
                  {submitting ? "Đang xử lý..." : "Tạo tài khoản"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* 5. MODAL CHỈNH SỬA NGƯỜI DÙNG */}
      {isEditModalOpen && selectedUser && (
        <div
          style={{
            position: "fixed",
            inset: 0,
            backgroundColor: "rgba(15, 23, 42, 0.65)",
            backdropFilter: "blur(4px)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            zIndex: 1000,
            padding: "var(--space-4)"
          }}
          onClick={() => setIsEditModalOpen(false)}
        >
          <div
            className="card"
            style={{
              width: "100%",
              maxWidth: "520px",
              padding: "var(--space-6)",
              backgroundColor: "var(--color-bg-card, #FFFFFF)",
              borderRadius: "16px",
              border: "1px solid var(--color-border)",
              boxShadow: "var(--shadow-modal)"
            }}
            onClick={(e) => e.stopPropagation()}
          >
            <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "var(--space-4)" }}>
              <h3 style={{ fontSize: "18px", fontWeight: "700", margin: 0, color: "var(--color-text-primary)" }}>
                Chỉnh sửa Người dùng #{selectedUser.id}
              </h3>
              <button
                type="button"
                onClick={() => setIsEditModalOpen(false)}
                style={{
                  background: "none",
                  border: "none",
                  color: "var(--color-text-muted)",
                  cursor: "pointer",
                  padding: "4px",
                  display: "flex",
                  alignItems: "center",
                  borderRadius: "4px"
                }}
                title="Đóng"
              >
                <X size={18} />
              </button>
            </div>
            <form onSubmit={handleEditSubmit}>
              <div style={{ marginBottom: "var(--space-3)" }}>
                <label className="label" style={{ fontWeight: "500", display: "block", marginBottom: "4px" }}>
                  Họ và tên <span style={{ color: "red" }}>*</span>
                </label>
                <input
                  type="text"
                  className="input"
                  style={{ width: "100%" }}
                  value={formData.HoTen}
                  onChange={(e) => setFormData({ ...formData, HoTen: e.target.value })}
                  required
                />
              </div>

              <div style={{ marginBottom: "var(--space-3)" }}>
                <label className="label" style={{ fontWeight: "500", display: "block", marginBottom: "4px" }}>
                  Địa chỉ Email <span style={{ color: "red" }}>*</span>
                </label>
                <input
                  type="email"
                  className="input"
                  style={{ width: "100%" }}
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                  required
                />
              </div>

              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "var(--space-3)", marginBottom: "var(--space-3)" }}>
                <div>
                  <label className="label" style={{ fontWeight: "500", display: "block", marginBottom: "4px" }}>
                    Vai trò hệ thống <span style={{ color: "red" }}>*</span>
                  </label>
                  <select
                    className="input"
                    style={{ width: "100%" }}
                    value={formData.MaVaiTro}
                    onChange={(e) => setFormData({ ...formData, MaVaiTro: e.target.value })}
                    required
                  >
                    <option value="">-- Chọn vai trò --</option>
                    {roles.map((r) => (
                      <option key={r.id} value={r.id}>
                        {r.name}
                      </option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className="label" style={{ fontWeight: "500", display: "block", marginBottom: "4px" }}>
                    Đơn vị phòng ban
                  </label>
                  <select
                    className="input"
                    style={{ width: "100%" }}
                    value={formData.MaDonVi}
                    onChange={(e) => setFormData({ ...formData, MaDonVi: e.target.value })}
                  >
                    <option value="">-- Không phân bổ --</option>
                    {departments.map((d) => (
                      <option key={d.id} value={d.id}>
                        {d.name}
                      </option>
                    ))}
                  </select>
                </div>
              </div>

              <div style={{ marginBottom: "var(--space-5)" }}>
                <label className="label" style={{ fontWeight: "500", display: "block", marginBottom: "4px" }}>
                  Trạng thái hoạt động
                </label>
                <select
                  className="input"
                  style={{ width: "100%" }}
                  value={formData.TrangThai}
                  onChange={(e) => setFormData({ ...formData, TrangThai: e.target.value })}
                >
                  <option value="Hoạt động">Hoạt động</option>
                  <option value="Khóa">Khóa tài khoản</option>
                </select>
              </div>

              <div style={{ display: "flex", justifyContent: "flex-end", gap: "var(--space-2)" }}>
                <button
                  type="button"
                  className="btn btn-outline"
                  onClick={() => setIsEditModalOpen(false)}
                  disabled={submitting}
                >
                  Hủy bỏ
                </button>
                <button
                  type="submit"
                  className="btn btn-primary"
                  disabled={submitting}
                >
                  {submitting ? "Đang lưu..." : "Lưu thay đổi"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* 6. MODAL ĐẶT LẠI MẬT KHẨU */}
      {isResetPassModalOpen && selectedUser && (
        <div
          style={{
            position: "fixed",
            inset: 0,
            backgroundColor: "rgba(15, 23, 42, 0.65)",
            backdropFilter: "blur(4px)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            zIndex: 1000,
            padding: "var(--space-4)"
          }}
          onClick={() => setIsResetPassModalOpen(false)}
        >
          <div
            className="card"
            style={{
              width: "100%",
              maxWidth: "440px",
              padding: "var(--space-6)",
              backgroundColor: "var(--color-bg-card, #FFFFFF)",
              borderRadius: "16px",
              border: "1px solid var(--color-border)",
              boxShadow: "var(--shadow-modal)"
            }}
            onClick={(e) => e.stopPropagation()}
          >
            <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "var(--space-3)" }}>
              <div style={{ display: "flex", alignItems: "center", gap: "8px" }}>
                <KeyRound size={20} style={{ color: "var(--color-primary)" }} />
                <h3 style={{ fontSize: "18px", fontWeight: "700", margin: 0, color: "var(--color-text-primary)" }}>
                  Đặt lại Mật khẩu
                </h3>
              </div>
              <button
                type="button"
                onClick={() => setIsResetPassModalOpen(false)}
                style={{
                  background: "none",
                  border: "none",
                  color: "var(--color-text-muted)",
                  cursor: "pointer",
                  padding: "4px",
                  display: "flex",
                  alignItems: "center",
                  borderRadius: "4px"
                }}
                title="Đóng"
              >
                <X size={18} />
              </button>
            </div>
            <p style={{ fontSize: "14px", color: "var(--color-text-secondary)", marginBottom: "var(--space-4)" }}>
              Tài khoản: <strong>{selectedUser.full_name}</strong> ({selectedUser.email})
            </p>
            <form onSubmit={handleResetPasswordSubmit}>
              <div style={{ marginBottom: "var(--space-5)" }}>
                <label className="label" style={{ fontWeight: "500", display: "block", marginBottom: "4px" }}>
                  Mật khẩu mới
                </label>
                <input
                  type="text"
                  className="input"
                  style={{ width: "100%" }}
                  value={resetPasswordVal}
                  onChange={(e) => setResetPasswordVal(e.target.value)}
                  placeholder="Nhập mật khẩu mới..."
                  required
                />
              </div>

              <div style={{ display: "flex", justifyContent: "flex-end", gap: "var(--space-2)" }}>
                <button
                  type="button"
                  className="btn btn-outline"
                  onClick={() => setIsResetPassModalOpen(false)}
                  disabled={submitting}
                >
                  Hủy bỏ
                </button>
                <button
                  type="submit"
                  className="btn btn-primary"
                  disabled={submitting}
                >
                  {submitting ? "Đang xử lý..." : "Xác nhận đặt lại"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
