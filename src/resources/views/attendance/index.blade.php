@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('content')
<div class="attendance">

    {{-- 勤務ステータス --}}
    <div class="attendance__status">
        <p>{{ $attendance ? $attendance->status : '勤務外' }}</p>
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

        @if(is_null($attendance) || $attendance->status === $attendanceStatuses['off'])
        {{-- 出勤ボタン --}}
            <form class="attendance__form" method="POST" action="{{ route('attendance.startWork') }}">
                @csrf
                <button class="attendance__btn" type="submit">出勤</button>
            </form>

        @elseif($attendance->status === $attendanceStatuses['working'])
        {{-- 退勤ボタン --}}
            <form class="attendance__form" method="POST" action="{{ route('attendance.endWork') }}">
                @csrf
                <button class="attendance__btn" type="submit">退勤</button>
            </form>

            {{-- 休憩開始ボタン --}}
            <form class="attendance__form attendance__form--break" method="POST" action="{{ route('attendance.startBreak') }}">
                @csrf
                <button class="attendance__btn attendance__btn--break" type="submit">休憩入</button>
            </form>

        @elseif($attendance->status === $attendanceStatuses['break'])
            {{-- 休憩終了ボタン --}}
            <form class="attendance__form attendance__form--break" method="POST" action="{{ route('attendance.endBreak') }}">
                @csrf
                <button class="attendance__btn attendance__btn--break" type="submit">休憩戻</button>
            </form>

            @elseif($attendance->status === $attendanceStatuses['done'])
                <p class="attendance__message">お疲れ様でした。</p>
            @endif
    </div>
</div>

<script>
    // 時間を分単位で自動更新
    function updateTime() {
        const timeElement = document.getElementById('current-time');
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        timeElement.textContent = `${hours}:${minutes}`;
    }

    // 1分ごとに時間を更新
    const UPDATE_INTERVAL_MS = 60 * 1000; // 60秒 = 1分
    setInterval(updateTime, UPDATE_INTERVAL_MS);
</script>
@endsection