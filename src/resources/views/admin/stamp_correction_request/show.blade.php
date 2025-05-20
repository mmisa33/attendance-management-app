@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/stamp_correction_request/show.css')}}">
@endsection

@section('content')
<div class="approval-detail">
    <h2 class="approval-detail__heading">勤怠詳細</h2>

    {{-- 勤怠承認フォーム --}}
    <div class="approval-detail__form">
        <table class="approval-detail__table">
            {{-- 名前 --}}
            <tr class="approval-detail__row">
                <th class="approval-detail__header">名前</th>
                <td class="approval-detail__content approval-detail__content--name">
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
                    <span class="content__time">{{ $attendance->formatted_start_time }}</span>
                    <span class="content__time-separator">～</span>
                    <span class="content__time">{{ $attendance->formatted_end_time }}</span>
                </td>
            </tr>

            {{-- 休憩 --}}
            @foreach ($breakRows as $breakRow)
                <tr class="approval-detail__row">
                    <th class="approval-detail__header">
                        休憩{{ $breakRow['index'] == 0 ? '' : $breakRow['index'] + 1 }}
                    </th>
                    <td class="approval-detail__content approval-detail__content--time">
                        <span class="content__time">{{ $breakRow['start'] }}</span>

                        @if (!$attendance->is_modified || $breakRow['start'])
                            <span class="content__time-separator">～</span>
                        @endif

                        <span class="content__time">{{ $breakRow['end'] }}</span>
                    </td>
                </tr>
            @endforeach

            {{-- 休憩開始が一度でも押された場合に、空の休憩行を追加 --}}
            @if (collect($breakRows)->contains(fn($breakRow) => !empty($breakRow['start'])))
                <tr class="approval-detail__row">
                    <th class="approval-detail__header">
                        休憩{{ count($breakRows) + 1 }}
                    </th>
                    <td class="approval-detail__content approval-detail__content--time">
                    </td>
                </tr>
            @endif

            {{-- 備考 --}}
            <tr class="approval-detail__row">
                <th class="approval-detail__header">備考</th>
                <td class="approval-detail__content approval-detail__content--textarea">
                    <span class="content__textarea">{{ $attendance->note }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- 承認ボタンまたは承認済みラベル --}}
    @if (!$attendance->is_approved)
        <form id="approval-form" action="{{ route('admin.stamp_correction_request.approve', ['attendance_correction_request' => $attendance->id]) }}" method="POST">
            @csrf
            <button type="submit" class="approval-detail__btn">承認</button>
        </form>
    @else
        <p class="attendance-detail__status--approved">承認済み</p>
    @endif
</div>
@endsection