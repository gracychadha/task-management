<?php

namespace Tests\Feature;

use App\Enums\ReviewDecision;
use App\Enums\TaskStatus;
use App\Models\Department;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReviewWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function department(): Department
    {
        return Department::create(['name' => 'Engineering']);
    }

    private function taskFor(Department $department, User $creator, string $status = TaskStatus::InProgress->value): Task
    {
        $task = Task::create([
            'title' => 'Build the landing page',
            'status' => $status,
            'priority' => 'medium',
            'created_by' => $creator->id,
        ]);
        $task->assignees()->attach($creator, ['assigned_at' => now()]);

        return $task;
    }

    private function createReviewTeam(Department $department): array
    {
        $manager = User::factory()->manager()->create(['email_verified_at' => now(), 'department_id' => $department->id]);
        $employee = User::factory()->employee()->create(['email_verified_at' => now(), 'department_id' => $department->id]);
        $reviewer = User::factory()->employee()->create(['email_verified_at' => now(), 'department_id' => $department->id]);

        return [$manager, $employee, $reviewer];
    }

    public function test_employee_can_start_a_new_task(): void
    {
        $department = $this->department();
        $employee = User::factory()->employee()->create(['email_verified_at' => now(), 'department_id' => $department->id]);
        $task = $this->taskFor($department, $employee, TaskStatus::New->value);

        $this->actingAs($employee)
            ->patch(route('tasks.update-status', $task), ['status' => 'in_progress'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(TaskStatus::InProgress->value, $task->fresh()->status);
    }

    public function test_employee_cannot_skip_directly_to_under_review_from_new(): void
    {
        $department = $this->department();
        $employee = User::factory()->employee()->create(['email_verified_at' => now(), 'department_id' => $department->id]);
        $task = $this->taskFor($department, $employee, TaskStatus::New->value);

        $this->actingAs($employee)
            ->patch(route('tasks.update-status', $task), [
                'status' => 'under_review',
                'submission_link' => 'https://example.com/deliverable',
            ])
            ->assertSessionHasErrors('status');

        $this->assertSame(TaskStatus::New->value, $task->fresh()->status);
    }

    public function test_employee_cannot_complete_a_task_directly(): void
    {
        $department = $this->department();
        $employee = User::factory()->employee()->create(['email_verified_at' => now(), 'department_id' => $department->id]);
        $task = $this->taskFor($department, $employee);

        $this->actingAs($employee)
            ->patch(route('tasks.update-status', $task), ['status' => 'completed'])
            ->assertForbidden();

        $this->assertSame(TaskStatus::InProgress->value, $task->fresh()->status);
    }

    public function test_employee_submitting_for_review_records_update_and_notifies_manager(): void
    {
        $department = $this->department();
        [$manager, $employee] = $this->createReviewTeam($department);
        $task = $this->taskFor($department, $manager);
        $task->assignees()->sync([$employee->id]);

        $this->actingAs($employee)
            ->patch(route('tasks.update-status', $task), ['status' => 'under_review'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $fresh = $task->fresh();
        $this->assertSame(TaskStatus::UnderReview->value, $fresh->status);
        $this->assertNotNull($fresh->submitted_at);
        $this->assertSame(1, $fresh->updates_count);
        $this->assertSame(0, $fresh->resubmissions_count);
        $this->assertSame(0, $fresh->reviewCyclesCount());

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $manager->id,
            'type' => 'task_ready_for_review',
            'data->task_id' => $task->id,
        ]);
    }

    public function test_employee_submitting_for_review_notifies_assigned_reviewer(): void
    {
        $department = $this->department();
        [$manager, $employee, $reviewer] = $this->createReviewTeam($department);

        $task = $this->taskFor($department, $manager);
        $task->assignees()->sync([$employee->id]);
        $task->update(['reviewer_id' => $reviewer->id]);

        $this->actingAs($employee)
            ->patch(route('tasks.update-status', $task), ['status' => 'under_review'])
            ->assertRedirect();

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $reviewer->id,
            'type' => 'task_ready_for_review',
            'data->task_id' => $task->id,
        ]);
    }

    public function test_manager_assigns_reviewer_and_reviewer_is_notified(): void
    {
        $department = $this->department();
        [$manager, $employee, $reviewer] = $this->createReviewTeam($department);

        $task = $this->taskFor($department, $manager);
        $task->assignees()->sync([$employee->id]);

        $this->actingAs($manager)
            ->post(route('reviews.assign', $task), ['reviewer_id' => $reviewer->id])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame($reviewer->id, $task->fresh()->reviewer_id);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $reviewer->id,
            'type' => 'task_review_assigned',
            'data->task_id' => $task->id,
        ]);
    }

    public function test_reviewer_approves_task_records_review_cycle_and_notifies_team(): void
    {
        $department = $this->department();
        [$manager, $employee, $reviewer] = $this->createReviewTeam($department);
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);

        $task = $this->taskFor($department, $manager);
        $task->assignees()->sync([$employee->id]);
        $task->update(['reviewer_id' => $reviewer->id]);

        $this->actingAs($employee)
            ->patch(route('tasks.update-status', $task), ['status' => 'under_review'])
            ->assertRedirect();

        $this->actingAs($reviewer)
            ->post(route('reviews.approve', $task), ['review_comment' => 'Looks good.'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $fresh = $task->fresh();
        $this->assertSame(TaskStatus::Completed->value, $fresh->status);
        $this->assertSame(Task::REVIEW_APPROVED, $fresh->review_decision);
        $this->assertNotNull($fresh->reviewed_at);
        $this->assertNotNull($fresh->completed_at);
        $this->assertSame(1, $fresh->reviewCyclesCount());

        $this->assertDatabaseHas('task_reviews', [
            'task_id' => $task->id,
            'reviewer_id' => $reviewer->id,
            'review_number' => 1,
            'decision' => ReviewDecision::Approved->value,
            'comment' => 'Looks good.',
        ]);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $employee->id,
            'type' => 'task_completed',
            'data->task_id' => $task->id,
        ]);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $manager->id,
            'type' => 'task_completed',
            'data->task_id' => $task->id,
        ]);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $admin->id,
            'type' => 'task_completed',
            'data->task_id' => $task->id,
        ]);
    }

    public function test_reviewer_requiring_changes_returns_task_to_employee(): void
    {
        $department = $this->department();
        [$manager, $employee, $reviewer] = $this->createReviewTeam($department);

        $task = $this->taskFor($department, $manager);
        $task->assignees()->sync([$employee->id]);
        $task->update(['reviewer_id' => $reviewer->id]);

        $this->actingAs($employee)
            ->patch(route('tasks.update-status', $task), ['status' => 'under_review'])
            ->assertRedirect();

        $this->actingAs($reviewer)
            ->post(route('reviews.request-changes', $task))
            ->assertSessionHasErrors('review_comment');

        $this->actingAs($reviewer)
            ->post(route('reviews.request-changes', $task), ['review_comment' => 'Please improve the copy.'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $fresh = $task->fresh();
        $this->assertSame(TaskStatus::ChangesRequested->value, $fresh->status);
        $this->assertSame(Task::REVIEW_NEEDS_CHANGES, $fresh->review_decision);
        $this->assertNotNull($fresh->reviewed_at);
        $this->assertSame($reviewer->id, $fresh->reviewer_id);

        $this->assertDatabaseHas('task_reviews', [
            'task_id' => $task->id,
            'reviewer_id' => $reviewer->id,
            'decision' => ReviewDecision::ChangesRequested->value,
        ]);
        $this->assertDatabaseHas('task_comments', [
            'task_id' => $task->id,
            'user_id' => $reviewer->id,
            'comment' => 'Review feedback: Please improve the copy.',
        ]);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $employee->id,
            'type' => 'task_changes_requested',
            'data->task_id' => $task->id,
        ]);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $manager->id,
            'type' => 'task_changes_requested',
            'data->task_id' => $task->id,
        ]);
    }

    public function test_employee_cannot_change_a_task_that_is_under_review(): void
    {
        $department = $this->department();
        [$manager, $employee, $reviewer] = $this->createReviewTeam($department);

        $task = $this->taskFor($department, $manager);
        $task->assignees()->sync([$employee->id]);
        $task->update(['reviewer_id' => $reviewer->id]);

        $this->actingAs($employee)
            ->patch(route('tasks.update-status', $task), ['status' => 'under_review'])
            ->assertRedirect();

        $this->actingAs($employee)
            ->patch(route('tasks.update-status', $task), ['status' => 'in_progress'])
            ->assertSessionHasErrors('status');

        $this->assertSame(TaskStatus::UnderReview->value, $task->fresh()->status);
    }

    public function test_admin_can_send_submitted_task_back_to_employee(): void
    {
        $department = $this->department();
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $employee = User::factory()->employee()->create(['email_verified_at' => now(), 'department_id' => $department->id]);

        $task = $this->taskFor($department, $admin, TaskStatus::UnderReview->value);
        $task->assignees()->sync([$employee->id]);

        $this->actingAs($admin)
            ->post(route('reviews.send-back', $task), [
                'assignee_id' => $employee->id,
                'update_message' => 'Add unit tests and re-submit.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $fresh = $task->fresh();
        $this->assertSame(TaskStatus::ChangesRequested->value, $fresh->status);
        $this->assertSame(Task::REVIEW_NEEDS_CHANGES, $fresh->review_decision);

        $this->assertDatabaseHas('task_comments', [
            'task_id' => $task->id,
            'user_id' => $admin->id,
            'comment' => 'Sent back with updates: Add unit tests and re-submit.',
        ]);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $employee->id,
            'type' => 'task_changes_requested',
            'data->task_id' => $task->id,
        ]);
    }

    public function test_employee_cannot_send_task_back(): void
    {
        $department = $this->department();
        [$manager, $employee] = $this->createReviewTeam($department);

        $task = $this->taskFor($department, $manager, TaskStatus::UnderReview->value);
        $task->assignees()->sync([$employee->id]);

        $this->actingAs($employee)
            ->post(route('reviews.send-back', $task), [
                'update_message' => 'Try again.',
            ])
            ->assertForbidden();
    }

    public function test_employee_who_is_not_the_reviewer_cannot_approve_a_task(): void
    {
        $department = $this->department();
        [$manager, $employee, $reviewer] = $this->createReviewTeam($department);

        $task = $this->taskFor($department, $manager, TaskStatus::UnderReview->value);
        $task->assignees()->sync([$employee->id]);
        $task->update(['reviewer_id' => $reviewer->id]);

        $this->actingAs($employee)
            ->post(route('reviews.approve', $task))
            ->assertForbidden();

        $this->assertNull($task->fresh()->review_decision);
    }

    public function test_reviewer_can_open_task_they_review(): void
    {
        $department = $this->department();
        [$manager, $employee, $reviewer] = $this->createReviewTeam($department);

        $task = $this->taskFor($department, $manager, TaskStatus::UnderReview->value);
        $task->assignees()->sync([$employee->id]);
        $task->update(['reviewer_id' => $reviewer->id]);

        $this->actingAs($reviewer)
            ->get(route('tasks.show', $task))
            ->assertOk()
            ->assertSee('Approve Task');
    }

    public function test_manager_detail_page_renders_review_workflow_panel(): void
    {
        $department = $this->department();
        [$manager, $employee] = $this->createReviewTeam($department);

        $task = $this->taskFor($department, $manager, TaskStatus::UnderReview->value);
        $task->assignees()->sync([$employee->id]);

        $this->actingAs($manager)
            ->get(route('manager.team-tasks.show', $task))
            ->assertOk()
            ->assertSee('Review Workflow')
            ->assertSee('awaiting a reviewer')
            ->assertSee('Assign Reviewer');
    }

    public function test_admin_detail_page_renders_review_panel_for_approved_task(): void
    {
        $department = $this->department();
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $employee = User::factory()->employee()->create(['email_verified_at' => now(), 'department_id' => $department->id]);

        $task = Task::create([
            'title' => 'Approved task',
            'status' => TaskStatus::Completed->value,
            'priority' => 'medium',
            'created_by' => $admin->id,
            'reviewer_id' => $employee->id,
            'reviewed_at' => now(),
            'completed_at' => now(),
            'review_decision' => Task::REVIEW_APPROVED,
            'review_comment' => 'Great work!',
        ]);
        $task->assignees()->attach($employee->id, ['assigned_at' => now()]);

        $this->actingAs($admin)
            ->get(route('admin.tasks.show', $task))
            ->assertOk()
            ->assertSee('Review Workflow')
            ->assertSee('Approved & Complete')
            ->assertSee('Great work!');
    }

    public function test_manager_dashboard_renders_review_queue_panels(): void
    {
        $department = $this->department();
        [$manager, $employee, $reviewer] = $this->createReviewTeam($department);

        $awaiting = Task::create([
            'title' => 'Awaiting panel task',
            'status' => TaskStatus::UnderReview->value,
            'priority' => 'medium',
            'created_by' => $manager->id,
        ]);
        $awaiting->assignees()->attach($employee->id, ['assigned_at' => now()]);

        $changes = Task::create([
            'title' => 'Needs changes task',
            'status' => TaskStatus::ChangesRequested->value,
            'priority' => 'medium',
            'created_by' => $manager->id,
            'reviewer_id' => $reviewer->id,
        ]);
        $changes->assignees()->attach($employee->id, ['assigned_at' => now()]);

        $response = $this->actingAs($manager)
            ->get(route('manager.dashboard'))
            ->assertOk()
            ->assertSee('Review Queue');

        $response->assertSee('Awaiting panel task');
        $response->assertSee('Needs Changes');
        $response->assertSee('Needs changes task');
    }

    public function test_employee_can_submit_task_with_link(): void
    {
        $department = $this->department();
        [$manager, $employee] = $this->createReviewTeam($department);

        $task = $this->taskFor($department, $manager);
        $task->assignees()->sync([$employee->id]);

        $this->actingAs($employee)
            ->patch(route('tasks.update-status', $task), [
                'status' => 'under_review',
                'submission_link' => 'https://example.com/deliverable',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $fresh = $task->fresh();
        $this->assertSame(TaskStatus::UnderReview->value, $fresh->status);
        $this->assertSame('https://example.com/deliverable', $fresh->submission_link);
        $this->assertNotNull($fresh->submitted_at);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $manager->id,
            'type' => 'task_ready_for_review',
            'data->task_id' => $task->id,
        ]);
    }

    public function test_employee_can_submit_task_with_image(): void
    {
        Storage::fake('public');

        $department = $this->department();
        [$manager, $employee] = $this->createReviewTeam($department);

        $task = $this->taskFor($department, $manager);
        $task->assignees()->sync([$employee->id]);

        $this->actingAs($employee)
            ->patch(route('tasks.update-status', $task), [
                'status' => 'under_review',
                'submission_image' => UploadedFile::fake()->image('proof.png', 100, 100),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $fresh = $task->fresh();
        $this->assertSame(TaskStatus::UnderReview->value, $fresh->status);
        $this->assertNotNull($fresh->submission_attachment_id);

        $attachment = TaskAttachment::find($fresh->submission_attachment_id);
        $this->assertNotNull($attachment);
        $this->assertSame('proof.png', $attachment->file_name);
        $this->assertSame($employee->id, $attachment->user_id);
        Storage::disk('public')->assertExists($attachment->file_path);
    }

    public function test_employee_detail_page_renders_start_and_submit_controls(): void
    {
        $department = $this->department();
        [$manager, $employee] = $this->createReviewTeam($department);

        $task = $this->taskFor($department, $manager, TaskStatus::New->value);
        $task->assignees()->sync([$employee->id]);

        $this->actingAs($employee)
            ->get(route('employee.my-tasks.show', $task))
            ->assertOk()
            ->assertSee('Start Task');

        $this->actingAs($employee)
            ->patch(route('tasks.update-status', $task), ['status' => 'in_progress'])
            ->assertRedirect();

        $this->actingAs($employee)
            ->get(route('employee.my-tasks.show', $task))
            ->assertOk()
            ->assertSee('Submit for Review');
    }

    public function test_manager_detail_page_renders_submission_evidence(): void
    {
        $department = $this->department();
        [$manager, $employee] = $this->createReviewTeam($department);

        $task = Task::create([
            'title' => 'Evidence task',
            'status' => TaskStatus::UnderReview->value,
            'priority' => 'medium',
            'created_by' => $manager->id,
            'submission_link' => 'https://example.com/deployed',
            'submitted_at' => now(),
        ]);
        $task->assignees()->attach($employee->id, ['assigned_at' => now()]);

        $this->actingAs($manager)
            ->get(route('manager.team-tasks.show', $task))
            ->assertOk()
            ->assertSee('Submission evidence')
            ->assertSee('https://example.com/deployed');
    }

    public function test_resubmission_tracks_updates_review_cycles_and_evidence(): void
    {
        $department = $this->department();
        [$manager, $employee, $reviewer] = $this->createReviewTeam($department);

        $task = $this->taskFor($department, $manager);
        $task->assignees()->sync([$employee->id]);
        $task->update(['reviewer_id' => $reviewer->id]);

        $this->actingAs($employee)
            ->patch(route('tasks.update-status', $task), [
                'status' => 'under_review',
                'submission_link' => 'https://example.com/v1',
            ])
            ->assertRedirect();

        $this->actingAs($reviewer)
            ->post(route('reviews.request-changes', $task), ['review_comment' => 'Revise please.'])
            ->assertRedirect();

        $sentBack = $task->fresh();
        $this->assertSame(TaskStatus::ChangesRequested->value, $sentBack->status);
        $this->assertSame('https://example.com/v1', $sentBack->submission_link);
        $this->assertSame(1, $sentBack->reviewCyclesCount());

        $this->actingAs($employee)
            ->patch(route('tasks.update-status', $task), [
                'status' => 'under_review',
                'submission_link' => 'https://example.com/v2',
            ])
            ->assertRedirect();

        $resubmitted = $task->fresh();
        $this->assertSame(TaskStatus::UnderReview->value, $resubmitted->status);
        $this->assertSame('https://example.com/v2', $resubmitted->submission_link);
        $this->assertSame(2, $resubmitted->updates_count);
        $this->assertSame(1, $resubmitted->resubmissions_count);
        $this->assertSame(1, $resubmitted->changesRequestedCount());
        $this->assertSame(1, $resubmitted->resubmissions());

        $this->actingAs($reviewer)
            ->post(route('reviews.approve', $task), ['review_comment' => 'Approved second round.'])
            ->assertRedirect();

        $approved = $task->fresh();
        $this->assertSame(TaskStatus::Completed->value, $approved->status);
        $this->assertSame(2, $approved->reviewCyclesCount());
        $this->assertSame(2, $approved->reviews()->count());
    }

    public function test_manager_can_send_task_back_with_remarks(): void
    {
        $department = $this->department();
        [$manager, $employee] = $this->createReviewTeam($department);

        $task = $this->taskFor($department, $manager, TaskStatus::UnderReview->value);
        $task->assignees()->sync([$employee->id]);

        $this->actingAs($manager)
            ->post(route('reviews.send-back', $task), [
                'update_message' => 'Align the copy with the brand tone.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(TaskStatus::ChangesRequested->value, $task->fresh()->status);
        $this->assertDatabaseHas('task_comments', [
            'task_id' => $task->id,
            'user_id' => $manager->id,
            'comment' => 'Sent back with updates: Align the copy with the brand tone.',
        ]);
    }
}
