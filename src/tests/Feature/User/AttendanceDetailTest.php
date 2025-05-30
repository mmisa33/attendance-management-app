<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $attendance;

    // テスト前準備処理
    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザー作成
        $this->user = User::factory()->create([
            'name' => 'テストユーザー',
        ]);

        // 勤怠データ作成
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => '2025-05-16',
            'start_time' => '2025-05-16 09:00:00',
            'end_time' => '2025-05-16 18:00:00',
            'note' => 'テスト備考',
            'status' => Attendance::STATUS_DONE,
            'is_modified' => false,
            'is_approved' => false,
        ]);

        // 休憩データ作成
        BreakTime::factory()->create([
            'attendance_id' => $this->attendance->id,
            'break_start' => '2025-05-16 12:00:00',
            'break_end' => '2025-05-16 13:00:00',
        ]);
    }

    /** @test */
    // 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
    public function name_is_login_user_name()
    {
        $response = $this->actingAs($this->user)
            ->get(route('attendance.show', ['id' => $this->attendance->id]));

        $response->assertStatus(200);
        $response->assertSeeText($this->user->name);
    }

    /** @test */
    // 勤怠詳細画面の「日付」が選択した日付になっている
    public function date_is_selected_date()
    {
        $response = $this->actingAs($this->user)
            ->get(route('attendance.show', ['id' => $this->attendance->id]));

        $response->assertStatus(200);

        $expectedDate = $this->attendance->formatted_monthday;
        $response->assertSeeText($expectedDate);
    }

    /** @test */
    // 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
    public function start_and_end_time_match_user_stamp()
    {
        $response = $this->actingAs($this->user)
            ->get(route('attendance.show', ['id' => $this->attendance->id]));

        $response->assertStatus(200);

        $startTime = $this->attendance->formatted_start_time;
        $endTime = $this->attendance->formatted_end_time;

        $response->assertSee('value="' . $startTime . '"', false);
        $response->assertSee('value="' . $endTime . '"', false);
    }

    /** @test */
    // 「休憩」にて記されている時間がログインユーザーの打刻と一致している
    public function break_time_match_user_stamp()
    {
        $response = $this->actingAs($this->user)
            ->get(route('attendance.show', ['id' => $this->attendance->id]));

        $response->assertStatus(200);

        $breakRow = $this->attendance->breakTimes->first();

        $start = Carbon::parse($breakRow->break_start)->format('H:i');
        $end = Carbon::parse($breakRow->break_end)->format('H:i');

        $response->assertSee('value="' . $start . '"', false);
        $response->assertSee('value="' . $end . '"', false);
    }
}