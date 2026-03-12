<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'title',
        'description',
        'duration',
        'order',
        'episode_number',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function characters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class)
            ->withPivot('dialogue')
            ->withTimestamps();
    }

    public function scenes(): HasMany
    {
        return $this->hasMany(Scene::class)->orderBy('order');
    }
}
