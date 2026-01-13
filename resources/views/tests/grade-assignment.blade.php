@extends('layouts.dashboard', [
    'title' => __('Grade Submission'),
    'subtitle' => __('Review and grade test submission'),
])

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $test->title }}</h1>
            <p class="mt-1 text-sm text-slate-400">
                {{ __('Participant') }}: {{ $assignment->participant->name ?? $assignment->participant->username }}
            </p>
        </div>
        <a href="{{ route('tests.grade', $test) }}"
            class="rounded-lg border border-white/20 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-white/5">
            {{ __('Back to Submissions') }}
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-lg border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
            {{ session('error') }}
        </div>
    @endif

    <!-- Test Progress Overview -->
    <div class="mb-6 grid gap-4 md:grid-cols-4">
        <div class="rounded-lg border border-white/10 bg-white/5 p-4 text-center">
            <p class="text-2xl font-bold text-emerald-400">{{ $assignment->responses->count() }}</p>
            <p class="text-xs text-slate-400">{{ __('Answered') }}</p>
        </div>
        <div class="rounded-lg border border-white/10 bg-white/5 p-4 text-center">
            <p class="text-2xl font-bold text-amber-400">{{ $assignment->responses->where('is_graded', false)->count() }}</p>
            <p class="text-xs text-slate-400">{{ __('Needs Grading') }}</p>
        </div>
        @if($test->isPercentile())
            <div class="rounded-lg border border-white/10 bg-white/5 p-4 text-center">
                <p class="text-2xl font-bold text-white">
                    {{ $assignment->testResult->total_marks_obtained ?? 0 }}/{{ $test->total_marks }}
                </p>
                <p class="text-xs text-slate-400">{{ __('Current Score') }}</p>
            </div>
            <div class="rounded-lg border border-white/10 bg-white/5 p-4 text-center">
                <p class="text-2xl font-bold text-white">{{ $assignment->testResult->percentage ?? 0 }}%</p>
                <p class="text-xs text-slate-400">{{ __('Percentage') }}</p>
            </div>
        @endif
    </div>

    <form action="{{ route('tests.save-grade', [$test, $assignment]) }}" method="POST" class="space-y-4">
        @csrf
        @foreach($questions as $index => $question)
            @php
                $response = $assignment->responses->firstWhere('test_question_id', $question->id);
            @endphp

            <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-amber-300">{{ __('Question') }} {{ $index + 1 }}</p>
                        <p class="mt-1 text-lg font-semibold text-white">{{ $question->question_text }}</p>
                        @if($test->isPercentile())
                            <p class="mt-1 text-xs text-slate-400">{{ __('Marks') }}: {{ $question->marks }}</p>
                        @endif
                    </div>
                    @if($response?->is_graded)
                        <span class="rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-semibold text-emerald-300">
                            {{ __('Graded') }}
                        </span>
                    @endif
                </div>

                @if($response)
                    <div class="mt-4">
                        @if($question->isMultipleChoice())
                            <div class="rounded-lg border border-white/5 bg-slate-900/40 p-3">
                                <p class="text-sm font-semibold text-slate-300">{{ __('Selected Answer') }}:</p>
                                <div class="mt-2 flex items-center gap-2">
                                    @if($test->isPercentile())
                                        @if($response->selectedChoice?->is_correct)
                                            <span class="text-emerald-400">✓</span>
                                            <span class="font-semibold text-emerald-300">{{ __('Correct') }}</span>
                                        @else
                                            <span class="text-rose-400">✗</span>
                                            <span class="font-semibold text-rose-300">{{ __('Incorrect') }}</span>
                                        @endif
                                    @endif
                                    <p class="text-white">{{ $response->selectedChoice?->choice_text }}</p>
                                </div>
                                @if($test->isPercentile() && $response->selectedChoice?->is_correct)
                                    <p class="mt-2 text-sm text-emerald-300">
                                        {{ __('Auto-graded') }}: {{ $response->marks_awarded ?? $question->marks }} {{ __('marks') }}
                                    </p>
                                @endif
                            </div>
                        @else
                            <!-- Typed Answer - Needs Manual Grading -->
                            <div class="space-y-3">
                                <div class="rounded-lg border border-amber-500/30 bg-amber-500/5 p-4">
                                    <p class="text-sm font-semibold text-amber-300">{{ __('Participant Answer') }}:</p>
                                    <p class="mt-2 whitespace-pre-wrap text-white">{{ $response->typed_answer }}</p>
                                </div>

                                @if($test->isPercentile())
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-300">
                                            {{ __('Marks Awarded') }} ({{ __('out of') }} {{ $question->marks }})
                                        </label>
                                        <input type="number" name="responses[{{ $response->id }}][marks_awarded]"
                                            value="{{ old('responses.'.$response->id.'.marks_awarded', $response->marks_awarded) }}"
                                            min="0" max="{{ $question->marks }}"
                                            class="mt-1 w-32 rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-amber-500 focus:outline-none"
                                            required>
                                    </div>
                                @endif

                                @if($test->isCategorical())
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-300">{{ __('Assign Category') }}</label>
                                        <select name="responses[{{ $response->id }}][assigned_category_id]"
                                            class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-amber-500 focus:outline-none">
                                            <option value="">{{ __('Select Category') }}</option>
                                            @foreach($test->categories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ old('responses.'.$response->id.'.assigned_category_id', $response->assigned_category_id) == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div>
                                    <label class="block text-sm font-semibold text-slate-300">{{ __('Assessor Feedback') }}</label>
                                    <textarea name="responses[{{ $response->id }}][assessor_feedback]" rows="2"
                                        class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white placeholder-slate-400 focus:border-amber-500 focus:outline-none"
                                        placeholder="{{ __('Optional feedback for the participant...') }}">{{ old('responses.'.$response->id.'.assessor_feedback', $response->assessor_feedback) }}</textarea>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="mt-4 rounded-lg border border-dashed border-white/10 px-4 py-3 text-center text-sm text-slate-400">
                        {{ __('No answer submitted') }}
                    </div>
                @endif
            </div>
        @endforeach

        @if($assignment->responses->where('question.question_type', 'typed')->where('is_graded', false)->count() > 0)
            <div class="flex justify-end gap-3 rounded-2xl border border-white/10 bg-white/5 p-6">
                <a href="{{ route('tests.grade', $test) }}"
                    class="rounded-lg border border-white/20 px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-white/5">
                    {{ __('Cancel') }}
                </a>
                <button type="submit"
                    class="rounded-lg bg-emerald-500 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-600">
                    {{ __('Save Grades') }}
                </button>
            </div>
        @endif
    </form>

    <!-- Live Status Indicator -->
    <div class="mt-6 rounded-lg border border-blue-500/30 bg-blue-500/10 p-4">
        <div class="flex items-center gap-3">
            <div class="h-3 w-3 animate-pulse rounded-full bg-blue-400"></div>
            <div>
                <p class="text-sm font-semibold text-blue-200">{{ __('Assignment Status') }}: {{ ucfirst($assignment->status) }}</p>
                <p class="text-xs text-blue-300">
                    {{ __('Submitted') }}: {{ $assignment->testResult?->completed_at?->format('Y-m-d H:i:s') ?? __('Not completed') }}
                </p>
                <p class="text-xs text-blue-300">
                    {{ __('Progress') }}: {{ $assignment->responses->count() }}/{{ $questions->count() }} {{ __('questions answered') }}
                </p>
            </div>
        </div>
    </div>
@endsection
