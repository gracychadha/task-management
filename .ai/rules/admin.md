---
paths:
  - app/Http/Controllers/Admin/ReportController.php
---

# Admin

## Reports are type-driven via ReportMetricsService
Admin/Manager ReportController index() takes ?type= (overview|employee|department|review|overdue|updates|turnaround) and passes summary/completionRate/statusBreakdown plus per-type data to admin.reports.index / manager.reports.index. Exports use ArrayExport (xlsx) named reports-{type}- / team-reports-{type}-; PDF export is overview-only. Tasks/statuses are read from the same mutable row, so no separate 'done'/'todo'/'review' statuses anywhere in code.
