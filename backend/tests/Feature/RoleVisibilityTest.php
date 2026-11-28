<?php

namespace Tests\Feature;

use App\Models\NghiQuyet;
use App\Models\User;
use Tests\TestCase;

class RoleVisibilityTest extends TestCase
{
    public function test_unit_rep_only_sees_allowed_menus_and_is_blocked_from_others(): void
    {
        $unitRep = User::where('email', 'daotao@stu.edu.vn')->firstOrFail();
        $this->actingAs($unitRep);

        $dashboard = $this->get('/dashboard');
        $dashboard->assertOk()
            ->assertSee('Hồ sơ trình')
            ->assertSee('Nghị quyết HĐT')
            ->assertSee('Kho tài liệu số')
            ->assertDontSee('Quản lý phiên họp')
            ->assertDontSee('Lịch sử phiên họp')
            ->assertDontSee('Chương trình họp')
            ->assertDontSee('Biểu quyết &amp; Bỏ phiếu', false)
            ->assertDontSee('Nhiệm vụ phân công')
            ->assertDontSee('Báo cáo tiến độ')
            ->assertDontSee('Thành viên Hội đồng')
            ->assertDontSee('Quản lý người dùng');

        $this->get('/meetings')->assertRedirect(route('dashboard'));
        $this->get('/votes')->assertRedirect(route('dashboard'));
        $this->get('/tasks')->assertRedirect(route('dashboard'));
        $this->get('/task-reports')->assertRedirect(route('dashboard'));
        $this->get('/users')->assertRedirect(route('dashboard'));
    }

    public function test_executing_unit_only_sees_execution_menus_and_actions(): void
    {
        $execUnit = User::where('email', 'cntt@stu.edu.vn')->firstOrFail();
        $this->actingAs($execUnit);

        $dashboard = $this->get('/dashboard');
        $dashboard->assertOk()
            ->assertSee('Nghị quyết HĐT')
            ->assertSee('Nhiệm vụ phân công')
            ->assertSee('Báo cáo tiến độ')
            ->assertSee('Kho tài liệu số')
            ->assertDontSee(url('/meetings'), false)
            ->assertDontSee(url('/submissions'), false)
            ->assertDontSee(route('votes.index'), false)
            ->assertDontSee(route('members.index'), false)
            ->assertDontSee(route('users.index'), false);

        $this->get('/tasks')
            ->assertOk()
            ->assertDontSee('Giao nhiệm vụ mới');

        $this->get('/task-reports')
            ->assertOk()
            ->assertSee('Nộp báo cáo tiến độ');

        $this->get('/submissions')->assertRedirect(route('dashboard'));
        $this->get('/votes')->assertRedirect(route('dashboard'));
    }

    public function test_member_only_sees_council_meeting_and_voting_menus(): void
    {
        $member = User::where('email', 'thanhvien_hdt@stu.edu.vn')->firstOrFail();
        $this->actingAs($member);

        $dashboard = $this->get('/dashboard');
        $dashboard->assertOk()
            ->assertSee('Quản lý phiên họp')
            ->assertSee('Biểu quyết & Bỏ phiếu', false)
            ->assertSee('Nghị quyết HĐT')
            ->assertDontSee(url('/tasks'), false)
            ->assertDontSee(route('task-reports.index'), false)
            ->assertDontSee(route('users.index'), false);

        $this->get('/tasks')->assertRedirect(route('dashboard'));
        $this->get('/task-reports')->assertRedirect(route('dashboard'));
        $this->get('/users')->assertRedirect(route('dashboard'));
    }

    public function test_secretary_sees_operations_but_not_user_management_or_chair_sign(): void
    {
        $secretary = User::where('email', 'thuky_hdt@stu.edu.vn')->firstOrFail();
        $this->actingAs($secretary);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Quản lý phiên họp')
            ->assertSee('Nghị quyết HĐT')
            ->assertSee('Nhiệm vụ phân công')
            ->assertDontSee('Quản lý người dùng');

        $this->get('/users')->assertRedirect(route('dashboard'));

        $draft = NghiQuyet::whereIn('TrangThai', ['DRAFT', 'Dự thảo'])->first();
        if ($draft) {
            $this->get(route('resolutions.show', $draft->MaNghiQuyet))
                ->assertOk()
                ->assertDontSee('Chủ tịch ký ban hành');
        }
    }

    public function test_rector_office_cannot_see_submit_or_create_task_buttons(): void
    {
        $rector = User::where('email', 'vphieutruong@stu.edu.vn')->firstOrFail();
        $this->actingAs($rector);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Nghị quyết HĐT')
            ->assertSee('Nhiệm vụ phân công')
            ->assertSee('Báo cáo tiến độ')
            ->assertDontSee('Biểu quyết &amp; Bỏ phiếu', false)
            ->assertDontSee('Quản lý người dùng');

        $this->get('/tasks')
            ->assertOk()
            ->assertDontSee('Giao nhiệm vụ mới');

        $this->get('/task-reports')
            ->assertOk()
            ->assertDontSee('Nộp báo cáo tiến độ');
    }
}
