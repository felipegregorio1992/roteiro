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
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration')->nullable();
            $table->integer('order')->default(0);
            $table->integer('episode_number')->nullable();
            $table->timestamps();
        });

        Schema::create('character_episode', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->onDelete('cascade');
            $table->foreignId('episode_id')->constrained()->onDelete('cascade');
            $table->text('dialogue')->nullable();
            $table->timestamps();

            $table->unique(['character_id', 'episode_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_episode');
        Schema::dropIfExists('episodes');
    }
};
