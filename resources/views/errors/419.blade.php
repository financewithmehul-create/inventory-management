<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>This page expired</title>
    @include('partials.plain-styles')
</head>
<body>
    <main class="plain-page">
        <div class="plain-box">
            <p class="plain-muted">Error 419</p>
            <h1>This page expired</h1>
            <p>The page was open for too long. Go back, refresh and try again.</p>
            <a class="plain-button" href="{{ url('/admin') }}">Go to the dashboard</a>
        </div>
    </main>
</body>
</html>
