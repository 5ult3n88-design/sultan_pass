<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name', 'PASS') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gradient-to-br from-iron-900 via-iron-950 to-iron-950 text-silver-100">
        <div class="absolute inset-0 opacity-60 bg-[radial-gradient(circle_at_top,_#B68A35_0,_transparent_70%)]"></div>
        <div class="relative flex min-h-screen items-center justify-center px-6 py-12">
            <div class="w-full max-w-5xl rounded-3xl bg-iron-900/80 backdrop-blur-xl shadow-2xl border border-uae-gold-300/20 overflow-hidden">
                <div class="grid gap-0 md:grid-cols-2">
                    <div class="hidden md:flex flex-col justify-between bg-gradient-to-br from-uae-gold-300/10 via-transparent to-uae-gold-400/20 p-10">
                        <div>
                            <p class="inline-flex items-center rounded-full bg-iron-800/60 px-4 py-1 text-xs font-semibold uppercase tracking-[0.25em] text-silver-300">{{ __('PASS Platform') }}</p>
                            <h1 class="mt-6 text-4xl font-semibold leading-tight text-white">
                                {{ __('AI-assisted Psychometric Assessments') }}
                            </h1>
                            <p class="mt-4 text-sm text-silver-300 leading-relaxed">
                                {{ __('Empower your selection process with multilingual assessments, real-time scoring, and tailored development plans for every candidate journey.') }}
                            </p>
                        </div>
                        <div class="space-y-4">
                            <div class="rounded-2xl border border-uae-gold-300/20 bg-uae-gold-300/5 p-6 shadow-lg backdrop-blur">
                                <p class="text-xs uppercase tracking-wide text-silver-200">{{ __('Platform Highlights') }}</p>
                                <ul class="mt-3 space-y-2 text-sm text-silver-200">
                                    <li class="flex items-center gap-2">
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-uae-gold-300/20 text-uae-gold-300">01</span>
                                        {{ __('AI-powered scoring & recommendations') }}
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-uae-gold-300/20 text-uae-gold-300">02</span>
                                        {{ __('Role-based dashboards for every stakeholder') }}
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-uae-gold-300/20 text-uae-gold-300">03</span>
                                        {{ __('Multilingual support with rich analytics') }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="p-8 sm:p-10">
                        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
                            <a href="{{ url('/') }}" class="flex items-center gap-3">
                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-uae-gold-300/20 text-uae-gold-300 text-xl font-semibold">P</span>
                                <div>
                                    <p class="text-sm uppercase tracking-[0.25em] text-silver-400">{{ __('PASS') }}</p>
                                    <p class="text-lg font-semibold text-white">{{ __('Psychometric Assessment Suite') }}</p>
                                </div>
                            </a>
                            <div class="flex items-center gap-3 text-sm text-silver-400">
                                @yield('locale-toggle')
                                @include('components.theme-toggle')
                            </div>
                        </div>
                        @isset($title)
                            <h2 class="text-2xl font-semibold tracking-tight text-white">{{ $title }}</h2>
                        @endisset
                        @isset($subtitle)
                            <p class="mt-2 text-sm text-silver-300">{{ $subtitle }}</p>
                        @endisset

                        @if (session('status'))
                            <div class="mt-6 rounded-lg border border-uae-gold-300/40 bg-uae-gold-300/10 px-4 py-3 text-sm text-uae-gold-100">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mt-6 rounded-lg border border-uae-gold-500/40 bg-uae-gold-500/10 px-4 py-3 text-sm text-uae-gold-100">
                                <ul class="list-disc space-y-1 pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mt-8">
                            @yield('content')
                        </div>

                        <div class="mt-12 text-xs text-silver-500">
                            &copy; {{ now()->year }} {{ config('app.name', 'PASS') }}. {{ __('All rights reserved.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>

