<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->enum('scoring_mode', ['categorical', 'percentile'])->default('percentile')->after('type');
            $table->decimal('max_total_score', 8, 2)->nullable()->after('scoring_mode');
        });
    }

    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn(['scoring_mode', 'max_total_score']);
        });
    }
};

