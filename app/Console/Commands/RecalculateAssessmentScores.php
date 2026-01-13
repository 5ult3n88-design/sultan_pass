<?php

namespace App\Console\Commands;

use App\Models\Assessment;
use App\Models\User;
use App\Services\AssessmentScoreService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateAssessmentScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assessments:recalculate-scores 
                            {--participant= : Recalculate for specific participant ID}
                            {--assessment= : Recalculate for specific assessment ID}
                            {--fix-old-scores : Also fix old 0-10 scale scores by multiplying by 10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate assessment scores for all participants from their responses';

    protected $scoreService;

    public function __construct(AssessmentScoreService $scoreService)
    {
        parent::__construct();
        $this->scoreService = $scoreService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting score recalculation...');

        // Fix old scores if requested
        if ($this->option('fix-old-scores')) {
            $this->fixOldScaleScores();
        }

        $participantId = $this->option('participant');
        $assessmentId = $this->option('assessment');

        if ($participantId) {
            $participant = User::find($participantId);
            if (!$participant) {
                $this->error("Participant with ID {$participantId} not found.");
                return 1;
            }
            $this->recalculateForParticipant($participant);
        } elseif ($assessmentId) {
            $assessment = Assessment::find($assessmentId);
            if (!$assessment) {
                $this->error("Assessment with ID {$assessmentId} not found.");
                return 1;
            }
            $this->recalculateForAssessment($assessment);
        } else {
            $this->recalculateAll();
        }

        $this->info('Score recalculation completed!');
        return 0;
    }

    protected function fixOldScaleScores(): void
    {
        $this->info('Fixing old 0-10 scale scores...');

        // Find scores that are likely on the old 0-10 scale (less than 20)
        $updated = DB::table('assessment_participants')
            ->whereNotNull('score')
            ->where('score', '<', 20)
            ->update([
                'score' => DB::raw('score * 10'),
                'updated_at' => now(),
            ]);

        $this->info("Fixed {$updated} scores from old 0-10 scale to 0-100 scale.");
    }

    protected function recalculateForParticipant(User $participant): void
    {
        $this->info("Recalculating scores for participant: {$participant->full_name} ({$participant->id})");

        $assessments = Assessment::whereHas('responses', function ($query) use ($participant) {
            $query->where('participant_id', $participant->id);
        })->get();

        $count = 0;
        foreach ($assessments as $assessment) {
            $score = $this->scoreService->calculateAndUpdateScore($assessment, $participant);
            if ($score !== null) {
                $count++;
                $this->line("  Assessment {$assessment->id}: {$score}%");
            }
        }

        $this->info("Recalculated scores for {$count} assessments.");
    }

    protected function recalculateForAssessment(Assessment $assessment): void
    {
        $this->info("Recalculating scores for assessment: {$assessment->id}");

        $participants = User::whereHas('participantResponses', function ($query) use ($assessment) {
            $query->where('assessment_id', $assessment->id);
        })->get();

        $count = 0;
        foreach ($participants as $participant) {
            $score = $this->scoreService->calculateAndUpdateScore($assessment, $participant);
            if ($score !== null) {
                $count++;
                $this->line("  Participant {$participant->full_name}: {$score}%");
            }
        }

        $this->info("Recalculated scores for {$count} participants.");
    }

    protected function recalculateAll(): void
    {
        $this->info('Recalculating scores for all participants and assessments...');

        $assessments = Assessment::whereHas('responses')->get();
        $totalAssessments = $assessments->count();
        $this->info("Found {$totalAssessments} assessments with responses.");

        $bar = $this->output->createProgressBar($totalAssessments);
        $bar->start();

        $totalRecalculated = 0;

        foreach ($assessments as $assessment) {
            $participants = User::whereHas('participantResponses', function ($query) use ($assessment) {
                $query->where('assessment_id', $assessment->id);
            })->get();

            foreach ($participants as $participant) {
                $score = $this->scoreService->calculateAndUpdateScore($assessment, $participant);
                if ($score !== null) {
                    $totalRecalculated++;
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Recalculated scores for {$totalRecalculated} participant-assessment combinations.");
    }
}



