<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $attendances;

    // テスト前準備処理
    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザーの作成
        $this->user = User::factory()->create();

        // 今日の日付を基準に勤怠データを生成
        Carbon::setTestNow(Carbon::parse('2025-05-16'));
        $this->attendances = Attendance::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'date' => Carbon::now()->startOfMonth(),
        ]);

        // ログイン処理
        $this->actingAs($this->user);
    }

    /** @test */
    // 勤怠一覧ページに自分の勤怠情報がすべて表示される()
    public function attendance_list_displays_all_my_attendances()
    {
        $response = $this->get(route('attendance.list'));

        $response->assertStatus(200);
        foreach ($this->attendances as $attendance) {
            $response->assertSee($attendance->formatted_date);
        }
    }

    /** @test */
    //  勤怠一覧ページに現在の月が表示される
    public function attendance_list_displays_current_month()
    {
        $response = $this->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('2025/05');
    }

    /** @test */
    // 前月ボタンを押すと前月の勤怠情報が表示される
    public function attendance_list_displays_previous_month_when_prev_button_clicked()
    {
        $response = $this->get(route('attendance.list', ['month' => '2025-04']));

        $response->assertStatus(200);
        $response->assertSee('2025/04');
    }

    /** @test */
    // 翌月ボタンを押すと翌月の勤怠情報が表示される
    public function attendance_list_displays_next_month_when_next_button_clicked()
    {
        $response = $this->get(route('attendance.list', ['month' => '2025-06']));

        $response->assertStatus(200);
        $response->assertSee('2025/06');
    }

    /** @test */
    // 詳細ボタンを押すとその日の勤怠詳細画面に遷移する
    public function attendance_list_detail_button_redirects_to_attendance_detail_page()
    {
        // 勤怠一覧ページにアクセス
        $response = $this->get(route('attendance.list'));

        // 勤怠一覧が表示されるか確認
        $response->assertStatus(200);
        $response->assertSee('詳細');

        // 勤怠情報を取得して詳細ページのリンクをクリック
        $attendance = $this->attendances->first();
        $response = $this->get(route('attendance.show', ['id' => $attendance->id]));

        // 詳細ページに遷移していることを確認
        $response->assertStatus(200);

        // 日付のフォーマットが表示されているか確認
        $response->assertSeeInOrder([
            $attendance->date->format('Y年'),
            $attendance->date->format('n月j日'),
        ]);
    }
}