<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ __('Examinee Performance Report') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        .header { border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px; }
        .title { font-size: 20px; font-weight: 700; margin: 0; }
        .muted { color: #64748b; }
        .grid { display: flex; flex-wrap: wrap; gap: 10px; }
        .card { border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; flex: 1 1 140px; }
        .card-label { font-size: 10px; text-transform: uppercase; letter-spacing: .04em; color: #64748b; margin-bottom: 4px; }
        .card-value { font-size: 18px; font-weight: 700; color: #0f172a; }
        .section { margin-top: 16px; }
        .section h3 { font-size: 14px; margin: 0 0 8px; }
        .charts { display: flex; flex-wrap: wrap; gap: 12px; }
        .chart-box { border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; flex: 1 1 240px; }
        .chart-title { font-size: 12px; font-weight: 600; margin-bottom: 8px; }
        img.chart { width: 100%; height: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border-bottom: 1px solid #e2e8f0; padding: 8px 6px; text-align: left; }
        th { font-size: 11px; color: #475569; }
        .right { text-align: right; }
        .center { text-align: center; }
    </style>
</head>
<body>
@php
    $totalTests = $testAttempts->count();
    $completedTests = $testAttempts->where('status', 'completed')->count();
    $passedTests = $testAttempts->filter(function($attempt) {
        return $attempt->status === 'completed' && $attempt->score_percentage >= ($attempt->test->passing_marks / $attempt->test->total_marks * 100);
    })->count();
    $passRate = $completedTests > 0 ? ($passedTests / $completedTests) * 100 : 0;
    $avgScore = $testAttempts->where('status', 'completed')->avg('score_percentage') ?? 0;
    $bestScore = $testAttempts->where('status', 'completed')->max('score_percentage') ?? 0;
    $totalTimeSpent = $testAttempts->where('status', 'completed')->sum(function($attempt) {
        return $attempt->started_at && $attempt->completed_at
            ? \Carbon\Carbon::parse($attempt->started_at)->diffInMinutes(\Carbon\Carbon::parse($attempt->completed_at))
            : 0;
    });
    $totalTimeSpentDisplay = $totalTimeSpent > 0
        ? round($totalTimeSpent, max(2 - (int) floor(log10(abs($totalTimeSpent))) - 1, 0))
        : 0;
@endphp

<div class="header">
    <h1 class="title">{{ __('Examinee Performance Report') }}</h1>
    <p class="muted">
        {{ $participant->full_name ?? $participant->username }} · {{ $participant->email }}
        @if($participant->department)
            · {{ __('Department') }}: {{ $participant->department }}
        @endif
    </p>
    <p class="muted">{{ __('Generated') }}: {{ date('Y-m-d') }}</p>
</div>

<div class="grid">
    <div class="card">
        <div class="card-label">{{ __('Overall Score') }}</div>
        <div class="card-value">{{ number_format($overallScore, 1) }}%</div>
    </div>
    <div class="card">
        <div class="card-label">{{ __('Total Tests') }}</div>
        <div class="card-value">{{ $totalTests }}</div>
    </div>
    <div class="card">
        <div class="card-label">{{ __('Completed') }}</div>
        <div class="card-value">{{ $completedTests }}</div>
    </div>
    <div class="card">
        <div class="card-label">{{ __('Pass Rate') }}</div>
        <div class="card-value">{{ number_format($passRate, 1) }}%</div>
    </div>
    <div class="card">
        <div class="card-label">{{ __('Avg Score') }}</div>
        <div class="card-value">{{ number_format($avgScore, 1) }}%</div>
    </div>
    <div class="card">
        <div class="card-label">{{ __('Best Score') }}</div>
        <div class="card-value">{{ number_format($bestScore, 1) }}%</div>
    </div>
    <div class="card">
        <div class="card-label">{{ __('Time Spent') }}</div>
        <div class="card-value">{{ $totalTimeSpentDisplay }}{{ __('m') }}</div>
    </div>
</div>

<div class="section">
    <h3>{{ __('Charts') }}</h3>
    <div class="charts">
        <div class="chart-box">
            <div class="chart-title">{{ __('Overall Evaluation') }}</div>
            @if(!empty($chartImages['overall']))
                <img class="chart" src="{{ $chartImages['overall'] }}" alt="Overall chart">
            @else
                <p class="muted">{{ __('Chart not available') }}</p>
            @endif
        </div>
        <div class="chart-box">
            <div class="chart-title">{{ __('Strengths & Weaknesses') }}</div>
            @if(!empty($chartImages['category']))
                <img class="chart" src="{{ $chartImages['category'] }}" alt="Category chart">
            @else
                <p class="muted">{{ __('Chart not available') }}</p>
            @endif
        </div>
        <div class="chart-box">
            <div class="chart-title">{{ __('Performance Trends') }}</div>
            @if(!empty($chartImages['trend']))
                <img class="chart" src="{{ $chartImages['trend'] }}" alt="Trend chart">
            @else
                <p class="muted">{{ __('Chart not available') }}</p>
            @endif
        </div>
    </div>
</div>

<div class="section">
    <h3>{{ __('Recent Test Results') }}</h3>
    @if($testAttempts->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>{{ __('Test Name') }}</th>
                    <th class="center">{{ __('Type') }}</th>
                    <th class="center">{{ __('Score') }}</th>
                    <th class="center">{{ __('Result') }}</th>
                    <th class="right">{{ __('Date') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($testAttempts->sortByDesc('completed_at')->take(5) as $attempt)
                    @php
                        $passingPercentage = $attempt->test->total_marks > 0
                            ? ($attempt->test->passing_marks / $attempt->test->total_marks) * 100
                            : 60;
                        $isPassed = $attempt->status === 'completed' && $attempt->score_percentage >= $passingPercentage;
                    @endphp
                    <tr>
                        <td>{{ $attempt->test->title }}</td>
                        <td class="center">{{ ucfirst($attempt->test->test_type) }}</td>
                        <td class="center">
                            @if($attempt->status === 'completed')
                                {{ number_format($attempt->score_percentage, 1) }}%
                            @else
                                {{ __('In Progress') }}
                            @endif
                        </td>
                        <td class="center">
                            @if($attempt->status === 'completed')
                                {{ $isPassed ? __('Passed') : __('Failed') }}
                            @else
                                {{ __('Pending') }}
                            @endif
                        </td>
                        <td class="right">
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
    @else
        <p class="muted">{{ __('No test results available yet.') }}</p>
    @endif
</div>
</body>
</html>
