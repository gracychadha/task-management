---
paths:
  - 'app/Models/**'
---

# Models

## Reviewer is an employee, stored on tasks.reviewer_id
There is no reviewer role value. Reviewers are active employees picked per-task via tasks.reviewer_id (any admin can assign; manager scoped to their department). Review cycles mutate the SAME tasks row (no duplicate tasks); each review is a task_reviews row (review_number, decision, comment).
