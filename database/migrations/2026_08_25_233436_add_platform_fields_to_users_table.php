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
        Schema::table('users', function (Blueprint $table): void {
            $table->uuid('public_id')->nullable()->unique();
            $table->string('name', 150)->change();
            $table->string('photo_path', 500)->nullable();
            $table->string('description', 500)->nullable();
            $table->boolean('is_super_admin')->default(false);
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['public_id']);
            $table->dropIndex(['status']);
            $table->dropColumn([
                'public_id',
                'photo_path',
                'description',
                'is_super_admin',
                'status',
                'deleted_at',
            ]);
            $table->string('name')->change();
        });
    }
};
