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
</div>

@push('scripts')
{{-- Load Chart.js from CDN as backup --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
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
} else {
    initPerformanceCharts();
}

// PDF Export function
function exportToPDF() {
    // Load html2pdf library dynamically
    if (typeof html2pdf === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
        script.onload = generatePDF;
        document.head.appendChild(script);
    } else {
        generatePDF();
    }
}

function generatePDF() {
    const element = document.querySelector('.space-y-6');
    const participantName = '{{ $participant->full_name ?? $participant->username }}';
    const date = new Date().toISOString().split('T')[0];

    const opt = {
        margin: [10, 10, 10, 10],
        filename: `Performance_Report_${participantName.replace(/\s+/g, '_')}_${date}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: {
            scale: 2,
            useCORS: true,
            logging: false,
            backgroundColor: '#1a1a2e'
        },
        jsPDF: {
            unit: 'mm',
            format: 'a4',
            orientation: 'portrait'
        },
        pagebreak: { mode: 'avoid-all' }
    };

    // Show loading state
    const btn = document.querySelector('button[onclick="exportToPDF()"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> {{ __("Generating...") }}';
    btn.disabled = true;

    html2pdf().set(opt).from(element).save().then(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }).catch(err => {
        console.error('PDF generation error:', err);
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('{{ __("Error generating PDF. Please try again.") }}');
    });
}
</script>
@endpush
@endsection
