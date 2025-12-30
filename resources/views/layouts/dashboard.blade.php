<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? config('app.name', 'PASS') }}</title>
        @if (! app()->environment('testing') && file_exists(public_path('build/manifest.json')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-iron-950 text-silver-100">
        <div class="flex min-h-screen">
            <aside class="w-72 border-r border-gray-700/50 bg-gradient-to-b from-gray-800 to-gray-900 backdrop-blur shadow-xl">
                <!-- MOD Header -->
                <div class="border-b border-white/10 bg-black/20 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <!-- UAE MOD Logo -->
                        <div class="flex-shrink-0">
                            <img src="{{ asset('images/logos/mod-hres-dark-logo.webp') }}" alt="UAE Ministry of Defence" class="h-14 w-auto object-contain" loading="lazy" onerror="this.src='{{ asset('images/logos/uae-mod-emblem.svg') }}'">
                        </div>
                        <!-- MOD Text -->
                        <div class="flex-1">
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-amber-400">{{ __('UAE MOD') }}</p>
                            <p class="text-sm font-medium text-amber-300 leading-tight">{{ __('Ministry of Defence') }}</p>
                        </div>
                    </div>
                </div>

                <!-- PASS System Header -->
                <div class="px-6 py-6">
                    <a href="{{ route('dashboard.admin') }}" class="flex items-center gap-3 group">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-amber-500/20 text-amber-400 text-lg font-bold transition group-hover:bg-amber-500/30">P</span>
                        <div>
                            <p class="text-xs uppercase tracking-[0.25em] text-amber-400">{{ __('PASS') }}</p>
                            <p class="text-base font-semibold text-amber-300 group-hover:text-amber-200 transition">{{ __('Psychometric Assessment') }}</p>
                        </div>
                    </a>
                </div>
                <nav class="mt-6 space-y-1 px-6 text-sm font-medium text-amber-400">
                    <a href="{{ route('dashboard.admin') }}" class="flex items-center justify-between rounded-lg px-4 py-2 transition hover:bg-amber-500/15 {{ request()->routeIs('dashboard.admin') ? 'bg-amber-500/20 text-amber-300' : '' }}">
                        <span>{{ __('Overview') }}</span>
                    </a>
                    <div class="space-y-1">
                        <button id="users-menu-toggle" class="flex w-full items-center justify-between rounded-lg px-4 py-2 transition hover:bg-amber-500/15">
                            <span class="text-xs font-semibold uppercase tracking-wider text-amber-400">{{ __('Users') }}</span>
                            <svg id="users-menu-icon" class="h-4 w-4 text-amber-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                        <div id="users-submenu" class="max-h-48 space-y-1 overflow-y-auto pl-4 transition-all {{ request()->routeIs('admin.users.*') ? '' : 'hidden' }}" style="scrollbar-width: thin; scrollbar-color: rgba(251, 191, 36, 0.4) transparent;">
                            <a href="{{ route('admin.users.index') }}" class="flex items-center justify-between rounded-lg px-4 py-2 transition hover:bg-amber-500/15 {{ (request()->routeIs('admin.users.index') || request()->routeIs('admin.users.create') || request()->routeIs('admin.users.edit')) && !request()->routeIs('admin.users.participants') && !request()->routeIs('admin.users.create-participant') ? 'bg-amber-500/20 text-amber-300' : '' }}">
                                <span>{{ __('Staff users') }}</span>
                                <span class="rounded-full bg-amber-500/20 px-2 py-0.5 text-xs text-amber-400">{{ \App\Models\User::where('role', '!=', 'participant')->count() }}</span>
                            </a>
                            <a href="{{ route('admin.users.participants') }}" class="flex items-center justify-between rounded-lg px-4 py-2 transition hover:bg-amber-500/15 {{ request()->routeIs('admin.users.participants') || request()->routeIs('admin.users.create-participant') ? 'bg-amber-500/20 text-amber-300' : '' }}">
                                <span>{{ __('Participants') }}</span>
                                <span class="rounded-full bg-amber-500/20 px-2 py-0.5 text-xs text-amber-400">{{ \App\Models\User::where('role', 'participant')->count() }}</span>
                    </a>
                        </div>
                    </div>
                    <a href="{{ route('admin.languages.index') }}" class="flex items-center justify-between rounded-lg px-4 py-2 transition hover:bg-amber-500/15 {{ request()->routeIs('admin.languages.*') ? 'bg-amber-500/20 text-amber-300' : '' }}">
                        <span>{{ __('Languages') }}</span>
                        <span class="rounded-full bg-amber-500/20 px-2 py-0.5 text-xs text-amber-400">{{ \App\Models\Language::count() }}</span>
                    </a>
                    <a href="{{ route('admin.password-resets.index') }}" class="flex items-center justify-between rounded-lg px-4 py-2 transition hover:bg-amber-500/15 {{ request()->routeIs('admin.password-resets.*') ? 'bg-amber-500/20 text-amber-300' : '' }}">
                        <span>{{ __('Password resets') }}</span>
                        <span class="rounded-full bg-amber-500/20 px-2 py-0.5 text-xs font-semibold text-amber-400">
                            {{ \App\Models\PasswordResetRequest::pending()->count() }}
                        </span>
                    </a>
                    <a href="{{ route('assessments.create') }}" class="flex items-center justify-between rounded-lg px-4 py-2 transition hover:bg-amber-500/15 {{ request()->routeIs('assessments.create') ? 'bg-amber-500/20 text-amber-300' : '' }}">
                        <span>{{ __('New assessment') }}</span>
                        <span class="rounded-full bg-amber-500/20 px-2 py-0.5 text-xs font-semibold text-amber-400">
                            {{ __('Go') }}
                        </span>
                    </a>
                    @if(auth()->user()->role !== 'participant')
                    <a href="{{ route('ai-assistant.index') }}" class="flex items-center justify-between rounded-lg px-4 py-2 transition hover:bg-amber-500/15 {{ request()->routeIs('ai-assistant.*') ? 'bg-amber-500/20 text-amber-300' : '' }}">
                        <span>{{ __('AI Assistant') }}</span>
                        <span class="rounded-full bg-purple-500/30 px-2 py-0.5 text-xs font-semibold text-purple-100">
                            {{ __('AI') }}
                        </span>
                    </a>
                    @endif
                </nav>
                <div class="mt-auto px-6 pb-8 pt-6">
                    <form action="{{ route('locale.switch') }}" method="POST" class="space-y-2">
                        @csrf
                        <input type="hidden" name="redirect" value="{{ url()->current() }}">
                        <button name="locale" value="{{ app()->getLocale() === 'ar' ? 'en' : 'ar' }}" class="w-full rounded-lg border border-amber-500/30 bg-amber-500/15 px-4 py-2 text-sm text-amber-400 transition hover:bg-amber-500/25">
                            {{ app()->getLocale() === 'ar' ? __('English') : __('العربية') }}
                        </button>
                    </form>
                    <form action="{{ route('logout') }}" method="POST" class="mt-3">
                        @csrf
                        <button class="w-full rounded-lg border border-amber-500/40 bg-amber-500/20 px-4 py-2 text-sm font-semibold text-amber-300 transition hover:bg-amber-500/30">
                            {{ __('Sign out') }}
                        </button>
                    </form>
                </div>
            </aside>
            <main class="flex-1 overflow-y-auto">
                <header class="border-b border-uae-gold-300/20 bg-iron-900/70 px-10 py-6 backdrop-blur flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                    <h1 class="text-2xl font-semibold text-white">{{ $title ?? __('Dashboard') }}</h1>
                    @isset($subtitle)
                            <p class="mt-2 text-sm text-silver-300">{{ $subtitle }}</p>
                    @endisset
                    </div>
                    <div class="flex items-center gap-3">
                        @include('components.theme-toggle')
                    </div>
                </header>
                <section class="px-10 py-10">
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
                </section>
            </main>
        </div>
        <script>
            // Users menu toggle
            document.addEventListener('DOMContentLoaded', function() {
                const toggle = document.getElementById('users-menu-toggle');
                const submenu = document.getElementById('users-submenu');
                const icon = document.getElementById('users-menu-icon');
                
                if (toggle && submenu) {
                    // Open by default if on users route
                    const isUsersRoute = {{ request()->routeIs('admin.users.*') ? 'true' : 'false' }};
                    if (isUsersRoute) {
                        submenu.classList.remove('hidden');
                        icon.classList.add('rotate-90');
                    }
                    
                    toggle.addEventListener('click', function() {
                        submenu.classList.toggle('hidden');
                        icon.classList.toggle('rotate-90');
                    });
                }
            });
        </script>
    </body>
</html>

