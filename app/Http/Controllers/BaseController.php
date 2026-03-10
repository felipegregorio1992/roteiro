<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\CacheService;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

abstract class BaseController extends Controller
{
    /**
     * Valida acesso do usuário ao projeto
     */
    protected function validateProjectAccess(int $projectId, int $userId): Project
    {
        $project = Project::findOrFail($projectId);
        
        if ($project->user_id !== $userId) {
            abort(403, 'Você não tem permissão para acessar este projeto.');
        }
        
        return $project;
    }
    
    /**
     * Registra atividade do usuário
     */
    protected function logActivity(string $action, array $data = []): void
    {
        LogService::logActivity($action, $data, request());
    }
    
    /**
     * Aplica paginação com configurações padrão
     */
    protected function paginate($query, int $perPage = 20): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $query->paginate($perPage);
    }
    
    /**
     * Resposta JSON padronizada para APIs
     */
    protected function jsonResponse(bool $success, string $message, $data = null, int $status = 200)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()
        ], $status);
    }
    
    /**
     * Resposta de erro padronizada
     */
    protected function errorResponse(string $message, int $status = 400, $errors = null)
    {
        return $this->jsonResponse(false, $message, $errors, $status);
    }
    
    /**
     * Resposta de sucesso padronizada
     */
    protected function successResponse(string $message, $data = null, int $status = 200)
    {
        return $this->jsonResponse(true, $message, $data, $status);
    }
}
