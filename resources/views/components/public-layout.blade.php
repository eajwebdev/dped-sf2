@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'keywords' => null,
    'robots' => 'index, follow',
    'schema' => null,
])

@php
    $siteUrl = rtrim(config('seo.site_url', config('app.url')), '/');
    $siteName = config('seo.site_name', config('app.name'));
    $pageTitle = $title ?: config('seo.default_title', config('app.name'));
    $pageDescription = $description ?: config('seo.default_description');
    $pageKeywords = $keywords ?: implode(', ', config('seo.keywords', []));
    $canonicalUrl = $canonical ?: $siteUrl.'/'.ltrim(request()->path() === '/' ? '' : request()->path(), '/');
    $shareImage = $siteUrl.'/eaj-primary.png';
    $schemaData = $schema ?: [
        '@context' => 'https://schema.org',
        '@type' => 'SoftwareApplication',
        'name' => $siteName,
        'applicationCategory' => 'EducationalApplication',
        'operatingSystem' => 'Web',
        'url' => $siteUrl,
        'description' => $pageDescription,
        'keywords' => $pageKeywords,
        'offers' => [
            '@type' => 'Offer',
            'priceCurrency' => 'PHP',
            'availability' => 'https://schema.org/InStock',
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <meta name="keywords" content="{{ $pageKeywords }}">
    <meta name="robots" content="{{ $robots }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $shareImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $shareImage }}">

    <link rel="icon" type="image/png" href="{{ asset('eaj-appicon.png') }}">
    <script type="application/ld+json">
        {!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-navy-900 text-slate-100 antialiased"
      x-data="{ scrolled: window.scrollY > 12 }"
      @scroll.window="scrolled = window.scrollY > 12"
      @load.window="scrolled = window.scrollY > 12">
    <header class="fixed inset-x-0 top-0 z-40 transition-all duration-300"
            :class="scrolled ? 'border-b border-white/10 bg-navy-900/80 backdrop-blur-2xl shadow-lg shadow-navy-950/40' : 'bg-transparent'">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6">
            <a href="{{ url('/') }}" class="flex shrink-0 items-center" aria-label="{{ config('app.name') }} — Home">
                <img src="{{ asset('eaj-appicon.png') }}" alt="" class="h-12 w-12 rounded-xl object-contain">
            </a>
            <nav class="hidden items-center gap-7 text-sm font-medium text-slate-300 md:flex">
                <a href="{{ route('public.school-forms') }}" class="transition-colors hover:text-white">School Forms</a>
                <a href="{{ url('/#features') }}" class="transition-colors hover:text-white">Modules</a>
                <a href="{{ url('/#capabilities') }}" class="transition-colors hover:text-white">Platform</a>
                <a href="{{ url('/#how-it-works') }}" class="transition-colors hover:text-white">How it works</a>
                <a href="{{ url('/#pricing') }}" class="transition-colors hover:text-white">Pricing</a>
                <a href="{{ url('/#faq') }}" class="transition-colors hover:text-white">FAQ</a>
            </nav>
            <nav class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('login') }}" class="rounded-xl px-4 py-2 text-sm font-medium text-slate-300 transition-colors hover:bg-white/5 hover:text-white">Log in</a>
                <a href="{{ route('register') }}" class="btn-primary btn-sm sm:btn-md">Get started</a>
            </nav>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="border-t border-white/10 bg-navy-950/60">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6">
            <div class="flex flex-col items-start justify-between gap-8 md:flex-row md:items-center">
                <div>
                    <div class="flex items-center gap-2.5">
                        <img src="{{ asset('eaj-appicon.png') }}" alt="" class="h-9 w-9 rounded-xl object-contain">
                        <div>
                            <span class="block text-base font-extrabold tracking-tight text-white">{{ config('app.name') }}</span>
                            <span class="block text-xs font-medium text-slate-500">Automated School Forms System</span>
                        </div>
                    </div>
                    <p class="mt-4 text-lg font-extrabold tracking-tight text-white">
                        One Platform. <span class="text-gradient-pink">Every DepEd School Form.</span>
                    </p>
                    <p class="mt-2 max-w-sm text-sm leading-relaxed text-slate-400">
                        Automate DepEd School Forms. Simplify Classroom Reporting — starting with QR attendance
                        and one-click SF2, and expanding across the forms a class adviser is responsible for.
                    </p>
                </div>
                <div class="flex flex-wrap gap-x-10 gap-y-4 text-sm">
                    <div class="flex flex-col gap-2.5">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Product</span>
                        <a href="{{ route('public.school-forms') }}" class="text-slate-300 transition-colors hover:text-white">School Forms SF1–SF10</a>
                        <a href="{{ url('/#features') }}" class="text-slate-300 transition-colors hover:text-white">Modules</a>
                        <a href="{{ url('/#capabilities') }}" class="text-slate-300 transition-colors hover:text-white">Platform</a>
                        <a href="{{ url('/#pricing') }}" class="text-slate-300 transition-colors hover:text-white">Pricing</a>
                    </div>
                    <div class="flex flex-col gap-2.5">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Account</span>
                        <a href="{{ route('login') }}" class="text-slate-300 transition-colors hover:text-white">Log in</a>
                        <a href="{{ route('register') }}" class="text-slate-300 transition-colors hover:text-white">Create account</a>
                    </div>
                </div>
            </div>
            <div class="mt-10 border-t border-white/10 pt-6 text-center text-xs text-slate-500">
                © {{ date('Y') }} EAJ ASFS — Automated School Forms System · An EAJ Systems product ·
                The Future of DepEd School Forms Automation
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
