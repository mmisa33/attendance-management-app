<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailUpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    // 出勤時間が退勤時間より後の場合、エラーメッセージが表示される
    public function error_message_is_displayed_when_start_time_is_after_end_time()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $data = [
            'start_time' => '19:00',
            'end_time' => '18:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => '備考テスト',
        ];

        $response = $this->actingAs($user)
            ->post(route('attendance.update', $attendance->id), $data);

        $response->assertSessionHasErrors(['start_time']);
        $response->assertSessionHasErrors([
            'start_time' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    // 休憩開始時間が退勤時間より後の場合、エラーメッセージが表示される
    public function error_message_is_displayed_when_break_start_is_after_end_time()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $data = [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start' => ['19:00'],  // 勤務時間外の休憩開始時間
            'break_end' => ['20:00'],
            'note' => '備考テスト',
        ];

        $response = $this->actingAs($user)
            ->post(route('attendance.update', $attendance->id), $data);

        $response->assertSessionHasErrors(['break_start.0']);
        $response->assertSessionHasErrors([
            'break_start.0' => '休憩時間が勤務時間外です',
        ]);
    }

    /** @test */
    // 休憩終了時間が退勤時間より後の場合、エラーメッセージが表示される
    public function error_message_is_displayed_when_break_end_is_after_end_time()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $data = [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start' => ['12:00'],
            'break_end' => ['19:00'], // 退勤後の休憩終了時間
            'note' => '備考テスト',
        ];

        $response = $this->actingAs($user)
            ->post(route('attendance.update', $attendance->id), $data);

        $response->assertSessionHasErrors(['break_start.0']);
        $response->assertSessionHasErrors([
            'break_start.0' => '休憩時間が勤務時間外です',
        ]);
    }

    /** @test */
    // 備考欄が未入力の場合、エラーメッセージが表示される
    public function error_message_is_displayed_when_note_is_empty()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $data = [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => '',
        ];

        $response = $this->actingAs($user)
            ->post(route('attendance.update', $attendance->id), $data);

        $response->assertSessionHasErrors(['note']);
        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }

    /** @test */
    // 修正申請処理が実行され、管理者側に表示される
    public function modification_request_is_executed_and_displayed_for_admin()
    {
        $user = User::factory()->create();
        $admin = Admin::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'is_modified' => false,
            'is_approved' => false,
        ]);

        $data = [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => '修正申請テスト',
        ];

        // 一般ユーザーで申請
        $response = $this->actingAs($user)
            ->post(route('attendance.update', $attendance->id), $data);

        $response->assertRedirect();
        $attendance->refresh();

        // 修正フラグが立っていることを確認
        $this->assertEquals(1, $attendance->is_modified);
        $this->assertEquals(0, $attendance->is_approved);

        // 管理者としてログインし、承認画面を確認
        $response = $this->actingAs($admin, 'admin')
            ->get(route("admin.stamp_correction_request.show", ['attendance_correction_request' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('修正申請テスト');

        // 管理者としてログインし、申請一覧画面を確認
        $this->actingAs($admin, 'admin');
        $this->assertAuthenticatedAs($admin, 'admin');

        $response = $this->get(route("stamp_correction_request.list"));

        $response->assertStatus(200);
        $response->assertSee('修正申請テスト');
    }

    /** @test */
    // 承認待ちの修正申請が申請一覧に表示される
    public function pending_modification_requests_are_displayed_in_the_list()
    {
        $user = User::factory()->create();

        // 勤怠情報を3件作成し修正申請を行う
        $attendances = Attendance::factory()->count(3)->create([
            'user_id' => $user->id,
            'is_modified' => false,
            'is_approved' => false,
            'note' => '承認待ち申請',
        ]);

        $data = [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => '承認待ち申請',
        ];

        foreach ($attendances as $attendance) {
            $this->actingAs($user)
                ->post(route('attendance.update', $attendance->id), $data);
        }

        // 申請一覧画面にアクセス
        $response = $this->actingAs($user)
            ->get(route('stamp_correction_request.list'));

        $response->assertStatus(200);

        // 承認待ちタブが存在することを確認
        $response->assertSee('id="pending"', false);

        // 承認待ちステータスの文字列が順に3回表示されていることを確認
        $response->assertSeeInOrder(array_fill(0, 3, '<td class="request-list__content">承認待ち</td>'), false);

        // 申請内容がそれぞれ表示されていることを確認
        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->note);
            $response->assertSee($attendance->formatted_full_date);
        }
    }

    /** @test */
    // 承認済みの修正申請が申請一覧に表示される
    public function approved_modification_requests_are_displayed_in_the_list()
    {
        $user = User::factory()->create();
        $admin = Admin::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'is_modified' => true,
            'is_approved' => false,
            'note' => '承認テスト',
        ]);

        // 管理者で承認処理を実行
        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.stamp_correction_request.approve', ['attendance_correction_request' => $attendance->id]));

        $response->assertRedirect(route('stamp_correction_request.list'));

        $attendance = $attendance->fresh();

        $this->assertEquals(1, $attendance->is_approved, 'is_approved should be 1 after approval');

        // ユーザーとして申請一覧ページにアクセス
        $response = $this->actingAs($user)
            ->get(route('stamp_correction_request.list'));

        $response->assertStatus(200);

        // 承認済みタブが存在することを確認
        $response->assertSee('id="approved"', false);

        // 承認済みステータスの文字列が表示されていることを確認
        $response->assertSee('<td class="request-list__content">承認済み</td>', false);

        // 申請内容が表示されていることを確認
        $response->assertSee('承認テスト');
    }

    /** @test */
    // 修正申請詳細画面にアクセスできる
    public function user_can_access_modification_request_detail_page()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'is_modified' => false,
            'is_approved' => false,
            'note' => '申請詳細確認用',
        ]);

        // 勤怠詳細を修正し保存処理
        $data = [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => '申請詳細確認用',
        ];

        $this->actingAs($user)
            ->post(route('attendance.update', $attendance->id), $data);

        // 申請一覧画面を開く
        $response = $this->actingAs($user)
            ->get(route('stamp_correction_request.list'));

        $response->assertStatus(200);

        // 「詳細」ボタンのリンクを取得
        preg_match('/href="([^"]+)"[^>]*>詳細<\/a>/', $response->getContent(), $matches);
        $this->assertNotEmpty($matches);

        $detailUrl = $matches[1];

        // 「詳細」リンクにアクセスし申請詳細画面に遷移できることを確認
        $detailResponse = $this->actingAs($user)->get($detailUrl);

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('申請詳細確認用');
    }
}