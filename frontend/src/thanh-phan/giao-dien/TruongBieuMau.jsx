import React from "react";

/**
 * FormField - Khung bao bọc trường nhập liệu kèm Label, Bắt buộc (*), Chú thích và Báo lỗi
 */
export function FormField({
  label,
  htmlFor,
  required = false,
  helperText,
  errorMessage,
  children,
  className = ""
}) {
  return (
    <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-1)", marginBottom: "var(--space-4)" }} className={className}>
      {label && (
        <label
          htmlFor={htmlFor}
          style={{
            fontSize: "var(--font-size-sm)",
            fontWeight: "var(--font-weight-medium)",
            color: "var(--color-text-primary)",
            display: "inline-flex",
            alignItems: "center",
            gap: "4px"
          }}
        >
          {label}
          {required && (
            <span style={{ color: "var(--color-danger)" }} aria-hidden="true">
              *
            </span>
          )}
        </label>
      )}

      {children}

      {errorMessage ? (
        <span
          id={htmlFor ? `${htmlFor}-error` : undefined}
          style={{ fontSize: "var(--font-size-xs)", color: "var(--color-danger)", marginTop: "2px" }}
          role="alert"
        >
          {errorMessage}
        </span>
      ) : helperText ? (
        <span
          id={htmlFor ? `${htmlFor}-helper` : undefined}
          style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)", marginTop: "2px" }}
        >
          {helperText}
        </span>
      ) : null}
    </div>
  );
}

/**
 * TextInput - Ô nhập dữ liệu văn bản
 */
export function TextInput({
  id,
  type = "text",
  value,
  onChange,
  placeholder,
  disabled = false,
  isError = false,
  required = false,
  className = "",
  style = {},
  ...props
}) {
  return (
    <input
      id={id}
      type={type}
      value={value}
      onChange={onChange}
      placeholder={placeholder}
      disabled={disabled}
      required={required}
      className={`form-input ${isError ? "is-error" : ""} ${className}`}
      style={style}
      aria-invalid={isError}
      aria-describedby={id ? `${id}-error ${id}-helper` : undefined}
      {...props}
    />
  );
}

/**
 * SelectInput - Menu chọn giá trị dropdown
 */
export function SelectInput({
  id,
  value,
  onChange,
  options = [],
  placeholder,
  disabled = false,
  isError = false,
  className = "",
  style = {},
  ...props
}) {
  return (
    <select
      id={id}
      value={value}
      onChange={onChange}
      disabled={disabled}
      className={`form-select ${isError ? "is-error" : ""} ${className}`}
      style={style}
      aria-invalid={isError}
      {...props}
    >
      {placeholder && (
        <option value="" disabled>
          {placeholder}
        </option>
      )}
      {options.map((opt) => (
        <option key={opt.value} value={opt.value}>
          {opt.label}
        </option>
      ))}
    </select>
  );
}

/**
 * TextareaInput - Ô nhập văn bản nhiều dòng
 */
export function TextareaInput({
  id,
  value,
  onChange,
  rows = 4,
  placeholder,
  disabled = false,
  isError = false,
  className = "",
  style = {},
  ...props
}) {
  return (
    <textarea
      id={id}
      value={value}
      onChange={onChange}
      rows={rows}
      placeholder={placeholder}
      disabled={disabled}
      className={`form-textarea ${isError ? "is-error" : ""} ${className}`}
      style={{ resize: "vertical", minHeight: "80px", ...style }}
      aria-invalid={isError}
      {...props}
    />
  );
}

/**
 * Checkbox - Hộp kiểm chọn với nhãn
 */
export function Checkbox({
  id,
  checked,
  onChange,
  label,
  disabled = false,
  className = ""
}) {
  return (
    <label
      htmlFor={id}
      style={{
        display: "inline-flex",
        alignItems: "center",
        gap: "var(--space-2)",
        cursor: disabled ? "not-allowed" : "pointer",
        opacity: disabled ? 0.6 : 1,
        fontSize: "var(--font-size-sm)",
        color: "var(--color-text-primary)",
        userSelect: "none"
      }}
      className={className}
    >
      <input
        type="checkbox"
        id={id}
        checked={checked}
        onChange={onChange}
        disabled={disabled}
        style={{
          width: "16px",
          height: "16px",
          accentColor: "var(--color-primary)",
          cursor: disabled ? "not-allowed" : "pointer"
        }}
      />
      <span>{label}</span>
    </label>
  );
}
