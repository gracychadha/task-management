---
paths:
  - 'resources/views/**'
---

# Views

## Partial views in resources/views/partials use @include, not x-partials
Files under resources/views/partials/ (status-badge, task-progress, review-workflow, review-history, activity-timeline, metric-card) are included via @include('partials.X', [...]) because x-partials.* namespace does not resolve for them. Pass what you need (e.g. label/color, reviewerCandidates); progress/history rely on $task with reviews loaded.
