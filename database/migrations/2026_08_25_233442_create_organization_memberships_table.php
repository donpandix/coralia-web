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
        Schema::create('organization_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('role', 30);
            $table->string('voice_type', 30)->nullable();
            $table->string('status', 30);
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'user_id']);
            $table->index(['user_id', 'status']);
            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'role']);
            $table->index(['organization_id', 'voice_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_memberships');
    }
};
