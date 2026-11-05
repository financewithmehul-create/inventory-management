<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Too many tries</title>
    @include('partials.plain-styles')
</head>
<body>
    <main class="plain-page">
        <div class="plain-box">
            <p class="plain-muted">Error 429</p>
            <h1>Too many tries</h1>
            <p>Please wait a minute before trying again.</p>
            <a class="plain-button" href="{{ url('/admin') }}">Go to the dashboard</a>
        </div>
    </main>
</body>
</html>
