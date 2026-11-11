import html2pdf from "html2pdf.js";

/**
 * Tiện ích xuất phần tử DOM HTML sang tệp PDF bằng html2pdf.js
 * Tên hàm và tên file chuẩn tiếng Việt không dấu không cách: xuatPdf.js
 * 
 * @param {HTMLElement|string} target - DOM Element hoặc Selector ID cần xuất PDF
 * @param {Object} options - Tùy chọn cấu hình in PDF
 * @param {string} options.filename - Tên tệp PDF xuất ra (ví dụ: Nghi_quyet_so_08.pdf)
 * @param {string} options.orientation - Hướng trang: 'portrait' (dọc - mặc định) hoặc 'landscape' (ngang)
 * @param {Array<number>} options.margin - Mảng lề [top, left, bottom, right] tính bằng mm
 * @returns {Promise<void>}
 */
export async function xuatPdf(target, options = {}) {
  const element = typeof target === "string" ? document.getElementById(target) : target;
  if (!element) {
    throw new Error("Không tìm thấy phần tử DOM cần xuất bản PDF.");
  }

  const {
    filename = "tai_lieu_stu.pdf",
    orientation = "portrait",
    // Căn lề chuẩn Nghị định 30/2020/NĐ-CP: Trên 20mm, Trái 30mm, Dưới 20mm, Phải 15mm
    margin = orientation === "portrait" ? [20, 30, 20, 15] : [15, 15, 15, 15],
  } = options;

  const opt = {
    margin: margin,
    filename: filename,
    image: { type: "jpeg", quality: 0.98 },
    html2canvas: {
      scale: 2,
      useCORS: true,
      logging: false,
      letterRendering: true,
      scrollY: 0,
    },
    jsPDF: {
      unit: "mm",
      format: "a4",
      orientation: orientation,
    },
    pagebreak: {
      mode: ["avoid-all", "css", "legacy"],
      avoid: [".pdf-no-break", "table", "tr", ".signature-block", ".resolution-article"],
    },
  };

  return html2pdf().set(opt).from(element).save();
}

// Giữ alias exportToPdf để tương thích kép
export const exportToPdf = xuatPdf;
export default xuatPdf;
