<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->text('submission_link')->nullable()->after('review_comment');
            $table->timestamp('submitted_at')->nullable()->after('submission_link');
            $table->foreignId('submission_attachment_id')
                ->nullable()
                ->after('submitted_at')
                ->constrained('task_attachments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('submission_attachment_id');
            $table->dropColumn(['submission_link', 'submitted_at']);
        });
    }
};
