<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Task;
use App\Models\TaskReview;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

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

        $employees = User::factory()->count(8)->employee()->create([
            'department_id' => $manager->department_id,
        ]);

        $rotatingStatuses = [
            'new',
            'in_progress',
            'in_progress',
            'under_review',
            'changes_requested',
            'completed',
            'on_hold',
            'cancelled',
        ];

        foreach ($employees as $i => $employee) {
            $status = $rotatingStatuses[$i % count($rotatingStatuses)];
            $inWorkflow = in_array($status, ['under_review', 'changes_requested', 'completed'], true);

            $task = Task::factory()->create([
                'title' => "Sample task for {$employee->name}",
                'created_by' => $manager->id,
                'status' => $status,
                'updates_count' => $inWorkflow ? 1 : 0,
                'submitted_at' => $inWorkflow ? now()->subDays(3) : null,
            ]);
            $task->assignees()->sync([$employee->id]);

            if ($status === 'under_review') {
                $task->update(['reviewer_id' => $employees[($i + 1) % 8]->id]);
            }

            if ($status === 'changes_requested') {
                $task->update([
                    'reviewer_id' => $employees[($i + 1) % 8]->id,
                    'review_decision' => Task::REVIEW_NEEDS_CHANGES,
                    'reviewed_at' => now()->subDay(),
                    'review_comment' => 'Almost there, please adjust the spacing and resubmit.',
                ]);
                TaskReview::factory()->changesRequested()->create([
                    'task_id' => $task->id,
                    'reviewer_id' => $employees[($i + 1) % 8]->id,
                    'review_number' => 1,
                    'comment' => 'Please adjust the layout spacing before final approval.',
                    'reviewed_at' => now()->subDay(),
                ]);
            }

            if ($status === 'completed') {
                $task->update([
                    'reviewer_id' => $employees[($i + 1) % 8]->id,
                    'review_decision' => Task::REVIEW_APPROVED,
                    'reviewed_at' => now()->subDays(2),
                    'completed_at' => now()->subDays(2),
                ]);
                TaskReview::factory()->approved()->create([
                    'task_id' => $task->id,
                    'reviewer_id' => $employees[($i + 1) % 8]->id,
                    'review_number' => 1,
                    'comment' => 'Great work, approved!',
                    'reviewed_at' => now()->subDays(2),
                ]);
            }
        }

        $task = Task::factory()->create([
            'title' => 'Conference Banner Redesign',
            'description' => 'Update the banner dimensions and replace the old logo with the new brand kit.',
            'created_by' => $manager->id,
            'status' => 'under_review',
            'priority' => 'high',
            'updates_count' => 2,
            'resubmissions_count' => 1,
            'submitted_at' => now(),
        ]);
        $task->assignees()->sync([$employees[0]->id]);
        $task->update([
            'reviewer_id' => $employees[1]->id,
            'department_id' => $employees[0]->department_id,
        ]);

        TaskReview::factory()->changesRequested()->create([
            'task_id' => $task->id,
            'reviewer_id' => $employees[1]->id,
            'review_number' => 1,
            'comment' => 'Please update the banner dimensions and replace the old logo.',
            'reviewed_at' => now()->subDays(2),
        ]);
        TaskReview::factory()->create([
            'task_id' => $task->id,
            'reviewer_id' => $employees[1]->id,
            'review_number' => 2,
            'decision' => 'changes_requested',
            'comment' => 'The revised dimensions are correct, but the mobile preview is cropped.',
            'reviewed_at' => now()->subDay(),
        ]);
    }
}
