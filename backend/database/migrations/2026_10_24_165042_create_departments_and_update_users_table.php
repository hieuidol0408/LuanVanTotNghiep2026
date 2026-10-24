<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 255);
            $table->string('email', 150)->nullable();
            $table->string('phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('id')->constrained('departments')->nullOnDelete();
            $table->string('username', 100)->nullable()->unique()->after('department_id');
            $table->string('full_name', 150)->nullable()->after('username');
            $table->string('academic_title', 50)->nullable()->after('full_name');
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('avatar_path', 255)->nullable()->after('phone');
            $table->string('role', 50)->default('MEMBER')->after('avatar_path');
            $table->boolean('is_active')->default(true)->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn([
                'department_id',
                'username',
                'full_name',
                'academic_title',
                'phone',
                'avatar_path',
                'role',
                'is_active',
            ]);
        });

        Schema::dropIfExists('departments');
    }
};
