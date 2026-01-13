<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\ParticipantResponse;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AssessmentScoreService
{
    /**
     * Calculate and update the overall score for a participant in an assessment
     *
     * @param Assessment $assessment
     * @param User $participant
     * @return float|null The calculated score (0-100) or null if calculation not possible
     */
    public function calculateAndUpdateScore(Assessment $assessment, User $participant): ?float
    {
        $score = $this->calculateScore($assessment, $participant);

        if ($score !== null) {
            // Update or create assessment_participants record
            DB::table('assessment_participants')->updateOrInsert(
                [
                    'assessment_id' => $assessment->id,
                    'participant_id' => $participant->id,
                ],
                [
                    'score' => $score,
                    'status' => $this->determineStatus($assessment, $participant),
                    'updated_at' => now(),
                ]
            );
        }

        return $score;
    }

    /**
     * Calculate the score for a participant in an assessment
     *
     * @param Assessment $assessment
     * @param User $participant
     * @return float|null The calculated score (0-100) or null if calculation not possible
     */
    public function calculateScore(Assessment $assessment, User $participant): ?float
    {
        $responses = ParticipantResponse::where('assessment_id', $assessment->id)
            ->where('participant_id', $participant->id)
            ->with('question')
            ->get();

        if ($responses->isEmpty()) {
            return null;
        }

        // Reload assessment to ensure scoring_mode is available
        $assessment->refresh();

        if ($assessment->scoring_mode === 'percentile') {
            return $this->calculatePercentileScore($assessment, $responses);
        } elseif ($assessment->scoring_mode === 'categorical') {
            return $this->calculateCategoricalScore($assessment, $responses);
        }

        return null;
    }

    /**
     * Calculate score for percentile mode
     *
     * @param Assessment $assessment
     * @param \Illuminate\Database\Eloquent\Collection $responses
     * @return float|null
     */
    protected function calculatePercentileScore(Assessment $assessment, $responses): ?float
    {
        $totalScore = 0;
        $maxPossibleScore = 0;
        $hasGradedResponses = false;

        foreach ($responses as $response) {
            $question = $response->question;

            if (!$question) {
                continue;
            }

            if ($question->question_type === 'written') {
                // For written questions, use graded_score if available
                if ($response->graded_score !== null) {
                    $totalScore += (float) $response->graded_score;
                    $maxPossibleScore += (float) ($question->max_score ?? 0);
                    $hasGradedResponses = true;
                }
            } elseif ($question->question_type === 'mcq') {
                // For MCQ questions, calculate from selected answers
                $questionScore = $this->calculateMCQScore($response, $question);
                if ($questionScore !== null) {
                    $totalScore += $questionScore['score'];
                    $maxPossibleScore += $questionScore['max'];
                    $hasGradedResponses = true;
                }
            }
        }

        if (!$hasGradedResponses || $maxPossibleScore == 0) {
            return null;
        }

        // Calculate percentage score (0-100)
        $percentage = ($totalScore / $maxPossibleScore) * 100;
        return min(100, max(0, round($percentage, 2)));
    }

    /**
     * Calculate score for categorical mode
     *
     * @param Assessment $assessment
     * @param \Illuminate\Database\Eloquent\Collection $responses
     * @return float|null
     */
    protected function calculateCategoricalScore(Assessment $assessment, $responses): ?float
    {
        $categoryTotals = [];
        $categoryMaxWeights = [];

        foreach ($responses as $response) {
            $question = $response->question;

            if (!$question) {
                continue;
            }

            if ($question->question_type === 'written') {
                // For written questions, use graded_categories if available
                if ($response->graded_categories && is_array($response->graded_categories)) {
                    foreach ($response->graded_categories as $categoryId => $weight) {
                        if (!isset($categoryTotals[$categoryId])) {
                            $categoryTotals[$categoryId] = 0;
                            $categoryMaxWeights[$categoryId] = 0;
                        }
                        $categoryTotals[$categoryId] += (float) $weight;
                        // Assume max weight per question is 3.0 (adjust based on your system)
                        $categoryMaxWeights[$categoryId] += 3.0;
                    }
                }
            } elseif ($question->question_type === 'mcq') {
                // For MCQ questions, calculate from selected answers and their category weights
                $selectedAnswerIds = $response->selected_answer_ids ?? [];
                if (!empty($selectedAnswerIds)) {
                    $weights = DB::table('answer_category_weights as acw')
                        ->whereIn('acw.answer_id', $selectedAnswerIds)
                        ->select('acw.category_id', 'acw.weight')
                        ->get();

                    foreach ($weights as $weight) {
                        $categoryId = $weight->category_id;
                        if (!isset($categoryTotals[$categoryId])) {
                            $categoryTotals[$categoryId] = 0;
                            $categoryMaxWeights[$categoryId] = 0;
                        }
                        $categoryTotals[$categoryId] += (float) $weight->weight;
                        // Assume max weight per answer is 3.0
                        $categoryMaxWeights[$categoryId] += 3.0;
                    }
                }
            }
        }

        if (empty($categoryTotals)) {
            return null;
        }

        // Calculate average score across all categories
        $categoryScores = [];
        foreach ($categoryTotals as $categoryId => $total) {
            if ($categoryMaxWeights[$categoryId] > 0) {
                $avgScore = $total / $categoryMaxWeights[$categoryId];
                // Normalize to 0-100 scale
                $categoryScores[$categoryId] = min(100, max(0, ($avgScore / 3.0) * 100));
            }
        }

        if (empty($categoryScores)) {
            return null;
        }

        // Return average of all category scores
        return round(array_sum($categoryScores) / count($categoryScores), 2);
    }

    /**
     * Calculate score for an MCQ response
     *
     * @param ParticipantResponse $response
     * @param \App\Models\AssessmentQuestion $question
     * @return array|null ['score' => float, 'max' => float] or null
     */
    protected function calculateMCQScore($response, $question): ?array
    {
        $selectedAnswerIds = $response->selected_answer_ids ?? [];
        if (empty($selectedAnswerIds)) {
            return null;
        }

        // Get scores for selected answers
        $scores = DB::table('answer_scores')
            ->whereIn('answer_id', $selectedAnswerIds)
            ->pluck('score_value', 'answer_id')
            ->toArray();

        $totalScore = 0;
        foreach ($selectedAnswerIds as $answerId) {
            $totalScore += (float) ($scores[$answerId] ?? 0);
        }

        // Get max possible score for this question
        $maxScore = (float) ($question->max_score ?? 0);
        if ($maxScore == 0) {
            // If no max_score set, try to get max from all answers
            $maxScore = DB::table('answer_scores')
                ->whereIn('answer_id', DB::table('assessment_answers')
                    ->where('question_id', $question->id)
                    ->pluck('id'))
                ->max('score_value') ?? 0;
        }

        return [
            'score' => $totalScore,
            'max' => $maxScore > 0 ? $maxScore : 1, // Avoid division by zero
        ];
    }

    /**
     * Determine the status of the participant in the assessment
     *
     * @param Assessment $assessment
     * @param User $participant
     * @return string
     */
    protected function determineStatus(Assessment $assessment, User $participant): string
    {
        $responses = ParticipantResponse::where('assessment_id', $assessment->id)
            ->where('participant_id', $participant->id)
            ->with('question')
            ->get();

        if ($responses->isEmpty()) {
            return 'not_started';
        }

        $totalQuestions = $assessment->questions()->count();
        $answeredQuestions = $responses->count();

        // Check if all questions are answered
        if ($answeredQuestions >= $totalQuestions) {
            // Check if all written questions are graded (if any)
            $writtenResponses = $responses->filter(function ($response) {
                return $response->question && $response->question->question_type === 'written';
            });

            if ($writtenResponses->isEmpty()) {
                // No written questions, assessment is complete
                return 'completed';
            }

            // Check if all written responses are graded
            $allGraded = $writtenResponses->every(function ($response) {
                return $response->graded_at !== null;
            });

            return $allGraded ? 'completed' : 'in_progress';
        }

        return 'in_progress';
    }
}

