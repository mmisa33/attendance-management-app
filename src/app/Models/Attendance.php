<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'date', 'status', 'start_time', 'end_time', 'note', 'is_modified', 'is_approved'];

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }
}
