@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/stamp_correction_request/show.css')}}">
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
<div class="approval-detail">
    <h2 class="approval-detail__heading">勤怠詳細</h2>

    {{-- 勤怠承認フォーム --}}
    <form class="approval-detail__form" method="POST"
        action="{{ route('admin.stamp_correction_request.approve', ['id' => $attendance->id]) }}" novalidate>
        @csrf

        <table class="approval-detail__table">
            {{-- 名前 --}}
            <tr class="approval-detail__row">
                <th class="approval-detail__header">名前</th>
                <td class="approval-detail__content">
                    {{ $attendance->user->name }}
                </td>
            </tr>

            {{-- 日付 --}}
            <tr class="approval-detail__row">
                <th class="approval-detail__header">日付</th>
                <td class="approval-detail__content">
                    <span class="content__year">{{ $attendance->formatted_year }}</span>
                    <span class="content__monthday">{{ $attendance->formatted_monthday }}</span>
                </td>
            </tr>

            {{-- 出勤・退勤 --}}
            <tr class="approval-detail__row">
                <th class="approval-detail__header">出勤・退勤</th>
                <td class="approval-detail__content approval-detail__content--time">
                    {{ substr($attendance->start_time, 11, 5) }}
                    <span class="content__time-separator">～</span>
                    {{ substr($attendance->end_time, 11, 5) }}
                </td>
            </tr>

            {{-- 休憩 --}}
            @foreach ($breakRows as $breakRow)
                <tr class="approval-detail__row">
                    <th class="approval-detail__header">
                        @if ($breakRow['index'] == 0)
                            休憩
                        @else
                            休憩{{ $breakRow['index'] + 1 }}
                        @endif
                    </th>
                    <td class="approval-detail__content approval-detail__content--time">
                        {{ $breakRow['start'] }}
                        @if ($breakRow['start'] && $breakRow['end'])
                            <span class="content__time-separator">～</span>
                        @endif
                        {{ $breakRow['end'] }}
                    </td>
                </tr>
            @endforeach

            {{-- 備考 --}}
            <tr class="approval-detail__row">
                <th class="approval-detail__header">備考</th>
                <td class="approval-detail__content">
                    {{ $attendance->note }}
                </td>
            </tr>
        </table>

        {{-- 承認ボタンまたは承認済みラベル --}}
        @if (!$attendance->is_approved)
            <form id="approval-form" action="{{ route('admin.stamp_correction_request.approve', ['id' => $attendance->id]) }}" method="POST">
                @csrf
                <button type="submit" class="approval-detail__button">承認</button>
            </form>
        @else
            <p class="approval-detail__approved-label">承認済み</p>
        @endif
    </form>
</div>
@endsection