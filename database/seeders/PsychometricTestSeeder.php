<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Test;
use App\Models\TestAnswerChoice;
use App\Models\TestCategory;
use App\Models\TestQuestion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PsychometricTestSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?: User::where('role', 'assessor')->first();
        if (! $admin) {
            $this->command->warn('No admin/assessor user found. Please create one first.');
            return;
        }

        $existing = Test::where('title', 'Psychometric Profile Test')->first();
        if ($existing) {
            $this->command->info('Psychometric Profile Test already exists. Skipping.');
            return;
        }

        $languages = Language::whereIn('code', ['en', 'ar'])->pluck('id', 'code');
        if ($languages->count() < 2) {
            $this->command->warn('English and Arabic languages not found. Creating them...');
            $enLang = Language::firstOrCreate(['code' => 'en'], ['name' => 'English']);
            $arLang = Language::firstOrCreate(['code' => 'ar'], ['name' => 'Arabic']);
            $languages = collect(['en' => $enLang->id, 'ar' => $arLang->id]);
        }

        DB::beginTransaction();
        try {
            $test = Test::create([
                'title' => 'Psychometric Profile Test',
                'description' => 'A short categorical psychometric assessment for behavior patterns.',
                'test_type' => 'categorical',
                'duration_minutes' => 20,
                'status' => 'published',
                'created_by' => $admin->id,
            ]);

            DB::table('test_translations')->insert([
                [
                    'test_id' => $test->id,
                    'language_id' => $languages['en'],
                    'title' => 'Psychometric Profile Test',
                    'description' => 'A short categorical psychometric assessment for behavior patterns.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'test_id' => $test->id,
                    'language_id' => $languages['ar'],
                    'title' => 'اختبار الملف النفسي',
                    'description' => 'تقييم نفسي فئوي قصير لأنماط السلوك.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            $categories = [
                ['name_en' => 'Leadership', 'name_ar' => 'القيادة', 'color' => '#f59e0b'],
                ['name_en' => 'Collaboration', 'name_ar' => 'التعاون', 'color' => '#10b981'],
                ['name_en' => 'Adaptability', 'name_ar' => 'المرونة', 'color' => '#3b82f6'],
                ['name_en' => 'Detail Focus', 'name_ar' => 'الاهتمام بالتفاصيل', 'color' => '#8b5cf6'],
            ];

            $categoryIdMap = [];
            foreach ($categories as $index => $categoryData) {
                $category = TestCategory::create([
                    'test_id' => $test->id,
                    'name' => $categoryData['name_en'],
                    'description' => null,
                    'color' => $categoryData['color'],
                    'order' => $index,
                ]);
                $categoryIdMap[$index] = $category->id;

                DB::table('test_category_translations')->insert([
                    [
                        'test_category_id' => $category->id,
                        'language_id' => $languages['en'],
                        'name' => $categoryData['name_en'],
                        'description' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'test_category_id' => $category->id,
                        'language_id' => $languages['ar'],
                        'name' => $categoryData['name_ar'],
                        'description' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            }

            $questions = [
                [
                    'text_en' => 'When a plan changes suddenly, I usually:',
                    'text_ar' => 'عندما تتغير الخطة فجأة، عادةً:',
                    'choices' => [
                        ['en' => 'Take charge and decide the new direction.', 'ar' => 'أتولى القيادة وأحدد الاتجاه الجديد.', 'cat' => 0],
                        ['en' => 'Ask the team for input and align quickly.', 'ar' => 'أستشير الفريق ونتفق بسرعة.', 'cat' => 1],
                        ['en' => 'Adapt fast and try a new approach.', 'ar' => 'أتأقلم بسرعة وأجرب نهجاً جديداً.', 'cat' => 2],
                        ['en' => 'Review details to avoid missing anything.', 'ar' => 'أراجع التفاصيل لتجنب الأخطاء.', 'cat' => 3],
                    ],
                ],
                [
                    'text_en' => 'In group work, I most often:',
                    'text_ar' => 'في العمل الجماعي، غالباً:',
                    'choices' => [
                        ['en' => 'Set the pace and motivate the team.', 'ar' => 'أحدد الوتيرة وأحفّز الفريق.', 'cat' => 0],
                        ['en' => 'Support others and keep harmony.', 'ar' => 'أدعم الآخرين وأحافظ على الانسجام.', 'cat' => 1],
                        ['en' => 'Stay flexible and fill gaps as needed.', 'ar' => 'أبقى مرناً وأسُد الفجوات عند الحاجة.', 'cat' => 2],
                        ['en' => 'Track tasks and ensure accuracy.', 'ar' => 'أتابع المهام وأضمن الدقة.', 'cat' => 3],
                    ],
                ],
                [
                    'text_en' => 'When solving a problem, I prefer to:',
                    'text_ar' => 'عند حل مشكلة، أفضل:',
                    'choices' => [
                        ['en' => 'Decide quickly and move forward.', 'ar' => 'أتخذ قراراً سريعاً وأمضي قدماً.', 'cat' => 0],
                        ['en' => 'Discuss options with the team.', 'ar' => 'أنافع الخيارات مع الفريق.', 'cat' => 1],
                        ['en' => 'Try different options until one works.', 'ar' => 'أجرب عدة خيارات حتى ينجح أحدها.', 'cat' => 2],
                        ['en' => 'Analyze details before choosing.', 'ar' => 'أحلل التفاصيل قبل الاختيار.', 'cat' => 3],
                    ],
                ],
                [
                    'text_en' => 'Under pressure, I usually:',
                    'text_ar' => 'تحت الضغط، عادةً:',
                    'choices' => [
                        ['en' => 'Lead and keep everyone focused.', 'ar' => 'أقود وأحافظ على التركيز.', 'cat' => 0],
                        ['en' => 'Support others and stay calm.', 'ar' => 'أدعم الآخرين وأبقى هادئاً.', 'cat' => 1],
                        ['en' => 'Adjust quickly to new demands.', 'ar' => 'أتكيف بسرعة مع المتطلبات الجديدة.', 'cat' => 2],
                        ['en' => 'Double-check details to avoid mistakes.', 'ar' => 'أراجع التفاصيل لتجنب الأخطاء.', 'cat' => 3],
                    ],
                ],
                [
                    'text_en' => 'In my tasks, I value most:',
                    'text_ar' => 'في مهامي، أقدّر أكثر:',
                    'choices' => [
                        ['en' => 'Clear direction and ownership.', 'ar' => 'اتجاه واضح ومسؤولية.', 'cat' => 0],
                        ['en' => 'Team agreement and support.', 'ar' => 'توافق الفريق والدعم.', 'cat' => 1],
                        ['en' => 'Flexibility and variety.', 'ar' => 'المرونة والتنوع.', 'cat' => 2],
                        ['en' => 'Precision and correctness.', 'ar' => 'الدقة والصحة.', 'cat' => 3],
                    ],
                ],
            ];

            foreach ($questions as $qIndex => $questionData) {
                $question = TestQuestion::create([
                    'test_id' => $test->id,
                    'question_text' => $questionData['text_en'],
                    'question_type' => 'multiple_choice',
                    'marks' => 1,
                    'order' => $qIndex,
                    'is_required' => true,
                ]);

                DB::table('test_question_translations')->insert([
                    [
                        'test_question_id' => $question->id,
                        'language_id' => $languages['en'],
                        'question_text' => $questionData['text_en'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'test_question_id' => $question->id,
                        'language_id' => $languages['ar'],
                        'question_text' => $questionData['text_ar'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);

                foreach ($questionData['choices'] as $choiceIndex => $choiceData) {
                    $choice = TestAnswerChoice::create([
                        'test_question_id' => $question->id,
                        'choice_text' => $choiceData['en'],
                        'is_correct' => false,
                        'category_id' => $categoryIdMap[$choiceData['cat']] ?? null,
                        'order' => $choiceIndex,
                    ]);

                    DB::table('test_answer_choice_translations')->insert([
                        [
                            'test_answer_choice_id' => $choice->id,
                            'language_id' => $languages['en'],
                            'choice_text' => $choiceData['en'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'test_answer_choice_id' => $choice->id,
                            'language_id' => $languages['ar'],
                            'choice_text' => $choiceData['ar'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    ]);
                }
            }

            DB::commit();
            $this->command->info('Psychometric Profile Test created.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error('Failed to create psychometric test: ' . $e->getMessage());
        }
    }
}
