<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class AttendanceCheckoutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    // 退勤ボタンが正しく機能する
    public function checkout_button_works_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤処理（正常にリダイレクトされることを確認）
        $response = $this->post(route('attendance.startWork'));
        $response->assertRedirect(route('attendance.index'));

        // 出勤中の画面に「退勤」ボタンが表示されているか確認
        $response = $this->get(route('attendance.index'));
        $response->assertSee('退勤');

        // 退勤処理
        $response = $this->post(route('attendance.endWork'));
        $response->assertRedirect(route('attendance.index'));

        // 画面のステータスが「退勤済」になっているか確認
        $response = $this->get(route('attendance.index'));
        $response->assertSee('退勤済');
    }

    /** @test */
    // 退勤時刻が管理画面で確認できる
    public function checkout_time_is_visible_in_admin_list()
    {
        // 管理者ユーザーを作成し、管理者ガードでログイン
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => User::factory()->create()->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'start_time' => Carbon::today()->setTime(9, 0), // 出勤9:00
            'end_time' => Carbon::today()->setTime(12, 5), // 退勤12:05
            'status' => Attendance::STATUS_DONE,
        ]);

        // 管理者用勤怠一覧ページへアクセス
        $response = $this->get(route('admin.attendance.list', ['date' => Carbon::today()->format('Y-m-d')]));

        // 退勤時間（例 "12:05"）が画面に表示されていることを確認
        $endTimeFormatted = $attendance->end_time->format('H:i');
        $response->assertStatus(200);
        $response->assertSee($endTimeFormatted);
    }
}
