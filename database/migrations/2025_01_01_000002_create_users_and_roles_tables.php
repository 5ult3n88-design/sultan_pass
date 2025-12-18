<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 100)->unique();
            $table->string('email', 150)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('full_name', 150)->nullable();
            $table->string('rank', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->enum('role', ['admin','manager','assessor','participant'])->default('participant');
            $table->foreignId('language_pref')->nullable()->constrained('languages');
            $table->enum('status', ['active','inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('roles_permissions', function (Blueprint $table) {
            $table->id();
            $table->enum('role', ['admin','assessor','participant','manager']);
            $table->string('module_name', 100);
            $table->boolean('can_view')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_export')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles_permissions');
        Schema::dropIfExists('users');
    }
};
