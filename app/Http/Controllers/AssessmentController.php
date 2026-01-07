<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentCategory;
use App\Models\AssessmentQuestion;
use App\Models\AnswerScore;
use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    private const TYPES = [
        'psychometric',
        'interview',
        'group_exercise',
        'written_test',
        'role_play',
        'committee_interview',
        'other',
    ];

    private const STATUSES = [
        'draft',
        'active',
        'closed',
    ];

    public function create(Request $request): View
    {
        $languages = Language::query()->orderBy('name')->get(['id', 'name', 'code']);
        $layout = $request->user()->role === 'admin' ? 'layouts.dashboard' : 'layouts.role';

        return view('assessments.create', [
            'languages' => $languages,
            'types' => self::TYPES,
            'statuses' => self::STATUSES,
            'layout' => $layout,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'type' => ['required', Rule::in(self::TYPES)],
            'scoring_mode' => ['required', Rule::in(['categorical', 'percentile'])],
            'status' => ['required', Rule::in(self::STATUSES)],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'translations' => ['required', 'array'],
            'translations.*.language_id' => ['required', 'exists:languages,id'],
            'translations.*.title' => ['required', 'string', 'max:255'],
            'translations.*.description' => ['nullable', 'string'],
            'categories' => ['required_if:scoring_mode,categorical', 'array', 'min:2', 'max:20'],
            'categories.*.name' => ['required_with:categories', 'string', 'max:100'],
            'categories.*.description' => ['nullable', 'string'],
            'categories.*.color' => ['nullable', 'string', 'max:7'],
            'categories.*.order' => ['nullable', 'integer'],
            'categories.*.translations' => ['nullable', 'array'],
            'categories.*.translations.*.language_id' => ['nullable', 'exists:languages,id'],
            'categories.*.translations.*.name' => ['nullable', 'string', 'max:100'],
            'categories.*.translations.*.description' => ['nullable', 'string'],
            'questions' => ['required', 'array', 'min:1', 'max:100'],
            'questions.*.question_type' => ['required', Rule::in(['mcq', 'written'])],
            'questions.*.question_text' => ['required', 'string'],
            'questions.*.question_image' => ['nullable', 'image', 'max:5120'], // 5MB
            'questions.*.order' => ['required', 'integer'],
            'questions.*.max_score' => ['required_if:scoring_mode,percentile', 'nullable', 'numeric', 'min:0'],
            'questions.*.translations' => ['nullable', 'array'],
            'questions.*.translations.*.language_id' => ['nullable', 'exists:languages,id'],
            'questions.*.translations.*.question_text' => ['nullable', 'string'],
            'questions.*.answers' => ['required_if:questions.*.question_type,mcq', 'array'],
            'questions.*.answers.*.answer_text' => ['required_with:questions.*.answers', 'string'],
            'questions.*.answers.*.translations' => ['nullable', 'array'],
            'questions.*.answers.*.translations.*.language_id' => ['nullable', 'exists:languages,id'],
            'questions.*.answers.*.translations.*.answer_text' => ['nullable', 'string'],
            'questions.*.answers.*.answer_image' => ['nullable', 'image', 'max:2048'], // 2MB
            'questions.*.answers.*.order' => ['nullable', 'integer'],
            'questions.*.answers.*.score_value' => ['required_if:scoring_mode,percentile', 'nullable', 'numeric', 'min:0'],
            'questions.*.answers.*.categories' => ['nullable', 'array'],
            'questions.*.answers.*.categories.*.weight' => ['nullable', 'numeric', 'min:0'],
        ]);

        $translations = collect($validated['translations'])
            ->map(function (array $translation): array {
                return [
                    'language_id' => (int) $translation['language_id'],
                    'title' => trim($translation['title']),
                    'description' => $translation['description'] ?? null,
                ];
            })
            ->filter(fn (array $translation) => $translation['title'] !== '');

        if ($translations->isEmpty()) {
            return back()
                ->withErrors(['translations' => __('Please provide at least one title for the assessment.')])
                ->withInput();
        }

        $assessment = DB::transaction(function () use ($validated, $translations, $user, $request): Assessment {
            // Create assessment
            $assessment = Assessment::create([
                'type' => $validated['type'],
                'scoring_mode' => $validated['scoring_mode'],
                'max_total_score' => $validated['scoring_mode'] === 'percentile' 
                    ? collect($validated['questions'])->sum('max_score') 
                    : null,
                'status' => $validated['status'],
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'created_by' => $user->id,
            ]);

            // Create translations
            $translations->each(function (array $translation) use ($assessment): void {
                $assessment->translations()->create($translation);
            });

            // Create categories (for categorical mode)
            $categoryIdMap = [];
            if ($validated['scoring_mode'] === 'categorical' && isset($validated['categories'])) {
                foreach ($validated['categories'] as $catIndex => $categoryData) {
                    $category = $assessment->categories()->create([
                        'name' => $categoryData['name'],
                        'description' => $categoryData['description'] ?? null,
                        'color' => $categoryData['color'] ?? '#3B82F6',
                        'order' => $categoryData['order'] ?? ($catIndex + 1),
                    ]);
                    $categoryIdMap[$catIndex] = $category->id;

                    // Create category translations
                    if (isset($categoryData['translations'])) {
                        foreach ($categoryData['translations'] as $translationData) {
                            if (isset($translationData['language_id']) && !empty($translationData['name'])) {
                                $category->translations()->create([
                                    'language_id' => $translationData['language_id'],
                                    'name' => $translationData['name'],
                                    'description' => $translationData['description'] ?? null,
                                ]);
                            }
                        }
                    }
                }
            }

            // Create questions and answers
            foreach ($validated['questions'] as $qIndex => $questionData) {
                // Handle question image upload
                $questionImagePath = null;
                if ($request->hasFile("questions.{$qIndex}.question_image")) {
                    $file = $request->file("questions.{$qIndex}.question_image");
                    $questionImagePath = $file->store('assessment-images/questions', 'public');
                }

                $question = $assessment->questions()->create([
                    'question_type' => $questionData['question_type'],
                    'question_text' => $questionData['question_text'],
                    'question_image_path' => $questionImagePath,
                    'order' => $questionData['order'],
                    'max_score' => $questionData['max_score'] ?? null,
                    'is_required' => true,
                ]);

                // Create question translations
                if (isset($questionData['translations'])) {
                    foreach ($questionData['translations'] as $translationData) {
                        if (isset($translationData['language_id']) && !empty($translationData['question_text'])) {
                            $question->translations()->create([
                                'language_id' => $translationData['language_id'],
                                'question_text' => $translationData['question_text'],
                            ]);
                        }
                    }
                }

                // Create answers (only for MCQ questions)
                if ($questionData['question_type'] === 'mcq' && isset($questionData['answers'])) {
                    foreach ($questionData['answers'] as $answerIndex => $answerData) {
                        // Handle answer image upload
                        $answerImagePath = null;
                        if ($request->hasFile("questions.{$qIndex}.answers.{$answerIndex}.answer_image")) {
                            $file = $request->file("questions.{$qIndex}.answers.{$answerIndex}.answer_image");
                            $answerImagePath = $file->store('assessment-images/answers', 'public');
                        }

                        $answer = $question->answers()->create([
                            'answer_text' => $answerData['answer_text'],
                            'answer_image_path' => $answerImagePath,
                            'order' => $answerData['order'] ?? ($answerIndex + 1),
                        ]);

                        // Create answer translations
                        if (isset($answerData['translations'])) {
                            foreach ($answerData['translations'] as $translationData) {
                                if (isset($translationData['language_id']) && !empty($translationData['answer_text'])) {
                                    $answer->translations()->create([
                                        'language_id' => $translationData['language_id'],
                                        'answer_text' => $translationData['answer_text'],
                                    ]);
                                }
                            }
                        }

                        // Handle scoring based on mode
                        if ($validated['scoring_mode'] === 'percentile') {
                            // Create score for percentile mode
                            $answer->score()->create([
                                'score_value' => $answerData['score_value'] ?? 0,
                            ]);
                        } elseif ($validated['scoring_mode'] === 'categorical' && isset($answerData['categories'])) {
                            // Create category weights for categorical mode
                            foreach ($answerData['categories'] as $catIndex => $catWeight) {
                                if (isset($categoryIdMap[$catIndex]) && isset($catWeight['weight'])) {
                                    $answer->categories()->attach($categoryIdMap[$catIndex], [
                                        'weight' => $catWeight['weight'],
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            return $assessment;
        });

        return redirect()
            ->route($user->role === 'admin' ? 'dashboard.admin' : 'dashboard.manager')
            ->with('status', __('Assessment ":title" created successfully.', [
                'title' => $this->primaryTitle($translations),
            ]));
    }

    private function primaryTitle(Collection $translations): string
    {
        $defaultLocale = app()->getLocale();
        $languages = Language::query()->pluck('id', 'code');
        $preferredLanguageId = $languages[$defaultLocale] ?? null;

        if ($preferredLanguageId) {
            $matched = $translations->firstWhere('language_id', $preferredLanguageId);
            if ($matched) {
                return $matched['title'];
            }
        }

        return $translations->first()['title'] ?? __('New assessment');
    }
}
