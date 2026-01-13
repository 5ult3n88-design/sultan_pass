<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Main tests table
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('test_type', ['percentile', 'categorical']); // Test type selection
            $table->integer('total_marks')->nullable(); // For percentile tests (e.g., 100)
            $table->integer('passing_marks')->nullable(); // For percentile tests
            $table->integer('duration_minutes')->nullable(); // Time limit
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        // Test translations for multilingual support
        Schema::create('test_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained('tests')->onDelete('cascade');
            $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['test_id', 'language_id']);
        });

        // Categories for categorical tests (e.g., 12 personality types)
        Schema::create('test_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained('tests')->onDelete('cascade');
            $table->string('name'); // e.g., "Extrovert", "Introvert", "INTJ", etc.
            $table->text('description')->nullable();
            $table->string('color')->nullable(); // For visual representation
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Category translations
        Schema::create('test_category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_category_id')->constrained('test_categories')->onDelete('cascade');
            $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['test_category_id', 'language_id'], 'test_category_trans_unique');
        });

        // Questions table
        Schema::create('test_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained('tests')->onDelete('cascade');
            $table->text('question_text');
            $table->enum('question_type', ['multiple_choice', 'typed']); // Choice or typed answer
            $table->integer('marks')->default(1); // For percentile tests
            $table->integer('order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });

        // Question translations
        Schema::create('test_question_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_question_id')->constrained('test_questions')->onDelete('cascade');
            $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');
            $table->text('question_text');
            $table->timestamps();

            $table->unique(['test_question_id', 'language_id'], 'test_question_trans_unique');
        });

        // Answer choices for multiple choice questions
        Schema::create('test_answer_choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_question_id')->constrained('test_questions')->onDelete('cascade');
            $table->text('choice_text');
            $table->boolean('is_correct')->default(false); // For percentile tests
            $table->foreignId('category_id')->nullable()->constrained('test_categories')->onDelete('set null'); // For categorical tests
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Answer choice translations
        Schema::create('test_answer_choice_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_answer_choice_id')->constrained('test_answer_choices')->onDelete('cascade');
            $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');
            $table->text('choice_text');
            $table->timestamps();

            $table->unique(['test_answer_choice_id', 'language_id'], 'test_ans_choice_trans_unique');
        });

        // Test assignments to participants
        Schema::create('test_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained('tests')->onDelete('cascade');
            $table->foreignId('participant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');
            $table->dateTime('assigned_at');
            $table->dateTime('due_date')->nullable();
            $table->enum('status', ['assigned', 'in_progress', 'submitted', 'graded'])->default('assigned');
            $table->timestamps();

            $table->unique(['test_id', 'participant_id']);
        });

        // Participant responses
        Schema::create('test_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_assignment_id')->constrained('test_assignments')->onDelete('cascade');
            $table->foreignId('test_question_id')->constrained('test_questions')->onDelete('cascade');
            $table->foreignId('selected_choice_id')->nullable()->constrained('test_answer_choices')->onDelete('set null'); // For multiple choice
            $table->text('typed_answer')->nullable(); // For typed answers
            $table->boolean('is_graded')->default(false); // For typed answers
            $table->boolean('is_correct')->nullable(); // For percentile tests (after grading)
            $table->foreignId('assigned_category_id')->nullable()->constrained('test_categories')->onDelete('set null'); // For categorical typed answers (after manual correction)
            $table->integer('marks_awarded')->nullable(); // For percentile tests
            $table->text('assessor_feedback')->nullable(); // For typed answers
            $table->foreignId('graded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('graded_at')->nullable();
            $table->timestamps();

            $table->unique(['test_assignment_id', 'test_question_id']);
        });

        // Test results summary
        Schema::create('test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_assignment_id')->constrained('test_assignments')->onDelete('cascade');
            $table->integer('total_marks_obtained')->nullable(); // For percentile tests
            $table->decimal('percentage', 5, 2)->nullable(); // For percentile tests
            $table->enum('result_status', ['pass', 'fail', 'pending'])->nullable(); // For percentile tests
            $table->foreignId('dominant_category_id')->nullable()->constrained('test_categories')->onDelete('set null'); // For categorical tests
            $table->json('category_scores')->nullable(); // For categorical tests: {category_id: count}
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_results');
        Schema::dropIfExists('test_responses');
        Schema::dropIfExists('test_assignments');
        Schema::dropIfExists('test_answer_choice_translations');
        Schema::dropIfExists('test_answer_choices');
        Schema::dropIfExists('test_question_translations');
        Schema::dropIfExists('test_questions');
        Schema::dropIfExists('test_category_translations');
        Schema::dropIfExists('test_categories');
        Schema::dropIfExists('test_translations');
        Schema::dropIfExists('tests');
    }
};
