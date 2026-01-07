<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Questions table
        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->onDelete('cascade');
            $table->enum('question_type', ['mcq', 'written'])->default('mcq');
            $table->text('question_text');
            $table->string('question_image_path')->nullable();
            $table->integer('order')->default(1);
            $table->decimal('max_score', 8, 2)->nullable(); // For percentile mode
            $table->boolean('is_required')->default(true);
            $table->timestamps();
            
            $table->index('assessment_id');
            $table->index('order');
        });

        // Question translations
        Schema::create('assessment_question_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('assessment_questions')->onDelete('cascade');
            $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');
            $table->text('question_text');
            $table->timestamps();
            
            $table->unique(['question_id', 'language_id']);
        });

        // Answers table
        Schema::create('assessment_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('assessment_questions')->onDelete('cascade');
            $table->text('answer_text');
            $table->string('answer_image_path')->nullable();
            $table->integer('order')->default(1);
            $table->timestamps();
            
            $table->index('question_id');
            $table->index('order');
        });

        // Answer translations
        Schema::create('assessment_answer_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('answer_id')->constrained('assessment_answers')->onDelete('cascade');
            $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');
            $table->text('answer_text');
            $table->timestamps();
            
            $table->unique(['answer_id', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_answer_translations');
        Schema::dropIfExists('assessment_answers');
        Schema::dropIfExists('assessment_question_translations');
        Schema::dropIfExists('assessment_questions');
    }
};

