@extends('layouts.survey', [
    'title' => $assessment->translations->first()->title ?? __('Assessment'),
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
        <p class="mt-2 text-[11px] text-slate-400">
            {{ __('Question :current of :total', ['current' => $index, 'total' => $totalQuestions]) }}
        </p>
    </div>

    <div class="grid gap-6 lg:grid-cols-[2fr,1fr]">
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
            <form
                method="POST"
                action="{{ route('assessments.store-response', $assessment) }}"
                class="space-y-6"
            >
                @csrf
                <input type="hidden" name="question_id" value="{{ $currentQuestion->id }}">
                <input type="hidden" name="index" value="{{ $index }}">

                <article>
                    <header>
                        <p class="text-xs uppercase tracking-wide text-uae-gold-300">
                            {{ __('Question :number', ['number' => $index]) }}
                        </p>
                        <h2 class="mt-2 text-lg font-semibold text-white">
                            {{ $currentQuestion->translated_text ?? $currentQuestion->question_text }}
                        </h2>
                    </header>

                    <div class="mt-4 space-y-3">
                        @if($currentQuestion->question_type === 'mcq')
                            <div class="grid gap-3">
                                @php
                                    $selectedIds = $existingResponse?->selected_answer_ids ?? [];
                                @endphp
                                @foreach($answers as $answer)
                                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-100 hover:bg-white/10">
                                        <input
                                            type="checkbox"
                                            name="selected_answers[]"
                                            value="{{ $answer->id }}"
                                            class="h-4 w-4 rounded border-white/20 bg-white/5 text-uae-gold-300 focus:ring-uae-gold-300"
                                            @checked(in_array($answer->id, $selectedIds ?? []))
                                        >
                                        <span>{{ $answer->translated_text ?? $answer->answer_text }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <textarea
                                name="written_response_text"
                                rows="6"
                                class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40"
                                placeholder="{{ __('Write your response here...') }}"
                            >{{ old('written_response_text', $existingResponse->written_response_text ?? '') }}</textarea>
                        @endif
                    </div>
                </article>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-white/5">
                    <div class="flex flex-wrap items-center gap-3">
                        @if($index > 1)
                            <button
                                type="submit"
                                name="action"
                                value="previous"
                                class="rounded-lg border border-white/15 px-4 py-2 text-xs font-semibold text-slate-200 hover:bg-white/10"
                            >
                                {{ __('Previous') }}
                            </button>
                        @endif

                        @if($index < $totalQuestions)
                            <button
                                type="submit"
                                name="action"
                                value="next"
                                class="rounded-lg bg-uae-gold-300/90 px-4 py-2 text-xs font-semibold text-slate-900 hover:bg-uae-gold-200"
                            >
                                {{ __('Next') }}
                            </button>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button
                            type="submit"
                            name="action"
                            value="mark_for_review"
                            class="rounded-lg border border-amber-400/60 px-4 py-2 text-xs font-semibold text-amber-200 hover:bg-amber-500/10"
                        >
                            {{ __('Mark for review & next') }}
                        </button>

                        @if($index === $totalQuestions)
                            <button
                                type="submit"
                                name="action"
                                value="submit"
                                class="rounded-lg bg-emerald-500/90 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-400"
                            >
                                {{ __('Submit assessment') }}
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
        <div class="space-y-6">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h3 class="text-sm font-semibold text-white">{{ __('Question navigator') }}</h3>
                <div class="mt-4 grid grid-cols-5 gap-2 text-sm">
                    @for($i = 1; $i <= $totalQuestions; $i++)
                        <a
                            href="{{ route('assessments.take', ['assessment' => $assessment->id, 'q' => $i]) }}"
                            class="flex h-10 items-center justify-center rounded-lg border text-sm font-semibold
                                @if($i === $index)
                                    border-uae-gold-300 bg-uae-gold-300/20 text-uae-gold-100
                                @else
                                    border-white/10 bg-white/5 text-slate-200 hover:bg-white/10
                                @endif"
                        >
                            {{ $i }}
                        </a>
                    @endfor
                </div>
                <div class="mt-4 space-y-2 text-xs text-slate-400">
                    <div class="flex items-center gap-2">
                        <span class="inline-block h-3 w-3 rounded bg-uae-gold-300/70"></span>
                        {{ __('Current question') }}
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h3 class="text-sm font-semibold text-white">{{ __('Need a break?') }}</h3>
                <p class="mt-2 text-xs text-slate-300">
                    {{ __('Your answers are saved each time you click Next, Previous, or Mark for review.') }}
                </p>
                <p class="mt-2 text-xs text-slate-400">
                    {{ __('You can safely close this tab and resume the assessment later from your dashboard.') }}
                </p>
            </div>
        </div>
    </div>
@endsection

