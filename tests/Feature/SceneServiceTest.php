<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Project;
use App\Models\Scene;
use App\Models\User;
use App\Services\SceneService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SceneServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SceneService $sceneService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sceneService = new SceneService;
    }

    public function test_create_scene()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $project = Project::create(['user_id' => $user->id, 'name' => 'Test Project', 'description' => 'Test Description']);
        $character = Character::create(['user_id' => $user->id, 'project_id' => $project->id, 'name' => 'Hero']);

        $data = [
            'project_id' => $project->id,
            'title' => 'Scene 1',
            'description' => 'Description',
            'duration' => 10,
            'order' => 1,
            'act' => 1,
            'characters' => [$character->id],
            'dialogues' => [$character->id => 'Hello World'],
        ];

        $scene = $this->sceneService->createScene($data);

        $this->assertDatabaseHas('scenes', ['title' => 'Scene 1', 'act' => 1]);
        $this->assertDatabaseHas('character_scene', [
            'scene_id' => $scene->id,
            'character_id' => $character->id,
            'dialogue' => 'Hello World',
        ]);
    }

    public function test_update_scene()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $project = Project::create(['user_id' => $user->id, 'name' => 'Test Project', 'description' => 'Test Description']);
        $character = Character::create(['user_id' => $user->id, 'project_id' => $project->id, 'name' => 'Hero']);

        $scene = Scene::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'title' => 'Old Title',
            'act' => 1,
            'order' => 1,
            'description' => 'Desc',
            'duration' => 10,
        ]);

        $data = [
            'title' => 'New Title',
            'characters' => [$character->id],
            'dialogues' => [$character->id => 'Updated Dialogue'],
        ];

        $this->sceneService->updateScene($scene, $data);

        $this->assertDatabaseHas('scenes', ['title' => 'New Title']);
        $this->assertDatabaseHas('character_scene', [
            'scene_id' => $scene->id,
            'character_id' => $character->id,
            'dialogue' => 'Updated Dialogue',
        ]);
    }

    public function test_add_character_to_scene()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $project = Project::create(['user_id' => $user->id, 'name' => 'Test Project', 'description' => 'Test Description']);
        $character = Character::create(['user_id' => $user->id, 'project_id' => $project->id, 'name' => 'Hero']);

        $scene = Scene::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'title' => 'Scene 1',
            'act' => 1,
            'order' => 1,
            'description' => 'Desc',
            'duration' => 10,
        ]);

        $this->sceneService->addCharacterToScene($scene, $character->id, 'Added Dialogue');

        $this->assertDatabaseHas('character_scene', [
            'scene_id' => $scene->id,
            'character_id' => $character->id,
            'dialogue' => 'Added Dialogue',
        ]);
    }

    public function test_remove_character_from_scene()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $project = Project::create(['user_id' => $user->id, 'name' => 'Test Project', 'description' => 'Test Description']);
        $character = Character::create(['user_id' => $user->id, 'project_id' => $project->id, 'name' => 'Hero']);

        $scene = Scene::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'title' => 'Scene 1',
            'act' => 1,
            'order' => 1,
            'description' => 'Desc',
            'duration' => 10,
        ]);

        $scene->characters()->attach($character->id);

        $this->sceneService->removeCharacterFromScene($scene, $character->id);

        $this->assertDatabaseMissing('character_scene', [
            'scene_id' => $scene->id,
            'character_id' => $character->id,
        ]);
    }
}
