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
        Schema::create('piece_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('piece_id')->constrained()->cascadeOnDelete();
            $table->timestamp('first_viewed_at');
            $table->timestamp('last_viewed_at');
            $table->unsignedInteger('view_count')->default(1);
            $table->timestamps();

            $table->unique(['user_id', 'piece_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('piece_views');
    }
};
