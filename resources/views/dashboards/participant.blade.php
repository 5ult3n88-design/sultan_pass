@extends('layouts.role', [
    'title' => __('Candidate Hub'),
    'subtitle' => __('Complete assessments and review your progress'),
])

@section('content')
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Left: My current assessments --}}
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
            <h2 class="text-lg font-semibold text-white">{{ __('My assessments') }}</h2>
            <p class="mt-1 text-xs text-slate-400">{{ __('Active and recently completed tasks') }}</p>
            <ul class="mt-6 space-y-3 text-sm text-slate-200">
                @forelse($assignments as $assignment)
                    <li class="rounded-xl border border-white/5 bg-slate-900/40 px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold capitalize">
                                    {{ str_replace('_', ' ', $assignment->type ?? 'assessment') }}
                                </p>
                                <p class="mt-1 text-xs text-slate-400">
                                    {{ __('Score') }}:
                                    {{ $assignment->score !== null ? number_format($assignment->score, 1) . '%' : __('Pending') }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold
                                    @class([
                                        'bg-uae-gold-300/20 text-uae-gold-200' => $assignment->status === 'in_progress',
                                        'bg-emerald-500/20 text-emerald-200' => $assignment->status === 'completed',
                                        'bg-amber-500/20 text-amber-200' => $assignment->status === 'invited',
                                        'bg-rose-500/20 text-rose-200' => $assignment->status === 'withdrawn',
                                    ])">
                                    {{ ucfirst(str_replace('_', ' ', $assignment->status)) }}
                                </span>

                                @php
                                    $actionLabel = match ($assignment->status) {
                                        'completed' => __('View results'),
                                        'in_progress' => __('Continue'),
                                        default => __('Start'),
                                    };
                                @endphp

                                <a
                                    href="{{ route('assessments.take', $assignment->assessment_id) }}"
                                    class="rounded-lg bg-uae-gold-300/90 px-3 py-1.5 text-xs font-semibold text-slate-900 hover:bg-uae-gold-200"
                                >
                                    {{ $actionLabel }}
                                </a>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="rounded-xl border border-dashed border-white/10 px-4 py-6 text-center text-slate-400">
                        {{ __('You have no assigned assessments at the moment.') }}
                    </li>
                @endforelse
            </ul>
        </div>

        {{-- Right: Available assessments to start --}}
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
            <h2 class="text-lg font-semibold text-white">{{ __('Available assessments') }}</h2>
            <p class="mt-1 text-xs text-slate-400">
                {{ __('Assessments that are open for you to start now') }}
            </p>
            <ul class="mt-6 space-y-3 text-sm text-slate-200">
                @forelse($availableAssessments as $assessment)
                    <li class="rounded-xl border border-white/5 bg-slate-900/40 px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-white">
                                    {{ $assessment->title ?? ucfirst(str_replace('_', ' ', $assessment->type ?? 'assessment')) }}
                                </p>
                                @if($assessment->start_date || $assessment->end_date)
                                    <p class="mt-1 text-xs text-slate-400">
                                        @if($assessment->start_date)
                                            {{ __('From') }} {{ optional($assessment->start_date)->format('Y-m-d') }}
                                        @endif
                                        @if($assessment->end_date)
                                            {{ __('to') }} {{ optional($assessment->end_date)->format('Y-m-d') }}
                                        @endif
                                    </p>
                                @endif
                            </div>
                            <a
                                href="{{ route('assessments.take', $assessment->id) }}"
                                class="rounded-lg bg-emerald-500/90 px-3 py-1.5 text-xs font-semibold text-slate-900 hover:bg-emerald-400"
                            >
                                {{ __('Start') }}
                            </a>
                        </div>
                    </li>
                @empty
                    <li class="rounded-xl border border-dashed border-white/10 px-4 py-6 text-center text-slate-400">
                        {{ __('There are no new assessments available for you at the moment.') }}
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
