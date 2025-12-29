<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\User;
use App\Models\Assessment;
use App\Models\Competency;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Seed Languages
        $languages = [
            ['code' => 'en', 'name' => 'English'],
            ['code' => 'ar', 'name' => 'العربية'],
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate(
                ['code' => $language['code']],
                $language
            );
        }

        $englishLang = Language::where('code', 'en')->first();

        // Seed Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@pass.local'],
            [
                'username'  => 'superadmin',
                'full_name' => 'Super Administrator',
                'password'  => Hash::make('Admin@12345'),
                'role'      => 'admin',
                'status'    => 'active',
            ]
        );

        // Seed Competencies
        $competenciesData = [
            ['category' => 'leadership', 'name' => 'Strategic Thinking', 'description' => 'Ability to think strategically and plan for the future'],
            ['category' => 'leadership', 'name' => 'Decision Making', 'description' => 'Making effective and timely decisions'],
            ['category' => 'leadership', 'name' => 'Team Leadership', 'description' => 'Leading and motivating teams effectively'],
            ['category' => 'behavioral', 'name' => 'Communication', 'description' => 'Clear and effective communication skills'],
            ['category' => 'behavioral', 'name' => 'Problem Solving', 'description' => 'Analyzing and solving complex problems'],
            ['category' => 'behavioral', 'name' => 'Teamwork', 'description' => 'Working collaboratively with others'],
            ['category' => 'behavioral', 'name' => 'Adaptability', 'description' => 'Adapting to change and new situations'],
            ['category' => 'technical', 'name' => 'Technical Expertise', 'description' => 'Job-specific technical skills'],
            ['category' => 'technical', 'name' => 'Analytical Skills', 'description' => 'Analyzing data and information'],
        ];

        $competencyIds = [];
        foreach ($competenciesData as $index => $comp) {
            $competency = Competency::firstOrCreate(['category' => $comp['category']]);
            
            DB::table('competency_translations')->updateOrInsert(
                ['competency_id' => $competency->id, 'language_id' => $englishLang->id],
                ['name' => $comp['name'], 'description' => $comp['description'], 'created_at' => now(), 'updated_at' => now()]
            );
            
            $competencyIds[] = $competency->id;
        }

        // Seed 12 Participant Users with varied backgrounds
        $participants = [
            ['username' => 'john.smith', 'full_name' => 'John Smith', 'email' => 'john.smith@uae.gov.ae', 'department' => 'Operations', 'rank' => 'Senior Officer'],
            ['username' => 'sarah.ahmed', 'full_name' => 'Sarah Ahmed', 'email' => 'sarah.ahmed@uae.gov.ae', 'department' => 'Strategic Planning', 'rank' => 'Director'],
            ['username' => 'mohammed.ali', 'full_name' => 'Mohammed Ali', 'email' => 'mohammed.ali@uae.gov.ae', 'department' => 'Human Resources', 'rank' => 'Manager'],
            ['username' => 'fatima.hassan', 'full_name' => 'Fatima Hassan', 'email' => 'fatima.hassan@uae.gov.ae', 'department' => 'Finance', 'rank' => 'Specialist'],
            ['username' => 'ahmed.omar', 'full_name' => 'Ahmed Omar', 'email' => 'ahmed.omar@uae.gov.ae', 'department' => 'IT Services', 'rank' => 'Technical Lead'],
            ['username' => 'layla.khalid', 'full_name' => 'Layla Khalid', 'email' => 'layla.khalid@uae.gov.ae', 'department' => 'Communications', 'rank' => 'Officer'],
            ['username' => 'omar.rashid', 'full_name' => 'Omar Rashid', 'email' => 'omar.rashid@uae.gov.ae', 'department' => 'Security', 'rank' => 'Chief Officer'],
            ['username' => 'noura.salem', 'full_name' => 'Noura Salem', 'email' => 'noura.salem@uae.gov.ae', 'department' => 'Legal Affairs', 'rank' => 'Legal Advisor'],
            ['username' => 'khalid.abdullah', 'full_name' => 'Khalid Abdullah', 'email' => 'khalid.abdullah@uae.gov.ae', 'department' => 'Project Management', 'rank' => 'Project Manager'],
            ['username' => 'maryam.hassan', 'full_name' => 'Maryam Hassan', 'email' => 'maryam.hassan@uae.gov.ae', 'department' => 'Public Relations', 'rank' => 'PR Specialist'],
            ['username' => 'ali.mohamed', 'full_name' => 'Ali Mohamed', 'email' => 'ali.mohamed@uae.gov.ae', 'department' => 'Research & Development', 'rank' => 'Senior Researcher'],
            ['username' => 'hessa.al.nuaimi', 'full_name' => 'Hessa Al Nuaimi', 'email' => 'hessa.al.nuaimi@uae.gov.ae', 'department' => 'Training & Development', 'rank' => 'Training Manager'],
        ];

        $participantUsers = [];
        foreach ($participants as $participant) {
            $participantUsers[] = User::updateOrCreate(
                ['email' => $participant['email']],
                array_merge($participant, ['password' => Hash::make('Pass@12345'), 'role' => 'participant', 'status' => 'active'])
            );
        }

        // Seed 5 Varied Assessments
        $assessmentTypes = [
            ['type' => 'psychometric', 'title' => 'Psychometric Assessment - Q1 2025', 'description' => 'Comprehensive psychometric evaluation covering personality traits and behavioral patterns'],
            ['type' => 'interview', 'title' => 'Leadership Interview Assessment', 'description' => 'Structured interview focusing on leadership capabilities and strategic vision'],
            ['type' => 'group_exercise', 'title' => 'Team Collaboration Exercise', 'description' => 'Group activity assessing teamwork, communication, and problem-solving skills'],
            ['type' => 'written_test', 'title' => 'Technical Competency Test', 'description' => 'Written examination evaluating technical knowledge and analytical abilities'],
            ['type' => 'role_play', 'title' => 'Crisis Management Simulation', 'description' => 'Role-play scenario testing decision-making under pressure and crisis response'],
        ];

        foreach ($assessmentTypes as $assessmentData) {
            $assessment = Assessment::create([
                'type' => $assessmentData['type'],
                'created_by' => $admin->id,
                'start_date' => now()->subDays(rand(30, 120)),
                'end_date' => now()->addDays(rand(10, 60)),
                'status' => 'active',
            ]);

            DB::table('assessment_translations')->insert([
                'assessment_id' => $assessment->id,
                'language_id' => $englishLang->id,
                'title' => $assessmentData['title'],
                'description' => $assessmentData['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $shuffledIds = $competencyIds;
            shuffle($shuffledIds);
            $selectedCompetencies = array_slice($shuffledIds, 0, rand(5, 8));

            foreach ($selectedCompetencies as $competencyId) {
                DB::table('assessment_items')->insert([
                    'assessment_id' => $assessment->id,
                    'competency_id' => $competencyId,
                    'max_score' => 5,
                    'weight' => round(0.5 + (rand(0, 15) / 10), 2), // 0.5 to 2.0
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Add varied participant performance
            foreach ($participantUsers as $index => $participant) {
                // Create performance variation: some excel, some struggle, most are average
                $performanceLevel = $this->determinePerformanceLevel($index);
                $overallScore = $this->generateScore($performanceLevel);
                
                DB::table('assessment_participants')->insert([
                    'assessment_id' => $assessment->id,
                    'participant_id' => $participant->id,
                    'status' => rand(1, 10) > 1 ? 'completed' : 'in_progress', // 90% completed
                    'score' => $overallScore,
                    'feedback' => $this->generateDetailedFeedback($overallScore, $participant->full_name),
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now()->subDays(rand(1, 20)),
                ]);

                foreach ($selectedCompetencies as $competencyId) {
                    $competencyScore = $this->generateCompetencyScore($performanceLevel, $competencyId, $selectedCompetencies);
                    
                    DB::table('assessor_notes')->insert([
                        'assessment_id' => $assessment->id,
                        'assessor_id' => $admin->id,
                        'participant_id' => $participant->id,
                        'competency_id' => $competencyId,
                        'score' => $competencyScore,
                        'notes' => $this->generateDetailedCompetencyNotes($competencyScore),
                        'created_at' => now()->subDays(rand(1, 25)),
                        'updated_at' => now()->subDays(rand(1, 20)),
                    ]);
                }
            }
        }

        $this->command->info('Database seeded successfully with 12 participants and 5 varied assessments!');
    }

    private function determinePerformanceLevel($index): string
    {
        // Create realistic distribution: 2 high performers, 2 low performers, 8 medium
        if ($index < 2) return 'high';
        if ($index >= 10) return 'low';
        return 'medium';
    }

    private function generateScore($performanceLevel): float
    {
        switch ($performanceLevel) {
            case 'high':
                return rand(85, 95) / 10; // 8.5 to 9.5
            case 'low':
                return rand(50, 65) / 10; // 5.0 to 6.5
            default:
                return rand(65, 85) / 10; // 6.5 to 8.5
        }
    }

    private function generateCompetencyScore($performanceLevel, $competencyId, $allCompetencies): float
    {
        $baseScore = $this->generateScore($performanceLevel);
        // Add some variation per competency
        $variation = (rand(-5, 5) / 10);
        $score = $baseScore + $variation;
        return max(2.0, min(5.0, $score));
    }

    private function generateDetailedFeedback($score, $name): string
    {
        if ($score >= 8.5) {
            return "$name demonstrated exceptional performance across all competencies. Shows strong leadership potential, excellent problem-solving abilities, and outstanding communication skills. Highly recommended for advancement opportunities.";
        } elseif ($score >= 7.5) {
            return "$name showed strong performance with notable strengths in key areas. Demonstrates good analytical thinking and effective teamwork. Shows clear potential for career advancement with targeted development.";
        } elseif ($score >= 6.5) {
            return "$name delivered solid performance with consistent competency demonstration. Shows capability in most areas with room for development in strategic thinking and decision-making. Recommend focused training programs.";
        } elseif ($score >= 5.5) {
            return "$name showed adequate performance but requires significant development in several competencies. Needs improvement in leadership skills and technical expertise. Recommend comprehensive development plan.";
        } else {
            return "$name's performance indicates need for substantial improvement across multiple competencies. Requires intensive training and close mentoring to meet role requirements. Consider reassignment or extended development period.";
        }
    }

    private function generateDetailedCompetencyNotes($score): string
    {
        if ($score >= 4.5) {
            return 'Exceptional demonstration of this competency with clear evidence of mastery. Consistently exceeds expectations and serves as a role model for others.';
        } elseif ($score >= 4.0) {
            return 'Strong performance in this competency area. Shows thorough understanding and consistent application of relevant skills and knowledge.';
        } elseif ($score >= 3.5) {
            return 'Good competency level with reliable performance. Demonstrates understanding of core concepts and applies them effectively in most situations.';
        } elseif ($score >= 3.0) {
            return 'Adequate competency demonstration with some gaps. Shows basic understanding but needs more consistent application and deeper knowledge.';
        } elseif ($score >= 2.5) {
            return 'Below expected competency level. Requires focused development and additional training to meet performance standards.';
        } else {
            return 'Significant competency gap identified. Needs immediate attention through structured training program and ongoing mentorship.';
        }
    }
}
