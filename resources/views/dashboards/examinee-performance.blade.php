@php
    $layout = auth()->check() && auth()->user()->role === 'participant'
        ? 'layouts.role'
        : 'layouts.dashboard';
@endphp
@extends($layout, [
    'title' => __('Examinee Performance Dashboard'),
    'subtitle' => __('Comprehensive performance analysis and evaluation summary'),
])

@section('content')
<div class="space-y-6">
    {{-- Participant Selector + Info Header --}}
    <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-white">{{ $participant->full_name ?? $participant->username }}</h2>
                <p class="mt-1 text-sm text-slate-400">{{ $participant->email }}</p>
                @if($participant->department)
                    <p class="mt-1 text-xs text-slate-500">{{ __('Department') }}: {{ $participant->department }}</p>
                @endif
            </div>

            {{-- Participant dropdown for admins / managers / assessors --}}
            @if(isset($participantsList) && $participantsList->isNotEmpty())
                <form
                    method="GET"
                    action="{{ route('dashboard.examinee-performance') }}"
                    class="w-full max-w-sm md:w-auto"
                >
                    <label for="participant_id" class="block text-xs font-semibold uppercase tracking-wide text-silver-400 mb-1">
                        {{ __('Select examinee') }}
                    </label>
                    <div class="flex items-center gap-2">
                        <input
                            type="text"
                            id="participant-search"
                            placeholder="{{ __('Search participants...') }}"
                            class="w-full rounded-lg border border-white/10 bg-slate-900/60 px-3 py-2 text-sm text-slate-100 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40"
                        >
                        <select
                            id="participant_id"
                            name="participant_id"
                            class="w-full rounded-lg border border-white/10 bg-slate-900/60 px-3 py-2 text-sm text-slate-100 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40"
                            onchange="this.form.submit()"
                        >
                            <option value="">{{ __('Choose a participant...') }}</option>
                            @foreach($participantsList as $p)
                                <option
                                    value="{{ $p->id }}"
                                    @selected($p->id === $participant->id)
                                >
                                    {{ $p->full_name ?? $p->username }}
                                    @if($p->department)
                                        ({{ $p->department }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            @endif

            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-sm text-slate-400">{{ __('Overall Score') }}</p>
                    <p class="text-3xl font-bold text-uae-gold-300">{{ number_format($overallScore, 1) }}%</p>
                </div>
                {{-- PDF Export Button --}}
                <button onclick="exportToPDF()" class="flex items-center gap-2 rounded-lg border border-uae-gold-300/30 bg-uae-gold-300/10 px-4 py-2 text-sm font-semibold text-uae-gold-100 hover:bg-uae-gold-300/20 transition print:hidden">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ __('Export PDF') }}
                </button>
            </div>
        </div>
    </div>

    <div id="pdf-report" class="space-y-6">
        <div class="hidden js-pdf-only rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-white">{{ $participant->full_name ?? $participant->username }}</h2>
                    <p class="mt-1 text-sm text-slate-400">{{ $participant->email }}</p>
                    @if($participant->department)
                        <p class="mt-1 text-xs text-slate-500">{{ __('Department') }}: {{ $participant->department }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-sm text-slate-400">{{ __('Overall Score') }}</p>
                    <p class="text-3xl font-bold text-uae-gold-300">{{ number_format($overallScore, 1) }}%</p>
                </div>
            </div>
        </div>

    {{-- Key Statistics Cards --}}
    @php
        $totalTests = $testAttempts->count();
        $completedTests = $testAttempts->where('status', 'completed')->count();
        $passedTests = $testAttempts->filter(function($attempt) {
            return $attempt->status === 'completed' && $attempt->score_percentage >= ($attempt->test->passing_marks / $attempt->test->total_marks * 100);
        })->count();
        $passRate = $completedTests > 0 ? ($passedTests / $completedTests) * 100 : 0;
        $avgScore = $testAttempts->where('status', 'completed')->avg('score_percentage') ?? 0;
        $bestScore = $testAttempts->where('status', 'completed')->max('score_percentage') ?? 0;
        $recentAttempt = $testAttempts->sortByDesc('completed_at')->first();
        $totalTimeSpent = $testAttempts->where('status', 'completed')->sum(function($attempt) {
            return $attempt->started_at && $attempt->completed_at
                ? \Carbon\Carbon::parse($attempt->started_at)->diffInMinutes(\Carbon\Carbon::parse($attempt->completed_at))
                : 0;
        });
        $totalTimeSpentDisplay = $totalTimeSpent > 0
            ? round($totalTimeSpent, max(2 - (int) floor(log10(abs($totalTimeSpent))) - 1, 0))
            : 0;
    @endphp
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-6">
        {{-- Total Tests --}}
        <div class="rounded-xl border border-white/10 bg-gradient-to-br from-blue-500/20 to-blue-600/10 p-4 shadow-lg">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-blue-500/20 p-2">
                    <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('Total Tests') }}</p>
                    <p class="text-2xl font-bold text-white">{{ $totalTests }}</p>
                </div>
            </div>
        </div>

        {{-- Completed --}}
        <div class="rounded-xl border border-white/10 bg-gradient-to-br from-emerald-500/20 to-emerald-600/10 p-4 shadow-lg">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-emerald-500/20 p-2">
                    <svg class="h-6 w-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('Completed') }}</p>
                    <p class="text-2xl font-bold text-white">{{ $completedTests }}</p>
                </div>
            </div>
        </div>

        {{-- Pass Rate --}}
        <div class="rounded-xl border border-white/10 bg-gradient-to-br from-uae-gold-400/20 to-uae-gold-500/10 p-4 shadow-lg">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-uae-gold-400/20 p-2">
                    <svg class="h-6 w-6 text-uae-gold-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('Pass Rate') }}</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($passRate, 0) }}%</p>
                </div>
            </div>
        </div>

        {{-- Average Score --}}
        <div class="rounded-xl border border-white/10 bg-gradient-to-br from-purple-500/20 to-purple-600/10 p-4 shadow-lg">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-purple-500/20 p-2">
                    <svg class="h-6 w-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('Avg Score') }}</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($avgScore, 1) }}%</p>
                </div>
            </div>
        </div>

        {{-- Best Score --}}
        <div class="rounded-xl border border-white/10 bg-gradient-to-br from-amber-500/20 to-amber-600/10 p-4 shadow-lg">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-amber-500/20 p-2">
                    <svg class="h-6 w-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('Best Score') }}</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($bestScore, 1) }}%</p>
                </div>
            </div>
        </div>

        {{-- Time Spent --}}
        <div class="rounded-xl border border-white/10 bg-gradient-to-br from-rose-500/20 to-rose-600/10 p-4 shadow-lg">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-rose-500/20 p-2">
                    <svg class="h-6 w-6 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('Time Spent') }}</p>
                    <p class="text-2xl font-bold text-white">{{ $totalTimeSpentDisplay }}{{ __('m') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 4-Chart Grid Layout --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Top-Left: Gauge Chart (Overall Evaluation) --}}
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg">
            <h3 class="mb-4 text-lg font-semibold text-white">{{ __('Overall Evaluation') }}</h3>
            <div class="flex items-center justify-center" style="height: 280px;">
                <canvas id="overallGaugeChart"></canvas>
            </div>
            <div class="mt-4 text-center">
                <p class="text-2xl font-bold text-uae-gold-300">{{ number_format($overallScore, 1) }}%</p>
                <p class="text-sm text-slate-400">{{ __('Based on all completed assessments') }}</p>
            </div>
            {{-- Legend for Gauge Chart --}}
            <div class="mt-4 flex items-center justify-center gap-4 text-xs">
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <span class="text-slate-400">{{ __('Low') }} (&lt;60%)</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 rounded-full bg-orange-500"></div>
                    <span class="text-slate-400">{{ __('Medium') }} (60-79%)</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                    <span class="text-slate-400">{{ __('High') }} (≥80%)</span>
                </div>
            </div>
        </div>

        {{-- Top-Right: Bar Chart (Strengths & Weaknesses) --}}
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg">
            <h3 class="mb-4 text-lg font-semibold text-white">{{ __('Strengths & Weaknesses') }}</h3>
            <div class="h-64">
                <canvas id="categoryBarChart" data-chart-data="{{ json_encode($categoryScores) }}"></canvas>
            </div>
            @if(empty($categoryScores['labels']))
                <p class="mt-4 text-center text-sm text-slate-400">{{ __('No category data available yet.') }}</p>
            @endif
            {{-- Legend for Bar Chart --}}
            <div class="mt-4 flex items-center justify-center gap-4 text-xs">
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <span class="text-slate-400">{{ __('Low') }} (&lt;50%)</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 rounded-full bg-orange-500"></div>
                    <span class="text-slate-400">{{ __('Medium') }} (50-69%)</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                    <span class="text-slate-400">{{ __('High') }} (≥70%)</span>
                </div>
            </div>
        </div>

        {{-- Bottom-Left: IQ Test Results (Percentage Display) --}}
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg">
            <h3 class="mb-4 text-lg font-semibold text-white">{{ __('IQ Test Results') }}</h3>
            @if(!empty($iqTestResults['score']))
                <div class="flex flex-col items-center justify-center h-64">
                    <div class="text-center">
                        <p class="text-sm text-slate-400 mb-2">{{ __('IQ Score') }}</p>
                        <p class="text-6xl font-bold text-uae-gold-300 mb-2">{{ number_format($iqTestResults['score'], 1) }}%</p>
                        <p class="text-xs text-slate-500">{{ __('Out of 100%') }}</p>
                        @if(!empty($iqTestResults['test_name']))
                            <p class="text-sm text-slate-400 mt-4">{{ $iqTestResults['test_name'] }}</p>
                        @endif
                        @if(!empty($iqTestResults['test_date']))
                            <p class="text-xs text-slate-500">{{ $iqTestResults['test_date'] }}</p>
                        @endif
                    </div>
                </div>
            @else
                <div class="flex items-center justify-center h-64">
                    <p class="text-center text-sm text-slate-400">{{ __('No IQ test results available yet.') }}</p>
                </div>
            @endif
            {{-- Legend for IQ Test Results --}}
            <div class="mt-4 flex items-center justify-center gap-4 text-xs">
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <span class="text-slate-400">{{ __('Low') }} (&lt;60%)</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 rounded-full bg-orange-500"></div>
                    <span class="text-slate-400">{{ __('Medium') }} (60-79%)</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                    <span class="text-slate-400">{{ __('High') }} (≥80%)</span>
                </div>
            </div>
        </div>

        {{-- Bottom-Right: Line Chart (Performance Trends) --}}
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg">
            <h3 class="mb-4 text-lg font-semibold text-white">{{ __('Performance Trends') }}</h3>
            <div class="h-64">
                <canvas id="performanceTrendChart" data-chart-data="{{ json_encode($performanceTrends) }}"></canvas>
            </div>
            @if(empty($performanceTrends['labels']))
                <p class="mt-4 text-center text-sm text-slate-400">{{ __('No trend data available yet.') }}</p>
            @endif
            {{-- Legend for Line Chart --}}
            <div class="mt-4 flex items-center justify-center gap-4 text-xs">
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <span class="text-slate-400">{{ __('Low') }} (&lt;60%)</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 rounded-full bg-orange-500"></div>
                    <span class="text-slate-400">{{ __('Medium') }} (60-79%)</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                    <span class="text-slate-400">{{ __('High') }} (≥80%)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Test Results Table --}}
    <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">{{ __('Recent Test Results') }}</h3>
            <span class="text-xs text-slate-400">{{ __('Last :count tests', ['count' => min(5, $testAttempts->count())]) }}</span>
        </div>
        @if($testAttempts->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="pb-3 text-left font-medium text-slate-400">{{ __('Test Name') }}</th>
                            <th class="pb-3 text-center font-medium text-slate-400">{{ __('Type') }}</th>
                            <th class="pb-3 text-center font-medium text-slate-400">{{ __('Score') }}</th>
                            <th class="pb-3 text-center font-medium text-slate-400">{{ __('Result') }}</th>
                            <th class="pb-3 text-right font-medium text-slate-400">{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($testAttempts->sortByDesc('completed_at')->take(5) as $attempt)
                            @php
                                $passingPercentage = $attempt->test->total_marks > 0
                                    ? ($attempt->test->passing_marks / $attempt->test->total_marks) * 100
                                    : 60;
                                $isPassed = $attempt->status === 'completed' && $attempt->score_percentage >= $passingPercentage;
                                $scoreColor = $attempt->score_percentage >= 80 ? 'text-emerald-400' : ($attempt->score_percentage >= 60 ? 'text-amber-400' : 'text-red-400');
                            @endphp
                            <tr class="hover:bg-white/5 transition">
                                <td class="py-3 text-white font-medium">
                                    {{ $attempt->test->title }}
                                </td>
                                <td class="py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $attempt->test->test_type === 'percentile' ? 'bg-purple-500/20 text-purple-300' : 'bg-blue-500/20 text-blue-300' }}">
                                        {{ ucfirst($attempt->test->test_type) }}
                                    </span>
                                </td>
                                <td class="py-3 text-center {{ $scoreColor }} font-bold">
                                    @if($attempt->status === 'completed')
                                        {{ number_format($attempt->score_percentage, 1) }}%
                                    @else
                                        <span class="text-slate-500">{{ __('In Progress') }}</span>
                                    @endif
                                </td>
                                <td class="py-3 text-center">
                                    @if($attempt->status === 'completed')
                                        @if($isPassed)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/20 text-emerald-300">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ __('Passed') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-500/20 text-red-300">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ __('Failed') }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-500/20 text-slate-300">
                                            {{ __('Pending') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 text-right text-slate-400 text-xs">
                                    @if($attempt->completed_at)
                                        {{ \Carbon\Carbon::parse($attempt->completed_at)->format('M d, Y H:i') }}
                                    @elseif($attempt->started_at)
                                        {{ \Carbon\Carbon::parse($attempt->started_at)->format('M d, Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-8 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="mt-2 text-sm text-slate-400">{{ __('No test results available yet.') }}</p>
            </div>
        @endif
    </div>
</div>
</div>

@push('scripts')
{{-- Load Chart.js from CDN as backup --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
function attachSelectSearch(inputId, selectId) {
    const input = document.getElementById(inputId);
    const select = document.getElementById(selectId);
    if (!input || !select) return;

    input.addEventListener('input', () => {
        const term = input.value.toLowerCase();
        Array.from(select.options).forEach((opt, idx) => {
            if (idx === 0) return;
            const label = (opt.textContent || '').toLowerCase();
            opt.hidden = term && !label.includes(term);
        });
    });
}

// Wait for Chart.js to be available
function initPerformanceCharts() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js not loaded');
        setTimeout(initPerformanceCharts, 100);
        return;
    }
    const isDark = document.documentElement.dataset.theme === 'dark';
    const textColor = isDark ? '#C6C6C6' : '#414042';
    const gridColor = isDark ? 'rgba(182, 138, 53, 0.1)' : 'rgba(182, 138, 53, 0.1)';
    const goldColor = '#B68A35';
    const goldColors = ['#B68A35', '#A67A2A', '#8F6A24', '#785A1F', '#614A19'];

    // Gauge Chart (Overall Evaluation)
    const gaugeCtx = document.getElementById('overallGaugeChart');
    if (gaugeCtx) {
        const overallScore = {{ $overallScore }};
        
        // Create a gauge chart using Chart.js with doughnut chart
        new Chart(gaugeCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    label: '{{ __("Overall Score") }}',
                    data: [overallScore, 100 - overallScore],
                    backgroundColor: [
                        overallScore >= 80 ? '#10B981' : overallScore >= 60 ? goldColor : '#EF4444',
                        'rgba(182, 138, 53, 0.1)'
                    ],
                    borderWidth: 0,
                    cutout: '75%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            color: textColor,
                            padding: 10,
                            font: { size: 12 }
                        }
                    },
                    tooltip: {
                        enabled: false
                    }
                }
            },
            plugins: [{
                id: 'gaugeCenter',
                beforeDraw: function(chart) {
                    const ctx = chart.ctx;
                    const centerX = chart.chartArea.left + (chart.chartArea.right - chart.chartArea.left) / 2;
                    const centerY = chart.chartArea.top + (chart.chartArea.bottom - chart.chartArea.top) / 2;
                    
                    ctx.save();
                    ctx.font = 'bold 48px sans-serif';
                    ctx.fillStyle = goldColor;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(overallScore.toFixed(1) + '%', centerX, centerY);
                    ctx.restore();
                }
            }]
        });
    }

    // Bar Chart (Strengths & Weaknesses)
    const categoryBarCtx = document.getElementById('categoryBarChart');
    if (categoryBarCtx) {
        const barData = JSON.parse(categoryBarCtx.dataset.chartData || '{"labels":[],"values":[],"colors":[]}');
        const hasData = barData.labels && barData.labels.length > 0;

        if (hasData) {
            // Sort by value to show strengths (high) and weaknesses (low)
            const sortedData = barData.labels.map((label, index) => ({
                label: label,
                value: barData.values[index],
                color: barData.colors[index] || goldColor
            })).sort((a, b) => b.value - a.value);

            // Color bars: green for strengths (>=70), yellow for medium (50-69), red for weaknesses (<50)
            const barColors = sortedData.map(item => {
                if (item.value >= 70) return '#10B981'; // Green for strengths
                if (item.value >= 50) return '#F59E0B'; // Yellow for medium
                return '#EF4444'; // Red for weaknesses
            });

            new Chart(categoryBarCtx, {
                type: 'bar',
                data: {
                    labels: sortedData.map(item => item.label),
                    datasets: [{
                        label: '{{ __("Category Score") }}',
                        data: sortedData.map(item => item.value),
                        backgroundColor: barColors,
                        borderColor: barColors.map(c => c),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y', // Horizontal bars
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                color: textColor,
                                padding: 10,
                                font: { size: 12 }
                            }
                        },
                        tooltip: {
                            backgroundColor: isDark ? '#414042' : '#FFFFFF',
                            titleColor: textColor,
                            bodyColor: textColor,
                            borderColor: goldColor,
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed.x;
                                    let strengthLevel = '';
                                    if (value >= 70) strengthLevel = ' ({{ __("Strength") }})';
                                    else if (value < 50) strengthLevel = ' ({{ __("Weakness") }})';
                                    return context.dataset.label + ': ' + value + '%' + strengthLevel;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                color: textColor,
                                stepSize: 20
                            },
                            grid: {
                                color: gridColor
                            }
                        },
                        y: {
                            ticks: {
                                color: textColor
                            },
                            grid: {
                                color: gridColor
                            }
                        }
                    }
                }
            });
        } else {
            // Show empty chart
            new Chart(categoryBarCtx, {
                type: 'bar',
                data: {
                    labels: ['{{ __("No data") }}'],
                    datasets: [{
                        label: '{{ __("Category Score") }}',
                        data: [0],
                        backgroundColor: ['#C6C6C6']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
    }

    // Line Chart (Performance Trends)
    const lineCtx = document.getElementById('performanceTrendChart');
    if (lineCtx) {
        const lineData = JSON.parse(lineCtx.dataset.chartData || '{"labels":[],"values":[]}');
        const hasData = lineData.labels && lineData.labels.length > 0;

        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: hasData ? lineData.labels : ['{{ __("No data") }}'],
                datasets: [{
                    label: '{{ __("Average Score") }}',
                    data: hasData ? lineData.values : [0],
                    borderColor: goldColor,
                    backgroundColor: 'rgba(182, 138, 53, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: goldColor,
                    pointBorderColor: '#FFFFFF',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                color: textColor,
                                padding: 10,
                                font: { size: 12 }
                            }
                        },
                    tooltip: {
                        backgroundColor: isDark ? '#414042' : '#FFFFFF',
                        titleColor: textColor,
                        bodyColor: textColor,
                        borderColor: goldColor,
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            color: textColor
                        },
                        grid: {
                            color: gridColor
                        }
                    },
                    x: {
                        ticks: {
                            color: textColor
                        },
                        grid: {
                            color: gridColor
                        }
                    }
                }
            }
        });
    }
}

