<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\OverviewSheet;
use App\Exports\Sheets\CharactersSheet;
use App\Exports\Sheets\ScenesSheet;
use App\Exports\Sheets\DialogueMatrixSheet;
use App\Exports\Sheets\StoryMatrixSheet;

class ProjectExport implements WithMultipleSheets
{
    protected $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function sheets(): array
    {
        return [
            new OverviewSheet($this->project),
            new StoryMatrixSheet($this->project),
            new CharactersSheet($this->project),
            new ScenesSheet($this->project),
            new DialogueMatrixSheet($this->project),
        ];
    }
}
