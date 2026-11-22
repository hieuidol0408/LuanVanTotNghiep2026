<?php

namespace App\Services;

use DOMDocument;
use Exception;
use ZipArchive;

class DocumentPreviewService
{
    /**
     * Chuyển đổi tệp DOCX sang định dạng HTML hiển thị trực tiếp trên trình duyệt với hiệu năng cao.
     */
    public function convertDocxToHtml(string $filePath): string
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return '<div class="stu-doc-alert stu-doc-alert-danger">Không tìm thấy tập tin tài liệu trên hệ thống lưu trữ máy chủ.</div>';
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return '<div class="stu-doc-alert stu-doc-alert-danger">Không thể mở tệp Word (.docx). Tập tin có thể bị hỏng hoặc có mật khẩu bảo vệ.</div>';
        }

        $documentXml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!$documentXml) {
            return '<div class="stu-doc-alert stu-doc-alert-warning">Tệp Word rỗng hoặc không đúng cấu trúc OpenXML.</div>';
        }

        try {
            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->loadXML($documentXml, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);

            $body = $dom->getElementsByTagName('body')->item(0);
            if (!$body || !$body->hasChildNodes()) {
                return '<div class="stu-doc-alert stu-doc-alert-info">Tài liệu không có nội dung văn bản hiển thị.</div>';
            }

            $html = '<div class="docx-preview-content">';

            foreach ($body->childNodes as $node) {
                if ($node->nodeName === 'w:p') {
                    $html .= $this->renderParagraph($node);
                } elseif ($node->nodeName === 'w:tbl') {
                    $html .= $this->renderTable($node);
                }
            }

            $html .= '</div>';
            return $html;
        } catch (Exception $e) {
            return '<div class="stu-doc-alert stu-doc-alert-danger">Lỗi khi phân tích nội dung Word: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    /**
     * Render một đoạn văn bản <w:p>
     */
    protected function renderParagraph(\DOMNode $node): string
    {
        $align = 'left';
        $isHeading = false;
        $headingTag = 'p';
        $pText = '';

        foreach ($node->childNodes as $pChild) {
            if ($pChild->nodeName === 'w:pPr') {
                foreach ($pChild->childNodes as $pr) {
                    if ($pr->nodeName === 'w:jc') {
                        $v = $pr->getAttribute('w:val');
                        if ($v === 'center') $align = 'center';
                        elseif ($v === 'right') $align = 'right';
                        elseif ($v === 'both') $align = 'justify';
                    } elseif ($pr->nodeName === 'w:pStyle') {
                        $s = strtolower($pr->getAttribute('w:val'));
                        if (str_contains($s, 'heading1') || $s === '1' || str_contains($s, 'tieude1')) { 
                            $headingTag = 'h2'; 
                            $isHeading = true; 
                        } elseif (str_contains($s, 'heading2') || $s === '2' || str_contains($s, 'tieude2')) { 
                            $headingTag = 'h3'; 
                            $isHeading = true; 
                        } elseif (str_contains($s, 'heading3') || $s === '3') { 
                            $headingTag = 'h4'; 
                            $isHeading = true; 
                        }
                    }
                }
            } elseif ($pChild->nodeName === 'w:r') {
                $rBold = false;
                $rItalic = false;
                $rUnderline = false;
                $rColor = '';
                $rText = '';

                foreach ($pChild->childNodes as $rChild) {
                    if ($rChild->nodeName === 'w:rPr') {
                        foreach ($rChild->childNodes as $rPr) {
                            if ($rPr->nodeName === 'w:b') $rBold = true;
                            elseif ($rPr->nodeName === 'w:i') $rItalic = true;
                            elseif ($rPr->nodeName === 'w:u') $rUnderline = true;
                            elseif ($rPr->nodeName === 'w:color') {
                                $c = $rPr->getAttribute('w:val');
                                if ($c && $c !== 'auto') $rColor = '#' . $c;
                            }
                        }
                    } elseif ($rChild->nodeName === 'w:t') {
                        $rText .= $rChild->nodeValue;
                    } elseif ($rChild->nodeName === 'w:br') {
                        $rText .= '<br>';
                    }
                }

                if ($rText !== '') {
                    $escaped = htmlspecialchars($rText);
                    $escaped = str_replace(['&lt;br&gt;', "\t"], ['<br>', '&nbsp;&nbsp;&nbsp;&nbsp;'], $escaped);
                    if ($rBold) $escaped = "<strong>$escaped</strong>";
                    if ($rItalic) $escaped = "<em>$escaped</em>";
                    if ($rUnderline) $escaped = "<u>$escaped</u>";
                    if ($rColor) $escaped = "<span style=\"color:$rColor\">$escaped</span>";
                    $pText .= $escaped;
                }
            }
        }

        if (trim(strip_tags($pText)) === '') {
            return '<p class="docx-empty-p" style="margin:0; min-height:1em;">&nbsp;</p>';
        }

        if ($isHeading) {
            return "<$headingTag class=\"docx-heading\" style=\"text-align:$align; margin:20px 0 10px 0; color:#1e3a8a; font-weight:700;\">$pText</$headingTag>";
        }

        return "<p class=\"docx-p\" style=\"text-align:$align; margin:0 0 8px 0; line-height:1.6;\">$pText</p>";
    }

    /**
     * Render bảng biểu <w:tbl>
     */
    protected function renderTable(\DOMNode $node): string
    {
        $html = '<div style="margin:16px 0; overflow-x:auto;"><table class="docx-table" style="width:100%; border-collapse:collapse; border:1px solid #cbd5e1; font-size:13px;">';
        $isFirstRow = true;

        foreach ($node->childNodes as $tblChild) {
            if ($tblChild->nodeName === 'w:tr') {
                $html .= '<tr' . ($isFirstRow ? ' style="background:#f1f5f9; font-weight:700;"' : '') . '>';
                foreach ($tblChild->childNodes as $trChild) {
                    if ($trChild->nodeName === 'w:tc') {
                        $tag = $isFirstRow ? 'th' : 'td';
                        $tcText = '';

                        foreach ($trChild->childNodes as $tcP) {
                            if ($tcP->nodeName === 'w:p') {
                                $pContent = '';
                                foreach ($tcP->childNodes as $tcR) {
                                    if ($tcR->nodeName === 'w:r') {
                                        $isB = false;
                                        $tVal = '';
                                        foreach ($tcR->childNodes as $subNode) {
                                            if ($subNode->nodeName === 'w:rPr') {
                                                foreach ($subNode->childNodes as $subPr) {
                                                    if ($subPr->nodeName === 'w:b') $isB = true;
                                                }
                                            } elseif ($subNode->nodeName === 'w:t') {
                                                $tVal .= htmlspecialchars($subNode->nodeValue);
                                            }
                                        }
                                        if ($isB) $tVal = "<strong>$tVal</strong>";
                                        $pContent .= $tVal;
                                    }
                                }
                                if ($pContent !== '') {
                                    $tcText .= "<p style=\"margin:0 0 4px 0;\">$pContent</p>";
                                }
                            }
                        }

                        $html .= "<$tag style=\"border:1px solid #cbd5e1; padding:8px 12px; vertical-align:top;\">" . ($tcText ?: '&nbsp;') . "</$tag>";
                    }
                }
                $html .= '</tr>';
                $isFirstRow = false;
            }
        }

        $html .= '</table></div>';
        return $html;
    }

    /**
     * Chuyển đổi tệp XLSX sang HTML bảng biểu trực quan với các tabs theo sheet
     */
    public function convertXlsxToHtml(string $filePath): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy tệp bảng tính Excel trên hệ thống lưu trữ.',
                'sheets' => [],
            ];
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [
                'success' => false,
                'message' => 'Không thể mở tệp Excel (.xlsx).',
                'sheets' => [],
            ];
        }

        // 1. Đọc sharedStrings.xml nếu có
        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml) {
            $domSS = new DOMDocument();
            $domSS->loadXML($ssXml, LIBXML_NOERROR | LIBXML_NOWARNING);
            $siNodes = $domSS->getElementsByTagName('si');
            foreach ($siNodes as $si) {
                $tNodes = $si->getElementsByTagName('t');
                $str = '';
                foreach ($tNodes as $t) {
                    $str .= $t->nodeValue;
                }
                $sharedStrings[] = $str;
            }
        }

        // 2. Đọc danh sách sheet từ workbook.xml
        $sheetsInfo = [];
        $wbXml = $zip->getFromName('xl/workbook.xml');
        if ($wbXml) {
            $domWb = new DOMDocument();
            $domWb->loadXML($wbXml, LIBXML_NOERROR | LIBXML_NOWARNING);
            $sheetElements = $domWb->getElementsByTagName('sheet');
            $idx = 1;
            foreach ($sheetElements as $se) {
                $sheetName = $se->getAttribute('name') ?: "Sheet $idx";
                $sheetsInfo[] = [
                    'name' => $sheetName,
                    'file' => "xl/worksheets/sheet{$idx}.xml",
                ];
                $idx++;
            }
        }

        if (empty($sheetsInfo)) {
            $sheetsInfo[] = ['name' => 'Sheet 1', 'file' => 'xl/worksheets/sheet1.xml'];
        }

        $renderedSheets = [];
        foreach ($sheetsInfo as $info) {
            $sheetXml = $zip->getFromName($info['file']);
            if (!$sheetXml) continue;

            $domSheet = new DOMDocument();
            $domSheet->loadXML($sheetXml, LIBXML_NOERROR | LIBXML_NOWARNING);

            $rows = $domSheet->getElementsByTagName('row');
            if ($rows->length === 0) continue;

            $tableHtml = '<div class="table-responsive" style="max-height: 70vh; overflow: auto; border: 1px solid #cbd5e1; border-radius: 6px;">';
            $tableHtml .= '<table class="xlsx-sheet-table table table-bordered table-hover" style="width: 100%; border-collapse: collapse; font-size: 12.5px;">';

            $rowCount = 0;
            foreach ($rows as $row) {
                $rowCount++;
                $isHeader = ($rowCount === 1);
                $tableHtml .= '<tr' . ($isHeader ? ' style="background-color: #f1f5f9; font-weight: 700; position: sticky; top: 0; z-index: 2;"' : '') . '>';

                $cells = $row->getElementsByTagName('c');
                foreach ($cells as $c) {
                    $type = $c->getAttribute('t');
                    $valElements = $c->getElementsByTagName('v');
                    $val = $valElements->length > 0 ? $valElements->item(0)->nodeValue : '';

                    if ($type === 's' && isset($sharedStrings[(int) $val])) {
                        $val = $sharedStrings[(int) $val];
                    }

                    $tag = $isHeader ? 'th' : 'td';
                    $tableHtml .= "<$tag style=\"border: 1px solid #cbd5e1; padding: 6px 12px; white-space: nowrap;\">" . htmlspecialchars($val) . "</$tag>";
                }

                $tableHtml .= '</tr>';
                if ($rowCount > 500) {
                    $tableHtml .= '<tr><td colspan="20" style="text-align: center; color: #64748b; padding: 12px;">... Đã hiển thị 500 hàng đầu tiên của bảng tính ...</td></tr>';
                    break;
                }
            }

            $tableHtml .= '</table></div>';

            $renderedSheets[] = [
                'name' => $info['name'],
                'html' => $tableHtml,
            ];
        }

        $zip->close();

        return [
            'success' => true,
            'sheets' => $renderedSheets,
        ];
    }

    /**
     * Phân tích tệp PPTX để hiển thị thẻ tóm tắt các slide bài trình bày
     */
    public function convertPptxToHtml(string $filePath): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy tệp trình chiếu PowerPoint trên hệ thống lưu trữ.',
                'slides' => [],
            ];
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [
                'success' => false,
                'message' => 'Không thể mở tệp PowerPoint (.pptx).',
                'slides' => [],
            ];
        }

        $slides = [];
        $slideIndex = 1;

        while (true) {
            $slideXml = $zip->getFromName("ppt/slides/slide{$slideIndex}.xml");
            if (!$slideXml) break;

            $dom = new DOMDocument();
            $dom->loadXML($slideXml, LIBXML_NOERROR | LIBXML_NOWARNING);
            
            $textNodes = $dom->getElementsByTagName('p');
            $slideTexts = [];

            foreach ($textNodes as $p) {
                $line = '';
                $tNodes = $p->getElementsByTagName('t');
                foreach ($tNodes as $t) {
                    $line .= $t->nodeValue;
                }
                $line = trim($line);
                if ($line !== '') {
                    $slideTexts[] = $line;
                }
            }

            $title = !empty($slideTexts) ? array_shift($slideTexts) : "Slide $slideIndex";

            $slides[] = [
                'index' => $slideIndex,
                'title' => $title,
                'content' => $slideTexts,
            ];

            $slideIndex++;
            if ($slideIndex > 50) break;
        }

        $zip->close();

        return [
            'success' => true,
            'slides' => $slides,
        ];
    }
}
