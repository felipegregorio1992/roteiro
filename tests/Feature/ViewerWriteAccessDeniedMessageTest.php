<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewerWriteAccessDeniedMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_change_member_level_to_editor_and_back_to_viewer(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $project = Project::create([
            'user_id' => $owner->id,
            'name' => 'Projeto',
            'description' => 'Desc',
        ]);

        $project->members()->attach($member->id, [
            'role' => 'viewer',
        ]);

        $this->actingAs($owner)
            ->patch(route('projects.members.update', ['project' => $project->id, 'user' => $member->id]), [
                'role' => 'editor',
            ])
            ->assertRedirect(route('projects.share', $project))
            ->assertSessionHas('success', 'Permissão atualizada com sucesso!');

        $this->actingAs($member)
            ->get(route('projects.edit', $project))
            ->assertOk();

        $this->actingAs($owner)
            ->patch(route('projects.members.update', ['project' => $project->id, 'user' => $member->id]), [
                'role' => 'viewer',
            ])
            ->assertRedirect(route('projects.share', $project))
            ->assertSessionHas('success', 'Permissão atualizada com sucesso!');

        $this->actingAs($member)
            ->from(route('dashboard'))
            ->get(route('projects.edit', $project))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', 'Você tem acesso apenas de leitura e não pode alterar este roteiro.');
    }

    public function test_removed_member_loses_access_to_project_pages(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $project = Project::create([
            'user_id' => $owner->id,
            'name' => 'Projeto',
            'description' => 'Desc',
        ]);

        $project->members()->attach($member->id, [
            'role' => 'viewer',
        ]);

        $this->actingAs($member)
            ->get(route('episodes.index', ['project' => $project->id]))
            ->assertOk();

        $project->members()->detach($member->id);

        $this->actingAs($member)
            ->from(route('dashboard'))
            ->get(route('episodes.index', ['project' => $project->id]))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', 'Você tem acesso apenas de leitura e não pode alterar este roteiro.');
    }

    public function test_viewer_gets_redirect_with_message_when_trying_to_edit_project(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();

        $project = Project::create([
            'user_id' => $owner->id,
            'name' => 'Projeto',
            'description' => 'Desc',
        ]);

        $project->members()->attach($viewer->id, [
            'role' => 'viewer',
        ]);

        $this->actingAs($viewer)
            ->from(route('dashboard'))
            ->get(route('projects.edit', $project))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', 'Você tem acesso apenas de leitura e não pode alterar este roteiro.');
    }

    public function test_viewer_gets_redirect_with_message_when_trying_to_update_project(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();

        $project = Project::create([
            'user_id' => $owner->id,
            'name' => 'Projeto',
            'description' => 'Desc',
        ]);

        $project->members()->attach($viewer->id, [
            'role' => 'viewer',
        ]);

        $this->actingAs($viewer)
            ->from(route('projects.edit', $project))
            ->patch(route('projects.update', $project), [
                'name' => 'Novo Nome',
                'description' => 'Nova descrição',
            ])
            ->assertRedirect(route('projects.edit', $project))
            ->assertSessionHas('error', 'Você tem acesso apenas de leitura e não pode alterar este roteiro.');
    }
}
