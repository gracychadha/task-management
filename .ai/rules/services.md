---
paths:
  - app/Services/TaskWorkflowService.php
---

# Services

## Managers notified on every task event
Managers are notified on every task event via InteractsWithTaskNotifications::notifyManagers: creation/assignment (Admin+Manager TaskControllers store/update), reviewer assignment, submit-for-review, approve, changes-requested, send-back, and every transition() status change. notifyManagers derives recipients from assignee departments PLUS the task's own department_id, plus an admin/manager creator (skipped if they are the actor).
