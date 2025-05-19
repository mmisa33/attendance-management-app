@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/show.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <h2 class="attendance-detail__heading">勤怠詳細</h2>

    {{-- 勤怠詳細フォーム --}}
    <form class="attendance-detail__form" method="POST"
        action="{{ route('attendance.update', ['id' => $attendance->id]) }}" novalidate>
        @csrf

        <table class="attendance-detail__table">
            {{-- 名前 --}}
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header">名前</th>
                <td class="attendance-detail__content attendance-detail__content--name">
                    {{ $user->name }}
                </td>
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
                <td class="attendance-detail__content attendance-detail__content--time @if ($attendance->is_modified) modified @endif">
                    <input type="time" name="start_time" id="start_time" value="{{ old('start_time', $attendance->formatted_start_time) }}" class="content__time" @if ($attendance->is_modified) disabled @endif>
                    <span class="content__time-separator">～</span>
                    <input type="time" name="end_time" id="end_time" value="{{ old('end_time', $attendance->formatted_end_time) }}" class="content__time" @if ($attendance->is_modified) disabled @endif>

                    {{-- エラーメッセージ --}}
                    @foreach (['start_time', 'end_time'] as $field)
                        @error($field)
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    @endforeach
                </td>
            </tr>

            {{-- 休憩レコード --}}
            @foreach ($breakRows as $breakRow)
                <tr class="attendance-detail__row">
                    <th class="attendance-detail__header">
                        休憩{{ $breakRow['index'] == 0 ? '' : $breakRow['index'] + 1 }}
                    </th>
                    <td class="attendance-detail__content attendance-detail__content--time @if ($attendance->is_modified) modified @endif">
                        <input type="time" name="break_start[{{ $breakRow['index'] }}]" value="{{ old('break_start.' . $breakRow['index'], $breakRow['start']) }}" class="content__time" @if ($attendance->is_modified) disabled @endif>

                        @if (!$attendance->is_modified || $breakRow['start'])
                            <span class="content__time-separator">～</span>
                        @endif

                        <input type="time" name="break_end[{{ $breakRow['index'] }}]" value="{{ old('break_end.' . $breakRow['index'], $breakRow['end']) }}" class="content__time" @if ($attendance->is_modified) disabled @endif>

                        {{-- エラーメッセージ --}}
                        @foreach (["break_start.{$breakRow['index']}", "break_end.{$breakRow['index']}"] as $field)
                            @error($field)
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        @endforeach
                    </td>
                </tr>
            @endforeach

            {{-- 休憩開始が一度でも押された場合に、空の休憩行を追加 --}}
            @if (collect($breakRows)->contains(fn($breakRow) => !empty($breakRow['start'])))
                <tr class="attendance-detail__row">
                    <th class="attendance-detail__header">
                        休憩{{ count($breakRows) + 1 }}
                    </th>
                    <td class="attendance-detail__content attendance-detail__content--time @if ($attendance->is_modified) modified @endif">
                        <input type="time" name="break_start[{{ count($breakRows) }}]" value="" class="content__time" @if ($attendance->is_modified) disabled @endif>

                        @if (!$attendance->is_modified)
                            <span class="content__time-separator">～</span>
                        @endif

                        <input type="time" name="break_end[{{ count($breakRows) }}]" value="" class="content__time" @if ($attendance->is_modified) disabled @endif>

                        {{-- エラーメッセージ --}}
                        @foreach (["break_start." . count($breakRows), "break_end." . count($breakRows)] as $field)
                            @error($field)
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        @endforeach
                    </td>
                </tr>
            @endif

            {{-- 備考 --}}
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header" for="note">備考</th>
                <td
                    class="attendance-detail__content attendance-detail__content--textarea
                @if ($attendance->is_modified) modified @endif">
                    <textarea class="content__textarea" name="note" id="note" rows="2" @if ($attendance->is_modified) disabled @endif>{{ old('note', $attendance->note) }}</textarea>

                    {{-- エラーメッセージ --}}
                    @error('note')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
        </table>

        <div class="attendance-detail__actions">
            {{-- 修正申請ボタン --}}
            @if (!$attendance->is_modified && !$attendance->is_approved)
                <button type="submit" class="attendance-detail__btn">修正</button>
            @endif

            {{-- 申請中の場合 --}}
            @if ($attendance->is_modified && !$attendance->is_approved)
                <div class="attendance-detail__alert-message">*承認待ちのため修正はできません。</div>
            @endif

            {{-- 承認済みの場合 --}}
            @if ($attendance->is_approved)
                <p class="attendance-detail__status--approved">承認済み</p>
            @endif
        </div>
    </form>
</div>

<script>
// 時計のデフォルト表示を制限
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[type="time"]').forEach(input => {
        // ページ読み込み時に空なら色を透明に
        if (!input.value) {
            input.style.color = 'transparent';
        }

        // 入力変化時に色を切り替え
        input.addEventListener('input', () => {
            if (!input.value) {
                input.style.color = 'transparent';
            } else {
                input.style.color = '';
            }
        });

        // フォーカス時は色を黒に（透明解除）
        input.addEventListener('focus', () => {
            input.style.color = '';
        });

        // フォーカスアウト時は値がなければ透明に戻す
        input.addEventListener('blur', () => {
            if (!input.value) {
                input.style.color = 'transparent';
            }
        });
    });
});
</script>
@endsection