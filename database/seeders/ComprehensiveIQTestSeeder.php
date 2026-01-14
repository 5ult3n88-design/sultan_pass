<?php

namespace Database\Seeders;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestAnswerChoice;
use App\Models\Language;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComprehensiveIQTestSeeder extends Seeder
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
            // Create Comprehensive IQ Test (25 questions)
            $iqTest = Test::create([
                'title' => 'Comprehensive IQ Assessment',
                'description' => 'A comprehensive intelligence test covering mathematics, logical reasoning, pattern recognition, and verbal reasoning. 25 questions to be completed in 45 minutes.',
                'test_type' => 'percentile',
                'total_marks' => 100,
                'passing_marks' => 60,
                'duration_minutes' => 45,
                'status' => 'published',
                'created_by' => $admin->id,
            ]);

            DB::table('test_translations')->insert([
                [
                    'test_id' => $iqTest->id,
                    'language_id' => $languages['en'],
                    'title' => 'Comprehensive IQ Assessment',
                    'description' => 'A comprehensive intelligence test covering mathematics, logical reasoning, pattern recognition, and verbal reasoning. 25 questions to be completed in 45 minutes.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'test_id' => $iqTest->id,
                    'language_id' => $languages['ar'],
                    'title' => 'اختبار الذكاء الشامل',
                    'description' => 'اختبار ذكاء شامل يغطي الرياضيات والتفكير المنطقي والتعرف على الأنماط والتفكير اللفظي. 25 سؤالاً يجب إكمالها في 45 دقيقة.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            $questions = $this->getQuestions();

            foreach ($questions as $index => $q) {
                $question = TestQuestion::create([
                    'test_id' => $iqTest->id,
                    'question_text' => $q['en'],
                    'question_type' => 'multiple_choice',
                    'marks' => 4,
                    'order' => $index,
                ]);

                DB::table('test_question_translations')->insert([
                    ['test_question_id' => $question->id, 'language_id' => $languages['en'], 'question_text' => $q['en'], 'created_at' => now(), 'updated_at' => now()],
                    ['test_question_id' => $question->id, 'language_id' => $languages['ar'], 'question_text' => $q['ar'], 'created_at' => now(), 'updated_at' => now()],
                ]);

                foreach ($q['choices'] as $choiceIndex => $choice) {
                    $c = TestAnswerChoice::create([
                        'test_question_id' => $question->id,
                        'choice_text' => $choice['en'],
                        'is_correct' => $choice['correct'],
                        'order' => $choiceIndex,
                    ]);
                    DB::table('test_answer_choice_translations')->insert([
                        ['test_answer_choice_id' => $c->id, 'language_id' => $languages['en'], 'choice_text' => $choice['en'], 'created_at' => now(), 'updated_at' => now()],
                        ['test_answer_choice_id' => $c->id, 'language_id' => $languages['ar'], 'choice_text' => $choice['ar'], 'created_at' => now(), 'updated_at' => now()],
                    ]);
                }
            }

            DB::commit();
            $this->command->info('Comprehensive IQ Test (25 questions) created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Failed to create IQ test: ' . $e->getMessage());
        }
    }

    private function getQuestions(): array
    {
        return [
            // MATHEMATICS (Questions 1-7)
            [
                'en' => 'What is 145 + 278?',
                'ar' => 'ما هو 145 + 278؟',
                'choices' => [
                    ['en' => '413', 'ar' => '413', 'correct' => false],
                    ['en' => '423', 'ar' => '423', 'correct' => true],
                    ['en' => '433', 'ar' => '433', 'correct' => false],
                    ['en' => '443', 'ar' => '443', 'correct' => false],
                ],
            ],
            [
                'en' => 'What is 17 × 8?',
                'ar' => 'ما هو 17 × 8؟',
                'choices' => [
                    ['en' => '126', 'ar' => '126', 'correct' => false],
                    ['en' => '136', 'ar' => '136', 'correct' => true],
                    ['en' => '146', 'ar' => '146', 'correct' => false],
                    ['en' => '156', 'ar' => '156', 'correct' => false],
                ],
            ],
            [
                'en' => 'What is 25% of 240?',
                'ar' => 'ما هو 25% من 240؟',
                'choices' => [
                    ['en' => '50', 'ar' => '50', 'correct' => false],
                    ['en' => '55', 'ar' => '55', 'correct' => false],
                    ['en' => '60', 'ar' => '60', 'correct' => true],
                    ['en' => '65', 'ar' => '65', 'correct' => false],
                ],
            ],
            [
                'en' => 'If 3x + 7 = 22, what is x?',
                'ar' => 'إذا كان 3x + 7 = 22، ما قيمة x؟',
                'choices' => [
                    ['en' => '3', 'ar' => '3', 'correct' => false],
                    ['en' => '4', 'ar' => '4', 'correct' => false],
                    ['en' => '5', 'ar' => '5', 'correct' => true],
                    ['en' => '6', 'ar' => '6', 'correct' => false],
                ],
            ],
            [
                'en' => 'What is the square root of 144?',
                'ar' => 'ما هو الجذر التربيعي لـ 144؟',
                'choices' => [
                    ['en' => '10', 'ar' => '10', 'correct' => false],
                    ['en' => '11', 'ar' => '11', 'correct' => false],
                    ['en' => '12', 'ar' => '12', 'correct' => true],
                    ['en' => '13', 'ar' => '13', 'correct' => false],
                ],
            ],
            [
                'en' => 'A train travels 240 km in 3 hours. What is its speed in km/h?',
                'ar' => 'يقطع قطار 240 كم في 3 ساعات. ما سرعته بالكم/ساعة؟',
                'choices' => [
                    ['en' => '70 km/h', 'ar' => '70 كم/ساعة', 'correct' => false],
                    ['en' => '80 km/h', 'ar' => '80 كم/ساعة', 'correct' => true],
                    ['en' => '90 km/h', 'ar' => '90 كم/ساعة', 'correct' => false],
                    ['en' => '100 km/h', 'ar' => '100 كم/ساعة', 'correct' => false],
                ],
            ],
            [
                'en' => 'What is 2³ + 3²?',
                'ar' => 'ما هو 2³ + 3²؟',
                'choices' => [
                    ['en' => '15', 'ar' => '15', 'correct' => false],
                    ['en' => '17', 'ar' => '17', 'correct' => true],
                    ['en' => '19', 'ar' => '19', 'correct' => false],
                    ['en' => '21', 'ar' => '21', 'correct' => false],
                ],
            ],

            // NUMBER SEQUENCES (Questions 8-12)
            [
                'en' => 'Complete the sequence: 2, 6, 18, 54, __',
                'ar' => 'أكمل السلسلة: 2، 6، 18، 54، __',
                'choices' => [
                    ['en' => '108', 'ar' => '108', 'correct' => false],
                    ['en' => '162', 'ar' => '162', 'correct' => true],
                    ['en' => '216', 'ar' => '216', 'correct' => false],
                    ['en' => '324', 'ar' => '324', 'correct' => false],
                ],
            ],
            [
                'en' => 'Complete the sequence: 1, 1, 2, 3, 5, 8, __',
                'ar' => 'أكمل السلسلة: 1، 1، 2، 3، 5، 8، __',
                'choices' => [
                    ['en' => '11', 'ar' => '11', 'correct' => false],
                    ['en' => '12', 'ar' => '12', 'correct' => false],
                    ['en' => '13', 'ar' => '13', 'correct' => true],
                    ['en' => '14', 'ar' => '14', 'correct' => false],
                ],
            ],
            [
                'en' => 'Complete the sequence: 3, 7, 15, 31, __',
                'ar' => 'أكمل السلسلة: 3، 7، 15، 31، __',
                'choices' => [
                    ['en' => '47', 'ar' => '47', 'correct' => false],
                    ['en' => '55', 'ar' => '55', 'correct' => false],
                    ['en' => '63', 'ar' => '63', 'correct' => true],
                    ['en' => '71', 'ar' => '71', 'correct' => false],
                ],
            ],
            [
                'en' => 'Complete the sequence: 100, 95, 85, 70, __',
                'ar' => 'أكمل السلسلة: 100، 95، 85، 70، __',
                'choices' => [
                    ['en' => '45', 'ar' => '45', 'correct' => false],
                    ['en' => '50', 'ar' => '50', 'correct' => true],
                    ['en' => '55', 'ar' => '55', 'correct' => false],
                    ['en' => '60', 'ar' => '60', 'correct' => false],
                ],
            ],
            [
                'en' => 'Complete the sequence: 2, 5, 10, 17, 26, __',
                'ar' => 'أكمل السلسلة: 2، 5، 10، 17، 26، __',
                'choices' => [
                    ['en' => '35', 'ar' => '35', 'correct' => false],
                    ['en' => '37', 'ar' => '37', 'correct' => true],
                    ['en' => '39', 'ar' => '39', 'correct' => false],
                    ['en' => '41', 'ar' => '41', 'correct' => false],
                ],
            ],

            // LOGICAL REASONING (Questions 13-18)
            [
                'en' => 'All roses are flowers. Some flowers fade quickly. Therefore:',
                'ar' => 'كل الورود أزهار. بعض الأزهار تذبل بسرعة. لذلك:',
                'choices' => [
                    ['en' => 'All roses fade quickly', 'ar' => 'كل الورود تذبل بسرعة', 'correct' => false],
                    ['en' => 'Some roses may fade quickly', 'ar' => 'بعض الورود قد تذبل بسرعة', 'correct' => true],
                    ['en' => 'No roses fade quickly', 'ar' => 'لا توجد ورود تذبل بسرعة', 'correct' => false],
                    ['en' => 'Roses never fade', 'ar' => 'الورود لا تذبل أبداً', 'correct' => false],
                ],
            ],
            [
                'en' => 'If all A are B, and all B are C, which statement must be true?',
                'ar' => 'إذا كان كل A هو B، وكل B هو C، أي عبارة يجب أن تكون صحيحة؟',
                'choices' => [
                    ['en' => 'All C are A', 'ar' => 'كل C هو A', 'correct' => false],
                    ['en' => 'Some C are not A', 'ar' => 'بعض C ليست A', 'correct' => false],
                    ['en' => 'All A are C', 'ar' => 'كل A هو C', 'correct' => true],
                    ['en' => 'No A are C', 'ar' => 'لا يوجد A هو C', 'correct' => false],
                ],
            ],
            [
                'en' => 'John is taller than Mike. Mike is taller than David. Who is the shortest?',
                'ar' => 'جون أطول من مايك. مايك أطول من ديفيد. من هو الأقصر؟',
                'choices' => [
                    ['en' => 'John', 'ar' => 'جون', 'correct' => false],
                    ['en' => 'Mike', 'ar' => 'مايك', 'correct' => false],
                    ['en' => 'David', 'ar' => 'ديفيد', 'correct' => true],
                    ['en' => 'Cannot determine', 'ar' => 'لا يمكن تحديد', 'correct' => false],
                ],
            ],
            [
                'en' => 'If it rains, the ground gets wet. The ground is wet. What can we conclude?',
                'ar' => 'إذا أمطرت، تبتل الأرض. الأرض مبتلة. ماذا نستنتج؟',
                'choices' => [
                    ['en' => 'It definitely rained', 'ar' => 'بالتأكيد أمطرت', 'correct' => false],
                    ['en' => 'It might have rained', 'ar' => 'ربما أمطرت', 'correct' => true],
                    ['en' => 'It did not rain', 'ar' => 'لم تمطر', 'correct' => false],
                    ['en' => 'Rain is impossible', 'ar' => 'المطر مستحيل', 'correct' => false],
                ],
            ],
            [
                'en' => 'Five people are in a line. Sara is between Tom and Ana. Tom is at one end. Who could be at the other end?',
                'ar' => 'خمسة أشخاص في صف. سارة بين توم وآنا. توم في أحد الأطراف. من يمكن أن يكون في الطرف الآخر؟',
                'choices' => [
                    ['en' => 'Only Sara', 'ar' => 'سارة فقط', 'correct' => false],
                    ['en' => 'Only Tom', 'ar' => 'توم فقط', 'correct' => false],
                    ['en' => 'Anyone except Tom and Sara', 'ar' => 'أي شخص باستثناء توم وسارة', 'correct' => true],
                    ['en' => 'Only Ana', 'ar' => 'آنا فقط', 'correct' => false],
                ],
            ],
            [
                'en' => 'A farmer has 17 sheep, and all but 9 die. How many sheep are left?',
                'ar' => 'لدى مزارع 17 خروفاً، وماتت جميعها إلا 9. كم خروفاً تبقى؟',
                'choices' => [
                    ['en' => '8', 'ar' => '8', 'correct' => false],
                    ['en' => '9', 'ar' => '9', 'correct' => true],
                    ['en' => '17', 'ar' => '17', 'correct' => false],
                    ['en' => '0', 'ar' => '0', 'correct' => false],
                ],
            ],

            // PATTERN RECOGNITION (Questions 19-22)
            [
                'en' => 'What letter comes next: A, C, F, J, O, __',
                'ar' => 'ما الحرف التالي: A، C، F، J، O، __',
                'choices' => [
                    ['en' => 'S', 'ar' => 'S', 'correct' => false],
                    ['en' => 'T', 'ar' => 'T', 'correct' => false],
                    ['en' => 'U', 'ar' => 'U', 'correct' => true],
                    ['en' => 'V', 'ar' => 'V', 'correct' => false],
                ],
            ],
            [
                'en' => 'If APPLE is coded as DSSOH, how would MANGO be coded?',
                'ar' => 'إذا كان APPLE مشفر كـ DSSOH، كيف يُشفر MANGO؟',
                'choices' => [
                    ['en' => 'PDQJR', 'ar' => 'PDQJR', 'correct' => true],
                    ['en' => 'OCPIQ', 'ar' => 'OCPIQ', 'correct' => false],
                    ['en' => 'NBOIP', 'ar' => 'NBOIP', 'correct' => false],
                    ['en' => 'QERKS', 'ar' => 'QERKS', 'correct' => false],
                ],
            ],
            [
                'en' => 'Find the odd one out: 2, 3, 5, 7, 9, 11, 13',
                'ar' => 'اختر الرقم المختلف: 2، 3، 5، 7، 9، 11، 13',
                'choices' => [
                    ['en' => '2', 'ar' => '2', 'correct' => false],
                    ['en' => '9', 'ar' => '9', 'correct' => true],
                    ['en' => '11', 'ar' => '11', 'correct' => false],
                    ['en' => '13', 'ar' => '13', 'correct' => false],
                ],
            ],
            [
                'en' => 'Which shape completes the pattern? □ ○ △ □ ○ △ □ ○ __',
                'ar' => 'أي شكل يكمل النمط؟ □ ○ △ □ ○ △ □ ○ __',
                'choices' => [
                    ['en' => '□ (Square)', 'ar' => '□ (مربع)', 'correct' => false],
                    ['en' => '○ (Circle)', 'ar' => '○ (دائرة)', 'correct' => false],
                    ['en' => '△ (Triangle)', 'ar' => '△ (مثلث)', 'correct' => true],
                    ['en' => '◇ (Diamond)', 'ar' => '◇ (معين)', 'correct' => false],
                ],
            ],

            // VERBAL REASONING (Questions 23-25)
            [
                'en' => 'DOCTOR is to HOSPITAL as TEACHER is to:',
                'ar' => 'طبيب بالنسبة للمستشفى كـ معلم بالنسبة لـ:',
                'choices' => [
                    ['en' => 'Student', 'ar' => 'طالب', 'correct' => false],
                    ['en' => 'School', 'ar' => 'مدرسة', 'correct' => true],
                    ['en' => 'Book', 'ar' => 'كتاب', 'correct' => false],
                    ['en' => 'Lesson', 'ar' => 'درس', 'correct' => false],
                ],
            ],
            [
                'en' => 'Which word is the opposite of ABUNDANT?',
                'ar' => 'ما هو عكس كلمة وفير (ABUNDANT)؟',
                'choices' => [
                    ['en' => 'Plentiful', 'ar' => 'كثير', 'correct' => false],
                    ['en' => 'Sufficient', 'ar' => 'كافٍ', 'correct' => false],
                    ['en' => 'Scarce', 'ar' => 'نادر', 'correct' => true],
                    ['en' => 'Generous', 'ar' => 'كريم', 'correct' => false],
                ],
            ],
            [
                'en' => 'BOOK : CHAPTER :: PLAY : __',
                'ar' => 'كتاب : فصل :: مسرحية : __',
                'choices' => [
                    ['en' => 'Director', 'ar' => 'مخرج', 'correct' => false],
                    ['en' => 'Actor', 'ar' => 'ممثل', 'correct' => false],
                    ['en' => 'Act', 'ar' => 'فصل مسرحي', 'correct' => true],
                    ['en' => 'Script', 'ar' => 'سيناريو', 'correct' => false],
                ],
            ],
        ];
    }
}
