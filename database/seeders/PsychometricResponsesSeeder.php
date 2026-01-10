<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentAnswer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PsychometricResponsesSeeder extends Seeder
{
    public function run(): void
    {
        // Find the psychometric assessment we created
        $assessment = Assessment::where('type', 'psychometric')
            ->where('scoring_mode', 'categorical')
            ->orderBy('id', 'desc')
            ->first();

        if (!$assessment) {
            $this->command->error('Please run PsychometricAssessmentSeeder first!');
            return;
        }

        // Get all participant users
        $participants = User::where('role', 'participant')->get();

        if ($participants->isEmpty()) {
            $this->command->error('No participants found. Please run DatabaseSeeder first!');
            return;
        }

        // Get all questions for this assessment
        $questions = AssessmentQuestion::where('assessment_id', $assessment->id)
            ->orderBy('order')
            ->get();

        if ($questions->isEmpty()) {
            $this->command->error('No questions found for this assessment!');
            return;
        }

        $responseCount = 0;

        // Create responses for each participant
        foreach ($participants as $participant) {
            // Create assessment_participants entry
            DB::table('assessment_participants')->updateOrInsert(
                [
                    'assessment_id' => $assessment->id,
                    'participant_id' => $participant->id,
                ],
                [
                    'status' => 'completed',
                    'score' => null, // Will be calculated from responses
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now()->subDays(rand(1, 20)),
                ]
            );

            // Create responses for each question
            foreach ($questions as $question) {
                // Get all answers for this question
                $answers = AssessmentAnswer::where('question_id', $question->id)
                    ->orderBy('order')
                    ->get();

                if ($answers->isEmpty()) {
                    continue;
                }

                // Randomly select one answer (or sometimes multiple for variety)
                $selectedAnswers = $answers->random(1)->pluck('id')->toArray();

                // Calculate category scores from selected answers
                $categoryScores = [];
                $weights = DB::table('answer_category_weights as acw')
                    ->join('assessment_categories as ac', 'ac.id', '=', 'acw.category_id')
                    ->whereIn('acw.answer_id', $selectedAnswers)
                    ->select('ac.id', 'acw.weight')
                    ->get();

                foreach ($weights as $weight) {
                    $categoryId = $weight->id;
                    if (!isset($categoryScores[$categoryId])) {
                        $categoryScores[$categoryId] = 0;
                    }
                    $categoryScores[$categoryId] += (float) $weight->weight;
                }

                // Create participant response
                DB::table('participant_responses')->updateOrInsert(
                    [
                        'assessment_id' => $assessment->id,
                        'participant_id' => $participant->id,
                        'question_id' => $question->id,
                    ],
                    [
                        'selected_answer_ids' => json_encode($selectedAnswers),
                        'graded_categories' => !empty($categoryScores) ? json_encode($categoryScores) : null,
                        'created_at' => now()->subDays(rand(1, 30)),
                        'updated_at' => now()->subDays(rand(1, 20)),
                    ]
                );

                $responseCount++;
            }
        }

        $this->command->info("Created {$responseCount} participant responses for " . $participants->count() . " participants!");
        $this->command->info("You can now view results in the Examinee Performance Dashboard!");
    }
}

