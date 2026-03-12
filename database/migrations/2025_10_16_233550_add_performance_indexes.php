<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Índices para tabela characters
        Schema::table('characters', function (Blueprint $table) {
            $table->index(['project_id', 'user_id'], 'idx_characters_project_user');
            $table->index('name', 'idx_characters_name');
            $table->index(['project_id', 'name'], 'idx_characters_project_name');
        });

        // Índices para tabela scenes
        Schema::table('scenes', function (Blueprint $table) {
            $table->index(['project_id', 'order'], 'idx_scenes_project_order');
            $table->index('user_id', 'idx_scenes_user');
            $table->index(['project_id', 'user_id'], 'idx_scenes_project_user');
        });

        // Índices para tabela projects
        Schema::table('projects', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'idx_projects_user_created');
        });

        // Índices para tabela character_scene
        Schema::table('character_scene', function (Blueprint $table) {
            $table->index(['character_id', 'scene_id'], 'idx_character_scene_compound');
            $table->index('character_id', 'idx_character_scene_character');
            $table->index('scene_id', 'idx_character_scene_scene');
        });

        // Índices para tabela excel_data
        Schema::table('excel_data', function (Blueprint $table) {
            $table->index(['user_id', 'project_id'], 'idx_excel_data_user_project');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Lista de índices para remover de forma segura
        $tables = [
            'characters' => [
                'idx_characters_project_user',
                'idx_characters_name',
                'idx_characters_project_name',
            ],
            'scenes' => [
                'idx_scenes_project_order',
                'idx_scenes_user',
                'idx_scenes_project_user',
            ],
            'projects' => [
                'idx_projects_user_created',
            ],
            'character_scene' => [
                'idx_character_scene_compound',
                'idx_character_scene_character',
                'idx_character_scene_scene',
            ],
            'excel_data' => [
                'idx_excel_data_user_project',
            ],
        ];

        foreach ($tables as $table => $indexes) {
            foreach ($indexes as $index) {
                if (Schema::hasIndex($table, $index)) {
                    try {
                        Schema::table($table, function (Blueprint $table) use ($index) {
                            $table->dropIndex($index);
                        });
                    } catch (QueryException $e) {
                        // Ignorar se o índice for necessário para uma chave estrangeira (Erro 1553)
                        if ($e->getCode() !== 'HY000' || ! str_contains($e->getMessage(), '1553')) {
                            throw $e;
                        }
                    }
                }
            }
        }
    }
};
