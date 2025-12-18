@php($title = __('Reset your password'))
@php($subtitle = __('Enter your email to receive a secure reset link'))

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
    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
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

        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-uae-gold-300 via-uae-gold-400 to-uae-gold-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-uae-gold-300/30 transition hover:shadow-uae-gold-300/50">
            {{ __('Send reset link') }}
        </button>
    </form>

    <div class="mt-8 text-sm text-slate-300">
        <a href="{{ route('login') }}" class="font-semibold text-uae-gold-300 hover:text-uae-gold-100">{{ __('Back to sign in') }}</a>
    </div>
@endsection

