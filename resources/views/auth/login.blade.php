@php($title = __('Sign in to your account'))
@php($subtitle = __('Access your personalized PASS dashboard'))

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
    <form method="POST" action="{{ route('login.perform') }}" class="space-y-6">
        @csrf
        <div class="space-y-2">
            <label for="email" class="block text-sm font-medium text-slate-200">{{ __('Email address') }}</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autofocus
                class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/50"
                placeholder="name@example.com"
            >
        </div>

        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <label for="password" class="block text-sm font-medium text-slate-200">{{ __('Password') }}</label>
                <a href="{{ route('password.request') }}" class="text-xs font-medium text-uae-gold-300 hover:text-uae-gold-200">{{ __('Forgot password?') }}</a>
            </div>
            <input
                id="password"
                name="password"
                type="password"
                required
                class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/50"
                placeholder="••••••••"
            >
        </div>

        <label class="inline-flex items-center gap-2 text-sm text-slate-300">
            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-white/10 bg-white/10 text-uae-gold-300 focus:ring-uae-gold-300">
            {{ __('Keep me signed in') }}
        </label>

        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-uae-gold-300 via-uae-gold-400 to-uae-gold-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-uae-gold-300/30 transition hover:shadow-uae-gold-300/50">
            {{ __('Sign in') }}
        </button>
    </form>

    <div class="mt-8 text-sm text-slate-300">
        {{ __('New to PASS?') }}
        <a href="{{ route('register') }}" class="font-semibold text-uae-gold-300 hover:text-uae-gold-100">{{ __('Create an account') }}</a>
    </div>
@endsection

