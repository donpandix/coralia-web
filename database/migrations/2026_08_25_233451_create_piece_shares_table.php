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
        Schema::create('piece_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('piece_id')->constrained()->cascadeOnDelete();
            $table->string('share_type', 30);
            $table->string('voice_type', 30)->nullable();
            $table->foreignId('group_id')->nullable()->index()->constrained()->cascadeOnDelete();
            $table->foreignId('membership_id')->nullable()->index()->constrained('organization_memberships')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['piece_id', 'share_type']);
            $table->index('voice_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('piece_shares');
    }
};
