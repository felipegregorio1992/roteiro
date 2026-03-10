<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\Scene;
use App\Services\SceneService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SceneList extends Component
{
    public Project $project;
    
    public $expandedSceneId = null;
    public $editingDialogues = [];

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount(Project $project)
    {
        $this->project = $project;
    }

    public function toggleExpand($sceneId)
    {
        if ($this->expandedSceneId === $sceneId) {
            $this->expandedSceneId = null;
            $this->editingDialogues = [];
        } else {
            $this->expandedSceneId = $sceneId;
            $this->loadDialogues($sceneId);
        }
    }

    public function loadDialogues($sceneId)
    {
        $scene = Scene::with('characters')->find($sceneId);
        if ($scene) {
            $this->editingDialogues = $scene->characters->mapWithKeys(function ($char) {
                return [$char->id => $char->pivot->dialogue];
            })->toArray();
        }
    }

    public function saveDialogue($sceneId, $characterId)
    {
        if (!isset($this->editingDialogues[$characterId])) {
            return;
        }

        $scene = Scene::find($sceneId);
        if ($scene && $scene->project_id === $this->project->id) {
            $scene->characters()->updateExistingPivot($characterId, [
                'dialogue' => $this->editingDialogues[$characterId]
            ]);
            
            // Opcional: Notificação de sucesso
            $this->dispatch('notify', 'Diálogo salvo!');
        }
    }

    public function render()
    {
        $sceneService = app(SceneService::class);
        $groupedActs = $sceneService->getScenesGroupedByAct($this->project->id, Auth::id());

        // Transform into array structure compatible with view
        $acts = [];
        foreach ($groupedActs as $actNumber => $actData) {
            $acts[$actNumber] = [
                'title' => $actData['title'],
                'scenes' => []
            ];

            foreach ($actData['scenes'] as $scene) {
                $acts[$actNumber]['scenes'][] = [
                    'id' => $scene->id,
                    'title' => $scene->title,
                    'description' => $scene->description,
                    'duration' => $scene->duration,
                    'order' => $scene->order,
                    'characters' => $scene->characters->map(function($character) {
                        return [
                            'id' => $character->id,
                            'name' => $character->name,
                            'dialogue' => $character->pivot->dialogue ?? ''
                        ];
                    })->toArray()
                ];
            }
        }

        return view('livewire.scene-list', [
            'acts' => $acts
        ]);
    }

    public function updateSceneOrder($groups)
    {
        $sceneService = app(SceneService::class);

        foreach ($groups as $group) {
            $actNumber = $group['value'];
            
            if (!isset($group['items'])) {
                continue;
            }

            $scenesData = [];
            foreach ($group['items'] as $item) {
                $scenesData[] = [
                    'id' => $item['value'],
                    'order' => $item['order']
                ];
            }

            $sceneService->reorderScenes($this->project->id, $actNumber, $scenesData);
        }
    }

    public function moveActUp($actNumber)
    {
        if ($actNumber <= 1) return;
        
        $prevAct = $actNumber - 1;
        
        app(SceneService::class)->swapActs($this->project->id, $actNumber, $prevAct);
        
        $this->dispatch('notify', 'Ato movido com sucesso!');
    }

    public function moveActDown($actNumber)
    {
        $nextAct = $actNumber + 1;
        
        // Check if next act exists
        $exists = Scene::where('project_id', $this->project->id)
            ->where('act', $nextAct)
            ->exists();
            
        if (!$exists) return;

        app(SceneService::class)->swapActs($this->project->id, $actNumber, $nextAct);

        $this->dispatch('notify', 'Ato movido com sucesso!');
    }

    public function deleteScene($sceneId)
    {
        $scene = Scene::find($sceneId);
        
        if ($scene && $scene->project_id === $this->project->id) {
            app(SceneService::class)->deleteScene($scene);
            session()->flash('success', 'Cena excluída com sucesso.');
        }
    }
}
