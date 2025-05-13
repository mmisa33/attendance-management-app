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
                'user_id' => 1,
                'date' => $start_time->toDateString(),
                'status' => '退勤済',
                'start_time' => $start_time,
                'end_time' => $end_time,
                'is_modified' => false,
                'is_approved' => false,
            ]);

            // 複数の休憩時間を追加
            foreach (range(1, rand(1, 3)) as $j) {
                $break_start = $start_time->copy()->addHours(rand(1, 3))->addMinutes(rand(0, 30));
                $break_end = $break_start->copy()->addMinutes(rand(30, 60)); // 休憩後30～60分で終了

                // 休憩終了が勤務終了を超えていたら調整
                if ($break_end > $end_time) {
                    $break_end = $end_time->copy()->subMinutes(rand(5, 15));
                }

                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $break_start,
                    'break_end' => $break_end,
                ]);
            }
        }
    }
}
