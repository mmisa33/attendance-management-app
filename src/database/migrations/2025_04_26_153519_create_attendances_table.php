<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // 外部キー
            $table->date('date'); // 日付
            $table->string('status')->default('勤務外'); // 勤務ステータス
            $table->timestamp('start_time')->nullable(); // 出勤時刻
            $table->timestamp('end_time')->nullable(); // 退勤時刻
            $table->boolean('is_modified')->default(false); // 修正申請フラグ
            $table->boolean('is_approved')->default(false);  // 承認フラグ
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}