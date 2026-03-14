<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role'])
            ->withTimestamps();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(ProjectInvitation::class);
    }

    public function characters()
    {
        return $this->hasMany(Character::class);
    }

    public function scenes()
    {
        return $this->hasMany(Scene::class);
    }

    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }
}
