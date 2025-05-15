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
        // 3名のユーザーを作成
        $users = [];
        for ($i = 1; $i <= 3; $i++) {
            $users[] = User::create([
                'name' => 'テストユーザー' . $i,
                'email' => 'user' . $i . '@example.com',
                'password' => Hash::make('password123'),
                'is_admin' => false,
                'email_verified_at' => Carbon::now(),
            ]);
        }

        // 5月の1日～10日分の出勤データを生成
        foreach ($users as $user) {
            foreach (range(1, 10) as $day) {
                $start_time = Carbon::create(2025, 5, $day, rand(8, 10), rand(0, 59), 0);
                $end_time = $start_time->copy()->addHours(rand(7, 9));

                // **今日のデータは「勤務外」、過去の日付は「退勤済」**
                $status = ($start_time->isToday()) ? '勤務外' : '退勤済';

                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'date' => $start_time->toDateString(),
                    'status' => $status,
                    'start_time' => $status === '退勤済' ? $start_time : null,
                    'end_time' => $status === '退勤済' ? $end_time : null,
                    'is_modified' => false,
                    'is_approved' => false,
                ]);

                if ($status === '退勤済') {
                    $existingBreaks = [];

                    // 複数の休憩時間を追加（重複しないように調整）
                    foreach (range(1, rand(1, 3)) as $j) {
                        $maxAttempts = 10; // 無限ループ防止
                        $attempts = 0;

                        do {
                            $attempts++;
                            $break_start = $start_time->copy()->addHours(rand(1, 3))->addMinutes(rand(0, 30));
                            $break_end = $break_start->copy()->addMinutes(rand(30, 60));

                            if ($break_end > $end_time) {
                                $break_end = $end_time->copy()->subMinutes(rand(5, 15));
                            }

                            $overlap = false;
                            foreach ($existingBreaks as $existing) {
                                if (
                                    ($break_start < $existing['end'] && $break_end > $existing['start'])
                                ) {
                                    $overlap = true;
                                    break;
                                }
                            }
                        } while ($overlap && $attempts < $maxAttempts);

                        if ($attempts >= $maxAttempts) {
                            continue;
                        }

                        $existingBreaks[] = [
                            'start' => $break_start,
                            'end' => $break_end,
                        ];

                        BreakTime::create([
                            'attendance_id' => $attendance->id,
                            'break_start' => $break_start,
                            'break_end' => $break_end,
                        ]);
                    }
                }
            }
        }
    }
}