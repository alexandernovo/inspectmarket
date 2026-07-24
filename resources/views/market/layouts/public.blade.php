<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'E-Inspect | Pandan Public Market')</title>
    <link rel="icon" href="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/bootstrap-icons/font/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/einspect/css/market.css') }}">
</head>
<body class="public-page">
    @yield('content')
</body>
</html>
