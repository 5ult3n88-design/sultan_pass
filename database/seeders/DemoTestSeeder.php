<?php

namespace Database\Seeders;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestAnswerChoice;
use App\Models\Language;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoTestSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $this->command->warn('No admin user found. Please create an admin user first.');
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
            // Create Percentile Test (IQ Test)
            $iqTest = Test::create([
                'title' => 'Sample IQ Test',
                'description' => 'A basic intelligence test with logical reasoning questions',
                'test_type' => 'percentile',
                'total_marks' => 50,
                'passing_marks' => 30,
                'duration_minutes' => 30,
                'status' => 'published',
                'created_by' => $admin->id,
            ]);

            DB::table('test_translations')->insert([
                [
                    'test_id' => $iqTest->id,
                    'language_id' => $languages['en'],
                    'title' => 'Sample IQ Test',
                    'description' => 'A basic intelligence test with logical reasoning questions',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'test_id' => $iqTest->id,
                    'language_id' => $languages['ar'],
                    'title' => 'اختبار ذكاء تجريبي',
                    'description' => 'اختبار ذكاء أساسي مع أسئلة التفكير المنطقي',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // Question 1
            $q1 = TestQuestion::create([
                'test_id' => $iqTest->id,
                'question_text' => 'What is 15 + 27?',
                'question_type' => 'multiple_choice',
                'marks' => 10,
                'order' => 0,
            ]);

            DB::table('test_question_translations')->insert([
                ['test_question_id' => $q1->id, 'language_id' => $languages['en'], 'question_text' => 'What is 15 + 27?', 'created_at' => now(), 'updated_at' => now()],
                ['test_question_id' => $q1->id, 'language_id' => $languages['ar'], 'question_text' => 'ما هو 15 + 27؟', 'created_at' => now(), 'updated_at' => now()],
            ]);

            foreach ([
                ['text_en' => '40', 'text_ar' => '40', 'correct' => false],
                ['text_en' => '42', 'text_ar' => '42', 'correct' => true],
                ['text_en' => '44', 'text_ar' => '44', 'correct' => false],
                ['text_en' => '45', 'text_ar' => '45', 'correct' => false],
            ] as $index => $choice) {
                $c = TestAnswerChoice::create([
                    'test_question_id' => $q1->id,
                    'choice_text' => $choice['text_en'],
                    'is_correct' => $choice['correct'],
                    'order' => $index,
                ]);
                DB::table('test_answer_choice_translations')->insert([
                    ['test_answer_choice_id' => $c->id, 'language_id' => $languages['en'], 'choice_text' => $choice['text_en'], 'created_at' => now(), 'updated_at' => now()],
                    ['test_answer_choice_id' => $c->id, 'language_id' => $languages['ar'], 'choice_text' => $choice['text_ar'], 'created_at' => now(), 'updated_at' => now()],
                ]);
            }

            // Question 2
            $q2 = TestQuestion::create([
                'test_id' => $iqTest->id,
                'question_text' => 'Complete the sequence: 2, 4, 8, 16, __',
                'question_type' => 'multiple_choice',
                'marks' => 10,
                'order' => 1,
            ]);

            DB::table('test_question_translations')->insert([
                ['test_question_id' => $q2->id, 'language_id' => $languages['en'], 'question_text' => 'Complete the sequence: 2, 4, 8, 16, __', 'created_at' => now(), 'updated_at' => now()],
                ['test_question_id' => $q2->id, 'language_id' => $languages['ar'], 'question_text' => 'أكمل السلسلة: 2، 4، 8، 16، __', 'created_at' => now(), 'updated_at' => now()],
            ]);

            foreach ([
                ['text_en' => '20', 'text_ar' => '20', 'correct' => false],
                ['text_en' => '24', 'text_ar' => '24', 'correct' => false],
                ['text_en' => '30', 'text_ar' => '30', 'correct' => false],
                ['text_en' => '32', 'text_ar' => '32', 'correct' => true],
            ] as $index => $choice) {
                $c = TestAnswerChoice::create([
                    'test_question_id' => $q2->id,
                    'choice_text' => $choice['text_en'],
                    'is_correct' => $choice['correct'],
                    'order' => $index,
                ]);
                DB::table('test_answer_choice_translations')->insert([
                    ['test_answer_choice_id' => $c->id, 'language_id' => $languages['en'], 'choice_text' => $choice['text_en'], 'created_at' => now(), 'updated_at' => now()],
                    ['test_answer_choice_id' => $c->id, 'language_id' => $languages['ar'], 'choice_text' => $choice['text_ar'], 'created_at' => now(), 'updated_at' => now()],
                ]);
            }

            // Question 3
            $q3 = TestQuestion::create([
                'test_id' => $iqTest->id,
                'question_text' => 'If all Bloops are Razzies and all Razzies are Lazzies, then all Bloops are definitely Lazzies?',
                'question_type' => 'multiple_choice',
                'marks' => 10,
                'order' => 2,
            ]);

            DB::table('test_question_translations')->insert([
                ['test_question_id' => $q3->id, 'language_id' => $languages['en'], 'question_text' => 'If all Bloops are Razzies and all Razzies are Lazzies, then all Bloops are definitely Lazzies?', 'created_at' => now(), 'updated_at' => now()],
                ['test_question_id' => $q3->id, 'language_id' => $languages['ar'], 'question_text' => 'إذا كانت كل البلوبز هي رازيز وكل الرازيز هي لازيز، فهل كل البلوبز هي بالتأكيد لازيز؟', 'created_at' => now(), 'updated_at' => now()],
            ]);

            foreach ([
                ['text_en' => 'True', 'text_ar' => 'صح', 'correct' => true],
                ['text_en' => 'False', 'text_ar' => 'خطأ', 'correct' => false],
            ] as $index => $choice) {
                $c = TestAnswerChoice::create([
                    'test_question_id' => $q3->id,
                    'choice_text' => $choice['text_en'],
                    'is_correct' => $choice['correct'],
                    'order' => $index,
                ]);
                DB::table('test_answer_choice_translations')->insert([
                    ['test_answer_choice_id' => $c->id, 'language_id' => $languages['en'], 'choice_text' => $choice['text_en'], 'created_at' => now(), 'updated_at' => now()],
                    ['test_answer_choice_id' => $c->id, 'language_id' => $languages['ar'], 'choice_text' => $choice['text_ar'], 'created_at' => now(), 'updated_at' => now()],
                ]);
            }

            // Question 4 - Typed answer
            $q4 = TestQuestion::create([
                'test_id' => $iqTest->id,
                'question_text' => 'Solve this problem and explain your reasoning: A farmer has 17 sheep, and all but 9 die. How many are left?',
                'question_type' => 'typed',
                'marks' => 10,
                'order' => 3,
            ]);

            DB::table('test_question_translations')->insert([
                ['test_question_id' => $q4->id, 'language_id' => $languages['en'], 'question_text' => 'Solve this problem and explain your reasoning: A farmer has 17 sheep, and all but 9 die. How many are left?', 'created_at' => now(), 'updated_at' => now()],
                ['test_question_id' => $q4->id, 'language_id' => $languages['ar'], 'question_text' => 'حل هذه المسألة واشرح منطقك: لدى مزارع 17 خروفاً، وماتت جميعها إلا 9. كم بقي؟', 'created_at' => now(), 'updated_at' => now()],
            ]);

            // Question 5
            $q5 = TestQuestion::create([
                'test_id' => $iqTest->id,
                'question_text' => 'What comes next in the pattern? A, C, F, J, __',
                'question_type' => 'multiple_choice',
                'marks' => 10,
                'order' => 4,
            ]);

            DB::table('test_question_translations')->insert([
                ['test_question_id' => $q5->id, 'language_id' => $languages['en'], 'question_text' => 'What comes next in the pattern? A, C, F, J, __', 'created_at' => now(), 'updated_at' => now()],
                ['test_question_id' => $q5->id, 'language_id' => $languages['ar'], 'question_text' => 'ما هو التالي في النمط؟ A، C، F، J، __', 'created_at' => now(), 'updated_at' => now()],
            ]);

            foreach ([
                ['text_en' => 'M', 'text_ar' => 'M', 'correct' => false],
                ['text_en' => 'N', 'text_ar' => 'N', 'correct' => false],
                ['text_en' => 'O', 'text_ar' => 'O', 'correct' => true],
                ['text_en' => 'P', 'text_ar' => 'P', 'correct' => false],
            ] as $index => $choice) {
                $c = TestAnswerChoice::create([
                    'test_question_id' => $q5->id,
                    'choice_text' => $choice['text_en'],
                    'is_correct' => $choice['correct'],
                    'order' => $index,
                ]);
                DB::table('test_answer_choice_translations')->insert([
                    ['test_answer_choice_id' => $c->id, 'language_id' => $languages['en'], 'choice_text' => $choice['text_en'], 'created_at' => now(), 'updated_at' => now()],
                    ['test_answer_choice_id' => $c->id, 'language_id' => $languages['ar'], 'choice_text' => $choice['text_ar'], 'created_at' => now(), 'updated_at' => now()],
                ]);
            }

            DB::commit();
            $this->command->info('Demo IQ test created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Failed to create demo test: ' . $e->getMessage());
        }
    }
}
