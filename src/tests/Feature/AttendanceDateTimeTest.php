<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceDateTimeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    // 現在の日時情報が画面に正しく表示されている
    public function current_datetime_is_displayed_correctly_on_view()
    {
        // テスト用のユーザーを作成しログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // 現在日時を固定（例: 2025-05-15 16:31）
        Carbon::setTestNow(Carbon::create(2025, 5, 15, 16, 31));

        // 勤怠画面にアクセス
        $response = $this->get(route('attendance.index'));

        // ビューに渡される日時の期待値を作成
        $expectedDate = Carbon::now()->isoFormat('YYYY年M月D日(ddd)');
        $expectedTime = Carbon::now()->format('H:i');

        // レスポンスの検証
        $response->assertStatus(200);
        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);

        // テスト後は日時の固定を解除
        Carbon::setTestNow();
    }
}