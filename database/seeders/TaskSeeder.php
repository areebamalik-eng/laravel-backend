<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Task::create([
            'name' => 'Buy groceries',
            'is_done' => false,
            'due_date' => now()->addDays(1),
        ]);

        Task::create([
            'name' => 'Pay electricity bill',
            'is_done' => false,
            'due_date' => now()->addDays(3),
        ]);

        Task::create([
            'name' => 'Attend meeting',
            'is_done' => true,
            'due_date' => now(),
        ]);
    }
}

