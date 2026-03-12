<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\Scene;
use App\Models\User;
use App\Services\SceneService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SceneServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_scenes_grouped_by_act()
    {
        $user = User::factory()->create();

        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Projeto Teste',
            'description' => 'Teste',
        ]);

        // Criar cenas com atos definidos
        Scene::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'act' => 1,
            'title' => 'Cena 1',
            'order' => 1,
            'duration' => 10,
            'description' => 'Desc',
        ]);

        Scene::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'act' => 1,
            'title' => 'Cena 2',
            'order' => 2,
            'duration' => 10,
            'description' => 'Desc',
        ]);

        Scene::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'act' => 2,
            'title' => 'Cena 3',
            'order' => 1,
            'duration' => 10,
            'description' => 'Desc',
        ]);

        $service = app(SceneService::class);
        $grouped = $service->getScenesGroupedByAct($project->id, $user->id);

        $this->assertCount(2, $grouped);
        $this->assertCount(2, $grouped[1]['scenes']);
        $this->assertCount(1, $grouped[2]['scenes']);
        $this->assertEquals('Ato 1', $grouped[1]['title']);
    }

    public function test_fallback_parsing_for_legacy_scenes()
    {
        $user = User::factory()->create();

        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Projeto Teste',
            'description' => 'Teste',
        ]);

        Scene::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'act' => 3,
            'title' => 'Qualquer Título',
            'order' => 1,
            'duration' => 10,
            'description' => 'Desc',
        ]);

        $service = app(SceneService::class);
        $grouped = $service->getScenesGroupedByAct($project->id, $user->id);

        $this->assertArrayHasKey(3, $grouped);
        $this->assertEquals('Ato 3', $grouped[3]['title']);
    }
}
