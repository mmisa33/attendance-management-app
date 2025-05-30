<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceStatusDisplayTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    // 勤怠ステータスが「勤務外」の場合、画面に「勤務外」と表示される
    public function status_off_is_displayed_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'status' => Attendance::STATUS_OFF,
        ]);

        $response = $this->get(route('attendance.index'));

        $response->assertStatus(200);
        $response->assertViewHas('attendance', function ($attendance) {
            return $attendance->status === Attendance::STATUS_OFF;
        });
        $response->assertSee('勤務外');
    }

    /** @test */
    // 勤怠ステータスが「出勤中」の場合、画面に「出勤中」と表示される
    public function status_working_is_displayed_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'status' => Attendance::STATUS_WORKING,
        ]);

        $response = $this->get(route('attendance.index'));

        $response->assertStatus(200);
        $response->assertViewHas('attendance', function ($attendance) {
            return $attendance->status === Attendance::STATUS_WORKING;
        });
        $response->assertSee('出勤中');
    }

    /** @test */
    // 勤怠ステータスが「休憩中」の場合、画面に「休憩中」と表示される
    public function status_break_is_displayed_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'status' => Attendance::STATUS_BREAK,
        ]);

        $response = $this->get(route('attendance.index'));

        $response->assertStatus(200);
        $response->assertViewHas('attendance', function ($attendance) {
            return $attendance->status === Attendance::STATUS_BREAK;
        });
        $response->assertSee('休憩中');
    }

    /** @test */
    // 勤怠ステータスが「退勤済」の場合、画面に「退勤済」と表示される
    public function status_done_is_displayed_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'status' => Attendance::STATUS_DONE,
        ]);

        $response = $this->get(route('attendance.index'));

        $response->assertStatus(200);
        $response->assertViewHas('attendance', function ($attendance) {
            return $attendance->status === Attendance::STATUS_DONE;
        });
        $response->assertSee('退勤済');
    }
}
