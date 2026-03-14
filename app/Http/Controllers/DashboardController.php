<?php

namespace App\Http\Controllers;

use App\Models\ProjectInvitation;
use App\Services\CacheService;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $projects = CacheService::getUserProjects($user->id);

        $pendingInvitations = ProjectInvitation::with(['project', 'invitedBy'])
            ->whereNull('accepted_at')
            ->where('email', mb_strtolower($user->email))
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderByDesc('created_at')
            ->get();

        return view('dashboard', compact('projects', 'pendingInvitations'));
    }
}
