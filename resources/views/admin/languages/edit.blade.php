@extends('layouts.dashboard', [
    'title' => __('Edit language'),
    'subtitle' => __('Update code or display label'),
])

@section('content')
    <form method="POST" action="{{ route('admin.languages.update', $language) }}" class="max-w-xl space-y-6">
        @csrf
        @method('PUT')
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-200">{{ __('Language code') }}</label>
            <input name="code" value="{{ old('code', $language->code) }}" required class="w-full uppercase rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-200">{{ __('Display name') }}</label>
            <input name="name" value="{{ old('name', $language->name) }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
        </div>
        <div class="flex justify-end gap-3 pt-4">
            <a href="{{ route('admin.languages.index') }}" class="rounded-lg border border-white/10 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">
                {{ __('Back') }}
            </a>
            <button type="submit" class="rounded-lg bg-uae-gold-300/90 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-uae-gold-300">
                {{ __('Save changes') }}
            </button>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.languages.destroy', $language) }}" class="mt-6 max-w-xl" onsubmit="return confirm('{{ __('Delete this language?') }}');">
        @csrf
        @method('DELETE')
        <button class="rounded-lg border border-rose-500/40 bg-rose-500/20 px-4 py-2 text-sm font-semibold text-rose-100 transition hover:bg-rose-500/30">
            {{ __('Delete language') }}
        </button>
    </form>
@endsection

