<!doctype html>

<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <title>Kora 3 - {{ $page_title }}</title>

    <link rel="stylesheet" href="{{config('app.url')}}assets/css/app.css">
</head>
<body class="{{ str_hyphenated($page_class) }}-body">

<div class="{{ str_hyphenated($page_class) }}">
    @yield('body')
</div>

@yield('javascripts')
</body>
</html>
