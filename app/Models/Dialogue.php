<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dialogue extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'character_id',
        'target_character_id',
        'scene_id',
        'content',
        'order',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function targetCharacter(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'target_character_id');
    }

    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }
}
