<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    // 会員登録後、認証メールが送信される
    public function verification_email_is_sent_after_registration()
    {
        Notification::fake();

        // 会員登録処理
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 登録成功時のリダイレクト
        $response->assertRedirect('/attendance');

        // 登録されたユーザーを取得
        $user = \App\Models\User::where('email', 'testuser@example.com')->first();

        // ユーザーが作成されていることを確認
        $this->assertNotNull($user);

        // VerifyEmail通知が送信されていることを検証
        Notification::assertSentTo($user, \Illuminate\Auth\Notifications\VerifyEmail::class);
    }

    /** @test */
    // メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
    public function clicking_verification_link_redirects_to_mail_site()
    {
        $user = User::factory()->unverified()->create();

        // 未認証のログインユーザーとしてアクセス
        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertStatus(200);
        $response->assertSee('認証はこちらから');
        $response->assertSee('http://localhost:8025'); // メール認証サイトへのリンク確認
    }

    /** @test */
    // メール認証サイトのメール認証を完了すると、勤怠画面に遷移する
    public function user_is_redirected_to_attendance_after_verification()
    {
        $user = User::factory()->unverified()->create();

        // メール認証リンクを生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 認証リンクへアクセス
        $response = $this->actingAs($user)->get($verificationUrl);

        // `/attendance` にリダイレクトされる（verified=1 はクエリパラメータ）
        $this->assertStringStartsWith(
            url('/attendance'),
            $response->headers->get('Location')
        );

        // ユーザーが認証済になっていることを確認
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}