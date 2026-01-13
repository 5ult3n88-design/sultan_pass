@extends('layouts.dashboard', [
    'title' => __('Tests'),
    'subtitle' => __('Manage percentile and categorical tests'),
])

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <p class="text-sm text-slate-300">
            {{ __('Create and manage tests for participant assessments.') }}
        </p>
        <a href="{{ route('tests.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-emerald-500/80 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-500">
            <span>+</span>
            {{ __('Create New Test') }}
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
        <table class="min-w-full divide-y divide-white/10 text-sm">
            <thead class="bg-white/5 text-left text-xs uppercase tracking-wide text-slate-300">
                <tr>
                    <th class="px-5 py-3">{{ __('Title') }}</th>
                    <th class="px-5 py-3">{{ __('Type') }}</th>
                    <th class="px-5 py-3">{{ __('Questions') }}</th>
                    <th class="px-5 py-3">{{ __('Status') }}</th>
                    <th class="px-5 py-3">{{ __('Created By') }}</th>
                    <th class="px-5 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-slate-100">
                @forelse($tests as $test)
                    <tr class="hover:bg-white/5">
                        <td class="px-5 py-4">
                            <div>
                                <div class="font-semibold">{{ $test->title }}</div>
                                @if ($test->description)
                                    <div class="text-xs text-slate-400">{{ Str::limit($test->description, 50) }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            @if ($test->test_type === 'percentile')
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-amber-500/20 px-2 py-1 text-xs font-semibold text-amber-300">
                                    {{ __('Percentile') }}
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-emerald-500/20 px-2 py-1 text-xs font-semibold text-emerald-300">
                                    {{ __('Categorical') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-slate-300">{{ $test->questions->count() }}</span>
                            @if ($test->test_type === 'categorical')
                                <span class="text-xs text-slate-400">
                                    · {{ $test->categories->count() }} {{ __('categories') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if ($test->status === 'published')
                                <span
                                    class="inline-flex rounded-full bg-emerald-500/20 px-2 py-1 text-xs font-semibold text-emerald-300">
                                    {{ __('Published') }}
                                </span>
                            @elseif($test->status === 'draft')
                                <span
                                    class="inline-flex rounded-full bg-slate-500/20 px-2 py-1 text-xs font-semibold text-slate-300">
                                    {{ __('Draft') }}
                                </span>
                            @else
                                <span
                                    class="inline-flex rounded-full bg-rose-500/20 px-2 py-1 text-xs font-semibold text-rose-300">
                                    {{ __('Archived') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-slate-300">
                            {{ $test->creator->name ?? 'N/A' }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('tests.show', $test) }}"
                                    class="rounded-lg border border-white/10 px-3 py-1.5 text-xs font-semibold text-slate-200 transition hover:bg-white/10">
                                    {{ __('View') }}
                                </a>
                                <a href="{{ route('tests.edit', $test) }}"
                                    class="rounded-lg border border-amber-500/30 bg-amber-500/10 px-3 py-1.5 text-xs font-semibold text-amber-300 transition hover:bg-amber-500/20">
                                    {{ __('Edit') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-300">
                            {{ __('No tests have been created yet.') }}
                            <a href="{{ route('tests.create') }}" class="text-emerald-400 hover:underline">
                                {{ __('Create your first test') }}
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $tests->links() }}
    </div>
@endsection
