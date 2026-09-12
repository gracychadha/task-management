---
paths:
  - resources/views/admin/tasks/create.blade.php
---

# Tasks

## Create-form assignee/reviewer filtering via shared partial
Task create forms filter assignees + reviewers by the chosen department and exclude selected assignees from the reviewer dropdown. The shared picker lives in resources/views/partials/assignee-reviewer-picker.blade.php (reads `$departments` + `$reviewers` passed to both create views; picks up `old('assigned_to')`, `old('reviewer_id')`, `old('department_id')`, and defaults assignee department to the manager's own department). IMPORTANT trap: Alpine `Alpine.data()` registered via an `alpine:init` listener in a pushed script FAILS on these pages (component scope never resolves, expressions like `reviewerId` throw ReferenceError). Use a self-contained inline `x-data='(() => {...})()'` IIFE on the root div instead (single-quoted attribute keeps `@json` output safe; `@json` escapes quotes/apostrophes as \u0022/\u0027). Keep the data-department attribute on every assignee/reviewer row (tests assert those strings). Server-side, reviewer_id must not equal any assigned_to (not_in rule in both Admin/TaskController and Manager/TeamTaskController rules).

## Two-department create form (assignees + cross-department reviewer)
Create forms have TWO independent department selectors: #department_id (the task's department, binds to `departmentId`) and #reviewer_department_id (UI-only filter, NOT persisted; binds to `reviewerDepartmentId`). Selected assignees are always hidden from the reviewer dropdown. The picker submits hidden `assigned_to[]` inputs for selected users plus a single `reviewer_id` hidden input. Server-side still only stores department_id and reviewer_id.