<?php

namespace Tests\Feature;

use App\Livewire\SceneList;
use App\Models\Character;
use App\Models\Project;
use App\Models\Scene;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SceneListTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Test Project',
            'description' => 'Test Description',
        ]);

        Livewire::test(SceneList::class, ['project' => $project])
            ->assertStatus(200)
            ->assertViewIs('livewire.scene-list');
    }

    public function test_can_expand_scene_and_load_dialogues()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $project = Project::create(['user_id' => $user->id, 'name' => 'Test', 'description' => 'Desc']);

        $scene = Scene::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'title' => 'Scene 1',
            'act' => 1,
            'order' => 1,
            'description' => 'Desc',
            'duration' => 10,
        ]);

        $character = Character::create(['project_id' => $project->id, 'user_id' => $user->id, 'name' => 'Hero']);

        $scene->characters()->attach($character->id, ['dialogue' => 'Hello World']);

        Livewire::test(SceneList::class, ['project' => $project])
            ->call('toggleExpand', $scene->id)
            ->assertSet('expandedSceneId', $scene->id)
            ->assertSet('editingDialogues', [$character->id => 'Hello World']);
    }

    public function test_can_update_dialogue()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $project = Project::create(['user_id' => $user->id, 'name' => 'Test', 'description' => 'Desc']);

        $scene = Scene::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'title' => 'Scene 1',
            'act' => 1,
            'order' => 1,
            'description' => 'Desc',
            'duration' => 10,
        ]);

        $character = Character::create(['project_id' => $project->id, 'user_id' => $user->id, 'name' => 'Hero']);
        $scene->characters()->attach($character->id, ['dialogue' => 'Old Dialogue']);

        Livewire::test(SceneList::class, ['project' => $project])
            ->call('toggleExpand', $scene->id)
            ->set('editingDialogues.'.$character->id, 'New Dialogue')
            ->call('saveDialogue', $scene->id, $character->id);

        $this->assertDatabaseHas('character_scene', [
            'scene_id' => $scene->id,
            'character_id' => $character->id,
            'dialogue' => 'New Dialogue',
        ]);
    }

    public function test_reorder_scenes_updates_act_and_order()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $project = Project::create(['user_id' => $user->id, 'name' => 'Test', 'description' => 'Desc']);

        $scene1 = Scene::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'title' => 'Scene 1',
            'act' => 1,
            'order' => 1,
            'description' => 'Desc',
            'duration' => 10,
        ]);

        // Simulate moving Scene 1 to Act 2, Order 1
        $groups = [
            [
                'value' => 2, // Act 2
                'items' => [
                    ['value' => $scene1->id, 'order' => 1],
                ],
            ],
        ];

        Livewire::test(SceneList::class, ['project' => $project])
            ->call('updateSceneOrder', $groups);

        $this->assertDatabaseHas('scenes', [
            'id' => $scene1->id,
            'act' => 2,
            'order' => 1,
        ]);
    }
}
