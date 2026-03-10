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
        Schema::table('scenes', function (Blueprint $table) {
            $table->integer('act')->default(1)->after('duration');
            $table->index(['project_id', 'act'], 'idx_scenes_project_act');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scenes', function (Blueprint $table) {
            $table->dropIndex('idx_scenes_project_act');
            $table->dropColumn('act');
        });
    }
};
