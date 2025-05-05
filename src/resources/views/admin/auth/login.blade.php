@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/auth/login.css')}}">
@endsection

@section('content')
<div class="admin-login-form">
    {{-- ページタイトル --}}
    <h2 class="admin-login-form__heading content__heading">管理者ログイン</h2>

    {{-- ログインフォーム --}}
    <div class="admin-login-form__inner">
        <form class="admin-login-form__form" action="{{ route('admin.login') }}" method="POST" novalidate>
            @csrf

            {{-- メールアドレス入力 --}}
            <div class="admin-login-form__group">
                <label class="admin-login-form__label" for="email">メールアドレス</label>
                <input class="admin-login-form__input" type="email" name="email" id="email" value="{{ old('email') }}">
                <p class="error-message">
                    @error('email')
                        {{ $message }}
                    @enderror
                </p>
            </div>

            {{-- パスワード入力 --}}
            <div class="admin-login-form__group">
                <label class="admin-login-form__label" for="password">パスワード</label>
                <input class="admin-login-form__input" type="password" name="password" id="password">
                <p class="error-message">
                    @error('password')
                        {{ $message }}
                    @enderror
                </p>
            </div>

            <div class="admin-login-form__actions">
                {{-- ログインボタン --}}
                <input class="admin-login-form__btn" type="submit" value="管理者ログインする">
            </div>
        </form>
    </div>
</div>
@endsection
