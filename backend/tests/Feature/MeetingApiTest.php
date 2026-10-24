<?php

namespace Tests\Feature;

use App\Models\PhienHop;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MeetingApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Danh sách phiên họp yêu cầu xác thực
     */
    public function test_unauthenticated_cannot_access_meetings(): void
    {
        $response = $this->getJson('/api/v1/meetings');
        $response->assertStatus(401);
    }

    /**
     * Thành viên đã đăng nhập có thể xem danh sách phiên họp
     */
    public function test_authenticated_user_can_list_meetings(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $this->assertNotNull($secretary);

        $response = $this->actingAs($secretary, 'sanctum')->getJson('/api/v1/meetings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'term',
                        'meeting_type',
                        'meeting_format',
                        'meeting_date',
                        'start_time',
                        'end_time',
                        'location',
                        'status',
                        'status_label',
                        'agendas_count',
                        'attendees_count',
                    ],
                ],
                'meta' => [
                    'page',
                    'per_page',
                    'total',
                    'total_pages',
                ],
            ]);
    }

    /**
     * Thư ký có thể tạo mới phiên họp
     */
    public function test_secretary_can_create_meeting(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();

        $payload = [
            'title' => 'Phiên họp bất thường Hội đồng trường STU tháng 12/2026',
            'meeting_date' => '2026-12-20',
            'start_time' => '09:00',
            'end_time' => '11:30',
            'location' => 'Phòng họp Hội đồng trường A202',
            'meeting_format' => 'IN_PERSON',
            'description' => 'Xem xét các nội dung khẩn cấp về công tác nhân sự và tài chính.',
        ];

        $response = $this->actingAs($secretary, 'sanctum')->postJson('/api/v1/meetings', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', $payload['title'])
            ->assertJsonPath('data.status', 'PREPARING');

        $this->assertDatabaseHas('phien_hop', [
            'TenPhienHop' => $payload['title'],
            'TrangThai' => 'PREPARING',
        ]);
    }

    /**
     * Xem chi tiết phiên họp kèm thành phần và chương trình họp
     */
    public function test_can_view_meeting_details(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $meeting = PhienHop::first();
        $this->assertNotNull($meeting);

        $response = $this->actingAs($secretary, 'sanctum')->getJson("/api/v1/meetings/{$meeting->MaPhienHop}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'agendas',
                    'attendees',
                    'documents',
                ],
            ]);
    }

    /**
     * Thêm khách mời và bảo đảm khách mời can_vote = false
     */
    public function test_add_guest_attendee_and_verify_no_voting_rights(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $meeting = PhienHop::first();

        $guestPayload = [
            'full_name' => 'ThS. Nguyễn Khách Mời Mới',
            'department' => 'Sở Giáo dục & Đào tạo TP.HCM',
            'academic_title' => 'ThS',
            'email' => 'guest@hcm.edu.vn',
            'phone' => '0988776655',
            'notes' => 'Mời tham vấn chuyên môn về kiểm định chất lượng',
        ];

        $response = $this->actingAs($secretary, 'sanctum')->postJson("/api/v1/meetings/{$meeting->MaPhienHop}/attendees", $guestPayload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.attendee_type', 'GUEST')
            ->assertJsonPath('data.can_vote', false); // Quy tắc cứng: Khách mời không có quyền biểu quyết

        $this->assertDatabaseHas('khach_moi', [
            'HoTen' => $guestPayload['full_name'],
            'Email' => $guestPayload['email'],
        ]);
    }

    /**
     * Thêm và xóa mục chương trình họp
     */
    public function test_manage_agenda_items(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->first();
        $meeting = PhienHop::first();

        $agendaPayload = [
            'title' => 'Báo cáo tổng kết công tác tuyển sinh và định hướng năm học mới',
            'duration_minutes' => 40,
            'presenter' => 'Trưởng Phòng Đào tạo',
            'is_voting_required' => true,
        ];

        // 1. Thêm mục chương trình
        $createResponse = $this->actingAs($secretary, 'sanctum')->postJson("/api/v1/meetings/{$meeting->MaPhienHop}/agendas", $agendaPayload);
        $createResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', $agendaPayload['title']);

        $agendaId = $createResponse->json('data.id');

        // 2. Xóa mục chương trình
        $deleteResponse = $this->actingAs($secretary, 'sanctum')->deleteJson("/api/v1/meetings/{$meeting->MaPhienHop}/agendas/{$agendaId}");
        $deleteResponse->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
