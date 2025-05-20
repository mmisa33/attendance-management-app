<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech 勤怠管理アプリ</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css" />
    <link rel="stylesheet" href="{{ asset('css/common.css')}}">
    @yield('css')
</head>

<body>
    <div class="app">
        {{-- ヘッダー --}}
        <header class="header
            @if (
                Request::is('login') ||
                Request::is('register') ||
                Request::is('admin/login') ||
                Request::is('email/verify*') && Auth::check() && !Auth::user()->hasVerifiedEmail())
                header--logo-only
            @endif
        ">
            {{-- ロゴは常に表示 --}}
            <div class="header__logo">
                <img src="{{ asset('images/logo.svg') }}" alt="coachtech 勤怠管理アプリ">
            </div>

            {{-- ログイン状態によってヘッダー内容を切り替え --}}
            @if (!Request::is('email/verify*'))
                @if (Auth::guard('admin')->check())
                    {{-- 管理者用ヘッダー --}}
                    @include('partials.header.header-admin')
                @elseif (Auth::check())
                    @if ($todayAttendance && $todayAttendance->status === $attendanceStatuses['done'])
                        {{-- 一般ユーザー用退勤時ヘッダー --}}
                        @include('partials.header.header-user-checkedout')
                    @else
                        {{-- 一般ユーザー用ヘッダー --}}
                        @include('partials.header.header-user')
                    @endif
                @endif
            @endif
        </header>


        {{-- メインコンテンツ --}}
        <main class="content">
            @yield('content')
        </main>
    </div>
</body>

</html>