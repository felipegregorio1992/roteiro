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
        Schema::create('dialogues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('character_id')->constrained()->onDelete('cascade');
            $table->foreignId('target_character_id')->nullable()->constrained('characters')->onDelete('set null');
            $table->foreignId('scene_id')->nullable()->constrained()->onDelete('cascade');
            $table->text('content');
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dialogues');
    }
};
