<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assessment_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained('users');
            $table->foreignId('assessment_id')->constrained('assessments');
            $table->decimal('overall_score',5,2)->nullable();
            $table->text('strengths')->nullable();
            $table->text('weaknesses')->nullable();
            $table->text('recommendations')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('assessment_reports');
            $table->enum('format',['pdf','excel','powerbi']);
            $table->foreignId('exported_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->text('message');
            $table->enum('notification_type',['assessment','plan','deadline','alert']);
            $table->timestamp('sent_at')->useCurrent();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('action',255);
            $table->string('module',100)->nullable();
            $table->text('details')->nullable();
            $table->string('ip_address',45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('report_exports');
        Schema::dropIfExists('assessment_reports');
    }
};
