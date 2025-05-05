@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/index.css')}}">
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
<div class="staff-list">
    {{-- ページタイトル --}}
    <h2 class="staff-list__heading">スタッフ一覧</h2>

    {{-- スタッフ一覧 --}}
    <table class="staff-list__table">
        <thead>
            <tr class="staff-list__row">
                <th class="staff-list__header">氏名</th>
                <th class="staff-list__header">メールアドレス</th>
                <th class="staff-list__header">月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach($staffList as $staff)
                <tr class="staff-list__row">
                    <td class="staff-list__content">{{ $staff->name }}</td>
                    <td class="staff-list__content">{{ $staff->email }}</td>
                    <td class="staff-list__content">
                        <a href="{{ route('admin.attendance.staff', ['id' => $staff->id]) }}" class="content__details">詳細</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
