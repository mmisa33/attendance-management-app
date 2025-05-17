<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    // その日の全ユーザーの勤怠情報が正確に確認できる
    public function admin_can_view_attendance_list_for_the_day()
    {
        $admin = Admin::factory()->create();
        $today = Carbon::today();

        // テスト用のユーザーと勤怠データを3件作成
        $users = User::factory()->count(3)->create();

        foreach ($users as $user) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $today->toDateString(),
                'start_time' => $today->copy()->setTime(9, 0, 0)->toDateTimeString(),
                'end_time' => $today->copy()->setTime(18, 0, 0)->toDateTimeString(),
                'status' => Attendance::STATUS_DONE,
            ]);

            $attendance->breakTimes()->create([
                'break_start' => $today->copy()->setTime(12, 0, 0)->toDateTimeString(),
                'break_end' => $today->copy()->setTime(13, 0, 0)->toDateTimeString(),
            ]);
        }

        // 他の日のデータ（昨日）を作成し、表示されないことを確認
        $yesterday = Carbon::yesterday();
        $otherUser = User::factory()->create(['name' => 'Not Expected User']);
        Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'date' => $yesterday->toDateString(),
            'start_time' => $yesterday->copy()->setTime(9, 0, 0)->toDateTimeString(),
            'end_time' => $yesterday->copy()->setTime(18, 0, 0)->toDateTimeString(),
            'status' => Attendance::STATUS_DONE,
        ]);

        // 管理者としてアクセス
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.list', ['date' => $today->toDateString()]));

        $response->assertStatus(200);

        // 画面上に全てのユーザーの勤怠情報が表示されているかチェック
        foreach ($users as $user) {
            $response->assertSee($user->name); // ユーザー名
            $response->assertSee('09:00'); // 出勤時間
            $response->assertSee('18:00'); // 退勤時間
            $response->assertSee('1:00'); // 休憩時間
            $response->assertSee('8:00'); // 合計労働時間
        }

        // 他の日付のデータが表示されていないことを確認
        $response->assertDontSee('Not Expected User');
    }

    /** @test */
    // 勤怠一覧画面にその日の日付が表示されている
    public function attendance_list_shows_current_date_and_all_users_attendance()
    {
        $admin = Admin::factory()->create();
        $today = Carbon::today();

        $users = User::factory()->count(3)->create();

        foreach ($users as $user) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $today->toDateString(),
                'start_time' => $today->copy()->setTime(9, 0, 0)->toDateTimeString(),
                'end_time' => $today->copy()->setTime(18, 0, 0)->toDateTimeString(),
                'status' => Attendance::STATUS_DONE,
            ]);
        }

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.list', ['date' => $today->toDateString()]));

        $response->assertStatus(200);
        // ビューの日付表示は「YYYY年n月j日」のフォーマット
        $response->assertSee($today->format('Y年n月j日'));

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee('09:00');
            $response->assertSee('18:00');
        }
    }

    /** @test */
    // 「前日」を押下した時に前の日の勤怠情報が表示される
    public function clicking_previous_day_button_shows_previous_day_attendance()
    {
        $admin = Admin::factory()->create();
        $yesterday = Carbon::yesterday();

        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $yesterday->toDateString(),
            'start_time' => $yesterday->copy()->setTime(8, 30, 0)->toDateTimeString(),
            'end_time' => $yesterday->copy()->setTime(17, 30, 0)->toDateTimeString(),
            'status' => Attendance::STATUS_DONE,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.list', ['date' => $yesterday->toDateString()]));

        $response->assertStatus(200);
        $response->assertSee($yesterday->format('Y年n月j日'));
        $response->assertSee($user->name);
        $response->assertSee('08:30');
        $response->assertSee('17:30');
    }

    /** @test */
    // 「翌日」を押下した時に次の日の勤怠情報が表示される
    public function clicking_next_day_button_shows_next_day_attendance()
    {
        $admin = Admin::factory()->create();
        $tomorrow = Carbon::tomorrow();

        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $tomorrow->toDateString(),
            'start_time' => $tomorrow->copy()->setTime(10, 0, 0)->toDateTimeString(),
            'end_time' => $tomorrow->copy()->setTime(19, 0, 0)->toDateTimeString(),
            'status' => Attendance::STATUS_DONE,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.list', ['date' => $tomorrow->toDateString()]));

        $response->assertStatus(200);
        $response->assertSee($tomorrow->format('Y年n月j日'));
        $response->assertSee($user->name);
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }
}