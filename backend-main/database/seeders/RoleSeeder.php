<?php

namespace Database\Seeders;

Use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{

    public function run(): void
    {
        Role::insert([
            ['nama_role' => 'Admin'],
            ['nama_role' => 'Dinas Kesehatan'],
            ['nama_role' => 'Dinas KB'],
            ['nama_role' => 'Dinas Sosial'],
            ['nama_role' => 'Dinas Pertanian'],
            ['nama_role' => 'Dinas PU'],
            ['nama_role' => 'Dispermades'],
            ['nama_role' => 'Kader Desa'],
        ]);
    }
}
