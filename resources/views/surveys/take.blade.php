@extends('layouts.survey', [
    'title' => __('Leadership Simulation'),
    'timeRemaining' => $timeRemaining ?? null,
])

@section('content')
    <div class="mb-6">
        <div class="flex items-center justify-between text-xs text-slate-400">
            <span>{{ __('Progress') }}</span>
            <span>{{ $progress }}%</span>
        </div>
        <div class="mt-2 h-2 rounded-full bg-slate-800">
            <div class="h-full rounded-full bg-gradient-to-r from-uae-gold-300 to-uae-gold-500" style="width: {{ $progress }}%"></div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[2fr,1fr]">
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
            @foreach($questions as $question)
                <article class="mb-8 last:mb-0">
                    <header>
                        <p class="text-xs uppercase tracking-wide text-uae-gold-300">{{ __('Question :number', ['number' => $loop->iteration]) }}</p>
                        <h2 class="mt-2 text-lg font-semibold text-white">{{ $question['title'] }}</h2>
                        <p class="mt-2 text-sm text-slate-300">{{ $question['prompt'] }}</p>
                    </header>

                    <div class="mt-4 space-y-3">
                        @switch($question['type'])
                            @case('scale')
                                <div class="flex items-center gap-4">
                                    @foreach($question['options'] as $option)
                                        <label class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full border border-white/10 bg-white/5 text-sm font-semibold text-slate-200 hover:bg-white/10">
                                            <input type="radio" name="question_{{ $question['id'] }}" value="{{ $option }}" class="sr-only">
                                            {{ $option }}
                                        </label>
                                    @endforeach
                                </div>
                                @break
                            @case('multiple_choice')
                                <div class="grid gap-3">
                                    @foreach($question['options'] as $option)
                                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-100 hover:bg-white/10">
                                            <input type="checkbox" class="h-4 w-4 rounded border-white/20 bg-white/5 text-uae-gold-300 focus:ring-uae-gold-300">
                                            <span>{{ $option }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @break
                            @case('essay')
                                <textarea rows="5" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40" placeholder="{{ __('Write your response here...') }}"></textarea>
                                @break
                        @endswitch
                    </div>
                </article>
            @endforeach
        </div>
        <div class="space-y-6">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h3 class="text-sm font-semibold text-white">{{ __('Question navigator') }}</h3>
                <div class="mt-4 grid grid-cols-5 gap-2 text-sm">
                    @foreach($questions as $question)
                        <button class="h-10 rounded-lg border border-white/10 bg-white/5 text-sm font-semibold text-slate-200 hover:bg-white/10">
                            {{ $loop->iteration }}
                        </button>
                    @endforeach
                </div>
                <div class="mt-4 space-y-2 text-xs text-slate-400">
                    <div class="flex items-center gap-2">
                        <span class="inline-block h-3 w-3 rounded bg-uae-gold-300/70"></span>
                        {{ __('Current') }}
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-block h-3 w-3 rounded border border-white/30"></span>
                        {{ __('Unanswered') }}
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h3 class="text-sm font-semibold text-white">{{ __('Need a break?') }}</h3>
                <p class="mt-2 text-xs text-slate-300">
                    {{ __('Your progress is saved automatically. You can pause now and resume within 24 hours.') }}
                </p>
                <div class="mt-4 flex flex-col gap-3 text-sm">
                    <button class="rounded-lg border border-white/10 px-4 py-2 text-slate-200 hover:bg-white/10">
                        {{ __('Save & pause') }}
                    </button>
                    <button class="rounded-lg bg-emerald-500/80 px-4 py-2 font-semibold text-white hover:bg-emerald-500">
                        {{ __('Submit assessment') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

