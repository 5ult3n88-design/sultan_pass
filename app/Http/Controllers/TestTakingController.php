<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\Test;
use App\Models\TestAssignment;
use App\Models\TestResponse;
use App\Models\TestResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestTakingController extends Controller
{
    public function available()
    {
        $assignments = TestAssignment::query()
            ->with(['test' => function ($query) {
                $query->where('status', 'published')
                    ->select('id', 'title', 'test_type', 'duration_minutes');
            }])
            ->where('participant_id', auth()->id())
            ->whereIn('status', ['assigned', 'in_progress'])
            ->latest()
            ->get()
            ->filter(fn($assignment) => $assignment->test !== null)
            ->values();

        return view('tests.available', compact('assignments'));
    }

    public function take(Test $test)
    {
        abort_unless($test->isPublished(), 404);

        $assignment = TestAssignment::query()
            ->where('test_id', $test->id)
            ->where('participant_id', auth()->id())
            ->first();

        if (! $assignment) {
            return redirect()->route('tests.available')
                ->with('error', __('This test is not assigned to you.'));
        }

        if ($assignment->isCompleted()) {
            return redirect()->route('dashboard.participant')
                ->with('error', __('You have already completed this test.'));
        }

        $languageIds = Language::whereIn('code', ['en', 'ar'])->pluck('id', 'code')->toArray();
        $languageIds = array_merge(['en' => null, 'ar' => null], $languageIds);
        $languageFilter = array_values(array_filter($languageIds));

        $testTranslations = DB::table('test_translations')
            ->where('test_id', $test->id)
            ->whereIn('language_id', $languageFilter)
            ->get()
            ->keyBy('language_id');

        $test->title_ar = optional($testTranslations[$languageIds['ar']] ?? null)->title;
        $test->description_ar = optional($testTranslations[$languageIds['ar']] ?? null)->description;

        $categories = $test->categories()->orderBy('order')->get();
        $categoryTranslations = $languageFilter
            ? DB::table('test_category_translations')
                ->whereIn('test_category_id', $categories->pluck('id'))
                ->whereIn('language_id', $languageFilter)
                ->get()
                ->groupBy('test_category_id')
            : collect();

        foreach ($categories as $category) {
            $translations = $categoryTranslations[$category->id] ?? collect();
            $category->name_ar = optional($translations->firstWhere('language_id', $languageIds['ar']))->name;
            $category->description_ar = optional($translations->firstWhere('language_id', $languageIds['ar']))->description;
        }

        $questions = $test->questions()->with('answerChoices')->orderBy('order')->get();
        $questionTranslations = DB::table('test_question_translations')
            ->whereIn('test_question_id', $questions->pluck('id'))
            ->whereIn('language_id', $languageFilter)
            ->get()
            ->groupBy('test_question_id');

        $choiceIds = $questions->flatMap(function ($question) {
            return $question->answerChoices->pluck('id');
        });

        $choiceTranslations = $languageFilter
            ? DB::table('test_answer_choice_translations')
                ->whereIn('test_answer_choice_id', $choiceIds)
                ->whereIn('language_id', $languageFilter)
                ->get()
                ->groupBy('test_answer_choice_id')
            : collect();

        foreach ($questions as $question) {
            $translations = $questionTranslations[$question->id] ?? collect();
            $question->text_ar = optional($translations->firstWhere('language_id', $languageIds['ar']))->question_text;

            foreach ($question->answerChoices as $choice) {
                $choiceTrans = $choiceTranslations[$choice->id] ?? collect();
                $choice->text_ar = optional($choiceTrans->firstWhere('language_id', $languageIds['ar']))->choice_text;
            }
        }

        if ($assignment->status === 'assigned') {
            $assignment->update(['status' => 'in_progress']);
        }

        return view('tests.take', compact('test', 'questions', 'categories', 'assignment'));
    }

    public function submit(Request $request, Test $test)
    {
        abort_unless($test->isPublished(), 404);

        $assignment = TestAssignment::query()
            ->where('test_id', $test->id)
            ->where('participant_id', auth()->id())
            ->first();

        if (! $assignment) {
            return redirect()->route('tests.available')
                ->with('error', __('This test is not assigned to you.'));
        }

        if ($assignment->isCompleted()) {
            return redirect()->route('dashboard.participant')
                ->with('error', __('You have already completed this test.'));
        }

        $questions = $test->questions()->with('answerChoices')->orderBy('order')->get();
        $answers = $request->input('answers', []);

        DB::beginTransaction();
        try {
            TestResponse::where('test_assignment_id', $assignment->id)->delete();

            $totalMarksPossible = 0;
            $marksObtained = 0;
            $categoryScores = [];

            foreach ($questions as $question) {
                $answerPayload = $answers[$question->id] ?? null;

                if ($question->isMultipleChoice()) {
                    $choiceId = $answerPayload['choice_id'] ?? null;
                    if (! $choiceId) {
                        throw new \RuntimeException(__('Please answer all questions.'));
                    }

                    $choice = $question->answerChoices->firstWhere('id', (int) $choiceId);
                    if (! $choice) {
                        throw new \RuntimeException(__('Invalid answer choice selected.'));
                    }

                    TestResponse::create([
                        'test_assignment_id' => $assignment->id,
                        'test_question_id' => $question->id,
                        'selected_choice_id' => $choice->id,
                        'is_correct' => $test->isPercentile() ? $choice->is_correct : null,
                        'marks_awarded' => $test->isPercentile() ? ($choice->is_correct ? $question->marks : 0) : null,
                    ]);

                    if ($test->isPercentile()) {
                        $totalMarksPossible += $question->marks;
                        if ($choice->is_correct) {
                            $marksObtained += $question->marks;
                        }
                    }

                    if ($test->isCategorical() && $choice->category_id) {
                        $categoryScores[$choice->category_id] = ($categoryScores[$choice->category_id] ?? 0) + 1;
                    }
                } else {
                    $typedAnswer = $answerPayload['typed_answer'] ?? null;
                    if (! $typedAnswer) {
                        throw new \RuntimeException(__('Please answer all questions.'));
                    }

                    TestResponse::create([
                        'test_assignment_id' => $assignment->id,
                        'test_question_id' => $question->id,
                        'typed_answer' => $typedAnswer,
                        'is_graded' => false,
                    ]);

                    if ($test->isPercentile()) {
                        $totalMarksPossible += $question->marks;
                    }
                }
            }

            $resultData = [
                'test_assignment_id' => $assignment->id,
                'completed_at' => now(),
            ];

            if ($test->isPercentile()) {
                $resultData['total_marks_obtained'] = $marksObtained;
                $possible = $test->total_marks ?: $totalMarksPossible;
                $resultData['percentage'] = $possible > 0 ? round(($marksObtained / $possible) * 100, 2) : null;
                $resultData['result_status'] = $test->passing_marks
                    ? ($marksObtained >= $test->passing_marks ? 'pass' : 'fail')
                    : null;
            } else {
                $resultData['category_scores'] = ! empty($categoryScores) ? $categoryScores : null;
                if (! empty($categoryScores)) {
                    arsort($categoryScores);
                    $resultData['dominant_category_id'] = array_key_first($categoryScores);
                }
            }

            TestResult::updateOrCreate(
                ['test_assignment_id' => $assignment->id],
                $resultData
            );

            $assignment->status = 'submitted';
            $assignment->save();

            DB::commit();

            return redirect()->route('dashboard.participant')
                ->with('status', __('Test submitted successfully.'));
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
