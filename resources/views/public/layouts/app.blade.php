<!DOCTYPE html>
<html lang="en" class="h-full bg-white">
<head>
    @include('public.partials.script-injections', ['placement' => 'head_start', 'pageType' => $pageType ?? 'home', 'storeId' => $storeId ?? null])
    {!! $region->head_start_script !!}

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if ($region->favicon_path)
        <link rel="icon" href="{{ Storage::url($region->favicon_path) }}">
    @endif

    <title>{{ $seoTitle ?? ($region->name . ' Coupons, Promo Codes & Deals') }}</title>
    <meta name="description" content="{{ $seoDescription ?? 'Verified coupon codes, promo codes and deals for ' . $region->name . '.' }}">
    <link rel="canonical" href="{{ $canonicalUrl ?? url()->current() }}">
    <meta name="robots" content="{{ ($robotsIndex ?? true) ? 'index' : 'noindex' }},{{ ($robotsFollow ?? true) ? 'follow' : 'nofollow' }}">

    <meta property="og:title" content="{{ $ogTitle ?? ($seoTitle ?? $region->name . ' Coupons, Promo Codes & Deals') }}">
    <meta property="og:description" content="{{ $seoDescription ?? '' }}">
    @if (!empty($ogImage))
        <meta property="og:image" content="{{ $ogImage }}">
    @endif

    @stack('schema')

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/ajax-filters.js'])
    @stack('head')

    @include('public.partials.script-injections', ['placement' => 'head_end', 'pageType' => $pageType ?? 'home', 'storeId' => $storeId ?? null])
    {!! $region->head_end_script !!}
</head>
<body class="flex min-h-full flex-col bg-white text-gray-900 antialiased">
    @include('public.partials.script-injections', ['placement' => 'body_start', 'pageType' => $pageType ?? 'home', 'storeId' => $storeId ?? null])
    {!! $region->body_start_script !!}

    @include('public.partials.header')

    <main class="flex-1">
        @yield('content')
    </main>

    @include('public.partials.footer')

    @include('public.partials.script-injections', ['placement' => 'body_end', 'pageType' => $pageType ?? 'home', 'storeId' => $storeId ?? null])
    {!! $region->body_end_script !!}
</body>
</html>
