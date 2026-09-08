<?php

use App\Http\Controllers\Admin\{
    ActivityLogController,
    DashboardController as AdminDashboardController,
    DepartmentController,
    EmployeeController,
    TaskController as AdminTaskController,
    ReportController as AdminReportController,
};
use App\Http\Controllers\Employee\{
    DashboardController as EmployeeDashboardController,
    MyTaskController,
    NotificationController,
};
use App\Http\Controllers\Manager\{
    DashboardController as ManagerDashboardController,
    PerformanceController,
    ReportController as ManagerReportController,
    TeamTaskController,
};
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    return match ($user->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'manager' => redirect()->route('manager.dashboard'),
        default => redirect()->route('employee.dashboard'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Task module - accessible to all roles
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::post('/tasks/{task}/comments', [TaskController::class, 'storeComment'])->name('tasks.comments.store');
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::post('/tasks/{task}/attachments', [TaskController::class, 'storeAttachment'])->name('tasks.attachments.store');
    Route::delete('/tasks/{task}/attachments/{attachment}', [TaskController::class, 'destroyAttachment'])->name('tasks.attachments.destroy');
});

// ===== ADMIN ROUTES =====
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('employees', EmployeeController::class);
    Route::patch('/employees/{employee}/toggle-active', [EmployeeController::class, 'toggleActive'])->name('employees.toggle-active');

    Route::resource('departments', DepartmentController::class);

    Route::resource('tasks', AdminTaskController::class);
    Route::patch('/tasks/{task}/status', [AdminTaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::patch('/tasks/{task}/assign', [AdminTaskController::class, 'assign'])->name('tasks.assign');

    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [AdminReportController::class, 'export'])->name('reports.export');
    Route::get('/reports/export-pdf', [AdminReportController::class, 'exportPdf'])->name('reports.export-pdf');

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    // Task API for Kanban drag-drop
    Route::patch('/api/tasks/{task}/move', [AdminTaskController::class, 'move'])->name('tasks.move');
    Route::post('/api/tasks/reorder', [AdminTaskController::class, 'reorder'])->name('tasks.reorder');
});

// ===== MANAGER ROUTES =====
Route::middleware(['auth', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');

    Route::get('/team-tasks', [TeamTaskController::class, 'index'])->name('team-tasks');
    Route::get('/team-tasks/board', [TeamTaskController::class, 'board'])->name('team-tasks.board');
    Route::get('/team-tasks/calendar', [TeamTaskController::class, 'calendar'])->name('team-tasks.calendar');
    Route::get('/team-tasks/gantt', [TeamTaskController::class, 'gantt'])->name('team-tasks.gantt');
    Route::get('/team-tasks/create', [TeamTaskController::class, 'create'])->name('team-tasks.create');
    Route::post('/team-tasks', [TeamTaskController::class, 'store'])->name('team-tasks.store');
    Route::get('/team-tasks/{task}', [TeamTaskController::class, 'show'])->name('team-tasks.show');
    Route::get('/team-tasks/{task}/edit', [TeamTaskController::class, 'edit'])->name('team-tasks.edit');
    Route::put('/team-tasks/{task}', [TeamTaskController::class, 'update'])->name('team-tasks.update');
    Route::delete('/team-tasks/{task}', [TeamTaskController::class, 'destroy'])->name('team-tasks.destroy');

    Route::patch('/api/tasks/{task}/move', [TeamTaskController::class, 'move'])->name('tasks.move');

    Route::get('/reports', [ManagerReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ManagerReportController::class, 'export'])->name('reports.export');

    Route::get('/performance', [PerformanceController::class, 'index'])->name('performance');
});

// ===== EMPLOYEE ROUTES =====
Route::middleware(['auth', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');

    Route::get('/my-tasks', [MyTaskController::class, 'index'])->name('my-tasks');
    Route::get('/my-tasks/{task}', [MyTaskController::class, 'show'])->name('my-tasks.show');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});

require __DIR__.'/auth.php';
