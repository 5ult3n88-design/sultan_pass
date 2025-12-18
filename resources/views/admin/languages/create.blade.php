@extends('layouts.dashboard', [
    'title' => __('Add language'),
    'subtitle' => __('Define code and display label for the new language'),
])

@section('content')
    <form method="POST" action="{{ route('admin.languages.store') }}" class="max-w-xl space-y-6">
        @csrf
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-200">{{ __('Language code') }}</label>
            <input name="code" value="{{ old('code') }}" required placeholder="en" class="w-full uppercase rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
            <p class="mt-2 text-xs text-slate-400">{{ __('Use ISO codes, e.g., en, ar, fr.') }}</p>
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-200">{{ __('Display name') }}</label>
            <input name="name" value="{{ old('name') }}" required placeholder="{{ __('Arabic') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
        </div>
        <div class="flex justify-end gap-3 pt-4">
            <a href="{{ route('admin.languages.index') }}" class="rounded-lg border border-white/10 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">
                {{ __('Cancel') }}
            </a>
            <button type="submit" class="rounded-lg bg-emerald-500/90 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-500">
                {{ __('Save language') }}
            </button>
        </div>
    </form>
@endsection

