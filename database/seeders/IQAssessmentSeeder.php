<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Language;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IQAssessmentSeeder extends Seeder
{
    public function run(): void
    {
        $englishLang = Language::where('code', 'en')->first();
        $admin = User::where('role', 'admin')->first();

        if (! $englishLang || ! $admin) {
            $this->command->error('Please run DatabaseSeeder first to create languages and admin user.');
            return;
        }

        // Create a dummy IQ assessment (out of 100%)
        $assessment = Assessment::create([
            'type' => 'written_test',
            'created_by' => $admin->id,
            'start_date' => now()->subDays(15),
            'end_date' => now()->addDays(45),
            'status' => 'active',
            'scoring_mode' => 'percentile',
            'max_total_score' => 100.00,
        ]);

        DB::table('assessment_translations')->insert([
            'assessment_id' => $assessment->id,
            'language_id' => $englishLang->id,
            'title' => 'General IQ Test (Dummy)',
            'description' => 'A dummy IQ test used for demonstrating IQ results on the dashboard, scored out of 100%.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Attach all participants with dummy IQ scores (0-100)
        $participants = User::where('role', 'participant')->get();

        $count = 0;
        foreach ($participants as $index => $participant) {
            // Generate a realistic IQ percentage score
            // Some high, some medium, some low
            if ($index % 5 === 0) {
                $score = rand(85, 100); // high
            } elseif ($index % 5 === 4) {
                $score = rand(40, 60); // low
            } else {
                $score = rand(60, 85); // medium
            }

            DB::table('assessment_participants')->updateOrInsert(
                [
                    'assessment_id' => $assessment->id,
                    'participant_id' => $participant->id,
                ],
                [
                    'status' => 'completed',
                    'score' => $score,
                    'feedback' => 'Dummy IQ test score for dashboard visualization.',
                    'created_at' => now()->subDays(rand(5, 20)),
                    'updated_at' => now()->subDays(rand(1, 4)),
                ]
            );

            $count++;
        }

        $this->command->info("Created dummy IQ assessment (ID: {$assessment->id}) with IQ scores for {$count} participants (0–100%).");
    }
}


