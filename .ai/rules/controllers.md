---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## All workflow transitions go through TaskWorkflowService
Employee/manager status changes, review assignments, approve/request-changes/send-back are enforced in App\Services\TaskWorkflowService (assertTransition/assertReviewer). Controllers (TaskController, ReviewController, Admin/Manager TaskControllers) must call the service, never set status/review fields directly, so the transition matrix and notifications stay single-sourced.
