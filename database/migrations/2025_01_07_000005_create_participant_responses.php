<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('participant_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->onDelete('cascade');
            $table->foreignId('participant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('assessment_questions')->onDelete('cascade');
            
            // For MCQ responses
            $table->json('selected_answer_ids')->nullable(); // Array of answer IDs
            
            // For written responses
            $table->text('written_response_text')->nullable();
            $table->string('written_response_image_path')->nullable();
            
            // Grading fields
            $table->decimal('graded_score', 8, 2)->nullable(); // For percentile mode
            $table->json('graded_categories')->nullable(); // For categorical mode: {category_id: weight}
            $table->foreignId('graded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('graded_at')->nullable();
            
            $table->timestamps();
            
            $table->index('assessment_id');
            $table->index('participant_id');
            $table->index('question_id');
            $table->unique(['assessment_id', 'participant_id', 'question_id'], 'participant_response_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_responses');
    }
};

