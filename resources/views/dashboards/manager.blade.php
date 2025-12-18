@extends('layouts.role', [
    'title' => __('Manager Dashboard'),
    'subtitle' => __('Monitor team readiness and upcoming assessments'),
])

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">{{ __('Create assessments') }}</h2>
            <p class="text-xs text-slate-500">{{ __('Design new tests tailored to your team’s development goals.') }}</p>
        </div>
        <a href="{{ route('assessments.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-uae-gold-300/90 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-uae-gold-300">
            <span class="text-base">+</span>
            {{ __('Create assessment') }}
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
            <h2 class="text-lg font-semibold text-white">{{ __('Team members') }}</h2>
            <p class="mt-1 text-xs text-slate-400">{{ __('Participants reporting to you') }}</p>
            <ul class="mt-6 space-y-3 text-sm text-slate-200">
                @forelse($teamMembers as $member)
                    <li class="flex items-center justify-between rounded-xl border border-white/5 bg-slate-900/40 px-4 py-3">
                        <div>
                            <p class="font-semibold">{{ $member->full_name ?? __('Unassigned name') }}</p>
                            <p class="text-xs text-slate-400">{{ $member->department ?? __('No department') }}</p>
                        </div>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $member->status === 'active' ? 'bg-emerald-500/20 text-emerald-200' : 'bg-rose-500/20 text-rose-200' }}">
                            {{ ucfirst($member->status) }}
                        </span>
                    </li>
                @empty
                    <li class="rounded-xl border border-dashed border-white/10 px-4 py-6 text-center text-slate-400">
                        {{ __('No team members have been assigned yet.') }}
                    </li>
                @endforelse
            </ul>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
            <h2 class="text-lg font-semibold text-white">{{ __('Upcoming assessments') }}</h2>
            <p class="mt-1 text-xs text-slate-400">{{ __('Key events scheduled for your team') }}</p>
            <ul class="mt-6 space-y-3 text-sm text-slate-200">
                @forelse($upcomingAssessments as $assessment)
                    <li class="rounded-xl border border-white/5 bg-slate-900/40 px-4 py-3">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold capitalize">{{ str_replace('_', ' ', $assessment->type) }}</p>
                            <span class="rounded-full bg-uae-gold-300/20 px-2.5 py-0.5 text-xs font-semibold text-uae-gold-200">
                                {{ ucfirst($assessment->status) }}
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-slate-400">
                            {{ __('Starts') }}: {{ optional($assessment->start_date)->format('Y-m-d') ?? __('TBD') }} ·
                            {{ __('Ends') }}: {{ optional($assessment->end_date)->format('Y-m-d') ?? __('TBD') }}
                        </p>
                    </li>
                @empty
                    <li class="rounded-xl border border-dashed border-white/10 px-4 py-6 text-center text-slate-400">
                        {{ __('No assessments scheduled. Create one to begin tracking progress.') }}
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
