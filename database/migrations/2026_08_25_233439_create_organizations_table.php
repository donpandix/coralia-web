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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('name', 200)->index();
            $table->text('description')->nullable();
            $table->string('logo_path', 500)->nullable();
            $table->foreignId('owner_user_id')->index()->constrained('users');
            $table->string('status', 30)->index();
            $table->string('city', 150)->nullable();
            $table->timestamps();
            $table->timestamp('archived_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
