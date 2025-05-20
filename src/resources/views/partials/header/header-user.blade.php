{{-- 一般ユーザー用ヘッダー --}}
<nav class="header__links">
    <a class="header__link" href="{{ route('attendance.index') }}">勤怠</a>
    <a class="header__link" href="{{ route('attendance.list') }}">勤怠一覧</a>
    <a class="header__link" href="{{ route('stamp_correction_request.list') }}">申請</a>
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <input class="header__link" type="submit" value="ログアウト">
    </form>
</nav>