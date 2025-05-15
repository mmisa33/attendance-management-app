@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css')}}">
@endsection

@section('content')
<div class="admin-attendance-list">
    {{-- ページタイトル --}}
    <h2 class="admin-attendance-list__heading">{{ $currentDate->format('Y年n月j日の勤怠') }}</h2>

    {{-- ナビ --}}
    <div class="admin-attendance-list__nav">
        <a href="{{ route('admin.attendance.list', ['date' => $previousDate->format('Y-m-d')]) }}" class="nav__btn">
            <img src="{{ asset('images/icon/arrow-left.png') }}" alt="前日へ戻る">前日
        </a>
        <h3 class="nav__month">
            <img src="{{ asset('images/icon/calender.png') }}" alt="カレンダー">{{ $currentDate->format('Y年m月d日') }}
        </h3>
        <a href="{{ route('admin.attendance.list', ['date' => $nextDate->format('Y-m-d')]) }}" class="nav__btn">
            翌日<img src="{{ asset('images/icon/arrow-right.png') }}" alt="翌日へ進む">
        </a>
    </div>

    {{-- 勤怠情報一覧 --}}
    <table class="admin-attendance-list__table">
        <thead>
            <tr class="admin-attendance-list__row">
                <th class="admin-attendance-list__header">氏名</th>
                <th class="admin-attendance-list__header">出勤</th>
                <th class="admin-attendance-list__header">退勤</th>
                <th class="admin-attendance-list__header">休憩</th>
                <th class="admin-attendance-list__header">合計</th>
                <th class="admin-attendance-list__header">詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)
                <tr class="admin-attendance-list__row">
                    <td class="admin-attendance-list__content">{{ $attendance->user->name }}</td>
                    <td class="admin-attendance-list__content">{{ $attendance->formatted_start_time }}</td>
                    <td class="admin-attendance-list__content">{{ $attendance->formatted_end_time }}</td>
                    <td class="admin-attendance-list__content">{{ $attendance->total_break_time }}</td>
                    <td class="admin-attendance-list__content">{{ $attendance->total_hours }}</td>
                    <td class="admin-attendance-list__content">
                        <a href="{{ route('attendance.show', ['id' => $attendance->id]) }}" class="content__detail">詳細</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection