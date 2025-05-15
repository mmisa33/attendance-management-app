<nav class="header__links">
    <a class="header__link" href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
    <a class="header__link" href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
    <a class="header__link" href="{{ route('stamp_correction_request.list') }}">申請一覧</a>
    <form action="{{ route('admin.logout') }}" method="POST">
        @csrf
        <input class="header__link" type="submit" value="ログアウト">
    </form>
</nav>