<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\CacheService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $projects = CacheService::getUserProjects(auth()->id());
        return view('dashboard', compact('projects'));
    }
} 