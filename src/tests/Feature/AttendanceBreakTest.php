<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class AttendanceBreakTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    // 休憩ボタンが正しく機能する（出勤中→休憩中）
    public function break_button_works_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤登録
        $this->post(route('attendance.startWork'));

        // 出勤中の画面に「休憩入」ボタンがあるか確認
        $response = $this->get(route('attendance.index'));
        $response->assertSee('休憩入');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today()->format('Y-m-d'))
            ->first();

        $this->assertNotNull($attendance);

        // 休憩開始登録
        $this->post(route('attendance.startBreak'));

        // break_times テーブルにattendance_idが存在するか確認
        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
        ]);

        // 休憩中の画面に「休憩戻」ボタンがあるか確認
        $response = $this->get(route('attendance.index'));
        $response->assertSee('休憩戻');
    }

    /** @test */
    // 休憩は一日に何回でもできる（休憩入→休憩戻→休憩入を繰り返す）
    public function break_can_be_taken_multiple_times_per_day()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤処理
        $this->post(route('attendance.startWork'));

        // 1回目の休憩開始
        $this->post(route('attendance.startBreak'));

        // 1回目の休憩終了（休憩戻）
        $this->post(route('attendance.endBreak'));

        // DBに1件の休憩が登録されているか確認
        $this->assertDatabaseCount('break_times', 1);

        // 2回目の休憩開始
        $this->post(route('attendance.startBreak'));

        // DBに2件の休憩が登録されているか確認
        $this->assertDatabaseCount('break_times', 2);

        // 「休憩戻」ボタンが表示されているか確認
        $response = $this->get(route('attendance.index'));
        $response->assertSee('休憩戻');
    }

    /** @test */
    // 休憩戻ボタンが正しく機能する（休憩中→出勤中）
    public function break_end_button_works_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤処理
        $this->post(route('attendance.startWork'));

        // 休憩開始
        $this->post(route('attendance.startBreak'));

        // 休憩終了（休憩戻）
        $this->post(route('attendance.endBreak'));

        // DBに休憩終了時間が記録されているか確認
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today()->format('Y-m-d'))
            ->first();

        $breakTime = $attendance->breakTimes()->latest()->first();
        $this->assertNotNull($breakTime->break_end);
        $this->assertNotEmpty($breakTime->break_end);

        // 画面に「出勤中」の表示があるか確認
        $response = $this->get(route('attendance.index'));
        $response->assertSee('出勤中');
    }

    /** @test */
    // 休憩戻は一日に何回でもできる（休憩入→休憩戻→休憩入を繰り返す）
    public function break_end_can_be_taken_multiple_times_per_day()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤処理
        $this->post(route('attendance.startWork'));

        // 1回目の休憩開始
        $this->post(route('attendance.startBreak'));

        // 1回目の休憩終了（休憩戻）
        $this->post(route('attendance.endBreak'));

        // 2回目の休憩開始
        $this->post(route('attendance.startBreak'));

        // 2回目の休憩終了（休憩戻）
        $this->post(route('attendance.endBreak'));

        // DBに2回分の休憩が記録されていることを確認
        $this->assertDatabaseCount('break_times', 2);
    }
}