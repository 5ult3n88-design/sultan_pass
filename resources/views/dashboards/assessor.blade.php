@extends('layouts.role', [
    'title' => __('Assessor Command Center'),
    'subtitle' => __('Review assigned assessments and track pending evaluations'),
])

@section('content')
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
            <h2 class="text-lg font-semibold text-white">{{ __('Assigned assessments') }}</h2>
            <p class="mt-1 text-xs text-slate-400">{{ __('Assessments queued for scoring') }}</p>
            <ul class="mt-6 space-y-3 text-sm text-slate-200">
                @forelse($assignedAssessments as $assessment)
                    <li class="rounded-xl border border-white/5 bg-slate-900/40 px-4 py-3">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold capitalize">{{ str_replace('_', ' ', $assessment->type) }}</p>
                            <span class="rounded-full bg-uae-gold-300/20 px-2.5 py-0.5 text-xs font-semibold text-uae-gold-200">
                                {{ ucfirst($assessment->status) }}
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-slate-400">
                            {{ __('Starts') }}: {{ optional($assessment->start_date)->format('Y-m-d') ?? __('TBD') }}
                        </p>
                    </li>
                @empty
                    <li class="rounded-xl border border-dashed border-white/10 px-4 py-6 text-center text-slate-400">
                        {{ __('No assessments have been assigned yet.') }}
                    </li>
                @endforelse
            </ul>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
            <h2 class="text-lg font-semibold text-white">{{ __('Pending evaluations') }}</h2>
            <p class="mt-1 text-xs text-slate-400">{{ __('Candidates awaiting scoring or feedback') }}</p>
            <ul class="mt-6 space-y-3 text-sm text-slate-200">
                @forelse($pendingEvaluations as $evaluation)
                    <li class="rounded-xl border border-white/5 bg-slate-900/40 px-4 py-3">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold capitalize">{{ str_replace('_', ' ', $evaluation->type ?? 'assessment') }}</p>
                            <span class="rounded-full bg-amber-500/20 px-2.5 py-0.5 text-xs font-semibold text-amber-200">
                                {{ ucfirst($evaluation->status) }}
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-slate-400">
                            {{ __('Score to date') }}: {{ $evaluation->score ?? __('Not started') }}
                        </p>
                    </li>
                @empty
                    <li class="rounded-xl border border-dashed border-white/10 px-4 py-6 text-center text-slate-400">
                        {{ __('Great job! No pending evaluations right now.') }}
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
