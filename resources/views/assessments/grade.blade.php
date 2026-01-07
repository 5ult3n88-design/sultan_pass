@extends($layout, [
    'title' => __('Grade Assessment'),
    'subtitle' => __('Grade participant submissions'),
])

@section('content')
    <div class="mx-auto max-w-6xl space-y-6">
        {{-- Assessment Info --}}
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg shadow-black/10">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-white">
                        {{ $assessment->translations->firstWhere('language_id', app()->getLocale())?->title ?? __('Untitled Assessment') }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">
                        {{ __('Scoring mode') }}: 
                        <span class="font-semibold text-uae-gold-300">
                            {{ $assessment->scoring_mode === 'categorical' ? __('Categorical') : __('Percentile') }}
                        </span>
                    </p>
                </div>
                <a href="{{ auth()->user()->role === 'admin' ? route('dashboard.admin') : route('dashboard.manager') }}" 
                   class="rounded-lg border border-white/10 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/10">
                    {{ __('Back') }}
                </a>
            </div>
        </div>

        {{-- Participants List --}}
        <div class="rounded-2xl border border-white/10 bg-white/5 shadow-lg shadow-black/10">
            <div class="border-b border-white/10 p-6">
                <h2 class="text-lg font-semibold text-white">{{ __('Participants') }}</h2>
                <p class="mt-1 text-xs text-slate-400">{{ __('Select a participant to grade their assessment.') }}</p>
            </div>

            @if($participants->isEmpty())
                <div class="p-8 text-center">
                    <p class="text-slate-400">{{ __('No participants have submitted this assessment yet.') }}</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-white/10 bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    {{ __('Participant') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    {{ __('Status') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    {{ __('Progress') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            @foreach($participants as $participant)
                                <tr class="transition hover:bg-white/5">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-white">{{ $participant->name }}</div>
                                        <div class="text-xs text-slate-400">{{ $participant->email }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $statusClass = match($participant->grading_status) {
                                                'graded' => 'bg-green-500/20 text-green-300',
                                                'partial' => 'bg-yellow-500/20 text-yellow-300',
                                                'ungraded' => 'bg-blue-500/20 text-blue-300',
                                                default => 'bg-slate-500/20 text-slate-300',
                                            };
                                            $statusText = match($participant->grading_status) {
                                                'graded' => __('Graded'),
                                                'partial' => __('Partially Graded'),
                                                'ungraded' => __('Ungraded'),
                                                default => __('No Written Questions'),
                                            };
                                        @endphp
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($participant->grading_status !== 'no_written')
                                            <div class="flex items-center gap-2">
                                                <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-700">
                                                    <div class="h-full bg-uae-gold-300 transition-all" 
                                                         style="width: {{ $participant->total_responses > 0 ? ($participant->graded_responses / $participant->total_responses * 100) : 0 }}%">
                                                    </div>
                                                </div>
                                                <span class="text-xs text-slate-400">
                                                    {{ $participant->graded_responses }} / {{ $participant->total_responses }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-400">{{ __('N/A') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('assessments.grade-participant', [$assessment, $participant]) }}"
                                           class="inline-flex items-center rounded-lg bg-uae-gold-300/80 px-4 py-2 text-sm font-semibold text-white transition hover:bg-uae-gold-300">
                                            {{ __('Grade') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
