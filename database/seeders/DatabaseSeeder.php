<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Department::insert([
            ['name' => 'Engineering', 'description' => 'Software development and technical operations', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Marketing', 'description' => 'Brand management and marketing campaigns', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sales', 'description' => 'Revenue generation and client relations', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Design', 'description' => 'UI/UX and visual design', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'HR', 'description' => 'Human resources and employee management', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'department_id' => Department::where('name', 'Engineering')->first()->id,
        ]);

        $manager = User::factory()->manager()->create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'department_id' => Department::where('name', 'HR')->first()->id,
        ]);

        $employees = User::factory()->count(8)->employee()->create();

        foreach ($employees as $i => $employee) {
            $task = Task::factory()->create([
                'title' => "Sample task for {$employee->name}",
                'created_by' => $manager->id,
            ]);

            $task->assignees()->sync([$employee->id]);
        }
    }
}
