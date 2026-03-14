<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProjectInviteAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_accepting_invite_makes_project_appear_on_dashboard_even_with_cached_empty_list(): void
    {
        $owner = User::factory()->create();
        $invited = User::factory()->create([
            'email' => 'invitee@example.com',
        ]);

        $project = Project::create([
            'user_id' => $owner->id,
            'name' => 'Projeto Compartilhado',
            'description' => 'Desc',
        ]);

        $token = Str::random(64);
        ProjectInvitation::create([
            'project_id' => $project->id,
            'invited_by_user_id' => $owner->id,
            'email' => $invited->email,
            'token_hash' => hash('sha256', $token),
            'role' => 'viewer',
            'expires_at' => now()->addDays(7),
        ]);

        $this->actingAs($invited);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('projects', fn ($projects) => $projects->isEmpty())
            ->assertViewHas('pendingInvitations', fn ($invites) => $invites->count() === 1);

        $this->get(route('invites.accept', ['token' => $token]))
            ->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('projects', fn ($projects) => $projects->contains('id', $project->id))
            ->assertViewHas('pendingInvitations', fn ($invites) => $invites->isEmpty());
    }

    public function test_registered_user_can_accept_invite_from_dashboard(): void
    {
        $owner = User::factory()->create();
        $invited = User::factory()->create([
            'email' => 'invitee@example.com',
        ]);

        $project = Project::create([
            'user_id' => $owner->id,
            'name' => 'Projeto Compartilhado',
            'description' => 'Desc',
        ]);

        $token = Str::random(64);
        $invitation = ProjectInvitation::create([
            'project_id' => $project->id,
            'invited_by_user_id' => $owner->id,
            'email' => $invited->email,
            'token_hash' => hash('sha256', $token),
            'role' => 'editor',
            'expires_at' => now()->addDays(7),
        ]);

        $this->actingAs($invited)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('pendingInvitations', fn ($invites) => $invites->contains('id', $invitation->id));

        $this->actingAs($invited)
            ->post(route('invites.accept.by-id', $invitation))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $invited->id,
            'role' => 'editor',
        ]);

        $invitation->refresh();
        $this->assertNotNull($invitation->accepted_at);
    }

    public function test_registered_user_can_reject_invite_from_dashboard(): void
    {
        $owner = User::factory()->create();
        $invited = User::factory()->create([
            'email' => 'invitee@example.com',
        ]);

        $project = Project::create([
            'user_id' => $owner->id,
            'name' => 'Projeto Compartilhado',
            'description' => 'Desc',
        ]);

        $token = Str::random(64);
        $invitation = ProjectInvitation::create([
            'project_id' => $project->id,
            'invited_by_user_id' => $owner->id,
            'email' => $invited->email,
            'token_hash' => hash('sha256', $token),
            'role' => 'viewer',
            'expires_at' => now()->addDays(7),
        ]);

        $this->actingAs($invited)
            ->post(route('invites.reject.by-id', $invitation))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseMissing('project_invitations', [
            'id' => $invitation->id,
        ]);

        $this->assertDatabaseMissing('project_user', [
            'project_id' => $project->id,
            'user_id' => $invited->id,
        ]);
    }

    public function test_owner_can_cancel_pending_invite_from_share_page(): void
    {
        $owner = User::factory()->create();
        $invited = User::factory()->create([
            'email' => 'invitee@example.com',
        ]);

        $project = Project::create([
            'user_id' => $owner->id,
            'name' => 'Projeto Compartilhado',
            'description' => 'Desc',
        ]);

        $token = Str::random(64);
        $invitation = ProjectInvitation::create([
            'project_id' => $project->id,
            'invited_by_user_id' => $owner->id,
            'email' => $invited->email,
            'token_hash' => hash('sha256', $token),
            'role' => 'viewer',
            'expires_at' => now()->addDays(7),
        ]);

        $this->actingAs($invited)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('pendingInvitations', fn ($invites) => $invites->contains('id', $invitation->id));

        $this->actingAs($owner)
            ->delete(route('projects.invites.destroy', ['project' => $project->id, 'invitation' => $invitation->id]))
            ->assertRedirect(route('projects.share', $project));

        $this->assertDatabaseMissing('project_invitations', [
            'id' => $invitation->id,
        ]);

        $this->actingAs($invited)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('pendingInvitations', fn ($invites) => $invites->isEmpty());
    }
}
