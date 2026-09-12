<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportViewTest extends TestCase
{
    use RefreshDatabase;

    private function department(): Department
    {
        return Department::create(['name' => 'Engineering']);
    }

    public function test_admin_report_overview_page_renders(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($admin)->get(route('admin.reports.index'));

        $response->assertOk()
            ->assertSee('Total Tasks')
            ->assertSee('Department Performance');
    }

    public function test_admin_report_types_render(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);

        foreach (['employee', 'department', 'review', 'overdue', 'updates', 'turnaround'] as $type) {
            $this->actingAs($admin)
                ->get(route('admin.reports.index', ['type' => $type]))
                ->assertOk();
        }
    }

    public function test_admin_employee_report_shows_review_metrics(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $dept = $this->department();
        $employee = User::factory()->employee()->create(['email_verified_at' => now(), 'department_id' => $dept->id]);

        $task = Task::create([
            'title' => 'Performance task',
            'status' => 'in_progress',
            'priority' => 'medium',
            'created_by' => $admin->id,
        ]);
        $task->assignees()->attach($employee->id, ['assigned_at' => now()]);

        $this->actingAs($admin)
            ->get(route('admin.reports.index', ['type' => 'employee', 'employee_id' => $employee->id]))
            ->assertOk()
            ->assertSee('Review Cycles')
            ->assertSee('Resubmissions');
    }

    public function test_manager_report_overview_page_renders(): void
    {
        $dept = $this->department();
        $manager = User::factory()->manager()->create(['email_verified_at' => now(), 'department_id' => $dept->id]);

        $this->actingAs($manager)
            ->get(route('manager.reports.index'))
            ->assertOk()
            ->assertSee('Total Tasks');
    }

    public function test_manager_report_types_render(): void
    {
        $dept = $this->department();
        $manager = User::factory()->manager()->create(['email_verified_at' => now(), 'department_id' => $dept->id]);

        foreach (['employee', 'department', 'review', 'overdue', 'updates', 'turnaround'] as $type) {
            $this->actingAs($manager)
                ->get(route('manager.reports.index', ['type' => $type]))
                ->assertOk();
        }
    }

    public function test_manager_performance_page_renders_workflow_metrics(): void
    {
        $dept = $this->department();
        $manager = User::factory()->manager()->create(['email_verified_at' => now(), 'department_id' => $dept->id]);
        $member = User::factory()->employee()->create(['email_verified_at' => now(), 'department_id' => $dept->id]);

        $task = Task::create([
            'title' => 'Perf metric task',
            'status' => 'in_progress',
            'priority' => 'medium',
            'created_by' => $manager->id,
        ]);
        $task->assignees()->attach($member->id, ['assigned_at' => now()]);

        $this->actingAs($manager)
            ->get(route('manager.performance'))
            ->assertOk()
            ->assertSee('Member Performance')
            ->assertSee('Reviews');
    }
}
