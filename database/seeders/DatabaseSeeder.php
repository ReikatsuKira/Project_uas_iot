<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Hapus dulu semua user (opsional)
        User::truncate();

        // Data user default
        $users = [
            ['rfid' => '11DD1316', 'nama' => 'surya', ],
            ['rfid' => '116CC16', 'nama' => 'nata']
        ];

        // Insert satu per satu
        foreach ($users as $u) {
            User::create($u);
        }
    }
}
