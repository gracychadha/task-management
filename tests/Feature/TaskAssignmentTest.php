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

    public function test_assignment_notification_includes_task_details(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $dept = Department::create(['name' => 'Engineering']);
        $employee = User::factory()->employee()->create(['email_verified_at' => now(), 'department_id' => $dept->id]);

        $this->actingAs($admin)->post('/admin/tasks', [
            'title' => 'Ship the feature',
            'description' => 'Build and deploy the feature end to end.',
            'status' => 'new',
            'priority' => 'high',
            'due_date' => '2026-12-31',
            'start_date' => '2026-09-10',
            'assigned_to' => [$employee->id],
        ])->assertRedirect();

        $task = Task::where('title', 'Ship the feature')->first();
        $notification = UserNotification::where('type', 'task_assignment')->where('data->task_id', $task->id)->first();

        $this->assertNotNull($notification);
        $this->assertSame('New task assigned to you', $notification->title);
        $this->assertStringContainsString("Task 'Ship the feature' has been assigned to you.", $notification->message);
        $this->assertStringContainsString('Priority: High', $notification->message);
        $this->assertStringContainsString('Due date: Dec 31, 2026', $notification->message);
        $this->assertStringContainsString('Start date: Sep 10, 2026', $notification->message);
        $this->assertStringContainsString('Build and deploy the feature end to end.', $notification->message);
        $this->assertStringContainsString('Created by: '.$admin->name, $notification->message);
    }

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
            'status' => 'new',
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
            'status' => 'new',
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
            'status' => 'new',
            'priority' => 'low',
            'assigned_to' => $assigneeIds,
        ])->assertRedirect();

        $task = Task::where('title', 'Q3 planning')->first();
        $this->assertNotNull($task);
        $this->assertEqualsCanonicalizing($assigneeIds, $task->assignees()->pluck('users.id')->all());
    }

    public function test_task_creation_notifies_the_department_manager(): void
    {
        $admin = $this->admin();
        $dept = Department::create(['name' => 'Engineering']);
        $manager = User::factory()->manager()->create(['email_verified_at' => now(), 'department_id' => $dept->id]);
        $employee = User::factory()->employee()->create(['department_id' => $dept->id]);

        $this->actingAs($admin)->post('/admin/tasks', [
            'title' => 'Manager visibility',
            'status' => 'new',
            'priority' => 'medium',
            'department_id' => $dept->id,
            'assigned_to' => [$employee->id],
        ])->assertRedirect();

        $task = Task::where('title', 'Manager visibility')->first();
        $notification = UserNotification::where('type', 'task_assignment')
            ->where('user_id', $manager->id)
            ->where('data->task_id', $task->id)
            ->first();

        $this->assertNotNull($notification);
        $this->assertSame('New task assigned to your team', $notification->title);
    }

    public function test_admin_can_assign_reviewer_from_a_different_department(): void
    {
        $admin = $this->admin();
        $assigneeDept = Department::create(['name' => 'Engineering']);
        $reviewerDept = Department::create(['name' => 'Design']);
        $employee = User::factory()->employee()->create(['department_id' => $assigneeDept->id]);
        $reviewer = User::factory()->employee()->create(['department_id' => $reviewerDept->id]);

        $this->actingAs($admin)->post('/admin/tasks', [
            'title' => 'Cross department review',
            'status' => 'new',
            'priority' => 'medium',
            'department_id' => $assigneeDept->id,
            'assigned_to' => [$employee->id],
            'reviewer_id' => $reviewer->id,
        ])->assertRedirect();

        $task = Task::where('title', 'Cross department review')->first();
        $this->assertSame($reviewer->id, $task->reviewer_id);
    }

    public function test_task_creation_rejects_reviewer_that_is_also_an_assignee(): void
    {
        $admin = $this->admin();
        $dept = Department::create(['name' => 'Engineering']);
        $employee = User::factory()->employee()->create(['department_id' => $dept->id]);

        $this->actingAs($admin)->post('/admin/tasks', [
            'title' => 'Invalid reviewer',
            'status' => 'new',
            'priority' => 'medium',
            'assigned_to' => [$employee->id],
            'reviewer_id' => $employee->id,
        ])->assertSessionHasErrors('reviewer_id');

        $this->assertDatabaseMissing('tasks', ['title' => 'Invalid reviewer']);
    }

    public function test_manager_create_page_lists_assignees_by_department(): void
    {
        $dept = Department::create(['name' => 'HR']);
        $otherDept = Department::create(['name' => 'Engineering']);
        $manager = User::factory()->manager()->create(['email_verified_at' => now(), 'department_id' => $dept->id]);
        $member = User::factory()->employee()->create(['department_id' => $dept->id]);
        $otherMember = User::factory()->employee()->create(['department_id' => $otherDept->id]);

        $response = $this->actingAs($manager)->get(route('manager.team-tasks.create'));

        $response->assertOk();
        $response->assertSee('Assignee Department');
        $response->assertSee('Reviewer Department');
        $response->assertSee($member->name);
        $response->assertSee($otherMember->name);
        $response->assertSee("data-department=\"{$dept->id}\"", false);
        $response->assertSee("data-department=\"{$otherDept->id}\"", false);
    }

    public function test_admin_create_page_lists_reviewers_with_department(): void
    {
        $admin = $this->admin();
        $dept = Department::create(['name' => 'Engineering']);
        $reviewer = User::factory()->employee()->create(['department_id' => $dept->id]);

        $response = $this->actingAs($admin)->get(route('admin.tasks.create'));

        $response->assertOk();
        $response->assertSee('Assignees');
        $response->assertSee($reviewer->name);
        $response->assertSee("data-department=\"{$dept->id}\"", false);
    }

    public function test_manager_create_page_hides_reviewers_who_are_selected_assignees(): void
    {
        $dept = Department::create(['name' => 'HR']);
        $manager = User::factory()->manager()->create(['email_verified_at' => now(), 'department_id' => $dept->id]);
        $employee = User::factory()->employee()->create(['email_verified_at' => now(), 'department_id' => $dept->id]);

        $response = $this->actingAs($manager)->get(route('manager.team-tasks.create'));

        $response->assertOk();
        $response->assertSee('Employees selected as assignees are hidden');
        $response->assertSee('!assigneeIds.includes('.$employee->id, false);
        $response->assertSee('reviewerMatches(&quot;', false);
        $response->assertDontSee('reviewerMatches("'.$employee->name, false);
    }

    public function test_manager_dashboard_includes_tasks_for_their_department(): void
    {
        $dept = Department::create(['name' => 'HR']);
        $otherDept = Department::create(['name' => 'Engineering']);
        $manager = User::factory()->manager()->create(['email_verified_at' => now(), 'department_id' => $dept->id]);
        $otherEmployee = User::factory()->employee()->create(['department_id' => $otherDept->id]);
        $admin = $this->admin();

        Task::create([
            'title' => 'Department tagged task',
            'status' => 'new',
            'priority' => 'medium',
            'department_id' => $dept->id,
            'created_by' => $admin->id,
        ]);

        $otherTask = Task::create([
            'title' => 'Other department task',
            'status' => 'new',
            'priority' => 'medium',
            'department_id' => $otherDept->id,
            'created_by' => $admin->id,
        ]);
        $otherTask->assignees()->attach($otherEmployee->id, ['assigned_at' => now()]);

        $response = $this->actingAs($manager)->get(route('manager.dashboard'));

        $response->assertOk();
        $response->assertSee('Department tagged task');
        $response->assertDontSee('Other department task');
    }

    public function test_admin_task_board_respects_priority_filter(): void
    {
        $admin = $this->admin();
        $dept = Department::create(['name' => 'Engineering']);
        $employee = User::factory()->employee()->create(['department_id' => $dept->id]);

        $highTask = Task::create([
            'title' => 'High priority task',
            'status' => 'new',
            'priority' => 'high',
            'created_by' => $admin->id,
        ]);
        $highTask->assignees()->attach($employee->id, ['assigned_at' => now()]);

        Task::create([
            'title' => 'Low priority task',
            'status' => 'new',
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
            'title' => 'New board task',
            'status' => 'new',
            'priority' => 'medium',
            'created_by' => $admin->id,
        ]);
        $todo->assignees()->attach($employee->id, ['assigned_at' => now()]);

        Task::create([
            'title' => 'Completed board task',
            'status' => 'completed',
            'priority' => 'medium',
            'created_by' => $admin->id,
        ])->assignees()->attach($employee->id, ['assigned_at' => now()]);

        $response = $this->actingAs($admin)->get('/admin/tasks?view=kanban&status=new');
        $response->assertOk();
        $response->assertSee('New board task');
        $response->assertDontSee('Completed board task');
    }
}
