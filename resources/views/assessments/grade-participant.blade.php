@extends($layout, [
    'title' => __('Grade Assessment'),
    'subtitle' => __('Grade responses for :name', ['name' => $participant->name]),
])

@section('content')
    <form action="{{ route('assessments.save-grade', [$assessment, $participant]) }}" method="POST" class="mx-auto max-w-6xl space-y-6">
        @csrf
        
        {{-- Header --}}
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg shadow-black/10">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-white">{{ $participant->name }}</h1>
                    <p class="mt-1 text-sm text-slate-400">{{ $participant->email }}</p>
                    <p class="mt-2 text-xs text-slate-400">
                        {{ __('Assessment') }}: {{ $assessment->translations->firstWhere('language_id', app()->getLocale())?->title ?? __('Untitled Assessment') }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    @if($prevParticipantId)
                        <a href="{{ route('assessments.grade-participant', [$assessment, $prevParticipantId]) }}"
                           class="rounded-lg border border-white/10 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/10">
                            ← {{ __('Previous') }}
                        </a>
                    @endif
                    @if($nextParticipantId)
                        <a href="{{ route('assessments.grade-participant', [$assessment, $nextParticipantId]) }}"
                           class="rounded-lg border border-white/10 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/10">
                            {{ __('Next') }} →
                        </a>
                    @endif
                    <a href="{{ route('assessments.grade', $assessment) }}"
                       class="rounded-lg border border-white/10 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/10">
                        {{ __('Back to List') }}
                    </a>
                </div>
            </div>
        </div>

        @if(session('status'))
            <div class="rounded-lg border border-green-500/30 bg-green-500/10 p-4 text-sm text-green-300">
                {{ session('status') }}
            </div>
        @endif

        {{-- Questions and Responses --}}
        <div class="space-y-6">
            @foreach($assessment->questions as $question)
                @php
                    $response = $responses->get($question->id);
                @endphp
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg shadow-black/10">
                    <div class="mb-4 flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-uae-gold-300/20 text-sm font-semibold text-uae-gold-300">
                                {{ $question->order }}
                            </span>
                            <div>
                                <h3 class="text-lg font-semibold text-white">{{ $question->translated_text }}</h3>
                                <span class="mt-1 inline-block rounded-full bg-blue-500/20 px-2 py-0.5 text-xs font-semibold text-blue-300">
                                    {{ $question->question_type === 'mcq' ? __('MCQ') : __('Written') }}
                                </span>
                            </div>
                        </div>
                        @if($assessment->scoring_mode === 'percentile' && $question->max_score)
                            <span class="text-sm text-slate-400">
                                {{ __('Max') }}: {{ number_format($question->max_score, 2) }}
                            </span>
                        @endif
                    </div>

                    @if($question->question_image_path)
                        <div class="mb-4">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($question->question_image_path) }}" 
                                 alt="Question image" 
                                 class="max-h-64 rounded-lg border border-white/10">
                        </div>
                    @endif

                    @if($question->question_type === 'mcq')
                        {{-- MCQ Response --}}
                        <div class="rounded-lg border border-white/10 bg-slate-900/50 p-4">
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                {{ __('Selected Answer(s)') }}
                            </p>
                            @if($response && $response->selected_answer_ids)
                                @foreach($question->answers->whereIn('id', $response->selected_answer_ids) as $answer)
                                    <div class="mb-2 flex items-start gap-3 rounded border border-green-500/30 bg-green-500/10 p-3">
                                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-green-500/20 text-xs font-semibold text-green-300">
                                            ✓
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm text-white">{{ $answer->translated_text }}</p>
                                            @if($answer->answer_image_path)
                                                <img src="{{ \Illuminate\Support\Facades\Storage::url($answer->answer_image_path) }}" 
                                                     alt="Answer image" 
                                                     class="mt-2 max-h-32 rounded border border-white/10">
                                            @endif
                                        </div>
                                    </div>
                                @endforeach

                                {{-- Show auto-scored result --}}
                                @if($assessment->scoring_mode === 'categorical')
                                    @php
                                        $categoryTotals = [];
                                        foreach($question->answers->whereIn('id', $response->selected_answer_ids) as $answer) {
                                            foreach($answer->categories as $category) {
                                                $categoryTotals[$category->id] = ($categoryTotals[$category->id] ?? 0) + $category->pivot->weight;
                                            }
                                        }
                                    @endphp
                                    @if(!empty($categoryTotals))
                                        <div class="mt-3 rounded-lg border border-purple-500/30 bg-purple-500/10 p-3">
                                            <p class="mb-2 text-xs font-semibold text-purple-300">{{ __('Auto-scored Categories') }}:</p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($categoryTotals as $catId => $weight)
                                                    @php $category = $assessment->categories->firstWhere('id', $catId); @endphp
                                                    @if($category)
                                                        <span class="rounded-full px-2 py-1 text-xs text-purple-300" style="background-color: {{ $category->color }}20;">
                                                            {{ $category->translated_name }}: {{ number_format($weight, 2) }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @elseif($assessment->scoring_mode === 'percentile')
                                    @php
                                        $totalScore = $question->answers->whereIn('id', $response->selected_answer_ids)->sum(function($answer) {
                                            return $answer->score?->score_value ?? 0;
                                        });
                                    @endphp
                                    <div class="mt-3 rounded-lg border border-blue-500/30 bg-blue-500/10 p-3">
                                        <p class="text-sm text-blue-300">
                                            <strong>{{ __('Auto Score') }}:</strong> 
                                            {{ number_format($totalScore, 2) }} / {{ number_format($question->max_score ?? 0, 2) }}
                                        </p>
                                    </div>
                                @endif
                            @else
                                <p class="text-sm text-slate-400">{{ __('No answer selected') }}</p>
                            @endif
                        </div>
                    @else
                        {{-- Written Response --}}
                        <div class="space-y-4">
                            <div class="rounded-lg border border-white/10 bg-slate-900/50 p-4">
                                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    {{ __('Participant Response') }}
                                </p>
                                @if($response && $response->written_response_text)
                                    <div class="mb-3 whitespace-pre-wrap rounded-lg border border-white/10 bg-slate-800/50 p-3 text-sm text-slate-200">
                                        {{ $response->written_response_text }}
                                    </div>
                                @else
                                    <p class="text-sm text-slate-400">{{ __('No written response provided') }}</p>
                                @endif
                                
                                @if($response && $response->written_response_image_path)
                                    <div class="mt-3">
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($response->written_response_image_path) }}" 
                                             alt="Response image" 
                                             class="max-h-64 rounded-lg border border-white/10">
                                    </div>
                                @endif
                            </div>

                            {{-- Grading Section --}}
                            <div class="rounded-lg border border-uae-gold-300/30 bg-uae-gold-300/10 p-4">
                                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-uae-gold-300">
                                    {{ __('Grade This Response') }}
                                </p>
                                <input type="hidden" name="responses[{{ $question->id }}][question_id]" value="{{ $question->id }}">

                                @if($assessment->scoring_mode === 'categorical')
                                    {{-- Categorical Grading --}}
                                    <div class="space-y-3">
                                        @foreach($assessment->categories as $category)
                                            <div class="flex items-center gap-3">
                                                <label class="flex-1 text-sm text-slate-300">
                                                    <span class="inline-block h-3 w-3 rounded-full mr-2" style="background-color: {{ $category->color }};"></span>
                                                    {{ $category->translated_name }}
                                                </label>
                                                <input type="number" 
                                                       name="responses[{{ $question->id }}][graded_categories][{{ $category->id }}]"
                                                       value="{{ $response?->graded_categories[$category->id] ?? '' }}"
                                                       step="0.1" 
                                                       min="0" 
                                                       max="10"
                                                       class="w-24 rounded border border-white/10 bg-slate-900/60 px-3 py-1.5 text-sm text-slate-200 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40"
                                                       placeholder="0.0">
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    {{-- Percentile Grading --}}
                                    <div>
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">
                                            {{ __('Score') }} ({{ __('Max') }}: {{ number_format($question->max_score ?? 0, 2) }})
                                        </label>
                                        <input type="number" 
                                               name="responses[{{ $question->id }}][graded_score]"
                                               value="{{ $response?->graded_score ?? '' }}"
                                               step="0.01" 
                                               min="0" 
                                               max="{{ $question->max_score ?? 0 }}"
                                               class="w-full rounded border border-white/10 bg-slate-900/60 px-3 py-2 text-sm text-slate-200 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40"
                                               placeholder="0.00">
                                        @if($response?->graded_at)
                                            <p class="mt-2 text-xs text-slate-400">
                                                {{ __('Graded by') }}: {{ $response->grader->name ?? __('Unknown') }} 
                                                {{ __('on') }} {{ $response->graded_at->format('Y-m-d H:i') }}
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-end gap-3 rounded-2xl border border-white/10 bg-white/5 p-6">
            <a href="{{ route('assessments.grade', $assessment) }}" 
               class="rounded-lg border border-white/10 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/10">
                {{ __('Cancel') }}
            </a>
            <button type="submit" class="rounded-lg bg-uae-gold-300/90 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-uae-gold-300">
                {{ __('Save Grades') }}
            </button>
        </div>
    </form>
@endsection
