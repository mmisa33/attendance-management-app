@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/modification/list.css')}}">
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
    <div class="container">
        <h1>勤怠修正申請一覧</h1>

        <!-- 承認待ちの申請 -->
        <h3>承認待ち</h3>
        @if ($pendingModifications->isEmpty())
            <p>承認待ちの申請はありません。</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>日付</th>
                        <th>出勤時間</th>
                        <th>退勤時間</th>
                        <th>備考</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pendingModifications as $modification)
                        <tr>
                            <td>{{ $modification->formatted_date }}</td>
                            <td>{{ $modification->start_time }}</td>
                            <td>{{ $modification->end_time }}</td>
                            <td>{{ $modification->note }}</td>
                            <td>
                                <a href="{{ route('stamp_correction_request.details', ['attendance' => $modification->id]) }}" class="btn btn-info">詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- 承認済みの申請 -->
        <h3>承認済み</h3>
        @if ($approvedModifications->isEmpty())
            <p>承認済みの申請はありません。</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>日付</th>
                        <th>出勤時間</th>
                        <th>退勤時間</th>
                        <th>備考</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($approvedModifications as $modification)
                        <tr>
                            <td>{{ $modification->formatted_date }}</td>
                            <td>{{ $modification->start_time }}</td>
                            <td>{{ $modification->end_time }}</td>
                            <td>{{ $modification->note }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
