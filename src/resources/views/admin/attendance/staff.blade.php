@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/staff.css')}}">
@endsection

@section('link')
{{-- ヘッダーリンク --}}
<div class="header__links">
    <a class="header__link" href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
    <a class="header__link" href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
    <a class="header__link" href="{{ route('stamp_correction_request.list') }}">申請一覧</a>
    <form action="{{ route('admin.logout') }}" method="POST">
        @csrf
        <input class="header__link" type="submit" value="ログアウト">
    </form>
</div>
@endsection

@section('content')
<div class="attendance-staff">
    <h2 class="attendance-staff__heading">{{ $staff->name }}さんの勤怠</h2>

    {{-- ナビ（前月・今月・翌月） --}}
        <div class="attendance-staff__nav">
            <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $previousMonth]) }}" class="nav__button">
                <img src="{{ asset('images/icon/arrow-left.png') }}" alt="前月">前月
            </a>
            <h3 class="nav__month">
                <img src="{{ asset('images/icon/calender.png') }}" alt="カレンダー">{{ $formattedMonth }}
            </h3>
            <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $nextMonth]) }}" class="nav__button">
                翌月<img src="{{ asset('images/icon/arrow-right.png') }}" alt="翌月">
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
                @forelse ($attendances as $attendance)
                    <tr class="attendance-staff__row">
                        <td class="attendance-staff__content">{{ $attendance->formatted_date }}</td>
                        <td class="attendance-staff__content">{{ $attendance->start_time_formatted }}</td>
                        <td class="attendance-staff__content">{{ $attendance->end_time_formatted }}</td>
                        <td class="attendance-staff__content">{{ $attendance->total_break_time }}</td>
                        <td class="attendance-staff__content">{{ $attendance->total_hours }}</td>
                        <td class="attendance-staff__content">
                            <a href="{{ route('admin.attendance.show', ['id' => $attendance->id]) }}" class="content__details">詳細</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="attendance-staff__content">該当する勤怠データがありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- CSV出力 --}}
        <div class="attendance-staff__csv">
            <a href="{{ route('admin.attendance.staff.csv', ['id' => $staff->id, 'month' => $currentMonth]) }}" class="csv__button">CSV出力</a>
        </div>

    </div>
</div>
@endsection