<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Character extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'role',
        'description',
        'user_id',
        'project_id',
        'goals',
        'fears',
        'history',
        'personality',
        'notes',
        'act_contents',
    ];

    protected $casts = [
        'act_contents' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function scenes(): BelongsToMany
    {
        return $this->belongsToMany(Scene::class, 'character_scene')
            ->withPivot('dialogue', 'is_hidden')
            ->withTimestamps();
    }

    public function episodes(): BelongsToMany
    {
        return $this->belongsToMany(Episode::class)
            ->withPivot('dialogue')
            ->withTimestamps();
    }

    public function dialogues(): HasMany
    {
        return $this->hasMany(Dialogue::class);
    }
}
