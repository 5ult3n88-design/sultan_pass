@extends('layouts.role', [
    'title' => __('Take Test'),
    'subtitle' => __('Answer in English and Arabic as needed, then submit'),
])

@section('content')
    <div class="mb-6 rounded-2xl border border-white/10 bg-white/5 p-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $test->title }}</h1>
                @if($test->title_ar)
                    <p class="text-lg font-semibold text-amber-200" dir="rtl" lang="ar">{{ $test->title_ar }}</p>
                @endif
                <p class="mt-2 text-sm text-slate-300">{{ ucfirst($test->test_type) }} • {{ __('Duration') }}: {{ $test->duration_minutes ?? __('Flexible') }} {{ __('mins') }}</p>
                @if($test->description)
                    <p class="mt-2 text-sm text-slate-300">{{ $test->description }}</p>
                @endif
                @if($test->description_ar)
                    <p class="mt-1 text-sm text-amber-200" dir="rtl" lang="ar">{{ $test->description_ar }}</p>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <span class="rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-semibold text-emerald-200">
                    {{ __('Assignment') }} #{{ $assignment->id }}
                </span>
                <button type="button" id="open-calculator"
                    class="rounded-lg border border-amber-500/40 bg-amber-500/10 px-4 py-2 text-sm font-semibold text-amber-200 hover:bg-amber-500/20">
                    {{ __('Open Calculator') }}
                </button>
            </div>
        </div>

        @if($categories->count() && $test->isCategorical())
            <div class="mt-4 flex flex-wrap gap-2 text-xs">
                @foreach($categories as $category)
                    <span class="rounded-full px-3 py-1 text-slate-900"
                        style="background-color: {{ $category->color ?? '#e5b453' }}">
                        {{ $category->name }}
                        @if($category->name_ar)
                            / <span dir="rtl" lang="ar">{{ $category->name_ar }}</span>
                        @endif
                    </span>
                @endforeach
            </div>
        @endif
    </div>

    <form action="{{ route('tests.submit', $test) }}" method="POST" class="space-y-4">
        @csrf
        @foreach($questions as $index => $question)
            <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-sm font-semibold text-amber-300">{{ __('Question') }} {{ $index + 1 }}</p>
                        <p class="mt-1 text-lg font-semibold text-white">{{ $question->question_text }}</p>
                        @if($question->text_ar)
                            <p class="text-lg font-semibold text-amber-200" dir="rtl" lang="ar">{{ $question->text_ar }}</p>
                        @endif
                    </div>
                    @if($test->isPercentile())
                        <span class="rounded-full bg-amber-500/20 px-3 py-1 text-xs font-semibold text-amber-200">
                            {{ __('Marks') }}: {{ $question->marks }}
                        </span>
                    @endif
                </div>

                <div class="mt-4 space-y-3">
                    @if($question->isMultipleChoice())
                        @foreach($question->answerChoices as $choice)
                            <label class="flex items-start gap-3 rounded-lg border border-white/10 bg-black/20 p-3 hover:border-amber-500/40">
                                <input type="radio" name="answers[{{ $question->id }}][choice_id]" value="{{ $choice->id }}"
                                    class="mt-1 h-4 w-4 text-amber-500" required>
                                <div>
                                    <p class="font-semibold text-white">{{ $choice->choice_text }}</p>
                                    @if($choice->text_ar)
                                        <p class="text-amber-200" dir="rtl" lang="ar">{{ $choice->text_ar }}</p>
                                    @endif
                                </div>
                                @if($test->isCategorical() && $choice->category_id)
                                    @php
                                        $category = $categories->firstWhere('id', $choice->category_id);
                                    @endphp
                                    @if($category)
                                        <span class="ml-auto rounded-full px-2 py-1 text-[11px] font-semibold text-slate-900"
                                            style="background-color: {{ $category->color ?? '#e5b453' }}">
                                            {{ $category->name }}
                                        </span>
                                    @endif
                                @endif
                            </label>
                        @endforeach
                    @else
                        <textarea name="answers[{{ $question->id }}][typed_answer]" rows="3" dir="auto"
                            class="w-full rounded-lg border border-white/10 bg-black/30 px-4 py-3 text-white placeholder-slate-400 focus:border-amber-500 focus:outline-none"
                            placeholder="{{ __('Write your answer in English or Arabic...') }}" required></textarea>
                    @endif
                </div>
            </div>
        @endforeach

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('tests.available') }}"
                class="rounded-lg border border-white/20 px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/5">
                {{ __('Back to tests') }}
            </a>
            <button type="submit"
                class="rounded-lg bg-emerald-500 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-600">
                {{ __('Submit Test') }}
            </button>
        </div>
    </form>

    <div id="calculator-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-sm rounded-2xl border border-amber-500/30 bg-iron-900 p-4 shadow-2xl">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">{{ __('Calculator') }}</h3>
                <button type="button" id="close-calculator" class="text-slate-300 hover:text-white">✕</button>
            </div>
            <input id="calc-display" type="text" class="mt-3 w-full rounded-lg border border-white/10 bg-black/40 px-3 py-2 text-right text-2xl text-white" readonly>
            <div class="mt-3 grid grid-cols-4 gap-2 text-lg font-semibold text-white">
                @foreach(['7','8','9','/',
                          '4','5','6','*',
                          '1','2','3','-',
                          '0','.','=','+'] as $key)
                    <button type="button" data-calc-key="{{ $key }}"
                        class="rounded-lg bg-white/10 px-3 py-3 hover:bg-amber-500/30">{{ $key }}</button>
                @endforeach
                <button type="button" data-calc-action="clear" class="col-span-2 rounded-lg bg-rose-500/30 px-3 py-3 hover:bg-rose-500/40">
                    {{ __('Clear') }}
                </button>
                <button type="button" data-calc-action="backspace" class="col-span-2 rounded-lg bg-slate-500/30 px-3 py-3 hover:bg-slate-500/40">
                    {{ __('Back') }}
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('calculator-modal');
            const openBtn = document.getElementById('open-calculator');
            const closeBtn = document.getElementById('close-calculator');
            const display = document.getElementById('calc-display');

            const showModal = () => modal.classList.remove('hidden');
            const hideModal = () => modal.classList.add('hidden');

            openBtn?.addEventListener('click', showModal);
            closeBtn?.addEventListener('click', hideModal);
            modal?.addEventListener('click', (e) => {
                if (e.target === modal) hideModal();
            });

            document.querySelectorAll('[data-calc-key]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const key = btn.getAttribute('data-calc-key');
                    if (key === '=') {
                        try {
                            display.value = display.value ? Function(`\"use strict\"; return (${display.value})`)() : '';
                        } catch (err) {
                            display.value = '{{ __('Error') }}';
                        }
                        return;
                    }
                    display.value += key;
                });
            });

            document.querySelectorAll('[data-calc-action]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const action = btn.getAttribute('data-calc-action');
                    if (action === 'clear') {
                        display.value = '';
                    } else if (action === 'backspace') {
                        display.value = display.value.slice(0, -1);
                    }
                });
            });
        });
    </script>
@endsection
