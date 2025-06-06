@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css')}}">
@endsection

@section('content')
<div class="register-form">
    {{-- ページタイトル --}}
    <h2 class="register-form__heading content__heading">会員登録</h2>

    {{-- ユーザー登録フォーム --}}
    <div class="register-form__inner">
        <form class="register-form__form" action="{{ route('register') }}" method="POST" novalidate>
            @csrf

            {{-- 名前入力 --}}
            <div class="register-form__group">
                <label class="register-form__label" for="name">名前</label>
                <input class="register-form__input" type="text" name="name" id="name" value="{{ old('name') }}">
                <p class="error-message">
                    @error('name')
                        {{ $message }}
                    @enderror
                </p>
            </div>

            {{-- メールアドレス入力 --}}
            <div class="register-form__group">
                <label class="register-form__label" for="email">メールアドレス</label>
                <input class="register-form__input" type="email" name="email" id="email" value="{{ old('email') }}">
                <p class="error-message">
                    @error('email')
                        {{ $message }}
                    @enderror
                </p>
            </div>

            {{-- パスワード入力 --}}
            <div class="register-form__group">
                <label class="register-form__label" for="password">パスワード</label>
                <input class="register-form__input" type="password" name="password" id="password">
                <p class="error-message">
                    @error('password')
                        @if ($message !== 'パスワードと一致しません')
                            {{ $message }}
                        @endif
                    @enderror
                </p>
            </div>

            {{-- 確認用パスワード入力 --}}
            <div class="register-form__group">
                <label class="register-form__label" for="password_confirmation">パスワード確認</label>
                <input class="register-form__input" type="password" name="password_confirmation" id="password_confirmation">
                <p class="error-message">
                    @error('password')
                        @if ($message === 'パスワードと一致しません')
                            {{ $message }}
                        @endif
                    @enderror
                </p>
            </div>

            <div class="register-form__actions">
                {{-- 登録ボタン --}}
                <input class="register-form__btn" type="submit" value="登録する">

                {{-- ログインページへ移行 --}}
                <a class="register-form__link" href="{{ route('login') }}">ログインはこちら</a>
            </div>
        </form>
    </div>
</div>
@endsection