@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/admin-login.css')}}">
@endsection

@section('content')
<div class="admin-form">
    {{-- ページタイトル --}}
    <h2 class="admin-form__heading content__heading">管理者ログイン</h2>

    {{-- ログインフォーム --}}
    <div class="admin-form__inner">
        <form class="admin-form__form" action="{{ route('admin.login') }}" method="POST" novalidate>
            @csrf

            {{-- メールアドレス入力 --}}
            <div class="admin-form__group">
                <label class="admin-form__label" for="email">メールアドレス</label>
                <input class="admin-form__input" type="email" name="email" id="email" value="{{ old('email') }}">
                <p class="error-message">
                    @error('email')
                        {{ $message }}
                    @enderror
                </p>
            </div>

            {{-- パスワード入力 --}}
            <div class="admin-form__group">
                <label class="admin-form__label" for="password">パスワード</label>
                <input class="admin-form__input" type="password" name="password" id="password">
                <p class="error-message">
                    @error('password')
                        {{ $message }}
                    @enderror
                </p>
            </div>

            <div class="admin-form__actions">
                {{-- ログインボタン --}}
                <input class="admin-form__btn" type="submit" value="管理者ログインする">
            </div>
        </form>
    </div>
</div>
@endsection
