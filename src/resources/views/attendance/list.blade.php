@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/list.css')}}">
@endsection

@section('link')
{{-- ヘッダーリンク --}}
<div class="header__links">
    <a class="header__link" href="{{ route('attendance.index') }}">勤怠</a>
    <a class="header__link" href="{{ route('attendance.list') }}">勤怠一覧</a>
    <a class="header__link" href="{{ route('stamp_correction_request.list') }}">申請</a>
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <input class="header__link" type="submit" value="ログアウト">
    </form>
</div>
@endsection

@section('content')
<div class="attendance-list">
    {{-- ページタイトル --}}
    <h2 class="attendance-list__heading">勤怠一覧</h2>

    {{-- ナビ --}}
    <div class="attendance-list__nav">
        <a href="{{ route('attendance.list', ['month' => $previousMonth]) }}" class="nav__button"><img src="{{ asset('images/icon/arrow-left.png') }}" alt="前月">前月</a>
        <h3 class="nav__month"><img src="{{ asset('images/icon/calender.png') }}" alt="カレンダー">{{ $formattedMonth }}</h3>
        <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" class="nav__button">翌月<img src="{{ asset('images/icon/arrow-right.png') }}" alt="翌月"></a>
    </div>

    {{-- 当月の勤怠情報一覧 --}}
    <table class="attendance-list__table">
        <thead>
            <tr class="attendance-list__row">
                <th class="attendance-list__header">日付</th>
                <th class="attendance-list__header">出勤</th>
                <th class="attendance-list__header">退勤</th>
                <th class="attendance-list__header">休憩</th>
                <th class="attendance-list__header">合計</th>
                <th class="attendance-list__header">詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)
                <tr class="attendance-list__row">
                    <td class="attendance-list__content">{{ $attendance->formatted_date }}</td>
                    <td class="attendance-list__content">{{ $attendance->start_time_formatted }}</td>
                    <td class="attendance-list__content">{{ $attendance->end_time_formatted }}</td>
                    <td class="attendance-list__content">{{ $attendance->total_break_time }}</td>
                    <td class="attendance-list__content">{{ $attendance->total_hours }}</td>
                    <td class="attendance-list__content">
                        <a href="{{ route('attendance.details', ['id' => $attendance->id]) }}" class="content__details">詳細</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection