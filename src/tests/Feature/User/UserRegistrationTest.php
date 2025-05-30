<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    // 名前が未入力の場合、バリデーションメッセージが表示される
    public function username_is_required()
    {
        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    /** @test */
    // メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function email_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /** @test */
    // パスワードが8文字未満の場合、バリデーションメッセージが表示される
    public function password_must_be_at_least_8_characters()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    /** @test */
    // パスワードが一致しない場合、バリデーションメッセージが表示される
    public function password_confirmation_must_match()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードと一致しません']);
    }

    /** @test */
    // パスワードが未入力の場合、バリデーションメッセージが表示される
    public function password_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /** @test */
    // フォームに内容が入力されていた場合、データが正常に保存される
    public function user_can_register_with_valid_input()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // リダイレクト先を確認
        $response->assertRedirect('/attendance');

        // データベースにユーザーが登録されているか確認
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }
}