<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | {{ $businessName }}</title>
    <link rel="icon" href="{{ asset('images/favicon.ico') }}">
    @include('partials.plain-styles')
</head>
<body>
    <main class="plain-page">
        <p class="plain-brand">{{ $businessName }}</p>
        <h1>@yield('title')</h1>
        <p class="plain-muted">Last updated: {{ \Illuminate\Support\Carbon::parse('2026-11-05')->format('d M Y') }}</p>
        @yield('content')
        <p class="plain-links">
            <a href="{{ url('/admin/login') }}">Sign in</a>
            <a href="{{ route('legal.terms') }}">Terms of service</a>
            <a href="{{ route('legal.privacy') }}">Privacy policy</a>
        </p>
    </main>
</body>
</html>
