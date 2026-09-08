<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tasks Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1 { font-size: 20px; margin-bottom: 5px; }
        .subtitle { color: #666; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f3f4f6; font-weight: bold; }
        .summary { display: flex; gap: 30px; margin-bottom: 20px; }
        .stat { text-align: center; }
        .stat-value { font-size: 24px; font-weight: bold; }
        .stat-label { color: #666; font-size: 11px; }
    </style>
</head>
<body>
    <h1>Tasks Report</h1>
    <div class="subtitle">{{ $dateFrom }} to {{ $dateTo }}</div>

    <div class="summary">
        <div class="stat">
            <div class="stat-value">{{ $summary['total'] }}</div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat">
            <div class="stat-value" style="color: green">{{ $summary['completed'] }}</div>
            <div class="stat-label">Completed</div>
        </div>
        <div class="stat">
            <div class="stat-value" style="color: blue">{{ $summary['in_progress'] }}</div>
            <div class="stat-label">In Progress</div>
        </div>
        <div class="stat">
            <div class="stat-value" style="color: gray">{{ $summary['pending'] }}</div>
            <div class="stat-label">Pending</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Assignees</th>
                <th>Department</th>
                <th>Due Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tasks as $task)
                <tr>
                    <td>{{ $task->title }}</td>
                    <td>{{ $task->getStatusLabel() }}</td>
                    <td>{{ $task->getPriorityLabel() }}</td>
                    <td>{{ $task->assignees->pluck('name')->implode(', ') ?: 'Unassigned' }}</td>
                    <td>{{ $task->assignees->pluck('department.name')->unique()->implode(', ') ?: '-' }}</td>
                    <td>{{ $task->due_date?->format('Y-m-d') ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
