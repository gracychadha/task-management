<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('tasks', 'team_id')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropForeign(['team_id']);
                $table->dropColumn('team_id');
            });
        }

        if (Schema::hasColumn('tasks', 'assigned_to')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropForeign(['assigned_to']);
                $table->dropColumn('assigned_to');
            });
        }

        Schema::dropIfExists('team_members');
        Schema::dropIfExists('teams');

        if (Schema::hasColumn('labels', 'team_id')) {
            Schema::table('labels', function (Blueprint $table) {
                $table->dropForeign(['team_id']);
                $table->dropColumn('team_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('created_by')->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->after('priority')->constrained('users')->nullOnDelete();
        });
    }
};
