<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->enum('type', [
                'psychometric','interview','group_exercise','written_test',
                'role_play','committee_interview','other'
            ]);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['draft','active','closed'])->default('draft');
            $table->timestamps();
        });

        Schema::create('assessment_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->onDelete('cascade');
            $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->unique(['assessment_id','language_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_translations');
        Schema::dropIfExists('assessments');
    }
};
