@extends('layouts.dashboard', [
    'title' => __('Language catalog'),
    'subtitle' => __('Manage localized content availability across the platform'),
])

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <p class="text-sm text-slate-300">
            {{ __('Languages listed here are available for UI and assessment translations.') }}
        </p>
        <a href="{{ route('admin.languages.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-500/80 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-500">
            <span>+</span>
            {{ __('Add language') }}
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
        <table class="min-w-full divide-y divide-white/10 text-sm">
            <thead class="bg-white/5 text-left text-xs uppercase tracking-wide text-slate-300">
                <tr>
                    <th class="px-5 py-3">{{ __('Code') }}</th>
                    <th class="px-5 py-3">{{ __('Name') }}</th>
                    <th class="px-5 py-3">{{ __('Users') }}</th>
                    <th class="px-5 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-slate-100">
                @forelse($languages as $language)
                    <tr>
                        <td class="px-5 py-4 font-semibold uppercase">{{ $language->code }}</td>
                        <td class="px-5 py-4">{{ $language->name }}</td>
                        <td class="px-5 py-4">{{ $language->users_count ?? $language->users()->count() }}</td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.languages.edit', $language) }}" class="rounded-lg border border-white/10 px-3 py-1.5 text-xs font-semibold text-slate-200 transition hover:bg-white/10">
                                {{ __('Edit') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-300">
                            {{ __('No languages have been added yet.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $languages->links() }}
    </div>
@endsection

