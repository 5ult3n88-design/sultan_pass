@php($title = __('Create your PASS account'))
@php($subtitle = __('Join the platform to access your assessments and development plans'))

@extends('layouts.auth')

@section('locale-toggle')
    <form action="{{ route('locale.switch') }}" method="POST" class="inline-flex items-center gap-2">
        @csrf
        <input type="hidden" name="redirect" value="{{ url()->current() }}">
        <button name="locale" value="{{ app()->getLocale() === 'ar' ? 'en' : 'ar' }}" class="rounded-full border border-white/20 bg-white/10 px-4 py-1 text-xs font-medium text-white transition hover:bg-white/20">
            {{ app()->getLocale() === 'ar' ? __('English') : __('العربية') }}
        </button>
    </form>
@endsection

@section('content')
    <form method="POST" action="{{ route('register.perform') }}" class="space-y-6">
        @csrf
        <div class="grid gap-6 sm:grid-cols-2">
            <div class="space-y-2">
                <label for="username" class="block text-sm font-medium text-slate-200">{{ __('Username') }}</label>
                <input
                    id="username"
                    name="username"
                    type="text"
                    value="{{ old('username') }}"
                    required
                    class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/50"
                    placeholder="username"
                >
            </div>

            <div class="space-y-2">
                <label for="full_name" class="block text-sm font-medium text-slate-200">{{ __('Full name (optional)') }}</label>
                <input
                    id="full_name"
                    name="full_name"
                    type="text"
                    value="{{ old('full_name') }}"
                    class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/50"
                    placeholder="{{ __('e.g. Sarah Ahmed') }}"
                >
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div class="space-y-2">
                <label for="email" class="block text-sm font-medium text-slate-200">{{ __('Email address') }}</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/50"
                    placeholder="name@example.com"
                >
            </div>

            <div class="space-y-2">
                <label for="language_pref" class="block text-sm font-medium text-slate-200">{{ __('Preferred language') }}</label>
                <select
                    id="language_pref"
                    name="language_pref"
                    class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/50"
                >
                    <option value="">{{ __('Auto detect') }}</option>
                    @foreach($languages as $language)
                        <option value="{{ $language->id }}" @selected(old('language_pref') == $language->id)>{{ __($language->name) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div class="space-y-2">
                <label for="password" class="block text-sm font-medium text-slate-200">{{ __('Password') }}</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/50"
                    placeholder="••••••••"
                >
            </div>

            <div class="space-y-2">
                <label for="password_confirmation" class="block text-sm font-medium text-slate-200">{{ __('Confirm password') }}</label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                    class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/50"
                    placeholder="••••••••"
                >
            </div>
        </div>

        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-uae-gold-300 via-uae-gold-400 to-uae-gold-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-uae-gold-300/30 transition hover:shadow-uae-gold-300/50">
            {{ __('Create account') }}
        </button>
    </form>

    <div class="mt-8 text-sm text-slate-300">
        {{ __('Already have an account?') }}
        <a href="{{ route('login') }}" class="font-semibold text-uae-gold-300 hover:text-uae-gold-100">{{ __('Sign in') }}</a>
    </div>
@endsection

