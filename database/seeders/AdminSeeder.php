<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Criar usuário administrador padrão (se não existir)
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'Admin123!@#Seguro')),
            ]
        );

        // Criar um projeto inicial de exemplo (se não existir)
        $project = $admin->projects()->firstOrCreate(
            ['name' => 'Meu Primeiro Roteiro'],
            [
                'description' => 'Este é um projeto de exemplo para você começar.',
            ]
        );

        // Se o projeto foi recém-criado, adicionar dados de exemplo
        if ($project->wasRecentlyCreated) {
            // Criar alguns personagens de exemplo
            $characters = [
                [
                    'name' => 'Nuno Baldaracci',
                    'role' => 'Protagonista',
                    'description' => 'Personagem principal do Ato 1',
                ],
                [
                    'name' => 'Clara Baldaracci',
                    'role' => 'Protagonista',
                    'description' => 'Personagem principal do Ato 2',
                ],
            ];

            foreach ($characters as $character) {
                $project->characters()->create(array_merge($character, ['user_id' => $admin->id]));
            }

            // Criar algumas cenas de exemplo
            $scenes = [
                [
                    'title' => 'Ato 1',
                    'description' => 'Introdução da história',
                    'duration' => 60,
                    'order' => 1,
                ],
                [
                    'title' => 'Ato 2',
                    'description' => 'Desenvolvimento da trama',
                    'duration' => 45,
                    'order' => 2,
                ],
            ];

            foreach ($scenes as $scene) {
                $project->scenes()->create(array_merge($scene, ['user_id' => $admin->id]));
            }
        }
    }
} 