// Initialize charts when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPerformanceCharts);
    document.addEventListener('DOMContentLoaded', () => attachSelectSearch('participant-search', 'participant_id'));
} else {
    initPerformanceCharts();
    attachSelectSearch('participant-search', 'participant_id');
}

// PDF Export function (server-side PDF with chart images)
function exportToPDF() {
    const btn = document.querySelector('button[onclick="exportToPDF()"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> {{ __("Generating...") }}';
    btn.disabled = true;

    const getChartDataUrl = (id) => {
        const canvas = document.getElementById(id);
        if (!canvas) return '';
        try {
            return canvas.toDataURL('image/png');
        } catch (err) {
            console.warn('Chart export failed:', id, err);
            return '';
        }
    };

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('dashboard.examinee-performance.pdf') }}';
    form.target = '_blank';

    const addInput = (name, value) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value || '';
        form.appendChild(input);
    };

    addInput('_token', '{{ csrf_token() }}');
    addInput('participant_id', '{{ $participant->id }}');
    addInput('chart_overall', getChartDataUrl('overallGaugeChart'));
    addInput('chart_category', getChartDataUrl('categoryBarChart'));
    addInput('chart_trend', getChartDataUrl('performanceTrendChart'));

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 800);
}
</script>
@endpush
@endsection
