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
        Schema::create('organization_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('requested_by')->constrained('users');
            $table->string('organization_name', 200);
            $table->text('description')->nullable();
            $table->string('city', 150)->nullable();
            $table->text('additional_info')->nullable();
            $table->string('status', 30)->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['requested_by', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_requests');
    }
};
