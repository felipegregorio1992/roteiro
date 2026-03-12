<?php

namespace App\Http\Controllers;

use App\Services\CacheService;

class DashboardController extends Controller
{
    public function index()
    {
        $projects = CacheService::getUserProjects(auth()->id());

        return view('dashboard', compact('projects'));
    }
}
