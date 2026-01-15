@extends('layouts.dashboard', [
    'title' => __('Edit Test'),
    'subtitle' => __('Update questions, answers, and marks'),
])

@section('content')
    <form action="{{ route('tests.update', $test) }}" method="POST" id="test-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="test_type" value="{{ $test->test_type }}">

        <div class="mx-auto max-w-5xl space-y-6">
            @if(session('error'))
                <div class="rounded-lg border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Test Details -->
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h3 class="mb-4 text-lg font-semibold text-white">{{ __('Test Details') }}</h3>

                <div class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">{{ __('Test Title (English)') }} *</label>
                            <input type="text" name="title_en" required dir="ltr" lang="en"
                                class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20"
                                value="{{ old('title_en', $test->title_en) }}">
                            @error('title_en')
                                <p class="mt-1 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">{{ __('Test Title (Arabic)') }} *</label>
                            <input type="text" name="title_ar" required dir="rtl" lang="ar"
                                class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20"
                                value="{{ old('title_ar', $test->title_ar) }}">
                            @error('title_ar')
                                <p class="mt-1 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">{{ __('Description (English)') }}</label>
                            <textarea name="description_en" rows="3" dir="ltr" lang="en"
                                class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20"
                                placeholder="{{ __('Brief description of this test...') }}">{{ old('description_en', $test->description_en) }}</textarea>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">{{ __('Description (Arabic)') }}</label>
                            <textarea name="description_ar" rows="3" dir="rtl" lang="ar"
                                class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20"
                                placeholder="{{ __('اكتب وصف الاختبار هنا') }}">{{ old('description_ar', $test->description_ar) }}</textarea>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-4">
                        @if ($test->isPercentile())
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-300">{{ __('Total Marks') }} *</label>
                                <input type="number" name="total_marks" min="1" required
                                    class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white placeholder-slate-400 focus:border-amber-500 focus:outline-none"
                                    value="{{ old('total_marks', $test->total_marks) }}">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-300">{{ __('Passing Marks') }} *</label>
                                <input type="number" name="passing_marks" min="0" required
                                    class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white placeholder-slate-400 focus:border-amber-500 focus:outline-none"
                                    value="{{ old('passing_marks', $test->passing_marks) }}">
                            </div>
                        @endif
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">{{ __('Duration (minutes)') }}</label>
                            <input type="number" name="duration_minutes" min="1"
                                class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white placeholder-slate-400 focus:border-amber-500 focus:outline-none"
                                value="{{ old('duration_minutes', $test->duration_minutes) }}">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">{{ __('Status') }} *</label>
                            <select name="status" required
                                class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white">
                                <option value="draft" @selected(old('status', $test->status) === 'draft')>{{ __('Draft') }}</option>
                                <option value="published" @selected(old('status', $test->status) === 'published')>{{ __('Published') }}</option>
                                <option value="archived" @selected(old('status', $test->status) === 'archived')>{{ __('Archived') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories (for categorical tests only) -->
            @if ($test->isCategorical())
                <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/5 p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white">{{ __('Categories / Types') }}</h3>
                        <button type="button" onclick="addCategory()"
                            class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">
                            + {{ __('Add Category') }}
                        </button>
                    </div>

                    <div id="categories-container" class="space-y-3">
                        @foreach($test->categories->sortBy('order') as $index => $category)
                            <div class="flex items-start gap-3 rounded-lg border border-emerald-500/20 bg-emerald-500/5 p-4" data-category-index="{{ $index }}">
                                <div class="flex-1 space-y-3">
                                    <div class="flex gap-3">
                                        <input type="text" name="categories[{{ $index }}][name_en]" required dir="ltr" lang="en"
                                            class="flex-1 rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-400"
                                            value="{{ old("categories.$index.name_en", $category->name_en) }}">
                                        <input type="text" name="categories[{{ $index }}][name_ar]" required dir="rtl" lang="ar"
                                            class="flex-1 rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-400"
                                            value="{{ old("categories.$index.name_ar", $category->name_ar) }}">
                                        <input type="color" name="categories[{{ $index }}][color]" value="{{ old("categories.$index.color", $category->color) }}"
                                            class="h-10 w-16 rounded-lg border border-white/10 bg-white/5 cursor-pointer">
                                    </div>
                                    <div class="grid gap-3 md:grid-cols-2">
                                        <textarea name="categories[{{ $index }}][description_en]" rows="2" dir="ltr" lang="en"
                                            class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-400">{{ old("categories.$index.description_en", $category->description_en) }}</textarea>
                                        <textarea name="categories[{{ $index }}][description_ar]" rows="2" dir="rtl" lang="ar"
                                            class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-400">{{ old("categories.$index.description_ar", $category->description_ar) }}</textarea>
                                    </div>
                                </div>
                                <button type="button" onclick="removeCategory(this)"
                                    class="rounded-lg bg-rose-500/20 px-3 py-2 text-sm text-rose-300 hover:bg-rose-500/30">
                                    {{ __('Remove') }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Questions -->
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white">{{ __('Questions') }}</h3>
                    <button type="button" onclick="addQuestion()"
                        class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">
                        + {{ __('Add Question') }}
                    </button>
                </div>

                <div id="questions-container" class="space-y-4">
                    @foreach($test->questions->sortBy('order') as $qIndex => $question)
                        <div class="rounded-lg border border-white/10 bg-white/5 p-5" data-question-index="{{ $qIndex }}">
                            <div class="mb-4 flex items-start justify-between">
                                <h4 class="font-semibold text-white">{{ __('Question') }} #{{ $qIndex + 1 }}</h4>
                                <button type="button" onclick="removeQuestion(this)"
                                    class="rounded-lg bg-rose-500/20 px-3 py-1.5 text-sm text-rose-300 hover:bg-rose-500/30">
                                    {{ __('Remove') }}
                                </button>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-300">{{ __('Question Text') }} *</label>
                                    <div class="grid gap-3 md:grid-cols-2">
                                        <textarea name="questions[{{ $qIndex }}][text_en]" required rows="2" dir="ltr" lang="en"
                                            class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white placeholder-slate-400">{{ old("questions.$qIndex.text_en", $question->text_en) }}</textarea>
                                        <textarea name="questions[{{ $qIndex }}][text_ar]" required rows="2" dir="rtl" lang="ar"
                                            class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white placeholder-slate-400">{{ old("questions.$qIndex.text_ar", $question->text_ar) }}</textarea>
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-slate-300">{{ __('Question Type') }} *</label>
                                        <select name="questions[{{ $qIndex }}][question_type]" required
                                            onchange="toggleQuestionType(this, {{ $qIndex }})"
                                            class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white">
                                            <option value="multiple_choice" @selected($question->question_type === 'multiple_choice')>{{ __('Multiple Choice') }}</option>
                                            <option value="typed" @selected($question->question_type === 'typed')>{{ __('Typed Answer') }}</option>
                                        </select>
                                    </div>
                                    @if ($test->isPercentile())
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-slate-300">{{ __('Marks') }}</label>
                                            <input type="number" name="questions[{{ $qIndex }}][marks]" min="1"
                                                class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white"
                                                value="{{ old("questions.$qIndex.marks", $question->marks) }}">
                                        </div>
                                    @endif
                                </div>

                                <div class="choices-container-{{ $qIndex }}" @if($question->question_type === 'typed') style="display:none;" @endif>
                                    <div class="mb-2 flex items-center justify-between">
                                        <label class="text-sm font-medium text-slate-300">{{ __('Answer Choices') }}</label>
                                        <button type="button" onclick="addChoice({{ $qIndex }})"
                                            class="rounded-lg bg-amber-500/20 px-3 py-1.5 text-xs text-amber-300 hover:bg-amber-500/30">
                                            + {{ __('Add Choice') }}
                                        </button>
                                    </div>
                                    <div class="choices-list-{{ $qIndex }} space-y-2">
                                        @foreach($question->answerChoices as $cIndex => $choice)
                                            @if ($test->isPercentile())
                                                <div class="flex items-center gap-3">
                                                    <input type="radio" name="questions[{{ $qIndex }}][correct_choice]" value="{{ $cIndex }}"
                                                        class="h-4 w-4 text-amber-500 focus:ring-amber-500"
                                                        onchange="markCorrect({{ $qIndex }}, {{ $cIndex }})"
                                                        @checked($choice->is_correct)>
                                                    <div class="flex-1 space-y-2">
                                                        <input type="text" name="questions[{{ $qIndex }}][choices][{{ $cIndex }}][text_en]" required
                                                            class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-400" dir="ltr" lang="en"
                                                            value="{{ old("questions.$qIndex.choices.$cIndex.text_en", $choice->text_en) }}">
                                                        <input type="text" name="questions[{{ $qIndex }}][choices][{{ $cIndex }}][text_ar]" required
                                                            class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-400" dir="rtl" lang="ar"
                                                            value="{{ old("questions.$qIndex.choices.$cIndex.text_ar", $choice->text_ar) }}">
                                                    </div>
                                                    <input type="hidden" name="questions[{{ $qIndex }}][choices][{{ $cIndex }}][is_correct]" value="{{ $choice->is_correct ? 1 : 0 }}" class="is-correct-{{ $qIndex }}-{{ $cIndex }}">
                                                    <button type="button" onclick="removeChoice(this)"
                                                        class="rounded-lg bg-rose-500/20 px-2 py-1 text-sm text-rose-300 hover:bg-rose-500/30">
                                                        {{ __('Remove') }}
                                                    </button>
                                                </div>
                                            @else
                                                <div class="flex items-center gap-3">
                                                    <select name="questions[{{ $qIndex }}][choices][{{ $cIndex }}][category_id]"
                                                        class="w-40 rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white">
                                                        <option value="">{{ __('Category...') }}</option>
                                                        @foreach($test->categories->sortBy('order') as $catIndex => $category)
                                                            <option value="{{ $catIndex }}" @selected($choice->category_id === $category->id)>
                                                                {{ $category->name_en }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="flex-1 space-y-2">
                                                        <input type="text" name="questions[{{ $qIndex }}][choices][{{ $cIndex }}][text_en]" required
                                                            class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-400" dir="ltr" lang="en"
                                                            value="{{ old("questions.$qIndex.choices.$cIndex.text_en", $choice->text_en) }}">
                                                        <input type="text" name="questions[{{ $qIndex }}][choices][{{ $cIndex }}][text_ar]" required
                                                            class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-400" dir="rtl" lang="ar"
                                                            value="{{ old("questions.$qIndex.choices.$cIndex.text_ar", $choice->text_ar) }}">
                                                    </div>
                                                    <button type="button" onclick="removeChoice(this)"
                                                        class="rounded-lg bg-rose-500/20 px-2 py-1 text-sm text-rose-300 hover:bg-rose-500/30">
                                                        {{ __('Remove') }}
                                                    </button>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-white/5 p-6">
                <a href="{{ route('tests.show', $test) }}"
                    class="rounded-lg border border-white/10 px-6 py-3 text-sm font-semibold text-slate-300 hover:bg-white/5">
                    {{ __('Cancel') }}
                </a>
                <button type="submit"
                    class="rounded-lg bg-emerald-500 px-6 py-3 text-sm font-semibold text-white shadow hover:bg-emerald-600">
                    {{ __('Save Changes') }}
                </button>
            </div>
        </div>
    </form>

    <script>
        const testType = '{{ $test->test_type }}';
        let questionIndex = {{ $test->questions->count() }};
        let categoryIndex = {{ $test->categories->count() }};

        function addCategory() {
            const container = document.getElementById('categories-container');
            const colors = ['#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'];
            const randomColor = colors[Math.floor(Math.random() * colors.length)];

            const categoryHtml = `
                <div class="flex items-start gap-3 rounded-lg border border-emerald-500/20 bg-emerald-500/5 p-4" data-category-index="${categoryIndex}">
                    <div class="flex-1 space-y-3">
                        <div class="flex gap-3">
                            <input type="text" name="categories[${categoryIndex}][name_en]" required dir="ltr" lang="en"
                                class="flex-1 rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-400"
                                placeholder="${'{{ __('Category Name (English)') }}'}">
                            <input type="text" name="categories[${categoryIndex}][name_ar]" required dir="rtl" lang="ar"
                                class="flex-1 rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-400"
                                placeholder="${'{{ __('اسم الفئة') }}'}">
                            <input type="color" name="categories[${categoryIndex}][color]" value="${randomColor}"
                                class="h-10 w-16 rounded-lg border border-white/10 bg-white/5 cursor-pointer">
                        </div>
                        <div class="grid gap-3 md:grid-cols-2">
                            <textarea name="categories[${categoryIndex}][description_en]" rows="2" dir="ltr" lang="en"
                                class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-400"
                                placeholder="${'{{ __('Description (English)') }}'} "></textarea>
                            <textarea name="categories[${categoryIndex}][description_ar]" rows="2" dir="rtl" lang="ar"
                                class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-400"
                                placeholder="${'{{ __('الوصف (اختياري)') }}'} "></textarea>
                        </div>
                    </div>
                    <button type="button" onclick="removeCategory(this)"
                        class="rounded-lg bg-rose-500/20 px-3 py-2 text-sm text-rose-300 hover:bg-rose-500/30">
                        {{ __('Remove') }}
                    </button>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', categoryHtml);
            categoryIndex++;
        }

        function removeCategory(btn) {
            btn.closest('[data-category-index]').remove();
        }

        function addQuestion() {
            const container = document.getElementById('questions-container');

            const questionHtml = `
                <div class="rounded-lg border border-white/10 bg-white/5 p-5" data-question-index="${questionIndex}">
                    <div class="mb-4 flex items-start justify-between">
                        <h4 class="font-semibold text-white">${'{{ __('Question') }}'} #${questionIndex + 1}</h4>
                        <button type="button" onclick="removeQuestion(this)"
                            class="rounded-lg bg-rose-500/20 px-3 py-1.5 text-sm text-rose-300 hover:bg-rose-500/30">
                            ${'{{ __('Remove') }}'}
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">${'{{ __('Question Text') }}'} *</label>
                            <div class="grid gap-3 md:grid-cols-2">
                                <textarea name="questions[${questionIndex}][text_en]" required rows="2" dir="ltr" lang="en"
                                    class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white placeholder-slate-400"
                                    placeholder="${'{{ __('Enter your question here...') }}'}"></textarea>
                                <textarea name="questions[${questionIndex}][text_ar]" required rows="2" dir="rtl" lang="ar"
                                    class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white placeholder-slate-400"
                                    placeholder="${'{{ __('اكتب السؤال هنا...') }}'}"></textarea>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-300">${'{{ __('Question Type') }}'} *</label>
                                <select name="questions[${questionIndex}][question_type]" required
                                    onchange="toggleQuestionType(this, ${questionIndex})"
                                    class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white">
                                    <option value="multiple_choice">${'{{ __('Multiple Choice') }}'}</option>
                                    <option value="typed">${'{{ __('Typed Answer') }}'}</option>
                                </select>
                            </div>
                            ${testType === 'percentile' ? `
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-300">${'{{ __('Marks') }}'}</label>
                                <input type="number" name="questions[${questionIndex}][marks]" min="1" value="1"
                                    class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white">
                            </div>
                            ` : ''}
                        </div>

                        <div class="choices-container-${questionIndex}">
                            <div class="mb-2 flex items-center justify-between">
                                <label class="text-sm font-medium text-slate-300">${'{{ __('Answer Choices') }}'}</label>
                                <button type="button" onclick="addChoice(${questionIndex})"
                                    class="rounded-lg bg-amber-500/20 px-3 py-1.5 text-xs text-amber-300 hover:bg-amber-500/30">
                                    + ${'{{ __('Add Choice') }}'}
                                </button>
                            </div>
                            <div class="choices-list-${questionIndex} space-y-2"></div>
                        </div>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', questionHtml);
            addChoice(questionIndex);
            addChoice(questionIndex);
            questionIndex++;
        }

        function removeQuestion(btn) {
            btn.closest('[data-question-index]').remove();
        }

        function toggleQuestionType(select, qIndex) {
            const container = document.querySelector(`.choices-container-${qIndex}`);
            if (select.value === 'typed') {
                container.style.display = 'none';
            } else {
                container.style.display = 'block';
            }
        }

        function addChoice(qIndex) {
            const container = document.querySelector(`.choices-list-${qIndex}`);
            const choiceCount = container.children.length;

            let choiceHtml = '';
            if (testType === 'percentile') {
                choiceHtml = `
                    <div class="flex items-center gap-3">
                        <input type="radio" name="questions[${qIndex}][correct_choice]" value="${choiceCount}"
                            class="h-4 w-4 text-amber-500 focus:ring-amber-500"
                            onchange="markCorrect(${qIndex}, ${choiceCount})">
                        <div class="flex-1 space-y-2">
                            <input type="text" name="questions[${qIndex}][choices][${choiceCount}][text_en]" required
                                class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-400" dir="ltr" lang="en"
                                placeholder="${'{{ __('Choice text (English)...') }}'} ">
                            <input type="text" name="questions[${qIndex}][choices][${choiceCount}][text_ar]" required
                                class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-400" dir="rtl" lang="ar"
                                placeholder="${'{{ __('نص الخيار (عربي)...') }}'} ">
                        </div>
                        <input type="hidden" name="questions[${qIndex}][choices][${choiceCount}][is_correct]" value="0" class="is-correct-${qIndex}-${choiceCount}">
                        <button type="button" onclick="removeChoice(this)"
                            class="rounded-lg bg-rose-500/20 px-2 py-1 text-sm text-rose-300 hover:bg-rose-500/30">
                            ${'{{ __('Remove') }}'}
                        </button>
                    </div>
                `;
            } else {
                choiceHtml = `
                    <div class="flex items-center gap-3">
                        <select name="questions[${qIndex}][choices][${choiceCount}][category_id]"
                            class="w-40 rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white">
                            <option value="">${'{{ __('Category...') }}'}</option>
                            ${getCategoryOptions()}
                        </select>
                        <div class="flex-1 space-y-2">
                            <input type="text" name="questions[${qIndex}][choices][${choiceCount}][text_en]" required
                                class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-400" dir="ltr" lang="en"
                                placeholder="${'{{ __('Choice text (English)...') }}'} ">
                            <input type="text" name="questions[${qIndex}][choices][${choiceCount}][text_ar]" required
                                class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-400" dir="rtl" lang="ar"
                                placeholder="${'{{ __('نص الخيار (عربي)...') }}'} ">
                        </div>
                        <button type="button" onclick="removeChoice(this)"
                            class="rounded-lg bg-rose-500/20 px-2 py-1 text-sm text-rose-300 hover:bg-rose-500/30">
                            ${'{{ __('Remove') }}'}
                        </button>
                    </div>
                `;
            }

            container.insertAdjacentHTML('beforeend', choiceHtml);
        }

        function removeChoice(btn) {
            btn.closest('div').remove();
        }

        function markCorrect(qIndex, choiceIndex) {
            const allChoices = document.querySelectorAll(`[class^="is-correct-${qIndex}-"]`);
            allChoices.forEach(input => input.value = '0');
            const selectedChoice = document.querySelector(`.is-correct-${qIndex}-${choiceIndex}`);
            if (selectedChoice) {
                selectedChoice.value = '1';
            }
        }

        function getCategoryOptions() {
            const categories = document.querySelectorAll('[data-category-index]');
            let options = '';
            categories.forEach((cat, index) => {
                const name = cat.querySelector('input[type="text"]').value || `Category ${index + 1}`;
                options += `<option value="${index}">${name}</option>`;
            });
            return options;
        }
    </script>
@endsection
