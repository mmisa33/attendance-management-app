<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Attendance;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $attendance;

    // テスト前準備処理
    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
        $this->user = User::factory()->create();

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => now()->setTime(9, 0, 0)->format('Y-m-d H:i:s'),
            'end_time' => now()->setTime(18, 0, 0)->format('Y-m-d H:i:s'),
            'note' => 'Test note',
            'is_modified' => false,
            'is_approved' => false,
        ]);
    }

    /** @test */
    // 詳細画面の内容が選択した情報と一致する
    public function admin_can_view_attendance_details()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('attendance.show', ['id' => $this->attendance->id]));

        // ステータスコードの確認
        $response->assertStatus(200);

        // 順番通りに表示されていることを確認
        $response->assertSeeInOrder([
            $this->attendance->user->name, // 名前
            $this->attendance->formatted_year, // 年
            $this->attendance->formatted_monthday, // 月日
            $this->attendance->formatted_start_time, // 出勤時間
            $this->attendance->formatted_end_time, // 退勤時間
            trim($this->attendance->note) // 備考
        ]);

        // 休憩時間の確認
        if ($this->attendance->breakTimes && $this->attendance->breakTimes->count() > 0) {
            $breakTimes = $this->attendance->breakTimes->sortBy('break_start');
            foreach ($breakTimes as $break) {
                $response->assertSeeInOrder([
                    $break->break_start,
                    $break->break_end
                ]);
            }
        }
    }

    /** @test */
    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function error_displayed_when_start_time_is_after_end_time()
    {
        $data = [
            'start_time' => '18:00', // 開始時間が終了時間より後 → エラー
            'end_time' => '09:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => '備考',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->from(route('attendance.show', ['id' => $this->attendance->id]))
            ->post(route('attendance.update', ['id' => $this->attendance->id]), $data);

        $response->assertRedirect(route('attendance.show', ['id' => $this->attendance->id]));
        $response->assertSessionHasErrors([
            'start_time' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    // 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function error_displayed_when_break_start_is_after_end_time()
    {
        $data = [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start' => ['19:00'],  // 休憩開始が勤務終了時間後 → エラー
            'break_end' => ['19:30'],
            'note' => '備考',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->from(route('attendance.show', ['id' => $this->attendance->id]))
            ->post(route('attendance.update', ['id' => $this->attendance->id]), $data);

        $response->assertRedirect(route('attendance.show', ['id' => $this->attendance->id]));
        $response->assertSessionHasErrors([
            'break_start.0' => '休憩時間が勤務時間外です',
        ]);
    }

    /** @test */
    // 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function error_displayed_when_break_end_is_after_end_time()
    {
        $data = [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start' => ['17:30'],
            'break_end' => ['19:00'],  // 休憩終了が勤務終了時間後 → エラー
            'note' => '備考',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->from(route('attendance.show', ['id' => $this->attendance->id]))
            ->post(route('attendance.update', ['id' => $this->attendance->id]), $data);

        $response->assertRedirect(route('attendance.show', ['id' => $this->attendance->id]));
        $response->assertSessionHasErrors([
            'break_start.0' => '休憩時間が勤務時間外です',
        ]);
    }

    /** @test */
    // 備考欄が未入力の場合のエラーメッセージが表示される
    public function error_displayed_when_note_is_empty()
    {
        $data = [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => '', // 備考空欄 → エラー
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->from(route('attendance.show', ['id' => $this->attendance->id]))
            ->post(route('attendance.update', ['id' => $this->attendance->id]), $data);

        $response->assertRedirect(route('attendance.show', ['id' => $this->attendance->id]));
        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }
}
