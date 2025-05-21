<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    // テスト前準備処理
    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
        $this->user = User::factory()->create();
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'is_modified' => true,
            'is_approved' => false,
        ]);
    }

    /** @test */
    // 承認待ちの修正申請が全て表示されている
    public function it_displays_all_pending_correction_requests()
    {
        // 他のユーザーの修正申請も作成
        $otherUser = User::factory()->create();
        $otherAttendance = Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'is_modified' => true,
            'is_approved' => false,
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get(route('stamp_correction_request.list', ['status' => 'pending']))
            ->assertStatus(200)
            ->assertSee($this->user->name)
            ->assertSee($this->attendance->formatted_full_date)
            ->assertSee($otherUser->name) // 他ユーザーの申請も確認
            ->assertSee($otherAttendance->formatted_full_date)
            ->assertSee('承認待ち');
    }

    /** @test */
    // 承認済みの修正申請が全て表示されている
    public function it_displays_all_approved_correction_requests()
    {
        // 他のユーザーの承認済み申請も作成
        $otherUser = User::factory()->create();
        $otherAttendance = Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'is_modified' => true,
            'is_approved' => true,
        ]);

        // メインの申請も承認済みに更新
        $this->attendance->update(['is_approved' => true]);

        $this->actingAs($this->admin, 'admin')
            ->get(route('stamp_correction_request.list', ['status' => 'approved']))
            ->assertStatus(200)
            ->assertSee('承認済み')
            ->assertSee($this->user->name)
            ->assertSee($otherUser->name) // 他ユーザーの承認済み申請も見えているか確認
            ->assertSee($this->attendance->formatted_full_date)
            ->assertSee($otherAttendance->formatted_full_date);
    }

    /** @test */
    // 修正申請の詳細内容が正しく表示されている
    public function it_displays_the_details_of_a_correction_request()
    {
        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.stamp_correction_request.show', $this->attendance->id))
            ->assertStatus(200)
            ->assertSee($this->user->name)
            ->assertSee($this->attendance->formatted_year)
            ->assertSee($this->attendance->formatted_monthday);
    }

    /** @test */
    // 修正申請の承認処理が正しく行われる
    public function it_correctly_approves_a_correction_request()
    {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.stamp_correction_request.approve', $this->attendance->id))
            ->assertRedirect(route('stamp_correction_request.list'));

        $this->assertDatabaseHas('attendances', [
            'id' => $this->attendance->id,
            'is_approved' => true
        ]);
    }
}