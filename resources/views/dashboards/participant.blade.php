@extends('layouts.role', [
    'title' => __('Candidate Hub'),
    'subtitle' => __('Complete assessments and review your progress'),
])

@section('content')
    <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
        <h2 class="text-lg font-semibold text-white">{{ __('My assessments') }}</h2>
        <p class="mt-1 text-xs text-slate-400">{{ __('Active and recently completed tasks') }}</p>
        <ul class="mt-6 space-y-3 text-sm text-slate-200">
            @forelse($assignments as $assignment)
                <li class="rounded-xl border border-white/5 bg-slate-900/40 px-4 py-3">
                    <div class="flex items-center justify-between">
                        <p class="font-semibold capitalize">{{ str_replace('_', ' ', $assignment->type ?? 'assessment') }}</p>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold
                            @class([
                                'bg-uae-gold-300/20 text-uae-gold-200' => $assignment->status === 'in_progress',
                                'bg-emerald-500/20 text-emerald-200' => $assignment->status === 'completed',
                                'bg-amber-500/20 text-amber-200' => $assignment->status === 'invited',
                                'bg-rose-500/20 text-rose-200' => $assignment->status === 'withdrawn',
                            ])">
                            {{ ucfirst(str_replace('_', ' ', $assignment->status)) }}
                        </span>
                    </div>
                    <p class="mt-2 text-xs text-slate-400">
                        {{ __('Score') }}: {{ $assignment->score ?? __('Pending') }}
                    </p>
                </li>
            @empty
                <li class="rounded-xl border border-dashed border-white/10 px-4 py-6 text-center text-slate-400">
                    {{ __('You have no assigned assessments at the moment.') }}
                </li>
            @endforelse
        </ul>
    </div>

    <div class="mt-6 rounded-2xl border border-white/10 bg-white/5 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-white">{{ __('Available Tests') }}</h2>
                <p class="mt-1 text-xs text-slate-400">{{ __('Published exams you can take now') }}</p>
            </div>
            <a href="{{ route('tests.available') }}"
                class="text-sm font-semibold text-amber-300 hover:text-amber-200">
                {{ __('View all') }} →
            </a>
        </div>
        <ul class="mt-6 space-y-3 text-sm text-slate-200">
            @forelse($availableTests as $test)
                <li class="rounded-xl border border-white/5 bg-slate-900/40 px-4 py-3 flex items-center justify-between">
                    <div>
                        <p class="font-semibold">{{ $test->title }}</p>
                        <p class="text-xs text-slate-400 mt-1">
                            {{ ucfirst($test->test_type) }} • {{ __('Duration') }}: {{ $test->duration_minutes ?? __('Flexible') }} {{ __('mins') }}
                        </p>
                    </div>
                    <a href="{{ route('tests.take', $test) }}"
                        class="rounded-lg bg-emerald-500 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-600">
                        {{ __('Start') }}
                    </a>
                </li>
            @empty
                <li class="rounded-xl border border-dashed border-white/10 px-4 py-6 text-center text-slate-400">
                    {{ __('No tests are published yet.') }}
                </li>
            @endforelse
        </ul>
    </div>
@endsection
