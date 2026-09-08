<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Task;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create(['email_verified_at' => now()]);
    }

    public function test_admin_can_create_task_and_notify_multiple_assignees(): void
    {
        $admin = $this->admin();
        $dept = Department::create(['name' => 'Engineering']);
        $employees = User::factory()->count(3)->employee()->create(['department_id' => $dept->id]);

        $assigneeIds = $employees->pluck('id')->all();

        $this->actingAs($admin)->post('/admin/tasks', [
            'title' => 'Ship the feature',
            'description' => 'Build it',
            'status' => 'todo',
            'priority' => 'high',
            'assigned_to' => $assigneeIds,
        ])->assertRedirect();

        $task = Task::where('title', 'Ship the feature')->first();
        $this->assertNotNull($task);
        $this->assertEqualsCanonicalizing($assigneeIds, $task->assignees()->pluck('users.id')->all());

        $notified = UserNotification::where('type', 'task_assignment')->pluck('user_id')->all();
        $this->assertEqualsCanonicalizing($assigneeIds, $notified);
    }

    public function test_admin_can_assign_whole_department_and_all_members_get_notified(): void
    {
        $admin = $this->admin();
        $dept = Department::create(['name' => 'Marketing']);
        $employees = User::factory()->count(4)->employee()->create(['department_id' => $dept->id]);

        $assigneeIds = $employees->pluck('id')->all();

        // Simulate the "assign to entire department" control by submitting all member ids.
        $this->actingAs($admin)->post('/admin/tasks', [
            'title' => 'Campaign launch',
            'status' => 'todo',
            'priority' => 'medium',
            'assigned_to' => $assigneeIds,
        ])->assertRedirect();

        $task = Task::where('title', 'Campaign launch')->first();
        $this->assertNotNull($task);
        $this->assertEqualsCanonicalizing($assigneeIds, $task->assignees()->pluck('users.id')->all());

        $notified = UserNotification::where('type', 'task_assignment')->where('data->task_id', $task->id)->pluck('user_id')->all();
        $this->assertEqualsCanonicalizing($assigneeIds, $notified);
    }

    public function test_manager_can_create_task_for_department_members(): void
    {
        $dept = Department::create(['name' => 'HR']);
        $manager = User::factory()->manager()->create(['email_verified_at' => now(), 'department_id' => $dept->id]);
        $employees = User::factory()->count(2)->employee()->create(['department_id' => $dept->id]);

        $assigneeIds = $employees->pluck('id')->all();

        $this->actingAs($manager)->post('/manager/team-tasks', [
            'title' => 'Q3 planning',
            'status' => 'todo',
            'priority' => 'low',
            'assigned_to' => $assigneeIds,
        ])->assertRedirect();

        $task = Task::where('title', 'Q3 planning')->first();
        $this->assertNotNull($task);
        $this->assertEqualsCanonicalizing($assigneeIds, $task->assignees()->pluck('users.id')->all());
    }

    public function test_admin_task_board_respects_priority_filter(): void
    {
        $admin = $this->admin();
        $dept = Department::create(['name' => 'Engineering']);
        $employee = User::factory()->employee()->create(['department_id' => $dept->id]);

        $highTask = Task::create([
            'title' => 'High priority task',
            'status' => 'todo',
            'priority' => 'high',
            'created_by' => $admin->id,
        ]);
        $highTask->assignees()->attach($employee->id, ['assigned_at' => now()]);

        Task::create([
            'title' => 'Low priority task',
            'status' => 'todo',
            'priority' => 'low',
            'created_by' => $admin->id,
        ])->assignees()->attach($employee->id, ['assigned_at' => now()]);

        $response = $this->actingAs($admin)->get('/admin/tasks?view=kanban&priority=high');
        $response->assertOk();
        $response->assertSee('High priority task');
        $response->assertDontSee('Low priority task');
    }

    public function test_admin_task_board_respects_status_filter(): void
    {
        $admin = $this->admin();
        $dept = Department::create(['name' => 'Engineering']);
        $employee = User::factory()->employee()->create(['department_id' => $dept->id]);

        $todo = Task::create([
            'title' => 'Todo board task',
            'status' => 'todo',
            'priority' => 'medium',
            'created_by' => $admin->id,
        ]);
        $todo->assignees()->attach($employee->id, ['assigned_at' => now()]);

        Task::create([
            'title' => 'Done board task',
            'status' => 'done',
            'priority' => 'medium',
            'created_by' => $admin->id,
        ])->assignees()->attach($employee->id, ['assigned_at' => now()]);

        $response = $this->actingAs($admin)->get('/admin/tasks?view=kanban&status=todo');
        $response->assertOk();
        $response->assertSee('Todo board task');
        $response->assertDontSee('Done board task');
    }
}
