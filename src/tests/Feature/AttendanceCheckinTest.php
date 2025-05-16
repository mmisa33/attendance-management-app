<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class AttendanceCheckinTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    // 勤務外状態のユーザーが出勤ボタンを押すと、出勤中のステータスに変わる
    public function checkin_button_works_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤画面にアクセス
        $response = $this->get(route('attendance.index'));

        // 勤務外ステータスで「出勤」ボタンが存在することを確認
        $response->assertSee('出勤');

        // 出勤ボタン押下
        $response = $this->post(route('attendance.startWork'));

        // データベースの確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'status' => Attendance::STATUS_WORKING
        ]);

        // 再度画面にアクセスして「出勤中」と表示されているか確認
        $response = $this->get(route('attendance.index'));
        $response->assertSee('出勤中');
    }

    /** @test */
    // 同じ日に二度目の出勤はできない
    public function test_checkin_button_is_disabled_after_one_checkin()
    {
        // 退勤済のユーザーを作成
        $user = User::factory()->create();
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'status' => Attendance::STATUS_DONE
        ]);
        $this->actingAs($user, 'web');

        // 勤怠画面にアクセス
        $response = $this->get(route('attendance.index'));

        // 「出勤」ボタンが表示されていないことを確認
        $response->assertDontSee('出勤');
        $response->assertSee('退勤済');
    }

    // 出勤後の時刻が管理画面に表示される
    public function test_checkin_time_is_visible_in_admin_view()
    {
        // 管理者の作成とログイン
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 出勤処理
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        $this->post(route('attendance.startWork'));

        // 管理画面にアクセスして出勤時刻が表示されていることを確認
        $response = $this->get(route('admin.attendance.list'));

        // 時刻のフォーマットを合わせて検証
        $checkinTime = Carbon::now()->format('H:i');
        $response->assertSee($checkinTime);
    }
}