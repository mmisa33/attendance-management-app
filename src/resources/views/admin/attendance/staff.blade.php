@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/staff.css')}}">
@endsection

@section('content')
<div class="attendance-staff">
    <h2 class="attendance-staff__heading">{{ $staff->name }}さんの勤怠</h2>

    {{-- ナビ --}}
        <div class="attendance-staff__nav">
            <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $previousMonth]) }}" class="nav__btn">
                <img src="{{ asset('images/icon/arrow-left.png') }}" alt="前月へ戻る">前月
            </a>
            <h3 class="nav__month">
                <img src="{{ asset('images/icon/calender.png') }}" alt="カレンダー">{{ $formattedMonth }}
            </h3>
            <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $nextMonth]) }}" class="nav__btn">
                翌月<img src="{{ asset('images/icon/arrow-right.png') }}" alt="翌月へ進む">
            </a>
        </div>

        {{-- 当月の勤怠情報一覧 --}}
        <table class="attendance-staff__table">
            <thead>
                <tr class="attendance-staff__row">
                    <th class="attendance-staff__header">日付</th>
                    <th class="attendance-staff__header">出勤</th>
                    <th class="attendance-staff__header">退勤</th>
                    <th class="attendance-staff__header">休憩</th>
                    <th class="attendance-staff__header">合計</th>
                    <th class="attendance-staff__header">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $attendance)
                    <tr class="attendance-staff__row">
                        <td class="attendance-staff__content">{{ $attendance->formatted_date }}</td>
                        <td class="attendance-staff__content">{{ $attendance->formatted_start_time }}</td>
                        <td class="attendance-staff__content">{{ $attendance->formatted_end_time }}</td>
                        <td class="attendance-staff__content">{{ $attendance->total_break_time }}</td>
                        <td class="attendance-staff__content">{{ $attendance->total_hours }}</td>
                        <td class="attendance-staff__content">
                            <a href="{{ route('attendance.show', ['id' => $attendance->id]) }}" class="content__detail">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- CSV出力 --}}
        <div class="attendance-staff__csv">
            <a href="{{ route('admin.attendance.staff.csv', ['id' => $staff->id, 'month' => $currentMonth]) }}" class="csv__btn">CSV出力</a>
        </div>
    </div>
</div>
@endsection