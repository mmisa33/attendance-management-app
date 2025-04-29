@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/modification/list.css') }}">
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
<div class="modification-list">
    {{-- ページタイトル --}}
    <h2 class="modification-list__heading">申請一覧</h2>

    {{-- タブ --}}
    <div class="modification-list__tab">
        <button class="tab__links" onclick="openTab(event, 'pending')" aria-controls="pending" aria-selected="false">承認待ち</button>
        <button class="tab__links" onclick="openTab(event, 'approved')" aria-controls="approved" aria-selected="false">承認済み</button>
    </div>

    {{-- 承認待ちの申請 --}}
    <div id="pending" class="tab__content">
        @if ($pendingModifications->isEmpty())
            <p>承認待ちの申請はありません</p>
        @else
            <table class="modification-list__table">
                <thead>
                    <tr class="modification-list__row">
                        <th class="modification-list__header">状態</th>
                        <th class="modification-list__header">名前</th>
                        <th class="modification-list__header">対象日時</th>
                        <th class="modification-list__header">申請理由</th>
                        <th class="modification-list__header">申請日時</th>
                        <th class="modification-list__header">詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pendingModifications as $modification)
                        <tr class="modification-list__row">
                            <td class="modification-list__content">承認待ち</td>
                            <td class="modification-list__content">{{ $modification->user->name }}</td>
                            <td class="modification-list__content">{{ $modification->formatted_date }}</td>
                            <td class="modification-list__content">{{ $modification->note }}</td>
                            <td class="modification-list__content">{{ $modification->formatted_created_at }}</td>
                            <td class="modification-list__content">
                                <a href="{{ route('attendance.details', ['attendance' => $modification->id]) }}" class="content__details">詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- 承認済みの申請 --}}
    <div id="approved" class="tab__content">
        @if ($approvedModifications->isEmpty())
            <p>承認済みの申請はありません</p>
        @else
            <table class="modification-list__table">
                <thead>
                    <tr class="modification-list__row">
                        <th class="modification-list__header">状態</th>
                        <th class="modification-list__header">名前</th>
                        <th class="modification-list__header">対象日時</th>
                        <th class="modification-list__header">申請理由</th>
                        <th class="modification-list__header">申請日時</th>
                        <th class="modification-list__header">詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($approvedModifications as $modification)
                        <tr class="modification-list__row">
                            <td class="modification-list__content">承認済み</td>
                            <td class="modification-list__content">{{ $modification->user->name }}</td>
                            <td class="modification-list__content">{{ $modification->formatted_date }}</td>
                            <td class="modification-list__content">{{ $modification->note }}</td>
                            <td class="modification-list__content">{{ $modification->formatted_created_at }}</td>
                            <td class="modification-list__content">
                                <a href="{{ route('attendance.details', ['attendance' => $modification->id]) }}" class="content__details">詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<script>
// タブの切り替え機能
function openTab(event, tabName) {
    var i, tabcontent, tablinks;

    // 全てのタブコンテンツを非表示にする
    tabcontent = document.getElementsByClassName("tab__content");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    // 全てのタブリンクのactiveクラスとaria-selectedを更新
    tablinks = document.getElementsByClassName("tab__links");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].classList.remove("active");
        tablinks[i].setAttribute("aria-selected", "false");
    }

    // 選択したタブコンテンツを表示
    document.getElementById(tabName).style.display = "block";

    // 選択したタブリンクにactiveクラスを追加し、aria-selectedをtrueに設定
    event.currentTarget.classList.add("active");
    event.currentTarget.setAttribute("aria-selected", "true");
}

// デフォルトでは承認待ちタブを表示
document.addEventListener("DOMContentLoaded", function() {
    var defaultTab = document.querySelector('.tab__links:first-child');
    defaultTab.click();
    defaultTab.setAttribute("aria-selected", "true");
});
</script>
@endsection