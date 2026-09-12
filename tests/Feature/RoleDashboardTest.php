<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create(['email_verified_at' => now()]);
    }

    private function manager(): User
    {
        $dept = Department::create(['name' => 'Engineering']);
        $manager = User::factory()->manager()->create(['email_verified_at' => now(), 'department_id' => $dept->id]);
        User::factory()->employee()->create(['department_id' => $dept->id]);

        return $manager;
    }

    private function employee(): User
    {
        return User::factory()->employee()->create(['email_verified_at' => now()]);
    }

    public function test_admin_dashboard_loads(): void
    {
        $this->actingAs($this->admin())->get('/admin/dashboard')->assertOk();
    }

    public function test_admin_employees_index_loads(): void
    {
        $this->actingAs($this->admin())->get('/admin/employees')->assertOk();
    }

    public function test_admin_departments_index_loads(): void
    {
        $this->actingAs($this->admin())->get('/admin/departments')->assertOk();
    }

    public function test_admin_tasks_index_loads(): void
    {
        $this->actingAs($this->admin())->get('/admin/tasks')->assertOk();
    }

    public function test_admin_reports_index_loads(): void
    {
        $this->actingAs($this->admin())->get('/admin/reports')->assertOk();
    }

    public function test_admin_activity_logs_loads(): void
    {
        $this->actingAs($this->admin())->get('/admin/activity-logs')->assertOk();
    }

    public function test_manager_dashboard_loads(): void
    {
        $this->actingAs($this->manager())->get('/manager/dashboard')->assertOk();
    }

    public function test_manager_team_tasks_list_loads(): void
    {
        $this->actingAs($this->manager())->get('/manager/team-tasks')->assertOk();
    }

    public function test_manager_team_tasks_board_loads(): void
    {
        $this->actingAs($this->manager())->get('/manager/team-tasks/board')->assertOk();
    }

    public function test_manager_calendar_loads(): void
    {
        $this->actingAs($this->manager())->get('/manager/team-tasks/calendar')->assertOk();
    }

    public function test_manager_gantt_loads(): void
    {
        $this->actingAs($this->manager())->get('/manager/team-tasks/gantt')->assertOk();
    }

    public function test_manager_performance_loads(): void
    {
        $this->actingAs($this->manager())->get('/manager/performance')->assertOk();
    }

    public function test_manager_reports_loads(): void
    {
        $this->actingAs($this->manager())->get('/manager/reports')->assertOk();
    }

    public function test_employee_dashboard_loads(): void
    {
        $this->actingAs($this->employee())->get('/employee/dashboard')->assertOk();
    }

    public function test_employee_my_tasks_loads(): void
    {
        $this->actingAs($this->employee())->get('/employee/my-tasks')->assertOk();
    }

    public function test_employee_notifications_loads(): void
    {
        $this->actingAs($this->employee())->get('/employee/notifications')->assertOk();
    }

    public function test_employee_without_review_tasks_sees_no_review_navigation(): void
    {
        $employee = $this->employee();

        $this->actingAs($employee)
            ->get('/employee/dashboard')
            ->assertOk()
            ->assertDontSee('Review Tasks')
            ->assertDontSee('Tasks to Review')
            ->assertDontSee('Nothing to review.');
    }

    public function test_employee_with_pending_review_sees_review_navigation(): void
    {
        $employee = $this->employee();
        $creator = $this->admin();

        Task::factory()->underReview()->create([
            'created_by' => $creator->id,
            'reviewer_id' => $employee->id,
        ]);

        $this->actingAs($employee)
            ->get('/employee/dashboard')
            ->assertOk()
            ->assertSee('Review Tasks')
            ->assertSee('Tasks to Review');
    }
}
