<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name', 'PASS') }}</title>
        @if (! app()->environment('testing') && file_exists(public_path('build/manifest.json')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-iron-950 text-silver-100">
        <header class="border-b border-uae-gold-300/20 bg-iron-900/80 backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-silver-400">{{ __('PASS') }}</p>
                    <h1 class="text-2xl font-semibold text-white">{{ $title ?? __('Dashboard') }}</h1>
                    @isset($subtitle)
                        <p class="mt-1 text-sm text-silver-300">{{ $subtitle }}</p>
                    @endisset
                </div>
                <div class="flex items-center gap-3">
                    <form action="{{ route('locale.switch') }}" method="POST" class="inline-flex">
                        @csrf
                        <input type="hidden" name="redirect" value="{{ url()->current() }}">
                        <button name="locale" value="{{ app()->getLocale() === 'ar' ? 'en' : 'ar' }}" class="rounded-lg border border-uae-gold-300/20 bg-uae-gold-300/5 px-3 py-2 text-xs font-semibold text-silver-200 hover:bg-uae-gold-300/10">
                            {{ app()->getLocale() === 'ar' ? __('English') : __('العربية') }}
                        </button>
                    </form>
                    @include('components.theme-toggle')
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="rounded-lg border border-uae-gold-400/40 bg-uae-gold-400/20 px-4 py-2 text-xs font-semibold text-uae-gold-100 hover:bg-uae-gold-400/30">
                            {{ __('Sign out') }}
                        </button>
                    </form>
                </div>
            </div>
        </header>
        <div class="mx-auto max-w-6xl px-6">
            @auth
                @if(auth()->user()->role === 'participant')
                    <nav class="border-b border-white/10 py-4 mb-6">
                        <div class="flex items-center gap-4 text-sm">
                            <a href="{{ route('dashboard.participant') }}" class="px-3 py-2 rounded-lg transition {{ request()->routeIs('dashboard.participant') ? 'bg-uae-gold-300/20 text-uae-gold-200' : 'text-slate-300 hover:bg-white/5' }}">
                                {{ __('My Dashboard') }}
                            </a>
                            <a href="{{ route('tests.available') }}" class="px-3 py-2 rounded-lg transition {{ request()->routeIs('tests.*') ? 'bg-uae-gold-300/20 text-uae-gold-200' : 'text-slate-300 hover:bg-white/5' }}">
                                {{ __('My Tests') }}
                            </a>
                            <a href="{{ route('dashboard.examinee-performance') }}" class="px-3 py-2 rounded-lg transition {{ request()->routeIs('dashboard.examinee-performance*') ? 'bg-uae-gold-300/20 text-uae-gold-200' : 'text-slate-300 hover:bg-white/5' }}">
                                {{ __('My Performance') }}
                            </a>
                        </div>
                    </nav>
                @elseif(auth()->user()->role === 'assessor' || auth()->user()->role === 'manager')
                    <nav class="border-b border-white/10 py-4 mb-6">
                        <div class="flex items-center gap-4 text-sm">
                            <a href="{{ auth()->user()->role === 'assessor' ? route('dashboard.assessor') : route('dashboard.manager') }}" class="px-3 py-2 rounded-lg transition {{ request()->routeIs('dashboard.*') && !request()->routeIs('dashboard.examinee-performance*') ? 'bg-uae-gold-300/20 text-uae-gold-200' : 'text-slate-300 hover:bg-white/5' }}">
                                {{ __('Dashboard') }}
                            </a>
                            <a href="{{ auth()->user()->role === 'assessor' ? route('assessor.assessments') : route('manager.assessments') }}" class="px-3 py-2 rounded-lg transition {{ request()->routeIs(auth()->user()->role . '.assessments') ? 'bg-uae-gold-300/20 text-uae-gold-200' : 'text-slate-300 hover:bg-white/5' }}">
                                {{ __('Assessments') }}
                            </a>
                            <a href="{{ auth()->user()->role === 'assessor' ? route('assessor.participants') : route('manager.participants') }}" class="px-3 py-2 rounded-lg transition {{ request()->routeIs(auth()->user()->role . '.participants') ? 'bg-uae-gold-300/20 text-uae-gold-200' : 'text-slate-300 hover:bg-white/5' }}">
                                {{ __('Participants') }}
                            </a>
                            <a href="{{ route('dashboard.examinee-performance') }}" class="px-3 py-2 rounded-lg transition {{ request()->routeIs('dashboard.examinee-performance*') ? 'bg-uae-gold-300/20 text-uae-gold-200' : 'text-slate-300 hover:bg-white/5' }}">
                                {{ __('Performance Dashboard') }}
                            </a>
                        </div>
                    </nav>
                @endif
            @endauth
        </div>
        <main class="mx-auto max-w-6xl px-6 pb-10">
            @if (session('status'))
                <div class="mb-6 rounded-xl border border-uae-gold-300/30 bg-uae-gold-300/15 px-5 py-4 text-sm text-uae-gold-100">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-uae-gold-500/30 bg-uae-gold-500/15 px-5 py-4 text-sm text-uae-gold-100">
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </body>
</html>

