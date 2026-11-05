<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Something went wrong</title>
    @include('partials.plain-styles')
</head>
<body>
    <main class="plain-page">
        <div class="plain-box">
            <p class="plain-muted">Error 500</p>
            <h1>Something went wrong</h1>
            <p>The problem has been recorded. Please try again in a moment.</p>
            <a class="plain-button" href="{{ url('/admin') }}">Go to the dashboard</a>
        </div>
    </main>
</body>
</html>
