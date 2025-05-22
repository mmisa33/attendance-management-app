<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();
        $today = Carbon::today();

        foreach ($users as $user) {
            // 4月分の勤怠
            foreach (range(1, $today->day - 1) as $day) {
                $date = Carbon::create(2025, 4, $day);
                if ($date->isFuture()) break;

                $this->createAttendanceForDate($user, $date);
            }

            // 5月1日〜20日分の勤怠
            foreach (range(1, 20) as $day) {
                $date = Carbon::create(2025, 5, $day);
                if ($date->isFuture()) break;

                $this->createAttendanceForDate($user, $date);
            }
        }
    }

    private function createAttendanceForDate($user, Carbon $date)
    {
        $isToday = $date->isToday();

        $start_time = $date->copy()->setHour(rand(8, 10))->setMinute(rand(0, 59));
        $end_time = $start_time->copy()->addHours(rand(7, 9));

        $status = $isToday ? '勤務外' : '退勤済';

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date->toDateString(),
            'status' => $status,
            'start_time' => $status === '退勤済' ? $start_time : null,
            'end_time' => $status === '退勤済' ? $end_time : null,
            'is_modified' => false,
            'is_approved' => false,
        ]);

        // 休憩をランダムで1～3回設定
        if ($status === '退勤済') {
            $breakCount = rand(1, 3);
            $currentBreakStart = $start_time->copy()->addHours(1); // 出勤後1時間からスタート

            for ($i = 0; $i < $breakCount; $i++) {
                // 休憩開始は currentBreakStart + 0〜30分のランダム遅延
                $break_start = $currentBreakStart->copy()->addMinutes(rand(0, 30));

                // 休憩時間は30〜60分ランダム
                $break_end = $break_start->copy()->addMinutes(rand(30, 60));

                // 休憩終了が退勤時間を超えないように調整
                if ($break_end > $end_time) {
                    $break_end = $end_time->copy()->subMinutes(rand(5, 15));
                }

                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $break_start,
                    'break_end' => $break_end,
                ]);

                // 次の休憩開始はこの休憩終了から最低30分後にセット
                $currentBreakStart = $break_end->copy()->addMinutes(30);

                // 次の休憩開始が退勤時間を超えたらループを終了
                if ($currentBreakStart >= $end_time) {
                    break;
                }
            }
        }
    }
}