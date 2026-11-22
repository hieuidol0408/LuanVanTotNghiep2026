<?php

namespace Tests\Feature;

use App\Models\TaiLieu;
use App\Models\User;
use App\Services\DocumentPreviewService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentPreviewWebTest extends TestCase
{
    use DatabaseTransactions;

    protected User $secretary;
    protected TaiLieu $doc;

    protected function setUp(): void
    {
        parent::setUp();

        $this->secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first()
            ?? User::factory()->create(['role' => 'SECRETARY']);

        $this->doc = TaiLieu::first() ?? TaiLieu::create([
            'TenTaiLieu' => 'Tài liệu thử nghiệm xem trực tuyến',
            'LoaiTaiLieu' => 'Tờ trình đính kèm',
            'DuongDan' => '/storage/documents/submissions/sample.pdf',
            'DinhDang' => 'pdf',
            'DungLuong' => 2048,
            'NgayTaiLen' => now(),
            'MaNguoiDung' => $this->secretary->MaNguoiDung,
        ]);
    }

    /**
     * 1. Kiểm tra trang xem trước tài liệu toàn màn hình hoạt động thành công
     */
    public function test_document_preview_page_renders(): void
    {
        $response = $this->actingAs($this->secretary)->get(route('documents.preview', $this->doc->MaTaiLieu));

        $response->assertStatus(200);
        $response->assertSee('Trình xem văn bản trực tiếp');
        $response->assertSee($this->doc->TenTaiLieu);
    }

    /**
     * 2. Kiểm tra API trả về nội dung phân tích tệp (JSON) phục vụ modal xem trực tuyến
     */
    public function test_document_content_api_returns_json(): void
    {
        $response = $this->actingAs($this->secretary)->getJson(route('documents.content', $this->doc->MaTaiLieu));

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('id', $this->doc->MaTaiLieu)
            ->assertJsonPath('title', $this->doc->TenTaiLieu);
    }

    /**
     * 3. Kiểm tra xem file trực tiếp (Stream inline) cho tệp PDF
     */
    public function test_document_view_inline_stream(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('kiem_tra.pdf', 500, 'application/pdf');
        $path = $file->storeAs('documents/test', 'kiem_tra.pdf', 'public');

        $pdfDoc = TaiLieu::create([
            'TenTaiLieu' => 'Tệp PDF xem trực tiếp',
            'LoaiTaiLieu' => 'Tờ trình đính kèm',
            'DuongDan' => '/storage/' . $path,
            'DinhDang' => 'pdf',
            'DungLuong' => 500 * 1024,
            'NgayTaiLen' => now(),
            'MaNguoiDung' => $this->secretary->MaNguoiDung,
        ]);

        $response = $this->actingAs($this->secretary)->get(route('documents.view', $pdfDoc->MaTaiLieu));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('inline', $response->headers->get('content-disposition'));
    }

    /**
     * 4. Kiểm tra DocumentPreviewService xử lý tệp DOCX thực tế
     */
    public function test_preview_service_parses_real_docx(): void
    {
        $realDocx = storage_path('app/public/documents/submissions/1795332478_18_11_HoiDongTruong.docx');
        if (!file_exists($realDocx)) {
            $this->markTestSkipped('Chưa có tệp 18_11_HoiDongTruong.docx mẫu để kiểm thử.');
        }

        $service = new DocumentPreviewService();
        $html = $service->convertDocxToHtml($realDocx);

        $this->assertStringContainsString('docx-preview-content', $html);
        $this->assertStringContainsString('TRƯỜNG ĐẠI HỌC CÔNG NGHỆ SÀI GÒN', $html);
    }
}
