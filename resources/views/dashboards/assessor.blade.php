@extends('layouts.dashboard', [
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

    {{-- Tests Requiring Grading --}}
    @if(isset($testsRequiringGrading) && $testsRequiringGrading->isNotEmpty())
        <div class="mt-6 rounded-2xl border border-white/10 bg-white/5 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-white">{{ __('Tests Requiring Grading') }}</h2>
                    <p class="mt-1 text-xs text-slate-400">{{ __('Participant test submissions awaiting assessment') }}</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-white/10 text-sm">
                    <thead class="bg-white/5 text-left text-xs uppercase tracking-wide text-slate-300">
                        <tr>
                            <th class="px-4 py-3">{{ __('Test') }}</th>
                            <th class="px-4 py-3">{{ __('Participant') }}</th>
                            <th class="px-4 py-3">{{ __('Progress') }}</th>
                            <th class="px-4 py-3">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-slate-100">
                        @foreach($testsRequiringGrading as $item)
                            <tr class="hover:bg-white/5">
                                <td class="px-4 py-3 font-semibold">{{ $item->test_title }}</td>
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="font-semibold">{{ $item->participant_name }}</p>
                                        <p class="text-xs text-slate-400">{{ $item->participant_email }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-xs">
                                        <span class="font-semibold text-blue-400">{{ $item->answered_count }}/{{ $item->total_questions }}</span>
                                        <span class="text-slate-400">{{ __('answered') }}</span>
                                    </div>
                                    @if($item->current_score !== null)
                                        <div class="text-xs text-slate-400 mt-1">
                                            {{ __('Score') }}: {{ $item->current_score }}/{{ $item->total_marks }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold
                                        @if($item->ungraded_count > 0) bg-amber-500/20 text-amber-300
                                        @else bg-emerald-500/20 text-emerald-300 @endif">
                                        @if($item->ungraded_count > 0)
                                            {{ $item->ungraded_count }} {{ __('need grading') }}
                                        @else
                                            {{ __('All graded') }}
                                        @endif
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('tests.grade-assignment', [$item->test_id, $item->assignment_id]) }}"
                                        class="rounded-lg border border-amber-500/30 bg-amber-500/10 px-3 py-1.5 text-xs font-semibold text-amber-300 hover:bg-amber-500/20">
                                        {{ __('Grade') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
