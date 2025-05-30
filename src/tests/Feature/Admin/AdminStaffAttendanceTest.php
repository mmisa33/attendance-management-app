<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminStaffAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $users;

    // テスト前準備処理
    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
        $this->users = User::factory()->count(3)->create();

        // 勤怠データを生成
        foreach ($this->users as $user) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => now()->format('Y-m-d'),
                'start_time' => now()->setTime(9, 0, 0)->format('Y-m-d H:i:s'),
                'end_time' => now()->setTime(18, 0, 0)->format('Y-m-d H:i:s'),
                'note' => 'Test note',
            ]);
        }
    }

    /** @test */
    // 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
    public function admin_can_view_all_users_with_name_and_email()
    {
        // 管理者としてログイン
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.staff.list'));

        $response->assertStatus(200);

        // 一般ユーザーのみ表示されていることを確認
        foreach ($this->users as $user) {
            $response->assertSee($user->name, false);
            $response->assertSee($user->email, false);
        }
    }

    /** @test */
    // ユーザーの勤怠情報が正しく表示される
    public function admin_can_view_user_attendance_list()
    {
        $user = $this->users->first();

        // ステータスを「退勤済」に更新
        $attendance = $user->attendances->first();
        $attendance->update(['status' => Attendance::STATUS_DONE]);

        // 管理者としてログイン
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.attendance.staff', ['id' => $user->id]));

        $response->assertStatus(200);

        // 各種フォーマットされた値が正しく表示されていることを確認
        $response->assertSee($attendance->formatted_date, false); // 日付
        $response->assertSee($attendance->formatted_start_time, false); // 出勤時間
        $response->assertSee($attendance->formatted_end_time, false); // 退勤時間
        $response->assertSee($attendance->total_break_time, false); // 休憩時間
        $response->assertSee($attendance->total_hours, false); // 労働時間
    }

    /** @test */
    // 「前月」を押下した時に表示月の前月の情報が表示される
    public function admin_can_navigate_to_previous_month()
    {
        $user = $this->users->first();
        $previousMonth = now()->subMonth()->format('Y-m');
        $previousMonthDate = now()->subMonth()->format('Y-m-d');

        // 前月の勤怠データを作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $previousMonthDate,
            'status' => Attendance::STATUS_DONE,
            'start_time' => now()->subMonth()->setTime(9, 0)->format('Y-m-d H:i:s'),
            'end_time' => now()->subMonth()->setTime(18, 0)->format('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.attendance.staff', [
                'id' => $user->id,
                'month' => $previousMonth
            ]));

        $response->assertStatus(200);

        // ビューに年月が表示されているか
        $response->assertSee(now()->subMonth()->format('Y/m'));

        // 勤怠データの「日付の一部」が表示されているかを確認
        $response->assertSee(Carbon::parse($previousMonthDate)->format('m/d'), false);

        // 必要に応じて開始時間の表示も確認
        $response->assertSee('09:00', false);
    }

    /** @test */
    public function admin_can_navigate_to_next_month()
    {
        $user = $this->users->first();
        $nextMonth = now()->addMonth()->format('Y-m');
        $nextMonthDate = now()->addMonth()->format('Y-m-d');

        // 翌月の勤怠データを作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $nextMonthDate,
            'status' => Attendance::STATUS_DONE,
            'start_time' => now()->addMonth()->setTime(9, 0)->format('Y-m-d H:i:s'),
            'end_time' => now()->addMonth()->setTime(18, 0)->format('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.attendance.staff', [
                'id' => $user->id,
                'month' => $nextMonth,
            ]));

        $response->assertStatus(200);

        // ビューでは「YYYY/MM」形式で翌月が表示されていることを確認
        $response->assertSee(now()->addMonth()->format('Y/m'));

        // 勤怠データの「日付の一部」が表示されているかを確認
        $response->assertSee(\Carbon\Carbon::parse($nextMonthDate)->format('m/d'), false);

        // 勤怠開始時間（09:00）が表示されているかを確認
        $response->assertSee('09:00', false);
    }

    /** @test */
    // 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function admin_can_navigate_to_attendance_detail()
    {
        $user = $this->users->first();
        $attendance = $user->attendances->first();

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('attendance.show', ['id' => $attendance->id]));

        $response->assertStatus(200);

        // 勤怠詳細ページの内容確認
        $response->assertSee($user->name);
        // 日付部分は分割してチェック
        $response->assertSee(date('Y年', strtotime($attendance->date)));
        $response->assertSee(date('n月j日', strtotime($attendance->date)));

        // 出勤時間・退勤時間は今まで通り
        $response->assertSee($attendance->formatted_start_time);
        $response->assertSee($attendance->formatted_end_time);
    }
}