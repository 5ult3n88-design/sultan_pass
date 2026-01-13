@extends('layouts.dashboard', [
    'title' => __('Test Submissions'),
    'subtitle' => __('View and grade test submissions'),
])

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $test->title }}</h1>
            <p class="mt-1 text-sm text-slate-400">{{ __('Grade test submissions') }}</p>
        </div>
        <a href="{{ route('tests.show', $test) }}"
            class="rounded-lg border border-white/20 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-white/5">
            {{ __('Back to Test') }}
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl border border-white/10 bg-white/5 overflow-hidden">
        <table class="min-w-full divide-y divide-white/10 text-sm">
            <thead class="bg-white/5 text-left text-xs uppercase tracking-wide text-slate-300">
                <tr>
                    <th class="px-5 py-3">{{ __('Participant') }}</th>
                    <th class="px-5 py-3">{{ __('Status') }}</th>
                    <th class="px-5 py-3">{{ __('Submitted At') }}</th>
                    @if($test->isPercentile())
                        <th class="px-5 py-3">{{ __('Score') }}</th>
                        <th class="px-5 py-3">{{ __('Result') }}</th>
                    @else
                        <th class="px-5 py-3">{{ __('Category') }}</th>
                    @endif
                    <th class="px-5 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-slate-100">
                @forelse($assignments as $assignment)
                    <tr class="hover:bg-white/5">
                        <td class="px-5 py-4">
                            <div class="font-semibold">{{ $assignment->participant->name ?? $assignment->participant->username }}</div>
                            <div class="text-xs text-slate-400">{{ $assignment->participant->email }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="rounded-full px-2 py-1 text-xs font-semibold
                                @if($assignment->status === 'in_progress') bg-amber-500/20 text-amber-300
                                @elseif($assignment->status === 'submitted') bg-emerald-500/20 text-emerald-300
                                @else bg-slate-500/20 text-slate-300 @endif">
                                {{ ucfirst(str_replace('_', ' ', $assignment->status)) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-slate-300">
                            {{ $assignment->testResult?->completed_at?->format('Y-m-d H:i') ?? '-' }}
                        </td>
                        @if($test->isPercentile())
                            <td class="px-5 py-4">
                                @if($assignment->testResult)
                                    <span class="font-semibold text-white">
                                        {{ $assignment->testResult->total_marks_obtained ?? 0 }}/{{ $test->total_marks }}
                                    </span>
                                    <span class="text-xs text-slate-400">
                                        ({{ $assignment->testResult->percentage ?? 0 }}%)
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($assignment->testResult?->result_status)
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold
                                        @if($assignment->testResult->result_status === 'pass') bg-emerald-500/20 text-emerald-300
                                        @else bg-rose-500/20 text-rose-300 @endif">
                                        {{ ucfirst($assignment->testResult->result_status) }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        @else
                            <td class="px-5 py-4">
                                @if($assignment->testResult?->dominantCategory)
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold text-slate-900"
                                        style="background-color: {{ $assignment->testResult->dominantCategory->color ?? '#e5b453' }}">
                                        {{ $assignment->testResult->dominantCategory->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        @endif
                        <td class="px-5 py-4 text-right">
                            @if($assignment->isCompleted())
                                <a href="{{ route('tests.grade-assignment', [$test, $assignment]) }}"
                                    class="rounded-lg border border-amber-500/30 bg-amber-500/10 px-3 py-1.5 text-xs font-semibold text-amber-300 hover:bg-amber-500/20">
                                    {{ $assignment->needsGrading() ? __('Grade') : __('View') }}
                                </a>
                            @else
                                <span class="text-xs text-slate-400">{{ __('Not submitted') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-300">
                            {{ __('No submissions yet.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $assignments->links() }}
    </div>
@endsection
