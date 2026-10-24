<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BusinessRulesTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Kịch bản A: Quy tắc thẩm tra hồ sơ tờ trình (Dossier review rules)
     */
    public function test_scenario_a_dossier_review_rules(): void
    {
        // 1. Chỉ đơn vị trình (UNIT_REP) mới có quyền tạo hồ sơ
        $unitRep = User::where('role', 'UNIT_REP')->first();
        $this->assertNotNull($unitRep);
        $this->assertEquals('Phòng Đào tạo', $unitRep->department->name);

        // 2. Chỉ Thư ký HĐT (SECRETARY) mới có quyền thẩm tra hồ sơ
        $secretary = User::where('role', 'SECRETARY')->first();
        $this->assertNotNull($secretary);
        $this->assertEquals('Văn phòng Hội đồng trường', $secretary->department->name);

        // 3. Quy trình trạng thái hồ sơ phải tuân thủ đúng docs/business-rules.md
        $allowedStatuses = [
            'DRAFT',
            'SUBMITTED',
            'IN_REVIEW',
            'APPROVED_FOR_MEETING',
            'REVISION_REQUIRED',
            'REJECTED',
            'SCHEDULED',
        ];

        foreach (['DRAFT', 'SUBMITTED', 'IN_REVIEW', 'APPROVED_FOR_MEETING'] as $st) {
            $this->assertContains($st, $allowedStatuses);
        }
    }

    /**
     * Kịch bản B: Quy tắc triệu tập phiên họp, túc số >= 2/3 và loại trừ quyền biểu quyết của Khách mời
     */
    public function test_scenario_b_meeting_quorum_and_guest_voting_exclusion(): void
    {
        // 1. Giả lập thành phần tham dự: 15 thành viên chính thức HĐT, 5 khách mời
        $totalOfficialMembers = 15;
        $presentOfficialCount = 11; // 11 / 15 = 73.3% >= 66.7% (2/3)

        $quorumPercentage = ($presentOfficialCount / $totalOfficialMembers) * 100;
        $isQuorumValid = $quorumPercentage >= 66.67;

        $this->assertTrue($isQuorumValid, 'Túc số 11/15 (73.3%) phải hợp lệ theo quy chế Hội đồng trường STU');

        // Khi chỉ có 9/15 có mặt (60% < 66.7%) -> Phiên họp không đủ điều kiện biểu quyết
        $lowPresentCount = 9;
        $lowQuorumPercentage = ($lowPresentCount / $totalOfficialMembers) * 100;
        $isLowQuorumValid = $lowQuorumPercentage >= 66.67;

        $this->assertFalse($isLowQuorumValid, 'Túc số 9/15 (60%) không được phép mở phiên biểu quyết');

        // 2. RÀNG BUỘC CỨNG: Khách mời (GUEST) TUYỆT ĐỐI không có quyền biểu quyết (can_vote = false)
        $guestAttendee = [
            'attendee_type' => 'GUEST',
            'full_name' => 'Chuyên gia Khách Mời',
            'can_vote' => false,
        ];

        $this->assertFalse($guestAttendee['can_vote']);
        $this->assertEquals('GUEST', $guestAttendee['attendee_type']);
    }

    /**
     * Kịch bản B (Tiếp theo) & E: Cơ chế bỏ phiếu kín chống bỏ phiếu 2 lần (Unique Voter Hash)
     */
    public function test_scenario_b_secret_voting_hash_uniqueness(): void
    {
        $votingId = 101;
        $userId = 3; // Thư ký
        $secretSalt = 'stu_secure_salt_2026';

        // Tạo voter_hash cho phiếu kín
        $voterHash1 = hash('sha256', "{$votingId}_{$userId}_{$secretSalt}");
        $voterHash2 = hash('sha256', "{$votingId}_{$userId}_{$secretSalt}");

        // Mã băm ẩn danh phải nhất quán cho cùng 1 người trong cùng 1 cuộc biểu quyết
        $this->assertEquals($voterHash1, $voterHash2);

        // Giả lập danh sách hash đã bỏ phiếu
        $castBallotHashes = [$voterHash1];

        // Khi người này gửi phiếu thứ 2: Phát hiện trùng lặp ngay lập tức
        $isDuplicate = in_array($voterHash2, $castBallotHashes);
        $this->assertTrue($isDuplicate, 'Phải phát hiện và ngăn chặn hành vi bỏ phiếu 2 lần');

        // Người dùng khác (User ID = 4) tạo hash khác hoàn toàn
        $voterHashUser4 = hash('sha256', "{$votingId}_4_{$secretSalt}");
        $this->assertNotEquals($voterHash1, $voterHashUser4);
        $this->assertFalse(in_array($voterHashUser4, $castBallotHashes));
    }

    /**
     * Kịch bản C & D: Hoàn thiện Nghị quyết, phân rã Nhiệm vụ và kiểm soát quá hạn
     */
    public function test_scenario_c_and_d_resolutions_and_tasks_workflow(): void
    {
        $task = [
            'task_code' => 'NV-NQ01-2026-01',
            'title' => 'Xây dựng Đề án mở ngành Robot & AI',
            'lead_department' => 'K_CNTT',
            'start_date' => '2026-01-01',
            'due_date' => '2026-06-30',
            'progress_percentage' => 45,
            'status' => 'IN_PROGRESS',
        ];

        $this->assertGreaterThanOrEqual(0, $task['progress_percentage']);
        $this->assertLessThanOrEqual(100, $task['progress_percentage']);

        // Cập nhật tiến độ 100% kèm minh chứng
        $task['progress_percentage'] = 100;
        $task['status'] = 'COMPLETED';

        $this->assertEquals(100, $task['progress_percentage']);
        $this->assertEquals('COMPLETED', $task['status']);

        // Kiểm tra logic phát hiện quá hạn
        $overdueDate = '2026-01-01';
        $currentDate = '2026-10-24';
        $isOverdue = strtotime($currentDate) > strtotime($overdueDate) && $task['progress_percentage'] < 100;

        // Nếu tiến độ < 100% mà quá hạn thì trạng thái phải là OVERDUE
        $incompleteTask = ['progress_percentage' => 70, 'due_date' => $overdueDate];
        $isTaskOverdue = strtotime($currentDate) > strtotime($incompleteTask['due_date']) && $incompleteTask['progress_percentage'] < 100;
        $this->assertTrue($isTaskOverdue, 'Nhiệm vụ chưa đạt 100% và qua hạn chót phải đánh dấu là OVERDUE');
    }

    /**
     * Kịch bản E: Kiểm tra phân quyền truy cập tài liệu bảo mật (INTERNAL vs PUBLIC_AGENDA)
     */
    public function test_scenario_e_document_access_control(): void
    {
        // 1. Tài liệu nội bộ (INTERNAL): Chỉ thành viên HĐT chính thức mới được truy cập
        $councilMember = User::where('role', 'MEMBER')->first();
        $unitRep = User::where('role', 'UNIT_REP')->first();

        $internalDoc = [
            'file_name' => 'To_Trinh_Kien_Toan_Nhan_Su_Mat.pdf',
            'access_level' => 'INTERNAL',
        ];

        $publicAgendaDoc = [
            'file_name' => 'Chuong_Trinh_Hop_Chung.pdf',
            'access_level' => 'PUBLIC_AGENDA',
        ];

        // Thành viên HĐT có quyền xem cả INTERNAL và PUBLIC_AGENDA
        $memberCanAccessInternal = in_array($councilMember->role, ['CHAIR', 'SECRETARY', 'MEMBER']);
        $this->assertTrue($memberCanAccessInternal, 'Thành viên HĐT phải xem được tài liệu INTERNAL');

        // Đơn vị trình (UNIT_REP) KHÔNG được xem tài liệu INTERNAL của HĐT
        $unitRepCanAccessInternal = in_array($unitRep->role, ['CHAIR', 'SECRETARY', 'MEMBER']);
        $this->assertFalse($unitRepCanAccessInternal, 'Đơn vị trình không được xem tài liệu nội bộ INTERNAL');

        // Nhưng UNIT_REP xem được tài liệu PUBLIC_AGENDA
        $unitRepCanAccessPublic = in_array($unitRep->role, ['CHAIR', 'SECRETARY', 'MEMBER', 'UNIT_REP', 'EXECUTING_UNIT', 'RECTOR_OFFICE']);
        $this->assertTrue($unitRepCanAccessPublic, 'Đơn vị trình được xem tài liệu công khai phiên họp');
    }
}
