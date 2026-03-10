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
        Schema::table('character_scene', function (Blueprint $table) {
            $table->boolean('is_hidden')->default(false)->after('dialogue');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('character_scene', function (Blueprint $table) {
            $table->dropColumn('is_hidden');
        });
    }
};
