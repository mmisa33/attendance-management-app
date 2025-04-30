<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'テストユーザー',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);

        // 2025年4月の10日分の出勤データを作成
        foreach (range(1, 10) as $i) {
            // ランダムな出勤時刻と退勤時刻を生成
            $start_time = Carbon::create(2025, 4, $i, rand(8, 10), rand(0, 59), 0);
            $end_time = $start_time->copy()->addHours(rand(7, 9));  // 出勤から7～9時間後の退勤時刻

            // 勤怠データを挿入
            $attendance = Attendance::create([
                'user_id' => 1, // 仮のユーザーID
                'date' => Carbon::create(2025, 4, $i)->toDateString(),
                'status' => '勤務中',
                'start_time' => $start_time,
                'end_time' => $end_time,
                'is_modified' => false,
                'is_approved' => false,
            ]);

            // 各出勤にランダムな休憩時間を設定
            $break_start = $start_time->copy()->addHours(rand(1, 3)); // 出勤後1～3時間後に休憩開始
            $break_end = $break_start->copy()->addMinutes(rand(30, 60)); // 休憩後30～60分で終了

            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => $break_start,
                'break_end' => $break_end,
            ]);
        }
    }
}
