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
        <header class="header
            @if(Request::is('login') || Request::is('admin/login') || Request::is('email/verify'))
                header--logo-only
            @endif
        ">
            <div class="header__logo">
                <img src="{{ asset('images/logo.svg') }}" alt="coachtech 勤怠管理アプリ">
            </div>

            @if (!Request::is('login') && !Request::is('admin/login') && !Request::is('password/*'))
                @if (Auth::guard('admin')->check())
                    @include('partials.header.header-admin')
                @elseif (Auth::check())
                    @if (Auth::user()->attendances()->whereNull('end_time')->exists())
                        @include('partials.header.header-user')
                    @else
                        @include('partials.header.header-user-checkedout')
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