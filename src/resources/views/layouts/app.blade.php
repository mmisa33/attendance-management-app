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
                Request::is('login') ||Request::is('admin/login') ||(Request::is('email/verify*') && !Auth::user()->hasVerifiedEmail()))
                header--logo-only
            @endif
        ">
            <div class="header__logo">
                <img src="{{ asset('images/logo.svg') }}" alt="coachtech 勤怠管理アプリ">
            </div>

            {{-- ロゴのみの場合はヘッダーの中身を表示しない --}}
            @if (!Request::is('email/verify*'))
                {{-- ヘッダー切り替え --}}
                @if (Auth::guard('admin')->check())
                    @include('partials.header.header-admin')
                @elseif (Auth::check())
                    @php
                        $todayAttendance = Auth::user()->attendances()
                            ->whereDate('date', now()->toDateString())
                            ->first();
                    @endphp

                    @if ($todayAttendance && $todayAttendance->status === \App\Models\Attendance::STATUS_DONE)
                        @include('partials.header.header-user-checkedout')
                    @else
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