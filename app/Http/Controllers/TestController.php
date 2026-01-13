<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestAnswerChoice;
use App\Models\TestCategory;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    // List all tests
    public function index()
    {
        $tests = Test::with('creator', 'questions', 'categories')
            ->latest()
            ->paginate(15);

        return view('tests.index', compact('tests'));
    }

    // Show test type selection
    public function create()
    {
        return view('tests.create');
    }

    // Show test builder form based on type
    public function createType(Request $request)
    {
        $request->validate([
            'test_type' => 'required|in:percentile,categorical',
        ]);

        $testType = $request->test_type;

        return view('tests.builder', compact('testType'));
    }

    // Store new test with questions
    public function store(Request $request)
    {
        $request->validate([
            'title_en' => 'required|string|max:255',
            'title_ar' => 'required|string|max:255',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'test_type' => 'required|in:percentile,categorical',
            'total_marks' => 'nullable|integer|min:1',
            'passing_marks' => 'nullable|integer|min:0',
            'duration_minutes' => 'nullable|integer|min:1',
            'status' => 'required|in:draft,published',

            // Categories for categorical tests
            'categories' => 'required_if:test_type,categorical|array',
            'categories.*.name_en' => 'required_with:categories|string|max:255',
            'categories.*.name_ar' => 'required_with:categories|string|max:255',
            'categories.*.description_en' => 'nullable|string',
            'categories.*.description_ar' => 'nullable|string',
            'categories.*.color' => 'nullable|string|max:7',

            // Questions
            'questions' => 'required|array|min:1',
            'questions.*.text_en' => 'required|string',
            'questions.*.text_ar' => 'required|string',
            'questions.*.question_type' => 'required|in:multiple_choice,typed',
            'questions.*.marks' => 'nullable|integer|min:1',

            // Answer choices for multiple choice
            'questions.*.choices' => 'required_if:questions.*.question_type,multiple_choice|array|min:2',
            'questions.*.choices.*.text_en' => 'required_with:questions.*.choices|string',
            'questions.*.choices.*.text_ar' => 'required_with:questions.*.choices|string',
            'questions.*.choices.*.is_correct' => 'nullable|boolean',
            'questions.*.choices.*.category_id' => 'nullable|integer',
        ]);

        $languages = Language::whereIn('code', ['en', 'ar'])->pluck('id', 'code');
        if ($languages->count() < 2) {
            return back()->withInput()->with('error', __('English and Arabic languages must be configured first.'));
        }

        DB::beginTransaction();
        try {
            // Create test
            $test = Test::create([
                'title' => $request->title_en,
                'description' => $request->description_en,
                'test_type' => $request->test_type,
                'total_marks' => $request->total_marks,
                'passing_marks' => $request->passing_marks,
                'duration_minutes' => $request->duration_minutes,
                'status' => $request->status,
                'created_by' => auth()->id(),
            ]);

            // Store translations for test
            DB::table('test_translations')->insert([
                [
                    'test_id' => $test->id,
                    'language_id' => $languages['en'],
                    'title' => $request->title_en,
                    'description' => $request->description_en,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'test_id' => $test->id,
                    'language_id' => $languages['ar'],
                    'title' => $request->title_ar,
                    'description' => $request->description_ar,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // Create categories for categorical tests
            $categoryIdMap = [];
            if ($request->test_type === 'categorical' && $request->categories) {
                foreach ($request->categories as $index => $categoryData) {
                    $category = TestCategory::create([
                        'test_id' => $test->id,
                        'name' => $categoryData['name_en'],
                        'description' => $categoryData['description_en'] ?? null,
                        'color' => $categoryData['color'] ?? $this->generateRandomColor(),
                        'order' => $index,
                    ]);
                    $categoryIdMap[$index] = $category->id;

                    DB::table('test_category_translations')->insert([
                        [
                            'test_category_id' => $category->id,
                            'language_id' => $languages['en'],
                            'name' => $categoryData['name_en'],
                            'description' => $categoryData['description_en'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'test_category_id' => $category->id,
                            'language_id' => $languages['ar'],
                            'name' => $categoryData['name_ar'],
                            'description' => $categoryData['description_ar'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    ]);
                }
            }

            // Create questions and choices
            foreach ($request->questions as $questionIndex => $questionData) {
                $question = TestQuestion::create([
                    'test_id' => $test->id,
                    'question_text' => $questionData['text_en'],
                    'question_type' => $questionData['question_type'],
                    'marks' => $questionData['marks'] ?? 1,
                    'order' => $questionIndex,
                    'is_required' => true,
                ]);

                DB::table('test_question_translations')->insert([
                    [
                        'test_question_id' => $question->id,
                        'language_id' => $languages['en'],
                        'question_text' => $questionData['text_en'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'test_question_id' => $question->id,
                        'language_id' => $languages['ar'],
                        'question_text' => $questionData['text_ar'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);

                // Create answer choices for multiple choice questions
                if ($questionData['question_type'] === 'multiple_choice' && isset($questionData['choices'])) {
                    foreach ($questionData['choices'] as $choiceIndex => $choiceData) {
                        $choice = TestAnswerChoice::create([
                            'test_question_id' => $question->id,
                            'choice_text' => $choiceData['text_en'],
                            'is_correct' => $request->test_type === 'percentile' ? ($choiceData['is_correct'] ?? false) : false,
                            'category_id' => $request->test_type === 'categorical' && isset($choiceData['category_id'])
                                ? $categoryIdMap[$choiceData['category_id']] ?? null
                                : null,
                            'order' => $choiceIndex,
                        ]);

                        DB::table('test_answer_choice_translations')->insert([
                            [
                                'test_answer_choice_id' => $choice->id,
                                'language_id' => $languages['en'],
                                'choice_text' => $choiceData['text_en'],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ],
                            [
                                'test_answer_choice_id' => $choice->id,
                                'language_id' => $languages['ar'],
                                'choice_text' => $choiceData['text_ar'],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ],
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('tests.show', $test)
                ->with('success', __('Test created successfully!'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', __('Failed to create test: ') . $e->getMessage());
        }
    }

    // Show test details
    public function show(Test $test)
    {
        $test->load('creator', 'questions.answerChoices', 'categories');

        return view('tests.show', compact('test'));
    }

    // Edit test
    public function edit(Test $test)
    {
        $test->load('questions.answerChoices', 'categories');

        return view('tests.edit', compact('test'));
    }

    // Update test
    public function update(Request $request, Test $test)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_marks' => 'nullable|integer|min:1',
            'passing_marks' => 'nullable|integer|min:0',
            'duration_minutes' => 'nullable|integer|min:1',
            'status' => 'required|in:draft,published,archived',
        ]);

        $test->update($request->only([
            'title',
            'description',
            'total_marks',
            'passing_marks',
            'duration_minutes',
            'status',
        ]));

        return redirect()->route('tests.show', $test)
            ->with('success', __('Test updated successfully!'));
    }

    // Delete test
    public function destroy(Test $test)
    {
        $test->delete();

        return redirect()->route('tests.index')
            ->with('success', __('Test deleted successfully!'));
    }

    // View test submissions for grading
    public function grade(Test $test)
    {
        // Check role hierarchy: Admin, Manager, and Assessor can grade
        abort_unless(auth()->user()->hasRoleOrAbove('assessor'), 403);

        $assignments = $test->assignments()
            ->with(['participant', 'testResult'])
            ->latest()
            ->paginate(20);

        return view('tests.grade', compact('test', 'assignments'));
    }

    // Grade specific assignment
    public function gradeAssignment(Test $test, \App\Models\TestAssignment $assignment)
    {
        // Check role hierarchy
        abort_unless(auth()->user()->hasRoleOrAbove('assessor'), 403);
        abort_unless($assignment->test_id === $test->id, 404);

        // Check if user can view this participant
        $currentUser = auth()->user();
        abort_unless($currentUser->canView($assignment->participant), 403);

        $assignment->load([
            'participant',
            'test.questions.answerChoices',
            'responses.question',
            'responses.selectedChoice',
            'testResult'
        ]);

        $questions = $test->questions()->with('answerChoices')->orderBy('order')->get();

        return view('tests.grade-assignment', compact('test', 'assignment', 'questions'));
    }

    // Save grades for typed answers
    public function saveGrade(Request $request, Test $test, \App\Models\TestAssignment $assignment)
    {
        abort_unless($assignment->test_id === $test->id, 404);

        $request->validate([
            'responses' => 'required|array',
            'responses.*.marks_awarded' => 'nullable|integer|min:0',
            'responses.*.assessor_feedback' => 'nullable|string',
            'responses.*.assigned_category_id' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $totalMarks = 0;
            $categoryScores = [];

            foreach ($request->responses as $responseId => $gradeData) {
                $response = \App\Models\TestResponse::findOrFail($responseId);

                $response->update([
                    'is_graded' => true,
                    'marks_awarded' => $gradeData['marks_awarded'] ?? null,
                    'assessor_feedback' => $gradeData['assessor_feedback'] ?? null,
                    'assigned_category_id' => $gradeData['assigned_category_id'] ?? null,
                    'graded_by' => auth()->id(),
                    'graded_at' => now(),
                ]);

                if ($test->isPercentile() && isset($gradeData['marks_awarded'])) {
                    $totalMarks += $gradeData['marks_awarded'];
                }

                if ($test->isCategorical() && isset($gradeData['assigned_category_id'])) {
                    $catId = $gradeData['assigned_category_id'];
                    $categoryScores[$catId] = ($categoryScores[$catId] ?? 0) + 1;
                }
            }

            // Recalculate test result
            $result = $assignment->testResult;
            if ($result) {
                if ($test->isPercentile()) {
                    $autoGradedMarks = \App\Models\TestResponse::where('test_assignment_id', $assignment->id)
                        ->whereNotNull('marks_awarded')
                        ->sum('marks_awarded');

                    $result->total_marks_obtained = $autoGradedMarks;
                    $possible = $test->total_marks ?: $test->questions->sum('marks');
                    $result->percentage = $possible > 0 ? round(($autoGradedMarks / $possible) * 100, 2) : null;
                    $result->result_status = $test->passing_marks
                        ? ($autoGradedMarks >= $test->passing_marks ? 'pass' : 'fail')
                        : null;
                }

                if ($test->isCategorical() && !empty($categoryScores)) {
                    $existingScores = json_decode($result->category_scores, true) ?: [];
                    foreach ($categoryScores as $catId => $count) {
                        $existingScores[$catId] = ($existingScores[$catId] ?? 0) + $count;
                    }
                    $result->category_scores = $existingScores;

                    arsort($existingScores);
                    $result->dominant_category_id = array_key_first($existingScores);
                }

                $result->save();
            }

            $assignment->update(['status' => 'graded']);

            DB::commit();

            return redirect()->route('tests.grade', $test)
                ->with('success', __('Grades saved successfully!'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', __('Failed to save grades: ') . $e->getMessage());
        }
    }

    // Helper to generate random colors for categories
    private function generateRandomColor()
    {
        $colors = ['#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'];
        return $colors[array_rand($colors)];
    }
}
