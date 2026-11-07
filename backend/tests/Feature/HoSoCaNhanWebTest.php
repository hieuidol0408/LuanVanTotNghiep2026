<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HoSoCaNhanWebTest extends TestCase
{
    /**
     * Kiểm tra truy cập trang hồ sơ cá nhân
     */
    public function test_can_view_profile_page(): void
    {
        $user = User::first();
        if (! $user) {
            $this->markTestSkipped('Không có dữ liệu người dùng');
        }

        $response = $this->actingAs($user)->get('/profile');
        $response->assertStatus(200);
        $response->assertSee('Hồ sơ cá nhân');
        $response->assertSee($user->full_name);
    }

    /**
     * Kiểm tra cập nhật thông tin họ tên cá nhân
     */
    public function test_can_update_profile_name(): void
    {
        $user = User::first();
        if (! $user) {
            $this->markTestSkipped('Không có dữ liệu người dùng');
        }

        $originalName = $user->HoTen;
        $newName = $originalName . ' (Cập nhật)';

        $response = $this->actingAs($user)->put('/profile', [
            'HoTen' => $newName,
        ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals($newName, $user->HoTen);

        // Khôi phục lại tên ban đầu
        $user->HoTen = $originalName;
        $user->save();
    }
}
