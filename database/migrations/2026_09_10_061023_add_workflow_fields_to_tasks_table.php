<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('department_id')
                ->nullable()
                ->after('description')
                ->constrained()
                ->nullOnDelete();
            $table->unsignedInteger('updates_count')->default(0)->after('sort_order');
            $table->unsignedInteger('resubmissions_count')->default(0)->after('updates_count');
            $table->timestamp('completed_at')->nullable()->after('resubmissions_count');
        });

        DB::table('tasks')->update([
            'status' => DB::raw("CASE
                WHEN status = 'todo' THEN 'new'
                WHEN status = 'review' THEN 'under_review'
                WHEN status = 'done' AND review_decision = 'approved' THEN 'completed'
                WHEN status = 'done' THEN 'under_review'
                ELSE status
            END"),
        ]);

        DB::table('tasks')
            ->where('status', 'completed')
            ->whereNull('completed_at')
            ->update(['completed_at' => DB::raw('COALESCE(reviewed_at, updated_at)')]);

        DB::table('tasks')
            ->whereIn('status', ['completed', 'under_review'])
            ->where('updates_count', 0)
            ->update(['updates_count' => 1]);

        $reviewed = DB::table('tasks')
            ->whereNotNull('review_decision')
            ->where('status', 'completed')
            ->get(['id', 'reviewer_id', 'review_decision', 'review_comment', 'reviewed_at']);

        $number = 1;
        foreach ($reviewed as $task) {
            DB::table('task_reviews')->insert([
                'task_id' => $task->id,
                'reviewer_id' => $task->reviewer_id ?? 1,
                'review_number' => ++$number,
                'decision' => $task->review_decision,
                'comment' => $task->review_comment,
                'reviewed_at' => $task->reviewed_at,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('tasks')->update([
            'status' => DB::raw("CASE
                WHEN status = 'new' THEN 'todo'
                WHEN status = 'under_review' THEN 'review'
                WHEN status = 'completed' THEN 'done'
                ELSE status
            END"),
        ]);

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropColumn(['updates_count', 'resubmissions_count', 'completed_at']);
        });
    }
};
