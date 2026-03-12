<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\User;
use App\Services\CacheService;
use Illuminate\Console\Command;

class WarmUpCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warm-up {--user= : ID do usuário específico} {--project= : ID do projeto específico}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Faz warm up do cache para melhorar performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando warm up do cache...');

        $userId = $this->option('user');
        $projectId = $this->option('project');

        if ($userId && $projectId) {
            // Warm up para usuário e projeto específicos
            $this->warmUpUserProject($userId, $projectId);
        } elseif ($userId) {
            // Warm up para todos os projetos de um usuário
            $this->warmUpUser($userId);
        } else {
            // Warm up para todos os usuários
            $this->warmUpAll();
        }

        $this->info('Warm up do cache concluído!');
    }

    private function warmUpUserProject(int $userId, int $projectId): void
    {
        $user = User::find($userId);
        $project = Project::find($projectId);

        if (! $user || ! $project) {
            $this->error('Usuário ou projeto não encontrado.');

            return;
        }

        $this->info("Fazendo warm up para usuário {$user->name} e projeto {$project->name}...");
        CacheService::warmUpProjectCache($projectId, $userId);
        $this->info("✅ Warm up concluído para projeto {$project->name}");
    }

    private function warmUpUser(int $userId): void
    {
        $user = User::find($userId);
        if (! $user) {
            $this->error('Usuário não encontrado.');

            return;
        }

        $projects = $user->projects;
        $this->info("Fazendo warm up para usuário {$user->name} ({$projects->count()} projetos)...");

        $bar = $this->output->createProgressBar($projects->count());

        foreach ($projects as $project) {
            CacheService::warmUpProjectCache($project->id, $userId);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Warm up concluído para {$projects->count()} projetos");
    }

    private function warmUpAll(): void
    {
        $users = User::with('projects')->get();
        $totalProjects = $users->sum(function ($user) {
            return $user->projects->count();
        });

        $this->info("Fazendo warm up para {$users->count()} usuários ({$totalProjects} projetos)...");

        $bar = $this->output->createProgressBar($totalProjects);

        foreach ($users as $user) {
            foreach ($user->projects as $project) {
                CacheService::warmUpProjectCache($project->id, $user->id);
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Warm up concluído para {$totalProjects} projetos de {$users->count()} usuários");
    }
}
