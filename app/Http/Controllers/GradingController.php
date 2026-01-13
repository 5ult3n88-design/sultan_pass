<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\ParticipantResponse;
use App\Models\User;
use App\Services\AssessmentScoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GradingController extends Controller
{
    public function index(Assessment $assessment): View
    {
        // Get all participants who have submitted at least one response
        $participants = User::whereHas('participantResponses', function ($query) use ($assessment) {
            $query->where('assessment_id', $assessment->id);
        })
            ->withCount(['participantResponses as total_responses' => function ($query) use ($assessment) {
                $query->where('assessment_id', $assessment->id);
            }])
            ->withCount(['participantResponses as graded_responses' => function ($query) use ($assessment) {
                $query->where('assessment_id', $assessment->id)
                    ->whereNotNull('graded_at');
            }])
            ->orderBy('name')
            ->get();

        // Calculate grading status for each participant
        $participants = $participants->map(function ($participant) use ($assessment) {
            $totalWritten = ParticipantResponse::where('assessment_id', $assessment->id)
                ->where('participant_id', $participant->id)
                ->whereHas('question', function ($query) {
                    $query->where('question_type', 'written');
                })
                ->count();
            
            $gradedWritten = ParticipantResponse::where('assessment_id', $assessment->id)
                ->where('participant_id', $participant->id)
                ->whereNotNull('graded_at')
                ->whereHas('question', function ($query) {
                    $query->where('question_type', 'written');
                })
                ->count();

            $participant->grading_status = $totalWritten > 0 
                ? ($gradedWritten === $totalWritten ? 'graded' : ($gradedWritten > 0 ? 'partial' : 'ungraded'))
                : 'no_written';

            return $participant;
        });

        $layout = request()->user()->role === 'admin' ? 'layouts.dashboard' : 'layouts.role';

        return view('assessments.grade', [
            'assessment' => $assessment->load('translations'),
            'participants' => $participants,
            'layout' => $layout,
        ]);
    }

    public function show(Assessment $assessment, User $participant): View
    {
        // Load assessment with questions and answers
        $assessment->load([
            'questions' => function ($query) {
                $query->orderBy('order');
            },
            'questions.translations',
            'questions.answers' => function ($query) {
                $query->orderBy('order');
            },
            'questions.answers.translations',
            'questions.answers.categories',
            'questions.answers.categories.translations',
            'questions.answers.score',
            'categories',
            'categories.translations',
        ]);

        // Load all responses for this participant
        $responses = ParticipantResponse::where('assessment_id', $assessment->id)
            ->where('participant_id', $participant->id)
            ->with('question')
            ->get()
            ->keyBy('question_id');

        // Get previous and next participants
        $allParticipants = User::whereHas('participantResponses', function ($query) use ($assessment) {
            $query->where('assessment_id', $assessment->id);
        })
            ->orderBy('name')
            ->pluck('id')
            ->toArray();

        $currentIndex = array_search($participant->id, $allParticipants);
        $prevParticipantId = $currentIndex > 0 ? $allParticipants[$currentIndex - 1] : null;
        $nextParticipantId = $currentIndex < count($allParticipants) - 1 ? $allParticipants[$currentIndex + 1] : null;

        $layout = request()->user()->role === 'admin' ? 'layouts.dashboard' : 'layouts.role';

        return view('assessments.grade-participant', [
            'assessment' => $assessment,
            'participant' => $participant,
            'responses' => $responses,
            'prevParticipantId' => $prevParticipantId,
            'nextParticipantId' => $nextParticipantId,
            'layout' => $layout,
        ]);
    }

    public function store(Request $request, Assessment $assessment, User $participant): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'responses' => ['required', 'array'],
            'responses.*.question_id' => ['required', 'exists:assessment_questions,id'],
            'responses.*.graded_score' => ['nullable', 'numeric', 'min:0'],
            'responses.*.graded_categories' => ['nullable', 'array'],
            'responses.*.graded_categories.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $assessment, $participant, $user) {
            foreach ($validated['responses'] as $responseData) {
                $questionId = $responseData['question_id'];
                
                // Only grade written questions
                $response = ParticipantResponse::where('assessment_id', $assessment->id)
                    ->where('participant_id', $participant->id)
                    ->where('question_id', $questionId)
                    ->with('question')
                    ->first();

                if ($response && $response->question && $response->question->question_type === 'written') {
                    $updateData = [
                        'graded_by' => $user->id,
                        'graded_at' => now(),
                    ];

                    if ($assessment->scoring_mode === 'percentile') {
                        $updateData['graded_score'] = $responseData['graded_score'] ?? null;
                    } elseif ($assessment->scoring_mode === 'categorical') {
                        $updateData['graded_categories'] = $responseData['graded_categories'] ?? null;
                    }

                    $response->update($updateData);
                }
            }

            // Recalculate and update the overall assessment score after grading
            $scoreService = new AssessmentScoreService();
            $scoreService->calculateAndUpdateScore($assessment, $participant);
        });

        return redirect()
            ->route('assessments.grade-participant', [$assessment, $participant])
            ->with('status', __('Grades saved successfully.'));
    }
}
