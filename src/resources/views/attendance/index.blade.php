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

@section('content')
<div class="attendance">

    {{-- 勤務ステータス --}}
    <div class="attendance__status">
        <p>{{ $attendance->status }}</p>
    </div>

    {{-- 現在の年月日 --}}
    <div class="attendance__date">
        <p>{{ $now->isoFormat('YYYY年M月D日(ddd)') }}</p>
    </div>

    {{-- 現在の時刻 --}}
    <div class="attendance__time">
        <p id="current-time">{{ $now->format('H:i') }}</p>
    </div>

    {{-- 出勤・退勤・休憩ボタン --}}
    <div class="attendance__actions">

        {{-- 出勤ボタン --}}
        @if($attendance->status === '勤務外')
            <form class="attendance__form" method="POST" action="{{ route('attendance.clockIn') }}">
                @csrf
                <button class="attendance__button" type="submit">出勤</button>
            </form>

        {{-- 退勤ボタン --}}
        @elseif($attendance->status === '出勤中')
            <form class="attendance__form" method="POST" action="{{ route('attendance.clockOut') }}">
                @csrf
                <button class="attendance__button" type="submit">退勤</button>
            </form>

            {{-- 休憩開始ボタン --}}
            <form class="attendance__form attendance__form--break" method="POST" action="{{ route('attendance.breakStart') }}">
                @csrf
                <button class="attendance__button attendance__button--break" type="submit">休憩入</button>
            </form>

        {{-- 休憩終了ボタン --}}
        @elseif($attendance->status === '休憩中')
            <form class="attendance__form attendance__form--break" method="POST" action="{{ route('attendance.breakEnd') }}">
                @csrf
                <button class="attendance__button attendance__button--break" type="submit">休憩戻</button>
            </form>
        @elseif($attendance->status === '退勤済')
            <p class="attendance__message">お疲れ様でした。</p>
        @endif

    </div>
</div>

<script>
    function updateTime() {
        const timeElement = document.getElementById('current-time');
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        timeElement.textContent = `${hours}:${minutes}`;
    }

    setInterval(updateTime, 1000); // 1秒ごとに時刻を更新
</script>
@endsection