<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    // メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function email_is_required()
    {
        Admin::factory()->create();

        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /** @test */
    // パスワードが未入力の場合、バリデーションメッセージが表示される
    public function password_is_required()
    {
        $admin = Admin::factory()->create();

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /** @test */
    // 登録内容と一致しない場合、バリデーションメッセージが表示される
    public function invalid_credentials_show_error_message()
    {
        Admin::factory()->create([
            'password' => bcrypt('correct_password'),
        ]);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }
}