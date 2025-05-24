<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Desa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run()
    {
        // 1. Buat user default (admin, dinkes, dll)
        $this->createDefaultUsers();
        
        // 2. Buat kader untuk setiap desa
        $this->createKaderForAllDesas();
    }

    protected function createDefaultUsers()
    {
        $defaultUsers = [
            [
                'username' => 'admin',
                'password' => 'admin123',
                'id_role' => 1,
                'id_desa' => null
            ],
            [
                'username' => 'dinkes',
                'password' => 'dinkes123',
                'id_role' => 2,
                'id_desa' => null
            ],
            [
                'username' => 'dinkb',
                'password' => 'dinkb123',
                'id_role' => 3,
                'id_desa' => null
            ],
            [
                'username' => 'dinsos',
                'password' => 'dinsos123',
                'id_role' => 4,
                'id_desa' => null
            ],
            [
                'username' => 'dispertan',
                'password' => 'dispertan123',
                'id_role' => 5,
                'id_desa' => null
            ],
            [
                'username' => 'dinpeku',
                'password' => 'dinpeku123',
                'id_role' => 6,
                'id_desa' => null
            ],
            [
                'username' => 'dispermades',
                'password' => 'dispermades123',
                'id_role' => 7,
                'id_desa' => null
            ]
        ];

        foreach ($defaultUsers as $user) {
            User::firstOrCreate(
                ['username' => $user['username']],
                [
                    'password' => Hash::make($user['password']),
                    'id_role' => $user['id_role'],
                    'id_desa' => $user['id_desa'],
                    'foto_profil' => null
                ]
            );
        }
    }

    protected function createKaderForAllDesas()
    {
        $desas = Desa::all();

        if ($desas->isEmpty()) {
            $this->command->error('Tidak ada desa ditemukan! Pastikan DesaSeeder sudah dijalankan.');
            return;
        }

        foreach ($desas as $desa) {
            // Format username: kader_[id_desa]_[nama_desa_slug]
            $username = 'kader_' . $desa->id_desa . '_' . Str::slug($desa->nama_desa);
            
            // Format password: kader[id_desa][nama_desa_slug]123
            $password = 'kader' . $desa->id_desa . Str::slug($desa->nama_desa) . '123';

            User::updateOrCreate(
                ['id_desa' => $desa->id_desa, 'id_role' => 8],
                [
                    'username' => $username,
                    'password' => Hash::make($password),
                    'foto_profil' => null
                ]
            );
        }

        $this->command->info('Berhasil membuat/memperbarui ' . $desas->count() . ' akun kader desa!');
        $this->command->info('Format password: kader[id_desa][nama-desa]123 (contoh: kader1nepungsari123)');
    }
}