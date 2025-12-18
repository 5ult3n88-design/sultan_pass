<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assessment_templates', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['psychometric','interview','group_exercise','other']);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('assessment_template_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('assessment_templates')->onDelete('cascade');
            $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');
            $table->string('template_name')->nullable();
            $table->text('description')->nullable();
            $table->unique(['template_id','language_id']);
            $table->timestamps();
        });

        Schema::create('competencies', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['leadership','behavioral','technical']);
            $table->timestamps();
        });

        Schema::create('competency_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competency_id')->constrained('competencies')->onDelete('cascade');
            $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');
            $table->string('name',150)->nullable();
            $table->text('description')->nullable();
            $table->unique(['competency_id','language_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competency_translations');
        Schema::dropIfExists('competencies');
        Schema::dropIfExists('assessment_template_translations');
        Schema::dropIfExists('assessment_templates');
    }
};
