<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('development_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->nullable()->constrained('users');
            $table->enum('plan_type',['individual','group'])->default('individual');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status',['planned','in_progress','completed'])->default('planned');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('development_plan_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('development_plans')->onDelete('cascade');
            $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');
            $table->string('title',200)->nullable();
            $table->text('description')->nullable();
            $table->unique(['plan_id','language_id']);
            $table->timestamps();
        });

        Schema::create('development_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('development_plans');
            $table->enum('activity_type',['training','mentoring','course','project','other']);
            $table->enum('completion_status',['not_started','in_progress','completed'])->default('not_started');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('development_activity_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('development_activities')->onDelete('cascade');
            $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');
            $table->string('activity_name',200)->nullable();
            $table->unique(['activity_id','language_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('development_activity_translations');
        Schema::dropIfExists('development_activities');
        Schema::dropIfExists('development_plan_translations');
        Schema::dropIfExists('development_plans');
    }
};
