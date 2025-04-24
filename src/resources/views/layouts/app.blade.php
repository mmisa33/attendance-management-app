<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech 勤怠管理アプリ</title>
    <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css" />
    <link rel="stylesheet" href="{{ asset('css/common.css')}}">
    @yield('css')
</head>

<body>
    <div class="app">
        <header class="header {{ View::hasSection('link') ? '' : 'header--logo-only' }}">
            {{--  サイトタイトル  --}}
            <div class="header__logo">
                {{-- 後にルート設定 --}}
                <a href="/">
                    <img src="{{ asset('images/logo.svg') }}" alt="coachtech 勤怠管理アプリ">
                </a>
            </div>

            {{--  ヘッダーリンク   --}}
            @yield('link')
        </header>

        {{--  メインコンテンツ  --}}
        <main class="content">
            @yield('content')
        </main>
    </div>
</body>

</html>