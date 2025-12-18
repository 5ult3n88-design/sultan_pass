<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class SurveyController extends Controller
{
    public function take(int $assessmentId): View
    {
        $questions = $this->sampleQuestions();

        return view('surveys.take', [
            'assessmentId' => $assessmentId,
            'questions' => $questions,
            'progress' => 40,
            'timeRemaining' => '00:24:18',
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

    protected function sampleQuestions(): Collection
    {
        return collect([
            [
                'id' => 1,
                'type' => 'scale',
                'title' => __('Leadership under pressure'),
                'prompt' => __('Rate how confident you feel leading a team during high-stress situations.'),
                'options' => range(1, 5),
            ],
            [
                'id' => 2,
                'type' => 'multiple_choice',
                'title' => __('Decision making style'),
                'prompt' => __('Choose the response that best matches your natural approach.'),
                'options' => [
                    __('Analyze data before deciding'),
                    __('Seek consensus from the team'),
                    __('Trust experience and act quickly'),
                    __('Escalate to leadership'),
                ],
            ],
            [
                'id' => 3,
                'type' => 'essay',
                'title' => __('Strategic reflection'),
                'prompt' => __('Describe a situation where you transformed feedback into a successful outcome.'),
            ],
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
