@extends('layouts.role', [
    'title' => __('Take Test'),
    'subtitle' => __('Answer in English and Arabic as needed, then submit'),
])

@section('content')
    <!-- Time Countdown Timer -->
    @if($test->duration_minutes)
        <div class="mb-4 rounded-2xl border border-blue-500/30 bg-blue-500/10 p-4">
            <div class="flex items-center justify-center gap-3">
                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-lg font-bold text-blue-200">
                    {{ __('Time Remaining') }}: <span id="timer-display">{{ $test->duration_minutes }}:00</span>
                </span>
            </div>
        </div>
    @endif

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

    <!-- Progress Indicators -->
    <div class="mb-6 rounded-2xl border border-white/10 bg-white/5 p-5">
        <div class="flex flex-col gap-4">
            <!-- Progress Stats -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="text-sm">
                        <span class="font-semibold text-blue-400" id="progress-percentage">0%</span>
                        <span class="text-slate-400">{{ __('Completed') }}</span>
                    </div>
                    <div class="h-4 w-px bg-white/10"></div>
                    <div class="text-sm">
                        <span class="font-semibold text-slate-300" id="questions-answered">0</span>
                        <span class="text-slate-400">/ {{ $questions->count() }} {{ __('Answered') }}</span>
                    </div>
                    <div class="h-4 w-px bg-white/10"></div>
                    <div class="text-sm">
                        <span class="font-semibold text-amber-400" id="questions-marked">0</span>
                        <span class="text-slate-400">{{ __('Marked for Review') }}</span>
                    </div>
                </div>
            </div>

            <!-- Progress Dots -->
            <div class="flex flex-wrap items-center gap-2">
                @foreach($questions as $index => $question)
                    <button type="button" data-question-dot="{{ $index }}"
                        class="question-dot h-3 w-3 rounded-full bg-slate-600 transition-all hover:scale-125"
                        title="{{ __('Question') }} {{ $index + 1 }}">
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <form action="{{ route('tests.submit', $test) }}" method="POST" id="test-form" class="space-y-4">
        @csrf
        @foreach($questions as $index => $question)
            <div class="question-container rounded-2xl border border-white/10 bg-white/5 p-5 {{ $index === 0 ? '' : 'hidden' }}" data-question-index="{{ $index }}">
                <div class="mb-4 flex items-start justify-between gap-2">
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-amber-300">{{ __('Question') }} {{ $index + 1 }}</p>
                        <p class="mt-1 text-lg font-semibold text-white">{{ $question->question_text }}</p>
                        @if($question->text_ar)
                            <p class="text-lg font-semibold text-amber-200" dir="rtl" lang="ar">{{ $question->text_ar }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if($test->isPercentile())
                            <span class="rounded-full bg-amber-500/20 px-3 py-1 text-xs font-semibold text-amber-200">
                                {{ __('Marks') }}: {{ $question->marks }}
                            </span>
                        @endif
                        <button type="button" data-mark-review="{{ $index }}"
                            class="mark-review-btn rounded-lg border border-slate-500/40 bg-slate-500/10 px-3 py-1.5 text-xs font-semibold text-slate-300 transition-all hover:bg-slate-500/20">
                            <span class="mark-text">{{ __('Mark for Review') }}</span>
                        </button>
                    </div>
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

        <!-- Navigation Buttons -->
        <div class="flex items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/5 p-6">
            <button type="button" id="prev-question"
                class="flex items-center gap-2 rounded-lg border border-white/20 px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/5 disabled:cursor-not-allowed disabled:opacity-40"
                disabled>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                {{ __('Previous') }}
            </button>

            <div class="flex items-center gap-3">
                <a href="{{ route('tests.available') }}"
                    class="rounded-lg border border-white/20 px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/5">
                    {{ __('Exit Test') }}
                </a>
                <button type="submit" id="submit-test"
                    class="rounded-lg bg-emerald-500 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-600">
                    {{ __('Submit Test') }}
                </button>
            </div>

            <button type="button" id="next-question"
                class="flex items-center gap-2 rounded-lg border border-amber-500/40 bg-amber-500/10 px-5 py-3 text-sm font-semibold text-amber-200 hover:bg-amber-500/20">
                {{ __('Next') }}
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
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
            // Calculator functionality
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
                            display.value = display.value ? Function(`"use strict"; return (${display.value})`)() : '';
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

            // Question navigation and progress tracking
            let currentQuestion = 0;
            const totalQuestions = {{ $questions->count() }};
            const questionContainers = document.querySelectorAll('.question-container');
            const prevBtn = document.getElementById('prev-question');
            const nextBtn = document.getElementById('next-question');
            const progressPercentage = document.getElementById('progress-percentage');
            const questionsAnswered = document.getElementById('questions-answered');
            const questionsMarked = document.getElementById('questions-marked');
            const questionDots = document.querySelectorAll('.question-dot');
            const markedForReview = new Set();

            // Update UI based on current question
            function updateUI() {
                // Hide all questions
                questionContainers.forEach(q => q.classList.add('hidden'));
                // Show current question
                questionContainers[currentQuestion].classList.remove('hidden');

                // Update button states
                prevBtn.disabled = currentQuestion === 0;
                nextBtn.disabled = currentQuestion === totalQuestions - 1;

                // Update progress
                updateProgress();
            }

            // Check if question is answered
            function isQuestionAnswered(index) {
                const container = questionContainers[index];
                const radioInputs = container.querySelectorAll('input[type="radio"]');
                const textInputs = container.querySelectorAll('textarea');

                if (radioInputs.length > 0) {
                    return Array.from(radioInputs).some(input => input.checked);
                }
                if (textInputs.length > 0) {
                    return Array.from(textInputs).some(input => input.value.trim() !== '');
                }
                return false;
            }

            // Update progress indicators
            function updateProgress() {
                let answeredCount = 0;

                questionDots.forEach((dot, index) => {
                    const answered = isQuestionAnswered(index);
                    const marked = markedForReview.has(index);

                    if (answered) answeredCount++;

                    // Reset classes
                    dot.className = 'question-dot h-3 w-3 rounded-full transition-all hover:scale-125';

                    // Apply appropriate styles
                    if (marked) {
                        dot.classList.add('bg-orange-500', 'ring-2', 'ring-orange-400/40');
                    } else if (answered) {
                        dot.classList.add('bg-blue-500', 'ring-2', 'ring-blue-400/40');
                    } else {
                        dot.classList.add('bg-slate-600');
                    }

                    // Highlight current question
                    if (index === currentQuestion) {
                        dot.classList.add('scale-150', 'ring-4');
                    }
                });

                const percentage = Math.round((answeredCount / totalQuestions) * 100);
                progressPercentage.textContent = percentage + '%';
                questionsAnswered.textContent = answeredCount;
                questionsMarked.textContent = markedForReview.size;
            }

            // Navigation buttons
            prevBtn.addEventListener('click', () => {
                if (currentQuestion > 0) {
                    currentQuestion--;
                    updateUI();
                }
            });

            nextBtn.addEventListener('click', () => {
                if (currentQuestion < totalQuestions - 1) {
                    currentQuestion++;
                    updateUI();
                }
            });

            // Question dots click navigation
            questionDots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    currentQuestion = index;
                    updateUI();
                });
            });

            // Mark for review functionality
            document.querySelectorAll('.mark-review-btn').forEach((btn, index) => {
                btn.addEventListener('click', () => {
                    if (markedForReview.has(index)) {
                        markedForReview.delete(index);
                        btn.classList.remove('bg-orange-500/20', 'border-orange-500/40', 'text-orange-300');
                        btn.classList.add('bg-slate-500/10', 'border-slate-500/40', 'text-slate-300');
                        btn.querySelector('.mark-text').textContent = '{{ __('Mark for Review') }}';
                    } else {
                        markedForReview.add(index);
                        btn.classList.remove('bg-slate-500/10', 'border-slate-500/40', 'text-slate-300');
                        btn.classList.add('bg-orange-500/20', 'border-orange-500/40', 'text-orange-300');
                        btn.querySelector('.mark-text').textContent = '{{ __('Marked') }}';
                    }
                    updateProgress();
                });
            });

            // Update progress on input changes
            document.querySelectorAll('input[type="radio"], textarea').forEach(input => {
                input.addEventListener('change', updateProgress);
                input.addEventListener('input', updateProgress);
            });

            // Timer functionality
            @if($test->duration_minutes)
                const timerDisplay = document.getElementById('timer-display');
                let timeRemaining = {{ $test->duration_minutes }} * 60; // Convert to seconds

                function updateTimer() {
                    const minutes = Math.floor(timeRemaining / 60);
                    const seconds = timeRemaining % 60;
                    timerDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;

                    // Change color when time is running out
                    if (timeRemaining <= 300) { // 5 minutes
                        timerDisplay.classList.add('text-rose-400');
                        timerDisplay.classList.remove('text-blue-200');
                    }

                    if (timeRemaining <= 60) { // 1 minute
                        timerDisplay.classList.add('animate-pulse');
                    }

                    if (timeRemaining <= 0) {
                        clearInterval(timerInterval);
                        alert('{{ __('Time is up! Submitting test...') }}');
                        document.getElementById('test-form').submit();
                    }

                    timeRemaining--;
                }

                const timerInterval = setInterval(updateTimer, 1000);
                updateTimer(); // Initial call
            @endif

            // Initial UI update
            updateUI();
        });
    </script>
@endsection
