@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request/list.css') }}">
@endsection

@section('link')
{{-- ヘッダーリンク --}}
<div class="header__links">
    {{-- 管理者用 --}}
    @if (auth()->guard('admin')->check())
        <a class="header__link" href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
        <a class="header__link" href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
        <a class="header__link" href="{{ route('stamp_correction_request.list') }}">申請一覧</a>
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
<div class="request-list">
    {{-- ページタイトル --}}
    <h2 class="request-list__heading">申請一覧</h2>

    {{-- タブ --}}
    <div class="request-list__tab">
        <button class="tab__links" onclick="openTab(event, 'pending')" aria-controls="pending" aria-selected="false">承認待ち</button>
        <button class="tab__links" onclick="openTab(event, 'approved')" aria-controls="approved" aria-selected="false">承認済み</button>
    </div>

    {{-- 承認待ちの申請 --}}
    <div id="pending" class="tab__content">
        @if ($pendingRequests->isEmpty())
            <p>承認待ちの申請はありません</p>
        @else
            <table class="request-list__table">
                <thead>
                    <tr class="request-list__row">
                        <th class="request-list__header">状態</th>
                        <th class="request-list__header">名前</th>
                        <th class="request-list__header">対象日時</th>
                        <th class="request-list__header">申請理由</th>
                        <th class="request-list__header">申請日時</th>
                        <th class="request-list__header">詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pendingRequests as $request)
                        <tr class="request-list__row">
                            <td class="request-list__content">承認待ち</td>
                            <td class="request-list__content">{{ $request->user->name }}</td>
                            <td class="request-list__content">{{ $request->formatted_full_date }}</td>
                            <td class="request-list__content">{{ $request->note }}</td>
                            <td class="request-list__content">{{ $request->formatted_request_date }}</td>
                            <td class="request-list__content">
                                @if(auth()->guard('admin')->check())
                                    <a href="{{ route('admin.stamp_correction_request.show', ['attendance_correction_request' => $request->id]) }}" class="content__detail">詳細</a>
                                @else
                                    <a href="{{ route('attendance.show', ['id' => $request->id]) }}" class="content__detail">詳細</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- 承認済みの申請 --}}
    <div id="approved" class="tab__content">
        @if ($approvedRequests->isEmpty())
            <p>承認済みの申請はありません</p>
        @else
            <table class="request-list__table">
                <thead>
                    <tr class="request-list__row">
                        <th class="request-list__header">状態</th>
                        <th class="request-list__header">名前</th>
                        <th class="request-list__header">対象日時</th>
                        <th class="request-list__header">申請理由</th>
                        <th class="request-list__header">申請日時</th>
                        <th class="request-list__header">詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($approvedRequests as $request)
                        <tr class="request-list__row">
                            <td class="request-list__content">承認済み</td>
                            <td class="request-list__content">{{ $request->user->name }}</td>
                            <td class="request-list__content">{{ $request->formatted_full_date }}</td>
                            <td class="request-list__content">{{ $request->note }}</td>
                            <td class="request-list__content">{{ $request->formatted_request_date }}</td>
                            <td class="request-list__content">
                                @if(auth()->guard('admin')->check())
                                    <a href="{{ route('admin.stamp_correction_request.show', ['attendance_correction_request' => $request->id]) }}" class="content__detail">詳細</a>
                                @else
                                    <a href="{{ route('attendance.show', ['id' => $request->id]) }}" class="content__detail">詳細</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<script>
// タブの切り替え
document.addEventListener("DOMContentLoaded", function() {
    const tabs = document.querySelectorAll(".tab__links");
    const contents = document.querySelectorAll(".tab__content");

    tabs.forEach(tab => {
        tab.addEventListener("click", (event) => {
            tabs.forEach(t => t.classList.remove("active"));
            contents.forEach(content => content.style.display = "none");

            const target = event.currentTarget.getAttribute("aria-controls");
            document.getElementById(target).style.display = "block";
            event.currentTarget.classList.add("active");
        });
    });

    tabs[0].click();
});
</script>
@endsection