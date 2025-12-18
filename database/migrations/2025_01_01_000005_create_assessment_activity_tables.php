<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assessment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments');
            $table->foreignId('competency_id')->constrained('competencies');
            $table->integer('max_score')->default(5);
            $table->decimal('weight',5,2)->default(1.00);
            $table->timestamps();
        });

        Schema::create('assessment_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments');
            $table->foreignId('participant_id')->constrained('users');
            $table->enum('status',['not_started','in_progress','completed'])->default('not_started');
            $table->decimal('score',5,2)->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();
        });

        Schema::create('assessor_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments');
            $table->foreignId('assessor_id')->constrained('users');
            $table->foreignId('participant_id')->constrained('users');
            $table->foreignId('competency_id')->constrained('competencies');
            $table->decimal('score',5,2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessor_notes');
        Schema::dropIfExists('assessment_participants');
        Schema::dropIfExists('assessment_items');
    }
};
