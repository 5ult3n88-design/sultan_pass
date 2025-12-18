<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? __('Assessment Experience') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-iron-950 text-silver-100">
        <div class="relative flex min-h-screen flex-col">
            <header class="border-b border-uae-gold-300/20 bg-iron-900/60 backdrop-blur">
                <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-silver-400">{{ __('PASS Assessment') }}</p>
                        <h1 class="text-xl font-semibold text-white">{{ $title ?? __('Assessment Experience') }}</h1>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-silver-300">
                        @isset($timeRemaining)
                            <div class="rounded-lg border border-uae-gold-400/40 bg-uae-gold-400/15 px-3 py-2 font-semibold text-uae-gold-100">
                                {{ __('Time remaining') }}: {{ $timeRemaining }}
                            </div>
                        @endisset
                        <form action="{{ route('locale.switch') }}" method="POST">
                            @csrf
                            <input type="hidden" name="redirect" value="{{ url()->current() }}">
                            <button name="locale" value="{{ app()->getLocale() === 'ar' ? 'en' : 'ar' }}" class="rounded-lg border border-uae-gold-300/20 bg-uae-gold-300/5 px-3 py-2 text-xs font-semibold text-silver-200 hover:bg-uae-gold-300/10">
                                {{ app()->getLocale() === 'ar' ? __('English') : __('العربية') }}
                            </button>
                        </form>
                        @include('components.theme-toggle')
                    </div>
                </div>
            </header>

            <main class="mx-auto flex w-full max-w-5xl flex-1 flex-col px-6 py-8">
                @yield('content')
            </main>
        </div>
    </body>
</html>

