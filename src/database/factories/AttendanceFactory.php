<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        $startTime = $this->faker->optional()->dateTimeBetween('-1 week', 'now');
        $endTime = $startTime ? $this->faker->optional()->dateTimeBetween($startTime, '+1 week') : null;

        return [
            'user_id' => User::factory(),
            'date' => $startTime ? Carbon::parse($startTime)->format('Y-m-d') : $this->faker->date(),
            'status' => $this->faker->randomElement([
                Attendance::STATUS_OFF,
                Attendance::STATUS_WORKING,
                Attendance::STATUS_BREAK,
                Attendance::STATUS_DONE
            ]),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'note' => $this->faker->optional()->sentence(),
            'is_modified' => $this->faker->boolean(10),
            'is_approved' => $this->faker->boolean(80),
            'request_date' => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}