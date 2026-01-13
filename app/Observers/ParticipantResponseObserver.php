<?php

namespace App\Observers;

use App\Models\ParticipantResponse;
use App\Services\AssessmentScoreService;

class ParticipantResponseObserver
{
    protected $scoreService;

    public function __construct(AssessmentScoreService $scoreService)
    {
        $this->scoreService = $scoreService;
    }

    /**
     * Handle the ParticipantResponse "saved" event.
     * This fires after both create and update operations.
     */
    public function saved(ParticipantResponse $response): void
    {
        // Only calculate score if the response has data that affects scoring
        if ($this->shouldCalculateScore($response)) {
            $assessment = $response->assessment;
            $participant = $response->participant;

            if ($assessment && $participant) {
                // Recalculate the overall assessment score
                $this->scoreService->calculateAndUpdateScore($assessment, $participant);
            }
        }
    }

    /**
     * Determine if score calculation should be triggered
     */
    protected function shouldCalculateScore(ParticipantResponse $response): bool
    {
        // For MCQ questions, calculate if answer is selected
        if ($response->selected_answer_ids && !empty($response->selected_answer_ids)) {
            return true;
        }

        // For written questions, calculate if graded
        if ($response->graded_at !== null) {
            return true;
        }

        return false;
    }
}



