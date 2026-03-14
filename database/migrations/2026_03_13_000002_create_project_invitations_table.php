<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('invited_by_user_id')->constrained('users')->onDelete('cascade');
            $table->string('email');
            $table->string('token_hash')->unique();
            $table->string('role')->default('viewer');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_invitations');
    }
};
