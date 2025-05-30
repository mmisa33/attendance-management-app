<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    // メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function email_is_required()
    {
        User::factory()->create();

        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /** @test */
    // パスワードが未入力の場合、バリデーションメッセージが表示される
    public function password_is_required()
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /** @test */
    // 登録内容と一致しない場合、バリデーションメッセージが表示される
    public function invalid_credentials_show_error_message()
    {
        User::factory()->create([
            'password' => bcrypt('correct_password'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }
}