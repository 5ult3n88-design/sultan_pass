<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Category weights for categorical mode
        Schema::create('answer_category_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('answer_id')->constrained('assessment_answers')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('assessment_categories')->onDelete('cascade');
            $table->decimal('weight', 5, 2)->default(1.00); // 0.00 to 999.99
            $table->timestamps();
            
            $table->unique(['answer_id', 'category_id']);
            $table->index('answer_id');
            $table->index('category_id');
        });

        // Scores for percentile mode
        Schema::create('answer_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('answer_id')->constrained('assessment_answers')->onDelete('cascade');
            $table->decimal('score_value', 8, 2)->default(0.00); // Can be 0, partial, or full
            $table->timestamps();
            
            $table->index('answer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answer_scores');
        Schema::dropIfExists('answer_category_weights');
    }
};

