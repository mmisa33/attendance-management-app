@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/details.css')}}">
@endsection

@section('link')
{{-- ヘッダーリンク --}}
<div class="header__links">
    {{-- 管理者用 --}}
    @if(auth()->guard('admin')->check())
        <a class="header__link" href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
        <a class="header__link" href="">スタッフ一覧</a>
        <a class="header__link" href="">申請一覧</a>
        <form action="{{ route('admin.logout') }}" method="POST">
            @csrf
            <input class="header__link" type="submit" value="ログアウト">
        </form>
    {{-- 一般ユーザー用 --}}
    @elseif(auth()->guard('web')->check())
        <a class="header__link" href="{{ route('attendance.index') }}">勤怠</a>
        <a class="header__link" href="{{ route('attendance.list') }}">勤怠一覧</a>
        <a class="header__link" href="{{ route('stamp_correction_request.list') }}">申請</a>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <input class="header__link" type="submit" value="ログアウト">
        </form>
    @endif
</div>
@endsection

@section('content')
<div class="attendance-detail">
    <h2 class="attendance-detail__heading">勤怠詳細</h2>

    <form class="attendance-detail__form" method="POST" action="{{ route('attendance.updateDetail', ['attendance' => $attendance->id]) }}"  novalidate>
        @csrf

        <table class="attendance-detail__table">
            {{-- 名前 --}}
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header">名前</th>
                <td class="attendance-detail__content attendance-detail__content--name">
                    {{-- 管理者が他のユーザーの勤怠を見ているので選択されたユーザー名を表示 --}}
                    @if(Auth::guard('admin')->check())
                        {{ $user->name }}

                    {{-- 一般ユーザーは自分の名前を表示 --}}
                    @elseif(Auth::guard('web')->check())
                        {{ $user->name }}
                    @endif
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
                <td class="attendance-detail__content attendance-detail__content--time @if($attendance->is_modified) modified @endif">
                    <input type="text" name="start_time" id="start_time" value="{{ old('start_time', substr($attendance->start_time, 11, 5)) }}" pattern="\d{2}:\d{2}" class="content__time" @if($attendance->is_modified) disabled @endif>
                    <span class="content__time-separator">～</span>
                    <input type="text" name="end_time" id="end_time" value="{{ old('end_time', substr($attendance->end_time, 11, 5)) }}" pattern="\d{2}:\d{2}" class="content__time" @if($attendance->is_modified) disabled @endif>

                    {{-- エラーメッセージ --}}
                    @error('start_time')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                    @error('end_time')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </td>
            </tr>

            {{-- 休憩レコード --}}
            @foreach ($breakRows as $breakRow)
                <tr class="attendance-detail__row">
                    <th class="attendance-detail__header">
                        {{-- 休憩1の場合は「休憩」それ以外は「休憩2」「休憩3」 --}}
                        @if ($breakRow['index'] == 0)
                            休憩
                        @else
                            休憩{{ $breakRow['index'] + 1 }}
                        @endif
                    </th>
                    <td class="attendance-detail__content attendance-detail__content--time @if($attendance->is_modified) modified @endif">
                        <input type="text" name="break_start[{{ $breakRow['index'] }}]" value="{{ $breakRow['start'] }}" pattern="\d{2}:\d{2}" class="content__time" @if($attendance->is_modified) disabled @endif>

                        @if (!$attendance->is_modified || $breakRow['start'])
                            <span class="content__time-separator">～</span>
                        @endif

                        <input type="text" name="break_end[{{ $breakRow['index'] }}]" value="{{ $breakRow['end'] }}" pattern="\d{2}:\d{2}" class="content__time" @if($attendance->is_modified) disabled @endif>

                        {{-- エラーメッセージ --}}
                        @if ($errors->has("break_start.{$breakRow['index']}"))
                            <div class="error-message">
                                {{ $errors->first("break_start.{$breakRow['index']}") }}
                            </div>
                        @endif
                        @if ($errors->has("break_end.{$breakRow['index']}"))
                            <div class="error-message">
                                {{ $errors->first("break_end.{$breakRow['index']}") }}
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach

            {{-- 備考 --}}
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header" for="note">備考</th>
                <td class="attendance-detail__content attendance-detail__content--textarea
                    @if($attendance->is_modified) modified @endif">
                    <textarea class="content__textarea" name="note" id="note" rows="2" @if($attendance->is_modified) disabled @endif>{{ old('note', $attendance->note) }}</textarea>

                    {{-- エラーメッセージ --}}
                    @error('note')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
        </table>

        {{-- 修正申請ボタン --}}
        @if(!$attendance->is_modified)
            <button type="submit" class="attendance-detail__button">修正</button>
        @endif

        {{-- 申請中の場合 --}}
        @if($attendance->is_modified)
            <div class="alert-message">*承認待ちのため修正はできません。</div>
        @endif
    </form>
</div>
@endsection