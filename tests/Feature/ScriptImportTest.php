<?php

namespace Tests\Feature;

use App\Imports\ScriptImport;
use App\Models\Character;
use App\Models\Episode;
use App\Models\ExcelData;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ScriptImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_creates_scenes_and_characters()
    {
        // Setup
        $user = User::factory()->create();
        Auth::login($user);

        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Projeto Teste',
            'description' => 'Descrição do projeto',
        ]);

        // Mock Excel Data
        // Row 1: Headers (skipped by logic usually, but let's see ScriptImport)
        // ScriptImport assumes first row is headers if it calculates totalActs from it?
        // Let's check ScriptImport logic: $totalActs = count($rows[0]) - 1;

        $rows = new Collection([
            // Header Row removed to avoid creating "Personagem" character
            // Data Row 1
            new Collection(['Hero', 'Fala do herói no ato 1', 'Fala do herói no ato 2']),
            // Data Row 2
            new Collection(['Villain', 'Fala do vilão no ato 1', 'Fala do vilão no ato 2']),
        ]);

        $importer = new ScriptImport($project->id, 'test.xlsx');

        // Execute
        $importer->collection($rows);

        // Assertions

        $this->assertDatabaseCount('episodes', 2);

        $episode1 = Episode::where('project_id', $project->id)->where('order', 1)->first();
        $this->assertNotNull($episode1);
        $this->assertEquals('Episódio 1', $episode1->title);

        $episode2 = Episode::where('project_id', $project->id)->where('order', 2)->first();
        $this->assertNotNull($episode2);

        // 2. Check Characters created
        $this->assertDatabaseCount('characters', 2);
        $this->assertDatabaseHas('characters', ['name' => 'Hero']);
        $this->assertDatabaseHas('characters', ['name' => 'Villain']);

        // 3. Check Dialogues (Pivot)
        $hero = Character::where('name', 'Hero')->first();

        $this->assertDatabaseHas('character_episode', [
            'character_id' => $hero->id,
            'episode_id' => $episode1->id,
            'dialogue' => 'Fala do herói no ato 1',
        ]);

        $this->assertDatabaseHas('character_episode', [
            'character_id' => $hero->id,
            'episode_id' => $episode2->id,
            'dialogue' => 'Fala do herói no ato 2',
        ]);

        // 4. Check ExcelData saved
        $this->assertDatabaseHas('excel_data', [
            'project_id' => $project->id,
            'file_name' => 'test.xlsx',
        ]);
    }

    public function test_import_handles_empty_rows()
    {
        $user = User::factory()->create();
        Auth::login($user);
        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Projeto Vazio',
            'description' => 'Desc',
        ]);

        $rows = new Collection([
            new Collection(['', '', '']),
            new Collection(['Hero', 'Fala']),
        ]);

        $importer = new ScriptImport($project->id, 'empty.xlsx');
        $importer->collection($rows);

        $this->assertDatabaseHas('characters', ['name' => 'Hero']);
        // Should filter out empty row and not crash
    }
}
