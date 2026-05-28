<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;class AdminSeeder extends Seeder
{
    /**
     * Crée l'unique compte administrateur du blog.
     * Modifiez email et password avant de lancer le seeder.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@thehackerexperiment.com'],
            [
                'name'     => 'TheHackerExperiment',
                'email'    => 'admin@thehackerexperiment.com',
                'password' => 'changeme123', // sera hashé automatiquement par le cast 'hashed'
            ]
        );
    }
}
