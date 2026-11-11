import React from "react";

/**
 * Component Mẫu Văn bản Nghị quyết Hội đồng trường STU
 * Tên file: MauVanBanNghiQuyet.jsx (Quy chuẩn Tiếng Việt không dấu không cách)
 * 
 * Thiết kế tham khảo thể thức văn bản hành chính (Khổ A4, font Times New Roman, Unicode)
 * Cấu hình CSS chống ngắt trang bất hợp lý trên các khối điều khoản, bảng biểu và chữ ký.
 */
export default function MauVanBanNghiQuyet({ resolution }) {
  if (!resolution) return null;

  // Xử lý ngày tháng năm ban hành
  const parseDate = (dateStr) => {
    if (!dateStr) {
      const now = new Date();
      return { day: now.getDate(), month: now.getMonth() + 1, year: now.getFullYear() };
    }
    const d = new Date(dateStr);
    return {
      day: d.getDate(),
      month: d.getMonth() + 1,
      year: d.getFullYear(),
    };
  };

  const { day, month, year } = parseDate(resolution.issued_date);

  // Xử lý tách các điều khoản từ content nếu có
  const renderContentArticles = () => {
    if (!resolution.content) {
      return (
        <div className="resolution-article" style={{ marginBottom: "16px", textIndent: "30px", textAlign: "justify" }}>
          <strong>Điều 1.</strong> Thông qua nội dung quyết nghị theo tờ trình và kết quả biểu quyết của phiên họp Hội đồng trường.
        </div>
      );
    }

    // Nếu văn bản có chứa cấu trúc Điều 1, Điều 2...
    const paragraphs = resolution.content.split("\n").filter((p) => p.trim().length > 0);
    return paragraphs.map((para, idx) => (
      <div
        key={idx}
        className="resolution-article pdf-no-break"
        style={{
          marginBottom: "12px",
          textAlign: "justify",
          textIndent: para.trim().startsWith("Điều") ? "0px" : "30px",
          pageBreakInside: "avoid",
          breakInside: "avoid",
        }}
      >
        {para.trim().startsWith("Điều") ? (
          <strong>{para.trim()}</strong>
        ) : (
          para.trim()
        )}
      </div>
    ));
  };

  return (
    <div
      id="resolution-document-print"
      style={{
        width: "794px",
        minHeight: "1123px",
        backgroundColor: "#ffffff",
        color: "#000000",
        fontFamily: "'Times New Roman', Times, serif",
        fontSize: "13pt",
        lineHeight: "1.45",
        boxSizing: "border-box",
        padding: "10px", // lề ngoài, html2pdf sẽ áp dụng lề in [20, 30, 20, 15] mm
        margin: "0 auto",
      }}
    >
      {/* 1. Phần Đầu: Quốc hiệu, Tiêu ngữ và Tên Cơ quan */}
      <table style={{ width: "100%", borderCollapse: "collapse", marginBottom: "20px" }}>
        <tbody>
          <tr>
            <td style={{ width: "45%", textAlign: "center", verticalAlign: "top" }}>
              <div style={{ fontSize: "11pt", fontWeight: "bold", textTransform: "uppercase" }}>
                TRƯỜNG ĐẠI HỌC CÔNG NGHỆ SÀI GÒN
              </div>
              <div style={{ fontSize: "12pt", fontWeight: "bold", textTransform: "uppercase" }}>
                HỘI ĐỒNG TRƯỜNG
              </div>
              <div style={{ margin: "2px auto 6px auto", width: "120px", borderBottom: "1px solid #000" }}></div>
              <div style={{ fontSize: "11pt" }}>
                Số: {resolution.resolution_number || "..../NQ-HĐT"}
              </div>
            </td>
            <td style={{ width: "55%", textAlign: "center", verticalAlign: "top" }}>
              <div style={{ fontSize: "11pt", fontWeight: "bold", textTransform: "uppercase" }}>
                CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM
              </div>
              <div style={{ fontSize: "12pt", fontWeight: "bold" }}>
                Độc lập - Tự do - Hạnh phúc
              </div>
              <div style={{ margin: "2px auto 8px auto", width: "160px", borderBottom: "1px solid #000" }}></div>
              <div style={{ fontSize: "11pt", fontStyle: "italic" }}>
                TP. Hồ Chí Minh, ngày {day} tháng {month} năm {year}
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      {/* 2. Tiêu đề Văn bản Nghị quyết */}
      <div style={{ textAlign: "center", marginBottom: "24px", marginTop: "10px" }}>
        <div style={{ fontSize: "15pt", fontWeight: "bold", textTransform: "uppercase", letterSpacing: "1px" }}>
          NGHỊ QUYẾT
        </div>
        <div style={{ fontSize: "13pt", fontWeight: "bold", marginTop: "6px" }}>
          Về việc: {resolution.title}
        </div>
        <div style={{ margin: "6px auto 16px auto", width: "180px", borderBottom: "1px solid #000" }}></div>
        <div style={{ fontSize: "13pt", fontWeight: "bold", textTransform: "uppercase" }}>
          HỘI ĐỒNG TRƯỜNG TRƯỜNG ĐẠI HỌC CÔNG NGHỆ SÀI GÒN
        </div>
      </div>

      {/* 3. Căn cứ pháp lý & Căn cứ thẩm quyền */}
      <div style={{ textAlign: "justify", marginBottom: "18px", fontStyle: "italic", fontSize: "12pt" }}>
        <p style={{ margin: "4px 0", textIndent: "30px" }}>
          Căn cứ Luật Giáo dục đại học ngày 18 tháng 6 năm 2012 và Luật sửa đổi, bổ sung một số điều của Luật Giáo dục đại học ngày 19 tháng 11 năm 2018;
        </p>
        <p style={{ margin: "4px 0", textIndent: "30px" }}>
          Căn cứ Quy chế Tổ chức và Hoạt động của Trường Đại học Công nghệ Sài Gòn;
        </p>
        <p style={{ margin: "4px 0", textIndent: "30px" }}>
          Căn cứ Biên bản phiên họp {resolution.meeting_title ? `"${resolution.meeting_title}"` : "Hội đồng trường"} của Hội đồng trường Trường Đại học Công nghệ Sài Gòn;
        </p>
        {resolution.related_votings && resolution.related_votings.length > 0 && (
          <p style={{ margin: "4px 0", textIndent: "30px" }}>
            Căn cứ Kết quả biểu quyết của các thành viên Hội đồng trường đối với các nội dung tờ trình liên quan;
          </p>
        )}
        <p style={{ margin: "4px 0", textIndent: "30px" }}>
          Theo đề nghị của Ban Giám hiệu và các Đơn vị liên quan trực thuộc Trường,
        </p>
      </div>

      {/* 4. Khẩu hiệu Quyết nghị */}
      <div style={{ textAlign: "center", marginBottom: "16px", marginTop: "10px" }}>
        <span style={{ fontSize: "14pt", fontWeight: "bold", letterSpacing: "1px" }}>
          QUYẾT NGHỊ:
        </span>
      </div>

      {/* 5. Nội dung các Điều khoản */}
      <div style={{ marginBottom: "20px" }}>
        {renderContentArticles()}

        {/* Bảng Danh mục Hồ sơ trình liên quan được thông qua nếu có */}
        {resolution.related_dossiers && resolution.related_dossiers.length > 0 && (
          <div className="pdf-no-break" style={{ marginTop: "16px", marginBottom: "16px", pageBreakInside: "avoid" }}>
            <div style={{ fontWeight: "bold", marginBottom: "6px" }}>
              Danh mục hồ sơ, tờ trình làm căn cứ phê duyệt:
            </div>
            <table
              style={{
                width: "100%",
                borderCollapse: "collapse",
                fontSize: "11.5pt",
                textAlign: "left",
              }}
            >
              <thead>
                <tr style={{ backgroundColor: "#f2f2f2" }}>
                  <th style={{ border: "1px solid #333", padding: "6px 8px", width: "45px", textAlign: "center" }}>STT</th>
                  <th style={{ border: "1px solid #333", padding: "6px 8px" }}>Tên Tờ trình / Hồ sơ</th>
                  <th style={{ border: "1px solid #333", padding: "6px 8px", width: "180px" }}>Đơn vị trình</th>
                </tr>
              </thead>
              <tbody>
                {resolution.related_dossiers.map((hs, index) => (
                  <tr key={hs.id}>
                    <td style={{ border: "1px solid #333", padding: "6px 8px", textAlign: "center" }}>{index + 1}</td>
                    <td style={{ border: "1px solid #333", padding: "6px 8px" }}>{hs.title}</td>
                    <td style={{ border: "1px solid #333", padding: "6px 8px" }}>{hs.department}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}

        {/* Bảng Phân công Nhiệm vụ triển khai nếu có */}
        {resolution.tasks && resolution.tasks.length > 0 && (
          <div className="pdf-no-break" style={{ marginTop: "16px", marginBottom: "16px", pageBreakInside: "avoid" }}>
            <div style={{ fontWeight: "bold", marginBottom: "6px" }}>
              Phân công nhiệm vụ triển khai thực hiện:
            </div>
            <table
              style={{
                width: "100%",
                borderCollapse: "collapse",
                fontSize: "11.5pt",
                textAlign: "left",
              }}
            >
              <thead>
                <tr style={{ backgroundColor: "#f2f2f2" }}>
                  <th style={{ border: "1px solid #333", padding: "6px 8px", width: "45px", textAlign: "center" }}>STT</th>
                  <th style={{ border: "1px solid #333", padding: "6px 8px" }}>Nhiệm vụ</th>
                  <th style={{ border: "1px solid #333", padding: "6px 8px", width: "160px" }}>Đơn vị chủ trì</th>
                  <th style={{ border: "1px solid #333", padding: "6px 8px", width: "100px", textAlign: "center" }}>Thời hạn</th>
                </tr>
              </thead>
              <tbody>
                {resolution.tasks.map((task, idx) => (
                  <tr key={task.id}>
                    <td style={{ border: "1px solid #333", padding: "6px 8px", textAlign: "center" }}>{idx + 1}</td>
                    <td style={{ border: "1px solid #333", padding: "6px 8px" }}>
                      <strong>{task.title}</strong>
                      {task.content && <div style={{ fontSize: "10.5pt", color: "#444" }}>{task.content}</div>}
                    </td>
                    <td style={{ border: "1px solid #333", padding: "6px 8px" }}>{task.lead_department}</td>
                    <td style={{ border: "1px solid #333", padding: "6px 8px", textAlign: "center" }}>{task.due_date || "—"}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}

        {/* Điều khoản chung về hiệu lực thi hành */}
        <div className="pdf-no-break" style={{ marginTop: "16px", textAlign: "justify", textIndent: "30px", pageBreakInside: "avoid" }}>
          Nghị quyết này có hiệu lực kể từ ngày ký ban hành. Các thành viên Hội đồng trường, Ban Giám hiệu, Trưởng các đơn vị trực thuộc và các cá nhân có liên quan chịu trách nhiệm thi hành Nghị quyết này./.
        </div>
      </div>

      {/* 6. Phần Chân: Nơi nhận và Khối Ký tên đóng dấu */}
      <div
        className="signature-block pdf-no-break"
        style={{
          marginTop: "30px",
          pageBreakInside: "avoid",
          breakInside: "avoid",
        }}
      >
        <table style={{ width: "100%", borderCollapse: "collapse" }}>
          <tbody>
            <tr>
              <td style={{ width: "50%", verticalAlign: "top", fontSize: "11pt" }}>
                <div style={{ fontWeight: "bold", fontStyle: "italic", marginBottom: "4px" }}>
                  Nơi nhận:
                </div>
                <div style={{ paddingLeft: "10px", lineHeight: "1.4" }}>
                  - Bộ Giáo dục và Đào tạo (b/c);<br />
                  - Ban Giám hiệu (thực hiện);<br />
                  - Thành viên Hội đồng trường;<br />
                  - Các đơn vị trực thuộc trường;<br />
                  - Lưu: VT, VP HĐT.
                </div>
              </td>
              <td style={{ width: "50%", textAlign: "center", verticalAlign: "top" }}>
                <div style={{ fontSize: "12pt", fontWeight: "bold", textTransform: "uppercase" }}>
                  TM. HỘI ĐỒNG TRƯỜNG
                </div>
                <div style={{ fontSize: "12pt", fontWeight: "bold", textTransform: "uppercase" }}>
                  CHỦ TỊCH
                </div>
                <div style={{ fontStyle: "italic", fontSize: "10pt", marginTop: "4px", color: "#666" }}>
                  (Ký tên và đóng dấu)
                </div>
                <div style={{ height: "80px" }}></div>
                <div style={{ fontSize: "13pt", fontWeight: "bold" }}>
                  PGS.TS. Cao Hào Thi
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  );
}
