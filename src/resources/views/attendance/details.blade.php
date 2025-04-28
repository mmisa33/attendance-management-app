@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/details.css')}}">
@endsection

@section('link')
{{-- ヘッダーリンク --}}
<div class="header__links">
    <a class="header__link" href="{{ route('attendance.index') }}">勤怠</a>
    <a class="header__link" href="{{ route('attendance.list') }}">勤怠一覧</a>
    <a class="header__link" href="">申請</a>
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <input class="header__link" type="submit" value="ログアウト">
    </form>
</div>
@endsection

@section('content')
<div class="attendance-detail">
    <h2 class="attendance-detail__heading">勤怠詳細</h2>

    <form class="attendance-detail__form" method="POST" action="{{ route('attendance.updateDetail', ['attendance' => $attendance->id]) }}">
        @csrf

        <table class="attendance-detail__table">
            {{-- 名前 --}}
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header">名前</th>
                <td class="attendance-detail__content attendance-detail__content--name">{{ Auth::user()->name }}</td>
            </tr>

            {{-- 日付 --}}
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header">日付</th>
                <td class="attendance-detail__content">
                    <span class="content__year">{{ $attendance->formatted_year }}</span>
                    <span class="content__monthday">{{ $attendance->formatted_monthday }}</span>
                </td>
            </tr>

            {{-- 出勤・退勤 --}}
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header">出勤・退勤</th>
                <td class="attendance-detail__content attendance-detail__content--time">
                    <input type="text" name="start_time" id="start_time" value="{{ old('start_time', substr($attendance->start_time, 11, 5)) }}" pattern="\d{2}:\d{2}" placeholder="hh:mm" class="content__time">
                    <span class="content__time-separator">～</span>
                    <input type="text" name="end_time" id="end_time" value="{{ old('end_time', substr($attendance->end_time, 11, 5)) }}" pattern="\d{2}:\d{2}" placeholder="hh:mm" class="content__time">
                    @error('start_time')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                    @error('end_time')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </td>
            </tr>

        {{-- 休憩レコード --}}
        <tr class="attendance-detail__row">
            <th class="attendance-detail__header">休憩</th>
            <td class="attendance-detail__content attendance-detail__content--time">
                @foreach($breakTimes as $i => $break)
                    <div class="break-row">
                        <input type="text" name="break_start[{{ $i }}]" value="{{ old('break_start.' . $i, substr($break->break_start, 11, 5)) }}" pattern="\d{2}:\d{2}" placeholder="hh:mm" class="content__time">
                        <span class="content__time-separator">～</span>
                        <input type="text" name="break_end[{{ $i }}]" value="{{ old('break_end.' . $i, substr($break->break_end, 11, 5)) }}" pattern="\d{2}:\d{2}" placeholder="hh:mm" class="content__time">
                    </div>
                @endforeach
            </td>
        </tr>

            {{-- 備考 --}}
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header" for="note">備考</th>
                <td class="attendance-detail__content attendance-detail__content--textarea">
                    <textarea class="content__textarea" name="note" id="note" rows="2">{{ old('note', $attendance->note) }}</textarea>
                    @error('note')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
        </table>

        {{-- 修正申請ボタン --}}
        <button type="submit" class="attendance-detail__button">修正</button>
    </form>
</div>
@endsection