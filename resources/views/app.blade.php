<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google" content="notranslate">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%230ea5e9'/><text x='16' y='24' font-family='system-ui,sans-serif' font-size='20' font-weight='700' fill='white' text-anchor='middle'>S</text></svg>">

    {{-- SEO --}}
    <title>{{ $page['props']['meta']['title'] ?? $page['props']['seo']['title'] ?? 'StayFlow — System rezerwacji online' }}</title>
    <meta name="description" content="{{ $page['props']['meta']['description'] ?? $page['props']['seo']['description'] ?? 'StayFlow to nowoczesny system rezerwacji apartamentow, domow i pokoi. Rezerwuj bezposrednio, bez posrednikow.' }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $page['props']['meta']['title'] ?? $page['props']['seo']['title'] ?? 'StayFlow — System rezerwacji online' }}">
    <meta property="og:description" content="{{ $page['props']['meta']['description'] ?? $page['props']['seo']['description'] ?? 'Rezerwuj apartamenty bezposrednio. Bez prowizji, bez posrednikow.' }}">
    @if(isset($page['props']['meta']['image']) && $page['props']['meta']['image'])
    <meta property="og:image" content="{{ $page['props']['meta']['image'] }}">
    @endif
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="StayFlow">

    {{-- Schema.org Structured Data --}}
    @if(isset($page['props']['schema']))
    <script type="application/ld+json">
{!! json_encode($page['props']['schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    @endif

    {{-- Google Analytics --}}
    @if(config('stayflow.google_analytics_id'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('stayflow.google_analytics_id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ config('stayflow.google_analytics_id') }}');
    </script>
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="bg-gray-50 antialiased">
    @inertia
</body>
</html>
