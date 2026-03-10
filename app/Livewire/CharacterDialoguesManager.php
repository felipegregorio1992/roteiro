<?php

namespace App\Livewire;

use App\Models\Character;
use App\Models\Dialogue;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class CharacterDialoguesManager extends Component
{
    public $isOpen = false;
    public $character;
    public $content;
    public $target_character_id;
    public $scene_id;
    public $project_characters = [];

    protected $rules = [
        'content' => 'required|string',
        'target_character_id' => 'nullable|exists:characters,id',
        'scene_id' => 'nullable|exists:scenes,id',
    ];

    #[On('openDialogueManager')]
    public function openDialogueManager($characterId)
    {
        $character = Character::with(['dialogues' => function($query) {
            $query->latest();
        }, 'dialogues.targetCharacter', 'dialogues.scene'])->findOrFail($characterId);
        
        // Ensure the user owns the character or project
        if ($character->user_id !== Auth::id()) {
            abort(403);
        }

        $this->character = $character;
        
        $this->project_characters = Character::where('project_id', $this->character->project_id)
            ->where('id', '!=', $characterId)
            ->orderBy('name')
            ->get();
            
        $this->isOpen = true;
        $this->resetForm();
    }

    public function close()
    {
        $this->isOpen = false;
        $this->reset(['character', 'content', 'target_character_id', 'scene_id', 'project_characters']);
    }

    public function resetForm()
    {
        $this->content = '';
        $this->target_character_id = null;
        $this->scene_id = null;
    }

    public function save()
    {
        $this->validate();

        Dialogue::create([
            'project_id' => $this->character->project_id,
            'character_id' => $this->character->id,
            'target_character_id' => $this->target_character_id ?: null,
            'scene_id' => $this->scene_id ?: null,
            'content' => $this->content,
        ]);

        $this->character->refresh();
        $this->resetForm();
    }

    public function delete($id)
    {
        $dialogue = Dialogue::findOrFail($id);
        
        if ($dialogue->character_id === $this->character->id) {
            $dialogue->delete();
            $this->character->refresh();
        }
    }

    public function render()
    {
        return view('livewire.character-dialogues-manager');
    }
}
