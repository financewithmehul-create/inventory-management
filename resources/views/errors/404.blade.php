<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page not found</title>
    @include('partials.plain-styles')
</head>
<body>
    <main class="plain-page">
        <div class="plain-box">
            <p class="plain-muted">Error 404</p>
            <h1>Page not found</h1>
            <p>The page you asked for does not exist. It may have been moved or deleted.</p>
            <a class="plain-button" href="{{ url('/admin') }}">Go to the dashboard</a>
        </div>
    </main>
</body>
</html>
