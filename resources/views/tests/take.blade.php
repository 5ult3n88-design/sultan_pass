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

    {{-- Windows-style Calculator Modal --}}
    <div id="calculator-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4" style="display: none;">
        <div id="calculator-container" class="w-80 rounded-xl bg-[#202020] shadow-2xl overflow-hidden select-none" style="font-family: 'Segoe UI', system-ui, sans-serif;">
            {{-- Title Bar --}}
            <div id="calc-titlebar" class="flex items-center justify-between px-3 py-2 bg-[#1f1f1f] cursor-move">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-white/70" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V4a2 2 0 00-2-2H6zm1 2h6v2H7V4zm0 4h6v2H7V8zm0 4h2v2H7v-2z"/>
                    </svg>
                    <span class="text-xs font-medium text-white/90">{{ __('Calculator') }}</span>
                </div>
                <div class="flex items-center gap-1">
                    <button type="button" id="calc-minimize" class="w-8 h-6 flex items-center justify-center text-white/70 hover:bg-white/10 rounded">
                        <svg class="w-3 h-0.5" fill="currentColor"><rect width="12" height="2"/></svg>
                    </button>
                    <button type="button" id="close-calculator" class="w-8 h-6 flex items-center justify-center text-white/70 hover:bg-red-500 hover:text-white rounded">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Display Area --}}
            <div class="px-3 py-2 bg-[#202020]">
                <div id="calc-history" class="text-right text-xs text-white/40 h-4 overflow-hidden"></div>
                <input id="calc-display" type="text" value="0"
                    class="w-full bg-transparent text-right text-4xl font-light text-white outline-none" readonly>
            </div>

            {{-- Memory Buttons --}}
            <div class="grid grid-cols-6 gap-0.5 px-1 py-1 bg-[#202020]">
                <button type="button" data-calc-memory="MC" class="py-1 text-xs text-white/50 hover:bg-white/10 rounded">MC</button>
                <button type="button" data-calc-memory="MR" class="py-1 text-xs text-white/50 hover:bg-white/10 rounded">MR</button>
                <button type="button" data-calc-memory="M+" class="py-1 text-xs text-white/50 hover:bg-white/10 rounded">M+</button>
                <button type="button" data-calc-memory="M-" class="py-1 text-xs text-white/50 hover:bg-white/10 rounded">M-</button>
                <button type="button" data-calc-memory="MS" class="py-1 text-xs text-white/50 hover:bg-white/10 rounded">MS</button>
                <button type="button" data-calc-memory="M▾" class="py-1 text-xs text-white/50 hover:bg-white/10 rounded">M▾</button>
            </div>

            {{-- Calculator Buttons --}}
            <div class="grid grid-cols-4 gap-0.5 p-1 bg-[#202020]">
                {{-- Row 1 --}}
                <button type="button" data-calc-func="percent" class="calc-btn calc-btn-func">%</button>
                <button type="button" data-calc-action="clear-entry" class="calc-btn calc-btn-func">CE</button>
                <button type="button" data-calc-action="clear" class="calc-btn calc-btn-func">C</button>
                <button type="button" data-calc-action="backspace" class="calc-btn calc-btn-func">⌫</button>

                {{-- Row 2 --}}
                <button type="button" data-calc-func="reciprocal" class="calc-btn calc-btn-func">1/x</button>
                <button type="button" data-calc-func="square" class="calc-btn calc-btn-func">x²</button>
                <button type="button" data-calc-func="sqrt" class="calc-btn calc-btn-func">√x</button>
                <button type="button" data-calc-key="/" class="calc-btn calc-btn-op">÷</button>

                {{-- Row 3 --}}
                <button type="button" data-calc-key="7" class="calc-btn calc-btn-num">7</button>
                <button type="button" data-calc-key="8" class="calc-btn calc-btn-num">8</button>
                <button type="button" data-calc-key="9" class="calc-btn calc-btn-num">9</button>
                <button type="button" data-calc-key="*" class="calc-btn calc-btn-op">×</button>

                {{-- Row 4 --}}
                <button type="button" data-calc-key="4" class="calc-btn calc-btn-num">4</button>
                <button type="button" data-calc-key="5" class="calc-btn calc-btn-num">5</button>
                <button type="button" data-calc-key="6" class="calc-btn calc-btn-num">6</button>
                <button type="button" data-calc-key="-" class="calc-btn calc-btn-op">−</button>

                {{-- Row 5 --}}
                <button type="button" data-calc-key="1" class="calc-btn calc-btn-num">1</button>
                <button type="button" data-calc-key="2" class="calc-btn calc-btn-num">2</button>
                <button type="button" data-calc-key="3" class="calc-btn calc-btn-num">3</button>
                <button type="button" data-calc-key="+" class="calc-btn calc-btn-op">+</button>

                {{-- Row 6 --}}
                <button type="button" data-calc-func="negate" class="calc-btn calc-btn-num">±</button>
                <button type="button" data-calc-key="0" class="calc-btn calc-btn-num">0</button>
                <button type="button" data-calc-key="." class="calc-btn calc-btn-num">.</button>
                <button type="button" data-calc-key="=" class="calc-btn calc-btn-equals">=</button>
            </div>
        </div>
    </div>

    <style>
        .calc-btn {
            @apply py-3 text-lg font-normal rounded transition-colors;
        }
        .calc-btn-num {
            @apply bg-[#3b3b3b] text-white hover:bg-[#4a4a4a] active:bg-[#5a5a5a];
        }
        .calc-btn-op {
            @apply bg-[#323232] text-white hover:bg-[#4a4a4a] active:bg-[#5a5a5a];
        }
        .calc-btn-func {
            @apply bg-[#323232] text-white hover:bg-[#4a4a4a] active:bg-[#5a5a5a] text-base;
        }
        .calc-btn-equals {
            @apply bg-[#4cc2ff] text-[#202020] hover:bg-[#5acfff] active:bg-[#3ab8f5] font-medium;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Enhanced Calculator functionality (Windows-style)
            const modal = document.getElementById('calculator-modal');
            const calcContainer = document.getElementById('calculator-container');
            const openBtn = document.getElementById('open-calculator');
            const closeBtn = document.getElementById('close-calculator');
            const minimizeBtn = document.getElementById('calc-minimize');
            const display = document.getElementById('calc-display');
            const history = document.getElementById('calc-history');
            const titlebar = document.getElementById('calc-titlebar');

            let currentValue = '0';
            let previousValue = '';
            let operation = null;
            let shouldResetDisplay = false;
            let memory = 0;

            function updateDisplay() {
                display.value = currentValue;
            }

            function showModal() {
                modal.style.display = 'flex';
                modal.classList.remove('hidden');
            }

            function hideModal() {
                modal.style.display = 'none';
                modal.classList.add('hidden');
            }

            // Draggable calculator
            let isDragging = false;
            let dragOffset = { x: 0, y: 0 };

            titlebar?.addEventListener('mousedown', (e) => {
                isDragging = true;
                const rect = calcContainer.getBoundingClientRect();
                dragOffset.x = e.clientX - rect.left;
                dragOffset.y = e.clientY - rect.top;
                calcContainer.style.position = 'fixed';
            });

            document.addEventListener('mousemove', (e) => {
                if (!isDragging) return;
                calcContainer.style.left = (e.clientX - dragOffset.x) + 'px';
                calcContainer.style.top = (e.clientY - dragOffset.y) + 'px';
                calcContainer.style.transform = 'none';
            });

            document.addEventListener('mouseup', () => {
                isDragging = false;
            });

            openBtn?.addEventListener('click', showModal);
            closeBtn?.addEventListener('click', hideModal);
            minimizeBtn?.addEventListener('click', hideModal);
            modal?.addEventListener('click', (e) => {
                if (e.target === modal) hideModal();
            });

            // Number and operator keys
            document.querySelectorAll('[data-calc-key]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const key = btn.getAttribute('data-calc-key');

                    if (key === '=') {
                        if (operation && previousValue !== '') {
                            try {
                                const prev = parseFloat(previousValue);
                                const curr = parseFloat(currentValue);
                                let result;

                                switch(operation) {
                                    case '+': result = prev + curr; break;
                                    case '-': result = prev - curr; break;
                                    case '*': result = prev * curr; break;
                                    case '/': result = curr !== 0 ? prev / curr : 'Error'; break;
                                }

                                history.textContent = `${previousValue} ${operation} ${currentValue} =`;
                                currentValue = result.toString();
                                previousValue = '';
                                operation = null;
                                shouldResetDisplay = true;
                            } catch (err) {
                                currentValue = 'Error';
                            }
                        }
                        updateDisplay();
                        return;
                    }

                    if (['+', '-', '*', '/'].includes(key)) {
                        if (previousValue !== '' && operation) {
                            // Chain operations
                            const prev = parseFloat(previousValue);
                            const curr = parseFloat(currentValue);
                            let result;
                            switch(operation) {
                                case '+': result = prev + curr; break;
                                case '-': result = prev - curr; break;
                                case '*': result = prev * curr; break;
                                case '/': result = curr !== 0 ? prev / curr : 0; break;
                            }
                            currentValue = result.toString();
                        }
                        previousValue = currentValue;
                        operation = key;
                        shouldResetDisplay = true;
                        history.textContent = `${previousValue} ${key}`;
                        updateDisplay();
                        return;
                    }

                    // Number input
                    if (shouldResetDisplay || currentValue === '0') {
                        currentValue = key === '.' ? '0.' : key;
                        shouldResetDisplay = false;
                    } else {
                        if (key === '.' && currentValue.includes('.')) return;
                        currentValue += key;
                    }
                    updateDisplay();
                });
            });

            // Action buttons (clear, backspace)
            document.querySelectorAll('[data-calc-action]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const action = btn.getAttribute('data-calc-action');
                    if (action === 'clear') {
                        currentValue = '0';
                        previousValue = '';
                        operation = null;
                        history.textContent = '';
                    } else if (action === 'clear-entry') {
                        currentValue = '0';
                    } else if (action === 'backspace') {
                        currentValue = currentValue.length > 1 ? currentValue.slice(0, -1) : '0';
                    }
                    updateDisplay();
                });
            });

            // Function buttons (sqrt, square, etc.)
            document.querySelectorAll('[data-calc-func]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const func = btn.getAttribute('data-calc-func');
                    const num = parseFloat(currentValue);
                    let result;

                    switch(func) {
                        case 'sqrt':
                            result = Math.sqrt(num);
                            history.textContent = `√(${currentValue})`;
                            break;
                        case 'square':
                            result = num * num;
                            history.textContent = `sqr(${currentValue})`;
                            break;
                        case 'reciprocal':
                            result = num !== 0 ? 1 / num : 'Error';
                            history.textContent = `1/(${currentValue})`;
                            break;
                        case 'percent':
                            result = previousValue ? (parseFloat(previousValue) * num / 100) : (num / 100);
                            break;
                        case 'negate':
                            result = num * -1;
                            break;
                    }

                    currentValue = result.toString();
                    shouldResetDisplay = true;
                    updateDisplay();
                });
            });

            // Memory buttons
            document.querySelectorAll('[data-calc-memory]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const action = btn.getAttribute('data-calc-memory');
                    const num = parseFloat(currentValue);

                    switch(action) {
                        case 'MC': memory = 0; break;
                        case 'MR': currentValue = memory.toString(); break;
                        case 'M+': memory += num; break;
                        case 'M-': memory -= num; break;
                        case 'MS': memory = num; break;
                    }
                    updateDisplay();
                });
            });

            // Keyboard support
            document.addEventListener('keydown', (e) => {
                if (modal.style.display !== 'flex') return;

                const key = e.key;
                if (/[0-9.]/.test(key)) {
                    document.querySelector(`[data-calc-key="${key}"]`)?.click();
                } else if (['+', '-', '*', '/'].includes(key)) {
                    document.querySelector(`[data-calc-key="${key}"]`)?.click();
                } else if (key === 'Enter' || key === '=') {
                    document.querySelector('[data-calc-key="="]')?.click();
                } else if (key === 'Escape') {
                    hideModal();
                } else if (key === 'Backspace') {
                    document.querySelector('[data-calc-action="backspace"]')?.click();
                } else if (key === 'c' || key === 'C') {
                    document.querySelector('[data-calc-action="clear"]')?.click();
                }
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
                nextBtn.classList.toggle('hidden', currentQuestion === totalQuestions - 1);

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
