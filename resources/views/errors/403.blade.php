<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>You cannot open this page</title>
    @include('partials.plain-styles')
</head>
<body>
    <main class="plain-page">
        <div class="plain-box">
            <p class="plain-muted">Error 403</p>
            <h1>You cannot open this page</h1>
            <p>Your account does not have access to this page. Ask the account owner if you need it.</p>
            <a class="plain-button" href="{{ url('/admin') }}">Go to the dashboard</a>
        </div>
    </main>
</body>
</html>
