@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/list.css')}}">
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
<div class="attendance-list">
    {{-- ページタイトル --}}
    <h2 class="attendance-list__heading">勤怠一覧</h2>

    {{-- ナビ --}}
    <div class="attendance-list__nav">
        <a href="{{ route('attendance.list', ['month' => $previousMonth]) }}" class="button">前月</a>
        <h3>{{ $formattedMonth }}</h3>
        <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" class="button">翌月</a>
    </div>

    {{-- 当月の勤怠情報一覧 --}}
    <table class="attendance-table">
        @foreach ($attendances as $attendance)
            <tr class="attendance-table__row">
                <th class="attendance-table__header">日付</th>
                <td class="attendance-table__content">
                    {{ $attendance->formatted_date }}
                </td>
            </tr>

            <tr class="attendance-table__row">
                <th class="attendance-table__header">出勤</th>
                <td class="attendance-table__content">
                    {{ $attendance->start_time_formatted }}
                </td>
            </tr>

            <tr class="attendance-table__row">
                <th class="attendance-table__header">退勤</th>
                <td class="attendance-table__content">
                    {{ $attendance->end_time_formatted }}
                </td>
            </tr>

            <tr class="attendance-table__row">
                <th class="attendance-table__header">休憩</th>
                <td class="attendance-table__content">
                    {{ $attendance->total_break_time }}
                </td>
            </tr>

            <tr class="attendance-table__row">
                <th class="attendance-table__header">合計</th>
                <td class="attendance-table__content">
                    {{ $attendance->total_hours }}
                </td>
            </tr>

            <tr class="attendance-table__row">
                <th class="attendance-table__header">詳細</th>
                <td class="attendance-table__content">
                    <a href="{{ route('attendance.details', ['attendance' => $attendance->id]) }}" class="button">詳細</a>
                </td>
            </tr>
        @endforeach
    </table>
</div>
@endsection