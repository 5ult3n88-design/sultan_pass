<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentAnswer;
use App\Models\Language;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PsychometricAssessmentSeeder extends Seeder
{
    public function run(): void
    {
        $englishLang = Language::where('code', 'en')->first();
        $admin = User::where('role', 'admin')->first();

        if (!$englishLang || !$admin) {
            $this->command->error('Please run DatabaseSeeder first to create languages and admin user.');
            return;
        }

        // Create Psychometric Assessment
        $assessment = Assessment::create([
            'type' => 'psychometric',
            'created_by' => $admin->id,
            'start_date' => now()->subDays(30),
            'end_date' => now()->addDays(60),
            'status' => 'active',
            'scoring_mode' => 'categorical',
        ]);

        // Create assessment translation
        DB::table('assessment_translations')->insert([
            'assessment_id' => $assessment->id,
            'language_id' => $englishLang->id,
            'title' => 'Comprehensive Psychometric Assessment',
            'description' => 'This assessment evaluates your personality traits, behavioral patterns, and cognitive abilities across multiple dimensions.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Categories (Strengths/Weaknesses areas)
        $categories = [
            ['name' => 'Leadership', 'description' => 'Ability to lead and influence others', 'color' => '#3B82F6', 'order' => 1],
            ['name' => 'Communication', 'description' => 'Effectiveness in verbal and written communication', 'color' => '#10B981', 'order' => 2],
            ['name' => 'Problem Solving', 'description' => 'Analytical thinking and problem-solving skills', 'color' => '#F59E0B', 'order' => 3],
            ['name' => 'Teamwork', 'description' => 'Collaboration and working effectively in teams', 'color' => '#8B5CF6', 'order' => 4],
            ['name' => 'Adaptability', 'description' => 'Flexibility and ability to adapt to change', 'color' => '#EF4444', 'order' => 5],
            ['name' => 'Emotional Intelligence', 'description' => 'Self-awareness and emotional management', 'color' => '#EC4899', 'order' => 6],
        ];

        $categoryModels = [];
        foreach ($categories as $catData) {
            $category = AssessmentCategory::create([
                'assessment_id' => $assessment->id,
                'name' => $catData['name'],
                'description' => $catData['description'],
                'color' => $catData['color'],
                'order' => $catData['order'],
            ]);
            $categoryModels[$catData['name']] = $category;
        }

        // Create Questions with Multiple Choice Answers
        $questions = [
            [
                'question' => 'When faced with a challenging problem, I prefer to:',
                'answers' => [
                    ['text' => 'Analyze it thoroughly before taking action', 'weights' => ['Problem Solving' => 3.0, 'Leadership' => 1.0]],
                    ['text' => 'Discuss it with my team immediately', 'weights' => ['Teamwork' => 3.0, 'Communication' => 2.0]],
                    ['text' => 'Take immediate action based on my experience', 'weights' => ['Adaptability' => 2.5, 'Leadership' => 2.5]],
                    ['text' => 'Consider the emotional impact on others first', 'weights' => ['Emotional Intelligence' => 3.0, 'Communication' => 2.0]],
                ]
            ],
            [
                'question' => 'In a team meeting, I typically:',
                'answers' => [
                    ['text' => 'Take charge and guide the discussion', 'weights' => ['Leadership' => 3.0, 'Communication' => 2.0]],
                    ['text' => 'Listen carefully and contribute when needed', 'weights' => ['Teamwork' => 3.0, 'Communication' => 2.0]],
                    ['text' => 'Focus on finding solutions to problems raised', 'weights' => ['Problem Solving' => 3.0, 'Teamwork' => 2.0]],
                    ['text' => 'Observe team dynamics and help resolve conflicts', 'weights' => ['Emotional Intelligence' => 3.0, 'Teamwork' => 2.0]],
                ]
            ],
            [
                'question' => 'When my work environment changes unexpectedly, I:',
                'answers' => [
                    ['text' => 'Quickly adapt and find new ways to succeed', 'weights' => ['Adaptability' => 3.0, 'Problem Solving' => 2.0]],
                    ['text' => 'Feel stressed but eventually adjust', 'weights' => ['Adaptability' => 1.5, 'Emotional Intelligence' => 2.0]],
                    ['text' => 'Seek support from colleagues', 'weights' => ['Teamwork' => 2.5, 'Communication' => 2.5]],
                    ['text' => 'Analyze the change and plan my response', 'weights' => ['Problem Solving' => 2.5, 'Leadership' => 2.0]],
                ]
            ],
            [
                'question' => 'When communicating with others, I prioritize:',
                'answers' => [
                    ['text' => 'Being clear and direct', 'weights' => ['Communication' => 3.0, 'Leadership' => 2.0]],
                    ['text' => 'Understanding their perspective first', 'weights' => ['Emotional Intelligence' => 3.0, 'Communication' => 2.0]],
                    ['text' => 'Building rapport and relationships', 'weights' => ['Teamwork' => 3.0, 'Emotional Intelligence' => 2.0]],
                    ['text' => 'Focusing on facts and data', 'weights' => ['Problem Solving' => 3.0, 'Communication' => 1.5]],
                ]
            ],
            [
                'question' => 'In a leadership role, I believe success comes from:',
                'answers' => [
                    ['text' => 'Making decisive decisions quickly', 'weights' => ['Leadership' => 3.0, 'Problem Solving' => 2.0]],
                    ['text' => 'Empowering and developing team members', 'weights' => ['Leadership' => 3.0, 'Teamwork' => 2.5]],
                    ['text' => 'Understanding team emotions and motivations', 'weights' => ['Emotional Intelligence' => 3.0, 'Leadership' => 2.5]],
                    ['text' => 'Adapting strategies to changing circumstances', 'weights' => ['Adaptability' => 3.0, 'Leadership' => 2.0]],
                ]
            ],
            [
                'question' => 'When working on a complex project, I:',
                'answers' => [
                    ['text' => 'Break it down into manageable steps', 'weights' => ['Problem Solving' => 3.0, 'Adaptability' => 1.5]],
                    ['text' => 'Collaborate closely with team members', 'weights' => ['Teamwork' => 3.0, 'Communication' => 2.5]],
                    ['text' => 'Take the lead and coordinate efforts', 'weights' => ['Leadership' => 3.0, 'Teamwork' => 2.0]],
                    ['text' => 'Remain flexible as requirements change', 'weights' => ['Adaptability' => 3.0, 'Problem Solving' => 2.0]],
                ]
            ],
            [
                'question' => 'When someone disagrees with me, I:',
                'answers' => [
                    ['text' => 'Try to understand their point of view', 'weights' => ['Emotional Intelligence' => 3.0, 'Communication' => 2.5]],
                    ['text' => 'Present my arguments clearly', 'weights' => ['Communication' => 3.0, 'Leadership' => 1.5]],
                    ['text' => 'Look for a compromise solution', 'weights' => ['Teamwork' => 3.0, 'Problem Solving' => 2.0]],
                    ['text' => 'Remain open to changing my position', 'weights' => ['Adaptability' => 3.0, 'Emotional Intelligence' => 2.0]],
                ]
            ],
            [
                'question' => 'I handle stress by:',
                'answers' => [
                    ['text' => 'Staying calm and analyzing the situation', 'weights' => ['Emotional Intelligence' => 3.0, 'Problem Solving' => 2.0]],
                    ['text' => 'Seeking support from others', 'weights' => ['Teamwork' => 2.5, 'Communication' => 2.5]],
                    ['text' => 'Taking action to resolve it quickly', 'weights' => ['Adaptability' => 2.5, 'Leadership' => 2.0]],
                    ['text' => 'Communicating my concerns openly', 'weights' => ['Communication' => 3.0, 'Emotional Intelligence' => 2.0]],
                ]
            ],
            [
                'question' => 'My ideal work environment includes:',
                'answers' => [
                    ['text' => 'Clear structure and defined processes', 'weights' => ['Problem Solving' => 2.0, 'Adaptability' => 1.0]],
                    ['text' => 'Collaborative team atmosphere', 'weights' => ['Teamwork' => 3.0, 'Communication' => 2.0]],
                    ['text' => 'Opportunities to lead initiatives', 'weights' => ['Leadership' => 3.0, 'Teamwork' => 1.5]],
                    ['text' => 'Flexibility and variety in tasks', 'weights' => ['Adaptability' => 3.0, 'Problem Solving' => 1.5]],
                ]
            ],
            [
                'question' => 'When making important decisions, I rely most on:',
                'answers' => [
                    ['text' => 'Data and logical analysis', 'weights' => ['Problem Solving' => 3.0, 'Leadership' => 1.5]],
                    ['text' => 'Input from trusted team members', 'weights' => ['Teamwork' => 3.0, 'Communication' => 2.0]],
                    ['text' => 'My intuition and experience', 'weights' => ['Leadership' => 2.5, 'Emotional Intelligence' => 2.5]],
                    ['text' => 'Flexibility to adjust as needed', 'weights' => ['Adaptability' => 3.0, 'Problem Solving' => 1.5]],
                ]
            ],
        ];

        $questionOrder = 1;
        foreach ($questions as $qData) {
            // Create Question
            $question = AssessmentQuestion::create([
                'assessment_id' => $assessment->id,
                'question_type' => 'mcq',
                'question_text' => $qData['question'],
                'order' => $questionOrder++,
                'is_required' => true,
            ]);

            // Create question translation
            DB::table('assessment_question_translations')->insert([
                'question_id' => $question->id,
                'language_id' => $englishLang->id,
                'question_text' => $qData['question'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create Answers
            $answerOrder = 1;
            foreach ($qData['answers'] as $answerData) {
                $answer = AssessmentAnswer::create([
                    'question_id' => $question->id,
                    'answer_text' => $answerData['text'],
                    'order' => $answerOrder++,
                ]);

                // Create answer translation
                DB::table('assessment_answer_translations')->insert([
                    'answer_id' => $answer->id,
                    'language_id' => $englishLang->id,
                    'answer_text' => $answerData['text'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Link answer to categories with weights
                foreach ($answerData['weights'] as $categoryName => $weight) {
                    if (isset($categoryModels[$categoryName])) {
                        DB::table('answer_category_weights')->insert([
                            'answer_id' => $answer->id,
                            'category_id' => $categoryModels[$categoryName]->id,
                            'weight' => $weight,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        $questionCount = $questionOrder - 1;
        $this->command->info("Created psychometric assessment with {$questionCount} questions and " . count($categories) . " categories!");
        $this->command->info("Assessment ID: {$assessment->id}");
        $this->command->info("You can now take this assessment and see results in the Examinee Performance Dashboard!");
    }
}

