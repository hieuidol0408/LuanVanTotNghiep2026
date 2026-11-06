<?php

namespace Tests\Feature;

use App\Models\NghiQuyet;
use App\Models\PhienHop;
use App\Models\User;
use Tests\TestCase;

class ResolutionWebTest extends TestCase
{
    /**
     * 1. Danh sách Nghị quyết hiển thị thành công với bộ lọc và danh sách dữ liệu.
     */
    public function test_resolutions_index_renders_successfully(): void
    {
        $chair = User::where('email', 'chutich_hdt@stu.edu.vn')->first();
        $this->actingAs($chair);

        $response = $this->get(route('resolutions.index'));

        $response->assertStatus(200);
        $response->assertSee('Nghị quyết Hội đồng trường STU');
        $response->assertSee('Tìm kiếm');
        $response->assertSee('Trạng thái');
        $response->assertSee('Phiên họp');
        $response->assertSee('Soạn thảo nghị quyết mới');
    }

    /**
     * 2. Biểu mẫu soạn thảo Nghị quyết mới hiển thị đầy đủ các trường nghiệp vụ.
     */
    public function test_resolutions_create_renders_form(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        $response = $this->get(route('resolutions.create'));

        $response->assertStatus(200);
        $response->assertSee('Soạn thảo Dự thảo Nghị quyết Hội đồng trường');
        $response->assertSee('Số hiệu Nghị quyết');
        $response->assertSee('Trích yếu nội dung');
        $response->assertSee('Toàn văn Quyết nghị');
        $response->assertSee('Kỳ họp Hội đồng trường ban hành');
    }

    /**
     * 3. Lưu Nghị quyết mới (Dự thảo) thành công và liên kết phiên họp.
     */
    public function test_resolutions_store_creates_draft_resolution(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        $meeting = PhienHop::first();
        $soNq = 'TEST-NQ-' . uniqid();

        $response = $this->post(route('resolutions.store'), [
            'SoNghiQuyet' => $soNq,
            'TrichYeu' => 'Nghị quyết kiểm thử tự động lưu dự thảo ' . uniqid(),
            'NoiDung' => "QUYẾT NGHỊ:\nĐiều 1. Thông qua chủ trương kiểm thử tự động.",
            'MaPhienHop' => $meeting->MaPhienHop,
            'NgayBanHanh' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $resolution = NghiQuyet::where('SoNghiQuyet', $soNq)->first();
        $this->assertNotNull($resolution);
        $this->assertEquals('Dự thảo', $resolution->TrangThai);
    }

    /**
     * 4. Xem chi tiết toàn văn Nghị quyết & danh sách nhiệm vụ phân rã.
     */
    public function test_resolutions_show_renders_full_text_and_tasks(): void
    {
        $resolution = NghiQuyet::first();
        if (! $resolution) {
            $this->markTestSkipped('Chưa có Nghị quyết trong CSDL.');
        }

        $chair = User::where('email', 'chutich_hdt@stu.edu.vn')->first();
        $this->actingAs($chair);

        $response = $this->get(route('resolutions.show', $resolution->MaNghiQuyet));

        $response->assertStatus(200);
        $response->assertSee($resolution->SoNghiQuyet);
        $response->assertSee('Các Nhiệm vụ phân rã thi hành');
        $response->assertSee('Tiến độ Thực hiện Nghị quyết');
    }

    /**
     * 5. Chủ tịch Hội đồng trường ký ban hành Nghị quyết.
     */
    public function test_resolutions_chair_can_sign_resolution(): void
    {
        $chair = User::where('email', 'chutich_hdt@stu.edu.vn')->first();
        $this->actingAs($chair);

        $resolution = NghiQuyet::first();
        if (! $resolution) {
            $this->markTestSkipped('Chưa có Nghị quyết.');
        }

        $response = $this->post(route('resolutions.sign', $resolution->MaNghiQuyet));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $resolution->refresh();
        $this->assertEquals('Đã ký ban hành', $resolution->TrangThai);
    }

    /**
     * 6. Thư ký / Chủ tịch công bố hiệu lực thi hành Nghị quyết toàn trường.
     */
    public function test_resolutions_secretary_can_publish_resolution(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->actingAs($secretary);

        $resolution = NghiQuyet::first();
        if (! $resolution) {
            $this->markTestSkipped('Chưa có Nghị quyết.');
        }

        $response = $this->post(route('resolutions.publish', $resolution->MaNghiQuyet));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $resolution->refresh();
        $this->assertEquals('Đã công bố hiệu lực', $resolution->TrangThai);
    }

    /**
     * 7. Đại diện đơn vị (UNIT_REP) không được phép ký ban hành Nghị quyết.
     */
    public function test_resolutions_unauthorized_unit_rep_cannot_sign(): void
    {
        $unitRep = User::where('email', 'daotao@stu.edu.vn')->first();
        if (! $unitRep) {
            $this->markTestSkipped('Chưa có tài khoản UNIT_REP.');
        }

        $this->actingAs($unitRep);

        $resolution = NghiQuyet::first();
        if (! $resolution) {
            $this->markTestSkipped('Chưa có Nghị quyết.');
        }

        $response = $this->post(route('resolutions.sign', $resolution->MaNghiQuyet));

        $response->assertSessionHas('error');
    }
}
