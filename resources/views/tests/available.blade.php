@extends('layouts.role', [
    'title' => __('Available Tests'),
    'subtitle' => __('Choose a published test to begin'),
])

@section('content')
    <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-white">{{ __('Assigned Tests') }}</h2>
            <span class="text-sm text-slate-300">{{ $assignments->count() }} {{ __('available') }}</span>
        </div>
        <div class="mt-6 grid gap-4 md:grid-cols-2">
            @forelse($assignments as $assignment)
                @php
                    $test = $assignment->test;
                @endphp
                <div class="rounded-xl border border-white/5 bg-slate-900/40 p-4 flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <p class="text-lg font-semibold text-white">{{ $test->title }}</p>
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold
                                @class([
                                    'bg-amber-500/20 text-amber-200' => $test->test_type === 'percentile',
                                    'bg-emerald-500/20 text-emerald-200' => $test->test_type === 'categorical',
                                ])">
                                {{ ucfirst($test->test_type) }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400">
                            {{ __('Duration') }}: {{ $test->duration_minutes ?? __('Flexible') }} {{ __('mins') }}
                        </p>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('tests.take', $test) }}"
                            class="inline-flex items-center justify-center rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">
                            {{ __('Start Test') }}
                        </a>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-300">{{ __('No assigned tests are available yet.') }}</p>
            @endforelse
        </div>
    </div>
@endsection
