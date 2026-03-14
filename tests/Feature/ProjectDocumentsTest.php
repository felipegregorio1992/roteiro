<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectDocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_document_and_it_appears_in_files(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Projeto',
            'description' => 'Desc',
        ]);

        $this->actingAs($user)
            ->post(route('projects.documents.store', ['project' => $project->id]), [
                'title' => 'Meu Documento',
                'content' => '<p><strong>Olá</strong> mundo</p>',
            ])
            ->assertRedirect(route('projects.files.index', ['project' => $project->id]));

        $files = Storage::disk('local')->allFiles("project_uploads/{$project->id}");
        $this->assertCount(1, $files);

        $storedPath = $files[0];
        $storedName = basename($storedPath);

        $this->get(route('projects.files.index', ['project' => $project->id]))
            ->assertOk()
            ->assertSee('Meu Documento.html');

        $this->get(route('projects.files.index', ['project' => $project->id, 'doc' => $storedName]))
            ->assertOk()
            ->assertViewHas('editingDocument', fn ($doc) => $doc && $doc['stored_name'] === $storedName);
    }

    public function test_user_can_update_document_content_and_rename_file(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Projeto',
            'description' => 'Desc',
        ]);

        $this->actingAs($user)->post(route('projects.documents.store', ['project' => $project->id]), [
            'title' => 'Doc Antigo',
            'content' => '<p>v1</p>',
        ]);

        $files = Storage::disk('local')->allFiles("project_uploads/{$project->id}");
        $storedPath = $files[0];
        $storedName = basename($storedPath);

        $this->actingAs($user)
            ->put(route('projects.documents.update', ['project' => $project->id, 'storedName' => $storedName]), [
                'title' => 'Doc Novo',
                'content' => '<p>v2</p>',
            ])
            ->assertRedirect(route('projects.files.index', ['project' => $project->id]));

        $updatedFiles = Storage::disk('local')->allFiles("project_uploads/{$project->id}");
        $this->assertCount(1, $updatedFiles);
        $updatedStoredName = basename($updatedFiles[0]);

        $this->assertNotSame($storedName, $updatedStoredName);
        $this->assertStringEndsWith('__Doc Novo.html', $updatedStoredName);
        Storage::disk('local')->assertExists("project_uploads/{$project->id}/{$updatedStoredName}");
        $this->assertSame('<p>v2</p>', Storage::disk('local')->get("project_uploads/{$project->id}/{$updatedStoredName}"));

        $this->get(route('projects.files.download', ['project' => $project->id, 'storedName' => $updatedStoredName]))
            ->assertOk();
    }
}
