<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentAnswer;
use App\Models\ParticipantResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SurveyController extends Controller
{
    /**
     * Show the single-question test-taking screen for a given assessment.
     */
    public function take(Request $request, Assessment $assessment): View
    {
        $user = $request->user();

        // Load questions in a stable order
        $questions = AssessmentQuestion::where('assessment_id', $assessment->id)
            ->orderBy('order')
            ->get();

        if ($questions->isEmpty()) {
            abort(404, 'This assessment has no questions yet.');
        }

        $totalQuestions = $questions->count();

        // Determine current question index (1-based)
        $index = (int) $request->query('q', 1);
        if ($index < 1) {
            $index = 1;
        } elseif ($index > $totalQuestions) {
            $index = $totalQuestions;
        }

        $currentQuestion = $questions[$index - 1];

        // Load existing response (if any)
        $existingResponse = ParticipantResponse::where('assessment_id', $assessment->id)
            ->where('participant_id', $user->id)
            ->where('question_id', $currentQuestion->id)
            ->first();

        // Load answers for MCQ questions
        $answers = collect();
        if ($currentQuestion->question_type === 'mcq') {
            $answers = AssessmentAnswer::where('question_id', $currentQuestion->id)
                ->orderBy('order')
                ->get();
        }

        // Progress: answered questions / total
        $answeredCount = ParticipantResponse::where('assessment_id', $assessment->id)
            ->where('participant_id', $user->id)
            ->where(function ($q) {
                $q->whereNotNull('selected_answer_ids')
                    ->orWhereNotNull('written_response_text');
            })
            ->count();

        $progress = $totalQuestions > 0
            ? round(($answeredCount / $totalQuestions) * 100)
            : 0;

        return view('surveys.take', [
            'assessment' => $assessment,
            'currentQuestion' => $currentQuestion,
            'answers' => $answers,
            'index' => $index,
            'totalQuestions' => $totalQuestions,
            'existingResponse' => $existingResponse,
            'progress' => $progress,
            'timeRemaining' => null,
        ]);
    }

    /**
     * Handle saving a response and navigating between questions / submitting.
     */
    public function storeResponse(
        Request $request,
        Assessment $assessment
    ): RedirectResponse {
        $user = $request->user();

        $validated = $request->validate([
            'question_id' => ['required', 'exists:assessment_questions,id'],
            'action' => ['required', 'in:next,previous,mark_for_review,submit'],
            'selected_answers' => ['array'],
            'selected_answers.*' => ['integer'],
            'written_response_text' => ['nullable', 'string'],
        ]);

        $question = AssessmentQuestion::where('assessment_id', $assessment->id)
            ->where('id', $validated['question_id'])
            ->firstOrFail();

        DB::transaction(function () use ($assessment, $user, $question, $validated) {
            // Upsert participant_responses
            $data = [
                'assessment_id' => $assessment->id,
                'participant_id' => $user->id,
                'question_id' => $question->id,
                'updated_at' => now(),
            ];

            if ($question->question_type === 'mcq') {
                $selected = $validated['selected_answers'] ?? [];
                $data['selected_answer_ids'] = !empty($selected) ? json_encode(array_values($selected)) : null;
            } else {
                $data['written_response_text'] = $validated['written_response_text'] ?? null;
            }

            // Basic upsert using unique constraint
            ParticipantResponse::updateOrCreate(
                [
                    'assessment_id' => $assessment->id,
                    'participant_id' => $user->id,
                    'question_id' => $question->id,
                ],
                $data
            );

            // Ensure assessment_participants row exists and mark as in_progress
            DB::table('assessment_participants')->updateOrInsert(
                [
                    'assessment_id' => $assessment->id,
                    'participant_id' => $user->id,
                ],
                [
                    'status' => 'in_progress',
                    'updated_at' => now(),
                ]
            );
        });

        // Determine next index based on action
        $currentIndex = (int) $request->input('index', 1);
        $totalQuestions = AssessmentQuestion::where('assessment_id', $assessment->id)->count();

        $action = $validated['action'];
        if ($action === 'previous') {
            $nextIndex = max(1, $currentIndex - 1);
        } elseif ($action === 'next' || $action === 'mark_for_review') {
            $nextIndex = min($totalQuestions, $currentIndex + 1);
        } else { // submit
            // Mark assessment as completed for this participant
            DB::table('assessment_participants')
                ->where('assessment_id', $assessment->id)
                ->where('participant_id', $user->id)
                ->update([
                    'status' => 'completed',
                    'updated_at' => now(),
                ]);

            return redirect()
                ->route('dashboard.participant')
                ->with('status', __('Your assessment has been submitted successfully.'));
        }

        return redirect()
            ->route('assessments.take', [
                'assessment' => $assessment->id,
                'q' => $nextIndex,
            ]);
    }

    public function assessorReport(int $assessmentId): View
    {
        return view('surveys.assessor-report', [
            'assessmentId' => $assessmentId,
            'overview' => $this->sampleOverview(),
            'participants' => $this->sampleParticipants(),
        ]);
    }

    public function managerReport(int $assessmentId): View
    {
        return view('surveys.manager-report', [
            'assessmentId' => $assessmentId,
            'overview' => $this->sampleOverview(),
            'competencyBreakdown' => $this->sampleCompetencies(),
            'participants' => $this->sampleParticipants(),
        ]);
    }

    protected function sampleOverview(): array
    {
        return [
            'title' => __('Leadership Simulation — Cohort A'),
            'status' => __('In progress'),
            'submitted' => 8,
            'total' => 12,
            'avgScore' => 82,
        ];
    }

    protected function sampleParticipants(): Collection
    {
        return collect([
            ['name' => 'Sara Al Mansoori', 'status' => 'completed', 'score' => 91, 'strengths' => ['Communication', 'Decision making']],
            ['name' => 'Omar Al Nuaimi', 'status' => 'in_review', 'score' => 78, 'strengths' => ['Analytical thinking']],
            ['name' => 'Layla Al Harbi', 'status' => 'pending', 'score' => null, 'strengths' => []],
        ]);
    }

    protected function sampleCompetencies(): Collection
    {
        return collect([
            ['name' => __('Leadership'), 'score' => 84],
            ['name' => __('Collaboration'), 'score' => 79],
            ['name' => __('Strategic thinking'), 'score' => 88],
            ['name' => __('Communication'), 'score' => 92],
        ]);
    }
}
