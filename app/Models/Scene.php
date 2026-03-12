<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Scene extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'episode_id',
        'title',
        'scene_type',
        'description',
        'duration',
        'order',
        'act',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    public function characters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class, 'character_scene')
            ->withPivot('dialogue', 'is_hidden')
            ->withTimestamps();
    }
}
