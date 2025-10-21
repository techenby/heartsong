<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name', "Heartsong") }}</title>

@env('production')
<link rel="icon" type="image/png" href="/favicon/favicon-96x96.png" sizes="96x96" />
<link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg" />
<link rel="shortcut icon" href="/favicon/favicon.ico" />
<link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png" />
<meta name="apple-mobile-web-app-title" content="Heartsong" />
<link rel="manifest" href="/favicon/site.webmanifest" />
@else
<link rel="icon" type="image/png" href="/favicon-dev/favicon-96x96.png" sizes="96x96" />
<link rel="icon" type="image/svg+xml" href="/favicon-dev/favicon.svg" />
<link rel="shortcut icon" href="/favicon-dev/favicon.ico" />
<link rel="apple-touch-icon" sizes="180x180" href="/favicon-dev/apple-touch-icon.png" />
<meta name="apple-mobile-web-app-title" content="Heartsong" />
<link rel="manifest" href="/favicon-dev/site.webmanifest" />
@endenv

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
