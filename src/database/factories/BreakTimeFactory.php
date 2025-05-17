<?php

namespace Database\Factories;

use App\Models\BreakTime;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakTimeFactory extends Factory
{
    // 対象モデル
    protected $model = BreakTime::class;

    public function definition()
    {
        return [
            'attendance_id' => null,  // テストで明示的に指定するためnullにする
            'break_start' => $this->faker->dateTimeBetween('09:00:00', '11:59:59'),
            'break_end' => $this->faker->dateTimeBetween('12:00:00', '13:00:00'),
        ];
    }
}
