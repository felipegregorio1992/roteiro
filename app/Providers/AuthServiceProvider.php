<?php

namespace App\Providers;

use App\Models\Character;
use App\Models\Episode;
use App\Models\Project;
use App\Models\Scene;
use App\Policies\CharacterPolicy;
use App\Policies\EpisodePolicy;
use App\Policies\ProjectPolicy;
use App\Policies\ScenePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Character::class => CharacterPolicy::class,
        Episode::class => EpisodePolicy::class,
        Project::class => ProjectPolicy::class,
        Scene::class => ScenePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
