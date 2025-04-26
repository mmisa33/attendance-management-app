@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css')}}">
@endsection

@section('link')
{{-- ヘッダーリンク --}}
<div class="header__links">
    <a class="header__link" href="">勤怠</a>
    <a class="header__link" href="">勤怠一覧</a>
    <a class="header__link" href="">申請</a>
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <input class="header__link" type="submit" value="ログアウト">
    </form>
</div>
@endsection