<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BackupProjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:backup {--user= : ID do usuário específico} {--format=json : Formato do backup (json, sql)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria backup dos projetos e dados relacionados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando backup dos projetos...');
        
        $userId = $this->option('user');
        $format = $this->option('format');
        
        if ($userId) {
            $this->backupUserProjects($userId, $format);
        } else {
            $this->backupAllProjects($format);
        }
        
        $this->info('Backup concluído!');
    }
    
    private function backupUserProjects(int $userId, string $format): void
    {
        $user = User::with('projects.characters', 'projects.scenes')->find($userId);
        
        if (!$user) {
            $this->error('Usuário não encontrado.');
            return;
        }
        
        $this->info("Fazendo backup para usuário: {$user->name}");
        
        foreach ($user->projects as $project) {
            $this->backupProject($project, $format);
        }
    }
    
    private function backupAllProjects(string $format): void
    {
        $projects = Project::with('user', 'characters', 'scenes')->get();
        
        $this->info("Fazendo backup de {$projects->count()} projetos...");
        
        $bar = $this->output->createProgressBar($projects->count());
        
        foreach ($projects as $project) {
            $this->backupProject($project, $format);
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
    }
    
    private function backupProject(Project $project, string $format): void
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "backup_{$project->id}_{$timestamp}.{$format}";
        
        $backupData = [
            'project' => $project->toArray(),
            'characters' => $project->characters->toArray(),
            'scenes' => $project->scenes->toArray(),
            'character_scenes' => $this->getCharacterScenes($project),
            'backup_metadata' => [
                'created_at' => now(),
                'user_name' => $project->user->name,
                'user_email' => $project->user->email,
                'format' => $format,
                'version' => '1.0'
            ]
        ];
        
        if ($format === 'json') {
            $content = json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } else {
            $content = $this->generateSqlBackup($backupData);
        }
        
        // Salvar backup
        $path = "backups/{$project->user_id}/{$filename}";
        Storage::put($path, $content);
        
        // Log da atividade
        Log::info('Project backup created', [
            'project_id' => $project->id,
            'user_id' => $project->user_id,
            'filename' => $filename,
            'format' => $format
        ]);
        
        $this->line("✅ Backup salvo: {$filename}");
    }
    
    private function getCharacterScenes(Project $project): array
    {
        return DB::table('character_scene as cs')
            ->join('scenes as s', 's.id', '=', 'cs.scene_id')
            ->join('characters as c', 'c.id', '=', 'cs.character_id')
            ->where('s.project_id', $project->id)
            ->select('cs.*', 's.title as scene_title', 'c.name as character_name')
            ->get()
            ->toArray();
    }
    
    private function generateSqlBackup(array $data): string
    {
        $sql = "-- Backup do Projeto: {$data['project']['name']}\n";
        $sql .= "-- Data: {$data['backup_metadata']['created_at']}\n";
        $sql .= "-- Usuário: {$data['backup_metadata']['user_name']}\n\n";
        
        // SQL para projeto
        $project = $data['project'];
        $sql .= "INSERT INTO projects (id, user_id, name, description, created_at, updated_at) VALUES ";
        $sql .= "({$project['id']}, {$project['user_id']}, '{$project['name']}', '{$project['description']}', ";
        $sql .= "'{$project['created_at']}', '{$project['updated_at']}');\n\n";
        
        // SQL para personagens
        if (!empty($data['characters'])) {
            $sql .= "INSERT INTO characters (id, user_id, project_id, name, role, description, type, goals, fears, history, personality, notes, created_at, updated_at) VALUES\n";
            $characters = [];
            foreach ($data['characters'] as $character) {
                $characters[] = "({$character['id']}, {$character['user_id']}, {$character['project_id']}, '{$character['name']}', '{$character['role']}', '{$character['description']}', '{$character['type']}', '{$character['goals']}', '{$character['fears']}', '{$character['history']}', '{$character['personality']}', '{$character['notes']}', '{$character['created_at']}', '{$character['updated_at']}')";
            }
            $sql .= implode(",\n", $characters) . ";\n\n";
        }
        
        // SQL para cenas
        if (!empty($data['scenes'])) {
            $sql .= "INSERT INTO scenes (id, user_id, project_id, title, description, duration, `order`, created_at, updated_at) VALUES\n";
            $scenes = [];
            foreach ($data['scenes'] as $scene) {
                $scenes[] = "({$scene['id']}, {$scene['user_id']}, {$scene['project_id']}, '{$scene['title']}', '{$scene['description']}', {$scene['duration']}, {$scene['order']}, '{$scene['created_at']}', '{$scene['updated_at']}')";
            }
            $sql .= implode(",\n", $scenes) . ";\n\n";
        }
        
        return $sql;
    }
}
